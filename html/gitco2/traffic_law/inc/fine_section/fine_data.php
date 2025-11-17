<?php

//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                     ADDITIONAL CONTROLLER
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////

//PER ULTERIORI DATI
//Determina se "Elaborare il verbale art. 180 in caso di omessa trasmissione della documentazione richiesta" è modificabile
$b_180 = false;
//Determina se "Procedura con la decurtazione punti della patente di guida del trasgressore comunicato" è modificabile
$b_LicensePoint = false;
//Determina se "Elaborare il verbale art. 126 Bis in caso di omessa comunicazione dei dati del trasgressore" è modificabile
$b_126bis = false;

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






$rs_ArticleTariff = $rs->SelectQuery("
        SELECT
			FA.Fee,
			FA.MaxFee,
			FA.PrefectureFee,
			FA.PrefectureDate,
    
			ArT.ReducedPayment,
			ArT.LicensePoint,
            		ArT.PresentationDocument,
            		ArT.126Bis,
            		ArT.PrefectureFixed,
    
			FH.NotificationTypeId,
			FH.NotificationFee,
			FH.ResearchFee,
			FH.DeliveryDate,
			FH.ResultId
        FROM Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId AND ArT.Year = F.ProtocolYear
        LEFT JOIN FineHistory FH ON FA.FineId = FH.FineId AND FH.NotificationTypeId=6
    
        WHERE FA.FineId=".$Id
    );
$r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);


$AdditionalFee          = $r_ArticleTariff['NotificationFee'] +	$r_ArticleTariff['ResearchFee'];
$Fee                    = $r_ArticleTariff['Fee'];
$MaxFee                 = $r_ArticleTariff['MaxFee'];
$PrefectureFee          = $r_ArticleTariff['PrefectureFee'];
$PrefectureDate         = $r_ArticleTariff['PrefectureDate'];

$TotalFee               = $r_ArticleTariff['Fee'];
$ReducedFee             = ($r_ArticleTariff['ReducedPayment']) ? ($r_ArticleTariff['Fee']*FINE_PARTIAL) : $TotalFee;
$TotalMaxFee            = $r_ArticleTariff['MaxFee']/2;

$str_ArticleDescription = $r_Fine['ArticleDescription' . LAN];

$str_ReasonDescription  = $r_Fine['ReasonTitle' . LAN];

$LicensePoint           = $r_ArticleTariff['LicensePoint'];

$b_180 = $r_ArticleTariff['PresentationDocument'] >= 1 ? true : false ;
$b_126bis = $r_ArticleTariff['126Bis'] >= 1 ? true : false ;
$PrefectureFixed = $r_ArticleTariff['PrefectureFixed'] >= 1 ? true : false ;


//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                     ADDITIONAL ARTICLE
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
$str_AdditionalArticle = '';
//$AdditionalFee = 0;
if($r_Fine['ArticleNumber']>1){
    
    $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle',"FineId=".$Id, "ArticleOrder");
    while($r_AdditionalArticle=mysqli_fetch_array($rs_AdditionalArticle)){
        $TotalFee += $r_AdditionalArticle['Fee'];
        $ReducedFee += ($r_AdditionalArticle['ReducedPayment']) ? ($r_AdditionalArticle['Fee']*FINE_PARTIAL) : $r_AdditionalArticle['Fee'];
        $TotalMaxFee += $r_AdditionalArticle['MaxFee']/2;
        
        $LicensePoint += $r_AdditionalArticle['LicensePoint'];
        
        if(!$b_180){
            $b_180 = $r_AdditionalArticle['PresentationDocument'] >= 1 ? true : false ;
        }
        if(!$b_126bis){
            $b_126bis = $r_AdditionalArticle['126Bis'] >= 1 ? true : false ;
        }
        
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


$b_LicensePoint = $LicensePoint > 0 ? true : false ;


$rs_FineOwner = $rs->Select('FineOwner',"FineId=".$Id);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);
if(strlen(trim($r_FineOwner['ArticleDescription'.LAN]))>0) $str_ArticleDescription = $r_FineOwner['ArticleDescription'.LAN];
if(strlen(trim($r_FineOwner['ReasonDescription'.LAN]))>0) $str_ReasonDescription = $r_FineOwner['ReasonDescription'.LAN];

$FormDynamicTitle=getFormDynamicTitle($r_Fine['FineTypeId'],$r_Fine['StatusTypeId'],$r_Fine['CityId'],$r_Fine['CountryId'],$r_Fine['TrespasserCountryId'],$r_Fine['ViolationTypeId']);
if(isset($r_Fine['TrespasserId']) && $r_Fine['TrespasserId'] > 0){
    $a_LanF = unserialize(LANGUAGE);
    $rs_LanguageF = $rs->SelectQuery("SELECT LanguageId FROM Trespasser WHERE Id={$r_Fine['TrespasserId']}");
    $LanF = $rs->getArrayLine($rs_LanguageF)['LanguageId'];
    if($LanF > 0)
        $LanF = $a_LanF[$LanF];
        else $LanF =$a_LanF[1];
} else $LanF =$a_LanF[1];


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
    
    $DetectorTitle = $detector['Title'.$LanF].' (Matr. '.$detector['Number'].')';
    
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

//***INIZIO ICONE PROTOCOL***
$queryIcons = "
select
f.Id AS FineId,
fh.NotificationTypeId AS NotificationTypeId
from
(((((((Fine f
left join FineHistory fh on
((f.Id = fh.FineId)))
left join FinePayment fp on
((fp.FineId = f.Id)))
left join FineCommunication fc on
(((fc.FineId = f.Id)
and ((fc.TrespasserTypeId = 1)
or (fc.TrespasserTypeId = 3)))))
left join FineDocumentation fd on
(((fd.FineId = f.Id)
and (fd.DocumentationTypeId = 2))))
left join FineDispute fdi on
((fh.FineId = fdi.FineId)))
left join Dispute d on
((fdi.DisputeId = d.Id)))
left join Office o on
((o.Id = d.OfficeId)))
where
((fh.NotificationTypeId = 6)
or (fh.NotificationTypeId = 30 and f.StatusTypeId in(8,9))
or (fh.NotificationTypeId = 1 and f.StatusTypeId in(13))
or isnull(fh.NotificationTypeId)) and f.Id = ".$Id;

$rs_FineHistoryTrespasser = $rs->selectQuery($queryIcons);
$hasHistory = $r_FineHistoryTrespasser = mysqli_fetch_array($rs_FineHistoryTrespasser);
$notificationTypeId = null;
if ($hasHistory)
{
    if (key_exists('NotificationTypeId', $r_FineHistoryTrespasser))
        $notificationTypeId = $r_FineHistoryTrespasser['NotificationTypeId'];
}

$str_PreviousId = "";
$str_Archive = "";
$str_ProtocolId = "";
$str_Kind = "";

if ($r_Fine['StatusTypeId'] == 35 || $r_Fine['StatusTypeId'] == 34 || $r_Fine['StatusTypeId'] == 37)
{
    $rs_Archive = $rs->SelectQuery("
		SELECT FA.ArchiveDate, FA.Note, R.TitleIta ReasonTitle
		FROM FineArchive FA JOIN Reason R ON FA.ReasonId = R.Id
		WHERE FA.FineId=" . $Id);
    $r_Archive = mysqli_fetch_array($rs_Archive);
}
if ($r_Fine['PreviousId'] > 0)
{
    $rs_Previous = $rs->Select('Fine', "Id=" . $r_Fine['PreviousId']);
    $r_Previous = mysqli_fetch_array($rs_Previous);
    
    if($r_Previous['StatusTypeId'] == 34){
        $str_PreviousId = '
		<a style="color:#48bfff" href="mgmt_fine_viw.php' . $str_GET_Parameter . '&Id=' . $r_Previous['Id'] . '">
		<span class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Verbale collegato Cron ' . $r_Previous['ProtocolId'] . '/' . $r_Previous['ProtocolYear'] . ' ' . $r_Archive['Note'] . '">
		<i class="fa fa-file-text" style="margin-top:0.4rem;font-size:1.3rem;"></i>
		</span>
		</a>';
    }
    else {
        $str_PreviousId = '
		<a style="color:#48bfff" href="mgmt_fine_viw.php' . $str_GET_Parameter . '&Id=' . $r_Previous['Id'] . '">
		<span class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Verbale collegato Cron ' . $r_Previous['ProtocolId'] . '/' . $r_Previous['ProtocolYear'] . '">
		<i class="fa fa-file-text" style="margin-top:0.4rem;font-size:1.3rem;"></i>
		</span>
		</a>';
    }
}

//Controlla se il verbale ha generato 126bis e nel caso gli associa un'icona nella prima colonna
$query126 = "SELECT Id, Code, PreviousId FROM Fine WHERE CityId='" . $_SESSION['cityid'] . "' AND PreviousId = ".$Id." AND Code LIKE '".$Id."BIS/%'";
$rs_query126 = $rs->selectQuery($query126);
$r_query126 = mysqli_fetch_assoc($rs_query126);
if(mysqli_num_rows($rs_query126)>0)
    $str_PreviousId .= '
	<a style="color:#48bfff" href="mgmt_fine_viw.php' . $str_GET_Parameter . '&Id=' . $r_query126['Id'] . '">
	<span class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Ha generato un 126bis con id '.$r_query126['Id'].'">
		<i class="fa fa-envelope" style="margin-top:0.4rem;font-size:1.3rem;"></i>
	</span>
	</a>';
    
    if ($r_Fine['StatusTypeId'] == 35 || $r_Fine['StatusTypeId'] == 37)
    {
        $str_Archive = '<span style="color:#A94442" class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Verbale archiviato in data ' . DateOutDB($r_Archive['ArchiveDate']) . ' ' . $r_Archive['ReasonTitle'] . ' ' . $r_Archive['Note'] . '"><i class="fa fa-info-circle" style="margin-top:0.4rem;font-size:1.3rem;"></i></span>';
    }
    else if ($r_Fine['StatusTypeId'] == 34)
    {
        $str_Archive = '<span style="color:#800080" class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Invito in AG chiuso in data '.DateOutDB($r_Archive['ArchiveDate']).'"><i class="fas fa-mail-bulk" style="margin-top:0.4rem;font-size:1.3rem;"></i></span>';
    }
    else if ($r_Fine['StatusTypeId'] == 36)
    {
        //Cerco il verbale successivo a quello con la rinotifica
        $rs_PreviousFwd = $rs->Select('Fine', " ProtocolYear in (" . $_SESSION['year']. ", ". ($_SESSION['year']+1). ", ". ($_SESSION['year']-1). ") AND CityId='" . $_SESSION['cityid'] . "'" . " AND Note=" . "'Violazione duplicata: ID {$Id}'");	//FIXME Soluzione temporanea, trovare un modo diretto per ricavare il verbale successivo (magari con una tabella in cui salvare la relazione dell'ID duplicato per non toccare quella del previousId che punta al primo della catena, tipo logical-Key di eport mentre qui serve il parent-referred di eport)
        $r_PreviousFwd = mysqli_fetch_array($rs_PreviousFwd);
        $str_PreviousId = '<a style="color:#48bfff" href="mgmt_fine_viw.php' . $str_GET_Parameter . '&Id=' . $r_PreviousFwd['Id'] . '"><span class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Verbale noleggio ristampato con Cron ' . $r_PreviousFwd['ProtocolId'] . '/' . $r_PreviousFwd['ProtocolYear'] . ' (ID: '.$r_PreviousFwd['Id'].')"><i class="fa fa-file-text" style="margin-top:0.4rem;font-size:1.3rem;"></i></span></a>';
    }
    else if ($r_Fine['StatusTypeId'] == 33)
    {
        $rs_PreviousFwd = $rs->Select('Fine', " ProtocolYear in (" . $_SESSION['year']. ", ". ($_SESSION['year']+1). ", ". ($_SESSION['year']-1). ") AND CityId='" . $_SESSION['cityid'] . "'" . " AND Note=" . "'Violazione duplicata: ID {$Id}'");
        $r_PreviousFwd = mysqli_fetch_array($rs_PreviousFwd);
        $str_ProtocolId = '<a style="color:#48bfff" href="mgmt_fine_viw.php' . $str_GET_Parameter . '&Id=' . $r_PreviousFwd['Id'] . '"><span class="tooltip-r" data-toggle="tooltip" data-placement="right" data-container="body" title="Verbale rinotificato (ID: '.$r_PreviousFwd['Id'].')"><i class="fa fa-exchange" style="margin-top:0.4rem;font-size:1.3rem;"></i></span></a>';
    }
    else if ($r_Fine['StatusTypeId'] == 8)
    {
        $str_Kind = '<span class="tooltip-r" data-container="body" data-toggle="tooltip" data-placement="right" title="Avviso bonario' . (! is_null($notificationTypeId) && ($notificationTypeId == 30) && ! is_null($flowDate) ? (' - creato in data ' . DateOutDB($flowDate)) : '') . '"><i class="fas fa-wallet" style="margin-top:0.4rem;font-size:1.3rem;"></i></span>';
    }
    else if ($r_Fine['StatusTypeId'] == 9)
    {
        $str_Kind = '<span class="tooltip-r" data-container="body" data-toggle="tooltip" data-placement="right" title="Avviso bonario' . (! is_null($notificationTypeId) && ($notificationTypeId == 30) && ! is_null($sendDate) ? ' - inviato in data ' . DateOutDB($sendDate) : '') . '"><i class="fas fa-wallet" style="margin-top:0.4rem;font-size:1.3rem;"></i></span>';
    }
    //***FINE ICONE PROTOCOL***
    
    $str_Fine_Data = '
    <div class="tab-pane active" id="Fine">
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel table_caption_I">
                Utente
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $r_Fine['UserId'] . '
            </div>
            <div class="col-sm-2 BoxRowLabel table_caption_I">
                Data reg.
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . DateOutDB($r_Fine['RegDate']) . '
            </div>
            <div class="col-sm-2 BoxRowLabel table_caption_I">
                Ora reg.
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . TimeOutDB($r_Fine['RegTime']) . '
            </div>
                    
            <div class="clean_row HSpace4"></div>
                    
            <div class="col-sm-1 BoxRowLabel">
                Cronologico
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . $r_Fine['ProtocolId'] . " " . $str_PreviousId . $str_Archive . $str_ProtocolId . $str_Kind . '
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
			<div class="col-sm-1 BoxRowLabel">
				P
			</div>
			<div class="col-sm-1 BoxRowCaption">
				<span class="'.($r_Fine['TemporaryPlate'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon-remove text-danger").'" style="line-height:1.6rem;"></span>
			</div>
            <div class="col-sm-1 BoxRowLabel">
                Massa
            </div>
            <div class="col-sm-1 BoxRowCaption">
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
            <div class="col-sm-6 BoxRowCaption font_small">
                ' . $DetectorTitle . '
            </div>
        	<div class="col-sm-2 BoxRowLabel">
                Ora
                <i data-toggle="tooltip" data-placement="top" data-container="body" title="L\'ora solare vige dalla fine di Ottobre alla fine di Marzo. L\'ora legale vige dalla fine di Marzo alla fine di Ottobre ed è uguale all\'ora solare +1 ora." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
       		</div>
            <div class="col-sm-2 BoxRowCaption">
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
            <div class="col-sm-2 BoxRowLabel font_small">
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
        <div class="clean_row HSpace4 prefecture"></div>
        <div class="col-sm-12 prefecture">
            <div class="col-sm-4 BoxRowLabel prefecture">
                Importo prefettura
            </div>
            <div class="col-sm-4 BoxRowCaption">
                '.NumberDisplay($PrefectureFee).'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Data prefettura
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.DateOutDB($PrefectureDate).'
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
            <div class="col-sm-3 BoxRowCaption">
                '. StringOutDB($r_Fine['StatusTitle']).'
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Data identificazione dati
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. DateOutDB($r_Fine['ReceiveDate']).'
            </div>
                    
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-3 BoxRowLabel">
                Testo dinamico collegato
            </div>
            <div class="col-sm-9 BoxRowCaption">
                ' . $FormDynamicTitle . '
            </div>
        </div>
    </div>
';