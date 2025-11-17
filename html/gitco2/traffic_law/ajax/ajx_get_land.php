<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$Options = "<option></option>";
$n_land = 0;

$CountryId = $_POST['CountryId'];

$rs_Land = $rs->Select("sarida.Land", 'CountryId="'.$CountryId.'"');
$n_land = mysqli_num_rows($rs_Land);

while ($r_Land = mysqli_fetch_array($rs_Land)){
    $Options .= '<option value='.$r_Land['Id'].'>'.StringOutDB($r_Land['Title']).'</option>';
}

echo json_encode(
    array(
        "Options" => $Options,
        "Results" => $n_land
    )
);
