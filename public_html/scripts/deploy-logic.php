<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$env = parse_ini_file(__DIR__ . '/../../.env');
$secret = $env['DEPLOY_SECRET'] ?? '';

$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');

if (hash_equals($signature, 'sha256=' . hash_hmac('sha256', $payload, $secret))) {

    $repo    = "/home/fezadano5/repo_yedek";
    $home    = "/home/fezadano5";
    $webRoot = "/home/fezadano5/public_html";

    echo "--- ZORUNLU DEPLOY BAŞLADI ---\n";

    // 1. ADIM: REPO GÜNCELLEME
    $git_cmd    = "cd $repo && git fetch --all && git reset --hard origin/main 2>&1";
    $git_output = shell_exec($git_cmd);
    echo "Git Durumu: " . $git_output . "\n";

    // 2. ADIM: DOSYA TRANSFERİ

    // A. Sistem dosyaları (app klasörü içeriğini kopyala)
    shell_exec("cp -Rf $repo/app/* $home/app/ 2>&1");
    echo "Sistem (app) klasörü içeriği güncellendi.\n";

    // B. Cron klasörü (generate-sitemap.php vb.)
    if (is_dir("$repo/cron")) {
        if (!is_dir("$home/cron")) {
            mkdir("$home/cron", 0755, true);
        }
        shell_exec("cp -Rf $repo/cron/* $home/cron/ 2>&1");
        echo "Cron klasörü güncellendi.\n";
    }

    // C. Web Varlıkları (Assets, CDN, INC)
    shell_exec("cp -Rf $repo/public_html/assets/* $webRoot/assets/ 2>&1");
    shell_exec("cp -Rf $repo/public_html/cdn/* $webRoot/cdn/ 2>&1");

    if (is_dir("$repo/public_html/inc")) {
        shell_exec("cp -Rf $repo/public_html/inc/* $webRoot/inc/ 2>&1");
    }
    echo "Web varlıkları (assets, cdn, inc) güncellendi.\n";

    // D. Ana dizin PHP, .htaccess ve .txt dosyaları
    shell_exec("find $repo/public_html/ -maxdepth 1 -name '*.php' ! -name 'config.php' -exec cp -f {} $webRoot/ \\;");
    shell_exec("cp -f $repo/public_html/.htaccess $webRoot/ 2>&1");
    shell_exec("find $repo/public_html/ -maxdepth 1 -name '*.txt' -exec cp -f {} $webRoot/ \\;");
    echo "PHP / .htaccess / .txt dosyaları güncellendi.\n";

    // E. Composer (composer.json/lock değiştiyse opsiyonel; autoload her zaman optimize)
    if (file_exists("$repo/composer.json")) {
        copy("$repo/composer.json", "$home/composer.json");
        if (file_exists("$repo/composer.lock")) {
            copy("$repo/composer.lock", "$home/composer.lock");
        }
        // Yeni bağımlılık veya class eklenmiş olabilir → autoload yenile
        $composerOut = shell_exec("cd $home && composer dump-autoload --optimize --classmap-authoritative --no-dev 2>&1");
        echo "Composer autoload: " . trim($composerOut) . "\n";
    }

    echo "\n--- DEPLOY TAMAMLANDI! ---\n";

} else {
    die('Geçersiz imza.');
}
