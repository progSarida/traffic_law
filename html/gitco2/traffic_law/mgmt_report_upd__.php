<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
include(INC . "/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$Id = CheckValue('Id', 'n');
$page = CheckValue('page', 'n');

if (isset($_GET['answer'])){
    $answer = $_GET['answer'];
    echo "<div class='alert alert-warning message'>$answer</div>";
    ?>
    <script>
        setTimeout(function(){ $('.message').hide()}, 4000);
    </script>
    <?php
}

$str_Where .= " AND StatusTypeId>10 AND ProtocolId>0 AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'];


if($_SESSION['userlevel']<3){
    $str_WherePage = $str_Where." AND Id=".$Id;
} else {
    $str_WherePage = "Id=".$Id;
}

$strOrder = "Id DESC";



$str_Next = "";
$str_Previous = "";
$str_Table = "";




$rs_Fine = $rs->Select('V_Fine',$str_WherePage);
$r_Fine = mysqli_fetch_array($rs_Fine);


$a_TrespasserType = array("","Proprietario/Trasgressore","Obbligato","Trasgressore");
$a_TrespasserType[10]= "Ditta noleggio/Leasing";
$a_TrespasserType[11]= "Locatario";
$a_TrespasserType[15]= "Patria potestà Proprietario/Obbligato";
$a_TrespasserType[16]= "Patria potestà Trasgressore";

$strTrespasser = "";
$trespasserId = '';
$str_Table = "V_Fine";
$CustomerAdditionalFee = '';
$trespasserSelected = 0;


$trespasser_name = array();
$a_TrespasserTypeId = array();
$a_TrespasserId = array(
        "10"=>0,
        "11"=>0,
        "15"=>0,
        "16"=>0
);

$rs_FineTrespasser = $rs->Select('V_FineTrespasser',"FineId=".$Id, "TrespasserTypeId");
$trespasser_select = '<select class="form-control" id="trespasser_select" name="trespasser_select">';
$tres_num = mysqli_num_rows($rs_FineTrespasser);

while($r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser)){
    $tres_name = $r_FineTrespasser['CompanyName'] . ' ' . $r_FineTrespasser['Name'] . ' ' . $r_FineTrespasser['Surname'];
    $trespasser_select .= '<option value="'.$r_FineTrespasser['TrespasserId'].'">' . $tres_name . '</option>';
    //print_r($r_FineTrespasser['TrespasserTypeId']);
    $CustomerAdditionalFee = $r_FineTrespasser['CustomerAdditionalFee'];
    $a_TrespasserTypeId[] = $r_FineTrespasser['TrespasserTypeId'];


    $trespasser_name[] = $r_FineTrespasser['CompanyName'] . ' ' . $r_FineTrespasser['Name'] . ' ' . $r_FineTrespasser['Surname'];

    if($r_FineTrespasser['TrespasserTypeId']==2 || $r_FineTrespasser['TrespasserTypeId']==1 || $r_FineTrespasser['TrespasserTypeId']==10) $index = 10;
    else if($r_FineTrespasser['TrespasserTypeId']==3 || $r_FineTrespasser['TrespasserTypeId']==11) $index = 11;
    else ($index = $r_FineTrespasser['TrespasserTypeId']);

    $a_TrespasserId[$index] = $r_FineTrespasser['TrespasserId'];

    $TrespasserId = $r_FineTrespasser['TrespasserId'];
    $TrespasserTyepId = $r_FineTrespasser['TrespasserTypeId'];

    $rs_Trespasser = $rs->Select('V_Trespasser',"Id=".$TrespasserId);
    $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
    $strTrespasser .= DivTrespasserView($r_Trespasser, $a_TrespasserType[$TrespasserTyepId],$r_FineTrespasser['FineNotificationDate']);

    $str_FineNotificationDate = ($r_FineTrespasser['FineNotificationDate'] != "") ? DateOutDB($r_FineTrespasser['FineNotificationDate']) : "";




    $strTrespasser .='
    
        <div class="col-sm-12">
            <div class="col-sm-3 BoxRowLabel">
                Data notifica
            </div>
            <div class="col-sm-6 BoxRowCaption">
                <input type="text" class="form-control frm_field_date" name="NotificationDate_'.$TrespasserTyepId.'" id="NotificationDate_'.$TrespasserTyepId.'" style="width:10rem" value="'.$str_FineNotificationDate.'">
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. CreateSelect("ServingType","Disabled=0","Id","NotificationType_".$TrespasserTyepId,"Id","Title",$r_FineTrespasser['FineNotificationType'],true,12) .'
            </div>
        </div>    
    
    ';


}
$trespasser_select .='</select>';

if(in_array('1', $a_TrespasserTypeId)) $trespasserSelected = 1;
if(in_array('2', $a_TrespasserTypeId)) $trespasserSelected = 2;
if(in_array('10', $a_TrespasserTypeId)) $trespasserSelected = 3;
$trespasserId.= "<input type='hidden' name='TrespasserType2' id='TrespasserType2' value='$trespasserSelected'>";



$str_JQPersonalization = '';
$str_BoxArticleOwner = '';
$str_BoxAdditionalArticleOwner = '';

$str_ButtonArticle = '';
$str_Tab = '';



$rs_FineOwner = $rs->Select('FineOwner',"FineId=".$Id);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);
$str_ArticleDescription = (strlen(trim($r_FineOwner['ArticleDescription'.LAN]))>0)  ? $r_FineOwner['ArticleDescription'.LAN] : $r_Fine['ArticleDescription' . LAN];



$contestazione = '
        <div class="col-sm-12" id="div_Reason">
            <div class="col-sm-3 BoxRowLabel">
                Mancata contestazione
            </div>
            <div class="col-sm-8 BoxRowCaption add_text">
                <div class="col-sm-1">
                    <input class="form-control frm_field_numeric" maxlength="2" type="text" name="ReasonCode" id="ReasonCode" style="width:4rem">
                </div>
                <div class="col-sm-11">
                    '. CreateSelect("Reason","CityId='".$_SESSION['cityid']."' OR Id=100","Id","ReasonId","Id","TitleIta",$r_Fine['ReasonId'],false,30, "frm_field_required") .'
                </div>
                <div class="col-sm-11">
                    <input name="ReasonOwner" id="ReasonOwner" type="text" class="form-control frm_field_string frm_field_required" style="display: none">
                </div>
            </div>
        
            <div class="col-sm-1 BoxRowLabel">
                <i class="fa fa-edit" id="Edit_Contestazione" style="position:absolute; top:1px;right:1px;font-size: 2rem;"></i>
            </div>
        </div> 
    ';


echo "<div class='check'></div>";




include './inc/page/VerbaleForm/Accertatore_upd.php';


if($_SESSION['userlevel']>=3){
    $a_Language = unserialize(LANGUAGE);
    $n_Lan = ($r_Fine['CountryId'] == 'Z000') ? 1 : 5;

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





$str_AdditionalArticle = '';
$AdditionalFee = 0;
if($r_Fine['ArticleNumber']>1){
    $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle',"FineId=$Id", "ArticleOrder");
    while($r_AdditionalArticle=mysqli_fetch_array($rs_AdditionalArticle)){
        $AdditionalFee += $r_AdditionalArticle['Fee'];

        $str_AdditionalArticleDescription = (strlen(trim($r_AdditionalArticle['AdditionalArticleDescription'.LAN]))>0)  ? $r_AdditionalArticle['AdditionalArticleDescription'.LAN] : $r_AdditionalArticle['ArticleDescription' . LAN];

        $str_ButtonAdditionalArticle = '';
        if($_SESSION['userlevel']>=3){
            $str_ArticleTitle = '';
            for ($i=1; $i<=$n_Lan; $i++){
                $str_ArticleTitle .= '*'.trim($r_AdditionalArticle['AdditionalArticleDescription'.$a_Language[$i]]);
            }

            $str_ButtonAdditionalArticle = '
                <span class="glyphicon glyphicon-pencil" id="'.$r_AdditionalArticle['ArticleId'].'" alt="'.$str_ArticleTitle.'"  style="position:absolute;bottom:5px;right:5px;"></span>
            ';
        }


        $str_AdditionalArticle .= '
                <div class="clean_row HSpace4"></div> 
	        	<div class="col-sm-12" >
        			<div class="col-sm-2 BoxRowLabel">
        				Articolo
					</div>
					<div class="col-sm-2 BoxRowCaption">
					    ' . $r_AdditionalArticle['Article'] . '    				
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Comma
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . $r_AdditionalArticle['Paragraph'] . '
 					</div>

        			<div class="col-sm-2 BoxRowLabel">
        				Lettera
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . $r_AdditionalArticle['Letter'] . '
 					</div>
  				</div>    					
	        	<div class="col-sm-12" style="height:6rem;">
        			<div class="col-sm-12 BoxRowLabel" style="height:6rem;background-color: rgb(40, 114, 150);">
            			<span id="span_Article" style="font-size:1.1rem;">' . $str_AdditionalArticleDescription . '</span>
            			'.$str_ButtonAdditionalArticle.'
        			</div>
  				</div>   																																								
  				<div class="clean_row HSpace4"></div> 
	        	<div class="col-sm-12">
        			<div class="col-sm-4 BoxRowLabel">
        				Min/Max edittale
					</div>
					<div class="col-sm-8 BoxRowCaption">
        				
        				'.NumberDisplay($r_AdditionalArticle['Fee']).' / ' . NumberDisplay($r_AdditionalArticle['MaxFee']) . '
        				
					</div>
  				</div>
  				';

    }

}

$charge_rows = $rs->Select('CustomerCharge',"CreationType=1 AND  CityId='".$_SESSION['cityid']."' AND ToDate IS NULL", "Id");
$charge_row = mysqli_fetch_array($charge_rows);


$str_Charge = ($r_Fine['CountryId']=='Z000') ? "National" : "Foreign";

if($charge_row[$str_Charge.'TotalFee']>0){
    $TotalCharge = $charge_row[$str_Charge.'TotalFee'];
} else  {
    if($charge_row[$str_Charge.'NotificationFee']>0){
        $TotalCharge = $charge_row[$str_Charge.'NotificationFee'] + $charge_row[$str_Charge.'ResearchFee'];
    }else{
        $TotalCharge = $charge_row[$str_Charge.'ResearchFee'];

    }

}

$TotalFee = $TotalCharge  + $r_Fine['Fee'] + $AdditionalFee;


if ($r_Fine['DetectorId'] == 0) $DetectorTitle = "";
else {
    $detectors = $rs->Select('Detector', "Id=" . $r_Fine['DetectorId']);
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
        if ($r_Fine['Locality'] == $r_Row['Id']) $str_Locality .= ' SELECTED ';

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

$str_Folder = ($r_Fine['CountryId'] == 'Z000') ? 'doc/national/fine' : 'doc/foreign/fine';

$str_tree = "";
$str_Img = "";
$doc_rows = $rs->Select('FineDocumentation', "FineId=" . $Id, "Id");
$doc_n = mysqli_num_rows($doc_rows);

if ($doc_n > 0) {
    $doc_row = mysqli_fetch_array($doc_rows);

    $File = $str_Folder . '/' . $_SESSION['cityid'] . '/' . $r_Fine['Id'] . '/' . $doc_row['Documentation'];
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
            $("#fileTreeDemo_1").fileTree({ root:\'' . $str_Folder . '/' . $_SESSION['cityid'] . '/' . $r_Fine['Id'] . '/\', script: \'jqueryFileTree.php\' }, function(file) {
            
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



$eludi = $rs->SelectQuery("SELECT ChkControl FROM Fine where Id = ".$Id);
$EludiControlli = mysqli_fetch_array($eludi)['ChkControl'];
if ($EludiControlli ==1){
    $checked = 'checked';
}else{
    $checked =null;
}


$str_out .='
    
    <div class="tab-pane fade in active" id="Fine">
        <form name="f_violation" id="f_violation" method="post" action="mgmt_report_upd_exe.php'.$str_GET_Parameter.'">
        <input type="hidden" name="Id" value="' . $Id . '">
        <input type="hidden" name="P" value="' . $str_BackPage . '">
        <input type="hidden" name="page" value="' . $page . '">
        <input type="hidden" name="UpdateTrasgressor" value="false" id="UpdateTrasgressor">
        <input type="hidden" name="Search_Ref" value="' . $Search_Ref . '">
        <input type="hidden" name="Search_Plate" value="' . $Search_Plate . '">
        <input type="hidden" name="Search_Violation" value="' . $Search_Violation . '">
        <input type="hidden" name="Search_Status" value="' . $Search_Status . '">
        <input type="hidden" name="TypePlate" value="' . $s_TypePlate . '">
        <input type="hidden" name="Search_Country" value="' . $Search_Country . '">
        <input type="hidden" name="AccertatoreNumber" id="AccertatoreNumber" value="'.$nr.'">
        <input type="hidden" name="ArticleNumber" id="ArticleNumber" value="'.$r_Fine['ArticleNumber'].'">  
        <input type="hidden" name="RuleTypeId" id="RuleTypeId" value="'.$r_Fine['RuleTypeId'].'"> 
        <input type="hidden" name="DetectorId" id="DetectorId" value="'.$r_Fine['DetectorId'].'">
        <input type="hidden" name="FineTypeId" id="FineTypeId" value="'.$r_Fine['FineTypeId'].'">
        <input type="hidden" name="CustomerAdditionalFee" value="'.$CustomerAdditionalFee.'">

        <input type="hidden" name="TrespasserId10" id="TrespasserId10" value="'. $a_TrespasserId[10] .'">
        <input type="hidden" name="TrespasserId11" id="TrespasserId11" value="'. $a_TrespasserId[11] .'">
        <input type="hidden" name="TrespasserId15" id="TrespasserId15" value="'. $a_TrespasserId[15] .'">
        <input type="hidden" name="TrespasserId16" id="TrespasserId16" value="'. $a_TrespasserId[16] .'">



        <input type="hidden" id="has_patria_potesta1" value="0">
        <input type="hidden" id="has_patria_potesta2" value="0">
        <input type="hidden" id="has_patria_potesta3" value="0">
           
    	<div class="row-fluid">
        	<div class="col-sm-6">
        	 <div class="col-sm-12">
                    <div class="col-sm-1 BoxRowCaption">
                            ' . $str_Previous . '
                        </div>
                    <div class="col-sm-10 BoxRowLabel text-center">
                        Cron: '.$r_Fine['ProtocolId']."/".$_SESSION['year'].'
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
                        <input name="Code" id="Code" type="text" class="form-control frm_field_string frm_field_required" value='.substr($r_Fine['Code'],0,strlen($r_Fine['Code'])-5).' style="width:15rem">
                        <span id="span_code"></span>
                    </div>
                    <div class="col-sm-1 BoxRowLabel" style="border-right:2px solid white;">
                      '."/".$_SESSION['year'].'
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Data
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_date frm_field_required" name="FineDate" id="FineDate" value="' . DateOutDB($r_Fine['FineDate']) . '" style="width:9rem">
                        <span id="span_date"></span>
                    </div>

                    <div class="col-sm-1 BoxRowLabel">
                        Ora
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_time frm_field_required" name="FineTime" id="FineTime" value="' .TimeOutDB($r_Fine['FineTime']) . '" style="width:7rem">
                        <span id="span_time"></span>
                    </div>					
					<div class="col-sm-2 BoxRowLabel" >
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
        				<input type="text" class="find_list form-control frm_field_string frm_field_required" name="FineAddress" id="Address" value="' . StringOutDB($r_Fine['Address']) . '">	
        				<ul id="Address_List" class="ul_SearchList"></ul>
					</div>
  				</div>                

  				<div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Tipo veicolo
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				'. CreateSelect("VehicleType","Disabled=0","Title".LAN,"VehicleTypeId","Id","Title".LAN,$r_Fine['VehicleTypeId'],true,14) .'
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Nazione
					</div>
					<div class="col-sm-3 BoxRowCaption">
					<input type="hidden" id="VehicleCountry" name="VehicleCountry" value="'.$r_Fine['VehicleCountry'].'"/>
                        '. CreateSelect("Country","Id IN (SELECT DISTINCT CountryId From Entity)","Title","CountryId","Id","Title", $r_Fine['CountryId'],false,15,"frm_field_required") .'
					</div>
					<div id="department" ';
if ($r_Fine['CountryId'] != "Z110") $str_out.= 'hidden';
$str_out.='>
                        <div class="col-sm-1 BoxRowLabel">
                            Dip.
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                           ';

if ($r_Fine['CountryId'] == "Z110") {
    $str_out .= CreateSelect("Department", "CountryId='" . $r_Fine['CountryId'] . "'", "Code", "DepartmentId", "Id", "Code", $r_Fine['DepartmentId'], false);
} else {
    $str_out .= '<select name="DepartmentId" id="DepartmentId"></select>';
}
$str_out .= '
					    </div>
					</div>
					<div class="col-sm-2 BoxRowCaption" id="toHide"';
if ($r_Fine['CountryId'] == "Z110") $str_out.= 'hidden';
$str_out.='></div>
  				</div>
  				<div class="clean_row HSpace4"></div>
  	        	<div class="col-sm-12">
        			<div class="col-sm-3 BoxRowLabel">
        				Targa
					</div>
					<div class="col-sm-3 BoxRowCaption">
        				<input type="text" class="form-control frm_field_string frm_field_required" name="VehiclePlate" id="VehiclePlate" style="width:10rem; text-transform:uppercase" value="' . StringOutDB($r_Fine['VehiclePlate']) . '">
					</div>
					<div id="massa" ';
if($r_Fine['VehicleTypeId']==2 || $r_Fine['VehicleTypeId']==9) $str_out.='hidden';
    $str_out.='>
                        <div class="col-sm-3 BoxRowLabel">
                            Massa
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input name="VehicleMass" id="VehicleMass" type="text" class="form-control frm_field_numeric" value="' . $r_Fine['VehicleMass'] . '">
                        </div>
					</div>
					<div id="toHide2" class="col-sm-6 BoxRowCaption" ';
if($r_Fine['VehicleTypeId']!=2 && $r_Fine['VehicleTypeId']!=9) $str_out.='hidden';
    $str_out.='>
  				</div> 
   				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-2 BoxRowLabel">
        				Colore
					</div>
					<div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_string" name="VehicleColor" id="VehicleColor" class="find_list" style="width:8rem" value="' . $r_Fine['VehicleColor'] . '">	
        				<ul id="VehicleColor_List" class="ul_SearchList"></ul>
					</div>
        			<div class="col-sm-2 BoxRowLabel">
        				Marca
					</div>
					<div class="col-sm-2 BoxRowCaption">
        			    <input type="text" class="form-control frm_field_string" name="VehicleBrand" id="VehicleBrand" class="find_list" style="width:8rem" value="' . $r_Fine['VehicleBrand'] . '">	
        				<ul id="VehicleBrand_List" class="ul_SearchList"></ul>
					</div>

        			<div class="col-sm-2 BoxRowLabel">
        				Modello
					</div>
					<div class="col-sm-2 BoxRowCaption">
        			    <input type="text" class="form-control frm_field_string" name="VehicleModel" id="VehicleModel" class="find_list" style="width:8rem" value="' . $r_Fine['VehicleModel'] . '">	
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
        				'. CreateSelect("TimeType","Disabled=0","Id","TimeTypeId","Id","Title", $r_Fine['TimeTypeId'],true) .'
					</div>					
  				</div>
  				<div class="clean_row HSpace4"></div>				
                 ';


if ($r_Fine['Speed'] > 0) {
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
					    ' . round($r_Fine['SpeedLimit']) . '    				
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Rilevata
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . round($r_Fine['SpeedControl']) . '
 					</div>
 					<div class="col-sm-2 BoxRowLabel">
        				Effettiva
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				' . round($r_Fine['Speed']) . '
 					</div>
  				</div> 
  				<div class="clean_row HSpace4"></div>    
                ';
}
if ($r_Fine['TimeTLightFirst'] > 0) {
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
					    <input class="form-control frm_field_numeric" type="text" name="TimeTLightFirst" id="TimeTLightFirst" value="' . $r_Fine['TimeTLightFirst'] . '" style="width:6rem">    				
 					</div>
 					<div class="col-sm-4 BoxRowLabel">
        				Secondo fotogramma
					</div>
					<div class="col-sm-2 BoxRowCaption">
        				<input class="form-control frm_field_numeric" type="text" name="TimeTLightSecond" id="TimeTLightSecond" value="' . $r_Fine['TimeTLightSecond'] . '" style="width:6rem">
 					</div>
                 </div>
                 <div class="clean_row HSpace4"></div>
               ';
}

if($r_Fine['ControllerDate']!=""){
    $str_ControllerDate =
        '
            <div class="col-sm-3 BoxRowLabel">
                Data e ora accertamento
            </div>						
            <div class="col-sm-3 BoxRowCaption">
                '.DateOutDB($r_Fine['ControllerDate']).' '. $r_Fine['ControllerTime'] .'
            </div>            
            ';
}else{
    $str_ControllerDate ="";
}
include './inc/page/VerbaleForm/Articolo_upd_one.php';

    $str_out.='<div class="col-sm-12">
        <div class="col-sm-2 BoxRowLabel">
            Tipo infrazione
        </div>
        <div class="col-sm-10 BoxRowCaption">
            <input type="hidden" name="ViolationTypeId" id="ViolationTypeId">
            <span id="span_ViolationTitle">' . utf8_encode($r_Fine['ViolationTitle']) . '</span>
        </div>
        <div class="clean_row HSpace4"></div>  
        '.$strAcertatore.'
        '. $str_ControllerDate .'
    </div> ';


  				$str_out.='
  				
  				<div class="col-sm-12 BoxRowCaption" style="background-color:rgb(40, 114, 150);" ></div>
  				<div class="clean_row HSpace4"></div>
	        	<div class="col-sm-12" id="div_Reason">
					'.$contestazione.'
  				</div> 
  				<div class="clean_row HSpace4"></div>
  				<div class="col-sm-12" style="height:6.4rem;">
        			<div class="col-sm-3 BoxRowLabel" style="height:6.4rem;">
        				Note operatore
					</div>
					<div class="col-sm-9 BoxRowCaption" style="height:6.4rem;">
                        <textarea name="Note" class="form-control frm_field_string" style="width:40rem;height:5.5rem">' . utf8_encode($r_Fine['Note']) . '</textarea>	
					</div>
  				</div>  
                <div class="clean_row HSpace4"></div>
  				<div class="col-sm-12">
        			<div class="col-sm-3 BoxRowLabel">
        				Tipo pratica
					</div>
					<div class="col-sm-9 BoxRowCaption">
                        ' . CreateSelect("StatusType", "Id=14", "Id", "StatusTypeId", "Id", "Title", $r_Fine['StatusTypeId'], true) . '
					</div>
				    <div class="clean_row HSpace4"></div>
                    <div class="clean_row HSpace4"></div>

					<div class="col-sm-12">
                        <div class="col-sm-3 BoxRowLabel">
                            Trasgressore
                        </div>
                        <div class="col-sm-9 BoxRowCaption">
                            <select class="form-control frm_field_required" name="TrespasserType" id="TrespasserType" style="width:40rem;">
                                <option value="0"></option>
                                <option value="1">Proprietario (Proprietario/Trasgressore)</option>
                                <option value="2">Obbligato / Trasgressore (Proprietario + Trasgressore/conducente)</option>
                                <option value="3">Noleggio(Leasing)/Noleggiante(Obbligato/Trasgressore)</option>
                            </select>
                        </div>
                    </div>
                    <div class="clean_row HSpace4"></div>
                <div id="DIV_TrespasserType" style="display:none">
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel">
                        <span id="trasgressor1">TRASGRESSORE:</span> <span id="span_name_11"></span><span id="error_name_11"></span>                       
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Data notifica
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="text" class="form-control frm_field_date" name="NotificationDate_11" id="NotificationDate_11" style="width:10rem">
                    </div>  
                    <div class="col-sm-2 BoxRowCaption">
                        <select name="NotificationType_11" id="NotificationType_11">
                            <option value="1">Su strada</option>
                            <option value="2">Messo</option>
                            <option value="3">Ufficio</option>
                        </select>
                    </div>
                </div>


                <div class="clean_row HSpace4"></div>       
                <div class="col-sm-12" style="height:20rem;">
                    <ul class="nav nav-tabs" id="mioTab">
                        <li class="active" id="tab_Trespasser11"><a href="#Trespasser11" data-toggle="tab">PERSONA</a></li>
                        <li id="tab_company11"><a href="#company11" data-toggle="tab">DITTA</a></li>
                        
                    </ul>
                    <span class="glyphicon glyphicon-plus-sign add_button_11" style="color:#294A9C;position:absolute; right:10px;top:10px;font-size:25px; "></span>     
                    <div class="tab-content">
                        <div class="tab-pane" id="company11">
                            <div class="row-fluid">
                                <div class="col-sm-12">
                                    <div class="col-sm-2 BoxRowLabel">
                                        <input type="hidden" name="Genre11" id="Genre11" value="D">
                                        Ragione sociale
                                    </div>
                                    <div class="col-sm-10 BoxRowCaption">
                                        <input name="CompanyName11" id="CompanyName11" type="text" style="width:15rem">
                                    </div>
                                   
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane active" id="Trespasser11">
                            <div class="row-fluid">
                                <div class="col-sm-12">
                                    <div class="col-sm-3 BoxRowLabel">
                                        Cognome
                                    </div>
                                    <div class="col-sm-3 BoxRowCaption">
                                        <input type="text" name="Surname11" id="Surname11" style="width:12rem">
                                    </div>
                                    <div class="col-sm-3 BoxRowLabel">
                                        Nome
                                    </div>
                                    <div class="col-sm-3 BoxRowCaption">
                                        <input type="text" name="Name11" id="Name11" style="width:12rem">
                                    </div>
                                    
                                </div>
                            </div>
                        </div> 
                        <div id="trespasser_content_11" class="col-sm-12" style="height:150px;overflow:auto"></div>
                    </div>
                </div>
                </div>
                <div class="clean_row HSpace4"></div>
                <div id="DIV_Tutor_11" style="display:none">
                    <div class="col-sm-12">
                        <div class="col-sm-5 BoxRowLabel">
                            ESERCENTE PATRIA POTESTA: <span id="span_name_16"></span><span id="error_name_16"></span>                       
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data notifica
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input type="text" class="form-control frm_field_date" name="NotificationDate_16" id="NotificationDate_16" style="width:10rem">
                        </div>    
                        <div class="col-sm-2 BoxRowCaption">
                            <select name="NotificationType_16" id="NotificationType_16">
                                <option value="1">Su strada</option>
                                <option value="2">Messo</option>
                                <option value="3">Ufficio</option>
                            </select>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <span class="glyphicon glyphicon-plus-sign add_button_16" style="color:#294A9C;position:absolute; right:10px;top:3px;font-size:17px; "></span> 
                        </div>  
                    </div>
                    <div class="clean_row HSpace4"></div>       
                    <div class="col-sm-12" style="height:20rem;">
                        <div class="row-fluid">
                            <div class="col-sm-12">
                                <div class="col-sm-3 BoxRowLabel">
                                    Cognome
                                </div>
                                <div class="col-sm-3 BoxRowCaption">
                                    <input type="text" name="Surname16" id="Surname16" style="width:12rem">
                                </div>
                                <div class="col-sm-3 BoxRowLabel">
                                    Nome
                                </div>
                                <div class="col-sm-3 BoxRowCaption">
                                    <input type="text" name="Name16" id="Name16" style="width:12rem">
                                </div>
                               
                            </div>
                        </div>
                        <div id="trespasser_content_16" class="col-sm-12" style="height:150px;overflow:auto"></div>
                    </div>                                  
                </div>
  				<div class="clean_row HSpace4"></div> 		
  				<div id="proprietario" style="display: none">		
                    <div>
                        <div class="col-sm-12">
                            <div class="col-sm-6 BoxRowLabel">
                                <span id="trasgressor2">PROPRIETARIO:</span> <span id="span_name_10"></span><span id="error_name_10"></span>        				
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Data notifica
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input type="text" class="form-control frm_field_date" name="NotificationDate_10" id="NotificationDate_10" style="width:10rem">
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <select name="NotificationType_10" id="NotificationType_10">
                                    <option value="1">Su strada</option>
                                    <option value="2">Messo</option>
                                    <option value="3">Ufficio</option>
                                </select>
                            </div>
                        </div>
                    </div>
				    <div class="clean_row HSpace4"></div>   	
                    <div class="col-sm-12" style="height:20rem;">
                        <ul class="nav nav-tabs" id="mioTab">
                            <li class="active" id="tab_Trespasser10"><a href="#Trespasser10" data-toggle="tab">PERSONA</a></li>
                            <li id="tab_company10"><a href="#company10" data-toggle="tab">DITTA</a></li>
                            
                        </ul>
                        <span class="glyphicon glyphicon-plus-sign add_button_10" style="color:#294A9C;position:absolute; right:10px;top:10px;font-size:25px; "></span> 
                        <div class="tab-content">
                            <div class="tab-pane" id="company10">
                                <div class="row-fluid">
                                    <div class="col-sm-12">
                                        <div class="col-sm-2 BoxRowLabel">
                                            <input type="hidden" name="Genre10" id="Genre10" value="D">
                                            Ragione sociale
                                        </div>
                                        <div class="col-sm-10 BoxRowCaption">
                                            <input name="CompanyName10" id="CompanyName10" type="text" style="width:15rem">
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane active" id="Trespasser10">
                                <div class="col-sm-12">
                                        <div class="col-sm-3 BoxRowLabel">
                                            Cognome
                                        </div>
                                        <div class="col-sm-3 BoxRowCaption">
                                            <input type="text" name="Surname10" id="Surname10" style="width:12rem">
                                        </div>
                                        <div class="col-sm-3 BoxRowLabel">
                                            Nome
                                        </div>
                                        <div class="col-sm-3 BoxRowCaption">
                                            <input type="text" name="Name10" id="Name10" style="width:12rem">
                                        </div>
                                        
                                    </div>
                            </div> 
                            <div id="trespasser_content_10" class="col-sm-12" style="height:150px;overflow:auto"></div>
                        </div>
                    </div>
                </div>
  				  				
  				<div class="clean_row HSpace4"></div>
   				<div id="DIV_Tutor_10" style="display:none">
                    <div class="col-sm-12">
                        <div class="col-sm-5 BoxRowLabel">
                            ESERCENTE PATRIA POTESTA: <span id="span_name_15"></span><span id="error_name_15"></span>        				
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data notifica
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input type="text" class="form-control frm_field_date" name="NotificationDate_15" id="NotificationDate_15" style="width:10rem">
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <select name="NotificationType_15" id="NotificationType_15">
                                <option value="1">Su strada</option>
                                <option value="2">Messo</option>
                                <option value="3">Ufficio</option>
                            </select>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <span class="glyphicon glyphicon-plus-sign add_button_15" style="color:#294A9C;position:absolute; right:10px;top:3px;font-size:17px; "></span> 
                        </div>                          
                    </div>
    
    
                    <div class="clean_row HSpace4"></div>   	
                    <div class="col-sm-12" style="height:20rem;">
    
                        <div class="row-fluid">
                            <div class="col-sm-12">
                                <div class="col-sm-3 BoxRowLabel">
                                    Cognome
                                </div>
                                <div class="col-sm-3 BoxRowCaption">
                                    <input type="text" name="Surname15" id="Surname15" style="width:12rem">
                                </div>
                            
                                <div class="col-sm-3 BoxRowLabel">
                                    Nome
                                </div>
                                <div class="col-sm-3 BoxRowCaption">
                                    <input type="text" name="Name15" id="Name15" style="width:12rem">
                                </div>
                                
                            </div>
                        </div>
                                           
                        <div id="trespasser_content_15" class="col-sm-12" style="height:150px;overflow:auto"></div>
                    </div>  				  				
  				</div>
  				</div>
  				<div class="col-sm-12" id="trasgressori_gia_esistenti">'.$strTrespasser.'</div>
  				<div>'.$trespasserId.'</div>
        	</div>
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
  		</div>
  		';
$chiudiVerbale = '<div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-4 BoxRowLabel">
                        Chiudi il verbale
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        <select name="CloseFine" id="CloseFine">';
                           if ($r_Fine['StatusTypeId'] ==32){
                               $chiudiVerbale.='<option value="1" selected>SI</option><option value="0">NO</option>';
                           }else{
                               $chiudiVerbale.='<option value="0">NO</option><option value="1">SI</option>';
                           }
                       $chiudiVerbale.=' </select> 
                    </div>
                </div> 
                 <div class="clean_row HSpace4"></div>
                <div class="col-sm-12">
                    <div class="col-sm-4 BoxRowLabel" style="height:2.8rem;">
        				Note motivazione
					</div>
                    <div class="col-sm-8 BoxRowCaption" style="height:2.8rem;">
                        <textarea name="NoteProcedure" class="form-control frm_field_string" style="width:100rem;height:2.4rem">'.$r_Fine['NoteProcedure'].'</textarea>	
					</div>
                </div>	';

$str_out.=$chiudiVerbale;
$str_out.='
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                   <input type="submit" class="btn btn-default" id="update" value="Modifica"  />
                    <input type="button" class="btn btn-default" id="indietro" value="Indietro"  />
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

$rs_city = $rs->SelectQuery("SELECT Id, Title FROM sarida.City ORDER BY Title ASC");
$r_city = $rs->SelectQuery("SELECT Id, Title FROM sarida.City ORDER BY Title ASC");

$str_out.= '
<div id="Div_Windows_Insert_Trespasser">
    <form name="f_ins_trespasser" id="f_ins_trespasser" class="submit_form">
    
        <input type="hidden" name="Genre" id="Genre" value="M">
        <input type="hidden" name="TrespasserTypeId" id="TrespasserTypeId">
        <input type="hidden" name="BornCity" id="BornCity">
        <input type="hidden" name="Fine_Date_Get" id="Fine_Date_Get">
        <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            <span id="TitleTrespasser"></span>
            <span class="fa fa-times-circle close_window_trespasser" style="color:#fff;position:absolute; right:2px;top:2px;font-size:20px; "></span>
        </div>

        <div class="clean_row HSpace4"></div>
                            
        <div class="col-sm-12">
            <ul class="nav nav-tabs" id="mioTab">
                <li class="active" id="tab_Trespasser_src"><a href="#Trespasser_src" data-toggle="tab">Persona fisica</a></li>
                <li id="tab_Company_src"><a href="#Company_src" data-toggle="tab">Ditta</a></li>
            </ul>
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="tab-content"><!-- open div tab-content -->
             <div class="tab-pane" id="Company_src"><!-- open div tab-pane Company -->
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Ragione sociale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string trespasser_frm_field_required" name="CompanyName" id="CompanyName" type="text" style="width:20rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Forma Giuridica
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                       <select class="form-control" name="LegalFormId" id="LegalFormId">
                                <option></option>';
                                $getLegalform = $rs->SelectQuery("SELECT * FROM LegalForm");
                                $type = null;

                                while($row = mysqli_fetch_array($getLegalform)){
                                    if ($row['Type'] != $type) {
                                        if ($type !== null) {
                                            echo '</optgroup>';
                                        }
                                        $type = $row['Type'];
                                        $str_out.='<optgroup label="' . htmlspecialchars($type) . '">';
                                    }

                                    $str_out.='<option value="'.$row['Id'].'">'.$row['Description'].'</option>';
                                }

    $str_out.='</select>
                    </div>
                </div>
            </div>
             <!-- open div tab-pane Company -->

            <div class="tab-pane active" id="Trespasser_src"><!-- open div tab-pane Trespasser -->
                <div class="col-sm-12">
                    <div class="col-sm-1 BoxRowLabel">
                        Sesso
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="radio" value="M" name="Sex" id="sexM">M &nbsp;
                        <input type="radio" value="F" name="Sex" id="sexF">F
                         <span id="sex_code"></span>
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Cognome
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string trespasser_frm_field_required" type="text" id="Surname" name="Surname">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Nome
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string trespasser_frm_field_required" type="text" id="Name" name="Name">
                        
                    </div>
                </div>
                
                <div class="clean_row HSpace4"></div>

                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle">
                        DATI NASCITA
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Nazione
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        ' . CreateSelect(MAIN_DB . ".Country", "1=1", "Title", "BornCountry", "Id", "Title", "Z000", false, 15) . '
                    </div>
                </div>   

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle"></div>
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                    </div>
                    <div class="col-sm-5 BoxRowCaption">
                        <input id="BornCityInput" class="form-control frm_field_string" type="text" name="BornCityInput" style="display:none; width:12rem">
                        <select id="BornCitySelect" class="form-control" name="BornCitySelect">
                             <option></option>';
                                while($row = mysqli_fetch_array($rs_city)) {
                                    $str_out .= '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
                                }
                                $str_out .='
                        </select>
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Data Nascita
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_date" type="text" name="BornDate" id="BornDate" style="width:12rem">
                    </div>     
                </div>   

                <div class="clean_row HSpace4"></div>

                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle">
                        DATI PATENTE
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Categoria
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="LicenseCategory"  style="width:4rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Numero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="LicenseNumber" style="width:12rem">
                    </div>
                </div>
                
                <div class="clean_row HSpace4"></div>
                
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle"></div>                   
                    <div class="col-sm-2 BoxRowLabel">
                        Data rilascio
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_date" type="text" name="LicenseDate"  style="width:12rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Ente rilascio
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="LicenseOffice" style="width:12rem">
                    </div>
                </div>
            </div><!-- close div tab-pane Trespasser -->
        </div><!-- close div tab-content -->
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle">
                DATI RESIDENZA
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                ' . CreateSelect(MAIN_DB . ".Country", "1=1", "Title", "CountryId", "Id", "Title", "Z000", true, 15) . '
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Città
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input id="CityInput" class="form-control frm_field_string" type="text" name="CityInput" style="display:none; width:20rem">
                <select id="CitySelect" class="form-control" name="CitySelect">
                     <option></option>';
                        while($row = mysqli_fetch_array($r_city)) {
                            $str_out .= '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
                        }
                        $str_out .='
                </select>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
                   
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle"></div>                      
            <div class="col-sm-1 BoxRowLabel">
                Indirizzo
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="Address2" name="Address2" style="width:30rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Cap
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="ZIP" id="ZIP" style="width:8rem">
            </div>
            <div id="province_div">
                <div class="col-sm-1 BoxRowLabel">
                    Provincia
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input class="form-control frm_field_string" type="text" name="Province" id="Province" style="width:8rem">
                </div>
            </div>
            
            <div class="clean_row HSpace4"></div>
        </div>
            
        <div class="clean_row HSpace4"></div>
                   
        <div class="col-sm-12">
            <div class="col-sm-1 BoxRowLabel">
                C.F./P.IVA
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="TaxCode" id="TaxCode" style="width:18rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                PEC
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="PEC" id="PEC" style="width:18rem">
            </div>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Mail
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Mail">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Telefono
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Phone">
            </div>
        </div>
      
        <div class="clean_row HSpace4"></div>      

        <div class="col-sm-12">
            <div class="col-sm-12 BoxRowButton">
            <button type="submit" class="btn btn-primary" id="btn_saveanagrafica">
                <i class="fa fa-floppy-o" style="font-size:3.5rem;"></i>
            </button>
            </div>
            <div class="alert alert-warning">
                <center>
                    <p id="erroriva"></p>
                </center>
            </div>
        </div>
    </form>

</div>
</div>
</div>
</div>';


echo $str_out;

echo $str_BoxArticleOwner;

echo $str_BoxAdditionalArticleOwner;
?>
    <script type="text/javascript">

        var rate_href = 'mgmt_report_rate.php<?=$str_GET_Parameter?>&Id='.$Id.'&payment_rate_id=<?=isset($payment_rate['Id']) ? $payment_rate['Id']:''?>';


        $('#indietro').click(function () {
            window.location = "<?= $str_BackPage . $str_GET_Parameter ?>";
            return false;
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

        var controller = true;
        var chkCode = true;
        var giaPresente = false;

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
        var tresRequired = 0;

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
            $("#btn_saveanagrafica").prop("disabled", false);
            $("#sex_code").removeClass("help-block");
            $("#sex_code").html('');
        });
        $('#sexF').click(function(){
            $('#Genre').val('F');
            $("#btn_saveanagrafica").prop("disabled", false);
            $("#sex_code").removeClass("help-block");
            $("#sex_code").html('');
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
            var type = $('#TrespasserType').val();
            var patria_potesta1 = $('#has_patria_potesta1').val();
            var patria_potesta2 = $('#has_patria_potesta2').val();
            var patria_potesta3 = $('#has_patria_potesta3').val();
            var validate = true;

            if(type==1 && patria_potesta1==1){

                var patria_potesta_name = $('#span_name_15').text();
                if (patria_potesta_name=="") {

                    $("#error_name_15").addClass("help-block");
                    $("#error_name_15").html('Richiesto!!');
                    validate=false;
                }else{
                    validate = true;
                }
            }
            if(type==2 && patria_potesta2==1){

                var trass_name = $('#span_name_11').text();
                var patria_potesta_name = $('#span_name_16').text();
                if (trass_name=="" || patria_potesta_name=="") {
                    if (trass_name == "") {
                        $("#error_name_11").addClass("help-block");
                        $("#error_name_11").html('Richiesto!!');
                    }
                    if (patria_potesta_name==""){
                        $("#error_name_16").addClass("help-block");
                        $("#error_name_16").html('Richiesto!!');
                    }
                    validate=false;
                }else{
                    validate = true;
                }
            }
            if(type==2 && patria_potesta3==1){

                var proprietario = $('#span_name_10').text();
                var patria_potesta_name = $('#span_name_15').text();
                if (proprietario=="" || patria_potesta_name=="") {
                    if (proprietario == "") {
                        $("#error_name_10").addClass("help-block");
                        $("#error_name_10").html('Richiesto!!');
                    }
                    if (patria_potesta_name==""){
                        $("#error_name_15").addClass("help-block");
                        $("#error_name_15").html('Richiesto!!');
                    }
                    validate=false;
                }else{
                    validate = true;
                }
            }

            e.preventDefault();
            var today_date = $('#FineDate').val();
            var ora = $('#FineTime').val();
            var targa = $('#VehiclePlate').val();
            var reason = $('#ReasonId').val();
            var Code = $('#Code').val();
            var Controller = $('.select_controller_1').val();
            var Eludi_Controlli  = $('#Conrolli').is(':checked');
            var FineTypeId = $('#FineTypeId').val();
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
            if (validate) {
                if($('#TrespasserId10').val()=='' && $('#TrespasserId11').val()=='' && $('#TrespasserId15').val()=='' && $('#TrespasserId16').val()=='' && $('#UpdateTrasgressor').val('false'));
                else $('#UpdateTrasgressor').val('true');

                $.ajax({
                    url: 'ajax/mgmt_validate_fine_ajax.php',
                    type: 'POST',
                    data: {
                        Code: Code,
                        Controller: Controller,
                        "update": "update",
                        Eludi_Controlli: Eludi_Controlli,
                        FineTypeId: FineTypeId
                    },
                    success:function(data){
                        console.log(data)
                        if(data=="NOB"){
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                            $("#span_code").addClass("help-block");
                            $("#span_code").html('Codice non essiste!!');
                            chkCode = false;
                        }
                        if(data=="NE"){
                            $("html, body").animate({ scrollTop: 200 }, "slow");
                            $("#span_acce_1").addClass("help-block");
                            $("#span_acce_1").html('Controller Errato!');
                            chkCode = false;
                        }

                        if(data == "GE"){
                            $("html, body").animate({ scrollTop: 0 }, "slow");
                            $("#span_code").addClass("help-block");
                            $("#span_code").html('Codice gia esiste!!');
                            chkCode = false;
                        }
                        if(data=="OK"){
                            if(today_date =="" || ora =="" || targa==""){
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
            }
        });
       $('#Conrolli').on('click',function(){
           $('#Code').trigger("change");
           var Eludi_Controlli  = $('#Conrolli').is(':checked');
           if (Eludi_Controlli == false){
               $("#update").prop("disabled", false);
               $("#span_acce").removeClass("help-block");
               $("#span_acce").html('');
           }
       });




    <?= $str_JQPersonalization ?>



    </script>
    <script>
        $(document).ready(function () {
            $('#Code').change(function() {
                var FirstCode = $(this).val();
                var SendCode = $(this).val();
                if (FirstCode.indexOf('/') > 0)
                {
                    FirstCode = FirstCode.replace("/","");
                }
                var validCode = true;
                var FineTypeId = $('#FineTypeId').val();
                if($.isNumeric(FirstCode)==false){
                    if (FirstCode.length >2){
                        var str1 = FirstCode.substr(0,2);
                        var str2 = FirstCode.substr(2,FirstCode.length);
                        if ($.isNumeric(str2)==false){

                            $("#update").prop("disabled", true);
                            $("#span_code").addClass("help-block");
                            $("#span_code").html('Errato inserimento code!');
                            validCode = false;
                        }
                        if ($.isNumeric(str1[0])){
                            $("#update").prop("disabled", true);
                            $("#span_code").addClass("help-block");
                            $("#span_code").html('Errato inserimento code!');
                            validCode = false;
                        }
                    }else if (FirstCode.length==2){
                        var str1 = FirstCode.substr(0,1);
                        var str2 = FirstCode.substr(1,FirstCode.length);
                        if ($.isNumeric(str1)==true || $.isNumeric(str1)==false && $.isNumeric(str2)==false) {
                            $("#update").prop("disabled", true);
                            $("#span_code").addClass("help-block");
                            $("#span_code").html('Errato inserimento code!');
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
                                giaPresente = true;
                                $("#span_code").addClass("help-block");
                                $("#span_code").html('Già presente!');
                                $("#update").prop("disabled", true);
                                chkCode = false;
                            }
                            if (data.Result == "OK") {
                                giaPresente = false;
                                $("#update").prop("disabled", false);
                                $("#span_code").removeClass("help-block");
                                $("#span_code").html('');
                                chkCode = true;
                            }

                        }
                    });
                }

            });

        })
    </script>
    <script>
        $(document).ready(function () {
            var ArticleNumber = <?=$r_Fine['ArticleNumber']?>;

            $( function() {
                $( "#BoxArticleSearch" ).draggable();
            } );

            $(".close_window_article").click(function () {
                $('#BoxArticleSearch').hide();
            });

            $(document).on('click','.glyphicon-search',function (e) {


                var RuleTypeId = $('#RuleTypeId').val();
                var get_ArticleNumber = $(this).attr('id');
                Article = get_ArticleNumber.split("_");
                ArticleNumber_Edit = Article[1];
                var id1=$('#id1_'+ArticleNumber_Edit).val();
                $("#art_num").val(ArticleNumber_Edit);
                $('#EditArticle_'+ArticleNumber).show();
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


        $(document).on('click','#search_btn',function (e) {
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
            var isFound = false;
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

                    $('#art_'+ArticleNumber).attr('fee', response.Fee);
                    $('#art_'+ArticleNumber).attr('addMass', response.AddMass);
                    $('#art_'+ArticleNumber).attr('maxFee', response.MaxFee);
                    $('#art_'+ArticleNumber).attr('desc', response.Description);
                    $('#AdditionalSanctionSelect_'+ArticleNumber).html(response.AdditionalSanctionSelect);
                    $('#YoungLicensePoint_'+ArticleNumber).html(response.LicensePoint);

                    $('#ArticleId_'+element).val(response.ArticleId);
                    $('#span_Article_'+element).html(response.Description);
                    $('#Fee_'+element).val(response.Fee);
                    $('#span_Fee_'+element).html(response.Fee);
                    $('#MaxFee_'+element).val(response.MaxFee);
                    $('#span_MaxFee_'+element).html(response.MaxFee);
                    $('#ReasonId').html(response.ReasonDescription);


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

                $('#ArticleNumber').val(ArticleNumber);
                $('#update').val("Salva");
                $('#update').show();
                $('#indietro').val("Annulla");
                // var totFee = 0;
                // for(var i=1;i<=ArticleNumber;i++){
                //     totFee = totFee + Number($('#Fee_'+i).val());
                // }
                // totFee = totFee + Number(totCharge);
                // $('#span_TotalFee').html(totFee.toFixed(2));


            });
            $(document).on('click','.add_article',function () {
                $('#update').val("Salva");
                $('#update').show();
                $('#indietro').val("Annulla")
            });
        });
    </script>
<script>
    $('document').ready(function () {

        $("form :input").change(function() {
            var changed = $(this).closest('form').data('changed', true);
        });
        $('form').focusout(function() {
            if($(this).closest('form').data('changed')) {
                $('#update').val("Salva");
                $('#update').show();
                $('#indietro').val("Annulla")
            }else{
                console.log("not changed")
            }
        });

        ////////////////////////////////////////////////////////////////////////////////////
        <?php
        $js_array = json_encode($a_TrespasserTypeId);
        echo "var type = ". $js_array . ";\n";
        $js_array = json_encode($trespasser_name);
        echo "var name = ". $js_array . ";\n";
        ?>


        $('#TrespasserType').on('change', function() {

            if(this.value==0) {
                $('#UpdateTrasgressor').val('false');
                tresRequired = 0;
                $('#UpdateTrasgressor').val('false');
                $('#DIV_TrespasserType').hide();
                $('#proprietario').hide();
                $('#trasgressori_gia_esistenti').show();
            } else if(this.value==2) {
                tresRequired = 1;
                $('#trasgressor2').html('PROPRIETARIO:');
                $('#trasgressor1').html('TRASGRESSORE:');
                $('#DIV_TrespasserType').show();
                $('#proprietario').show();
                $('#trasgressori_gia_esistenti').hide();
                $('#NotificationDate_10').removeClass('frm_field_required');
            } else if(this.value==3) {
                tresRequired = 1;
                $('#NotificationDate_10').addClass('frm_field_required');
                $('#trasgressor2').html('NOLEGGIANTE:');
                $('#trasgressor1').html('NOLEGGIO:');
                $('#proprietario').show();
                $('#DIV_TrespasserType').show();
                $('#trasgressori_gia_esistenti').hide();
            } else {
                tresRequired = 1;
                $('#NotificationDate_10').removeClass('frm_field_required');
                $('#trasgressor2').html('PROPRIETARIO:');
                $('#DIV_TrespasserType').hide();
                $('#proprietario').show();
                $('#trasgressori_gia_esistenti').hide();
            }
            if(this.value==<?=$trespasserSelected?>) {
                for(var i = 0; i<type.length;i++){
                    if(type[i]==1 || type[i]==2 || type[i]==10){
                        $('#span_name_10').html(name[i]);
                    }
                    if(type[i]==3 || type[i]==11){
                        $('#span_name_11').html(name[i]);
                    }
                    if(type[i]==15) {
                        tresRequired ++;
                        $("#DIV_Tutor_10").show();
                        $('#span_name_15').html(name[i]);
                    }
                    if(type[i]==16) {
                        tresRequired ++;
                        $("#DIV_Tutor_11").show();
                        $('#span_name_16').html(name[i]);
                    }
                }
            } else {
                $("#DIV_Tutor_10").hide();
                $("#DIV_Tutor_11").hide();
                $('#span_name_10').html('');
                $('#span_name_11').html('');
                $('#span_name_15').html('');
                $('#span_name_16').html('');
            }

        });


        $(".add_button_10").click(function () {
            if($('#TrespasserType').val()==3){
                $('#TitleTrespasser').html('Inserimento Noleggio');
            } else {
                $('#TitleTrespasser').html('Inserimento Obbligato');
            }
            $('#TrespasserTypeId').val(10);
            $('#overlay').fadeIn('fast');
            var finedate_ = $('#FineDate').val();

            $('#Fine_Date_Get').val(finedate_);
            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });

        $(".add_button_11").click(function () {
            if($('#TrespasserType').val()==3){
                $('#TitleTrespasser').html('Inserimento Noleggiante');
            } else {
                $('#TitleTrespasser').html('Inserimento Trasgressore');
            }
            $('#TrespasserTypeId').val(11);
            $('#overlay').fadeIn('fast');
            var finedate_ = $('#FineDate').val();

            $('#Fine_Date_Get').val(finedate_);
            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });

        $(".add_button_15").click(function () {
            $('#TitleTrespasser').html('Inserimento Trasgressore');
            $('#TrespasserTypeId').val(15);
            $('#overlay').fadeIn('fast');
            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });
        $(".add_button_16").click(function () {
            $('#TitleTrespasser').html('Inserimento Obbligato');
            $('#TrespasserTypeId').val(16);
            $('#overlay').fadeIn('fast');
            $('#Div_Windows_Insert_Trespasser').fadeIn('slow');
        });

        $('#btn_saveanagrafica').on('click',function () {
            var Fine_Date_Get = $('#Fine_Date_Get').val();
            var new_fine = Fine_Date_Get.split("/");
            var newfinedt = new_fine[2]+"/"+new_fine[1]+"/"+new_fine[0];

            var BornDate = $('#BornDate').val();
            var new_born = BornDate.split("/");
            var new_borndt = new_born[2]+"/"+new_born[1]+"/"+new_born[0];

            var TrespasserTypeId = $('#TrespasserTypeId').val();

            $("#error_name_"+$('#TrespasserTypeId').val()).removeClass("help-block");
            $("#error_name_"+$('#TrespasserTypeId').val()).html('');

            var years = new Date(new Date(newfinedt)- new Date(new_borndt)).getFullYear() - 1970;

            if($('#TrespasserType').val()!=3) {
                if (TrespasserTypeId == 10) {
                    if (years < 18) {
                        $("#DIV_Tutor_10").show();
                        $("#has_patria_potesta1").val(1);
                        $("#has_patria_potesta3").val(1);
                    }

                }
                if (TrespasserTypeId == 11) {
                    if (years < 18) {
                        $("#has_patria_potesta2").val(1);
                        $("#DIV_Tutor_11").show();
                    }

                }
            }

        });

        $(".close_window_trespasser").click(function () {
            $('#overlay').fadeOut('fast');
            $('#Div_Windows_Insert_Trespasser').hide();
        });

        $("#overlay").click(function () {
            $(this).fadeOut('fast');
            $('#Div_Windows_Insert_Trespasser').hide();

        });


        $('#tab_Company').click(function () {
            $('#Genre').val('D');
            $("#sexM").prop("checked", true);
            $("#sexF").prop("checked", false);

            $('#TaxCode').val('');

        });

        $('#tab_Trespasser').click(function () {
            $('#Genre').val('M');

            $('#TaxCode').val('');
            $('#CompanyName').val('');


        });

        $('#sexM').click(function () {
            $('#Genre').val('M');
            $("#sexF").prop("checked", false);

        });
        $('#sexF').click(function () {
            $('#Genre').val('F');
            $("#sexM").prop("checked", false);

        });


        $("#BornCountry, #CountryId").change(function () {

            //var str_FieldProvince = ($(this).attr("id")=="BornCountry") ? "BornProvinceTitle" : "ProvinceTitle";
            var str_FieldCityS = ($(this).attr("id")=="BornCountry") ? "BornCitySelect" : "CitySelect";
            var str_FieldCityI = ($(this).attr("id")=="BornCountry") ? "BornCityInput" : "CityInput";

            var Country = $(this).val();


            $("#ZIP").val('');
            $("#ZIP").removeClass('txt-success txt-warning txt-danger');

            if (Country == "Z000") {
                //$("#"+str_FieldProvince).show().children('option:not(:first)').remove();
                $("#"+str_FieldCityS).show().children('option:not(:first)').remove();
                $("#"+str_FieldCityI).hide();
                if(str_FieldCityS=="CitySelect") $("#Province").show();

                $.ajax({
                    url: 'ajax/ajx_src_prov_city.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {Country: Country},
                    success: function (data) {
                        //$("#"+str_FieldProvince).children('option:not(:first)').remove();
                        $.each(data.selectValues, function (key, value) {
                            $("#"+str_FieldCityS)
                                .append($("<option></option>")
                                    .attr({
                                        'value': key,
                                    })
                                    .text(value['Title']));
                        });

                    }
                });
            } else {
                $("#TaxCode").removeClass('txt-success txt-warning txt-danger');
                if(str_FieldCityS=="CitySelect") $("#Province").hide();
                //$("#"+str_FieldProvince).hide().children('option:not(:first)').remove();
                $("#"+str_FieldCityS).hide().children('option:not(:first)').remove();
                $("#"+str_FieldCityI).show();
            }
        });


        $("#BornProvinceTitle, #ProvinceTitle").change(function () {
            var str_FieldCityS = ($(this).attr("id")=="BornProvinceTitle") ? "BornCitySelect" : "CitySelect";

            if(str_FieldCityS=="CitySelect"){
                $('#Province').val($('option:selected', this).attr('short_title'));
                $("#ZIP").val('');
            }else{
                $('#BornProvince').val($('option:selected', this).attr('short_title'));
            }
            var Province = $(this).val();


            $.ajax({
                url: 'ajax/ajx_src_prov_city.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Province: Province},
                success: function (data) {

                    $("#"+str_FieldCityS).children('option:not(:first)').remove();


                    $.each(data.selectValues, function (key, value) {
                        $('#'+str_FieldCityS)
                            .append($("<option></option>")
                                .attr({
                                    'value': key,
                                })
                                .text(value['Title']));
                    });

                }
            });

        });


        $("#CityInput, #BornCitySelect, #CitySelect, #Address2").change(function () {

            var str_FieldNaneId = $(this).attr("id");

            if(str_FieldNaneId=="BornCitySelect") $('#BornCity').val($("#"+str_FieldNaneId+" option:selected" ).text());
            else {
                var CityId="";
                if(str_FieldNaneId=="CitySelect" || str_FieldNaneId=="Address2"){
                    $('#City').val($("#"+str_FieldNaneId+" option:selected" ).text());
                    CityId =  $('#CitySelect').val();
                } else if (str_FieldNaneId=="CityInput" || str_FieldNaneId=="Address2") {
                    CityId =  $('#CityInput').val();
                }

                var Address2 = $('#Address2').val();
                var CountryId = $('#CountryId').val();

                if(CityId!=""){

                    $.ajax({
                        url: 'ajax/ajx_src_zip.php',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {CountryId:CountryId, CityId: CityId, Address:Address2},
                        success: function (data) {
                            $("#ZIP").removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                            $("#ZIP").val(data.ZIP);
                            $("#Province").removeClass('txt-success txt-warning txt-danger').addClass(data.CSS);
                            $("#Province").val(data.province);
                        }
                    });

                } else $("#ZIP").val('');
            }




        });

        function checkPiva(taxcode){
            if(taxcode==''){
                $('#TaxCode').removeClass('txt-success txt-danger txt-warning').addClass('txt-warning');
                $('#btn_saveanagrafica').prop('disabled',false);
                $("#erroriva").text(' ');
            } else {
                $.ajax({
                    url: 'ajax/checkpiva.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {TaxCode:taxcode},
                    success: function (data) {
                        if (data == 'Exists') {
                            $("#erroriva").text('Il C.F./P.IVA esiste gia nella database');
                            $('#TaxCode').removeClass('txt-success txt-danger txt-warning').addClass('txt-danger');
                            $('#btn_saveanagrafica').prop('disabled',true);
                        } else {
                            $("#erroriva").text(' ');
                            $('#TaxCode').removeClass('txt-success txt-danger txt-warning').addClass('txt-success');
                            $('#btn_saveanagrafica').prop('disabled',false);
                        }
                    }
                });
            }
        }

        $("#TaxCode").change(function(){
            var taxcode = $("#TaxCode").val();
            checkPiva(taxcode);
        });

        $("#BornCountry, #BornProvinceTitle, #Surname, #Name, #sexM, #sexF, #BornDate, #BornCitySelect, #CountryId").change(function(){

            if ($('#CountryId').val() == 'Z000') {
                var Surname = $('#Surname').val();
                var Name = $('#Name').val();
                var Sex = $('#sexM').prop('checked') ? 'M' : 'F';

                var BornDate = $('#BornDate').val();
                var BornCitySelect = ($('#BornCountry').val() == 'Z000') ? $('#BornCitySelect').val() : $('#BornCountry').val();

                if (Surname && Name && Sex && BornDate && BornCitySelect) {
                    var TaxCode = compute_CF(Surname, Name, Sex, BornDate, BornCitySelect);

                    if (TaxCode.length == 16){
                        $('#TaxCode').val(TaxCode);
                        checkPiva(TaxCode);
                    } else {
                        $('#TaxCode').removeClass('txt-success txt-danger').addClass('txt-danger');
                    }

                } else {
                    $('#TaxCode').val("")
                }

            } else {
                $('#TaxCode').val("")
            }
        });


        $('#f_ins_trespasser').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        $('#Search_Province').change(function () {
            var shorttitle = $(this).val();
            $.ajax({
                url: 'ajax/ajx_src_prov_city.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {province_shorttitle: shorttitle},
                success: function (data) {

                    $("#Search_City").children('option:not(:first)').remove();

                    $.each(data.selectValues, function (key, value) {
                        $('#Search_City')
                            .append($("<option></option>")
                                .attr({
                                    'value': key,
                                })
                                .text(value['Title']));
                    });

                }
            });
        });

        $('#f_ins_trespasser').bootstrapValidator({
            live: 'disabled',
            fields: {
                frm_field_required: {
                    selector: '.trespasser_frm_field_required',
                    validators: {
                        notEmpty: {
                            message: 'Richiesto'
                        }
                    }
                },
            }
        }).on('success.form.bv', function(event) {

        event.preventDefault();
        var form = $("#f_ins_trespasser").serialize();
        var TaxCode = $('#TaxCode').val();
        var surname = $('#Surname').val();
        var name = $('#Name').val();

        var genre = $('#Genre').val();
        var validateform = true;
        if (genre != 'D') {
            if (surname == '' || name == '') {
                validateform = false;
            }
        } else {
            var companyname = $('#CompanyName').val();
            if (companyname == '') {
                validateform = false;
            }
        }
            if (genre!='D' && !$("input[name='Sex']").is(':checked')) {
                $("#btn_saveanagrafica").prop("disabled", true);
                $("#sex_code").addClass("help-block");
                $("#sex_code").html('Richiesto!');
                validateform = false;
            } else {
                validateform = true;
            }
        if (validateform == true) {
            $.ajax({
                url: 'ajax/checkpiva.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {TaxCode: TaxCode},
                success: function (data) {
                    if (data == 'Exists' && TaxCode != "") {
                        $('#erroriva').html('Il trassgressore esiste nella database.');
                    } else {
                        $('#erroriva').html(' ');
                        $.ajax({
                            url: 'ajax/ajx_add_trespasser_exe.php',
                            type: 'POST',
                            dataType: 'json',
                            cache: false,
                            data: form,
                            success: function (data) {
                                $("#span_name_" + $('#TrespasserTypeId').val()).html(data.TrespasserName);
                                $("#TrespasserId" + $('#TrespasserTypeId').val()).val(data.TrespasserId);
                                $(".add_button_" + $('#TrespasserTypeId').val()).hide();
                                $("#f_ins_trespasser").trigger("reset");
                                $("#Div_Windows_Insert_Trespasser").hide();
                                $('#overlay').fadeOut('fast');
                            },
                            error: function (result) {
                                alert("error");
                            }
                        });
                    }
                }
            });
        }
    });

        $( function() {
            $( "#Div_Windows_Insert_Trespasser" ).draggable();
            $( "#BoxArticleSearch" ).draggable();
        } );

        $('#tab_company10').click(function () {
            var NumberTab = 10;
            $('#Genre'+NumberTab).val('D');
            $('#Surname'+NumberTab).val('');
            $('#Name'+NumberTab).val('');
        });
        $('#tab_Company_src').click(function () {
            var NumberTab = 10;
            $('#Genre').val('D');
            $('#Surname'+NumberTab).val('');
            $('#Name'+NumberTab).val('');
        });

        $('#tab_Trespasser10').click(function () {
            var NumberTab = 10;
            $('#Genre'+NumberTab).val('M');
            $('#CompanyName'+NumberTab).val('');
        });
        $('#tab_Trespasser_src').click(function () {
            var NumberTab = 10;
            $('#Genre').val('M');
            $('#CompanyName'+NumberTab).val('');
            $("#sex_code").html("");
        });



        $('#tab_company11').click(function () {
            var NumberTab = 11;

            $('#Genre'+NumberTab).val('D');
            $('#Surname'+NumberTab).val('');
            $('#Name'+NumberTab).val('');

        });

        $('#tab_Trespasser11').click(function () {
            var NumberTab = 11;
            $('#Genre'+NumberTab).val('M');
            $('#CompanyName'+NumberTab).val('');
        });

        $('#CompanyName10').keyup({ NumberTab:10 },chkTrespasser);
        $('#Name10').keyup({ NumberTab:10 },chkTrespasser);
        $('#Surname10').keyup({ NumberTab:10 },chkTrespasser);

        $('#CompanyName11').keyup({ NumberTab:11 },chkTrespasser);
        $('#Name11').keyup({ NumberTab:11 },chkTrespasser);
        $('#Surname11').keyup({ NumberTab:11 },chkTrespasser);


        $('#Name15').keyup({ NumberTab:15 },chkTrespasser);
        $('#Surname15').keyup({ NumberTab:15 },chkTrespasser);

        $('#Name16').keyup({ NumberTab:16 },chkTrespasser);
        $('#Surname16').keyup({ NumberTab:16 },chkTrespasser);


        function chkTrespasser(e){
            var NumberTab = e.data.NumberTab;
            var min_length = 3;
            if(NumberTab<14){
                var CompanyName = $('#CompanyName' + NumberTab).val();
                //var Genre = $('#Genre' + NumberTab).val();
            }else{
                var Genre = '';
                var CompanyName ='';
            }

            var Name = $('#Name' + NumberTab).val();
            var Surname = $('#Surname' + NumberTab).val();
            var FineDate = $('#FineDate').val();
            if (Name !="" || Surname!=""){
                var Genre = $('#Genre').val();
            } else {
                var Genre = $('#Genre' + NumberTab).val();
            }

            if (CompanyName.length >= min_length || Surname.length >= min_length ||Name.length >=min_length) {
                $.ajax({
                    url: 'ajax/search_trespasser_rent.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {FineDate:FineDate, CompanyName: CompanyName, Surname: Surname, Name: Name, Genre: Genre, NumberTab: NumberTab},
                    success: function (data) {
                        $('#trespasser_content_' + NumberTab ).show();
                        $('#trespasser_content_' + NumberTab).html(data.Trespasser);

                    }
                });
            } else {
                $('#trespasser_content_' + NumberTab).hide();
            }
        }
    })

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
                    $('#indietro').val("Anulla")
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


/*

invitato con verbale di accertamento al C.d.S. n. {PreviousProtocolId}/{PreviousProtocolYear}/U del {PreviousFineDate} (ritualmente notificato), a fornire le informazioni utili per l'identificazione del trasgressore, effettivo conducente del veicolo al momento dell'accertamento della violazione, senza giustificato e documentato motivo, non ottemperava all'invito nei modi richiesti e nei termini assegnati



*/