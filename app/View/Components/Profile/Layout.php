<?php

namespace App\View\Components\Profile;

use Illuminate\View\Component;
use Illuminate\View\View;

class Layout extends Component
{
    public $designer;
    public $projectsData;
    public $productsData;
    public $servicesData;

    /**
     * Create a new component instance.
     */
    public function __construct($designer = null, $projectsData = [], $productsData = [], $servicesData = [])
    {
        $this->designer = $designer;
        $this->projectsData = $projectsData;
        $this->productsData = $productsData;
        $this->servicesData = $servicesData;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.profile.layout');
    }

    /**
     * Generate a UUID for upload sessions.
     * Moved from inline Alpine.js to be reusable.
     */
    public static function generateUUID(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
