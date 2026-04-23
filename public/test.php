<?php
echo "PCH Direct Access Works!<br>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "<br>";
echo ".htaccess readable: " . (is_readable(__DIR__ . '/.htaccess') ? 'YES' : 'NO') . "<br>";
echo ".htaccess perms: " . substr(sprintf('%o', fileperms(__DIR__ . '/.htaccess')), -4) . "<br>";
echo "index.php perms: " . substr(sprintf('%o', fileperms(__DIR__ . '/index.php')), -4) . "<br>";
echo "Dir perms: " . substr(sprintf('%o', fileperms(__DIR__)), -4) . "<br>";

// Check if mod_rewrite is working
echo "<br>Try accessing: <a href='/PalestineCreativeHub/en'>/PalestineCreativeHub/en</a><br>";
echo "If that 404s but this page works, .htaccess rewrite is not working.<br>";
