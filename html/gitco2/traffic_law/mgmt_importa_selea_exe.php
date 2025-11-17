<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs = new CLS_DB(); 

$file = CheckValue('file', 's');

$filePath = '../traffic_law/public/_SELEA_/'.$file;

$handle = @fopen($filePath, 'r');

$images = glob('../traffic_law/public/_SELEA_/*.jpg');

if ($handle) {

    while (($line = fgetcsv($handle, 0, ';')) !== false) {
		
		 //print_r($line);

    	$City = explode(' ', $line[3])[0];

		$customers = $rs->SelectQuery("SELECT CityId FROM traffic_law.Customer WHERE ManagerName LIKE '%$City%'");

		$CityId = mysqli_fetch_array($customers)['CityId'];

		$FineTimestamp = strtotime($line[1]);

		$SeleaTypeId = strpos($line[4], 'Revisione') !== false ? 1 : 2;

		if ($CityId) {

	        foreach ($images as $image) {

				if ($line[0] == explode('-', explode('_', basename($image))[1])[0]) {

					$basename = basename($image);
					@mkdir("../traffic_law/public/_SELEA_/$CityId");
					rename($image, "../traffic_law/public/_SELEA_/$CityId/$basename");
	        	
				}
	        }

	        $insertSelea =  array(
			    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
			    array('field'=>'FineDate','selector'=>'value','type'=>'str','value'=>date('Y-m-d', $FineTimestamp)),
			    array('field'=>'FineTime','selector'=>'value','type'=>'str','value'=>date('H:m', $FineTimestamp)),
			    array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$line[0]),
			    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$basename),
			    array('field'=>'DocumentationOrder','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
			    array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>$CityId),
			    array('field'=>'Address','selector'=>'value','type'=>'str','value'=>explode(" ", $line[3])[0]),
			    array('field'=>'SeleaTypeId','selector'=>'value','type'=>'int','value'=>$SeleaTypeId,'settype'=>'int'),
			    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
			    array('field'=>'RegTime','selector'=>'value','type'=>'time','value'=>date('H:m')),
			    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['userid']),
			);
	       
			$rs->Insert('traffic_law.TMP_ImportSelea',$insertSelea);
		} else {
			echo 'ERROR: City ID NOT FOUND!';
		}   
    }

    fclose($handle);

    unlink($filePath);

    header('Location: mgmt_importa_selea.php');

} else {

    echo 'ERROR: Could not open file!';
}