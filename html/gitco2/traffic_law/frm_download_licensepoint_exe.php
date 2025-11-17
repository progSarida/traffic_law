<?php
require ("_path.php");
require (INC . "/parameter.php");
require (CLS . "/cls_db.php");
require (CLS . "/cls_message.php");
require (INC . "/function.php");
include (INC . "/header.php");
require (INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');
ini_set('max_execution_time', 30000);


$ftp_connection = false;

$str_FileName = "LicensePoint_" . date("Y-m-d_H-i") . "_DOWNLOAD_" . $_SESSION['cityid'];
if (PRODUCTION)
  {
  $server = $r_Customer['LicensePointFtpServer'];
  $username = $r_Customer['LicensePointFtpUser'];
  $password = $r_Customer['LicensePointFtpPassword'];
  $conn = @ftp_connect($server);
  if (! $conn)
    {

    $output = shell_exec('sudo '.PERCORSO_VPN.' > /dev/null 2>/dev/null &');
    sleep(3);
    }
  $conn = @ftp_connect($server);
  if ($conn)
    {
    $login = @ftp_login($conn, $username, $password);
    ftp_pasv($conn, FTP_PASSIVE_MODE_MCTC);
    if ($login)
      {
      $ftp_connection = true;
      $str_Connection = '
            <div class="table_caption_H col-sm-12 alert-success">
                Connessione riuscita
            </div>';
      }
    else
      {
          trigger_error("Login fallita a $server con $username $password",E_USER_WARNING);
      $str_Connection = '
            <div class="table_caption_H col-sm-12 alert-danger">
                Tentativo di login fallito
            </div>';
      }
    }
  else
    {
    $str_Connection = '
    <div class="table_caption_H col-sm-12 alert-danger">
        Tentativo di connessione fallito
    </div>';
    trigger_error("Connessione fallita a $server",E_USER_WARNING);
    }
  $str_out .= '
        <div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-12">CONNESSIONE FTP</div>
				<div class="clean_row HSpace4"></div>
                ' . $str_Connection . '
			    <div class="clean_row HSpace4"></div>';
  if ($ftp_connection)
    {
        trigger_error("Connesso a $server con $username $password",E_USER_WARNING);

    $path = "/";
    $str_out .= '
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-12">CONTROLLO RICHIESTE PENDENTI</div>
				<div class="clean_row HSpace4"></div>
			</div>';
    $file = "PUNTI-I";

        $check_file_exist = $path . $file;
        trigger_error("Cerco file a $check_file_exist",E_USER_NOTICE);

        $contents_on_server = ftp_nlist($conn, $path);
    if (in_array($check_file_exist, $contents_on_server))
      {
      $str_out .= '
            <div class="table_caption_H col-sm-12 alert-danger">
                File richiesta ancora presente sul server
            </div>
             <div class="clean_row HSpace4"></div>';
      echo $str_out;
      DIE();
      }
    else
      {
      $file = "PUNTI-O";
      $check_file_exist = $path . $file;
      $contents_on_server = ftp_nlist($conn, $path);
      if (in_array($check_file_exist, $contents_on_server))
        {
        $str_FileName = "LicensePoint_" . date("Y-m-d_H-i") . "_DOWNLOAD_" . $_SESSION['cityid'];
        $download = @ftp_get($conn, LICENSE_POINT . "/" . $str_FileName, $file, FTP_BINARY);
            trigger_error("File scaricato $download",E_USER_NOTICE);

            if ($download)
          {
          $str_out .= '
                    <div class="table_caption_H col-sm-12 alert-success">
                        FILE PUNTI scaricato correttamente
                    </div>
                    <div class="clean_row HSpace4"></div>';
          }
        else
          {
          $str_out .= '
                    <div class="table_caption_H col-sm-12 alert-danger">
                        Problemi nel download FILE DATI motorizzazione
                    </div>
                    <div class="clean_row HSpace4"></div>';
          echo $str_out;
          DIE();
          }
        }
      else
        {
        $str_out .= '
            <div class="table_caption_H col-sm-12 alert-success">
                Nessun file per l\'importazione presente
            </div>';
        echo $str_out;
        DIE();
        }
      }
    }
  }
$a_LicensePointMex = getLicensePointCodeMex($rs);
$str_out .= '
            <div class="table_label_H col-sm-12">SCARICAMENTO ESITO PUNTI</div>
            <div class="clean_row HSpace4"></div>';
$n_TotalRecord = 0;
$n_TotalPositive = 0;
$n_TotalNegative = 0;
if (PRODUCTION)
  $f_ImportFile = fopen(LICENSE_POINT . "/" . $str_FileName, "r+");
else
  $f_ImportFile = fopen(ROOT . "/doc/LicensePoint_DOWNLOAD", "r+");
$rs->Start_Transaction();
trigger_error("Letto file $str_FileName", E_USER_NOTICE);
if (! $f_ImportFile)
  {
  $message = new CLS_MESSAGE();
  $message->addError("File $str_FileName non trovato.");
  $str_out .= $message->getMessagesString();
  echo $str_out;
  die();
  }
$ReducedDate = date("Y-m-d");

while (! feof($f_ImportFile))
  {
  $content = fgets($f_ImportFile);
  $str_Letter = substr($content, 0, 1);
  $LicensePointCode = substr($content, 1, 4);
  trigger_error("Letta riga $content", E_USER_NOTICE);
  switch ($str_Letter)
    {
    case "T":
      if ($LicensePointCode == "0000")
        {
        $str_out .= '<div class="table_label_H col-sm-12">Acquisizione file: ' . $a_LicensePointMex[$LicensePointCode] . '</div>
                        <div class="clean_row HSpace4"></div>';
        break;
        }
      else
        {
        $str_out .= '<div class="table_label_H col-sm-12 alert-danger">Acquisizione file: ' . $a_LicensePointMex[$LicensePointCode] . '</div>
                        <div class="clean_row HSpace4"></div>	';
        echo $str_out;
        DIE();
        }
    case "C":
      if ($LicensePointCode == "0000")
        {
        $str_out .= '<div class="table_label_H col-sm-12">Fine lettura file: ' . $a_LicensePointMex[$LicensePointCode] . '</div>
                        <div class="clean_row HSpace4"></div>';
        }
      else
        {
        $str_out .= '<div class="table_label_H col-sm-12 alert-danger">Fine lettura file: ' . $a_LicensePointMex[$LicensePointCode] . '</div>
                        <div class="clean_row HSpace4"></div>';
        }
      break;
    default:
      $str_FineData = $rs->conn->real_escape_string(substr($content, 250, 20));
      $str_Surname = $rs->conn->real_escape_string(trim(substr($content, 5, 32)));
      $str_Name = $rs->conn->real_escape_string(trim(substr($content, 38, 34)));
      $a_FineData = explode("-", trim($str_FineData));
      $arr_Protocol = explode("/", trim($a_FineData[count($a_FineData)-1]));
      $str_Protocol = $arr_Protocol[0];
      $str_Year = $arr_Protocol[1];
      trigger_error("Find fine with  protocol $str_Protocol/$str_Year and CityId {$_SESSION['cityid']} for $str_Surname $str_Name", E_USER_NOTICE);

      $rs_fine = $rs->ExecuteQuery("SELECT f.*
FROM Fine f LEFT JOIN FineCommunication ft ON(ft.FineId=f.Id) JOIN Trespasser t ON (t.Id=ft.TrespasserId)
WHERE f.CityId = '{$_SESSION['cityid']}' AND f.ProtocolYear ='$str_Year' AND f.ProtocolId ='$str_Protocol' AND t.Surname ='$str_Surname' AND t.Name ='$str_Name' AND f.StatusTypeId NOT IN(33,35,36);");
      //aggiungere filtro su regolamento CDS
      if ($fine = mysqli_fetch_array($rs_fine))
        {
        $n_LicensePoint = (int) substr($content, 473, 2);
        $FineId = $fine['Id'];
        trigger_error("Found fine $FineId $n_PointType $n_LicensePoint $LicensePointCode", E_USER_NOTICE);
        $str_Css = '';
        $str_Point = '';
        $n_TotalRecord ++;
        if ($LicensePointCode == "0000")
          {
          if ($str_Letter == "2")
            {
            $n_TotalPositive ++;
            $str_Point = " - punti aggiunti " . $n_LicensePoint;
            updateCommunicationStatus($rs, $FineId, 0, $LicensePointCode, 0, null, 9);
            }
          else
            {
            $n_TotalPositive ++;
            $str_Point = " - punti decurtati " . $n_LicensePoint;
            updateCommunicationStatus($rs, $FineId, 5, $LicensePointCode, $n_LicensePoint, $ReducedDate);
            }
          }
        else
          {
          $n_TotalNegative ++;
          $str_Css = ' alert-warning';
          updateCommunicationStatus($rs, $FineId, 3, $LicensePointCode);
          }
        $str_out .= '<div class="table_caption_H col-sm-1' . $str_Css . '">' . $FineId . '</div>
                    <div class="table_caption_H col-sm-2' . $str_Css . '">' . $str_Protocol . '</div>
                    <div class="table_caption_H col-sm-9' . $str_Css . '">' . $a_LicensePointMex[$LicensePointCode] . $str_Point . '</div>
                    <div class="clean_row HSpace4"></div>';
        break;
        }
    }
  }
$str_out .= '<div class="table_caption_H col-sm-4">RECORD PRESENTI</div>
        <div class="table_caption_H col-sm-8">' . $n_TotalRecord . '</div>
        <div class="clean_row HSpace4"></div>
        <div class="table_caption_H col-sm-4">RECORD ELABORATI</div>
        <div class="table_caption_H col-sm-8">' . $n_TotalPositive . '</div>
        <div class="clean_row HSpace4"></div>
        <div class="table_caption_H col-sm-4">ANOMALIE</div>
        <div class="table_caption_H col-sm-8">' . $n_TotalNegative . '</div>
        <div class="clean_row HSpace4"></div>';
$delete = @ftp_delete($conn, $file);
if ($delete || ! PRODUCTION)
  {
  $str_out .= '<div class="table_caption_H col-sm-12 alert-success">
            FILE PUNTI cancellato correttamente dal server
        </div>
        <div class="clean_row HSpace4"></div>';
  $rs->End_Transaction();
  }
else
  {
  $str_out .= '<div class="table_caption_H col-sm-12 alert-danger">
            Problemi nella cancellazione dl FILE PUNTI su server
        </div>
        <div class="clean_row HSpace4"></div>';
  $rs->Rollback();
  }
echo $str_out;

if (PRODUCTION)
  ftp_close($conn);
