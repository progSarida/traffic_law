<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

//header('Content-type: text/html;charset=utf-8');

//Aggancia un numero definito di spazi davanti o in fondo alla stringa passata
function formatLicensePointP($string, $spaces, $before = false){
    $part = strtoupper(trim($string));
    if ($spaces - strlen($string) >= 0)
        return $before ? str_repeat(' ', $spaces - strlen($string)).$part : $part.str_repeat(' ', $spaces - strlen($string));
    else
        return substr($part, 0, 2);
}

$BackPage = CheckValue('BackPage','s');

$Id = CheckValue('Id', 'n');
$Year = CheckValue('Year', 'n');
$CityId = CheckValue('CityId','s');
$AdditionalSanctionId = CheckValue('AdditionalSanctionId','n');

$Id1 = CheckValue('Id1','n');
$Id2 = CheckValue('Id2','s');
$Id3 = CheckValue('Id3','s');
//TODO BUG 3239 rimosso in quanto sviluppo annullato
//$VehicleTypeId = CheckValue('VehicleTypeId', 'n') > 0 ? CheckValue('VehicleTypeId', 'n') : null;

$LicensePointCode1P1 = formatLicensePointP(CheckValue('LicensePointCode1P1','s'), 4);
$LicensePointCode1P2 = formatLicensePointP(CheckValue('LicensePointCode1P2','s'), 2);
$LicensePointCode1P3 = formatLicensePointP(CheckValue('LicensePointCode1P3','s'), 2, true);

$LicensePointCode2P1 = formatLicensePointP(CheckValue('LicensePointCode2P1','s'), 4);
$LicensePointCode2P2 = formatLicensePointP(CheckValue('LicensePointCode2P2','s'), 2);
$LicensePointCode2P3 = formatLicensePointP(CheckValue('LicensePointCode2P3','s'), 2, true);

//TODO BUG 3239 rimosso in quanto sviluppo annullato
//$rs_Article = $rs->Select('Article', "Id !=" .$Id. " AND Id1=" . $Id1. " AND Id2='". $Id2 . "' AND Id3='". $Id3 . "' AND CityId='". $CityId ."' AND VehicleTypeId".($VehicleTypeId > 0 ? '='.$VehicleTypeId : ' IS NULL'));
$rs_Article = $rs->Select('Article', "Id !=" .$Id. " AND Id1=" . $Id1. " AND Id2='". $Id2 . "' AND Id3='". $Id3 . "' AND CityId='". $CityId ."'");

if(mysqli_num_rows($rs_Article)>0){
    $_SESSION['Message'] = 'Articolo già presente per questo comune.';
    header("location: ".impostaParametriUrl(array('P' => $BackPage), 'tbl_article_upd.php'.$str_GET_Parameter.'&Id='.$Id.'&Year='.$Year));
    DIE;
}

$rs->Start_Transaction();

$a_Article = array(
    array('field'=>'Article','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'Paragraph','selector'=>'field','type'=>'str'),
    array('field'=>'Letter','selector'=>'field','type'=>'str'),
    array('field'=>'ViolationTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
	array('field'=>'ArtComune','selector'=>'field','type'=>'str'),
    array('field'=>'ArticleLetterAssigned','selector'=>'field','type'=>'str'),
    array('field'=>'Amicable','selector'=>'chkbox','type'=>'int'),
    //TODO BUG 3239 rimosso in quanto sviluppo annullato
    //array('field'=>'VehicleTypeId','selector'=>'value','type'=>'str','value'=>$VehicleTypeId),
    array('field'=>'Id1','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'Id2','selector'=>'field','type'=>'str'),
    array('field'=>'Id3','selector'=>'field','type'=>'str'),
    array('field'=>'DescriptionIta','selector'=>'field','type'=>'str'),
	array('field'=>'DescriptionEng','selector'=>'field','type'=>'str'),
	array('field'=>'DescriptionGer','selector'=>'field','type'=>'str'),
	array('field'=>'DescriptionSpa','selector'=>'field','type'=>'str'),
	array('field'=>'DescriptionFre','selector'=>'field','type'=>'str'),
    array('field'=>'DescriptionRom','selector'=>'field','type'=>'str'),
    array('field'=>'DescriptionPor','selector'=>'field','type'=>'str'),
    array('field'=>'DescriptionPol','selector'=>'field','type'=>'str'),
    array('field'=>'DescriptionHol','selector'=>'field','type'=>'str'),
    array('field'=>'DescriptionAlb','selector'=>'field','type'=>'str'),
    array('field'=>'DescriptionDen','selector'=>'field','type'=>'str'),
	array('field'=>'AdditionalTextIta','selector'=>'field','type'=>'str'),
	array('field'=>'Note','selector'=>'field','type'=>'str'),
);

$rs->Update('Article',$a_Article,"Id=".$Id);


$a_ArticleTariff = array(
	array('field'=>'Fee','selector'=>'field','type'=>'flt','settype'=>'flt'),
	array('field'=>'MaxFee','selector'=>'field','type'=>'flt','settype'=>'flt'),
	array('field'=>'LicensePoint','selector'=>'field','type'=>'int','settype'=>'int'),
	array('field'=>'YoungLicensePoint','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'LicensePointCode1','selector'=>'value','type'=>'str','value'=>$LicensePointCode1P1.$LicensePointCode1P2.$LicensePointCode1P3),
    array('field'=>'LicensePointCode2','selector'=>'value','type'=>'str','value'=>$LicensePointCode2P1.$LicensePointCode2P2.$LicensePointCode2P3),
	array('field'=>'PresentationDocument','selector'=>'chkbox','type'=>'int'),
	array('field'=>'LossLicense','selector'=>'chkbox','type'=>'int'),
	array('field'=>'AdditionalMass','selector'=>'chkbox','type'=>'int'),
	array('field'=>'AdditionalNight','selector'=>'chkbox','type'=>'int'),
	array('field'=>'126Bis','selector'=>'chkbox','type'=>'int'),
	array('field'=>'Habitual','selector'=>'chkbox','type'=>'int'),
	array('field'=>'ReducedPayment','selector'=>'chkbox','type'=>'int'),
	array('field'=>'SuspensionLicense','selector'=>'chkbox','type'=>'int'),
    array('field'=>'UseAdditionalSanction','selector'=>'field','type'=>'str'),
    array('field'=>'AdditionalSanctionId','selector'=>'value','type'=>'int','value' => $AdditionalSanctionId, 'settype'=>'int'),
    array('field'=>'PrefectureFixed','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'PenalSanction','selector'=>'chkbox','type'=>'int'),
    array('field'=>'RevisionLicense','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'RevisionHabitual','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'RevocationLicense','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'RevocationHabitual','selector'=>'chkbox','type'=>'int','settype'=>'int'),
    array('field'=>'LossHabitual','selector'=>'chkbox','type'=>'int','settype'=>'int'),
);

$rs->Update('ArticleTariff',$a_ArticleTariff,"ArticleId=".$Id." AND Year=".$Year);

$_SESSION['Message']['Success'] = 'Azione eseguita con successo.';

$rs->End_Transaction();

header("location: ".$BackPage.$str_GET_Parameter);
