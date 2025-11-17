<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

ini_set('max_execution_time', 3000);


$P = CheckValue('P','s');


$CityId = $_SESSION['cityid'];

$ImportFile = CheckValue('ImportFile','s');
$path = PUBLIC_FOLDER."/".$_SESSION['cityid']."/";


$file = fopen($path.$ImportFile,  "r");
$delimiter = ",";
if(is_resource($file)) {
    while (!feof($file)) {
        $row = fgetcsv($file, 1000, $delimiter);
        if (isset($row[0]) && trim($row[0]) != "") {

            $TrespasserId = $row[1];
            $str_TaxCode = trim($row[2]);
            $str_PEC = trim($row[3]);

            $n_IrideCode = (isset($row[4]) && strlen(trim($row[4])) > 0) ? trim($row[4]) : 0;

            if (strlen($str_TaxCode) != 16 && strlen($str_TaxCode) != 11 && strlen($str_TaxCode) != 0) {
                for ($i = strlen($str_TaxCode); $i < 11; $i++) {
                    $str_TaxCode = "0" . $str_TaxCode;
                }

            }

            if ($str_PEC != "") {


                $a_Trespasser = array(
                    array('field' => 'PEC', 'selector' => 'value', 'type' => 'str', 'value' => $str_PEC),
                    array('field' => 'TaxCode', 'selector' => 'value', 'type' => 'str', 'value' => $str_TaxCode),
                    array('field' => 'IrideCode', 'selector' => 'value', 'type' => 'int', 'value' => $n_IrideCode, 'settype' => 'int'),
                );

                $rs->Update('Trespasser', $a_Trespasser, "Id=" . $TrespasserId);


            }
        }

    }
    fclose($file);
}
unlink($path.$ImportFile);

header("location: ".$P);

