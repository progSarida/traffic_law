<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');




$FromFineDate   = CheckValue('FromFineDate','s');
$ToFineDate     = CheckValue('ToFineDate','s');





$str_out .= '
<div class="row-fluid">
    <form id="f_Search" action="prn_report_exe.php" method="post">
    <div class="col-sm-12" >
        <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-1 BoxRowLabel">
                Periodo:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_date datepicker"  name="FromFineDate" type="text" style="width:9rem" value="'.$FromFineDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_date datepicker"  name="ToFineDate" type="text" style="width:9rem" value="'.$ToFineDate.'">
            </div>  
            
            <div class="col-sm-1 BoxRowCaption" style="font-size:1rem;text-align:right">
                <input type="hidden" name="NotPayed" value="0">
                <input type="checkbox" name="NotPayed" id="NotPayed" value="1">
            </div>
            <div class="col-sm-1 BoxRowCaption">
                Non pagati
            </div> 
                      
            <div class="col-sm-1 BoxRowCaption" style="font-size:1rem;text-align:right">
                <input type="hidden" name="LumpSum" value="0">
                <input type="checkbox" name="LumpSum" id="LumpSum" value="1">
            </div>
            <div class="col-sm-1 BoxRowCaption">
                CAN e CAD accorpati
            </div> 
                      
        </div>
        <div class="col-sm-1 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
            <button class="btn btn-primary" id="btn_src">
                <i class="glyphicon glyphicon-search" style="margin-top:0.6rem;font-size:2.5rem;"></i>
            </button>
        </div>
    </form>
    </div>
</div>
<div class="clean_row HSpace4"></div>
';


$str_out .= '   
    	<div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-2">Articolo</div>
				<div class="table_label_H col-sm-2">N verbali</div>
				<div class="table_label_H col-sm-2">Totale Sanzioni</div>
				<div class="table_label_H col-sm-2">Totale Ricerca</div>
				<div class="table_label_H col-sm-2">Totale Notifica</div>
				<div class="table_label_H col-sm-2">Importo totale</div>			
            </div>
            <div class="clean_row HSpace4"></div>
';


        $str_FromDate   = DateInDB($FromFineDate);
        $str_ToDate     = DateInDB($ToFineDate);






echo $str_out;

?>

    <script type="text/javascript">

        $(document).ready(function () {

            $("#btn_src").on('click',function(e){
                $('#f_Search').submit();
            });


        });
    </script>
<?php


include(INC . "/footer.php");
