<?php

return [
    'backup' => [
        // to backup the database tables
        'database' => [
            'allow' => true,
            'connection' => $_ENV['DB_CONNECTION'],
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'name' => $_ENV['DB_DATABASE'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
        ],
        // upload backup file into cloud services [] 
        'cloud_services' => [
            'google_drive' => [
                'allow' => true,
                'folder_id' => '', // 
                'client_id' => '', // 
                'client_secret' => '', // 
            ],
            'aws' => [
                'allow' => true,
                'access_key' => '', // 
                'secret_key' => '', // 
                'bucket_name' => '', // 
            ]
        ],
        // Main email that will get emails about backup whatever it is success or failure 
        // [you could add cc on each type of emails in the options below]
        'mail' => [
            'allow' => true,
            'to' => 'to@example.com'
        ],
        // to backup the complete project files
        'full_project' => true,
        // to backup the storage folder only
        'storage_only' => true,
        // location of project storage
        'storage_path' => "storage/",
        // to backup specific folders or files
        'specific_folders_or_files' => [
            'tests',
            '.env'
            // 'app/folder1',
            // 'app/file.txt',
        ],
        // to store backup files to Google Drive  
        'google_drive' => false,
        // enable sending alert emails
        'email_alert' => true,
        // to exclude specific folders
        'exclude_folders' => [
            '.git',
            'vendor',
            'node_modules',
        ],
        // that's mean the original back up folders will be kept in "/storage/backup-sentry/files/" [Which will be kept uncompressed]
        'keep_original_backup_folders' => true,
        // cleanup
        'cleanup' => null // null OR (int) n of days 
    ],
    'options' => [
        // put the emails to be alerted with the successful backup notification
        'alert_successful_backup_email_to' => ['first_email@example.com', 'second_email@example.com'],
        // put the emails to be alerted with the failure backup notification
        'alert_failure_backup_email_to' => ['developer@example.com', 'devops@example.com'],
        // encryption type for the compressed file
        'encryption' => 'EM_AES_256',
    ]
];
