<?php
require_once("../_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(INC."/initialization.php");

function processInipecForniture(array $row, $codiciRichieste){
    global $rs;
    if(!isset($row[2])) return;
    
    if($row[8]=='OK'){
        $pec=$row[7];
    } else if($row[18]=='OK'){
        $pec=$row[17];
    } else $pec=null;
                
    $codiceFiscale=str_replace('"', '',$row[2]);
    $pec=str_replace('"', '',$pec);
    
    if (!empty($pec) && !empty($codiceFiscale)){
        $rs->Start_Transaction();
        $rs_trespasser=$rs->Select("Trespasser","VatCode='$codiceFiscale' or TaxCode='$codiceFiscale'");
        while ($r_trespasser = mysqli_fetch_array($rs_trespasser)) {
            if($r_trespasser['PEC']!=$pec){
                $rs->ExecuteQuery("insert into TrespasserHistory (TrespasserId,Genre,CompanyName,Surname,Name,
                Address,StreetNumber, Ladder, Indoor, Plan,ZIP, City, Province, CountryId, BornPlace,
                BornCountryId,BornDate, TaxCode,ForcedTaxCode,VatCode,Phone,Phone2, Fax, Notes, PEC, UserId,
                VersionDate, Mail, ZoneId,LanguageId,DeathDate,LandId)
                select Id,Genre,CompanyName,Surname,Name,
                Address,StreetNumber, Ladder, Indoor, Plan,ZIP, City, Province, CountryId, BornPlace,
                BornCountryId,BornDate, TaxCode,ForcedTaxCode,VatCode,Phone,Phone2, Fax, Notes, PEC, '".
                    $_SESSION['username']."', '".DateInDB(date("d/m/Y")).
                    "', Mail, ZoneId,LanguageId,DeathDate,LandId from Trespasser
                where Id={$r_trespasser['Id']}");
                
                $updateTrespasser=array(array('field' => 'PEC', 'selector' => 'value','type' => 'str', 'value'=>$pec));
                $rs->Update("Trespasser",$updateTrespasser,"Id={$r_trespasser['Id']}");
            }
        }
        
        $updateRequestPec = array(
            array('field' => 'der_risposta','selector' => 'value','type' => 'str','value' => $pec),
        );
        $rs->Update("dettaglio_richieste_servizi_est", $updateRequestPec, "der_cod_richiesta IN(".implode(',',$codiciRichieste).") AND der_oggetto ='$codiceFiscale'");
        
        $rs->End_Transaction();
    }
                
}

$idRichiesta=CheckValue('Id','s');
$userName=CheckValue('UserName','s');

$zip = new ZipArchive();
$filename=INIPEC_RESPONSE."/response_{$userName}_{$idRichiesta}.zip";

$richieste = $rs->getResults($rs->Select("richieste_servizi_esterni", "rse_tipo=4 AND rse_id_richiesta='$idRichiesta'"));
$codiciRichieste = array_column($richieste, 'rse_codice', 'Id');

if(!empty($codiciRichieste)){
    if(file_exists($filename)) {
        if ($zip-> open($filename)) {
            trigger_error("Zip aperto $filename",E_USER_NOTICE);
            $data = str_replace('"','',$zip->getFromIndex(0));
            $zip->close();
            $rows=explode(PHP_EOL,$data);
            foreach($rows as $row){
                trigger_error("Processo riga ".$row,E_USER_NOTICE);
                processInipecForniture(explode("~",$row), $codiciRichieste);
            }
            $message = "Importazione delle pec dalla risposta Inipec riuscita per Nome utente: $userName e Id Richiesta:$idRichiesta";
            $error=0;
        }
    }
    else{
        $message = "Impossibile trovare il file scaricato per Nome utente: $userName e Id Richiesta:$idRichiesta";
        $error=1;
    }
} else {
    $message = "Impossibile trovare richieste effettuate per Nome utente: $userName e Id Richiesta:$idRichiesta";
    $error=1;
}

echo json_encode(
    array(
        "error" => $error,
        "message" => $message
    )
);
?>