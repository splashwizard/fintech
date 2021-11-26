<?php

return [
    
     /*
    |--------------------------------------------------------------------------
    | App Constants
    |--------------------------------------------------------------------------
    |List of all constants for the app
    */

    'langs' => [
        'en' => ['full_name' => 'English', 'short_name' => 'English'],
        'id' => ['full_name' => 'Malay', 'short_name' => 'Bahasa'],
        'ce' => ['full_name' => 'Chinese', 'short_name' => '中文']
    ],
    //'langs_rtl' => ['ar'],
    //'non_utf8_languages' => ['ar', 'hi', 'ps'],
    
    'document_size_limit' => '1000000', //in Bytes,
    'image_size_limit' => '500000', //in Bytes

    'asset_version' => 44,

    'disable_expiry' => false,

    'disable_purchase_in_other_currency' => true,
    
    'iraqi_selling_price_adjustment' => false,

    'currency_precision' => 2, //Maximum 4
    'quantity_precision' => 2,  //Maximum 4

    'product_img_path' => 'img',

    'enable_custom_payment_1' => true,
    'enable_custom_payment_2' => false,
    'enable_custom_payment_3' => false,

    'enable_sell_in_diff_currency' => false,
    'currency_exchange_rate' => 1,
    'orders_refresh_interval' => 600, //Auto refresh interval on Kitchen and Orders page in seconds,

    'default_date_format' => 'd-m-Y' //Default date format to be used if session is not set. All valid formats can be found on https://www.php.net/manual/en/function.date.php
];
