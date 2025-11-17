<?php
include ("_path.php");
include (INC . "/parameter.php");
include (CLS . "/cls_db.php");
include (INC . "/function.php");
include (INC . "/header.php");
require (INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');
include (CLS . "/cls_view.php");
include (CLS . "/cls_message.php");

$a_SusAddictionalSanctionId = array(1,2,3);

$message = new CLS_MESSAGE();
if ($r_Customer['LicensePointPaymentCompletion'] == 0)
  $licensepoint = new CLS_VIEW(V_LICENSEPOINT0);
else
  $licensepoint = new CLS_VIEW(V_LICENSEPOINT1);

$n_RecordLimit = CheckValue('RecordLimit', 'n');
if ($n_RecordLimit == 0)
  $n_RecordLimit = 5;

$aUserButton = array("upd",'cut','trs');

$s_Limit5 = ($n_RecordLimit == 5) ? " SELECTED " : "";
$s_Limit25 = ($n_RecordLimit == 25) ? " SELECTED " : "";
$s_Limit50 = ($n_RecordLimit == 50) ? " SELECTED " : "";
$s_Limit100 = ($n_RecordLimit == 100) ? " SELECTED " : "";
$s_Limit200 = ($n_RecordLimit == 200) ? " SELECTED " : "";
$btn_search = CheckValue('btn_search', 'n');
$str_GET_Parameter.="&btn_search=$btn_search";

$minYear = mysqli_fetch_array($rs->ExecuteQuery("select min(Year) as minYear from ArticleTariff art join Article a on (a.Id=art.ArticleId) where a.CityId='" . $_SESSION['cityid'] . "'"));
if ($Search_FromNotificationDate == '')
  $Search_FromNotificationDate = "01/01/" . $minYear['minYear'];
if ($Search_ToNotificationDate == '')
  $Search_ToNotificationDate = date('d/m/Y', strtotime("-180 days"));
$str_CheckLicenseYoung1 = $str_CheckLicenseYoung2 = $str_CheckLicenseYoung0 = "";
$str_CheckLicenseHidden1 = $str_CheckLicenseHidden2 = $str_CheckLicenseHidden0 = $str_CheckLicenseHidden3 = "";
$str_CheckAnomalies1 = $str_CheckAnomalies2 = $str_CheckAnomalies0 = $str_CheckAnomalies3 = "";

if ($Search_LicenseYoung == 0)
  $str_CheckLicenseYoung0 = " CHECKED ";
else if ($Search_LicenseYoung == 2)
  $str_CheckLicenseYoung2 = " CHECKED ";
else
  $str_CheckLicenseYoung1 = " CHECKED ";

if ($Search_Anomalies == 1)
  $str_CheckAnomalies1 = "CHECKED";
else if ($Search_Anomalies == 2)
  $str_CheckAnomalies2 = "CHECKED";
else
  $str_CheckAnomalies0 = "CHECKED";

if ($Search_LicenseHidden == 1)
  $str_CheckLicenseHidden1 = " CHECKED ";
else if ($Search_LicenseHidden == 2)
  $str_CheckLicenseHidden2 = " CHECKED ";
else
  $str_CheckLicenseHidden0 = " CHECKED ";

$ftp_connection = false;
$server = $r_Customer['LicensePointFtpServer'];
$username = $r_Customer['LicensePointFtpUser'];
$password = $r_Customer['LicensePointFtpPassword'];
$expiration = !(empty($r_Customer['LicensePointFtpPasswordExpiration'])) ? new DateTime($r_Customer['LicensePointFtpPasswordExpiration']) : null;
$folder = $r_Customer['LicensePointFtpFolder'];

if ($expiration!=null) {
$diff = date_diff(new DateTime("now"), $expiration);
if ($diff->invert == 1)
  {
  $message->addError("La password per la connessione al servizio è scaduta.");
  $str_out .= $message->getMessagesString();
  echo $str_out;
  die();
  }
else if ($diff->days < 10)
  $message->addWarning("La password per la connessione al servizio scadrà il giorno {$expiration->format("d/m/Y")}");
}

if (PRODUCTION)
  {
  $conn = @ftp_connect($server);
  if (! $conn)
    {
    $output = shell_exec('sudo sh /home/ucucni/vpn_mctc.sh > /dev/null 2>/dev/null &');
    sleep(4);
    }
  $conn = @ftp_connect($server);
  $path = "/";
    $file = "PUNTI-O";
    $check_file_exist = $path. $file;

  if ($conn)
    $login = @ftp_login($conn, $username, $password);
  else
  if ($login)
      {
    if ($folder != null && $folder != "")
      {
      if (! ftp_chdir($conn, $folder))
        {
        $message->addError("La cartella $folder sul server non è raggiungibile. Contattare l'amministratore per verficare l'esistenza o i permessi di accesso.");
        $str_out .= $message->getMessagesString();
        echo $str_out;
        DIE();
        }
      }
    } else
      trigger_error("Login fallita a $server con $username $password",E_USER_WARNING);

  }
$str_out .= $message->getMessagesString();
if (empty($s_SelPlateN) && empty($s_SelPlateF))
  $s_SelPlateN = "selected";

$str_out .= '
    <div class="clean_row HSpace4"></div>

    <div class="progress" style="display:none;margin:0;">
	   <div id="progressbar" class="progress-bar progress-bar-striped progress-bar-success" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
	</div>

    <div class="row-fluid">
      <div id="message"></div>
    </div>

    <div class="clean_row HSpace4"></div>

    <form id="f_Search" action="' . $str_CurrentPage . '" method="post">
    <input type="hidden" name="btn_search" value="1">
    <div class="col-sm-12" >
        <div class="col-sm-11 BoxRow" style="height:9rem; border-right:1px solid #E7E7E7;">
            <div class="col-sm-1 BoxRowLabel">
			    Da cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
			    <input class="form-control frm_field_numeric" type="text" value="' . $Search_FromProtocolId . '" id="Search_FromProtocolId" name="Search_FromProtocolId" style="width:8rem">
			</div>
            <div class="col-sm-1 BoxRowLabel">
			    A cron
            </div>
            <div class="col-sm-1 BoxRowCaption">
			    <input class="form-control frm_field_numeric" type="text" value="' . $Search_ToProtocolId . '" id="Search_ToProtocolId" name="Search_ToProtocolId" style="width:8rem">
		    </div>
            <div class="col-sm-1 BoxRowLabel">
                Da anno
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" type="text" value="' . $Search_FromProtocolYear . '" id="Search_FromProtocolYear" name="Search_FromProtocolYear" style="width:8rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Ad anno
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_numeric" type="text" value="' . $Search_ToProtocolYear . '" id="Search_ToProtocolYear" name="Search_ToProtocolYear" style="width:8rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Da data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="' . $Search_FromFineDate . '" name="Search_FromFineDate" style="width:12rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="' . $Search_ToFineDate . '" name="Search_ToFineDate" style="width:12rem">
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-1 BoxRowLabel">
                Riferimento
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Ref" type="text" style="width:8rem" value="' . $Search_Ref . '">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nazionalità
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <select class="form-control" name="TypePlate" id="TypePlate">
                    <option></option>
                    <option value="N"' . $s_SelPlateN . '>Nazionali</option>
                    <option value="F"' . $s_SelPlateF . '>Estere</option>
                </select>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-2 BoxRowCaption">
                ' . CreateSelectConcat("SELECT DISTINCT F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY C.Title", "Search_Country", "CountryId", "Title", $Search_Country, false, 24) . '
            </div>
            <div class="col-sm-1 BoxRowCaption"></div>
<div class="col-sm-1 BoxRowLabel">
                Da data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="' . $Search_FromNotificationDate . '" name="Search_FromNotificationDate" style="width:12rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                A data notifica
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_date" type="text" value="' . $Search_ToNotificationDate . '" name="Search_ToNotificationDate" style="width:12rem">
            </div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-1 BoxRowLabel">
                Neopatentati:
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="Search_LicenseYoung" value="1" ' . $str_CheckLicenseYoung1 . '><span  style="position:relative;top:-1rem">Escludi</span>
                <input type="radio" name="Search_LicenseYoung" value="0" ' . $str_CheckLicenseYoung0 . '><span  style="position:relative;top:-1rem">Includi</span>
                <input type="radio" name="Search_LicenseYoung" value="2" ' . $str_CheckLicenseYoung2 . '><span  style="position:relative;top:-1rem">Solo loro</span>
            </div>
            
<div class="col-sm-1 BoxRowLabel">
                Esclusi:
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="Search_LicenseHidden" value="0" ' . $str_CheckLicenseHidden0 . '><span  style="position:relative;top:-1rem">Escludi</span>
                <input type="radio" name="Search_LicenseHidden" value="1" ' . $str_CheckLicenseHidden1 . '><span  style="position:relative;top:-1rem">Includi</span>
                <input type="radio" name="Search_LicenseHidden" value="2" ' . $str_CheckLicenseHidden2 . '><span  style="position:relative;top:-1rem">Solo loro</span>
            </div>


            <div class="col-sm-1 BoxRowLabel">
                Anomalie:
            </div>
                <div class="col-sm-2 BoxRowCaption">
                <input type="radio" name="Search_Anomalies" value="0" ' . $str_CheckAnomalies0 . '><span  style="position:relative;top:-1rem">Escludi</span>
                <input type="radio" name="Search_Anomalies" value="1" ' . $str_CheckAnomalies1 . '><span  style="position:relative;top:-1rem">Includi</span>
                <input type="radio" name="Search_Anomalies" value="2" ' . $str_CheckAnomalies2 . '><span  style="position:relative;top:-1rem">Solo loro</span>
            </div>
                        <div class="col-sm-1 BoxRowCaption"></div>

            <div class="col-sm-1 BoxRowLabel" >
                            Numero record
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <select name="RecordLimit" id="RecordLimit" />
                                <option value="5"' . $s_Limit5 . '>5</option>
                                <option value="25"' . $s_Limit25 . '>25</option>
                                <option value="50"' . $s_Limit50 . '>50</option>
                                <option value="100"' . $s_Limit100 . '>100</option>
                                <option value="200"' . $s_Limit200 . '>200</option>
                            </select>
                        </div>

            <div class="clean_row HSpace4"></div>
            <div class="col-sm-1 BoxRowLabel">
                Trasgressore
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string" name="Search_Trespasser" type="text" value="' . $Search_Trespasser . '">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Riattribuzione:
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input type="checkbox" name="Search_Reattribution" value="1" ' . ChkCheckButton($Search_Reattribution) . '/>
            </div>
                        
        </div>
        <div class="col-sm-1 BoxRow" style="height:7rem;">
            <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                <i class="glyphicon glyphicon-search" style="margin-top:0.6rem;font-size:3rem;"></i>
            </div>
        </div>
    </div>
    </form>
</div>
<div class="clean_row HSpace4"></div>';
if (! PRODUCTION && file_exists(ROOT . "/doc/LicensePoint_DOWNLOAD"))
  {
  $str_out .= '
            <form name="f_download" id="f_download" action="frm_download_licensepoint_exe.php' . $str_Parameters . '" method="post">
                <div class="table_caption_H col-sm-12 alert-danger" style="height:5rem; text-align:center">
                    <input type="submit" id="sub_Button" class="btn btn-success" style="width:40rem;margin-top:1rem;" value="Presente file per download punti" />
                </div>
            </form>
            ';
  }
else
  {

  $contents_on_server = ftp_nlist($conn, $path.$folder);

  if (in_array("/".$folder.$check_file_exist, $contents_on_server))
    {
    $str_out .= '
            <form name="f_download" id="f_download" action="frm_download_licensepoint_exe.php' . $str_Parameters . '" method="post">
                <div class="table_caption_H col-sm-12 alert-danger" style="height:5rem; text-align:center">
                    <input type="submit" id="sub_Button" class="btn btn-success" style="width:40rem;margin-top:1rem;" value="Presente file per download punti" />
                </div>
            </form>
            ';

    echo $str_out;
    DIE();
    }
  }

$str_out .= '
    	<div class="row-fluid">
    	<form name="f_upload" id="f_upload" action="frm_upload_licensepoint_exe.php' . $str_Parameters . '" method="post">
        	<div class="col-sm-12">
        	    <div class="table_label_H col-sm-1">Riga | Cron</div>
                <div class="table_label_H col-sm-1">Rif</div>
				<div class="table_label_H col-sm-2">Dati atto</div>
				<div class="table_label_H col-sm-2">Trasgressore</div>
				<div class="table_label_H col-sm-1">CF</div>
				<div class="table_label_H col-sm-2">Dati Patente</div>
				<div class="table_label_H col-sm-1">Articolo</div>
				<div class="table_label_H col-sm-1" style="font-size:1.3rem">Pt. da decurtare</div>
				<div class="table_label_H col-sm-1">Invia
                    <a href="mgmt_licensepoint_add.php">
                        <span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span>
                    </a>
				</div>
				<div class="clean_row HSpace4"></div>';
$n = 0;


if ($btn_search == 1)
  {
  $str_Where .= " AND CityId='{$_SESSION['cityid']}' ".createLicensePointWhere($s_TypePlate,$Search_Country,$Search_Ref,$Search_FromProtocolYear,$Search_ToProtocolYear,$Search_FromProtocolId,$Search_ToProtocolId,$Search_FromFineDate,$Search_ToFineDate,$Search_FromNotificationDate,$Search_ToNotificationDate,$Search_Anomalies,$Search_LicenseYoung,$Search_LicenseHidden,$Search_Trespasser,$Search_Reattribution);

  if ($s_TypePlate == "")
    {
    $str_out .= '<div class="table_caption_H col-sm-12">
                Scegliere tipologia targa
            </div>';
    }
  else
    {

    $strOrder = "FineDate ASC, ProtocolId ASC";
    $rs_LicensePoint = $rs->selectQuery($licensepoint->generateSelect($str_Where, null, $strOrder, $n_RecordLimit));

    $RowNumber = mysqli_num_rows($rs_LicensePoint);
    $n_ContRow = 0;
    $str_out .= '
                <input type="hidden" name="TypePlate" value="' . $s_TypePlate . '">';

    if ($RowNumber == 0)
      {
      $str_out .= '<div class="table_caption_H col-sm-12">
                    Nessun record presente
                </div>';
      }
    else
      {
          $a_LicensePointMex = getLicensePointCodeMex($rs);
      $PreviousProtocolId = 0;
      while ($r_LicensePoint = mysqli_fetch_array($rs_LicensePoint))
        {
        $n_ContRow ++;
        $b_Warning = false;
        $b_RemovePointsCap = false;
        $str_CssRow = "";
        $str_Check = "";
        $tooltip = "";
        $str_Trespasser = trim(trim($r_LicensePoint['Surname']) . ' ' . trim($r_LicensePoint['Name']));
        $fixPage = 0;
        if (trim($r_LicensePoint['ZIP']) == "" || trim($r_LicensePoint['TaxCode']) == "" || $str_Trespasser == "" || trim($r_LicensePoint['LicenseNumber']) == "" || trim($r_LicensePoint['LicenseDate']) == "" || trim($r_LicensePoint['LicenseCategory']) == "")
          {
          $b_Warning = true;
          $fixPage = 1;
          $str_CssRow = ' alert-warning';
          $tooltip = "Dati trasgressore incompleti";
          trigger_error("ZIP {$r_LicensePoint['ZIP']}, TaxCode {$r_LicensePoint['TaxCode']} Trespasser $str_Trespasser LicenseNumber {$r_LicensePoint['LicenseNumber']} LicenseDate {$r_LicensePoint['LicenseDate']} LicenseCategory {$r_LicensePoint['LicenseCategory']} ", E_USER_WARNING);
          }
        else if ($r_Customer['LicensePointPaymentCompletion'] == 0)//la data di notifica va controllata solo nel caso 0 in cui la data di notifica è obbligatoria. Nel caso 1 può bastare che ci sia un pagamento.
          {
          if (trim($r_LicensePoint['NotificationDate']) == "")
            {
            $b_Warning = true;
            $fixPage = 2;
            $tooltip = "Data notifica mancante, questo ente effettua la decurtazione dei punti solo per i verbali completati";
            $str_CssRow = ' alert-warning';
            }
          }
        if ($r_LicensePoint['CommunicationStatus'] == 3)
          {
          $b_Warning = true;
          $tooltip = "Elaborazione già avvenuta in stato anomalo: ({$r_LicensePoint['LicensePointId']}) ".$a_LicensePointMex[$r_LicensePoint['LicensePointId']];
          trigger_error("CommunicationStatus {$r_LicensePoint['CommunicationStatus']}", E_USER_WARNING);
          $str_CssRow = ' alert-danger';
          }
        else if (trim($r_LicensePoint['LicensePointCode1']) == "" && trim($r_LicensePoint['LicensePointCode2']) == "")
          {
          $b_Warning = true;
          $fixPage = 3;
          $tooltip = "Codice decurtazione punti mancante sull' articolo del verbale";
          trigger_error("LicensePointCodes {$r_LicensePoint['LicensePointCode1']} - {$r_LicensePoint['LicensePointCode2']}", E_USER_WARNING);
          $str_CssRow = ' alert-warning';
          }
        if ($r_LicensePoint['DocumentTypeId'] == 5 || $r_LicensePoint['DocumentTypeId'] == 6)
          {
          $b_Warning = true;
          $fixPage = 4;
          $tooltip = "Il trasgressore possiede un tipo di documento esente dalla decurtazione: {$r_LicensePoint['DocumentTypeTitle']}";
          trigger_error("DocumentTypeId {$r_LicensePoint['DocumentTypeId']}", E_USER_WARNING);
          $str_CssRow = ' alert-warning';
          }
        
        //NEL CASO DI SPOSENSIONE, REVOCA O RITIRO PATENTE, IL LIMITE DI 15 PUNTI VIENE RIMOSSO
        if ($r_LicensePoint['SuspensionLicense'] == 1 || $r_LicensePoint['LossLicense'] == 1 || in_array($r_LicensePoint['AddictionalSanctionId'], $a_SusAddictionalSanctionId))
          {
          $b_RemovePointsCap = true;
          }

        $n_LicenseYear = $r_LicensePoint['LicenseYear'];

        $n_Point = ($n_LicenseYear >= 3) ? $r_LicensePoint['LicensePoint'] : $r_LicensePoint['YoungLicensePoint'];

        if ($r_LicensePoint['ArticleNumber'] > 1)
          {
          $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $r_LicensePoint['Id'] . " AND LicensePoint>0");
          while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle))
            {
            $n_PointLicense = ($n_LicenseYear >= 3) ? $r_AdditionalArticle['LicensePoint'] : $r_AdditionalArticle['YoungLicensePoint'];
            $n_Point += $n_PointLicense;
            //NEL CASO DI SPOSENSIONE, REVOCA O RITIRO PATENTE, IL LIMITE DI 15 PUNTI VIENE RIMOSSO
            if ($r_AdditionalArticle['SuspensionLicense'] == 1 || $r_AdditionalArticle['LossLicense'] == 1 || in_array($r_AdditionalArticle['AddictionalSanctionId'], $a_SusAddictionalSanctionId))
              {
              $b_RemovePointsCap = true;
              }
            }
          }

        if ($n_Point > 15 && !$b_RemovePointsCap)
          {
          $n_Point = 15;
          }

        if (! $b_Warning)
          {
          $str_Check = '<input type="checkbox" name="checkbox[]" id="checkbox' . $n . '" value="' . $r_LicensePoint['Id'] . '" checked />';
          $n ++;
          $str_CssRow = '';
          }
        else
          {
              $CommunicationStatus = ($r_LicensePoint['CommunicationStatus'] == 1 || $r_LicensePoint['CommunicationStatus'] == 3) ? 0 : 1;
              $TooltipText = ($r_LicensePoint['CommunicationStatus'] == 1 || $r_LicensePoint['CommunicationStatus'] == 3) ? 'Reincludi nell\'elaborazione' : 'Escludi dall\'elaborazione';
              $Icon = ($r_LicensePoint['CommunicationStatus'] == 1 || $r_LicensePoint['CommunicationStatus'] == 3) ? 'fa-history' : 'fa-scissors';
              switch($fixPage){
                  //Modifica trasgressore
                  case 1:
                      $str_Check.=ChkButton($aUserButton, 'upd', '<a target="_blank" href="mgmt_trespasser_upd.php' . $str_GET_Parameter . '&Id=' . $r_LicensePoint['TrespasserId'] . '"><span class="tooltip-r fa fa-wrench fa-fw" title="Correggi" data-placement="top"  style="margin-top: 0.2rem;font-size: 1.8rem;"></span></a>');
                      break;
                  //Modifica comunicazione
                  case 2:
                      $str_Check.=ChkButton($aUserButton, 'upd', '<a target="_blank" href="mgmt_communication_upd.php' . $str_GET_Parameter . '&Id=' . $r_LicensePoint['Id'] . '"><span class="tooltip-r fa fa-wrench fa-fw" title="Correggi" data-placement="top"  style="margin-top: 0.2rem;font-size: 1.8rem;"></span></a>');
                      break;
                  //Ricerca articolo
                  case 3:
                      $str_Check.=ChkButton($aUserButton, 'upd', '<a target="_blank" href="tbl_article.php"><span class="tooltip-r fa fa-wrench fa-fw" title="Correggi" data-placement="top"  style="margin-top: 0.2rem;font-size: 1.8rem;"></span></a>');
                      break;
                  //Modifica comunicazione
                  case 4:
                      $str_Check.=ChkButton($aUserButton, 'upd', '<a target="_blank" href="mgmt_communication_upd.php' . $str_GET_Parameter . '&Id=' . $r_LicensePoint['Id'] . '"><span class="tooltip-r fa fa-wrench fa-fw" title="Correggi" data-placement="top"  style="margin-top: 0.2rem;font-size: 1.8rem;"></span></a>');
                      break;
                  default:
                      $str_Check.='';
              }
              $str_Check.=ChkButton($aUserButton, 'cut', '<a><span id="' . $r_LicensePoint['Id'] . '" status="' . $CommunicationStatus . '" class="tooltip-r fa '.$Icon.' fa-fw actionicon" title="'.$TooltipText.'" data-placement="top"  style="margin-top: 0.2rem;font-size: 1.8rem;"></span></a>');

          }
        if ($PreviousProtocolId == $r_LicensePoint['ProtocolId'])
          $str_Check = "";
        else
          $PreviousProtocolId = $r_LicensePoint['ProtocolId'];

        if ($str_CssRow != '')
          {
          $str_out .= '
                <div class="table_caption_H col-sm-1' . $str_CssRow . '"><i class="tooltip-r fas fa-exclamation-circle fa-fw text-danger" style="margin-top: 0.2rem;font-size: 1.4rem;" data-container="body" data-toggle="tooltip" data-placement="right" title="' . $tooltip . '"></i>
                ' . $n_ContRow . ' | '.$r_LicensePoint['ProtocolId'] . '/' . $r_LicensePoint['ProtocolYear']. '</div>';
          }
        else
          {
          $str_out .= '
                <div title="' . $tooltip . '" class="table_caption_H col-sm-1' . $str_CssRow . '">' . $n_ContRow . ' | '.$r_LicensePoint['ProtocolId'] . '/' . $r_LicensePoint['ProtocolYear'].'</div>';
          }
        if ($r_LicensePoint['CommunicationStatus'] != 9)
          $point_sign = "<span class='col-sm-12' style='text-align: right; color:red ; '>-$n_Point</span>";
        else
          $point_sign = "<span class='col-sm-12' style='text-align:right; color:green ; a'>+$n_Point</span>";
        $str_VehicleType = '<i class="' . $aVehicleTypeId[$r_LicensePoint['VehicleTypeId']] . '" style="color:#337AB7;"></i>';
        $str_out .= '
                <div class="table_caption_H col-sm-1' . $str_CssRow . '">' . $r_LicensePoint['Code'] . '</div>
                <div class="table_caption_H col-sm-2' . $str_CssRow . '">' . $a_FineTypeId[$r_LicensePoint['FineTypeId']] . ' ' . DateOutDB($r_LicensePoint['FineDate']) . ' - ' . TimeOutDB($r_LicensePoint['FineTime']) . ' <span style="position:absolute; right:0.5rem;">' . StringOutDB($r_LicensePoint['VehiclePlate']) . ' ' . $str_VehicleType . '</span></div>
                <div class="table_caption_H col-sm-2' . $str_CssRow . '">' . $str_Trespasser . '</div>
                <div class="table_caption_H col-sm-1' . $str_CssRow . '">' . $r_LicensePoint['TaxCode'] . '</div>
                <div class="table_caption_H col-sm-2' . $str_CssRow . '">' . $r_LicensePoint['LicenseNumber'] . ' (' . $r_LicensePoint['LicenseCategory'] . ')' . ' - ' . $r_LicensePoint['LicenseOffice'] . ' ' . DateOutDB($r_LicensePoint['LicenseDate']) . '</div>
                <div class="table_caption_H col-sm-1' . $str_CssRow . '">' . $r_LicensePoint['Article'] . '/' . $r_LicensePoint['Paragraph'] . '/' . $r_LicensePoint['Letter'] . '</div>
                <div class="table_caption_H col-sm-1' . $str_CssRow . '">' . $point_sign . '</div>
                <div class="table_caption_button col-sm-1" style="text-align:center;">' . $str_Check . '</div>

                <div class="clean_row HSpace16"></div>';
        }
      $file = "PUNTI-I";
      $check_file_exist = $path . $file;
      if ($folder != null && $folder != "")
        {
        if (! ftp_chdir($conn, $folder))
          {
          $str_out .= '
                        <div class="table_caption_H col-sm-12 alert-danger">
                            La cartella ' . $folder . ' sul server non è raggiungibile. Contattare l\'amministratore per verficare l\'esistenza o i permessi di accesso.
                        </div>
                        <div class="clean_row HSpace4"></div>
                        ';
          echo $str_out;
          DIE();
          }
        }

      $contents_on_server = ftp_nlist($conn, $path.$folder);
  trigger_error($contents_on_server,E_USER_NOTICE);
    trigger_error("/".$folder.$check_file_exist,E_USER_NOTICE);

      if (in_array("/".$folder.$check_file_exist, $contents_on_server))
        {
        $chk_inp_file = true;
        $str_Button .= 'File precedente già caricato sul server';
        }
      else
        $str_Button = '<button progress-tick="500"  type="button" class="btn btn-primary" id="frm_upload" style="margin-top:0;">Upload punti</button>';

      $str_out .= '
            <div class="col-sm-12 table_caption_H"  style="height:6rem;text-align:center;line-height:6rem;">
                ' . $str_Button . '
                <button class="btn btn-warning" type="button" id="btn_pdf" style="margin-top:0;width:5rem;">
                    <i class="fa fa-file-pdf-o" style="font-size:3rem;"></i>
                </button>
            <img src="' . IMG . '/progress.gif" style="display: none;" id="Progress"/>
            </div>
            </form>
        </div>';
      }
    }
  }

echo $str_out;
?>

<script src="<?= JS; ?>/progressbar.js" type="text/javascript"></script>

<script type="text/javascript">
	$(document).ready(function () {
		if ($("#TypePlate").val()=='F'){
	        $('#Search_Country').prop("disabled", false);

	    }else{
	        $('#Search_Country').prop("disabled", true);
	        $('#Search_Country').val("");
	    }

        $('#sub_Button').click(function() {
            $(this).hide();
            $('#f_download').submit();
        });


        $('.glyphicon-search').click(function() {
            $('#f_Search').submit();
        });

        $("#btn_pdf").on('click',function(e){
            e.preventDefault();
            $('#f_Search').attr('action', 'prn_upload_licensepoint_exe.php<?=$str_GET_Parameter?>');
            $('#glyphicon-search').hide();
            $('#btn_pdf').hide();
            $('#Progress').show();

            $('#f_Search').submit();
        });
        $("#Search_FromProtocolId").change(function(){
    		$("#Search_ToProtocolId").val($("#Search_FromProtocolId").val());
    		                });
        
        $(".actionicon").hover(function(){
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("cursor","");
        });

        $(".actionicon").click(function () {

            var id      = $(this).attr('id');
            var status  = $(this).attr('status');
            $(this).hide();

            $.ajax({
                url: 'ajax/ajx_upd_licensepoint_exe.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {Id: id, Status: status},
                success: function (data) {
                    alert('Comunicazione '+ data.Mex);
                }
            });
        });
        $("#TypePlate").change(function(){
    	    if ($("#TypePlate").val()=='F'){
    	        $('#Search_Country').prop("disabled", false);
    	    }else{
    	        $('#Search_Country').prop("disabled", true);
    	        $('#Search_Country').val("");

    	    }
    	});
        $("#Search_FromProtocolYear").change(function() {
            if($("#Search_FromProtocolId").val()!='' || $("#Search_ToProtocolId").val()!='')
                $("#Search_ToProtocolYear").val($("#Search_FromProtocolYear").val());
                });
            $("#Search_FromProtocolId").change(function(){
            if($("#Search_FromProtocolId").val()!='' || $("#Search_ToProtocolId").val()!='')
                $("#Search_ToProtocolYear").prop("disabled", true);
            else
                $("#Search_ToProtocolYear").prop("disabled", false);
        });

        $('#frm_upload').click(function(){
			$('.progress').show();
            
        	var selected=[];
        	var n=0;
        	for(var i=0;i<<?=$n?>;i++)
              	if($('#checkbox'+i).prop('checked')){
          		selected[n]=$('#checkbox'+i).val();
          		n++;
              	}
        	var params={"checkbox":selected};
    		progressBar_start('frm_upload_licensepoint_exe.php', this,params );
        	})
        $('#frm_upload').on('progressDone', function(e, data){
        	  $('#message').empty();
        	  console.log(data.Messaggio);
        	$('#message').html(data.Messaggio);
        	});

	});
</script>
<?php
include (INC . "/footer.php");
?>
