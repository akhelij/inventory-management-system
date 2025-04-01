<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Toast extends Component
{
    /**
     * Default toast type
     */
    public string $type;
    
    /**
     * Title for the toast (optional)
     */
    public ?string $title;
    
    /**
     * Position of the toast
     */
    public string $position;
    
    /**
     * Timeout in milliseconds
     */
    public int $timeout;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        string $type = 'info',
        ?string $title = null,
        string $position = 'top-end',
        int $timeout = 5000
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->position = $position;
        $this->timeout = $timeout;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.toast');
    }
} 