<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ActivityProperties extends Component
{
    public function __construct(
        public array $properties,
    ) {}

    public function render(): View
    {
        return view('components.activity-properties');
    }
}
