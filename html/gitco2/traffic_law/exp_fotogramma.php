<?php
require_once('cost-sarida-gitco.php');

$perc = percorsoCorrente();

define ('PERCORSO_DOC', $perc);

//multe sviluppo
//define ('PERCORSO_DOC', '/var/www/doc-priv/sarida/gitco/');

//multe archivio prod
//define ('PERCORSO_DOC', '/home/sviluppo/aree_lavoro/sarida/doc-prod/');

//gitco prod
//define ('PERCORSO_DOC', '/var/www/archivio/');

//header('Content-Type: text/plain');
header('Content-Type: image/jpeg');

//percorso file deve partire da doc
$percorsoRelativoFile = $_GET["percorsoFile"];
$immagineInput = PERCORSO_DOC.$percorsoRelativoFile;
$testo = $_GET["testo"];

if(!file_exists($immagineInput)){
    if(PRODUCTION) {
        //se in produzione non ho trovato l'immagine vado a cercare l'exp_fotogramma su archivio prod
        header('Location: https://multe.ovunque-si.it/prod/archivio/exp_fotogramma.php?percorsoFile=$percorsoRelativoFile&testo="$testo"');
        exit;
    }
}


//echo "Immagine input: " . $immagineInput;
//echo "Etichetta: " . $testo;

$comando = "convert -set 'option:size' '%[fx:w*0.6]x%[fx:h*0.1]' -font helvetica +pointsize -gravity west $immagineInput -background '#000000' -fill white \"label:$testo\" -append -";
//echo "Eseguo il comando sopra: " . $comando;
passthru($comando);

function percorsoCorrente(){
    
    if(PRODUCTION) { //se sono in production il percorso doc può essere solo:
        $ris = '/var/www/archivio/';
    }
    else { //altrimenti (ovvero se sono su multe), differenzio tra archvio di produzione e sviluppo
        if(strpos($_SERVER["SCRIPT_FILENAME"], 'doc-prod'))
            $ris = '/home/sviluppo/aree_lavoro/sarida/doc-prod/';
         else
             $ris = '/var/www/doc-priv/sarida/gitco/';
    }
    return $ris;
}
