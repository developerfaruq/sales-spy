<?php
// config/email_config.php

// Email configuration settings
return [
    'smtp' => [
        'host' => 'smtp.ethereal.email', // Your SMTP server
        'username' => 'carson.kessler@ethereal.email', // Your email address
        'password' => 'e5BhuTHEfyKfbjEH6R', // app password
        'port' => 587,
        'encryption' => 'tls', 
        'from_email' => 'sales-spy@gmail.com',
        'from_name' => 'Sales-Spy Admin'
    ],
    
    // Alternative SMTP providers configurations
    /*'providers' => [
        'gmail' => [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'encryption' => 'tls'
        ],
        'outlook' => [
            'host' => 'smtp.live.com',
            'port' => 587,
            'encryption' => 'tls'
        ],
        'yahoo' => [
            'host' => 'smtp.mail.yahoo.com',
            'port' => 587,
            'encryption' => 'tls'
        ],
        'mailgun' => [
            'host' => 'smtp.mailgun.org',
            'port' => 587,
            'encryption' => 'tls'
        ],
        'sendgrid' => [
            'host' => 'smtp.sendgrid.net',
            'port' => 587,
            'encryption' => 'tls'
        ]
    ],*/
    
    // Email templates settings
    'templates' => [
        'suspension' => [
            'subject' => 'Account Suspension Notice - Sales-Spy',
            'support_email' => 'developerfaruq@gmail.com',
            'company_name' => 'Sales-Spy',
            'website_url' => 'http://localhost/sales-spy/'
        ],
        'unsuspension' => [
            'subject' => 'Account Reactivated - Sales-Spy',
            'login_url' => 'http://localhost/sales-spy/signup.php?form=login',
            'support_email' => 'developerfaruq@gmail.com',
            'company_name' => 'Sales-Spy',
            'website_url' => 'http://localhost/sales-spy/'
        ]
    ],
    
    // Email settings
    'settings' => [
        'timeout' => 30, // SMTP timeout in seconds
        'debug' => 0, // 0 = off, 1 = client messages, 2 = client and server messages
        'charset' => 'UTF-8',
        'word_wrap' => 50
    ]
];
?>