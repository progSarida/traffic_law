<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$a_PaymentTypes = array(
    "2"=>"BONIFICI", //tipo 1
    "1"=>"BOLLETTINI", //tipo 2
    "11"=>"BOLLETTINI 674 e 896", //tipo 1
    "18"=>"BOLLETTINI TELEMATICI", //tipo 1
    "100"=>"POSTA ONLINE", //tipo 2
    "9"=>"PAGO PA", //tipo 1
    "19"=>"PAGO PA SPONTANEI" //tipo 1
);

$Id = CheckValue('Id', 'n');
$str_Fine = "";
$str_Documentation = "";
$str_Tree = "";
$PaymentId = 0;

$PaymentTypeId = CheckValue('PaymentTypeId', 'n');

if($PaymentTypeId==0) $PaymentTypeId = 2;

$PaymentTypeIdType1 = ($PaymentTypeId==2 || $PaymentTypeId==18 || $PaymentTypeId==9 || $PaymentTypeId==19 || $PaymentTypeId==11);

$str_out = '
    <div class="col-sm-12">
        <div class="col-sm-6 table_caption_I">
            Tipo Pagamento
        </div>
        <div class="col-sm-6 BoxRowCaption">
            '.CreateArraySelect($a_PaymentTypes,true,"PaymentTypeId","PaymentTypeId",$PaymentTypeId,true).'
        </div>
    </div>

    <div class="clean_row HSpace4"></div>';

//*******************Gestione Regolamento*******************
$RuleTypeId = $_SESSION['ruletypeid'];
$RuleTypeTitle = $_SESSION['ruletypetitle'];
$str_Where .= " AND RuleTypeId = $RuleTypeId";
//**********************************************************

$str_City = " AND CityId='".$_SESSION['cityid']."'";

if($PaymentTypeIdType1){
    $str_out .= '
        <div class="col-sm-11">
        	<div class="col-sm-2 BoxRowLabel">
        		Da data pagamento:
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<input type="text" value="'.$Search_FromPaymentDate.'" id="Search_FromPaymentDate" class="frm_field_date form-control">
        	</div>
        	<div class="col-sm-2 BoxRowLabel">
        		A data pagamento:
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<input type="text" value="'.$Search_ToPaymentDate.'" id="Search_ToPaymentDate" class="frm_field_date form-control">
        	</div>
        	<div class="col-sm-2 BoxRowLabel">
        		Da data accredito:
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<input type="text" value="'.$Search_FromCreditDate.'" id="Search_FromCreditDate" class="frm_field_date form-control">
        	</div>
        	<div class="col-sm-2 BoxRowLabel">
        		A data accredito:
        	</div>
        	<div class="col-sm-1 BoxRowCaption">
        		<input type="text" value="'.$Search_ToCreditDate.'" id="Search_ToCreditDate" class="frm_field_date form-control">
        	</div>
        	
        	<div class="clean_row HSpace4"></div>
        	
        	<div class="col-sm-1 BoxRowLabel">
                Note contiene:
        	</div>
        	<div class="col-sm-5 BoxRowCaption">
        		<input type="text" value="'.htmlentities($Search_Note).'" id="Search_Note" class="frm_field_string form-control">
        	</div>
        	<div class="col-sm-6 BoxRowLabel">
        	</div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem;">
        	<button type="button" data-toggle="tooltip" data-container="body" data-placement="top" title="Filtra" class="tooltip-r btn btn-primary pull-left" id="filter" style="margin-top:0;height:100%;width:100%"><i class="fas fa-filter" style="font-size:2.5rem;"></i></button>
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-12">
            <div class="col-sm-12 table_label_H">
        		'.$a_PaymentTypes[$PaymentTypeId].'
        	</div>
        </div>

        <div class="clean_row HSpace4"></div>';
    
    $str_Id = "";
    
    if($Id>0){
        $str_Id = " AND Id=".$Id;
    }
    
    $str_paymentType = " AND PaymentTypeId=$PaymentTypeId ";
    
    if ($PaymentTypeId==11){
        $str_paymentType = " AND PaymentTypeId=1 AND DocumentType in (674, 896) ";
    }
    
    $str_paymentDate = '';
    
    if ($Search_FromPaymentDate != "") {
        $str_paymentDate .= " AND PaymentDate>='".DateInDB($Search_FromPaymentDate)."'";
    }
    if ($Search_ToPaymentDate != "") {
        $str_paymentDate .= " AND PaymentDate<='".DateInDB($Search_ToPaymentDate)."'";
    }
    if ($Search_FromCreditDate != "") {
        $str_paymentDate .= " AND CreditDate>='".DateInDB($Search_FromCreditDate)."'";
    }
    if ($Search_ToCreditDate != "") {
        $str_paymentDate .= " AND CreditDate<='".DateInDB($Search_ToCreditDate)."'";
    }
    if ($Search_Note != "") {
        $str_paymentDate .= " AND Note LIKE'%".mysqli_real_escape_string($rs->conn, $Search_Note)."%'";
    }
    
    $rs_payment = $rs->Select('FinePayment', "Hidden=0 AND FineId=0 ".$str_paymentDate.$str_paymentType.$str_City.$str_Id, 'ReclaimOrder');
    $n_Number = mysqli_num_rows($rs_payment);
    
    if ($n_Number == 0) {
        $str_out .=
            '<div class="table_caption_H col-sm-12">
			    Nessun record presente
		    </div>';
        $r_payment = mysqli_fetch_array($rs_payment);
        
    } else {
        $r_payment = mysqli_fetch_array($rs_payment);
        $a_BankMgmt = array("","");
        $a_BankMgmt[$r_payment['BankMgmt']] = " SELECTED ";
        $a_Causal = explode(" ID",trim($r_payment['Note']));
        $a_FindStr = array("142/7",
            "142/8",
            "142/9",
            "142-7",
            "142-8",
            "142-9",
            "142.7",
            "142.8",
            "142.9",
            "ART 142",
            "ART142",
            "ART. 142",
            "ART.142",
            "DEL 30",
            " 5 G",
            "RIDOTTA 30",
            " 5GG",
            " 5 GG",
            " 5 GIORNI",
            " 5GIO"
        );
        $a_ReplaceStr = array("","","","","","","","","","","","","","","","","","","","");

        if (strpos($r_payment['Note'], 'CRON') !== false) $aId = explode("CRON",trim($r_payment['Note']));
        else if(strpos($r_payment['Note'], 'RI1') !== false)$aId = explode("RI1",trim($r_payment['Note']));
        else if(strpos($r_payment['Note'], 'ZZ3') !== false)$aId = explode("ZZ3",trim($r_payment['Note']));
        else $Id=0;

        $pos_StartNumber = -1;
        $pos_EndNumber = 0;

        if(isset($aId[1])){
            $str_Clean = str_replace($a_FindStr,$a_ReplaceStr,$aId[1]);
            for($i=0;$i<strlen($str_Clean);$i++){
                if(is_numeric(substr($str_Clean,$i,1))){
                    if($pos_StartNumber<0){
                        $pos_StartNumber=$i;
                    }
                }else{
                    if($pos_StartNumber>=0){
                        $pos_EndNumber = $i;
                        break;
                    }
               }

            }
            $n_Start = $pos_StartNumber;
            $n_Lenght = $pos_EndNumber-$pos_StartNumber;
            if($n_Lenght==0) $n_Lenght=1;
            $Id = substr($str_Clean,$n_Start,$n_Lenght);
        } else $str_Clean="";
        
        if(!is_numeric($Id)) $Id=0;
        
        //Fatta una modifica per far vedere solo i trasgressori che sono associati a verbali del regolamento in sessione
        $fines = $rs->Select('V_FineTrespasser', "(TrespasserTypeId=1 OR TrespasserTypeId=11) AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolId=" . $Id." AND RuleTypeId=".$RuleTypeId,"ProtocolYear DESC");
        $FindNumber = mysqli_num_rows($fines);
        
        if ($FindNumber > 0) {
            $r_fine = mysqli_fetch_array($fines);
            $NameOut = substr($r_fine['CompanyName']." ".$r_fine['Surname']." ".$r_fine['Name'],0,25);
            $FineId = $r_fine['FineId'];
            $rs_Row = $rs->SelectQuery("
    			SELECT  
    			FA.Fee,
    			FA.MaxFee,			
    			ArT.ReducedPayment,
    			FH.NotificationTypeId,
    			FH.NotificationFee,
    			FH.ResearchFee,
    			FH.SendDate,
    			FH.DeliveryDate,
    			FH.ResultId
    			FROM FineArticle FA JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId
    			JOIN FineHistory FH ON FA.FineId = FH.FineId
    			WHERE FA.FineId=".$FineId." AND (NotificationTypeId=6 OR NotificationTypeId=15)");
            $r_Row = mysqli_fetch_array($rs_Row);
            
            $AdditionalFee = $r_Row['NotificationFee'] +	$r_Row['ResearchFee'];
            $Fee = $r_Row['Fee'] + $AdditionalFee;
            $MaxFee = $r_Row['MaxFee'] + $AdditionalFee;
            $ReducedFee = "";
            
            if($r_Row['ReducedPayment']){
                $ReducedFee = ($r_Row['Fee']*FINE_PARTIAL)+$AdditionalFee;
                $ReducedFee = NumberDisplay($ReducedFee);
            }
            
            $str_Fine .= '
                <input type="hidden" value="'.$r_fine['FineId'].'"  name="Search_FineId" style="">
                <input type="hidden" value="' . $r_payment['Id'] . '" name="PaymentId">
                <input type="hidden" value="'.$PaymentTypeId.'" name="PaymentTypeId">         
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Protocollo
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                    '.$r_fine['ProtocolId'].'/'.$r_fine['ProtocolYear'].'
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                    Data Infrazione
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                    '.DateOutDB($r_fine['FineDate']).'
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel">
                        Targa
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                    '.$r_fine['VehiclePlate'].'
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel">
                        Nominativo
                    </div>
                    <div class="col-sm-6 BoxRowLabel">
                    '.$NameOut.'
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Ridotto
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                    '.$ReducedFee.'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Minima
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                    '.NumberDisplay($Fee).'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Massima
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                    '.NumberDisplay($MaxFee).'
                    </div>
                </div>     
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel">
                    Verbale spedito
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                    '.DateOutDB($r_Row['SendDate']).'
                    </div>
                </div> 
                <div class="clean_row HSpace4"></div>    
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel">
                    Verbale ricevuto
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                    '.DateOutDB($r_Row['DeliveryDate']).'
                    </div>
                </div>';
        }

        $str_out .= '
        	<div class="row-fluid">
            	<div class="col-sm-12">
                    <div class="col-sm-6">
                        <div class="col-sm-12">
                            <div class="col-sm-4 BoxRowLabel" style="font-size:1.2rem">
                                    Pagamenti da bonificare
                            </div>
                            <div class="col-sm-8 BoxRowCaption" style="font-size:1.2rem">
                            '.$n_Number.'
                            </div>
                        </div>
                        <div class="clean_row HSpace4"></div>
                        <input type="hidden" value="'. $r_payment['Id'].'" id="PaymentId"> <!--ID Pagamento-->
                        <div class="col-sm-12">
                            <div class="col-sm-4 BoxRowLabel">
                                Nominativo pagamento
                            </div>                     
                            <div class="col-sm-8 BoxRowCaption">
                                <input class="form-control frm_field_string" type="text" id="Search_Payer" value="'. $r_payment['Name'].'">	
                            </div>
                        </div>
                        <div class="clean_row HSpace4"></div>
                        <div id="fine_payer" class="col-sm-12" style="width:100%;height:230px;overflow:auto;position:absolute;top:36px;background-color: #4E91FF;z-index: 5;color;#fff;display: none">
                        </div>
                        <div class="col-sm-12"> 
                            <div class="col-sm-4 BoxRowLabel">
                                Data pagamento
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                ' . DateOutDB($r_payment['PaymentDate']) . '
                            </div>
                            <div class="col-sm-3 BoxRowLabel">
                                Importo
                            </div>
                            <div class="col-sm-3 BoxRowCaption">
                                ' . $r_payment['Amount'] . '	
                            </div>
                        </div>
                        <div class="clean_row HSpace4"></div>

                        <div class="col-sm-12"> 
                            <div class="col-sm-4 BoxRowLabel">
                                Data accredito
                            </div>
                            <div class="col-sm-8 BoxRowCaption">
                                ' . DateOutDB($r_payment['CreditDate']) . '
                            </div>
                        </div>

                        <div class="clean_row HSpace4"></div>
                        <div class="col-sm-12"> 
                            <div class="col-sm-4 BoxRowLabel">
                                Quinto campo
                            </div>
                            <div class="col-sm-8 BoxRowCaption">
                                ' . $r_payment['FifthField'] . '	
                            </div>
                        </div>
                        <div class="clean_row HSpace4"></div>
                        <div class="col-sm-12">
                            <div class="col-sm-4 BoxRowLabel">
                                    Rata
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input class="form-control frm_field_numeric" id="PaymentFee" type="text" name="PaymentFee" value="0" style="width:5rem">	
                            </div>
                            <div class="col-sm-6 BoxRowCaption">
                                <select class="form-control" name="InstallmentList" id="InstallmentList" style="visibility:hidden">
                                    <!--Le option vengono inserite dalla risors ajax "search_installments.php"-->
                                </select>
                            </div>
                        </div>
                        <div class="clean_row HSpace4"></div>							
                        <div class="col-sm-12" style="height:20rem">
                            <div class="col-sm-4 BoxRowLabel" style="height:20rem">
                                Note 
                                    <button id="delete" data-container="body" data-placement="right" title="Elimina pagamento" class="btn btn-danger tooltip-r" style="position:absolute;bottom:5px;left:5px;">
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                    </button>
                            </div>
                            <div class="col-sm-8 BoxRowCaption" style="height:20rem">
                               ' . $r_payment['Note'] . ' 			
                                <a href="mgmt_payment_exp_exe.php'.$str_GET_Parameter.'&Id='.$r_payment['Id'].'&PaymentTypeId='.$PaymentTypeId.'" data-container="body" data-placement="left" title="Metti in coda" class="btn btn-info tooltip-r" style="position:absolute;bottom:5px;right:5px;"><i class="fa fa-level-down" aria-hidden="true"></i></a>
                            </div>
                        </div> 
                    </div>	     	
                    <div class="col-sm-6">
                        '.$str_Fine.'
                    </div>
                    <div class="clean_row HSpace4"></div>';
        $PaymentId = $r_payment['Id'];
    }
    
} else {
    $str_out .= '
        <div class="col-sm-12">
            <div class="col-sm-12 table_label_H">
        		'.$a_PaymentTypes[$PaymentTypeId].'
        	</div>
        </div>

        <div class="clean_row HSpace4"></div>';
    
    $n_File = 0;
    $a_File = glob(PAYMENT_RECLAIM.'/'.$_SESSION['cityid'].'/*.jpg');
    if ($a_File){
        $n_File = count($a_File);
    }
    
    $r_payment=array("Id"=>"");
    
	$str_Documentation = '
		<form id="f_paymentfine" action="frm_reclaim_payment_exe.php" method="post">
            <input type="hidden" value="' . $PaymentTypeId . '" name="PaymentTypeId">

    		<div class="row-fluid">
                <div class="col-sm-12" >
                    <div class="col-sm-8">
                        <div class="col-sm-12">
                            <div class="col-sm-2 BoxRowLabel">
                                Nominativo
                            </div>
                            <div class="col-sm-10 BoxRowCaption">
                                <input class="form-control frm_field_string" type="text" name="Name" id="Name" style="width:30rem">	
                            </div>
                        </div>
                        <div class="clean_row HSpace4"></div>
                        <div class="col-sm-12">
                            <div class="col-sm-2 BoxRowLabel">
                                Rata
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input class="form-control frm_field_numeric" id="PaymentFee" type="text" name="PaymentFee" value="0" style="width:5rem">
                            </div>
                            <div class="col-sm-6 BoxRowCaption">
                                <select class="form-control" name="InstallmentList" id="InstallmentList" style="visibility:hidden">
                                    <!--Le option vengono inserite dalla risors ajax "search_installments.php"-->
                                </select>
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Tipo documento
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input class="form-control frm_field_numeric" type="text" name="DocumentType" id="DocumentType" style="width:5rem">	
                            </div>
                        </div> 
                        <div class="clean_row HSpace4"></div>                   			                
                        <div class="col-sm-12">
                            <div class="col-sm-2 BoxRowLabel">
                                Data Pagamento
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input type="text" class="form-control frm_field_date" name="PaymentDate" id="PaymentDate">	
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Data Accredito
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input type="text" class="form-control frm_field_date" name="CreditDate" id="CreditDate">	
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Quinto campo
                            </div>
                            <div class="col-sm-4 BoxRowCaption">
                                <input class="form-control frm_field_numeric" type="text" name="FifthField" id="FifthField" style="width:25rem">	
                            </div>
                        </div>
                        <div class="clean_row HSpace4"></div>                   			                
                        <div class="col-sm-12">
                            <div class="col-sm-2 BoxRowLabel">
                                Importo
                            </div>
                            <div class="col-sm-4 BoxRowCaption">
                                <input class="form-control frm_field_numeric" type="text" name="Amount" id="Amount" style="width:10rem">	
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Tipo pagamento
                            </div>
                            <div class="col-sm-4 BoxRowCaption">
                                <select name="PaymentDocumentId">
                                    <option value="0">Ridotto
                                    <option value="1">Normale
                                    <option value="2">Maggiorato
                                </select>	
                            </div>
                        </div>
                        <div class="clean_row HSpace4"></div>
                        <div class="col-sm-12">
                            <div class="col-sm-2 BoxRowLabel">
                                Ref
                            </div>
                            <div class="col-sm-4 BoxRowCaption">
                                <input class="form-control frm_field_string" type="text" name="Code" id="Code" style="width:10rem">	
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Targa
                            </div>
                            <div class="col-sm-4 BoxRowCaption">
                                <input class="form-control frm_field_string" type="text" name="VehiclePlate" id="VehiclePlate" style="width:25rem">	
                            </div>
                        </div>
                        <div class="clean_row HSpace4"></div>                                     			                
                        <div class="col-sm-12">
                            <div class="col-sm-2 BoxRowLabel">
                                Cron
                            </div>
                            <div class="col-sm-4 BoxRowCaption">
                                <input class="form-control frm_field_numeric" type="text" name="ProtocolId" id="ProtocolId" style="width:10rem">	
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Data violazione
                            </div>
                            <div class="col-sm-4 BoxRowCaption">
                                <input class="form-control frm_field_date" type="text" name="FineDate" id="FineDate" style="width:10rem">	
                            </div>
                        </div>              			                
                    </div>   
                    <div class="col-sm-4" >
                        <div class="col-sm-12">
                            <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                                DOCUMENTAZIONE ('. $n_File. ' immagini presenti)
                            </div> 
                        </div>
                        <div class="col-sm-12" style="position:relative; width:100%;height:19rem;">
                            <div class="example">
                                <div id="fileTreeReclaim" class="BoxRowLabel" style="height:17rem;overflow:auto"></div>                            
                            </div>
                            <span id="span_DocumentationDel" style="position:absolute; bottom:0.1rem;left:1rem;z-index:900; "></span>
                            <span id="span_DocumentationUpd" style="position:absolute; bottom:0.1rem;right:1rem;z-index:900; "></span>
                        </div>
                    </div>
                </div>  
                <div class="clean_row HSpace4"></div>          
                <div class="col-sm-12 BoxRow" style="width:100%;height:50rem;">
                    <div class="imgWrapper" id="preview_img" style="height:60rem;overflow:auto; display: none;">
                        <img id="preview" class="iZoom"/>
                    </div>
                    <div id="preview_doc" style="height:100%;overflow:auto; display: none;"></div>
                </div>					
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Documento
                    </div>
                    <div class="col-sm-10 BoxRowCaption">
                        <input type="hidden" name="Documentation" id="Documentation" value="">
                        
                        <span id="span_Documentation" style="height:6rem;width:30rem;font-size:1.1rem;"></span>
                    </div>
                </div>	
            </div>
    
            <div class="clean_row HSpace4"></div>';

$str_Tree ="
    $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});
    $('#fileTreeReclaim').fileTree({ root: 'doc/reclaim/payment/".$_SESSION['cityid'] ."/', script: 'jqueryFileTree.php' }, function(file) {

        var FileType = file.split('.').pop();
            
        if(FileType.toLowerCase()=='pdf' || FileType.toLowerCase()=='doc' || FileType.toLowerCase()=='html'){
            $('#preview_img').hide();
            
            $('#preview_doc').html('<iframe style=\"width:100%; height:100%; background:white;\" src=\"'+file+'\"></iframe>');
            $('#preview_doc').show();
            
        }else{
            $('#preview_doc').hide();
            
            $('#preview').attr('src',file);
            $('#preview_img').show();
        }

        $('#Documentation').val(file);
        $('#span_Documentation').html(file);
        $('#span_DocumentationDel').html('<a href=\"mgmt_payment_del_exe.php?Documentation='+file+'\"><i style=\"font-size: 1.5rem;color:#ff1a1f\" class=\"fa fa-times-circle-o\"></i></a>');
        $('#span_DocumentationUpd').html('<a href=\"mgmt_payment_act_exe.php?Documentation='+file+'\"><i style=\"font-size: 1.5rem;color:#3EFF20\" class=\"fa fa-level-down\"></i></a>');
        	$.ajax({
        		url: 'ajax/search_payment.php',
        		type: 'POST',
        		dataType: 'json',
        		cache: false,
        		data: {Documentation: file},
        		success: function (data) {
        			$('#Name').val(data.Name);		
        			$('#DocumentType').val(data.DocumentType);
        		    $('#PaymentDate').val(data.PaymentDate);
                    $('#CreditDate').val(data.CreditDate);
        			$('#FifthField').val(data.FifthField);
        			$('#Amount').val(data.Amount);
        			$('#Code').val(data.Code);
        			$('#ProtocolId').val(data.ProtocolId);
        			$('#VehiclePlate').val(data.VehiclePlate);
        			$('#FineDate').val(data.FineDate);			
        			$('#PaymentId').val(data.Search_PaymentId);
        			$('#Search_PaymentId').val(data.Search_PaymentId);
        			$('#Id').val(data.Search_PaymentId);
        			$('#btn_refresh').show();
        		}
        	});
    });";
}

if($PaymentTypeIdType1){
    $str_out .= '
        <form id="f_payment" action="frm_reclaim_payment_exe.php" method="post">
            <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">';
}

    $str_out .= 
            $str_Documentation.'
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel" style="text-align:center">
                    C/Terzi
                </div>
                <div class="col-sm-1 BoxRowCaption" style="text-align:center"> 
                    <select class="form-control" id="BankMgmt" name="BankMgmt">
                        <option value="0"'.$a_BankMgmt[0].'>SI</option>
                        <option value="1"'.$a_BankMgmt[1].'>NO</option>
                    </select>
                </div>
                <div class="col-sm-2 BoxRowLabel" style="text-align:center">
                    Assegnazione pagamento
                </div>
                <div class="col-sm-7 BoxRowCaption" style="text-align:center"> 	
                    <span id="span_name"></span>
                </div>	  
                <input type="hidden" value="" id="Search_FineId" name="Search_FineId">
                <input type="hidden" value="' . $PaymentTypeId . '" name="PaymentTypeId">
                <input type="hidden" value="1" name="TableId">
                <input type="hidden" value="' . $PaymentId . '" name="PaymentId" id="Search_PaymentId">
                <input type="hidden" name="Id" value="'.$r_payment['Id'].'"></input>
    
            </div>
    
            <div class="clean_row HSpace4"></div> 	
    
            <div class="col-sm-12">             
                <div class="col-sm-1 BoxRowCaption">
                    <div class="col-sm-2">
                        Id
                    </div>
                    <div class="col-sm-10">
                        <input class="form-control frm_field_numeric" name="Search_Id" id="Search_Id" type="text">
                    </div>
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <div class="col-sm-4">
                        Cron
                    </div>
                    <div class="col-sm-8">
                        <input class="form-control frm_field_numeric" name="Search_Protocol" id="Search_Protocol" type="text">
                    </div>
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <div class="col-sm-3">
                        Ref 
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control frm_field_string" name="Search_Code" id="Search_Code" type="text">
                    </div>
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <div class="col-sm-4">
                        Targa 
                    </div>
                    <div class="col-sm-8">
                        <input class="form-control frm_field_string" name="Search_Plate" id="Search_Plate" type="text">
                    </div>
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <div class="col-sm-3">
                        Nominativo 
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control frm_field_string" name="Search_Trespasser" id="Search_Trespasser" type="text">
                    </div>
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <div class="col-sm-2">
                        Iuv 
                    </div>
                    <div class="col-sm-10">
                        <input class="form-control frm_field_string" name="Search_Iuv" id="Search_Iuv" type="text">
                    </div>
                </div>                                                                                             
                <div class="col-sm-2 BoxRowCaption">                 
                </div>
                <div class="col-sm-1 BoxRowCaption text-right">
                    <button id="search" type="button" data-container="body" data-placement="left" title="Cerca vebale" class="btn btn-primary tooltip-r" style="margin:0;width:50%;height:100%;padding:0">
                        <i class="glyphicon glyphicon-search"></i>
                    </button>
                 </div>                                                                           
            </div>
    
            <div class="clean_row HSpace4"></div> 
    
            <div class="col-sm-12">
                <div class="col-sm-12" style="height:40rem;">
                    <div id="fine_content" class="col-sm-12" style="height:150px;overflow:auto"></div>
                    <div id="payment_content" class="col-sm-12" style="margin-top:2rem;height:100px;overflow:auto"></div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:6rem;">
                    <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                        <input type="button" class="btn btn-default" id="update" style="margin-top:1rem;"  value="Salva" />
                    </div>    
                </div>
    		</div>
	    </form>
	</div> 
</div>';

echo $str_out;
?>
<script type="text/javascript">
	var page = "bonifica";
	var showInstallmentList = false;
	function filter(){
        var PaymentTypeId 				= $( "#PaymentTypeId" ).val();
        var Search_FromPaymentDate 		= $("#Search_FromPaymentDate").val() || "";
        var Search_ToPaymentDate 		= $("#Search_ToPaymentDate").val() || "";
        var Search_FromCreditDate 		= $("#Search_FromCreditDate").val() || "";
        var Search_ToCreditDate 		= $("#Search_ToCreditDate").val() || "";
        var Search_Note					= $("#Search_Note").val() || "";
            
        $(window.location).attr('href', `<?= $str_CurrentPage ?>&PaymentTypeId=`+PaymentTypeId+
        	`&Search_FromPaymentDate=`+Search_FromPaymentDate+
        	`&Search_ToPaymentDate=`+Search_ToPaymentDate+
        	`&Search_FromCreditDate=`+Search_FromCreditDate+
        	`&Search_ToCreditDate=`+Search_ToCreditDate+
        	`&Search_Note=`+Search_Note);
	}

	

    $('document').ready(function () {
    	var PaymentTypeIdType1 = '<?= $PaymentTypeIdType1?>';
    	
    	//Questa gestione del pulsante è necessaria perchè tutte le bonifiche diverse da Bollettini e Posta online attivano prima un Ajax che salva i dati esterni alle form su FinePayment e quindi è stato necessario mettere in fila le chiamate
    	if(PaymentTypeIdType1 == 1)				//Bonifici ecc...
    		$('#update').attr("type","button");
		else									//Bollettini e Posta online
			$('#update').attr("type","submit");
			
		$('#f_paymentfine').bootstrapValidator({
    		live: 'disabled',
    		fields: {
    			frm_field_required: {
    				selector: '.frm_field_required',
    				validators: {
    					notEmpty: {
    						message: 'Richiesto'
    					}
    				}
    			}
    		}
    	});
        $("#btn_refresh").hover(function(){
            $(this).css("color","#2684b1");
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("color","#fff");
            $(this).css("cursor","");
        });
        $('#update').attr('disabled','disabled');
        $('#search').click(function () {
        	$('#search i').removeClass('glyphicon glyphicon-search').addClass('fas fa-circle-notch fa-spin');
        	$(this).prop('disabled', true);
            var Search_Protocol = $('#Search_Protocol').val();
            var Search_Trespasser = $('#Search_Trespasser').val();
            var Search_Plate = $('#Search_Plate').val();
            var Search_Code = $('#Search_Code').val();
            var Search_Iuv = $('#Search_Iuv').val();
            var Search_Id = $('#Search_Id').val();
            $.ajax({
                url: 'ajax/search_fine.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Search_Code: Search_Code, Search_Protocol: Search_Protocol, Search_Trespasser: Search_Trespasser, Search_Plate:Search_Plate,Search_Iuv:Search_Iuv,Search_Id:Search_Id},
                success: function (data) {
                    $('#fine_content').show();
                    $('#fine_content').html(data.Trespasser);
                    $('#search i').addClass('glyphicon glyphicon-search').removeClass('fas fa-circle-notch fa-spin');
                	$('#search').prop('disabled', false);
                }
            });
        });
        $('#Search_Payer').keyup(function () {
            var Search_Payer = $(this).val();
            if (Search_Payer.length >= 3) {
                $.ajax({
                    url: 'ajax/search_payer.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Search_Payer: Search_Payer},
                    success: function (data) {
                        $('#fine_payer').show();
                        $('#fine_payer').html(data.Payer);
                    }
                });
            }
        });
        $('#PaymentTypeId').change(function(){
        	filter();
        });
        $('#filter').click(function(){
        	filter();
        });
        <?= $str_Tree ?>
        $('#delete').click(function(){
            var c = confirm("Si sta per cancellare il pagamento in maniera definitiva. Continuare?");
            if(c){
                $('#f_payment').attr('action','mgmt_payment_del_exe.php');
                $('#f_payment').submit();
            }
        });
        
        //Operazioni legate al tasto salva
        $('#update').click(function () {
        
        	//I due casi, gestiti dall'if, servono perchè le varie operazioni creano e funzionano tramite due form differenti
        	if(PaymentTypeIdType1 == 1){ //Bonifici ecc...
                	var Name =  $("#Search_Payer").val();
                    var RateNumber =  $("#PaymentFee").val();
                    var InstallmentId =  $("#InstallmentList").val();
                    var PaymentId =  $("#PaymentId").val();
                    $.ajax({
                        url: 'ajax/ajx_upd_payment_exe.php',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {PaymentTypeIdType1: PaymentTypeIdType1, Name:Name, PaymentFee:RateNumber, InstallmentId:InstallmentId, PaymentId:PaymentId},
                        success: function (data) {
                        	console.log(data);
                			$('#f_payment').submit();
                            return true;
                        }
                    });
                }
            else{	//Bollettini e posta online
                var Name =  $("#Name").val();
                var DocumentType =  $("#DocumentType").val();
                var PaymentDate =  $("#PaymentDate").val();
                var CreditDate =  $("#CreditDate").val();
                var FifthField =  $("#FifthField").val();
                var Amount =  $("#Amount").val();
                var Code =  $("#Code").val();
                var ProtocolId =  $("#ProtocolId").val();
                var VehiclePlate =  $("#VehiclePlate").val();
                var FineDate =  $("#FineDate").val();
                var PaymentId =  $("#PaymentId").val();
                $.ajax({
                    url: 'ajax/ajx_upd_payment_exe.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Name:Name, DocumentType: DocumentType, PaymentDate: PaymentDate, CreditDate: CreditDate, FifthField:FifthField, Amount:Amount, Code:Code, ProtocolId:ProtocolId, VehiclePlate:VehiclePlate, FineDate:FineDate, PaymentId:PaymentId },
                    success: function (data) {
                        $('#Id').val(data.PaymentId);
                        $('#PaymentId').val(data.PaymentId);
                        $('#Search_PaymentId').val(data.PaymentId);
                        $('#f_paymentfine').attr('action','frm_reclaim_payment_exe.php');
                        $('#f_paymentfine').submit();
                        console.log(data);
                        return true;
                    }
                });
			}
        });
        
        $('#PaymentFee').change(function(){
        	if($('#PaymentFee').val()>0){
        		showInstallmentList = true;
        		$('#InstallmentList').css('visibility','visible');
        		}
    		else{
    			showInstallmentList = false;
        		$('#InstallmentList').css('visibility','hidden');
        		}
        	
    	});	
    });
</script>
<?php
include(INC . "/footer.php");