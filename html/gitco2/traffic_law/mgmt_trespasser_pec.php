<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
$Id = $_GET['Id'];
$query = $_GET['query'];
$rs = new CLS_DB();
$TrespasserId = CheckValue('Id', 'n');
$Trespasser = $rs->SelectQuery("SELECT * FROM Trespasser WHERE Id='$TrespasserId'");
$Trespasser = mysqli_fetch_array($Trespasser);
$CityId = $Trespasser['CityId'];

if($Trespasser['Mail'] == '') {
    header("Location: mgmt_trespasser_viw.php".$str_GET_Parameter.'&message=Email errato&Id='.$TrespasserId.'&query='.$_GET['query']); die;
}
if (isset($_POST['sendmail'])) {
    $res = $_POST['email_body'];
    require __DIR__ . '/../vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf();
    $form = mb_convert_encoding($res, 'UTF-8', 'UTF-8');
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

    header("Location: mgmt_trespasser_viw.php?Id=$Id&PageTitle=Verbali/Anagrafica&answer=l'e-mail è stata inviata con successo&query=$query");


}
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


$Registry = $rs->SelectQuery("SELECT RegistryOfficeAddress, RegistryOfficeFax, RegistryOfficeNumber, RegistryOfficePhone, RegistryOfficeZIP, RegistryOfficePEC FROM sarida.City WHERE Id='$CityId'");
if(mysqli_num_rows($Registry) == 0) {
    $Registry = $rs->SelectQuery("SELECT RegistryOfficeAddress, RegistryOfficeFax, RegistryOfficeNumber, RegistryOfficePhone, RegistryOfficeZIP, RegistryOfficePEC FROM sarida.City WHERE Title LIKE '%".$Trespasser['City']."%'");
    if(mysqli_num_rows($Registry) == 0 || mysqli_num_rows($Registry) > 1) {
        header("Location: mgmt_trespasser_viw.php".$str_GET_Parameter.'&message=Trasgressore non italiano o senza dati rezsidenza&Id='.$TrespasserId.'&query='.$_GET['query']); die;
    }
}
$Registry = mysqli_fetch_array($Registry);

$form = $rs->SelectQuery("SELECT * FROM traffic_law.Form WHERE FormTypeId=100 AND CityId='".$_SESSION['cityid']."' AND LanguageId=1");
if(mysqli_num_rows($form) == 0) {
    header("Location: mgmt_trespasser_viw.php".$str_GET_Parameter.'&message=Non esiste una forma&Id='.$TrespasserId.'&query='.$_GET['query']); die;
}
$form = mysqli_fetch_array($form);
$form = $form['Content'];

foreach ($Trespasser as $key => $value){
    $form = str_replace('{'.$key.'}', $value, $form);
}
foreach ($Registry as $key => $value){
    $form = str_replace('{'.$key.'}', $value, $form);
}
echo $str_out;
?>
    <div class="col-md-12">
        <div class="col-md-2"></div>
        <div class="col-md-8">
            <form method="post" action="" accept-charset="UTF-8" enctype='multipart/form-data'>
                <textarea  id="note_verbali" name="email_body" rows="50" class="form-control"><?php  echo utf8_encode($form); ?></textarea>
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRow" style="height:6rem;">
                        <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                            <input class="btn btn-default" type="submit" id="sendmail" name="sendmail" value="Manda Mail" />
                            <button class="btn btn-default" id="back">Indietro</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    <div class="col-md-2"></div>
    </div>
    <script type="text/javascript">
        var pec = CKEDITOR.replace('note_verbali',{
            on: {
                instanceReady: function (ev) {
                    this.dataProcessor.writer.setRules('row', {
                        closeTag: false
                    });
                }
            },
            height: 600,
            filebrowserBrowseUrl: './ckfinder/ckfinder.html',
            filebrowserImageBrowseUrl: './ckfinder/ckfinder.html?type=Images',
            removePlugins: 'sourcearea',

        });
        pec.config.allowedContent = true;
        pec.config.removePlugins = 'Source';
        $('#back').click(function () {
            window.history.back();
            return false;
        });
    </script>
