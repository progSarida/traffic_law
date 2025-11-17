<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

ini_set('max_execution_time', 3000);


$P = CheckValue('P','s');
$compress = CheckValue('compress','n');




$path = PUBLIC_FOLDER."/_PAYMENT_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');


$n_FineAssigned = 0;
$n_FineNotAssigned = 0;
$a_FinePaymentReg = array();

$f_TotalFineAssigned = 0.00;
$f_TotalFineNotAssigned = 0.00;



$PaymentTypeId = 1;
$ImportationId = 1;

$rs_customer = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
$r_customer = mysqli_fetch_array($rs_customer);

$BankMgmt = $r_customer['NationalBankMgmt'];
$FinePaymentSpecificationType = $r_customer['FinePaymentSpecificationType'];

$rs->Start_Transaction();
$file = fopen($path.$ImportFile,  "r");
$aFile = explode(".",$ImportFile);
if (strtolower($aFile[count($aFile)-1])=="csv") {
	$delimiter = detectDelimiter($path . $ImportFile);


    if(is_resource($file)) {
        while (!feof($file)) {
            $row = fgetcsv($file, 1000, $delimiter);
            if (isset($row[0])) {

                if ($row[0] == "ACCOUNTNUMBER" || trim($row[0] == "")) continue;


                $ReasonId = 1;
                $DocumentationTypeId = 1;
                $PaymentDate = $row[3];
                $CreditDate = $row[9];


                $FifthField = str_replace("*", "", trim($row[10]));
                if (strlen($FifthField) < 16) {
                    $n_NumDiff = 16 - strlen($FifthField);

                    for ($i = 1; $i <= $n_NumDiff; $i++) {
                        $FifthField = "0" . $FifthField;
                    }
                }
                $DocumentType = $row[6];
                $Amount = trim($row[7]);
                $Documentation = $row[18];


                $FifthFieldTable = intval(substr($FifthField, 0, 2));
                $FifthFieldPaymentNumber = intval(substr($FifthField, 2, 2));
                $PaymentDocumentId = substr($FifthField, 4, 1); //indica se pagamento ridotto 0, normale 1 o maggiorato 2
                $FifthFieldAdditionalField = substr($FifthField, 5, 1); //da usare per tipo atto

                $FineId = intval(substr($FifthField, 6));


                $PaymentDate = substr($PaymentDate, 0, 4) . "-" . substr($PaymentDate, 4, 2) . "-" . substr($PaymentDate, 6, 2);
                $CreditDate = substr($CreditDate, 0, 4) . "-" . substr($CreditDate, 4, 2) . "-" . substr($CreditDate, 6, 2);


                $Amount = substr($Amount, 0, (strlen($Amount) - 2)) . "." . substr($Amount, -2);


                $rs_Fine = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND Id=" . $FineId);
                $FindNumber = mysqli_num_rows($rs_Fine);


                if ($FindNumber == 0) {


                    $FifthFieldTable = 1;
                    $FifthFieldPaymentNumber = 0;
                    $PaymentDocumentId = 0;


                    $rs_FinePayment = $rs->Select('FinePayment', "CityId='" . $_SESSION['cityid'] . "' AND FifthField='" . $FifthField . "'");

                    if (mysqli_num_rows($rs_FinePayment) > 0) {
                        $r_FinePayment = mysqli_fetch_array($rs_FinePayment);
                        $FinePaymentId = $r_FinePayment['Id'];
                        $a_FinePayment = array(
                            array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => str_replace('.tif', '.jpg', $Documentation)),
                        );
                        $rs->Update('FinePayment', $a_FinePayment, "Id=" . $FinePaymentId);


                        $a_FinePaymentReg[] = 'Aggiornato pagamento con quinto campo ' . $FifthField . ' (o immagini o dati)';

                    } else {
                        $a_Payment = array(
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
                            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                            array('field' => 'BankMgmt', 'selector' => 'value', 'type' => 'int', 'value' => $BankMgmt, 'settype' => 'int'),
                            array('field' => 'PaymentTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentTypeId, 'settype' => 'int'),
                            array('field' => 'PaymentDocumentId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentDocumentId, 'settype' => 'int'),
                            array('field' => 'ImportationId', 'selector' => 'value', 'type' => 'int', 'value' => $ImportationId, 'settype' => 'int'),
                            array('field' => 'PaymentDate', 'selector' => 'value', 'type' => 'date', 'value' => $PaymentDate),
                            array('field' => 'CreditDate', 'selector' => 'value', 'type' => 'date', 'value' => $CreditDate),
                            array('field' => 'TableId', 'selector' => 'value', 'type' => 'int', 'value' => $FifthFieldTable, 'settype' => 'int'),
                            array('field' => 'PaymentFee', 'selector' => 'value', 'type' => 'int', 'value' => $FifthFieldPaymentNumber, 'settype' => 'int'),
                            array('field' => 'Amount', 'selector' => 'value', 'type' => 'flt', 'value' => $Amount, 'settype' => 'flt'),
                            array('field' => 'DocumentType', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentType, 'settype' => 'int'),
                            array('field' => 'FifthField', 'selector' => 'value', 'type' => 'str', 'value' => $FifthField),
                            array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => str_replace('.tif', '.jpg', $Documentation)),
                            array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                            array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                            array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                        );


                        $rs->Insert('FinePayment', $a_Payment);


                        $n_FineNotAssigned++;
                        $f_TotalFineNotAssigned += $Amount;

                    }


                    $img = new Imagick($path . $Documentation);
                    $width = intval($img->getimagewidth());
                    $height = intval($img->getimageheight());
                    $img->stripImage();
                    $img->SetImageFormat('JPG');
                    $img->writeImage(PAYMENT_RECLAIM . "/" . $_SESSION['cityid'] . "/" . str_replace('.tif', '.jpg', $Documentation));
                    //$img->destroy();


                    if (file_exists(PAYMENT_RECLAIM . "/" . $_SESSION['cityid'] . "/" . str_replace('.tif', '.jpg', $Documentation))) {
                        unlink($path . $Documentation);
                    } else {
                        echo "Poblemi con la creazione del documento: " . $Documentation;
                        die;
                    }


                } else {
                    $DocumentType = 896;
                    $r_Fine = mysqli_fetch_array($rs_Fine);

                    $ProtocolYear = $r_Fine['ProtocolYear'];


                    $payments = $rs->Select('FinePayment', "CityId='" . $_SESSION['cityid'] . "' AND FineId=" . $FineId . " AND TableId=" . $FifthFieldTable . " AND PaymentFee=" . $FifthFieldPaymentNumber);
                    $FindNumber = mysqli_num_rows($payments);

                    if ($FindNumber == 0) {

                        $rs_FinePayment = $rs->Select('FinePayment', "CityId='" . $_SESSION['cityid'] . "' AND FineId=" . $FineId . " AND FifthField='" . $FifthFieldTable . "'");

                        if (mysqli_num_rows($rs_FinePayment) > 0) {
                            $r_FinePayment = mysqli_fetch_array($rs_FinePayment);
                            $FinePaymentId = $r_FinePayment['Id'];
                            $a_FinePayment = array(
                                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => str_replace('.tif', '.jpg', $Documentation)),
                            );
                            $rs->Update('FinePayment', $a_FinePayment, "Id=" . $FinePaymentId);
                            $a_FinePaymentReg[] = 'Aggiornato pagamento con quinto campo ' . $FifthFieldTable . ' (o immagini o dati)';

                        } else {
                            $a_Payment = array(
                                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                                array('field' => 'BankMgmt', 'selector' => 'value', 'type' => 'int', 'value' => $BankMgmt, 'settype' => 'int'),
                                array('field' => 'PaymentTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentTypeId, 'settype' => 'int'),
                                array('field' => 'PaymentDocumentId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentDocumentId, 'settype' => 'int'),
                                array('field' => 'ImportationId', 'selector' => 'value', 'type' => 'int', 'value' => $ImportationId, 'settype' => 'int'),
                                array('field' => 'PaymentDate', 'selector' => 'value', 'type' => 'date', 'value' => $PaymentDate),
                                array('field' => 'CreditDate', 'selector' => 'value', 'type' => 'date', 'value' => $CreditDate),
                                array('field' => 'TableId', 'selector' => 'value', 'type' => 'int', 'value' => $FifthFieldTable, 'settype' => 'int'),
                                array('field' => 'PaymentFee', 'selector' => 'value', 'type' => 'int', 'value' => $FifthFieldPaymentNumber, 'settype' => 'int'),
                                array('field' => 'Amount', 'selector' => 'value', 'type' => 'flt', 'value' => $Amount, 'settype' => 'flt'),
                                array('field' => 'DocumentType', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentType, 'settype' => 'int'),
                                array('field' => 'FifthField', 'selector' => 'value', 'type' => 'str', 'value' => $FifthField),
                                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => str_replace('.tif', '.jpg', $Documentation)),
                                array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                                array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                                array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                            );

                            //Controllo se l'articolo del verbale prevede pagamento ridotto
                            $rs_Reduced = $rs->Select('V_FineTariff', "FineId=$FineId AND ReducedPayment > 0");
                            
                            $a_Fee = separatePayment($FinePaymentSpecificationType, (mysqli_num_rows($rs_Reduced) > 0 ? 0 : 1), false, $Amount, $FineId, $_SESSION['cityid'], $ProtocolYear, $PaymentDate, $r_Fine['ReminderDate']);
                            
                            $a_Payment[] = array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['Fee'], 'settype' => 'flt');
                            $a_Payment[] = array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['ResearchFee'], 'settype' => 'flt');
                            $a_Payment[] = array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['NotificationFee'], 'settype' => 'flt');
                            $a_Payment[] = array('field' => 'PercentualFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['PercentualFee'], 'settype' => 'flt');
                            $a_Payment[] = array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CustomerFee'], 'settype' => 'flt');
                            $a_Payment[] = array('field' => 'CanFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CanFee'], 'settype' => 'flt');
                            $a_Payment[] = array('field' => 'CadFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CadFee'], 'settype' => 'flt');


                            $rs->Insert('FinePayment', $a_Payment);
                            $n_FineAssigned++;
                            $f_TotalFineAssigned += $Amount;
                        }


                        if (file_exists($path . $Documentation)) {


                            $str_Folder = ($r_Fine['CountryId'] == 'Z000') ? NATIONAL_FINE : FOREIGN_FINE;


                            if ($compress) {
                                $img = new Imagick($path . $Documentation);
                                $width = intval($img->getimagewidth() / 3);
                                $height = intval($img->getimageheight() / 3);
                                $img->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
                                $img->setImageCompression(Imagick::COMPRESSION_JPEG);
                                $img->setImageCompressionQuality(40);
                                $img->stripImage();
                                $img->SetImageFormat('JPG');
                                $img->writeImage($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . str_replace('.tif', '.jpg', $Documentation));
                                $img->destroy();

                            } else {
                                $img = new Imagick($path . $Documentation);
                                $width = intval($img->getimagewidth());
                                $height = intval($img->getimageheight());
                                $img->stripImage();
                                $img->SetImageFormat('JPG');
                                $img->writeImage($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . str_replace('.tif', '.jpg', $Documentation));
                                $img->destroy();


                            }


                        }
                    } else {

                        if (file_exists($str_Folder . "/" . $_SESSION['cityid'] . "/" . $FineId . "/" . $Documentation)) {
                            unlink($path . $Documentation);
                        } else {
                            echo "Poblemi con la creazione del documento: " . $Documentation;
                            die;
                        }
                    }


                }
            }
        }
    }

}else{
	$delimiter = detectDelimiter($path . $ImportFile);
	$chk_Bank = 0;
    if(is_resource($file)) {

        while (!feof($file)) {
            $row = fgets($file);
            if (strlen(trim($row)) != "") {
                if ($chk_Bank == 0) {
                    $chk_Bank++;
                } else {

                    $a_row = explode($delimiter, $row);

                    if (trim($a_row[5]) != "") {
                        $FifthField = trim($a_row[5]);
                        if (strlen($FifthField) < 16) {
                            $n_NumDiff = 16 - strlen($FifthField);

                            for ($i = 1; $i <= $n_NumDiff; $i++) {
                                $FifthField = "0" . $FifthField;
                            }
                        }

                        $DocumentType = $a_row[3];

                        $Amount = str_replace(",", ".", $a_row[7]);

                        $CreditDate = DateInDB($a_row[1]);
                        $PaymentDate = DateInDB($a_row[2]);

                        $DocumentType = $a_row[3];


                        if (($DocumentType == 674 || $DocumentType == 896) && $FifthField != "0000000000000000") {

                            $FifthFieldTable = intval(substr($FifthField, 0, 2));
                            $FifthFieldPaymentNumber = intval(substr($FifthField, 2, 2));
                            $PaymentDocumentId = substr($FifthField, 4, 1); //indica se pagamento ridotto 0, normale 1 o maggiorato 2
                            $FifthFieldAdditionalField = substr($FifthField, 5, 1);
                            $FineId = intval(substr($FifthField, 6));
                            /*
                            echo "<br> FifthField: ".$FifthField;
                            echo "<br> PaymentDocumentId: ".$PaymentDocumentId;
                            echo "<br> FifthFieldAdditionalField: ".$FifthFieldAdditionalField;
                            echo "<br> FineId: ".$FineId;

                            echo "<br>CityId='" . $_SESSION['cityid'] . "' AND Id=" . $FineId;
                            echo "<br>";
                            */
                            $rs_Fine = $rs->Select('Fine', "CityId='" . $_SESSION['cityid'] . "' AND Id=" . $FineId);

                            if (mysqli_num_rows($rs_Fine) > 0) {

                                $r_Fine = mysqli_fetch_array($rs_Fine);

                                $ProtocolYear = $r_Fine['ProtocolYear'];
                                
                                $a_Payment = array(
          
                                    array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                                    array('field' => 'BankMgmt', 'selector' => 'value', 'type' => 'int', 'value' => $BankMgmt, 'settype' => 'int'),
                                    array('field' => 'PaymentTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentTypeId, 'settype' => 'int'),
                                    array('field' => 'PaymentDocumentId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentDocumentId, 'settype' => 'int'),
                                    array('field' => 'ImportationId', 'selector' => 'value', 'type' => 'int', 'value' => $ImportationId, 'settype' => 'int'),
                                    array('field' => 'PaymentDate', 'selector' => 'value', 'type' => 'date', 'value' => $PaymentDate),
                                    array('field' => 'CreditDate', 'selector' => 'value', 'type' => 'date', 'value' => $CreditDate),
                                    array('field' => 'TableId', 'selector' => 'value', 'type' => 'int', 'value' => $FifthFieldTable, 'settype' => 'int'),
                                    array('field' => 'PaymentFee', 'selector' => 'value', 'type' => 'int', 'value' => $FifthFieldPaymentNumber, 'settype' => 'int'),
                                    array('field' => 'Amount', 'selector' => 'value', 'type' => 'flt', 'value' => $Amount, 'settype' => 'flt'),
                                    array('field' => 'DocumentType', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentType, 'settype' => 'int'),
                                    array('field' => 'FifthField', 'selector' => 'value', 'type' => 'str', 'value' => $FifthField),
                                    array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                                    array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                                    array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                                );
                                
                                //Se viene impostato il pagamento ridotto controllo se è previsto dall'articolo
                                if($PaymentDocumentId == 0){
                                    //Controllo se l'articolo del verbale prevede pagamento ridotto
                                    $rs_Reduced = $rs->Select('V_FineTariff', "FineId=$FineId AND ReducedPayment > 0");
                                    //Se non è previsto imposto automaticamente il pagamento come normale
                                    if(mysqli_num_rows($rs_Reduced) == 0)
                                        $PaymentDocumentId = 1;
                                }
                                
                                $a_Fee = separatePayment($FinePaymentSpecificationType, $PaymentDocumentId, false, $Amount, $FineId, $_SESSION['cityid'], $ProtocolYear, $PaymentDate, $r_Fine['ReminderDate']);
                                
                                $a_Payment[] = array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['Fee'], 'settype' => 'flt');
                                $a_Payment[] = array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['ResearchFee'], 'settype' => 'flt');
                                $a_Payment[] = array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['NotificationFee'], 'settype' => 'flt');
                                $a_Payment[] = array('field' => 'PercentualFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['PercentualFee'], 'settype' => 'flt');
                                $a_Payment[] = array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CustomerFee'], 'settype' => 'flt');
                                $a_Payment[] = array('field' => 'CanFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CanFee'], 'settype' => 'flt');
                                $a_Payment[] = array('field' => 'CadFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CadFee'], 'settype' => 'flt');
                                

                                $payments = $rs->Select('FinePayment', "CityId='" . $_SESSION['cityid'] . "' AND FineId=" . $FineId . " AND TableId=" . $FifthFieldTable . " AND PaymentFee=" . $FifthFieldPaymentNumber);

                                $FindNumber = mysqli_num_rows($payments);

                                if ($FindNumber == 0) {

                                    //per i pagamenti associati metto il FineId
                                    $a_Payment[] = array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int');
                                    $rs->Insert('FinePayment', $a_Payment);

                                    $n_FineAssigned++;
                                    $f_TotalFineAssigned += $Amount;

                                } else {
                                    //Bug 1695
                                    //inserisco sempre il pagamento ma prima confronto il $FifthField 
                                    // se è uguale mettere il pagamento non associato 
                                    // se è diverso inserire il pagamento come associato
                                    // non va gestita parte documentale
                                    $r_FinePayment = mysqli_fetch_array($payments);
                                    if (isset($FifthField) && isset($r_FinePayment['FifthField']) && $FifthField == $r_FinePayment['FifthField']) {
                              
                                        //salvo il pagamento senza associazione con il verbale (FineId = 0)
                                        $a_Payment[] = array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int');                          
                                        $a_FinePaymentReg[] = 'Pagamento importato tra i record da bonificare (' . $Amount . ') con quinto campo già presente:' . $FifthField . " CRON " . $r_Fine['ProtocolId'] . "/" . $r_Fine['ProtocolYear'] . " Si prega di caricare il file csv con immagini e procedere alla bonifica.";
                                        $n_FineNotAssigned++;
                                        $f_TotalFineNotAssigned += $Amount;
                                    } else {
                                        $a_Payment[] = array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int');
                                        
                                        $a_FinePaymentReg[] = 'Pagamento importato (' . $Amount . ') con quinto campo differente:' . $FifthField . " CRON " . $r_Fine['ProtocolId'] . "/" . $r_Fine['ProtocolYear'] . ".";
                                        $n_FineAssigned++;
                                        $f_TotalFineAssigned += $Amount;
                                    }
                                    
                                    $rs->Insert('FinePayment', $a_Payment);      
                                }
                                
                            } else {
                                $a_FinePaymentReg[] = 'Pagamento non importato (' . $Amount . ') quinto campo non riconosciuto:' . $FifthField . " Si prega di caricare il file csv con immagini e procedere alla bonifica.";
                            }

                        } else {

                            $FifthFieldTable = 1;
                            $FifthFieldPaymentNumber = 0;


                            $a_Payment = array(
                                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
                                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                                array('field' => 'BankMgmt', 'selector' => 'value', 'type' => 'int', 'value' => $BankMgmt, 'settype' => 'int'),
                                array('field' => 'PaymentTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentTypeId, 'settype' => 'int'),
                                array('field' => 'ImportationId', 'selector' => 'value', 'type' => 'int', 'value' => $ImportationId, 'settype' => 'int'),
                                array('field' => 'PaymentDate', 'selector' => 'value', 'type' => 'date', 'value' => $PaymentDate),
                                array('field' => 'CreditDate', 'selector' => 'value', 'type' => 'date', 'value' => $CreditDate),
                                array('field' => 'TableId', 'selector' => 'value', 'type' => 'int', 'value' => $FifthFieldTable, 'settype' => 'int'),
                                array('field' => 'PaymentFee', 'selector' => 'value', 'type' => 'int', 'value' => $FifthFieldPaymentNumber, 'settype' => 'int'),
                                array('field' => 'Amount', 'selector' => 'value', 'type' => 'flt', 'value' => $Amount, 'settype' => 'flt'),
                                array('field' => 'DocumentType', 'selector' => 'value', 'type' => 'int', 'value' => $DocumentType, 'settype' => 'int'),
                                array('field' => 'FifthField', 'selector' => 'value', 'type' => 'str', 'value' => $FifthField),
                                array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                                array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                                array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
                            );


                            $rs->Insert('FinePayment', $a_Payment);

                            $n_FineNotAssigned++;
                            $f_TotalFineNotAssigned += $Amount;

                        }
                    }
                }
            }
        }
    }

}
fclose($file);

$rs->End_Transaction();





include_once TCPDF . "/tcpdf.php";

$pdf = new TCPDF('', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, true);


$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($_SESSION['citytitle']);
$pdf->SetTitle('Request');
$pdf->SetSubject('Request');
$pdf->SetKeywords('');


$pdf->SetMargins(10,10,10);

$r_Payments = $rs->Select('Customer',"CityId='".$_SESSION['cityid']."'");
$r_Payment = mysqli_fetch_array($r_Payments);

$MangerName = $r_Payment['ManagerName'];
$ManagerAddress = $r_Payment['ManagerAddress'];
$ManagerCity = $r_Payment['ManagerZIP']." ".$r_Payment['ManagerCity']." (".$r_Payment['ManagerProvince'].")";
$ManagerPhone = $r_Payment['ManagerPhone'];







$pdf->AddPage();
$pdf->SetFont('arial', '', 9, '', true);

$pdf->setFooterData(array(0,64,0), array(0,64,128));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);

$pdf->Image($_SESSION['blazon'], 10, 10, 10, 18);


$pdf->writeHTMLCell(150, 0, 30, '', $MangerName, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerAddress, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '',$ManagerCity, 0, 0, 1, true, 'L', true);
$pdf->LN(4);
$pdf->writeHTMLCell(150, 0, 30, '', $ManagerPhone, 0, 0, 1, true, 'L', true);

$pdf->LN(10);



$pdf->writeHTMLCell(200, 0, 30, '', "IMPORTAZIONE PAGAMENTI ".date('d/m/Y H:i:s'), 0, 0, 1, true, 'C', true);





$pdf->LN(10);
$y = $pdf->getY();

$pdf->writeHTMLCell(30, 4, 10, $y, "Pagamenti associati" , 1, 0, 1, true, 'R', true);
$pdf->writeHTMLCell(30, 4, 40, $y, $n_FineAssigned, 1, 0, 1, true, 'R', true);
$pdf->writeHTMLCell(30, 4, 70, $y,"Importo totale" , 1, 0, 1, true, 'R', true);
$pdf->writeHTMLCell(30, 4, 100, $y, NumberDisplay($f_TotalFineAssigned), 1, 0, 1, true, 'R', true);

$pdf->LN(6);
for($i=0; $i<Count($a_FinePaymentReg);$i++){
    $pdf->LN(8);
    $y = $pdf->getY();
    $pdf->writeHTMLCell(180, 4, 10, $y, $a_FinePaymentReg[$i], 1, 0, 1, true, 'L', true);
}



$pdf->LN(10);
$y = $pdf->getY();

$pdf->writeHTMLCell(30, 4, 10, $y, "Pagamenti non associati" , 1, 0, 1, true, 'R', true);
$pdf->writeHTMLCell(30, 4, 40, $y, $n_FineNotAssigned, 1, 0, 1, true, 'R', true);
$pdf->writeHTMLCell(30, 4, 70, $y,"Importo totale" , 1, 0, 1, true, 'R', true);
$pdf->writeHTMLCell(30, 4, 100, $y, NumberDisplay($f_TotalFineNotAssigned), 1, 0, 1, true, 'R', true);

$FileName = $_SESSION['cityid'].'_importazione_pagamenti_'.date("Y-m-d_H-i").'.pdf';

$pdf->Output(ROOT."/doc/print/payment/".$FileName, "F");
$_SESSION['Documentation'] = $MainPath.'/doc/print/payment/'.$FileName;



unlink($path.$ImportFile);
header("location: ".$P);