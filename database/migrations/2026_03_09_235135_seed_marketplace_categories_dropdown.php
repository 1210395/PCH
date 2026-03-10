<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\DropdownOption;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            ['label' => '3D Modeling', 'label_ar' => 'نمذجة ثلاثية الأبعاد'],
            ['label' => 'Advertising', 'label_ar' => 'إعلانات'],
            ['label' => 'Animation', 'label_ar' => 'رسوم متحركة'],
            ['label' => 'Architecture', 'label_ar' => 'هندسة معمارية'],
            ['label' => 'Art Direction', 'label_ar' => 'إخراج فني'],
            ['label' => 'Branding', 'label_ar' => 'هوية بصرية'],
            ['label' => 'Ceramics', 'label_ar' => 'خزف'],
            ['label' => 'Consulting', 'label_ar' => 'استشارات'],
            ['label' => 'Content Creation', 'label_ar' => 'إنشاء محتوى'],
            ['label' => 'Crafts', 'label_ar' => 'حرف يدوية'],
            ['label' => 'Digital Art', 'label_ar' => 'فن رقمي'],
            ['label' => 'Education', 'label_ar' => 'تعليم'],
            ['label' => 'Fashion Design', 'label_ar' => 'تصميم أزياء'],
            ['label' => 'Graphic Design', 'label_ar' => 'تصميم جرافيك'],
            ['label' => 'Illustration', 'label_ar' => 'رسم توضيحي'],
            ['label' => 'Interior Design', 'label_ar' => 'تصميم داخلي'],
            ['label' => 'Jewelry', 'label_ar' => 'مجوهرات'],
            ['label' => 'Marketing', 'label_ar' => 'تسويق'],
            ['label' => 'Metalwork', 'label_ar' => 'أعمال معدنية'],
            ['label' => 'Motion Graphics', 'label_ar' => 'موشن جرافيك'],
            ['label' => 'Packaging', 'label_ar' => 'تغليف'],
            ['label' => 'Photography', 'label_ar' => 'تصوير فوتوغرافي'],
            ['label' => 'Print Design', 'label_ar' => 'تصميم طباعة'],
            ['label' => 'Product Design', 'label_ar' => 'تصميم منتجات'],
            ['label' => 'Social Media', 'label_ar' => 'وسائل التواصل الاجتماعي'],
            ['label' => 'Textiles', 'label_ar' => 'منسوجات'],
            ['label' => 'Typography', 'label_ar' => 'خطوط وطباعة'],
            ['label' => 'UI/UX Design', 'label_ar' => 'تصميم واجهات المستخدم'],
            ['label' => 'Video Production', 'label_ar' => 'إنتاج فيديو'],
            ['label' => 'Web Design', 'label_ar' => 'تصميم ويب'],
            ['label' => 'Woodworking', 'label_ar' => 'أعمال خشبية'],
            ['label' => 'Workshops', 'label_ar' => 'ورش عمل'],
            ['label' => 'Other', 'label_ar' => 'أخرى'],
        ];

        foreach ($categories as $index => $category) {
            DropdownOption::firstOrCreate(
                ['type' => 'marketplace_category', 'value' => strtolower(str_replace(['/', ' '], ['-', '-'], $category['label']))],
                [
                    'label' => $category['label'],
                    'label_ar' => $category['label_ar'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'is_system' => false,
                ]
            );
        }
    }

    public function down(): void
    {
        DropdownOption::where('type', 'marketplace_category')->delete();
    }
};
