<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$str_Payment = "";

$Search_FineId = CheckValue('Search_FineId','n');
$str_Documentation = CheckValue('Documentation','s');



$str_Payment ='

    	<div class="row-fluid">
        	<div class="col-sm-12">';

if(isset($_POST['b_Page_Fine'])){
    $rs_Payment = $rs->Select('FinePayment', "FineId > 0 AND FineId=".$Search_FineId);
    While($r_Payment = mysqli_fetch_array($rs_Payment)){
        $str_Payment .='

  	            <div class="col-sm-12" >
                    <div class="col-sm-3 BoxRowLabel">
                        Nominativo
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                        ' . $r_Payment['Name'] . '
                    </div>
                    <div class="clean_row HSpace4"></div>  	            
                    <div class="col-sm-3 BoxRowLabel">
                        Data pagamento
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                        ' . DateOutDB($r_Payment['PaymentDate']) . '
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Data accredito
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                        ' . DateOutDB($r_Payment['CreditDate']) . '
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel">
                        Importo
                    </div>
                    <div class="col-sm-9 BoxRowCaption">
                        ' . NumberDisplay($r_Payment['Amount']) . '
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-3 BoxRowLabel" style="height:6.4rem;">
                        Note
                    </div>
                    <div class="col-sm-9 BoxRowCaption" style="height:6.4rem;">
                        ' . $r_Payment['Note'] . '
                    </div>
                </div>
                ';
    }


    $str_Payment .='    
            </div>
        </div>            

';
}else if($str_Documentation!=""){

    $str_Documentation = pathinfo($str_Documentation, PATHINFO_BASENAME);

    //TODO possibile miglioria: cercare solo i pagamenti con FineId = 0
    $rs_Payment = $rs->Select('FinePayment', "Documentation='".$str_Documentation."'");
    $r_Payment = mysqli_fetch_array($rs_Payment);


    $FineDate = ($r_Payment['FineDate']!="") ? DateOutDB($r_Payment['FineDate']) : "";

    echo json_encode(
        array(
            "Name" => $r_Payment['Name'],
            "DocumentType" => $r_Payment['DocumentType'],
            "PaymentDate" => DateOutDB($r_Payment['PaymentDate']),
            "CreditDate" => DateOutDB($r_Payment['CreditDate']),
            "FifthField" => $r_Payment['FifthField'],
            "Amount" => $r_Payment['Amount'],
            "Search_PaymentId" => $r_Payment['Id'],
            "Code" => $r_Payment['Code'],
            "ProtocolId" => $r_Payment['ProtocolId'],
            "VehiclePlate" => $r_Payment['VehiclePlate'],
            "FineDate" => $FineDate,
        )
    );
    DIE;

} else {
    $str_Payment .='

	<div class="table_label_H col-sm-1">Data pag.</div>
    <div class="table_label_H col-sm-1">Data accr.</div>
	<div class="table_label_H col-sm-1">Importo</div>
	<div class="table_label_H col-sm-2">Quinto campo</div>
    <div class="table_label_H col-sm-4">Rateizzazione</div>
    <div class="table_label_H col-sm-1">N°Rata</div>
	<div class="table_label_H col-sm-2">Note</div>
';




    $rs_fine = $rs->Select('FinePayment', "FineId=".$Search_FineId);
    $n_Number = mysqli_num_rows($rs_fine);

    

    $str_Payment .= '<div class="row-fluid">';

    if($n_Number==0) {
        $str_Payment .= '<div class="table_caption_H col-sm-12">Nessun pagamento associato</div>  ';
    }else{
        while($r_fine = mysqli_fetch_array($rs_fine)){
            if(!empty($r_fine['InstallmentId'])){
                $r_Installment = $rs->getArrayLine($rs->Select("PaymentRate", "Id = ".$r_fine['InstallmentId']));
                $InstallmentStatus = (($r_Installment['StatusRateId'] == RATEIZZAZIONE_APERTA) ?  "Rat. aperta": "Rat. chiusa");
                $InstallmentDate = DateOutDB($r_Installment['RegDate']);
                $r_DocumentType = $rs->getArrayLine($rs->Select("Document_Type","Id = ".$r_Installment['DocumentTypeId']));
                $InstallmentDocumentType = $r_DocumentType['Description'];
                $InstallmentName = $r_Installment['RateName'];
                $InstallmentPosition = $r_Installment['Position'];
                $InstallmentPaymentNumber = $r_fine['PaymentFee'];
                
                $str_Installment = $InstallmentStatus." - ".$InstallmentDate." ".$InstallmentDocumentType." ".$InstallmentName." ".$InstallmentPosition;
                }
            else 
                {
                $str_Installment = '';
                $InstallmentPaymentNumber = '';
                }
            $str_Payment .= '		
			<div class="table_caption_H col-sm-1">'.DateOutDB($r_fine['PaymentDate']).'</div>
            <div class="table_caption_H col-sm-1">'.DateOutDB($r_fine['CreditDate']).'</div>
			<div class="table_caption_H col-sm-1">'.$r_fine['Amount'].'</div>
			<div class="table_caption_H col-sm-2">'.$r_fine['FifthField'].'</div>
            <div class="table_caption_H col-sm-4">'.$str_Installment.'</div>
            <div class="table_caption_H col-sm-1">'.$InstallmentPaymentNumber.'</div>
			<div class="table_caption_H col-sm-2">'.$r_fine['Note'].'</div>
		';
        }
    }
    $str_Payment .= '</div>';

}




echo json_encode(
	array(
		"Payment" => $str_Payment,

		)
);