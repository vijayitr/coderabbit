<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Select extends Component
{
    public $name;
    public $label;
    public $options;
    public $selected;
    public $iconImg;

    /**
     * Create a new component instance.
     *
     * @param string $name
     * @param string $label
     * @param array $options
     * @param string|null $selected
     */
    public function __construct($name, $label, $options = [], $selected = null, $iconImg = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->options = $options;
        $this->selected = $selected;
        $this->iconImg = $iconImg;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.select');
    }
}
