
# 🛡️ BackupSentry [Backup today, relax tomorrow]

BackupSentry is a PHP backup package designed to provide a secure and seamless solution for protecting and backup your critical data. With its advanced features and intuitive interface, Effortlessly create scheduled backups of databases, files, and directories.


# Features
### ✅ Store backup files over Google Drive / AWS
### ✅ Database Backup
### ✅ Full Project Backup Or Specific Folders
### ✅ Exclude Specific Folders / Files
### ✅ Detailed Configuration Easy to use

<hr/>

### 🔑 Environment Keys Required For File Compress 

```
BACKUP_SENTRY_ZIP_PASSWORD=###
```
### 🔑 Environment Keys Required For Database Backup 

```
DB_CONNECTION=###
DB_HOST=###
DB_PORT=###
DB_DATABASE=###
DB_USERNAME=###
DB_PASSWORD=###
```

### 🔑 Environment Keys Required For Google Drive Backup  
```
GOOGLE_BACKUP_FOLDER_ID=###
GOOGLE_DRIVE_CLIENT_ID=###
GOOGLE_DRIVE_CLIENT_SECRET=###
GOOGLE_DRIVE_REFRESH_TOKEN=###
```
### 🔑 Environment Keys Required For AWS Backup  
```
AWS_ACCESS_KEY_ID=###
AWS_SECRET_ACCESS_KEY=###
AWS_DEFAULT_REGION=###
AWS_BUCKET=###
AWS_USE_PATH_STYLE_ENDPOINT=###
```
### 🔑 Environment Keys Required For Sending Email Alerts
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