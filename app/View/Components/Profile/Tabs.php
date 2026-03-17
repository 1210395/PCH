<?php

namespace App\View\Components\Profile;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders the tabbed navigation area on the profile edit page.
 *
 * Hosts the Profile Info tab and the Portfolio tabs (Projects, Products, Services),
 * passing all required data down to each nested tab component.
 */
class Tabs extends Component
{
    /** @var mixed The authenticated designer model being edited. */
    public $designer;

    /** @var array Paginated projects data array. */
    public $projectsData;

    /** @var array Paginated products data array. */
    public $productsData;

    /** @var array Paginated services data array. */
    public $servicesData;

    /** @var array Pre-resolved asset URLs keyed by 'avatar' and 'cover'. */
    public $assetPaths;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $designer
     * @param  array  $projectsData
     * @param  array  $productsData
     * @param  array  $servicesData
     * @param  array  $assetPaths
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
