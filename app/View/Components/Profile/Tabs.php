<?php

namespace App\View\Components\Profile;

use Illuminate\View\Component;
use Illuminate\View\View;

class Tabs extends Component
{
    public $designer;
    public $projectsData;
    public $productsData;
    public $servicesData;
    public $assetPaths;

    /**
     * Create a new component instance.
     */
    public function __construct($designer, array $projectsData, array $productsData, array $servicesData, array $assetPaths)
    {
        $this->designer = $designer;
        $this->projectsData = $projectsData;
        $this->productsData = $productsData;
        $this->servicesData = $servicesData;
        $this->assetPaths = $assetPaths;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.profile.tabs');
    }
}
