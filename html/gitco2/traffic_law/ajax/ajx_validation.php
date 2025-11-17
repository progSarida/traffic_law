<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
$rs = new CLS_DB();

if(isset($_GET['PEC'])){
    $i=0;
    while (true){
        if(isset($_POST['trespasser_id_'.$i])) {
            $TrespasserId = $_POST['trespasser_id_'.$i];$i++;
            $Trespasser = $rs->SelectQuery("SELECT * FROM Trespasser WHERE Id='$TrespasserId'");
            $Trespasser = mysqli_fetch_array($Trespasser);
            $CityId = $Trespasser['CityId'];

            if($Trespasser['Mail'] == '') {
                header('Location: ../mgmt_validation_notifiche.php?message=Email errato'); die;
            }

            $Registry = $rs->SelectQuery("SELECT RegistryOfficeAddress, RegistryOfficeFax, RegistryOfficeNumber, RegistryOfficePhone, RegistryOfficeZIP, RegistryOfficePEC FROM sarida.City WHERE Id='$CityId'");
            if(mysqli_num_rows($Registry) == 0) {
                $Registry = $rs->SelectQuery("SELECT RegistryOfficeAddress, RegistryOfficeFax, RegistryOfficeNumber, RegistryOfficePhone, RegistryOfficeZIP, RegistryOfficePEC FROM sarida.City WHERE Title LIKE '%".$Trespasser['City']."%'");
                if(mysqli_num_rows($Registry) == 0 || mysqli_num_rows($Registry) > 1) {
                    header('Location: ../mgmt_validation_notifiche.php?message=Trasgressore non italiano o senza dati'); die;
                }
            }
            $Registry = mysqli_fetch_array($Registry);

            $form = $rs->SelectQuery("SELECT * FROM traffic_law.Form WHERE FormTypeId=100 AND CityId='".$_SESSION['cityid']."' AND LanguageId=1");
            if(mysqli_num_rows($form) == 0) {
                header('Location: ../mgmt_validation_notifiche.php?message=Non esiste una forma'); die;
            }
            $form = mysqli_fetch_array($form);
            $form = $form['Content'];

            foreach ($Trespasser as $key => $value){
                $form = str_replace('{'.$key.'}', $value, $form);
            }
            foreach ($Registry as $key => $value){
                $form = str_replace('{'.$key.'}', $value, $form);
            }
            require '../../vendor/autoload.php';
            $mpdf = new \Mpdf\Mpdf();
            $form = mb_convert_encoding($form, 'UTF-8', 'UTF-8');
            $mpdf->WriteHTML($form);
            $content = $mpdf->Output("", 'S');


            $attachment = new Swift_Attachment($content, 'PEC.pdf', 'application/pdf');

            $message = (new Swift_Message('Sarida'))
                ->setSubject('Sarida')
                ->setFrom(array('sarida@gmail.com' => 'Sarida'))
                ->setTo($Trespasser['Mail'])
                ->setBody('Here is the message itself')
                ->attach($attachment);

            $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
                ->setUsername('wikisoftsarida@gmail.com')
                ->setPassword('ujtwoneoiydeivmz');

            $mailer = new Swift_Mailer($transport);
            $mailer->send($message);
        } else break;
    }
    header('Location: ../mgmt_validation_notifiche.php');
} else if(isset($_GET['FAX'])) {
    require '../../vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf();
    $i=0;
    while (true){
        if(isset($_POST['trespasser_id_'.$i])){
            $mpdf->AddPage();
            $TrespasserId = $_POST['trespasser_id_'.$i];
            $i++;
            $Trespasser = $rs->SelectQuery("SELECT * FROM Trespasser WHERE Id='$TrespasserId'");
            $Trespasser = mysqli_fetch_array($Trespasser);
            $CityId = $Trespasser['CityId'];
            $Registry = $rs->SelectQuery("SELECT RegistryOfficeAddress, RegistryOfficeFax, RegistryOfficeNumber, RegistryOfficePhone, RegistryOfficeZIP, RegistryOfficePEC FROM sarida.City WHERE Id='$CityId'");
            if(mysqli_num_rows($Registry) == 0) {
                $Registry = $rs->SelectQuery("SELECT RegistryOfficeAddress, RegistryOfficeFax, RegistryOfficeNumber, RegistryOfficePhone, RegistryOfficeZIP, RegistryOfficePEC FROM sarida.City WHERE Title LIKE '%".$Trespasser['City']."%'");
                if(mysqli_num_rows($Registry) == 0 || mysqli_num_rows($Registry) > 1) {
                    header('Location: ../mgmt_validation_notifiche.php?message=Trasgressore non italiano o senza dati'); die;
                }
            }
            $Registry = mysqli_fetch_array($Registry);

            $form = $rs->SelectQuery("SELECT * FROM traffic_law.Form WHERE FormTypeId=100 AND CityId='".$_SESSION['cityid']."' AND LanguageId=1");
            if(mysqli_num_rows($form) == 0) {
                header('Location: ../mgmt_validation_notifiche.php?message=Non esiste una forma'); die;
            }
            $form = mysqli_fetch_array($form);
            $form = $form['Content'];

            foreach ($Trespasser as $key => $value){
                $form = str_replace('{'.$key.'}', $value, $form);
            }
            foreach ($Registry as $key => $value){
                $form = str_replace('{'.$key.'}', $value, $form);
            }
            $form = mb_convert_encoding($form, 'UTF-8', 'UTF-8');
            $mpdf->WriteHTML($form);
        } else break;
    }
    $mpdf->Output("FAX.pdf", \Mpdf\Output\Destination::DOWNLOAD);
    header('Location: ../mgmt_validation_notifiche.php');
}
if (isset($_GET['trasgressor'])) {
    $file = fopen('anagraficha_tribunale.txt', 'w');
    for($i=0; $i<$_GET['num'];$i++){
        if($_POST['trespasser_id_'.$i]!=''){
            $TrespasserId = $_POST['trespasser_id_'.$i];
            $trespasser = $rs->SelectQuery("SELECT * FROM V_Trespasser WHERE Id='$TrespasserId'");
            $trespasser = mysqli_fetch_array($trespasser);
            if($trespasser['Genre']!='D'){
                fwrite($file, '1');
                fwrite($file, getString('RICHIESTA DATI PER CDS', 40));
                fwrite($file, getString($trespasser['TaxCode'], 16));
                fwrite($file, getString($trespasser['Surname'], 40));
                fwrite($file, getString($trespasser['Name'], 40));
                fwrite($file, $trespasser['Genre']);
                $birthdate = explode('-',$trespasser['BornDate']);
                fwrite($file,$birthdate[2].$birthdate[1].$birthdate[0]);
                fwrite($file, getString($trespasser['CountryTitle'], 45));
                fwrite($file, getString($trespasser['Province'], 2));
                fwrite($file, 'A');
                fwrite($file, 'CR');
            } else {
                fwrite($file, '2');
                fwrite($file, getString('RICHIESTA DATI PER CDS', 40));
                fwrite($file, getString($trespasser['TaxCode'], 11));
                fwrite($file, getString($trespasser['CompanyName'], 150));
                fwrite($file, getString($trespasser['CityId'], 4));
                fwrite($file, getString($trespasser['CountryTitle'], 45));
                fwrite($file, getString($trespasser['Province'], 2));
                fwrite($file, 'A');
                fwrite($file, 'CR');
            }
            fwrite($file, "\n");
        }
    }
    fclose($file);
    //$file = fopen('anagraficha_tribunale.txt', 'r');
    //echo fread($file, filesize('anagraficha_tribunale.txt'));
    //fclose($file);
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename('anagraficha_tribunale.txt').'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize('anagraficha_tribunale.txt'));
    readfile('anagraficha_tribunale.txt');
    unlink('anagraficha_tribunale.txt');
} else {

    $i=0;
    while (true) {
        if (isset($_POST['fine_id_' . $i])) {

            $fine_id = $_POST['fine_id_' . $i];
            $i++;
            $aInsert = array(
                array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $fine_id, 'settype' => 'int'),
                array('field' => 'RegDate', 'selector' => 'value', 'type' => 'date', 'value' => date('m/d/Y')),
            );
            $rs->Insert('FineValidated', $aInsert);
        } else break;
    }
}

function getString($string, $length){
    $str_length = strlen($string);
    if($str_length>=$length){
        return substr($string, 0, $length);
    } else {
        for($i = 0; $i<($length-$str_length);$i++) $string .= ' ';
        return $string;
    }
}
