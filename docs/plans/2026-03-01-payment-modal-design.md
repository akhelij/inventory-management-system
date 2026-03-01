# Payment Creation Modal + Order Pay Button

## Summary

Replace the full-page payment creation form with an inline Alpine.js modal on the customer show page. Add a "Pay" button on each order card that opens the same modal with amount prefilled and auto-allocates after creation.

## Entry Points

1. **"+" button** in Payments card header — opens modal with empty fields
2. **"Pay" button** on Approved order cards with `due > 0` — opens modal with amount prefilled to `order.due`, auto-allocates to that order after creation

## Modal Form Fields

- Nature (text, unique for non-cash)
- Payment Type (HandCash / Cheque / Exchange)
- Bank (dropdown, shown only for Cheque/Exchange)
- Date (d/m/Y)
- Echeance (d/m/Y)
- Amount (numeric, prefilled when opened from order)
- Description (optional textarea)

## Backend

Modify `PaymentController@store` to detect JSON requests (`$request->expectsJson()`) and return JSON instead of redirect. Response includes all fields needed by the allocation panel.

Accept optional `order_id` parameter. When present, auto-allocate the created payment to that order using existing `OrderPaymentController` logic.

Validation rules unchanged from existing implementation.

## Frontend Flow

1. User clicks "+" or "Pay" button
2. Modal opens (Tabler `.modal` styling)
3. User fills form, submits via `fetch()`
4. On success: modal closes, payment slides into list with animation
5. If auto-allocated: order card updates `pay`/`due`, allocation chip appears
6. Header totals (Paid/Pending/Due) update
7. On validation error: display field errors inline

## Tech

- Alpine.js modal state within existing `allocationPanel()` component
- `fetch()` to `POST /payments/{customer}` with `Accept: application/json`
- Existing `slideIntoChip` CSS animation for new payment appearance
- No new dependencies or Livewire components
