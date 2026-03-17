<?php

namespace App\View\Components\Portfolio;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders the hero header section on the public portfolio view page.
 *
 * Shows the designer's cover image, avatar, name, title, sector, and follow/edit actions.
 */
class Header extends Component
{
    /** @var mixed The designer model whose header information is displayed. */
    public $designer;

    /** @var array Pre-resolved asset URLs, keyed by 'avatar' and 'cover'. */
    public $assetPaths;

    /** @var bool Whether the currently authenticated user owns this portfolio. */
    public $isOwner;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $designer
     * @param  array  $assetPaths  Pre-resolved URLs keyed by 'avatar' and 'cover'
     * @param  bool   $isOwner
     */
    public function __construct($designer, array $assetPaths, bool $isOwner)
    {
        $this->designer = $designer;
        $this->assetPaths = $assetPaths;
        $this->isOwner = $isOwner;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.portfolio.header');
    }
}
