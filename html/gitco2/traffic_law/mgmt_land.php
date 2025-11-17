<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Search_CountryId = CheckValue('Search_CountryId','s');
$Search_Title = CheckValue('Search_Title','s');
$Filter = CheckValue('Filter','n');
$str_WhereT = "1=1";

$order_CountryTitle = 'CountryTitle asc';
$order_Title = 'Title asc';
$link_CountryTitle= 'CountryTitle asc';
$link_Title = 'Title asc';

if(isset($_GET['order_CountryTitle']) && $_GET['order_CountryTitle']!=""){
    $order_CountryTitle = $_GET['order_CountryTitle'];
    $strOrder = $order_CountryTitle;
    $link_CountryTitle = $order_CountryTitle=='CountryTitle asc' ? $order_CountryTitle = 'CountryTitle desc': $order_CountryTitle = 'CountryTitle asc';
}

if(isset($_GET['order_Title']) && $_GET['order_Title']!=""){
    $order_Title = $_GET['order_Title'];
    $strOrder = $order_Title;
    $link_Title = $order_Title=='Title asc' ? $order_Title = 'Title desc': $order_Title = 'Title asc';
}

if (
    !isset($_GET['order_CountryTitle']) &&
    !isset($_GET['order_Title'])){
        $strOrder = "Id DESC";
}


if($Search_CountryId!=""){
    $str_WhereT .= " AND CountryId='". $Search_CountryId ."'";
    $str_Where .= " AND CountryId='". $Search_CountryId ."'";
    $str_CurrentPage .= "&Search_CountryId=$Search_CountryId";
}
if($Search_Title!=""){
    $str_WhereT .= " AND L.Title LIKE '". $Search_Title ."%'";
    $str_Where .= " AND Title LIKE '". $Search_Title ."%'";
    $str_CurrentPage .= "&Search_Title=$Search_Title";
}


$chh_FindFilter = trim($str_Where);

$str_GET_Parameter .= "&Search_CountryId=$Search_CountryId&Search_Title=$Search_Title";

$str_out .= '
            <form name="f_search_land" id="f_search_land" action="'.$str_CurrentPage.'&Filter=1" method="post">
            <div class="col-sm-12">
                <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
                    <div class="col-sm-2 BoxRowLabel">
                        Nazione
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '. CreateSelect("Country","Id IN ('Z102','Z112')","Title","Search_CountryId","Id","Title",$Search_CountryId,false) .'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Titolo
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_string text-uppercase" value="'.$Search_Title.'" name="Search_Title" id="Search_Title">
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
                <div class="table_label_H col-sm-5">Nazione
                    <a href="'.$str_CurrentPage.'&order_CountryTitle='.$link_CountryTitle.'&Filter=1">
                        <span class="glyphicon glyphicon-sort SortBtnCodice" style="color: white;position: absolute;right: 5px;top: 7px;"></span>
                    </a>
                </div>
                <div class="table_label_H col-sm-6">Titolo
                    <a href="'.$str_CurrentPage.'&order_Title='.$link_Title.'&Filter=1">
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
    $rs_Land = $rs->SelectQuery("
        SELECT L.*, C.Title CountryTitle 
        FROM sarida.Land L
        JOIN Country C ON L.CountryId=C.Id 
        WHERE $str_WhereT
        ORDER BY $strOrder LIMIT $pagelimit,".PAGE_NUMBER);
    $RowNumber = mysqli_num_rows($rs_Land);
    if ($RowNumber == 0) {
        $str_out.= 'Nessun record presente';
    } else {
        while ($r_Land = mysqli_fetch_array($rs_Land)) {
            
            $str_out.=
            '
                <div class="table_caption_H col-sm-5 text-center">' . $r_Land['CountryTitle'] .'</div>
                <div class="table_caption_H col-sm-6 text-center">' . $r_Land['Title'] .'</div>
                ';
            
            $str_out.=
            '<div class="table_caption_button col-sm-1">';
            
            $str_out.= ChkButton($aUserButton, 'upd','<a href="#Edit"><span value="" landid='.$r_Land['Id'].' data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>');
            
            $str_out.=
            '
    			</div>
    			<div class="clean_row HSpace4"></div>
    			';
            
        }
    }
    $table_users_number = $rs->Select('sarida.Land',$str_Where, 'Id');
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
        	<form id="f_land" method="post" action="mgmt_land_add_exe.php<?php echo $str_GET_Parameter."&Filter=1";?>" accept-charset="UTF-8" enctype='multipart/form-data'>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 id="ModalTitle" class="modal-title">Aggiungi land</h4>
                </div>
                <div class="modal-body">
                    <div id="groupCountry" class="form-group">
                    	<label>Nazione</label>
                    	<?php echo CreateSelect("Country","Id IN ('Z102','Z112')","Title","CountryId","Id","Title","",true) ?>
                    	<div id="CountryTitle" style="display:none;"></div>
                    </div>
                    <div id="groupTitle" class="form-group">
                    	<label>Titolo</label>
                    	<input type="text" id="Title" name="Title" class="form-control text-uppercase" required>
                    	<small style="display:none;" id="errorTitle" class="form-text text-danger">Land già presente per questa nazione.</small>
                    	<input type="hidden" name="LandId" id="LandId">
                    	<input type="hidden" name="OldTitle" id="OldTitle">
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
    		$("#LandId, #OldTitle, #Title").val("");
    		$('#ModalTitle').html('Aggiungi land');
    		$('#SaveButton').html('Inserisci');
    		$("#SaveButton").prop( "disabled", false );
    		
    		$("#CountryId").show();
    		$("#CountryId").val($("#CountryId option:first").val());
    		$("#CountryTitle").hide();
    		
        	$("#errorTitle").hide();
        	$("#groupTitle").removeClass("has-error");
        	$('#f_land').attr('action', 'mgmt_land_add_exe.php<?= $str_GET_Parameter."&Filter=1"; ?>');
    		$('#actions').modal('show');
    	});

    	$(".glyphicon-pencil").on('click',function(){
        	var ActionType = "fetch";
        	var LandId = $(this).attr('landid');
        	
        	$("#LandId").val(LandId);
        	$('#ModalTitle').html('Modifica land');
        	$('#SaveButton').html('Modifica');
    		$("#SaveButton").prop( "disabled", false );

    		$("#CountryId").hide();
    		$("#CountryTitle").show();
    		
        	$("#errorTitle").hide();
        	$("#groupTitle").removeClass("has-error");
        	$('#f_land').attr('action', 'mgmt_land_upd_exe.php<?= $str_GET_Parameter."&Filter=1"; ?>');

    		$.ajax({
 	            type: "POST",
 	        	dataType: 'json',
 	            url: "ajax/ajx_actions_land.php",
 	            data: {ActionType: ActionType, LandId: LandId},
 	            success: function(data)
 	            {
 	 	            console.log(data);
 	        		$('#Title').val(data.Title);
 	        		$('#CountryId').val(data.CountryId);
 	        		$('#CountryTitle').html(data.CountryTitle);
 	        		$('#OldTitle').val(data.Title);
 	            },
 	            error: function(data)
 	            {
 	        	    alert(data);
 	        	    console.log("error");
 	            },
			});
        	
    		$('#actions').modal('show');
    	});

    	$("#Title, #CountryId").on('change',function(){
    		var ActionType = "check";
    		var Title = $('#Title').val();
    		var OldTitle = $('#OldTitle').val();
    		var CountryId = $('#CountryId').val();
    		
    		$.ajax({
 	            type: "POST",
 	        	dataType: 'json',
 	            url: "ajax/ajx_actions_land.php",
 	            data: {ActionType: ActionType, Title: Title, OldTitle: OldTitle, CountryId: CountryId},
 	            success: function(data)
 	            {
 	 	            console.log(data);
 	            	if (data.Result == "OK"){
                    	$("#errorTitle").hide();
                    	$("#groupTitle").removeClass("has-error");
                    	$("#SaveButton").prop( "disabled", false );
 	            	}
 	            	if (data.Result == "NO"){
                    	$("#errorTitle").show();
                    	if (!$("#groupTitle").hasClass("has-error")) $("#groupTitle").addClass("has-error");
                    	$("#SaveButton").prop( "disabled", true );
 	            	}
 	            },
 	            error: function(data)
 	            {
 	        	    alert("error: " + data.responseText);
 	        	    console.log(data);
 	            },
			});
    	});
    });

    $(".glyphicon-search").on('click',function(e){

        $('#f_search_land').submit();
    });

	//$('#RuleTypeId').change();
</script>


<?php
include(INC."/footer.php");