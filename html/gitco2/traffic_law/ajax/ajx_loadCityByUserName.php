<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_message.php");
include(INC."/function.php");

$rs= new CLS_DB();
$UserName=CheckValue('UserName','s');

$customers=$rs->ExecuteQuery("select c.ManagerName,c.CityId from Customer c join IniPecProcessing ipr on ipr.CityId=c.CityId where ipr.UserName='$UserName'");
$Options="";
while ($customer = mysqli_fetch_array($customers)){
    $Options .= '<option value='.$customer['CityId'].'>'.$customer['ManagerName'].'</option>';
}
echo json_encode(
    array(
        "Options" => $Options,
    )
);
?>