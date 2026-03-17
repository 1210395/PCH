<?php

namespace App\View\Components\Portfolio;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders the tabbed portfolio content area on the public portfolio view page.
 *
 * Hosts the Projects, Products, and Services tabs with their respective data sets
 * and exposes an isOwner flag to conditionally show add/edit controls.
 */
class Tabs extends Component
{
    /** @var mixed The designer model that owns the portfolio items. */
    public $designer;

    /** @var array Paginated projects data array with items and pagination metadata. */
    public $projectsData;

    /** @var array Paginated products data array with items and pagination metadata. */
    public $productsData;

    /** @var array Paginated services data array with items and pagination metadata. */
    public $servicesData;

    /** @var array Pre-resolved asset URLs keyed by 'avatar' and 'cover'. */
    public $assetPaths;

    /** @var bool Whether the currently authenticated user owns this portfolio. */
    public $isOwner;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $designer
     * @param  array  $projectsData
     * @param  array  $productsData
     * @param  array  $servicesData
     * @param  array  $assetPaths
     * @param  bool   $isOwner
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
