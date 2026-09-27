<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Document Store Disk
    |--------------------------------------------------------------------------
    |
    | Which disk App\Support\Documents\DocumentStore writes to — see
    | docs/build/00-build-plan.md Step 0.9 and docs/build/DECISIONS.md
    | D-010. 'local' (private, not web-accessible — see the 'local' disk
    | below) in dev; set DOCUMENTS_DISK=s3 in staging/prod once real S3
    | credentials exist. Never 'public' — documents are access-controlled
    | via App\Policies\DocumentPolicy and a signed download route, not a
    | guessable public URL.
    |
    */

    'documents_disk' => env('DOCUMENTS_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
            // Server-side encryption-at-rest, per docs/02-architecture.md's
            // "encrypted object storage" requirement — forwarded as the
            // default PutObject option for every write to this disk. Not
            // yet exercised against a real bucket (no S3 credentials
            // exist in this environment) — see docs/build/DECISIONS.md
            // D-030, which covers app-layer encryption as the mechanism
            // actually verified so far, independent of this.
            'options' => [
                'ServerSideEncryption' => env('AWS_SERVER_SIDE_ENCRYPTION', 'AES256'),
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
