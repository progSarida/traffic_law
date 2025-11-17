<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$Options = "<option></option>";

$CountryId = $_POST['CountryId'];

$rs_ForeignCity = $rs->Select("ForeignCity", 'CountryId="'.$CountryId.'"');

while ($r_ForeignCity = mysqli_fetch_array($rs_ForeignCity)){
    $Options .= '<option value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>';
}

echo json_encode(
    array(
        "Options" => $Options,
    )
);
