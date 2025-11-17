<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Search_Description = CheckValue('Search_Description','s');
$Filter = CheckValue('Filter','n');

// if($_SESSION['usertype']>50){
//     $str_CustomerSearch = "1=1";
// } else {
//     $str_CustomerSearch = " AND CityId='".$_SESSION['cityid']."'";
// }
$order_Description = 'Description asc';
$link_Description = 'Description asc';

if(isset($_GET['order_Description']) && $_GET['order_Description']!=""){
    $order_Description = $_GET['order_Description'];
    $strOrder = $order_Description;
    $link_Description = $order_Description=='Description asc' ? $order_Description = 'Description desc': $order_Description = 'Description asc';
}

if (!isset($_GET['order_Description'])){
        $strOrder = "Id DESC";
}

$str_CitySearch = " AND CityId='".$_SESSION['cityid']."'";

if($Search_Description!=""){
    $str_Where .= " AND  Description LIKE '".$Search_Description."%'";
    $str_CurrentPage .= "&Search_Description=$Search_Description";
}


$chh_FindFilter = trim($str_Where);

$str_GET_Parameter .= "&Search_Description=$Search_Description";

$str_out .= '
            <form name="f_search_keyword" id="f_search_keyword" action="'.$str_CurrentPage.'&Filter=1" method="post">
            <div class="col-sm-12">
                <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
                    <div class="col-sm-2 BoxRowLabel">
                        Descrizione
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_string" value="'.$Search_Description.'" name="Search_Description">  
                    </div>
                </div>
                <div class="col-sm-1 BoxRow" style="height:4.6rem;">
                    <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                        <i class="glyphicon glyphicon-search" style="margin-top:0.5rem;font-size:3rem;"></i>
                    </div>
                </div>
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="clean_row HSpace4"></div>
            </form>
                            
    ';


$str_out .='
            <div class="col-sm-12">
                <div class="table_label_H col-sm-11">Descrizione
                    <a href="'.$str_CurrentPage.'&order_Description='.$link_Description.'&Filter=1">
                        <span class="glyphicon glyphicon-sort SortBtnCodice" style="color: white;position: absolute;right: 5px;top: 7px;"></span>
                    </a>
                </div>

                <div class="table_add_button col-sm-1 right">
                    '.ChkButton($aUserButton, 'add','<a href="#Add"><span data-toggle="tooltip" data-placement="left" title="Aggiungi" class="tooltip-r glyphicon glyphicon-plus-sign add_button" style="height:2.5rem;margin-right:0.3rem; line-height:2.3rem;"></span></a>').'
                </div>
                <div class="clean_row HSpace4"></div>
                ';

if($chh_FindFilter=="1=1" && $Filter!=1){
    $str_out.= '
        <div class="table_caption_H col-sm-12" style="font-size:2rem;color:orange;text-align: center">
        Inserire criteri ricerca
        </div>
        ';
} else {
    $rs_QualificationType = $rs->SelectQuery("
        SELECT * FROM QualificationType
        WHERE $str_Where $str_CitySearch
        ORDER BY $strOrder LIMIT $pagelimit,".PAGE_NUMBER);
    $RowNumber = mysqli_num_rows($rs_QualificationType);
    if ($RowNumber == 0) {
        $str_out.= 'Nessun record presente';
    } else {
        while ($r_QualificationType = mysqli_fetch_array($rs_QualificationType)) {
            
            $str_out.=
            '
                <div class="table_caption_H col-sm-11 text-center">' . $r_QualificationType['Description'] .'</div>
                ';
            
            $str_out.=
            '<div class="table_caption_button col-sm-1">';
            
            $str_out.= ChkButton($aUserButton, 'upd','<a href="#Edit"><span value="" qualificationid='.$r_QualificationType['Id'].' data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>');
            
            $str_out.=
            '
    			</div>
    			<div class="clean_row HSpace4"></div>
    			';
            
        }
    }
    $table_users_number = $rs->Select('FormKeyword',$str_Where.$str_CitySearch, 'Id');
    $UserNumberTotal = mysqli_num_rows($table_users_number);
    
    $str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage."&Filter=".$Filter,"");
}

$str_out.= '<div>
    </div>';


echo $str_out;
?>

<div id="actions" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
        	<form id="f_qualification" method="post" action="mgmt_qualification_add_exe.php" accept-charset="UTF-8" enctype='multipart/form-data'>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 id="ModalTitle" class="modal-title">Aggiungi qualifica</h4>
                </div>
                <div class="modal-body">
                    <div id="groupDescription" class="form-group">
                    	<label>Descrizione</label>
                    	<input type="text" id="Description" name="Description" class="form-control" required>
                    	<small style="display:none;" id="errorDescription" class="form-text text-danger">Qualifica già presente per questo comune.</small>
                    	<input type="hidden" name="Id" id="Id">
                    	<input type="hidden" name="OldDescription" id="OldDescription">
                    </div>
                </div>
                <div class="modal-footer">
                	<button id="SaveButton" type="submit" class="btn btn-success ">Inserisci</button>
                	<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
    $(document).ready(function () {
    	$(".glyphicon-plus-sign").on('click',function(){
    		$("#Id, #OldDescription, #Description").val("");
    		$('#ModalTitle').html('Aggiungi qualifica');
    		$('#SaveButton').html('Inserisci');
    		$("#SaveButton").prop( "disabled", false );
        	$("#errorDescription").hide();
        	$("#groupDescription").removeClass("has-error");
        	$('#f_qualification').attr('action', 'mgmt_qualification_add_exe.php');
    		$('#actions').modal('show');
    	});

    	$(".glyphicon-pencil").on('click',function(){
        	var ActionType = "fetch";
        	var QualificationId = $(this).attr('qualificationid');
        	
        	$("#Id").val(QualificationId);
        	$('#ModalTitle').html('Modifica qualifica');
        	$('#SaveButton').html('Modifica');
    		$("#SaveButton").prop( "disabled", false );
        	$("#errorDescription").hide();
        	$("#groupDescription").removeClass("has-error");
        	$('#f_qualification').attr('action', 'mgmt_qualification_upd_exe.php');

    		$.ajax({
 	            type: "POST",
 	        	dataType: 'json',
 	            url: "ajax/ajx_actions_qualification.php",
 	            data: {ActionType: ActionType, QualificationId: QualificationId},
 	            success: function(data)
 	            {
 	        		$('#Description').val(data.Description);
 	        		$('#OldDescription').val(data.Description);
 	            },
 	            error: function(data)
 	            {
 	        	    alert(data);
 	        	    console.log("error");
 	            },
			});
        	
    		$('#actions').modal('show');
    	});

    	$("#Description").on('change',function(){
    		var ActionType = "check";
    		var Description = $('#Description').val();
    		var OldDescription = $('#OldDescription').val();
    		$.ajax({
 	            type: "POST",
 	        	dataType: 'json',
 	            url: "ajax/ajx_actions_qualification.php",
 	            data: {ActionType: ActionType, Description: Description, OldDescription: OldDescription},
 	            success: function(data)
 	            {
 	            	if (data.Result == "OK"){
                    	$("#errorDescription").hide();
                    	$("#groupDescription").removeClass("has-error");
                    	$("#SaveButton").prop( "disabled", false );
 	            	}
 	            	if (data.Result == "NO"){
                    	$("#errorDescription").show();
                    	if (!$("#groupDescription").hasClass("has-error")) $("#groupDescription").addClass("has-error");
                    	$("#SaveButton").prop( "disabled", true );
 	            	}
 	            },
 	            error: function(data)
 	            {
 	        	    alert(data);
 	        	    console.log("error");
 	            },
			});
    	});
    });

    $(".glyphicon-search").on('click',function(e){

        $('#f_search_keyword').submit();
    });

	//$('#RuleTypeId').change();
</script>


<?php
include(INC."/footer.php");