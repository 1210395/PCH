<?php

namespace App\View\Components\Portfolio;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Wrapper layout component for the public portfolio view page.
 *
 * Provides the outer HTML scaffold (navigation, page shell) that surrounds all
 * portfolio sub-components, and exposes a UUID generator for upload session tracking.
 */
class Layout extends Component
{
    /**
     * Generate a UUID v4 string for upload session tracking.
     *
     * Uses mt_rand to construct a compliant UUID v4 formatted string.
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

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.portfolio.layout');
    }
}
