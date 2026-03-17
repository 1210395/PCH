<?php

namespace App\View\Components\Portfolio;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders all Alpine.js-driven modal dialogs for the portfolio view page.
 *
 * Groups contact, follow, and subscription modals into a single include so the
 * parent view stays clean. Modals are opened via JavaScript events.
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
        return view('components.portfolio.modals');
    }
}
