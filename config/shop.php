<?php

return [
    'minimum_order_cents' => 1000,
    'registration_email_rule' => env('APP_ENV') === 'testing' ? 'email:rfc' : 'email:rfc,dns',
    'orders' => [
        'admin_email' => env('ORDER_ADMIN_EMAIL'),
    ],
];
