<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
ini_set('max_execution_time', 0);





/*

Identificativo violazione 	Data violazione 	Targa	Importo Sanzione e spese Comando affidato	 Importo sanzione e spese Comando pagato	Importo provvigioni	Data pagamento	Spese notifica pagate	Spese postali pagate	commissioni ON-LINE	ECCEDENZA

v20554r/2017	            22/05/2017	        14BBR5	        0	                                    74.22	                                    0	            07/03/2018	        0	                    0   	            0	                    0
2908/15	19/07/2015	EZ717FA	96	96	28.8	01/01/2017	0	8	2.67	2.67

4445Z/2016/V	06/05/2016	ZH699001	                    56.7	                                    56.7	                                    17.01	            02/01/2017	        29.28	                0.5             	1.73	            1.73
2666Z/2015/V	16/08/2015	EY859RW	95	95	28.5	02/01/2017	0	8	2.65	2.65
4812Z/2016/V	28/06/2016	HH-EK2558	56.7	56.7	17.01	03/01/2017	29.28	0.61	1.73	1.73


*/
$file_Payment = fopen(ROOT."/public/_TMP_EXP/".'payment.csv', 'w');



$str_PaymentCsvFile = 'Identificativo violazione;Data violazione;Targa;Importo Sanzione e spese Comando affidato;Importo sanzione e spese Comando pagato;Importo provvigioni;Data pagamento;Spese notifica pagate;Spese postali pagate;commissioni ON-LINE;ECCEDENZA'.PHP_EOL;
fwrite($file_Payment, $str_PaymentCsvFile);

$rs_Payment = $rs->SelectQuery("
SELECT
F.CityId,
F.Code,
F.FineDate,
F.VehiclePlate,

FP.PaymentDate,


FP.Fee Fee ,
FP.ResearchFee + FP.NotificationFee + FP.CanFee + FP.CadFee + FP.CustomerFee + FP.OfficeNotificationFee AS  NotificationFee

FROM Fine F JOIN FinePayment FP ON F.Id = FP.FineId
WHERE F.CityId='".$_SESSION['cityid']."' AND PaymentDate='2018-03-07' AND F.CountryId!='Z000'
");


while ($r_Payment = mysqli_fetch_array($rs_Payment)) {

    $str_PaymentCsvFile =
        $r_Payment['Code'].';'.
        f_Exp_DateOutDB_Maggioli($r_Payment['FineDate']).';'.
        $r_Payment['VehiclePlate'].';0;'.
        $r_Payment['Fee'].';0;'.
        f_Exp_DateOutDB_Maggioli($r_Payment['PaymentDate']).';'.
        $r_Payment['NotificationFee'].';0;0;0'.PHP_EOL;

    fwrite($file_Payment, $str_PaymentCsvFile);

}
fclose($file_Payment);