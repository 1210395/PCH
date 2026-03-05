<?php

namespace App\View\Components\Portfolio;

use Illuminate\View\Component;
use Illuminate\View\View;

class Header extends Component
{
    public $designer;
    public $assetPaths;
    public $isOwner;

    /**
     * Create a new component instance.
     */
    public function __construct($designer, array $assetPaths, bool $isOwner)
    {
        $this->designer = $designer;
        $this->assetPaths = $assetPaths;
        $this->isOwner = $isOwner;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.portfolio.header');
    }
}
