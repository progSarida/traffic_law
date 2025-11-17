<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$Search_CityId = CheckValue('Search_CityId','s');
$Search_RuleType = CheckValue('Search_RuleType','s');
$Filter = CheckValue('Filter','n');

if($_SESSION['usertype']>50){
    $str_CustomerSearch = "1=1";
} else {
    $str_CustomerSearch = "CityId='".$_SESSION['cityid']."'";
}


$CityId = $_SESSION['cityid'];

if($Search_CityId!=""){
    $str_Where .= " AND CityId='". $Search_CityId ."'";
    $str_CurrentPage .= "&Search_CityId=$Search_CityId";
}
if($Search_RuleType!=""){
    $str_Where .= " AND Id='". $Search_RuleType ."'";
    $str_CurrentPage .= "&Search_RuleType=$Search_RuleType";
}

$chh_FindFilter = trim($str_Where);

$str_GET_Parameter .= "&Search_CityId=$Search_CityId&Search_RuleType=$Search_RuleType";

$str_out .= '
            <form name="f_search_article" id="f_search_article" action="'.$str_CurrentPage.'&Filter=1" method="post">
            <div class="col-sm-12">
                <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
                    <div class="col-sm-2 BoxRowLabel">
                        Ente
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        '. CreateSelect("Customer",$str_CustomerSearch,"CityId","Search_CityId","CityId","ManagerCity",$Search_CityId,false,20) .'
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Genere
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        '. CreateSelect(MAIN_DB.".Rule","1=1","Title","Search_RuleType","Id","Title",$Search_RuleType,false,20) .'
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
                <div class="table_label_H col-sm-1">Id</div>
                <div class="table_label_H col-sm-5">Ente</div>
                <div class="table_label_H col-sm-5">Genere</div>
    
                <div class="table_add_button col-sm-1 right">
                    <a href="mgmt_rule_add.php'.$str_GET_Parameter.'&Filter='.$Filter.'">
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
$rules = $rs->Select('RuleType',$str_Where, "CityId", $pagelimit . ',' . PAGE_NUMBER);
$RowNumber = mysqli_num_rows($rules);
if ($RowNumber == 0) {
    $str_out.= 'Nessun record presente';
} else {
        while ($rule = mysqli_fetch_array($rules)) {
            $str_out.=
            '
                <div class="table_caption_H col-sm-1">' . $rule['Id'] .'</div>
                <div class="table_caption_H col-sm-5">' . $rule['CityId'] .'</div>
                <div class="table_caption_H col-sm-5">' . $rule['Title'].'</div>
                ';
            
    //         $str_out.=
    //         '<div class="table_caption_button col-sm-1">
    // 				'. ChkButton($aUserButton, 'viw','<a href="mgmt_document_viw.php'.$str_GET_Parameter.'"><span data-toggle="tooltip" data-placement="top" title="Visualizza" class="tooltip-r glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>');
            $str_out.=
            '<div class="table_caption_button col-sm-1">';
            
            $str_out.= ChkButton($aUserButton, 'viw','<a href="mgmt_rule_viw.php'.$str_GET_Parameter.'&CityId='.$rule['CityId'].'&RuleTypeId='.$rule['Id'].'&Filter='.$Filter.'"><span data-toggle="tooltip" data-placement="top" title="Visualizza" class="tooltip-r glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>');
            
            $str_out.= ChkButton($aUserButton, 'upd','<a href="mgmt_rule_upd.php'.$str_GET_Parameter.'&CityId='.$rule['CityId'].'&RuleTypeId='.$rule['Id'].'&Filter='.$Filter.'"><span data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>');
            
            $str_out.=
            '
    			</div>
    			<div class="clean_row HSpace4"></div>
    			';
            
        }
    }
    $table_users_number = $rs->Select('RuleType',$str_Where, 'Id');
    $UserNumberTotal = mysqli_num_rows($table_users_number);
    
    $str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage."&Filter=".$Filter,"");
}

$str_out.= '<div>
    </div>';


echo $str_out;
?>
<script>
//     $(document).ready(function () {
//         $(".glyphicon-pencil").click(function(){
//             var id = $(this).attr("id");
//             window.location.href = "tbl_article_upd.php?Id="+id;
//         });
//     });

    $(".glyphicon-search").on('click',function(e){

        $('#f_search_article').submit();
    });
</script>



<?php
include(INC."/footer.php");