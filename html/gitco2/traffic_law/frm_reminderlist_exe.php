<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_frm_reminderlist.php");
require_once(INC."/initialization.php");

require_once(TCPDF . "/tcpdf.php");
require_once(CLS."/cls_pdf.php");
require_once(TCPDF . "/fpdi.php");

ini_set('max_execution_time', 3000);

$s_TypePlate        = CheckValue('TypePlate','s');
$CreationDate       = CheckValue('CreationDate','s');
$Operation          = CheckValue('Operation','s');

//Mantiene le checkbox selezionate
$_SESSION['Checkboxes']["frm_reminderlist.php"] = $_POST['checkbox'];
    
if($s_TypePlate=="F"){
    require(COD."/printlist_reminder_foreign.php");
} else {
    require(COD."/printlist_reminder_national.php");
}

$AdditionalFilters = array();
if ($CreationDate != '') $AdditionalFilters['CreationDate'] = $CreationDate;
$AdditionalFilters['Filter'] = 1;

// $str_Message = "";

// if (!empty($a_InvalidReminders)){
//     $str_Message .= "Attenzione, alcuni verbali selezionati non sono stati inclusi nella stampa perché hanno totali negativi. Gli ID dei verbali sono i seguenti:<br/><ul style='list-style-position: inside;'>";
//     foreach($a_InvalidReminders as $id){
//         $str_Message .= "<li>".$id."</li>";
//     }
//     $str_Message .= "</ul>";
//     $_SESSION['Message'] = $str_Message;
// }

switch ($Operation){
    case INDEX_OPERATION_LIST_CREATED: {
        header("location: ".impostaParametriUrl($AdditionalFilters, 'frm_create_printlist_reminder.php'.$str_GET_Parameter));
        break;
    }
    case INDEX_OPERATION_LIST_EMITTED: {
        header("location: ".impostaParametriUrl($AdditionalFilters, 'frm_reminderlist.php'.$str_GET_Parameter));
        break;
    }
}

