<?php

namespace App\Livewire;

use App\Services\CinOcrService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class CinScanner extends Component
{
    use WithFileUploads;

    #[Validate('required|image|max:5120')]
    public $cinImage;

    public ?string $name = null;

    public ?string $cin = null;

    public ?string $dateOfBirth = null;

    public ?string $address = null;

    public ?string $cinPhotoPath = null;

    public bool $processing = false;

    public bool $scanned = false;

    public ?string $error = null;

    public function scan(): void
    {
        $this->validate(['cinImage' => 'required|image|max:5120']);
        $this->processing = true;
        $this->error = null;

        $result = app(CinOcrService::class)->extract($this->cinImage);

        if ($result['success']) {
            $data = $result['data'];
            $this->name = $data['name'] ?? null;
            $this->cin = $data['cin'] ?? null;
            $this->dateOfBirth = $data['date_of_birth'] ?? null;
            $this->address = $data['address'] ?? null;
            $this->cinPhotoPath = $this->cinImage->store('customers/cin', 'public');
            $this->scanned = true;

            $this->dispatch('cin-scanned', data: [
                'name' => $this->name,
                'cin' => $this->cin,
                'date_of_birth' => $this->dateOfBirth,
                'address' => $this->address,
                'cin_photo' => $this->cinPhotoPath,
            ]);
        } else {
            $this->error = $result['error'];
            $this->scanned = true;
        }

        $this->processing = false;
    }

    public function render()
    {
        return view('livewire.cin-scanner');
    }
}
