<?php
require("_path.php");
require(INC . "/parameter.php");
require(CLS . "/cls_db.php");
include(INC . "/function.php");
$rs = new CLS_DB();
//$cityid = $_SESSION['cityid'];

$keywords = array();
$params = $_POST['params'];

isset($_POST['city'])&&!empty($_POST['city'])? array_push($keywords,"traffic_law.ForeignCity.Id =".$_POST['city']) : null;
isset($_POST['street'])&&!empty($_POST['street'])? array_push($keywords,"sarida.ForeignZIPCity.StreetName like '%".$_POST['street']."%'") : null;
isset($_POST['title'])&&!empty($_POST['title'])? array_push($keywords,"sarida.ForeignZIPCity.Title like '%".$_POST['title']."%'") : null;
isset($_POST['zip'])&&!empty($_POST['zip'])? array_push($keywords,"sarida.ForeignZIPCity.ZIP =".$_POST['zip']."") : null;
$string ="";
foreach ($keywords as $index =>$value){
    if ($index===0){
        $string .=$value;
    }else{
        $string .= " AND $value";
    }
}
$search = $rs->SelectQuery("
    SELECT sarida.ForeignZIPCity.*,traffic_law.ForeignCity.Id AS C_Id,traffic_law.ForeignCity.Title AS CityName, IFNULL(sarida.Toponym.Title,'') AS ToponymName
    FROM sarida.ForeignZIPCity 
    JOIN traffic_law.ForeignCity on sarida.ForeignZIPCity.ForeignCityId = traffic_law.ForeignCity.Id 
    LEFT JOIN sarida.Toponym ON sarida.ForeignZIPCity.ToponymId=sarida.Toponym.Id 
    WHERE $string 
    LIMIT 100");

$array = array();
while ($row_results = mysqli_fetch_array($search)) {
    array_push($array,array('Id' => $row_results['Id'],'City'=>$row_results['CityName'],'StreetName'=>$row_results['StreetName'],'Toponimo'=> $row_results['ToponymName'],'Title'=>$row_results['Title'],'ZIP'=>$row_results['ZIP'],'Params'=>$params));
}
echo json_encode($array);
