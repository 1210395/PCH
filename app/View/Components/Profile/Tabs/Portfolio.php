<?php

namespace App\View\Components\Profile\Tabs;

use Illuminate\View\Component;
use Illuminate\View\View;

class Portfolio extends Component
{
    public $type;
    public $items;
    public $title;
    public $iconPath;

    /**
     * Create a new component instance.
     *
     * @param string $type - 'project', 'product', or 'service'
     * @param array $items - Collection of portfolio items
     * @param string $title - Display title ('Projects', 'Products', 'Services')
     * @param string $iconPath - SVG icon path
     */
    public function __construct(string $type, array $items, string $title, string $iconPath)
    {
        $this->type = $type;
        $this->items = $items;
        $this->title = $title;
        $this->iconPath = $iconPath;
    }

    /**
     * Get the modal function name based on type.
     */
    public function modalFunction(): string
    {
        return 'open' . ucfirst($this->type) . 'Modal';
    }

    /**
     * Get the edit function name based on type.
     */
    public function editFunction(): string
    {
        return 'edit' . ucfirst($this->type);
    }

    /**
     * Get the delete function name based on type.
     */
    public function deleteFunction(): string
    {
        return 'delete' . ucfirst($this->type);
    }

    /**
     * Get the items array name based on type.
     */
    public function itemsArrayName(): string
    {
        return $this->type . 's';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.profile.tabs.portfolio');
    }
}
