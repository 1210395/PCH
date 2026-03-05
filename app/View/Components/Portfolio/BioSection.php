<?php

namespace App\View\Components\Portfolio;

use Illuminate\View\Component;
use Illuminate\View\View;

class BioSection extends Component
{
    public $designer;
    public $isOwner;

    /**
     * Create a new component instance.
     */
    public function __construct($designer, bool $isOwner)
    {
        $this->designer = $designer;
        $this->isOwner = $isOwner;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.portfolio.bio-section');
    }
}
