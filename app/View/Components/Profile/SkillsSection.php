<?php

namespace App\View\Components\Profile;

use App\Helpers\DropdownHelper;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders the interactive skills selection section on the profile edit page.
 *
 * Loads available skill options from DropdownHelper and exposes them alongside
 * the designer's currently selected skills for the Blade view.
 */
class SkillsSection extends Component
{
    /** @var mixed The designer's current skills (array or collection). */
    public $skills;

    /** @var array Full list of available skill options from DropdownHelper. */
    public $skillOptions;

    /**
     * Create a new component instance.
     *
     * @param  mixed  $skills  The designer's currently selected skills
     */
    public function __construct($skills)
    {
        $this->skills = $skills;
        $this->skillOptions = self::getSkillOptions();
    }

    /**
     * Get the predefined skill options from database.
     * Public static to allow usage in layout component.
     */
    public static function getSkillOptions(): array
    {
        return DropdownHelper::skills();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.profile.skills-section');
    }
}
