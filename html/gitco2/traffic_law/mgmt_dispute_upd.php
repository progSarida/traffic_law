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
$disputeView->groupBy = "D.Id, D.GradeTypeId, T.Id";
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
                &nbsp;&nbsp;
                <i class="fa fa-plus" id="newDoc" style="cursor: pointer;" title="Crea nuovo documento"></i>
            </div>
            <div class="clean_row HSpace48"></div>
            <div class="col-sm-12 BoxRow docsDiv" style="height:60rem; display: none;">
                <div class="col-sm-5" style="height:60rem;">
                    
                    <div class="center-block" style="margin-bottom: 2rem;">
                        <div class="example" class="center-block">
                            <div id="fileTreeDemo_1" ></div>
                        </div>
                        <button id=btn_sana class="btn btn-success center-block" title="Acquisisci documenti dal SAN.A">SAN.A</button>
                    </div>                   
                    <div style="font-size:1.2rem;" class="center-block">
                    
                        <form name="frm_upl" id="upload" method="post" action="mgmt_dispute_upl_exe.php" enctype="multipart/form-data">
                            <input type="hidden" name="FineId" value="'.$r_Dispute['FineId'].'">
                            <input type="hidden" name="CountryId" value="'.$r_Dispute['CountryId'].'">
                            <input type="hidden" name="DisputeId" value="'.$r_Dispute['DisputeId'].'">
                            
                            <div id="drop" class="center-block">             
                                <a>Upload</a>
                                <input type="file" name="upl" multiple />
                            </div>
                    
                            <ul class="center-block">
                                <!-- The file uploads will be shown here -->
                            </ul>
                            
                        </form>
                    </div>
                
                </div>
                
                <div class="col-sm-7">
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

    $a_suspension = array(1=>"SEMPRE",0=>"SOLO SE SOSPENSIVA PRESENTE");
    $a_ownerPresentation = array(0=>"Trasgressore",1=>"Ente Emittente");
    //ARRAY PER SELECT Office
    $a_temp = $rs->getResults($rs->ExecuteQuery("SELECT * FROM Office"));
    foreach ($a_temp as $arrayOffice){
        if($arrayOffice['Id']>0)
            $a_office[$arrayOffice['Id']] = $arrayOffice['TitleIta'];
    }
    ;
    if($grades>$r_Dispute['GradeTypeId']){
        $angleIcon = "fa fa-angle-down";
        $displayDispute = "display:none;";
        $disabled = "disabled";

        $officeInput = $a_office[$r_Dispute['OfficeId']];
        $FineSuspensionInput = $a_suspension[$r_Dispute['FineSuspension']];
        $ownerPresentationInput = $a_ownerPresentation[$r_Dispute['OwnerPresentation']];
    }
    else{
        $angleIcon = "fa fa-angle-up";
        $displayDispute = "";
        $disabled = "";

        $officeInput = CreateSelect("Office",null,null,"OfficeId","Id","TitleIta",$r_Dispute['OfficeId'],true);
        $ownerPresentationInput = CreateArraySelect(
            $a_ownerPresentation,
            true,"OwnerPresentation","OwnerPresentation",$r_Dispute['OwnerPresentation'],true);
        $FineSuspensionInput = CreateArraySelect($a_suspension,true,"FineSuspension","FineSuspension",$r_Dispute['FineSuspension'],true);
    }

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
            <div class="col-sm-2 BoxRowCaption" style="font-weight:bold;font-size:1.2rem">

            </div> 
        </div>
         <div class="clean_row HSpace48"></div>
        <div class="col-sm-12 BoxRowCaption">
            <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">
                SOSPENSIVA
            </div> 
            
            <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Data
            </div>          
            <div class="col-sm-1 BoxRowCaption">
                <input type="text" name="SuspensiveDate" '.$disabled.' value="'.DateOutDB($r_Dispute['SuspensiveDate']).'" class="form-control frm_field_date" style="width:9rem">
            </div>
            <div class="col-sm-2 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Numero
            </div>          
            <div class="col-sm-2 BoxRowCaption">
                <input type="text" name="SuspensiveNumber" '.$disabled.' value="'.$r_Dispute['SuspensiveNumber'].'" class="form-control frm_field_string" style="width:15rem">
            </div>   
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Sospensione verbale
            </div>          
            <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
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

    $a_HearingType = array("Prima comparizione","Sospensione termini","No udienza");

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
        $a_LosingParty = $rs->getArrayLine(
            $rs->ExecuteQuery(
                "SELECT * FROM DisputeInvoice WHERE DisputeId = ".$DisputeId." AND DisputeDateId=".$r_DisputeDate['Id']." AND Type=1 ORDER BY Type ASC"
            )
        );

        $a_Parcel = $rs->getArrayLine(
            $rs->ExecuteQuery(
                "SELECT * FROM DisputeInvoice WHERE DisputeDateId=".$r_DisputeDate['Id']." AND Type=2 ORDER BY Type ASC"
            )
        );

        if($disabled==""){
            $HearingTypeInput = CreateArraySelect($a_HearingType,false,"TypeHearing[".$countHearing."]","TypeHearing_".$hearing_number,$r_DisputeDate['TypeHearing'],true,null);
            $disputeActInput = CreateArraySelect($a_disputeAct,true,"DisputeActId[".$countHearing."]","DisputeActId_".$hearing_number,$r_DisputeDate['DisputeActId'],false);
            $disputeResultInput = CreateArraySelect($a_disputeResult,true,"DisputeResultId[".$countHearing."]","DisputeResultId_".$hearing_number,$r_DisputeDate['DisputeResultId'],false,null,"judgment");
            $LosingPartyInput = CreateArraySelect(array(0=>"No",1=>"Si"),true,"LosingParty[".$countHearing."]","LosingParty_".$hearing_number,$r_DisputeDate['LosingParty'],true,8, "losingparty");
            $ParcelInput = CreateArraySelect(array(0=>"No",1=>"Si"),true,"Parcel[".$countHearing."]","Parcel_".$hearing_number,$r_DisputeDate['Parcel'],true,8, "parcel");
        }else{
            $HearingTypeInput = $r_DisputeDate['TypeHearing'];
            $disputeActInput = $a_disputeAct[$r_DisputeDate['DisputeActId']];
            $disputeResultInput = $a_disputeResult[$r_DisputeDate['DisputeResultId']];
            $LosingPartyInput = "No";
            $ParcelInput = "No";
        }

        if($r_DisputeDate['LosingParty']==1)
            $showLosingParty = "";
        else
            $showLosingParty = "display:none;";
        if($r_DisputeDate['Parcel']==1)
            $showParcel = "";
        else
            $showParcel = "display:none;";

        if($r_DisputeDate['DisputeResultId']>1)
            $showMeasure = "";
        else
            $showMeasure = "display:none;";

        if($r_DisputeDate['Amount']>0) {
            $oldAmount = '<div class="col-sm-2 BoxRowLabel" style="font-size:1.2rem">Importo gestione vecchia</div>  
                <div class="col-sm-5 BoxRowCaption">' . $r_DisputeDate['Amount'] . ' &euro;</div>';
        }
        else
            $oldAmount = '<div class="col-sm-7 BoxRowCaption"></div>';

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
                <input type="text" name="TimeHearing['.$countHearing.']" '.$disabled.' id="TimeHearing_'.$hearing_number.'" value="'.( (!is_null($r_DisputeDate['TimeHearing'])) ? TimeOutDB($r_DisputeDate['TimeHearing']) : null ).'" class="form-control frm_field_time" style="width:6rem">
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
                Tipo Atto
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
            <div class="clean_row HSpace16"></div>
            <div class="col-sm-12 judgment_'.$hearing_number.'" style="'.$showMeasure.'">
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">ATTO</div>
                 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data
                </div>    
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' value="'.DateOutDB($r_DisputeDate['DateMeasure']).'" name="HearingDateMeasure['.$countHearing.']" id="HearingDateMeasure_'.$hearing_number.'" class="ws-date" style="width:9rem">
                </div> 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Numero
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" '.$disabled.' value="'. $r_DisputeDate['Number'] .'" type="text" name="Number['.$countHearing.']" id="Number_'.$hearing_number.'" style="width:100%">
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Soccombenza
                </div>
                <div class="col-sm-1 BoxRowCaption">'.$LosingPartyInput.'</div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Parcella
                </div>
                <div class="col-sm-3 BoxRowCaption">'.$ParcelInput.'</div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data deposito
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' value="'.DateOutDB($r_DisputeDate['DateAction']).'" name="DateAction['.$countHearing.']" id="DateAction_'.$hearing_number.'" class="ws-date" style="width:9rem">
                </div>    
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data notifica
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' name="DateNotification['.$countHearing.']" id="DateNotification_'.$hearing_number.'" value="'.DateOutDB($r_DisputeDate['DateNotification']).'" style="width:9rem">
                </div>
                '.$oldAmount.'
            </div>
            
            <div class="col-sm-12" id="losingpartydiv_'.$hearing_number.'" style="'.$showLosingParty.'">
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">SOCCOMBENZA
                <input type="hidden" name="LosingPartyId['.$countHearing.']" value="'.$a_LosingParty['Id'].'">
                </div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Parte
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                
                '.CreateArraySelect(
                $a_ownerPresentation,
                true,"LosingSide[".$countHearing."]","LosingSide_".$hearing_number, $a_LosingParty['Side'],true,null,$disabled).'
                </div> 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data Pagamento
                </div>    
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' value="'.DateOutDB($a_LosingParty['PaymentDate']).'" name="LosingPaymentDate['.$countHearing.']" id="LosingPaymentDate_'.$hearing_number.'" style="width:9rem">
                </div>  
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Pagante
                </div>  
                <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" '.$disabled.' value="'.$a_LosingParty['Payer'].'" type="text" name="LosingPayer['.$countHearing.']" id="LosingPayer_'.$hearing_number.'" style="width:100%">
                </div>    
                <div class="col-sm-4 BoxRowCaption" style="font-size:1.2rem">
                </div>    
            </div>
            <div class="col-sm-12" id="parceldiv_'.$hearing_number.'" style="'.$showParcel.'">
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">PARCELLA
                <input type="hidden" name="ParcelId['.$countHearing.']" value="'.$a_Parcel['Id'].'">
                </div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data
                </div>   
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' value="'.DateOutDB($a_Parcel['Date']).'" name="ParcelDate['.$countHearing.']" id="ParcelDate_'.$hearing_number.'" style="width:9rem">
                </div>  
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Numero
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" '.$disabled.' value="'.$a_Parcel['Number'].'" type="text" name="ParcelNumber['.$countHearing.']" id="ParcelNumber_'.$hearing_number.'" style="width:100%">
                </div> 
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Parte
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                
                '.CreateArraySelect(
                $a_ownerPresentation,
                true,"ParcelSide[".$countHearing."]","ParcelSide_".$hearing_number,$a_Parcel['Side'],true,null,$disabled).'
                </div> 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Avvocato
                </div>  
                <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" '.$disabled.' value="'.$a_Parcel['Lawyer'].'" type="text" name="ParcelLawyer['.$countHearing.']" id="ParcelLawyer_'.$hearing_number.'" style="width:100%">
                </div>    
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem"></div>
                
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data Pagamento
                </div>    
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' value="'.DateOutDB($a_Parcel['PaymentDate']).'" name="ParcelPaymentDate['.$countHearing.']" id="ParcelPaymentDate_'.$hearing_number.'" style="width:9rem">
                </div>  
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Pagante
                </div>  
                <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" '.$disabled.' value="'.$a_Parcel['Payer'].'" type="text" name="ParcelPayer['.$countHearing.']" id="ParcelPayer_'.$hearing_number.'" style="width:100%">
                </div>    
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Note
                </div>  
                <div class="col-sm-5 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" '.$disabled.' value="'.$a_Parcel['Notes'].'" type="text" name="ParcelNotes['.$countHearing.']" id="ParcelNotes_'.$hearing_number.'" style="width:100%">
                </div>
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-1"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Diritti ed onorari
                </div> 
                <div class="col-sm-1 BoxRowCaption">
                    <input readonly type="text" id="ParcelFeeOtherExpenses_'.$hearing_number.'" class="form-control frm_field_currency" 
                    value="'.number_format($a_Parcel['Fee']+$a_Parcel['OtherExpenses'],2,".","").'" style="width:9rem;">
                </div> 
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Diritti
                </div> 
                <div class="col-sm-1 BoxRowCaption">
                    <input type="text" id="ParcelFee_'.$hearing_number.'" name="ParcelFee['.$countHearing.']" class="form-control frm_field_currency parcelCalc" 
                    value="'.number_format($a_Parcel['Fee'],2,".","").'" style="width:9rem;">
                </div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Onorari
                </div> 
                <div class="col-sm-3 BoxRowCaption">
                    <input type="text" id="ParcelOtherExpenses_'.$hearing_number.'" name="ParcelOtherExpenses['.$countHearing.']" class="form-control frm_field_currency parcelCalc" 
                    value="'.number_format($a_Parcel['OtherExpenses'],2,".","").'" style="width:9rem;">
                </div> 
                 
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem; text-align: center;"><i class="fa fa-plus" style="margin-top:0.5rem"></i></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Spese generali
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input readonly type="text" id="ParcelOverheads_'.$hearing_number.'" name="ParcelOverheads['.$countHearing.']" class="form-control frm_field_currency" 
                    value="'.number_format($a_Parcel['Overheads'],2,".","").'" style="width:9rem;">
                </div>
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-6 BoxRowCaption"></div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem; text-align: center;"><i class="fa fa-plus" style="margin-top:0.5rem"></i></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Cassa Avvocati
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input readonly type="text" id="ParcelLawyerFund_'.$hearing_number.'" name="ParcelLawyerFund['.$countHearing.']" class="form-control frm_field_currency" 
                    value="'.number_format($a_Parcel['LawyerFund'],2,".","").'" style="width:9rem;">
                </div>
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-6 BoxRowCaption"></div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem; text-align: center;"><i class="fa fa-plus" style="margin-top:0.5rem"></i></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    IVA
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input readonly type="text" id="ParcelVAT_'.$hearing_number.'" name="ParcelVAT['.$countHearing.']" class="form-control frm_field_currency" 
                    value="'.number_format($a_Parcel['VAT'],2,".","").'" style="width:9rem;">
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    '.CreateArraySelect(
                    array(0=>"",1=>"Esenzione"),
                    true,"ParcelVATExemption[".$countHearing."]","ParcelVATExemption_".$hearing_number,$a_Parcel['VATExemption'],true,null,"parcelCalc ".$disabled).'
                </div>
                <div class="col-sm-6 BoxRowCaption"></div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem; text-align: center;"><i class="fa fa-plus" style="margin-top:0.5rem"></i></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Spese Vive
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input readonly type="text" id="ParcelLivingExpenses_'.$hearing_number.'" class="form-control frm_field_currency" 
                    value="'.number_format($a_Parcel['CU']+$a_Parcel['RevenueStamp']+$a_Parcel['OtherLivingExpenses'],2,".","").'" style="width:9rem;">
                </div>
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    C.U.
                </div> 
                <div class="col-sm-1 BoxRowCaption">
                    <input type="text" id="ParcelCU_'.$hearing_number.'" name="ParcelCU['.$countHearing.']" class="form-control frm_field_currency parcelCalc" 
                    value="'.number_format($a_Parcel['CU'],2,".","").'" style="width:9rem;">
                </div> 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Marche
                </div> 
                <div class="col-sm-1 BoxRowCaption">
                    <input type="text" id="ParcelRevenueStamp_'.$hearing_number.'" name="ParcelRevenueStamp['.$countHearing.']" class="form-control frm_field_currency parcelCalc" 
                    value="'.number_format($a_Parcel['RevenueStamp'],2,".","").'" style="width:9rem;">
                </div> 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Altro
                </div> 
                <div class="col-sm-1 BoxRowCaption">
                    <input type="text" id="ParcelOtherCosts_'.$hearing_number.'" name="ParcelOtherCosts['.$countHearing.']" class="form-control frm_field_currency parcelCalc" 
                    value="'.number_format($a_Parcel['OtherLivingExpenses'],2,".","").'" style="width:9rem;">
                </div> 
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem; text-align: center;"><i class="fa fa-minus" style="margin-top:0.5rem"></i></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    R.A.
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input readonly type="text" id="ParcelRA_'.$hearing_number.'" name="ParcelRA['.$countHearing.']" class="form-control frm_field_currency" 
                    value="'.number_format($a_Parcel['RA'],2,".","").'" style="width:9rem;">
                </div>
                <div class="col-sm-1 BoxRowCaption">
                '.CreateArraySelect(
                    array(0=>"",1=>"Esenzione"),
                    true,"ParcelRAExemption[".$countHearing."]","ParcelRAExemption_".$hearing_number,$a_Parcel['RAExemption'],true,null,"parcelCalc ".$disabled).'
                </div>
                <div class="col-sm-6 BoxRowCaption"></div>
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-1 "></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem; text-align: center;"><i class="fas fa-equals" style="margin-top:0.5rem"></i></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    TOTALE
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input readonly type="text" id="ParcelTotal_'.$hearing_number.'" name="ParcelTotal['.$countHearing.']" class="form-control frm_field_currency" 
                    value="'.number_format($a_Parcel['Total'],2,".","").'" style="width:9rem;">
                </div>
            </div>
        ';
        $hearing_number++;
        $countHearing++;

        $lastJudgment = $r_DisputeDate['DisputeResultId'];
        $lastDisputeDateId = $r_DisputeDate['Id'];
    }

    if($lastJudgment!=1)
        $showNewHearing = "display: none;";
    else
        $showNewHearing = "";
    //NUOVA UDIENZA DA AGGIUNGERE IN BASE ALL'ESITO DELLA PRECEDENTE UDIENZA
    $str_out .= '       
        <div class="clean_row HSpace16"></div>   
        <div style="'.$showNewHearing.'" id="newHearing_'.$hearing_number.'">
            <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">
                UDIENZA '.$countHearing.'
                <input type="hidden" name="DisputeHearingId['.$countHearing.']">
            </div> 
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Data udienza
            </div>          
            <div class="col-sm-1 BoxRowCaption" >
                <input type="text" name="DateHearing['.$countHearing.']" '.$disabled.' id="DateHearing_'.$hearing_number.'" class="form-control frm_field_date" style="width:9rem">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Ora udienza
            </div>                  
            <div class="col-sm-1 BoxRowCaption" >
                <input type="text" name="TimeHearing['.$countHearing.']" '.$disabled.' id="TimeHearing_'.$hearing_number.'" class="form-control frm_field_time" style="width:6rem">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Tipo
            </div>     
             <div class="col-sm-2 BoxRowCaption">
                '. CreateArraySelect($a_HearingType,false,"TypeHearing[".$countHearing."]","TypeHearing_".$hearing_number,null,true) .'
            </div>  
            <div class="col-sm-4 BoxRowCaption"></div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem"></div>
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Tipo Atto
            </div>
            <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                '. CreateArraySelect($a_disputeAct,true,"DisputeActId[".$countHearing."]","DisputeActId_".$hearing_number,null,false) .'
            </div>
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Esito
            </div>
            <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                '. CreateArraySelect($a_disputeResult,true,"DisputeResultId[".$countHearing."]","DisputeResultId_".$hearing_number,null,false,null,"judgment") .'
            </div>
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Note aggiuntive
            </div>
            <div class="col-sm-5 BoxRowCaption" style="font-size:1.2rem">
                <input class="form-control frm_field_string" type="text" '.$disabled.' name="Note['.$countHearing.']" id="Note_'.$hearing_number.'" style="width:100%">
            </div>

            <div class="col-sm-12 judgment_'.$hearing_number.'" style="display:none;">
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem">ATTO</div> 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data
                </div>    
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' name="HearingDateMeasure['.$countHearing.']" id="HearingDateMeasure_'.$hearing_number.'" class="ws-date" style="width:9rem">
                </div>  
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Numero
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" type="text" name="Number['.$countHearing.']" id="Number_'.$hearing_number.'" style="width:100%">
                </div> 
                
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Soccombenza
                </div>
                <div class="col-sm-1 BoxRowCaption">'.CreateArraySelect(array(0=>"No",1=>"Si"),true,"LosingParty[".$countHearing."]","LosingParty_".$hearing_number,null,true,8, "losingparty").'</div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Parcella
                </div>
                <div class="col-sm-3 BoxRowCaption">'.CreateArraySelect(array(0=>"No",1=>"Si"),true,"Parcel[".$countHearing."]","Parcel_".$hearing_number,null,true,8, "parcel").'</div>
                
                <div class="clean_row HSpace4"></div>
                
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data deposito
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' name="DateAction['.$countHearing.']" id="DateAction_'.$hearing_number.'" class="ws-date" style="width:9rem">
                </div>    
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data notifica
                </div>  
                <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' name="DateNotification['.$countHearing.']" id="DateNotification_'.$hearing_number.'" style="width:9rem">
                </div>    
                <div class="col-sm-6 BoxRowCaption" style="font-size:1.2rem"></div>
            </div>
            
            <div class="col-sm-12" id="losingpartydiv_'.$hearing_number.'" style="display:none;">
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem">SOCCOMBENZA
                <input type="hidden" name="LosingPartyId['.$countHearing.']"></div> 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Parte
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                '.CreateArraySelect(
                    $a_ownerPresentation,true,"LosingSide[".$countHearing."]","LosingSide_".$hearing_number,
                    null,true,null,$disabled).'
                </div> 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data Pagamento
                </div>    
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' value="" name="LosingPaymentDate['.$countHearing.']" id="LosingPaymentDate_'.$hearing_number.'" style="width:9rem">
                </div>  
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Pagante
                </div>  
                <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" '.$disabled.' value="" type="text" name="LosingPayer['.$countHearing.']" id="LosingPayer_'.$hearing_number.'" style="width:100%">
                </div>    
                <div class="col-sm-4 BoxRowCaption" style="font-size:1.2rem">
                </div>    
            </div>
            <div class="col-sm-12" id="parceldiv_'.$hearing_number.'" style="display:none;">
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem">PARCELLA
                <input type="hidden" name="ParcelId['.$countHearing.']"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data
                </div>   
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' value="" name="ParcelDate['.$countHearing.']" id="ParcelDate_'.$hearing_number.'" style="width:9rem">
                </div>  
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Numero
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" '.$disabled.' value="" type="text" name="ParcelNumber['.$countHearing.']" id="ParcelNumber_'.$hearing_number.'" style="width:100%">
                </div> 
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Parte
                </div>  
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                
                '.CreateArraySelect(
            $a_ownerPresentation,
            true,"ParcelSide['.$countHearing.']","ParcelSide_".$hearing_number,null,true,null,$disabled).'
                </div> 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Avvocato
                </div>  
                <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" '.$disabled.' value="" type="text" name="ParcelLawyer['.$countHearing.']" id="ParcelLawyer_'.$hearing_number.'" style="width:100%">
                </div>    
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.3rem"></div>
                
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Data Pagamento
                </div>    
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_date" type="text" '.$disabled.' value="" name="ParcelPaymentDate['.$countHearing.']" id="ParcelPaymentDate_'.$hearing_number.'" style="width:9rem">
                </div>  
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Pagante
                </div>  
                <div class="col-sm-2 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" '.$disabled.' value="" type="text" name="ParcelPayer['.$countHearing.']" id="ParcelPayer_'.$hearing_number.'" style="width:100%">
                </div>    
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Note
                </div>  
                <div class="col-sm-5 BoxRowCaption" style="font-size:1.2rem">
                    <input class="form-control frm_field_string" '.$disabled.' value="" type="text" name="ParcelNotes['.$countHearing.']" id="ParcelNotes_'.$hearing_number.'" style="width:100%">
                </div>
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-1"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Diritti ed onorari
                </div> 
                <div class="col-sm-1 BoxRowCaption">
                    <input readonly type="text" id="ParcelFeeOtherExpenses_'.$hearing_number.'" class="form-control frm_field_currency" value="" style="width:9rem;">
                </div> 
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Diritti
                </div> 
                <div class="col-sm-1 BoxRowCaption">
                    <input type="text" id="ParcelFee_'.$hearing_number.'" name="ParcelFee['.$countHearing.']" class="form-control frm_field_currency parcelCalc" value="" style="width:9rem;">
                </div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Onorari
                </div> 
                <div class="col-sm-3 BoxRowCaption">
                    <input type="text" id="ParcelOtherExpenses_'.$hearing_number.'" name="ParcelOtherExpenses['.$countHearing.']" class="form-control frm_field_currency parcelCalc" value="" style="width:9rem;">
                </div> 
                 
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem; text-align: center;"><i class="fa fa-plus" style="margin-top:0.5rem"></i></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Spese generali
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input readonly type="text" id="ParcelOverheads_'.$hearing_number.'" name="ParcelOverheads['.$countHearing.']" class="form-control frm_field_currency" value="" style="width:9rem;">
                </div>
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-6 BoxRowCaption"></div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem; text-align: center;"><i class="fa fa-plus" style="margin-top:0.5rem"></i></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Cassa Avvocati
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input readonly type="text" id="ParcelLawyerFund_'.$hearing_number.'" name="ParcelLawyerFund['.$countHearing.']" class="form-control frm_field_currency" value="" style="width:9rem;">
                </div>
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-6 BoxRowCaption"></div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem; text-align: center;"><i class="fa fa-plus" style="margin-top:0.5rem"></i></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    IVA
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input readonly type="text" id="ParcelVAT_'.$hearing_number.'" name="ParcelVAT['.$countHearing.']" class="form-control frm_field_currency" value="" style="width:9rem;">
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    '.CreateArraySelect(
                    array(0=>"",1=>"Esenzione"),
                    true,"ParcelVATExemption[".$countHearing."]","ParcelVATExemption_".$hearing_number,null,true,null,"parcelCalc ".$disabled).'
                </div>
                <div class="col-sm-6 BoxRowCaption"></div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem; text-align: center;"><i class="fa fa-plus" style="margin-top:0.5rem"></i></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Spese Vive
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input readonly type="text" id="ParcelLivingExpenses_'.$hearing_number.'" class="form-control frm_field_currency" value="" style="width:9rem;">
                </div>
                <div class="col-sm-1 BoxRowCaption"></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    C.U.
                </div> 
                <div class="col-sm-1 BoxRowCaption">
                    <input type="text" id="ParcelCU_'.$hearing_number.'" name="ParcelCU['.$countHearing.']" class="form-control frm_field_currency parcelCalc" value="" style="width:9rem;">
                </div> 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Marche
                </div> 
                <div class="col-sm-1 BoxRowCaption">
                    <input type="text" id="ParcelRevenueStamp_'.$hearing_number.'" name="ParcelRevenueStamp['.$countHearing.']" class="form-control frm_field_currency parcelCalc" value="" style="width:9rem;">
                </div> 
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    Altro
                </div> 
                <div class="col-sm-1 BoxRowCaption">
                    <input type="text" id="ParcelOtherCosts_'.$hearing_number.'" name="ParcelOtherCosts['.$countHearing.']" class="form-control frm_field_currency parcelCalc" value="" style="width:9rem;">
                </div> 
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1"></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem; text-align: center;"><i class="fa fa-minus" style="margin-top:0.5rem"></i></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    R.A.
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input readonly type="text" id="ParcelRA_'.$hearing_number.'" name="ParcelRA['.$countHearing.']" class="form-control frm_field_currency" value="" style="width:9rem;">
                </div>

                <div class="col-sm-1 BoxRowCaption">
                    '.CreateArraySelect(
                        array(0=>"",1=>"Esenzione"),
                        true,"ParcelRAExemption[".$countHearing."]","ParcelRAExemption_".$hearing_number,null,true,null,"parcelCalc ".$disabled).'
                </div>
                <div class="col-sm-6 BoxRowCaption"></div>
                <div class="clean_row HSpace16"></div>
                <div class="col-sm-1 "></div>
                <div class="col-sm-1 BoxRowCaption" style="font-weight:bold;font-size:1.2rem; text-align: center;"><i class="fas fa-equals" style="margin-top:0.5rem"></i></div>
                <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                    TOTALE
                </div> 
                <div class="col-sm-1 BoxRowCaption" style="font-size:1.2rem">
                    <input readonly type="text" id="ParcelTotal_'.$hearing_number.'" name="ParcelTotal['.$countHearing.']" class="form-control frm_field_currency" value="" style="width:9rem;">
                </div>
            </div>
            </div>
    ';

    if($DateMerit != "" && $lastDisputeDateId>0){

        $disputeAmountView = new CLS_VIEW(MGMT_FINE_DISPUTE_AMOUNT);
        $disputeAmountView->where = "DD.Id=" . $lastDisputeDateId." AND DD.GradeTypeId=".$GradeTypeId;
        $rs_FineDisputeAmount = $rs->getResults($rs->ExecuteQuery($disputeAmountView->generateSelect()));
        $rs_DisputeAmount = $rs->getResults($rs->ExecuteQuery("SELECT * FROM DisputeAmount WHERE DisputeDateId=" . $lastDisputeDateId));
        if($disabled!=""){
            $addIcon = '';
        }
        else{
            $addIcon = '<i class="fa fa-plus-circle AddAmounts" id="AddAmounts_'.$GradeTypeId.'" 
                data-grade="'.$GradeTypeId.'" data-dispute="'.$DisputeId.'" data-disputedate="'.$lastDisputeDateId.'"
                data-count="'.count($rs_DisputeAmount).'"
                style="cursor: pointer"></i>';
        }
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
            if($disabled!=""){
                $deleteIcon = '';
            }
            else{
                $deleteIcon = '<i class="fa fa-trash removeAmounts" data-id="'.$r_DisputeAmount['Id'].'" style="cursor: pointer;color:#b40808; font-size: 1.5rem;"></i>';
            }
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
            if($disabled!=""){
                $inputFineHidden = "";
                $selectFineAmount = "<b>".$a_fineAmount[$r_FineDispute['DisputeAmountId']]."</b>";
            }
            else{
                $inputFineHidden = '<input type="hidden" name="FDAFineId['.$countFine.']" value="'.$r_FineDispute['FineId'].'">';
                $selectFineAmount = CreateArraySelect($a_fineAmount,true,"FDADisputeAmountId[".$countFine."]","FineDisputeAmountId_".$countFine, $r_FineDispute['DisputeAmountId']);
            }

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

    if($disabled==""){
        $str_out.='
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRow" style="height:6rem;">
        
                        <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                            <input type="submit" class="btn btn-default" id="save" style="margin-top:1rem;"  value="Salva" />
                            <input type="button" class="btn btn-default back" style="margin-top:1rem;" value="Indietro" />
                        </div>
                     </div>
                </div>';
    }

    $str_out.='
      	    </form>
      	</div>
      	<div class="clean_row HSpace48"></div>
    ';

}

if($GradeTypeId<3 && $DateMerit != "" ){
    $str_out .= '
        <div class="col-sm-12 BoxRowLabel" style="font-weight:bold;font-size:1.5rem;text-align:center; height:2.4rem;">
            Aggiungi Grado '.$a_GradeType[$GradeTypeId+1].'
            <i class="fa fa-angle-down" id="showGrade'.($r_Dispute['GradeTypeId']+1).'" style="cursor: pointer;"></i>
        </div>
        <div class="col-sm-12 disputeDiv'.($r_Dispute['GradeTypeId']+1).'" style="display:none;">
        <form name="frm_dispute_upd'.($r_Dispute['GradeTypeId']+1).'" id="frm_dispute_upd'.$r_Dispute['GradeTypeId'].'" method="post" action="mgmt_dispute_upd_exe.php">
        
        <input type="hidden" name="FineId" value="'.$r_Dispute['FineId'].'" />
        <input type="hidden" name="DisputeId" value="'.$r_Dispute['DisputeId'].'" />
        <input type="hidden" name="GradeTypeId" value="'.($r_Dispute['GradeTypeId']+1).'" />
        <input type="hidden" name="AddGrade" value="1">
        
        <div class="clean_row HSpace48"></div>
        <div class="col-sm-12 ">
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Registrazione
            </div>          
            <div class="col-sm-1 BoxRowCaption">
                '.date('d/m/Y').'
            </div> 
            <div class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Ufficio giudicante
            </div>          
            <div class="col-sm-2 BoxRowCaption" >
                '.$officeInput.'
            </div>  
            <div class="col-sm-1 BoxRowCaption">
                <input type="text" name="OfficeCity" value="'.$r_Dispute['OfficeCity'].'" style="width:95%">
            </div>
            <div  class="col-sm-1 BoxRowLabel" style="font-weight:bold;font-size:1.2rem">
                Presentazione ricorso
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.CreateArraySelect($a_ownerPresentation,true,"OwnerPresentation","OwnerPresentation",null,true).'
            </div>
            <div class="col-sm-3 BoxRowCaption">
               <b>Grado '.$a_GradeType[$GradeTypeId+1].'</b>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">

                <div class="col-sm-12 BoxRowLabel" style="text-align:center;line-height:6rem;">
                    <input type="submit" class="btn btn-default" id="save" style="margin-top:1rem;"  value="Salva" />
                    <input type="button" class="btn btn-default back" style="margin-top:1rem;" value="Indietro" />
                </div>
             </div>
        </div>
        </form>';


}

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

        $("#DisputeResultId").change(function() {

            var ResultId = $("#DisputeResultId").val();

            if(ResultId==0){
                $("#Note").val("");
                $("#Number").val("");
                $("#DateAction").val("");

                $('#add_DateHearing').hide();
                $('#CloseDispute1').hide();
                $('#CloseDispute2').hide();

            } else if(ResultId==1){
                $('#add_DateHearing').show();

                $("#Number").val("");
                $("#DateAction").val("");


                $('#CloseDispute1').hide();
                $('#CloseDispute2').hide();

            } else {


                $('#CloseDispute1').show();
                $('#CloseDispute2').show();
                $('#add_DateHearing').hide();
            }
        });


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

        $('#newDoc').click(function () {
            window.location.href = "doc_dispute_add.php?DisputeId=<?=$DisputeId;?>&FineId=<?=$FineId;?>";
        });

        $("#btn_sana").click(function(){
            alert('Attenzione! Mancano le credenziali di accesso al SAN.A');
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

        $('.AddAmounts').click(function () {
            let dispute = $(this).attr('data-dispute');
            let disputeDate = $(this).attr('data-disputedate');
            let gradeType = $(this).attr('data-grade');

            $.ajax({
                url: 'ajax/mgmt_disputeAmounts.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Type: "add", DisputeId: dispute, DisputeDateId: disputeDate, GradeTypeId:gradeType},
                success: function (data) {
                    window.location.href = "mgmt_dispute_upd.php?&Id="+dispute+"&FineId=<?=$FineId;?>&answer=Nuovo importo aggiunto con successo";
                }
            });

        });

        $('.removeAmounts').click(function () {
            let amountId = $(this).attr('data-id');

            $.ajax({
                url: 'ajax/mgmt_disputeAmounts.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Type: "remove", AmountId:amountId},
                success: function (data) {
                    window.location.href = "mgmt_dispute_upd.php?&Id=<?=$DisputeId;?>&FineId=<?=$FineId;?>&answer=Importo eliminato con successo";
                }
            });

        });

        $('.judgment').change(
            function(){
                let judgementId = $(this).attr('id');
                let judgmentVal = $(this).val();
                let a_judgementId = judgementId.split('_');

                if(judgmentVal>1){
                    $('.judgment_'+a_judgementId[1]).show();
                    $('#newHearing_'+(parseInt(a_judgementId[1])+1)).hide();
                }
                else{
                    $('.judgment_'+a_judgementId[1]).hide();
                    $('#Number_'+a_judgementId[1]).val('');
                    $('#DateAction_'+a_judgementId[1]).val('');
                    $('#HearingDateMeasure_'+a_judgementId[1]).val('');
                    $('#DateNotification_'+a_judgementId[1]).val('');

                    $('#LosingParty_'+a_judgementId[1]).val(0);
                    $('#LosingPaymentDate_'+a_judgementId[1]).val('');
                    $('#LosingPayer_'+a_judgementId[1]).val('');

                    $('#Parcel_'+a_judgementId[1]).val(0);
                    $('#ParcelPaymentDate_'+a_judgementId[1]).val('');
                    $('#ParcelPayer_'+a_judgementId[1]).val('');

                    $('#losingpartydiv_'+(parseInt(a_judgementId[1]))).hide();
                    $('#parceldiv_'+(parseInt(a_judgementId[1]))).hide();

                    if(judgmentVal==1)
                        $('#newHearing_'+(parseInt(a_judgementId[1])+1)).show();
                    else
                        $('#newHearing_'+(parseInt(a_judgementId[1])+1)).hide();
                }

            }
        );

        $('.losingparty').change(
            function(){
                let losingpartyId = $(this).attr('id');
                let losingpartyVal = parseInt($(this).val());
                let a_losingpartyId = losingpartyId.split('_');
                if(losingpartyVal==1){
                    $('#losingpartydiv_'+a_losingpartyId[1]).show();
                }
                else{
                    $('#losingpartydiv_'+a_losingpartyId[1]).hide();
                }

            }
        );

        $('.parcel').change(
            function(){
                let parcelId = $(this).attr('id');
                let parcelVal = parseInt($(this).val());
                let a_parcelId = parcelId.split('_');
                if(parcelVal==1){
                    $('#parceldiv_'+a_parcelId[1]).show();
                }
                else{
                    $('#parceldiv_'+a_parcelId[1]).hide();
                }

            }
        );

        $('.parcelCalc').change(
            function(){
                "use strict";

                let Id = $(this).attr('id');
                let a_Id = Id.split('_');
                let parcelId = a_Id[1];

                let fee = $('#ParcelFee_'+parcelId).val();
                let otherExpenses = $('#ParcelOtherExpenses_'+parcelId).val();
                let cu = $('#ParcelCU_'+parcelId).val();
                let stamp_duty = $('#ParcelRevenueStamp_'+parcelId).val();
                let other_costs = $('#ParcelOtherCosts_'+parcelId).val();

                if(fee!="") fee = parseFloat( fee.replace(",",".") );
                else        fee = 0.00;

                if(otherExpenses!="")   otherExpenses = parseFloat( otherExpenses.replace(",",".") );
                else                    otherExpenses = 0.00;

                if(cu!="")  cu = parseFloat( cu.replace(",",".") );
                else        cu = 0.00;

                if(stamp_duty!="")  stamp_duty = parseFloat( stamp_duty.replace(",",".") );
                else                stamp_duty = 0.00;

                if(other_costs!="") other_costs = parseFloat( other_costs.replace(",",".") );
                else                other_costs = 0.00;

                let feeOtherExpenses = Math.round((fee+otherExpenses)*100)/100;
                let overheads = Math.round(feeOtherExpenses*15)/100;
                let withholding_tax = Math.round((feeOtherExpenses+overheads)*20)/100;
                let lawyer_fund = Math.round((feeOtherExpenses+overheads)*4)/100;
                let partial = (feeOtherExpenses+overheads)+lawyer_fund;
                let VAT = Math.round(partial*22)/100;

                if($('#ParcelRAExemption_'+parcelId).val()==1)     withholding_tax = 0.00;
                if($('#ParcelVATExemption_'+parcelId).val()==1)                 VAT = 0.00;

                let actual_costs = Math.round((cu+stamp_duty+other_costs)*100)/100;
                let bill_total = partial+VAT+actual_costs-withholding_tax;
                bill_total = Math.round(bill_total*100)/100;

                $('#ParcelFeeOtherExpenses_'+parcelId).val(feeOtherExpenses);
                $('#ParcelOverheads_'+parcelId).val(overheads);
                $('#ParcelLawyerFund_'+parcelId).val(lawyer_fund);
                $('#ParcelVAT_'+parcelId).val(VAT);
                $('#ParcelLivingExpenses_'+parcelId).val(actual_costs);
                $('#ParcelRA_'+parcelId).val(withholding_tax);
                $('#ParcelTotal_'+parcelId).val(bill_total);
            }
        );

    });

</script>

<?php

include(INC."/footer.php");

