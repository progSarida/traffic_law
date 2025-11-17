<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Year = CheckValue('Year', 'n');
$NewYear = CheckValue('NewYear', 'n');
$CityId = CheckValue('CityId', 's');
$Filters = CheckValue('Filters', 's');
$Action = CheckValue('Action', 'n');

$str_where= "";

if ($CityId != 'All'){
    $str_where = "AND Art.CityId='".$CityId."'";
}

$rs->Start_Transaction();

$rs_ArticleTariff = $rs->SelectQuery("
    SELECT AOld.ArticleId FROM Article Art 
    INNER JOIN ArticleTariff AOld ON AOld.ArticleId = Art.Id and AOld.Year = $NewYear
    WHERE AOld.Year = $NewYear $str_where");

if (mysqli_num_rows($rs_ArticleTariff) <= 0){
    $rs->ExecuteQuery("
    INSERT INTO ArticleTariff
    (ArticleId, Year, Fee, MaxFee, LicensePoint, YoungLicensePoint, LicensePointCode1, LicensePointCode2,
     AdditionalSanctionId, UseAdditionalSanction, PresentationDocument, LossLicense, 
     AdditionalMass, AdditionalNight, 126Bis, Habitual, ReducedPayment, SuspensionLicense, 
     PrefectureFixed, PenalSanction)
    SELECT 
    AOld.ArticleId, $NewYear, AOld.Fee, AOld.MaxFee, AOld.LicensePoint, AOld.YoungLicensePoint, AOld.LicensePointCode1, AOld.LicensePointCode2,
    AOld.AdditionalSanctionId, AOld.UseAdditionalSanction, AOld.PresentationDocument, AOld.LossLicense, 
    AOld.AdditionalMass, AOld.AdditionalNight, AOld.126Bis, AOld.Habitual, AOld.ReducedPayment, AOld.SuspensionLicense, 
    AOld.PrefectureFixed, AOld.PenalSanction
    FROM Article Art
    INNER JOIN ArticleTariff AOld ON AOld.ArticleId = Art.Id and AOld.Year = $Year
    WHERE AOld.Year = $Year $str_where;");
    
    $AffectedRows = mysqli_affected_rows($rs->conn);
    
    if ($AffectedRows > 0){
        //radio: Copia applicando le modifiche nel file
        if ($Action == 1){
            if(isset($_FILES['InputCsv']) && $_FILES['InputCsv']['error'] == 0) {
                
                $str_FileName = basename($_FILES['InputCsv']['name']);
                
                //Crea la cartella CSV in public/TEMP se non esiste
                if (!is_dir(TMP . "/CSV")) {
                    mkdir(TMP . "/CSV", 0777);
                }
                
                //Sposta il file caricato nella cartella temporanea
                if (move_uploaded_file($_FILES['InputCsv']['tmp_name'], TMP . "/CSV/". $str_FileName)) {
                    ini_set('auto_detect_line_endings',TRUE);
                    if (($handle = fopen(TMP . "/CSV/". $str_FileName, "r")) !== FALSE) {
                        
                        $row = 1;
                        
                        while (($data = fgetcsv($handle,0, ";")) !== FALSE) {
                            
                            //Dalla seconda riga in poi, la prima è destinata ai nomi delle colonne
                            if ($row > 1){
                                $Min = str_replace('.', '', $data[0]);
                                $Max = str_replace('.', '', $data[1]);
                                $NewMin = str_replace('.', '', $data[2]);
                                $NewMax = str_replace('.', '', $data[3]);
                                
                                //echo $Min."-".$Max.' '.$NewMin.'-'.$NewMax.'<br>';
                                
                                $a_UpdateMin = array(
                                    array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $NewMin, 'settype' => 'flt'),
                                );
                                
                                $rs->Update('ArticleTariff', $a_UpdateMin, "Fee=$Min AND Year=$NewYear");
                                
                                $a_UpdateMax = array(
                                    array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $NewMax, 'settype' => 'flt'),
                                );
                                
                                $rs->Update('ArticleTariff', $a_UpdateMax, "MaxFee=$Max AND Year=$NewYear");
                                
                            }
                            $row ++;
                        }
                        fclose($handle);
                        
                        $_SESSION['Message'] = "Azione eseguita con successo.";
                    }
                    ini_set('auto_detect_line_endings',FALSE);
                } else $_SESSION['Message'] = "Errore: caricamento del file fallito.";
            } else $_SESSION['Message'] = "Errore: caricamento del file fallito.";
        }
        //radio: Copia senza applicare modifiche alla tariffa
        else {
            $_SESSION['Message'] = "Azione eseguita con successo.";
        }
    } else $_SESSION['Message'] = "Nessun elemento da inserire per i parametri specificati.";
} else $_SESSION['Message'] = "Errore: sono già presenti tariffe per l'anno e ente specificati.";

$rs->End_Transaction();

header("location:tbl_customer_articletariffcsv_add.php".$Filters);