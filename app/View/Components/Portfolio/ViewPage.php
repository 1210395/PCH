<?php

namespace App\View\Components\Portfolio;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Root component for the public designer portfolio view page.
 *
 * Orchestrates all portfolio sub-components (Header, BioSection, Tabs, etc.),
 * resolves asset URLs, and determines whether the viewer is the profile owner.
 */
class ViewPage extends Component
{
    public $designer;
    public $projectsData;
    public $productsData;
    public $servicesData;
    public $assetPaths;
    public $isOwner;

    /**
     * Create a new component instance.
     *
     * @param mixed $designer
     * @param array $projectsData
     * @param array $productsData
     * @param array $servicesData
     */
    public function __construct($designer, array $projectsData, array $productsData, array $servicesData)
    {
        $this->designer = $designer;
        $this->projectsData = $projectsData;
        $this->productsData = $productsData;
        $this->servicesData = $servicesData;

        // Prepare asset paths for views
        $this->assetPaths = [
            'avatar' => $designer->avatar ? url('media/' . $designer->avatar) : null,
            'cover' => $designer->cover_image ? url('media/' . $designer->cover_image) : null,
        ];

        // Check if current user is the portfolio owner
        $this->isOwner = auth('designer')->check() && auth('designer')->id() === $designer->id;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.portfolio.view-page');
    }
}
