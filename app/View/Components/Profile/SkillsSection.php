<?php

namespace App\View\Components\Profile;

use App\Helpers\DropdownHelper;
use Illuminate\View\Component;
use Illuminate\View\View;

class SkillsSection extends Component
{
    public $skills;
    public $skillOptions;

    /**
     * Create a new component instance.
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
