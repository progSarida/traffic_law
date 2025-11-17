<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$rs->SetCharset("utf8");

$a_Results = array();
$resultForm = '<option>Tutti</option>';
$RuleTypeId = $_POST['RuleTypeId'];
$NationalityId = $_POST['NationalityId'];
$LanguageId = $_POST['LanguageId'];
$SelectedFormTypeId = $_POST['FormTypeId'];
$CityId = $_POST['CityId'];

$a_Form = $rs->getResults($rs->SelectQuery('
    SELECT distinct f.FormTypeId, f.Title AS TypeTitle, "Testi dinamici" AS Category
    FROM FormDynamic AS f
    WHERE RuleTypeId='.$RuleTypeId.' AND CityId="'.$CityId.'" AND NationalityId='.$NationalityId.' AND LanguageId='.$LanguageId.' AND Deleted=0
    UNION
    SELECT FT.Id AS FormTypeId, FT.Title AS TypeTitle, "Altro" AS Category
    FROM FormType FT
    WHERE FT.Id = '.($NationalityId == NAZIONALE ? TIPO_SOTTOTESTI_FISSI_NAZ : TIPO_SOTTOTESTI_FISSI_EST)
));

foreach ($a_Form as $form){
    $a_Results[$form["Category"]][] = $form;
}

foreach ($a_Results as $category => $elements){
    $resultForm .= '<optgroup label="' . htmlspecialchars($category) . '">';
    foreach ($elements as $element){
        $resultForm .= '<option'.($SelectedFormTypeId > 0 && $SelectedFormTypeId == $element['FormTypeId'] ? ' selected' : '').' value='.$element['FormTypeId'].'>'.htmlspecialchars($element['TypeTitle']).'</option>';
    }
    $resultForm .= '</optgroup>';
    
}

echo json_encode(
    array(
        "Form" => $resultForm,
    )
);