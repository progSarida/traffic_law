<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require (INC . "/initialization.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Id= CheckValue('Id','s');
$Type= CheckValue('Type','s');
$FormTypeId= CheckValue('FormTypeId','s');
$RuleTypeId= CheckValue('RuleTypeId','s');
$CityId= CheckValue('CityId','s');
$LanguageId= CheckValue('LanguageId','s');
$NationalityId= CheckValue('NationalityId','s');

$Base = CheckValue('Base','s');

$charge_rows = $rs->Select("FormVariable", "Id='$Id' AND Type=$Type AND FormTypeId=$FormTypeId AND RuleTypeId=$RuleTypeId AND LanguageId=$LanguageId AND NationalityId=$NationalityId AND CityId='$CityId'");

$row = mysqli_fetch_array($charge_rows);

//Carica il sottotesto di default per i parametri definiti se quello aperto in modifica è vuoto
if (empty(trim($row['Content']))){
    $rs_DeafultContent = $rs->Select("FormVariable", "Type=$Type AND FormTypeId=$FormTypeId AND RuleTypeId=$RuleTypeId AND LanguageId=$LanguageId AND NationalityId=$NationalityId AND CityId=''");
    $r_DefaultContent = mysqli_fetch_array($rs_DeafultContent);
    $title = utf8_encode($r_DefaultContent['Content']);
} else $title = utf8_encode($row['Content']);


$title = str_replace("", "&euro;", $title);

$rs_lang = $rs->Select("Language", "Id=".$row['LanguageId']);
$r_lang = mysqli_fetch_array($rs_lang);
$rs_rule = $rs->Select(MAIN_DB.".Rule", "Id=".$row['RuleTypeId']);
$r_rule = mysqli_fetch_array($rs_rule);
$rs_typetitle = $rs->Select("FormType", "Id=".$row['FormTypeId']);
$r_typetitle = mysqli_fetch_array($rs_typetitle);
$Nationality = ($row['NationalityId'] == 1) ? "Nazionale" : "Straniero";

$rs_Keywords = $rs->Select("FormKeyword", "FormTypeId=". $FormTypeId ." AND LanguageId=". $row['LanguageId'] ." AND RuleTypeId=". $row['RuleTypeId'] ." AND CityId='' AND NationalityId=". $NationalityId ." AND Disabled=0 AND Deleted=0 ORDER BY Title");

// $rs_Keywords = $rs->SelectQuery("SELECT DISTINCT
//     COALESCE(t2.Title, t1.Title) AS Title, COALESCE(t2.Description, t1.Description) AS Description
//     FROM FormKeyword t1
//     LEFT OUTER JOIN FormKeyword t2 on t2.CityId = '".$CityId."' AND t2.FormTypeId=". $FormTypeId ." AND t2.NationalityId=". $NationalityId ." AND t2.LanguageId=". $row['LanguageId'] ." AND t2.RuleTypeId=". $row['RuleTypeId'] ." AND t2.Disabled=0 AND t2.Deleted=0
//     WHERE t1.CityId = '' AND t1.FormTypeId=". $FormTypeId ." AND t1.NationalityId=". $NationalityId ." AND t1.LanguageId=". $row['LanguageId'] ." AND t1.RuleTypeId=". $RuleTypeId ." AND t1.Disabled=0 AND t1.Deleted=0 ORDER BY Title");
echo $str_out;
?>

<div class="row-fluid">
    <form id="f_variable" method="post" action="mgmt_variable_upd_exe.php" accept-charset="UTF-8" enctype='multipart/form-data'>
    <input type="hidden" name="Filters" value="<?= $str_GET_Parameter.'&Base='.$Base; ?>">
	<input type="hidden" id="Save" name="Save">
	<input type="hidden" name="Id" value="<?php echo $row['Id'];?>">
	<input type="hidden" name="FormTypeId" value="<?php echo $row['FormTypeId'];?>">
	<input type="hidden" name="RuleTypeId" value="<?php echo $row['RuleTypeId'];?>">
	<input type="hidden" name="LanguageId" value="<?php echo $row['LanguageId'];?>">
	<input type="hidden" name="NationalityId" value="<?php echo $row['NationalityId'];?>">
	<input type="hidden" name="CityId" value="<?php echo $row['CityId'];?>">
	<input type="hidden" name="Type" value="<?php echo $row['Type'];?>">
    <div class="col-md-10 col-md-offset-1">
    	<div class="col-md-12">
            <div class="panel panel-primary" style="margin-top: 20px;">
            	<div class="panel-heading"><h5 style="margin:0;">Parametri di sottotesto</h5></div>
            	<div class="panel-body">
                	<div class="col-sm-12">
                        <div class="form-group col-lg-4">
                        	<label>Segnaposto sottotesto</label>
                        	<div><?php echo StringOutDB($row['Id']) ?></div>
                        </div>
                        <div class="form-group col-lg-4">
                        	<label>Tipo Testo</label>
                        	<div><?php echo StringOutDB($r_typetitle['Title']) ?></div>
                        </div>
                        <div class="form-group col-lg-4">
                        	<label>Lingua</label>
                        	<div><?php echo $r_lang['Title'] ?></div>
                        </div>
                        <div class="form-group col-lg-4">
                        	<label>Sottotesto</label>
                    		<?= CreateSelectQuery("SELECT Type,Description FROM FormVariable WHERE Id='$Id' AND FormTypeId=$FormTypeId AND RuleTypeId=$RuleTypeId AND LanguageId=$LanguageId AND NationalityId=$NationalityId AND CityId='$CityId' ORDER BY Type",'Subtext', 'Type', 'Description', $row['Type'], true, 30) ?>
                        </div>
                        <div class="form-group col-lg-4">
                        	<label>Genere</label>
                        	<div><?php echo $r_rule['Title'] ?></div>
                        </div>
                        <div class="form-group col-lg-4">
                        	<label>Nazionalità</label>
                        	<div><?php echo $Nationality ?></div>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                	<label>Disabilitato</label>
                	<input type="checkbox" name="Disabled" value="1" <?= ChkCheckButton($row['Disabled']); ?>>
                </div>
            </div>
        </div>
        <div class="col-md-12" style="display:table;margin-bottom:30px">
            <div class="panel panel-primary col-sm-8" style="float:none;display:table-cell;padding:0;">
            	<div class="panel-heading"><h5 style="margin:0;">Contenuto</h5></div>
                <div class="panel-body">
                	<?php if (empty(trim($row['Content']))): ?>
        				<div class="table_caption_H col-sm-12 alert-warning">
                            <i class="fas fa-fw fa-info-circle col-sm-1" style="margin-top: 0.5rem;"></i>&nbsp;&nbsp;&nbsp;Nota: è stato caricato il contenuto del sottotesto per ente base, in quanto quello in apertura risulta essere vuoto. 
                        </div>
                	<?php endif;?>
                    <textarea  id="Content" name="Content" rows="50" class="form-control"><?php  echo $title; ?></textarea>
                    <div class="panel-footer">
                        <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                            <input type="submit" value="Modifica" class="btn btn-success">
                            <button type="button" class="btn btn-default" id="back">Indietro</button>
                        </div>
                    </div>
				</div>
            </div>
            <div class="panel panel-primary col-sm-4" style="float:none;display:table-cell;padding:0;">
                <div class="panel-heading"><h5 style="margin:0;">Variabili</h5></div>
                <ul class="list" id="keywords" style="overflow: scroll;max-height:1420px;">
    				<?php
    				while ($r_Keyword = mysqli_fetch_array($rs_Keywords)){
    				    echo '<li class="list-group-item"><b>'.StringOutDB($r_Keyword['Title']).'</b> -> '.StringOutDB($r_Keyword['Description']).'</li>';
    				}
    				?>
                </ul>
            </div>
        </div>
    </div>
    </form>
</div>
    <?php
    echo createModal(
        'Salvare prima di passare al seguente sottotesto?', 
        'Le modifiche non salvate andranno perse.', 
        '<button id="saveTrue" type="button" class="btn btn-success ">Salva e vai</button>
            	<button id="saveFalse" save="false" type="button" class="btn btn-danger ">Non salvare</button>
            	<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>',
        'question',
        'ModalConfirm'
        );
    ?>  

    <script>
        var edit = CKEDITOR.replace('Content', {

            customConfig: '',
            filebrowserBrowseUrl: './ckfinder/ckfinder.html',
            filebrowserImageBrowseUrl: './ckfinder/ckfinder.html?type=Images',
            disallowedContent: 'img{width,height,float}',
            extraAllowedContent: 'img[width,height,align];span{background}',
            extraPlugins: 'colorbutton,font,justify,print,tableresize,uploadimage,pastefromword,liststyle',
            height: 1000,
            contentsCss: [
                'https://cdn.ckeditor.com/4.11.3/full-all/contents.css',
                'assets/css/pastefromword.css'
            ],
        });
        //edit.config.allowedContent = true;
        edit.config.removePlugins = 'Source';
        edit.config.enterMode = CKEDITOR.ENTER_BR;
        edit.config.fontSize_sizes = '4/4px;5/5px;6/6px;7/7px;'+edit.config.fontSize_sizes;
        edit.execCommand( 'shiftEnter' );

        
    </script>
    
<script type="text/javascript">


$('document').ready(function(){
    $('#back').click(function () {
    	window.location = "<?= impostaParametriUrl(array('Filter' => 1, 'Base' => $Base), $str_BackPage) ?>";
        return false;
    });
	
	$.fn.insertAtCaret = function (myValue) {
	    myValue = myValue.trim();
	    CKEDITOR.instances['Content'].insertText(myValue);
	};
	
    $("#keywords li b").dblclick(function(){
    	$("#Content").insertAtCaret($(this).text());
    });

    var Type = $("#Subtext").val();
    var Typechanged;

    $("#Subtext").change(function(){
    	$('#ModalConfirm').modal('show');
    	Typechanged = $(this).val();
    	$(this).val(Type);
    	console.log(Typechanged);
    });

    $('#ModalConfirm').on('hide.bs.modal', function () {
    	$("#Save").val("");
	})

    $("#saveTrue, #saveFalse").click(function(){
    	var url = (window.location.href).replace("&Type=<?= $row['Type']; ?>", "&Type=" + Typechanged);
    	
        if ($(this).attr('id') == "saveTrue") {
        	$("#Save").val("1");
        } else {
        	$("#Save").val("0");
        }

        CKEDITOR.instances['Content'].updateElement();
		var form = $("#f_variable").serialize();

		$.ajax({
	           type: "POST",
	           url: "mgmt_variable_upd_exe.php",
	           data: form,
	           success: function(data)
	           {
		           window.location.href = url;
	           },
	           error: function(data)
	           {
	        	   alert(data);
	        	   console.log("error");
	           },
  		});
	      
    });

    
});

</script>

<?php
include(INC."/footer.php");
