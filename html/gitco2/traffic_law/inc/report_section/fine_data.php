<?php

//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                     ADDITIONAL CONTROLLER
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
$str_AdditionalController = "";
$rs_AdditionalController = $rs->SelectQuery("
  SELECT C.Name, C.Code 
  FROM Controller C JOIN FineAdditionalController FAC  
  ON C.Id = FAC.ControllerId 
  
  WHERE FAC.FineId=" .$Id
);

while ($r_AdditionalController = mysqli_fetch_array($rs_AdditionalController)){
    $str_AdditionalController.= '
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel">
            Accertatore 
        </div>						
        <div class="col-sm-10 BoxRowCaption">
            '. $r_AdditionalController['Code'].' - '.StringOutDB($r_AdditionalController['Name']) .'
        </div>
   ';
}





$strAcertatore= '';
$str_CssDisplayA = '';
$str_Buttons = '
    <i class="fa fa-caret-up" style="position:absolute; top:3px;right:3px;font-size: 2rem; display:none" id="upace_upd"></i>
    <i class="fa fa-caret-down" style="position:absolute; bottom:3px;right:3px;font-size: 2rem;" id="downace_upd"></i>
';
$cityid = $_SESSION['cityid'];


$all_controllers = $rs->SelectQuery("SELECT Id,Code, CONCAT(Code,' ',Name) AS Name FROM Controller WHERE CityId ='".$cityid."' and disabled = 0 order by name");

$first_controller = $rs->SelectQuery("select Controller.*,CONCAT(Controller.Code,' ',Controller.Name) AS Name,Fine.Id,Fine.ControllerId from Controller left join Fine on Controller.Id = Fine.ControllerId where Fine.Id ='".$Id."'");
$first_controller_array= array();
while($row=mysqli_fetch_array($first_controller)){

    $first_controller_array=array( 'Id'=>$row['ControllerId'],'Name'=>$row['Name'],'Code'=>$row['Code']);
}
$results_many = $rs->SelectQuery("select FineAdditionalController.*,Controller.Id,Controller.Code,CONCAT(Controller.Code,' ',Controller.Name) AS Name FROM FineAdditionalController LEFT JOIN Controller ON FineAdditionalController.ControllerId = Controller.Id WHERE FineAdditionalController.FineId = '.$Id.'");
$selected_controllers = array();
while($row_many=mysqli_fetch_array($results_many)){
    array_push($selected_controllers, array( 'Id'=>$row_many['Id'],'Name'=>$row_many['Name'],'Code'=>$row_many['Code']));
}
array_unshift($selected_controllers,$first_controller_array);

$nr = count($selected_controllers);
$j = 0;
$strAcertatore .= '<div id="Controllers">';

foreach ($selected_controllers as $row_select){
    $j++;
    $id = $row_select['Id'];
    $value = $row_select['Name'];
    $code = $row_select['Code'];

    $strAcertatore .= '<div class id="BoxAccertatore_' . $j . '"">	
                        <div class="col-sm-4 BoxRowLabel" style="height:4rem;background-color: rgb(40, 114, 150);">
        				Accertatore
        				<input type="hidden" id="nr" value="'.$nr.'">
					</div>
        			<div class="col-sm-2 BoxRowCaption" style="height:4rem;background-color: rgb(40, 114, 150);">
        				<input class="form-control frm_field_numeric input_accertatore_number" data-number="'.$j.'"  type="text" name="ControllerCode" id="ControllerCode" style="width:5rem">
					</div>				
					<div class="col-sm-6 BoxRowCaption datas_'.$j.'" style="height:4rem;background-color: rgb(40, 114, 150);">
					    <span id="span_acce_'.$j.'"></span>
                        <select class="form-control frm_field_required select_controller_'.$j.'" name="ControllerId[]" id="ControllerId[]" order="'.$j.'">';
    foreach ($all_controllers as $row_selected ){

        $id_all = $row_selected['Id'];
        $value_all = $row_selected['Name'];
        $code_all = $row_selected['Code'];
        if ($value_all != $value) {
            $strAcertatore .= "<option  value=\"$id_all\">$value_all</option>";
        }
    }
    $strAcertatore .='<option  value="'.$id.'" selected>'.$value.'</option>';




    $strAcertatore .= '</select>
                    </div>
					<div style="height:3rem;">
                        <div class="col-sm-12 BoxRowLabel" style="height:4rem;background-color: rgb(40, 114, 150);">
                            <span id="span_Article_' . $j . '" style="font-size:1.1rem;">&nbsp;</span>
                            ' . $str_Buttons . '
                            
                        </div>
                    </div></div> ';




    $str_CssDisplayA = 'style="display: none;"';
    $str_Buttons = '';




}

$strAcertatore .= '</div>';
?>
<script>
    $('document').ready(function () {
        $(document).on('keyup','.input_accertatore_number',function (e) {
            const code = $(this).val();
            const index = $(this).data('number');
            var isFound = false;
            if(!code){
                $('.select_controller_' +index).val('');
                return;
            }
            $('.select_controller_' +index+' option').each(function(){
                $('.select_controller_'+index).trigger("change");
                if ($(this).html().startsWith(code)) {
                    $('.select_controller_' +index).val($(this).val());
                    isFound = true;
                }

            })
            if(!isFound){
                $('.select_controller_' +index).val('');
                return;
            }

        })
    });

</script>




<?php
$rs_FineOwner = $rs->Select('FineOwner',"FineId=".$Id);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);
if(strlen(trim($r_FineOwner['ArticleDescription'.LAN]))>0) $str_ArticleDescription = $r_FineOwner['ArticleDescription'.LAN];
if(strlen(trim($r_FineOwner['ReasonDescription'.LAN]))>0) $str_ReasonDescription = $r_FineOwner['ReasonDescription'.LAN];


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




$rs_ArticleTariff = $rs->SelectQuery("
        SELECT  
			FA.Fee,
			FA.MaxFee,
			
			ArT.ReducedPayment,
			ArT.LicensePoint,
			
			FH.NotificationTypeId,
			FH.NotificationFee,
			FH.ResearchFee,
			FH.DeliveryDate,
			FH.ResultId
        FROM Fine F 
        JOIN FineArticle FA ON F.Id = FA.FineId 
        JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId AND ArT.Year = F.ProtocolYear 
        LEFT JOIN FineHistory FH ON FA.FineId = FH.FineId
        
        WHERE FA.FineId=".$Id
);
$r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);


$AdditionalFee          = $r_ArticleTariff['NotificationFee'] +	$r_ArticleTariff['ResearchFee'];
$Fee                    = $r_ArticleTariff['Fee'];
$MaxFee                 = $r_ArticleTariff['MaxFee'];

$TotalFee               = $r_ArticleTariff['Fee'] + $AdditionalFee;
$ReducedFee             = ($r_ArticleTariff['ReducedPayment']) ? ($r_ArticleTariff['Fee']*FINE_PARTIAL)+$AdditionalFee : $TotalFee;
$TotalMaxFee            = $r_ArticleTariff['MaxFee']/2 + $AdditionalFee;

$str_ArticleDescription = $r_Fine['ArticleDescription' . LAN];

$str_ReasonDescription  = $r_Fine['ReasonTitle' . LAN];

$LicensePoint           = $r_ArticleTariff['LicensePoint'];


//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                     ADDITIONAL ARTICLE
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
$str_AdditionalArticle = '';
$AdditionalFee = 0;
if($r_Fine['ArticleNumber']>1){

    $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle',"FineId=".$Id, "ArticleOrder");
    while($r_AdditionalArticle=mysqli_fetch_array($rs_AdditionalArticle)){
        $TotalFee += $r_AdditionalArticle['Fee'];
        $ReducedFee += ($r_AdditionalArticle['ReducedPayment']) ? ($r_AdditionalArticle['Fee']*FINE_PARTIAL) : $r_AdditionalArticle['Fee'];
        $TotalMaxFee += $r_AdditionalArticle['MaxFee']/2;

        $LicensePoint += $r_AdditionalArticle['LicensePoint'];

        $str_ExpirationDate = ($r_AdditionalArticle['ExpirationDate'] != "") ? " (".DateOutDB($r_AdditionalArticle['ExpirationDate']).")" : "";
        $str_AdditionalArticleDescription = (strlen(trim($r_AdditionalArticle['AdditionalArticleDescription'.LAN]))>0)  ? $r_AdditionalArticle['AdditionalArticleDescription'.LAN] : $r_AdditionalArticle['ArticleDescription' . LAN].$str_ExpirationDate;

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






if($r_Fine['ControllerDate']!=""){
    $str_ControllerDate =
        '

        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-3 BoxRowLabel">
            Data e ora accertamento
        </div>						
        <div class="col-sm-3 BoxRowCaption">
            '.DateOutDB($r_Fine['ControllerDate']).' '. $r_Fine['ControllerTime'] .'
        </div> 
        <div class="col-sm-6 BoxRowCaption"></div>
        
        
        ';
}else{
    $str_ControllerDate =
        '';
}

if($r_Fine['DetectorId']==0) $DetectorTitle = "";
else {
    $detectors = $rs->Select('Detector',"Id=".$r_Fine['DetectorId']);
    $detector = mysqli_fetch_array($detectors);

    $DetectorTitle = $detector['Title'.LAN];

}


if($r_Fine['CountryId']=="Z110"){
    $str_Department = $r_Fine['DepartmentId'];
} else {
    $str_Department ='';
}

$str_Speed = '';

$str_TimeTLight = '';


if ($r_Fine['Speed'] > 0) {
    $str_Speed = '
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                        VELOCITA
                    </div>
                </div> 
                <div class="col-sm-12" id="DIV_Speed" >
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
    $str_TimeTLight = '
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
                         ' . $r_Fine['TimeTLightFirst'] . '    				
                    </div>
                    <div class="col-sm-4 BoxRowLabel">
                        Secondo fotogramma
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        ' . $r_Fine['TimeTLightSecond'] . '
                    </div>
                </div> 
                <div class="clean_row HSpace4"></div>
                ';
}





$AdditionalDescriptionIta = $rs->Select('FineOwner', 'FineId='.$Id);
$AdditionalDescriptionIta = mysqli_fetch_array($AdditionalDescriptionIta);
$AdditionalDescriptionIta = trim($AdditionalDescriptionIta['AdditionalDescriptionIta']);

if($AdditionalDescriptionIta == ''){
    $AdditionalSanction = $rs->SelectQuery("SELECT AdditionalSanction.TitleIta FROM ArticleTariff JOIN AdditionalSanction on ArticleTariff.AdditionalSanctionId = AdditionalSanction.Id 
    WHERE ArticleTariff.ArticleId='".$r_Fine['ArticleId']."' AND ArticleTariff.Year='".$_SESSION['year']."'");
    $AdditionalSanction = mysqli_fetch_array($AdditionalSanction);
    $AdditionalDescriptionIta = $AdditionalSanction['TitleIta'];
}



$str_Fine_Data = '
    <div class="tab-pane active" id="Fine">
        <div class="col-sm-12">
            <div class="col-sm-1 BoxRowLabel">
                Cronologico
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $r_Fine['ProtocolId'] . '
            </div>            
            <div class="col-sm-1 BoxRowLabel">
                Riferimento
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $r_Fine['Code'] . '
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Data
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . DateOutDB($r_Fine['FineDate']) . '
            </div>
    
            <div class="col-sm-1 BoxRowLabel">
                Ora
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . TimeOutDB($r_Fine['FineTime']) . '
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-1 BoxRowLabel">
                Comune
            </div>
            <div class="col-sm-3 BoxRowCaption">
            ' . $r_Fine['CityTitle'] . '
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Strada
            </div>
            <div class="col-sm-1 BoxRowCaption">
                '. $r_Fine['StreetTypeTitle'].'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Località
            </div>
            <div class="col-sm-5 BoxRowCaption">
                ' . utf8_encode($r_Fine['Address']) . '
            </div>
        </div>  
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Tipo veicolo
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. $r_Fine['VehicleTitle'.LAN] .'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. $r_Fine['VehicleCountry'] .'
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Dip.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                ' . $str_Department . '
            </div>
        </div>   
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-3 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-3 BoxRowCaption">
                ' . StringOutDB($r_Fine['VehiclePlate']) .'
            </div>			
            <div class="col-sm-3 BoxRowLabel">
                Massa
            </div>
            <div class="col-sm-3 BoxRowCaption">
                ' . $r_Fine['VehicleMass'] .' 	
            </div>
        </div>  				
        
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Colore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $r_Fine['VehicleColor'] . '	
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Marca
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $r_Fine['VehicleBrand'] . '	
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Modello
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $r_Fine['VehicleModel'] . '	
            </div>
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
                '. $a_TimeTypeId[$r_Fine['TimeTypeId']] .'
            </div>					
        </div>
        <div class="clean_row HSpace4"></div>				
         
        ' . $str_Speed . '
    
        ' . $str_TimeTLight . '
        <div class="col-sm-12" >
            <div class="col-sm-2 BoxRowLabel">
                Articolo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $r_Fine['Article'] . '    				
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Comma
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $r_Fine['Paragraph'] . '
            </div>
    
            <div class="col-sm-2 BoxRowLabel">
                Lettera
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $r_Fine['Letter'] . '
            </div>
            

        </div>    	
            
        <div class="col-sm-12" style="height:6rem;">
            <div class="col-sm-12 BoxRowLabel" style="height:6rem;background-color: rgb(40, 114, 150);">
                <span id="span_Article" style="font-size:1.1rem;">' . StringOutDB($str_ArticleDescription ). '</span>
            </div>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Sanzione accessoria
            </div>
            <div class="col-sm-10 BoxRowCaption">
                <span id="span_Article" style="font-size:1.1rem;">' . StringOutDB($AdditionalDescriptionIta ). '</span>
            </div>
        </div>
        <div class="clean_row HSpace4"></div> 
        <div class="col-sm-12">
            <div class="col-sm-4 BoxRowLabel">
                Min/Max edittale
            </div>
            <div class="col-sm-4 BoxRowCaption">
                
                '.NumberDisplay($Fee).' / ' . NumberDisplay($MaxFee) . '
                
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Spese notifica
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.NumberDisplay($AdditionalFee).'
            </div>
        </div>    
       
         '.$str_AdditionalArticle.'	
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-3 BoxRowLabel">
                Importo Ridotto (entro 5 gg)
            </div>
            <div class="col-sm-1 BoxRowCaption">
                ' . NumberDisplay($ReducedFee) . '
            </div>   
            <div class="col-sm-3 BoxRowLabel">
                Importo totale (entro 60 gg)
            </div>
            <div class="col-sm-1 BoxRowCaption">
                ' . NumberDisplay($TotalFee) . '
            </div>
             <div class="col-sm-3 BoxRowLabel">
                Importo totale (oltre 60 gg)
            </div>
            <div class="col-sm-1 BoxRowCaption">
                ' . NumberDisplay($TotalMaxFee) . '
            </div>                                                                             
        </div>
        
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Tipo infrazione
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input type="hidden" name="ViolationTypeId" id="ViolationTypeId">
                <span id="span_ViolationTitle">' . StringOutDB($r_Fine['ViolationTitle']) . '</span>
            </div>
              <div class="col-sm-3 BoxRowLabel">
                Totale Punti
            </div>
            <div class="col-sm-3 BoxRowCaption">
                ' . $LicensePoint . '
            </div>          
            
            
            
            
            <div class="clean_row HSpace4"></div>
            
            
            <div class="col-sm-2 BoxRowLabel">
                Accertatore
            </div>						
            <div class="col-sm-10 BoxRowCaption">
                '.$r_Fine['ControllerCode'].' - '.StringOutDB($r_Fine['ControllerName']).'
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            
            '.$str_AdditionalController.'
            '. $str_ControllerDate .'
            
            '.$strAcertatore.'
    
            <div class="clean_row HSpace4"></div>
    
            <div class="col-sm-3 BoxRowLabel" style="height:6rem;">
                Mancata contestazione
            </div>
            <div class="col-sm-9 BoxRowCaption" style="height:6rem;">			    
                <span id="span_ReasonDescription" style="height:6rem;width:40rem;font-size:1.1rem;">' . StringOutDB($str_ReasonDescription) . '</span>
            </div>
        </div> 
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12" style="height:6.4rem;">
            <div class="col-sm-3 BoxRowLabel" style="height:6.4rem;">
                Note operatore
            </div>
            <div class="col-sm-9 BoxRowCaption" style="height:6.4rem;">
                ' . StringOutDB($r_Fine['Note']) . '
            </div>
        </div>  
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12">
            <div class="col-sm-3 BoxRowLabel">
                Stato pratica
            </div>
            <div class="col-sm-9 BoxRowCaption">
                '. StringOutDB($r_Fine['StatusTitle']).'
            </div>
        </div>
    </div>
';

