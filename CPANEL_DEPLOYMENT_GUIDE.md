# cPanel Deployment Guide

This guide will help you deploy your Laravel pharmacy management system to cPanel hosting using Git.

## Prerequisites

- cPanel hosting account with Git support
- PHP 8.2 or higher
- MySQL database
- Composer access (or ability to upload vendor folder)

## Step 1: Prepare Your Repository

1. Ensure all your code is committed to a Git repository (GitHub, GitLab, etc.)
2. The `.cpanel.yml` file is already included in your project
3. Make sure your `.env.example` file contains all necessary environment variables

## Step 2: Set Up Database

1. **Create MySQL Database:**
   - Log into cPanel
   - Go to "MySQL Databases"
   - Create a new database (e.g., `pharmacy_db`)
   - Create a database user and assign to the database
   - Note down: database name, username, password, and host

## Step 3: Configure Git Deployment

1. **Access Git Version Control:**
   - In cPanel, find "Git Version Control"
   - Click "Create" to add a new repository

2. **Repository Settings:**
   - **Repository URL:** Your Git repository URL
   - **Repository Path:** `/public_html` (or subdirectory if needed)
   - **Branch:** `main` or `master`
   - Click "Create"

3. **Deploy:**
   - After creation, click "Manage" on your repository
   - Click "Pull or Deploy" to deploy your code
   - The `.cpanel.yml` file will automatically run deployment tasks

## Step 4: Manual Configuration (After Deployment)

### 4.1 Environment Configuration

Edit the `.env` file in your public_html directory:

```env
APP_NAME="Pharmacy Management System"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Email Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Session and Cache
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

# File Storage
FILESYSTEM_DISK=local
```

### 4.2 Install Composer Dependencies (if not automated)

If Composer didn't run automatically:

```bash
# Via SSH (if available)
cd public_html
composer install --no-dev --optimize-autoloader

# OR upload vendor folder manually
# Download dependencies locally and upload the vendor folder via File Manager
```

### 4.3 Run Artisan Commands

```bash
# Generate application key
php artisan key:generate --force

# Run database migrations
php artisan migrate --force

# Seed initial data (optional)
php artisan db:seed --class=UserSeeder

# Clear and cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink
php artisan storage:link
```

## Step 5: Set Up Document Root (Important!)

### Option A: Subdomain/Domain Setup
1. In cPanel, go to "Subdomains" or "Addon Domains"
2. Set the document root to `/public_html/public` (not just `/public_html`)

### Option B: Main Domain Setup
If using your main domain:
1. Move contents of `/public_html/public/*` to `/public_html/`
2. Update `/public_html/index.php`:
   ```php
   require __DIR__.'/bootstrap/app.php';
   ```
   Change to:
   ```php
   require __DIR__.'/../bootstrap/app.php';
   ```

## Step 6: File Permissions

Ensure proper permissions are set:

```bash
# Storage and cache directories
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Make sure .env is not publicly accessible
chmod 644 .env
```

## Step 7: Create Admin User

Access your site and run this via Tinker or create a seeder:

```bash
php artisan tinker
```

```php
use App\Models\User;
User::create([
    'name' => 'Admin',
    'email' => 'admin@yourdomain.com',
    'password' => bcrypt('your-secure-password'),
    'email_verified_at' => now(),
]);
```

## Step 8: Test Your Application

1. Visit your domain
2. You should see the Filament login page
3. Log in with your admin credentials
4. Test the drug import/export functionality

## Troubleshooting

### Common Issues:

1. **500 Internal Server Error:**
   - Check file permissions
   - Verify .env configuration
   - Check error logs in cPanel

2. **Database Connection Error:**
   - Verify database credentials in .env
   - Ensure database user has proper permissions

3. **Composer Dependencies Missing:**
   - Upload vendor folder manually
   - Or run composer install via SSH

4. **Storage/Cache Permissions:**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 bootstrap/cache/
   ```

5. **App Key Not Set:**
   ```bash
   php artisan key:generate --force
   ```

### Log Files:
- Laravel logs: `storage/logs/laravel.log`
- cPanel error logs: Available in cPanel Error Logs section

## Security Considerations

1. **Environment File:** Ensure `.env` is not publicly accessible
2. **Debug Mode:** Set `APP_DEBUG=false` in production
3. **HTTPS:** Enable SSL certificate in cPanel
4. **Database:** Use strong passwords and limit user permissions
5. **File Uploads:** The system validates Excel file uploads for security

## Updating Your Application

To update your application:

1. Push changes to your Git repository
2. In cPanel Git Version Control, click "Pull or Deploy"
3. The `.cpanel.yml` will handle the update process
4. Run any new migrations if needed:
   ```bash
   php artisan migrate --force
   ```

## Performance Optimization

For better performance:

1. **Enable OPcache** in cPanel PHP settings
2. **Use production caching:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
3. **Optimize Composer autoloader:**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

## Support

If you encounter issues:
1. Check the Laravel logs in `storage/logs/`
2. Verify all environment variables are set correctly
3. Ensure proper file permissions
4. Contact your hosting provider for server-specific issues

Your pharmacy management system should now be successfully deployed and running on cPanel!