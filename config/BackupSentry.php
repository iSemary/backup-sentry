<?php

return [
    'backup' => [
        // to backup the database tables
        'database' => true,
        // to backup the complete project files
        'full_project' => true,
        // to backup the storage folder only
        'storage_only' => false,
        // location of project storage
        'storage_path' => "storage/",
        // to backup specific folders
        'specific_folders' => [
            'app/folder1',
            'app/folder2',
        ],
        // to store backup files to Google Drive  
        'google_drive' => false,
        // enable sending alert emails
        'email_alert' => true,
        // to exclude specific folders
        'exclude_folders' => [
            'vendor',
            'node_modules',
        ],
        // encryption type for the compressed file
        'encryption' => 'default', // EM_AES_256
        // cleanup
        'cleanup' => null // null OR n of days 
    ],
    'options' => [
        // put the emails to be alerted with the successful backup notification
        'alert_successful_backup_email_to' => ['first_email@example.com', 'second_email@example.com'],
        // put the emails to be alerted with the failure backup notification
        'alert_failure_backup_email_to' => ['developer@example.com', 'devops@example.com'],
    ]
];
