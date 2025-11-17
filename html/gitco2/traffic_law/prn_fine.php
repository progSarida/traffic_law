<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');



$a_RadioChk = array(
    array('Option' => 'Immagini rilievi', 'ColSize' => 1),
    array('Option' => 'Elenco', 'ColSize' => 1),
    array('Option' => 'Verbali', 'ColSize' => 1),
    array('Option' => 'Notifiche', 'ColSize' => 1),
    array('Option' => 'Verbali e relate di notifica', 'ColSize' => 2),
    array('Option' => 'Contabilità', 'ColSize' => 1)
);

$PageTitle = CheckValue('PageTitle','s');
$btn_search = CheckValue('btn_search','n');
$b_PrintFine = CheckValue('b_PrintFine','n');

$Search_Article = CheckValue('Search_Article','n');

$Search_FromRegDate = CheckValue('Search_FromRegDate','s');
$Search_ToRegDate = CheckValue('Search_ToRegDate','s');



$str_Where = '1=1';

$str_Where .= " AND CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];


if($Search_Article>0){
    $str_Where .= " AND ArticleId=".$Search_Article;
}
if($Search_FromProtocolId>0)
    $str_Where .= " AND ProtocolId>=".$Search_FromProtocolId;
if($Search_ToProtocolId>0)
    $str_Where .= " AND ProtocolId<=".$Search_ToProtocolId;
if($Search_FromFineDate != "") 
    $str_Where .= " AND FineDate>='".DateInDB($Search_FromFineDate)."'";
if($Search_ToFineDate != "")
    $str_Where .= " AND FineDate<='".DateInDB($Search_ToFineDate)."'";
if($Search_Trespasser!="")
    $str_Where .= " AND (CompanyName LIKE '%".addslashes($Search_Trespasser)."%' OR TrespasserSurname LIKE '%".addslashes($Search_Trespasser)."%')";

$Search_StatusType= CheckValue('Search_StatusType','n');

if($Search_StatusType==15){



    $str_Where .= ($Search_FromProtocolId==0) ? " AND ProtocolId>0" : "";


    if($Search_FromRegDate!=""){
        $str_Where .= " AND RegDate>='".DateInDB($Search_FromRegDate)."'";
    }
    if($Search_ToRegDate!=""){
        $str_Where .= " AND RegDate<='".DateInDB($Search_ToRegDate)."'";
    }

    $str_Where .= " AND StatusTypeId>14";
    $strOrder = "ProtocolId";

}else{
    $str_Where .= " AND StatusTypeId<15";
    $strOrder = "FineDate, FineTime";
}






$rs_Row = $rs->Select(MAIN_DB.'.City',"UnionId='".$_SESSION['cityid']."'", "Title");
$n_Code = mysqli_num_rows($rs_Row);

$str_Locality = '
    <div class="col-sm-5 BoxRowCaption">
    </div>
';
if($n_Code>0) {
    $str_Locality = '
                <div class="col-sm-1 BoxRowLabel">
                    Comune
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    <select class="form-control" name="Search_Locality">
                    <option></option>
';

    while ($r_Row = mysqli_fetch_array($rs_Row)) {

        $str_Locality .= '<option value="' . $r_Row['Id'].'"';

        if($Search_Locality==$r_Row['Id']) $str_Locality .=' SELECTED ';


        $str_Locality .= '>' . $r_Row['Title'] . '</option>';

    }

    $str_Locality .= '        </select>
                    </div>';

}

$str_ArticleQuery = "SELECT DISTINCT ArticleId, CONCAT(Article,' ',Paragraph,' ',Letter) ArticleDescription FROM V_FineArticle WHERE CityId='".$_SESSION['cityid']."' AND ProtocolYear=".$_SESSION['year'];






$str_out .='
<div class="row-fluid">
    <form id="f_Search" action="prn_fine.php" method="post">
    <input type="hidden" name="PageTitle" value="'.$PageTitle.'">
    <input type="hidden" name="btn_search" value="1">
    
    <div class="col-sm-12">
        <div class="col-sm-11" style="height:6.8rem;">
            <div class="col-sm-1 BoxRowLabel">
                Articolo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.CreateSelectQuery($str_ArticleQuery, "Search_Article", "ArticleId", "ArticleDescription", $Search_Article,0).'
            </div>       
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromFineDate.'" name="Search_FromFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToFineDate.'" name="Search_ToFineDate">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nominativo
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Trespasser" type="text" value="'.$Search_Trespasser.'">
            </div>                                              
    
            <div class="clean_row HSpace4"></div>                 
                   
            <div class="col-sm-1 BoxRowLabel">
                Pratica
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelect("StatusType","Id=1 OR Id=15","Id","Search_StatusType","Id","Title",$Search_StatusType,false) .'
            </div>
            <div class="col-sm-1 BoxRowLabel">
    		    Da cron 
            </div>
            <div class="col-sm-1 BoxRowCaption">			
    		    <input class="form-control frm_field_numeric" type="text" value="'. $Search_FromProtocolId. '" id="Search_FromProtocolId" name="Search_FromProtocolId" '.($Search_StatusType <= 1 ? ' disabled' : '').'>
    		</div>
            <div class="col-sm-1 BoxRowLabel">		
    		    A cron 
            </div>
            <div class="col-sm-1 BoxRowCaption">			
    		    <input class="form-control frm_field_numeric" type="text" value="'. $Search_ToProtocolId. '" id="Search_ToProtocolId" name="Search_ToProtocolId" '.($Search_StatusType <= 1 ? ' disabled' : '').'>
    	    </div>            
            
            '.$str_Locality.'
                
            <div class="clean_row HSpace4"></div>  
            
            <div class="col-sm-2 BoxRowLabel">
                Da data presa in carico
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_FromRegDate.'" name="Search_FromRegDate">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                A data presa in carico
            </div>                
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="'.$Search_ToRegDate.'" name="Search_ToRegDate">
            </div>               
            <div class="col-sm-6 BoxRowCaption"></div>                
        </div>
        <div class="col-sm-1">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
            	<button type="submit" data-toggle="tooltip" data-placement="top" title="Cerca" class="tooltip-r btn btn-primary" id="search" name="search" style="margin-top:0;width:100%;height:6.8rem;"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
            </div>
        </div>
    </div>
</div>
<div class="clean_row HSpace4"></div>
';

$str_out .='        
    	<div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">Protocollo</div>
				<div class="table_label_H col-sm-1">Rif.to</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Ora</div>
				<div class="table_label_H col-sm-2">Targa</div>
				<div class="table_label_H col-sm-5">Localita</div>
                <div class="table_label_H col-sm-1">Articolo</div>
				<div class="clean_row HSpace4"></div>';


if($btn_search==1 && $Search_StatusType > 0){

	$table_rows = $rs->Select('V_FineAll',$str_Where, $strOrder);
	$RowNumber = mysqli_num_rows($table_rows);

	if ($RowNumber == 0) {
		$str_out.=
			'<div class="table_caption_H col-sm-12">
			Nessun record presente
		</div>';
	} else {
	    $n_Count = 0;
		while ($table_row = mysqli_fetch_array($table_rows)) {
            $n_Count++;

            $str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['ProtocolId'] .'</div>';
			$str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['Code'] .'</div>';
			$str_out.= '<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>';
			$str_out.= '<div class="table_caption_H col-sm-1">' . TimeOutDB($table_row['FineTime']) .'</div>';
			$str_out.= '<div class="table_caption_H col-sm-2">' . $table_row['VehiclePlate'] .'</div>';
			$str_out.= '<div class="table_caption_H col-sm-5">' . $table_row['Address'] .'</div>';
            $str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['Article'] .' ' . $table_row['Paragraph'] .' ' . $table_row['Letter'] .'</div>';

			$str_out.= '<div class="clean_row HSpace4"></div>';
		}
        $str_out.= '<div class="BoxRowLabel col-sm-2">Totale verbali:</div>';
        $str_out.= '<div class="BoxRowCaption col-sm-1">'. $n_Count .'</div>';
        $str_out.= '<div class="BoxRowLabel col-sm-1">Tipo di stampa:</div>';
        
        if($Search_StatusType == 15){
            foreach($a_RadioChk as $key => $radio){
                $str_out.= '<div class="BoxRowCaption col-sm-'.$radio['ColSize'].' text-center"><input type="radio" value="'.$key.'" name="b_PrintFine" style="top: 0;"'.($key == $b_PrintFine ? ' checked' : '').'><span style="position:relative;top:-0.3rem;"> '.$radio['Option'].'</span></div>';
            }
        } else {
            $str_out.= '<div class="BoxRowCaption col-sm-'.$a_RadioChk[0]['ColSize'].' text-center"><input type="radio" value="0" name="b_PrintFine" style="top: 0;" checked><span style="position:relative;top:-0.3rem;"> '.$a_RadioChk[0]['Option'].'</span></div>';
        }
        
        $str_out.= '<div class="BoxRowCaption'.($Search_StatusType==15 ? ' col-sm-1' : ' col-sm-7').'"></div>';
        
        $strButtons = '<button type="submit" class="btn btn-success" id="prn_button" style="margin-top:2rem;width:16rem;">Stampa</button>';
        
        $str_out.= '
        <div class="clean_row HSpace4"></div>
		<div class="table_label_H HSpace4" style="height:8rem;">
		'.ChkButton($aUserButton, 'prn',$strButtons) .'
		</div>
		</form>
	</div>';
	}
} else {
    $str_out .= '
        	<div class="table_caption_H col-sm-12 text-center" style="font-size: 2rem;color: orange;">
				Scegliere una tipologia di pratica
			</div>';
}

	$str_out.= '<div>
</div>';


	echo $str_out;
?>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#search').click(function () {
                $("#search, #prn_button").prop('disabled', true);
                $('#search i').toggleClass('glyphicon glyphicon-search fa fa-circle-notch fa-spin');
                $('#f_Search').submit();
            });
            
            $('#f_Search').on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) {
                    $("#f_Search").submit();
                }
            });
            $('#prn_button').click(function(){
                $("#search, #prn_button").prop('disabled', true);
                $('#prn_button').html('<i class="fas fa-circle-notch fa-spin" style="font-size:2rem;">');
                $('#f_Search').attr('action', 'prn_fine_exe.php<?= $str_GET_Back_Page ?>');
                $("#f_Search").submit();
            });

            $("#Search_StatusType").change(function(){
                if ($("#Search_StatusType").val()==15){
                    $('#Search_FromProtocolId, #Search_ToProtocolId').prop("disabled", false);
                }else{
                	$('#Search_FromProtocolId, #Search_ToProtocolId').val('');
                    $('#Search_FromProtocolId, #Search_ToProtocolId').prop("disabled", true);
                }
            });

        });
    </script>
<?php
include(INC."/footer.php");
