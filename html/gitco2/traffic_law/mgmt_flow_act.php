<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_html.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$actType= CheckValue('actType','s');
$Id= CheckValue('Id','n');

if($actType=="updInvoice"){
    $submitText = "Modifica";
}
else if($actType=="addInvoice"){
    $submitText = "Aggiungi";
}

$rs_Row = $rs->Select('Flow_Invoices',"Id=".$Id);
$r_Row = mysqli_fetch_array($rs_Row);
$str_flowList = '';

$str_Where = "(F.PrintInvoiceId='".$Id."' OR F.PostageInvoiceId='".$Id."')";
$a_flow = $rs->getResults($rs->SelectQuery("SELECT F.*, C.ManagerName FROM V_Flow F JOIN Customer C ON F.CityId=C.CityId WHERE ".$str_Where." ORDER BY F.Year DESC, F.Number ASC"));

if(count($a_flow)>0){
    $str_flowList = '
    <div class="col-sm-12">

        <div class="col-sm-12 BoxRowLabel">
            Flussi
        </div>
    </div>
    <div class="clean_row HSpace4"></div>
    <div class="col-sm-12">
        <div class="table_label_H col-sm-3">Ente</div>
        <div class="table_label_H col-sm-1">Numero</div>
        <div class="table_label_H col-sm-1">Documento</div>
        <div class="table_label_H col-sm-1">Stampatore</div>
        <div class="table_label_H col-sm-1">Status</div>
        <div class="table_label_H col-sm-1">Data status</div>				
        <div class="table_label_H col-sm-2">Stampa e imbustamento</div>
        <div class="table_label_H col-sm-2">Spese postali</div>
    </div>
';

    $totalPrint = 0;
    $totalPostage = 0;
    $totalRecords = 0;
    for ($i=0;$i<count($a_flow);$i++) {

        if(DateOutDB($a_flow[$i]['SendDate'])!=null){
            $step = "<span class='color_green'><b>CONSEGNATO</b></span>";
            $dataStep = DateOutDB($a_flow[$i]['SendDate']);
        }
        else if(DateOutDB($a_flow[$i]['PaymentDate'])!=null){
            $step = "<span class='color_orange'><b>PAGATO</b></span>";
            $dataStep = DateOutDB($a_flow[$i]['PaymentDate']);
        }
        else if(DateOutDB($a_flow[$i]['ProcessingDate'])!=null){
            $step = "<span class='color_titolo'><b>LAVORATO</b></span>";
            $dataStep = DateOutDB($a_flow[$i]['ProcessingDate']);
        }
        else if(DateOutDB($a_flow[$i]['UploadDate'])!=null){
            $step = "<span class='color_red'><b>UPLOAD</b></span>";
            $dataStep = DateOutDB($a_flow[$i]['UploadDate']);
        }
        else{
            $step = "<span><b>CREATO</b></span>";
            $dataStep = DateOutDB($a_flow[$i]['CreationDate']);
        }

        $totalRecords+=$a_flow[$i]['RecordsNumber'];
        $printCost = 0;
        if($a_flow[$i]['PrintInvoiceId']==$Id){
            $printCost = $a_flow[$i]['PrintCost']*$a_flow[$i]['RecordsNumber'];
            $totalPrint+= $printCost;
        }

        $postage = 0;
        if($a_flow[$i]['PostageInvoiceId']==$Id){
            $postageZone0 = $a_flow[$i]['Zone0Postage']*$a_flow[$i]['Zone0Number'];
            $postageZone1 = $a_flow[$i]['Zone1Postage']*$a_flow[$i]['Zone1Number'];
            $postageZone2 = $a_flow[$i]['Zone2Postage']*$a_flow[$i]['Zone2Number'];
            $postageZone3 = $a_flow[$i]['Zone3Postage']*$a_flow[$i]['Zone3Number'];
            $postage = $postageZone0+$postageZone1+$postageZone2+$postageZone3;
            $totalPostage+= $postage;
        }

        $str_flowList.= '
        <div class="col-sm-12">
            <div class="table_caption_H col-sm-3">' . $a_flow[$i]['ManagerName'] .'</div>
            <div class="table_caption_H col-sm-1">' . $a_flow[$i]['Number'].'/'.$a_flow[$i]['Year'] .'</div>
        	<div class="table_caption_H col-sm-1">' . $a_flow[$i]['DocumentType'] .'</div>
			<div class="table_caption_H col-sm-1">' . $a_flow[$i]['Printer'] .'</div>
			<div class="table_caption_H col-sm-1">' . $step .'</div>
        	<div class="table_caption_H col-sm-1">' . $dataStep .'</div>
        	<div class="table_caption_H col-sm-1">Qt. '.$a_flow[$i]['RecordsNumber'].'</div>
			<div class="table_caption_H col-sm-1">€ '.NumberDisplay($printCost).'</div>
      	    <div class="table_caption_H col-sm-1">Qt. '.($postage).'</div>
			<div class="table_caption_H col-sm-1">€ '.NumberDisplay($postage).'</div>
			</div>
            ';
    }
    $str_flowList.= '<div class="clean_row HSpace16"></div>';
}




$str_out .='
        <form name="f_invoice" id="f_invoice" method="post" action="mgmt_flow_act_exe.php">
        <input type="hidden" name="Id" value="'.$Id.'">
        <input type="hidden" name="Filters" value="'.$str_GET_Parameter.'">
        
    	<div class="row-fluid">
        	<div class="col-sm-12">
	        	<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Numero
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				<input class="form-control frm_field_string frm_field_required" type="text" id="Number" name="Number" value="'. $r_Row['Number'] .'" style="width:8rem">
					</div>
					
        			<div class="col-sm-1 BoxRowLabel">
        				Anno
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				<input class="form-control frm_field_numeric frm_field_required" type="text" id="Year" name="Year" value="'. $r_Row['Year'] .'" style="width:8rem">
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
	        	<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Data
					</div>
					<div class="col-sm-10 BoxRowCaption">
        				<input class="form-control frm_field_date frm_field_required" type="text" id="Date" name="Date" value="'. DateOutDB($r_Row['Date']) .'" style="width:9rem">
					</div>				
  				</div>
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">

                    <div class="col-sm-2 BoxRowLabel">
                        Note
                    </div>
                    <div class="col-sm-10 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Notes" name="Notes" value="'. $r_Row['Notes'] .'" style="width:60rem">
                    </div>
                </div>
                <div class="clean_row HSpace16"></div>
  		    </div>	
            '.$str_flowList.'
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    <input class="btn btn-default" type="submit" id="update" value="'.$submitText.'" />
                    <button class="btn btn-default" id="back">Indietro</button>
                 </div>    
            </div>

    </form>	
    </div>';



echo $str_out;
?>
<script type="text/javascript">

    $('document').ready(function(){
        $('#back').click(function () {
            window.location = "<?= impostaParametriUrl(array('Filter' => 1), 'mgmt_flow.php'.$str_GET_Parameter); ?>";
            return false;
        });

        $('#f_invoice').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_required: {
                    selector: '.frm_field_required',
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
                        }
                    }
                },

                frm_field_numeric: {
                    selector: '.frm_field_numeric',
                    validators: {
                        numeric: {
                            message: 'Numero'
                        }
                    }
                },

                frm_field_currency: {
                    selector: '.frm_field_currency',
                    validators: {
                        numeric: {
                            message: 'Euro'
                        }
                    }
                },

                frm_field_date: {
                    selector: '.frm_field_date',
                    validators: {
                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }
                    }
                },
            }
        });

    });

</script>
<?php
include(INC."/footer.php");
