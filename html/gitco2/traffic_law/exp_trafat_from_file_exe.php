<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$path = PUBLIC_FOLDER . "/_TMP_EXP/";
$str_FileName = basename($_FILES["fileToUpload"]["name"]);
$target_file = $path . basename($_FILES["fileToUpload"]["name"]);

$b_UploadFile = true;
$str_FileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));









$a_PaymentType = array();
$a_PaymentType[16] = "6805511";
$a_PaymentType[66] = "6805511";
$a_PaymentType[19] = "8405005";


$flt_Tot = 0.00;

// Check if file already exists
if (file_exists($target_file)) {
    $str_out .= "File con stesso nome presente";
    $b_UploadFile = false;
}

// Allow certain file formats
if ($str_FileType != "csv") {
    $str_out .= "Caricare solo file csv.";
    $b_UploadFile = 0;
}
if ($b_UploadFile == 0) {
    echo $str_out;
} else {
    if (!move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $str_out .= "Problemi nel caricamento file";
    } else {

        $file_r = fopen($path . $str_FileName, "r");
        $delimiter = ";";

        $str_file = "trafat_output.txt";
        $str_zipfile = "trafat_output.zip";

        $file_w = fopen($path . $str_file, "w");

        while (!feof($file_r)) {
            $row = fgetcsv($file_r, 1000, $delimiter);

            if (isset($row[0]) && strpos($row[0], 'IBAN') !== false) {
                $BankAccount = substr($row[1], 15);
                $pos_StartNumber = 0;
                for ($i = 0; $i < strlen($BankAccount); $i++) {

                    if ((substr($BankAccount, $i, 1)) == "0") $pos_StartNumber = $i;
                    else break;

                }
                if($pos_StartNumber>0)$pos_StartNumber++;
                $BankAccount = substr($BankAccount, $pos_StartNumber);
            }


            if (isset($row[0]) && strpos($row[0], '/') !== false) {
                if (isset($a_PaymentType[$row[2]])) {
                    $str_Record = '';

                    $str_Record .= '00080';   //Codice della ditta che deve essere lungo 5 bit

                    $str_Record .= '1';       //Versione del programma che deve essere lungo 1 bit

                    $str_Record .= '0';       //Tipo di archivio che deve essere lungo 1 bit

                    for ($i = 0; $i <= 61; $i++) {
                        // 32 spazi vuoti per la ragione sociale
                        // 30 spazi vuoti per l'indirizzo
                        $str_Record .= ' ';
                    }

                    // Cap del contribuente
                    $str_Record .= '00000';

                    for ($i = 0; $i <= 42; $i++) {
                        // 25 spazi per la citta
                        // 2 spazi per la provincia
                        // 16 spazi per il codice fiscale
                        $str_Record .= ' ';
                    }
                    //Partita Iva del contribuente
                    $str_Record .= '00000000000';

                    //Persona Fisica
                    $str_Record .= ' ';
                    //Posizione spazio fra Cognome e Nome
                    $str_Record .= '00';

                    //Causale
                    $str_Record .= '027';

                    //Descrizione della causale 15 caratteri
                    $Description = substr($row[3], 0, 15);
                    $str_Record .= $Description;


                    for ($i = 0; $i < (15 - strlen($Description)); $i++) {
                        $str_Record .= ' ';
                    }


                    //Fine prima parte record e aggiunta del nome del comune e della data del pagamento



                    $str_Record .= $BankAccount;

                    for ($i = 0; $i <= (17 - strlen($BankAccount)); $i++) {
                        $str_Record .= ' ';
                    }

                    $PaymentDate = $row[0];
                    $str_data = substr($PaymentDate, 0, 2) . substr($PaymentDate, 3, 2) . substr($PaymentDate, 8, 2);


                    $str_Record .= $str_data;

                    //Inizio seconda parte del record
                    //Scrivo 5 zeri per numero documento, 2 per il sezionale,6 per l'estratto conto numero della partita
                    //2 per l'anno della partita
                    for ($i = 0; $i <= 14; $i++) {
                        $str_Record .= '0';
                    }

                    //Ciclo di 8 volte dei campi imponibile (11),prima metto un + aliquota (3), iva11 (1),imposta (10) finisco con un +
                    for ($j = 0; $j <= 7; $j++) {
                        for ($i = 0; $i <= 10; $i++) {
                            $str_Record .= '0';
                        }
                        $str_Record .= '+';

                        for ($i = 0; $i <= 13; $i++) {
                            $str_Record .= '0';
                        }
                        $str_Record .= '+';
                    }

                    //Totale fattura (11) seguito da +
                    for ($i = 0; $i <= 10; $i++) {
                        $str_Record .= '0';
                    }
                    $str_Record .= '+';

                    //Ciclo di 8 volte  sul conto di ricavo (7) e l'importo di ricavo (11) seguito da un +
                    for ($j = 0; $j <= 7; $j++) {
                        for ($i = 0; $i <= 17; $i++) {
                            $str_Record .= '0';
                        }
                        $str_Record .= '+';
                    }
                    //Fine seconda parte del record.
                    //Selezione del tipo di conto dare a seconda del tipo di pagamento selezionato


                    $str_Record .= $a_PaymentType[$row[2]];

                    //Scrittura del conto che occupa 7 bit seguito da D che sta per Dare
                    $str_Record .= 'D';

                    //Totale dei versamenti per data e comune seguito da un +


                    $str_Amount = (float)str_replace(",", ".", trim($row[5]));

                    if (strpos($str_Amount, '.') !== false) {
                        if(strlen(substr($str_Amount,strpos($str_Amount, '.')+1))==1){
                            $str_Amount .= "0";
                        }
                    } else {
                        $str_Amount .= ".00";
                    }


                    $flt_Tot += $row[5];

                    $str_Zero = '';
                    for ($i = 0; $i <= (10 - strlen($str_Amount)); $i++) {
                        $str_Zero .= '0';
                    }
                    $str_Record .= $str_Zero . $str_Amount;
                    $str_Record .= '+';


                    //Pagamento parziale costituito da 18 spazi vuoti (per la causale del pagamento), 6 zeri par il numero
                    //della partita del pagamento, 2 zeri per l'anno della partita
                    for ($i = 0; $i <= 17; $i++) {
                        $str_Record .= ' ';
                    }

                    for ($i = 0; $i <= 7; $i++) {
                        $str_Record .= '0';
                    }


                    //Numero del conto avere lungo 7 bit seguito da A che sta per avere
                    $str_Record .= "2405065A";


                    //Totale dei pagamenti per comune e data che deve occupare 11 bit seguito da +
                    $str_Amount = (float)str_replace(",", ".", trim($row[5]));

                    if (strpos($str_Amount, '.') !== false) {
                        if(strlen(substr($str_Amount,strpos($str_Amount, '.')+1))==1){
                            $str_Amount .= "0";
                        }
                    } else {
                        $str_Amount .= ".00";
                    }
                    $str_Zero = '';
                    for ($i = 0; $i <= (10 - strlen($str_Amount)); $i++) {
                        $str_Zero .= '0';
                    }


                    $str_Record .= $str_Zero . $str_Amount;
                    $str_Record .= '+';


                    //Pagamento parziale costituito da 18 spazi vuoti (per la causale del pagamento), 6 zeri par il numero
                    //della partita del pagamento, 2 zeri per l'anno della partita
                    for ($i = 0; $i <= 17; $i++) {
                        $str_Record .= ' ';
                    }

                    for ($i = 0; $i <= 7; $i++) {
                        $str_Record .= '0';
                    }


                    //Ciclo di 78 volte sul pagamento vuoto [costituito da 7 zeri per il conto, seguiti da uno spazio vuoto per D (dare)
                    //o A (Avere), e da 11 zeri per l'importo e da un +] e sul pagamento parziale.
                    for ($j = 0; $j <= 77; $j++) {
                        //Pagamento vuoto
                        for ($i = 0; $i <= 6; $i++) {
                            $str_Record .= '0';
                        }
                        $str_Record .= ' ';
                        for ($i = 0; $i <= 10; $i++) {
                            $str_Record .= '0';
                        }
                        $str_Record .= '+';

                        //Pagamento prziale costituito da 18 spazi vuoti (per la causale del pagamento), 6 zeri par il numero
                        //della partita del pagamento, 2 zeri per l'anno della partita
                        for ($i = 0; $i <= 17; $i++) {
                            $str_Record .= ' ';
                        }
                        for ($i = 0; $i <= 7; $i++) {
                            $str_Record .= '0';
                        }
                    }
                    fwrite($file_w, $str_Record);
                }


            }
        }
        /*
        $str_Record = '';

        $str_Record .= '00080';   //Codice della ditta che deve essere lungo 5 bit

        $str_Record .= '1';       //Versione del programma che deve essere lungo 1 bit

        $str_Record .= '0';       //Tipo di archivio che deve essere lungo 1 bit

        for ($i = 0; $i <= 61; $i++) {
            // 32 spazi vuoti per la ragione sociale
            // 30 spazi vuoti per l'indirizzo
            $str_Record .= ' ';
        }

        // Cap del contribuente
        $str_Record .= '00000';

        for ($i = 0; $i <= 42; $i++) {
            // 25 spazi per la citta
            // 2 spazi per la provincia
            // 16 spazi per il codice fiscale
            $str_Record .= ' ';
        }
        //Partita Iva del contribuente
        $str_Record .= '00000000000';

        //Persona Fisica
        $str_Record .= ' ';
        //Posizione spazio fra Cognome e Nome
        $str_Record .= '00';

        //Causale
        $str_Record .= '027';

        //Descrizione della causale 15 caratteri
        $str_Record .= $BankAccount;


        for ($i = 0; $i < (15 - strlen($BankAccount)); $i++) {
            $str_Record .= ' ';
        }




        //Fine prima parte record e aggiunta del nome del comune e della data del pagamento


        $ManagerName = substr($_SESSION['citytitle'], 0, 18);

        $str_Record .= $ManagerName;

        for ($i = 0; $i <= (17 - strlen($ManagerName)); $i++) {
            $str_Record .= ' ';
        }

        $PaymentDate = $row[0];
        $str_data = substr($PaymentDate,0,2) . substr($PaymentDate, 3, 2) . substr($PaymentDate, 8, 2);


        $str_Record .= $str_data;

        //Inizio seconda parte del record
        //Scrivo 5 zeri per numero documento, 2 per il sezionale,6 per l'estratto conto numero della partita
        //2 per l'anno della partita
        for ($i = 0; $i <= 14; $i++) {
            $str_Record .= '0';
        }

        //Ciclo di 8 volte dei campi imponibile (11),prima metto un + aliquota (3), iva11 (1),imposta (10) finisco con un +
        for ($j = 0; $j <= 7; $j++) {
            for ($i = 0; $i <= 10; $i++) {
                $str_Record .= '0';
            }
            $str_Record .= '+';

            for ($i = 0; $i <= 13; $i++) {
                $str_Record .= '0';
            }
            $str_Record .= '+';
        }

        //Totale fattura (11) seguito da +
        for ($i = 0; $i <= 10; $i++) {
            $str_Record .= '0';
        }
        $str_Record .= '+';

        //Ciclo di 8 volte  sul conto di ricavo (7) e l'importo di ricavo (11) seguito da un +
        for ($j = 0; $j <= 7; $j++) {
            for ($i = 0; $i <= 17; $i++) {
                $str_Record .= '0';
            }
            $str_Record .= '+';
        }
        //Fine seconda parte del record.
        //Selezione del tipo di conto dare a seconda del tipo di pagamento selezionato


        $str_Record .=  "2405065";

        //Scrittura del conto che occupa 7 bit seguito da D che sta per Dare
        $str_Record .= 'A';

        //Totale dei versamenti per data e comune seguito da un +


        $str_Amount = $flt_Tot;

        $str_Zero = '';
        for ($i = 0; $i <= (10 - strlen($str_Amount)); $i++) {
            $str_Zero .= '0';
        }
        $str_Record .= $str_Zero . $str_Amount;
        $str_Record .= '+';


        //Pagamento parziale costituito da 18 spazi vuoti (per la causale del pagamento), 6 zeri par il numero
        //della partita del pagamento, 2 zeri per l'anno della partita
        for ($i = 0; $i <= 17; $i++) {
            $str_Record .= ' ';
        }

        for ($i = 0; $i <= 7; $i++) {
            $str_Record .= '0';
        }


        //Numero del conto avere lungo 7 bit seguito da A che sta per avere
        $str_Record .= "2405065A";


        //Totale dei pagamenti per comune e data che deve occupare 11 bit seguito da +
        $str_Amount =  $flt_Tot;
        $str_Zero = '';
        for ($i = 0; $i <= (10 - strlen($str_Amount)); $i++) {
            $str_Zero .= '0';
        }


        $str_Record .= $str_Zero . $str_Amount;
        $str_Record .= '+';


        //Pagamento parziale costituito da 18 spazi vuoti (per la causale del pagamento), 6 zeri par il numero
        //della partita del pagamento, 2 zeri per l'anno della partita
        for ($i = 0; $i <= 17; $i++) {
            $str_Record .= ' ';
        }

        for ($i = 0; $i <= 7; $i++) {
            $str_Record .= '0';
        }


        //Ciclo di 78 volte sul pagamento vuoto [costituito da 7 zeri per il conto, seguiti da uno spazio vuoto per D (dare)
        //o A (Avere), e da 11 zeri per l'importo e da un +] e sul pagamento parziale.
        for ($j = 0; $j <= 77; $j++) {
            //Pagamento vuoto
            for ($i = 0; $i <= 6; $i++) {
                $str_Record .= '0';
            }
            $str_Record .= ' ';
            for ($i = 0; $i <= 10; $i++) {
                $str_Record .= '0';
            }
            $str_Record .= '+';

            //Pagamento prziale costituito da 18 spazi vuoti (per la causale del pagamento), 6 zeri par il numero
            //della partita del pagamento, 2 zeri per l'anno della partita
            for ($i = 0; $i <= 17; $i++) {
                $str_Record .= ' ';
            }
            for ($i = 0; $i <= 7; $i++) {
                $str_Record .= '0';
            }
        }
        fwrite($file_w, $str_Record);
*/
        fclose($file_w);

        $zip = new ZipArchive();
        if ($zip->open($path . $str_zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFile($path . $str_file, $str_file);
            $zip->close();
            $_SESSION['Documentation'] = $MainPath . '/public/_TMP_EXP/' . $str_zipfile;

            unlink($path . $str_FileName);
            echo "FILE CREATO";
        }

    }



}


include(INC . "/footer.php");