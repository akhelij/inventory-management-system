<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\InstallmentGuarantee;
use App\Models\PaymentSchedule;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class InstallmentGuaranteeForm extends Component
{
    public ?int $scheduleId = null;

    public ?int $guaranteeId = null;

    public string $type = 'person';

    public string $customerSearch = '';

    public Collection $customerMatches;

    public ?int $personCustomerId = null;

    public ?array $personPreview = null;

    public ?string $chequeNature = null;

    public ?float $chequeAmount = null;

    public ?string $chequeBank = null;

    public ?string $chequeEcheance = null;

    public ?string $chequePhoto = null;

    public ?string $error = null;

    public function mount(): void
    {
        $this->customerMatches = collect();
    }

    #[On('guarantee:prepare')]
    public function prepare(int $scheduleId): void
    {
        $this->reset(['guaranteeId', 'customerSearch', 'personCustomerId', 'personPreview',
            'chequeNature', 'chequeAmount', 'chequeBank', 'chequeEcheance',
            'chequePhoto', 'error']);
        $this->customerMatches = collect();
        $this->type = 'person';

        $this->scheduleId = $scheduleId;

        $schedule = PaymentSchedule::with('guarantee.person')->find($this->scheduleId);
        if (! $schedule) {
            $this->error = __('Schedule not found.');

            return;
        }

        if ($schedule->guarantee) {
            $g = $schedule->guarantee;
            $this->guaranteeId = $g->id;
            $this->type = $g->type;

            if ($g->type === 'person' && $g->person) {
                $this->personCustomerId = $g->person->id;
                $this->personPreview = [
                    'id' => $g->person->id,
                    'name' => $g->person->name,
                    'cin' => $g->person->cin,
                    'phone' => $g->person->phone,
                    'cin_photo' => $g->person->cin_photo,
                ];
            }

            if ($g->type === 'cheque') {
                $this->chequeNature = $g->cheque_nature;
                $this->chequeAmount = $g->cheque_amount !== null ? (float) $g->cheque_amount : null;
                $this->chequeBank = $g->cheque_bank;
                $this->chequeEcheance = $g->cheque_echeance?->format('Y-m-d');
                $this->chequePhoto = $g->cheque_photo;
            }
        }

        $this->js('bootstrap.Modal.getOrCreateInstance(document.getElementById("guaranteeModal")).show();');
    }

    public function setType(string $type): void
    {
        if (! in_array($type, ['person', 'cheque'], true)) {
            return;
        }
        $this->type = $type;
    }

    public function updatedCustomerSearch(): void
    {
        if (trim($this->customerSearch) === '') {
            $this->customerMatches = collect();

            return;
        }

        $this->customerMatches = Customer::query()
            ->search($this->customerSearch)
            ->limit(8)
            ->get(['id', 'name', 'cin', 'phone', 'cin_photo']);
    }

    public function selectPerson(int $customerId): void
    {
        $customer = Customer::find($customerId);
        if (! $customer) {
            return;
        }

        $this->personCustomerId = $customer->id;
        $this->personPreview = [
            'id' => $customer->id,
            'name' => $customer->name,
            'cin' => $customer->cin,
            'phone' => $customer->phone,
            'cin_photo' => $customer->cin_photo,
        ];
        $this->customerSearch = '';
        $this->customerMatches = collect();
    }

    public function clearPerson(): void
    {
        $this->personCustomerId = null;
        $this->personPreview = null;
    }

    #[On('cheque-scanned')]
    public function onChequeScanned(array $data): void
    {
        $this->chequeNature = $data['nature'] ?? null;
        $this->chequeAmount = isset($data['amount']) ? (float) $data['amount'] : null;
        $this->chequeBank = $data['bank'] ?? null;
        $this->chequePhoto = $data['cheque_photo'] ?? null;

        if (! empty($data['echeance']) && preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $data['echeance'], $m)) {
            $this->chequeEcheance = $m[3].'-'.$m[2].'-'.$m[1];
        } else {
            $this->chequeEcheance = $data['echeance'] ?? null;
        }
    }

    public function save(): void
    {
        if (! $this->scheduleId) {
            return;
        }

        if ($this->type === 'person') {
            $this->validate([
                'personCustomerId' => 'required|integer|exists:customers,id',
            ]);

            $payload = [
                'payment_schedule_id' => $this->scheduleId,
                'type' => 'person',
                'person_customer_id' => $this->personCustomerId,
                'cheque_nature' => null,
                'cheque_amount' => null,
                'cheque_bank' => null,
                'cheque_echeance' => null,
                'cheque_photo' => null,
            ];
        } else {
            $this->validate([
                'chequeAmount' => 'required|numeric|min:0',
                'chequeNature' => 'nullable|string|max:255',
                'chequeBank' => 'nullable|string|max:255',
                'chequeEcheance' => 'nullable|date',
                'chequePhoto' => 'nullable|string|max:500',
            ]);

            $payload = [
                'payment_schedule_id' => $this->scheduleId,
                'type' => 'cheque',
                'person_customer_id' => null,
                'cheque_nature' => $this->chequeNature,
                'cheque_amount' => $this->chequeAmount,
                'cheque_bank' => $this->chequeBank,
                'cheque_echeance' => $this->chequeEcheance,
                'cheque_photo' => $this->chequePhoto,
            ];
        }

        InstallmentGuarantee::updateOrCreate(
            ['payment_schedule_id' => $this->scheduleId],
            $payload
        );

        session()->flash('success', __('Guarantee saved.'));
        $this->js('
            bootstrap.Modal.getInstance(document.getElementById("guaranteeModal"))?.hide();
            setTimeout(() => window.location.reload(), 200);
        ');
    }

    public function delete(): void
    {
        if (! $this->scheduleId) {
            return;
        }

        InstallmentGuarantee::where('payment_schedule_id', $this->scheduleId)->delete();

        session()->flash('success', __('Guarantee removed.'));
        $this->js('
            bootstrap.Modal.getInstance(document.getElementById("guaranteeModal"))?.hide();
            setTimeout(() => window.location.reload(), 200);
        ');
    }

    public function render()
    {
        return view('livewire.installment-guarantee-form');
    }
}
