<?php
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                         Procedure
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////

$a_Procedure =array("No","Si");
$a_ProcedureNotes = array();
$a_ProcedureTypes = unserialize(FINE_PROCEDURE_TYPES);


$str_FineClosed = $str_Procedure = '';



$str_CSSProcedure = ' style="color:#C43A3A; cursor:not-allowed;" display:hidden;';
$str_CSSProcedure = 'data-toggle="tab"';


$d_update = isset($r_Fine['ProcessingPaymentDateTime']) ? $r_Fine['ProcessingPaymentDateTime'] : null;

if ($r_Fine['StatusTypeId'] == 32){
    $str_FineClosed ='
        <div class="col-sm-12 BoxRowLabel">
            Verbale chiuso
        </div>

        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-4 BoxRowLabel" style="height:5.6rem;">
            Note motivazione
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:5.6rem;">
           '.$r_Fine['NoteProcedure'].'
        </div>
    ';
}else{
    
    $rs_FineProcedure = $rs->SelectQuery("
        SELECT FP1.*
        FROM FineProcedure FP1
        LEFT JOIN FineProcedure AS FP2 ON  FP1.FineId = FP2.FineId and FP1.VersionDate < FP2.VersionDate and FP1.ProcedureType = FP2.ProcedureType
        WHERE FP2.VersionDate IS NULL AND FP1.VersionDate IS NOT NULL AND FP1.FineId = $Id
    ");
    
    while($r_FineProcedure = mysqli_fetch_assoc($rs_FineProcedure)){
        $a_ProcedureNotes[$r_FineProcedure['ProcedureType']] = $r_FineProcedure['ProcedureNotes'];
    }
    $procedureTypes = unserialize(FINE_PROCEDURE_TYPES);
    //Cerco Note aggiuntive per chiusura da elaborazione 126bis nel relativo FineProcedure
    $str_auto126 = addslashes(BASIC_MESSAGE_126BIS_ELABORATION);    //Radice del messaggio automatico 126bis in FineProcedure
    //Recupero l'ultimo messaggio automatico 126bis
    $rs_auto126Bis = $rs->SelectQuery("SELECT * FROM FineProcedure WHERE FineId = $Id AND ProcedureType = {$procedureTypes['126Bis']} AND SUBSTRING(ProcedureNotes,1,71) = '$str_auto126' ORDER BY Id DESC LIMIT 1");
    $num_auto126 = mysqli_num_rows($rs_auto126Bis);
    //Recupero la lista dei messaggi personalizzati scritti su 126bis
    $rs_custom126Bis = $rs->SelectQuery("SELECT * FROM FineProcedure WHERE FineId = $Id AND ProcedureType = {$procedureTypes['126Bis']} AND SUBSTRING(ProcedureNotes,1,71) <> '$str_auto126' ORDER BY Id DESC");
    $a_ProcedureNotes[$a_ProcedureTypes['126Bis']] = "";
    while($r_custom126Bis = $rs->getArrayLine($rs_custom126Bis)){ //I messaggi personalizzati li stampo sempre
        //Se un messaggio è già presente nel messaggio principale, evito la sua duplicazione
        if(!(strpos($a_ProcedureNotes[$a_ProcedureTypes['126Bis']],$r_custom126Bis['ProcedureNotes']) === false)){
            continue;
            }
        $a_ProcedureNotes[$a_ProcedureTypes['126Bis']] .= " [".$r_custom126Bis['ProcedureNotes']."] ";
    }
    //Se ci sono messaggi automatici li aggancia solo se non sono già inclusi 
    if($num_auto126 > 0 && (strpos($a_ProcedureNotes[$a_ProcedureTypes['126Bis']],BASIC_MESSAGE_126BIS_ELABORATION) === false)){
        $r_auto126Bis = $rs->getArrayLine($rs_auto126Bis);
        //Dovessero esserci tanti messaggi automatici, stampo solo l'ultimo
        $a_ProcedureNotes[$a_ProcedureTypes['126Bis']] .= " [".$r_auto126Bis['ProcedureNotes']."] ";
        }
        
    //Cerco Note aggiuntive per chiusura da elaborazione 180/8 nel relativo FineProcedure
    $str_auto180 = addslashes(BASIC_MESSAGE_180_ELABORATION);    //Radice del messaggio automatico 180/8 in FineProcedure
    //Recupero l'ultimo messaggio automatico 180/8
    $rs_auto180 = $rs->SelectQuery("SELECT * FROM FineProcedure WHERE FineId = $Id AND ProcedureType = {$procedureTypes['PresentationDocument']} AND SUBSTRING(ProcedureNotes,1,69) = '$str_auto180' ORDER BY Id DESC LIMIT 1");
    $num_auto180 = mysqli_num_rows($rs_auto180);
    //Recupero la lista dei messaggi personalizzati scritti su 126bis
    $rs_custom180 = $rs->SelectQuery("SELECT * FROM FineProcedure WHERE FineId = $Id AND ProcedureType = {$procedureTypes['PresentationDocument']} AND SUBSTRING(ProcedureNotes,1,69) <> '$str_auto180' ORDER BY Id DESC");
    $a_ProcedureNotes[$a_ProcedureTypes['PresentationDocument']] = "";
    while($r_custom180 = $rs->getArrayLine($rs_custom180)){ //I messaggi personalizzati li stampo sempre
        //Se un messaggio è già presente nel messaggio principale, evito la sua duplicazione
        if(!(strpos($a_ProcedureNotes[$a_ProcedureTypes['PresentationDocument']],$r_custom180['ProcedureNotes']) === false)){
            continue;
        }
        $a_ProcedureNotes[$a_ProcedureTypes['PresentationDocument']] .=  " [".$r_custom180['ProcedureNotes']."] ";
    }
    //Se ci sono messaggi automatici li aggancia solo se non sono già inclusi 
    if($num_auto180 > 0 && (strpos($a_ProcedureNotes[$a_ProcedureTypes['PresentationDocument']],BASIC_MESSAGE_180_ELABORATION) === false)){
        $r_auto180 = $rs->getArrayLine($rs_auto180);
        //Dovessero esserci tanti messaggi automatici, stampo solo l'ultimo
        $a_ProcedureNotes[$a_ProcedureTypes['PresentationDocument']] .=  " [".$r_auto180['ProcedureNotes']."] ";
    }
    
    $rs_FineNotification = $rs->Select('FineNotification', "FineId=" . $Id);
    if(mysqli_num_rows($rs_FineNotification)==1){
        $r_FineNotification = mysqli_fetch_array($rs_FineNotification);
        $str_LicensePointProcedure          = ($r_FineNotification['LicensePointProcedure']>0) ? 'Si' : 'No';
        $str_PaymentProcedure               = $a_Procedure[$r_FineNotification['PaymentProcedure']];
        $str_ReminderAdditionalFeeProcedure = $a_Procedure[$r_FineNotification['ReminderAdditionalFeeProcedure']];
        $str_126BisProcedure                = $a_Procedure[$r_FineNotification['126BisProcedure']];
        $str_PresentationDocumentProcedure  = $a_Procedure[$r_FineNotification['PresentationDocumentProcedure']];
        $str_InjunctionProcedure            = $a_Procedure[$r_FineNotification['InjunctionProcedure']];
        $str_PaymentProcedureOffReason      = $r_FineNotification['PaymentProcedureOffReason'];

    } else {

        $rs_PaymentProcedure = $rs->Select("TMP_PaymentProcedure", "FineId=". $Id);
        $str_PaymentProcedure = (mysqli_num_rows($rs_PaymentProcedure)==0) ? 'Si' : 'No';
        
        //In questo caso il valore è opposto, se c'è il record su TMP è SI, altrimenti NO
        $rs_ReminderAdditionalFeeProcedure = $rs->Select("TMP_ReminderAdditionalFeeProcedure", "FineId=". $Id);
        $str_ReminderAdditionalFeeProcedure = (mysqli_num_rows($rs_ReminderAdditionalFeeProcedure)==0) ? 'No' : 'Si';

        $rs_126BisProcedure = $rs->Select("TMP_126BisProcedure", "FineId=". $Id);
        $str_126BisProcedure = (mysqli_num_rows($rs_126BisProcedure)==0) ? 'Si' : 'No';

        $rs_PresentationDocumentProcedure = $rs->Select("TMP_PresentationDocumentProcedure", "FineId=". $Id);
        $str_PresentationDocumentProcedure = (mysqli_num_rows($rs_PresentationDocumentProcedure)==0) ? 'Si' : 'No';

        $rs_LicensePointProcedure = $rs->Select("TMP_LicensePointProcedure", "FineId=". $Id);
        $str_LicensePointProcedure = (mysqli_num_rows($rs_LicensePointProcedure)==0) ? 'Si' : 'No';

        $rs_InjunctionProcedure = $rs->Select("TMP_InjunctionProcedure", "FineId=". $Id);
        $str_InjunctionProcedure = (mysqli_num_rows($rs_InjunctionProcedure)==0) ? 'Si' : 'No';
    }
    
    $srt_PaymentProcedure_data = '';
    
    if (!empty($str_PaymentProcedureOffReason)) {
        $srt_PaymentProcedure_data = 
        '<div class="clean_row HSpace4"></div>   
 
        <div class="col-sm-10 BoxRowLabel"> '
        . $str_PaymentProcedureOffReason. ' il '. $d_update .
        '</div>
        <div class="col-sm-2 BoxRowCaption">
        </div>';
    }

    $str_Procedure = '        
        <div class="col-sm-12 BoxRowLabel table_caption_I" style="text-align:center">
            Elaborazioni
        </div>

        <div class="clean_row HSpace4"></div> 

        <div class="col-sm-10 BoxRowLabel">
            Elaborare il sollecito di pagamento/ingiunzione in caso di infedele/tardivo/omesso pagamento 
        </div>  
        <div class="col-sm-2 BoxRowCaption" id="div_Payment"> 
            '. $str_PaymentProcedure .'
        </div>
        '. $srt_PaymentProcedure_data .'  
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;">
            Note
        </div>
        <div class="col-sm-10 BoxRowCaption" style="height:4.6rem;">
            '.($a_ProcedureNotes[$a_ProcedureTypes['Payment']] ?? '').'
        </div>

        <div class="clean_row HSpace16"></div>

        <div class="col-sm-10 BoxRowLabel">
            Mantieni la sanzione al minimo edittale
        </div>  
        <div class="col-sm-2 BoxRowCaption" id="div_ReminderAdditionalFee"> 
            '. $str_ReminderAdditionalFeeProcedure .'
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;">
            Note
        </div>
        <div class="col-sm-10 BoxRowCaption" style="height:4.6rem;">
            '.($a_ProcedureNotes[$a_ProcedureTypes['ReminderAdditionalFee']] ?? '').'
        </div>

        <div class="clean_row HSpace16"></div>

        <div class="col-sm-10 BoxRowLabel">
            Elaborare il verbale art. 126 Bis in caso di omessa comunicazione dei dati del trasgressore
        </div>  
        <div class="col-sm-2 BoxRowCaption" id="div_126Bis">
            '. $str_126BisProcedure .'
        </div>           
        <div class="clean_row HSpace4"></div> 
        <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;">
            Note
        </div>
        <div class="col-sm-10 BoxRowCaption" style="height:4.6rem;">
            '.($a_ProcedureNotes[$a_ProcedureTypes['126Bis']] ?? '').'
        </div>

        <div class="clean_row HSpace16"></div>

        <div class="col-sm-10 BoxRowLabel">
            Elaborare il verbale art. 180 in caso di omessa trasmissione della documentazione richiesta
        </div>  
        <div class="col-sm-2 BoxRowCaption" id="div_PresentationDocument">
            '. $str_PresentationDocumentProcedure .'
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;">
            Note
        </div>
        <div class="col-sm-10 BoxRowCaption" style="height:4.6rem;">
            '.($a_ProcedureNotes[$a_ProcedureTypes['PresentationDocument']] ?? '').'
        </div>

        <div class="clean_row HSpace16"></div>

        <div class="col-sm-10 BoxRowLabel">
            Procedi con la decurtazione punti della patente di guida del trasgressore comunicato
        </div>  
        <div class="col-sm-2 BoxRowCaption" id="div_LicensePoint">
            '. $str_LicensePointProcedure .'
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;">
            Note
        </div>
        <div class="col-sm-10 BoxRowCaption" style="height:4.6rem;">
            '.($a_ProcedureNotes[$a_ProcedureTypes['LicensePoint']] ?? '').'
        </div>

        <div class="clean_row HSpace16"></div>

        <div class="col-sm-10 BoxRowLabel">
           '."Procedi con l'iscrizione a ruolo - l'estrazione delle partite non pagate in tutto o in parte o pagate in ritardo
           ".'        </div>  
        <div class="col-sm-2 BoxRowCaption" id="div_Injunction">
            '. $str_InjunctionProcedure .'
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;">
            Note
        </div>
        <div class="col-sm-10 BoxRowCaption" style="height:4.6rem;">
            '.($a_ProcedureNotes[$a_ProcedureTypes['Injunction']] ?? '').'
        </div>

        <div class="clean_row HSpace16"></div>

        <div class="col-sm-12 table_label_H">
            Chiudi il verbale
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <form name="f_FineClose" id="f_FineClose" method="post" action="mgmt_fine_act_exe.php">
            <input type="hidden" name="Id" value="'. $Id .'">
            <div class="col-sm-2 BoxRowLabel" style="height:5.6rem;">
                Note chiusura
            </div>
            <div class="col-sm-8 BoxRowCaption" style="height:5.6rem;">
                '.$r_Fine['NoteProcedure'].'	
            </div>
            <div class="col-sm-2 BoxRowLabel" style="height:5.6rem;">

            </div>    
        </form>
        
    
    
    
    ';

}









$str_Procedure_data = '
<div class="tab-pane" id="Procedure">            
    <div class="col-sm-12">
        '. $str_Procedure.$str_FineClosed .'
    </div>
</div>
';



