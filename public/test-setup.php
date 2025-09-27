<?php
echo "<h1>KIIT SEVA - Setup Test</h1>";

// Test PHP Version
echo "<h2>PHP Version: " . phpversion() . "</h2>";

// Test Extensions
$extensions = ['pdo', 'pdo_mysql'];
foreach ($extensions as $ext) {
    echo extension_loaded($ext) ? "✅ {$ext}<br>" : "❌ {$ext}<br>";
}

// Test Files
$files = [
    '../app/config/database.php',
    '../database/schema.sql',
    'index.php'
];

foreach ($files as $file) {
    echo file_exists($file) ? "✅ {$file}<br>" : "❌ {$file}<br>";
}

echo "<p><a href='index.php'>Go to Homepage</a></p>";
?>