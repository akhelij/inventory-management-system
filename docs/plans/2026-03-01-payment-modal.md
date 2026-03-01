# Payment Creation Modal Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Replace the full-page payment creation form with an inline Alpine.js modal on the customer show page, and add a "Pay" button on order cards that auto-allocates.

**Architecture:** Alpine.js modal integrated into the existing `allocationPanel()` component. Backend returns JSON when `Accept: application/json` is sent. Optional `order_id` triggers auto-allocation via existing `OrderPaymentController` logic.

**Tech Stack:** Alpine.js, Tabler modal CSS, Laravel `expectsJson()`, existing `slideIntoChip` animation.

---

### Task 1: Backend — PaymentController returns JSON for AJAX requests

**Files:**
- Modify: `app/Http/Controllers/PaymentController.php:23-46`
- Test: `tests/Feature/PaymentControllerTest.php` (create)

**Step 1: Write the failing test**

Create `tests/Feature/PaymentControllerTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Payment;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    #[Test]
    public function store_returns_json_when_requested(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $customer = $this->createCustomer($user);

        $response = $this->postJson("/payments/{$customer->id}", [
            'customer_id' => $customer->id,
            'nature' => 'CHQ-TEST-001',
            'payment_type' => 'Cheque',
            'bank' => 'CIH',
            'date' => now()->format('d/m/Y'),
            'echeance' => now()->addMonth()->format('d/m/Y'),
            'amount' => 5000,
            'description' => 'Test payment',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'payment' => ['id', 'nature', 'payment_type', 'date', 'echeance', 'amount', 'cashed_in', 'reported', 'unallocated_amount', 'is_fully_allocated'],
        ]);

        $this->assertDatabaseHas('payments', [
            'nature' => 'CHQ-TEST-001',
            'customer_id' => $customer->id,
        ]);
    }

    #[Test]
    public function store_returns_redirect_for_standard_requests(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $customer = $this->createCustomer($user);

        $response = $this->post("/payments/{$customer->id}", [
            'customer_id' => $customer->id,
            'nature' => 'CHQ-TEST-002',
            'payment_type' => 'Cheque',
            'bank' => 'CIH',
            'date' => now()->format('d/m/Y'),
            'echeance' => now()->addMonth()->format('d/m/Y'),
            'amount' => 3000,
        ]);

        $response->assertRedirect(route('customers.show', $customer->uuid));
    }

    #[Test]
    public function store_returns_validation_errors_as_json(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);
        $customer = $this->createCustomer($user);

        $response = $this->postJson("/payments/{$customer->id}", [
            'customer_id' => $customer->id,
            'nature' => '',
            'payment_type' => 'InvalidType',
            'amount' => '',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['nature', 'payment_type', 'amount', 'date', 'echeance']);
    }
}
```

**Step 2: Run test to verify it fails**

Run: `./vendor/bin/sail test tests/Feature/PaymentControllerTest.php --filter=store_returns_json_when_requested -v`
Expected: FAIL — controller returns redirect, not JSON

**Step 3: Implement JSON response in PaymentController@store**

Modify `app/Http/Controllers/PaymentController.php`. Change the `store` method return type and add JSON branch:

```php
use Illuminate\Http\JsonResponse;

// Change method signature (line 23):
public function store(Request $request, Customer $customer): RedirectResponse|JsonResponse
{
    $request->validate([
        'date' => 'required|date_format:d/m/Y',
        'bank' => 'string|nullable',
        'payment_type' => 'required|string|in:HandCash,Cheque,Exchange',
        'nature' => [
            'required',
            'string',
            $request->payment_type != 'HandCash' ? 'unique:payments,nature' : '',
        ],
        'echeance' => 'required|date_format:d/m/Y',
        'amount' => 'required|numeric',
        'description' => 'nullable|string|max:1000',
    ]);

    $data = $request->all();
    $data['date'] = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');
    $data['echeance'] = Carbon::createFromFormat('d/m/Y', $request->echeance)->format('Y-m-d');

    $payment = Payment::create($data);

    if ($request->expectsJson()) {
        return response()->json([
            'payment' => [
                'id' => $payment->id,
                'nature' => $payment->nature,
                'payment_type' => $payment->payment_type,
                'date' => $payment->date,
                'echeance' => $payment->echeance,
                'amount' => (float) $payment->amount,
                'bank' => $payment->bank,
                'cashed_in' => (bool) $payment->cashed_in,
                'reported' => (bool) $payment->reported,
                'unallocated_amount' => $payment->unallocated_amount,
                'is_fully_allocated' => $payment->is_fully_allocated,
                'dragging' => false,
            ],
        ], 201);
    }

    return to_route('customers.show', $customer->uuid);
}
```

**Step 4: Run tests to verify they pass**

Run: `./vendor/bin/sail test tests/Feature/PaymentControllerTest.php -v`
Expected: All 3 tests PASS

**Step 5: Commit**

```bash
git add app/Http/Controllers/PaymentController.php tests/Feature/PaymentControllerTest.php
git commit -m "feat: PaymentController returns JSON for AJAX requests"
```

---

### Task 2: Backend — Auto-allocate payment to order when order_id provided

**Files:**
- Modify: `app/Http/Controllers/PaymentController.php:23-46` (store method)
- Test: `tests/Feature/PaymentControllerTest.php`

**Step 1: Write the failing test**

Add to `tests/Feature/PaymentControllerTest.php`:

```php
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Str;

#[Test]
public function store_auto_allocates_when_order_id_provided(): void
{
    $user = $this->createUser();
    $this->actingAs($user);
    $customer = $this->createCustomer($user);

    $order = Order::create([
        'uuid' => Str::uuid(),
        'user_id' => $user->id,
        'customer_id' => $customer->id,
        'order_date' => now(),
        'order_status' => OrderStatus::APPROVED,
        'total_products' => 1,
        'sub_total' => 10000,
        'vat' => 0,
        'total' => 10000,
        'invoice_no' => 'INV-MODAL-' . Str::random(4),
        'payment_type' => 'HandCash',
        'pay' => 0,
        'due' => 10000,
    ]);

    $response = $this->postJson("/payments/{$customer->id}", [
        'customer_id' => $customer->id,
        'nature' => 'CHQ-AUTO-001',
        'payment_type' => 'Cheque',
        'bank' => 'CIH',
        'date' => now()->format('d/m/Y'),
        'echeance' => now()->addMonth()->format('d/m/Y'),
        'amount' => 5000,
        'order_id' => $order->id,
    ]);

    $response->assertCreated();
    $response->assertJsonPath('allocation.allocated_amount', 5000);
    $response->assertJsonPath('allocation.order.due', 5000);

    $this->assertDatabaseHas('order_payment', [
        'order_id' => $order->id,
        'allocated_amount' => 5000,
    ]);
}

#[Test]
public function store_ignores_invalid_order_id(): void
{
    $user = $this->createUser();
    $this->actingAs($user);
    $customer = $this->createCustomer($user);

    $response = $this->postJson("/payments/{$customer->id}", [
        'customer_id' => $customer->id,
        'nature' => 'CHQ-IGNORE-001',
        'payment_type' => 'Cheque',
        'bank' => 'CIH',
        'date' => now()->format('d/m/Y'),
        'echeance' => now()->addMonth()->format('d/m/Y'),
        'amount' => 5000,
        'order_id' => 99999,
    ]);

    $response->assertCreated();
    $response->assertJsonMissing(['allocation']);
}
```

**Step 2: Run tests to verify they fail**

Run: `./vendor/bin/sail test tests/Feature/PaymentControllerTest.php --filter=store_auto_allocates -v`
Expected: FAIL — no `allocation` key in response

**Step 3: Add auto-allocation logic to PaymentController@store**

In the JSON branch of `store()`, after creating the payment, add:

```php
if ($request->expectsJson()) {
    $responseData = [
        'payment' => [
            'id' => $payment->id,
            'nature' => $payment->nature,
            'payment_type' => $payment->payment_type,
            'date' => $payment->date,
            'echeance' => $payment->echeance,
            'amount' => (float) $payment->amount,
            'bank' => $payment->bank,
            'cashed_in' => (bool) $payment->cashed_in,
            'reported' => (bool) $payment->reported,
            'unallocated_amount' => $payment->unallocated_amount,
            'is_fully_allocated' => $payment->is_fully_allocated,
            'dragging' => false,
        ],
    ];

    // Auto-allocate to order if order_id provided
    if ($request->filled('order_id')) {
        $order = Order::where('id', $request->order_id)
            ->where('customer_id', $customer->id)
            ->where('order_status', OrderStatus::APPROVED)
            ->where('due', '>', 0)
            ->first();

        if ($order) {
            $allocatedAmount = min($payment->amount, $order->due);
            $order->payments()->attach($payment->id, [
                'allocated_amount' => $allocatedAmount,
                'user_id' => auth()->id(),
            ]);
            $order->recalculatePayments();
            $order->refresh();
            $payment->refresh();

            $responseData['allocation'] = [
                'allocated_amount' => $allocatedAmount,
                'order' => [
                    'id' => $order->id,
                    'pay' => $order->pay,
                    'due' => $order->due,
                ],
            ];
            $responseData['payment']['unallocated_amount'] = $payment->unallocated_amount;
            $responseData['payment']['is_fully_allocated'] = $payment->is_fully_allocated;
        }
    }

    return response()->json($responseData, 201);
}
```

Add imports at top of file:

```php
use App\Enums\OrderStatus;
use App\Models\Order;
```

Also add `'order_id' => 'nullable|integer'` to the validation rules.

**Step 4: Run tests to verify they pass**

Run: `./vendor/bin/sail test tests/Feature/PaymentControllerTest.php -v`
Expected: All 5 tests PASS

**Step 5: Commit**

```bash
git add app/Http/Controllers/PaymentController.php tests/Feature/PaymentControllerTest.php
git commit -m "feat: auto-allocate payment to order when order_id provided"
```

---

### Task 3: Pass bank data to customer show view

**Files:**
- Modify: `app/Http/Controllers/CustomerController.php:82-91`

**Step 1: Add `banks` to the view data**

In `CustomerController@show` (line 82), add `banks` to the array:

```php
return view('customers.show', [
    'customer' => $customer,
    'totalOrders' => $customer->total_orders,
    'totalPayments' => $customer->total_payments,
    'due' => $due,
    'amountPendingPayments' => $customer->total_pending_payments,
    'diff' => $due,
    'limit_reached' => $customer->is_out_of_limit,
    'allocationEnabled' => $allocationEnabled,
    'banks' => MoroccanBank::cases(),
]);
```

`MoroccanBank` is already imported at the top of this file.

**Step 2: Verify no errors**

Run: `./vendor/bin/sail test tests/Feature/PaymentControllerTest.php -v`
Expected: PASS (no regressions)

**Step 3: Commit**

```bash
git add app/Http/Controllers/CustomerController.php
git commit -m "feat: pass bank enum to customer show view for modal"
```

---

### Task 4: Frontend — Add modal HTML to allocation panel

**Files:**
- Modify: `resources/views/customers/partials/_allocation-panel.blade.php`

**Step 1: Replace the "+" link with a modal trigger button**

Replace line 108:

```blade
<a href="{{ '/payments/'.$customer->id.'/create'}}" class="btn btn-sm btn-primary" title="{{ __('Add Payment') }}">
```

With:

```blade
<button type="button" class="btn btn-sm btn-primary" title="{{ __('Add Payment') }}"
        @click="openModal()">
```

Keep the SVG icon inside unchanged. Close with `</button>` instead of `</a>` (line 114 changes from `</a>` to `</button>`).

**Step 2: Add "Pay" button to order cards**

After line 57 (the `Due:` small text), add a "Pay" button visible when `order.due > 0` and `order.status === 'Approved'`:

```blade
<button class="btn btn-sm btn-outline-primary mt-1 py-0 px-1"
        x-show="order.due > 0 && order.status === 'Approved'"
        @click="openModal(order)"
        style="font-size: 0.7rem;">
    {{ __('Pay') }}
</button>
```

**Step 3: Add the modal markup**

Insert before the closing `</div>` of the root `x-data` div (before line 242). Place it after the loading overlay template (after line 241):

```blade
<!-- Payment Creation Modal -->
<div class="modal modal-blur fade" :class="{ show: modal.open }"
     :style="modal.open ? 'display: block;' : 'display: none;'"
     x-show="modal.open" x-cloak
     @keydown.escape.window="closeModal()"
     tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document"
         @click.outside="closeModal()">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Add Payment') }}</h5>
                <button type="button" class="btn-close" @click="closeModal()"></button>
            </div>
            <div class="modal-body">
                <!-- Validation errors -->
                <div class="alert alert-danger" x-show="Object.keys(modal.errors).length > 0" x-cloak>
                    <ul class="mb-0">
                        <template x-for="(msgs, field) in modal.errors" :key="field">
                            <template x-for="msg in msgs" :key="msg">
                                <li x-text="msg"></li>
                            </template>
                        </template>
                    </ul>
                </div>

                <div class="row gx-3">
                    <!-- Nature -->
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Nature') }}</label>
                        <input type="text" class="form-control" x-model="modal.form.nature"
                               :class="{ 'is-invalid': modal.errors.nature }">
                    </div>

                    <!-- Payment Type -->
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Payment type') }}</label>
                        <select class="form-select" x-model="modal.form.payment_type"
                                :class="{ 'is-invalid': modal.errors.payment_type }">
                            <option value="HandCash">Cash</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Exchange">Lettre de change</option>
                        </select>
                    </div>

                    <!-- Bank (shown for Cheque/Exchange) -->
                    <div class="col-6 mb-3" x-show="modal.form.payment_type !== 'HandCash'">
                        <label class="form-label">{{ __('Bank') }}</label>
                        <select class="form-select" x-model="modal.form.bank"
                                :class="{ 'is-invalid': modal.errors.bank }">
                            <option value="">{{ __('Select a bank:') }}</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank->value }}">{{ $bank->value }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date -->
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Date') }}</label>
                        <input type="text" class="form-control" x-model="modal.form.date"
                               placeholder="dd/mm/yyyy"
                               :class="{ 'is-invalid': modal.errors.date }"
                               @input="formatDateInput($event)">
                    </div>

                    <!-- Echeance -->
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Echeance') }}</label>
                        <input type="text" class="form-control" x-model="modal.form.echeance"
                               placeholder="dd/mm/yyyy"
                               :class="{ 'is-invalid': modal.errors.echeance }"
                               @input="formatDateInput($event)">
                    </div>

                    <!-- Amount -->
                    <div class="col-6 mb-3">
                        <label class="form-label">{{ __('Amount') }}</label>
                        <input type="number" class="form-control" x-model="modal.form.amount"
                               step="0.01"
                               :class="{ 'is-invalid': modal.errors.amount }">
                    </div>

                    <!-- Description -->
                    <div class="col-12 mb-3">
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea class="form-control" x-model="modal.form.description" rows="2"></textarea>
                    </div>
                </div>

                <!-- Auto-allocate hint -->
                <div class="alert alert-info py-2" x-show="modal.orderId" x-cloak>
                    <small>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M12 9v4"/>
                            <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z"/>
                            <path d="M12 16h.01"/>
                        </svg>
                        {{ __('This payment will be automatically allocated to order') }}
                        <strong x-text="modal.orderInvoice"></strong>
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn me-auto" @click="closeModal()">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" @click="submitPayment()" :disabled="modal.submitting">
                    <span class="spinner-border spinner-border-sm me-1" x-show="modal.submitting"></span>
                    {{ __('Add Payment') }}
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Modal backdrop -->
<div class="modal-backdrop fade" :class="{ show: modal.open }"
     x-show="modal.open" x-cloak></div>
```

**Step 4: Verify the template renders without errors**

Open the customer show page in the browser and verify no Blade compilation errors. The modal should be hidden by default.

**Step 5: Commit**

```bash
git add resources/views/customers/partials/_allocation-panel.blade.php
git commit -m "feat: add payment creation modal HTML and Pay button on order cards"
```

---

### Task 5: Frontend — Add Alpine.js modal state and methods

**Files:**
- Modify: `resources/views/customers/partials/_allocation-panel.blade.php` (the `<script>` block)

**Step 1: Add modal state to `allocationPanel()` return object**

After `loading: false,` (line 293), add:

```javascript
modal: {
    open: false,
    submitting: false,
    orderId: null,
    orderInvoice: '',
    errors: {},
    form: {
        nature: '',
        payment_type: 'HandCash',
        bank: '',
        date: new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }),
        echeance: '',
        amount: '',
        description: '',
    },
},
```

**Step 2: Add modal methods**

After the `detachAllocation` method (after line 424), add:

```javascript
openModal(order = null) {
    this.modal.errors = {};
    this.modal.form = {
        nature: '',
        payment_type: 'HandCash',
        bank: '',
        date: new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }),
        echeance: '',
        amount: order ? order.due : '',
        description: '',
    };
    this.modal.orderId = order ? order.id : null;
    this.modal.orderInvoice = order ? order.invoice_no : '';
    this.modal.submitting = false;
    this.modal.open = true;
    document.body.classList.add('modal-open');
},

closeModal() {
    this.modal.open = false;
    this.modal.errors = {};
    document.body.classList.remove('modal-open');
},

formatDateInput(event) {
    let value = event.target.value.replace(/\D/g, '');
    if (value.length >= 2) value = value.substring(0, 2) + '/' + value.substring(2);
    if (value.length >= 5) value = value.substring(0, 5) + '/' + value.substring(5, 9);
    event.target.value = value;
    // Sync back to Alpine model
    const model = event.target.getAttribute('x-model');
    if (model === 'modal.form.date') this.modal.form.date = value;
    if (model === 'modal.form.echeance') this.modal.form.echeance = value;
},

async submitPayment() {
    this.modal.submitting = true;
    this.modal.errors = {};

    const body = {
        customer_id: {{ $customer->id }},
        nature: this.modal.form.nature,
        payment_type: this.modal.form.payment_type,
        bank: this.modal.form.bank || null,
        date: this.modal.form.date,
        echeance: this.modal.form.echeance,
        amount: this.modal.form.amount,
        description: this.modal.form.description || null,
    };

    if (this.modal.orderId) {
        body.order_id = this.modal.orderId;
    }

    try {
        const response = await fetch(`/payments/{{ $customer->id }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(body),
        });

        const data = await response.json();

        if (!response.ok) {
            if (response.status === 422 && data.errors) {
                this.modal.errors = data.errors;
            } else {
                alert(data.message || 'Error creating payment.');
            }
            return;
        }

        // Add new payment to list with animation
        this.payments.unshift(data.payment);
        this.$nextTick(() => {
            const firstCard = this.$el.querySelector('.col-7 .card-body .card');
            if (firstCard) {
                firstCard.classList.add('animating-in');
                setTimeout(() => firstCard.classList.remove('animating-in'), 400);
            }
        });

        // If auto-allocated, update order card
        if (data.allocation) {
            const order = this.orders.find(o => o.id === this.modal.orderId);
            if (order) {
                order.pay = data.allocation.order.pay;
                order.due = data.allocation.order.due;
                order.allocations.push({
                    payment_id: data.payment.id,
                    nature: data.payment.nature,
                    allocated_amount: data.allocation.allocated_amount,
                });
            }
        }

        this.closeModal();

    } catch (error) {
        console.error('Payment creation error:', error);
        alert('Network error. Please try again.');
    } finally {
        this.modal.submitting = false;
    }
},
```

**Step 3: Add slideIntoChip animation to card elements**

In the `<style>` block at the top (line 17), add a rule for card animation:

```css
.card.animating-in { animation: slideIntoChip 0.4s ease-out; }
```

**Step 4: Test manually**

1. Go to a customer page with orders and payments
2. Click "+" button → modal opens with empty fields
3. Fill form and submit → payment slides into list
4. Click "Pay" on an approved order with `due > 0` → modal opens with amount prefilled
5. Submit → payment appears in list AND allocation chip appears on order card
6. Submit with empty fields → validation errors display inline

**Step 5: Commit**

```bash
git add resources/views/customers/partials/_allocation-panel.blade.php
git commit -m "feat: wire modal Alpine.js state, methods, and auto-allocation"
```

---

### Task 6: Run full test suite and fix any issues

**Step 1: Run all tests**

Run: `./vendor/bin/sail test -v`
Expected: All tests pass

**Step 2: Run linter**

Run: `./vendor/bin/pint`

**Step 3: Fix any failures**

If any test fails, debug and fix. Common issues:
- `createCustomer` helper might not exist in base TestCase — check existing tests for pattern
- Date format mismatches between test data and validation

**Step 4: Commit any fixes**

```bash
git add -A
git commit -m "fix: address test/lint issues from payment modal"
```

---

### Task 7: Final cleanup and push

**Step 1: Verify everything works end-to-end**

Manual checklist:
- [ ] "+" button opens modal with empty fields
- [ ] Modal closes on Escape, backdrop click, or Cancel
- [ ] Form validation errors display inline
- [ ] Payment creation works, payment slides into list
- [ ] "Pay" button appears on approved orders with due > 0
- [ ] Pay button prefills amount with order's due
- [ ] Auto-allocation creates chip on order card
- [ ] Progress bar updates on new payment
- [ ] Bank dropdown shows only for Cheque/Exchange

**Step 2: Push**

```bash
git push
```
