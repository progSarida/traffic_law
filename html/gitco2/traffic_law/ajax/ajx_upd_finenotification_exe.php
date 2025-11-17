<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$a_ProcedureTypes = unserialize(FINE_PROCEDURE_TYPES);

if($_POST) {
    $FineId = CheckValue('Id','n');
    $str_Field = CheckValue('Field','s');
    $str_Notes = CheckValue('Notes','s');
    $b_isChecked = filter_var($_POST['Checked'], FILTER_VALIDATE_BOOLEAN);
    $str_Error = '';
    
    $ProcedureType = $a_ProcedureTypes[$str_Field];

    $rs_FineNotification = $rs->Select('FineNotification',"FineId=".$FineId);
    if(mysqli_num_rows($rs_FineNotification)>0) {

        if ($str_Field == "Payment") {
            if ($b_isChecked) {
                $n_Value = 1;
                
                /*$a_Fine = array(
                 array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 30, 'settype' => 'int'),
                 );
                
                 $rs->Update('Fine', $a_Fine, "Id=" . $FineId);
                 */
                
                
            } else {
                //TODO tenere monitorata e chiedere delucidazioni. Visto che era già stato tolto il cambio di stato a pagato (30) quando
                //si diceva di non elaborare i pagamenti per i solleciti, non ha senso riforzare a notificato il verbale se lo si riaccende
                //La variazione di flag così si lega alla volontà dell'operatore, ma non allo stato pagato/non pagato
                //                 $a_Fine = array(
                //                     array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 25, 'settype' => 'int'),
                //                 );
                
                //$rs->Update('Fine', $a_Fine, "Id=" . $FineId);
                
                $n_Value = 0;
            }
        } else if ($str_Field == "LicensePoint") {
            if (!$b_isChecked) {
                $n_Value = 0;
            } else {
                $rs_Tariff = $rs->SelectQuery("
                    SELECT    
                    	F.Id,    
                    	A.LicensePoint+coalesce(SUM(AA.LicensePoint),0) LicensePoint,    
                    	A.YoungLicensePoint+coalesce(SUM(AA.YoungLicensePoint),0) YoungLicensePoint 
                    FROM Fine F 
                    INNER JOIN FineArticle FA ON FA.FineId = F.Id 
                    INNER JOIN ArticleTariff A on FA.ArticleId = A.ArticleId and A.Year = F.ProtocolYear 
                    LEFT OUTER JOIN FineAdditionalArticle FAA  ON FAA.FineId = F.Id 
                    LEFT OUTER JOIN ArticleTariff AA on FAA.ArticleId = AA.ArticleId and AA.Year = F.ProtocolYear 
                    WHERE F.Id={$FineId} GROUP BY F.id, A.LicensePoint, A.YoungLicensePoint;
                ");
                $r_Tariff = mysqli_fetch_array($rs_Tariff);

                $LicensePointProcedure = $r_Tariff['LicensePoint'];

                if($LicensePointProcedure > 0){
                    $n_Value = $LicensePointProcedure;
                } else {
                    $str_Error = 'Non è possibile attivare la procedura di decurtazione punti per questo verbale in quanto non sono previsti punti da decurtare.';
                }
            }
        } else {
            $n_Value = $b_isChecked ? 1 : 0;
        }

        $a_FineNotification = array(
            array('field' => $str_Field . 'Procedure', 'selector' => 'value', 'type' => 'int', 'value' => $n_Value, 'settype' => 'int'),
        );

        $rs->Update('FineNotification', $a_FineNotification, "FineId=" . $FineId);
    } else {
        $rs_Tmp = $rs->Select('TMP_'.$str_Field.'Procedure', "FineId=".$FineId);

        if(mysqli_num_rows($rs_Tmp)>0){
            if($b_isChecked || (!$b_isChecked && $str_Field == 'ReminderAdditionalFee')){
                $rs->Delete('TMP_'.$str_Field.'Procedure', "FineId=".$FineId);
            }
        } else {
            if(!$b_isChecked || ($b_isChecked && $str_Field == 'ReminderAdditionalFee')){
                $a_Insert = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>$str_Field. 'Procedure', 'selector'=>'value', 'type'=>'int', 'value'=>0, 'settype'=>'int'),
                );
                $rs->Insert('TMP_'.$str_Field.'Procedure', $a_Insert);
            }
        }
    }
    
    $a_Insert = array(
        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
        array('field'=>'ProcedureType', 'selector'=>'value', 'type'=>'int', 'value'=>$ProcedureType, 'settype'=>'int'),
        array('field'=>'ProcedureNotes', 'selector'=>'value', 'type'=>'str', 'value'=>$str_Notes),
        array('field'=>'UserId', 'selector'=>'value', 'type'=>'str', 'value'=>$_SESSION['username']),
        array('field'=>'VersionDate', 'selector'=>'value', 'type'=>'str', 'value'=>date("Y-m-d H:i:s")),
    );
    
    $rs->Insert('FineProcedure', $a_Insert);

    echo json_encode(
        array(
            "Error" => $str_Error,
        )
    );
    
}






