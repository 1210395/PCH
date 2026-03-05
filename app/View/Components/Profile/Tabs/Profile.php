<?php

namespace App\View\Components\Profile\Tabs;

use Illuminate\View\Component;
use Illuminate\View\View;

class Profile extends Component
{
    public $designer;
    public $assetPaths;
    public $initialForm;

    /**
     * Create a new component instance.
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
