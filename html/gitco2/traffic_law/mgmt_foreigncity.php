<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$Search_CountryId = CheckValue('Search_CountryId','s');
$Search_Title = CheckValue('Search_Title','s');
$Search_ZIP = CheckValue('Search_ZIP','s');
$Search_LandId = CheckValue('Search_LandId','s');
$Filter = CheckValue('Filter','n');
$str_WhereT = "1=1";

if($Search_CountryId!=""){
    $str_WhereT .= " AND CountryId='". $Search_CountryId ."'";
    $str_Where .= " AND CountryId='". $Search_CountryId ."'";
    $str_CurrentPage .= "&Search_CountryId=$Search_CountryId";
}
if($Search_Title!=""){
    $str_WhereT .= " AND FC.Title LIKE '". $Search_Title ."%'";
    $str_Where .= " AND Title LIKE '". $Search_Title ."%'";
    $str_CurrentPage .= "&Search_Title=$Search_Title";
}
if($Search_ZIP!=""){
    $str_WhereT .= " AND ZIP LIKE '". $Search_ZIP ."%'";
    $str_Where .= " AND ZIP LIKE '". $Search_ZIP ."%'";
    $str_CurrentPage .= "&Search_ZIP=$Search_ZIP";
}
if($Search_LandId!=""){
    $str_Where .= " AND LandId='". $Search_LandId ."'";
    $str_CurrentPage .= "&Search_LandId=$Search_LandId";
}

$chh_FindFilter = trim($str_Where);

$str_GET_Parameter .= "&Search_CountryId=$Search_CountryId&Search_Title=$Search_Title&Search_ZIP=$Search_ZIP&Search_LandId=$Search_LandId";

$str_out .= '
            <form name="f_search_foreigncity" id="f_search_foreigncity" action="'.$str_CurrentPage.'&Filter=1" method="post">
            <div class="col-sm-12">
                <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
                    <div class="col-sm-2 BoxRowLabel">
                        Nazione
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '. CreateSelect("Country","Id NOT IN ('Z000','Z00Z')","Title","Search_CountryId","Id","Title",$Search_CountryId,false) .'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Comune
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_string text-uppercase" value="'.$Search_Title.'" name="Search_Title" id="Search_Title">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        CAP
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_string" value="'.$Search_ZIP.'" name="Search_ZIP" id="Search_ZIP">
                    </div>

                    <div class="clean_row HSpace4"></div>

                    <div class="col-sm-2 BoxRowLabel">
                        Land
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <select class="form-control" name="Search_LandId" id="Search_LandId"'.($Search_CountryId == "Z102" || $Search_CountryId == "Z112" ? "" : " disabled").'>
                            <option></option>';
                            if ($Search_CountryId != ""){
                                $rs_Land = $rs->Select("sarida.Land", 'CountryId="'.$Search_CountryId.'"');
                                while ($r_Land = mysqli_fetch_array($rs_Land)){
                                    $str_out .= ($Search_LandId == $r_Land['Id'])
                                        ? '<option selected value='.$r_Land['Id'].'>'.StringOutDB($r_Land['Title']).'</option>'
                                        : '<option value='.$r_Land['Id'].'>'.StringOutDB($r_Land['Title']).'</option>';
                                }
                            }
           $str_out .= '</select>
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
                <div class="table_label_H col-sm-4">Nazione</div>
                <div class="table_label_H col-sm-4">Comune</div>
                <div class="table_label_H col-sm-3">CAP</div>
    
                <div class="table_add_button col-sm-1 right">
                    <a href="mgmt_foreigncity_add.php'.$str_GET_Parameter.'&Filter='.$Filter.'">
                        <span data-toggle="tooltip" data-placement="left" title="Aggiungi" class="tooltip-r glyphicon glyphicon-plus-sign add_button" style="height:2.5rem;margin-right:0.3rem; line-height:2.3rem;"></span>
                    </a>
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
    $rs_ForeignCity = $rs->SelectQuery('SELECT FC.*, C.Title CountryTitle FROM ForeignCity FC JOIN Country C ON FC.CountryId=C.Id WHERE '.$str_WhereT.' ORDER BY CountryTitle LIMIT '.$pagelimit . ',' . PAGE_NUMBER);
$RowNumber = mysqli_num_rows($rs_ForeignCity);
if ($RowNumber == 0) {
    $str_out.= 'Nessun record presente';
} else {
    while ($r_ForeignCity = mysqli_fetch_array($rs_ForeignCity)) {
            $str_out.=
            '
                <div class="table_caption_H col-sm-4">' . $r_ForeignCity['CountryTitle'] .'</div>
                <div class="table_caption_H col-sm-4">' . $r_ForeignCity['Title'] .'</div>
                <div class="table_caption_H col-sm-3">' . $r_ForeignCity['Zip'].'</div>
                ';
            
    //         $str_out.=
    //         '<div class="table_caption_button col-sm-1">
    // 				'. ChkButton($aUserButton, 'viw','<a href="mgmt_document_viw.php'.$str_GET_Parameter.'"><span data-toggle="tooltip" data-placement="top" title="Visualizza" class="tooltip-r glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>');
            $str_out.=
            '<div class="table_caption_button col-sm-1">';
            
            $str_out.= ChkButton($aUserButton, 'viw','<a href="mgmt_foreigncity_viw.php'.$str_GET_Parameter.'&Id='.$r_ForeignCity['Id'].'&Filter='.$Filter.'"><span data-toggle="tooltip" data-placement="top" title="Visualizza" class="tooltip-r glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>');
            
            $str_out.= ChkButton($aUserButton, 'upd','<a href="mgmt_foreigncity_upd.php'.$str_GET_Parameter.'&Id='.$r_ForeignCity['Id'].'&Filter='.$Filter.'"><span data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>');
            
            $str_out.= ChkButton($aUserButton, 'del','<a href="mgmt_foreigncity_del.php'.$str_GET_Parameter.'&Id='.$r_ForeignCity['Id'].'&Filter='.$Filter.'"><span data-toggle="tooltip" data-placement="top" title="Elimina" class="tooltip-r glyphicon glyphicon-remove-sign" style="position:absolute;left:45px;top:5px;"></span></a>');
            
            $str_out.=
            '
    			</div>
    			<div class="clean_row HSpace4"></div>
    			';
            
        }
    }
    $table_users_number = $rs->Select('ForeignCity',$str_Where, 'Id');
    $UserNumberTotal = mysqli_num_rows($table_users_number);
    
    $str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage."&Filter=".$Filter,"");
}

$str_out.= '<div>
    </div>';


echo $str_out;
?>
<script type="text/javascript">

$(document).ready(function () {
	$('#Search_CountryId').change(function() {
		if ($(this).val() == "Z102" || $(this).val() == "Z112")
			$('#Search_LandId').prop("disabled", false);
		else
			$('#Search_LandId').prop("disabled", true);

        var CountryId = $(this).val();
            
        $.ajax({
            url: 'ajax/ajx_get_land.php',
            type: 'POST',
            dataType: 'json',
            data: {CountryId:CountryId},
            success: function (data) {
            	$('#Search_LandId').html(data.Options);
            },
            error: function (result) {
                console.log(result);
                alert("error: " + result.responseText);
            }
        });
	});

});

$(".glyphicon-search").on('click',function(e){
    $('#f_search_foreigncity').submit();
});
</script>



<?php
include(INC."/footer.php");