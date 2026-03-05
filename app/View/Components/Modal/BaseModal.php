<?php

namespace App\View\Components\Modal;

use Illuminate\View\Component;

abstract class BaseModal extends Component
{
    /**
     * Get the modal title based on create/edit mode.
     */
    abstract public function title(bool $isEdit): string;

    /**
     * Get the modal description based on create/edit mode.
     */
    abstract public function description(bool $isEdit): string;

    /**
     * Get the form fields for this modal.
     * Returns array of field configurations.
     */
    abstract public function fields(): array;

    /**
     * Get the save URL for this modal.
     */
    abstract public function saveUrl(): string;

    /**
     * Get the delete URL for this modal.
     */
    abstract public function deleteUrl(): string;

    /**
     * Get the icon SVG path for this modal header.
     */
    abstract public function iconPath(): string;
}
