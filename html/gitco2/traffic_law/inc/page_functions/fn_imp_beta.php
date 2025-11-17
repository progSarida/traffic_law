<?php
require_once(INC."/function_import.php");

define('IMP_BETA_CSV_PATH', VIOLATION_FOLDER."/".$_SESSION['cityid']."/");

function decodeVehicleType(string $VehicleTypeId):string{
    if ($VehicleTypeId == 'autocarri oltre 12t'
        || $VehicleTypeId == 'autocarri >3.5t e fino a 12t'
        || $VehicleTypeId == 'autoveicoli trasporto cose > 12T'
        || $VehicleTypeId == 'autocarri fino a 3.5t'
        || $VehicleTypeId == 'autocaravan fino a 3.5t'
        || $VehicleTypeId == 'autocaravan >3.5t fino a 12t')
        return "Autocarro";
        if ($VehicleTypeId == 'filobus fino a 8t'
            || $VehicleTypeId == 'filobus oltre 8t'
            || $VehicleTypeId == 'autobus oltre 8t'
            || $VehicleTypeId == 'autobus fino a 8t')
            return "Autobus";
            if ($VehicleTypeId == 'autovetture con rimorchio')
                return "Rimorchio";
                return trim($VehicleTypeId);
}

function getArticleFromBetaString(string $value, int $year){
    global $rs;
    $splitArt = array();
    //Corrispondenze possibili: <123> c.<123(bis|,123)>
    preg_match('/^([1-9]{3})(?: c\.)([1-9]{1,3}bis|[1-9]{1,3})(?:\,|)((?<=,)[1-9]{1,3}|)$/', $value, $splitArt);
    if(!empty($splitArt)){
        $str_ArticleWhere = "a.Article=".$splitArt[1];
        $str_ArticleWhere .= !empty($splitArt[2]) ? " AND a.Paragraph='".$splitArt[2]."'" : " AND COALESCE(a.Paragraph,'') = ''";
        $str_ArticleWhere .= !empty($splitArt[3]) ? " AND a.Letter='".$splitArt[3]."'" : " AND COALESCE(a.Letter,'') = ''";
        $articles = $rs->SelectQuery("select a.*, at.AdditionalNight, at.Fee, at.MaxFee from Article a join ArticleTariff at on at.Year={$year} and a.CityId='{$_SESSION['cityid']}' and at.ArticleId=a.Id where $str_ArticleWhere");
        return mysqli_fetch_assoc($articles);
    } else return null;
}

function getImpBetaControllers(){
    global $rs;
    $rs_Controller = $rs->Select('Controller', "CityId='".$_SESSION['cityid']."'");
    return controllersByCodeArray($rs_Controller);
}

function getImpBetaVehicleTypes(){
    global $rs;
    $a_chk_VehicleType = array();
    
    $rs_VehicleType = $rs->Select('VehicleType', "Disabled=0");
    while ($r_VehicleType = mysqli_fetch_assoc($rs_VehicleType)){
        $a_chk_VehicleType[$r_VehicleType['TitleIta']] = $r_VehicleType['Id'];
    }
    
    return $a_chk_VehicleType;
}