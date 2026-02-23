# Payment-to-Order Allocation System — Design Document

**Date:** 2026-02-22
**Status:** Approved
**Author:** Mohamed / Claude

---

## Problem

The current system has two disconnected payment mechanisms:

1. **PaymentObserver auto-cascade:** When a payment is cashed in, it automatically sweeps across the customer's orders (LIFO), reducing `due`/`pay` and setting `order_status = 2`. This status value is undefined in the `OrderStatus` enum, causing orders to show as "Unknown", disappear from filtered views, and enter an unrecoverable state.

2. **DueOrderController:** A separate modal that lets users pay down a specific order's `due` directly, with no payment record created.

Neither matches the actual business process. The owner wants to manually match specific cashed-in payments (checks, cash, lettres de change) to specific orders, with the ability to split a single payment across multiple orders.

---

## Solution

A manual drag-and-drop allocation system on the customer page. The owner drags cashed-in payment cards onto order cards to allocate amounts. A pivot table tracks the allocations. A ticket-tear animation visualizes partial splits.

---

## Data Model

### New table: `order_payment`

| Column             | Type           | Constraints                        |
|--------------------|----------------|------------------------------------|
| `id`               | bigint PK      | auto-increment                     |
| `order_id`         | FK -> orders   | cascade delete                     |
| `payment_id`       | FK -> payments | cascade delete                     |
| `allocated_amount` | decimal(10,2)  | > 0                                |
| `user_id`          | FK -> users    | who performed the allocation       |
| `created_at`       | timestamp      |                                    |
| `updated_at`       | timestamp      |                                    |

**Unique constraint:** `(order_id, payment_id)` — one allocation record per pair.

**Application-level constraints:**
- `sum(allocated_amount for a payment) <= payment.amount`
- `sum(allocated_amount for an order) <= order.total`

### Changes to existing tables

**`orders` table:**
- `pay` and `due` kept as denormalized cache
- Recalculated on every allocation change: `pay = sum(allocated_amounts)`, `due = total - pay`
- Orders previously corrupted with `order_status = 2` reset to `order_status = 1` (APPROVED) with `pay = 0`, `due = total`

**`payments` table:** No schema changes. New computed attributes on the model:
- `unallocated_amount` = `amount - sum(order_payment.allocated_amount)`
- `is_fully_allocated` = `unallocated_amount == 0`

### Cleanup migration

Reset all orders with `order_status = 2` (set by the broken observer):
- `order_status` -> `1` (APPROVED, since they were approved before the cascade mangled them)
- `pay` -> `0`
- `due` -> `total`

---

## Backend

### New controller: `OrderPaymentController`

**`store(order, payment)`** — Allocate a payment to an order
- Validate: same customer, payment is cashed_in, payment has unallocated remainder, order is approved, order has due > 0
- Calculate: `allocated_amount = min(payment.unallocated_amount, order.due)`
- Create pivot record
- Recalculate order's `pay`/`due`
- Return updated state (order with allocations, payment with new unallocated_amount)

**`destroy(order, payment)`** — Detach a payment from an order
- Delete pivot record
- Recalculate order's `pay`/`due`
- Return updated state

### PaymentObserver changes

**`updated()` method:** Remove the entire auto-cascade logic. The method becomes empty (or removed entirely).

### Payment un-cash protection

If a payment has any allocations (`order_payment` records), block the un-cash action. The owner must detach all allocations first.

### Cascade behaviors

- **Order canceled:** All allocations for that order are deleted. Payments regain their available amounts.
- **Order deleted:** Same — cascade delete on pivot.
- **Payment deleted:** Cascade delete on pivot. Affected orders get `pay`/`due` recalculated.

### Validation rules summary

| Rule | Enforced where |
|------|----------------|
| Payment belongs to same customer as order | Controller validation |
| Payment must be `cashed_in = true` | Controller validation |
| Payment must have `unallocated_amount > 0` | Controller validation |
| Order must have `due > 0` | Controller validation |
| Order must be approved (`order_status = 1`) | Controller validation |
| `allocated_amount <= payment.unallocated_amount` | Auto-calculated (min of both caps) |
| `allocated_amount <= order.due` | Auto-calculated (min of both caps) |
| One allocation per order-payment pair | Database unique constraint |

---

## Frontend: Customer Page Drag-and-Drop

### Layout

The existing two-column layout on the customer show page is enhanced:

- **Left column (Orders):** Each order is a card showing invoice number, total, due, and a drop zone. Attached payment chips are displayed inside the card.
- **Right column (Payments):** Each cashed-in payment is a draggable card showing nature, amount, type, and a progress bar for allocated vs remaining. Pending/reported payments are greyed out and not draggable.

### Interaction flow

1. **Drag:** User grabs a cashed-in payment card. Pending payments are not draggable.
2. **Hover over order:** Order card highlights as a drop zone. Preview text shows: "Will allocate X MAD".
3. **Drop:** Auto-allocates `min(payment.unallocated, order.due)`. Triggers the split animation.
4. **Settle:** Payment card shows updated progress bar. Order card shows the new payment chip.
5. **Detach:** Click X on a payment chip inside an order. Reverse animation, amount flows back.

### Drop zone rules

| Order state | Drop zone |
|-------------|-----------|
| Approved, `due > 0` | Active (accepts drops) |
| Approved, `due = 0` | Disabled, green "Fully Paid" badge |
| Pending | No drop zone |
| Canceled | No drop zone |

---

## Frontend: Ticket-Tear Split Animation

### Sequence (~800ms)

**Step 1 (0ms):** Drop detected. Payment card lands on the order area.

**Step 2 (~200ms):** A perforated zigzag line appears across the card at the split point (proportional to the allocated vs remaining amounts).

**Step 3 (~400ms):** The card tears apart along the zigzag line. The two pieces separate.

**Step 4 (~800ms):** The allocated piece slides and shrinks into a chip inside the order card. The remaining piece slides back to the payments column with updated amount and progress bar.

### Full consumption

When the entire payment goes to one order (no remainder), the card smoothly slides from the payments column into the order's attached area, scaling down into chip form. No tear needed.

### Detach (reverse)

Click X on a chip: the chip pops out, grows to card size, slides back to the payments column. If the payment was split, the two pieces visually merge back.

### Zigzag edge

Both pieces of a split payment keep a torn zigzag edge on the cut side (CSS `clip-path` or SVG mask). Payment chips inside orders retain a subtle torn-left-edge as a visual cue that they are pieces of a larger payment.

### Tech approach

- Drag-and-drop: HTML5 Drag & Drop API or SortableJS
- Animations: CSS transitions + keyframes, clip-path for zigzag
- State management: Alpine.js (consistent with existing codebase)
- API calls: Fetch POST/DELETE to `OrderPaymentController`, returns updated state

---

## Edge Cases

| Scenario | Behavior |
|----------|----------|
| Payment deleted after allocation | Cascade delete pivot records. Affected orders recalculate `pay`/`due`. Chips disappear from order cards. |
| Order canceled after payments attached | Allocations released. Pivot records deleted. Payments regain available amounts. Chips fly back to payments column. |
| Order deleted | Same as cancellation — cascade delete, payments freed. |
| Un-cash a payment with allocations | Blocked. Owner must detach all allocations first, then un-cash. |
| Payment fully allocated | Card stays in payments column but is not draggable. Shows "Fully Allocated" badge. |
| Customer-level balance totals | Unchanged. Paid/Pending/Due aggregates remain the same calculation. Allocation is a granular organizational layer on top. |

---

## What stays the same

- Customer-level balance aggregates (Paid / Pending / Due)
- Payment CRUD (create, cash-in, report, delete)
- Order approval/cancellation workflow
- `OrderStatus` enum (PENDING = null, APPROVED = 1, CANCELED = 0) — no new status added
- Stock management (deduct on approve, restore on cancel)
- Activity logging via Spatie

---

## Permissions

Payment allocation uses the existing permission system. The `allocate payments` action requires the user to have the `update orders` permission (or a new dedicated `allocate payments` permission if finer-grained control is needed later).
