<?php

namespace App\Livewire;

use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Slug extends Component
{
    #[Validate('required')]
    public string $slug = '';

    #[On('name-selected')]
    public function generateSlug(string $selectedName): void
    {
        $this->slug = Str::slug($selectedName, '-');
    }

    public function render()
    {
        return view('livewire.slug');
    }
}
