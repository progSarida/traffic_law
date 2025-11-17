<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/pagopa.php");
require(INC."/initialization.php");

require(TCPDF . "/tcpdf.php");
require(CLS."/cls_pdf.php");
require(TCPDF . "/fpdi.php");

$PrintDestination = CheckValue('PrintDestination', 'n');
$ArticleId = CheckValue('ArticleSelect', 'n');
$Id= CheckValue('Id','n');
$Filters= CheckValue('Filters','s');

$rs_Fine = $rs->Select('Fine',"Id=$Id");
$r_Fine = mysqli_fetch_array($rs_Fine);

$str_Folder = ($r_Fine['CountryId']=='Z000') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;

$rs->Start_Transaction();

//AGGIORNO LO STATO DEL VECCHIO VERBALE///////////////////////////////////////////////////////////////////////////////////////////
$aFine = array(
    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>34,'settype'=>'int'),
);

$rs->Update('Fine',$aFine, 'Id='.$Id);

//FINEARCHIVE//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$a_FineArchive = array(
    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$Id,'settype'=>'int'),
    array('field'=>'ReasonId','selector'=>'value','type'=>'int','value'=>15,'settype'=>'int'), //ARCHIVIAZIONE D'UFFICIO
    array('field'=>'ArchiveDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
    array('field'=>'Note','selector'=>'value','type'=>'str','value'=>'Creazione verbale da invito in AG'),
    array('field'=>'PreviousStatusTypeId','selector'=>'value','type'=>'int','value'=>$r_Fine['StatusTypeId'],'settype'=>'int'),
    array('field'=>'PreviousNote','selector'=>'value','type'=>'str','value'=>$r_Fine['Note']),
    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
);
$rs->Insert('FineArchive',$a_FineArchive);

//NUOVO VERBALE//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$Note = "Generato da invito AG: ID ".$Id;
$PreviousId = ($r_Fine['PreviousId']==0) ? $Id : $r_Fine['PreviousId'];

//se l'ente prevede la gestione del PagoPa resettiamo gli Iuv nella rinotifica
// in modo da farli elaborare per il preinserimento generato dalla rinotifica
if ($r_Fine['CountryId']=='Z000'){
    $PagoPA1 = $r_Customer['PagoPAPayment'] == 1 ? null : $r_Fine['PagoPA1'];
    $PagoPA2 = $r_Customer['PagoPAPayment'] == 1 ? null : $r_Fine['PagoPA2'];
} else {
    $PagoPA1 = $r_Customer['PagoPAPaymentForeign'] == 1 ? null : $r_Fine['PagoPA1'];
    $PagoPA2 = $r_Customer['PagoPAPaymentForeign'] == 1 ? null : $r_Fine['PagoPA2'];
}

$a_Fine = array(
    array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$r_Fine['Code']),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>1),
    array('field'=>'ProtocolIdAssigned','selector'=>'value','type'=>'int','value'=>$r_Fine['ProtocolId'], 'settype'=>'int'),
    array('field'=>'ProtocolYear','selector'=>'value','type'=>'year','value'=>$r_Fine['ProtocolYear']),
    array('field'=>'FineDate','selector'=>'value','type'=>'date','value'=>$r_Fine['FineDate']),
    array('field'=>'FineTime','selector'=>'value','type'=>'str','value'=>$r_Fine['FineTime']),
    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$r_Fine['ControllerId'], 'settype'=>'int'),
    array('field'=>'ControllerDate','selector'=>'value','type'=>'date','value'=>$r_Fine['ControllerDate']),
    array('field'=>'ControllerTime','selector'=>'value','type'=>'str','value'=>$r_Fine['ControllerTime']),
    array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>$r_Fine['Locality']),
    array('field'=>'StreetTypeId','selector'=>'value','type'=>'int','value'=>$r_Fine['StreetTypeId'], 'settype'=>'int'),
    array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$r_Fine['Address']),
    array('field'=>'DepartmentId','selector'=>'value','type'=>'int','value'=>$r_Fine['DepartmentId'],'settype'=>'int'),
    array('field'=>'VehicleTypeId','selector'=>'value','type'=>'int','value'=>$r_Fine['VehicleTypeId'],'settype'=>'int'),
    array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$r_Fine['VehiclePlate']),
    array('field'=>'VehicleCountry','selector'=>'value','type'=>'str','value'=>$r_Fine['VehicleCountry']),
    array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$r_Fine['CountryId']),
    array('field'=>'VehicleBrand','selector'=>'value','type'=>'str','value'=>$r_Fine['VehicleBrand']),
    array('field'=>'VehicleModel','selector'=>'value','type'=>'str','value'=>$r_Fine['VehicleModel']),
    array('field'=>'VehicleColor','selector'=>'value','type'=>'str','value'=>$r_Fine['VehicleColor']),
    array('field'=>'PreviousId','selector'=>'value','type'=>'int','value'=>$PreviousId,'settype'=>'int'),
    array('field'=>'VehicleMass','selector'=>'value','type'=>'flt','value'=>$r_Fine['VehicleMass'],'settype'=>'flt'),
    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
    array('field'=>'Note','selector'=>'value','type'=>'str','value'=>$Note),
    array('field'=>'IuvCode','selector'=>'value','type'=>'str','value'=>$r_Fine['IuvCode']),
    array('field'=>'GpsLat','selector'=>'value','type'=>'str','value'=>$r_Fine['GpsLat']),
    array('field'=>'GpsLong','selector'=>'value','type'=>'str','value'=>$r_Fine['GpsLong']),
    array('field'=>'PagoPA1','selector'=>'value','type'=>'str','value'=>$PagoPA1),
    array('field'=>'PagoPA2','selector'=>'value','type'=>'str','value'=>$PagoPA2),
    array('field'=>'FineChiefControllerId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$r_Fine['FineChiefControllerId']),
    array('field'=>'UIFineChiefControllerId','selector'=>'value','type'=>'int','settype'=>'int','value'=>$r_Fine['UIFineChiefControllerId'])
);

$FineId = $rs->Insert('Fine',$a_Fine);

//FINEARTICLE//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$rs_FineArticle = $rs->Select('FineArticle',"FineId=$Id");
$r_FineArticle = mysqli_fetch_array($rs_FineArticle);

//Nel caso sia presente l'articolo scelto da tendina e sia diverso dall'articolo del verbale originale, viene sostituito e vengono ricalcolati gli importi
if($ArticleId > 0 && $ArticleId != $r_FineArticle['ArticleId']){
    $rs_Article = $rs->SelectQuery("SELECT * FROM Article A JOIN ArticleTariff AT ON A.Id=AT.ArticleId AND AT.Year={$_SESSION['year']} WHERE A.Id=$ArticleId");
    $r_Article = mysqli_fetch_array($rs_Article);
    
    if($r_Article['AdditionalNight']){
        $aTime = explode(":",$r_Fine['FineTime']);
        if($aTime[0]<FINE_HOUR_START_DAY ||  ($aTime[0]>FINE_HOUR_END_DAY) || ($aTime[0]==FINE_HOUR_END_DAY && $aTime[1]!="00")){
            $r_Article['Fee'] = $r_Article['Fee'] + round($r_Article['Fee']/FINE_NIGHT,2);
            $r_Article['MaxFee'] = $r_Article['MaxFee'] + round($r_Article['MaxFee']/FINE_NIGHT,2);
        }
    }
    
    $Fee = $r_Article['Fee'];
    $MaxFee   = $r_Article['MaxFee'];
    $ViolationTypeId = $r_Article['ViolationTypeId'];
} else {
    $ArticleId = $r_FineArticle['ArticleId'];
    $Fee = $r_FineArticle['Fee'];
    $MaxFee   = $r_FineArticle['MaxFee'];
    $ViolationTypeId = $r_FineArticle['ViolationTypeId'];
}

$DetectorId = $r_FineArticle['DetectorId'];
$SpeedLimit = $r_FineArticle['SpeedLimit'];
$SpeedControl = $r_FineArticle['SpeedControl'];
$SpeedTimeAverage = $r_FineArticle['SpeedTimeAverage'];
$Speed = $r_FineArticle['Speed'];
$ReasonId = $r_FineArticle['ReasonId'];

$TimeTLightFirst = $r_FineArticle['TimeTLightFirst'];
$TimeTLightSecond = $r_FineArticle['TimeTLightSecond'];
$RoadSideDistance = $r_FineArticle['RoadSideDistance'];
$ArticleNumber = $r_FineArticle['ArticleNumber'];
$TrespasserId1_180 = $r_FineArticle['TrespasserId1_180'];
$DayNumber_180 = $r_FineArticle['DayNumber_180'];
$ExpirationDate = $r_FineArticle['ExpirationDate'];

$a_FineArticle = array(
    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
    array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$ArticleId,'settype'=>'int'),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
    array('field'=>'ViolationTypeId','selector'=>'value','type'=>'int','value'=>$ViolationTypeId,'settype'=>'int'),
    array('field'=>'ReasonId','selector'=>'value','type'=>'int','value'=>$ReasonId,'settype'=>'int'),
    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$Fee,'settype'=>'flt'),
    array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$MaxFee,'settype'=>'flt'),
    array('field'=>'DetectorId','selector'=>'value','type'=>'int','value'=>$DetectorId,'settype'=>'int'),
    array('field'=>'SpeedLimit','selector'=>'value','type'=>'flt','value'=>$SpeedLimit,'settype'=>'flt'),
    array('field'=>'SpeedControl','selector'=>'value','type'=>'flt','value'=>$SpeedControl,'settype'=>'flt'),
    array('field'=>'SpeedTimeAverage','selector'=>'value','type'=>'flt','value'=>$SpeedTimeAverage,'settype'=>'flt'),
    array('field'=>'Speed','selector'=>'value','type'=>'flt','value'=>$Speed,'settype'=>'flt'),
    array('field'=>'TimeTLightFirst','selector'=>'value','type'=>'int','value'=>$TimeTLightFirst,'settype'=>'int'),
    array('field'=>'TimeTLightSecond','selector'=>'value','type'=>'int','value'=>$TimeTLightSecond,'settype'=>'int'),
    array('field'=>'RoadSideDistance','selector'=>'value','type'=>'int','value'=>$RoadSideDistance,'settype'=>'int'),
    array('field'=>'ArticleNumber','selector'=>'value','type'=>'int','value'=>$ArticleNumber,'settype'=>'int'),
    array('field'=>'TrespasserId1_180','selector'=>'value','type'=>'int','value'=>$TrespasserId1_180,'settype'=>'int'),
    array('field'=>'DayNumber_180','selector'=>'value','type'=>'int','value'=>$DayNumber_180,'settype'=>'int'),
    array('field'=>'ExpirationDate','selector'=>'value','type'=>'date','value'=>$ExpirationDate,'settype'=>'date'),
);
$rs->Insert('FineArticle',$a_FineArticle);

//FINEOWNER//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$rs_FineOwner = $rs->Select('FineOwner',"FineId=$Id");
if(mysqli_num_rows($rs_FineOwner)>0){
    $rs->SelectQuery("
            INSERT INTO FineOwner (FineId,ArticleDescriptionIta,ArticleDescriptionEng,ArticleDescriptionGer,ArticleDescriptionSpa,
ArticleDescriptionFre,ReasonDescriptionIta,ReasonDescriptionEng,ReasonDescriptionGer,ReasonDescriptionSpa,ReasonDescriptionFre,
AdditionalDescriptionIta,AdditionalDescriptionEng,AdditionalDescriptionGer,AdditionalDescriptionSpa,AdditionalDescriptionFre,
DeclarationDescriptionIta,DeclarationDescriptionEng,DeclarationDescriptionGer,DeclarationDescriptionSpa,DeclarationDescriptionFre,
DamageDescriptionIta,DamageDescriptionEng,DamageDescriptionGer,DamageDescriptionSpa,DamageDescriptionFre,
RemovalDescriptionIta,RemovalDescriptionEng,RemovalDescriptionGer,RemovalDescriptionSpa,RemovalDescriptionFre,
NoteDescriptionIta,NoteDescriptionEng,NoteDescriptionGer,NoteDescriptionSpa,NoteDescriptionFre,
ArticleDescriptionRom,ArticleDescriptionPor,ArticleDescriptionPol,ArticleDescriptionHol,ArticleDescriptionAlb,
ArticleDescriptionDen,ReasonDescriptionRom,ReasonDescriptionPor,ReasonDescriptionPol,ReasonDescriptionHol,ReasonDescriptionAlb,
ReasonDescriptionDen,AdditionalDescriptionRom,AdditionalDescriptionPor,AdditionalDescriptionPol,AdditionalDescriptionHol,
AdditionalDescriptionAlb,AdditionalDescriptionDen,DeclarationDescriptionRom,DeclarationDescriptionPor,DeclarationDescriptionPol,DeclarationDescriptionHol,
DeclarationDescriptionAlb,DeclarationDescriptionDen,DamageDescriptionRom,DamageDescriptionPor,DamageDescriptionPol,
DamageDescriptionHol,DamageDescriptionAlb,DamageDescriptionDen,RemovalDescriptionRom,RemovalDescriptionPor,RemovalDescriptionPol,
RemovalDescriptionHol,RemovalDescriptionAlb,RemovalDescriptionDen,NoteDescriptionRom,NoteDescriptionPor,NoteDescriptionPol,
NoteDescriptionHol,NoteDescriptionAlb,NoteDescriptionDen
    )
    SELECT
    ". $FineId .",
    ArticleDescriptionIta,
    ArticleDescriptionEng,
    ArticleDescriptionGer,
    ArticleDescriptionSpa,
    ArticleDescriptionFre,
    ReasonDescriptionIta,
    ReasonDescriptionEng,
    ReasonDescriptionGer,
    ReasonDescriptionSpa,
    ReasonDescriptionFre,
    AdditionalDescriptionIta,
    AdditionalDescriptionEng,
    AdditionalDescriptionGer,
    AdditionalDescriptionSpa,
    AdditionalDescriptionFre,
    DeclarationDescriptionIta,
    DeclarationDescriptionEng,
    DeclarationDescriptionGer,
    DeclarationDescriptionSpa,
    DeclarationDescriptionFre,
    DamageDescriptionIta,
    DamageDescriptionEng,
    DamageDescriptionGer,
    DamageDescriptionSpa,
    DamageDescriptionFre,
    RemovalDescriptionIta,
    RemovalDescriptionEng,
    RemovalDescriptionGer,
    RemovalDescriptionSpa,
    RemovalDescriptionFre,
    NoteDescriptionIta,
    NoteDescriptionEng,
    NoteDescriptionGer,
    NoteDescriptionSpa,
    NoteDescriptionFre,
    ArticleDescriptionRom,
    ArticleDescriptionPor,
    ArticleDescriptionPol,
    ArticleDescriptionHol,
    ArticleDescriptionAlb,
    ArticleDescriptionDen,
    ReasonDescriptionRom,
    ReasonDescriptionPor,
    ReasonDescriptionPol,
    ReasonDescriptionHol,
    ReasonDescriptionAlb,
    ReasonDescriptionDen,
    AdditionalDescriptionRom,
    AdditionalDescriptionPor,
    AdditionalDescriptionPol,
    AdditionalDescriptionHol,
    AdditionalDescriptionAlb,
    AdditionalDescriptionDen,
    DeclarationDescriptionRom,
    DeclarationDescriptionPor,
    DeclarationDescriptionPol,
    DeclarationDescriptionHol,
    DeclarationDescriptionAlb,
    DeclarationDescriptionDen,
    DamageDescriptionRom,
    DamageDescriptionPor,
    DamageDescriptionPol,
    DamageDescriptionHol,
    DamageDescriptionAlb,
    DamageDescriptionDen,
    RemovalDescriptionRom,
    RemovalDescriptionPor,
    RemovalDescriptionPol,
    RemovalDescriptionHol,
    RemovalDescriptionAlb,
    RemovalDescriptionDen,
    NoteDescriptionRom,
    NoteDescriptionPor,
    NoteDescriptionPol,
    NoteDescriptionHol,
    NoteDescriptionAlb,
    NoteDescriptionDen
        
    FROM FineOwner
    WHERE FineId=".$Id.";
        ");
}

//FINEADDITIONALCONTROLLER//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$rs_FineAdditionalController = $rs->Select('FineAdditionalController',"FineId=$Id");
if(mysqli_num_rows($rs_FineAdditionalController)>0){
    $rs->SelectQuery("
            INSERT INTO FineAdditionalController (FineId,ControllerId)
            SELECT
            ".$FineId.",
            ControllerId
        
            FROM FineAdditionalController
            WHERE FineId=".$Id.";
        ");
}

//FINETRESPASSER//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$rs_FineTrespasser = $rs->Select('V_FineTrespasser',"FineId=".$Id);
if(mysqli_num_rows($rs_FineTrespasser) > 0){
    while($r_FineTrespasser = $rs->getArrayLine($rs_FineTrespasser)){
        $a_FineTrespasser = array(
            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_FineTrespasser['TrespasserId'], 'settype'=>'int'),
            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
            array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$r_FineTrespasser['TrespasserTypeId'], 'settype'=>'int'),
        );
        $rs->Insert('FineTrespasser',$a_FineTrespasser);
    }
    
    $a_Fine = array(
        array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>10),
    );
    
    $rs->Update('Fine',$a_Fine, 'Id='.$FineId);
}

//FINEDOCUMENTATION////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$rs_FineDocumentation = $rs->Select('FineDocumentation',"FineId=$Id AND DocumentationTypeId=1");
while($r_FineDocumentation = mysqli_fetch_array($rs_FineDocumentation)){
    $a_FineDocumentation = array(
        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId),
        array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$r_FineDocumentation['Documentation']),
        array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>1),
        array('field'=>'VersionDate','selector'=>'value','type'=>'str','value'=>date("Y-m-d H:i:s"))
    );
    $rs->Insert('FineDocumentation',$a_FineDocumentation);
    if (!is_dir($str_Folder."/".$_SESSION['cityid'])) {
        mkdir($str_Folder."/".$_SESSION['cityid'], 0777);
    }
    if (!is_dir($str_Folder."/".$_SESSION['cityid']."/".$FineId)) {
        mkdir($str_Folder."/".$_SESSION['cityid']."/".$FineId, 0777);
    }
    copy($str_Folder."/".$_SESSION['cityid']."/".$Id."/".$r_FineDocumentation['Documentation'], $str_Folder."/".$_SESSION['cityid']."/".$FineId."/".$r_FineDocumentation['Documentation']);
}

//STAMPA////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($PrintDestination == 1){ //Consegna in ufficio
    //NON MODIFICARE
    $s_TypePlate = $r_Fine['CountryId'] == 'Z000' ? 'N' : 'F'; //Usata nella createdynamic per essere passata a getFineFees per determinare se vanno prese spese per nazionali o esteri
    $b_DisableTransaction = true; //Usata nelle create e createdynamic per disabilitare la transazione siccome è già inizializzata qui
    $AGCreation = true;
    $CreationType = 5;
    $PrintDestinationFold = 1; //Usata nelle createdynamic per determinare il testo della restituzione del piego, in questo caso è sempre quello per Ufficio
    $_POST['checkbox'] = array($FineId); //Usata nelle create e createdynamic per la select dei verbali, in questo caso del nuovo verbale creato
    
    if($r_Fine['CountryId'] != "Z000"){
        require(COD."/createdynamic_fine_foreign.php");
    } else {
        require(COD."/createdynamic_fine_national.php");
    }
} else {
    $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
}

//Non conclude la transazione se in createdynamic_fine_national il pagopa ha fallito
if(!isset($a_FailedPagoPA) || empty($a_FailedPagoPA)){
    $rs->End_Transaction();
}


header("location: mgmt_fine.php".$Filters);


