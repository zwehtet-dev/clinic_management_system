<?php
/**
 * Simple health check script for cPanel deployment
 * Access this file directly to check if your Laravel app is properly configured
 * URL: https://yourdomain.com/health-check.php
 */

// Basic PHP version check
echo "<h2>🏥 Pharmacy System Health Check</h2>";
echo "<hr>";

// PHP Version
echo "<h3>✅ PHP Version</h3>";
echo "Current PHP Version: " . PHP_VERSION . "<br>";
echo "Required: PHP 8.2+<br>";
if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
    echo "<span style='color: green;'>✅ PHP version is compatible</span><br>";
} else {
    echo "<span style='color: red;'>❌ PHP version is too old</span><br>";
}

echo "<hr>";

// Check if Laravel is accessible
echo "<h3>🚀 Laravel Application</h3>";
try {
    // Try to include Laravel bootstrap
    if (file_exists(__DIR__ . '/bootstrap/app.php')) {
        echo "✅ Laravel bootstrap file found<br>";
        
        // Check if we can create Laravel app instance
        $app = require_once __DIR__ . '/bootstrap/app.php';
        echo "✅ Laravel application instance created<br>";
        
        // Check if .env exists
        if (file_exists(__DIR__ . '/.env')) {
            echo "✅ Environment file (.env) exists<br>";
        } else {
            echo "❌ Environment file (.env) missing<br>";
        }
        
    } else {
        echo "❌ Laravel bootstrap file not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Laravel Error: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Check required directories and permissions
echo "<h3>📁 Directory Permissions</h3>";
$directories = [
    'storage' => 'Storage directory',
    'storage/logs' => 'Logs directory',
    'storage/framework' => 'Framework cache directory',
    'bootstrap/cache' => 'Bootstrap cache directory',
    'public' => 'Public directory'
];

foreach ($directories as $dir => $description) {
    if (is_dir(__DIR__ . '/' . $dir)) {
        $perms = substr(sprintf('%o', fileperms(__DIR__ . '/' . $dir)), -4);
        if (is_writable(__DIR__ . '/' . $dir)) {
            echo "✅ {$description}: exists and writable (permissions: {$perms})<br>";
        } else {
            echo "⚠️ {$description}: exists but not writable (permissions: {$perms})<br>";
        }
    } else {
        echo "❌ {$description}: does not exist<br>";
    }
}

echo "<hr>";

// Check required PHP extensions
echo "<h3>🔧 PHP Extensions</h3>";
$required_extensions = [
    'pdo' => 'PDO (Database)',
    'pdo_mysql' => 'PDO MySQL',
    'mbstring' => 'Multibyte String',
    'openssl' => 'OpenSSL',
    'tokenizer' => 'Tokenizer',
    'xml' => 'XML',
    'ctype' => 'Character Type',
    'json' => 'JSON',
    'bcmath' => 'BC Math',
    'fileinfo' => 'File Info',
    'zip' => 'ZIP (for Excel operations)'
];

foreach ($required_extensions as $ext => $description) {
    if (extension_loaded($ext)) {
        echo "✅ {$description}: loaded<br>";
    } else {
        echo "❌ {$description}: missing<br>";
    }
}

echo "<hr>";

// Check Composer dependencies
echo "<h3>📦 Composer Dependencies</h3>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "✅ Composer dependencies installed<br>";
    
    // Check specific packages
    $packages = [
        'filament/filament' => 'Filament Admin Panel',
        'maatwebsite/excel' => 'Excel Import/Export',
        'barryvdh/laravel-dompdf' => 'PDF Generation'
    ];
    
    foreach ($packages as $package => $description) {
        $composerLock = __DIR__ . '/composer.lock';
        if (file_exists($composerLock)) {
            $lockContent = file_get_contents($composerLock);
            if (strpos($lockContent, $package) !== false) {
                echo "✅ {$description}: installed<br>";
            } else {
                echo "⚠️ {$description}: not found in composer.lock<br>";
            }
        }
    }
} else {
    echo "❌ Composer dependencies not installed (vendor/autoload.php missing)<br>";
    echo "Run: composer install --no-dev --optimize-autoloader<br>";
}

echo "<hr>";

// Database connection test (if .env exists)
echo "<h3>🗄️ Database Connection</h3>";
if (file_exists(__DIR__ . '/.env')) {
    $env = file_get_contents(__DIR__ . '/.env');
    preg_match('/DB_HOST=(.*)/', $env, $host_matches);
    preg_match('/DB_DATABASE=(.*)/', $env, $db_matches);
    preg_match('/DB_USERNAME=(.*)/', $env, $user_matches);
    
    if (!empty($host_matches[1]) && !empty($db_matches[1]) && !empty($user_matches[1])) {
        echo "Database configuration found in .env<br>";
        echo "Host: " . trim($host_matches[1]) . "<br>";
        echo "Database: " . trim($db_matches[1]) . "<br>";
        echo "Username: " . trim($user_matches[1]) . "<br>";
        echo "<span style='color: orange;'>⚠️ Database connection test requires Laravel bootstrap</span><br>";
    } else {
        echo "❌ Database configuration incomplete in .env<br>";
    }
} else {
    echo "❌ Cannot test database connection (.env file missing)<br>";
}

echo "<hr>";

// Final recommendations
echo "<h3>📋 Next Steps</h3>";
echo "<ol>";
echo "<li>If all checks pass, try accessing your main application</li>";
echo "<li>If Laravel bootstrap fails, run: <code>php artisan key:generate</code></li>";
echo "<li>If database connection fails, verify your .env database settings</li>";
echo "<li>If permissions are wrong, run: <code>chmod -R 755 storage bootstrap/cache</code></li>";
echo "<li>Delete this health-check.php file after successful deployment</li>";
echo "</ol>";

echo "<hr>";
echo "<p><strong>🔗 Quick Links:</strong></p>";
echo "<ul>";
echo "<li><a href='/admin'>Admin Panel (Filament)</a></li>";
echo "<li><a href='/drugs/import-page'>Drug Import Page</a></li>";
echo "<li><a href='/drugs/template'>Download Drug Template</a></li>";
echo "</ul>";

echo "<hr>";
echo "<p><em>Generated at: " . date('Y-m-d H:i:s') . "</em></p>";
echo "<p><em>🚨 Remember to delete this file after deployment verification!</em></p>";
?>