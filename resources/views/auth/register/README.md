# Register Form Partials

This directory contains the partials for the multi-step registration wizard.

## Structure

The registration form has been refactored into modular partials for better maintainability.

### Main File
- `register.blade.php` - Main registration page (60 lines, down from 3080!)

### Partials

#### UI Components
- `progress-indicator.blade.php` (61 lines)
  - Mobile step counter with progress bar
  - Desktop full progress stepper with icons

- `form-errors.blade.php` (26 lines)
  - Form opening tag
  - General error alert display

#### Registration Steps
- `step-1-account.blade.php` (202 lines)
  - Personal information (First Name, Last Name, Email, Password)

- `step-2-profile-type.blade.php` (74 lines)
  - Sector selection (Designer, Architect, Manufacturer, etc.)
  - Specialization/sub-sector selection
  - Manufacturer showroom field

- `step-3-details.blade.php` (291 lines)
  - Account details (Company, Position, Phone, City, Address)
  - Profile and cover image uploads
  - Bio and skills

- `step-4-products.blade.php` (152 lines)
  - Sample products showcase
  - Product image uploads (up to 6 per product)
  - Maximum 5 products

- `step-5-projects.blade.php` (180 lines)
  - Sample projects showcase
  - Project image uploads (up to 6 per project)
  - Maximum 5 projects

- `step-6-services.blade.php` (94 lines)
  - Services offered
  - Service descriptions

- `step-7-review.blade.php` (148 lines)
  - Review all entered information
  - Final submission

#### JavaScript
- `alpine-data.blade.php` (1821 lines)
  - All Alpine.js reactive data
  - All methods and functions
  - Validation logic
  - Image upload handlers
  - Form submission logic

## Benefits of Refactoring

1. **Maintainability**: Each section is now isolated and easy to find
2. **Readability**: Main file is now just 60 lines with clear @include statements
3. **Reusability**: Partials can be reused or modified independently
4. **Team Collaboration**: Multiple developers can work on different steps simultaneously
5. **Testing**: Each partial can be tested individually
6. **Performance**: No impact on performance - Blade compiles includes efficiently

## Usage

To modify a specific step, edit the corresponding partial file:

```blade
// To modify Step 1
resources/views/auth/register/step-1-account.blade.php

// To add new Alpine.js methods
resources/views/auth/register/alpine-data.blade.php
```

## Notes

- All Alpine.js functionality remains intact
- All form validation works as before
- Image upload functionality preserved
- Auto-save feature maintained
- No features were lost in the refactoring
