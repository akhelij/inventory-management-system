<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;

class Name extends Component
{
    #[Validate('required')]
    public string $name = '';

    public function selectedName(): void
    {
        $this->dispatch('name-selected', selectedName: $this->name)
            ->to(Slug::class);
    }

    public function render()
    {
        return view('livewire.name');
    }
}
