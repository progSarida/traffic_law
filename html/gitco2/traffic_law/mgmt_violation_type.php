<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Search_CityId = CheckValue('Search_CityId','s');
$Filter = CheckValue('Filter','n');

if($_SESSION['usertype']>50){
    $str_CustomerSearch = "1=1";
} else {
    $str_CustomerSearch = "CityId='".$_SESSION['cityid']."'";
}

//*******************Gestione Regolamento*******************
$RuleTypeId = $_SESSION['ruletypeid'];
$RuleTypeTitle = $_SESSION['ruletypetitle'];
$str_Where .= " AND RuleTypeId = $RuleTypeId";
$str_CurrentPage .= "&RuleTypeId=".$RuleTypeId;
//**********************************************************

$str_GET_Parameter .= "&Search_CityId=$Search_CityId";

$CityId = $_SESSION['cityid'];

$chh_FindFilter = trim($str_Where);

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
                        '.$RuleTypeTitle.'
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
                <div class="table_label_H col-sm-4">Genere</div>
                <div class="table_label_H col-sm-6">Titolo</div>
    
                <div class="table_add_button col-sm-1 right">
                    <a href="mgmt_violation_type_add.php'.$str_GET_Parameter.'&Filter='.$Filter.'">
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
    $rs_ViolationTypes = $rs->SelectQuery('SELECT VT.Id, VT.Title, R.Title AS RuleTitle FROM ViolationType VT LEFT JOIN '.MAIN_DB.'.Rule R on VT.RuleTypeId = R.Id WHERE '.$str_Where.' ORDER BY Id LIMIT '.$pagelimit . ',' . PAGE_NUMBER);
    $RowNumber = mysqli_num_rows($rs_ViolationTypes);
    if ($RowNumber == 0) {
        $str_out.= 'Nessun record presente';
    } else {
        while ($r_ViolationType = mysqli_fetch_array($rs_ViolationTypes)) {
            $str_out.=
            '
                <div class="table_caption_H col-sm-1">' . $r_ViolationType['Id'] .'</div>
                <div class="table_caption_H col-sm-4">' . $r_ViolationType['RuleTitle'] .'</div>
                <div class="table_caption_H col-sm-6">' . StringOutDB($r_ViolationType['Title']).'</div>
                ';
            
    //         $str_out.=
    //         '<div class="table_caption_button col-sm-1">
    // 				'. ChkButton($aUserButton, 'viw','<a href="mgmt_document_viw.php'.$str_GET_Parameter.'"><span data-toggle="tooltip" data-placement="top" title="Visualizza" class="tooltip-r glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>');
            $str_out.=
            '<div class="table_caption_button col-sm-1">';
            
            $str_out.= ChkButton($aUserButton, 'upd','<a href="mgmt_violation_type_upd.php'.$str_GET_Parameter.'&Id='.$r_ViolationType['Id'].'&Filter='.$Filter.'"><span data-toggle="tooltip" data-placement="top" title="Modifica" class="tooltip-r glyphicon glyphicon-pencil" style="position:absolute;left:5px;top:5px;"></span></a>');
            
            $str_out.=
            '
    			</div>
    			<div class="clean_row HSpace4"></div>
    			';
            
        }
    }
    $table_users_number = $rs->Select('ViolationType',$str_Where, 'Id');
    $UserNumberTotal = mysqli_num_rows($table_users_number);
    
    $str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage."&Filter=".$Filter,"");
}

$str_out.= '<div>
    </div>';


echo $str_out;
?>
<script>

$(document).ready(function () {
	
    setTimeout(function () {
    	$('#Search_CityId').change();
    }, 100);
	
});
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