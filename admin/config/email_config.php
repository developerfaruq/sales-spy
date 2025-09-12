<?php
// config/email_config.php

return [
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'username' => 'developerfaruq@gmail.com', // mail
        'password' => 'rinf vyyn yvrc ckwf',   // 16-digit app password
        'port' => 587,
        'encryption' => 'tls',
        'from_email' => 'developerfaruq@gmail.com',
        'from_name' => 'Sales-Spy Admin'
    ],

    'templates' => [
        'suspension' => [
            'subject' => 'Account Suspension Notice - Sales-Spy',
            'support_email' => 'developerfaruq@gmail.com',
            'company_name' => 'Sales-Spy',
            'website_url' => 'https://sales-spy.test/'  //  update to HTTPS
        ],
        'unsuspension' => [
            'subject' => 'Account Reactivated - Sales-Spy',
            'login_url' => 'https://sales-spy.test/signup.php?form=login',
            'support_email' => 'developerfaruq@gmail.com',
            'company_name' => 'Sales-Spy',
            'website_url' => 'https://sales-spy.test/'  //  update to HTTPS
        ]
    ],

    'settings' => [
        'timeout' => 30,
        'debug' => 0,
        'charset' => 'UTF-8',
        'word_wrap' => 50
    ]
];
?>