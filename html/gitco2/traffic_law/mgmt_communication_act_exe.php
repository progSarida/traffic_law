<?php
include ("_path.php");
include (INC . "/parameter.php");
include (CLS . "/cls_db.php");
include (INC . "/function.php");
require (INC . "/initialization.php");
include(CLS."/cls_message.php");
$Id = CheckValue('Id', 'n');
$rs_FineCommunication = $rs->Select('FineCommunication', "FineId=" . $Id . " AND (TrespasserTypeId =1 OR TrespasserTypeId=3) AND CommunicationStatus=5");
$message=new CLS_MESSAGE();
if (mysqli_num_rows($rs_FineCommunication) == 1)
  {
  $a_FineCommunication = array(array('field' => 'CommunicationStatus','selector' => 'value','type' => 'int','value' => 9,'settype' => 'int'));
  $rs->Update('FineCommunication', $a_FineCommunication, "FineId=" . $Id . " AND (TrespasserTypeId =1 OR TrespasserTypeId=3) AND CommunicationStatus=5");
      $message->addInfo("Possibile caricare richiesta MCTC per questa decurtazione!");
  $_SESSION['Message'] = "Possibile caricare richiesta MCTC per questa decurtazione!";
  }
else{
    $rs_fine=$rs->Select("Fine","Id=$Id");
    $fine=mysqli_fetch_array($rs_fine);
    $message->addError("Problemi con il verbalie {$fine['ProtocolId']}/{$fine['ProtocolYear']}");
}
$_SESSION['Message'] = $message->getMessagesString();
header("location: " . impostaParametriUrl(array("Filter" => 1), "mgmt_communication.php" . $str_GET_Parameter));