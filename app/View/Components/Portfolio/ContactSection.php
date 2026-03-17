<?php

namespace App\View\Components\Portfolio;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders the contact details section on the public portfolio view page.
 *
 * Displays the designer's email, phone, website, and social links.
 */
class ContactSection extends Component
{
    /** @var mixed The designer model whose contact information is displayed. */
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
        return view('components.portfolio.contact-section');
    }
}
