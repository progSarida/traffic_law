<?php
define("DB_HOST", 'localhost');
define("DB_USERNAME", 'sarida');
define("DB_PASSWORD", "fcnmc2eZv[<7#X7");

define("CERT_PATH","/var/www/config/");

define("PRODUCTION",true);
//attenzione! se non è impostato a vero alcuni pezzi non funzionano (16 nov 2020)
define("DEBUG",true);

define("PERCORSO_INST", "/var/www/html");
define("CONTESTO_URL", "");
define("PAGOPA_EXE_URL","https://nodopagamenti.regione.liguria.it/portale/nodopagamenti/rest/pagamentodiretto/inviarichiesta");
define("FTP_PASSIVE_MODE_MCTC",true); //messo a true il 16-06-2023 dopo ripristino server Gitco.
define('CHILKAT_CODICE_LICENZA', 'OVUNQU.CB1082022_2YL7hddkj374');

define("ARCHIVIO", "/var/www/archivio");

define("CARTELLA_DA_IMPORTARE", ARCHIVIO."/public/DA_IMPORTARE");
define("CARTELLA_TMP", ARCHIVIO."/public/TMP");
?>
