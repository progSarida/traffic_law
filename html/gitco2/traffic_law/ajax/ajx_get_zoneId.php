<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$a_Zone = array(
    1 => "Europa e aree mediterranee",
    2 => "Altri stati non compresi",
    3 => "Oceania",
);
$CountryId = $_POST['CountryId'];
$ZoneId = "";
$LanguageId = "";
$Language = "";

$rs_Country = $rs->Select('Country',"Id='".$CountryId."'");
$r_Country = mysqli_fetch_array($rs_Country);

$rs_Language = $rs->Select('Language',"Id=".$r_Country['LanguageId']);
$r_Language = mysqli_fetch_array($rs_Language);

$ZoneId = $r_Country['ZoneId'];
$ZoneDescription = $a_Zone[$ZoneId];
$LanguageId = $r_Country['LanguageId'];
$Language = $r_Language['Title'];

echo json_encode(
    array(
        "ZoneId" => $ZoneId,
        "ZoneDescription" => $ZoneDescription,
        "LanguageId" => $LanguageId,
        "Language" => $Language,
    )
);
