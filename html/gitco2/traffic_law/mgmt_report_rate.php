<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");


$Id = CheckValue('Id', 'n');
$PaymentRateId =  CheckValue('payment_rate_id', 'n');
$PrintType = CheckValue('PrintType', 'n');




$payment_exists = $rs->SelectQuery("SELECT * FROM PaymentRate WHERE Id=" . $PaymentRateId);
$payment_rate = mysqli_fetch_array($payment_exists);

if (!is_dir("./doc/national/rate/".$_SESSION['cityid']."/")) {
    mkdir("./doc/national/rate/".$_SESSION['cityid']."/", 0777);
}
if (!is_dir("./doc/national/rate/".$_SESSION['cityid']."/".$payment_rate['FineId'])) {
    mkdir("./doc/national/rate/".$_SESSION['cityid']."/".$payment_rate['FineId'], 0777);
}
if($_POST){
    echo "qui";
    $res = $_POST['email_body'];
    require __DIR__ . '/vendor/autoload.php';
    $mpdf = new \Mpdf\Mpdf();
    $form = mb_convert_encoding($res, 'UTF-8', 'UTF-8');
    $mpdf->WriteHTML($form);

    $mpdf->Output("./doc/national/rate/".$_SESSION['cityid']."/".$payment_rate['FineId']."/provvisoria_rate.pdf", \Mpdf\Output\Destination::FILE);
    $mpdf->Output("provvisoria_rate.pdf", \Mpdf\Output\Destination::DOWNLOAD);
} else {
    if (isset($_GET['delete'])) {
        $rs->ExecuteQuery("DELETE FROM PaymentRate WHERE Id=" . $PaymentRateId);
        $rs->ExecuteQuery("DELETE FROM PaymentRateNumber WHERE PaymentRateId=" . $PaymentRateId);
        header('Location:mgmt_fine_upd.php' . $str_GET_Parameter . '&Id=' . $_GET['Id']);
    } else {

        $payment_rates = $rs->SelectQuery("SELECT * FROM PaymentRateNumber WHERE PaymentRateId=" . $payment_rate['Id']);
        $payment_trespasser = $rs->Select('Trespasser', "Id=" . $payment_rate['TrespasserId']);


        $Trespasser = mysqli_fetch_array($payment_trespasser);
        $rate_number = mysqli_num_rows($payment_rates);


        $payment_body = '<table>';
        $total = 0;

        while ($payment = mysqli_fetch_array($payment_rates)) {
            $total += $payment['Amount'];
            $payment_body .= '<tr><td style="width: 6rem"><strong>- Rata ' . $payment['RateNumber'] . '</strong></td>';
            $payment_body .= '<td style="width: 10rem"><strong>' . $payment['Amount'] . ' Euro</strong></td>';
            $payment_body .= '<td style="width: 5rem"><strong>Scadenza</strong></td>';
            $payment_body .= '<td style="width: 7rem"><strong>' . DateOutDB($payment['PaymentDate']) . '</strong></td></tr>';
            '&nbsp;&nbsp;&nbsp;' . $payment['Amount'] . ' Euro       Scadenza  ' . DateOutDB($payment['PaymentDate']);
        }
        $payment_body .= '</table>';

        $Registry = $rs->SelectQuery("SELECT * FROM sarida.City WHERE City.Id='" . $_SESSION['cityid'] . "'");
        $Registry = mysqli_fetch_array($Registry);
        $customer = $rs->SelectQuery("SELECT * FROM Customer WHERE CityId='" . $_SESSION['cityid'] . "'");
        $customer = mysqli_fetch_array($customer);
        $form = $rs->SelectQuery("SELECT * FROM Form WHERE FormTypeId=50 AND CityId='" . $_SESSION['cityid'] . "' AND LanguageId=1");

        if (mysqli_num_rows($form) < 1) {
            header("Location: mgmt_report_upd.php" . $str_GET_Parameter . '&answer=Non esiste una forma&Id=' . $payment_rate['FineId']);
            die;
        }

        $form = mysqli_fetch_array($form);

        $fine = $rs->SelectQuery("SELECT * FROM Fine WHERE Id=" . $payment_rate['FineId']);
        $fine = mysqli_fetch_array($fine);

        $province = $rs->SelectQuery("SELECT * FROM sarida.Province WHERE Id=" . $Registry['ProvinceId']);
        $province = mysqli_fetch_array($province);
        $form = $form['Content'];

        $header = '
            <hr>
                <table>
                    <tr>
                        <td>
                            <img src="'.$_SESSION['blazon'].'" style="width: 40px; height: 50px">
                        </td>
                        <td colspan="4">Comune di ' . $customer['ManagerCity'] . ' 
                        <br>
            Provincia di ' . $province['Title'] . ' <br>
            ' . $customer['ManagerAddress'] . ' - ' . $customer['ManagerZIP'] . ' ' .$customer['ManagerCity'] . ' (' . $customer['ManagerProvince'] . ')<br>
            P.I.:' . $customer['ManagerVAT'] . ' - C.F.:' . $customer['ManagerTaxCode'] . '<br>
            Tel: ' . $customer['ManagerPhone'] . ' - Fax: ' . $customer['ManagerFax'] . '<br></td></tr></table><hr>';

        $body = '<div style="float: left"><p>CODICE UTENTE: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $Trespasser['Code'] . ' / ' . $Trespasser['CustomerId'] . '</p></div>
<div style="float: right; text-align: left; width: 300px;">Spett.le ' . $Trespasser['CompanyName'] . ' ' . $Trespasser['Surname'] . ' ' . $Trespasser['Name'] . '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $Trespasser['Address'] . '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $Trespasser['ZIP'] . ' ' . $Trespasser['City'] . ' (' . $Trespasser['Province'] . ')</div><br>';
        $body2 = '<div style="float: left"><p>CODICE UTENTE: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $Trespasser['Code'] . ' / ' . $Trespasser['CustomerId'] . '</p></div>
<div style="float: right; text-align: left; width: 300px;">Spett.le Comune di ' . $Registry['Title'] . '<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $customer['ManagerAddress'] . '<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .  $customer['ManagerZIP'] .' '. $customer['ManagerCity'] .' (' . $customer['ManagerProvince'] . ')<br></div><br><br>';

        $form = explode('<page-break>', $form);

        foreach ($Trespasser as $key => $value) {
            if(strpos(strtolower($key),"date")!==false)
                $value = DateOutDB($value);
            $form[0] = str_replace('{' . $key . '}', $value, $form[0]);
            $form[1] = str_replace('{' . $key . '}', $value, $form[1]);
        }

        foreach ($fine as $key => $value) {
            if(strpos(strtolower($key),"date")!==false)
                $value = DateOutDB($value);
            $form[0] = str_replace('{' . $key . '}', $value, $form[0]);
            $form[1] = str_replace('{' . $key . '}', $value, $form[1]);
        }

        $form[0] = str_replace('{Payment}', $payment_body, $form[0]);
        $form[0] = str_replace('{RegDate}', DateOutDB($payment_rate['RegDate']), $form[0]);
        $form[0] = str_replace('{RateNumber}', $rate_number, $form[0]);
        $form[0] = str_replace('{Nominativo}', $payment_rate['RateName'], $form[0]);


        $form[0] = str_replace('{Position}', $payment_rate['Position'], $form[0]);

        $form[1] = str_replace('{Payment}', $payment_body, $form[1]);
        $form[1] = str_replace('{RegDate}', DateOutDB($payment_rate['RegDate']), $form[1]);
        $form[1] = str_replace('{RateNumber}', $rate_number, $form[1]);
        $form[1] = str_replace('{Total}', $total, $form[1]);

        require __DIR__ . '/vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
        ]);
        $mpdf->setAutoTopMargin = 'stretch';

        if ($_GET['form'] == 1) {
            $form = $header . $body . $form[0];
        } else {
            $form = $header . $body2 . $form[1];
        }

        if ($PrintType == 0) {
            include(INC . "/header.php");
            require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');
            ?>
            <div class="col-md-12">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <form method="post" action="" accept-charset="UTF-8" enctype='multipart/form-data'>
                        <textarea id="note_verbali" name="email_body" rows="50"
                                  class="form-control"><?php echo utf8_encode($form); ?></textarea>
                        <div class="col-sm-12">
                            <div class="col-sm-12 BoxRow" style="height:6rem;">
                                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                                    <input class="btn btn-default" type="submit" id="sendmail" name="sendmail"
                                           value="Stampa"/>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-2"></div>
            </div>
            <script type="text/javascript">
                var pec = CKEDITOR.replace('note_verbali', {
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
            </script>

            <?php
        } else {
            $mpdf->WriteHTML($form);
            $mpdf->Output("./doc/national/rate/" . $_SESSION['cityid'] . "/" . $payment_rate['FineId'] . "/rate.pdf", \Mpdf\Output\Destination::FILE);
            $mpdf->Output("rate.pdf", \Mpdf\Output\Destination::DOWNLOAD);
        }

    }
}