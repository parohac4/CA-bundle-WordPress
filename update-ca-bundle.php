<?php
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <title>Aktualizace certifikátu</title>
  <style>
    body {
      background-color: white;
      color: black;
      font-size: 20px;
      font-family: Arial, sans-serif;
      margin: 20px;
      line-height: 1.6;
    }
    .message {
      margin-bottom: 20px;
    }
    .error {
      color: red;
    }
  </style>
</head>
<body>
';

// Funkce pro výpis zprávy do HTML
function printMessage($message, $isError = false) {
    $class = $isError ? "message error" : "message";
    echo "<div class='$class'>$message</div>\n";
    flush();
    sleep(1); // Kratší pauza pro přehlednější postup
}

// Funkce pro stažení obsahu z dané URL. Nejprve se použije file_get_contents, pokud selže, použije cURL.
function fetch_remote_file($url) {
    $data = @file_get_contents($url);
    if ($data === false) {
        if (function_exists('curl_version')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Script for updating WP CA bundle');
            $data = curl_exec($ch);
            if (curl_errno($ch)) {
                printMessage('Curl error: ' . curl_error($ch), true);
                curl_close($ch);
                return false;
            }
            curl_close($ch);
        } else {
            return false;
        }
    }
    return $data;
}

// URL pro stažení nového souboru s certifikátem z GitHubu
$url = 'https://raw.githubusercontent.com/WordPress/WordPress/master/wp-includes/certificates/ca-bundle.crt';

// Cesty – skript předpokládá, že je spuštěn z kořenového adresáře WordPressu
$localDir  = __DIR__ . '/wp-includes/certificates';
$localFile = $localDir . '/ca-bundle.crt';

// Kontrola existence cílového adresáře
if (!is_dir($localDir)) {
    printMessage("Adresář '$localDir' neexistuje. Spusťte skript z kořenového adresáře WordPressu.", true);
    echo '</body></html>';
    exit;
}

printMessage("Stahuji nový soubor s certifikátem z URL: $url ...");
$bundleContent = fetch_remote_file($url);
if ($bundleContent === false) {
    printMessage("Nepodařilo se stáhnout soubor s certifikátem.", true);
    echo '</body></html>';
    exit;
}

// Ověření platnosti souboru (kontrola řetězce "BEGIN CERTIFICATE")
if (strpos($bundleContent, 'BEGIN CERTIFICATE') === false) {
    printMessage("Stažený soubor nevypadá jako platný certifikát.", true);
    echo '</body></html>';
    exit;
}

// Pokud již původní soubor existuje, vytvoříme jeho zálohu
if (file_exists($localFile)) {
    $backupFile = $localFile . '.backup_' . date('YmdHis');
    if (copy($localFile, $backupFile)) {
        printMessage("Původní soubor byl zálohován do: $backupFile");
    } else {
        printMessage("Nepodařilo se vytvořit zálohu původního souboru.", true);
    }
}

// Uložení nového souboru s certifikátem
if (file_put_contents($localFile, $bundleContent) === false) {
    printMessage("Nepodařilo se uložit nový soubor s certifikátem do: $localFile", true);
    echo '</body></html>';
    exit;
}

printMessage("Nový soubor s certifikátem byl stažen a uložen do: $localFile");

echo '</body></html>';
?>