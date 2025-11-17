<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");


$rs= new CLS_DB();



$table =  CheckValue('table', 's');
$qtype = CheckValue('qtype', 's');


$aField = $_POST['aField'];

$a_Update = [];

foreach ($aField as $key => $value) {
	
	if ($value['hidden'] == 'false') {

		$a_Update[] = array(
			'field' => $value['field'],
			'selector' => 'field',
			'type' => $value['type'],
			'settype' => $value['type']
		);


	} else {

		// teknika mreti lojes mos e prek se e dhjet
		if ($qtype=='ins') {
			$a_Update[] = array(
				'field' => $value['field'],
				'selector' => 'value',
				'value' => $value['type'] == 'str' ? '' : 0,
				'type' => $value['type'],
				'settype' => $value['type']
			);
		}
	}
}

// teknika mreti lojes mos e prek se e dhjet
if ($table == 'Detector') {
	$a_Update[] = array(
		'field' => 'CityId',
		'selector' => 'value',
		'value' => $_SESSION['cityid'],
		'type' => 'str',
		'settype' => 'str'
	);
}

$aField = $a_Update;

print_r($aField);
 
if($qtype=='upd'){
	$id = CheckValue('Id','n');
	$rs->Update($table,$aField,"Id=".$id);

		echo "Record inserito";

}else if($qtype=='ins'){
	$rs->Insert($table,$aField);


		echo "Record inserito";

}else if($qtype=='del'){
	$Id = CheckValue('Id','n');
	$rs->ExecuteQuery("DELETE FROM $table where Id=".$Id);


}

