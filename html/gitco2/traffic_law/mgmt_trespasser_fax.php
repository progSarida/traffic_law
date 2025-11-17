<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$rs = new CLS_DB();
$TrespasserId = CheckValue('Id', 'n');

$Trespasser = $rs->SelectQuery("SELECT * FROM Trespasser WHERE Id='$TrespasserId'");
$Trespasser = mysqli_fetch_array($Trespasser);
$CityId = $Trespasser['CityId'];

$Registry = $rs->SelectQuery("SELECT RegistryOfficeAddress, RegistryOfficeFax, RegistryOfficeNumber, RegistryOfficePhone, RegistryOfficeZIP, RegistryOfficePEC FROM sarida.City WHERE Id='$CityId'");
if(mysqli_num_rows($Registry) == 0) {
    $Registry = $rs->SelectQuery("SELECT RegistryOfficeAddress, RegistryOfficeFax, RegistryOfficeNumber, RegistryOfficePhone, RegistryOfficeZIP, RegistryOfficePEC FROM sarida.City WHERE Title LIKE '%".$Trespasser['City']."%'");
    if(mysqli_num_rows($Registry) == 0 || mysqli_num_rows($Registry) > 1) {
        header("Location: mgmt_trespasser_viw.php".$str_GET_Parameter.'&message=Trasgressore non italiano o senza dati rezidenza&Id='.$TrespasserId.'&query='.$_GET['query']); die;
    }
}
$Registry = mysqli_fetch_array($Registry);

$form = $rs->SelectQuery("SELECT * FROM traffic_law.Form WHERE FormTypeId=100 AND CityId='".$_SESSION['cityid']."' AND LanguageId=1");
if(mysqli_num_rows($form) == 0) {
    header("Location: mgmt_trespasser_viw.php".$str_GET_Parameter.'&message=Non esiste una forma&Id='.$TrespasserId.'&query='.$_GET['query']); die;
}
$form = mysqli_fetch_array($form);
$form = $form['Content'];

foreach ($Trespasser as $key => $value){
    $form = str_replace('{'.$key.'}', $value, $form);
}
foreach ($Registry as $key => $value){
    $form = str_replace('{'.$key.'}', $value, $form);
}



require __DIR__ . '/../vendor/autoload.php';
$mpdf = new \Mpdf\Mpdf();
$form = mb_convert_encoding($form, 'UTF-8', 'UTF-8');
$mpdf->WriteHTML($form);
$mpdf->Output("FAX.pdf", \Mpdf\Output\Destination::DOWNLOAD);