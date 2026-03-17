<?php

namespace App\View\Components\Profile;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders all Alpine.js-driven modal dialogs for the profile edit page.
 *
 * Consolidates add/edit modals for projects, products, and services so that the
 * parent EditPage view remains concise.
 */
class Modals extends Component
{
    /** @var mixed The designer model passed to modal views for form pre-population. */
    public $designer;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $designer
     */
    public function __construct($designer)
    {
        $this->designer = $designer;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.profile.modals');
    }
}
