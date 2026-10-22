<?php

/**
 * ⚠️ WARNING: DO NOT REMOVE OR RENAME DEFAULT KEYS IN THIS CONFIGURATION FILE.
 * Modifying key names may break core application logic, especially 
 * genealogy, crypto wallet integrations, and package distributions.
 * Always use the .env file to override values where applicable.
 */

return [
    'otp' => [
        'enable_for_registration' => env('OTP_ENABLE_FOR_REGISTRATION', false),
        'enable_for_login' => env('OTP_ENABLE_FOR_LOGIN', false),
    ],
    'geneology' => [
        'tree' => 'matrix', // 5-5,
        'show_progression_count' => false
    ],
    'crypto_wallet' => [
        'enable' => false,
        'bep20' => [
            'use_api' => 'moralis', // moralis | rpc_ankr | nodereal | bscscan (need paid plan)
            'api_key' => env('BEP_API_KEY', null),
            'contract_address' => env('BEP20_CONTRACT_ADDRESS', '0x55d398326f99059fF775485246999027B3197955'), // USDT
            'bscscan_url' => env('BSCSCAN_API_URL', null),
            'bscscan_key' => env('BSCSCAN_API_KEY', null),
            'nodereal_api_url' => env('NODEREAL_API_URL', null),
            'rpc_ankr_url' => env('RPC_ANKR_API_URL', null),
            'moralis_api_key' => env('MORALIS_API_KEY', null),
        ],
    ],
    'packages' => [
        'enable' => true,
        'has_levels' => true,   
        'max_levels' => 5,
        'income_config' => false,
    ],
    'reset_db' => [
        'enable' => env('APP_ENV') === 'production' ? false : true, // for testing
        'create_backup' => env('APP_ENV') === 'production' ? false : true, // for testing
        'mysqldump_path' => env('MYSQLDUMP_PATH'), // required if create_backup is true
    ],
    'pricing' => [
        'icon' => 'rupee', // dollar
        'symbol' => '₹', // $
        'position' => 'before', // after
        'currency_code' => 'INR', // USD
        'format' => [
            'decimal_place' => 2,
            'thousand_separator' => ',',
            'decimal_separator' => '.',
        ]
    ]
];
