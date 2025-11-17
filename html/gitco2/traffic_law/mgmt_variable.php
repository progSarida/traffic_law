<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require (INC . "/initialization.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Filter = CheckValue('Filter','n');
$Base = CheckValue('Base','s');
$PageTitle = CheckValue('PageTitle', 's');

$a_Nationality = array(1 => 'Nazionale', 2 => 'Estero');

$CityId = $Base ? '' : $_SESSION['cityid'];

$str_Where = "1=1 AND CityId='".$CityId."'";

//*******************Gestione Regolamento*******************
$RuleTypeId = $_SESSION['ruletypeid'];
$RuleTypeTitle = $_SESSION['ruletypetitle'];
$str_Where .= " AND FV.RuleTypeId = $RuleTypeId";
$str_CurrentPage .= "&RuleTypeId=".$RuleTypeId;
//**********************************************************

if($Search_NationalityId > 0){
    $str_Where .= " AND FV.NationalityId=$Search_NationalityId";
}
if($Search_LanguageId > 0){
    $str_Where .= " AND FV.LanguageId=$Search_LanguageId";
}
if($Search_FormTypeId > 0){
    $str_Where .= " AND FV.FormTypeId=$Search_FormTypeId";
}
if($Search_Id != ""){
    $str_Where .= " AND FV.Id LIKE '%". $Search_Id ."%'";
}

echo $Search_FormTypeId;
        
if ($Filter == 1){
    $rs_Formvariable = $rs->SelectQuery("
        SELECT FV.*,L.Title AS LanguageTitle,R.Title AS RuleTitle,FT.Title AS FormTypeTitle FROM FormVariable FV
        LEFT JOIN ".MAIN_DB.".Rule R ON R.Id=FV.RuleTypeId
        LEFT JOIN Language L ON L.Id=FV.LanguageId
        LEFT JOIN FormType FT ON FT.Id=FV.FormTypeId
        WHERE $str_Where
        ORDER BY FV.Id,FV.Type");
    $RowNumber=mysqli_num_rows($rs_Formvariable);
    mysqli_data_seek($rs_Formvariable, $pagelimit);
}

echo $str_out;
?>

<div class="row-fluid">
	<div class="col-sm-12">
        <div class="col-sm-12 alert alert-danger" style="display: flex;margin: 0px;align-items: center;">
            <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
            <div class="col-sm-11" style="font-size: 1.2rem;">
                Qualora siano presenti tra i testi configurati per l\'ente nell\'elenco sottostante possono esser modificati solo i sottotesti relativi a:
                <ul style="list-style-position: inside;">
                    <li> Verbale Italiano generico o Verbale generico estero </li>
                    <li> 126bis/180 </li>
                    <li> Notifica PEC </li>
                    <li> Notifica verbale contratto </li>
                    <li> Avviso bonario </li>
                    <li> Richiesta rateizzazione </li>
                    <li> Esito rateizzazione </li>
                    <li> Sollecito </li>
                </ul>
				Gli altri non sono ancora gestiti
            </div>
        </div>
    </div>

	<form id="f_search" action="mgmt_variable.php" method="post" autocomplete="off">
		<input type="hidden" name="Filter" value="1">
		<input type="hidden" name="PageTitle" value="<?= $PageTitle; ?>">
		<input type="hidden" name="Base" value="<?= $Base; ?>">
        <div class="col-sm-11">
            <div class="col-sm-2 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= $RuleTypeTitle ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect($a_Nationality, true, 'Search_NationalityId', 'Search_NationalityId', $Search_NationalityId); ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Lingua
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelectConcat('SELECT Id,Title FROM Language', 'Search_LanguageId', 'Id', 'Title', $Search_LanguageId, false, null, null, $Search_NationalityId < 2) ?>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Testo
            </div>
            <div class="col-sm-2 BoxRowCaption">
    			<select id="Search_FormTypeId" name="Search_FormTypeId" class="form-control" disabled>
    				<option>Tutti</option>
    			</select>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Segnaposto sottotesto
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="text" class="form-control frm_field_string" value="<?= $Search_Id ?>" name="Search_Id">
            </div>
            <div class="col-sm-4 BoxRowLabel"></div>
        </div>
		<div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem">
            <button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="" class="tooltip-r col-sm-4 btn btn-primary" id="search" name="search" style="font-size:3rem;padding:0;margin:0;width:100%;height:100%" data-original-title="Cerca">
                <i class="glyphicon glyphicon-search"></i>
            </button>
        </div>
	</form>
	
    <div class="clean_row HSpace4"></div>

    <div class="table_label_H col-sm-1">Genere</div>
    <div class="table_label_H col-sm-1">Nazionalità</div>
    <div class="table_label_H col-sm-2">Lingua</div>
    <div class="table_label_H col-sm-2">Sottotesto</div>
    <div class="table_label_H col-sm-2">Segnaposto sottotesto</div>
    <div class="table_label_H col-sm-3">Tipo testo</div>
    <div class="table_label_H col-sm-1"></div>
    
    <div class="clean_row HSpace4"></div>
    
    <?php if ($Filter != 1):?>
        <div class="table_caption_H col-sm-12 text-center">
        	Inserire criteri di ricerca
        </div>
    <?php else: ?>
		<?php if ($RowNumber > 0):?>
			<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
				<?php $r_FormVariable = mysqli_fetch_assoc($rs_Formvariable);?>
				<?php if (! empty($r_FormVariable)): ?>
					<div class="tableRow">
            			<div class="table_caption_H col-sm-1">
							<?= $r_FormVariable['RuleTitle']; ?>
        				</div>
            			<div class="table_caption_H col-sm-1">
            				<?= $a_Nationality[$r_FormVariable['NationalityId']]; ?>
        				</div>
                    	<div class="table_caption_H col-sm-1">
                    		<?= $r_FormVariable['LanguageTitle']; ?>
                		</div>
                    	<div class="table_caption_H col-sm-1 text-center">
                    		<img src="<?= IMG . '/' . $aLan[$r_FormVariable['LanguageId']]; ?>" style="width:16px;margin-top:0.2rem" />
                		</div>
                    	<div class="table_caption_H col-sm-2">
                    		<?= $r_FormVariable['Description']; ?>
                		</div>
                    	<div class="table_caption_H col-sm-2">
                    		<?= $r_FormVariable['Id']; ?>
                		</div>
                    	<div class="table_caption_H col-sm-3">
                    		<?= $r_FormVariable['FormTypeTitle']; ?>
                		</div>
                        <div class="table_caption_button  col-sm-1">
                        	<?=ChkButton($aUserButton, 'upd', '<a href="mgmt_variable_upd.php'.$str_GET_Parameter.'&Id='.$r_FormVariable['Id'].'&Type='.$r_FormVariable['Type'].'&FormTypeId='.$r_FormVariable['FormTypeId'].'&LanguageId='.$r_FormVariable['LanguageId'].'&NationalityId='.$r_FormVariable['NationalityId'].'&RuleTypeId='.$r_FormVariable['RuleTypeId'].'&CityId='.$r_FormVariable['CityId'].'&Filter='.$Filter.'&Base='.$Base.'"><span data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="margin-top: 0.4rem;"></span></a>');?>
                        </div>
            		</div>
            		<div class="clean_row HSpace4"></div>
        		<?php endif; ?>
			<?php endfor; ?>
			<?=CreatePagination(PAGE_NUMBER, $RowNumber, $page, impostaParametriUrl(array('Filter' => 1, 'Base' => $Base), $str_CurrentPage.$str_GET_Parameter), '');?>
    	<?php else: ?>
            <div class="table_caption_H col-sm-12 text-center">
            	Nessun record presente
            </div>
    	<?php endif; ?>
    <?php endif; ?>
</div>

<script>
    $(document).ready(function () {
    	$('#Search_NationalityId, #Search_LanguageId').change();

      	$(".tableRow").mouseover(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "#cfeaf7c7");
      	});
      	$(".tableRow").mouseout(function(){
      		$( this ).find( '.table_caption_H, .table_caption_button' ).css("background-color", "");
      	});
    });

	$('#Search_NationalityId, #Search_LanguageId').change(function() {
		var CityId = '<?= $CityId ?>';
        var RuleTypeId = '<?= $RuleTypeId ?>';
        var FormTypeId = '<?= $Search_FormTypeId ?>';
        var NationalityId = $('#Search_NationalityId').val();
        if (NationalityId==1){
        	$('#Search_LanguageId').val('');
            $('#Search_LanguageId').prop('disabled', true);
            LanguageId = 1;
        } else {
        	$('#Search_LanguageId').prop('disabled', false);
        	var LanguageId = $('#Search_LanguageId').val();
        }

        if (RuleTypeId !="" && NationalityId!="" && LanguageId!=""){
            $.ajax({
                url: 'ajax/ajx_find_violationFormTypeId.php',
                type: 'POST',
                dataType: 'json',
                data: {CityId: CityId, RuleTypeId: RuleTypeId, NationalityId: NationalityId, LanguageId: LanguageId, FormTypeId: FormTypeId},
                success: function (data) {
                    $('#Search_FormTypeId').html(data.Form);
                    if (data.Form == "")  $('#Search_FormTypeId').prop('disabled', true); else $('#Search_FormTypeId').prop('disabled', false);
                },
                error: function (result) {
                    console.log(result);
                    alert("error: " + result.responseText);
                }
            });
        } else $('#Search_FormTypeId').attr('disabled', 'disabled');
        
	});
</script>

<?php
include(INC."/footer.php");
