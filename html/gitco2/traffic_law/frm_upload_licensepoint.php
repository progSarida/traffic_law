        <?php
    require_once("_path.php");
    require_once(INC . "/parameter.php");
    require_once(CLS . "/cls_db.php");
    require_once(INC . "/function.php");
    require_once(INC . "/header.php");
    require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');
    require_once(CLS . "/cls_view.php");
    require_once(CLS . "/cls_message.php");

    $str_Where = '1=1';

    $a_SusAddictionalSanctionId = array(1, 2, 3);

    $rs_Customer = $rs->Select('V_Customer', "CityId='" . $_SESSION['cityid'] . "'");
    $r_Customer = mysqli_fetch_assoc($rs_Customer);

    $rs_Customer = $rs->Select('V_CustomerParameter', "CityId='" . $_SESSION['cityid'] . "'");
    $r_Customer = mysqli_fetch_assoc($rs_Customer);

    $daysNational = ($r_Customer['Data126BisNationalRangeDayMin'] ?? 0) + ($r_Customer['Data126BisNationalRangeDayMax'] ?? 0) + ($r_Customer['Data126BisNationalWaitDay'] ?? 0);
    $daysForeign = ($r_Customer['Data126BisForeignRangeDayMin'] ?? 0) + ($r_Customer['Data126BisForeignRangeDayMax'] ?? 0) + ($r_Customer['Data126BisForeignWaitDay'] ?? 0);
    if($Search_DiscrepancyType==null)
        $Search_DiscrepancyType='Tutte';

    $message = new CLS_MESSAGE();
    if ($r_Customer['LicensePointPaymentCompletion'] == 0)
        $licensepoint = new CLS_VIEW(V_LICENSEPOINT0);
    else
        $licensepoint = new CLS_VIEW(V_LICENSEPOINT1);

    $n_RecordLimit = CheckValue('RecordLimit', 'n');

    $aUserButton = array("upd", 'cut', 'trs');

    $btn_search = CheckValue('btn_search', 'n');
    $str_GET_Parameter .= "&btn_search=$btn_search";

    $str_CheckLicenseYoung1 = $str_CheckLicenseYoung2 = $str_CheckLicenseYoung0 = "";
    $str_CheckLicenseHidden1 = $str_CheckLicenseHidden2 = $str_CheckLicenseHidden0 = "";
    $str_CheckAnomalies1 = $str_CheckAnomalies2 = $str_CheckAnomalies0 = "";
    $str_CheckDiscrepancy1 = $str_CheckDiscrepancy2 = $str_CheckDiscrepancy0 = "";
    $str_CheckDispute1 = $str_CheckDispute2 = $str_CheckDispute0 = "";

    if ($Search_LicenseYoung == 0)
        $str_CheckLicenseYoung0 = " CHECKED ";
    else if ($Search_LicenseYoung == 2)
        $str_CheckLicenseYoung2 = " CHECKED ";
    else
        $str_CheckLicenseYoung1 = " CHECKED ";

    if (true)
        $str_CheckDispute1 = " CHECKED ";
    else if ($Search_HasDispute == 2)
        $str_CheckDispute2 = " CHECKED ";
    else
        $str_CheckDispute1 = " CHECKED ";

    if ($Search_Anomalies === "0")
        $str_CheckAnomalies0 = "CHECKED";
    else if ($Search_Anomalies == 2)
        $str_CheckAnomalies2 = "CHECKED";
    else
        $str_CheckAnomalies1 = "CHECKED";

    if ($Search_Discrepancy === "0")
        $str_CheckDiscrepancy0 = "CHECKED";
    else if ($Search_Discrepancy == 2)
        $str_CheckDiscrepancy2 = "CHECKED";
    else
        $str_CheckDiscrepancy1 = "CHECKED";

    if ($Search_LicenseHidden == 1)
        $str_CheckLicenseHidden1 = " CHECKED ";
    else if ($Search_LicenseHidden == 2)
        $str_CheckLicenseHidden2 = " CHECKED ";
    else
        $str_CheckLicenseHidden0 = " CHECKED ";

    $Search_LicenseTypeN = "";
    $Search_LicenseTypeF = "";



    $ftp_connection = false;
    $server = $r_Customer['LicensePointFtpServer'];
    $username = $r_Customer['LicensePointFtpUser'];
    $password = $r_Customer['LicensePointFtpPassword'];
    $expiration = new DateTime($r_Customer['LicensePointFtpPasswordExpiration']);
    $path = "";


    $ManagerLicensePointCode = $r_Customer['LicensePointCode'];
    $diff = date_diff(new DateTime("now"), $expiration);
    if ($ManagerLicensePointCode == null || $ManagerLicensePointCode == '') {
        $message->addError("Il codice di decurtazione dell' ente è vuoto. Inserire il codice \"Codice ente\" in Gestione Ente nella sezione MCTC.");
        $str_out .= $message->getMessagesString();
        echo $str_out;
        die();
    }

    if ($diff->invert == 1) {
        $message->addError("La password per la connessione al servizio è scaduta.");
        $str_out .= $message->getMessagesString();
        echo $str_out;
        die();
    } else if ($diff->days < 10) {
        $message->addWarning("La password per la connessione al servizio scadrà il giorno {$expiration->format("d/m/Y")}");
        $message=new CLS_MESSAGE();
    }

    if (PRODUCTION) {
        $conn = @ftp_connect($server);
        if (!$conn) {
            $output = shell_exec('sudo '.PERCORSO_VPN.' > /dev/null 2>/dev/null &');
            sleep(4);
        }
        $conn = @ftp_connect($server);
        if ($conn){
            $login = @ftp_login($conn, $username, $password);
            ftp_pasv($conn, FTP_PASSIVE_MODE_MCTC);
            
            if (!$login)
                trigger_error("Login fallita a $server con $username $password", E_USER_WARNING);
        } else
            trigger_error("Server $server non raggiungibile", E_USER_WARNING);

    }
    $str_out .= $message->getMessagesString();

    $str_out .= '<div class="clean_row HSpace4"></div>
    
        <div class="progress" style="display:none;margin:0;">
           <div id="progressbar" class="progress-bar progress-bar-striped progress-bar-success" role="progressbar" style="width: 0%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
    
        <div class="row-fluid">
          <div id="message"></div>
        </div>
    
        <div class="clean_row HSpace4"></div>
    
        <form id="f_Search" action="frm_upload_licensepoint.php" method="post">
        <input type="hidden" name="btn_search" value="1">
        <input type="hidden" name="PageTitle" value="Moduli/Decurta-attribuisci punti">
        <div class="col-sm-12" >
            <div class="col-sm-11" style="height:11.4rem;">
                <div class="col-sm-1 BoxRowLabel">
                    Da anno
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    ' . CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_FromProtocolYear', 'Search_FromProtocolYear', $Search_FromProtocolYear, false) . '
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Ad anno
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    ' . CreateArraySelect($_SESSION['YearArray'][MENU_ID][$_SESSION['cityid']], false, 'Search_ToProtocolYear', 'Search_ToProtocolYear', $Search_ToProtocolYear, false) . '
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Da cron
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_numeric" type="text" value="' . $Search_FromProtocolId . '" id="Search_FromProtocolId" name="Search_FromProtocolId">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    A cron
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_numeric" type="text" value="' . $Search_ToProtocolId . '" id="Search_ToProtocolId" name="Search_ToProtocolId">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Da data accert.
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_date" type="text" value="' . $Search_FromFineDate . '" name="Search_FromFineDate">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    A data accert.
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_date" type="text" value="' . $Search_ToFineDate . '" name="Search_ToFineDate">
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-1 BoxRowLabel">
                    Riferimento
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_Ref" type="text" value="' . $Search_Ref . '">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Nazionalità patente
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <!-- Forza nazionalità a nazionali -->
                    <input type="hidden" value="N" name="Search_LicenseType">
                    <select disabled class="form-control" name="Search_LicenseType" id="Search_LicenseType">
                        <option>Tutti</option>
                        <option value="N"' . $Search_LicenseTypeN . '>Nazionali</option>
                        <option value="F"' . $Search_LicenseTypeF . '>Estere</option>
                    </select>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Nazione patente
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    ' . CreateSelectConcat("SELECT '' as CountryId, 'Tutti' AS Title UNION SELECT distinct F.CountryId, C.Title FROM Fine F JOIN Country C ON F.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY Title='Tutti' DESC, Title", "Search_LicenseCountry", "CountryId", "Title", $Search_LicenseCountry, true) . '
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Da data notifica
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_date" type="text" value="' . $Search_FromNotificationDate . '" name="Search_FromNotificationDate">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    A data notifica
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input data-ndate="' . date('d/m/Y', strtotime("-$daysNational days")) . '" data-fdate="' . date('d/m/Y', strtotime("-$daysForeign days")) . '" class="form-control frm_field_date" type="text" value="' . $Search_ToNotificationDate . '" name="Search_ToNotificationDate" id="Search_ToNotificationDate">
                </div>
    
                <div class="clean_row HSpace4"></div>
    
                <div class="col-sm-1 BoxRowLabel" >
                    Numero record
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    ' . CreateArraySelect(array('',5, 25, 50, 100, 200, 500), false, 'RecordLimit', 'RecordLimit', $n_RecordLimit, true) . '
                </div>
    
                <div class="col-sm-1 BoxRowLabel">
                    Trasgressore
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_Trespasser" type="text" value="' . $Search_Trespasser . '">
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Da riattribuire
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input type="checkbox" name="Search_Reattribution" value="1" ' . ChkCheckButton($Search_Reattribution) . '/>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Già riattribuiti
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input type="checkbox" name="Search_Reattributed" value="1" ' . ChkCheckButton($Search_Reattributed) . '/>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Neopatentati:
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="radio" style="top:0;" name="Search_LicenseYoung" value="1" ' . $str_CheckLicenseYoung1 . '><span  style="position:relative;top:-0.3rem;"> Escludi</span>
                    <input type="radio" style="top:0;" name="Search_LicenseYoung" value="0" ' . $str_CheckLicenseYoung0 . '><span  style="position:relative;top:-0.3rem"> Includi</span>
                    <input type="radio" style="top:0;" name="Search_LicenseYoung" value="2" ' . $str_CheckLicenseYoung2 . '><span  style="position:relative;top:-0.3rem"> Solo loro</span>
                </div>
    
    
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-2 BoxRowLabel font_small">
                    Incongruenze su posizioni da trasmettere:
                </div>
                    <div class="col-sm-2 BoxRowCaption">
                    <input type="radio" style="top:0;" name="Search_Discrepancy" value="0" ' . $str_CheckDiscrepancy0 . '><span  style="position:relative;top:-0.3rem"> Escludi</span>
                    <input type="radio" style="top:0;" name="Search_Discrepancy" value="1" ' . $str_CheckDiscrepancy1 . '><span  style="position:relative;top:-0.3rem"> Includi</span>
                    <input type="radio" style="top:0;" name="Search_Discrepancy" id="Search_Discrepancy2" value="2" ' . $str_CheckDiscrepancy2 . '><span  style="position:relative;top:-0.3rem"> Solo loro</span>
                </div>
                <div class="col-sm-1 BoxRowLabel font_small" >
                    Tipo Incongruenze
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    ' . CreateArraySelect(array('Tutte','Trasgressore','Patente', 'Data notifica', 'Articolo', 'Comunicazione'), false, 'Search_DiscrepancyType', 'Search_DiscrepancyType', $n_RecordLimit, true) . '
                </div>
    
                <div class="col-sm-1 BoxRowLabel">
                    Esclusi:
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="radio" style="top:0;" name="Search_LicenseHidden" value="0" ' . $str_CheckLicenseHidden0 . '><span  style="position:relative;top:-0.3rem"> Escludi</span>
                    <input type="radio" style="top:0;" name="Search_LicenseHidden" value="1" ' . $str_CheckLicenseHidden1 . '><span  style="position:relative;top:-0.3rem"> Includi</span>
                    <input type="radio" style="top:0;" name="Search_LicenseHidden" value="2" ' . $str_CheckLicenseHidden2 . '><span  style="position:relative;top:-0.3rem"> Solo loro</span>
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Ricorso:
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="radio" style="top:0;" name="Search_HasDispute" value="1" ' . $str_CheckDispute1 . ' ><span  style="position:relative;top:-0.3rem"> Escludi</span>
                    <input type="radio" style="top:0;" name="Search_HasDispute" value="0" ' . $str_CheckDispute0 . ' disabled><span  style="position:relative;top:-0.3rem"> Includi</span>
                    <input type="radio" style="top:0;" name="Search_HasDispute" value="2" ' . $str_CheckDispute2 . ' disabled><span  style="position:relative;top:-0.3rem"> Solo loro</span>
                </div>
    
                <div class="clean_row HSpace4"></div>
    
                <div class="col-sm-2 BoxRowLabel">
                    Anomalie da MCTC:
                </div>
                <div class="col-sm-2 BoxRowCaption">
                <input type="radio" style="top:0;" name="Search_Anomalies" value="0" ' . $str_CheckAnomalies0 . '><span  style="position:relative;top:-0.3rem"> Escludi</span>
                    <input type="radio" style="top:0;" name="Search_Anomalies" value="1" ' . $str_CheckAnomalies1 . '><span  style="position:relative;top:-0.3rem"> Includi</span>
                    <input type="radio" style="top:0;" name="Search_Anomalies" value="2" ' . $str_CheckAnomalies2 . '><span  style="position:relative;top:-0.3rem"> Solo loro</span>
                </div>
                <div class="col-sm-8 BoxRowLabel">
                </div>
    
            </div>
            <div class="col-sm-1 BoxRowFilterButton" style="height:11.4rem">
                <button'.($n_RecordLimit > 0 ? '' : ' disabled').' type="submit" data-toggle="tooltip" data-container="body" data-placement="top" title="Cerca" class="tooltip-r col-sm-4 btn btn-primary" id="search" name="search" style="height:100%;font-size:3rem;padding:0;margin:0;width:100%">
                    <i class="glyphicon glyphicon-search"></i>
                </button>
            </div>
        </div>
        </form>
    </div>
    <div class="clean_row HSpace4"></div>';
    $contents_on_server = ftp_nlist($conn, $path);
    $existsDownloadFile = (!PRODUCTION && file_exists(ROOT . "/doc/LicensePoint_DOWNLOAD")) || in_array($path . "PUNTI-O", $contents_on_server);
    trigger_error( "Controllo se esistono $path.PUNTI-O e  $path.PUNTI-I in $path",E_USER_NOTICE);

    $existsUploadFile =PRODUCTION && in_array($path . "PUNTI-I", $contents_on_server);
    if ($existsDownloadFile) {
        $str_out .= '
                <form name="f_download" id="f_download" action="frm_download_licensepoint_exe.php' . $str_Parameters . '" method="post">
                    <div class="col-sm-12 alert-info alert" style="margin:0;text-align:center">
                        <button type="submit" id="sub_Button" class="btn btn-success" style="width:25rem">Presente file per download punti</button>
                    </div>
                    <div class="clean_row HSpace4"></div>
                </form>
                ';
    }

    $n = 0;


    if ($btn_search == 1 && $n_RecordLimit>0) {
        $str_Where .= " AND CityId='{$_SESSION['cityid']}' AND RuleTypeId={$_SESSION['ruletypeid']} " . createLicensePointWhere();
        if ($Search_LicenseType == "") {
            $message->addMessage(" Scegliere tipologia patente");
            $str_out .= $message->getMessagesString();
        } else {

            $strOrder = "FineDate ASC, ProtocolId ASC";
            $rs_LicensePoint = $rs->selectQuery($licensepoint->generateSelect($str_Where, null, $strOrder, $n_RecordLimit));
            //trigger_error("Query: ".$licensepoint->generateSelect($str_Where, null, $strOrder, $n_RecordLimit), E_USER_WARNING);
            $RowNumber = mysqli_num_rows($rs_LicensePoint);
            $n_ContRow = 0;
            $str_out .= '
            <div class="row-fluid">
            <form name="f_upload" id="f_upload" action="frm_upload_licensepoint_exe.php' . $str_Parameters . '" method="post">
                <div class="col-sm-12">
                    <div class="table_label_H col-sm-1">Riga | Cron</div>
                    <div class="table_label_H col-sm-1">Rif</div>
                    <div class="table_label_H col-sm-2">Dati atto</div>
                    <div class="table_label_H col-sm-2">Trasgressore</div>
                    <div class="table_label_H col-sm-1">CF</div>
                    <div class="table_label_H col-sm-1">Dati Patente</div>
                    <div class="table_label_H col-sm-1">Articolo</div>
                    <div class="table_label_H col-sm-1">Data comunicazione</div>
                    <div class="table_label_H col-sm-1">Punti</div>
                    <div class="table_label_H col-sm-1">Operazioni
                        <a href="mgmt_licensepoint_add.php?'.$str_GET_Parameter.'">
                            <span class="glyphicon glyphicon-plus-sign add_button tooltip-r" title="Registrazione decurtazioni/attribuzioni fatte esternamente" style="margin-right:0.3rem; "></span>
                        </a>
                    </div>
                    <div class="clean_row HSpace4"></div>';

            $str_out .= '
                    <input type="hidden" name="TypePlate" value="' . $s_TypePlate . '">';


            if ($RowNumber == 0) {
                $message->addInfo(" Nessun record presente");
                $str_out .= $message->getMessagesString();
            } else {
                $a_LicensePointMex = getLicensePointCodeMex($rs);
                $PreviousProtocolId = 0;
                while ($r_LicensePoint = mysqli_fetch_array($rs_LicensePoint)) {
                    $n_ContRow++;
                    $b_Warning = false;
                    $b_RemovePointsCap = false;
                    $str_CssRow = "";
                    $str_Check = "";
                    $str_Anomaly = '';
                    $a_tooltips = array();
                    $tooltip = '';
                    $str_Trespasser = "{$r_LicensePoint['TrespasserCode']} - ".trim(trim($r_LicensePoint['Surname']) . ' ' . trim($r_LicensePoint['Name']));
                    $str_TrespasserTooltip = checkTrespasser($r_LicensePoint,$str_Trespasser);
                    $str_LicenseTooltip = checkLicense($r_LicensePoint);
                    $str_actions="";
                    if(!is_null($str_TrespasserTooltip)){
                        $b_Warning = true;
                        $str_actions .= ChkButton($aUserButton, 'upd', '<a target="_blank" href="mgmt_trespasser_upd.php' . $str_GET_Parameter . '&Id=' . $r_LicensePoint['TrespasserId'] . '"><span class="tooltip-r fa fa-wrench fa-fw" title="Correggi Trasgressore" data-placement="top"  style="margin-top: 0.3rem;font-size: 1.5rem;"></span></a>');
                        $a_tooltips[] = $str_TrespasserTooltip;
                        $str_CssRow = ' alert-warning';
                    }
                    if(!is_null($str_LicenseTooltip)){
                        $b_Warning = true;
                        $str_actions .= ChkButton($aUserButton, 'upd', '<a target="_blank" href="mgmt_trespasser_upd.php' . $str_GET_Parameter . '&Id=' . $r_LicensePoint['TrespasserId'] . '"><span class="tooltip-r fa fa-wrench fa-fw" title="Correggi Patente" data-placement="top"  style="margin-top: 0.3rem;font-size: 1.5rem;"></span></a>');
                        $a_tooltips[] = $str_LicenseTooltip;
                        $str_CssRow = ' alert-warning';
                    }
                    if($r_Customer['LicensePointPaymentCompletion'] == 0 && trim($r_LicensePoint['NotificationDate']) == "")//la data di notifica va controllata solo nel caso 0 in cui la data di notifica è obbligatoria. Nel caso 1 può bastare che ci sia un pagamento.
                    {
                        $b_Warning = true;
                        $str_actions .= ChkButton($aUserButton, 'upd', '<a target="_blank" href="mgmt_notification_viw.php?page=1&PageTitle=/&Search_PaymentRate=0&Search_Status=0&Search_Locality=U480&Search_ProtocolId=5733&Search_RuleTypeId=1&Search_HasLicensePoints=0&Search_CurrentYear=1&Search_CommunicationType=0&P=mgmt_fine.php' . $str_GET_Parameter . '&Id=' . $r_LicensePoint['Id'].'"><span class="tooltip-r fa fa-wrench fa-fw" title="Correggi Data notifica" data-placement="top"  style="margin-top: 0.3rem;font-size: 1.5rem;"></span></a>');
                        $a_tooltips[] = "Data notifica mancante, questo ente effettua la decurtazione dei punti solo per i verbali notificati.";
                        $str_CssRow = ' alert-warning';
                    }
                    if (trim($r_LicensePoint['LicensePointCode1']) == "" || (trim($r_LicensePoint['LicensePointCode2']) == "" && $r_LicensePoint["Habitual"]==1)) {
                        $b_Warning = true;
                        $str_actions .= ChkButton($aUserButton, 'upd', '<a target="_blank" href="tbl_article_city.php"><span class="tooltip-r fa fa-wrench fa-fw" title="Correggi articolo" data-placement="top"  style="margin-top: 0.3rem;font-size: 1.5rem;"></span></a>');
                        $a_tooltips[] = "Codice decurtazione punti mancante sull' articolo del verbale";
                        trigger_error("LicensePointCodes {$r_LicensePoint['LicensePointCode1']} - {$r_LicensePoint['LicensePointCode2']}", E_USER_WARNING);
                        $str_CssRow = ' alert-warning';
                    }
                    if ($r_LicensePoint['DocumentTypeId'] != 1) {
                        $b_Warning = true;
                        $str_Check .= ChkButton($aUserButton, 'upd', '<a target="_blank" href="mgmt_communication_upd.php' . $str_GET_Parameter . '&Id=' . $r_LicensePoint['Id'] . '"><span class="tooltip-r fa fa-wrench fa-fw" title="Correggi" data-placement="top"  style="margin-top: 0.3rem;font-size: 1.5rem;"></span></a>');
                        $a_tooltips[] = "Il trasgressore possiede un tipo di documento esente dalla decurtazione: {$r_LicensePoint['DocumentTypeTitle']}";
                        trigger_error("DocumentTypeId {$r_LicensePoint['DocumentTypeId']}", E_USER_WARNING);
                        $str_CssRow = ' alert-warning';
                    }
                    if ($r_LicensePoint['CommunicationStatus'] == 3) {
                        $b_Warning = true;
                        $a_tooltips[] = "Elaborazione già avvenuta in stato anomalo: ({$r_LicensePoint['LicensePointId']}) " . $a_LicensePointMex[$r_LicensePoint['LicensePointId']];
                        trigger_error("CommunicationStatus {$r_LicensePoint['CommunicationStatus']}", E_USER_WARNING);
                        $str_CssRow = ' alert-danger';
                    }

                    if(empty($a_tooltips))
                        $tooltip="Nessuna anomalia";
                    else
                        $tooltip=implode('<br>------------------------------------<br>', $a_tooltips);

                    //NEL CASO DI SOSPENSIONE, REVOCA O RITIRO PATENTE, IL LIMITE DI 15 PUNTI VIENE RIMOSSO
                    if ($r_LicensePoint['SuspensionLicense'] == 1 || $r_LicensePoint['LossLicense'] == 1 || in_array($r_LicensePoint['AdditionalSanctionId'], $a_SusAddictionalSanctionId))
                        $b_RemovePointsCap = true;

                    $n_LicenseYear = $r_LicensePoint['LicenseYear'];
                    $n_Point = ($n_LicenseYear >= 3) ? $r_LicensePoint['LicensePoint'] : $r_LicensePoint['YoungLicensePoint'];

                    if ($r_LicensePoint['ArticleNumber'] > 1) {
                        $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle', "FineId=" . $r_LicensePoint['Id'] . " AND LicensePoint>0");
                        while ($r_AdditionalArticle = mysqli_fetch_array($rs_AdditionalArticle)) {
                            $n_PointLicense = ($n_LicenseYear >= 3) ? $r_AdditionalArticle['LicensePoint'] : $r_AdditionalArticle['YoungLicensePoint'];
                            $n_Point += $n_PointLicense;
                            //NEL CASO DI SPOSENSIONE, REVOCA O RITIRO PATENTE, IL LIMITE DI 15 PUNTI VIENE RIMOSSO
                            if ($r_AdditionalArticle['SuspensionLicense'] == 1 || $r_AdditionalArticle['LossLicense'] == 1 || in_array($r_AdditionalArticle['AdditionalSanctionId'], $a_SusAddictionalSanctionId)) {
                                $b_RemovePointsCap = true;
                            }
                        }
                    }

                    if ($n_Point > 15 && !$b_RemovePointsCap) {
                        $n_Point = 15;
                    }
                    $str_Anomaly='<i class="tooltip-r fas'.($b_Warning ? ' fa-exclamation-circle' : ' fa-check-circle').' fa-fw" style="margin-top: 0.3rem;font-size: 1.4rem;" data-html="true" data-container="body" data-toggle="tooltip" data-placement="left" title="' . $tooltip . '"></i>';
                    if (!$b_Warning) {
                        $str_Check .= '<input type="checkbox" name="checkbox[]" id="checkbox' . $n . '" value="' . $r_LicensePoint['Id'] . '" checked />';
                        $n++;
                        $str_CssRow = '';
                    } else {
                        $CommunicationStatus = ($r_LicensePoint['CommunicationStatus'] == 1 || $r_LicensePoint['CommunicationStatus'] == 3) ? 0 : 1;
                        $TooltipText = ($r_LicensePoint['CommunicationStatus'] == 1 || $r_LicensePoint['CommunicationStatus'] == 3) ? 'Reincludi nell\'elaborazione' : 'Escludi dall\'elaborazione';
                        $Icon = ($r_LicensePoint['CommunicationStatus'] == 1 || $r_LicensePoint['CommunicationStatus'] == 3) ? 'fa-history' : 'fa-scissors';
                        $str_Check.=$str_actions;
                        $str_Check .= ChkButton($aUserButton, 'upd', '<a target="_blank" href="mgmt_communication_upd.php' . $str_GET_Parameter . '&Id=' . $r_LicensePoint['Id'] . '"><span class="tooltip-r fa fa-pencil fa-fw" title="Consulta/modifica comunicazione art. 126 bis" data-placement="top"  style="margin-top: 0.3rem;font-size: 1.5rem;"></span></a>');
                        $str_Check .= ChkButton($aUserButton, 'cut', '<a><span id="' . $r_LicensePoint['Id'] . '" status="' . $CommunicationStatus . '" class="tooltip-r fa ' . $Icon . ' fa-fw actionicon" title="' . $TooltipText . '" data-placement="top"  style="margin-top: 0.3rem;font-size: 1.5rem;"></span></a>');
                    }
                    if ($PreviousProtocolId == $r_LicensePoint['ProtocolId'])
                        $str_Check = "";
                    else
                        $PreviousProtocolId = $r_LicensePoint['ProtocolId'];

                    $str_out .= '<div class="tableRow">';

                    if ($str_CssRow != '') {
                        $str_out .= '
                    <div class="table_caption_H col-sm-1' . $str_CssRow . '"></i>
                    ' . $n_ContRow . ' | ' . $r_LicensePoint['ProtocolId'] . '/' . $r_LicensePoint['ProtocolYear'] . '</div>';
                    } else {
                        $str_CssRow = ' alert-success';
                        $str_out .= '
                    <div class="table_caption_H col-sm-1' . $str_CssRow . '">
                    ' . $n_ContRow . ' | ' . $r_LicensePoint['ProtocolId'] . '/' . $r_LicensePoint['ProtocolYear'] . '</div>';
                    }

                    if ($r_LicensePoint['CommunicationStatus'] != 9)
                        $point_sign = "<span style='text-align: right; color:red;float:right'>-$n_Point</span>";
                    else
                        $point_sign = "<span style='text-align:right; color:green;float:right'>+$n_Point</span>";
                    $str_VehicleType = '<i class="' . $aVehicleTypeId[$r_LicensePoint['VehicleTypeId']] . '" style="color:#337AB7;"></i>';
                    $str_out .= '
                    <div class="table_caption_H col-sm-1' . $str_CssRow . '">' . $r_LicensePoint['Code'] . '</div>
                    <div class="table_caption_H col-sm-2' . $str_CssRow . '">' . $a_FineTypeId[$r_LicensePoint['FineTypeId']] . ' ' . DateOutDB($r_LicensePoint['FineDate']) . ' - ' . TimeOutDB($r_LicensePoint['FineTime']) . ' <span style="position:absolute; right:0.5rem;">' . StringOutDB($r_LicensePoint['VehiclePlate']) . ' ' . $str_VehicleType . '</span></div>
                    <div class="table_caption_H col-sm-2' . $str_CssRow . '">' . $str_Trespasser . '</div>
                    <div class="table_caption_H col-sm-1' . $str_CssRow . '">' . $r_LicensePoint['TaxCode'] . '</div>
                    <div class="table_caption_H col-sm-1' . $str_CssRow . '">' . $r_LicensePoint['LicenseNumber'] . ' (' . $r_LicensePoint['LicenseCategory'] . ')' . ' - ' . $r_LicensePoint['LicenseOffice'] . ' ' . DateOutDB($r_LicensePoint['LicenseDate']) . '</div>
                    <div class="table_caption_H col-sm-1' . $str_CssRow . '">' .getArticleString($r_LicensePoint['Article'] , $r_LicensePoint['Paragraph'] , $r_LicensePoint['Letter']) . '</div>
                    <div class="table_caption_H col-sm-1' . $str_CssRow . '">' . DateOutDB($r_LicensePoint['CommunicationDate']) . '</div>
                    <div class="table_caption_H col-sm-1' . $str_CssRow . '" style="padding-right: 0.5rem;">' . $point_sign . '</div>
                    <div class="alert-info table_caption_button col-sm-1" style="text-align:left;">'. $str_Anomaly . $str_Check . '</div>
                    <div class="clean_row HSpace4"></div>
                </div>';
                }
                if ($ManagerLicensePointCode != null && $ManagerLicensePointCode != '')
                    if ($existsUploadFile) {
                        $chk_inp_file = true;
                        $str_Button = '<button class="btn-primary btn" style="margin-top:2rem;width:auto;" disabled>File precedente già caricato sul server</button>';
                    } else
                        $str_Button = '<button progress-tick="500"  type="button" class="btn btn-primary" id="frm_upload" style="margin-top:2rem;">Upload punti</button>';

                $str_out .= '
                <div class="table_label_H col-sm-12" style="height:8rem; position:relative">
                    <button class="btn btn-warning btn-primary" type="button" id="btn_pdf" style="margin-top:2rem;width:16rem">
                        <i class="fa fa-file-pdf-o fa-fw"></i> Stampa prospetto
                    </button>
                                    <button class="btn btn-success btn-primary" type="button" id="btn_excel" style="margin-top:2rem;width:16rem">
                        <i class="fa fa-file-excel-o fa-fw"></i> Stampa prospetto
                    </button>
                    ' . $str_Button . '
                    <div style="position:absolute; top:5px;font-size:1.2rem;color:#fff;width:405px;text-align: left;line-height:2.5rem">
                        <div style="width:200px;float:left;">
                            <i class="fa fa-exclamation-circle" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Anomalia/Incongruenza
                        </div>
                        <div style="width:200px;float:left;">
                            <i class="fa fa-wrench" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Correggi
                        </div>
                        <div style="width:200px;float:left;">
                            <i class="fa fa-scissors" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Escludi dall\'elaborazione
                        </div>
                        <div style="width:200px;float:left;">
                            <i class="fa fa-history" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Reincludi nell\'elaborazione
                        </div>
                        <div style="width:200px;float:left;">
                            <i class="fa fa-pencil" style="margin-top:0.2rem;margin-left:1rem;font-size:1.2rem;"></i> Modifica com. art. 126 bis
                        </div>
                    </div>
                </div>';

            }
        }

    }
    else{
        $message->addError("Scegliere il numero di record");
        $str_out .= $message->getMessagesString();
    }
    if ($btn_search != 1 && $existsUploadFile) {

        $str_out .= '
                <div class="table_label_H col-sm-12" style="height:8rem; position:relative">
                    <button class="btn btn-warning btn-primary" type="button" id="btn_pdf" style="margin-top:2rem;width:16rem">
                        <i class="fa fa-file-pdf-o fa-fw"></i> Stampa prospetto
                    </button>
                    <button class="btn btn-success btn-primary" type="button" id="btn_excel" style="margin-top:2rem;width:16rem">
                        <i class="fa fa-file-excel-o fa-fw"></i> Stampa prospetto
                    </button>
                    <button class="btn-primary btn" style="margin-top:2rem;width:auto;" disabled>File precedente già caricato sul server</button>
                    </div>';
    }
    $str_out .= '</form>
            </div>';
    echo $str_out;
    ?>
    <script src="<?= JS; ?>/progressbar.js" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function () {


            if ($("#Search_LicenseType").val()=='F'){
                $('#Search_LicenseCountry').prop("disabled", false);

            }else{
                $('#Search_LicenseCountry') . prop("disabled", true);
                $('#Search_LicenseCountry') . val("");
            }
            if(!$("#Search_Discrepancy2").prop('checked')){
                $("#Search_DiscrepancyType") . prop('disabled',!$("#Search_Discrepancy2").prop('checked'));
                $('#Search_DiscrepancyType').val('Tutte');
            }
            else
                $('#Search_DiscrepancyType').val('<?=$Search_DiscrepancyType?>');

            $('#sub_Button') . click(function () {
                $("#btn_pdf, #search, #frm_upload, #sub_Button") . prop('disabled', true);
                $('#sub_Button') . html('<i class="fa fa-circle-notch fa-spin" style="font-size: 2rem;"></i>');
                $('#f_download') . submit();
            });

            $('#RecordLimit').on('change', function(){
                if($(this).val() != '')
                    $('#search, #btn_excel, #btn_pdf').prop('disabled', false);
                else $('#search, #btn_excel, #btn_pdf').prop('disabled', true);
            });

            $("input[name='Search_Discrepancy']").change(function (){
                $("#Search_DiscrepancyType") . prop('disabled',!$("#Search_Discrepancy2").prop('checked'));
                if(!$("#Search_Discrepancy2").prop('checked'))
                    $("#Search_DiscrepancyType").val("Tutte");
            });

            $('#search') . click(function () {
                $("#btn_pdf, #search, #frm_upload, #sub_Button") . prop('disabled', true);
                $('#search i') . toggleClass('glyphicon glyphicon-search fa fa-circle-notch fa-spin');
                $('#f_Search') . submit();
            });

            $("#btn_pdf") . on('click', function (e){
                $("#btn_pdf, #search, #frm_upload, #sub_Button") . prop('disabled', true);
                e . preventDefault();
                $('#f_Search') . attr('action', 'prn_upload_licensepoint_exe.php<?=$str_GET_Parameter."&PrintType=pdf"?>');
                $('#btn_pdf') . html('<i class="fa fa-circle-notch fa-spin" style="font-size: 3rem;"></i>');

                $('#f_Search') . submit();
  x          });
            $("#btn_excel") . on('click', function (e){
                $("#btn_excel, #search, #frm_upload, #sub_Button") . prop('disabled', true);
                e . preventDefault();
                $('#f_Search') . attr('action', 'prn_upload_licensepoint_exe.php<?=$str_GET_Parameter."&PrintType=xls"?>');
                $('#btn_excel') . html('<i class="fa fa-circle-notch fa-spin" style="font-size: 3rem;"></i>');
                $('#f_Search') . submit();
            });

            $(".actionicon") . hover(function () {
                $(this) . css("cursor", "pointer");
            }, function () {
                $(this) . css("cursor", "");
            });

            $(".actionicon") . click(function () {

                var
                    id = $(this) . attr('id');
                var
                    status = $(this) . attr('status');
                $(this) . hide();

                $.ajax({
                    url: 'ajax/ajx_upd_licensepoint_exe.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {
                        Id:
                        id, Status: status},
                    success: function (data) {
                        alert('Comunicazione ' + data . Mex);
                    }
                });
            });
            $("#Search_LicenseType") . change(function () {
                if ($("#Search_LicenseType") . val() == 'F') {
                    $('#Search_LicenseCountry') . prop("disabled", false);
                    $('#Search_ToNotificationDate') . val($('#Search_ToNotificationDate') . data('fdate'));
                } else {
                    $('#Search_LicenseCountry') . prop("disabled", true);
                    $('#Search_LicenseCountry') . val("");
                    $('#Search_ToNotificationDate') . val($('#Search_ToNotificationDate') . data('ndate'));
                }
            });

            $("input[name='Search_Discrepancy'], input[name='Search_Anomalies']") . change(function () {
                var
                    fieldToChange = ($(this) . attr('name') == 'Search_Discrepancy' ? $("input[name='Search_Anomalies']") : $("input[name='Search_Discrepancy']"));
                fieldToChangeVal = fieldToChange . filter(':checked') . val()

                if ($(this) . val() == 2 && (fieldToChangeVal == 2 || fieldToChangeVal == 1)) {
                    fieldToChange . val([0]);
                } else if ($(this) . val() == 1 && fieldToChangeVal == 2) {
                    fieldToChange . val([0]);
                }
            });

            $('#frm_upload') . click(function () {
                $('#btn_pdf, #search, #sub_Button') . prop('disabled', true);
                $('.progress') . show();

                var
                    selected = [];
                var
                    n = 0;
                for (var i = 0;i <<?=$n?>;i++)
                    if($('#checkbox'+i).prop('checked')){
                        selected[n]=$('#checkbox'+i).val();
                        n++;
                    }
                var params={"checkbox":selected};
                progressBar_start('frm_upload_licensepoint_exe.php', this,params );
            });

            $('#frm_upload').on('progressDone', function(e, data){
                $('#message').empty();
                console.log(data.Messaggio);
                $('#message').html(data.Messaggio);
                $('#btn_pdf, #search, #sub_Button').prop('disabled', false);
            });

            $(".tableRow").mouseover(function(){
                $( this ).find( '.table_caption_H, .table_caption_button' ).css({"background-color":"#cfeaf7c7", "background-image":"none"});
            });
            $(".tableRow").mouseout(function(){
                $( this ).find( '.table_caption_H, .table_caption_button' ).css({"background-color":"", "background-image":""});
            });

            $("#Search_FromProtocolYear, #Search_FromProtocolId").change(function() {
                if($("#Search_FromProtocolId").val()!='' || $("#Search_ToProtocolId").val()!='')
                    $("#Search_ToProtocolYear").val($("#Search_FromProtocolYear").val());
            });
            $("#Search_FromProtocolId").change(function(){
                if($.trim($("#Search_ToProtocolId").val()) == '')
                    $("#Search_ToProtocolId").val($("#Search_FromProtocolId").val());
            });

            $("#Search_FromProtocolId").change(function(){
                if($(this).val() != '' && $(this).val() > $("#Search_ToProtocolId").val())
                    $("#Search_ToProtocolId").val($(this).val());
            });

            $("#Search_ToProtocolId").change(function(){
                if($(this).val() != '' && $(this).val() < $("#Search_FromProtocolId").val())
                    $("#Search_FromProtocolId").val($(this).val());
            });

            $("#Search_FromProtocolYear").change(function(){
                if($(this).val() != '' && $(this).val() > $("#Search_ToProtocolYear").val())
                    $("#Search_ToProtocolYear").val($(this).val());
            });

            $("#Search_ToProtocolYear").change(function(){
                if($(this).val() != '' && $(this).val() < $("#Search_FromProtocolYear").val())
                    $("#Search_FromProtocolYear").val($(this).val());
            });

        });
    </script>
<?php
require_once(INC . "/footer.php");
?>
