<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include (INC."/function_postalCharge.php");
require(INC . "/initialization.php");
include(INC . "/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');



$Id = CheckValue('Id', 'n');
$page = CheckValue('page', 'n');
if (isset($_GET['answer'])){
    $answer = $_GET['answer'];
    $class = strlen($answer)>50?"alert-warning":"alert-success";
    echo "<div class='alert $class message'>$answer</div>";
    ?>
    <script>
        setTimeout(function(){ $('.message').hide()}, 4000);
    </script>
    <?php
}


$str_Where = "StatusTypeId=13 AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'];


$strOrder = "Id DESC";



$str_Next = "";
$str_Previous = "";
$str_Table = "";




$table_rows = $rs->Select('V_Violation',"Id=$Id");

$FindNumber = mysqli_num_rows($table_rows);



if($FindNumber==0){
    $str_Table = "V_ViolationRent";
    $table_rows = $rs->Select('V_ViolationRent',"Id=$Id", "Id");
    $table_row = mysqli_fetch_array($table_rows);

    $rs_TrespasserRent = $rs->Select('FineTrespasser', "TrespasserTypeId=11 AND FineId=".$Id);
    $FindNumber = mysqli_num_rows($rs_TrespasserRent);
    if($FindNumber==0){

        $rs_TrespasserRent = $rs->Select('FineTrespasser', "TrespasserTypeId=10 AND FineId=".$Id);
        $FindNumber = mysqli_num_rows($rs_TrespasserRent);
        if($FindNumber==0){
            $TrespasserId = 0;

        }else{
            $r_TrespasserRent = mysqli_fetch_array($rs_TrespasserRent);
            $TrespasserId = $r_TrespasserRent['TrespasserId'];
        }
        $trespasser_rows = $rs->Select('V_Trespasser',"Id=".$TrespasserId);
        $trespasser_row = mysqli_fetch_array($trespasser_rows);
        $strTrespasser = DivTrespasserRentView($trespasser_row,"NOLEGGIO");

    }else{
        $r_TrespasserRent = mysqli_fetch_array($rs_TrespasserRent);
        $TrespasserId = $r_TrespasserRent['TrespasserId'];

        $trespasser_rows = $rs->Select('V_Trespasser',"Id=".$TrespasserId);
        $trespasser_row = mysqli_fetch_array($trespasser_rows);
        $strTrespasser = DivTrespasserUpdate($trespasser_row,"LOCATARIO");

    }

} else {
    $str_Table = "V_Violation";
    $table_row = mysqli_fetch_array($table_rows);
    $TrespasserId = (is_null($table_row['TrespasserId'])) ? 0 : $table_row['TrespasserId'];
    $whereTrespasser = "Id=".$TrespasserId;
    $trespasser_rows = $rs->Select('V_Trespasser',$whereTrespasser, "Id");
    $trespasser_row = mysqli_fetch_array($trespasser_rows);
    $strTrespasser = DivTrespasserUpdate($trespasser_row, "TRASGRESSORE");

}



$str_JQPersonalization = '';
$str_BoxArticleOwner = '';
$str_BoxAdditionalArticleOwner = '';

$str_ButtonArticle = '';
$str_Tab = '';


$rs_FineOwner = $rs->Select('FineOwner',"FineId=".$Id);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);
$str_ArticleDescription = (strlen(trim($r_FineOwner['ArticleDescription'.LAN]))>0)  ? $r_FineOwner['ArticleDescription'.LAN] : $table_row['ArticleDescription' . LAN];
$str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescription'.LAN]))>0) ? $r_FineOwner['ReasonDescription'.LAN] : $table_row['ReasonTitle' . LAN];
$allReason = $rs->Select('Reason',"CityId = '".$_SESSION['cityid']."'");
if ($table_row['ReasonId']!=100){
    if (strlen(trim($r_FineOwner['ReasonDescription'.LAN]))<1){
        $contestazione = '<div class="col-sm-8 BoxRowCaption add_text">
                        <div class="col-sm-1">
                            <input class="form-control frm_field_numeric" maxlength="2" type="text" name="ReasonCode" id="ReasonCode" style="width:5rem">
                        </div>
                        <div class="col-sm-11">
                            <select class="form-control" name="ReasonId" id="ReasonId"">
        				        <option value="'.$table_row['ReasonId'].'">'.StringOutDB($str_ReasonDescription).'</option>';
                                while ($all_reason = mysqli_fetch_array($allReason)) {
                                    if ($all_reason['Id'] != $table_row['ReasonId']) {
                                        $contestazione .= '
                                                    <option value="' . $all_reason['Id'] . '">
                                                    ' . $all_reason['Code'] . " " . $all_reason['TitleIta'] . '
                                                    </option>
                                                ';
                                    }
                                }
                            $contestazione.='</select>
                        </div>
                        <div class="col-sm-11">
                            <input name="Reason_Text" id="Reason_Text" type="text" class="form-control frm_field_string" style="display: none">
                        </div>
                    </div>
					<div class="col-sm-1 BoxRowLabel">
                        <i class="fa fa-edit" id="Edit_Contestazione" style="position: absolute; top: 1px; right: 1px; font-size: 2rem; color: rgb(255, 255, 255);"></i>
					</div>';
    }else{
        $contestazione = '<div class="col-sm-9 BoxRowCaption">			    
        				<input type="text" class="form-control frm_field_string" name="Reason_Text" id="Reason_Text" value="' . StringOutDB($str_ReasonDescription) . '"> 
					</div>';
    }
    $styleCon=null;
}else{
    $contestazione ="";
    $styleCon = "display:none";
}


echo "<div class='check'></div>";
include './inc/page/VerbaleForm/Accertatore_upd.php';
$AdditionalFee = 0;



if($_SESSION['userlevel']>=3){
    $a_Language = unserialize(LANGUAGE);
    $n_Lan = ($table_row['CountryId'] == 'Z000') ? 1 : 5;

    $a_TabId = array("Article","Reason","Additional","Declaration","Damage","Removal","Note");
    $a_TabTitle = array("Articolo","Contestazione","Sanzione","Dichiarazioni","Danni","Rimozione","Note");
    $a_TabContent = array();

    $str_ActivePanel = " active";


    for($n_Tab=0;$n_Tab<7;$n_Tab++){
        if($n_Tab>0) $str_ActivePanel = "";
        $a_TabContent[$n_Tab] =  '
            <div class="tab-pane'.$str_ActivePanel.'" id="'.$a_TabId[$n_Tab].'">
        ';
        for ($i=1; $i<=$n_Lan; $i++){
            $a_TabContent[$n_Tab] .=  '
                 <div class="col-sm-12" >
                    <div class="col-sm-3 BoxRowLabel" style="height:6rem">
                        '.$a_TabTitle[$n_Tab].' <img src="' .IMG.'/'.$aLan[$i] .'" style="width:16px" /> 
                    </div>
                    <div class="col-sm-9 BoxRowCaption" style="height:6rem">
                        <textarea name="'.$a_TabId[$n_Tab].'Description'.$a_Language[$i].'" style="height:5rem; width:50rem;">'.$r_FineOwner[$a_TabId[$n_Tab].'Description'.$a_Language[$i]].'</textarea>
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
        ';
        }

        $a_TabContent[$n_Tab] .=  '
                
             </div>
        ';
    }



    for($n_Tab=0;$n_Tab<7;$n_Tab++){
        $str_Tab .= $a_TabContent[$n_Tab];
    }










    $str_ButtonArticle = '
    <span class="glyphicon glyphicon-pencil" id="0" style="position:absolute;bottom:5px;right:5px;"></span>
    ';


    $str_JQPersonalization="
        $('.glyphicon-pencil').click(function(){
            $('#overlay').fadeIn('fast');

            if($(this).attr('id')!=0){
                var a_Language = ['','Ita','Eng','Ger','Spa','Fre'];
                $('#AdditionalArticleId').val($(this).attr('id'));                
                var a_LanText = $(this).attr('alt').split('*');    
                for(i=1;i<a_LanText.length;i++){
                   $('#AdditionalArticleDescription'+ a_Language[i]).val(a_LanText[i]);                
                }
            
                $('#BoxAdditionalArticleOwner').fadeIn('slow');
            } else {
                $('#BoxArticleOwner').fadeIn('slow');
            }
                        
            
    
        });
    

    ";



    $str_BoxArticleOwner = '
    <div id="BoxArticleOwner">
        <div id="FormArticleOwner">
            <form name="f_ins" id="f_ins" class="form-horizontal" action="mgmt_warning_upd_exe.php'.$str_GET_Parameter.'" method="post">
                <input type="hidden" name="FineOwner" value="1">
                <input type="hidden" name="Id" value="' . $Id . '">
    
                
                <div class="BoxRow form-group" style="height:4rem;">
                    <div class="BoxRowTitle" id="BoxRowTitle">
                        Personalizzazione verbale
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <ul class="nav nav-tabs" id="mioTab">
                    <li class="active" id="tab_Article"><a href="#Article" data-toggle="tab">Articolo</a></li>
                    <li id="tab_Reason"><a href="#Reason" data-toggle="tab">Contestazione</a></li>
                    <li id="tab_Additional"><a href="#Additional" data-toggle="tab">Sanzione acc</a></li>
                    
                    <li id="tab_Declaration"><a href="#Declaration" data-toggle="tab">Dichiarazioni</a></li>
                    <li id="tab_Damage"><a href="#Damage" data-toggle="tab">Danni</a></li>
                    <li id="tab_Removal"><a href="#Removal" data-toggle="tab">Rimozione</a></li>
                    <li id="tab_Note"><a href="#Note" data-toggle="tab">Note</a></li>
                    
                </ul>
                
                <div class="tab-content">
                '.$str_Tab.'
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRow" style="height:6rem;">
                        <div class="col-sm-12" style="text-align:center;">
                            <button style="margin-top:1rem;" class="btn btn-default">Salva dati</button>
                        </div>    
                    </div>
                </div>         
            </form>
        </div>    
    </div>
			
    ';





    $str_BoxAdditionalArticleOwner = '
    <div id="BoxAdditionalArticleOwner">
        <div id="FormAdditionalArticleOwner">
            <form name="f_ins" id="f_ins" class="form-horizontal" action="mgmt_warning_upd_exe.php'.$str_GET_Parameter.'" method="post">
            <input type="hidden" name="FineOwner" value="1">
            <input type="hidden" name="Id" value="' . $Id . '">
            <input type="hidden" name="AdditionalArticleId" id="AdditionalArticleId">
                
                <div class="BoxRow form-group" style="height:4rem;">
                    <div class="BoxRowTitle" id="BoxRowTitle">
                        Personalizzazione Articoli
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
    ';
    for ($i=1; $i<=$n_Lan; $i++){
        $str_BoxAdditionalArticleOwner .=  '
                 <div class="col-sm-12" >
                    <div class="col-sm-3 BoxRowLabel" style="height:6rem">
                        Articolo <img src="' .IMG.'/'.$aLan[$i] .'" style="width:16px" /> 
                    </div>
                    <div class="col-sm-9 BoxRowCaption" style="height:6rem">
                        <textarea name="ArticleDescription'.$a_Language[$i].'" id="AdditionalArticleDescription'.$a_Language[$i].'" style="height:5rem; width:50rem;">'.$r_FineOwner['ArticleDescription'.$a_Language[$i]].'</textarea>
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
        ';
    }

    $str_BoxAdditionalArticleOwner .=  '
            <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRow" style="height:6rem;">
                        <div class="col-sm-12" style="text-align:center;">
                            <button style="margin-top:1rem;" class="btn btn-default">Salva dati</button>
                        </div>    
                    </div>
                </div>         
            </form>
        </div>    
    </div>
    ';
}







$rs_Id = $rs->SelectQuery("SELECT MIN(Id) NextId
           FROM ".$str_Table."
          WHERE Id > $Id AND $str_Where");

$r_Id = mysqli_fetch_array($rs_Id);

if (!is_null($r_Id['NextId'])) {
    $str_Next = '<a href="' . $str_GET_FilterPage . '&Id=' . $r_Id['NextId'] . '"><i class="glyphicon glyphicon-arrow-right" style="font-size:2rem;color:#fff"></i></a>';
}


$rs_Id = $rs->SelectQuery("SELECT MAX(Id) PreviousId
           FROM ".$str_Table."
          WHERE Id < $Id AND $str_Where");

$r_Id = mysqli_fetch_array($rs_Id);

if (!is_null($r_Id['PreviousId'])) {
    $str_Previous = '<a href="' . $str_GET_FilterPage . '&Id=' . $r_Id['PreviousId'] . '"><i class="glyphicon glyphicon-arrow-left" style="font-size:2rem;color:#fff"></i></a>';
}


$charge_rows = $rs->Select('CustomerCharge',"CreationType=1 AND CityId='".$_SESSION['cityid']."' AND ToDate IS NULL", "Id");
$charge_row = mysqli_fetch_array($charge_rows);


$str_Charge = ($table_row['CountryId']=='Z000') ? "National" : "Foreign";

if($charge_row[$str_Charge.'TotalFee']>0){
    $TotalCharge = $charge_row[$str_Charge.'TotalFee'];
} else  {
    if($charge_row[$str_Charge.'NotificationFee']>0){
        $TotalCharge = $charge_row[$str_Charge.'NotificationFee'] + $charge_row[$str_Charge.'ResearchFee'];
    }else{
        $TotalCharge = $charge_row[$str_Charge.'ResearchFee'];
        if($TrespasserId>0){
            $postalcharge=getPostalCharge($_SESSION['cityid'],$table_row['FineDate']);
            $TotalCharge += $postalcharge['Zone'.$trespasser_row['ZoneId']];
        }
    }

}

$TotalFee = $TotalCharge  + $table_row['Fee'] + $AdditionalFee;


if ($table_row['DetectorId'] == 0) $DetectorTitle = "";
else {
    $detectors = $rs->Select('Detector', "Id=" . $table_row['DetectorId']);
    $detector = mysqli_fetch_array($detectors);

    $DetectorTitle = $detector['Title' . LAN];

}


$rs_Row = $rs->Select(MAIN_DB . '.City', "UnionId='" . $_SESSION['cityid'] . "'", "Title");
$n_Code = mysqli_num_rows($rs_Row);

if ($n_Code > 0) {
    $str_Locality='
                   
                        <div class="col-sm-1 BoxRowLabel">
                            Comune
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <select name="Locality" class="form-control" style="width:10rem;">
                                <option></option>
               
    ';


    while ($r_Row = mysqli_fetch_array($rs_Row)) {
        $str_Locality .= '<option value="' . $r_Row['Id'] . '"';
        if ($table_row['Locality'] == $r_Row['Id']) $str_Locality .= ' SELECTED ';

        $str_Locality .= '>' . $r_Row['Title'] . '</option>';

    }

    $str_Locality .= '      
                            </select>
                        </div>
                       ';

} else {
    $str_Locality='    <div class="col-sm-1 BoxRowLabel">
                            Comune
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                        ' . $_SESSION['citytitle'] . '
                        </div>

    ';
}

$str_Folder = ($table_row['CountryId'] == 'Z000') ? 'doc/national/violation' : 'doc/foreign/violation';

$str_tree = "";
$str_Img = "";
$doc_rows = $rs->Select('FineDocumentation', "FineId=" . $Id, "Id");
$doc_n = mysqli_num_rows($doc_rows);

if ($doc_n > 0) {
    $doc_row = mysqli_fetch_array($doc_rows);

    $File = $str_Folder . '/' . $_SESSION['cityid'] . '/' . $table_row['Id'] . '/' . $doc_row['Documentation'];
    if (strtolower(substr($doc_row['Documentation'], -3)) == "jpg") {
        $str_Img = ' 
            $("#preview").attr("src","' . $File . '");
            $("#preview_img").show();
        ';
    } else {
        //$str_Img = '$("#preview_img").html("<iframe style=\"width:100%; height:100%\" src=\"' . $File . '\"></iframe>");';
        $str_Img = '
            $("#preview_doc").html("<object><embed width=\"100%\" height=\"100%\" src=\"' . $File . '\" type=\"application/pdf\" /></object>");
            $("#preview_doc").show();
            ';


    }

    $str_tree = '
            $("#fileTreeDemo_1").fileTree({ root:\'' . $str_Folder . '/' . $_SESSION['cityid'] . '/' . $table_row['Id'] . '/\', script: \'jqueryFileTree.php\' }, function(file) {
            
            var FileType = file.substr(file.length - 3);

            if(FileType.toLowerCase()==\'pdf\' || FileType.toLowerCase()==\'doc\'){
                $("#preview_img").hide();
                $("#preview_doc").html("<iframe style=\"width:100%; height:100%\" src=\'"+file+"\'></iframe>");
                $("#preview_doc").show();
            }else{
                $("#preview_doc").hide();
                $("#preview").attr("src",file);
                $("#preview_img").show();
            }            
        });
    ';

}
if ($table_row['ChkControl'] ==1){
    $checked = 'checked';
}else{
    $checked =null;
}
$str_out .= '
        <form name="f_violation" id="f_violation" method="post" action="mgmt_warning_upd_exe.php'.$str_GET_Parameter.'">
        <input type="hidden" name="Id" value="' . $Id . '">
        <input type="hidden" name="P" value="' . $str_BackPage . '">
        <input type="hidden" name="page" value="' . $page . '">
          <input type="hidden" id="FineTypeId" value="2">
        <input type="hidden" name="Search_Ref" value="' . $Search_Ref . '">
        <input type="hidden" name="Search_Plate" value="' . $Search_Plate . '">        
        <input type="hidden" name="Search_Violation" value="' . $Search_Violation . '">
        <input type="hidden" name="Search_Status" value="' . $Search_Status . '">
        <input type="hidden" name="TypePlate" value="' . $s_TypePlate . '">
        <input type="hidden" name="Search_Country" value="' . $Search_Country . '">
        <input type="hidden" name="AccertatoreNumber" id="AccertatoreNumber" value="'.$nr.'">
        <input type="hidden" name="ArticleNumber" id="ArticleNumber" value="'.$table_row['ArticleNumber'].'">  
        <input type="hidden" name="RuleTypeId" id="RuleTypeId" value="'.$table_row['RuleTypeId'].'"> 
        <input type="hidden" name="DetectorId" id="DetectorId" value="'.$table_row['DetectorId'].'">   
         <input type="hidden" name="ReasonId_Default" id="ReasonId_Default" value="'.$table_row['ReasonId'].'">

    	<div class="row-fluid">
        	<div class="col-sm-6">
        	    <div class="col-sm-12">
                    <div class="col-sm-1 BoxRowCaption">
                            ' . $str_Previous . '
                        </div>
                    <div class="col-sm-10 BoxRowLabel text-center">
                        Cron: '.$table_row['ProtocolId']."/".$_SESSION['year'].'
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        ' . $str_Next . '
                    </div>	
                </div>
        	    <div class="clean_row HSpace4"></div>
  	            <div class="col-sm-12" >
  	              	
					<div class="col-sm-1 BoxRowLabel">
                        Riferimento
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input name="Code" id="Code" type="text" class="form-control frm_field_string frm_field_required" value=' . substr($table_row['Code'],0,strlen($table_row['Code'])-5) . '>
                        <span id="span_code"></span>
                    </div>
                    <div class="col-sm-1 BoxRowLabel" style="border-right:2px solid white;">
                      '."/".$_SESSION['year'].'
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Data
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_date frm_field_required" name="FineDate" id="FineDate" value="' . DateOutDB($table_row['FineDate']) . '" style="width:9rem">
                        <span id="span_date"></span>
                    </div>

                    <div class="col-sm-1 BoxRowLabel">
                        Ora
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_time frm_field_required" name="FineTime" id="FineTime" value="' .TimeOutDB($table_row['FineTime']) . '" style="width:7rem">
                          <span id="span_time"></span>
                    </div>	
                    <div class="col-sm-2 BoxRowLabel">
                        Eludi Controlli
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="checkbox" id="Conrolli" '.$checked.' name="Controlli">
                    </div>				
									
  				</div> 
                <div class="clean_row HSpace4"></div>
                
                <div class="col-sm-12">
                    '.$str_Locality.'
        			<div class="col-sm-1 BoxRowLabel">
        				Località
					</div>
					<div class="col-sm-7 BoxRowCaption">
        				<input type="text" class="find_list form-control frm_field_string frm_field_required" name="FineAddress" id="Address" value="' . StringOutDB($table_row['Address']) . '">	
        				<ul id="Address_List" class="ul_SearchList"></ul>
					</div>
  				</div>                

  				<div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Tipo veicolo
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				'. CreateSelect("VehicleType","Disabled=0","Title".LAN,"VehicleTypeId","Id","Title".LAN,$table_row['VehicleTypeId'],true,14) .'
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Nazione
					</div>
					<div class="col-sm-3 BoxRowCaption">
					<input type="hidden" id="VehicleCountry" name="VehicleCountry" value="'.$table_row['VehicleCountry'].'"/>
                        '. CreateSelect("Country","Id IN (SELECT DISTINCT CountryId From Entity)","Title","CountryId","Id","Title", $table_row['CountryId'],false,15,"frm_field_required") .'
					</div>
        			<div id="department" ';
if ($table_row['CountryId'] != "Z110") $str_out.= 'hidden';
$str_out.='>
                        <div class="col-sm-1 BoxRowLabel">
                            Dip.
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                           
                           ';

if ($table_row['CountryId'] == "Z110") {
    $str_out .= CreateSelect("Department", "CountryId='" . $table_row['CountryId'] . "'", "Code", "DepartmentId", "Id", "Code", $table_row['DepartmentId'], false);
} else {
    $str_out .= '<select name="DepartmentId" id="DepartmentId"></select>';
}

$str_out .= '
					    </div>
					</div>
					<div class="col-sm-2 BoxRowCaption" id="toHide" ';
if ($table_row['CountryId'] == "Z110") $str_out.= 'hidden';
$str_out.='></div>
  				</div>
  				<div class="clean_row HSpace4"></div>
  	        	<div class="col-sm-12">
        			<div class="col-sm-3 BoxRowLabel">
        				Targa
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				<input type="text" class="form-control frm_field_string frm_field_required" name="VehiclePlate" id="VehiclePlate" style="width:10rem; text-transform:uppercase" value="' . StringOutDB($table_row['VehiclePlate']) . '">
					</div>			
        			<div id="massa" ';
if($table_row['VehicleTypeId']==2 || $table_row['VehicleTypeId']==9) $str_out.='hidden';
    $str_out.='>
                        <div class="col-sm-3 BoxRowLabel">
                            Massa
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input name="VehicleMass" id="VehicleMass" type="text" class="form-control frm_field_numeric" value="' . $table_row['VehicleMass'] . '">
                        </div>
					</div>
					<div id="toHide2" class="col-sm-6 BoxRowCaption" ';
if($table_row['VehicleTypeId']!=2 && $table_row['VehicleTypeId']!=9) $str_out.='hidden';
    $str_out.='>

  				</div>

  				</div>  				
  				
   				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Colore
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_string" name="VehicleColor" id="VehicleColor" class="find_list" style="width:8rem" value="' . $table_row['VehicleColor'] . '">	
        				<ul id="VehicleColor_List" class="ul_SearchList"></ul>
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Marca
					</div>
					<div class="col-sm-2 BoxRowCaption">
        			    <input type="text" class="form-control frm_field_string" name="VehicleBrand" id="VehicleBrand" class="find_list" style="width:8rem" value="' . $table_row['VehicleBrand'] . '">	
        				<ul id="VehicleBrand_List" class="ul_SearchList"></ul>
					</div>

        			<div class="col-sm-2 BoxRowLabel">
        				Modello
					</div>
					<div class="col-sm-2 BoxRowCaption">
        			    <input type="text" class="form-control frm_field_string" name="VehicleModel" id="VehicleModel" class="find_list" style="width:8rem" value="' . $table_row['VehicleModel'] . '">	
        				<ul id="VehicleModel_List" class="ul_SearchList"></ul>
					</div>
  				</div>
  				<div class="col-sm-12" id="div_chkPlate">
  					<div id="trespasser_content" class="col-sm-12" style="display: none;"></div>
				    <div id="fine_content" class="col-sm-12" style="display: none;"></div>
                </div> 				
  				
    			<div class="clean_row HSpace4"></div>				
 				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Rilevatore
					</div>
					<div class="col-sm-5 BoxRowCaption">
        				' . $DetectorTitle . '
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Ora
        				<span class="tooltip-r" data-toggle="tooltip" data-placement="left" title="L\'ora solare vige dalla fine di Ottobre alla fine di Marzo.
                        L\'ora legale vige dalla fine di Marzo alla fine di Ottobre ed è uguale all\'ora solare +1 ora. "><i class="glyphicon glyphicon-info-sign" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				'. CreateSelect("TimeType","Disabled=0","Id","TimeTypeId","Id","Title", $table_row['TimeTypeId'],true) .'
					</div>					
  				</div>
  				<div class="clean_row HSpace4"></div>				
                 ';


if ($table_row['Speed'] > 0) {
    $str_out .= '
  	            <div class="col-sm-12">
        			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
        				VELOCITA
					</div>
  				</div> 
	        	<div class="col-sm-12" id="DIV_Speed" style="display:none;">
        			<div class="col-sm-2 BoxRowLabel">
        				Limite
					</div>
					<div class="col-sm-2 BoxRowCaption" id="">
					    ' . round($table_row['SpeedLimit']) . '    				
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Rilevata
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . round($table_row['SpeedControl']) . '
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Effettiva
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . round($table_row['Speed']) . '
 					</div>
  				</div> 
  				<div class="clean_row HSpace4"></div>    
                ';
}


if($table_row['ControllerDate']!=""){
    $str_ControllerDate =
        '
            <div class="col-sm-3 BoxRowLabel">
                Data e ora accertamento
            </div>						
            <div class="col-sm-3 BoxRowCaption">
                '.DateOutDB($table_row['ControllerDate']).' '. $table_row['ControllerTime'] .'
            </div>            
            ';
}else{
    $str_ControllerDate =
        '
            <div class="col-sm-6 BoxRowCaption"></div>
            ';
}
if ($table_row['TimeTLightFirst'] > 0) {
    $str_out .= '
                 <div class="col-sm-12">
                   <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                       SEMAFORO
                    </div>
                 </div>
                <div class="col-sm-12" id="DIV_TLight">
                   
                    <div class="col-sm-4 BoxRowLabel">
        				Primo fotogramma
					</div>
					<div class="col-sm-2 BoxRowCaption" id="">
					    <input class="form-control frm_field_numeric" type="text" name="TimeTLightFirst" id="TimeTLightFirst" value="' . $table_row['TimeTLightFirst'] . '" style="width:6rem">    				
 					</div>
 					<div class="col-sm-4 BoxRowLabel">
        				Secondo fotogramma
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				<input class="form-control frm_field_numeric" type="text" name="TimeTLightSecond" id="TimeTLightSecond" value="' . $table_row['TimeTLightSecond'] . '" style="width:6rem">
 					</div>
                 </div>
                 <div class="clean_row HSpace4"></div>
               ';
}
include './inc/page/VerbaleForm/Articolo_upd_one.php';

$a_Procedure =array('<option value="1">Si</option><option value="0" selected>No</option>','<option value="1" selected>Si</option><option value="0">No</option>');
$rs_FineNotification = $rs->Select('FineNotification', "FineId=" . $Id);
if(mysqli_num_rows($rs_FineNotification) > 0){
    $r_FineNotification = mysqli_fetch_array($rs_FineNotification);
    $str_LicensePoint = ($r_FineNotification['LicensePointProcedure']>0) ? $r_FineNotification['LicensePointProcedure'] : "No";
    $str_Procedure = '        
        <div class="col-sm-12 BoxRowTitle" >
            <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                Elaborazioni
            </div>
        </div>
        <div class="clean_row HSpace4"></div> 
        <div class="col-sm-12">
            <div class="col-sm-4 BoxRowLabel">
                Elaborare il sollecito di pagamento/ingiunzione in caso di infedele/tardivo/omesso pagamento
            </div>
            <div class="col-sm-1 BoxRowCaption" id="div_Payment">
                <select class="TMP_Procedure" id="'. $Id .'" alt="Payment">
                '.$a_Procedure[$r_FineNotification['PaymentProcedure']].'
                </select>
            </div>
            <div class="col-sm-1 BoxRowCaption" style="width: 3rem">
            </div>    
            <div class="col-sm-6.5 BoxRowLabel">
            </div>         
        </div>
        <div class="clean_row HSpace4"></div> 
        <div class="col-sm-12">                 
            <div class="col-sm-4 BoxRowLabel">
                Elaborare il verbale art. 126 Bis in caso di omessa comunicazione dei dati del trasgressore
            </div>  
            <div class="col-sm-1 BoxRowCaption" id="div_126Bis">
                <select class="TMP_Procedure" id="'. $Id .'" alt="126Bis">
                '.$a_Procedure[$r_FineNotification['126BisProcedure']].'
                </select>
            </div>
            <div class="col-sm-1 BoxRowCaption" style="width: 3rem">
            </div>    
            <div class="col-sm-6.5 BoxRowLabel">
            </div>         
        </div>
        <div class="clean_row HSpace4"></div> 
        <div class="col-sm-12">               
            <div class="col-sm-4 BoxRowLabel">
                Elaborare il verbale art. 180 in caso di omessa trasmissione della documentazione richiesta
            </div>  
            <div class="col-sm-1 BoxRowCaption" id="div_PresentationDocument">
                <select class="TMP_Procedure" id="'. $Id .'" alt="PresentationDocument">
                '.$a_Procedure[$r_FineNotification['PresentationDocumentProcedure']].'
                </select>
            </div>
            <div class="col-sm-1 BoxRowCaption" style="width: 3rem">
            </div>   
            <div class="col-sm-6.5 BoxRowLabel">
            </div>
        </div>
        <div class="clean_row HSpace4"></div> 
        <div class="col-sm-12">                        
            <div class="col-sm-4 BoxRowLabel">
                Procedura con la decurtazione punti della patente di guida del trasgressore comunicato
            </div>  
            <div class="col-sm-1 BoxRowCaption" id="div_LicensePoint">
                <select class="TMP_Procedure" id="'. $Id .'" alt="LicensePoint">
                '.$a_Procedure[$r_FineNotification['LicensePointProcedure']].'
                </select>
            </div>
            <div class="col-sm-1 BoxRowCaption" style="width: 3rem">
            </div>   
            <div class="col-sm-6.5 BoxRowLabel">
            </div>           
            
        </div>                       
        ';
} else {

    $PaymentProcedure = $rs->SelectQuery("SELECT * FROM traffic_law.TMP_PaymentProcedure WHERE FineId=". $Id);
    if(mysqli_num_rows($PaymentProcedure)>0){
        $PaymentProcedure = mysqli_fetch_array($PaymentProcedure);
        if($PaymentProcedure['PaymentProcedure']==1) $PaymentProcedure = '<select class="TMP_Procedure" id="'. $Id .'" alt="TMP_PaymentProcedure"><option value="1" selected>Si</option><option value="0">No</option></select>';
        else $PaymentProcedure = '<select class="TMP_Procedure" id="'. $Id .'" alt="TMP_PaymentProcedure"><option value="1">Si</option><option value="0" selected>No</option></select>';
    } else $PaymentProcedure = '<select class="TMP_Procedure" id="'. $Id .'" alt="TMP_PaymentProcedure"><option></option><option value="1">Si</option><option value="0">No</option></select>';

    $BisProcedure = $rs->SelectQuery("SELECT * FROM TMP_126BisProcedure WHERE FineId=". $Id);
    if(mysqli_num_rows($BisProcedure)>0){
        $BisProcedure = mysqli_fetch_array($BisProcedure);
        if($BisProcedure['BisProcedure']==1) $BisProcedure = '<select class="TMP_Procedure" id="'. $Id .'" alt="TMP_BisProcedure"><option value="1" selected>Si</option><option value="0">No</option></select>';
        else $BisProcedure = '<select class="TMP_Procedure" id="'. $Id .'" alt="TMP_BisProcedure"><option value="1">Si</option><option value="0" selected>No</option></select>';
    } else $BisProcedure = '<select class="TMP_Procedure" id="'. $Id .'" alt="TMP_BisProcedure"><option></option><option value="1">Si</option><option value="0">No</option></select>';

    $PresentationDocumentProcedure = $rs->SelectQuery("SELECT * FROM traffic_law.TMP_PresentationDocumentProcedure WHERE FineId=". $Id);
    if(mysqli_num_rows($PresentationDocumentProcedure)>0){
        $PresentationDocumentProcedure = mysqli_fetch_array($PresentationDocumentProcedure);
        if($PresentationDocumentProcedure['PresentationDocumentProcedure']==1) $PresentationDocumentProcedure = '<select class="TMP_Procedure" id="'. $Id .'" alt="TMP_PresentationDocumentProcedure"><option value="1" selected>Si</option><option value="0">No</option></select>';
        else $PresentationDocumentProcedure = '<select class="TMP_Procedure" id="'. $Id .'" alt="TMP_PresentationDocumentProcedure"><option value="1">Si</option><option value="0" selected>No</option></select>';
    } else $PresentationDocumentProcedure = '<select class="TMP_Procedure" id="'. $Id .'" alt="TMP_PresentationDocumentProcedure"><option></option><option value="1">Si</option><option value="0">No</option></select>';

    $LicensePointProcedure = $rs->SelectQuery("SELECT * FROM traffic_law.TMP_LicensePointProcedure WHERE FineId=". $Id);
    if(mysqli_num_rows($LicensePointProcedure)>0){
        $LicensePointProcedure = mysqli_fetch_array($LicensePointProcedure);
        if($LicensePointProcedure['LicensePointProcedure']==1) $LicensePointProcedure = '<select class="TMP_Procedure" id="'. $Id .'" alt="TMP_LicensePointProcedure"><option value="1" selected>Si</option><option value="0">No</option></select>';
        else $LicensePointProcedure = '<select class="TMP_Procedure" id="'. $Id .'" alt="TMP_LicensePointProcedure"><option value="1">Si</option><option value="0" selected>No</option></select>';
    } else $LicensePointProcedure = '<select class="TMP_Procedure" id="'. $Id .'" alt="TMP_LicensePointProcedure"><option></option><option value="1">Si</option><option value="0">No</option></select>';

    $str_Procedure = '        
        <div class="col-sm-12 BoxRowTitle" >
            <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                Elaborazioni
            </div>
        </div>
        <div class="clean_row HSpace4"></div> 
        <div class="col-sm-12">
            <div class="col-sm-4 BoxRowLabel">
                Elaborare il sollecito di pagamento/ingiunzione in caso di infedele/tardivo/omesso pagamento
            </div>  
            <div class="col-sm-1 BoxRowCaption" id="div_TMP_PaymentProcedure">
                '.$PaymentProcedure.'
            </div>
            <div class="col-sm-1 BoxRowCaption" style="width: 3rem">
            </div>
            <div class="col-sm-6.5 BoxRowLabel">
            </div>
        </div>
        <div class="clean_row HSpace4"></div> 
        <div class="col-sm-12">                 
            <div class="col-sm-4 BoxRowLabel">
                Elaborare il verbale art. 126 Bis in caso di omessa comunicazione dei dati del trasgressore
            </div>  
            <div class="col-sm-1 BoxRowCaption" id="div_TMP_BisProcedure" >
                '.$BisProcedure.'
            </div>
            <div class="col-sm-1 BoxRowCaption" style="width: 3rem">
            </div>       
             <div class="col-sm-6.5 BoxRowLabel">
            </div>      
        </div>
        <div class="clean_row HSpace4"></div> 
        <div class="col-sm-12">               
            <div class="col-sm-4 BoxRowLabel">
                Elaborare il verbale art. 180 in caso di omessa trasmissione della documentazione richiesta
            </div>  
            <div class="col-sm-1 BoxRowCaption" id="div_TMP_PresentationDocumentProcedure">
                '.$PresentationDocumentProcedure.'
            </div>
            <div class="col-sm-1 BoxRowCaption" style="width: 3rem">
            </div>   
             <div class="col-sm-6.5 BoxRowLabel">
            </div>   
        </div>
        <div class="clean_row HSpace4"></div> 
        <div class="col-sm-12">                        
            <div class="col-sm-4 BoxRowLabel">
                Procedura con la decurtazione punti della patente di guida del trasgressore comunicato
            </div>  
            <div class="col-sm-1 BoxRowCaption" id="div_TMP_LicensePointProcedure">
                '.$LicensePointProcedure.'
            </div>
            <div class="col-sm-1 BoxRowCaption" style="width: 3rem">
            </div>              
             <div class="col-sm-6.5 BoxRowLabel">
            </div> 
        </div>                       
        ';
}


  				$str_out.='
                <div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Tipo infrazione
					</div>
					<div class="col-sm-10 BoxRowCaption">
        				<input type="hidden" name="ViolationTypeId" id="ViolationTypeId">
        				<span id="span_ViolationTitle">' . utf8_encode($table_row['ViolationTitle']) . '</span>
					</div>
					 <div class="clean_row HSpace4"></div>
					'. $strAcertatore .'
					'. $str_ControllerDate .'
  				</div> 
  				<div class="clean_row HSpace4"></div>
	        	<div class="col-sm-12"  style="'.$styleCon.'">
	        	<input type="hidden" value="" id="ReasonId_Second" name="ReasonId_Second">
        			<div class="col-sm-3 BoxRowLabel" >
        				Mancata contestazione
					</div>
					'.$contestazione.'
  				</div> 
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12" style="height:6.4rem;">
        			<div class="col-sm-3 BoxRowLabel" style="height:6.4rem;">
        				Note operatore
					</div>
					<div class="col-sm-9 BoxRowCaption" style="height:6.4rem;">
                        <textarea name="Note" class="form-control frm_field_string" style="width:40rem;height:5.5rem">' . $table_row['Note'] . '</textarea>	
					</div>
  				</div>  
                <div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-3 BoxRowLabel">
        				Stato pratica
					</div>
					<div class="col-sm-9 BoxRowCaption">
                        ' . CreateSelect("StatusType", "Id=13", "Id", "StatusTypeId", "Id", "Title", $table_row['StatusTypeId'], true) . '
					</div>
  				</div>
  				<div class="clean_row HSpace4"></div>
  				' . $strTrespasser . '
        	</div>
        	
        	<div class="col-sm-6">
        	    <div class="col-sm-12" >
        			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
        				DOCUMENTAZIONE
					</div>
  				</div> 
                <div class="col-sm-12 BoxRow" style="width:100%;height:10rem;">
                    <div class="example">
                        <div id="fileTreeDemo_1" class="col-sm-12 BoxRowLabel" style="height:10rem;overflow:auto"></div>
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-12 BoxRow" style="width:100%;height:60.2rem;">
                    <div class="imgWrapper" id="preview_img" style="height:60rem;overflow:auto; display: none;">
                        <img id="preview" class="iZoom"  />
                    </div>
                    <div id="preview_doc" style="height:60rem;overflow:auto; display: none;"></div>                
                </div>		
  			</div>
  		</div>';
$str_out.=$str_Procedure;
$str_out.='
  		
  		
  		
  		
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                   <input type="submit" class="btn btn-default" id="update" value="Modifica"  />
                   <input type="button" class="btn btn-default" id="back" value="Indietro"  />
                </div>    
            </div>
        </div>
    </form>	
    </div>';

$str_out .= '
<div id="BoxArticleSearch" style="top:40rem;left:50rem;">
    <div class="col-sm-12">
        <div class="col-sm-12 BoxRowLabel" style="text-align:center">           
            Articoli trovati
        </div>
        <div class="row-fluid">
            <div class="col-sm-12">
                 <div class="BoxRowCaption col-sm-3">
                    <input type="text" name="searchs" id="searchs"  class="form-control bv-form">
                    <input type="hidden" id="art_num" value="">
                 </div>
                 <div class="BoxRowCaption col-sm-3">
                    <button type="button" id="search_btn" class="btn btn-default btn-xs" style="margin-top: -8px">Cerca</button>
                    <i class="glyphicon glyphicon-remove" id="remove_btn" style="margin-top:0.1rem;font-size:1.7rem;"></i>
                 </div>
                 <div class="BoxRowCaption col-sm-6"></div>
            </div>
        </div>  
        <span class="fa fa-times-circle close_window_article" style="color:#fff;position:absolute; right:10px;top:2px;font-size:20px; "></span>  
    </div>
    <div class="clean_row HSpace4"></div> 
    <div id="Div_Article_Content" style="overflow:auto;height:23rem"></div>
</div>       	
';
echo $str_out;

echo $str_BoxArticleOwner;

echo $str_BoxAdditionalArticleOwner;
?>

    <script type="text/javascript">

        $('.TMP_Procedure').change(function () {
            var Field = $(this).attr("alt");
            var Id = $(this).attr("id");
            var Value = $(this).val();
            $.ajax({
                url: 'ajax/ajx_upd_finenotification_exe.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id: Id, Field: Field, Value:Value},
                success: function (data) {
                    $("#div_"+Field).html(data.str_value);
                }
            });
        });

        $('.EditAdditionalSanction').click(function () {
            var get_ArticleNumber_text = $(this).attr('id');
            Article = get_ArticleNumber_text.split("_");
            ArticleNumber_Text = Article[1];

            if($('#AdditionalSanction_'+ArticleNumber_Text).is(":visible")) {
                $('#AdditionalSanction_'+ArticleNumber_Text).hide();
                $('#AdditionalSanctionInput_'+ArticleNumber_Text).show();
            } else {
                $('#AdditionalSanction_'+ArticleNumber_Text).show();
                $('#AdditionalSanctionInput_'+ArticleNumber_Text).hide();
                $('#AdditionalSanctionInputText_'+ArticleNumber_Text).val('');
            }
        });

        $(document).on('click','#Edit_Contestazione',function (e) {
            if ($('#ReasonId').is(":visible")){
                $('#ReasonId').hide();
                $('#Reason_Text').show();
            }else{
                $('#ReasonId').show();
                $('#Reason_Text').hide().val('');
            }
        });

        var addMass = <?=MASS?>;
        $('#VehicleMass').change(function() {
            checkFee();
        });

        function checkFee() {
            var mass = $('#VehicleMass').val();
            var sum = 0;
            for (var i = 1; i <= 5; i++) {

                var Fee = parseFloat($('#art_' + i).attr('fee'));
                var MaxFee = parseFloat($('#art_' + i).attr('maxFee'));

                if ($('#art_' + i).attr('addMass') == 1) {
                    if(mass<addMass){
                        $('#Fee_'+i).val(Fee.toFixed(2));
                        $('#MaxFee_'+i).val(MaxFee.toFixed(2));
                        sum+=Fee;
                        $('#span_Fee_'+i).html(Fee.toFixed(2));
                        $('#span_MaxFee_'+i).html(MaxFee.toFixed(2));
                    } else {
                        $('#Fee_'+i).val((Fee*2).toFixed(2));
                        $('#MaxFee_'+i).val((MaxFee*2).toFixed(2));
                        sum+=Fee*2;
                        $('#span_Fee_'+i).html((Fee*2).toFixed(2));
                        $('#span_MaxFee_'+i).html((MaxFee*2).toFixed(2));
                    }
                }
                if ($('#art_' + i).attr('addMass') == '0') {
                    sum+=Fee;
                }
            }
            sum += parseFloat($('#AdditionalFee').html());
            $('#span_TotalFee').html(sum.toFixed(2));
        }

    </script>
    <script type="text/javascript">
        var controller = true;
        var chkCode = true;
        var giaPresente = false;
        $('#VehicleTypeId').change(function () {
            var type = $('#VehicleTypeId').val();
            if(type==2 || type==9) {
                $('#massa').hide();
                $('#toHide2').show();
            }
            else {
                $('#massa').show();
                $('#toHide2').hide();
            }
        });

        var namespan;
        $('#tab_company').click(function(){
            $('#Genre').val('D');
            $( "#sexM" ).prop( "checked", true );
            $( "#sexF" ).prop( "checked", false );
        });

        $('#tab_Trespasser').click(function(){
            $('#Genre').val('M');
        });

        $('#sexM').click(function(){
            $('#Genre').val('M');
        });
        $('#sexF').click(function(){
            $('#Genre').val('F');
        });



        $('.find_list').keyup(function () {
            var min_length = 2; // min caracters to display the autocomplete
            var keyword = $(this).val();

            namespan = 'Address';
            if (keyword.length >= min_length) {

                $.ajax({
                    url: 'ajax/search.php?searchtype=t',
                    type: 'POST',
                    data: {keyword: keyword, field: namespan},
                    success: function (data) {

                        $('#' + namespan + '_List').show();
                        $('#' + namespan + '_List').html(data);
                    }
                });
            } else {
                $('#' + namespan + '_List').hide();
            }
        });


        $('#Address').focusout(function(){
            setTimeout(function () {
                $('#Address_List').hide();
            }, 150);
        });
        $('#Address').focusin(function(){
            $('#Address_List').show();
        });


        function set_item(item, id) {
            // change input value
            $('#' + namespan).val(item);
            //$('#CustomerID').val(id);
            $('#' + namespan + '_List').hide();
        }


        $('document').ready(function () {
            $('#update').hide();
            $('#preview').iZoom({borderColor: '#294A9C', borderStyle: 'double', borderWidth: '3px'});


            <?= CreateTxtChangeJQ("Reason", "ReasonTypeId=1", "Title" . LAN, "ReasonId", "Id", "Title" . LAN, "span_ReasonId") ?>

            <?= CreateSltChangeJQ("Controller", "CityId='" . $_SESSION['cityid'] . "' AND Disabled=0", "Name", "ControllerCode", "Code", "Id", "ControllerId") ?>



            chkDate = true;
            $('#FineDate').focusout(function() {
                chkDate = true;
                var str = $('#FineDate').val();
                if(str=='') {
                    $("#span_date").addClass("help-block");
                    $("#span_date").html('Data errato!');
                }
                if(str.length==10){
                    if(parseInt(str.substr(6,4)) != parseInt('<?= $_SESSION['year']?>')) {
                        chkDate = false;
                        $("#span_date").addClass("help-block");
                        $("#span_date").html('Data errato!');
                    }
                    if(parseInt(str.substr(0,2)) > 31 || parseInt(str.substr(0,2)) < 1) {
                        chkDate = false;
                        $("#span_date").addClass("help-block");
                        $("#span_date").html('Data errato!');
                    }
                    if(parseInt(str.substr(3,2)) > 12 || parseInt(str.substr(3,2)) < 1) {
                        chkDate = false;
                        $("#span_date").addClass("help-block");
                        $("#span_date").html('Data errato!');
                    }
                } else {
                    chkDate = false;
                    $("#span_date").addClass("help-block");
                    $("#span_date").html('Data errato!');
                }
                if(chkDate){
                    $("#span_date").removeClass("help-block");
                    $("#span_date").html('');
                }
            });

            $('#FineTime').change(function() {

                var str = $('#FineTime').val();
                var part1 = parseInt(str.substr(0,2));
                var part2 = parseInt(str.substr(3,2));

                var hours = 24;
                var minutes = 59;

                if (part1 >hours || part2 >minutes){

                    chkTime = false;
                    $("#update").prop("disabled", true);
                    $("#span_time").addClass("help-block");
                    $("#span_time").html('Ora errato!');
                } else{

                    chkTime=true;
                    if(chkTime==true) {
                        $("#update").prop("disabled", false);
                        $("#span_time").removeClass("help-block");
                        $("#span_time").html('');
                    }
                }
                if(str.length==4){

                    $('#FineTime').val( str.substr(0,2) + ":" + str.substr(2,2) );
                }
            });


            <?= $str_tree ?>

            <?= $str_Img ?>

            $('#CountryId').change(function(){
                var id = $( "#CountryId" ).val();
                $('#VehicleCountry').val($( "#CountryId option:selected" ).text());

                if(id=='Z110'){
                    var ent = $.ajax({
                        url: "ajax/department.php",
                        type: "POST",
                        data: {id:id},
                        dataType: "text"
                    });
                    ent.done(function(data){
                        $('#DepartmentId').html(data).show();
                        $('#department').show();
                        $('#toHide').hide();
                    });
                    ent.fail(function(jqXHR, textStatus){
                        alert( "Request failed: " + textStatus );
                    });
                }else{
                    $('#DepartmentId').html('').hide();
                    $('#department').hide();
                    $('#toHide').show();
                }
            });

            $('#VehiclePlate').change(function () {
                var VehiclePlate = $(this).val();
                var FineDate = $('#FineDate').val();
                var FineTime = $('#FineTime').val();
                var id = $( "#CountryId" ).val();
                var checkPlate;
                var find;
                var message = "Attenzione: la targa potrebbe non essere italiana ";
                var VehicleTypeId = $('#VehicleTypeId').val();
                if(id=='Z000'){
                    find = 0;
                    // checkPlate = '^[a-zA-Z]{2}[a-zA-Z0-9][0-9]{5}$';
                    // if(VehiclePlate.match(checkPlate)!=null) find = 1;
                    if(VehicleTypeId == 9){
                        checkPlate = '^[a-zA-Z]{2}[0-9]{5}$';
                        if(VehiclePlate.match(checkPlate)!=null)  find = 1;
                    } else {
                        checkPlate = '^[a-zA-Z]{2}[0-9]{3}[a-zA-Z]{2}$';
                        if(VehiclePlate.match(checkPlate)!=null) find = 1;
                    }
                    if(VehiclePlate.includes('q') || VehiclePlate.includes('Q') || VehiclePlate.includes('u') || VehiclePlate.includes('U') ||
                        VehiclePlate.includes('o') || VehiclePlate.includes('O') || VehiclePlate.includes('i') || VehiclePlate.includes('I')) {
                        find = 0;
                        message += 'oppure si tratta di una vecchia targa';
                    }
                    if(find==0) alert(message);
                }

                if(id=='Z112'){
                    checkPlate ='^[a-zA-Z]{1,3}[ t][ t][a-zA-Z]{1,3}[0-9]{1,4}$';
                    if(VehiclePlate.match(checkPlate)==null) alert("Attenzione: la targa potrebbe non essere tedesca");
                }

                if(id=='Z110'){
                    find = 0;
                    checkPlate ='^[a-zA-Z]{2}[0-9]{3}[a-zA-Z]{2}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;

                    checkPlate ='^[0-9]{3,4}[a-zA-Z]{2}[0-9]{2}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;

                    if(find==0) alert("Attenzione: la targa potrebbe non essere francese");
                }


                if(id=='Z131'){
                    find = 0;
                    checkPlate ='^[a-zA-Z]{2}[0-9]{4}[a-zA-Z]{2}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;

                    checkPlate ='^[0-9]{4}[a-zA-Z]{3}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;

                    if(find==0) alert("Attenzione: la targa potrebbe non essere spagnola");
                }

                if(id=='Z129'){
                    checkPlate ='^[a-zA-Z]{1,2}[0-9]{2}[a-zA-Z]{3}$';
                    if((VehiclePlate.match(checkPlate))==null) alert("Attenzione: la targa potrebbe non essere rumena");
                }
                if(id=='Z133'){
                    checkPlate ='^[a-zA-Z]{2}[0-9]{2,6}$';
                    if((VehiclePlate.match(checkPlate))==null) alert("Attenzione: la targa potrebbe non essere svizzera");
                }
                if(id=='Z103'){
                    find = 0;
                    checkPlate ='^[0-9][a-zA-Z]{2,3}[0-9]{2,3}$';
                    if(VehiclePlate.match(checkPlate)==null) find = 1;

                    checkPlate ='^[a-zA-Z]{3}[0-9]{3}$';
                    if((VehiclePlate.match(checkPlate))==null) find = 1;

                    if(find==0) alert("Attenzione: la targa potrebbe non essere belga");
                }

                $.ajax({
                    url: 'ajax/search_plate.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {VehiclePlate:VehiclePlate, FineDate:FineDate, FineTime:FineTime},
                    success:function(data){


                        if(data.H>0){
                            $('#div_chkPlate').addClass("div_Height_"+data.H);
                        }else{
                            $('#div_chkPlate').removeClass("div_Height_150");
                            $('#div_chkPlate').removeClass("div_Height_300");
                        }

                        if(data.F!=""){
                            $('#fine_content').addClass("div_HiddenContent");
                            $('#fine_content').html(data.F);
                            $('#fine_content').show();
                        }else{
                            $('#fine_content').removeClass("div_HiddenContent");
                            $('#fine_content').hide();
                        }

                        if(data.T!=""){
                            $('#trespasser_content').addClass("div_HiddenContent");
                            $('#trespasser_content').html(data.T);
                            $('#trespasser_content').show();
                        }else{
                            $('#trespasser_content').removeClass("div_HiddenContent");
                            $('#trespasser_content').hide();
                        }

                    }
                });
            });
            $("#overlay").click(function () {
                $(this).fadeOut('fast');
                $('#BoxArticleOwner').hide();
                $('#BoxAdditionalArticleOwner').hide();

            });

            $('#back').click(function () {
                window.location = "<?= $str_BackPage . $str_GET_Parameter ?>"
                return false;
            });
        });

        $('#f_violation').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        $('#f_violation').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_required: {
                    selector: '.frm_field_required',
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
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

                frm_field_date: {
                    selector: '.frm_field_date',
                    validators: {
                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }
                    }
                },


                FineDate:{
                    validators: {
                        notEmpty: {message: 'Richiesto'},

                        date: {
                            format: 'DD/MM/YYYY',
                            message: 'Data non valida'
                        }

                    }

                },
                FineTime: {
                    validators: {
                        notEmpty: {message: 'Richiesto'},
                        regexp: {
                            //regexp: '^(1?[0-9]|2[0-9]):[0-5][0-9]$',
                            regexp: '^(0?[0-9]|1?[0-9]|2[0-4]):[0-5][0-9]$',
                            message: 'Ora non valida'
                        }
                    }

                },

            }
        }).on('success.form.bv', function(e){
            e.preventDefault();
            var today_date = $('#FineDate').val();
            var ora = $('#FineTime').val();
            var targa = $('#VehiclePlate').val();
            var reason = $('#ReasonId').val();
            var Code = $('#Code').val();
            var Controller = $('.select_controller_1').val();
            var FineTypeId = $('#FineTypeId').val();
            var Eludi_Controlli  = $('#Conrolli').is(':checked');
            var FineId = '<?=$_GET['Id']?>';
            var article1 = $('#ArticleId_1').val();
            var article2 = $('#ArticleId_2').val();
            var article3 = $('#ArticleId_3').val();
            var article4 = $('#ArticleId_4').val();
            var article5 = $('#ArticleId_5').val();
            var articles = {today_date:today_date,
                ora:ora,
                targa:targa,
                article1:article1,
                article2:article2,
                article3:article3,
                article4:article4,
                article5:article5,
                update:"update",
                FineId:FineId,
            };
            var chkYear;
            $.ajax({
                url: 'ajax/mgmt_validate_fine_ajax.php',
                type: 'POST',
                data: {Code:Code,Controller:Controller,"update":"update",Eludi_Controlli:Eludi_Controlli,FineTypeId:FineTypeId},
                success:function(data){
                    if(data=="NOB"){
                        $("#update").prop("disabled", true);
                        $("#span_code").addClass("help-block");
                        $("#span_code").html('Codice non essiste!!');
                        chkCode = false;
                    }
                    if(data=="NE"){
                        $("#update").prop("disabled", true);
                        $("#span_acce_1").addClass("help-block");
                        $("#span_acce_1").html('Controller Errato!');
                        chkCode = false;
                    }

                    if(data == "GE"){
                        $("#update").prop("disabled", true);
                        $("#span_code").addClass("help-block");
                        $("#span_code").html('Codice gia esiste!!');
                        chkCode = false;
                    }
                    if(data=="OK"){
                        if(today_date =="" || ora ==""  || targa==""){
                            e.preventDefault();
                        }else {
                            $("#update").prop("disabled", false);
                            $("#span_acce").removeClass("help-block");
                            $("#span_acce").html('');

                            $.ajax({
                                url: 'ajax/mgmt_check_verbali.php',
                                type: 'POST',
                                data: articles,
                                success:function(data){
                                    if (data == "NOK"){
                                        $('.check').append('<div class="alert alert-warning">Questa multa esiste nel database. Per favore seleziona un altro articolo!</div>');
                                        $("html, body").animate({ scrollTop: 0 }, "slow");
                                        $("#update").prop("disabled", true);
                                    }
                                    if (data == "OK"){
                                        $('.check').hide();
                                        if (chkCode && giaPresente==false && controller && chkDate) document.f_violation.submit();
                                    }
                                }
                            });
                        }

                    }

                }
            });
            return false;
        });

        $('#Conrolli').on('click',function(){
            $('#Code').trigger("change");
            $('#ControllerId').trigger("change");
            var Eludi_Controlli  = $('#Conrolli').is(':checked');
            if (Eludi_Controlli == true){
                $("#update").prop("disabled", false);
                $("#span_acce").removeClass("help-block");
                $("#span_acce").html('');
            }
        });

        $('#Code').change(function() {
            var FirstCode = $(this).val();
            var SendCode = $(this).val();
            var validCode = true;
            var FineTypeId = $('#FineTypeId').val();
            if (FirstCode.indexOf('/') > 0)
            {
                FirstCode = FirstCode.replace("/","");
            }
            if($.isNumeric(FirstCode)==false){
                if (FirstCode.length >2){
                    var str1 = FirstCode.substr(0,2);
                    var str2 = FirstCode.substr(2,FirstCode.length);
                    if ($.isNumeric(str2)==false){

                        $("#update").prop("disabled", true);
                        $("#span_code").addClass("help-block");
                        $("#span_code").html('Errato inserimento code1!');
                        validCode = false;
                    }
                    if ($.isNumeric(str1[0])){
                        $("#update").prop("disabled", true);
                        $("#span_code").addClass("help-block");
                        $("#span_code").html('Errato inserimento code2!');
                        validCode = false;
                    }
                }else if (FirstCode.length==2){
                    var str1 = FirstCode.substr(0,1);
                    var str2 = FirstCode.substr(1,FirstCode.length);
                    if ($.isNumeric(str1)==true || $.isNumeric(str1)==false && $.isNumeric(str2)==false) {
                        $("#update").prop("disabled", true);
                        $("#span_code").addClass("help-block");
                        $("#span_code").html('Errato inserimento code3!');
                        validCode = false;
                    }
                }
                FirstCode = str1+str2;
            }else{
                $("#update").prop("disabled", false);
                $("#span_code").removeClass("help-block");
                $("#span_code").html('');
            }
            if (validCode) {
                $.ajax({
                    url: 'ajax/search_code.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {Code: SendCode,FineTypeId:FineTypeId},
                    success: function (data) {
                        if (data.Result == "NO") {
                            $("#span_code").addClass("help-block");
                            $("#span_code").html('Già presente!');
                            $("#update").prop("disabled", true);
                            chkCode =false;
                        }
                        if (data.Result == "OK") {
                            $("#update").prop("disabled", false);
                            $("#span_code").removeClass("help-block");
                            $("#span_code").html('');
                            chkCode = true;
                        }
                    }
                });
            }

        });


        <?= $str_JQPersonalization ?>



    </script>
    <script>
        $(document).ready(function () {
            checkFee();
            var ArticleNumber = <?=$table_row['ArticleNumber']?>;

            $( function() {
                $( "#BoxArticleSearch" ).draggable();
            } );

            $(".close_window_article").click(function () {
                $('#BoxArticleSearch').hide();
            });

            $('.glyphicon-search').on('click',function () {


                var RuleTypeId = $('#RuleTypeId').val();
                var get_ArticleNumber = $(this).attr('id');
                Article = get_ArticleNumber.split("_");
                ArticleNumber_Edit = Article[1];
                var id1=$('#id1_'+ArticleNumber_Edit).val();
                $('#EditArticle_'+ArticleNumber).show();
                $("#art_num").val(ArticleNumber_Edit);
                $.ajax({
                    url: 'ajax/ajx_src_article_lst.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Id1: id1, RuleTypeId: RuleTypeId, ArticleCounter: ArticleNumber_Edit},
                    success: function (data) {

                        $('#Div_Article_Content').html(data.Article);
                        $('#BoxArticleSearch').fadeIn('slow');

                    }
                });

            });

            $("#search_btn").click(function () {
                var src = $('#searchs').val();
                var id1=null;
                var RuleTypeId = $('#RuleTypeId').val();
                var art_number = $("#art_num").val();
                $.ajax({
                    url: 'ajax/ajx_src_article_lst.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Id1: id1, search: src, ArticleCounter: art_number, RuleTypeId: RuleTypeId},
                    success: function (data) {
                        $('#Div_Article_Content').html(data.Article);
                    }
                });
            });
            $("#remove_btn").click(function () {
                var src = $('#searchs').val('');
            });

            $('.article_change_1').on('change',function () {
                var artNum = 1;
                chkArticle(artNum);
            });
            $('.article_change_2').on('change',function () {
                var artNum = 2;
                chkArticle(artNum);
            });
            $('.article_change_3').on('change',function () {
                var artNum = 3;
                chkArticle(artNum);
            });
            $('.article_change_4').on('change',function () {
                var artNum = 4;
                chkArticle(artNum);
            });
            $('.article_change_5').on('change',function () {
                var artNum = 5;
                chkArticle(artNum);
            });

            //detector


            function chkArticle(ArticleNumber) {

                var id1=$('#id1_'+ArticleNumber).val();
                var id2=$('#id2_'+ArticleNumber).val();
                var id3=$('#id3_'+ArticleNumber).val();
                var FineTime = $('#FineTime').val();
                if(id1!='' && (id2!='' || id3!='')){

                    var RuleTypeId = $('#RuleTypeId').val();
                    var request = $.ajax({
                        url: "ajax/search_article.php",
                        dataType: 'json',
                        type: "post",
                        cache: false,
                        data: "FineTime="+FineTime+"&RuleTypeId="+RuleTypeId +"&id1=" + id1 + "&id2=" + id2 + "&id3=" + id3+"&ArticleCounter="+ArticleNumber
                    });

                    request.done(function (response){
                        $("#downart").show();
                        if (response.NonExist ==1){

                            $('#span_Article_'+ArticleNumber).html(response.Description).css("background-color","red");
                            $("#update").prop("disabled", true);
                        }else{
                            $("#update").prop("disabled", false);
                            $('#Edit_Contestazione').show();
                            $('#span_Article_'+ArticleNumber).html(response.Description);
                            $('#span_Article_'+ArticleNumber).css("background-color","");
                        }
                        if(response.Result==1){
                            $('#ArticleId_'+ArticleNumber).val(response.ArticleId);
                            if(ArticleNumber==1){
                                $('#ViolationTypeId').val(response.ViolationTypeId);
                                $('#span_ViolationTitle').html(response.ViolationTitle);
                            }
                            $('#artcomune_'+ArticleNumber).val(response.ArtComunali);
                            $('#Fee_'+ArticleNumber).val(response.Fee);
                            $('#span_Fee_'+ArticleNumber).html(response.Fee);
                            $('#MaxFee_'+ArticleNumber).val(response.MaxFee);
                            $('#span_MaxFee_'+ArticleNumber).html(response.MaxFee);
                            $('#art_'+ArticleNumber).attr('fee', response.Fee);
                            $('#art_'+ArticleNumber).attr('addMass', response.AddMass);
                            $('#art_'+ArticleNumber).attr('maxFee', response.MaxFee);
                            $('#art_'+ArticleNumber).attr('desc', response.Description);
                            $('#AdditionalSanctionSelect_'+ArticleNumber).html(response.AdditionalSanctionSelect);
                            $('#YoungLicensePoint_'+ArticleNumber).html(response.LicensePoint);
                            checkFee();
                        }
                    });
                    request.fail(function (jqXHR, textStatus){
                        $('#span_Article_'+ArticleNumber).html("ERR: "+ textStatus);
                    });

                    //event.preventDefault();
                }

            }

            /////comunali

            $('#artcomune_1').on('change',function () {
                var artNum = 1;
                checkArticleComunali(artNum);
            });
            $('#artcomune_2').on('change',function () {
                var artNum = 2;
                checkArticleComunali(artNum);
            });
            $('#artcomune_3').on('change',function () {
                var artNum = 3;
                checkArticleComunali(artNum);
            });
            $('#artcomune_4').on('change',function () {
                var artNum = 4;
                checkArticleComunali(artNum);
            });
            $('#artcomune_5').on('change',function () {
                var artNum = 5;
                checkArticleComunali(artNum);
            });

            function checkArticleComunali(index){
                var search = $("#artcomune_"+index).val();
                var FineTime = $('#FineTime').val();
                if(search!=''){
                    var RuleTypeId = $('#RuleTypeId').val();
                    var request = $.ajax({
                        url: "ajax/search_article.php",
                        dataType: 'json',
                        type: "POST",
                        cache: false,
                        data: "FineTime="+FineTime+"&RuleTypeId="+RuleTypeId +"&ArticleCounter="+index+"&search="+search
                    });

                    request.done(function (response){

                        $("#downart").show();
                        $('#span_Article_'+index).html(response.Description);
                        $('#ReasonId').html(response.ReasonDescription);
                        $('#ReasonId').trigger("change");
                        $('#EditArticle_'+index).show();
                        $('#Edit_Contestazione').show();
                        if(response.Result==1){

                            $('#id1_'+index).val(response.Id1);
                            $('#id2_'+index).val(response.Id2);
                            $('#id3_'+index).val(response.Id3);
                            $('#ArticleId_'+index).val(response.ArticleId);

                            if(index==1){
                                $('#ViolationTypeId').val(response.ViolationTypeId);
                                $('#span_ViolationTitle').html(response.ViolationTitle);
                            }
                            $('#artcomune_'+index).val(response.ArtComunali);
                            $('#Fee_'+index).val(response.Fee);
                            $('#span_Fee_'+index).html(response.Fee);
                            $('#MaxFee_'+index).val(response.MaxFee);
                            $('#span_MaxFee_'+index).html(response.MaxFee);

                            $('#art_'+index).attr('fee', response.Fee);
                            $('#art_'+index).attr('addMass', response.AddMass);
                            $('#art_'+index).attr('maxFee', response.MaxFee);
                            $('#art_'+index).attr('desc', response.Description);
                            $('#AdditionalSanctionSelect_'+index).html(response.AdditionalSanctionSelect);
                            $('#YoungLicensePoint_'+index).html(response.LicensePoint);
                            checkFee();
                        }

                    });
                    request.fail(function (jqXHR, textStatus){
                        $('#span_Article_'+index).html("ERR: "+ textStatus);
                    });
                }
            }

            $('.article_change').keyup(function (e) {
            const code = $(this).val();
            var id1=null;
            var RuleTypeId = $('#RuleTypeId').val();
            let isFound = false;
            const index = $(this).data('number');
            $.ajax({
                url: 'ajax/ajx_src_article_lst.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id1: id1, search_code: code, ArticleCounter: 1, RuleTypeId: RuleTypeId},
                success: function (data) {
                    $('#span_Article_'+index).html(data.Article);
                    $('#id1_'+index).val(data.Id1);
                    $('#id2_'+index).val(data.Id2);
                    $('#id3_'+index).val(data.Id3);
                    if (data.Article != 'Nessun articolo trovato') {
                        $("#downart").show();
                        var id1=data.Id1;
                        var id2=data.Id2;
                        var id3=data.Id3;
                        var FineTime = $('#FineTime').val();
                        var request = $.ajax({
                            url: "ajax/search_article.php",
                            dataType: 'json',
                            type: "post",
                            cache: false,
                            data: "FineTime="+FineTime+"&RuleTypeId="+RuleTypeId +"&id1=" + id1 + "&id2=" + id2 + "&id3=" + id3
                        });

                        request.done(function (response){
                            $('#ArticleId_'+index).val(response.ArticleId);
                            $('#span_Article_'+index).html(response.Description);
                            $('#Fee_'+index).val(response.Fee);
                            $('#span_Fee_'+index).html(response.Fee);
                            $('#MaxFee_'+index).val(response.MaxFee);
                            $('#span_MaxFee_'+index).html(response.MaxFee);
                            $('#YoungLicensePoint_'+index).html(response.LicensePoint);
                            $('#ReasonId').html(response.ReasonDescription);
                        });
                    }
                }
            });

        });

            $(document).on('click','.add_article',function(e){
                $("#downart").show();
                var element = $('#element').val();
                var get = $(this).attr('id');
                var str = get.split("*");
                var RuleTypeId = $('#RuleTypeId').val();
                var FineTime = $('#FineTime').val();
                var id1 = str[0];
                var id2 = str[1];
                var id3 = str[2];
                $('#id1_'+element).val(id1);
                $('#id2_'+element).val(id2);
                $('#id3_'+element).val(id3);
                var request = $.ajax({
                    url: "ajax/search_article.php",
                    dataType: 'json',
                    type: "post",
                    cache: false,
                    data: "FineTime="+FineTime+"&RuleTypeId="+RuleTypeId +"&id1=" + id1 + "&id2=" + id2 + "&id3=" + id3
                });

                request.done(function (response){

                    $('#ArticleId_'+element).val(response.ArticleId);
                    $('#span_Article_'+element).html(response.Description);
                    $('#Fee_'+element).val(response.Fee);
                    $('#span_Fee_'+element).html(response.Fee);
                    $('#MaxFee_'+element).val(response.MaxFee);
                    $('#span_MaxFee_'+element).html(response.MaxFee);
                    $('#ReasonId').html(response.ReasonDescription);

                    $('#art_'+ArticleNumber).attr('fee', response.Fee);
                    $('#art_'+ArticleNumber).attr('addMass', response.AddMass);
                    $('#art_'+ArticleNumber).attr('maxFee', response.MaxFee);
                    $('#art_'+ArticleNumber).attr('desc', response.Description);
                    $('#AdditionalSanctionSelect_'+ArticleNumber).html(response.AdditionalSanctionSelect);
                    $('#YoungLicensePoint_'+element).html(response.LicensePoint);
                    checkFee();
                });


            });


            $(".fa-pencil-square-o, .glyphicon-search, .fa-share, .fa-caret-down, .fa-caret-up, .fa-edit").hover(function(){
                $(this).css("color","#2684b1");
                $(this).css("cursor","pointer");
            },function(){
                $(this).css("color","#fff");
                $(this).css("cursor","");
            });

            // $('#EditArticle_1').click(function () {
            //
            //     $('#ArticleText_1').delay(2000).show();
            //     $('#span_Article_1').html('');
            // });

            $('.fa-pencil-square-o').click(function () {

                var idButton = $(this).attr('id');
                var str = idButton.split("_");
                articlenum_nr = parseInt(str[1]);
                var text = $('#span_Article_'+articlenum_nr).text();
                if($('#ArticleText_'+articlenum_nr).is(":visible")) {
                    $('#textarea_'+articlenum_nr).val('');
                    $('#ArticleText_'+articlenum_nr).hide();
                    $('#span_Article_'+articlenum_nr).html($('#art_'+articlenum_nr).attr('desc'));
                }
                else {
                    $('#ArticleText_'+articlenum_nr).show();
                    $('#span_Article_'+articlenum_nr).html('');
                    $('#textarea_'+articlenum_nr).val(text);
                }

            });
            if (ArticleNumber > 1){
                $("#upart").show();
            }


            $('#downart').click(function () {

                ArticleNumber++;

                $("#BoxArticle_"+ArticleNumber).show();
                $("#upart").show();
                $(this).hide();
                if(ArticleNumber==5){
                    $("#downart").hide();
                }
                $('#ArticleNumber').val(ArticleNumber);

            });
            $(document).on('click','.add_article',function () {
                $('#update').val("Salva");
                $('#update').show();
                $('#back').val("Annulla")
            });

            $('#upart').click(function () {

                $("#BoxArticle_"+ArticleNumber).hide();
                ArticleNumber--;

                if(ArticleNumber==1){
                    $("#upart").hide();
                    $("#downart").show();
                }else{
                    $("#downart").show();
                }
                $('#ArticleNumber').val(ArticleNumber);
                $('#update').val("Salva");
                $('#update').show();
                $('#back').val("Annulla");
                // var totFee = 0;
                // for(var i=1;i<=ArticleNumber;i++){
                //     totFee = totFee + Number($('#Fee_'+i).val());
                // }
                // totFee = totFee + Number(totCharge);
                // $('#span_TotalFee').html(totFee.toFixed(2));


            });

            $("form :input").change(function() {
                var changed = $(this).closest('form').data('changed', true);
            });
            $('form').focusout(function() {
                if($(this).closest('form').data('changed')) {
                    $('#update').val("Salva");
                    $('#update').show();
                    $('#back').val("Annulla")
                }else{
                    console.log("not changed")
                }
            });
        });
    </script>
    <script>
        $(document).ready(function(){
            var AccertatoreNumber = $('#AccertatoreNumber').val();
            for(i=1;i<=AccertatoreNumber;i++){
                $(document).on('change','.select_controller_'+i,function (e) {
                    var cont_id = $(this).attr('order');
                    var id = $(this).val();
                    var article = $('#id1_1').val();
                    var FineDate = $('#FineDate').val();
                    check_accertatore(id,article,FineDate,cont_id)
                });
            }
            var nr = $('#nr').val();
            if(nr>1){
                $("#upace").show();
            }

            var ControllerTemplate = index =>`
                <div class id="BoxAccertatore_${index}">

                    <div class="col-sm-4 BoxRowLabel" style="height:4rem;background-color: rgb(40, 114, 150);">
                        Accertatore
                    </div>

                    <div class="col-sm-2 BoxRowCaption" style="height:4rem;background-color: rgb(40, 114, 150);">
                        <input class="form-control frm_field_numeric input_accertatore_number" data-number="${index}" type="text" name="ControllerCode" id="ControllerCode" style="width:5rem">
                    </div>

                    <div class="col-sm-6 BoxRowCaption datas_${index}" style="height:4rem;background-color: rgb(40, 114, 150);">
                        <span id="span_acce_${index}"></span>
                        <select class="form-control frm_field_required select_controller_${index}" name="ControllerId[]" id="ControllerId[]" order="${index}">
                        <option></option>
                        <?php
                $ControllersOptions = "";
                foreach ($all_controllers as $row_selected ){
                    $id_all = $row_selected['Id'];
                    $value_all = $row_selected['Name'];
                    $code_all = $row_selected['Code'];
                    $ControllersOptions .= "<option  value=\"$id_all\">$value_all</option>";
                }
                echo $ControllersOptions;
                ?>
                        </select>
                    </div>

                </div>
            `;

            if (AccertatoreNumber > 1){
                $("#upace_upd").show();
            }

            $(document).on('change','#FineDate',function() {
                for(i=1;i<=5;i++) {
                    $('.select_controller_'+i).trigger("change");
                }
            });

            $('#downace_upd').click(function () {

                AccertatoreNumber++;

                $(document).on('change','.select_controller_'+AccertatoreNumber,function (e) {
                    var cont_id = $(this).attr('order');
                    var id = $(this).val();
                    var article = $('#id1_1').val();
                    var FineDate = $('#FineDate').val();
                    check_accertatore(id,article,FineDate,cont_id)
                });
                $("#Controllers").append(ControllerTemplate (AccertatoreNumber))
                $("#upace_upd").show();

                if(AccertatoreNumber==5){
                    $("#downace_upd").hide();
                }else{
                    $("#downace_upd").show();
                }

                $('#AccertatoreNumber').val(AccertatoreNumber);

                $(document).on('change','.select_controller_'+AccertatoreNumber,function () {
                    $('#update').val("Salva");
                    $('#update').show();
                    $('#back').val("Anulla")
                });

            });

            function check_accertatore(id,article,FineDate,acce_number){
                $.ajax({
                    url: 'ajax/ajx_selected_controller.php',
                    type: 'POST',
                    data: {id: id, article: article, FineDate: FineDate,acce_number:acce_number,"update":"update"},
                    success: function (data) {
                        if(data=="NOTOK"){
                            $("#update").prop("disabled", true);
                            $("#span_acce_"+acce_number).addClass("help-block");
                            $("#span_acce_"+acce_number).html('IL controller e Ausilario!');
                            controller = false;
                            chkCode = false;
                        }
                        if(data=="OK"){
                            $("#update").prop("disabled", false);
                            $("#span_acce_"+acce_number).removeClass("help-block");
                            $("#span_acce_"+acce_number).html('');
                            controller = true;
                            chkCode = true;
                        }
                        if (data == "NO") {
                            giaPresente = true;
                            $("#save").prop("disabled", true);
                            $("#span_acce_"+acce_number).addClass("help-block");
                            $("#span_acce_"+acce_number).html('Il controller non è nell\'incarico!');
                            controller = false;
                            chkCode = false;
                        }
                    }
                });
            }


            $('#upace_upd').click(function () {

                $("#BoxAccertatore_"+AccertatoreNumber).remove();
                AccertatoreNumber--;

                if(AccertatoreNumber==1){
                    $("#upace_upd").hide();
                }else{
                    $("#downace_upd").show();
                }
                $('#AccertatoreNumber').val(AccertatoreNumber);
                $('#update').val("Salva");
                $('#update').show();
                $('#back').val("Anulla");
            });
        });
    </script>
<?php
include(INC . "/footer.php");
