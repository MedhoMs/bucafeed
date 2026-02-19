<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Servidor PHP</h1>";
echo "<pre>";
echo "User: " . exec('whoami') . "\n";
echo "Group: " . exec('id -gn') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "\n--- FILE CHECKS ---\n";

function check($path) {
    if (file_exists($path)) {
        echo "[OK] Found: $path\n";
        echo "     Writable: " . (is_writable($path) ? 'YES' : 'NO') . "\n";
        echo "     Permissions: " . substr(sprintf('%o', fileperms($path)), -4) . "\n";
    } else {
        echo "[ERROR] Not Found: $path\n";
    }
}

check(__DIR__ . '/../vendor/autoload.php');
check(__DIR__ . '/../.env');
check(__DIR__ . '/../bootstrap/app.php');
check(__DIR__ . '/../storage');
check(__DIR__ . '/../storage/logs');
check(__DIR__ . '/../storage/framework');
check(__DIR__ . '/../storage/framework/views');
check(__DIR__ . '/../bootstrap/cache');

echo "\n--- BOOTSTRAP ATTEMPT ---\n";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "[OK] Composer Autoload loaded\n";
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "[OK] Laravel App instance created\n";
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "[OK] HTTP Kernel resolved\n";

} catch (\Throwable $e) {
    echo "[CRITICAL ERROR] Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
