-- ============================================================
-- Arabic Translations for dropdown_options table
-- Run this on your production MySQL database.
-- Only updates rows where label_ar IS NULL (safe to re-run).
-- ============================================================

-- === SECTORS ===
UPDATE `dropdown_options` SET `label_ar` = 'أكاديمي' WHERE `type` = 'sector' AND `value` = 'academic' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'الموردون والبائعون' WHERE `type` = 'sector' AND `value` = 'supplier_vendors' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مهندس معماري' WHERE `type` = 'sector' AND `value` = 'architect' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مصمم' WHERE `type` = 'sector' AND `value` = 'designer' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مصنّع' WHERE `type` = 'sector' AND `value` = 'manufacturer' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'صالة عرض / تجزئة' WHERE `type` = 'sector' AND `value` = 'showroom' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'زائر' WHERE `type` = 'sector' AND `value` = 'guest' AND `label_ar` IS NULL;

-- === SUBSECTORS - Academic ===
UPDATE `dropdown_options` SET `label_ar` = 'تعليم العمارة' WHERE `type` = 'subsector' AND `value` = 'architecture_education' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'تعليم الفنون' WHERE `type` = 'subsector' AND `value` = 'art_education' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'تاريخ الفن' WHERE `type` = 'subsector' AND `value` = 'art_history' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'تعليم التصميم' WHERE `type` = 'subsector' AND `value` = 'design_education' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'بحث التصميم' WHERE `type` = 'subsector' AND `value` = 'design_research' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'دراسات المتاحف' WHERE `type` = 'subsector' AND `value` = 'museum_studies' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'باحث' WHERE `type` = 'subsector' AND `value` = 'researcher' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'طالب' WHERE `type` = 'subsector' AND `value` = 'student' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'أستاذ جامعي' WHERE `type` = 'subsector' AND `value` = 'university_professor' AND `label_ar` IS NULL;

-- === SUBSECTORS - Supplier/Vendors ===
UPDATE `dropdown_options` SET `label_ar` = 'مورّد كهربائي' WHERE `type` = 'subsector' AND `value` = 'electrical_supplier' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مورّد إضاءة' WHERE `type` = 'subsector' AND `value` = 'lightning_supplier' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مورّد أخشاب' WHERE `type` = 'subsector' AND `value` = 'wood_supplier' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مورّد ميكانيكي' WHERE `type` = 'subsector' AND `value` = 'mechanical_supplier' AND `label_ar` IS NULL;

-- === SUBSECTORS - Architect ===
UPDATE `dropdown_options` SET `label_ar` = 'مهندس معماري تجاري' WHERE `type` = 'subsector' AND `value` = 'commercial_architect' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'هندسة' WHERE `type` = 'subsector' AND `value` = 'engineering' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مهندس معماري صناعي' WHERE `type` = 'subsector' AND `value` = 'industrial_architect' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مهندس معماري داخلي' WHERE `type` = 'subsector' AND `value` = 'interior_architect' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مهندس معماري للمناظر الطبيعية' WHERE `type` = 'subsector' AND `value` = 'landscape_architect' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مهندس معماري سكني' WHERE `type` = 'subsector' AND `value` = 'residential_architect' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مهندس ترميم' WHERE `type` = 'subsector' AND `value` = 'restoration_architect' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مهندس التصميم المستدام' WHERE `type` = 'subsector' AND `value` = 'sustainable_design_architect' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مخطط حضري' WHERE `type` = 'subsector' AND `value` = 'urban_planner' AND `label_ar` IS NULL;

-- === SUBSECTORS - Designer ===
UPDATE `dropdown_options` SET `label_ar` = 'خزّاف' WHERE `type` = 'subsector' AND `value` = 'ceramicist' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'فنان مفاهيمي' WHERE `type` = 'subsector' AND `value` = 'conceptual_artist' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'فنان رقمي' WHERE `type` = 'subsector' AND `value` = 'digital_artist' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مصمم أزياء' WHERE `type` = 'subsector' AND `value` = 'fashion_designer' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مصور فنون جميلة' WHERE `type` = 'subsector' AND `value` = 'fine_art_photographer' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'فنان زجاج' WHERE `type` = 'subsector' AND `value` = 'glass_artist' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مصمم جرافيك' WHERE `type` = 'subsector' AND `value` = 'graphic_designer' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'فنان تركيبات' WHERE `type` = 'subsector' AND `value` = 'installation_artist' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مصمم داخلي' WHERE `type` = 'subsector' AND `value` = 'interior_designer' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'فنان وسائط متعددة' WHERE `type` = 'subsector' AND `value` = 'mixed_media_artist' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'رسّام' WHERE `type` = 'subsector' AND `value` = 'painter' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'فنان طباعة' WHERE `type` = 'subsector' AND `value` = 'printmaker' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مصمم منتجات' WHERE `type` = 'subsector' AND `value` = 'product_designer' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'نحّات' WHERE `type` = 'subsector' AND `value` = 'sculptor' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'فنان شارع' WHERE `type` = 'subsector' AND `value` = 'street_artist' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'فنان نسيج' WHERE `type` = 'subsector' AND `value` = 'textile_artist' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مصمم UX/UI' WHERE `type` = 'subsector' AND `value` = 'ux_ui_designer' AND `label_ar` IS NULL;

-- === SUBSECTORS - Manufacturer ===
UPDATE `dropdown_options` SET `label_ar` = 'مصنّع أثاث' WHERE `type` = 'subsector' AND `value` = 'furniture_manufacturer' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'حرفيون وصنّاع يدويون' WHERE `type` = 'subsector' AND `value` = 'handicrafters___artisans' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'أعمال المعادن والخشب' WHERE `type` = 'subsector' AND `value` = 'metal_and_wood_work' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'أعمال المعادن' WHERE `type` = 'subsector' AND `value` = 'metal_works' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'مصنّع نسيج' WHERE `type` = 'subsector' AND `value` = 'textile_manufacturer' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'أعمال الخشب' WHERE `type` = 'subsector' AND `value` = 'woodworks' AND `label_ar` IS NULL;

-- === SUBSECTORS - Showroom ===
UPDATE `dropdown_options` SET `label_ar` = 'معرض فني' WHERE `type` = 'subsector' AND `value` = 'art_gallery' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'متجر حرف يدوية' WHERE `type` = 'subsector' AND `value` = 'craft_store' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'متجر تصميم' WHERE `type` = 'subsector' AND `value` = 'design_store' AND `label_ar` IS NULL;
UPDATE `dropdown_options` SET `label_ar` = 'صالة عرض أثاث' WHERE `type` = 'subsector' AND `value` = 'furniture_showroom' AND `label_ar` IS NULL;

-- ============================================================
-- Also update any rows that HAVE label_ar but it might be
-- empty string instead of NULL
-- ============================================================
UPDATE `dropdown_options` SET `label_ar` = 'أكاديمي' WHERE `type` = 'sector' AND `value` = 'academic' AND (`label_ar` IS NULL OR `label_ar` = '');

-- ============================================================
-- Clear dropdown caches after running this script
-- Run in Laravel: \App\Models\DropdownOption::clearCache();
-- Or manually delete cache files in storage/framework/cache/
-- ============================================================
