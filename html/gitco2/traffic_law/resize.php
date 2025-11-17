<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");


ini_set('max_execution_time', 30000);


$path = PUBLIC_FOLDER."/".$_SESSION['cityid']."/";


$ImportFile = "kria.csv";


$file = fopen($path.$ImportFile,  "r");
$delimiter = ";";


while (!feof($file)) {
	$row = fgetcsv($file, 1000, $delimiter);
	if (isset($row[0])) {



		$Documentation = $row[9];

		$DocumentName = $Documentation;

		$img = new Imagick($path.$Documentation);
		$width = intval($img->getimagewidth() / 3);
		$height = intval($img->getimageheight() / 3);
		$img->resizeImage($width,$height,Imagick::FILTER_LANCZOS,1);
		$img->setImageCompression(Imagick::COMPRESSION_JPEG);
		$img->setImageCompressionQuality(40);
		$img->stripImage();
		$img->writeImage(ROOT."/kria/".$DocumentName);
		$img->destroy();


		if (file_exists(ROOT."/kria/".$DocumentName)) {
			unlink($path.$Documentation);
		}
		else{
			echo "Poblemi con la creazione del documento: ".$DocumentName;
			DIE;
		}



	}
}

fclose($file);

echo "FATTO";