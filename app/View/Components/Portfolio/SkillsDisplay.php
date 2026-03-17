<?php

namespace App\View\Components\Portfolio;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders the skills tag list on the public portfolio view page.
 *
 * Displays the designer's selected skills as styled badges and, for the owner,
 * provides an inline edit link.
 */
class SkillsDisplay extends Component
{
    /** @var mixed The designer model whose skills are displayed. */
    public $designer;

    /** @var bool Whether the currently authenticated user owns this portfolio. */
    public $isOwner;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $designer
     * @param  bool   $isOwner
     */
    public function __construct($designer, bool $isOwner)
    {
        $this->designer = $designer;
        $this->isOwner = $isOwner;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.portfolio.skills-display');
    }
}
