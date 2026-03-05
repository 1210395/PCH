<?php

namespace App\View\Components\Portfolio;

use Illuminate\View\Component;
use Illuminate\View\View;

class Tabs extends Component
{
    public $designer;
    public $projectsData;
    public $productsData;
    public $servicesData;
    public $assetPaths;
    public $isOwner;

    /**
     * Create a new component instance.
     */
    public function __construct(
        $designer,
        array $projectsData,
        array $productsData,
        array $servicesData,
        array $assetPaths,
        bool $isOwner
    ) {
        $this->designer = $designer;
        $this->projectsData = $projectsData;
        $this->productsData = $productsData;
        $this->servicesData = $servicesData;
        $this->assetPaths = $assetPaths;
        $this->isOwner = $isOwner;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.portfolio.tabs');
    }
}
