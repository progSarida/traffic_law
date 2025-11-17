<?php
use traffic_law\cls\visureMCTC\Controlli;

include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/function_visure.php");
require_once(CLS."/visureMCTC/Controlli.php");
include(CLS."/cls_progressbar.php");

$str_Action = CheckValue('action','s');

//Carica gli elementi html nel caso di Carica richiesta MCTC
if ($str_Action != "import"){
    include(INC . "/header.php");
    require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');
} else {
    $rs = new CLS_DB();
}

ini_set('max_execution_time', 30000);
$rs_ProcessingMCTC = $rs->Select('ProcessingMCTC', "CityId='{$_SESSION['cityid']}'", 'Position');
$r_ProcessingMCTC = mysqli_fetch_array($rs_ProcessingMCTC);

$ftp_connection = false;
$chk_inp_file = false;
$chk_out_file = false;
$server         = $r_ProcessingMCTC['FTP'];
$username       = $r_ProcessingMCTC['Username'];
$password       = $r_ProcessingMCTC['Password'];

$NotificationTypeId = 1;
$CountryId = "Z000";

$conn=true;
$str_Connection='';
$path = "/";
$dataRichiesta = date('Y-m-d');
$oraRichiesta = date('H:i:s');

//OPERAZIONI DI CONTROLLO PRELIMINARI/////////////////////////////////////////////
if(PRODUCTION){
    $conn = @ftp_connect($server);
    if (! $conn) {
        $output = shell_exec('sudo '.PERCORSO_VPN.' > /dev/null 2>/dev/null &');
        sleep(3);
    }
    
    $conn = @ftp_connect($server);
    
    if ($conn) {
        $login = @ftp_login($conn, $username, $password);
        ftp_pasv($conn, FTP_PASSIVE_MODE_MCTC);
        
        if ($login) {
            $ftp_connection = true;
            $str_Connection = '
            <div class="table_caption_H col-sm-12 alert-success">
                Tentativo di connessione riuscita
            </div>';
        } else {
            $ftp_connection = false;
            $str_Connection = '
            <div class="table_caption_H col-sm-12 alert-danger">
                Tentativo di login fallito. Verificare che le credenziali di accesso siano ancora valide.
            </div>';
        }
    } else {
        $ftp_connection = false;
        $str_Connection = '
        <div class="table_caption_H col-sm-12 alert-danger">
            Tentativo di connessione fallito
        </div>';
    }
} else {
    $ftp_connection = true;
}
////////////////////////////////////////////////////////////////////////

if ($str_Action != "import"){
    
    $str_out .= '
        <div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-12">CONNESSIONE FTP</div>
				<div class="clean_row HSpace4"></div>
			</div>
            <div class="col-sm-12">
                ' . $str_Connection . '
			    <div class="clean_row HSpace4"></div>
			</div>';
    
}


if ($ftp_connection) {
    
    
    if($str_Action=="import"){
        $str_Message = '';
        $file = $r_ProcessingMCTC['FileOutput'];
        
        $check_file_exist = $path . $file;
        $contents_on_server =null;
        
        if(PRODUCTION){ 
            $contents_on_server = ftp_nlist($conn, $path);
            if (in_array($check_file_exist, $contents_on_server)) {
                $chk_inp_file = true;
            }
            ftp_close($conn);
        } else $chk_inp_file = true;
        
        if ($chk_inp_file) {
            $richieste = $rs->getResults($rs->Select("richieste_servizi_esterni", "rse_utente_servizio='$username' AND rse_tipo=8 AND rse_esito IS NULL"));
            $codiciRichieste = array_column($richieste, 'rse_codice', 'Id');
            
            if(!empty($codiciRichieste)){
                if(PRODUCTION){
                $str_FileName = "Response_" . date("Y-m-d_H-i");
                } else {
                $str_FileName = "test_response";
                }
                
                
                if(PRODUCTION){
                    //RIAPRE LA CONNESSIONE
                    $conn = @ftp_connect($server);
                    
                    if ($conn) {
                        $login = @ftp_login($conn, $username, $password);
                        ftp_pasv($conn, FTP_PASSIVE_MODE_MCTC);
                        if ($login) {
                            $ftp_connection = true;
                            $str_Message .= '
                            <div class="table_caption_H col-sm-12 alert-success">
                            Connessione riuscita
                            </div>';
                        } else {
                            $ftp_connection = false;
                            $str_Message .= '
                            <div class="table_caption_H col-sm-12 alert-danger">
                            Tentativo di login fallito. Verificare che le credenziali di accesso siano ancora valide.
                            </div>';
                        }
                    } else {
                        $ftp_connection = false;
                        $str_Message .= '
                        <div class="table_caption_H col-sm-12 alert-danger">
                        Tentativo di connessione fallito
                        </div>';
                    }
                    
                    if(!$ftp_connection){
                        echo json_encode(
                            array(
                                "Esito" => 0,
                                "Messaggio" => trim($str_Message),
                            )
                            );
                        DIE;
                    }
                    
                    $download = @ftp_get($conn, NATIONAL_REQUEST . "/" . $str_FileName, $r_ProcessingMCTC['FileOutput'], FTP_BINARY);
                } else {
                    $download=true;
                }
                
                if ($download) {
                    $str_Message .= '
                    <div class="col-sm-12">
                        <div class="table_caption_H col-sm-12 alert-success">
                            FILE DATI motorizzazione scaricato
                        </div>
                        <div class="clean_row HSpace4"></div>
                    </div>';
                    
                    if(PRODUCTION){
                        
                        $delete = @ftp_delete($conn, $r_ProcessingMCTC['FileOutput']);
                        if ($delete) {
                            $str_Message .= '
                            <div class="col-sm-12">
                                <div class="table_caption_H col-sm-12 alert-success">
                                    FILE DATI cancellato correttamente dal server
                                </div>
                                <div class="clean_row HSpace4"></div>
                            </div>';
                        } else {
                             $str_Message .= '
                             <div class="col-sm-12">
                                <div class="table_caption_H col-sm-12 alert-danger">
                                    Problemi nella cancellazione dl FILE DATI su server
                                </div>
                                <div class="clean_row HSpace4"></div>
                             </div>';
                        }
                        
                        $delete = @ftp_delete($conn, $r_ProcessingMCTC['FileFlag']);
                        if ($delete) {
                            $str_Message .= '
                            <div class="col-sm-12">
                            <div class="table_caption_H col-sm-12 alert-success">
                                FILE CONTROLLO cancellato correttamente dal server
                            </div>
                            <div class="clean_row HSpace4"></div>
                            </div>';
                        } else {
                        $str_Message .= '
                            <div class="col-sm-12">
                             <div class="table_caption_H col-sm-12 alert-danger">
                                Problemi nella cancellazione dl FILE CONTROLLO su server
                             </div>
                             <div class="clean_row HSpace4"></div>
                            </div>';
                        }
                     
                        $delete = @ftp_delete($conn, $r_ProcessingMCTC['FileInput']);
                        if ($delete) {
                            $str_Message .= '
                            <div class="col-sm-12">
                             <div class="table_caption_H col-sm-12 alert-success">
                                FILE RICHIESTA cancellato correttamente dal server
                             </div>
                             <div class="clean_row HSpace4"></div>
                            </div>';
                        } else {
                            $str_Message .= '
                            <div class="col-sm-12">
                             <div class="table_caption_H col-sm-12 alert-danger">
                                Problemi nella cancellazione dl FILE RICHIESTA su server
                             </div>
                             <div class="clean_row HSpace4"></div>
                            </div>';
                        }
                        ftp_close($conn);
                    }
                } else {
                    $str_Message .= '
                    <div class="col-sm-12">
                        <div class="table_caption_H col-sm-12 alert-danger">
                            Problemi nel download FILE DATI motorizzazione
                        </div>
                        <div class="clean_row HSpace4"></div>
                    </div>';
                }
                
                
                if ($download) {
    
                    //Carica la mappa delle marca modello eventualmente già corrette
                    $a_AnomalyBrandModelMap = buildAnomalyBrandModelMap();
                    
                    $DataSourceId = 2;
                    $DataSourceDate = date("Y-m-d");
                    $DataSourceTime = date('H:i:s');
                    $a_ZIPCity = array();
                    $rs_ZIPCity = $rs->SelectQuery("SELECT DISTINCT Title FROM sarida.ZIPCity");
                    
                    while ($r_ZIPCity = mysqli_fetch_array($rs_ZIPCity))
                        $a_ZIPCity[] = strtoupper($r_ZIPCity['Title']);
                    
                    $cont = 0;
                    $contaInserite = 0;
                    $contaAggiornate = 0;
                    $fileRows = count(file((PRODUCTION)
                        ? NATIONAL_REQUEST . "/" . $str_FileName
                        : TESTVISURE_FOLDER . "/" . $str_FileName));
                    
                    $f_ImportFile = fopen((PRODUCTION) 
                        ? NATIONAL_REQUEST . "/" . $str_FileName 
                        : TESTVISURE_FOLDER . "/" . $str_FileName, "r+");
                    
                    
                    $ProgressFileName = CheckValue("ProgressFile", "s");
                    $ProgressFile = TMP . "/".$ProgressFileName;
                    
                    $progress = new CLS_PROGRESSBAR($fileRows);
                    
                    $rs->Start_Transaction();
                    
                if (is_resource($f_ImportFile)){
                    $updateRequest = array(
                        array('field' => 'rse_esito','selector' => 'value','type' => 'str','value' => 'S'),
                        array('field' => 'rse_ute_risposta','selector' => 'value','type' => 'str','value' => $_SESSION['username'])
                    );
                    
                    $rs->Update("richieste_servizi_esterni", $updateRequest, "rse_codice IN(".implode(',',$codiciRichieste).")");
                    
                    while (!feof($f_ImportFile)) {
                        $str_Error = '';
                        $str_Output = '';
                        $content = fgets($f_ImportFile);
                        
                        
                        if (strlen($content) > 560) {
                            $chk_Data = substr($content, 0, 2);
                            
                            switch ($chk_Data) {
                                case 91:
                                    $str_Error = "Riga: ".($cont+1)." - Errore nei campi trasmessi";
                                    break;
                                case 92:
                                    $str_Error = "Riga: ".($cont+1)." - Data infrazione maggiore data cessazione";
                                    break;
                                case 93:
                                    $str_Error = "Riga: ".($cont+1)." - Provincia targa errata o tipo veicolo errato o numero targa errata";
                                    break;
                                case 94:
                                    $str_Error = "Riga: ".($cont+1)." - Mancata digitazione utente";
                                    break;
                                case 95:
                                    $str_Error = "Riga: ".($cont+1)." - Errore per targa ciclomotore";
                                    break;
                                default:
                                    
                                    if (trim(substr($content, 256, 42)) != "" || trim(substr($content, 216, 40)) != "") {
                                        $TresspasserTypeId = 1;
                                        
                                        $ZoneId = 1;
                                        $LanguageId = 1;
                                        $StatusTypeId = 10;
                                        
                                        $Genre = substr($content, 33, 1);
                                        $TrespasserName = trim(substr($content, 34, 70));
                                        $City = extract_substr($content, 139, 22);
                                        $TrespasserCountryId = 'Z000';
                                        
                                        $controlli = new Controlli($rs, $a_ZIPCity, $City);
                                        
                                        $CityId = $controlli->controllaCittàResidenza();
                                        
                                        //Identificativo stato residenza estera
                                        $MegaspCountry = extract_substr($content, 163, 3);
                                        
                                        //Con MegaspCountry possiamo sapere se il trasgressore è residente all'estero, in quel caso
                                        //segnamo l'atto a cui viene attribuito il trasgressore come nazione "Da assegnare"
                                        if($MegaspCountry != ''){
                                            $rs_Country = $rs->Select('Country', "MegaspCountry='$MegaspCountry'");
                                            $r_Country = $rs->getArrayLine($rs_Country);
                                            if($r_Country){
                                                $TrespasserCountryId = $r_Country['Id'];
                                                $ZoneId = $r_Country['ZoneId'];
                                                $LanguageId = $r_Country['LanguageId'];
                                            }
                                            $b_CountryToAssign = true;
                                        } else {
                                            $b_CountryToAssign = false;
                                        }
                                        
                                        $Street = extract_substr($content, 166, 5);
                                        $Address = extract_substr($content, 171, 34);
                                        $Number = extract_substr($content, 205, 6);
                                        
                                        $VehiclePlate = extract_substr($content, 25, 8);
                                        
                                        $str_Message .= '
                                        <div class="col-sm-12">
                                         <div class="table_caption_H col-sm-12 alert-info">
                                            Targa: '.$VehiclePlate.'
                                         </div>
                                         <div class="clean_row HSpace4"></div>
                                        </div>';
                                        
                                        $FullStreet = controlli::normalizzaIndirizzo($Address, $Street, $Number);
                                        //$FullStreet = ChkAddressInsertDB($Street, $Address, $Number);
                                        
                                        //se l'indirizzo del trasgressore è troppo lungo lo inseriamo nelle anomalie
                                        $controlli->controllaLungIndirizzo($FullStreet);
                                        
                                        $Province = $controlli->controllaProvincia(extract_substr($content, 161, 2));
                                        $ZIP = $controlli->controllaCap($FullStreet, extract_substr($content, 211, 5));
                                        
                                        $VehicleMass = substr($content, 444, 3) . "." . substr($content, 447, 3);
                                        
                                        $FineDate = substr($content, 15, 4) . "-" . substr($content, 13, 2) . "-" . substr($content, 11, 2);
                                        
                                        
                                        $VehicleLastRevision = (trim(substr($content, 529, 8)) == "" || trim(substr($content, 529, 8)) == "00000000") ? NULL : substr($content, 533, 4) . "-" . substr($content, 531, 2) . "-" . substr($content, 529, 2);
                                        
                                        
                                        //$potenza=substr($contenuto,408,3); //Potenza fiscale
                                        //$portata=substr($contenuto,411,5); //Portata utile
                                        //$locatario=substr($contenuto,433,1); //Indicatore locatario
                                        //$categoria_uso=substr($contenuto,450,2);
                                        
                                        
                                        if (trim(substr($content, 256, 42)) == "") {
                                            $a_ModelBrand = explode(" ", trim(substr($content, 216, 40)));
                                            
                                            $VehicleBrand = isset($a_ModelBrand[0]) ? strtoupper($a_ModelBrand[0]) : '';
                                            $VehicleModel = isset($a_ModelBrand[1]) ? strtoupper($a_ModelBrand[1]) : '';
                                            
                                        } else {
                                            $VehicleBrand = strtoupper(extract_substr($content, 256, 42));
                                            $VehicleModel = strtoupper(extract_substr($content, 298, 40));
                                        }
                                        
                                        
                                        $Genre = (trim($Genre) == "") ? 'M' : 'D';
                                        $TaxCode = substr($content, 452, 16);
                                        
                                        if ($Genre == 'D') {
                                            $CompanyName = $TrespasserName;
                                            
                                        } else {
                                            
                                            $a_TrespasserName = explode("*", $TrespasserName);
                                            $Surname = $a_TrespasserName[0];
                                            $Name = $a_TrespasserName[1];
                                            
                                            
                                            $BornCity = substr($content, 104, 22);
                                            
                                            $BornDate = substr($content, 135, 4) . "-" . substr($content, 133, 2) . "-" . substr($content, 131, 2);
                                            
                                            
                                            if (!checkIsAValidDate($BornDate)) $BornDate = NULL;
                                            
                                            
                                            if (substr($TaxCode, 11, 1) == 'Z') {
                                                $BornCountryId = substr($TaxCode, 11, 4);
                                            } else {
                                                $BornCountryId = 'Z000';
                                            }
                                            
                                            $BornDateDay = substr($TaxCode, 9, 2);
                                            if ((int)$BornDateDay > 40) {
                                                $Genre = 'F';
                                            }
                                            
                                            
                                            $LicenseDate = (trim(substr($content, 425, 8)) == "" || trim(substr($content, 425, 8)) == "00000000") ? "" : substr($content, 429, 4) . "-" . substr($content, 427, 2) . "-" . substr($content, 425, 2);
                                            
                                            
                                            $LicenseNumber = substr($content, 434, 10);
                                            
                                            
                                        }
                                        
                                        $updateRequestMCTC = array(
                                            array('field' => 'der_risposta','selector' => 'value','type' => 'str','value' => $Genre == 'D' ? $CompanyName : trim($Name).' '.trim($Surname))
                                        );
                                        $rs->Update("dettaglio_richieste_servizi_est", $updateRequestMCTC, "der_cod_richiesta IN(".implode(',',$codiciRichieste).") AND der_oggetto ='$VehiclePlate'");
                                                                          
                                        $rs_FineCityId = $rs->SelectQuery("SELECT DISTINCT CityId FROM Fine WHERE FineDate='" . $FineDate . "' AND VehiclePlate='" . $VehiclePlate . "' AND (StatusTypeId=1 OR StatusTypeId=5 OR StatusTypeId=14) AND CountryId='" . $CountryId . "'");
    
                                        if (mysqli_num_rows($rs_FineCityId) == 0) {
                                            $VehiclePlateTemporary = substr($VehiclePlate,0,2).'P'.substr($VehiclePlate,2);
                                            $rs_FineCityId = $rs->SelectQuery("SELECT DISTINCT CityId FROM Fine WHERE FineDate='" . $FineDate . "' AND TemporaryPlate=1 AND VehiclePlate='" . $VehiclePlateTemporary . "' AND (StatusTypeId=1 OR StatusTypeId=5 OR StatusTypeId=14) AND CountryId='" . $CountryId . "'");
                                        }
                                        while ($r_FineCityId = mysqli_fetch_array($rs_FineCityId)) {
                                            $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='".$r_FineCityId['CityId']."'");
                                            $Code = mysqli_fetch_array($rs_Code)['Code'];
                                            
                                            //CASO DITTA
                                            if ($Genre == 'D') {
                                                $str_Output .= '
                                                <div class="table_caption_H col-sm-1">' . $VehiclePlate . '</div>
                                                <div class="table_caption_H col-sm-3">' . $CompanyName . '</div>
                                                <div class="table_caption_H col-sm-3">' . $FullStreet . '</div>
                                                <div class="table_caption_H col-sm-1">' . $ZIP . '</div>
                                                <div class="table_caption_H col-sm-2">' . $City . '(' . $Province . ')' . '</div>';
                                                
                                                $rs_Trespasser = $rs->Select('Trespasser', "CompanyName='" . addslashes($CompanyName) . "' AND City='" . addslashes($City) . "' AND CustomerId='" . $r_FineCityId['CityId'] . "'");
                                                $i_RowNumber = mysqli_num_rows($rs_Trespasser);
                                                
                                                $a_Insert = array(
                                                    array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $r_FineCityId['CityId']),
                                                    array('field' => 'Code','selector' => 'value','type' => 'int', 'value' => $Code, 'settype' => 'int'),
                                                    array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                                                    array('field' => 'CompanyName', 'selector' => 'value', 'type' => 'str', 'value' => trim($CompanyName)),
                                                    array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => trim($FullStreet)),
                                                    array('field' => 'TaxCode', 'selector' => 'value', 'type' => 'str', 'value' => trim($TaxCode)),
                                                    array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $ZIP),
                                                    array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => trim($City)),
                                                    array('field' => 'CityId', 'selector'=>'value', 'type'=>'str', 'value'=>$CityId),
                                                    array('field' => 'Province', 'selector' => 'value', 'type' => 'str', 'value' => trim($Province)),
                                                    array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserCountryId),
                                                    array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                                                    array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                                                    array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                                                    array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
                                                    array('field' => 'DataSourceTime','selector'=>'value','type'=>'str','value'=>$DataSourceTime),
                                                    array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate)
                                                );
                                                
                                                if ($i_RowNumber == 0) {
                                                    $TrespasserId = 0;
    //                                                 if(PRODUCTION)
    //                                                 {
    
                                                        $TrespasserId = $rs->Insert('Trespasser', $a_Insert); 
                                                        $contaInserite++;
                                                        if ($TrespasserId == 0) {
                                                            $str_Message .= "Poblemi con l'inserimento del trasgressore";
                                                            echo json_encode(
                                                                array(
                                                                    "Esito" => 0,
                                                                    "Messaggio" => $str_Message,
                                                                )
                                                                );
                                                            //echo "Poblemi con l'inserimento del trasgressore";
                                                            DIE;
                                                        }
    //                                                 }
                                                        $str_Output .= '<div class="table_caption_H col-sm-2">ANAG. INSERITA</div>';
                                                } else {
                                                    $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                                                    $TrespasserId = $r_Trespasser['Id'];
                                                    
    //                                                 if(PRODUCTION)
    //                                                 {
                                                        $a_oldTrespasserData = array(
                                                            "CompanyName" => StringOutDB($r_Trespasser['CompanyName']),
                                                            "CountryId" => StringOutDB($r_Trespasser['CountryId']),
                                                            "City" => StringOutDB($r_Trespasser['City']),
                                                            "Province" => StringOutDB($r_Trespasser['Province']),
                                                            "Address" => StringOutDB($r_Trespasser['Address']),
                                                            "ZIP" => StringOutDB($r_Trespasser['ZIP']),
                                                        );
                                                        
                                                        $a_newTrespasserData = array(
                                                            "CompanyName" => trim($CompanyName),
                                                            "CountryId" => $TrespasserCountryId,
                                                            "City" => trim($City),
                                                            "Province" => trim($Province),
                                                            "Address" => trim($FullStreet),
                                                            "ZIP" => $ZIP,
                                                        );
                                                        
                                                        insertTrespsserHistory($r_Trespasser, $a_oldTrespasserData, $a_newTrespasserData, $DataSourceId);
                                                        
                                                        $a_Update = $a_Insert;
                                                    
                                                        $rs->Update('Trespasser', $a_Update, "Id=" . $TrespasserId . " AND CustomerId='" . $r_FineCityId['CityId'] . "'");
                                                        $contaAggiornate++;
    //                                                 }
                                                        $str_Output .= '<div class="table_caption_H col-sm-2">ANAG. AGGIORNATA</div>';
                                                }
                                                
                                            }
                                            //CASO PERSONA FISICA
                                            else {
                                                $str_Output .= '
                                                        <div class="table_caption_H col-sm-1">' . $VehiclePlate . '</div>
                                                        <div class="table_caption_H col-sm-3">' . $Surname . ' ' . $Name . '</div>
                                                        <div class="table_caption_H col-sm-3">' . $FullStreet . '</div>
                                                        <div class="table_caption_H col-sm-1">' . $ZIP . '</div>
                                                        <div class="table_caption_H col-sm-2">' . $City . '(' . $Province . ')' . '</div>';
                                                
                                                if (strlen(trim($TaxCode)) > 0) {
                                                    $rs_Trespasser = $rs->Select('Trespasser', "TaxCode='" . $TaxCode . "' AND CustomerId='" . $r_FineCityId['CityId'] . "'");
                                                    $i_RowNumber = mysqli_num_rows($rs_Trespasser);
                                                    
                                                } else $i_RowNumber = 0;
                                                
                                                $a_Insert = array(
                                                    array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $r_FineCityId['CityId']),
                                                    array('field' => 'Code','selector' => 'value','type' => 'int', 'value' => $Code, 'settype' => 'int'),
                                                    array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                                                    array('field' => 'Surname', 'selector' => 'value', 'type' => 'str', 'value' => trim($Surname)),
                                                    array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => trim($Name)),
                                                    array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => trim($FullStreet)),
                                                    array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $ZIP),
                                                    array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => trim($City)),
                                                    array('field' => 'CityId', 'selector'=>'value', 'type'=>'str', 'value'=>$CityId),
                                                    array('field' => 'Province', 'selector' => 'value', 'type' => 'str', 'value' => trim($Province)),
                                                    array('field' => 'LicenseNumber', 'selector' => 'value', 'type' => 'str', 'value' => trim($LicenseNumber)),
                                                    array('field' => 'BornDate', 'selector' => 'value', 'type' => 'date', 'value' => $BornDate),
                                                    array('field' => 'BornPlace', 'selector' => 'value', 'type' => 'str', 'value' => trim($BornCity)),
                                                    array('field' => 'BornCountryId', 'selector' => 'value', 'type' => 'str', 'value' => trim($BornCountryId)),
                                                    array('field' => 'TaxCode', 'selector' => 'value', 'type' => 'str', 'value' => trim($TaxCode)),
                                                    array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $TrespasserCountryId),
                                                    array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                                                    array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                                                    array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                                                    array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
                                                    array('field' => 'DataSourceTime','selector'=>'value','type'=>'str','value'=>$DataSourceTime),
                                                    array('field' => 'VersionDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate)
                                                );
                                                
                                                if ($LicenseDate != "") {
                                                    $a_Insert[] = array('field' => 'LicenseDate', 'selector' => 'value', 'type' => 'date', 'value' => $LicenseDate);
                                                }
                                                
                                                if ($i_RowNumber == 0) {
                                                    
                                                    $TrespasserId = 0;
    //                                                 if(PRODUCTION)
    //                                                 {
                                                        $TrespasserId = $rs->Insert('Trespasser', $a_Insert);
                                                        $contaInserite++;
                                                        if ($TrespasserId == 0) {
                                                            $str_Message .= "Poblemi con l'inserimento del trasgressore";
                                                            echo json_encode(
                                                                array(
                                                                    "Esito" => 0,
                                                                    "Messaggio" => $str_Message,
                                                                )
                                                            );
                                                            //echo "Poblemi con l'inserimento del trasgressore";
                                                            DIE;
                                                        }
    //                                                 }
                                                        $str_Output .= '<div class="table_caption_H col-sm-2">ANAG. INSERITA</div>';
                                                } else {
                                                    $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                                                    $TrespasserId = $r_Trespasser['Id'];
                                                    
    //                                                 if(PRODUCTION)
    //                                                 {
                                                        $a_oldTrespasserData = array(
                                                            "Surname" => StringOutDB($r_Trespasser['Surname']),
                                                            "Name" => StringOutDB($r_Trespasser['Name']),
                                                            "CountryId" => StringOutDB($r_Trespasser['CountryId']),
                                                            "City" => StringOutDB($r_Trespasser['City']),
                                                            "Province" => StringOutDB($r_Trespasser['Province']),
                                                            "Address" => StringOutDB($r_Trespasser['Address']),
                                                            "ZIP" => StringOutDB($r_Trespasser['ZIP']),
                                                        );
                                                        
                                                        $a_newTrespasserData = array(
                                                            "Surname" => trim($Surname),
                                                            "Name" => trim($Name),
                                                            "CountryId" => $CountryId,
                                                            "City" => trim($City),
                                                            "Province" => trim($Province),
                                                            "Address" => trim($FullStreet),
                                                            "ZIP" => $ZIP,
                                                        );
                                                        
                                                        insertTrespsserHistory($r_Trespasser, $a_oldTrespasserData, $a_newTrespasserData, $DataSourceId);
                                                        
                                                        $a_Update = $a_Insert;
                                                        
                                                        $rs->Update('Trespasser', $a_Update, "Id=" . $TrespasserId . " AND CustomerId='" . $r_FineCityId['CityId'] . "'");
                                                        $contaAggiornate++;
    //                                                 }
                                                        $str_Output .= '<div class="table_caption_H col-sm-2">ANAG. AGGIORNATA</div>';
                                                }
                                                
                                            }
                                            
                                            $rs_Fine = $rs->Select('Fine', "FineDate='" . $FineDate . "' AND VehiclePlate='" . $VehiclePlate . "' AND (StatusTypeId=1 OR StatusTypeId=5) AND CountryId='" . $CountryId . "' AND CityId='" . $r_FineCityId['CityId'] . "'");
                                            //se non trova nulla cercare se è targa prova
                                            if (mysqli_num_rows($rs_Fine) == 0) {
                                                $VehiclePlateTemporary = substr($VehiclePlate,0,2).'P'.substr($VehiclePlate,2);
                                                //echo "<br> VehiclePlateTemporary 2 $VehiclePlateTemporary";
                                                $rs_Fine = $rs->Select('Fine', "FineDate='" . $FineDate . "' AND TemporaryPlate=1 AND VehiclePlate='" . $VehiclePlateTemporary . "' AND (StatusTypeId=1 OR StatusTypeId=5) AND CountryId='" . $CountryId . "' AND CityId='" . $r_FineCityId['CityId'] . "'");
                                            }
                                            
                                            while ($r_Fine = mysqli_fetch_array($rs_Fine)) {
                                                $FineId = $r_Fine['Id'];
    //                                            if(PRODUCTION)
    //                                            {
                                                    $a_Insert = array(
                                                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                                                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TresspasserTypeId, 'settype' => 'int'),
                                                        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => "Inserimento da Import motorizzazione"),
                                                    );
                                                    
                                                    $rs->Insert('FineTrespasser', $a_Insert);
    //                                            }
    
                                               //INIZIO verifica marca modello
                                                $b_ValidBrandModel = checkModelBrand($a_AnomalyBrandModelMap,$VehicleBrand,$VehicleModel,$r_Fine['VehicleTypeId'],$FineId,1,$DataSourceId,$str_Message);
                                               //FINE verifica marca modello
                                               
    //                                            if(PRODUCTION)
    //                                            {
                                                    $a_Update = array(
                                                        array('field' => 'VehicleMass', 'selector' => 'value', 'type' => 'flt', 'value' => $VehicleMass, 'settype' => 'flt'),
                                                        array('field' => 'VehicleLastRevision', 'selector' => 'value', 'type' => 'date', 'value' => $VehicleLastRevision),
                                                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
                                                    );
                                                    
                                                    if($b_ValidBrandModel){
                                                        $a_Update[] = array('field' => 'VehicleBrand', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleBrand);
                                                        $a_Update[] = array('field' => 'VehicleModel', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleModel);
                                                        
                                                        $a_HistoryFineAnomalyBrandModel = array(
                                                            array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                                                            array('field'=>'WrongBrand','selector'=>'value','type'=>'str','value'=>StringOutDB($r_Fine['VehicleBrand'])),
                                                            array('field'=>'WrongModel','selector'=>'value','type'=>'str','value'=>StringOutDB($r_Fine['VehicleModel'])),
                                                            array('field'=>'WrongVehicleTypeId','selector'=>'value','type'=>'int','value'=>$r_Fine['VehicleTypeId'],'settype'=>'int'),
                                                            array('field'=>'CorrectBrand','selector'=>'value','type'=>'str','value'=>$VehicleBrand),
                                                            array('field'=>'CorrectModel','selector'=>'value','type'=>'str','value'=>$VehicleModel),
                                                            array('field'=>'CorrectVehicleTypeId','selector'=>'value','type'=>'int','value'=>$r_Fine['VehicleTypeId'],'settype'=>'int'),
                                                        );
                                                        $rs->Insert("HistoryFineAnomalyBrandModel", $a_HistoryFineAnomalyBrandModel);
                                                    }
                                                    
                                                    if($b_CountryToAssign){
                                                        $a_Update[] = array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => "ZZZZ");
                                                        $a_Update[] = array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => "Da assegnare");
                                                    }
                                                
                                                    $rs->Update('Fine', $a_Update, 'Id=' . $FineId);
    //                                             }
                                                if ($VehicleMass > MASS) {
                                                    $rs_rows = $rs->SelectQuery("
                                            SELECT F.FineTime, AT.ArticleId, AT.Fee, AT.MaxFee, AT.AdditionalNight
                                                   FROM Fine F JOIN FineArticle FA ON F.Id = FA.FineId
                                                   JOIN ArticleTariff AT ON AT.ArticleId = FA.ArticleId
                                                   WHERE AT.AdditionalMass = 1 AND FA.FineId=" . $FineId);
                                                    while ($rs_row = mysqli_fetch_array($rs_rows)) {
                                                        $Fee = $rs_row['Fee'] * FINE_MASS;
                                                        $MaxFee = $rs_row['MaxFee'] * FINE_MASS;
                                                        
                                                        //NOTA 30/06/2022 condizioniamo il calcolo della maggiorazione notturna agli articoli che la prevedono altrimenti viene applicata sempre
                                                        // si è visto comunque che non è un problema perché gli articoli che prevedono addizionale massa prevedono anche la notturna (almeno fino ad ora)
                                                        if ($rs_row['AdditionalNight'] == 1)
                                                        {
                                                            $FineTime = $rs_row['FineTime'];
                                                            $aTime = explode(":", $FineTime);
                                                            
                                                            if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY) || ($aTime[0] == FINE_HOUR_END_DAY && $aTime[1] != "00")) {
                                                                //FINE_MINUTE_START_DAY
                                                                //FINE_MINUTE_END_DAY
                                                                $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                                                                $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);
                                                            }
                                                        }
                                                        
    //                                                     if(PRODUCTION)
    //                                                     {
                                                            $a_Update = array(
                                                                array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
                                                                array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
                                                            );
                                                        
                                                            $rs->Update('FineArticle', $a_Update, 'ArticleId=' . $rs_row['ArticleId'] . " AND FineId=" . $FineId);
    //                                                     }
                                                    }
                                                }
                                            }
                                            $rs_Fine = $rs->Select('Fine', "FineDate='" . $FineDate . "' AND VehiclePlate='" . $VehiclePlate . "' AND StatusTypeId=14 AND CountryId='" . $CountryId . "' AND CityId='" . $r_FineCityId['CityId'] . "' AND Id NOT IN (SELECT FineId FROM FineTrespasser) ");
                                            //se non trova nulla cercare se è targa prova
                                            if (mysqli_num_rows($rs_Fine) == 0) {
                                                $VehiclePlateTemporary = substr($VehiclePlate,0,2).'P'.substr($VehiclePlate,2);
                                               // echo "<br> VehiclePlateTemporary3 $VehiclePlateTemporary";
                                                $rs_Fine = $rs->Select('Fine', "FineDate='" . $FineDate . "' AND TemporaryPlate=1 AND VehiclePlate='" . $VehiclePlateTemporary . "' AND StatusTypeId=14 AND CountryId='" . $CountryId . "' AND CityId='" . $r_FineCityId['CityId'] . "' AND Id NOT IN (SELECT FineId FROM FineTrespasser) ");                      
                                            }
                                            
                                            while ($r_Fine = mysqli_fetch_array($rs_Fine)) {
                                                $FineId = $r_Fine['Id'];
    //                                             if(PRODUCTION)
    //                                             {
                                                    $a_Insert = array(
                                                        array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                                        array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                                                        array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TresspasserTypeId, 'settype' => 'int'),
                                                        array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => "Inserimento da Import motorizzazione"),
                                                    );
                                                    $rs->Insert('FineTrespasser', $a_Insert);
                                                    
                                                    if($b_CountryToAssign){
                                                        $a_Update = array(
                                                            array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => "ZZZZ"),
                                                            array('field' => 'VehicleCountry', 'selector' => 'value', 'type' => 'str', 'value' => "Da assegnare")
                                                        );
                                                        
                                                        $rs->Update('Fine', $a_Update, 'Id=' . $FineId);
                                                    }
    //                                             }
                                            }
                                        }
                                        
                                        if(!empty($controlli->listaAnomalie)){
                                            $str_Error = "Riga: ".($cont+1)." - ".implode("; ", $controlli->listaAnomalie);
                                            $chk_Data = 10;
                                        }
                                    } else {
                                        $chk_Data = 50;
                                        $str_Error = "Marca veicolo non presente";                                    
                                    }
                            }
                            if ($chk_Data != 90) {
                                $VehiclePlate = extract_substr($content, 25, 8);
                                
                                $FineDate = substr($content, 15, 4) . "-" . substr($content, 13, 2) . "-" . substr($content, 11, 2);
                                //se $FineDate non è una data valida non va avanti a inserire l'anomalia ma traccia la riga errata sui log                            
                                $TestDate = DateTime::createFromFormat('Y-m-d', $FineDate);
                                if($TestDate !== false)
                                {
                                    $rs_Fine = $rs->Select('Fine', "FineDate='" . $FineDate . "' AND VehiclePlate='" . $VehiclePlate . "' AND (StatusTypeId=1 OR StatusTypeId=5 OR StatusTypeId=14) AND CountryId='" . $CountryId . "'");
                                    //se non trova nulla cercare se è targa prova
                                    if (mysqli_num_rows($rs_Fine) == 0) {
                                        $VehiclePlateTemporary = substr($VehiclePlate,0,2).'P'.substr($VehiclePlate,2);
                                        $rs_Fine = $rs->Select('Fine', "FineDate='" . $FineDate . "' AND TemporaryPlate=1 AND VehiclePlate='" . $VehiclePlateTemporary . "' AND (StatusTypeId=1 OR StatusTypeId=5 OR StatusTypeId=14) AND CountryId='" . $CountryId . "'");
                                    }
                                
                                    while ($r_Fine = mysqli_fetch_array($rs_Fine)) {
                                        $FineId = $r_Fine['Id'];
                                        $Anomaly = substr($str_Error, 0, 500);                                
                                        $str_Error = "Id:" . $FineId . " - " . $str_Error;
    //                                     if(PRODUCTION)
    //                                     {
                                            $rs_FineAnomaly = $rs->Select("FineAnomaly", "FineId=$FineId");
                                            if(mysqli_num_rows($rs_FineAnomaly) <= 0){
                                                $a_Insert = array(
                                                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                                    array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                                                    array('field' => 'Anomaly', 'selector' => 'value', 'type' => 'str', 'value' => $Anomaly),
                                                );
                                                
                                                $rs->Insert('FineAnomaly', $a_Insert);
                                                
                                                if($r_Fine['FineTypeId'] == 1 || $r_Fine['FineTypeId'] == 2){
                                                    $a_Update = array(
                                                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                                                    );
                                                    
                                                    $rs->Update('Fine', $a_Update, 'Id=' . $FineId);
                                                }
                                            }
    //                                     }
                                    } 
                                }
                                else{
                                    trigger_error("riga numero ".($cont+1). " con data errata: ".$FineDate, E_USER_NOTICE);
                                }
                            }
                            if ($str_Error != '') {
                                $str_Message .= '
                                <div class="col-sm-12">
                                    <div class="table_caption_H col-sm-12 alert-danger">
                                        ' . $str_Error . '
                                    </div>
                                    <div class="clean_row HSpace4"></div>
                                </div>
                                ';
                            } else {
                                $str_Message .= '
                                <div class="col-sm-12">
                                    ' . $str_Output . '
                                    <div class="clean_row HSpace4"></div>
    			                </div>
    			                ';
                                
                            }
                        }
                        $progress->writeJSON($cont, $ProgressFile);
                        $cont++;
                    }
                }
                
                    //salvo il file della risposta
                	trigger_error("Salvo file di risposta: inserite ".$contaInserite. "aggiornate ".$contaAggiornate, E_USER_NOTICE);
                
                    $a_Import = array(
                        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                        array('field'=>'Date','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                        array('field'=>'Type','selector'=>'value','type'=>'int','value'=>5), //serve nuovo tipo per risposta visura mctc
                        array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$str_FileName),
                        array('field'=>'RowsCount','selector'=>'value','type'=>'int','value'=>$contaInserite+$contaAggiornate),
                    );
                    $importedFilesId = $rs->Insert('ImportedFiles',$a_Import);
                    trigger_error("Registrato file di risposta: ".$importedFilesId, E_USER_NOTICE);
                    
                    $rs->End_Transaction();
                    
                }
            } else {
                $str_Message .= '
                 <div class="col-sm-12">
                    <div class="table_caption_H col-sm-12 alert-danger">
                        Errore imprevisto su banca dati, controllare lo stato delle richieste
                    </div>
                    <div class="clean_row HSpace4"></div>
                 </div>';
            }
        } else {
            $str_Message .= '
                 <div class="col-sm-12">
                    <div class="table_caption_H col-sm-12 alert-danger">
                        File di richiesta non trovato
                    </div>
                    <div class="clean_row HSpace4"></div>
                 </div>';
        }
        $str_Message = preg_replace('~>\s+<~', '><', $str_Message);
        echo json_encode(
            array(
                "Esito" => 1,
                "Messaggio" => trim($str_Message),
            )
        );
        DIE;
        
    }else{
        
        $str_out = '
        <div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-12">CONTROLLO RICHIESTE PENDENTI</div>
				<div class="clean_row HSpace4"></div>
			</div>
            ';
        
        
        
        $file = $r_ProcessingMCTC['FileInput'];
        $check_file_exist = $path . $file;
        if(PRODUCTION){   
            $contents_on_server = ftp_nlist($conn, $path);
        }

         if (PRODUCTION && in_array($check_file_exist, $contents_on_server))
         {
            $chk_inp_file = true;
            $str_out .= '
            <div class="table_caption_H col-sm-12 alert-danger">
                File richiesta ancora presente sul server
            </div>
             <div class="clean_row HSpace4"></div>
            ';
        }else{
            $str_out .= '
            <div class="table_caption_H col-sm-12 alert-success">
                Nessuna richiesta precedente presente
            </div>
             <div class="clean_row HSpace4"></div>
            ';
        }
        if ($chk_inp_file) {
            echo $str_out;
            DIE;
        }
        
        $file = $r_ProcessingMCTC['FileOutput'];
        $check_file_exist = $path . $file;
        if(PRODUCTION){
            $contents_on_server = ftp_nlist($conn, $path);
        }
         if (PRODUCTION && in_array($check_file_exist, $contents_on_server)){
            $chk_out_file = true;
            $str_out .= '
            <div class="table_caption_H col-sm-12 alert-danger">
                File pronto per l\'importazione presente
            </div>
            ';
        } else {
            $str_out .= '
            <div class="table_caption_H col-sm-12 alert-success">
                Nessun file per l\'importazione presente
            </div>
            ';
        };
        
        if ($chk_out_file) {
            echo $str_out;
            DIE;
        }
        
        if(PRODUCTION){
            ftp_close($conn);
        }
        
        $str_out .= '
        <div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-12">CARICAMENTO NUOVA RICHIESTA</div>
				<div class="clean_row HSpace4"></div>
			</div>
            ';
        echo $str_out;
        
        $str_WhereCity="";
        
        if(isset($_POST['checkbox'])) {
            $str_WhereCity = "AND (";
            $n_CityId=0;
            foreach($_POST['checkbox'] as $CityId) {
                if($n_CityId>0) $str_WhereCity.= " OR ";
                $str_WhereCity .= "CityId='". $CityId ."'";
                $n_CityId++;
            }
            $str_WhereCity.=")";
        }


        //La join su FineAnomaly è stata messa per evitare di fare richieste su atti che per qualche motivo hanno una anomalia non ancora sanata 
        //visto che ci può essere una sola anomalia per atto, altrimenti se lo elaborano più volte e ci sono più anomalie va in violazione di chiave
        $table_rows = $rs->SelectQuery("
          SELECT CityId, FineDate, VehiclePlate, VehicleTypeId, TemporaryPlate
          FROM Fine
          WHERE
          (StatusTypeId=1 OR StatusTypeId=14)
          AND ControllerId IS NOT NULL
          AND CountryId='Z000'
          AND ProtocolYear>2018
          AND Id NOT IN (SELECT FineId FROM FineTrespasser)
          AND Id NOT IN(SELECT FineId AS Id FROM FineAnomaly)
          ".$str_WhereCity."
          GROUP BY CityId, FineDate, VehiclePlate, VehicleTypeId, TemporaryPlate ");
        
        $risultati = array();
        while($riga = $rs->getArrayLine($table_rows)){
            $risultati[$riga['CityId']][] = $riga;
        }
        
        $RowNumber = mysqli_num_rows($table_rows);
        $requestFile = null;
        if ($RowNumber > 0) {
            
            $FileName = "Request_" . date("Y-m-d_H-i");
            $OutFile = fopen((PRODUCTION) 
                ? NATIONAL_REQUEST . "/" . $FileName 
                : TESTVISURE_FOLDER . "/" . $FileName , "w") or die("Unable to open file!");
            
            $requestFile = $FileName;
          
            $n_ContFine = 0;
            foreach($risultati as $ente => $elementi){
                
                $insertRequest = array(
                    array('field' => 'rse_ente','selector' => 'value','type' => 'str','value' => $ente),
                    array('field' => 'rse_utente_servizio','selector' => 'value','type' => 'str','value' => $username),
                    array('field' => 'rse_ute_richiesta','selector' => 'value','type' => 'str','value' => $_SESSION['username']),
                    array('field' => 'rse_data_richiesta','selector' => 'value','type' => 'date','value' => $dataRichiesta),
                    array('field' => 'rse_ora_richiesta','selector' => 'value','type' => 'str','value' => $oraRichiesta),
                    array('field' => 'rse_tipo','selector' => 'value','type' => 'int','value' => 8, 'settype' => 'int'),
                );
                
                $codRichiesta = $rs->insert("richieste_servizi_esterni", $insertRequest);
                $progressivo = 1;
                
                foreach ($elementi as $table_row) {
                    //vanno esaminati anche altri tipi oltre il 12, ad esempio il 4
                    if(strstr($table_row['VehiclePlate'],'X') && 
                        (($table_row['VehicleTypeId'] == 12) //AUTOARTICOLATO
                        || ($table_row['VehicleTypeId'] == 4) //AUTOCARRO
                        || ($table_row['VehicleTypeId'] == 11) //SEMIRIMORCHIO
                        )
                    )
                        $VehicleType='R';
                    elseif ($table_row['VehicleTypeId'] == 2) $VehicleType = 'M';
                    elseif ($table_row['VehicleTypeId'] == 9) $VehicleType = 'C';
                    elseif ($table_row['VehicleTypeId'] == 7) $VehicleType = 'R';
                    else $VehicleType = 'A';
                    
                    $PlateOut = $table_row['VehiclePlate'];
                    $FineDate = $table_row['FineDate'];
                    $TemporaryPlate = $table_row['TemporaryPlate'];
                    //echo "<br> $TemporaryPlate";
                    //echo "<br> $PlateOut";
                    $a_Date = explode("-", $FineDate);
                    $FineDateOut = $a_Date[2] . $a_Date[1] . $a_Date[0];
                    $RequestDate = date("dmY");
                                    //Se la targa è contrassegnata come provvisoria elimina il carattere P dalla targa se presente
                    if ($table_row['VehicleTypeId'] == 1 || $table_row['VehicleTypeId'] == 2){
                        if ($table_row['TemporaryPlate'] && $PlateOut[2] == 'P')
                            $PlateOut = substr_replace($PlateOut, '', 2, 1);
                    }
                
                    //se è una targa di prova a prescindere dal tipo veicolo va tolta la P
                    /*if ($TemporaryPlate == 1) {
                        echo "<br>PlateOut $PlateOut";
                        $pos = strpos($PlateOut, 'P');
                        $PlateOut1 = substr($PlateOut, 0, $pos);
                        echo "<br>PlateOut1 $PlateOut1";
                        $PlateOut2 = substr($PlateOut, $pos+1);
                        echo "<br>PlateOut2 $PlateOut2";
                        $PlateOut = $PlateOut1.$PlateOut2;
                        echo "<br>PlateOut ripulito $PlateOut";
                    }*/
                        
                    $Plate1 = substr($PlateOut, 0, 2);               
                    $Plate2 = substr($PlateOut, 2);
      
                     if ($VehicleType == 'A') {
                        if (strlen($Plate2) == 5) {
                            if (is_numeric(substr($Plate2, 1))) $Plate2 = "0" . $Plate2;
                        }
                    }
                    
    				$plate = "90" . $Plate1 . $VehicleType . $Plate2;
                    
                    for ($i = strlen($plate); $i < 11; $i++) {
                        $plate .= " ";
                    }
                    
                    for ($i = strlen($RequestDate); $i < 16; $i++) {
                        $RequestDate .= " ";
                    }
                    
                    //22/02/2022 per non far sforare targhe lunghe trovate su Formigine
                    for ($i = strlen(($plate . $PlateOut)); $i < (11+14); $i++) {
                        $PlateOut .= " ";
                    }
                    
                    $ProvinceOut = $r_ProcessingMCTC['Province'];
                    $NameOut = str_pad($r_ProcessingMCTC['Name'], 41, " ");
                    
                    $txt = $plate . $PlateOut . $FineDateOut . "        " . $ProvinceOut.$NameOut."\n";
                    //trigger_error($txt, E_USER_NOTICE);
                    fwrite($OutFile, $txt);
                    
                    $insertRequest = array(
                        array('field' => 'der_cod_richiesta','selector' => 'value','type' => 'str','value' => $codRichiesta, 'settype' => 'str'),
                        array('field' => 'der_progressivo','selector' => 'value','type' => 'int','value' => $progressivo, 'settype' => 'int'),
                        array('field' => 'der_oggetto','selector' => 'value','type' => 'str','value' => $PlateOut),
                    );
                    $rs->insert("dettaglio_richieste_servizi_est", $insertRequest);
                    
                    $progressivo++;
                    
                    $n_ContFine++;
                    
                    //ciclo per inserire il record che indica la richiesta fatta alla motorizzazione per il preinserimento da fatturare
                    $fines = $rs->Select('V_Violation', "VehiclePlate='" . $PlateOut . "' AND CountryId='Z000' AND StatusTypeId=1 AND FineDate='" . $FineDate . "'", 'Id');
                    while ($fine = mysqli_fetch_array($fines)) {
                        trigger_error("Da registrare fine: ".$fine['Id']. " - ".$fine['CityId'], E_USER_NOTICE);
                        $CityId = $fine['CityId'];
                        $EntityId = 41;
                        $NotificationTypeIdInHistory = 1;
                        $NotificationDate = date("Y-m-d");
                        $FlowDate = date("Y-m-d");
                        $SendDate = date("Y-m-d");
                        $PrintDate = date("Y-m-d");
                        
                        $rs->Start_Transaction();
                        $aInsert = array(
                            array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeIdInHistory),
                            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $fine['Id'], 'settype' => 'int'),
                            array('field' => 'EntityId', 'selector' => 'value', 'type' => 'int', 'value' => $EntityId),
                            array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                            array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => $FlowDate),
                            array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                            array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => $PrintDate),
                            array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $FileName),
                        );
                        
                        $chk = $rs->Insert('FineHistory', $aInsert);
                        $rs->End_Transaction();
                        trigger_error("Registrato fine: ".$fine['Id'], E_USER_NOTICE);
                        if ($chk == 0) {
                            trigger_error("Poblemi con l'inserimento nello storico per FineId: ".$fine['Id'], E_USER_NOTICE);
                            echo "Poblemi con l'inserimento nello storico per FineId: ".$fine['Id'];
                            DIE;
                        }
                    }
                    
                    //ciclo per inserire il record che indica la richiesta fatta alla motorizzazione da fatturare
                    $fines = $rs->Select('V_Violation', "VehiclePlate='" . $PlateOut . "' AND CountryId='Z000' AND StatusTypeId=14 AND Id NOT IN (SELECT FineId FROM FineTrespasser) AND FineDate='" . $FineDate . "'", 'Id');
                    while ($fine = mysqli_fetch_array($fines)) {
                        
                        $CityId = $fine['CityId'];
                        $EntityId = 41;
                        $NotificationTypeIdInHistory = 1;
                        $NotificationDate = date("Y-m-d");
                        $FlowDate = date("Y-m-d");
                        $SendDate = date("Y-m-d");
                        $PrintDate = date("Y-m-d");
                        
                        $rs->Start_Transaction();
                        $aInsert = array(
                            array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeIdInHistory),
                            array('field' => 'CityId', 'selector' => 'value', 'type' => 'str', 'value' => $CityId),
                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $fine['Id'], 'settype' => 'int'),
                            array('field' => 'EntityId', 'selector' => 'value', 'type' => 'int', 'value' => $EntityId),
                            array('field' => 'NotificationDate', 'selector' => 'value', 'type' => 'date', 'value' => $NotificationDate),
                            array('field' => 'FlowDate', 'selector' => 'value', 'type' => 'date', 'value' => $FlowDate),
                            array('field' => 'SendDate', 'selector' => 'value', 'type' => 'date', 'value' => $SendDate),
                            array('field' => 'PrintDate', 'selector' => 'value', 'type' => 'date', 'value' => $PrintDate),
                            array('field' => 'Documentation', 'selector' => 'value', 'type' => 'str', 'value' => $FileName),
                        );
                        
                        $chk = $rs->Insert('FineHistory', $aInsert);
                        $rs->End_Transaction();
                        if ($chk == 0) {
                            echo "Poblemi con l'inserimento nello storico per FineId: ".$fine['Id'];
                            DIE;
                        }         
                    }
                }
            }
            fclose($OutFile);
            
//             if(!PRODUCTION) {
                //salvo il file della richiesta
                trigger_error("salvo il file della richiesta in test: ".$requestFile, E_USER_NOTICE);
                $rs->Start_Transaction();
                $a_Import = array(
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                    array('field'=>'Date','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                    array('field'=>'Type','selector'=>'value','type'=>'int','value'=>4), //serve nuovo tipo per richiesta visura mctc
                    array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$requestFile),
                    array('field'=>'RowsCount','selector'=>'value','type'=>'int','value'=>$n_ContFine), //per qualche motivo conta una riga vuota in fondo al file
                );
                $importedFilesId = $rs->Insert('ImportedFiles',$a_Import);
                
                trigger_error("Registrato file di richiesta in test: ".$importedFilesId, E_USER_NOTICE);
                $rs->End_Transaction();
//             }
            if (file_exists((PRODUCTION) 
                ? NATIONAL_REQUEST . $r_ProcessingMCTC['FileFlag'] 
                : TESTVISURE_FOLDER . $r_ProcessingMCTC['FileFlag'])) {
                    
                $b_Delete = @unlink((PRODUCTION)
                    ? NATIONAL_REQUEST . $r_ProcessingMCTC['FileFlag']
                    : TESTVISURE_FOLDER . $r_ProcessingMCTC['FileFlag']);
                if (!$b_Delete) {
                    echo
                    '<div class="col-sm-12">
                    <div class="table_caption_H col-sm-12 alert-danger">
                        PROBLEMI NELLA CANCELLAZIONE '.$r_ProcessingMCTC['FileFlag'].'
                    </div>
                    <div class="clean_row HSpace4"></div>
                </div>';
                    DIE;
                }
            }
            
            $OutFile = fopen((PRODUCTION) 
                ? NATIONAL_REQUEST . $r_ProcessingMCTC['FileFlag'] 
                : TESTVISURE_FOLDER . $r_ProcessingMCTC['FileFlag'], "w") or die("Unable to open file!");
            fwrite($OutFile, "INPUT COMPLETO " . $RequestDate);
            fclose($OutFile);

            if(PRODUCTION){
                //RIAPRE LA CONNESSIONE
                $conn = @ftp_connect($server);
                
                if ($conn) {
                    $login = @ftp_login($conn, $username, $password);
                    ftp_pasv($conn, FTP_PASSIVE_MODE_MCTC);
                    if ($login) {
                        echo
                        '<div class="table_caption_H col-sm-12 alert-success">
                            Connessione riuscita
                        </div>';
                    } else {
                        echo
                        '<div class="table_caption_H col-sm-12 alert-danger">
                            Tentativo di login fallito. Verificare che le credenziali di accesso siano ancora valide.
                        </div>';
                        DIE;
                    }
                } else {
                    echo 
                    '<div class="table_caption_H col-sm-12 alert-danger">
                        Tentativo di connessione fallito
                    </div>';
                    DIE;
                }
                
              $upload = @ftp_put($conn, $r_ProcessingMCTC['FileInput'], NATIONAL_REQUEST . "/" . $FileName, FTP_BINARY);
              if ($upload) {
                 echo
                 '<div class="col-sm-12">
                     <div class="table_caption_H col-sm-12 alert-success">
                        FILE RICHIESTA DATI motorizzazione caricato
                     </div>
                 <div class="clean_row HSpace4"></div>
                 </div>';
             
                $upload = @ftp_put($conn, $r_ProcessingMCTC['FileFlag'], NATIONAL_REQUEST . $r_ProcessingMCTC['FileFlag'], FTP_BINARY);
                 if ($upload) {
                     
                     //salvo il file della richiesta
                     trigger_error("salvo il file della richiesta: ".$requestFile, E_USER_NOTICE);
                     $rs->Start_Transaction();
                     $a_Import = array(
                         array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                         array('field'=>'Date','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                         array('field'=>'Type','selector'=>'value','type'=>'int','value'=>4), //serve nuovo tipo per richiesta visura mctc
                         array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$requestFile),
                         array('field'=>'RowsCount','selector'=>'value','type'=>'int','value'=>$n_ContFine), //per qualche motivo conta una riga vuota in fondo al file
                     );
                     $importedFilesId = $rs->Insert('ImportedFiles',$a_Import);
                     
                     trigger_error("Registrato file di richiesta: ".$importedFilesId, E_USER_NOTICE);
                     $rs->End_Transaction();

                     ftp_close($conn);
                     
                     echo
                     '<div class="col-sm-12">
                     <div class="table_caption_H col-sm-12 alert-success">
                     FILE CONTROLLO DATI motorizzazione caricato
                     </div>
                     <div class="clean_row HSpace4"></div>
                     </div>';
                     DIE;
                 } else {
                     echo
                     '<div class="col-sm-12">
                     <div class="table_caption_H col-sm-12 alert-danger">
                     Problemi nel caricamento FILE CONTROLLO DATI motorizzazione
                     </div>
                     <div class="clean_row HSpace4"></div>
                     </div>';
                     DIE;
                 }
             
             } else {
                 echo
                 '<div class="col-sm-12">
                 <div class="table_caption_H col-sm-12 alert-danger">
                 Problemi nel caricamento FILE RICHIESTA DATI motorizzazione
                 </div>
                 <div class="clean_row HSpace4"></div>
                 </div>';
                 DIE;
             }
            } 
            
        } else {          
            echo '
            <div class="table_caption_H col-sm-12 alert-warning">
                Nessun dato per esportazione
            </div>
            ';
            DIE;
        }             
    }
} else {
    if ($str_Action != "import"){
        echo json_encode(
            array(
                "Esito" => 1,
                "Messaggio" => trim($str_Message),
            )
            );
        DIE;
    } else {
        DIE;
    }
}
