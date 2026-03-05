<?php

namespace App\View\Components\Profile;

use Illuminate\View\Component;
use Illuminate\View\View;

class Modals extends Component
{
    public $designer;

    /**
     * Create a new component instance.
     */
    public function __construct($designer)
    {
        $this->designer = $designer;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.profile.modals');
    }
}
