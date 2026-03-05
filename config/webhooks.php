<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Jobs.ps Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for receiving tender webhooks from jobs.ps
    |
    */

    'jobs_ps' => [
        /*
        |--------------------------------------------------------------------------
        | Public Key Path
        |--------------------------------------------------------------------------
        |
        | The path to the public key file provided by jobs.ps for signature
        | verification. This key is used to verify that incoming webhooks
        | are genuinely from jobs.ps.
        |
        | Example: storage_path('keys/jobs_ps_public.pem')
        |
        */
        'public_key_path' => env('JOBS_PS_PUBLIC_KEY_PATH') ?: storage_path('keys/jobs_ps_public.pem'),

        /*
        |--------------------------------------------------------------------------
        | Public Key (Inline)
        |--------------------------------------------------------------------------
        |
        | Alternatively, you can provide the public key directly as a string.
        | This is useful for environments where file storage is not available.
        |
        | Note: If both public_key_path and public_key are set, the path takes
        | precedence if the file exists.
        |
        */
        'public_key' => env('JOBS_PS_PUBLIC_KEY'),

        /*
        |--------------------------------------------------------------------------
        | Skip Verification (Development Only)
        |--------------------------------------------------------------------------
        |
        | WARNING: Only use this in development environments!
        | When set to true and app.env is 'local', signature verification
        | will be skipped. This should NEVER be true in production.
        |
        */
        'skip_verification' => env('JOBS_PS_SKIP_VERIFICATION', false),
    ],

];
