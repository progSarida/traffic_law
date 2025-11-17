<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$jsonBody = json_decode($_POST['Body'], true); //Composto da array di righe
$response = array();

if($jsonBody){
    $rs->Start_Transaction();
    
    foreach ($jsonBody as $obj){    //Ciclo l'array
        $fineId = intval($obj["fineid"]);
        $trespasserId = intval($obj["trespasserid"]);
        $letter = $obj["letter"];
        
        $a_FineFlowInfo = array(
            array('field'=>'FineId', 'selector'=>'value','type'=>'int', 'value'=>$fineId),
            array('field'=>'TrespasserId', 'selector'=>'value','type'=>'int', 'value'=>$trespasserId),
            array('field'=>'LetterNumber', 'selector'=>'value', 'type'=>'str', 'value'=>$letter),
            array('field'=>'RegDate', 'selector'=>'value','type'=>'date', 'value'=>date("Y-m-d")),
            array('field'=>'RegTime', 'selector'=>'value','type'=>'str', 'value'=>date("H:i"))
        );
        //Controllo se è già presente nella tabella
        $rs_FineFlows = $rs->Select("FineFlowInfo","FineId = $fineId AND TrespasserId = $trespasserId");
        
        //In caso sia presente ne faccio l'update, altrimenti l'insert
        if(mysqli_num_rows($rs_FineFlows) > 0):
            $rs->Update("FineFlowInfo", $a_FineFlowInfo, "FineId = $fineId AND TrespasserId = $trespasserId");
            $response[] = array(
                "Operation" => "Update",
                "FineId" => $fineId,
                "TrespasserId" => $trespasserId,
                "Letter" => $letter
            );
        else :
            $rs->Insert("FineFlowInfo", $a_FineFlowInfo);
            $response[] = array(
                "Operation" => "Insert",
                "FineId" => $fineId,
                "TrespasserId" => $trespasserId,
                "Letter" => $letter
            );
        endif;
    }

$rs->End_Transaction();
}

echo json_encode($response);