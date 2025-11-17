<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
ini_set('max_execution_time', 0);



include_once TCPDF . "/tcpdf.php";


$table_rows = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
$table_row = mysqli_fetch_array($table_rows);

$MangerName = $table_row['ManagerName'];
$ManagerAddress = $table_row['ManagerAddress'];
$ManagerCity = $table_row['ManagerZIP']." ".$table_row['ManagerCity']." (".$table_row['ManagerProvince'].")";
$ManagerPhone = $table_row['ManagerPhone'];

$a_FileImgPath = array();
$a_FileImgName = array();

$file_Document = fopen(ROOT."/public/_TMP_EXP/".'file.csv', 'w');

$strDocumentCsvFile = 'REF verbale;Verbale PDF'.PHP_EOL;
fwrite($file_Document, $strDocumentCsvFile);




$rs_FineDocumentation = $rs->SelectQuery("
SELECT 
F.Id,
F.CityId,
F.Code,
F.FineDate,
F.ProtocolId,
F.ProtocolYear,
F.VehiclePlate,
F.CountryId,

FD.Documentation

FROM Fine F JOIN FineDocumentation FD ON F.Id= FD.FineId AND FD.DocumentationTypeId=2


WHERE F.CityId='".$_SESSION['cityid']."' AND F.ProtocolYear=".$_SESSION['year']." AND F.ProtocolId>0



");
// AND F.FineDate<='".$_SESSION['year']."-06-30'
// AND F.FineDate>'".$_SESSION['year']."-06-30'



while ($r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation)) {
/*
Identificativo violazione 	Data violazione	    Targa	    Data notifica	    Data ritorno	Numero Raccomandata	    Immagine
190479/2014	                03/10/2014	        DN-AD8139	17/02/2016	        12/04/2016	    RA690223209	            CARTOLINE\RIC_KS6133.pdf
198168/2014	                29/11/2014	        TI234502	22/07/2015	        15/09/2015	    RA689470640	            CARTOLINE\RIC_KB6497.pdf


*/




    $str_DocumentFolder = ($r_FineDocumentation['CountryId']=='Z000') ?  NATIONAL_FINE."/".$_SESSION['cityid']."/".$r_FineDocumentation['Id']  : FOREIGN_FINE."/".$_SESSION['cityid']."/".$r_FineDocumentation['Id'];

    $a_FileImgPath[] = $str_DocumentFolder.'/';
    $a_FileImgName[] = $r_FineDocumentation['Documentation'];

    $strDocumentCsvFile =  $r_FineDocumentation['Code'].';'.
        $r_FineDocumentation['Documentation'].PHP_EOL;

    fwrite($file_Document, $strDocumentCsvFile);


}
fclose($file_Document);

$str_OutMonitor = "File creato con successo!";

$str_FileNameZip = "EXP_" . $_SESSION['cityid'] ."_" . $_SESSION['year'] . "_" . date("Y-m-d") . "_" . date("H-i-s") . "_" . count($a_FileImgName) . ".zip";

$obj_Zip = new ZipArchive();
if($obj_Zip->open(ROOT."/public/_TMP_EXP/". $str_FileNameZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)===true){
    $_SESSION['Documentation'] = $MainPath . '/public/_TMP_EXP/' . $str_FileNameZip;

    $obj_Zip->addFile(ROOT."/public/_TMP_EXP/file.csv", "file.csv");

    for ($i = 0; $i < count($a_FileImgName); $i++) {
        $obj_Zip->addFile($a_FileImgPath[$i].$a_FileImgName[$i], $a_FileImgName[$i]);
        sleep(1);
    }
    $obj_Zip->close();


    unlink(ROOT."/public/_TMP_EXP/file.csv");


    $_SESSION['Message'] = $str_OutMonitor;
}