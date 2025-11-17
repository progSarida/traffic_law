<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

function printIcon($type) {
    switch($type){
        case 'S': return '<i class="fa fa-check-circle" style="color:green;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'W': return '<i class="fa fa-exclamation-circle" style="color:orange;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        case 'D': return '<i class="fa fa-exclamation-circle" style="color:red;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
        default:  return '<i class="fa fa-question-circle" style="color:grey;font-size:1.3rem;float:left;margin-top:0.4rem;"></i>&nbsp;';
    }
}

$PageTitle = CheckValue('PageTitle','s');

$str_Where = "1=1 AND F.CityId='{$_SESSION['cityid']}'";

if ($Search_Year != ''){
    $str_Where .= " AND F.ProtocolYear=".$Search_Year;
}
if ($Search_FromFineDate != "") {
    $str_Where .= " AND F.RegDate>='".DateInDB($Search_FromFineDate)."'";
}
if ($Search_ToFineDate != "") {
    $str_Where .= " AND F.RegDate<='".DateInDB($Search_ToFineDate)."'";
}
if ($s_TypePlate == "N") {
    $str_Where .= " AND F.CountryId='Z000'";
} else {
    $str_Where .= " AND F.CountryId!='Z000'";
}

if ($Search_Type > 0 && !empty($s_TypePlate)){
    
    switch($Search_Type){
        case 1:
            $view = EXP_MAGGIOLI_INVII;
            $documentColumnName = 'Immagine';
            break;
        case 2:
            $view = EXP_MAGGIOLI_CARTOLINE;
            $documentColumnName = 'Immagine';
            break;
        case 3:
            $view = EXP_MAGGIOLI_PAGAMENTI;
            $documentColumnName = null;
            break;
        default:
            $_SESSION['Message']['Error'] = 'Tipo esportazione non riconosciuto.';
    }
    
    $cls_view = new CLS_VIEW($view);
    $rs_Table = $rs->SelectQuery($cls_view->generateSelect($str_Where, null, 'F.ProtocolId ASC'));
    $RowNumber = mysqli_num_rows($rs_Table);
}

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_exp_maggioli" action="exp_maggioli.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
		
		<div class="col-sm-11">
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect(array('N' => 'Nazionali', 'F' => 'Estere'), true, 'TypePlate', 'TypePlate', $s_TypePlate) ?>
            </div>
        	<div class="col-sm-2 BoxRowLabel font_small">
            	Tipo di esportazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect(array(1 => 'Verbali inviati', 2 => 'Notifiche', 3 => 'Pagamenti'), true, 'Search_Type', 'Search_Type', $Search_Type, false); ?>
            </div>
        	<div class="col-sm-1 BoxRowLabel">
            	Anno
            </div>
            <div class="col-sm-1 BoxRowCaption">
            	<?= CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_Year', 'Search_Year', $Search_Year != '' ? $Search_Year : $_SESSION['year'], true); ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data reg.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= (empty($Search_FromFineDate) && $Search_Type <= 0 && empty($s_TypePlate)) ? date("d/m/Y", strtotime("-1 months")) : $Search_FromFineDate; ?>" name="Search_FromFineDate" id="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data reg.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="<?= (empty($Search_ToFineDate) && $Search_Type <= 0 && empty($s_TypePlate)) ? date("d/m/Y") : $Search_ToFineDate; ?>" name="Search_ToFineDate" id="Search_ToFineDate">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
        	<div class="col-sm-12 BoxRowLabel">
            </div>
		</div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="height:4.5rem">
            	<button type="button" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" style="margin-top:0;width:100%;height:100%"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>

    	<div class="clean_row HSpace4"></div>
    
        <div class="table_label_H col-sm-1">Riga</div>
        <div class="table_label_H col-sm-2">Cronologico</div>
        <div class="table_label_H col-sm-2">Data violazione</div>
        <div class="table_label_H col-sm-2">Targa</div>
        <div class="table_label_H col-sm-4">Documento</div>
        <div class="table_label_H col-sm-1"></div>
        
        <?php if($Search_Type <= 0 || empty($s_TypePlate)): ?>
        	<div class="table_caption_H col-sm-12 text-center">
				Scegliere la nazionalità della targa e il tipo di estrazione da effettuare.
			</div>
        <?php else: ?>
        	<?php if ($RowNumber > 0): ?>
        		<?php $i=1; ?>
        		<?php while ($r_Table = $rs->getArrayLine($rs_Table)): ?>
        			<div class="clean_row HSpace4"></div>
        			
                    <div class="tableRow">
            			<div class="table_caption_H col-sm-1">
            				<?= $i++; ?>
        				</div>
                        <div class="table_caption_H col-sm-2">
                        	<?= $r_Table['Numero_Multa']; ?>
                    	</div>
                        <div class="table_caption_H col-sm-2">
                        	<?= $r_Table['Data_multa']; ?>
                    	</div>
                        <div class="table_caption_H col-sm-2">
                        	<?= $r_Table['Targa']; ?>
                    	</div>
                        <div class="table_caption_H col-sm-4" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        	<?php if(isset($documentColumnName)): ?>
                        		<?= printIcon(
                        		    !empty($r_Table[$documentColumnName]) && 
                        		    file_exists(($s_TypePlate == 'N' ? NATIONAL_FINE : FOREIGN_FINE)."/{$_SESSION['cityid']}/{$r_Table['Id']}/{$r_Table[$documentColumnName]}") ? 'S' : 'D'); ?> 
                    		    <?= $r_Table[$documentColumnName] ?? ''; ?>
                        	<?php endif; ?>
                    	</div>
                        <div class="table_caption_button col-sm-1">
                        	<?= ChkButton($aUserButton, 'viw','<a href="mgmt_fine_viw.php'.$str_GET_Parameter.'&Id='.$r_Table['Id'].'"><span class="glyphicon glyphicon-eye-open tooltip-r" title="Visualizza" data-placement="top"></span></a>'); ?>
                    	</div>
                    </div>
        		<?php endwhile; ?>
        		
                <div class="clean_row HSpace4"></div>
        	
        	    <div class="table_label_H HSpace4" style="height:8rem;">
        	    	<div style="padding-top:2rem;">
            	    	<?= ChkButton($aUserButton, 'exp','<button type="button" id="export" class="btn btn-success" style="width:19rem;"><i class="fas fa-file-csv fa-fw fa-2x" style="vertical-align:middle;"></i> Crea esportazione </button>'); ?>
        	    	</div>
                </div>
        	<?php else: ?>
    	        <div class="table_caption_H col-sm-12 text-center">
                	Nessun record presente.
                </div>
        	<?php endif; ?>
        <?php endif; ?>
    </form>
</div>

<script type="text/javascript">
	$(document).ready(function () {
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
      	
        $('#search').click(function () {
            $("#search, #export").prop('disabled', true);
            $('#search i').toggleClass('glyphicon glyphicon-search fa fa-circle-notch fa-spin');
            $('#f_exp_maggioli').submit();
        });
        
        $("#export").on('click',function(e){
            $('#f_exp_maggioli').attr('action', 'exp_maggioli_exe.php');
            $("#search, #export").prop('disabled', true);
            $(this).html('<i class="fas fa-circle-notch fa-spin fa-2x"></i>');
            $('#f_exp_maggioli').submit();
        });
	});
</script>

<?php
include(INC."/footer.php");
