
<?php
// deploy-hook.php
if (!isset($_GET['secret']) || $_GET['secret'] !== env('SECRET_TOKEN', '8f3a7c2e9b4d6f1a5c8e3b7d2f9a4c6e1b5d8f3a7c2e9b4d6f1a5c8e3b7d2f9a')) {
    http_response_code(403);
    die('❌ Secret invalide');
}

echo "🚀 POST-DÉPLOIEMENT...\n";

try {
    // Clear & Recache
    shell_exec('php artisan config:clear');
    shell_exec('php artisan cache:clear');
    shell_exec('php artisan route:clear');
    shell_exec('php artisan view:clear');
    
    // Regenerate
    shell_exec('php artisan config:cache');
    shell_exec('php artisan route:cache');
    shell_exec('php artisan view:cache');
    
    echo "✅ Déploiement terminé !\n";
} catch (Exception $e) {
    echo "⚠️ Erreur : " . $e->getMessage() . "\n";
}
?>