<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_tbl_form.php");
require_once(INC."/header.php");
require_once(INC . "/initialization.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Filter = CheckValue('Filter','n');
$Base=CheckValue('Base','s');
$PageTitle = CheckValue('PageTitle', 's');

$a_Nationality = unserialize(TBL_FORM_NATIONALITY);
$a_ExcludedFormType = array(TIPO_SOTTOTESTI_FISSI_NAZ, TIPO_SOTTOTESTI_FISSI_EST);

$queryFormType = "
    SELECT Id,Title,'Categorizzati' AS Category 
    FROM FormType WHERE Id IN(
        SELECT NationalFormId FROM ViolationType WHERE RuleTypeId={$_SESSION['ruletypeid']} 
        UNION SELECT ForeignFormId FROM ViolationType Where RuleTypeId={$_SESSION['ruletypeid']} )
    UNION SELECT Id,Title,'Non categorizzati' AS Category FROM FormType WHERE Id NOT IN(
        SELECT NationalFormId FROM ViolationType 
        UNION SELECT ForeignFormId FROM ViolationType)
        AND Id NOT IN(".implode(',', $a_ExcludedFormType).")";

$a_SearchFormTypes = $rs->getResults($rs->SelectQuery($queryFormType));
$a_FormTypes = array();

foreach($a_SearchFormTypes as $result){
    $a_FormTypes[$result['Category']][$result['Id']] = $result['Title'];
}

if($Base){
    $str_Where .= " AND CityId=''";
} else {
    $str_Where .= " AND CityId='{$_SESSION['cityid']}'";
}
if($Search_LanguageId > 0){
    $str_Where .= " AND LanguageId='". $Search_LanguageId ."'";
}
if($Search_FormTypeId > 0){
    $str_Where .= " AND FormTypeId =$Search_FormTypeId";
} else {
    $str_Where .= " AND FormTypeId IN(".implode(',', array_column($a_SearchFormTypes, "Id")).")";
}
if($Search_NationalityId > 0){
    $str_Where .= " AND NationalityId='". $Search_NationalityId ."'";
}

if ($Filter == 1){
    $cls_view = new CLS_VIEW(TBL_FORM);
    $query = $cls_view->generateSelect($str_Where, null, "FT.Title");
    //echo $str_Where;
    $rs_FormDynamic = $rs->SelectQuery($query);
    $RowNumber = mysqli_num_rows($rs_FormDynamic);
    mysqli_data_seek($rs_FormDynamic, $pagelimit);
}

echo $str_out;
?>

<div class="row-fluid">
	<div class="col-sm-12">
        <div class="col-sm-12 alert alert-danger" style="display: flex;margin: 0px;align-items: center;">
            <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
            <div class="col-sm-11" style="font-size: 1.2rem;">
                Qualora siano presenti tra i testi configurati per l\'ente nell\'elenco sottostante possono esser modificati solo i testi relativi a:
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
    
	<div class="clean_row HSpace4"></div>
    
	<form id="f_tbl_form" action="tbl_form.php" method="post" autocomplete="off">
        <input type="hidden" name="Filter" value="1">
	    <input type="hidden" name="PageTitle" value="<?= $PageTitle ?>">
        <input type="hidden" name="Base" value="<?= $Base ?>">
        <div class="col-sm-11">
            <div class="col-sm-2 BoxRowLabel">
                Genere
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= $_SESSION['ruletypetitle'] ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateArraySelect($a_Nationality, true, "Search_NationalityId", "Search_NationalityId", $Search_NationalityId); ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Lingua
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= CreateSelect("Language","1=1","Title","Search_LanguageId","Id","Title",$Search_LanguageId,false); ?>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Tipo testo
            </div>
            <div class="col-sm-2 BoxRowCaption">
            	<?= CreateSelectQueryGroup($queryFormType, "Search_FormTypeId", "Search_FormTypeId", "Id", "Title", "Category", $Search_FormTypeId, false); ?>
            </div>
            <div class="col-sm-8 BoxRowLabel">
            </div>
        </div>
        <div class="col-sm-1 BoxRowFilterButton" style="height:4.5rem;">
        	<button type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary pull-left" id="search" name="search" style="margin-top:0;width:100%;height:100%;padding:0;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
        </div>
	</form>
	
	<div class="clean_row HSpace4"></div>
	
	<div class="col-sm-12">
        <div class="table_label_H col-sm-2">Tipo testo</div>
        <div class="table_label_H col-sm-2">Nazionalità</div>
        <div class="table_label_H col-sm-3">Lingua</div>
        <div class="table_label_H col-sm-4">Titolo</div>
        <div class="table_add_button col-sm-1 right">
        	<?php if(!$Base): ?>
        		<?= ChkButton($aUserButton, 'add','<a href="tbl_form_add.php'.$str_GET_Parameter.'&Filter='.$Filter.'&Base='.$Base.'"><span data-toggle="tooltip" data-placement="left" title="Aggiungi" class="tooltip-r glyphicon glyphicon-plus-sign add_button" style="height:2.5rem;margin-right:0.3rem; line-height:2.3rem;"></span></a>'); ?>
        	<?php endif; ?>
        </div>
        
        <div class="clean_row HSpace4"></div>
	
    	<?php if($Filter <= 0): ?>
        	<div class="table_caption_H col-sm-12 text-center">
    			Inserire criteri ricerca
    		</div>
        <?php else: ?>
        	<?php if ($RowNumber > 0):?>
        		<?php for ($i = 0; $i < PAGE_NUMBER; $i ++):?>
        			<?php if (! empty($r_FormDynamic = $rs->getArrayLine($rs_FormDynamic))): ?>
        				<?php $deletedCSS = $r_FormDynamic['Deleted'] > 0 ? ' style="background-color: #a0a0a0;"' : "" ?>
        		        <div class="tableRow">
                        	<div class="table_caption_H col-sm-2 text-center"<?= $deletedCSS; ?>><?= $r_FormDynamic['FormTypeTitle']; ?></div>
                        	<div class="table_caption_H col-sm-2 text-center"<?= $deletedCSS; ?>><?= $a_Nationality[$r_FormDynamic['NationalityId']]; ?></div>
                        	<div class="table_caption_H col-sm-2 text-center"<?= $deletedCSS; ?>><?= $r_FormDynamic['LanguageTitle']; ?></div>
                        	<div class="table_caption_H col-sm-1 text-center"<?= $deletedCSS; ?>><img src="<?= IMG.'/'. $aLan[$r_FormDynamic['LanguageId']]; ?>" style="width:16px;margin-top:0.2rem;" /></div>
                        	<div class="table_caption_H col-sm-4 text-center"<?= $deletedCSS; ?>><?= $r_FormDynamic['Title']; ?></div>
                        	<div class="table_caption_button col-sm-1 text-center">
                        		<?= ChkButton($aUserButton, 'viw','<a href="#"><span formtypeid="'.$r_FormDynamic['FormTypeId'].'" cityid="'.$r_FormDynamic['CityId'].'" ruletypeid="'.$r_FormDynamic['RuleTypeId'].'" nationalityid="'.$r_FormDynamic['NationalityId'].'" languageid="'.$r_FormDynamic['LanguageId'].'" deleted="'.$r_FormDynamic['Deleted'].'" data-toggle="tooltip" data-placement="top" title="Visualizza" class="tooltip-r glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>') ?>
                        		<?php if($r_FormDynamic['Deleted'] <= 0): ?>
                        			<?= ChkButton($aUserButton, 'upd','<a href="tbl_form_upd.php'.$str_GET_Parameter.'&FormTypeId='.$r_FormDynamic['FormTypeId'].'&CityId='.$r_FormDynamic['CityId'].'&RuleTypeId='.$r_FormDynamic['RuleTypeId'].'&NationalityId='.$r_FormDynamic['NationalityId'].'&LanguageId='.$r_FormDynamic['LanguageId'].'&Deleted='.$r_FormDynamic['Deleted'].'&Title='.$r_FormDynamic['Title'].'&Filter='.$Filter.'&Base='.$Base.'"><span data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>'); ?>
                        			<?php if(!$Base): ?>
                        				<?= ChkButton($aUserButton, 'del','<a href="#Delete"><span formtypeid="'.$r_FormDynamic['FormTypeId'].'" data-toggle="tooltip" data-placement="top" title="Elimina" class="tooltip-r glyphicon glyphicon-remove-sign" style="position:absolute;left:45px;top:5px;"></span></a>'); ?>
                        			<?php endif; ?>
                        		<?php endif?>
                        	</div>
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
        			<?php endif; ?>
        		<?php endfor; ?>
		    <?php else: ?>
            	<div class="table_caption_H col-sm-12">
            		Nessun record presente.
            	</div>
            	<div class="clean_row HSpace4"></div>
            <?php endif; ?>
            
            <?= CreatePagination(PAGE_NUMBER, $RowNumber, $page, impostaParametriUrl(array('Filter' => 1, 'Base' => $Base), $str_CurrentPage.$str_GET_Parameter),""); ?>
        <?php endif;?>
    </div>
</div>

<div id="testmodal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Modello</h4>
            </div>
            <div class="modal-body">

                <textarea  id='note_verbali' name='note_verbali' rows='50' class='form-control'></textarea>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>

    </div>
</div>

<script>
    $(document).ready(function () {
        $(".glyphicon-remove-sign").click(function(e){
        	e.preventDefault();
        	
            var FormTypeId = $(this).attr('formtypeid'); 
			console.log(FormTypeId);
			
			if (confirm('Sei sicuro di voler cancellare questo testo?\n\nNOTA BENE: se il testo è straniero verranno eliminati tutti gli altri testi nelle altre lingue in relazione ad esso. Continuare?')) {
				$.ajax({
	    	           type: "POST",
	    	           url: "tbl_form_del_exe.php",
	    	           data: "FormTypeId="+FormTypeId,
	    	           success: function(data)
	    	           {
	    	        	   window.location.href += "&answer=Eliminato con successo!";
	    	           },
	    	           error: function(data)
	    	           {
	    	        	   window.location.href += "&error=Qualcosa è andato storto.";
	    	           },
		         });
			} else return false;
    	    
    	});

        $(".glyphicon-eye-open").click(function(e){
        	
            var FormTypeId = $(this).attr('formtypeid');
            var CityId = $(this).attr('cityid'); 
            var RuleTypeId = $(this).attr('ruletypeid'); 
            var LanguageId = $(this).attr('languageid'); 
            var NationalityId = $(this).attr('nationalityid');
            var Deleted = $(this).attr('deleted'); 
            
			$('#testmodal').modal('show');
            $.ajax({
                url: 'ajax/ajx_search_formContent.php',
                type: 'POST',
                dataType: 'json',
                data: {FormTypeId:FormTypeId, CityId:CityId, RuleTypeId:RuleTypeId, LanguageId:LanguageId, NationalityId:NationalityId, Deleted:Deleted},
                success: function (data) {

                	$('#cke_note_verbali').remove();
                    
                    var instance = CKEDITOR.instances['note_verbali'];

                    if(instance) CKEDITOR.remove(instance);
                    
                    $('#note_verbali').html(data.Result);
                    
                    CKEDITOR.replace('note_verbali',{
                        toolbar: [
                            { name: 'document', items: [ 'Print'] }
                        ],
                        height: 1000,

                    });
                    CKEDITOR.config.readOnly = true;
                    CKEDITOR.config.allowedContent = true;
                },
                error: function (result) {
                    console.log(result);
                    alert("error");
                }
            });

    	});

        $("#Search_NationalityId").on('change',function(){
            if ($(this).val() == 1){
            	$("#Search_LanguageId").val("");
            	$('#Search_LanguageId').attr('disabled', 'disabled');
            } else {
            	$('#Search_LanguageId').removeAttr('disabled');
            }
        });

        $("#Search_NationalityId").change();
    	
    });
</script>



<?php
include(INC."/footer.php");