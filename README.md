# CA-bundle-WordPress
PHP skript pro automatickou opravu kořenového certifikátu WordPress


## Informace
WordPress používá vlastní soubor certifikátů (tzv. CA bundle), který najdete ve složce **/wp-includes/certificates**. Tento soubor obsahuje důvěryhodné kořenové certifikáty, které slouží k ověřování SSL spojení.

Problém byl v tom, že verze CA bundle, která byla dodávána se staršími verzemi WordPressu, byla zastaralá. To může způsobit potíže při ověřování SSL spojení.

Tento PHP skript zajistí stažení aktuálního kořenového certifikátu a umístění do správné složky. Por jistotu zajistí zazálohování původního certifikátu. 

## Instalace
Soubor **update-ca-bundle.php** umístíte do kořenového adresáře vaši instalace WordPress, například */www/domains/domena.tld* a poté otevřete URL `https://domena.tld/update-ca-bundle.php` v přohlížeči. Skript stáhne certifikát, zazálohuje původní (přejmenuje jej) a uloží certifiká do adresáře */wp-includes/certificates*. 

## Doporučení

Důrazně doporučuji po upgrade certifikátu ihned tento skript smazat z vašeho FTP! 
