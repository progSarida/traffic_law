<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");


$a_Action = array("ins"=>"Inserimento","viw"=>"Visualizza","upd"=>"Modifica","del"=>"Cancella");
$a_Box = array("ins"=>"BoxInsert","viw"=>"BoxView","upd"=>"BoxUpdate","del"=>"BoxDelete");


$a_CssField = array(
    "int" => "frm_field_numeric",
    "str" => "frm_field_string",
    "date" => "frm_field_date",
    "time" => "frm_field_time",
    "cur" => "frm_field_currency"

);













$rs= new CLS_DB();

$aField = $_POST['aField'];
$Id = CheckValue('Id','n');
$table = $_POST['table'];
$action = $_POST['action'];



if($action!='ins'){
	$table_rows = $rs->Select($table,"Id=".$Id);

	$table_row = mysqli_fetch_array($table_rows);
}

$strJSon = "\"aField\":" . json_encode($aField);


 

$str='
<form id="Form" style="margin-top:10px">
	<input type="hidden" name="qtype" value="'.$action.'">
	<input type="hidden" name="Id" id="Id" value="'.$Id.'" />
	<input type="hidden" name="table" value="'.$table.'" />

	<div class="row-fluid">
	    <div class="col-sm-12">
            <div class="col-sm-12">
                <div class="BoxRowTitle" id="BoxRowTitle">
                '.$a_Action[$action].' record
            </div>

            <div class="clean_row HSpace4"></div>';







foreach ($aField as $F) :
	if($F['hidden']=='false'){
		$str.= '
        
            <div class="col-sm-4 BoxRowLabel">
                '.$F['label'].'
            </div>';

	if($action=='upd' || $action=='ins'){
	@$str.= '
        <div class="col-sm-8 BoxRowCaption">
            <input type="text" name="'.$F['field'].'" id="'.$F['field'].'" class="form-control '.$a_CssField[$F['css']].'"';
            $str.= ($action!='ins') ? 'value="'.$table_row[$F['field']].'"' : '';

            $str.= ' />
	    </div>
    	
	';
	}
	else{
		$str.= '
		 <div class="col-sm-8 BoxRowCaption">
		    '.$table_row[$F['field']].'
	    </div>';
	}
	$str.= '
	</div>
	<div class="clean_row HSpace4"></div>';
	}
endforeach;
if($action!='viw'){

	$str .= '
		<div class="BoxRow">
			<div class="BoxRowButton" id="BoxRowButton">
				<input type="submit" value="'.$a_Action[$action].'" class="btn btn-primary" />
			</div>
		</div>';
}
$str .= '
</form>
';

echo $str;

include(INC."/footer.php");
?>

 <script type="text/javascript">
 $('document').ready(function () {

     $('#Form').bootstrapValidator({
           
         fields: {
             frm_field_required: {
                 selector: '.frm_field_required',
                 validators: {
                     notEmpty: {
                         message: 'Richiesto'
                     }
                 }
             },

             frm_field_currency: {
                 selector: '.frm_field_currency',
                 validators: {
                     numeric: {
                         message: 'Valuta'
                     }
                 }
             },

             frm_field_numeric: {
                 selector: '.frm_field_numeric',
                 validators: {
                     numeric: {
                         message: 'Numero'
                     }
                 }
             },
         }

     }).on('success.form.bv', function(e) {

             e.preventDefault();

             var formData = {
                 "qtype": $("input[name=qtype]").val(),
                 "table": $("input[name=table]").val(),
                 <?php
                 foreach ($aField as $F):
                     echo '"'.$F['field'].'": $("input[name='.$F['field'].']").val(),';
                 endforeach;
                 echo $strJSon;
                 ?>
             };

             console.log(formData)

             $.post("ajax/table_exe.php", formData, function(data) {
                 location.reload();

             }).fail(function(err) {
                 $("#'.$a_Box[$action].'").html(data);
                 alert("NON DA CHIAMATA :"+err.statusText);
             });
         });

         $("input[type=text].frm_field_date").keyup(function (e) {

             this.value = this.value.replace(/[^0-9\/]/g,'');

             var textSoFar = $(this).val();

             if (textSoFar.length == 2 || textSoFar.length == 5) {
                 $(this).val(textSoFar + "/");
             }
             else if (textSoFar.length > 10) {
                 $(this).val(textSoFar.substr(0,10));
             }
         });

         $("input[type=text].frm_field_time").keyup(function (e) {

             var textSoFar = $(this).val();

             if (textSoFar.length == 2) {
                 $(this).val(textSoFar + ":");
             }
             else if (textSoFar.length > 5) {
                 $(this).val(textSoFar.substr(0,5));
             }
         });


         $("input[type=text].frm_field_numeric").keyup(function (e) {
             this.value = this.value.replace(/[^0-9\.\,]/g,'').replace(',','.');
         });

         $("input[type=text].frm_field_currency").keyup(function (e) {
             this.value = this.value.replace(/[^0-9\.\,]/g,'').replace(',','.');
         });


     });

</script>

