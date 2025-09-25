# 🚀 cPanel Deployment Checklist

Use this checklist to ensure successful deployment of your Pharmacy Management System to cPanel.

## Pre-Deployment ✅

- [ ] Code is committed to Git repository (GitHub/GitLab)
- [ ] `.cpanel.yml` file is present in repository root
- [ ] `.env.example` is updated with production settings
- [ ] All dependencies are listed in `composer.json`
- [ ] Database migrations are ready
- [ ] Admin user seeder is prepared

## cPanel Setup ✅

- [ ] **Database Created**
  - [ ] MySQL database created
  - [ ] Database user created and assigned
  - [ ] Database credentials noted down

- [ ] **Git Repository Connected**
  - [ ] Git Version Control accessed in cPanel
  - [ ] Repository URL added
  - [ ] Branch selected (main/master)
  - [ ] Initial deployment completed

## Post-Deployment Configuration ✅

- [ ] **Environment File**
  - [ ] `.env` file exists in public_html
  - [ ] Database credentials updated
  - [ ] APP_URL set to your domain
  - [ ] APP_ENV set to `production`
  - [ ] APP_DEBUG set to `false`
  - [ ] MAIL settings configured (if needed)

- [ ] **Composer Dependencies**
  - [ ] `vendor` folder exists
  - [ ] All required packages installed
  - [ ] Run `composer install --no-dev --optimize-autoloader` if needed

- [ ] **Laravel Configuration**
  - [ ] Application key generated: `php artisan key:generate --force`
  - [ ] Database migrated: `php artisan migrate --force`
  - [ ] Admin user created: `php artisan db:seed --class=UserSeeder`
  - [ ] Storage linked: `php artisan storage:link`

- [ ] **Caching (Production Optimization)**
  - [ ] Config cached: `php artisan config:cache`
  - [ ] Routes cached: `php artisan route:cache`
  - [ ] Views cached: `php artisan view:cache`

- [ ] **File Permissions**
  - [ ] Storage directory: `chmod -R 755 storage/`
  - [ ] Bootstrap cache: `chmod -R 755 bootstrap/cache/`
  - [ ] Environment file: `chmod 644 .env`

- [ ] **Document Root Setup**
  - [ ] Domain/subdomain points to `/public_html/public/` OR
  - [ ] Files moved from `/public_html/public/` to `/public_html/` with index.php updated

## Testing ✅

- [ ] **Health Check**
  - [ ] Access `https://yourdomain.com/health-check.php`
  - [ ] All checks pass (green ✅)
  - [ ] Delete health-check.php after verification

- [ ] **Application Access**
  - [ ] Main site loads without errors
  - [ ] Admin panel accessible: `/admin`
  - [ ] Login works with admin credentials
  - [ ] Dashboard displays correctly

- [ ] **Drug Management Features**
  - [ ] Drugs list page loads
  - [ ] Can create new drug
  - [ ] Excel template downloads: `/drugs/template`
  - [ ] Excel import works: test with sample data
  - [ ] Excel export works: download drugs data

- [ ] **Printing System**
  - [ ] Print settings accessible
  - [ ] Test invoice printing (if applicable)
  - [ ] Thermal/A4 print options work

## Security Verification ✅

- [ ] **File Access**
  - [ ] `.env` file not publicly accessible
  - [ ] `composer.json` not publicly accessible
  - [ ] Storage directory not publicly accessible
  - [ ] Bootstrap cache not publicly accessible

- [ ] **SSL Certificate**
  - [ ] SSL certificate installed and active
  - [ ] HTTPS redirect working
  - [ ] Mixed content warnings resolved

- [ ] **Application Security**
  - [ ] Strong admin password set
  - [ ] APP_DEBUG is false
  - [ ] Error reporting disabled for public
  - [ ] File upload validation working

## Performance Optimization ✅

- [ ] **PHP Settings**
  - [ ] OPcache enabled in cPanel
  - [ ] PHP version 8.2+ selected
  - [ ] Memory limit adequate (256MB+)
  - [ ] Max execution time sufficient (60s+)

- [ ] **Laravel Optimization**
  - [ ] All caches enabled (config, route, view)
  - [ ] Composer autoloader optimized
  - [ ] Unnecessary files removed (tests, docs)

## Backup & Maintenance ✅

- [ ] **Backup Strategy**
  - [ ] Database backup scheduled
  - [ ] File backup scheduled
  - [ ] Git repository is backup source

- [ ] **Monitoring**
  - [ ] Error logs location noted: `storage/logs/laravel.log`
  - [ ] cPanel error logs accessible
  - [ ] Disk space monitoring set up

## Final Steps ✅

- [ ] **Documentation**
  - [ ] Admin credentials documented securely
  - [ ] Database credentials documented
  - [ ] Deployment process documented
  - [ ] User guide provided to end users

- [ ] **Cleanup**
  - [ ] `health-check.php` deleted
  - [ ] Test files removed
  - [ ] Development tools disabled

- [ ] **User Training**
  - [ ] Admin user trained on system
  - [ ] Drug import/export process explained
  - [ ] Printing system demonstrated
  - [ ] Backup/maintenance procedures explained

## Emergency Contacts 📞

- **Hosting Provider:** ________________
- **Domain Registrar:** ________________
- **Developer:** ________________
- **Database Admin:** ________________

## Important URLs 🔗

- **Main Site:** https://yourdomain.com
- **Admin Panel:** https://yourdomain.com/admin
- **Drug Import:** https://yourdomain.com/drugs/import-page
- **Template Download:** https://yourdomain.com/drugs/template

---

**✅ Deployment Complete!** 

Your Pharmacy Management System is now live and ready for use. Remember to:
- Keep regular backups
- Monitor error logs
- Update dependencies periodically
- Test all features after any changes