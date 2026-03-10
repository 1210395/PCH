<?php

namespace App\Helpers;

use App\Models\DropdownOption;

/**
 * Helper class for accessing dropdown options throughout the application.
 * All methods use caching for performance.
 */
class DropdownHelper
{
    /**
     * Sanitize a string to ensure valid UTF-8 encoding
     * Use this for any user-generated content that may contain malformed UTF-8
     *
     * @param mixed $string The string to sanitize
     * @return string The sanitized string
     */
    public static function sanitizeUtf8($string): string
    {
        if (!is_string($string)) {
            return '';
        }

        // Convert to UTF-8, handling invalid sequences
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

        // Remove control characters (except newlines and tabs)
        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $string);

        // If preg_replace failed due to invalid UTF-8, try iconv as fallback
        if ($string === null) {
            $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);
        }

        return $string ?? '';
    }

    /**
     * Sanitize an array of strings for UTF-8 encoding
     *
     * @param array $data The array to sanitize
     * @return array The sanitized array
     */
    public static function sanitizeUtf8Array(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $result[$key] = self::sanitizeUtf8($value);
            } elseif (is_array($value)) {
                $result[$key] = self::sanitizeUtf8Array($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Check if dropdown options table exists
     */
    private static function tableExists(): bool
    {
        try {
            return \Schema::hasTable('dropdown_options');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get sectors formatted for JavaScript (with subsectors array)
     * Used in registration and profile edit forms
     */
    public static function sectorsForJs(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultSectors();
        }

        try {
            $result = DropdownOption::getSectorsForJs();
            return !empty($result) ? $result : self::getDefaultSectors();
        } catch (\Exception $e) {
            return self::getDefaultSectors();
        }
    }

    /**
     * Get simple sector list for searchable select
     */
    public static function sectorOptions(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultSectorOptions();
        }

        try {
            $sectors = DropdownOption::getOptions('sector');
            if (empty($sectors)) {
                return self::getDefaultSectorOptions();
            }
            $locale = app()->getLocale();
            return array_map(function ($sector) use ($locale) {
                $label = ($locale === 'ar' && !empty($sector['label_ar'])) ? $sector['label_ar'] : $sector['label'];
                return [
                    'value' => $sector['value'],
                    'label' => $label
                ];
            }, $sectors);
        } catch (\Exception $e) {
            return self::getDefaultSectorOptions();
        }
    }

    /**
     * Get subsectors grouped by sector value
     * Used in subsector dropdowns that depend on selected sector
     */
    public static function subsectorsByType(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultSubsectors();
        }

        try {
            $result = DropdownOption::getSubsectorsByType();
            return !empty($result) ? $result : self::getDefaultSubsectors();
        } catch (\Exception $e) {
            return self::getDefaultSubsectors();
        }
    }

    /**
     * Get skills list
     */
    public static function skills(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultSkills();
        }

        try {
            // Clear any stale cache that might contain empty arrays
            $cacheKey = "dropdown_options_skill_labels";
            $cached = \Cache::get($cacheKey);
            if ($cached !== null && empty($cached)) {
                \Cache::forget($cacheKey);
            }

            $result = DropdownOption::getLabels('skill');
            return !empty($result) ? $result : self::getDefaultSkills();
        } catch (\Exception $e) {
            return self::getDefaultSkills();
        }
    }

    /**
     * Get cities/governorates list
     */
    public static function cities(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultCities();
        }

        try {
            // Clear any stale cache that might contain empty arrays
            $cacheKey = "dropdown_options_city_labels";
            $cached = \Cache::get($cacheKey);
            if ($cached !== null && empty($cached)) {
                \Cache::forget($cacheKey);
            }

            $result = DropdownOption::getLabels('city');
            return !empty($result) ? $result : self::getDefaultCities();
        } catch (\Exception $e) {
            return self::getDefaultCities();
        }
    }

    /**
     * Get product categories
     */
    public static function productCategories(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultProductCategories();
        }

        try {
            // Clear any stale cache that might contain empty arrays
            $cacheKey = "dropdown_options_product_category_labels";
            $cached = \Cache::get($cacheKey);
            if ($cached !== null && empty($cached)) {
                \Cache::forget($cacheKey);
            }

            $result = DropdownOption::getLabels('product_category');
            return !empty($result) ? $result : self::getDefaultProductCategories();
        } catch (\Exception $e) {
            return self::getDefaultProductCategories();
        }
    }

    /**
     * Get project categories
     */
    public static function projectCategories(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultProjectCategories();
        }

        try {
            // Clear any stale cache that might contain empty arrays
            $cacheKey = "dropdown_options_project_category_labels";
            $cached = \Cache::get($cacheKey);
            if ($cached !== null && empty($cached)) {
                \Cache::forget($cacheKey);
            }

            $result = DropdownOption::getLabels('project_category');
            return !empty($result) ? $result : self::getDefaultProjectCategories();
        } catch (\Exception $e) {
            return self::getDefaultProjectCategories();
        }
    }

    /**
     * Get project roles
     */
    public static function projectRoles(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultProjectRoles();
        }

        try {
            // Clear any stale cache that might contain empty arrays
            $cacheKey = "dropdown_options_project_role_labels";
            $cached = \Cache::get($cacheKey);
            if ($cached !== null && empty($cached)) {
                \Cache::forget($cacheKey);
            }

            $result = DropdownOption::getLabels('project_role');
            return !empty($result) ? $result : self::getDefaultProjectRoles();
        } catch (\Exception $e) {
            return self::getDefaultProjectRoles();
        }
    }

    /**
     * Get service categories
     */
    public static function serviceCategories(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultServiceCategories();
        }

        try {
            // Clear any stale cache that might contain empty arrays
            $cacheKey = "dropdown_options_service_category_labels";
            $cached = \Cache::get($cacheKey);
            if ($cached !== null && empty($cached)) {
                \Cache::forget($cacheKey);
            }

            $result = DropdownOption::getLabels('service_category');
            return !empty($result) ? $result : self::getDefaultServiceCategories();
        } catch (\Exception $e) {
            return self::getDefaultServiceCategories();
        }
    }

    /**
     * Get years of experience options
     */
    public static function yearsOfExperience(): array
    {
        $default = ['0-1 years', '1-3 years', '3-5 years', '5-10 years', '10+ years'];

        if (!self::tableExists()) {
            return $default;
        }

        try {
            // Clear any stale cache that might contain empty arrays
            $cacheKey = "dropdown_options_years_experience_labels";
            $cached = \Cache::get($cacheKey);
            if ($cached !== null && empty($cached)) {
                \Cache::forget($cacheKey);
            }

            $result = DropdownOption::getLabels('years_experience');
            return !empty($result) ? $result : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Get fablab types
     */
    public static function fablabTypes(): array
    {
        $default = [
            ['value' => 'fablab', 'label' => 'FabLab'],
            ['value' => 'makerspace', 'label' => 'Makerspace'],
            ['value' => 'hackerspace', 'label' => 'Hackerspace'],
            ['value' => 'workshop', 'label' => 'Workshop'],
            ['value' => 'studio', 'label' => 'Studio']
        ];

        if (!self::tableExists()) {
            return $default;
        }

        try {
            $options = DropdownOption::getOptions('fablab_type');
            if (empty($options)) {
                return $default;
            }
            $locale = app()->getLocale();
            return array_map(function ($opt) use ($locale) {
                $label = ($locale === 'ar' && !empty($opt['label_ar'])) ? $opt['label_ar'] : $opt['label'];
                return ['value' => $opt['value'], 'label' => $label];
            }, $options);
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Get marketplace types
     */
    public static function marketplaceTypes(): array
    {
        $default = [
            ['value' => 'service', 'label' => 'Service'],
            ['value' => 'collaboration', 'label' => 'Collaboration'],
            ['value' => 'showcase', 'label' => 'Showcase'],
            ['value' => 'opportunity', 'label' => 'Opportunity']
        ];

        if (!self::tableExists()) {
            return $default;
        }

        try {
            $options = DropdownOption::getOptions('marketplace_type');
            if (empty($options)) {
                return $default;
            }
            $locale = app()->getLocale();
            return array_map(function ($opt) use ($locale) {
                $label = ($locale === 'ar' && !empty($opt['label_ar'])) ? $opt['label_ar'] : $opt['label'];
                return ['value' => $opt['value'], 'label' => $label];
            }, $options);
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * Get marketplace categories
     */
    public static function marketplaceCategories(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultMarketplaceCategories();
        }

        try {
            // Clear any stale cache that might contain empty arrays
            $locale = app()->getLocale();
            $cacheKey = "dropdown_options_marketplace_category_labels_{$locale}";
            $cached = \Cache::get($cacheKey);
            if ($cached !== null && empty($cached)) {
                \Cache::forget($cacheKey);
            }
            // Also clear old non-locale cache key
            \Cache::forget("dropdown_options_marketplace_category_labels");

            $result = DropdownOption::getLabels('marketplace_category');
            return !empty($result) ? $result : self::getDefaultMarketplaceCategories();
        } catch (\Exception $e) {
            return self::getDefaultMarketplaceCategories();
        }
    }

    /**
     * Get marketplace tags
     */
    public static function marketplaceTags(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultMarketplaceTags();
        }

        try {
            // Clear any stale cache that might contain empty arrays
            $locale = app()->getLocale();
            $cacheKey = "dropdown_options_marketplace_tag_labels_{$locale}";
            $cached = \Cache::get($cacheKey);
            if ($cached !== null && empty($cached)) {
                \Cache::forget($cacheKey);
            }
            // Also clear old non-locale cache key
            \Cache::forget("dropdown_options_marketplace_tag_labels");

            $result = DropdownOption::getLabels('marketplace_tag');
            return !empty($result) ? $result : self::getDefaultMarketplaceTags();
        } catch (\Exception $e) {
            return self::getDefaultMarketplaceTags();
        }
    }

    /**
     * Get training categories
     */
    public static function trainingCategories(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultTrainingCategories();
        }

        try {
            // Clear any stale cache that might contain empty arrays
            $cacheKey = "dropdown_options_training_category_labels";
            $cached = \Cache::get($cacheKey);
            if ($cached !== null && empty($cached)) {
                \Cache::forget($cacheKey);
            }

            $result = DropdownOption::getLabels('training_category');
            return !empty($result) ? $result : self::getDefaultTrainingCategories();
        } catch (\Exception $e) {
            return self::getDefaultTrainingCategories();
        }
    }

    /**
     * Get tender categories
     */
    public static function tenderCategories(): array
    {
        if (!self::tableExists()) {
            return self::getDefaultTenderCategories();
        }

        try {
            // Clear any stale cache that might contain empty arrays
            $cacheKey = "dropdown_options_tender_category_labels";
            $cached = \Cache::get($cacheKey);
            if ($cached !== null && empty($cached)) {
                \Cache::forget($cacheKey);
            }

            $result = DropdownOption::getLabels('tender_category');
            return !empty($result) ? $result : self::getDefaultTenderCategories();
        } catch (\Exception $e) {
            return self::getDefaultTenderCategories();
        }
    }

    /**
     * Format phone number with country code
     *
     * @param string|null $phoneNumber The phone number
     * @param string|null $countryCode The country code (e.g., 'PS', 'US', 'IL')
     * @return string The formatted phone number
     */
    public static function formatPhoneWithCountry(?string $phoneNumber, ?string $countryCode = null): string
    {
        if (empty($phoneNumber)) {
            return '';
        }

        // Country dial codes mapping
        $dialCodes = [
            'PS' => '+970',
            'IL' => '+972',
            'JO' => '+962',
            'EG' => '+20',
            'SA' => '+966',
            'AE' => '+971',
            'US' => '+1',
            'GB' => '+44',
            'DE' => '+49',
            'FR' => '+33',
            'TR' => '+90',
            'LB' => '+961',
            'SY' => '+963',
            'IQ' => '+964',
            'KW' => '+965',
            'BH' => '+973',
            'QA' => '+974',
            'OM' => '+968',
            'YE' => '+967',
            'MA' => '+212',
            'DZ' => '+213',
            'TN' => '+216',
            'LY' => '+218',
            'SD' => '+249',
        ];

        // Clean the phone number (remove spaces, dashes, etc.)
        $cleanPhone = preg_replace('/[^0-9+]/', '', $phoneNumber);

        // If already has a + prefix, return as is
        if (str_starts_with($cleanPhone, '+')) {
            return $cleanPhone;
        }

        // If country code is provided, prepend the dial code
        if ($countryCode && isset($dialCodes[strtoupper($countryCode)])) {
            $dialCode = $dialCodes[strtoupper($countryCode)];

            // Remove leading zero if present
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = substr($cleanPhone, 1);
            }

            return $dialCode . ' ' . $cleanPhone;
        }

        // Default: return the phone number as-is
        return $phoneNumber;
    }

    // =====================
    // Default Fallback Data
    // =====================

    private static function getDefaultSectors(): array
    {
        return [
            ['value' => 'academic', 'label' => 'Academic', 'subSectors' => ['Architecture Education', 'Art Education', 'Art History', 'Design Education', 'Design Research', 'Museum Studies', 'Researcher', 'Student', 'University Professor']],
            ['value' => 'architect', 'label' => 'Architect', 'subSectors' => ['Commercial Architect', 'Engineering', 'Industrial Architect', 'Interior Architect', 'Landscape Architect', 'Residential Architect', 'Restoration Architect', 'Sustainable Design Architect', 'Urban Planner']],
            ['value' => 'designer', 'label' => 'Designer', 'subSectors' => ['Ceramicist', 'Conceptual Artist', 'Digital Artist', 'Fashion Designer', 'Fine Art Photographer', 'Glass Artist', 'Graphic Designer', 'Installation Artist', 'Interior Designer', 'Mixed Media Artist', 'Painter', 'Printmaker', 'Product Designer', 'Sculptor', 'Street Artist', 'Textile Artist', 'UX/UI Designer']],
            ['value' => 'manufacturer', 'label' => 'Manufacturer', 'subSectors' => ['Furniture Manufacturer', 'Handicraft Producer', 'Metal and Wood work', 'Metal Works', 'Textile Manufacturer', 'Woodworks']],
            ['value' => 'showroom', 'label' => 'Showroom/Retailer', 'subSectors' => ['Art Gallery', 'Craft Store', 'Design Store', 'Furniture Showroom']],
            ['value' => 'guest', 'label' => 'Guest', 'subSectors' => []]
        ];
    }

    private static function getDefaultSectorOptions(): array
    {
        return [
            ['value' => 'academic', 'label' => 'Academic'],
            ['value' => 'architect', 'label' => 'Architect'],
            ['value' => 'designer', 'label' => 'Designer'],
            ['value' => 'manufacturer', 'label' => 'Manufacturer'],
            ['value' => 'showroom', 'label' => 'Showroom/Retailer'],
            ['value' => 'guest', 'label' => 'Guest']
        ];
    }

    private static function getDefaultSubsectors(): array
    {
        return [
            'academic' => ['Architecture Education', 'Art Education', 'Art History', 'Design Education', 'Design Research', 'Museum Studies', 'Researcher', 'Student', 'University Professor'],
            'architect' => ['Commercial Architect', 'Engineering', 'Industrial Architect', 'Interior Architect', 'Landscape Architect', 'Residential Architect', 'Restoration Architect', 'Sustainable Design Architect', 'Urban Planner'],
            'designer' => ['Ceramicist', 'Conceptual Artist', 'Digital Artist', 'Fashion Designer', 'Fine Art Photographer', 'Glass Artist', 'Graphic Designer', 'Installation Artist', 'Interior Designer', 'Mixed Media Artist', 'Painter', 'Printmaker', 'Product Designer', 'Sculptor', 'Street Artist', 'Textile Artist', 'UX/UI Designer'],
            'manufacturer' => ['Furniture Manufacturer', 'Handicraft Producer', 'Metal and Wood work', 'Metal Works', 'Textile Manufacturer', 'Woodworks'],
            'showroom' => ['Art Gallery', 'Craft Store', 'Design Store', 'Furniture Showroom'],
            'guest' => []
        ];
    }

    private static function getDefaultSkills(): array
    {
        return [
            '3D Modeling', '3ds Max', 'Abstract Art', 'Acrylic Painting', 'Animation', 'ArchiCAD',
            'Architectural Drawing', 'Art Theory', 'Assemblage', 'AutoCAD', 'Branding', 'Building Codes',
            'Building Design', 'Business Development', 'Ceramics', 'Charcoal', 'Clay Modeling',
            'Collage', 'Color Theory', 'Composition', 'Concept Art', 'Construction Documentation',
            'Content Writing', 'Corel Painter', 'Darkroom', 'Digital Art', 'Digital Painting', 'Drawing',
            'Etching', 'Fiber Art', 'Figure Drawing', 'Fine Art Photography', 'Glass Art', 'Glassblowing',
            'Graffiti', 'Graphic Design', 'Illustrator', 'Installation Art', 'LEED Certification',
            'Lithography', 'Marketing', 'Metalworking', 'Mixed Media', 'Mobile Development',
            'Mural Painting', 'Oil Painting', 'Photoshop', 'Photo Editing', 'Photography', 'Portrait Art',
            'Pottery', 'Printmaking', 'Procreate', 'Project Management', 'Public Art', 'Realism',
            'Relief Printing', 'Revit', 'Rhino', 'Screen Printing', 'Sculpture', 'Site Planning',
            'Sketching', 'SketchUp', 'Social Media', 'Spray Paint', 'Street Art', 'Structural Design',
            'Sustainable Design', 'Textile Art', 'UX/UI Design', 'V-Ray', 'Videography', 'Watercolor',
            'Weaving', 'Web Development', 'Woodworking'
        ];
    }

    private static function getDefaultCities(): array
    {
        return [
            'Jerusalem', 'Ramallah and Al-Bireh', 'Bethlehem', 'Hebron', 'Nablus',
            'Jenin', 'Tulkarm', 'Qalqilya', 'Tubas', 'Salfit', 'Jericho',
            'North Gaza', 'Gaza', 'Deir al-Balah', 'Khan Yunis', 'Rafah'
        ];
    }

    private static function getDefaultProductCategories(): array
    {
        return [
            'Furniture', 'Interior Design', 'Architecture', 'Decoration Pieces',
            'Artwork', 'Printmaking Artwork', 'Kitchens', 'Bedrooms',
            'Dining Tables', 'Sofas & Seating', 'Wood Works', 'Sanitary Ware',
            'Glass Products', 'Fabrics & Textiles', 'Lighting', 'Space Planning',
            'Product Design', 'Drawing on Glass', 'Building', 'Designing', 'Other'
        ];
    }

    private static function getDefaultProjectCategories(): array
    {
        return [
            'Branding', 'UI/UX', 'Photography', 'Illustration', 'Architecture',
            'Fashion', 'Digital Art', 'Graphic Design', 'Interior Design',
            'General', 'Other'
        ];
    }

    private static function getDefaultProjectRoles(): array
    {
        return [
            'Lead Designer', 'Designer', 'Architect', 'Interior Designer',
            'Interior Architect', 'Lead Interior & Furniture Designer',
            'Interior Architect & Fit-Out Designer', 'Interior Designer & Revit Modeler',
            'Key Urban Planner', 'Lead Graphic Designer', 'Lead UI/UX Designer',
            'Lead Social Media Designer', '3D Rendering Specialist', 'Project Manager',
            'Planning & Supervision', 'Developer', 'Services Provider', 'Other'
        ];
    }

    private static function getDefaultServiceCategories(): array
    {
        return [
            'Carpentry', 'Consultation', 'Design', 'Development', 'Digital Illustration',
            'Graphic Design', 'Installation', 'Maintenance', 'Manufacturing',
            'Material Specification', 'Other', 'Photography', 'Strategy', 'Supervision'
        ];
    }

    private static function getDefaultMarketplaceCategories(): array
    {
        return [
            'Graphic Design', 'Web Design', 'UI/UX Design', 'Interior Design',
            'Fashion Design', 'Product Design', 'Architecture', 'Photography',
            'Illustration', 'Animation', 'Video Production', 'Branding',
            'Print Design', 'Packaging', 'Typography', '3D Modeling',
            'Motion Graphics', 'Art Direction', 'Digital Art', 'Crafts',
            'Jewelry', 'Textiles', 'Ceramics', 'Woodworking', 'Metalwork',
            'Marketing', 'Advertising', 'Social Media', 'Content Creation',
            'Consulting', 'Education', 'Workshops', 'Other'
        ];
    }

    private static function getDefaultMarketplaceTags(): array
    {
        return [
            'Design', 'Art', 'Creative', 'Digital', 'Photography', 'Illustration',
            'Branding', 'UI/UX', 'Web Design', 'Graphic Design', 'Interior Design',
            'Architecture', 'Fashion', 'Product Design', '3D Modeling', 'Animation',
            'Video', 'Marketing', 'Social Media', 'Print', 'Packaging', 'Logo',
            'Typography', 'Motion Graphics', 'Consulting', 'Freelance', 'Collaboration',
            'Commission', 'For Sale', 'Hiring', 'Looking for Work', 'Partnership'
        ];
    }

    private static function getDefaultTrainingCategories(): array
    {
        return [
            'Design', 'Development', 'Marketing', 'Business', 'Photography',
            'Video Production', '3D Modeling', 'Entrepreneurship'
        ];
    }

    private static function getDefaultTenderCategories(): array
    {
        return [
            'Branding & Identity', 'Web Development', 'Product Design', 'UX/UI Design',
            'Digital Marketing', 'Architecture', 'Illustration', 'Video Production',
            'Consulting', 'Other'
        ];
    }
}
