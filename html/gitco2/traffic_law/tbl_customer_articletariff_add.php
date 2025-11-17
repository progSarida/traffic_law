<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC."/initialization.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Search_Year = CheckValue('Search_Year','s');
$Search_CityId = CheckValue('Search_CityId','s');
$Search_RuleTypeId = CheckValue('Search_RuleTypeId','s');

$Search_Fee = CheckValue('Search_Fee','s');
$Search_MaxFee = CheckValue('Search_MaxFee','s');

$Search_Article = CheckValue('Search_Article','s');
$Search_Paragraph = CheckValue('Search_Paragraph','s');
$Search_Letter = CheckValue('Search_Letter','s');

$PageTitle = CheckValue('PageTitle','s');
$Filter = CheckValue('Filter','s');

$str_where = "1=1";

if ($Search_RuleTypeId != ''){
    $str_where .= " AND R.Id=".$Search_RuleTypeId;
}
if ($Search_CityId != ''){
    if ($Search_CityId != 'All')
    $str_where .= " AND CityId='".$Search_CityId."'";
}

if ($Search_Fee != ''){
    $str_where .= " AND Fee=".$Search_Fee;
}

if ($Search_MaxFee != ''){
    $str_where .= " AND MaxFee=".$Search_MaxFee;
}
if ($Search_Article != ''){
    $str_where .= " AND Article = '".$Search_Article."'";
}
if ($Search_Paragraph != ''){
    $str_where .= " AND Paragraph = '".$Search_Paragraph."'";
}
if ($Search_Letter != ''){
    $str_where .= " AND Letter = '".$Search_Letter."'";
}

$chh_FindFilter = trim($str_where);

if($chh_FindFilter!="1=1" || $Filter == 1){
    $rs_Article = $rs->SelectQuery(
        "SELECT A.*,AT.*,C.Title AS CityTitle,R.Title AS RuleTitle
        FROM Article A
        JOIN ArticleTariff AT ON AT.ArticleId=A.Id AND AT.Year=$Search_Year 
        JOIN ViolationType V ON A.ViolationTypeId=V.Id 
        JOIN ".MAIN_DB.".Rule R ON V.RuleTypeId=R.Id 
        LEFT JOIN sarida.City C ON A.CityId=C.Id
        WHERE ".$str_where." ORDER BY A.Article ASC,A.Paragraph ASC,A.Letter ASC");
    $RowNumber = mysqli_num_rows($rs_Article);
}


echo $str_out;

?>

<div class="row-fluid">
	<form id="f_search" action="tbl_customer_articletariff_add.php" method="post" autocomplete="off">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
        <input type="hidden" name="Filter" value="1">
        <div class="col-sm-11">
            <div class="col-sm-2 BoxRowLabel">
                Tipo di regolamento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= CreateSelect('Rule', '1=1', 'Id', 'Search_RuleTypeId', 'Id', 'Title', $Search_RuleTypeId, true) ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Ente
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <select id="Search_CityId" name="Search_CityId" class="form-control">
                	<option value="All">TUTTI</option>
                	<option<?= $Search_CityId == ENTE_BASE ? ' selected' : '' ?> value="<?= ENTE_BASE; ?>">BASE</option>
					<?php
                	$cities = $rs->SelectQuery("SELECT CityId, CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserId=".$_SESSION['userid']." AND MainMenuId=".MENU_ID. " GROUP BY CityId, CityTitle;");
                	while($city = mysqli_fetch_array($cities)){
                	    if ($Search_CityId != ""){
                	        echo '<option'.($city['CityId'] == $Search_CityId ? " selected" : "" ).' value="'.$city['CityId'].'">'.$city['CityTitle'].'</option>';
                	    } else {
                	        echo '<option value="'.$city['CityId'].'">'.$city['CityTitle'].'</option>';
                	    }
                	}
                	?>
                </select>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Anno da correggere
            </div>
            <div class="col-sm-1 BoxRowCaption">
				<?= CreateSelectQuery('SELECT DISTINCT Year FROM ArticleTariff ORDER BY Year DESC', 'Search_Year', 'Year', 'Year', ($Search_Year != '' ? $Search_Year : ($_SESSION['year'] + 1)), true, '', 'form-control'); ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
    		<div class="col-sm-1 BoxRowLabel">
    			Articolo
    		</div>
    		<div class="col-sm-1 BoxRowCaption">
    			<input value="<?= $Search_Article; ?>" type="text" class="form-control frm_field_string" name="Search_Article" id="Search_Article">
    		</div>
    		<div class="col-sm-1 BoxRowLabel">
    			Comma
    		</div>
    		<div class="col-sm-1 BoxRowCaption">
    			<input value="<?= $Search_Paragraph; ?>" type="text" class="form-control frm_field_string" name="Search_Paragraph" id="Search_Paragraph">
    		</div>
    		<div class="col-sm-1 BoxRowLabel">
    			Lettera
            </div>
    		<div class="col-sm-1 BoxRowCaption">
    			<input value="<?= $Search_Letter; ?>" type="text" class="form-control frm_field_string" name="Search_Letter" id="Search_Letter">
            </div>
    		<div class="col-sm-1 BoxRowLabel">
    			Importo min.
    		</div>
    		<div class="col-sm-2 BoxRowCaption">
    			<input value="<?= $Search_Fee; ?>" type="text" class="form-control frm_field_currency" name="Search_Fee" id="Search_Fee">
    		</div>
    		<div class="col-sm-1 BoxRowLabel">
    			Importo max.
    		</div>
    		<div class="col-sm-2 BoxRowCaption">
    			<input value="<?= $Search_MaxFee; ?>" type="text" class="form-control frm_field_currency" name="Search_MaxFee" id="Search_MaxFee">
    		</div>
        </div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </form>
        
    <div class="clean_row HSpace4"></div>
    
    <form id="f_articletariff" action="tbl_customer_articletariff_add_exe.php" method="post" autocomplete="off">
        <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <input type="hidden" name="Year" value="<?= $Search_Year; ?>">
        
        <div class="table_label_H col-sm-1">Selez. <input type="checkbox" id="checkAll"/></div>
        <div class="table_label_H col-sm-2">Articolo</div>
        <div class="table_label_H col-sm-1">Tipo Reg.</div>
        <div class="table_label_H col-sm-2">Ente</div>
        <div class="table_label_H col-sm-4">Testo</div>
        <div class="table_label_H col-sm-1">Min</div>
        <div class="table_label_H col-sm-1">Max</div>
        
        <?php if($chh_FindFilter=="1=1" && $Filter!=1):?>
	        <div class="table_caption_H col-sm-12 text-center" style="font-size:2rem;color:orange;">
        		Inserire criteri ricerca
        	</div>
        <?php else: ?>
        	<?php if ($RowNumber > 0): ?>
            	<?php while ($r_Article = mysqli_fetch_assoc($rs_Article)): ?>
        			<div class="clean_row HSpace4"></div>
                    <div class="tableRow">
        				<div class="table_caption_H col-sm-1 text-center">
        					<input type="checkbox" name="check[]" value="<?= $r_Article['ArticleId']; ?>">
        				</div>
                    	<div class="col-sm-2 text-right" style="padding:0">
                    		<div class="table_caption_H col-sm-4" style="padding-right: 0.5rem;">
                    			<?= $r_Article['Article']; ?>
                			</div>
                    		<div class="table_caption_H col-sm-4" style="padding-right: 0.5rem;">
                				<?= $r_Article['Paragraph']; ?>
            				</div>
                    		<div class="table_caption_H col-sm-4" style="padding-right: 0.5rem;">
                    			<?= $r_Article['Letter']; ?>
                			</div>
                    	</div>
        				<div class="table_caption_H col-sm-1">
        					<?= $r_Article['RuleTitle']; ?>
        				</div>
        				<div class="table_caption_H col-sm-2">
        					<?= $r_Article['CityId'].' '.$r_Article['CityTitle']; ?>
        				</div>
        				<div class="table_caption_H col-sm-4" style="text-overflow: ellipsis;overflow: hidden;white-space: nowrap;">
        					<?= $r_Article['DescriptionIta']; ?>
        				</div>
        				<div class="table_caption_H col-sm-1 text-right" style="padding-right: 0.5rem;">
        					<?= $r_Article['Fee'].' €'; ?>
        				</div>
        				<div class="table_caption_H col-sm-1 text-right" style="padding-right: 0.5rem;">
        					<?= $r_Article['MaxFee'].' €'; ?>
        				</div>
                    </div>
            	<?php endwhile; ?>
            	<div class="clean_row HSpace4"></div>
            	
	            <div class="table_label_H col-sm-12">Parametri per l'aggiornamento</div>
            
                <div class="clean_row HSpace4"></div>
                        		
            	<div class="col-sm-2 BoxRowLabel">
            		Nuovo minimo edittale
            	</div>
            	<div class="col-sm-2 BoxRowCaption">
            		<input name="NewFee" type="text" class="form-control frm_field_currency">
            	</div>
            	<div class="col-sm-2 BoxRowLabel">
            		Nuovo massimo edittale
            	</div>
            	<div class="col-sm-2 BoxRowCaption">
            		<input name="NewMaxFee" type="text" class="form-control frm_field_currency">
            	</div>
            	<div class="col-sm-4 BoxRowLabel">
            	</div>
            	
            	<div class="clean_row HSpace4"></div>
            	
        	    <div class="table_label_H HSpace4" style="height:8rem;">
					<?= ChkButton($aUserButton, 'add','<button type="submit" id="update" class="btn btn-success" style="margin-top:2rem;" disabled>Modifica</button>'); ?>
                </div>
            <?php else: ?>
    	        <div class="table_caption_H col-sm-12 text-center">
                	Nessun record presente
                </div>
            <?php endif; ?>
        <?php endif; ?>
	</form>
</div>

<script type="text/javascript">

	$(document).ready(function () {

	    $('#f_articletariff').bootstrapValidator({
	        live: 'disabled',
	        fields: {
	            frm_field_required: {
	                selector: '#f_articletariff .frm_field_required',
	                validators: {
	                    notEmpty: {
	                        message: 'Richiesto'
	                    }
	                }
	            },
	        }
	    });

        $('#checkAll').click(function() {
            $('input[name=check\\[\\]]').prop('checked', this.checked);
            $("#f_articletariff").trigger( "check" );
        });

        $('input[name=check\\[\\]]').change(function() {
            $("#f_articletariff").trigger( "check" );
        });

        $("#f_articletariff").on('check', function(){
        	if ($('input[name=check\\[\\]]:checked').length > 0)
        		$('#update').prop('disabled', false);
        	else
        		$('#update').prop('disabled', true);
        });
	    
      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});

        $("#f_search").on('submit', function(e){
            if ($("#Search_Article").val() == "" && $("#Search_Paragraph").val() == "" && $("#Search_Letter").val() == "" && $("#Search_Fee").val() == "" && $("#Search_MaxFee").val() == ""){
                alert('Inserire almeno un filtro di ricerca per articolo o importo');
                e.preventDefault();
            } else {
            	return true;
            }
        });
	});
</script>

<?php
include(INC."/footer.php");

