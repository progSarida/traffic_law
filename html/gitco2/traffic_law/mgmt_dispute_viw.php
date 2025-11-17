<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$DisputeId= CheckValue('Id','n');
$FineId= CheckValue('FineId','n');

//$disputeFineListView = new CLS_VIEW(MGMT_DISPUTE_FINE_TRESPASSER);
//$disputeFineListView->where = "FD.DisputeId=".$DisputeId;
//$rs_DisputeFineList = $rs->getResults($rs->ExecuteQuery($disputeFineListView->generateSelect()));

$a_GradeType = array("","I","II","III");

$str_Front = "";

$rs= new CLS_DB();
$rs->SetCharset('utf8');

$disputeView = new CLS_VIEW(MGMT_DISPUTE);
$disputeView->where = "D.Id=" . $DisputeId." AND F.Id=".$FineId;
$rs_Dispute= $rs->getResults($rs->ExecuteQuery($disputeView->generateSelect(null,null, "GradeTypeId ASC")));

$fineDisputeView = new CLS_VIEW(MGMT_DISPUTE);
$rs_FineDispute = $rs->getResults($rs->ExecuteQuery($fineDisputeView->generateSelect("D.Id=" . $DisputeId)));

$str_out.='<div class="col-sm-12 BoxRowLabel" style="font-weight:bold;font-size:1.5rem;text-align:center; height:2.4rem;">
                Verbali
                <i class="fa fa-angle-down" id="showFine" style="cursor: pointer;"></i>
            </div>
            
            <div class="col-sm-12 fineDiv" style="display: none;">
                <div class="clean_row HSpace48"></div>
            ';

$fineFeeView = new CLS_VIEW(FINE_FEE);
foreach ($rs_FineDispute as $r_FineDispute){

    $fineFeeView->where = "F.Id=".$r_FineDispute['FineId']." GROUP BY FA.FineId, FA.ArticleId, FH.NotificationFee, FH.ResearchFee";
    $r_FineFee = $rs->getArrayLine($rs->ExecuteQuery($fineFeeView->generateSelect()));
    $NameOut = substr($r_FineDispute['CompanyName']." ".$r_FineDispute['Surname']." ".$r_FineDispute['Name'],0,25);
    $str_out.='
               
                    <div class="BoxRowCaption col-sm-2" style="font-weight:bold;font-size:1.2rem;">'.$NameOut.'</div>
                    <div class="BoxRowLabel col-sm-1" style="font-weight:bold;font-size:1.2rem;">Verbale</div>
                    <div class="BoxRowCaption col-sm-1" style="font-weight:bold;font-size:1.2rem;">'.$r_FineDispute['ProtocolId'].' / '.$r_FineDispute['ProtocolYear'].'</div>			
                    <div class="BoxRowLabel col-sm-1" style="font-weight:bold;font-size:1.2rem;">Targa</div>
                    <div class="BoxRowCaption col-sm-1" style="font-weight:bold;font-size:1.2rem;">'.$r_FineDispute['VehiclePlate'].'</div>
                    <div class="BoxRowLabel col-sm-1" style="font-weight:bold;font-size:1.2rem;">Entro 5 gg</div>
                    <div class="BoxRowCaption col-sm-1" style="font-size:1.2rem;">'.number_format($r_FineFee['ReducedFee'],2).' &euro;</div>
                    <div class="BoxRowLabel col-sm-1" style="font-weight:bold;font-size:1.2rem;">Entro 60 gg</div>
                    <div class="BoxRowCaption col-sm-1" style="font-size:1.2rem;">'.number_format($r_FineFee['TotalFee'],2).' &euro;</div>
                    <div class="BoxRowLabel col-sm-1" style="font-weight:bold;font-size:1.2rem;">Oltre 60 gg</div>
                    <div class="BoxRowCaption col-sm-1" style="font-size:1.2rem;">'.number_format($r_FineFee['TotalMaxFee'],2).' &euro;</div>  
                    <div class="clean_row HSpace4"></div>
                
            ';
}

$grades = count($rs_Dispute);
$hearing_number = 1;
$amount_number = 1;
foreach($rs_Dispute as $r_Dispute){
    $GradeTypeId = $r_Dispute['GradeTypeId'];

    //DOCUMENTI
    if($GradeTypeId==1){

        $str_Folder = ($r_Dispute['CountryId']=='Z000') ? 'doc/national/dispute' : 'doc/foreign/dispute';

        $str_tree = "";
        $str_Img = "";
        $doc_rows = $rs->Select('DisputeDocumentation',"FineId=" . $r_Dispute['FineId'], "Id");
        $doc_n = mysqli_num_rows($doc_rows);

        if($doc_n>0){

            $doc_row = mysqli_fetch_array($doc_rows);

            $File = $str_Folder.'/'.$_SESSION['cityid'].'/'.$FineId.'/'.$doc_row['Documentation'];
            if (strtolower(substr($doc_row['Documentation'],-3))=="jpg" || strtolower(substr($doc_row['Documentation'],-3))=="png") {
                $str_Img = ' 
                    $("#preview").attr("src","' . $File . '");
                    $("#preview_img").show();
                ';
            }else{
                $str_Img = '
                    $("#preview_doc").html("<object><embed width=\"100%\" height=\"100%\" src=\"' . $File . '\" type=\"application/pdf\" /></object>");
                    $("#preview_doc").show();
                ';
            }

            $str_tree ='
                    $("#fileTreeDemo_1").fileTree({ root:\''.$str_Folder.'/'.$_SESSION['cityid'].'/'.$r_Dispute['FineId'].'/\', script: \'jqueryFileTree.php\' }, function(file) {
                    var FileType = file.substr(file.length - 3);
        
                    if(FileType.toLowerCase()==\'pdf\' || FileType.toLowerCase()==\'doc\'){
                        $("#preview_img").hide();
                        $("#preview_doc").html("<iframe style=\"width:100%; height:100%\" src=\'"+file+"\'></iframe>");
                        $("#preview_doc").show();
                    }else{
                        $("#preview_doc").hide();
                        $("#preview").attr("src",file);
                        $("#preview_img").show();
                    }
                });
            ';
        }

        $str_out .= '    </div>
            <div class="clean_row HSpace48"></div>
            <div class="col-sm-12 BoxRowLabel" style="font-weight:bold;font-size:1.5rem;text-align:center; height:2.4rem;">
                Documenti
                <i class="fa fa-angle-down" id="showDocs" style="cursor: pointer;"></i>
            </div>
            <div class="clean_row HSpace48"></div>
            <div class="col-sm-12 BoxRow docsDiv" style="height:60rem; display: none;">
                <div class="col-sm-2" style="height:60rem;">
                    
                    <div style="margin-bottom:20px;padding:5px;max-height: 35rem; overflow: auto;">
                        <div class="example">
                            <div id="fileTreeDemo_1" ></div>
                        </div>
                    </div>                   
                    <div style="font-size:1.2rem;">

                    </div>
                
                </div>
                
                <div class="col-sm-10">
                    <div class="col-sm-12" style="height:60rem; width:100%;display: flex; justify-content: center;">
                        <div id="preview_img" style="width:100%; display: none; overflow: auto">
                            <img id="preview" class="iZoom"/>
                        </div>
                        <div id="preview_doc" style="width:100%;display: none;"></div>
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace48 docsDiv" style="display: none;"></div>
            ';

    }

    $DateMerit = $r_Dispute['DateMerit'];

    $a_suspension = array(1=>"SI",0=>"NO");
    $a_ownerPresentation = array(0=>"Trasgressore",1=>"Ente Emittente");
    //ARRAY PER SELECT Office
    $a_temp = $rs->getResults($rs->ExecuteQuery("SELECT * FROM Office"));
    foreach ($a_temp as $arrayOffice){
        if($arrayOffice['Id']>0)
            $a_office[$arrayOffice['Id']] = $arrayOffice['TitleIta'];
    }
    ;
        $angleIcon = "fa fa-angle-down";
        $displayDispute = "display:none;";
        $disabled = "disabled";

        $officeInput = $a_office[$r_Dispute['OfficeId']];
        $FineSuspensionInput = $a_suspension[$r_Dispute['FineSuspension']];
        $ownerPresentationInput = $a_ownerPresentation[$r_Dispute['OwnerPresentation']];

    $str_out .= '
        <div class="col-sm-12 BoxRowLabel" style="font-weight:bold;font-size:1.5rem;text-align:center; height:2.4rem;">
            Grado '.$a_GradeType[$r_Dispute['GradeTypeId']].'
            <i class="'.$angleIcon.'" id="showGrade'.$r_Dispute['GradeTypeId'].'" style="cursor: pointer;"></i>
        </div>
        <div class="col-sm-12 disputeDiv'.$r_Dispute['GradeTypeId'].'" style="'.$displayDispute.'">
        <form name="frm_dispute_upd'.$r_Dispute['GradeTypeId'].'" id="frm_dispute_upd'.$r_Dispute['GradeTypeId'].'" method="post" action="mgmt_dispute_upd_exe.php">
        
        <input type="hidden" name="FineId" value="'.$r_Dispute['FineId'].'" />
        <input type="hidden" name="DisputeId" value="'.$r_Dispute['DisputeId'].'" />
        <input type="hidden" name="GradeTypeId" value="'.$r_Dispute['GradeTypeId'].'" />
        
        <div class="clean_row HSpace48"></div>
        <div class="col-sm-12 ">
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Registrazione
            </div>          
            <div class="col-sm-1 BoxRowCaption">
                '.DateOutDB($r_Dispute['RegDate']).'
            </div> 
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Ufficio giudicante
            </div>          
            <div class="col-sm-2 BoxRowCaption" >
                '.$officeInput.'
            </div>  
            <div class="col-sm-1 BoxRowCaption">
                <input type="text" name="OfficeCity" value="'.$r_Dispute['OfficeCity'].'" '.$disabled.' style="width:95%">
            </div>
            <div  class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Presentazione ricorso
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.$ownerPresentationInput.'
            </div>
            <div  class="col-sm-3 BoxRowCaption">
                
            </div>
        </div>
        <div class="col-sm-12" >
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Altri dati ufficio
            </div>   
            <div class="col-sm-11 BoxRowCaption" style="height:7rem">
                <textarea name="OfficeAdditionalData" '.$disabled.' style="width:95%;height:5.5rem">'.$r_Dispute['OfficeAdditionalData'].'</textarea>
            </div>               
        </div>
        <div class="clean_row HSpace48"></div>
        <div class="col-sm-12 BoxRowCaption">
            <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">
                RICORSO
            </div> 
            <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Data Spedizione
            </div>          
            <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                <input type="text" name="DateSend" '.$disabled.' value="'.DateOutDB($r_Dispute['DateSend']).'" class="form-control frm_field_date" style="width:9rem" >
            </div>
            <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Data Ricezione
            </div>          
            <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                <input type="text" name="DateReceive"  '.$disabled.' value="'.DateOutDB($r_Dispute['DateReceive']).'" class="form-control frm_field_date" style="width:9rem">
            </div>
            <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Data Deposito
            </div>          
            <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                <input type="text" name="DateFile" '.$disabled.' value="'.DateOutDB($r_Dispute['DateFile']).'" class="form-control frm_field_date" style="width:9rem">
            </div>
        </div>
        <div class="col-sm-12 BoxRowCaption">
            <div class="col-sm-1 BoxRowCaption"></div>
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                RG
            </div>          
            <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                <input type="text" name="RGNumber" class="form-control frm_field_string" '.$disabled.' value="'.$r_Dispute['Number'].'" style="width:15rem">
            </div>                
             <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                SEZ
            </div>          
            <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                <input type="text" name="Division" class="form-control frm_field_string" '.$disabled.' value="'.$r_Dispute['Division'].'" style="width:15rem">
            </div>                
        </div>
        <div class="col-sm-12 BoxRowCaption">
            <div class="col-sm-1 BoxRowCaption"></div>
            <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Data provvedimento
            </div>          
            <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                <input type="text" name="DateMeasure" '.$disabled.' value="'.DateOutDB($r_Dispute['DisputeDateMeasure']).'" class="form-control frm_field_date" style="width:9rem">
            </div>            
            <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Numero provvedimento
            </div>          
            <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                <input type="text" name="MeasureNumber" '.$disabled.' value="'.$r_Dispute['MeasureNumber'].'" class="form-control frm_field_string" style="width:15rem">
            </div>            
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Sospensione verbale
            </div>          
            <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                '.$FineSuspensionInput.'
            </div> 
        </div>
        
        <div class="clean_row HSpace48"></div>
        <div class="col-sm-12 BoxRowCaption">
            <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">
                ENTE
            </div> 
            <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Data Protocollo
            </div>          
            <div class="col-sm-1 BoxRowCaption">
                <input type="text" name="DateProtocolEntity" '.$disabled.' value="'.DateOutDB($r_Dispute['DateProtocolEntity']).'" class="form-control frm_field_date" style="width:9rem">
            </div>
            <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Numero protocollo
            </div>          
            <div class="col-sm-2 BoxRowCaption">
                <input type="text" name="EntityProtocolNumber" '.$disabled.' value="'.$r_Dispute['EntityProtocolNumber'].'" class="form-control frm_field_string" style="width:15rem">
            </div>                             
        </div>
        <div class="clean_row HSpace48"></div>
        <div class="col-sm-12 BoxRowCaption">
            <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">
                PRESA IN CARICO
            </div> 
              
            <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Data Protocollo
            </div>          
            <div class="col-sm-1 BoxRowCaption">
                <input type="text" name="DateProtocol" '.$disabled.' value="'.DateOutDB($r_Dispute['DateProtocol']).'"class="form-control frm_field_date" style="width:9rem">
            </div>
            <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Numero protocollo
            </div>          
            <div class="col-sm-2 BoxRowCaption">
                <input type="text" name="ProtocolNumber" '.$disabled.' value="'.$r_Dispute['ProtocolNumber'].'"class="form-control frm_field_string" style="width:15rem">
            </div>                        
        </div>       
        <div class="clean_row HSpace48"></div>
    ';




    $str_out .= '
        
            <div class="col-sm-12 BoxRowLabel" style="font-weight:bold;font-size:1.3rem;text-align:center">
                Udienze
            </div>
        
            <div class="clean_row HSpace16"></div>
    ';

    $a_disputeDate = $rs->getResults(
        $rs->ExecuteQuery(
            "SELECT * FROM DisputeDate WHERE DisputeId = ".$DisputeId." AND GradeTypeId=".$r_Dispute['GradeTypeId']." ORDER BY Id ASC"
        )
    );

    $a_HearingType = array("Prima comparizione","Sospensione termini");

    //ARRAY PER SELECT DisputeAct
    $a_temp = $rs->getResults($rs->ExecuteQuery("SELECT * FROM DisputeAct"));
    foreach ($a_temp as $arrayAct){
        if($arrayAct['Id']>0)
            $a_disputeAct[$arrayAct['Id']] = $arrayAct['Title'];
    }

    //ARRAY PER SELECT DisputeResult
    $a_temp = $rs->getResults($rs->ExecuteQuery("SELECT * FROM DisputeResult"));
    foreach ($a_temp as $arrayResult){
        if($arrayResult['Id']>0)
            $a_disputeResult[$arrayResult['Id']] = $arrayResult['Title'];
    }

    //NUMERO UDIENZA RIFERITA AL GRADO DEL RICORSO
    $countHearing = 1;

    if(count($a_disputeDate)==0)
        $lastJudgment = 1;
    //UDIENZE
    foreach($a_disputeDate as $r_DisputeDate){

            $HearingTypeInput = $r_DisputeDate['TypeHearing'];
            $disputeActInput = $a_disputeAct[$r_DisputeDate['DisputeActId']];
            $disputeResultInput = $a_disputeResult[$r_DisputeDate['DisputeResultId']];


        if($r_DisputeDate['DisputeResultId']>1)
            $showMeasure = "";
        else
            $showMeasure = "display:none;";
        if($r_DisputeDate['Amount']>0) {
            $oldAmount = '<div class="col-sm-1 BoxRowLabel" style="font-size:1.2rem">
                Importo gestione vecchia
            </div>  
            <div class="col-sm-1 BoxRowCaption">
                ' . $r_DisputeDate['Amount'] . ' &euro;
            </div>';
        }
        else
            $oldAmount = "";

        $str_out .= '       
            <div class="clean_row HSpace16"></div>   
            <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">
                UDIENZA '.$countHearing.'
                <input type="hidden" name="DisputeHearingId['.$countHearing.']" value="'. $r_DisputeDate['Id'] .'">
            </div> 
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Data udienza
            </div>          
            <div class="col-sm-1 BoxRowCaption" >
                <input type="text" name="DateHearing['.$countHearing.']" '.$disabled.' id="DateHearing_'.$hearing_number.'" value="'.DateOutDB($r_DisputeDate['DateHearing']).'" class="form-control frm_field_date" style="width:9rem">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Ora udienza
            </div>                  
            <div class="col-sm-1 BoxRowCaption" >
                <input type="text" name="TimeHearing['.$countHearing.']" '.$disabled.' id="TimeHearing_'.$hearing_number.'" value="'.TimeOutDB($r_DisputeDate['TimeHearing']).'" class="form-control frm_field_time" style="width:6rem">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Tipo
            </div>     
             <div class="col-sm-2 BoxRowCaption">
                '. $HearingTypeInput .'
            </div>  
            <div class="col-sm-4 BoxRowCaption"></div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem"></div> 
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Atto
            </div>  
            <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
            '. $disputeActInput .'

            </div>                
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Esito
            </div>  
            <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
            '. $disputeResultInput .'
            </div>       
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Note aggiuntive
            </div>  
            <div class="col-sm-5 BoxRowCaption" style="font-size:1.2rem">
                <input class="form-control frm_field_string" '.$disabled.' value="'. $r_DisputeDate['Note'] .'" type="text" name="Note['.$countHearing.']" id="Note_'.$hearing_number.'" style="width:100%">
            </div>  
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12 BoxRow" id="judgment_'.$hearing_number.'" style="'.$showMeasure.'">
                <div class="col-sm-1" style="font-weight:bold;font-size:1.2rem"></div> 
                <div class="col-sm-1 BoxRowLabel" style="font-size:1.2rem">
                    N. atto
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" '.$disabled.' value="'. $r_DisputeDate['Number'] .'" type="text" name="Number['.$countHearing.']" id="Number_'.$hearing_number.'" style="width:100%">
                </div> 
                <div class="col-sm-1 BoxRowLabel" style="font-size:1.2rem">
                    Data Provvedimento
                </div>    
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' value="'.DateOutDB($r_DisputeDate['DateMeasure']).'" name="HearingDateMeasure['.$countHearing.']" id="HearingDateMeasure_'.$hearing_number.'" class="ws-date" style="width:9rem">
                </div>  
                <div class="col-sm-1 BoxRowLabel" style="font-size:1.2rem">
                    Data deposito atto
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' value="'.DateOutDB($r_DisputeDate['DateAction']).'" name="DateAction['.$countHearing.']" id="DateAction_'.$hearing_number.'" class="ws-date" style="width:9rem">
                </div>    
                <div class="col-sm-1 BoxRowLabel" style="font-size:1.2rem">
                    Data notifica
                </div>  
                <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' name="DateNotification['.$countHearing.']" id="DateNotification_'.$hearing_number.'" value="'.DateOutDB($r_DisputeDate['DateNotification']).'" style="width:9rem">
                </div>
                '.$oldAmount.'
            </div>
            <div class="clean_row HSpace4"></div>
        ';
        $hearing_number++;
        $countHearing++;

        $lastJudgment = $r_DisputeDate['DisputeResultId'];
        $lastDisputeDateId = $r_DisputeDate['Id'];
    }

    if($DateMerit != ""){

        $disputeAmountView = new CLS_VIEW(MGMT_FINE_DISPUTE_AMOUNT);
//        $disputeAmountView->where = "DA.DisputeDateId=" . $lastDisputeDateId." AND DA.GradeTypeId=".$GradeTypeId;
        $rs_DisputeAmount = $rs->getResults($rs->ExecuteQuery("SELECT * FROM DisputeAmount WHERE DisputeDateId=" . $lastDisputeDateId));

        $disputeAmountView->where = "DD.Id=" . $lastDisputeDateId." AND DD.GradeTypeId=".$GradeTypeId;
        $rs_FineDisputeAmount = $rs->getResults($rs->ExecuteQuery($disputeAmountView->generateSelect()));

            $addIcon = '';

        $str_out .= '
            <div class="clean_row HSpace16"></div>
            <div class="col-sm-12 BoxRowLabel" style="font-weight:bold;font-size:1.3rem;text-align:center">
                Importi
                '.$addIcon.'
            </div>        
            <div class="clean_row HSpace16"></div>
            <div class="col-sm-6" style="border:4px solid #6397e2; max-height:30rem;overflow:auto">
            <input type="hidden" name="LastDisputeDateId" value="'.$lastDisputeDateId.'">
        ';

        $countAmount = 1;
        foreach ($rs_DisputeAmount as $r_DisputeAmount){

                $deleteIcon = '';

            $str_out.='
                <div style="" id="disputeAmount_'.$amount_number.'">
                    <div class="table_caption_H col-sm-2" style="font-weight:bold;font-size:1.2rem;">
                        IMPORTO '.$countAmount.'
                        <input type="hidden" name="DisputeAmountId['.$countAmount.']" value="'.$r_DisputeAmount['Id'].'">
                    </div> 
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_numeric" type="text" '.$disabled.' value="'.$r_DisputeAmount['Amount'].'"
                        name="DisputeAmount['.$countAmount.']" style="width:15rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">Note</div>
                    <div class="col-sm-6 BoxRowCaption" style="display: flex;">
                        <input class="form-control frm_field_string" type="text" '.$disabled.' value="'.$r_DisputeAmount['Note'].'"
                        name="DisputeAmountNotes['.$countAmount.']" style="width:95%">
                        &nbsp;&nbsp;
                        '.$deleteIcon.'
                    </div>
                    <div class="clean_row HSpace4"></div>
                </div>
            ';

            $countAmount++;
            $amount_number++;
        }

        //ARRAY PER SELECT Office
        $a_fineAmount = array();
        $a_temp = $rs->getResults($rs->ExecuteQuery("SELECT * FROM DisputeAmount WHERE DisputeDateId = ".$lastDisputeDateId));
        $countRow = 0;
        foreach ($a_temp as $arrayFineDisputeAmount){
            $countRow++;
            if($arrayFineDisputeAmount['Amount']>0)
                $a_fineAmount[$arrayFineDisputeAmount['Id']] = "IMPORTO ".$countRow;
        }

        $str_out.='</div>
            <div class="col-sm-6" style="border:4px solid #6397e2; max-height:30rem;overflow:auto">
            ';
        $countFine = 1;
        foreach ($rs_FineDisputeAmount as $r_FineDispute){

                $inputFineHidden = "";
                $selectFineAmount = "<b>".$a_fineAmount[$r_FineDispute['DisputeAmountId']]."</b>";


            $NameOut = substr($r_FineDispute['CompanyName']." ".$r_FineDispute['Surname']." ".$r_FineDispute['Name'],0,25);
            $str_out.='
                <div>
                    <div class="table_caption_H col-sm-2" style="font-weight:bold;font-size:1.2rem;">'.$NameOut.'</div>
                    <div class="BoxRowLabel col-sm-2" style="font-size:1.2rem;"><b>Verbale</b></div>
                    <div class="table_caption_H col-sm-2" style="font-size:1.2rem;">'.$r_FineDispute['ProtocolId'].' / '.$r_FineDispute['ProtocolYear'].'</div>			
                    <div class="BoxRowLabel col-sm-2" style="font-size:1.2rem;"><b>Targa</b></div>
                    <div class="table_caption_H col-sm-2" style="font-size:1.2rem;">'.$r_FineDispute['VehiclePlate'].'</div>
                    <div class="BoxRowCaption col-sm-2">
                        '.$inputFineHidden.' 
                        '.$selectFineAmount.'
                    </div>
                    <div class="clean_row HSpace4"></div>
                </div>
            ';

            $countFine++;
        }


        $str_out.="</div>";
    }

    $hearing_number++;

    $str_out.='
      	    </form>
      	</div>
      	<div class="clean_row HSpace48"></div>
    ';

}
$str_out.='
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">

                <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                    <input type="button" class="btn btn-default back" style="margin-top:1rem;" value="Indietro" />
                </div>
             </div>
        </div>';

echo $str_out;

?>

    <script src="<?= LIB ?>/upload/js/jquery.ui.widget.js"></script>
    <script src="<?= LIB ?>/upload/js/jquery.iframe-transport.js"></script>
    <script src="<?= LIB ?>/upload/js/jquery.fileupload.js"></script>

    <!-- Our main JS file -->
    <script src="<?= LIB ?>/upload/js/script.js"></script>


    <script>

        $('document').ready(function(){

            var height = $( '#firstCol' ).height();
            $( '#secondCol' ).height(height);
            $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});

            <?= $str_tree ?>
            <?= $str_Img ?>


            $('.back').click(function(){
                window.location="mgmt_dispute.php";
            });

            $('#showFine').click(function () {
                if($(this).attr('class')=="fa fa-angle-down"){
                    $(this).attr('class','fa fa-angle-up');
                }
                else{
                    $(this).attr('class','fa fa-angle-down');
                }
                $('.fineDiv').toggle();

            });

            $('#showDocs').click(function () {
                if($(this).attr('class')=="fa fa-angle-down"){
                    $(this).attr('class','fa fa-angle-up');
                }
                else{
                    $(this).attr('class','fa fa-angle-down');
                }
                $('.docsDiv').toggle();

            });

            $('#showGrade1').click(function () {
                if($(this).attr('class')=="fa fa-angle-down"){
                    $(this).attr('class','fa fa-angle-up');
                }
                else{
                    $(this).attr('class','fa fa-angle-down');
                }
                $('.disputeDiv1').toggle();

            });

            $('#showGrade2').click(function () {
                if($(this).attr('class')=="fa fa-angle-down"){
                    $(this).attr('class','fa fa-angle-up');
                }
                else{
                    $(this).attr('class','fa fa-angle-down');
                }
                $('.disputeDiv2').toggle();

            });

            $('#showGrade3').click(function () {
                if($(this).attr('class')=="fa fa-angle-down"){
                    $(this).attr('class','fa fa-angle-up');
                }
                else{
                    $(this).attr('class','fa fa-angle-down');
                }

                $('.disputeDiv3').toggle();

            });
        });

    </script>

<?php

include(INC."/footer.php");

