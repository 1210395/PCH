<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Populate Arabic translations for all dropdown_options rows.
     */
    public function up(): void
    {
        $translations = [
            // === SECTORS ===
            ['type' => 'sector', 'value' => 'supplier_vendors', 'label_ar' => 'الموردون والبائعون'],
            ['type' => 'sector', 'value' => 'architect', 'label_ar' => 'مهندس معماري'],
            ['type' => 'sector', 'value' => 'designer', 'label_ar' => 'مصمم'],
            ['type' => 'sector', 'value' => 'manufacturer', 'label_ar' => 'مصنّع'],
            ['type' => 'sector', 'value' => 'showroom', 'label_ar' => 'صالة عرض / تجزئة'],
            ['type' => 'sector', 'value' => 'guest', 'label_ar' => 'زائر'],

            // === SUBSECTORS - Supplier/Vendors ===
            ['type' => 'subsector', 'value' => 'electrical_supplier', 'label_ar' => 'مورّد كهربائي'],
            ['type' => 'subsector', 'value' => 'lightning_supplier', 'label_ar' => 'مورّد إضاءة'],
            ['type' => 'subsector', 'value' => 'wood_supplier', 'label_ar' => 'مورّد أخشاب'],
            ['type' => 'subsector', 'value' => 'mechanical_supplier', 'label_ar' => 'مورّد ميكانيكي'],
            ['type' => 'subsector', 'value' => 'design_research', 'label_ar' => 'بحث التصميم'],
            ['type' => 'subsector', 'value' => 'museum_studies', 'label_ar' => 'دراسات المتاحف'],
            ['type' => 'subsector', 'value' => 'researcher', 'label_ar' => 'باحث'],
            ['type' => 'subsector', 'value' => 'student', 'label_ar' => 'طالب'],
            ['type' => 'subsector', 'value' => 'university_professor', 'label_ar' => 'أستاذ جامعي'],

            // === SUBSECTORS - Architect ===
            ['type' => 'subsector', 'value' => 'commercial_architect', 'label_ar' => 'مهندس معماري تجاري'],
            ['type' => 'subsector', 'value' => 'engineering', 'label_ar' => 'هندسة'],
            ['type' => 'subsector', 'value' => 'industrial_architect', 'label_ar' => 'مهندس معماري صناعي'],
            ['type' => 'subsector', 'value' => 'interior_architect', 'label_ar' => 'مهندس معماري داخلي'],
            ['type' => 'subsector', 'value' => 'landscape_architect', 'label_ar' => 'مهندس معماري للمناظر الطبيعية'],
            ['type' => 'subsector', 'value' => 'residential_architect', 'label_ar' => 'مهندس معماري سكني'],
            ['type' => 'subsector', 'value' => 'restoration_architect', 'label_ar' => 'مهندس ترميم'],
            ['type' => 'subsector', 'value' => 'sustainable_design_architect', 'label_ar' => 'مهندس التصميم المستدام'],
            ['type' => 'subsector', 'value' => 'urban_planner', 'label_ar' => 'مخطط حضري'],

            // === SUBSECTORS - Designer ===
            ['type' => 'subsector', 'value' => 'ceramicist', 'label_ar' => 'خزّاف'],
            ['type' => 'subsector', 'value' => 'conceptual_artist', 'label_ar' => 'فنان مفاهيمي'],
            ['type' => 'subsector', 'value' => 'digital_artist', 'label_ar' => 'فنان رقمي'],
            ['type' => 'subsector', 'value' => 'fashion_designer', 'label_ar' => 'مصمم أزياء'],
            ['type' => 'subsector', 'value' => 'fine_art_photographer', 'label_ar' => 'مصور فنون جميلة'],
            ['type' => 'subsector', 'value' => 'glass_artist', 'label_ar' => 'فنان زجاج'],
            ['type' => 'subsector', 'value' => 'graphic_designer', 'label_ar' => 'مصمم جرافيك'],
            ['type' => 'subsector', 'value' => 'installation_artist', 'label_ar' => 'فنان تركيبات'],
            ['type' => 'subsector', 'value' => 'interior_designer', 'label_ar' => 'مصمم داخلي'],
            ['type' => 'subsector', 'value' => 'mixed_media_artist', 'label_ar' => 'فنان وسائط متعددة'],
            ['type' => 'subsector', 'value' => 'painter', 'label_ar' => 'رسّام'],
            ['type' => 'subsector', 'value' => 'printmaker', 'label_ar' => 'فنان طباعة'],
            ['type' => 'subsector', 'value' => 'product_designer', 'label_ar' => 'مصمم منتجات'],
            ['type' => 'subsector', 'value' => 'sculptor', 'label_ar' => 'نحّات'],
            ['type' => 'subsector', 'value' => 'street_artist', 'label_ar' => 'فنان شارع'],
            ['type' => 'subsector', 'value' => 'textile_artist', 'label_ar' => 'فنان نسيج'],
            ['type' => 'subsector', 'value' => 'ux_ui_designer', 'label_ar' => 'مصمم UX/UI'],

            // === SUBSECTORS - Manufacturer ===
            ['type' => 'subsector', 'value' => 'furniture_manufacturer', 'label_ar' => 'مصنّع أثاث'],
            ['type' => 'subsector', 'value' => 'handicrafters___artisans', 'label_ar' => 'حرفيون وصنّاع يدويون'],
            ['type' => 'subsector', 'value' => 'metal_and_wood_work', 'label_ar' => 'أعمال المعادن والخشب'],
            ['type' => 'subsector', 'value' => 'metal_works', 'label_ar' => 'أعمال المعادن'],
            ['type' => 'subsector', 'value' => 'textile_manufacturer', 'label_ar' => 'مصنّع نسيج'],
            ['type' => 'subsector', 'value' => 'woodworks', 'label_ar' => 'أعمال الخشب'],

            // === SUBSECTORS - Showroom ===
            ['type' => 'subsector', 'value' => 'art_gallery', 'label_ar' => 'معرض فني'],
            ['type' => 'subsector', 'value' => 'craft_store', 'label_ar' => 'متجر حرف يدوية'],
            ['type' => 'subsector', 'value' => 'design_store', 'label_ar' => 'متجر تصميم'],
            ['type' => 'subsector', 'value' => 'furniture_showroom', 'label_ar' => 'صالة عرض أثاث'],

            // === CITIES ===
            ['type' => 'city', 'value' => 'Jerusalem', 'label_ar' => 'القدس'],
            ['type' => 'city', 'value' => 'Ramallah and Al-Bireh', 'label_ar' => 'رام الله والبيرة'],
            ['type' => 'city', 'value' => 'Bethlehem', 'label_ar' => 'بيت لحم'],
            ['type' => 'city', 'value' => 'Hebron', 'label_ar' => 'الخليل'],
            ['type' => 'city', 'value' => 'Nablus', 'label_ar' => 'نابلس'],
            ['type' => 'city', 'value' => 'Jenin', 'label_ar' => 'جنين'],
            ['type' => 'city', 'value' => 'Tulkarm', 'label_ar' => 'طولكرم'],
            ['type' => 'city', 'value' => 'Qalqilya', 'label_ar' => 'قلقيلية'],
            ['type' => 'city', 'value' => 'Tubas', 'label_ar' => 'طوباس'],
            ['type' => 'city', 'value' => 'Salfit', 'label_ar' => 'سلفيت'],
            ['type' => 'city', 'value' => 'Jericho', 'label_ar' => 'أريحا'],
            ['type' => 'city', 'value' => 'North Gaza', 'label_ar' => 'شمال غزة'],
            ['type' => 'city', 'value' => 'Gaza', 'label_ar' => 'غزة'],
            ['type' => 'city', 'value' => 'Deir al-Balah', 'label_ar' => 'دير البلح'],
            ['type' => 'city', 'value' => 'Khan Yunis', 'label_ar' => 'خان يونس'],
            ['type' => 'city', 'value' => 'Rafah', 'label_ar' => 'رفح'],

            // === PRODUCT CATEGORIES ===
            ['type' => 'product_category', 'value' => 'furniture', 'label_ar' => 'أثاث'],
            ['type' => 'product_category', 'value' => 'interior_design', 'label_ar' => 'تصميم داخلي'],
            ['type' => 'product_category', 'value' => 'architecture', 'label_ar' => 'عمارة'],
            ['type' => 'product_category', 'value' => 'decoration_pieces', 'label_ar' => 'قطع ديكور'],
            ['type' => 'product_category', 'value' => 'artwork', 'label_ar' => 'أعمال فنية'],
            ['type' => 'product_category', 'value' => 'printmaking_artwork', 'label_ar' => 'فن الطباعة'],
            ['type' => 'product_category', 'value' => 'kitchens', 'label_ar' => 'مطابخ'],
            ['type' => 'product_category', 'value' => 'bedrooms', 'label_ar' => 'غرف نوم'],
            ['type' => 'product_category', 'value' => 'dining_tables', 'label_ar' => 'طاولات طعام'],
            ['type' => 'product_category', 'value' => 'sofas___seating', 'label_ar' => 'أرائك ومقاعد'],
            ['type' => 'product_category', 'value' => 'wood_works', 'label_ar' => 'أعمال خشبية'],
            ['type' => 'product_category', 'value' => 'sanitary_ware', 'label_ar' => 'أدوات صحية'],
            ['type' => 'product_category', 'value' => 'glass_products', 'label_ar' => 'منتجات زجاجية'],
            ['type' => 'product_category', 'value' => 'fabrics___textiles', 'label_ar' => 'أقمشة ومنسوجات'],
            ['type' => 'product_category', 'value' => 'lighting', 'label_ar' => 'إضاءة'],
            ['type' => 'product_category', 'value' => 'space_planning', 'label_ar' => 'تخطيط المساحات'],
            ['type' => 'product_category', 'value' => 'product_design', 'label_ar' => 'تصميم المنتجات'],
            ['type' => 'product_category', 'value' => 'drawing_on_glass', 'label_ar' => 'الرسم على الزجاج'],
            ['type' => 'product_category', 'value' => 'building', 'label_ar' => 'بناء'],
            ['type' => 'product_category', 'value' => 'designing', 'label_ar' => 'تصميم'],
            ['type' => 'product_category', 'value' => 'other', 'label_ar' => 'أخرى'],
            ['type' => 'product_category', 'value' => 'tv_wall_units', 'label_ar' => 'وحدات حائط تلفزيون'],

            // === PROJECT CATEGORIES ===
            ['type' => 'project_category', 'value' => 'branding', 'label_ar' => 'العلامة التجارية'],
            ['type' => 'project_category', 'value' => 'ui_ux', 'label_ar' => 'واجهة المستخدم/تجربة المستخدم'],
            ['type' => 'project_category', 'value' => 'photography', 'label_ar' => 'تصوير'],
            ['type' => 'project_category', 'value' => 'illustration', 'label_ar' => 'رسم توضيحي'],
            ['type' => 'project_category', 'value' => 'architecture', 'label_ar' => 'عمارة'],
            ['type' => 'project_category', 'value' => 'fashion', 'label_ar' => 'أزياء'],
            ['type' => 'project_category', 'value' => 'digital_art', 'label_ar' => 'فن رقمي'],
            ['type' => 'project_category', 'value' => 'graphic_design', 'label_ar' => 'تصميم جرافيك'],
            ['type' => 'project_category', 'value' => 'interior_design', 'label_ar' => 'تصميم داخلي'],
            ['type' => 'project_category', 'value' => 'general', 'label_ar' => 'عام'],
            ['type' => 'project_category', 'value' => 'other', 'label_ar' => 'أخرى'],

            // === PROJECT ROLES ===
            ['type' => 'project_role', 'value' => 'lead_designer', 'label_ar' => 'مصمم رئيسي'],
            ['type' => 'project_role', 'value' => 'designer', 'label_ar' => 'مصمم'],
            ['type' => 'project_role', 'value' => 'architect', 'label_ar' => 'مهندس معماري'],
            ['type' => 'project_role', 'value' => 'interior_designer', 'label_ar' => 'مصمم داخلي'],
            ['type' => 'project_role', 'value' => 'interior_architect', 'label_ar' => 'مهندس معماري داخلي'],
            ['type' => 'project_role', 'value' => 'lead_interior___furniture_designer', 'label_ar' => 'مصمم داخلي وأثاث رئيسي'],
            ['type' => 'project_role', 'value' => 'interior_architect___fit_out_designer', 'label_ar' => 'مهندس معماري داخلي ومصمم تشطيبات'],
            ['type' => 'project_role', 'value' => 'interior_designer___revit_modeler', 'label_ar' => 'مصمم داخلي ومصمم Revit'],
            ['type' => 'project_role', 'value' => 'key_urban_planner', 'label_ar' => 'مخطط حضري رئيسي'],
            ['type' => 'project_role', 'value' => 'lead_graphic_designer', 'label_ar' => 'مصمم جرافيك رئيسي'],
            ['type' => 'project_role', 'value' => 'lead_ui_ux_designer', 'label_ar' => 'مصمم UI/UX رئيسي'],
            ['type' => 'project_role', 'value' => 'lead_social_media_designer', 'label_ar' => 'مصمم وسائل التواصل الاجتماعي رئيسي'],
            ['type' => 'project_role', 'value' => '3d_rendering_specialist', 'label_ar' => 'متخصص في العرض ثلاثي الأبعاد'],
            ['type' => 'project_role', 'value' => 'project_manager', 'label_ar' => 'مدير مشروع'],
            ['type' => 'project_role', 'value' => 'planning___supervision', 'label_ar' => 'تخطيط وإشراف'],
            ['type' => 'project_role', 'value' => 'developer', 'label_ar' => 'مطور'],
            ['type' => 'project_role', 'value' => 'services_provider', 'label_ar' => 'مزود خدمات'],
            ['type' => 'project_role', 'value' => 'other', 'label_ar' => 'أخرى'],

            // === SERVICE CATEGORIES ===
            ['type' => 'service_category', 'value' => 'carpentry', 'label_ar' => 'نجارة'],
            ['type' => 'service_category', 'value' => 'consultation', 'label_ar' => 'استشارة'],
            ['type' => 'service_category', 'value' => 'design', 'label_ar' => 'تصميم'],
            ['type' => 'service_category', 'value' => 'development', 'label_ar' => 'تطوير'],
            ['type' => 'service_category', 'value' => 'digital_illustration', 'label_ar' => 'رسم رقمي'],
            ['type' => 'service_category', 'value' => 'graphic_design', 'label_ar' => 'تصميم جرافيك'],
            ['type' => 'service_category', 'value' => 'installation', 'label_ar' => 'تركيب'],
            ['type' => 'service_category', 'value' => 'maintenance', 'label_ar' => 'صيانة'],
            ['type' => 'service_category', 'value' => 'manufacturing', 'label_ar' => 'تصنيع'],
            ['type' => 'service_category', 'value' => 'material_specification', 'label_ar' => 'مواصفات المواد'],
            ['type' => 'service_category', 'value' => 'photography', 'label_ar' => 'تصوير'],
            ['type' => 'service_category', 'value' => 'strategy', 'label_ar' => 'استراتيجية'],
            ['type' => 'service_category', 'value' => 'supervision', 'label_ar' => 'إشراف'],
            ['type' => 'service_category', 'value' => 'other', 'label_ar' => 'أخرى'],

            // === YEARS OF EXPERIENCE ===
            ['type' => 'years_experience', 'value' => '0-1 years', 'label_ar' => '0-1 سنوات'],
            ['type' => 'years_experience', 'value' => '1-3 years', 'label_ar' => '1-3 سنوات'],
            ['type' => 'years_experience', 'value' => '3-5 years', 'label_ar' => '3-5 سنوات'],
            ['type' => 'years_experience', 'value' => '5-10 years', 'label_ar' => '5-10 سنوات'],
            ['type' => 'years_experience', 'value' => '10+ years', 'label_ar' => '+10 سنوات'],

            // === FABLAB TYPES ===
            ['type' => 'fablab_type', 'value' => 'fablab', 'label_ar' => 'مختبر تصنيع'],
            ['type' => 'fablab_type', 'value' => 'makerspace', 'label_ar' => 'مساحة صنّاع'],
            ['type' => 'fablab_type', 'value' => 'hackerspace', 'label_ar' => 'مساحة مبتكرين'],
            ['type' => 'fablab_type', 'value' => 'workshop', 'label_ar' => 'ورشة عمل'],
            ['type' => 'fablab_type', 'value' => 'studio', 'label_ar' => 'استوديو'],

            // === MARKETPLACE TYPES ===
            ['type' => 'marketplace_type', 'value' => 'service', 'label_ar' => 'خدمة'],
            ['type' => 'marketplace_type', 'value' => 'collaboration', 'label_ar' => 'تعاون'],
            ['type' => 'marketplace_type', 'value' => 'showcase', 'label_ar' => 'عرض'],
            ['type' => 'marketplace_type', 'value' => 'opportunity', 'label_ar' => 'فرصة'],

            // === MARKETPLACE TAGS ===
            ['type' => 'marketplace_tag', 'value' => 'design', 'label_ar' => 'تصميم'],
            ['type' => 'marketplace_tag', 'value' => 'art', 'label_ar' => 'فن'],
            ['type' => 'marketplace_tag', 'value' => 'creative', 'label_ar' => 'إبداعي'],
            ['type' => 'marketplace_tag', 'value' => 'digital', 'label_ar' => 'رقمي'],
            ['type' => 'marketplace_tag', 'value' => 'photography', 'label_ar' => 'تصوير'],
            ['type' => 'marketplace_tag', 'value' => 'illustration', 'label_ar' => 'رسم توضيحي'],
            ['type' => 'marketplace_tag', 'value' => 'branding', 'label_ar' => 'علامة تجارية'],
            ['type' => 'marketplace_tag', 'value' => 'ui_ux', 'label_ar' => 'UI/UX'],
            ['type' => 'marketplace_tag', 'value' => 'web_design', 'label_ar' => 'تصميم ويب'],
            ['type' => 'marketplace_tag', 'value' => 'graphic_design', 'label_ar' => 'تصميم جرافيك'],
            ['type' => 'marketplace_tag', 'value' => 'interior_design', 'label_ar' => 'تصميم داخلي'],
            ['type' => 'marketplace_tag', 'value' => 'architecture', 'label_ar' => 'عمارة'],
            ['type' => 'marketplace_tag', 'value' => 'fashion', 'label_ar' => 'أزياء'],
            ['type' => 'marketplace_tag', 'value' => 'product_design', 'label_ar' => 'تصميم منتجات'],
            ['type' => 'marketplace_tag', 'value' => '3d_modeling', 'label_ar' => 'نمذجة ثلاثية الأبعاد'],
            ['type' => 'marketplace_tag', 'value' => 'animation', 'label_ar' => 'رسوم متحركة'],
            ['type' => 'marketplace_tag', 'value' => 'video', 'label_ar' => 'فيديو'],
            ['type' => 'marketplace_tag', 'value' => 'marketing', 'label_ar' => 'تسويق'],
            ['type' => 'marketplace_tag', 'value' => 'social_media', 'label_ar' => 'وسائل التواصل'],
            ['type' => 'marketplace_tag', 'value' => 'print', 'label_ar' => 'طباعة'],
            ['type' => 'marketplace_tag', 'value' => 'packaging', 'label_ar' => 'تغليف'],
            ['type' => 'marketplace_tag', 'value' => 'logo', 'label_ar' => 'شعار'],
            ['type' => 'marketplace_tag', 'value' => 'typography', 'label_ar' => 'فن الخط'],
            ['type' => 'marketplace_tag', 'value' => 'motion_graphics', 'label_ar' => 'رسوم متحركة'],
            ['type' => 'marketplace_tag', 'value' => 'consulting', 'label_ar' => 'استشارات'],
            ['type' => 'marketplace_tag', 'value' => 'freelance', 'label_ar' => 'عمل حر'],
            ['type' => 'marketplace_tag', 'value' => 'collaboration', 'label_ar' => 'تعاون'],
            ['type' => 'marketplace_tag', 'value' => 'commission', 'label_ar' => 'عمل بالطلب'],
            ['type' => 'marketplace_tag', 'value' => 'for_sale', 'label_ar' => 'للبيع'],
            ['type' => 'marketplace_tag', 'value' => 'hiring', 'label_ar' => 'توظيف'],
            ['type' => 'marketplace_tag', 'value' => 'looking_for_work', 'label_ar' => 'أبحث عن عمل'],
            ['type' => 'marketplace_tag', 'value' => 'partnership', 'label_ar' => 'شراكة'],

            // === TRAINING CATEGORIES ===
            ['type' => 'training_category', 'value' => 'design', 'label_ar' => 'تصميم'],
            ['type' => 'training_category', 'value' => 'development', 'label_ar' => 'تطوير'],
            ['type' => 'training_category', 'value' => 'marketing', 'label_ar' => 'تسويق'],
            ['type' => 'training_category', 'value' => 'business', 'label_ar' => 'أعمال'],
            ['type' => 'training_category', 'value' => 'photography', 'label_ar' => 'تصوير'],
            ['type' => 'training_category', 'value' => 'video', 'label_ar' => 'إنتاج فيديو'],
            ['type' => 'training_category', 'value' => '3d_modeling', 'label_ar' => 'نمذجة ثلاثية الأبعاد'],
            ['type' => 'training_category', 'value' => 'entrepreneurship', 'label_ar' => 'ريادة الأعمال'],

            // === TENDER CATEGORIES ===
            ['type' => 'tender_category', 'value' => 'branding', 'label_ar' => 'العلامة التجارية والهوية'],
            ['type' => 'tender_category', 'value' => 'web_development', 'label_ar' => 'تطوير الويب'],
            ['type' => 'tender_category', 'value' => 'product_design', 'label_ar' => 'تصميم المنتجات'],
            ['type' => 'tender_category', 'value' => 'ux_ui_design', 'label_ar' => 'تصميم UX/UI'],
            ['type' => 'tender_category', 'value' => 'digital_marketing', 'label_ar' => 'التسويق الرقمي'],
            ['type' => 'tender_category', 'value' => 'architecture', 'label_ar' => 'عمارة'],
            ['type' => 'tender_category', 'value' => 'illustration', 'label_ar' => 'رسم توضيحي'],
            ['type' => 'tender_category', 'value' => 'video_production', 'label_ar' => 'إنتاج فيديو'],
            ['type' => 'tender_category', 'value' => 'consulting', 'label_ar' => 'استشارات'],
            ['type' => 'tender_category', 'value' => 'other', 'label_ar' => 'أخرى'],

            // === SKILLS (Arabic translations for display) ===
            ['type' => 'skill', 'value' => '3d_modeling', 'label_ar' => 'نمذجة ثلاثية الأبعاد'],
            ['type' => 'skill', 'value' => '3ds_max', 'label_ar' => '3ds Max'],
            ['type' => 'skill', 'value' => 'abstract_art', 'label_ar' => 'فن تجريدي'],
            ['type' => 'skill', 'value' => 'acrylic_painting', 'label_ar' => 'رسم أكريليك'],
            ['type' => 'skill', 'value' => 'animation', 'label_ar' => 'رسوم متحركة'],
            ['type' => 'skill', 'value' => 'archicad', 'label_ar' => 'ArchiCAD'],
            ['type' => 'skill', 'value' => 'architectural_drawing', 'label_ar' => 'رسم معماري'],
            ['type' => 'skill', 'value' => 'art_theory', 'label_ar' => 'نظرية الفن'],
            ['type' => 'skill', 'value' => 'assemblage', 'label_ar' => 'تجميع فني'],
            ['type' => 'skill', 'value' => 'autocad', 'label_ar' => 'AutoCAD'],
            ['type' => 'skill', 'value' => 'branding', 'label_ar' => 'علامة تجارية'],
            ['type' => 'skill', 'value' => 'building_codes', 'label_ar' => 'أكواد البناء'],
            ['type' => 'skill', 'value' => 'building_design', 'label_ar' => 'تصميم المباني'],
            ['type' => 'skill', 'value' => 'business_development', 'label_ar' => 'تطوير الأعمال'],
            ['type' => 'skill', 'value' => 'ceramics', 'label_ar' => 'خزف'],
            ['type' => 'skill', 'value' => 'charcoal', 'label_ar' => 'فحم'],
            ['type' => 'skill', 'value' => 'clay_modeling', 'label_ar' => 'نمذجة الصلصال'],
            ['type' => 'skill', 'value' => 'collage', 'label_ar' => 'فن الكولاج'],
            ['type' => 'skill', 'value' => 'color_theory', 'label_ar' => 'نظرية الألوان'],
            ['type' => 'skill', 'value' => 'composition', 'label_ar' => 'تكوين'],
            ['type' => 'skill', 'value' => 'concept_art', 'label_ar' => 'فن مفاهيمي'],
            ['type' => 'skill', 'value' => 'construction_documentation', 'label_ar' => 'وثائق البناء'],
            ['type' => 'skill', 'value' => 'content_writing', 'label_ar' => 'كتابة المحتوى'],
            ['type' => 'skill', 'value' => 'corel_painter', 'label_ar' => 'Corel Painter'],
            ['type' => 'skill', 'value' => 'darkroom', 'label_ar' => 'غرفة مظلمة'],
            ['type' => 'skill', 'value' => 'digital_art', 'label_ar' => 'فن رقمي'],
            ['type' => 'skill', 'value' => 'digital_painting', 'label_ar' => 'رسم رقمي'],
            ['type' => 'skill', 'value' => 'drawing', 'label_ar' => 'رسم'],
            ['type' => 'skill', 'value' => 'etching', 'label_ar' => 'حفر'],
            ['type' => 'skill', 'value' => 'fiber_art', 'label_ar' => 'فن الألياف'],
            ['type' => 'skill', 'value' => 'figure_drawing', 'label_ar' => 'رسم الأشكال'],
            ['type' => 'skill', 'value' => 'fine_art_photography', 'label_ar' => 'تصوير فنون جميلة'],
            ['type' => 'skill', 'value' => 'glass_art', 'label_ar' => 'فن الزجاج'],
            ['type' => 'skill', 'value' => 'glassblowing', 'label_ar' => 'نفخ الزجاج'],
            ['type' => 'skill', 'value' => 'graffiti', 'label_ar' => 'غرافيتي'],
            ['type' => 'skill', 'value' => 'graphic_design', 'label_ar' => 'تصميم جرافيك'],
            ['type' => 'skill', 'value' => 'illustrator', 'label_ar' => 'Illustrator'],
            ['type' => 'skill', 'value' => 'installation_art', 'label_ar' => 'فن التركيبات'],
            ['type' => 'skill', 'value' => 'leed_certification', 'label_ar' => 'شهادة LEED'],
            ['type' => 'skill', 'value' => 'lithography', 'label_ar' => 'طباعة حجرية'],
            ['type' => 'skill', 'value' => 'marketing', 'label_ar' => 'تسويق'],
            ['type' => 'skill', 'value' => 'metalworking', 'label_ar' => 'أعمال المعادن'],
            ['type' => 'skill', 'value' => 'mixed_media', 'label_ar' => 'وسائط متعددة'],
            ['type' => 'skill', 'value' => 'mobile_development', 'label_ar' => 'تطوير تطبيقات الجوال'],
            ['type' => 'skill', 'value' => 'mural_painting', 'label_ar' => 'رسم جداري'],
            ['type' => 'skill', 'value' => 'oil_painting', 'label_ar' => 'رسم زيتي'],
            ['type' => 'skill', 'value' => 'photoshop', 'label_ar' => 'Photoshop'],
            ['type' => 'skill', 'value' => 'photo_editing', 'label_ar' => 'تحرير الصور'],
            ['type' => 'skill', 'value' => 'photography', 'label_ar' => 'تصوير'],
            ['type' => 'skill', 'value' => 'portrait_art', 'label_ar' => 'فن البورتريه'],
            ['type' => 'skill', 'value' => 'pottery', 'label_ar' => 'فخار'],
            ['type' => 'skill', 'value' => 'printmaking', 'label_ar' => 'فن الطباعة'],
            ['type' => 'skill', 'value' => 'procreate', 'label_ar' => 'Procreate'],
            ['type' => 'skill', 'value' => 'project_management', 'label_ar' => 'إدارة المشاريع'],
            ['type' => 'skill', 'value' => 'public_art', 'label_ar' => 'فن عام'],
            ['type' => 'skill', 'value' => 'realism', 'label_ar' => 'واقعية'],
            ['type' => 'skill', 'value' => 'relief_printing', 'label_ar' => 'طباعة بارزة'],
            ['type' => 'skill', 'value' => 'revit', 'label_ar' => 'Revit'],
            ['type' => 'skill', 'value' => 'rhino', 'label_ar' => 'Rhino'],
            ['type' => 'skill', 'value' => 'screen_printing', 'label_ar' => 'طباعة شاشية'],
            ['type' => 'skill', 'value' => 'sculpture', 'label_ar' => 'نحت'],
            ['type' => 'skill', 'value' => 'site_planning', 'label_ar' => 'تخطيط الموقع'],
            ['type' => 'skill', 'value' => 'sketching', 'label_ar' => 'رسم تخطيطي'],
            ['type' => 'skill', 'value' => 'sketchup', 'label_ar' => 'SketchUp'],
            ['type' => 'skill', 'value' => 'social_media', 'label_ar' => 'وسائل التواصل'],
            ['type' => 'skill', 'value' => 'spray_paint', 'label_ar' => 'رش الدهان'],
            ['type' => 'skill', 'value' => 'street_art', 'label_ar' => 'فن الشارع'],
            ['type' => 'skill', 'value' => 'structural_design', 'label_ar' => 'تصميم إنشائي'],
            ['type' => 'skill', 'value' => 'sustainable_design', 'label_ar' => 'تصميم مستدام'],
            ['type' => 'skill', 'value' => 'textile_art', 'label_ar' => 'فن النسيج'],
            ['type' => 'skill', 'value' => 'ux_ui_design', 'label_ar' => 'تصميم UX/UI'],
            ['type' => 'skill', 'value' => 'v_ray', 'label_ar' => 'V-Ray'],
            ['type' => 'skill', 'value' => 'videography', 'label_ar' => 'تصوير فيديو'],
            ['type' => 'skill', 'value' => 'watercolor', 'label_ar' => 'ألوان مائية'],
            ['type' => 'skill', 'value' => 'weaving', 'label_ar' => 'نسج'],
            ['type' => 'skill', 'value' => 'web_development', 'label_ar' => 'تطوير الويب'],
            ['type' => 'skill', 'value' => 'woodworking', 'label_ar' => 'أعمال الخشب'],
        ];

        foreach ($translations as $translation) {
            DB::table('dropdown_options')
                ->where('type', $translation['type'])
                ->where('value', $translation['value'])
                ->whereNull('label_ar')
                ->update(['label_ar' => $translation['label_ar']]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set all label_ar back to NULL (except vendor sector ones from other migration)
        DB::table('dropdown_options')
            ->whereNotIn('value', ['vendor_supplier', 'electrical_vendor', 'wood_vendor', 'mechanical_vendor', 'lighting_vendor', 'general_vendor'])
            ->update(['label_ar' => null]);
    }
};
