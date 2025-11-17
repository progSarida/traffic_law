<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

header('Content-type: text/html;charset=utf-8');

$rs->Start_Transaction();

$CityId = CheckValue('CityId','s');


$customers = $rs->Select('Customer', "CityId='".$CityId."'");
$RowNumber = mysqli_num_rows($customers);



$a_Customer = array(
	array('field'=>'CityId','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerAdditionalName','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerName','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerSector','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerTaxCode','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerVAT','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerCity','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerProvince','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerAddress','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerZIP','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerCountry','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerPhone','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerFax','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerMail','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerPEC','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerWeb','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerInfo','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerProcessName','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerDataEntryName','selector'=>'field','type'=>'str'),
	array('field'=>'ManagerSignName','selector'=>'field','type'=>'str'),
	array('field'=>'NationalBankOwner','selector'=>'field','type'=>'str'),
	array('field'=>'NationalBankName','selector'=>'field','type'=>'str'),
	array('field'=>'NationalBankAccount','selector'=>'field','type'=>'str'),
	array('field'=>'NationalBankIban','selector'=>'field','type'=>'str'),
	array('field'=>'NationalBankSwift','selector'=>'field','type'=>'str'),
	array('field'=>'ForeignBankOwner','selector'=>'field','type'=>'str'),
	array('field'=>'ForeignBankName','selector'=>'field','type'=>'str'),
	array('field'=>'ForeignBankAccount','selector'=>'field','type'=>'str'),
	array('field'=>'ForeignBankIban','selector'=>'field','type'=>'str'),
	array('field'=>'ForeignBankSwift','selector'=>'field','type'=>'str'),
	array('field'=>'Reference','selector'=>'field','type'=>'str'),
	array('field'=>'FifthField','selector'=>'field','type'=>'int','settype'=>'int'),
	array('field'=>'ReturnPlace','selector'=>'field','type'=>'str'),
	array('field'=>'MCTCUserName','selector'=>'field','type'=>'str'),
	array('field'=>'MCTCPassword','selector'=>'field','type'=>'str'),
	
	array('field'=>'NationalBankMgmt','selector'=>'chkbox','type'=>'int'),
	array('field'=>'ForeignBankMgmt','selector'=>'chkbox','type'=>'int'),
	array('field'=>'LumpSum','selector'=>'chkbox','type'=>'int'),
	array('field'=>'DigitalSignature','selector'=>'chkbox','type'=>'int'),
	array('field'=>'ExternalRegistration','selector'=>'chkbox','type'=>'int'),
	array('field'=>'NationalAnticipateCost','selector'=>'chkbox','type'=>'int'),
	array('field'=>'ForeignAnticipateCost','selector'=>'chkbox','type'=>'int'),
	array('field'=>'CityUnion','selector'=>'chkbox','type'=>'int'),
	array('field'=>'FinePaymentSpecificationType','selector'=>'chkbox','type'=>'int'),
	array('field'=>'FinePDFList','selector'=>'chkbox','type'=>'int'),
	array('field'=>'ChiefControllerList','selector'=>'chkbox','type'=>'int'),

);




$a_Folder[] = "public/_PAYMENT_/";
$a_Folder[] = "public/";
$a_Folder[] = "doc/national/fine/";

$dirI= "img/index/";

$toDirI = $dirI.'/'.$CityId .'/';

	mkdir($toDirI, 0777, true);
	chmod($toDirI, 0777);
	
	move_uploaded_file($_FILES['Image']['tmp_name'], $toDirI . basename($_FILES['Image']['name']));

$dirL = "img/blazon/";

$toDirL = $dirL.'/'.$CityId .'/';

 
	mkdir($toDirL, 0777, true);
	chmod($toDirL, 0777);

	move_uploaded_file($_FILES['Blazon']['tmp_name'], $toDirL . basename($_FILES['Blazon']['name']));


if ($RowNumber == 0) {
	 
	$ArticleId = $rs->Insert('Customer',$a_Customer);

 		foreach ($a_Folder as $folder) {
		if (!file_exists($folder.'/'.$CityId)) {
	    	mkdir($folder.'/'.$CityId, 0777, true);
		}
	}



} else {
	$customers = mysqli_fetch_array($customer);
	$ArticleId = $customers['CityId'];

	$rs->Update('Customer',$a_Customer,"CityId='".$ArticleId."'");
}





$rs->End_Transaction();

header("location: tbl_customer.php");