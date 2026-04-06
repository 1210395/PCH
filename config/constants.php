<?php
return [
    'langs' => [
        'es' => 'www.domain.es',
        'en' => 'www.domain.us'
        // etc
    ],
    'web_path' => env('APP_URL', 'http://demo.intertech.ps')."/",
    'web_path1' => env('APP_URL', 'http://demo.intertech.ps')."/",
    'web_static' => env('APP_URL', 'http://demo.intertech.ps')."/",
    'web_folder' => env('WEB_FOLDER', 'PalestineCreativeHub'), //set folder to nts when website is online
    'FOLDER_NAME' => env('WEB_FOLDER', 'PalestineCreativeHub'),
    'web_title' => env('APP_TITLE', 'Palestine Creative Hub'),
    'web_en_title' => env('APP_EN_TITLE', 'Palestine Creative Hub'),
    'meta_description' => '',
    'meta_keywords' => '',
    'QR_PATH' => 'qrcodes/',
    'BARCODE_PATH' => 'barcodes/',
    'FROM_EMAIL' => env('FROM_EMAIL', 'noreply@intertech.ps'),
    'NO_REPLY' => env('NO_REPLY_EMAIL', 'noreply@intertech.ps'),
    'CONTACT_US' => env('CONTACT_US_EMAIL', 'info@intertech.ps'),
    'INQUIRY' => env('INQUIRY_EMAIL', 'info@intertech.ps'),
    'COMPLAINT' => env('COMPLAINT_EMAIL', 'info@intertech.ps'),
    'COPIES_REQUEST' => env('COPIES_REQUEST_EMAIL', 'info@intertech.ps'),
    'NOTIFICATION_EMAIL' => env('NOTIFICATION_EMAIL', 'info@intertech.ps'),
    'COOKIE_EXPIRE' => time() + (10 * 365 * 24 * 60 * 60),
    'CLIENT'=> "0",
    'ORGANIZER' => "1",
    'ASSISTANT' => "2",
    'AVATAR_URL' => "public/images/avatar.png",
    'COVER_URL' => "public/images/cover.jpg",
    'ORG_AVATAR' => "public/images/organizer_avatar.png",
    'ITEMS_PER_PAGE' => 20,
    'MOBILE_PER_PAGE' => 10,
    'CURRENCY' => "<i class='fa fa-ils' aria-hidden='true' style='font-size:12px;'></i>",
    'TIME_ZONE' => "Asia/Jerusalem",
    'REST_CLASS' => "1",   
    'COURSE_CLASS' => "5",   
    'ORG_CLASSES' => "1,5",
    'TICKETS' => 2,
    'REGISTRATION' => 1,
    'OPEN' => 0,
    'FACEBOOK_AUTH' => 1,
    'LINKEDIN_AUTH' => 2,
    'GOOGLE_AUTH' => 3,
    'IMG_RATIO' => 1.33, // 4:3 standard ratio
    'EXT_ACCEPTED'=>".jpg,.png,.doc,.docx,.xlsx,.pdf,.ppt,.pptx,.xls,.mp3,.mp4,.wmv",

    'captcha_secret' => env('CAPTCHA_SECRET', ''),
    'captcha_sitekey' => env('CAPTCHA_SITEKEY', ''),

    'OG_IMAGE' => env('APP_URL').env('APP_FOLDER').'/public/images/logo.png',
    'OG_EXT' => 'PNG',

    'MERCHANT_ID' => env('BOP_MERCHANT_ID', ''),
    'ACQUIRER_ID' => env('BOP_ACQUIRER_ID', ''),
    'VERSION' => "1.0.0",
    'PAL_CURRENCY' => 376,
    'CURRENCY_EXP' => 2,
    'CAPTURE_FLAG' => 'M',
    'PASSWORD'  => env('BOP_PASSWORD', ''),
    'PAL_SUBMIT' => env('BOP_SUBMIT_URL', 'https://e-commerce.bop.ps/EcomPayment/RedirectAuthLink'),

    'IMG_EXTRA_LARGE' => '1700x300',
    'IMG_LARGE' => '1366x768',
    'IMG_MID' => '750x400',
    'IMG_XSMALL'=> '500x320',
    'IMG_SMALL'=> '350x200',
    'IMG_SMALL2'=> '270x140',
    'IMG_SMALL3'=> '200x140',
    'IMG_MINI'=> '90x90',
    'IMG_SQUARE' => '350x350',  
    'IMG_MOBILE'=> '490x245',  

    'DEFAULT_IMG' => 'public/images/default-logo.jpg',
    'DEFAULT_HEADER'=> 'public/files/red bg.png', 

    'PALPAY_USERNAME' => env('PALPAY_USERNAME', ''),
    'PALPAY_PASSWORD' => env('PALPAY_PASSWORD', ''),

    //Categories Types
    'PAGE_CATEGORY' => 1,
    'FILE_CATEGORY'=> 2,
    'BLOG_CATEGORY' => 3,
    'PHOTO_GALLERY' => 4,
    'VIDEO_GALLERY' => 5,
    'AUDIO_GALLERY' => 6,

    'WRONG_REQUEST' => 11,
    'USER_NOT_FOUND' => 22,
    'DATA_NOT_FOUND' => 33,
    'FAILED_REQUEST' => 44,
    'WRONG_DATA' => 55,
    'WRONG_AUTH' => 99,    
    'OK' => 200,

    'SMS_API' => env('SMS_API_KEY', '')
];

?>