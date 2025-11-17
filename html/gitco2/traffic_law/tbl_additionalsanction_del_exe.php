<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Filters = CheckValue('Filters', 's');
$Id = CheckValue('Id', 'n');

$rs_UsedASanctions = $rs->SelectQuery("
    SELECT NULLIF(A1.AdditionalSanctionId , 0), NULLIF(A2.AdditionalSanctionId , 0)
    FROM Fine F
    JOIN FineArticle F2 ON F2.FineId = F.Id
    LEFT JOIN FineAdditionalArticle F3 ON F3.FineId = F.Id
    LEFT JOIN ArticleTariff A1 ON A1.ArticleId = F2.ArticleId
    LEFT JOIN ArticleTariff A2 ON A2.ArticleId = F3.ArticleId
    WHERE F.CityId = '{$_SESSION['cityid']}'
    GROUP BY A1.AdditionalSanctionId, A2.AdditionalSanctionId;
");
while($r_UsedASanctions = $rs->getArrayLine($rs_UsedASanctions)){
    foreach ($r_UsedASanctions as $value){
        if(!is_null($value)) $a_UsedASanctions[] = $value;
    }
}

$a_UsedASanctions = array_unique($a_UsedASanctions);

if(!in_array($Id, $a_UsedASanctions)){
    $rs->Delete('AdditionalSanction', "Id=$Id");
    $_SESSION['Message']['Success'] = "Azione eseguita con successo.";
} else {
    $_SESSION['Message']['Error'] = "Impossibile eliminare: la sanzione accessoria è usata almeno in un verbale.";
}

header("Location: tbl_additionalsanction.php".$Filters);