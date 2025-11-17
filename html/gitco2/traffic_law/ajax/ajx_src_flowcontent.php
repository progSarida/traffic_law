<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs = new CLS_DB();
$CityId = CheckValue('CityId','s');
$TypeId = CheckValue('TypeId','s');
$PrintTypeId = CheckValue('PrintTypeId','n');

$StartStatusId = CheckValue('StartStatusId','n');
$EndStatusId = CheckValue('EndStatusId','n');
$Day = CheckValue('Day','n');

$a_deadline = array("",
    "CreationDate",
    "UploadDate",
    "ProcessingDate",
    "PaymentDate",
    "SendDate",
    );



if($TypeId=="N")
    $query= "SELECT F.FileName, F.Id FROM `Flow` F JOIN `Customer` C ON F.CityId= C.CityId WHERE F.FileName LIKE '%_ita_%'";
else if($TypeId=="F")
    $query= "SELECT F.FileName, F.Id FROM `Flow` F JOIN `Customer` C ON F.CityId= C.CityId WHERE F.FileName NOT LIKE '%_ita_%'";


$query.= " AND F.CityId='".$CityId."'";
$query.= " AND F.PrintTypeId=".$PrintTypeId;

$query.= " AND F.".$a_deadline[$StartStatusId]." is not null";
$query.= " AND F.".$a_deadline[$EndStatusId]." is null";
$query.= " AND DATEDIFF(CURDATE(),F.".$a_deadline[$StartStatusId].")>".$Day;


$rs_Flow = $rs->ExecuteQuery($query);


$str_ProtocolId = '
    <div class="row-fluid">
        <div class="table_label_H col-sm-12">Flussi</div>
    </div>
';


while($r_Flow = mysqli_fetch_array($rs_Flow)){
    $str_ProtocolId .= '
        <div class="table_caption_H col-sm-12">'. $r_Flow['FileName'] .'</div>
        <div class="clean_row HSpace4"></div>
';
    
}

echo json_encode(
    array(
        "content" => $str_ProtocolId,
    )
);









