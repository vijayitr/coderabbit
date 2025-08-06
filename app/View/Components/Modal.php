<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Modal extends Component
{
    public $id;
    public $title;
    public $body;
    public $buttons;

    public function __construct($id = 'modal', $title = 'Modal Title', $body = 'Modal Body', $buttons = [])
    {
        $this->id = $id;
        $this->title = $title;
        $this->body = $body;
        $this->buttons = $buttons; // Expect an array of buttons
    }

    public function render()
    {
        return view('components.modal');
    }
}
