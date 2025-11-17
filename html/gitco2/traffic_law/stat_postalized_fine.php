<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');



$n_Month        = CheckValue('search_month','n');
$FromFineDate   = CheckValue('FromFineDate','s');
$ToFineDate     = CheckValue('ToFineDate','s');



$a_ChkMonth = array("","","","","","","","","","","","","");

$a_ChkMonth[$n_Month] = " SELECTED ";


$str_out .= '
<div class="row-fluid">
    <form id="f_Search" action="stat_postalized_fine.php" method="post">
    <div class="col-sm-12" >
        <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">

            <div class="col-sm-2 BoxRowLabel">
                Mese riferimento
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <select name="search_month" id="search_month">
                    <option></option>
                    <option value="1"'. $a_ChkMonth[1] .'>Gennaio</option>
                    <option value="2"'. $a_ChkMonth[2] .'>Febbraio</option>
                    <option value="3"'. $a_ChkMonth[3] .'>Marzo</option>
                    <option value="4"'. $a_ChkMonth[4] .'>Aprile</option>
                    <option value="5"'. $a_ChkMonth[5] .'>Maggio</option>
                    <option value="6"'. $a_ChkMonth[6] .'>Giugno</option>
                    <option value="7"'. $a_ChkMonth[7] .'>Luglio</option>
                    <option value="8"'. $a_ChkMonth[8] .'>Agosto</option>
                    <option value="9"'. $a_ChkMonth[9] .'>Settembre</option>
                    <option value="10"'. $a_ChkMonth[10] .'>Ottobre</option>
                    <option value="11"'. $a_ChkMonth[11] .'>Novembre</option>
                    <option value="12"'. $a_ChkMonth[12] .'>Dicembre</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Periodo:
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                Da
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_date" name="FromFineDate" type="text" style="width:9rem" value="'.$FromFineDate.'">
            </div>
            <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                A
            </div>        
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_date" name="ToFineDate" type="text" style="width:9rem" value="'.$ToFineDate.'">
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


if(($n_Month==0)&&($FromFineDate=="" || $ToFineDate=="")){
    $str_out.= '<div class="table_caption_H col-sm-12">Scegliere un mese o un periodo</div>';
} else {
    if($n_Month>0){
        if($n_Month<10) $n_Month = "0".$n_Month;
        $str_FromDate = $_SESSION['year'].'-'.$n_Month.'-01';

        $str_ToDate = ($n_Month==12) ? ($_SESSION['year']+1).'-01-01' :  $_SESSION['year'].'-'.($n_Month+1).'-01';
    } else {
        $str_FromDate   = DateInDB($FromFineDate);
        $str_ToDate     = DateInDB($ToFineDate);

    }

    $str_Query = "
    SELECT 
    
        A.Id1, A.Id2, A.Id3,
        COUNT(*) Tot,
        SUM(FA.Fee) Fee, 
        SUM(FH.CustomerFee) CustomerFee,
        SUM(FH.NotificationFee) NotificationFee,
        SUM(FH.ResearchFee) ResearchFee,
        SUM(FH.CanFee) CanFee,
        SUM(FH.CadFee) CadFee
        
        FROM FineArticle FA 
        JOIN Fine F ON FA.FineId=F.Id
        JOIN Article A ON A.Id=FA.ArticleId
        JOIN FineHistory FH ON FH.FineId = F.Id
    
    WHERE
    
        F.CityId='" . $_SESSION['cityid'] . "' AND F.ProtocolYear=" . $_SESSION['year']." AND 
        FH.NotificationTypeId=6 AND FH.SendDate IS NOT NULL AND FH.SendDate<'".$str_ToDate."' AND FH.SendDate>='".$str_FromDate."' 
        AND ((F.StatusTypeId>=20 AND F.StatusTypeId<=30) OR F.StatusTypeId=12)
        
    GROUP BY    
        A.Id1, A.Id2, A.Id3
    ";
    $rs_Fine = $rs->SelectQuery($str_Query);


    $str_OutMinFee = '
            <div class="col-sm-12"> 
                <div class="table_label_H col-sm-12">ANALISI IMPORTO RIDOTTO</div>
            </div>    
            <div class="clean_row HSpace4"></div>
    ';
    $str_OutFee = '
            <div class="col-sm-12"> 
                <div class="table_label_H col-sm-12">ANALISI IMPORTO NORMALE</div>
            </div>    
            <div class="clean_row HSpace4"></div>
    ';


    $flt_TotFine = $flt_totMinFine = 0;
    while($r_Fine = mysqli_fetch_array($rs_Fine)){



        $flt_MinFee = $r_Fine['Fee']*FINE_PARTIAL;



        $flt_Notification = $r_Fine['CustomerFee']+$r_Fine['NotificationFee']+$r_Fine['CanFee']+$r_Fine['CadFee'];


        $flt_MinTotal = $flt_MinFee+$r_Fine['ResearchFee']+$flt_Notification;
        $flt_Total = $r_Fine['Fee']+$r_Fine['ResearchFee']+$flt_Notification;

        $flt_totMinFine += $flt_MinTotal;
        $flt_TotFine += $flt_Total;
        $str_OutMinFee.= '

            <div class="col-sm-12">

				<div class="table_label_H col-sm-2">'.$r_Fine['Id1'].' '.$r_Fine['Id2'].' '.$r_Fine['Id3'].'</div>
				<div class="table_label_H col-sm-2">'.$r_Fine['Tot'].'</div>
				<div class="table_label_H col-sm-2">'.NumberDisplay($flt_MinFee).'</div>
				<div class="table_label_H col-sm-2">'.NumberDisplay($r_Fine['ResearchFee']).'</div>
				<div class="table_label_H col-sm-2">'.NumberDisplay($flt_Notification).'</div>
				<div class="table_label_H col-sm-2">'.NumberDisplay($flt_MinTotal).'</div>				
            </div>
            <div class="clean_row HSpace4"></div>

        
        ';


        $str_OutFee.= '

            <div class="col-sm-12">

				<div class="table_label_H col-sm-2">'.$r_Fine['Id1'].' '.$r_Fine['Id2'].' '.$r_Fine['Id3'].'</div>
				<div class="table_label_H col-sm-2">'.$r_Fine['Tot'].'</div>
				<div class="table_label_H col-sm-2">'.NumberDisplay($r_Fine['Fee']).'</div>
				<div class="table_label_H col-sm-2">'.NumberDisplay($r_Fine['ResearchFee']).'</div>
				<div class="table_label_H col-sm-2">'.NumberDisplay($flt_Notification).'</div>
				<div class="table_label_H col-sm-2">'.NumberDisplay($flt_Total).'</div>				
            </div>
            <div class="clean_row HSpace4"></div>

        
        ';



    }
    $str_OutMinFee.= '
            <div class="col-sm-12">
				<div class="table_label_H col-sm-10">Totale verbali ridotti</div>
				<div class="table_label_H col-sm-2">'.NumberDisplay($flt_totMinFine).'</div>				
            </div>
            <div class="clean_row HSpace4"></div>    
    
    
    ';

    $str_OutFee.= '
            <div class="col-sm-12">
				<div class="table_label_H col-sm-10">Totale verbali normali</div>
				<div class="table_label_H col-sm-2">'.NumberDisplay($flt_TotFine).'</div>				
            </div>
            <div class="clean_row HSpace4"></div>    
    
    
    ';

    $str_out .= $str_OutMinFee.$str_OutFee;
}







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
