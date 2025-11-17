<?php
require_once ("_path.php");
require_once (INC . "/parameter.php");
require_once (CLS . "/cls_db.php");
require_once (INC . "/function.php");
require_once (CLS . "/cls_view.php");
require_once (CLS . "/cls_progressbar.php");
require_once (CLS . "/cls_message.php");
require_once (INC . "/initialization.php");

const MAX_LUNGHEZZA_RIGA = 492;

function createRowForLicensePointUpload(CLS_DB $rs, array $r_LicensePoint, array $r_Trespasser, $str_Customer, $ManagerCity, $ManagerProvince): array
{
    $str_FileRow = "";
    $b_Decurtation = ($r_LicensePoint['CommunicationStatus'] == 9) ? false : true;
  $b_RemovePointsCap = false;
  $a_SusAddictionalSanctionId = array(1,2,3);
    $n_PointType = ($b_Decurtation) ? 1 : 2;
    $str_FileRow = $n_PointType . "    ";
    $str_FileRow .= AddSpaceUpperStr($r_Trespasser['Surname'], 33);
    $str_FileRow .= AddSpaceUpperStr($r_Trespasser['Name'], 35);
    $str_FileRow .= (trim($r_Trespasser['BornDate']) != "") ? AddSpaceUpperStr(str_replace("-", "", $r_Trespasser['BornDate']), 8) : "        ";
    if (substr($r_Trespasser['TaxCode'], 11, 1) == "Z")
    {
        $str_FileRow .= "                                          ";
        $CountryId = substr($r_Trespasser['TaxCode'], 11, 4);
        $rs_Country = $rs->Select('Country', "Id='" . $CountryId . "'");
        $r_Country = mysqli_fetch_array($rs_Country);
        $CountryCode = $r_Country['ONUCode'];
        $str_FileRow .= AddSpaceUpperStr($CountryCode, 3);
        $str_FileRow .= AddSpaceUpperStr($r_Trespasser['BornPlace'], 22);
    }
    else
    {
        $CityId = substr($r_Trespasser['TaxCode'], 11, 4);
        $rs_City = $rs->SelectQuery("
                SELECT C.Title CityTitle, P.ShortTitle ProvinceTitle
                FROM " . MAIN_DB . ".City C JOIN " . MAIN_DB . ".Province P ON C.ProvinceId=P.Id
                WHERE C.Id = '" . $CityId . "'");
        $r_City = mysqli_fetch_array($rs_City);
        $City = strtoupper($r_City['CityTitle']);
        $Province = $r_City['ProvinceTitle'];
        $str_FileRow .= AddSpaceUpperStr($City, 40);
        if ($Province == 'PU')
            $Province = 'PS';
        else if ($Province == 'FC')
            $Province = 'FO';
        else if ($Province == 'MB' && trim($r_Trespasser['BornDate']) < '2004-06-11')
            $Province = 'MI';
        else if ($Province == 'VB' && trim($r_Trespasser['BornDate']) < '1992-04-30')
            $Province = 'NO';
        else if (trim($r_Trespasser['BornDate']) < '1992-03-06')
        {
            if ($Province == 'LO')
                $Province = 'MI';
            if ($Province == 'VV')
                $Province = 'CZ';
            if ($Province == 'PO')
                $Province = 'FI';
            if ($Province == 'LC')
                $Province = 'CO';
        }
        $str_FileRow .= AddSpaceUpperStr($Province, 2);
        $str_FileRow .= "                         ";
    }
    $City = $r_Trespasser['City'];
    $Province = $r_Trespasser['Province'];
    $ZIP = $r_Trespasser['ZIP'];
    // COMUNE DI RESIDENZA
    $str_FileRow .= AddSpaceUpperStr($City, 40);
    // SIGLA PROVINCIA RESIDENZA
    $str_FileRow .= AddSpaceUpperStr($Province, 2);
    // CAP COMUNE RESIDENZA
    $str_FileRow .= AddSpaceUpperStr($ZIP, 5);
    $Address = trim($r_Trespasser['Address']);
    $StreetNumber = trim($r_Trespasser['StreetNumber']) . trim($r_Trespasser['Ladder']) . trim($r_Trespasser['Indoor']) . trim($r_Trespasser['Plan']);
    if ($StreetNumber == "")
    {
        $n_StrLen = strcspn($Address, '0123456789');
        $StreetNumber = substr($Address, $n_StrLen);
        $Address = substr($Address, 0, $n_StrLen);
        if ($StreetNumber == "")
            $StreetNumber = "snc";
    }
    // TOPONIMO (facoltativo 5 spazi)
    $str_FileRow .= "     ";
    // INDIRIZZO RESIDENZA
    $str_FileRow .= AddSpaceUpperStr($Address, 34);
    // NUMERO CIVICO (facoltativo 6 spazi)
    $str_FileRow .= AddSpaceUpperStr($StreetNumber, 6);
    // IDENTIFICATIVO PATENTE
    // Provincia 2
    // Numero 7
    // CIN (codice identificativo) 1
    $LicenseNumber = trim($r_Trespasser['LicenseNumber']);
    $str_FileRow .= AddSpaceUpperStr($LicenseNumber, 10);
    // IDENTIFICATIVO DEL VERBALE
    $str_Id = $r_LicensePoint['ProtocolId'] . '/' . $r_LicensePoint['ProtocolYear'];
    $str_FileRow .= AddSpaceUpperStr($str_Id, 20);
    // DESCRIZIONE ENTE RILEVATORE
    $str_FileRow .= AddSpaceUpperStr($str_Customer, 45);
    // DESCRIZIONE COMUNE ENTE RILEVATORE
    $str_FileRow .= AddSpaceUpperStr($ManagerCity, 40);
    // PROVINCIA ENTE RILEVATORE
    $str_FileRow .= AddSpaceUpperStr($ManagerProvince, 2);
    // DATA CONTESTAZIONE INFRAZIONE : AAAAMMGG
    $str_FileRow .= (trim($r_LicensePoint['FineDate']) != "") ? AddSpaceUpperStr(str_replace("-", "", $r_LicensePoint['FineDate']), 8) : "        ";
    // DATA DEFINIZIONE DEL VERBALE
    if ($r_LicensePoint['FineTypeId'] > 2)
    {
        $str_FileRow .= (trim($r_LicensePoint['NotificationDate']) != "") ? AddSpaceUpperStr(str_replace("-", "", $r_LicensePoint['NotificationDate']), 8) : AddSpaceUpperStr(str_replace("-", "", $r_LicensePoint['FineDate']), 8);
    }
    else
    {
        if (trim($r_LicensePoint['NotificationDate']) != "")
        {
            $str_FileRow .= AddSpaceUpperStr(str_replace("-", "", $r_LicensePoint['NotificationDate']), 8);
        }
        else
        {
            $str_FileRow .= AddSpaceUpperStr(str_replace("-", "", $r_LicensePoint['PaymentDate']), 8);
        }
    }
    // ELENCO INFRAZIONI VERBALI (MAX 10 MA NOI NE POSSIAMO INSERIRE AL MASSIMO 3)
    $n_LicenseYear = $r_LicensePoint['LicenseYear'];
    $n_Point = ($n_LicenseYear >= 3) ? $r_LicensePoint['LicensePoint'] : $r_LicensePoint['YoungLicensePoint'];
    $n_TotalPoint = $n_Point;
    $str_Point = ($n_Point > 9) ? $n_Point : "0" . $n_Point;
    $str_Article = getArticleDecode($rs, $r_LicensePoint['TrespasserId'], $r_LicensePoint['ArticleId'], $r_LicensePoint['LicensePointCode1'], $r_LicensePoint['LicensePointCode2'], $r_LicensePoint['Habitual'], $r_LicensePoint['Article'],$r_LicensePoint['Paragraph'],$n_Point);
    trigger_error("Article: $str_Article",E_USER_NOTICE);
  //NEL CASO DI SPOSENSIONE, REVOCA O RITIRO PATENTE, IL LIMITE DI 15 PUNTI VIENE RIMOSSO
  if ($r_LicensePoint['SuspensionLicense'] == 1 || $r_LicensePoint['LossLicense'] == 1 || in_array($r_LicensePoint['AdditionalSanctionId'], $a_SusAddictionalSanctionId))
    {
    $b_RemovePointsCap = true;
    }
    if ($r_LicensePoint['ArticleNumber'] > 1)
    {
        $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $r_LicensePoint['Id'] . " AND LicensePoint>0");
        while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle))
        {
            $n_Point = ($n_LicenseYear >= 3) ? $r_AdditionalArticle['LicensePoint'] : $r_AdditionalArticle['YoungLicensePoint'];
            $n_TotalPoint += $n_Point;
            $str_Article .= getArticleDecode($rs, $r_LicensePoint['Article'], $r_AdditionalArticle['ArticleId'], $r_AdditionalArticle['LicensePointCode1'], $r_AdditionalArticle['LicensePointCode2'], $r_AdditionalArticle['Habitual'], $r_AdditionalArticle['Article'],$r_AdditionalArticle['Paragraph'],$n_Point);
      //NEL CASO DI SPOSENSIONE, REVOCA O RITIRO PATENTE, IL LIMITE DI 15 PUNTI VIENE RIMOSSO
      if ($r_AdditionalArticle['SuspensionLicense'] == 1 || $r_AdditionalArticle['LossLicense'] == 1 || in_array($r_AdditionalArticle['AdditionalSanctionId'], $a_SusAddictionalSanctionId))
        {
        $b_RemovePointsCap = true;
        }
        }
    }
    $str_FileRow .= AddSpaceUpperStr($str_Article, 100);
    // PUNTEGGIO TOTALE DEL VERBALE
  if ($n_TotalPoint > 15 && !$b_RemovePointsCap)
        $n_TotalPoint = 15;
    if ($b_Decurtation)
    {
        $n_TotalPointUpload += $n_TotalPoint;
    }
    else
    {
        $n_TotalPointAddUpload += $n_TotalPoint;
    }
    $str_Point = ($n_TotalPoint > 9) ? $n_TotalPoint : "0" . $n_TotalPoint;
    $str_FileRow .= AddSpaceUpperStr($str_Point, 2);
    $str_FileRow .= "      " . "          " . PHP_EOL; //aggiungo altri 10 spazi vuoti
    return array("Row" => $str_FileRow,"TotalPointUpload" => $n_TotalPointUpload,"TotalPointAddUpload" => $n_TotalPointAddUpload);
}

function getArticleDecode(CLS_DB $rs, string $trespasserId, $articleId, $licensePointCode1, $licensePointCode2, $habitual,$article,$paragraph,$point): string
{
    $str_Article="";
    $lpc = $licensePointCode1;
    if ($habitual)
    {
        $rs_habitual = $rs->ExecuteQuery("Select count( distinct f.Id) as cnt
    from Fine f
    left join FineTrespasser ft on (ft.FineId=f.Id)
    left join FineArticle fa on (f.Id=fa.FineId)
    left join FineAdditionalArticle faa on(f.Id=faa.FineId)
    left join FineNotification fn on (fn.FineId=f.Id)
    where ft.TrespasserId=$trespasserId and (faa.ArticleId=$articleId or fa.ArticleId=$articleId) and (fn.NotificationDate>DATE_ADD(CURDATE(), INTERVAL -2 YEAR) or f.FineDate>DATE_ADD(CURDATE(), INTERVAL -2 YEAR))");
        if ($fines = mysqli_fetch_array($rs_habitual))
            if ($fines['cnt'] >= 2)
                $lpc = $licensePointCode2;
    }

    $str_Point = ($point > 9) ? $point : "0" . $point;
    $str_Article .= AddSpaceUpperStr($lpc, 8) .
        AddSpaceUpperStr($str_Point, 2);

    trigger_error("License point code: $lpc", E_USER_NOTICE);
    return $str_Article;
}

ini_set('max_execution_time', 30000);
$message = new CLS_MESSAGE();
$progressBar = new CLS_PROGRESSBAR(count($_POST['checkbox']));
$cont = 1;
if ($r_Customer['LicensePointPaymentCompletion'] == 0)
    $licensepoint = new CLS_VIEW(V_LICENSEPOINT0);
else
    $licensepoint = new CLS_VIEW(V_LICENSEPOINT1);

$ftp_connection = false;
$chk_inp_file = false;
$chk_out_file = false;
$str_out = "";
$str_Customer = $r_Customer['LicensePointOffice'];
$ManagerCity = $r_Customer['ManagerCity'];
$ManagerProvince = $r_Customer['ManagerProvince'];

$MangerLicensePointCode = $r_Customer['LicensePointCode'];
$ProgressFileName = CheckValue("ProgressFile", "s");
$progressFile = TMP . "/" . $ProgressFileName;
if (PRODUCTION)
{
  $server = $r_Customer['LicensePointFtpServer'];
    $username = $r_Customer['LicensePointFtpUser'];
    $password = $r_Customer['LicensePointFtpPassword'];

    $conn = @ftp_connect($server);
    if (! $conn)
    {
        shell_exec('sudo '.PERCORSO_VPN.' > /dev/null 2>/dev/null &');
        sleep(3);
    }

    $conn = @ftp_connect($server);
    $message->addInfo("CONNESSIONE FTP");
    if ($conn)
    {
        $login = @ftp_login($conn, $username, $password);
        ftp_pasv($conn, FTP_PASSIVE_MODE_MCTC);
        if ($login)
        {
            $ftp_connection = true;
            $message->addInfo("Connessione riuscita");
        }
        else
            $message->addError("Tentativo di login fallito");
    }
    else
        $message->addError("Tentativo di connessione fallito");

    if ($ftp_connection)
    {
        $path = "/";
        $message->addInfo("CONNESSIONE FTP");
        $file = "PUNTI-I";

        $contents_on_server = ftp_nlist($conn, $path);
        if (in_array($path."/".$file, $contents_on_server))
        {
            $chk_inp_file = true;
            $message->addError("File richiesta ancora presente sul server");
        }
        else
            $message->addWarning(" Nessuna richiesta precedente presente");

        $file = "PUNTI-O";
        $contents_on_server = ftp_nlist($conn, $path);
        if (in_array($path."/".$file, $contents_on_server))
        {
            $chk_out_file = true;
            $message->addError("File pronto per l\'importazione presente");
        }
        else
            $message->addInfo("Nessun file per l\'importazione presente");

        if ($chk_out_file){
            echo json_encode(array("Esito" => "ko","Messaggio" => $message->getMessagesString()));
            die();
        }

    }
}

if (isset($_POST['checkbox']))
{
    if (! file_exists(LICENSE_POINT))
    {
        mkdir(LICENSE_POINT, 0770, true);
    }
    $FileName = "LicensePoint_" . date("Y-m-d_H-i") . "_UPLOAD_" . $_SESSION['cityid'];
    $OutFile = fopen(LICENSE_POINT . "/" . $FileName, "w") or die("Unable to open file!");
    $str_SendDate = date("Ymd");
    $str_Header = AddSpaceUpperStr("T    " . $MangerLicensePointCode . $str_SendDate, MAX_LUNGHEZZA_RIGA -1). PHP_EOL;
    trigger_error("Aggiunto header $str_Header al file LICENSE_POINT $FileName", E_USER_NOTICE);
    fwrite($OutFile, $str_Header);

    foreach ($_POST['checkbox'] as $FineId)
    {
        $rs_LicensePoint = $rs->selectQuery($licensepoint->generateSelect(" Id=" . $FineId));
        $r_LicensePoint = mysqli_fetch_array($rs_LicensePoint);
        $rs_Trespasser = $rs->Select('Trespasser', "Id=" . $r_LicensePoint['TrespasserId']);
        $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
        $result = createRowForLicensePointUpload($rs, $r_LicensePoint, $r_Trespasser, $str_Customer, $ManagerCity, $ManagerProvince);
        trigger_error("Aggiunta riga: {$result['Row']}", E_USER_NOTICE);
        $rigaRipulitaDaAsterischi = str_replace("*", " ", $result['Row']);
        fwrite($OutFile, $rigaRipulitaDaAsterischi);
        $progressBar->writeJSON($cont, $progressFile);
        $cont ++;
    }
    $n_PointType = 1;
    $str_Footer = "C    " . $str_SendDate . $n_PointType . AddSpaceUpperStr($result["TotalPointUpload"], 7);
    if ($result["TotalPointAddUpload"] > 0)
    {
        $str_Footer .= "2" . $result["TotalPointAddUpload"] . AddSpaceUpperStr($result["TotalPointAddUpload"], 7);
    }
    fwrite($OutFile, AddSpaceUpperStr($str_Footer, MAX_LUNGHEZZA_RIGA-1));
    fclose($OutFile);
    if (PRODUCTION)
    {
        $upload = @ftp_put($conn, 'PUNTI-I', LICENSE_POINT . "/" . $FileName, FTP_BINARY);
        if ($upload)
            $message->addInfo('FILE decurtazione punti caricato');
        else
        {
            $message->addError('Problemi nel caricamento FILE RICHIESTA DATI motorizzazione');
            echo json_encode(array("Esito" => "ko","Messaggio" => $message->getMessagesString()));
            ftp_close($conn);
            die();
        }
        ftp_close($conn);
    }
    else
        $message->addInfo('FILE decurtazione punti caricato');
}
echo json_encode(array("Esito" => "ok","Messaggio" => $message->getMessagesString()));
