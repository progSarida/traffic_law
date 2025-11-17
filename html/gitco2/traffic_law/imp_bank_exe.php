<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

include_once TCPDF . "/tcpdf.php";

ini_set('max_execution_time', 3000);



$P = CheckValue('P','s');
$compress = CheckValue('compress','n');

$PaymentTypeId=CheckValue('PaymentTypeId','s');


$path = PUBLIC_FOLDER."/_PAYMENT_/".$_SESSION['cityid']."/";
$ImportFile = CheckValue('ImportFile','s');


$n_FineAssigned = 0;
$n_FineNotAssigned = 0;

$f_TotalFineAssigned = 0.00;
$f_TotalFineNotAssigned = 0.00;

$ImportationId = 1;


$rs_customer = $rs->Select('Customer', "CityId='" . $_SESSION['cityid'] . "'");
$r_customer = mysqli_fetch_array($rs_customer);


$NationalBankMgmt = $r_customer['NationalBankMgmt'];
$ForeignBankMgmt = $r_customer['ForeignBankMgmt'];

$FinePaymentSpecificationType = $r_customer['FinePaymentSpecificationType'];





$rs->Start_Transaction();

if (strtolower(substr($ImportFile,-3))=="csv") {
	$delimiter = detectDelimiter($path . $ImportFile);


	$List = 0;
	$TableId=1;

    $a_FindStr = array("142/7",
        "142/8",
        "142/9",
        "142-7",
        "142-8",
        "142-9",
        "142.7",
        "142.8",
        "142.9",
        "ART 142",
        "ART142",
        "ART. 142",
        "ART.142",
        "DEL 30",
        " 5 G",
        "RIDOTTA 30",
        " 5GG",
        " 5 GG",
        " 5 GIORNI",
        " 5GIO"
    );

    $a_ReplaceStr = array("","","","","","","","","","","","","","","","","","","","");
    
    $file = fopen($path.$ImportFile,  "r");

    if(is_resource($file)) {
        
        while (!feof($file)) {
            $row = fgetcsv($file, 1000, $delimiter);

            $Id = "";

            if (isset($row[0])) {
                if ($List == 0) {
                    if (trim($row[0] == "Data Contabile")) {
                        $List = 1;
                    }
                } else {


                    $PaymentDate = $row[0];
                    if (strlen($PaymentDate) == 8) {
                        $PaymentDate = substr($PaymentDate, 0, 6) . "20" . substr($PaymentDate, 6, 2);
                    }
                    $CreditDate = $row[1];
                    if (strlen($CreditDate) == 8) {
                        $CreditDate = substr($CreditDate, 0, 6) . "20" . substr($CreditDate, 6, 2);
                    }

                    $Amount = $row[6];
                    $Causal = $row[8];


                    $Descr = trim($row[3]);

                    $Name = "";
                    if ($Descr == "Bonifico dall'estero") {
                        //////////////////////////////////////////////////////////////////////////////
                        //////////////////////////////////////////////////////////////////////////////
                        ////
                        ////
                        ////    FOREIGN
                        ////
                        ////
                        //////////////////////////////////////////////////////////////////////////////
                        //////////////////////////////////////////////////////////////////////////////


                        $BankMgmt = $ForeignBankMgmt;

                        if (strpos($Causal, 'ZZ21/') !== false) {
                            $a_NameCausal = explode("ZZ21/", trim($Causal));
                            $aName = explode("ZZ3", trim($a_NameCausal[1]));
                            $Name = trim($aName[0]);

                        } else if (strpos($Causal, 'ZZ2') !== false) {
                            $a_NameCausal = explode("ZZ2", trim($Causal));
                            $aName = explode("ZZ3", trim($a_NameCausal[1]));
                            $Name = trim($aName[0]);
                        }


                        if (strpos($Amount, ".") === false && strpos($Amount, ",") === false) {
                            $Amount = $Amount . ".00";
                        } else {

                            $Amount = str_replace(",", ".", $Amount);

                        }

                        if ($Name != "") {
                            if (strpos($Causal, 'CRON') !== false) {
                                $aId = explode("CRON", trim($Causal));

                            } else {
                                $aId = explode("ZZ3", trim($Causal));
                            }

                            $pos_StartNumber = -1;
                            $pos_EndNumber = 0;

                            if (isset($aId[1])) {
                                $str_Clean = str_replace($a_FindStr, $a_ReplaceStr, $aId[1]);

                                for ($i = 0; $i < strlen($str_Clean); $i++) {

                                    if (is_numeric(substr($str_Clean, $i, 1))) {
                                        if ($pos_StartNumber < 0) {
                                            $pos_StartNumber = $i;
                                        }
                                    } else {
                                        if ($pos_StartNumber >= 0) {
                                            $pos_EndNumber = $i;
                                            break;
                                        }
                                    }

                                }
                                $n_Start = $pos_StartNumber;
                                $n_Lenght = $pos_EndNumber - $pos_StartNumber;

                                if ($n_Lenght == 0) $n_Lenght = 1;

                                $Id = substr($str_Clean, $n_Start, $n_Lenght);
                            } else $str_Clean = "";
                        }

                        $FindNumber = 0;
                        if ($Id == "" || $Id == 0 || $Id == "/") {
                            $Id = 0;
                        } else {
                            $fines = $rs->Select('V_FineTrespasser', "(TrespasserTypeId=1 OR TrespasserTypeId=11) AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolId=" . $Id, "ProtocolYear DESC");
                            $FindNumber = mysqli_num_rows($fines);

                            $chkFine = '';
                            $chkPayment = '';
                            $chkTrespasser = '';
                            $chkAmount = '';

                            if ($FindNumber == 0) {
                                $Id = 0;
                            } else {
                                $trespasser = mysqli_fetch_array($fines);
                                $Id = $trespasser['FineId'];
                                $ProtocolYear = $trespasser['ProtocolYear'];

                                $payments = $rs->Select('FinePayment', "CityId='" . $_SESSION['cityid'] . "' AND FineId=" . $Id . " AND TableId=" . $TableId);
                                $FindNumber = mysqli_num_rows($payments);

                                if ($FindNumber > 0) {

                                    $Id = 0;
                                    $PaymentPresents = true;
                                } else {

                                    if ($trespasser['Genre'] == 'D') {
                                        $a_TrespasserCompany = explode(" ", $trespasser['CompanyName']);
                                        $str_TrespasserFind = $a_TrespasserCompany[0];
                                    } else $str_TrespasserFind = $trespasser['Surname'];

                                    if (strpos($Causal, $str_TrespasserFind) === false) {
                                        if (strpos($Causal, $trespasser['VehiclePlate']) === false) {
                                            $Id = 0;

                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        //////////////////////////////////////////////////////////////////////////////
                        //////////////////////////////////////////////////////////////////////////////
                        ////
                        ////
                        ////    NATIONAL
                        ////
                        ////
                        //////////////////////////////////////////////////////////////////////////////
                        //////////////////////////////////////////////////////////////////////////////

                        $BankMgmt = $NationalBankMgmt;

                        $a_Causal = explode(" ID", trim($Causal));

                        $a_Name = explode(" ", trim($a_Causal[0]));

                        $Name = str_replace($a_Name[0], "", trim($a_Causal[0]));
                        if (strpos($Amount, ".") === false && strpos($Amount, ",") === false) {
                            $Amount = $Amount . ".00";
                        } else {

                            $Amount = str_replace(",", ".", $Amount);

                        }


                        if (strpos($Causal, 'CRON') !== false) {
                            $aId = explode("CRON", trim($Causal));

                        } else {
                            $aId = explode("RI1", trim($Causal));
                        }


                        $pos_StartNumber = -1;
                        $pos_EndNumber = 0;

                        if (isset($aId[1])) {
                            $str_Clean = str_replace($a_FindStr, $a_ReplaceStr, $aId[1]);


                            for ($i = 0; $i < strlen($str_Clean); $i++) {

                                if (is_numeric(substr($str_Clean, $i, 1))) {
                                    if ($pos_StartNumber < 0) {
                                        $pos_StartNumber = $i;
                                    }
                                } else {
                                    if ($pos_StartNumber >= 0) {
                                        $pos_EndNumber = $i;
                                        break;
                                    }
                                }

                            }
                            $n_Start = $pos_StartNumber;
                            $n_Lenght = $pos_EndNumber - $pos_StartNumber;

                            if ($n_Lenght == 0) $n_Lenght = 1;

                            $Id = substr($str_Clean, $n_Start, $n_Lenght);
                        } else $str_Clean = "";


                        if ($Id == "" || $Id == 0 || $Id == "/") {
                            $Id = 0;
                        } else {
                            $fines = $rs->Select('V_FineTrespasser', "(TrespasserTypeId=1 OR TrespasserTypeId=11) AND CityId='" . $_SESSION['cityid'] . "' AND ProtocolId=" . $Id, "ProtocolYear DESC");
                            $FindNumber = mysqli_num_rows($fines);

                            $chkFine = '';
                            $chkPayment = '';
                            $chkTrespasser = '';
                            $chkAmount = '';

                            if ($FindNumber == 0) {
                                $Id = 0;
                            } else {
                                $trespasser = mysqli_fetch_array($fines);
                                $Id = $trespasser['FineId'];
                                $ProtocolYear = $trespasser['ProtocolYear'];
                                $payments = $rs->Select('FinePayment', "CityId='" . $_SESSION['cityid'] . "' AND FineId=" . $Id . " AND TableId=" . $TableId);
                                $FindNumber = mysqli_num_rows($payments);

                                if ($FindNumber > 0) {

                                    $Id = 0;
                                } else {

                                    if ($trespasser['Genre'] == 'D') {
                                        $a_TrespasserCompany = explode(" ", $trespasser['CompanyName']);
                                        $str_TrespasserFind = $a_TrespasserCompany[0];
                                    } else $str_TrespasserFind = $trespasser['Surname'];

                                    if (strpos($Causal, $str_TrespasserFind) === false) {
                                        if (strpos($Causal, $trespasser['VehiclePlate']) === false) {
                                            $Id = 0;

                                        }
                                    }
                                }
                            }
                        }
                    }


                    // morelli choice
                    //$Id = 0;


                    //contolle se importo diverso o se ci sono anche pagamenti già presenti
                    $a_Payment = array(
                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $Id, 'settype' => 'int'),
                        array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                        array('field' => 'BankMgmt', 'selector' => 'value', 'type' => 'int', 'value' => $BankMgmt, 'settype' => 'int'),
                        array('field' => 'PaymentTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $PaymentTypeId, 'settype' => 'int'),
                        array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $rs->conn->real_escape_string(utf8_decode(trim($Name)))),
                        array('field' => 'ImportationId', 'selector' => 'value', 'type' => 'int', 'value' => $ImportationId, 'settype' => 'int'),
                        array('field' => 'PaymentDate', 'selector' => 'value', 'type' => 'date', 'value' => DateInDB($PaymentDate)),
                        array('field' => 'CreditDate', 'selector' => 'value', 'type' => 'date', 'value' => DateInDB($CreditDate)),
                        array('field' => 'TableId', 'selector' => 'value', 'type' => 'int', 'value' => $TableId, 'settype' => 'int'),
                        array('field' => 'Amount', 'selector' => 'value', 'type' => 'flt', 'value' => $Amount, 'settype' => 'flt'),
                        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => $rs->conn->real_escape_string(utf8_decode(trim($Causal)))),
                        array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                        array('field' => 'RegTime', 'selector' => 'value', 'type' => 'str', 'value' => date("H:i")),
                        array('field' => 'UserId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),

                    );

                    if ($Id > 0) {
                        //Controllo se l'articolo del verbale prevede pagamento ridotto
                        $rs_Reduced = $rs->Select('V_FineTariff', "FineId=$Id AND ReducedPayment > 0");
                        $ReminderDate = $rs->getArrayLine($rs->Select("Fine", "Id=$Id"))['ReminderDate'] ?? null;
                        
                        //Se si fa lo scorporo per l'importo ridotto (0) altrimenti per il normale (1)
                        $a_Fee = separatePayment($FinePaymentSpecificationType, (mysqli_num_rows($rs_Reduced) > 0 ? 0 : 1), false, $Amount, $Id, $_SESSION['cityid'], $ProtocolYear, DateInDB($PaymentDate), $ReminderDate);

                        $a_Payment[] = array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['Fee'], 'settype' => 'flt');
                        $a_Payment[] = array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['ResearchFee'], 'settype' => 'flt');
                        $a_Payment[] = array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['NotificationFee'], 'settype' => 'flt');
                        $a_Payment[] = array('field'=>'PercentualFee','selector'=>'value','type'=>'flt','value'=>$a_Fee['PercentualFee'],'settype'=>'flt');
                        $a_Payment[] = array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CustomerFee'], 'settype' => 'flt');
                        $a_Payment[] = array('field' => 'CanFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CanFee'], 'settype' => 'flt');
                        $a_Payment[] = array('field' => 'CadFee', 'selector' => 'value', 'type' => 'flt', 'value' => $a_Fee['CadFee'], 'settype' => 'flt');

                        $n_FineAssigned++;
                        $f_TotalFineAssigned += $Amount;
                    } else {

                        $n_FineNotAssigned++;
                        $f_TotalFineNotAssigned += $Amount;
                    }


                    $a = $rs->Insert('FinePayment', $a_Payment);


                }
            }
        }
        fclose($file);
    }
}
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