<?php

namespace App\View\Components\Profile;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Wrapper layout component for the authenticated profile edit page.
 *
 * Provides the outer HTML scaffold and exposes a UUID generator used by Alpine.js
 * upload sessions within profile sub-components.
 */
class Layout extends Component
{
    /** @var mixed|null The authenticated designer model. */
    public $designer;

    /** @var array Paginated projects data for the portfolio tab. */
    public $projectsData;

    /** @var array Paginated products data for the portfolio tab. */
    public $productsData;

    /** @var array Paginated services data for the portfolio tab. */
    public $servicesData;

    /**
     * Create a new component instance.
     *
     * @param  mixed|null  $designer
     * @param  array       $projectsData
     * @param  array       $productsData
     * @param  array       $servicesData
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
     * Generate a UUID v4 string for upload sessions.
     *
     * Originally inlined in Alpine.js; centralised here so both Profile and Portfolio
     * layout components produce consistent session identifiers.
     *
     * @return string
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
