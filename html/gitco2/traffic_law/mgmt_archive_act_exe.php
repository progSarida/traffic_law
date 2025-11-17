<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

print_r($_SESSION['debug']);

$Id= CheckValue('Id','n');

$rs_FineArchive = $rs->Select('FineArchive',"FineId=".$Id);
$rs_Fine = $rs->Select('Fine',"Id=$Id");

if(mysqli_num_rows($rs_FineArchive)==1 
    && mysqli_num_rows($rs_Fine)==1 ){
    $r_FineArchive = mysqli_fetch_array($rs_FineArchive);
    $r_Fine = mysqli_fetch_array($rs_Fine);
    
    $str_Folder = ($r_Fine['CountryId']=='Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;
    
    $Note = $r_FineArchive['PreviousNote'];
    $StatusTypeId = $r_FineArchive['PreviousStatusTypeId'];

    $FineStatusTypeId = $r_Fine['StatusTypeId'];
    
    $rs->Start_Transaction();


    $a_Fine = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype'=>'int'),
        array('field'=>'Note','selector'=>'value','type'=>'str','value'=>$Note),
    );
    

    if ($FineStatusTypeId == 36)
    {
        $Code = $r_Fine['Code'];
        $CountryId = $r_Fine['CountryId'];
        $CityId = $r_Fine['CityId'];
        $ProtocolYear = $r_Fine['ProtocolYear'];
        $FineDate = $r_Fine['FineDate'];
        $FineTime = $r_Fine['FineTime'];
        $ControllerId = $r_Fine['ControllerId'];
        $Locality = $r_Fine['Locality'];
        $StreetTypeId = $r_Fine['StreetTypeId'];
        $Address = mysqli_real_escape_string($rs->conn, $r_Fine['Address']);
        $VehiclePlate = $r_Fine['VehiclePlate'];
        $VehicleMass = $r_Fine['VehicleMass'];
    
        $srt_where = "Code = '$Code' AND CountryId = '$CountryId' AND CityId = '$CityId' AND "
                        ."ProtocolYear = '$ProtocolYear' AND FineDate = '$FineDate' AND FineTime = '$FineTime' AND "
                        ."ControllerId = $ControllerId AND Locality = '$Locality' AND "
                        ."StreetTypeId = $StreetTypeId AND Address = '$Address' AND "
                        ."VehiclePlate = '$VehiclePlate' AND VehicleMass = $VehicleMass AND "
                        ."Id <> $Id";
                    
        $rs_FineRelated = $rs->Select('Fine',$srt_where);
        if(mysqli_num_rows($rs_FineRelated)== 1) {
            $r_FineRelated = mysqli_fetch_array($rs_FineRelated);
            if ($r_FineRelated['StatusTypeId'] < 15){
                
                $FineIdRelated = $r_FineRelated['Id'];
                
                $rs->Delete('FineArticle','FineId='.$FineIdRelated);
                $rs->Delete('FineAdditionalArticle','FineId='.$FineIdRelated);
                $rs->Delete('FineOwner','FineId='.$FineIdRelated);
                $rs->Delete('FineTrespasser','FineId='.$FineIdRelated); //non filtro per tipo di trasgressore perché devo eliminarli tutti
                $rs->Delete('FineAdditionalController','FineId='.$FineIdRelated);
                
                $rs_FineDocumentation = $rs->Select('FineDocumentation',"FineId=$FineIdRelated"); // AND DocumentationTypeId=1");
                if (is_dir($str_Folder."/".$_SESSION['cityid']."/".$FineIdRelated)) {
                    while($r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation)){
                        $Documentation = $r_FineDocumentation['Documentation'];
                        $DocumentationId = $r_FineDocumentation['Id'];
                        if(unlink($str_Folder."/".$_SESSION['cityid']."/".$FineIdRelated."/".$Documentation))
                            $rs->Delete('FineDocumentation',"Id = $DocumentationId");
                    }
                    
                    if (count(scandir($str_Folder."/".$_SESSION['cityid']."/".$FineIdRelated)) == 2) {
                        rmdir($str_Folder."/".$_SESSION['cityid']."/".$FineIdRelated);
                    }
                } 
                $rs->Delete('Fine','Id='.$FineIdRelated);
            } else {
                $_SESSION['Archive']['Error'] = "Non è possibile annullare archiviazione per: ". $Id;
                header("location: mgmt_archive.php".$str_GET_Parameter);
                exit;
            }
        }     
    }
    $rs->Update('Fine',$a_Fine, 'Id='.$Id);
    
    $rs->Delete('FineArchive','FineId='.$Id);
    
    $_SESSION['Archive']['Success'] = "Annullamento archiviazione avvenuto con successo!";

    $rs->End_Transaction();

} else $_SESSION['Archive']['Error'] ="Problemi con il verbale id ". $Id;

header("location: mgmt_archive.php".$str_GET_Parameter);