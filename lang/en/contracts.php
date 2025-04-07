<?php

return [
    'validation' => [
        'duration' => [
            'required' => 'Please select contract duration',
            'in' => 'Contract duration must be 1, 2, or 3 years',
        ],
        'start_date' => [
            'required' => 'Please select contract start date',
            'after_or_equal' => 'Contract start date must be today or after',
        ],
        'level' => [
            'required' => 'Please select contract level',
            'in' => 'Contract level must be High, Medium, or Low',
        ],
        'services' => [
            'required' => 'Please select at least one service',
            'min' => 'Please select at least one service',
            'distinct' => 'You cannot select the same service multiple times',
        ],
        'terms_accepted' => [
            'accepted' => 'You must accept terms and conditions',
        ],
        'payment_type' => [
            'required' => 'Please select payment method',
            'in' => 'Invalid payment method',
        ],
        'reasons' => [
            'hotel_only' => 'The account must be a hotel account.',
            'complete_profile' => 'The account must be complete.',
            'bank_info' => 'The account must be linked to bank information.',
        ],
        'unauthorized' => 'You are not authorized to create a new contract.',
    ],
];
