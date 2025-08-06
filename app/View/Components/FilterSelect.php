<?php

namespace App\View\Components;

use Illuminate\View\Component;

class FilterSelect extends Component
{
    public $label;
    public $options;
    public $selectedOption;
    public $id; // Add an ID property

    /**
     * Create a new component instance.
     *
     * @param string $label
     * @param array $options
     * @param string $selectedOption
     * @param string $id
     */
    public function __construct($label, $options, $selectedOption, $id)
    {
        $this->label = $label;
        $this->options = $options;
        $this->selectedOption = $selectedOption;
        $this->id = $id; // Initialize the ID
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.filter-select');
    }
}
