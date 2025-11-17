<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(TCPDF . "/tcpdf.php");
require_once(CLS."/cls_pdf.php");
require_once(TCPDF . "/fpdi.php");
require_once(CLS."/cls_message.php");
$Filters = CheckValue('Filters', 's');
$hasPrivateKey=hasPrivateKey($_SESSION['userid']);
if (!is_dir(TOSIGN_FOLDER))
    mkdir(TOSIGN_FOLDER, 0777);
if (!is_dir(SIGNED_FOLDER . "/" . $_SESSION['cityid']))
    mkdir(SIGNED_FOLDER . "/" . $_SESSION['cityid'], 0777);
$rs= new CLS_DB();
$message=new CLS_MESSAGE();
$nationalFolder=NATIONAL_FINE . "/" . $_SESSION['cityid'] . '/';
$foreignFolder=FOREIGN_FINE."/" . $_SESSION['cityid'] . '/';
$fineId=$_POST['FineId'];
$rs_fine=$rs->ExecuteQuery("select f.* from Fine f where f.id not in (select FineId from FineHistory fh where fh.NotificationTypeId=13 and CityId='    {$_SESSION['cityid']}')");
$fine=mysqli_fetch_array($rs_fine);
if($fine['CountryId']=='Z000')
  $folder=$nationalFolder.$fineId."/";
  else
    $folder=$foreignFolder.$fineId."/";
    
$rs_fineDocumentation=$rs->Select("FineDocumentation", "FineId=$fineId and DocumentationTypeId=2");
while($fineDocumentation=mysqli_fetch_array($rs_fineDocumentation)){
    if($hasPrivateKey){
            $filename=$fineDocumentation['Documentation'];
            $signedFilename=str_replace(".", "_signed.", $filename);
            digitalSign($folder.$filename, $folder.$signedFilename, $_GET['password'],'Firma verbale' );
            $aInsert = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $fineId, 'settype' => 'int'),
                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $signedFilename),
                array('field' => 'DocumentationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 3),
            );
            $rs->Insert('FineDocumentation', $aInsert);
            $message->addInfo("Documento $filename del verbale $fineId firmato correttamente");
        }
    else {
            $filename = $fineDocumentation['Documentation'];
            $path = NATIONAL_FINE . "/" . $_SESSION['cityid'] . "/" . $fineDocumentation['FineId'] . "/" . $filename;
            $pathToSign = TOSIGN_FOLDER . "/" . $_SESSION['cityid'] . "/" . $filename;
            if (!copy($path, $pathToSign))
                trigger_error("Copia file da $path a $pathToSign fallita", E_USER_WARNING);
            $message->addInfo("Documento $filename del verbale $fineId pronto per essere scaricato e firmato");
            $aInsert = array(
                array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 13, 'settype' => 'int'),
                array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['cityid']),
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $fineId, 'settype' => 'int'),
                array('field' => 'CustomerFee', 'selector' => 'value', 'type' => 'flt', 'value' => 0.0, 'settype' => 'flt'),
                array('field' => 'NotificationFee', 'selector' => 'value', 'type' => 'flt', 'value' => 0.0, 'settype' => 'flt'),
                array('field' => 'ResearchFee', 'selector' => 'value', 'type' => 'flt', 'value' => 0.0, 'settype' => 'flt'),
                array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => date("Y-m-d")),
                array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $filename),
            );
            $rs->Insert("FineHistory",$aInsert);
        }
}
$_SESSION['Message'] = $message->getMessagesString();
header("location: mgmt_digital_sign.php$Filters");
?>