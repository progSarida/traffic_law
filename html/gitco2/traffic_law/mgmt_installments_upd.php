<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(CLS."/cls_installment.php");
require_once(INC."/function.php");
require_once(PGFN."/fn_mgmt_installments.php");
require_once(INC."/header.php");
require_once(INC.'/menu_'.$_SESSION['UserMenuType'].'.php');

$FineId = CheckValue("FineId", 'n');
$instalmentId = CheckValue("Id", 'n');
$InstallmentPage = "mgmt_installments.php";


require(INC."/fine_section/rate_data.php");

$str_out .= $str_Rate_data_menu;

echo $str_out;
?>
<div id="DocumentationSection" class="col-sm-6">
    <div class="col-sm-12 DocumentPreview" >
        <div class="col-sm-12 BoxRowLabel" style="text-align:center">
            DOCUMENTAZIONE
        </div>
    </div>
    <div id="backPanel" class="col-sm-12 BoxRow DocumentPreview" style="width:100%;height:60rem; position:relative;">
        <div id="preview_img" style="height:60rem;">
            <iframe id="preview_iframe_img" style="height:60rem;width:100%;" ></iframe>
        </div>
    </div>
</div>
<div class="col-sm-12">
    <div class="table_label_H HSpace4" style="height:8rem;">
    	<button type="button" id="back" class="btn btn-default" style="margin-top:2rem;">Indietro</button>
    </div>
</div>
<script type="text/javascript">
	var fine_id = <?=$FineId?>;
	var installment_id = <?=$instalmentId?>;
	<?= $instalmentScript;  ?>
	
	$('#back').on('click', function(){
		window.location="<?= impostaParametriUrl(array('Filter' => 1), 'mgmt_installments.php'.$str_GET_Parameter); ?>";
	});
	var numRate = '<?= $n_Rate?>';
	var file = '';
    if(webSignedRequestFile!='')
        file = webSignedRequestFile;
    else if(requestFile != '')
        file = webRequestFile;
    
	var rateWindowHeight = $('#rateWindow').height();
	if(file != ''){
        $('#preview_iframe_img').attr('src',file);
   		}
	else{
    	$('#preview_iframe_img').hide();
    	$('#backPanel').css('height',763);
		}
</script>
<?php
require_once(INC."/footer.php");