<?php
require("_path.php");
require(INC."/parameter.php");
require(CLS."/cls_db.php");
require(CLS."/cls_literal_number.php");
require(INC."/function.php");
require_once(INC."/pagopa.php");
require(INC."/initialization.php");

require(TCPDF . "/tcpdf.php");
require(CLS."/cls_pdf.php");

require(TCPDF . "/fpdi.php");

ini_set('max_execution_time', 3000);
ini_set('memory_limit', '2048M');
$rs= new CLS_DB();

$Parameters = "";
$n_Params = 0;

foreach ($_GET as $key => $value){
    $Parameters .= ($n_Params == 0 ? "?" : "&").$key."=".$value;
    ++$n_Params;
}
$PrintType = CheckValue('PrintType','n');

$Operation          = CheckValue('Operation','s');
$s_TypePlate        = CheckValue('TypePlate','s');
$CreationDate       = CheckValue('CreationDate','s');
$ProcessingDate     = CheckValue('ProcessingDate','s');
$CreationType       = CheckValue('CreationType','n');
$PrintDestinationFold = CheckValue('PrintDestinationFold','n');

$ChiefControllerId  = CheckValue('ChiefControllerId','n');
$n_ControllerId     = CheckValue('ControllerId','s');
$n_ControllerId = intval($n_ControllerId);

if ($Operation == "CREATE_DOC"){
    $P = "frm_createdynamic_reminder.php";
    
    if($s_TypePlate=="F"){
//         require(COD."/createdynamic_reminder_foreign.php");
        require(COD."/createdocument_reminder_foreign.php");
    } else {
//         require(COD."/createdynamic_reminder_national.php");
        require(COD."/createdocument_reminder_national.php");
    }
} else {
    $P = "frm_create_printlist_reminder.php";
    
    if($s_TypePlate=="F"){
        require(COD."/printlist_create_reminder_foreign.php");
    } else {
        require(COD."/printlist_create_reminder_national.php");
    }
}

//TODO inutilizzato, verificare
// if ($ultimate){
// 	if($table_row['DigitalSignature']==1){

// 		echo "<script>window.location='".$P."?DisplayMsg=1'</script>";
// 	}
// }

$str_Message = "";

if (!empty($a_InvalidReminders)){
    $str_Message .= "Attenzione, alcuni verbali selezionati non sono stati inclusi nella stampa perché hanno totali negativi. Gli ID dei verbali sono i seguenti:<br/><ul style='list-style-position: inside;'>";
    foreach($a_InvalidReminders as $id){
        $str_Message .= "<li>".$id."</li>";
    }
    $str_Message .= "</ul>";
    $_SESSION['Message'] = $str_Message;
}

header("location: ".$P.$Parameters."&PrintType=".$PrintType."&ControllerId=".$n_ControllerId.'&PrintDestinationFold='.$PrintDestinationFold);







