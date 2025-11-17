<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');
$a_Lan = unserialize(LANGUAGE);

$rs->Start_Transaction();

$RuleTypeId   = CheckValue('RuleTypeId','n');
$CityId = CheckValue('CityId','s');

$rs_Rule = $rs->Select('RuleType', "Id=" . $RuleTypeId. " AND CityId='". $CityId . "'");
$rs_RuleTitle = $rs->Select(MAIN_DB.'.Rule', "Id=$RuleTypeId");
$r_RuleTitle = mysqli_fetch_array($rs_RuleTitle);

if(mysqli_num_rows($rs_Rule)>0){
    header("location: mgmt_rule_add.php".$str_GET_Parameter."&error=Ruolo già presente per questo comune");
    DIE;
}

$a_RuleType = array(
    array('field'=>'Id','selector'=>'value','type'=>'int','value'=>$RuleTypeId,'settype'=>'int'),
    array('field'=>'CityId','selector'=>'field','type'=>'str'),
    array('field'=>'Title','selector'=>'value','type'=>'str','value'=>$r_RuleTitle['Title']),
);

foreach($a_Lan as $value){
    if ($value != ""){
        $str_Header = $_POST['PrintHeader'.$value];
        $str_Header = str_replace(PHP_EOL, '*', $str_Header);
        $a_RuleType[] = array('field'=>'PrintHeader'.$value,'selector'=>'value','type'=>'str','value'=>preg_replace('#\R+#', "", $str_Header));
    }
    
    if ($value != ""){
        $str_Object = $_POST['PrintObject'.$value];
        $str_Object = str_replace(PHP_EOL, '*', $str_Object);
        $a_RuleType[] = array('field'=>'PrintObject'.$value,'selector'=>'value','type'=>'str','value'=>preg_replace('#\R+#', "", $str_Object));
    }
    
}

// $a_RuleType = array(
//     array('field'=>'Id','selector'=>'value','type'=>'int','value'=>$RuleTypeId,'settype'=>'int'),
//     array('field'=>'CityId','selector'=>'field','type'=>'str'),
//     array('field'=>'Title','selector'=>'value','type'=>'str','value'=>$r_RuleTitle['Title']),
    
//     array('field'=>'PrintHeaderIta','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintHeaderEng','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintHeaderGer','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintHeaderSpa','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintHeaderFre','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintHeaderRom','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintHeaderPor','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintHeaderPol','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintHeaderHol','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintHeaderAlb','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintHeaderDen','selector'=>'field','type'=>'str'),
    
//     array('field'=>'PrintObjectIta','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintObjectEng','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintObjectGer','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintObjectSpa','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintObjectFre','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintObjectRom','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintObjectPor','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintObjectPol','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintObjectHol','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintObjectAlb','selector'=>'field','type'=>'str'),
//     array('field'=>'PrintObjectDen','selector'=>'field','type'=>'str'),
// );

$rs->Insert('RuleType', $a_RuleType);


$rs->End_Transaction();


header("location: mgmt_rule.php".$str_GET_Parameter."&answer=Inserito con successo.");
