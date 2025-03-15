<?php

namespace App\Livewire;

use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Slug extends Component
{
    #[Rule('required')]
    public $slug = '';

    public function render()
    {
        return view('livewire.slug');
    }

    #[On('name-selected')]
    public function generateSlug($selectedName): void
    {
        $this->slug = Str::slug($selectedName, '-');
    }
}
