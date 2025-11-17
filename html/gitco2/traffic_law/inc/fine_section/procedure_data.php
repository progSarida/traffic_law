<?php
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                         Procedure
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////

$a_Procedure =array("","checked");
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
        <div class="col-sm-6 BoxRowCaption" style="height:5.6rem;">
           '.$r_Fine['NoteProcedure'].'
        </div>
        <form name="f_FineOpenClose" id="f_FineOpenClose" method="post" action="mgmt_fine_act_exe.php">
            <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
            <input type="hidden" name="Operation" value="Open">
            <input type="hidden" name="Id" value="'. $Id .'">
            <div class="col-sm-2 BoxRowCaption text-center" style="height:5.6rem;">
                <button id="btn_OpenFine" class="btn btn-success" style="width:100%;height:100%;padding:0">Riapri verbale</button>
            </div>    
        </form>
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
        $a_ProcedureNotes[$a_ProcedureTypes['PresentationDocument']] .= " [".$r_custom180['ProcedureNotes']."] ";
    }
    //Se ci sono messaggi automatici li aggancia solo se non sono già inclusi 
    if($num_auto180 > 0 && (strpos($a_ProcedureNotes[$a_ProcedureTypes['PresentationDocument']],BASIC_MESSAGE_180_ELABORATION) === false)){
        $r_auto180 = $rs->getArrayLine($rs_auto180);
        //Dovessero esserci tanti messaggi automatici, stampo solo l'ultimo
        $a_ProcedureNotes[$a_ProcedureTypes['PresentationDocument']] .= " [".$r_auto180['ProcedureNotes']."] ";
    }
    

    $rs_FineNotification = $rs->Select('FineNotification', "FineId=" . $Id);
    if(mysqli_num_rows($rs_FineNotification)==1){
        $r_FineNotification = mysqli_fetch_array($rs_FineNotification);
        $str_LicensePointProcedure          = ($r_FineNotification['LicensePointProcedure']>0) ? 'checked' : '';
        $str_PaymentProcedure               = $a_Procedure[$r_FineNotification['PaymentProcedure']];
        $str_ReminderAdditionalFeeProcedure = $a_Procedure[$r_FineNotification['ReminderAdditionalFeeProcedure']];
        $str_126BisProcedure                = $a_Procedure[$r_FineNotification['126BisProcedure']];
        $str_PresentationDocumentProcedure  = $a_Procedure[$r_FineNotification['PresentationDocumentProcedure']];
        $str_InjunctionProcedure            = $a_Procedure[$r_FineNotification['InjunctionProcedure']];
        $str_PaymentProcedureOffReason      = $r_FineNotification['PaymentProcedureOffReason'];

    } else {

        $rs_PaymentProcedure = $rs->Select("TMP_PaymentProcedure", "FineId=". $Id);
        $str_PaymentProcedure = (mysqli_num_rows($rs_PaymentProcedure)==0) ? 'checked' : '';
        
        //In questo caso il valore è opposto, se c'è il record su TMP è SI, altrimenti NO
        $rs_ReminderAdditionalFeeProcedure = $rs->Select("TMP_ReminderAdditionalFeeProcedure", "FineId=". $Id);
        $str_ReminderAdditionalFeeProcedure = (mysqli_num_rows($rs_ReminderAdditionalFeeProcedure)==0) ? '' : 'checked';

        $rs_126BisProcedure = $rs->Select("TMP_126BisProcedure", "FineId=". $Id);
        $str_126BisProcedure = (mysqli_num_rows($rs_126BisProcedure)==0) ? 'checked' : '';

        $rs_PresentationDocumentProcedure = $rs->Select("TMP_PresentationDocumentProcedure", "FineId=". $Id);
        $str_PresentationDocumentProcedure = (mysqli_num_rows($rs_PresentationDocumentProcedure)==0) ? 'checked' : '';

        $rs_LicensePointProcedure = $rs->Select("TMP_LicensePointProcedure", "FineId=". $Id);
        $str_LicensePointProcedure = (mysqli_num_rows($rs_LicensePointProcedure)==0) ? 'checked' : '';

        $rs_InjunctionProcedure = $rs->Select("TMP_InjunctionProcedure", "FineId=". $Id);
        $str_InjunctionProcedure = (mysqli_num_rows($rs_InjunctionProcedure)==0) ? 'checked' : '';
    }
    
    $srt_PaymentProcedure_data = '';
    if (!empty($str_PaymentProcedureOffReason)) {
        $srt_PaymentProcedure_data = '
        <div class="clean_row HSpace4"></div>   
 
        <div class="col-sm-12 BoxRowLabel"> '
            . $str_PaymentProcedureOffReason. ' il '. DateTimeOutDB($d_update) .
        '</div>';
    }
    
    $srt_procedureWarning = '
		  <div class="table_caption_H col-sm-12 alert-warning">
            <i class="fas fa-fw fa-info-circle col-sm-1" style="margin-top: 0.5rem;"></i>&nbsp;&nbsp;&nbsp;%s
          </div>
        <div class="clean_row HSpace4"></div>';

    $str_Procedure = '        
        <div class="col-sm-12 BoxRowLabel table_caption_I" style="text-align:center">
            Elaborazioni
        </div>

        <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
            <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
            <div class="col-sm-11" style="font-size: 1.2rem;">
                <ul>
                    <li>Per poter modificare una voce:
                    <ul style="list-style-position: inside;">
                        <li>Fare click sulla corrispettiva levetta per attivare/disattivare la procedura</li>
                        <li>Inserire nel campo "Note" una motivazione che giustifichi l\'attivazione/disattivazione dell\'elaborazione interessata<br>
                            <strong>Nota bene:</strong> Perchè sia valido, il campo note non deve essere vuoto e il contenuto deve essere diverso da quello salvato in precedenza</li>
                        <li>Premere il corrispettivo pulsante "<i class="fa fa-save"></i>" per salvare le modifiche</li>
                    </ul>
                </ul>
            </div>
        </div>

        <div class="clean_row HSpace4"></div> 

        <div class="col-sm-12 BoxRowLabel">
            Elaborare il sollecito di pagamento/ingiunzione in caso di infedele/tardivo/omesso pagamento 
        </div>  
            '. $srt_PaymentProcedure_data .'  
        <div class="clean_row HSpace4"></div>

        <div class="col-sm-1 BoxRowCaption" id="div_Payment" style="height: 4.6rem;line-height: 4.6rem;"> 
            <div class="material-switch" style="margin-left:1rem;">
                <input data-field-ref="Payment" id="input_Payment" value="1" class="procedureInput" type="checkbox" '.$str_PaymentProcedure.'/>
                <label for="input_Payment" class="label-default"></label>
            </div>    
        </div>
        <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;">
            Note
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:4.6rem;">
            <textarea disabled id="notes_Payment" data-field-ref="Payment" name="PaymentNotes" class="form-control frm_field_string procedureNotes" style="height:4rem;margin-left:0;">'.($a_ProcedureNotes[$a_ProcedureTypes['Payment']] ?? '').'</textarea>	
        </div>

        <div class="col-sm-1 BoxRowCaption" style="height: 4.6rem;">
			<button disabled previous-note="'.htmlspecialchars($a_ProcedureNotes[$a_ProcedureTypes['Payment']] ?? '').'" previous-value="'.$str_PaymentProcedure.'" data-toggle="tooltip" data-container="body" data-placement="right" title="Modifica" data-fineid="'. $Id .'" data-field="Payment" class="tooltip-r btn btn-primary procedure" style="width:100%;height:100%;padding:0;margin:0"><i class="fa fa-save" ></i></button>
        </div>

        <div class="clean_row HSpace16"></div>

        <div class="col-sm-12 BoxRowLabel">
            Mantieni la sanzione al minimo edittale
        </div>  

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-1 BoxRowCaption" id="div_ReminderAdditionalFee" style="height: 4.6rem;line-height: 4.6rem;"> 
            <div class="material-switch" style="margin-left:1rem;">
                <input data-field-ref="ReminderAdditionalFee" id="input_ReminderAdditionalFee" value="1" class="procedureInput" type="checkbox" '.$str_ReminderAdditionalFeeProcedure.'/>
                <label for="input_ReminderAdditionalFee" class="label-default"></label>
            </div>    
        </div>
        <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;">
            Note
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:4.6rem;">
            <textarea disabled id="notes_ReminderAdditionalFee" data-field-ref="ReminderAdditionalFee" name="ReminderAdditionalFeeNotes" class="form-control frm_field_string procedureNotes" style="height:4rem;margin-left:0;">'.($a_ProcedureNotes[$a_ProcedureTypes['ReminderAdditionalFee']] ?? '').'</textarea>	
        </div>

        <div class="col-sm-1 BoxRowCaption" style="height: 4.6rem;">
			<button disabled previous-note="'.htmlspecialchars($a_ProcedureNotes[$a_ProcedureTypes['ReminderAdditionalFee']] ?? '').'" previous-value="'.$str_ReminderAdditionalFeeProcedure.'" data-toggle="tooltip" data-container="body" data-placement="right" title="Modifica" data-fineid="'. $Id .'" data-field="ReminderAdditionalFee" class="tooltip-r btn btn-primary procedure" style="width:100%;height:100%;padding:0;margin:0"><i class="fa fa-save" ></i></button>
        </div> 

        <div class="clean_row HSpace16"></div>

        <div class="col-sm-12 BoxRowLabel">
            Elaborare il verbale art. 126 Bis in caso di omessa comunicazione dei dati del trasgressore
        </div>  

        <div class="clean_row HSpace4"></div>

        '.(!$b_126bis ? sprintf($srt_procedureWarning, 'Nota: non è prevista l\'elaborazione art. 126 bis per questo atto') : '').'

        <div class="col-sm-1 BoxRowCaption" id="div_126Bis" style="height: 4.6rem;line-height: 4.6rem;">
            <div class="material-switch" style="margin-left:1rem;">
                <input id="input_126Bis" data-field-ref="126Bis" value="1" class="procedureInput" type="checkbox" '.$str_126BisProcedure.'/>
                <label for="input_126Bis" class="label-default"></label>
            </div>       
        </div>
        <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;">
            Note
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:4.6rem;">
            <textarea disabled id="notes_126Bis" data-field-ref="126Bis" name="126BisNotes" class="form-control frm_field_string procedureNotes" style="height:4rem;margin-left:0;">'.($a_ProcedureNotes[$a_ProcedureTypes['126Bis']] ?? '').'</textarea>	
        </div>

        <div class="col-sm-1 BoxRowCaption" style="height:4.6rem;">
            <button disabled previous-note="'.htmlspecialchars($a_ProcedureNotes[$a_ProcedureTypes['126Bis']] ?? '').'" previous-value="'.$str_126BisProcedure.'" data-toggle="tooltip" data-container="body" data-placement="right" title="Modifica" data-fineid="'. $Id .'" data-field="126Bis" class="tooltip-r btn btn-primary procedure" style="width:100%;height:100%;padding:0;margin:0"><i class="fa fa-save"></i></button>
        </div>

        <div class="clean_row HSpace16"></div>

        <div class="col-sm-12 BoxRowLabel">
            Elaborare il verbale art. 180 in caso di omessa trasmissione della documentazione richiesta
        </div>  

        <div class="clean_row HSpace4"></div>

        '.(!$b_180 ? sprintf($srt_procedureWarning, 'Nota: non è prevista l\'elaborazione art. 180 per questo atto') : '').'

        <div class="col-sm-1 BoxRowCaption" id="div_PresentationDocument" style="height: 4.6rem;line-height: 4.6rem;">
            <div class="material-switch" style="margin-left:1rem;">
                <input data-field-ref="PresentationDocument" id="input_PresentationDocument" value="1" class="procedureInput" type="checkbox" '.$str_PresentationDocumentProcedure.'/>
                <label for="input_PresentationDocument" class="label-default"></label>
            </div>            
        </div>
        <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;">
            Note
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:4.6rem;">
            <textarea disabled id="notes_PresentationDocument" data-field-ref="PresentationDocument" name="PresentationDocumentNotes" class="form-control frm_field_string procedureNotes" style="height:4rem;margin-left:0;">'.($a_ProcedureNotes[$a_ProcedureTypes['PresentationDocument']] ?? '').'</textarea>	
        </div>

        <div class="col-sm-1 BoxRowCaption" style="height:4.6rem;">
			<button disabled previous-note="'.htmlspecialchars($a_ProcedureNotes[$a_ProcedureTypes['PresentationDocument']] ?? '').'" previous-value="'.$str_PresentationDocumentProcedure.'" data-toggle="tooltip" data-container="body" data-placement="right" title="Modifica" data-fineid="'. $Id .'" data-fineid="'. $Id .'" data-field="PresentationDocument" class="tooltip-r btn btn-primary procedure" style="width:100%;height:100%;padding:0;margin:0"><i class="fa fa-save"></i></button>
        </div>  

        <div class="clean_row HSpace16"></div>

        <div class="col-sm-12 BoxRowLabel">
            Procedi con la decurtazione punti della patente di guida del trasgressore comunicato
        </div>  

        <div class="clean_row HSpace4"></div>

        '.(!$b_LicensePoint ? sprintf($srt_procedureWarning, 'Nota: non vi sono punti da decurtare per questo atto') : '').'

        <div class="col-sm-1 BoxRowCaption" id="div_LicensePoint" style="height: 4.6rem;line-height: 4.6rem;">
            <div class="material-switch" style="margin-left:1rem;">
                <input '.(!$b_LicensePoint ? 'disabled' : '').' data-field-ref="LicensePoint" id="input_LicensePoint" value="1" class="procedureInput" type="checkbox" '.$str_LicensePointProcedure.'/>
                <label for="input_LicensePoint" class="label-default"></label>
            </div>  
        </div>
        <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;">
            Note
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:4.6rem;">
            <textarea disabled id="notes_LicensePoint" data-field-ref="LicensePoint" name="LicensePointNotes" class="form-control frm_field_string procedureNotes" style="height:4rem;margin-left:0;">'.($a_ProcedureNotes[$a_ProcedureTypes['LicensePoint']] ?? '').'</textarea>	
        </div>

        <div class="col-sm-1 BoxRowCaption" style="height:4.6rem;">
			<button disabled previous-note="'.htmlspecialchars($a_ProcedureNotes[$a_ProcedureTypes['LicensePoint']] ?? '').'" previous-value="'.$str_LicensePointProcedure.'" data-toggle="tooltip" data-container="body" data-placement="right" title="Modifica" data-fineid="'. $Id .'" data-field="LicensePoint" class="tooltip-r btn btn-primary procedure" style="width:100%;height:100%;padding:0;margin:0"><i class="fa fa-save"></i></button>
        </div>

        <div class="clean_row HSpace16"></div>

        <div class="col-sm-12 BoxRowLabel">
            Procedi con l\'iscrizione a ruolo - l\'estrazione delle partite non pagate in tutto o in parte o pagate in ritardo
        </div>  

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-1 BoxRowCaption" id="div_Injunction" style="height: 4.6rem;line-height: 4.6rem;">
            <div class="material-switch" style="margin-left:1rem;">
                <input data-field-ref="Injunction" id="input_Injunction" value="1" class="procedureInput" type="checkbox" '.$str_InjunctionProcedure.'/>
                <label for="input_Injunction" class="label-default"></label>
            </div>  
        </div>
        <div class="col-sm-2 BoxRowLabel" style="height:4.6rem;">
            Note
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:4.6rem;">
            <textarea disabled id="notes_Injunction" data-field-ref="Injunction" name="InjunctionNotes" class="form-control frm_field_string procedureNotes" style="height:4rem;margin-left:0;">'.($a_ProcedureNotes[$a_ProcedureTypes['Injunction']] ?? '').'</textarea>	
        </div>

        <div class="col-sm-1 BoxRowCaption" style="height:4.6rem;">
			<button disabled previous-note="'.htmlspecialchars($a_ProcedureNotes[$a_ProcedureTypes['Injunction']] ?? '').'" previous-value="'.$str_InjunctionProcedure.'" data-toggle="tooltip" data-container="body" data-placement="right" title="Modifica" data-fineid="'. $Id .'" data-field="Injunction" class="tooltip-r btn btn-primary procedure" style="width:100%;height:100%;padding:0;margin:0"><i class="fa fa-save"></i></button>
        </div>    

        <div class="clean_row HSpace16"></div>

        <div class="col-sm-12 table_label_H">
            Chiudi il verbale
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <form name="f_FineOpenClose" id="f_FineOpenClose" method="post" action="mgmt_fine_act_exe.php">
            <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
            <input type="hidden" name="Id" value="'. $Id .'">
            <input type="hidden" name="Operation" value="Close">
            <div class="col-sm-2 BoxRowLabel" style="height:5.6rem;">
                Note chiusura
            </div>
            <div class="col-sm-8 BoxRowCaption" style="height:5.6rem;">
                <textarea name="NoteProcedure" class="form-control frm_field_string" style="height:5rem;margin-left:0;">'.$r_Fine['NoteProcedure'].'</textarea>	
            </div>
            <div class="col-sm-2 BoxRowCaption text-center" style="height:5.6rem;">
                <button id="btn_CloseFine" class="btn btn-danger" style="width:100%;height:100%;padding:0">Chiudi verbale</button>
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



