<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");




$rs= new CLS_DB();



$n_ArticleNumber = 1;

$VehiclePlate       = strtoupper(CheckValue('VehiclePlate','s'));
$Address            = strtoupper(CheckValue('Address','s'));
$ArticleId          = CheckValue('ArticleId','n');
$CountryId          = CheckValue('CountryId','s');
$Locality           = CheckValue('Locality','s');

$VehicleBrand        = strtoupper(CheckValue('VehicleBrand','s'));



$rs_Country = $rs->Select('Country', "Id='".$CountryId."'");
$VehicleCountry = mysqli_fetch_array($rs_Country)['Title'];

$rs_ArticleApp = $rs->Select('ArticleApp', "Id=".$ArticleId);
$r_ArticleApp = mysqli_fetch_array($rs_ArticleApp);

$ArticleId          = $r_ArticleApp['ArticleId'];
$str_ArticleText    = $r_ArticleApp['Description'];



$FineTypeId = 2;


$FineId=10;


if($Locality=="") $Locality = $_SESSION['usercity'];

$StatusTypeId = 13;
$ViolationTypeId = 1;

$rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['usercity']."'");

if (mysqli_num_rows($rs_RuleType) > 0){
    $r_RuleType = mysqli_fetch_array($rs_RuleType);
    $RuleTypeId = $r_RuleType['Id'];
    $str_WhereRule = " AND VT.RuleTypeId=$RuleTypeId";
} else $str_WhereRule = "";


$rs_Protocol = $rs->SelectQuery(
    "SELECT
        IFNULL(MAX(ProtocolId)+1, 1) ProtocolId,
        IFNULL(MAX(ProtocolIdAssigned)+1, 1) ProtocolIdAssigned
        FROM Fine F
        inner join FineArticle FA on F.Id = FA.FineId
        inner join ViolationType VT on FA.ViolationTypeId = VT.Id
        WHERE F.CityId='" . $_SESSION['usercity'] . "' AND F.ProtocolYear=" . Date("Y").$str_WhereRule);

$r_Protocol = mysqli_fetch_array($rs_Protocol);
$n_Protocol_num = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];


$n_Protocol_num = 0;
$ProtocolNumber = $n_Protocol_num;



$a_Fine = array(
	array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['usercity']),
	array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId,'settype'=>'int'),
	array('field'=>'ProtocolYear','selector'=>'value','type'=>'year','value'=>Date("Y")),
    array('field'=>'ProtocolId','selector'=>'value','type'=>'int','value'=>$ProtocolNumber,'settype'=>'int'),
	array('field'=>'FineDate','selector'=>'field','type'=>'date'),
	array('field'=>'FineTime','selector'=>'field','type'=>'str'),
    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$_SESSION['controllerid'],'settype'=>'int'),
	array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>$Locality),
	array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
	array('field'=>'VehicleTypeId','selector'=>'field','type'=>'int','settype'=>'int'),
    array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$VehiclePlate),
    array('field'=>'VehicleCountry','selector'=>'value','type'=>'str','value'=>$VehicleCountry),
	array('field'=>'CountryId','selector'=>'field','type'=>'str'),
    array('field'=>'VehicleBrand','selector'=>'value','type'=>'str', 'value'=>$VehicleBrand),
	array('field'=>'VehicleModel','selector'=>'field','type'=>'str'),
	array('field'=>'VehicleColor','selector'=>'field','type'=>'str'),
    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
	array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
	array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
    array('field'=>'FineTypeId','selector'=>'value','type'=>'int','value'=>$FineTypeId,'settype'=>'int'),

);










$rs->Start_Transaction();

$FineId = $rs->Insert('Fine',$a_Fine);

if($FineId==0){
	echo "Poblemi con l'inserimento del verbale";
	DIE;
}

$rss = new CLS_DB();


$Where = "Disabled=0 AND CityId='" . $_SESSION['usercity'] . "' AND Year=" . Date("Y") . " AND Id=". $ArticleId;
$finds = $rs->Select('V_Article', $Where);

$find = mysqli_fetch_array($finds);

$ArticleId          = $find['Id'];
$Fee                = $find['Fee'];
$MaxFee             = $find['MaxFee'];
$LicensePoint       = $find['LicensePoint'];


$str_Where = " ReasonTypeId=1 AND CityId='" . $_SESSION['usercity'] . "' AND ViolationTypeId=" . $ViolationTypeId;;


$rs_Reason = $rs->Select('Reason', $str_Where);
$r_Reason = mysqli_fetch_array($rs_Reason);

$ReasonId = $r_Reason['Id'];


$a_FineArticle = array(
	array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
    array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$ArticleId,'settype'=>'int'),
	array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['usercity']),
	array('field'=>'ViolationTypeId','selector'=>'value','type'=>'int','value'=>$ViolationTypeId, 'settype'=>'int'),
    array('field'=>'ReasonId','selector'=>'value','type'=>'int','value'=>$ReasonId,'settype'=>'int'),
    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$Fee,'settype'=>'flt'),
    array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$MaxFee,'settype'=>'flt'),
    array('field'=>'ArticleNumber','selector'=>'value','type'=>'int','value'=>$n_ArticleNumber,'settype'=>'int'),
    array('field'=>'LicensePoint','selector'=>'field','type'=>'int','value'=>$LicensePoint,'settype'=>'int'),
);

$rs->Insert('FineArticle',$a_FineArticle);








$a_FineOwner = array( array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int') );
$a_FineOwner [] = array('field'=>'ArticleDescriptionIta','selector'=>'value','type'=>'str','value'=>$str_ArticleText);
$rs->Insert('FineOwner', $a_FineOwner);




if(isset($_FILES)){
    $doc_count = 0;
    $str_DocumentationPath =  ($VehicleCountry=='Italia') ? NATIONAL_VIOLATION : FOREIGN_VIOLATION;


    if (!is_dir($str_DocumentationPath ."/". $_SESSION['cityid'] ."/". $FineId)) {
        mkdir($str_DocumentationPath ."/". $_SESSION['cityid'] ."/". $FineId, 0777);
    }
    $DocumentationTypeId=1;


    foreach ($_FILES as $file) {
        $doc_count++;


        $fileName = $FineId.'_'.$doc_count.'.jpg';

        if(move_uploaded_file($file['tmp_name'],$str_DocumentationPath ."/". $_SESSION['cityid'] ."/". $FineId.'/'.$fileName)) {

                $a_FineDocumentation = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $fileName),
                array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentationTypeId, 'settype' => 'int'),
            );
            $rs->Insert('FineDocumentation', $a_FineDocumentation);
        }


    }
}







$rs->End_Transaction();

$_SESSION['Message']['Success'] = "Inserito con successo.";
header("location: panel.php");
