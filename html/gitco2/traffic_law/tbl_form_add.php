<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require (INC . "/initialization.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$CityId = $_SESSION['cityid'];
$a_ExcludedFormType = array(TIPO_SOTTOTESTI_FISSI_NAZ, TIPO_SOTTOTESTI_FISSI_EST);

$queryFormType = "
    SELECT Id,Title FROM FormType WHERE Id NOT IN(
        SELECT NationalFormId FROM ViolationType
        UNION SELECT ForeignFormId FROM ViolationType)
        AND Id NOT IN(".implode(',', $a_ExcludedFormType).")";

echo $str_out;
?>
    <div class="col-md-10 col-md-offset-1">
    	<form name="f_form" id="f_form" method="post" action="tbl_form_add_exe.php" accept-charset="UTF-8" enctype='multipart/form-data'>
    		<input type="hidden" name="CityId" id="CityId" value="<?= $CityId; ?>">
    		<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
            <div class="panel panel-primary" style="margin-top: 20px;">
            	<div class="panel-heading"><h5 style="margin:0;">Parametri di testo</h5></div>
            	<div class="panel-body">
                	<div class="col-sm-12">
                        <div class="form-group col-lg-3" style="width:20%;">
                        	<label>Genere</label>
                        	<div><?= $_SESSION['ruletypetitle']; ?></div>
                        	<input type="hidden" id="RuleTypeId" name="RuleTypeId" value="<?= $_SESSION['ruletypeid'] ?>"/>
                        </div>
                        <div class="form-group col-lg-3" style="width:20%;">
                        	<label>Sottofamiglia</label>
                        	<select id="ViolationTypeId" name="ViolationTypeId" class="form-control frm_field_required">
                        	</select>
                        </div>
                        <div class="form-group col-lg-3" style="width:20%;">
                        	<label>Nazionalità</label>
                        	<select id="NationalityId" name="NationalityId" class="form-control frm_field_required">
                        		<option value="1">Nazionale</option>
                        		<option value="2">Straniero</option>
                        	</select>
                        </div>
                        <div id="Foreign" class="form-group col-lg-3" style="width:20%;display:none;">
                        	<label>Lingua</label>
                        	<?= CreateSelect("Language","1=1","Title","LanguageId","Id","Title",1,true,"","form-control frm_field_required");?>
                        </div>
                        <div id="National" class="form-group col-lg-3" style="width:20%;">
                        	<label>Lingua</label>
                        	<input readonly class="form-control frm_field_required" type="text" value=" Italiano">
                        </div>
                        <div class="form-group col-lg-3 text-center" style="width:20%;">
                        	<label>Tipo</label>
                        	<div class="BoxRow form-control" id="span_FormType" style="display: none"></div>
                        	<input type="hidden" id="FormTypeId" name="FormTypeId" value="">
                        	<?= CreateSelectQuery($queryFormType, "FormTypeSelect", "Id", "Title", "", false, null, "frm_field_required"); ?>
                        </div>
                        <div id="groupTitle" class="form-group col-lg-12">
                        	<label>Titolo</label>
                        	<input type="text" id="Title" name="Title" class="form-control frm_field_required">
                        	<small style="display:none;" id="errorTitle" class="form-text text-danger">Titolo già presente per questa combinazione di dati.</small>
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
                                <input type="submit" id="save" value="Salva" class="btn btn-success ">
                                <button type="button" class="btn btn-default" id="back">Indietro</button>
                            </div>
                        </div>
    				</div>
                </div>
                <div style="flex: 0.4;display: flex;flex-direction: column;">
	                <div class="panel panel-info" style="flex-grow: 1;margin: 0;max-height: 50%;display: flex;flex-direction: column;">
                        <div class="panel-heading"><h5 style="margin:0;">Variabili</h5></div>
                        <ul class="list" id="keywords" style="overflow: scroll;height: 500px;">
                        </ul>
                    </div>
	                <div class="panel panel-danger" style="flex-grow: 1;max-height: 50%;display: flex;flex-direction: column;">
                        <div class="panel-heading"><h5 style="margin:0;">Sottotesti</h5></div>
                        <ul class="list" id="variables" style="overflow: scroll;height: 500px;">
                        </ul>
                    </div>
                </div>
            </div>
        </form>
        <?= createModal(
            'Testo dinamico esistente', 
            'Impossibile salvare: esiste già un testo dinamico lo stesso Genere, Nazionalità,Lingua e Tipo',
            '<button type="button" class="btn btn-default" data-dismiss="modal">Chiudi</button>',
            'error',
            'ModalError'); ?>  
</div>
    
<script type="text/javascript">

function checkIfExists() {
    var NationalityId = $("#NationalityId").val();
    var RuleTypeId = $("#RuleTypeId").val();
    var LanguageId = $("#LanguageId").val();
    var FormTypeId = $("#FormTypeId").val();
    var Title = $("#Title").val();
    var CityId = '<?= $CityId?>';
    var Action = "checkPrimaryKey";
    
    return $.ajax({
        url: 'ajax/ajx_get_formData.php',
        type: 'POST',
        dataType: 'json',
        async: false,
        data: {Action: Action, NationalityId: NationalityId, RuleTypeId: RuleTypeId, FormTypeId: FormTypeId, LanguageId: LanguageId, CityId,Title},
    });
}

function getViolationTypesAjax(NationalityId, RuleTypeId, FormTypeId, LanguageId){
    return $.ajax({
        url: 'ajax/ajx_get_formData.php',
        type: 'POST',
        dataType: 'json',
        async: false,
        data: {Action: "getViolationTypes", NationalityId: NationalityId, RuleTypeId: RuleTypeId, FormTypeId: FormTypeId, LanguageId: LanguageId},
        ContentType: "application/json; charset=UTF-8"
    });
}

function checkTitle(Title, NationalityId, LanguageId, CityId, RuleTypeId){
    if (Title != "" && NationalityId != "" && LanguageId != "" && CityId != "" && RuleTypeId != ""){
        $.ajax({
            url: 'ajax/ajx_check_formTitle.php',
            type: 'POST',
            dataType: 'json',
            data: {NationalityId: NationalityId, RuleTypeId: RuleTypeId, Title: Title, LanguageId: LanguageId, CityId: CityId},
            success: function (data) {
            	if (data.Result == "OK"){
                	$("#errorTitle").hide();
                	$("#groupTitle").removeClass("has-error");
                	$("#save").prop( "disabled", false );
            	}
            	if (data.Result == "NO"){
                	$("#errorTitle").show();
                	if (!$("#groupTitle").hasClass("has-error")) $("#groupTitle").addClass("has-error");
                	$("#save").prop( "disabled", true );
            	}
            },
            error: function (result) {
                console.log(result);
                alert("error");
            }
        });
    }
}

$('document').ready(function(){
    $('#back').click(function () {
    	window.location = "<?= impostaParametriUrl(array('Filter' => 1), $str_BackPage) ?>";
        return false;
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
    }).on('success.form.bv', function(e){
    	e.preventDefault();

        $.when(checkIfExists()).done(function(data){
            if(data['exists']){
            	$('#ModalError').modal('show');
            } else {
                $('#f_form').data('bootstrapValidator').defaultSubmit();
            }
        }).fail(function (data, responseText) {
            console.log(data);
            alert("error: " + responseText);
        });
    });

	$.fn.insertAtCaret = function (myValue) {
	    myValue = myValue.trim();
	    CKEDITOR.instances['Content'].insertText(myValue);
	};	
    $(document).on("dblclick", "#keywords li b, #variables li b", function(){
    	$("#Content").insertAtCaret($(this).text());
    });
    $("#NationalityId").change(function(){
    	if ($(this).val() == 2){
    		$("#Foreign").show();
    		$("#National").hide();
    	} 
    	else {
    		$("#Foreign").hide();
    		$("#National").show();
    		$("#LanguageId").val(1);
    	}
    });
    
    $("#Title").change(function(){
        var NationalityId = $("#NationalityId").val();
        var RuleTypeId = $("#RuleTypeId").val();
        var Title = $("#Title").val();
        var LanguageId = $("#LanguageId").val();
        var CityId = $("#CityId").val();
        
		checkTitle(Title, NationalityId, LanguageId, CityId, RuleTypeId);
    });
    
    $("#NationalityId, #LanguageId, #FormTypeId,#FormTypeSelect, #ViolationTypeId").change(function(){
        var id = $(this).attr("id");
        var NationalityId = $("#NationalityId").val();
        var RuleTypeId = $("#RuleTypeId").val();
        var Title = $("#Title").val();
        var LanguageId = $("#LanguageId").val();
        var CityId = $("#CityId").val();
        var FormTypeSelect = $("#FormTypeSelect").val();
        
        if (id == "NationalityId"){
        console.log('trigger');
    		$.when(getViolationTypesAjax(NationalityId, RuleTypeId, FormTypeId, LanguageId)).done(function (data) {
                $('#ViolationTypeId').empty().append($('<option>'));
            	$.each(data.Result, function(i, v) {
                    $('#ViolationTypeId')
                        .append($('<option>', { value : v.value })
                        .text(v.text));
                    ;
                });
    		}).fail(function(data){
                console.log(data);
                alert("Errore");
                $("#Progress").hide();
    		});
        }
        
        if(id == "FormTypeSelect"){
        	console.log('trigger2');
    		$("#ViolationTypeId").prop('disabled', $(this).val().length > 0).val('').toggleClass('frm_field_required', $(this).val().length <= 0);
        }
		
        var ViolationTypeId = $("#ViolationTypeId").val();
        var FormTypeId = ViolationTypeId;
        
        //POPOLAMENTO TESTO
            if ($("#ViolationTypeId").val() != null && $("#ViolationTypeId").val() != ""){
                $("#FormTypeSelect").val('');
            	var Action = "getContent";
            	$("#Progress").show();
                $.ajax({
                    url: 'ajax/ajx_get_formData.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {Action: Action, NationalityId: NationalityId, RuleTypeId: RuleTypeId, FormTypeId: FormTypeId, LanguageId: LanguageId},
                    success: function (data) {
                        editor = CKEDITOR.instances.Content;
                        editor.setData(data.Result);
                        $("#span_FormType").html(data.FormTypeTitle);
                        $("#FormTypeId").val(data.FormTypeId);
                        $("#keywords").html(data.Keywords);
                        $("#variables").html(data.Variables);
                        $("#Progress").hide();
                        $("#span_FormType").show();
                        $("#FormTypeSelect").hide();
                    },
                    error: function (result) {
                        console.log(result);
                        alert("error: " + result.responseText);
                        $("#span_FormType").html('');
                        $("#FormTypeId").val('');
                        $("#Progress").hide();
                    }
                });
            } else {
            	 $("#span_FormType").hide();
                 $("#FormTypeSelect").show();
                 
                 if(FormTypeSelect!="") {
                	 var Action = "getContent";
                 	$("#Progress").show();
                     $.ajax({
                         url: 'ajax/ajx_get_formData.php',
                         type: 'POST',
                         dataType: 'json',
                         data: {Action: Action, NationalityId: NationalityId, RuleTypeId: RuleTypeId, FormTypeId: FormTypeSelect, LanguageId: LanguageId},
                         success: function (data) {
                             editor = CKEDITOR.instances.Content;
                             editor.setData(data.Result);
                             $("#span_FormType").html(data.FormTypeTitle);
                             $("#FormTypeId").val(data.FormTypeId);
                             $("#keywords").html(data.Keywords);
                             $("#variables").html(data.Variables);
                             $("#Progress").hide();
                         },
                         error: function (result) {
                             console.log(result);
                             alert("error: " + result.responseText);
                             $("#span_FormType").html('');
                             $("#FormTypeId").val('');
                             $("#Progress").hide();
                         }                     
                     });                            
                 }
            }
            checkTitle(Title, NationalityId, LanguageId, CityId, RuleTypeId)
    });
    
    $("#NationalityId").change();
});

</script>

<script>
    var edit = CKEDITOR.replace('Content', {

        customConfig: '',
        filebrowserBrowseUrl: './ckfinder/ckfinder.html',
        filebrowserImageBrowseUrl: './ckfinder/ckfinder.html?type=Images',
        disallowedContent: 'img{width,height,float}',
        extraAllowedContent: 'img[width,height,align];span{background}',
        extraPlugins: 'colorbutton,font,justify,print,tableresize,uploadimage,pastefromword,liststyle',
        height: 800,
        contentsCss: [
            'https://cdn.ckeditor.com/4.11.3/full-all/contents.css',
            'assets/css/pastefromword.css'
        ],
    });
    //edit.config.allowedContent = true;
    edit.config.removePlugins = 'Source';
    edit.config.forcePasteAsPlainText = true;
    edit.config.enterMode = CKEDITOR.ENTER_BR;
    edit.config.fontSize_sizes = '4/4px;5/5px;6/6px;7/7px;'+edit.config.fontSize_sizes;
    edit.execCommand( 'shiftEnter' );

 // ON CKEDITOR READY:
    CKEDITOR.on('instanceReady', function() {;
    var iframe = $('#cke_editable_area iframe').contents ();
    iframe.find ('html').css ({
    'background-color': '#b0b0b0'
    });
    iframe.find ('body').css ({
    'width': '297mm',
    'height': '210mm',
    'background-color': '#ffffff',
    'margin': '0mm',
    'padding': '5mm'
    });
    });
    
</script>

<?php
include(INC."/footer.php");
?>