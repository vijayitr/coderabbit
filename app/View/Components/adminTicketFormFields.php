<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AdminTicketFormFields extends Component
{
    // Public property to store data
    public $data;
    public $index;

    /**
     * Create a new component instance.
     */
    public function __construct($data = [], $index = 0)
    {
        $this->data = $data;
        $this->index = $index;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin-ticket-form-fields', ['data' => $this->data, 'index' => $this->index]);
    }
}
