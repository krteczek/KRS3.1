<?php
/**
 * Instalační skript pro databázi KRS3 systému
 *
 * Tento skript automaticky vytvoří databázi, naimportuje schéma
 * a výchozí data potřebná pro fungování redakčního systému.
 * Používá se pro počáteční nastavení aplikace.
 *
 * @package Install
 * @author KRS3
 * @version 3.0
 */

// Vypnout zobrazení chyb (pro production)
ini_set('display_errors', '0');

echo "🔄 Zakládám databázi...\n";

// Načti konfiguraci ručně (bez autoloadu)
$config = require __DIR__ . '/../config/config.php';
$dbConfig = $config['database'];

try {
    // Připojení k MySQL (bez výběru databáze)
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']}",
        $dbConfig['user'],
        $dbConfig['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Vytvoření databáze
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbConfig['name']} CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci");
    echo "✅ Databáze '{$dbConfig['name']}' vytvořena\n";

    // Použití databáze
    $pdo->exec("USE {$dbConfig['name']}");
    echo "✅ Přepnuto do databáze\n";

    // Načtení SQL souboru po řádcích (kvůli MariaDB)
    $sqlFile = __DIR__ . '/database-schema.sql';
    $sql = file_get_contents($sqlFile);

    // Rozdělení na jednotlivé příkazy
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($queries as $query) {
        if (!empty($query)) {
            print_r($query);
            $pdo->exec($query);
        }
    }

    echo "✅ Tabulky a data úspěšně importovány\n";
    echo "🎉 Instalace dokončena! Databáze je připravena.\n";
    echo "📊 Počet uživatelů: 3 (admin, editor, author)\n";
    echo "📝 Počet článků: 2\n";
    echo "📂 Počet kategorií: 3\n";

} catch (PDOException $e) {
    echo "❌ Chyba databáze: " . $e->getMessage() . "\n";
    echo "📋 Error code: " . $e->getCode() . "\n";
}