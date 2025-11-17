<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Search_FormTypeId = CheckValue('Search_FormTypeId','s');
$Search_NationalityId = CheckValue('Search_NationalityId','s');
$Search_LanguageId = CheckValue('Search_LanguageId','s');
$Search_RuleType = CheckValue('Search_RuleType','s');
$Search_Title = CheckValue('Search_Title','s');
$Filter = CheckValue('Filter','n');

// if($_SESSION['usertype']>50){
//     $str_CustomerSearch = "1=1";
// } else {
//     $str_CustomerSearch = " AND CityId='".$_SESSION['cityid']."'";
// }

//$str_CitySearch = " AND CityId='".$_SESSION['cityid']."'";

if($Search_RuleType!=""){
    $str_Where .= " AND RuleTypeId='". $Search_RuleType ."'";
    $str_CurrentPage .= "&Search_RuleType=$Search_RuleType";
}
if($Search_NationalityId!=""){
    $str_Where .= " AND NationalityId='". $Search_NationalityId ."'";
    $str_CurrentPage .= "&Search_NationalityId=$Search_NationalityId";
}
if($Search_LanguageId!=""){
    $str_Where .= " AND LanguageId='". $Search_LanguageId ."'";
    $str_CurrentPage .= "&Search_LanguageId=$Search_LanguageId";
}
if($Search_FormTypeId!=""){
    $str_Where .= " AND FormTypeId='". $Search_FormTypeId ."'";
    $str_CurrentPage .= "&Search_FormTypeId=$Search_FormTypeId";
}
if($Search_Title!=""){
    $str_Where .= " AND Title='". $Search_Title ."'";
    $str_CurrentPage .= "&Search_Title=$Search_Title";
}



$chh_FindFilter = trim($str_Where);

$str_GET_Parameter .= "&Search_NationalityId=$Search_NationalityId&Search_RuleType=$Search_RuleType&Search_FormTypeId=$Search_FormTypeId&Search_LanguageId=$Search_LanguageId&Search_Title=$Search_Title&Search_RuleType=$Search_RuleType";

$str_out .= '
            <form name="f_search_keyword" id="f_search_keyword" action="'.$str_CurrentPage.'&Filter=1" method="post">
            <div class="col-sm-12">
                <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
                    <div class="col-sm-2 BoxRowLabel">
                        Genere
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '. CreateSelect(MAIN_DB.".Rule","1=1","Title","Search_RuleType","Id","Title",$Search_RuleType,false,20) .'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Nazionalità
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <select id="Search_NationalityId" name="Search_NationalityId" class="form-control">
                            <option></option>
                            <option value="1" '.(($Search_NationalityId == "1") ? 'selected' : '').'>Nazionale</option>
                            <option value="2" '.(($Search_NationalityId == "2") ? 'selected' : '').'>Straniero</option>
                        </select>
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Lingua
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '. CreateSelect("Language","1=1","Title","Search_LanguageId","Id","Title",$Search_LanguageId,false,20) .'
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <div class="col-sm-2 BoxRowLabel">
                        Tipo Testo
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <select id="Search_FormTypeId" class="form-control" name="Search_FormTypeId" disabled></select>
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Titolo
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_string" value="'.$Search_Title.'" name="Search_Title">
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
                <div class="table_label_H col-sm-3">Titolo</div>
                <div class="table_label_H col-sm-1">Genere</div>
                <div class="table_label_H col-sm-2">Nazionalità</div>
                <div class="table_label_H col-sm-1">Lingua</div>
                <div class="table_label_H col-sm-4">Titolo testo</div>
    
                <div class="table_add_button col-sm-1 right">
                    <a href="mgmt_keyword_add.php'.$str_GET_Parameter.'&Filter='.$Filter.'">
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
    $rs_Formkeywords = $rs->SelectQuery("
        SELECT * FROM FormKeyword
        WHERE $str_Where AND CityId=''
        ORDER BY Title LIMIT $pagelimit,".PAGE_NUMBER);
    $RowNumber = mysqli_num_rows($rs_Formkeywords);
    if ($RowNumber == 0) {
        $str_out.= 'Nessun record presente';
    } else {
        while ($r_Formkeywords = mysqli_fetch_array($rs_Formkeywords)) {
            //TEMP
            $rs_rule = $rs->Select("Rule", "Id=".$r_Formkeywords['RuleTypeId']);
            $r_rule = mysqli_fetch_array($rs_rule);
            $nationality = ($r_Formkeywords['NationalityId'] == 1 ? "Nazionale" : "Straniero");
            $rs_lang = $rs->Select("Language", "Id=".$r_Formkeywords['LanguageId']);
            $r_lang = mysqli_fetch_array($rs_lang);
            $rs_type = $rs->Select("FormType", "Id=".$r_Formkeywords['FormTypeId']);
            $r_type = mysqli_fetch_array($rs_type);
            //
            if ($r_Formkeywords['Deleted'] != 1){
                $str_status = ($r_Formkeywords['Disabled'] == 1)
                ? '<span class="glyphicon glyphicon-ban-circle" aria-hidden="true" style="float: right;color: red;font-size: 1.3rem;"></span>'
                    : '<span class="glyphicon glyphicon-ok-circle" aria-hidden="true" style="float: right;color: green;font-size: 1.3rem;"></span>';
            } else $str_status = "";
            
            $deleted = ($r_Formkeywords['Deleted'] == 1)
            ? 'style="background-color: #a0a0a0;"'
                : '';
                
                $str_out.=
                '
                <div class="table_caption_H col-sm-3" '.$deleted.'>' . $r_Formkeywords['Title'] .$str_status.'</div>
                <div class="table_caption_H col-sm-1" '.$deleted.'>' . $r_rule['Title'] .'</div>
                <div class="table_caption_H col-sm-2" '.$deleted.'>' . $nationality  .'</div>
                <div class="table_caption_H col-sm-1" '.$deleted.'>' . $r_lang['Title'].'</div>
                <div class="table_caption_H col-sm-4" '.$deleted.'>' . $r_type['Title'] .'</div>
                ';
                
                //         $str_out.=
                //         '<div class="table_caption_button col-sm-1">
                // 				'. ChkButton($aUserButton, 'viw','<a href="mgmt_document_viw.php'.$str_GET_Parameter.'"><span data-toggle="tooltip" data-placement="top" title="Visualizza" class="tooltip-r glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>');
                $str_out.=
                '<div class="table_caption_button col-sm-1">';
                
                //             $str_out.= ChkButton($aUserButton, 'viw','<a href="mgmt_rule_viw.php'.$str_GET_Parameter.'&Filter='.$Filter.'"><span data-toggle="tooltip" data-placement="top" title="Visualizza" class="tooltip-r glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>');
                
                //             $str_out.= ChkButton($aUserButton, 'upd','<a href="mgmt_rule_upd.php'.$str_GET_Parameter.'&Filter='.$Filter.'"><span data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>');
                $str_out.= ChkButton($aUserButton, 'viw','<a href="mgmt_keyword_viw.php'.$str_GET_Parameter.'&Id='.$r_Formkeywords['Id'].'&Filter='.$Filter.'"><span data-toggle="tooltip" data-placement="top" title="Visualizza" class="tooltip-r glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>');
                
                if ($r_Formkeywords['Deleted'] != 1){
                    $str_out.= ChkButton($aUserButton, 'upd','<a href="mgmt_keyword_upd.php'.$str_GET_Parameter.'&Id='.$r_Formkeywords['Id'].'&Filter='.$Filter.'"><span data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>');
                }
                
                if ($r_Formkeywords['Deleted'] != 1){
                    $str_out.= ChkButton($aUserButton, 'del','<a href="#Delete" <span keywordid="'.$r_Formkeywords['Id'].'" data-toggle="tooltip" data-placement="top" title="Elimina" class="tooltip-r glyphicon glyphicon-remove-sign" style="position:absolute;left:45px;top:5px;"></span></a>');
                }
                
                $str_out.=
                '
    			</div>
    			<div class="clean_row HSpace4"></div>
    			';
                
        }
    }
    $table_users_number = $rs->Select('FormKeyword',$str_Where." AND CityId=''", 'Id');
    $UserNumberTotal = mysqli_num_rows($table_users_number);
    
    $str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage."&Filter=".$Filter,"");
}

$str_out.= '<div>
    </div>';


echo $str_out;
?>
<script>
    $(document).ready(function () {
        $(".glyphicon-remove-sign").click(function(e){
        	e.preventDefault();
        	
            var id = $(this).attr('keywordid'); 
			console.log(id);
			
			if (confirm('Sei sicuro di voler cancellare questa keyword?')) {
				$.ajax({
	    	           type: "POST",
	    	           url: "mgmt_keyword_del_exe.php",
	    	           data: "Id="+id,
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
    });

    $(".glyphicon-search").on('click',function(e){

        $('#f_search_keyword').submit();
    });

	$('#Search_RuleType, #Search_NationalityId, #Search_LanguageId').change(function() {

        var RuleTypeId = $('#Search_RuleType').val();
        var NationalityId = $('#Search_NationalityId').val();
        var LanguageId = $('#Search_LanguageId').val();

        if (RuleTypeId !="" && NationalityId!="" && LanguageId!=""){
            $.ajax({
                url: 'ajax/ajx_find_violationFormTypeId.php',
                type: 'POST',
                dataType: 'json',
                data: {RuleTypeId: RuleTypeId, NationalityId: NationalityId, LanguageId: LanguageId},
                success: function (data) {
                    
                    $('#Search_FormTypeId').html(data.Form);
                    if (data.Form == "")  $('#Search_FormTypeId').attr('disabled', 'disabled'); else $('#Search_FormTypeId').removeAttr('disabled');

                },
                error: function (data) {
                    console.log(data);
                    alert("error");
                }
            });
        } else $('#Search_FormTypeId').attr('disabled', 'disabled');
        
	});

	//$('#RuleTypeId').change();
</script>


<?php
include(INC."/footer.php");