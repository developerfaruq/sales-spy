<?php
require 'smtp_details.php';// smtp details

return [
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'username' => $mails, // mail
        'password' => $passs,   // 16-digit app password
        'port' => 587,
        'encryption' => 'tls',
        'from_email' => $mails,
        'from_name' => 'Sales-Spy Admin'
    ],

    'templates' => [
        'suspension' => [
            'subject' => 'Account Suspension Notice - Sales-Spy',
            'support_email' => $mails,
            'company_name' => 'Sales-Spy',
            'website_url' => 'https://sales-spy.test/'  //  update to HTTPS
        ],
        'unsuspension' => [
            'subject' => 'Account Reactivated - Sales-Spy',
            'login_url' => 'https://sales-spy.test/signup.php?form=login',
            'support_email' => $mails,
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