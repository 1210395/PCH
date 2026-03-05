<?php

namespace App\View\Components\Profile;

use Illuminate\View\Component;
use Illuminate\View\View;

class EditPage extends Component
{
    public $designer;
    public $projectsData;
    public $productsData;
    public $servicesData;
    public $assetPaths;

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
            'avatar' => $designer->avatar ? asset('storage/' . $designer->avatar) : null,
            'cover' => $designer->cover_image ? asset('storage/' . $designer->cover_image) : null,
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.profile.edit-page');
    }
}
