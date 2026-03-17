<?php

namespace App\View\Components\Profile\Tabs;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders a single portfolio item tab panel (projects, products, or services) on the profile edit page.
 *
 * Dynamically derives Alpine.js function names from the $type property so the same
 * component handles all three item types without duplication.
 */
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
     * Get the Alpine.js function name used to open the add modal for this item type.
     *
     * @return string  e.g. 'openProjectModal'
     */
    public function modalFunction(): string
    {
        return 'open' . ucfirst($this->type) . 'Modal';
    }

    /**
     * Get the Alpine.js function name used to open the edit modal for an existing item.
     *
     * @return string  e.g. 'editProject'
     */
    public function editFunction(): string
    {
        return 'edit' . ucfirst($this->type);
    }

    /**
     * Get the Alpine.js function name used to delete an item.
     *
     * @return string  e.g. 'deleteProject'
     */
    public function deleteFunction(): string
    {
        return 'delete' . ucfirst($this->type);
    }

    /**
     * Get the Alpine.js reactive array name that holds this type's items.
     *
     * @return string  e.g. 'projects', 'products', or 'services'
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
