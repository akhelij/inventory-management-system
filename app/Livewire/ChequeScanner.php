<?php

namespace App\Livewire;

use App\Services\ChequeOcrService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChequeScanner extends Component
{
    use WithFileUploads;

    #[Validate('required|image|max:5120')]
    public $chequeImage;

    public ?string $nature = null;

    public ?float $amount = null;

    public ?string $bank = null;

    public ?string $echeance = null;

    public ?string $accountHolder = null;

    public ?string $chequePhotoPath = null;

    public bool $processing = false;

    public bool $scanned = false;

    public ?string $error = null;

    public function scan(): void
    {
        $this->validate(['chequeImage' => 'required|image|max:5120']);
        $this->processing = true;
        $this->error = null;

        $result = app(ChequeOcrService::class)->extract($this->chequeImage);

        if ($result['success']) {
            $data = $result['data'];
            $this->nature = $data['nature'] ?? null;
            $this->amount = $data['amount'] ?? null;
            $this->bank = $data['bank'] ?? null;
            $this->echeance = $data['echeance'] ?? null;
            $this->accountHolder = $data['account_holder'] ?? null;
            $this->chequePhotoPath = $this->chequeImage->store('payments/cheques', 'public');
            $this->scanned = true;

            $this->dispatch('cheque-scanned', data: [
                'nature' => $this->nature,
                'amount' => $this->amount,
                'bank' => $this->bank,
                'echeance' => $this->echeance,
                'account_holder' => $this->accountHolder,
                'cheque_photo' => $this->chequePhotoPath,
            ]);
        } else {
            $this->error = $result['error'];
            $this->scanned = true;
        }

        $this->processing = false;
    }

    public function render()
    {
        return view('livewire.cheque-scanner');
    }
}
