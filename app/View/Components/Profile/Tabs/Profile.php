<?php

namespace App\View\Components\Profile\Tabs;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Renders the "Profile Info" tab panel within the profile edit page tabs.
 *
 * Pre-populates the Alpine.js reactive form object with the designer's current
 * data so the user sees their existing values immediately on load.
 */
class Profile extends Component
{
    /** @var mixed The authenticated designer model being edited. */
    public $designer;

    /** @var array Pre-resolved asset URLs keyed by 'avatar' and 'cover'. */
    public $assetPaths;

    /** @var array Initial form state passed to Alpine.js for reactive binding. */
    public $initialForm;

    /**
     * Create a new component instance.
     *
     * Builds the $initialForm array from the designer model if none is provided.
     * Upload-related keys default to null so Alpine.js can detect a fresh state.
     *
     * @param  mixed       $designer
     * @param  array       $assetPaths
     * @param  array|null  $initialForm  Override the auto-generated form state
     */
    public function __construct($designer, array $assetPaths, ?array $initialForm = null)
    {
        $this->designer = $designer;
        $this->assetPaths = $assetPaths;

        // Prepare initial form data for Alpine.js
        // Use provided initialForm or create a complete one
        $this->initialForm = $initialForm ?? [
            'name' => $designer->name,
            'title' => $designer->title ?? '',
            'bio' => $designer->bio ?? '',
            'email' => $designer->email,
            'phone' => $designer->phone_number ?? '',
            'city' => $designer->city ?? '',
            'address' => $designer->address ?? '',
            'website' => $designer->website ?? '',
            'avatarPath' => null,
            'avatarPreview' => null,
            'avatarUploading' => false,
            'coverPath' => null,
            'coverPreview' => null,
            'coverUploading' => false,
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.profile.tabs.profile');
    }
}
