<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ActivityProperties extends Component
{
    public $properties;
    /**
     * Create a new component instance.
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.activity-properties', [
            'properties' => $this->properties
        ]);
    }
}
