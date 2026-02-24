<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Toast extends Component
{
    public function __construct(
        public string $type = 'info',
        public ?string $title = null,
        public string $position = 'top-end',
        public int $timeout = 5000,
    ) {}

    public function render(): View
    {
        return view('components.toast');
    }
}
