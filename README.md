
# 🛡️ BackupSentry [Backup today, relax tomorrow]

BackupSentry is a PHP backup package designed to provide a secure and seamless solution for protecting and backup your critical data. With its advanced features and intuitive interface, Effortlessly create scheduled backups of databases, files, and directories.


# Features
### ✅ Store backup files over Google Drive / AWS
### ✅ Database Backup
### ✅ Full Project Backup Or Specific Folders
### ✅ Exclude Specific Folders / Files
### ✅ Detailed Configuration Easy to use
<br/>


# Flow Overview
![alt text](https://i.postimg.cc/zDPpHkPg/Backup-Sentry-Flow.jpg)

<br/>

# Installation

```
composer require isemary/backup-sentry
```

```
php artisan backup-sentry:publish
```
<br/>

# Configuration



<br/>

# Example ~ PHP Native
```php
<?php

require 'vendor/autoload.php';

use iSemary\BackupSentry\BackupSentry;

(new BackupSentry)->run();

?>
```
# Example ~ Laravel
```php
use iSemary\BackupSentry\BackupSentry;

Route::get('/', function() {
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