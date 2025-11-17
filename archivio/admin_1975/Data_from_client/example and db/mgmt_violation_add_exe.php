<?php

$aInsert = array(
	array('field'=>'Code','type'=>'str','getvaluefield'=>'Code'),
	array('field'=>'CityId','type'=>'value','getvaluefield'=>$CityId),
	array('field'=>'StatusTypeId','type'=>'value','getvaluefield'=>$StatusTypeId),
	array('field'=>'ProtocolYear','type'=>'value','getvaluefield'=>$ProtocolYear),
	array('field'=>'FineDate','type'=>'value','getvaluefield'=>$FineDate),
	array('field'=>'FineTime','type'=>'str','getvaluefield'=>'FineTime'),
	array('field'=>'ControllerId','type'=>'int','getvaluefield'=>'ControllerId'),
	array('field'=>'Locality','type'=>'value','getvaluefield'=>$Locality),
	array('field'=>'Address','type'=>'str','getvaluefield'=>'Address'),
	array('field'=>'ReasonId','type'=>'int','getvaluefield'=>'ReasonId'),
	array('field'=>'VehicleTypeId','type'=>'int','getvaluefield'=>'VehicleTypeId'),
	array('field'=>'VehiclePlate','type'=>'value','getvaluefield'=>$VehiclePlate),
	array('field'=>'VehicleCountry','type'=>'str','getvaluefield'=>'VehicleCountry'),
	array('field'=>'CountryId','type'=>'str','getvaluefield'=>'CountryId'),
	array('field'=>'DepartmentId','type'=>'value','getvaluefield'=>$DepartmentId),
	array('field'=>'VehicleBrand','type'=>'str','getvaluefield'=>'VehicleBrand'),
	array('field'=>'VehicleModel','type'=>'str','getvaluefield'=>'VehicleModel'),
	array('field'=>'VehicleColor','type'=>'str','getvaluefield'=>'VehicleColor'),
	array('field'=>'VehicleMass','type'=>'int','getvaluefield'=>'VehicleMass'),
	array('field'=>'RegDate','type'=>'value','getvaluefield'=>$RegDate),
	array('field'=>'RegTime','type'=>'value','getvaluefield'=>$RegTime),
	array('field'=>'UserId','type'=>'value','getvaluefield'=>$UserId),
	array('field'=>'Note','type'=>'str','getvaluefield'=>'Note'),
);


$rs = new CLS_DB();
$FineId = $rs->Insert('Fine',$aInsert);



$aInsert = array(
	array('field'=>'FineId','type'=>'value','getvaluefield'=>$FineId),
	array('field'=>'ArticleId','type'=>'int','getvaluefield'=>'ArticleId'),
	array('field'=>'CityId','type'=>'value','getvaluefield'=>$CityId),
	array('field'=>'ViolationTypeId','type'=>'int','getvaluefield'=>'ViolationTypeId'),
	array('field'=>'Fee','type'=>'int','getvaluefield'=>'Fee'),
	array('field'=>'MaxFee','type'=>'int','getvaluefield'=>'MaxFee'),
	array('field'=>'DetectorId','type'=>'value','getvaluefield'=>$DetectorId),
	array('field'=>'SpeedLimit','type'=>'str','getvaluefield'=>'SpeedLimit'),
	array('field'=>'SpeedControl','type'=>'str','getvaluefield'=>'SpeedControl'),
	array('field'=>'Speed','type'=>'str','getvaluefield'=>'Speed'),
);

$rs->Insert('FineArticle',$aInsert);


