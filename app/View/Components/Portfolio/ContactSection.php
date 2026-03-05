<?php

namespace App\View\Components\Portfolio;

use Illuminate\View\Component;
use Illuminate\View\View;

class ContactSection extends Component
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
        return view('components.portfolio.contact-section');
    }
}
