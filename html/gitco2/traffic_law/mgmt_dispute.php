<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(CLS."/cls_dispute.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Search_Trespasser  = addSlashes(CheckValue('Search_Trespasser','s'));
$Search_Office      = CheckValue('Search_Office','n');
$FromProtocolId     = CheckValue('FromProtocolId','n');
$ToProtocolId       = CheckValue('ToProtocolId','n');

$FromHearingDate    = CheckValue('FromHearingDate','s');
$ToHearingDate      = CheckValue('ToHearingDate','s');

$FromCityId         = CheckValue('FromCityId','s');
$ToCityId           = CheckValue('ToCityId','s');

$GradeType          = CheckValue('GradeType','n');

$a_SelectGrade = array("","","","");
$a_SelectGrade[$GradeType] = " SELECTED ";
//ARRAY PER SELECT DisputeAct
$a_temp = $rs->getResults($rs->ExecuteQuery("SELECT * FROM DisputeAct"));
foreach ($a_temp as $arrayAct){
    if($arrayAct['Id']>0)
        $a_disputeAct[$arrayAct['Id']] = $arrayAct['Title'];
}

$str_FromToCity = "";
if($_SESSION['usertype']>90) {
    $str_FromToCity .= '
    
        <div class="col-sm-1 BoxRowLabel">
            Ente:
        </div>
        <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
            Da
        </div>        
        <div class="col-sm-2 BoxRowCaption">
            ' . CreateSelect("Customer", "1=1", "CityId", "FromCityId", "CityId", "ManagerCity", $FromCityId, false) . '
        </div>
        <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
            A
        </div>        
        <div class="col-sm-2 BoxRowCaption">
            ' . CreateSelect("Customer", "1=1", "CityId", "ToCityId", "CityId", "ManagerCity", $ToCityId, false) . '
        </div>
    
    ';
} else {
    $str_FromToCity .= '    
        <div class="col-sm-7 BoxRowLabel"></div>
        ';
}
$str_WhereCity = " AND CityId='".$_SESSION['cityid']."' ";

//if($FromCityId!="" || $ToCityId!=""){
//    $str_WhereCity = "";
//    if($FromCityId!="") $str_WhereCity.= " AND CityId>='".$FromCityId."' ";
//    if($ToCityId!="") $str_WhereCity.= " AND CityId<='".$ToCityId."' ";
//}






$str_Where .= $str_WhereCity;

if($Search_Trespasser!=""){
    $str_Where .= " AND (CompanyName LIKE '%".$Search_Trespasser."%' OR Surname LIKE '%".$Search_Trespasser."%' OR Name LIKE '%".$Search_Trespasser."%')";
}


if($FromProtocolId>0){
    $str_Where .= " AND ProtocolId >= $FromProtocolId";
}else{
    $FromProtocolId="";
}

if($ToProtocolId>0){
    $str_Where .= " AND ProtocolId <= $ToProtocolId";
}else{
    $ToProtocolId="";
}


if($Search_Office>0){
    $str_Where .= " AND OfficeId =".$Search_Office;
}


if($FromHearingDate!=""){
    $str_Where .= " AND  (IFNULL(DateHearing, DateFile)  >= '".DateInDB($FromHearingDate)  ."')";
}

if($ToHearingDate!=""){
    $str_Where .= " AND (IFNULL(DateHearing, DateFile) <= '".DateInDB($ToHearingDate)  ."')";
}
if($GradeType>0){
    $str_Where .= " AND GradeTypeId=".$GradeType;
}



$strOrder = "DateHearing";

$a_GradeTypeValue = array("","I","II","III");

$a_DisputeStatusId = array(
        "",
    "#ffe087",  //Giallo
    "#3C763D",  //Verde
    "#A94442"); //Rosso

$a_GradeType = array(
    1=>"#0C8F9C",   //Azzurro
    2=>"#0C4D9C",   //Blu
    3=>"#8E0C9C");  //Fucsia

$str_out .= '
    	<div class="row-fluid">
            <form id="f_Search" action="'.$str_CurrentPage.'" method="post">
            <div class="col-sm-12">
                <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
                    <div class="col-sm-1 BoxRowLabel">
                        Cron:
                    </div>
                    <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                        Da
                    </div>        
                    <div class="col-sm-1 BoxRowCaption">                
                        <input class="form-control frm_field_numeric" name="FromProtocolId" type="text" style="width:8rem" value="'.$FromProtocolId.'">
                    </div>
                    <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                        A
                    </div>        
                    <div class="col-sm-1 BoxRowCaption"> 
                        <input class="form-control frm_field_numeric" name="ToProtocolId" type="text" style="width:8rem" value="'.$ToProtocolId.'">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Nominativo
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" name="Search_Trespasser" type="text" style="width:15rem" value="'.stripslashes($Search_Trespasser).'">
                    </div>                      
                    <div class="col-sm-1 BoxRowLabel">
                        Autorità
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        '. CreateSelect("Office","1=1","TitleIta","Search_Office","Id","TitleIta",$Search_Office,false) .'
                    </div>                   
                    <div class="col-sm-1 BoxRowLabel">
                        Grado
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <select name="GradeType">
                            <option></option>
                            <option value="1"'. $a_SelectGrade[1] .'>I</option>
                            <option value="2"'. $a_SelectGrade[2] .'>II</option>
                            <option value="3"'. $a_SelectGrade[3] .'>III</option>
                        </select>
                    </div>   
                    
                    
                    <div class="clean_row HSpace4"></div>
            
                    <div class="col-sm-1 BoxRowLabel">
                        Udienza:
                    </div>
                    <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                        Da
                    </div>        
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_date" name="FromHearingDate" type="text" style="width:9rem" value="'.$FromHearingDate.'">
                    </div>
                    <div class="col-sm-1 BoxRowLabel" style="text-align: right;padding-right:1rem;">
                        A
                    </div>        
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_date" name="ToHearingDate" type="text" style="width:9rem" value="'.$ToHearingDate.'">
                    </div>
                    '. $str_FromToCity .'
                    
                </div>
                <div class="col-sm-1 BoxRow" style="height:4.6rem;">
                    <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">	
                        
                        <button class="btn btn-primary" id="btn_src" style="position:absolute;top:-0.5rem;left:2px;width:3rem;height:4rem">
                            <i class="glyphicon glyphicon-search" style="position:relative;left:-1rem;font-size:2.5rem;"></i>
                        </button>
                        
                        <button class="btn btn-primary" id="btn_pdf" style="position:absolute;top:-0.5rem;right:2px;width:3rem;height:4rem">    
                            <i class="fa fa-file-pdf-o" style="position:relative;left:-1rem;font-size:2.5rem;"></i>
                        </button>
            
            
                    </div>
                </div>    
                </form>
            </div>
        </div>   
        <div class="clean_row HSpace4"></div>
        ';



$str_out .='
    	<div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-3">Verbale</div>
				<div class="table_label_H col-sm-2">Trasgressore</div>
                <div class="table_label_H col-sm-6">Ricorso</div>
    
        		<div class="table_add_button col-sm-1 right">
        			'.ChkButton($aUserButton, 'add','<a href="mgmt_dispute_add.php'.$str_GET_Parameter.'"><span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span></a>').'      				
				</div>
                
				<div class="table_label_H col-sm-1">Cron</div>
				<div class="table_label_H col-sm-1">Data</div>
				<div class="table_label_H col-sm-1">Targa</div>
				<div class="table_label_H col-sm-2">Nome/Ragione soc</div>
				<div class="table_label_H col-sm-1">Grado</div>
				<div class="table_label_H col-sm-1">RG</div>
                <div class="table_label_H col-sm-2">Autorità</div>
                <div class="table_label_H col-sm-2">Status</div>
        		<div class="table_add_button col-sm-1 right"></div>
				<div class="clean_row HSpace4"></div>';


$disputeView = new CLS_VIEW(MGMT_DISPUTE);
$disputeView->groupBy = "D.Id, D.GradeTypeId, F.Id, T.Id";
$table_rows= $rs->selectQuery($disputeView->generateSelect($str_Where,null, "DisputeId DESC",$pagelimit . ',' . PAGE_NUMBER));

$UserNumberTotal = mysqli_num_rows($rs->selectQuery($disputeView->generateSelect($str_Where,null, "DisputeId DESC")));
$RowNumber = mysqli_num_rows($table_rows);

$cls_dispute = new cls_dispute();
if ($RowNumber == 0) {
	$str_out.= 'Nessun record presente';
} else {
	while ($table_row = mysqli_fetch_array($table_rows)) {
	    $cls_dispute->setDispute($table_row);
//        echo "<br><br>";
//	    var_dump($table_row);
//	    echo "<br><br>";
	    $cls_dispute->setInfo();
	    $a_disputeDate = $rs->getArrayLine($rs->ExecuteQuery("SELECT * FROM DisputeDate WHERE DisputeId = ".$table_row['DisputeId']." AND ".$table_row['GradeTypeId']." ORDER BY DateHearing DESC LIMIT 1"));
		$str_out.='
			<div class="table_caption_H col-sm-1">' . $table_row['ProtocolId'].' / '.$table_row['ProtocolYear'].'</div>
        	<div class="table_caption_H col-sm-1">' . DateOutDB($table_row['FineDate']) .'</div>
        	<div class="table_caption_H col-sm-1">' . $table_row['VehiclePlate'] .'</div>
			<div class="table_caption_H col-sm-2">' . $table_row['CompanyName'] .$table_row['Surname'] .' '.$table_row['Name'] . '</div>
			<div class="table_caption_H col-sm-1" style="font-size: 1.5rem; text-align: center; color:' .$a_GradeType[$table_row['GradeTypeId']].'">' . $a_GradeTypeValue[$table_row['GradeTypeId']] .'</div>
			<div class="table_caption_H col-sm-1">' . $table_row['Number'] .'</div>
			<div class="table_caption_H col-sm-2">' . $table_row['OfficeTitle']." ".$table_row['OfficeCity'].'</div>
			
			';
		$Status = '';

		if(DateOutDB($a_disputeDate['DateHearing'])!=""){
            $judgmentText="";
		    if($a_disputeDate['DisputeActId']>0)
                $judgmentText.= $a_disputeAct[$a_disputeDate['DisputeActId']]." n. ".$a_disputeDate['Number']." del ".DateOutDB($a_disputeDate['DateMeasure'])."\n";

		    $textHearing = $a_disputeDate['TypeHearing'].' il '. DateOutDB($a_disputeDate['DateHearing']);
		    if($a_disputeDate['Note']!="")
                $textHearing.= "\n".$a_disputeDate['Note'];

            $Status .= '<i class="fa fa-calendar" title="'.$textHearing.'" 
                    style="margin-top:0.2rem;margin-right:0.5rem;font-size:1.7rem;"></i></span>';
        }

        $Status .= '
            <i class="fa fa-gavel info-icon" title="'.$judgmentText.$cls_dispute->a_info['msg'].'" style="margin-top:0.2rem;margin-right:0.5rem;font-size:1.7rem;color:'.$a_DisputeStatusId[$table_row['DisputeStatusId']].'"></i>
        ';



        $str_out.=
			'<div class="table_caption_H col-sm-2">' . $Status .'</div>
			<div class="table_caption_button col-sm-1">
				'. ChkButton($aUserButton, 'viw','<a href="mgmt_dispute_viw.php'.$str_GET_Parameter.'&Id='.$table_row['DisputeId'].'&FineId='.$table_row['FineId'].'"><span class="glyphicon glyphicon-eye-open" style="position:absolute;left:5px;top:5px;"></span></a>') .'
				'. ChkButton($aUserButton, 'upd','<a href="mgmt_dispute_upd.php'.$str_GET_Parameter.'&Id='.$table_row['DisputeId'].'&FineId='.$table_row['FineId'].'"><span class="glyphicon glyphicon-pencil" style="position:absolute;left:25px;top:5px;"></span></a>') .'
				&nbsp;
	&nbsp;		
							</div>
			<div class="clean_row HSpace4"></div>';
	}
}

$strLabel ='';


$str_out.=CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, $str_CurrentPage,$strLabel);
$str_out.= '<div>
	</div>';


echo $str_out;
?>






<script type="text/javascript">


	$(document).ready(function () {

        $("#btn_src").on('click',function(e){
            e.preventDefault();
            $('#f_Search').attr('action', 'mgmt_dispute.php');
            $('#btn_pdf').hide();

            $('#f_Search').submit();
        });


        $("#btn_pdf").on('click',function(e){
            e.preventDefault();
            $('#f_Search').attr('action', 'prn_dispute_exe.php');
            $('#btn_src').hide();


            $('#f_Search').submit();
        });



	});

</script>
<?php
include(INC."/footer.php");
