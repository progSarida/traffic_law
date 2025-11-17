<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require (INC . "/initialization.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$CityId = $_SESSION['cityid'];

$DisputeId = $_REQUEST['DisputeId'];
$FineId = $_REQUEST['FineId'];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//echo $str_out;

?>
    <div class="col-md-10 col-md-offset-1">
    	<form name="f_form" id="f_form" method="post" action="" accept-charset="UTF-8" enctype='multipart/form-data'>
            <div class="col-md-12">
                <input type="hidden" name="DisputeId" id="DisputeId" value="<?= $DisputeId; ?>">
                <input type="hidden" name="FineId" id="FineId" value="<?= $FineId; ?>">
                <input type="hidden" name="CityId" id="CityId" value="<?= $CityId; ?>">
                <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
                <div class="panel panel-primary" style="margin-top: 20px;">
<!--                    <div class="panel-heading"><h5 style="margin:0;">Parametri di testo</h5></div>-->
                    <div class="panel-body">
                        <div id="groupTitle" class="form-group col-lg-12">
                            <label>Nome file</label>
                            <input type="text" id="FileName" name="FileName" class="form-control frm_field_required">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-12" style="display: flex;margin-bottom: 30px;">
                <div class="panel panel-primary" style="flex: 1;display: flex;flex-direction: column;">
                    <div class="panel-heading">
                    	<h5 class ="col-sm-11"style="margin:0;">Testo</h5>
                    	<img src="img/progress.gif" style="display: none;width: 1.5rem;filter: brightness(100);" id="Progress" class="col-sm-1">
                	</div>
                	<input type="hidden" name="CityId" value="<?=  $CityId; ?>">
                    <div class="panel-body">  					
                        <textarea  id="Content" name="Content" rows="50" class="form-control"></textarea>
                        <div class="panel-footer">
                            <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                                <input type="button" value="Salva" class="btn btn-success ">
                                <button type="button" class="btn btn-default" id="back">Indietro</button>
                            </div>
                        </div>
    				</div>
                </div>
                <div style="flex: 0.4;display: flex;flex-direction: column;">
	                <div class="panel panel-info" style="flex-grow: 1;margin: 0;max-height: 98%;display: flex;flex-direction: column;">
                        <div class="panel-heading"><h5 style="margin:0;">Variabili</h5></div>
                        <ul class="list" id="keywords" style="overflow: scroll;height: 100%;">
                        </ul>
                    </div>
<!--	                <div class="panel panel-danger" style="flex-grow: 1;max-height: 50%;display: flex;flex-direction: column;">-->
<!--                        <div class="panel-heading"><h5 style="margin:0;">Sottotesti</h5></div>-->
<!--                        <ul class="list" id="variables" style="overflow: scroll;height: 500px;">-->
<!--                        </ul>-->
<!--                    </div>-->
                </div>
            </div>
        </form>
    </div>

    
<script type="text/javascript">

$('document').ready(function(){
    $('#back').click(function () {
        window.location.href = "mgmt_dispute_upd.php<?=$str_BackPage?>&Id=<?=$DisputeId;?>&FineId=<?=$FineId;?>";
    });
    $('#f_form').bootstrapValidator({
        live: 'disabled',
        group: 'null',
        fields: {
            frm_field_required: {
                selector: '.frm_field_required',
                validators: {
                    notEmpty: {
                        message: 'Richiesto'
                    }
                }
            },
        }
    });

	$.fn.insertAtCaret = function (myValue) {
	    myValue = myValue.trim();
	    CKEDITOR.instances['Content'].insertText(myValue);
	};	
    $(document).on("dblclick", "#keywords li b, #variables li b", function(){
    	$("#Content").insertAtCaret($(this).text());
    });
});

</script>

<script>
    var edit = CKEDITOR.replace('Content', {

        customConfig: '',
        filebrowserBrowseUrl: './ckfinder/ckfinder.html',
        filebrowserImageBrowseUrl: './ckfinder/ckfinder.html?type=Images',
        disallowedContent: 'img{width,height,float}',
        extraAllowedContent: 'img[width,height,align];span{background}',
        // extraPlugins: 'colorbutton,font,justify,print,tableresize,uploadimage,uploadfile,pastefromword,liststyle',
        height: 800
    });
    //edit.config.allowedContent = true;
    edit.config.removePlugins = 'Source';
    edit.config.forcePasteAsPlainText = true;
    edit.config.enterMode = CKEDITOR.ENTER_BR;
    edit.execCommand( 'shiftEnter' );


    
</script>

<?php
include(INC."/footer.php");
?>