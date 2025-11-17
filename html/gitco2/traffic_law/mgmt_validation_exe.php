<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");




if (isset($_POST['deleted_id']) && $_POST['deleted_id']!=""){
    $deletedId = $_POST['deleted_id'];

    $rs->Start_Transaction();
    
    $rs->Delete('Fine','Id='.$deletedId);
    $rs->Delete('FineArticle','FineId='.$deletedId);
    $rs->Delete('FineAdditionalArticle','FineId='.$deletedId);
    $rs->Delete('FineDocumentation','FineId='.$deletedId);
    $rs->Delete('FineAdditionalController','FineId='.$deletedId);
    $str_Where = "StatusTypeId=0 AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'];

    $table_rows = $rs->Select('V_Validation',$str_Where,"Id=$deletedId");
    $table_row = mysqli_fetch_array($table_rows);
    $str_Folder = ($table_row['CountryId'] == 'Z000') ? 'doc/national/violation' : 'doc/foreign/violation';
    $dir = $str_Folder . '/' . $_SESSION['cityid'] . '/' . $deletedId;

    array_map('unlink', glob("$dir/*"));
    rmdir($dir);
    
    $rs->End_Transaction();
} else {

    $Id = CheckValue('Id', 'n');
    $VehiclePlate = strtoupper(CheckValue('VehiclePlate', 's'));

    $nextId = $_POST['nextId'];
    $previousId = $_POST['PreviousId'];
    $controllers = $_POST['ControllerId'];
    $first_controller = $controllers[0];

    $unique = array();
    foreach ($controllers as $value) {
        if ($value != 0) {
            if (isset($unique[$value])) {
                if (isset($unique[$value])) {
                    header("location: mgmt_validation.php" . $str_GET_Parameter . "&Id=" . $Id . "&answer=Si prega di non scegliere lo stesso accertatore più di una volta!");
                    DIE;
                }
            }
            $unique[$value] = '';

        }
    }
    
    $rs->Start_Transaction();
    
    $rs_row = $rs->Select('Fine','Id='.$Id);
    $r_row = mysqli_fetch_array($rs_row);
    $OldCountryId = $r_row['CountryId'];
    
    $Address = CheckValue('FineAddress', 's');
    $aFine = array(
        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'FineDate', 'selector' => 'field', 'type' => 'date'),
        array('field' => 'FineTime', 'selector' => 'field', 'type' => 'str'),
        array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $Address),
        array('field' => 'ControllerId', 'selector' => 'value', 'type' => 'int', 'value' => $first_controller, 'settype' => 'int'),
        array('field' => 'VehicleTypeId', 'selector' => 'field', 'type' => 'int', 'settype' => 'int'),
        array('field' => 'VehiclePlate', 'selector' => 'value', 'type' => 'str', 'value' => $VehiclePlate),
        array('field' => 'VehicleCountry','selector'=>'field','type'=>'str'),
        array('field' => 'CountryId', 'selector' => 'field', 'type' => 'str'),
    );


    $rs->Update('Fine', $aFine, 'Id=' . $Id);
    $a_FineArticle = array(
        array('field' => 'DetectorId', 'selector' => 'field', 'type' => 'int', 'settype' => 'int'),
        array('field' => 'ReasonId', 'selector' => 'field', 'type' => 'int', 'settype' => 'int'),
    );

    $rs->Update('FineArticle',$a_FineArticle,'FineId='.$Id);
    
    //gestione cartelle in base alla nazionalità
    $CountryId = CheckValue('CountryId','s');
    trigger_error("LOG CountryId  $CountryId",E_USER_NOTICE);

    trigger_error("ROW CountryId ".$OldCountryId,E_USER_NOTICE);
    if(($CountryId!=$OldCountryId)&&($CountryId=='Z000'||$OldCountryId=='Z000')){
        
        if($CountryId=='Z000'){
            if (!is_dir(NATIONAL_VIOLATION."/".$_SESSION['cityid']."/".$Id)) {
                mkdir(NATIONAL_VIOLATION."/".$_SESSION['cityid']."/".$Id, 0777);             
            }
            $str_OldFolder = FOREIGN_VIOLATION;
            $str_NewFolder = NATIONAL_VIOLATION;
            
        } else{
            if (!is_dir(FOREIGN_VIOLATION."/".$_SESSION['cityid']."/".$Id)) {
                mkdir(FOREIGN_VIOLATION."/".$_SESSION['cityid']."/".$Id, 0777);              
            }
            
            $str_OldFolder = NATIONAL_VIOLATION;
            $str_NewFolder = FOREIGN_VIOLATION;
        }
        trigger_error("OLD ".$str_OldFolder,E_USER_NOTICE);
        trigger_error("NEW ".$str_NewFolder,E_USER_NOTICE);
        
        $rs_row = $rs->Select('FineDocumentation','FineId='.$Id);
        while($r_row = mysqli_fetch_array($rs_row)){
            trigger_error("ROW Id ".$Id,E_USER_NOTICE);
            trigger_error("ROW Doc ".$r_row['Documentation'],E_USER_NOTICE);
            copy($str_OldFolder."/".$_SESSION['cityid']."/".$Id."/".$r_row['Documentation'],$str_NewFolder."/".$_SESSION['cityid']."/".$Id."/".$r_row['Documentation']);
            if (file_exists($str_NewFolder."/".$_SESSION['cityid']."/".$Id."/".$r_row['Documentation'])) {
                unlink($str_OldFolder."/".$_SESSION['cityid']."/".$Id."/".$r_row['Documentation']);
            }
        }
        
        rmdir($str_OldFolder."/".$_SESSION['cityid']."/".$Id);
    }
    
    $rs->End_Transaction();
    
    if ($nextId != ""){
        header("location: mgmt_validation_upd.php".$str_GET_Parameter."&Id=".$nextId);
    }elseif($previousId !=""){
        header("location: mgmt_validation_upd.php".$str_GET_Parameter."&Id=".$previousId);
    }else{
        header("location: mgmt_validation.php".$str_GET_Parameter);
    }

}


