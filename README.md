
# 🛡️ BackupSentry [Backup today, relax tomorrow]

### BackupSentry is a PHP backup package designed to provide a secure and seamless solution for protecting and backup your critical data. With its advanced features and intuitive interface, Effortlessly create scheduled backups of databases, files, and directories.


# Features
<h2>
 ☁️ Store backup files over Google Drive / AWS <br/>
 ✅ Database Backup [Sql/NoSQL] <br/>
 📂 Full Project Backup Or Specific Folders <br/>
 ⛔ Exclude Specific Folders / Files <br/>
 🎛 Detailed Configuration Easy to use <br/>
 🗄️ Up-to-date Log file with each process <br/>
 🚨 Alerts on Mail, Slack, and Telegram <br/>
</h2>
<br/>


# Flow Overview
![alt text](https://i.postimg.cc/zDPpHkPg/Backup-Sentry-Flow.jpg)

<br/>

# Installation

### Install the package
```
composer require isemary/backup-sentry
```
### Publish the configuration file
### For Laravel > 5.4
```php 
php artisan backup-sentry:publish
```
### For Laravel < 5.4
```php
php artisan vendor:publish --provider="iSemary\BackupSentry\BackupSentryServiceProvider"
```
<br/>

# Configuration

### It's important to make sure that config file exists in your root directory as <b>[config/BackupSentry.php]</b> 

## ./config/BackupSentry.php
```php
<?php

return [
    'backup' => [
        // The compressed file password, leave empty if the default value is same as env key : BACKUP_SENTRY_ZIP_PASSWORD
        'compressed_password' => '',
        // to backup the database tables
        'database' => [
            'allow' => true,
            'connection' => '',
            'host' => '',
            'port' => '',
            'name' => '',
            'username' => '',
            'password' => '',
        ],
        // upload backup file into cloud services 
        'cloud_services' => [
            'google_drive' => [
                'allow' => false,
                'folder_id' => '', // leave empty if the default value is same as env key : GOOGLE_DRIVE_BACKUP_FOLDER_ID
                'client_id' => '', // leave empty if the default value is same as env key : GOOGLE_DRIVE_CLIENT_ID
                'refresh_token' => '', // leave empty if the default value is same as env key : GOOGLE_DRIVE_REFRESH_TOKEN
                'client_secret' => '', // leave empty if the default value is same as env key : GOOGLE_DRIVE_CLIENT_SECRET
            ],
            'aws' => [
                'allow' => false,
                'access_key' => '', // leave empty if the default value is same as env key : AWS_ACCESS_KEY_ID
                'secret_key' => '', // leave empty if the default value is same as env key : AWS_SECRET_ACCESS_KEY
                'bucket_name' => '', // leave empty if the default value is same as env key : AWS_BUCKET
                'region' => '', // leave empty if the default value is same as env key : AWS_DEFAULT_REGION
            ]
        ],
        // Main email that will get emails about backup whatever it is success or failure 
        // [you could add cc on each type of emails in the options below]
        'mail' => [
            'allow' => false,
            'to' => ['to@example.com']
        ],
        // enable sending alert emails
        'email_alert' => false,
        'channels' => [
            // enable sending alert via slack channels
            'slack' => [
                'allow' => true,
                'webhook_url' => '' // you can create a new app on slack OR leave empty if the default value is same as env key : SLACK_WEBHOOK_URL
            ],
            // enable sending alert via telegram bots
            'telegram' => [
                'allow' => false,
                'bot_token' => '', // you can create a new bot on telegram OR leave empty if the default value is same as env key : TELEGRAM_BOT_TOKEN
                'chat_ids' => [] // array of chat ids of your users ** [Telegram not sending messages by phone number]
            ]
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
        // to exclude specific files/folders
        'excludes' => [
            '.git',
            'vendor',
            'node_modules',
        ],
        // that's mean the original back up folders will be kept in "/storage/backup-sentry/files/" [Which will be kept uncompressed]
        'keep_original_backup_folders' => true,
        // cleanup
        'cleanup' => false
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
```
<br/>

# Example on PHP Native
```php
<?php

require 'vendor/autoload.php';

use iSemary\BackupSentry\BackupSentry;

(new BackupSentry)->run();

?>
```
# Example on Laravel
```php
use iSemary\BackupSentry\BackupSentry;

Route::get('/backup', function() {
    (new BackupSentry)->run();
});
```
## 🔑 Default Environment Keys Required For :

### File Compress 

```
BACKUP_SENTRY_ZIP_PASSWORD=###
```
### Database Backup 

```
DB_CONNECTION=###
DB_HOST=###
DB_PORT=###
DB_DATABASE=###
DB_USERNAME=###
DB_PASSWORD=###
```

### Google Drive Backup  
```
GOOGLE_DRIVE_CLIENT_ID=###
GOOGLE_DRIVE_CLIENT_SECRET=###
GOOGLE_DRIVE_REFRESH_TOKEN=###
GOOGLE_DRIVE_BACKUP_FOLDER_ID=###
```
### AWS Backup  
```
AWS_ACCESS_KEY_ID=###
AWS_SECRET_ACCESS_KEY=###
AWS_DEFAULT_REGION=###
AWS_BUCKET=###
```
### Sending Email Alerts
```
MAIL_DRIVER=###
MAIL_HOST=###
MAIL_PORT=###
MAIL_USERNAME=###
MAIL_PASSWORD=###
MAIL_ENCRYPTION=###
MAIL_FROM_ADDRESS=###
MAIL_FROM_NAME=###
```
### Slack Alert
```
SLACK_WEBHOOK_URL=###
```
### Telegram Alert
```
TELEGRAM_BOT_TOKEN=###
```
## Contact

For any inquiries or support, please email me at [abdelrahmansamirmostafa@gmail.com](mailto:abdelrahmansamirmostafa@gmail.com) or visit my website at [abdelrahman.online](https://www.abdelrahman.online).