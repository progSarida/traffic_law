<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();

$CityId = CheckValue("City", "s");
$Year = CheckValue("Year", "s");
$AllFee = CheckValue("AllFee",'n');

$FromDate = date("d/m/Y",mktime(0, 0, 0, 1, 1, $Year));
$ToDate = date("d/m/Y",mktime(23, 59, 59, 12, 31, $Year));

//echo "AllFee $AllFee"; 

if ($CityId != "" && $Year != ""){
    
    $rs_Payments = $rs->SelectQuery("
    SELECT SUM(FP.Amount) AS SumNot142, SUM(FP.Fee) AS SumNot142Fee
	FROM V_FinePayment FP
	INNER JOIN FineArticle FA on FA.FineId=FP.FineId
	INNER JOIN Article A on A.Id=FA.ArticleId
	WHERE FP.CityId='".$CityId."' AND PaymentDate >= '". DateInDB($FromDate) . "' AND PaymentDate <= '".DateInDB($ToDate)."' ".
        "AND (A.ViolationTypeId <> 2 OR (A.ViolationTypeId = 2 AND A.Article <> 142));");
    
    $r_Payments = mysqli_fetch_array($rs_Payments);
    $SumNot142 = ($AllFee ? $r_Payments['SumNot142'] : $r_Payments['SumNot142Fee']);
    $SumNot142 = number_format((float)$SumNot142, 2, ',', ' ');
    
    $rs_Payments = $rs->SelectQuery("
    SELECT SUM(FP.Amount) AS Sum142, SUM(FP.Fee) AS Sum142Fee
	FROM V_FinePayment FP
	INNER JOIN FineArticle FA on FA.FineId=FP.FineId
    INNER JOIN Article A on A.Id=FA.ArticleId
	WHERE FP.CityId='".$CityId."' AND PaymentDate >= '". DateInDB($FromDate) . "' AND PaymentDate <= '".DateInDB($ToDate)."' ".
        " AND A.ViolationTypeId = 2 AND A.Article = 142;");
    
    $r_Payments = mysqli_fetch_array($rs_Payments);
    $Sum142 = ($AllFee ? $r_Payments['Sum142'] : $r_Payments['Sum142Fee']);
    $Sum142 = number_format((float)$Sum142, 2, ',', ' ');
    
    $SumC = $Sum142 <0.01 ? number_format((float)0, 2, ',', ' ')." €" : 'Inserire Importo';
    //$SumD = $Sum142 <0.01 ? number_format((float)0, 2, ',', ' ')." €" : 'Inserire Importo';
    
    echo json_encode(
        array(
            "SumNot142" => $SumNot142,
            "Sum142" => $Sum142,
            "SumC" => $SumC,
            //"SumD" => $SumD,
        )
    );
} else {
    echo json_encode(
        array(
            "SumNot142" => "",
            "Sum142" => "",
            "SumC" => "",
            //"SumD" => "",
        )
    );
}

