<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

require(TCPDF . "/tcpdf.php");
require(CLS."/cls_pdf.php");

ini_set('max_execution_time', 3000);
$rs= new CLS_DB();


$P = "mgmt_fine.php";

$s_CountryId= CheckValue('CountryId','s');
$n_LanguageId = CheckValue('LanguageId','n');
$FineId = CheckValue('FineId','n');





if($s_CountryId=="Z000" && $LanguageId==1){
    require(COD."/print_fine_national.php");
} else {
    require(COD."/print_fine_foreign.php");
}



$FileName = 'reprint.pdf';

if($s_CountryId=="Z000"){
    $pdf->Output(NATIONAL_FINE."/".$_SESSION['cityid']."/".$FileName, "F");
    $_SESSION['Documentation'] = $MainPath.'/doc/national/fine/'.$_SESSION['cityid'].'/'.$FileName;
}else{
    $pdf->Output(FOREIGN_FINE."/".$_SESSION['cityid']."/".$FileName, "F");
    $_SESSION['Documentation'] = $MainPath.'/doc/foreign/fine/'.$_SESSION['cityid'].'/'.$FileName;

}










header("location: ".$P);







