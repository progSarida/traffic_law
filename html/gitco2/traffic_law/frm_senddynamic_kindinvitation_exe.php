<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

require(TCPDF . "/tcpdf.php");
require(CLS."/cls_pdf.php");

require(TCPDF . "/fpdi.php");

set_time_limit(-1);

$s_TypePlate= CheckValue('TypePlate','s');
$CreationDate = date('Y-m-d');
$Filters = CheckValue('Filters', 's');

//TODO Queste condizioni saranno da unificare qualora tutti gli enti useranno la nuova procedura
//Si assicura che l'ente che ha chiamato questa procedura non appartenga a quelli che usano la vecchia procedura
$a_OldProcedureCities = unserialize(OLD_SENDKINDFINE_CITIES);
$b_IsOldProcedureCity = in_array($_SESSION['cityid'], $a_OldProcedureCities);
//Fine condizioni

if(!$b_IsOldProcedureCity){
    if($s_TypePlate=="F"){
        //require(COD . "/send_kindfine_foreign.php");
    } else {
        require(COD . "/senddynamic_kindinvitation_national.php");
    }
} else {
    $_SESSION['Message']['Error'] = "Operazione non prevista per questo ente.";
}

header("location: frm_send_kindfine.php".$Filters);





