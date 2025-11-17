<?php
require("_path.php");
require(INC . "/parameter.php");
require(CLS . "/cls_db.php");
include(INC . "/function.php");
$rs = new CLS_DB();

//$cityid = $_SESSION['cityid'];

$keywords = array();
$params = $_POST['params'];

isset($_POST['city'])&&!empty($_POST['city'])? array_push($keywords,"City.Title like '%".$_POST['city']."%'") : null;
isset($_POST['street'])&&!empty($_POST['street'])? array_push($keywords,"StreetName  like '%".$_POST['street']."%'") : null;
isset($_POST['title'])&&!empty($_POST['title'])? array_push($keywords,"ZIPCity.Title like '%".$_POST['title']."%'") : null;
isset($_POST['zip'])&&!empty($_POST['zip'])? array_push($keywords,"ZIPCity.ZIP =".$_POST['zip']."") : null;
$string ="";
foreach ($keywords as $index =>$value){
    if ($index===0){
        $string .=$value;
    }else{
        $string .= " AND $value";
    }

}

//$search = $rs->SelectQuery("SELECT sarida.ZIPCity.*,sarida.City.Id as C_Id,sarida.City.Title as CityName,sarida.Toponym.Title as ToponymName
//from sarida.ZIPCity JOIN sarida.City on sarida.ZIPCity.CityId = sarida.City.Id JOIN sarida.Toponym ON sarida.ZIPCity.ToponymId=sarida.Toponym.Id where $string AND sarida.ZIPCity.CityId = '".$_SESSION['cityid']."' limit 100");

$search = $rs->SelectQuery("SELECT sarida.ZIPCity.*,sarida.City.Id as C_Id,sarida.City.Title as CityName,sarida.Toponym.Title as ToponymName
from sarida.ZIPCity JOIN sarida.City on sarida.ZIPCity.CityId = sarida.City.Id JOIN sarida.Toponym ON sarida.ZIPCity.ToponymId=sarida.Toponym.Id where $string limit 100");

$array = array();
while ($row_results = mysqli_fetch_array($search)) {
    //array_push($array,array('Id' => $row_results['Id'],'City'=>$row_results['CityName'],'StreetName'=> $row_results['Strada'].' '.$row_results['StreetName'],'Toponimo'=> $row_results['ToponymName'],'Title'=>$row_results['Title'],'ZIP'=>$row_results['ZIP']));
    array_push($array,array('Id' => $row_results['Id'],'City'=>$row_results['CityName'],'StreetName'=>$row_results['StreetName'],'Toponimo'=> $row_results['ToponymName'],'Title'=>$row_results['Title'],'ZIP'=>$row_results['ZIP'],'Params'=>$params));
}
echo json_encode($array);
