<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs= new CLS_DB();
$rs->SetCharset('utf8');

$Action = CheckValue('Action', 's');
$FormTypeId = CheckValue('FormTypeId', 's');
$RuleTypeId = CheckValue('RuleTypeId', 's');
$NationalityId = CheckValue('NationalityId', 's');
$LanguageId = CheckValue('LanguageId', 's');
$CityId=CheckValue('CityId','s');
$Title=CheckValue('Title','s');
$Content = "";
$Keywords = "";
$Variables = "";
$FormTypeTitle = "";
$a_Results = array();

if ($Action =='checkPrimaryKey'){
    $rs_check=$rs->SelectQuery("select count(*) as count from FormDynamic f where CityId='$CityId' and title='$Title' and FormTypeId=$FormTypeId and RuleTypeId=$RuleTypeId AND NationalityId=$NationalityId AND LanguageId=$LanguageId");    
    if ($r_count = mysqli_fetch_array($rs_check))
    echo json_encode(
        array(
            "Result" => utf8_encode($Content),
            "exists" => $r_count['count']>0,
        )
        ); 
}
else if ($Action == "getContent"){
    $rs_Form = $rs->SelectQuery("SELECT
        F.Content, FT.Id as FormTypeId, FT.Title FormTypeTitle
        FROM FormType FT
        LEFT JOIN FormDynamic F ON (F.FormTypeId = FT.Id
        and FormTypeId=$FormTypeId AND CityId='' AND RuleTypeId=$RuleTypeId AND NationalityId=$NationalityId AND LanguageId=$LanguageId AND Deleted=0) where FT.Id=$FormTypeId");
    $r_Form = mysqli_fetch_array($rs_Form);
    $Content = $r_Form['Content'];
    $FormTypeTitle = $r_Form['FormTypeTitle'];
    $rs_Keywords = $rs->Select("FormKeyword", "FormTypeId=". $FormTypeId ." AND LanguageId=". $LanguageId ." AND RuleTypeId=". $RuleTypeId ." AND CityId='' AND NationalityId=". $NationalityId ." AND Disabled=0 AND Deleted=0 ORDER BY Title");
    
    $rs_Variables = $rs->SelectQuery("SELECT DISTINCT
        COALESCE(t2.Id, t1.Id) AS Id, COALESCE(t2.Description, t1.Description) AS Description
        FROM FormVariable t1
        LEFT OUTER JOIN FormVariable t2 on t2.CityId = '".$_SESSION['cityid']."' AND t2.FormTypeId=". $FormTypeId ." AND t2.NationalityId=". $NationalityId ." AND t2.LanguageId=". $LanguageId ." AND t2.RuleTypeId=". $RuleTypeId ." AND t2.Disabled=0
        WHERE t1.CityId = '' AND t1.FormTypeId=". $FormTypeId ." AND t1.NationalityId=". $NationalityId ." AND t1.LanguageId=". $LanguageId ." AND t1.RuleTypeId=". $RuleTypeId ." AND t1.Disabled=0");
    while ($r_Keyword = mysqli_fetch_array($rs_Keywords))
        $Keywords .= '<li class="list-group-item"><b>'.StringOutDB($r_Keyword['Title']).'</b> -> '.StringOutDB($r_Keyword['Description']).'</li>';
    $r_Variables = mysqli_fetch_all($rs_Variables,MYSQLI_ASSOC);
    $a_Variables = array();
    foreach ($r_Variables as $values)
        $a_Variables[$values['Id']][] = $values['Description'];
    foreach ($a_Variables as $Variable => $Description){
        $Variables .= '<li class="list-group-item"><b>'.StringOutDB($Variable).'</b> -> ';
        foreach ($Description as $Value)
            $Variables .= '</br>'.StringOutDB($Value);
        $Variables .= '</li>';
    }
    
    echo json_encode(
        array(
            "Result" => utf8_encode($Content),
            "FormTypeTitle" => $FormTypeTitle,
            "FormTypeId" => $r_Form['FormTypeId'],
            "Keywords" => $Keywords,
            "Variables" => $Variables,
        )
        );
} else if ($Action == "getViolationTypes"){
    $a_ViolationType = $rs->getResults($rs->Select("ViolationType", "RuleTypeId=$RuleTypeId AND Disabled=0".($NationalityId == NAZIONALE ? " AND NationalFormId > 0" : " AND ForeignFormId > 0")));
    
    foreach($a_ViolationType as $record){
        $o_Result = new stdClass();
        
        $o_Result->value = $NationalityId == NAZIONALE ? $record['NationalFormId'] : $record['ForeignFormId'];
        $o_Result->text = $record['Title'];
        
        array_push($a_Results, $o_Result);
    }
    echo json_encode(
        array(
            "Result" => $a_Results,
        )
    );
}