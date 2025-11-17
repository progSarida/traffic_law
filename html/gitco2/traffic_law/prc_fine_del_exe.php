<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


$str_Table = "H416_TMP";

$n_CountRow = 0;
$rs_FineDel = $rs->Select($str_Table, "1=1","FineId");

while($r_FineDel = mysqli_fetch_array($rs_FineDel)) {
    $n_CountRow++;

    echo $n_CountRow . " - " . $r_FineDel['FineId']." <br />";
    $rs_Fine = $rs->Select('Fine', "Id=" . $r_FineDel['FineId'] . " AND VehiclePlate='" . $r_FineDel['VehiclePlate'] . "'");
    $r_Fine = mysqli_fetch_array($rs_Fine);



    $str_FolderFine         = ($r_Fine['CountryId']=='Z000') ? NATIONAL_FINE : FOREIGN_FINE;
    $str_FolderViolation    = ($r_Fine['CountryId']=='Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;


    $rs->Start_Transaction();



    $rs_FineDocumentation = $rs->Select('FineDocumentation', "FineId=".$r_Fine['Id']);

    while($r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation)) {


        $str_Folder = ($r_FineDocumentation['DocumentationTypeId']==1) ? $str_FolderViolation : $str_FolderFine;

        if (file_exists($str_Folder."/".$r_Fine['CityId']."/".$r_Fine['Id']."/".$r_FineDocumentation['Documentation'])) {
            unlink($str_Folder."/".$r_Fine['CityId']."/".$r_Fine['Id']."/".$r_FineDocumentation['Documentation']);
        } else{
            echo $r_Fine['Id'].": Documento non trovato - ".$r_FineDocumentation['Documentation'];
            DIE;
        }

        $rs->Delete('FineDocumentation', "Id=" . $r_FineDocumentation['Id']);

    }
    if(!rmdir($str_Folder."/".$r_Fine['CityId']."/".$r_Fine['Id']."/")){
        echo $r_Fine['Id'].": Cartella non cancellata";
        DIE;
    }


    $rs_FineDocumentation = $rs->Select('FineDocumentation', "FineId=".$r_Fine['Id']);
    if(mysqli_num_rows($rs_FineDocumentation)>0){
        echo "Ancora documenti presenti per FineId:".$r_Fine['Id'];
        DIE;
    }




$rs->Delete('FineArticle', "FineId=" . $r_Fine['Id']);
$rs->Delete('FineHistory', "FineId=" . $r_Fine['Id']);
$rs->Delete('FineTrespasser', "FineId=" . $r_Fine['Id']);
$rs->Delete('FineArchive', "FineId=" . $r_Fine['Id']);
$rs->Delete('FineNotification', "FineId=" . $r_Fine['Id']);
$rs->Delete('FineOwner', "FineId=" . $r_Fine['Id']);
$rs->Delete('Fine', "Id=" . $r_Fine['Id']);
$rs->Delete($str_Table, "FineId=" . $r_Fine['Id']);

$rs->End_Transaction();


}
