<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();

$ActionType = $_POST['ActionType'];

if ($ActionType == "fetch"){
    $LandId = $_POST['LandId'];
    
    $rs_Land = $rs->SelectQuery('
        SELECT L.*, C.Title CountryTitle 
        FROM sarida.Land L
        JOIN Country C ON L.CountryId=C.Id
        WHERE L.Id='.$LandId
    );
    $r_Land = mysqli_fetch_array($rs_Land);
    echo json_encode(
        array(
            "Title" => StringOutDB($r_Land['Title']),
            "CountryId" => StringOutDB($r_Land['CountryId']),
            "CountryTitle" => StringOutDB($r_Land['CountryTitle']),
        )
    );
}

if ($ActionType == "check"){
    $Title = $_POST['Title'];
    $OldTitle = $_POST['OldTitle'];
    $CountryId = $_POST['CountryId'];
    
    if ($OldTitle != "")
        $rs_Land = $rs->Select("sarida.Land", 'Title="'.$Title.'" AND Title<>"'.$OldTitle.'"  AND CountryId="'.$CountryId.'"');
    else
        $rs_Land = $rs->Select("sarida.Land", 'Title="'.$Title.'" AND CountryId="'.$CountryId.'"');
    
    echo json_encode(
        array(
            "Result" => mysqli_num_rows($rs_Land) > 0 ? "NO" : "OK",
            "Title" => $Title,
            "OldTitle" => $OldTitle,
        )
    );
    
}