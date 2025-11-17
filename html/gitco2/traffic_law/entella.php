<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');


ini_set('max_execution_time', 30000);

define('ENTELLA_CUSTOMER_ID', 'U173');



$rs_ProcessingMCTC = $rs->Select('ProcessingMCTC', "Disabled=0", 'Position');
while ($r_ProcessingMCTC = mysqli_fetch_array($rs_ProcessingMCTC)) {

    $ftp_connection = false;
    $chk_inp_file   = false;
    $server         = $r_ProcessingMCTC['FTP'];
    $username       = $r_ProcessingMCTC['Username'];
    $password       = $r_ProcessingMCTC['Password'];

    $NotificationTypeId = 1;
    $CountryId = "Z000";


    $conn = @ftp_connect($server);

    if ($conn) {
        $login = @ftp_login($conn, $username, $password);
        if ($login) {
            $ftp_connection = true;
            $str_Connection = '
                <div class="table_caption_H col-sm-12 alert-success">
                    '. $r_ProcessingMCTC['FTP'] .' - Connessione riuscita
                </div>
            ';

            $path = "/";

            $file = $r_ProcessingMCTC['FileOutput'];

            $check_file_exist = $path . $file;

            $contents_on_server = ftp_nlist($conn, $path);
            if (in_array($check_file_exist, $contents_on_server)) {
                $chk_inp_file = true;
                $str_Connection .= '
                    <div class="table_caption_H col-sm-12 alert-success">
                        file pronto per l\'importazione;
                    </div>
                    ';
            } else {
                $str_Connection .= '
                    <div class="table_caption_H col-sm-12 alert-warning">
                        file non ancora presente per l\'importazione
                    </div>
                    ';
            }


        } else {
            $str_Connection = '
            <div class="table_caption_H col-sm-12 alert-danger">
                Tentativo di login fallito
            </div>';
        }
    } else {
        $str_Connection = '
    <div class="table_caption_H col-sm-12 alert-danger">
        Tentativo di connessione fallito
    </div>';
    }


    $str_out .= '
		<div class="col-sm-12">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-12">CONNESSIONE FTP</div>	
			</div>
			
			<div class="clean_row HSpace4"></div>
            
            <div class="col-sm-12">
                ' . $str_Connection . '
			</div>

			<div class="clean_row HSpace4"></div>			
			';


    echo $str_out;

    if ($ftp_connection) {
        if ($chk_inp_file) {

            $str_FileName = "Response_".$r_ProcessingMCTC['CityId']."_" . date("Y-m-d_H-i");


            $download = @ftp_get($conn, NATIONAL_REQUEST . "/" . $str_FileName, $file, FTP_BINARY);
            if ($download) {
                echo
                '<div class="col-sm-12">
                <div class="table_caption_H col-sm-12 alert-success">
                    FILE DATI motorizzazione scaricato
                </div>
                <div class="clean_row HSpace4"></div>
            </div>';

                $delete = @ftp_delete($conn, $file);
                if ($delete) {
                    echo
                    '<div class="col-sm-12">
                            <div class="table_caption_H col-sm-12 alert-success">
                                FILE DATI cancellato correttamente dal server
                            </div>
                            <div class="clean_row HSpace4"></div>
                        </div>';
                } else {
                    echo
                    '<div class="col-sm-12">
                            <div class="table_caption_H col-sm-12 alert-danger">
                                Problemi nella cancellazione dl file su server
                            </div>
                            <div class="clean_row HSpace4"></div>
                        </div>';
                }

            } else {
                echo
                '<div class="col-sm-12">
                <div class="table_caption_H col-sm-12 alert-danger">
                    Problemi nel download FILE DATI motorizzazione
                </div>
                <div class="clean_row HSpace4"></div>
            </div>';
            }


            if ($download) {
                $a_ChkModelBrand = array(
                    "FIAT",
                    "NISSAN",
                    "FORD",
                    "AUDI",
                    "MITSUBISHI",
                    "DACIA",
                    "TOYOTA",
                    "ALFA",
                    "MINI",
                    "HONDA",
                    "MAZDA",
                    "LANCIA",
                );

                //Title
                //StreetName
                //ZIP
                //Suburb


                $DataSourceId = 2;
                $DataSourceDate = date("Y-m-d");

                $a_ZIPCity = array();

                $rs_ZIPCity = $rs->SelectQuery("SELECT DISTINCT Title FROM sarida.ZIPCity");

                while ($r_ZIPCity = mysqli_fetch_array($rs_ZIPCity)) {
                    $a_ZIPCity[] = strtoupper($r_ZIPCity['Title']);
                }


                $cont = 0;

                $f_ImportFile = fopen(NATIONAL_REQUEST . "/" . $str_FileName, "r+");

                $rs->Start_Transaction();
                while (!feof($f_ImportFile)) {
                    $cont++;

                    $str_Error = '';
                    $str_Output = '';
                    $content = fgets($f_ImportFile);


                    if (strlen($content) > 560) {
                        $chk_Data = substr($content, 0, 2);


                        switch ($chk_Data) {
                            case 91:
                                $str_Error = "Errore nei campi trasmessi";
                                break;
                            case 92:
                                $str_Error = "Data infrazione maggiore data cessazione";
                                break;
                            case 93:
                                $str_Error = "Provincia targa errata o tipo veicolo errato o numero targa errata";
                                break;
                            case 94:
                                $str_Error = "Mancata digitazione utente";
                                break;
                            case 95:
                                $str_Error = "Errore per targa ciclomotore";
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
                                    $Province = extract_substr($content, 161, 2);

                                    $Street = extract_substr($content, 166, 5);
                                    $Address = extract_substr($content, 171, 34);
                                    $Number = extract_substr($content, 205, 6);

                                    $ZIP = extract_substr($content, 211, 5);

                                    if(strlen(trim($ZIP))!=5 || $ZIP=="00000"){
                                        $chk_Data = 30;
                                        $str_Error = "Controllare CAP utente";
                                    }



                                    $VehiclePlate = extract_substr($content, 25, 8);


                                    $FullStreet = ChkAddressInsertDB($Street, $Address, $Number);

                                    if (in_array($City, $a_ZIPCity)) {
                                        if (substr($ZIP, 2, 3) == "100" || substr($ZIP, 2, 3) == "   ") {

                                            $a_FullStreet = explode(" ", $FullStreet);

                                            $str_Where = "";

                                            for ($i = 1; $i < count($a_FullStreet) - 1; $i++) {
                                                $str_Where .= "AND StreetName LIKE '%" . str_replace(".", "", addslashes($a_FullStreet[$i])) . "%' ";
                                            }

                                            $rs_ZIP = $rs->SelectQuery("SELECT ZIP FROM sarida.ZIPCity WHERE Title LIKE '%" . $City . "%' " . $str_Where);
                                            $RowNumber = mysqli_num_rows($rs_ZIP);
                                            if ($RowNumber == 0) echo "<div class=\"table_caption_H col-sm-12 alert-danger\">
                                                                NON Trovato cappato: $VehiclePlate
                                                            </div>";
                                            else if ($RowNumber == 1) {
                                                $r_ZIP = mysqli_fetch_array($rs_ZIP);
                                                $ZIP = $r_ZIP['ZIP'];
                                                echo "
                                            <div class=\"table_caption_H col-sm-12 alert-success\">
                                                Trovato cap: $ZIP
                                            </div>";
                                            } else {
                                                $r_ZIP = mysqli_fetch_array($rs_ZIP);
                                                $ZIP = $r_ZIP['ZIP'];

                                                $rs_ZIP = $rs->SelectQuery("SELECT ZIP FROM sarida.ZIPCity WHERE Title LIKE '%" . $City . "%' " . $str_Where . " AND StreetName LIKE '%" . addslashes($a_FullStreet[0]) . "%'");
                                                $RowNumber = mysqli_num_rows($rs_ZIP);
                                                if ($RowNumber == 1) {
                                                    $r_ZIP = mysqli_fetch_array($rs_ZIP);
                                                    $ZIP = $r_ZIP['ZIP'];
                                                    echo "
                                            <div class=\"table_caption_H col-sm-12 alert-success\">
                                                Trovato cap: $ZIP
                                            </div>";
                                                } else {
                                                    echo "
                                            <div class=\"table_caption_H col-sm-12 alert-warning\">
                                                CONTROLLARE - Trovati più cap: $ZIP
                                            </div>";
                                                }
                                            }

                                        }

                                    }


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
                                        $str_Output .= '
                                            <div class="table_caption_H col-sm-1">' . $VehiclePlate . '</div>
                                            <div class="table_caption_H col-sm-4">' . $CompanyName . '</div>
                                            <div class="table_caption_H col-sm-4">' . $FullStreet . '</div>
                                            <div class="table_caption_H col-sm-1">' . $ZIP . '</div>
                                            <div class="table_caption_H col-sm-2">' . $City . '(' . $Province . ')' . '</div>
                                        ';
                                    } else {

                                        $a_TrespasserName = explode("*", $TrespasserName);
                                        $Surname = $a_TrespasserName[0];
                                        $Name = $a_TrespasserName[1];

                                        $BornCity = substr($content, 104, 22);

                                        $BornDate = (trim(substr($content, 131, 8)) == "" || trim(substr($content, 529, 8)) == "00000000") ? NULL : substr($content, 135, 4) . "-" . substr($content, 133, 2) . "-" . substr($content, 131, 2);

                                        if (substr($TaxCode, 11, 1) == 'Z') {
                                            $BornProvince = substr($content, 128, 3);
                                            $BornPlace = $BornCity . " - " . $BornProvince;

                                        } else {

                                            $BornProvince = substr($content, 126, 2);
                                            $BornPlace = $BornCity . " (" . $BornProvince . ")";
                                        }

                                        $BornDateDay = substr($TaxCode, 9, 2);
                                        if ((int)$BornDateDay > 40) {
                                            $Genre = 'F';
                                        }



                                        $LicenseDate = (trim(substr($content, 425, 8)) == "" || trim(substr($content, 425, 8)) == "00000000") ? "" : substr($content, 429, 4) . "-" . substr($content, 427, 2) . "-" . substr($content, 425, 2);


                                        $LicenseNumber = substr($content, 434, 10);

                                        $str_Output .= '
                                            <div class="table_caption_H col-sm-1">' . $VehiclePlate . '</div>
                                            <div class="table_caption_H col-sm-4">' . $Surname . ' ' . $Name . '</div>
                                            <div class="table_caption_H col-sm-4">' . $FullStreet . '</div>
                                            <div class="table_caption_H col-sm-1">' . $ZIP . '</div>
                                            <div class="table_caption_H col-sm-2">' . $City . '(' . $Province . ')' . '</div>
                                        
                                        ';

                                    }
                                    
                                    $rs_Code = $rs->SelectQuery("SELECT IFNULL(MAX(Code)+1, 1) Code FROM Trespasser WHERE CustomerId='".ENTELLA_CUSTOMER_ID."'");
                                    $Code = mysqli_fetch_array($rs_Code)['Code'];

                                    if ($Genre == 'D') {
                                        $rs_Trespasser = $rs->Select('Trespasser', "CompanyName='" . addslashes($CompanyName) . "' AND City='" . addslashes($City) . "'");
                                        $i_RowNumber = mysqli_num_rows($rs_Trespasser);

                                        if ($i_RowNumber == 0) {


                                            $a_Insert = array(
                                                array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => ENTELLA_CUSTOMER_ID),
                                                array('field' => 'Code','selector' => 'value','type' => 'int', 'value' => $Code, 'settype' => 'int'),       
                                                array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                                                array('field' => 'CompanyName', 'selector' => 'value', 'type' => 'str', 'value' => $CompanyName),
                                                array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $FullStreet),
                                                array('field' => 'TaxCode', 'selector' => 'value', 'type' => 'str', 'value' => $TaxCode),
                                                array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $ZIP),
                                                array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $City),
                                                array('field' => 'Province', 'selector' => 'value', 'type' => 'str', 'value' => $Province),
                                                array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                                                array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                                                array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                                                array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                                                array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
                                            );
                                            $TrespasserId = $rs->Insert('Trespasser', $a_Insert);
                                            if ($TrespasserId == 0) {
                                                echo "Poblemi con l'inserimento del trasgressore";
                                                DIE;
                                            }
                                        } else {
                                            $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                                            $TrespasserId = $r_Trespasser['Id'];

                                            $a_Update = array(
                                                array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                                                array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
                                            );

                                            $rs->Update('Trespasser', $a_Update, 'Id=' . $TrespasserId);
                                        }

                                    } else {
                                        if(strlen(trim($TaxCode))>0){
                                            $rs_Trespasser = $rs->Select('Trespasser', "TaxCode='" . $TaxCode . "'");
                                            $i_RowNumber = mysqli_num_rows($rs_Trespasser);

                                        } else $i_RowNumber = 0;


                                        if ($i_RowNumber == 0) {
                                            $a_Insert = array(
                                                array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => ENTELLA_CUSTOMER_ID),
                                                array('field' => 'Code','selector' => 'value','type' => 'int', 'value' => $Code, 'settype' => 'int'),
                                                array('field' => 'Genre', 'selector' => 'value', 'type' => 'str', 'value' => $Genre),
                                                array('field' => 'Surname', 'selector' => 'value', 'type' => 'str', 'value' => $Surname),
                                                array('field' => 'Name', 'selector' => 'value', 'type' => 'str', 'value' => $Name),
                                                array('field' => 'Address', 'selector' => 'value', 'type' => 'str', 'value' => $FullStreet),
                                                array('field' => 'ZIP', 'selector' => 'value', 'type' => 'str', 'value' => $ZIP),
                                                array('field' => 'City', 'selector' => 'value', 'type' => 'str', 'value' => $City),
                                                array('field' => 'Province', 'selector' => 'value', 'type' => 'str', 'value' => $Province),
                                                array('field' => 'LicenseNumber', 'selector' => 'value', 'type' => 'str', 'value' => $LicenseNumber),
                                                array('field' => 'BornDate', 'selector' => 'value', 'type' => 'date', 'value' => $BornDate),
                                                array('field' => 'BornPlace', 'selector' => 'value', 'type' => 'str', 'value' => $BornPlace),
                                                array('field' => 'TaxCode', 'selector' => 'value', 'type' => 'str', 'value' => $TaxCode),
                                                array('field' => 'CountryId', 'selector' => 'value', 'type' => 'str', 'value' => $CountryId),
                                                array('field' => 'ZoneId', 'selector' => 'value', 'type' => 'int', 'value' => $ZoneId, 'settype' => 'int'),
                                                array('field' => 'LanguageId', 'selector' => 'value', 'type' => 'int', 'value' => $LanguageId, 'settype' => 'int'),
                                                array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                                                array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),


                                            );

                                            if($LicenseDate!=""){
                                                $a_Insert[] = array('field'=>'LicenseDate','selector'=>'value','type'=>'date','value'=>$LicenseDate);
                                            }


                                            $TrespasserId = $rs->Insert('Trespasser', $a_Insert);
                                            if ($TrespasserId == 0) {
                                                echo "Poblemi con l'inserimento del trasgressore";
                                                DIE;
                                            }
                                        } else {
                                            $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                                            $TrespasserId = $r_Trespasser['Id'];
                                            $a_Update = array(
                                                array('field' => 'DataSourceId', 'selector' => 'value', 'type' => 'int', 'value' => $DataSourceId, 'settype' => 'int'),
                                                array('field' => 'DataSourceDate', 'selector' => 'value', 'type' => 'date', 'value' => $DataSourceDate),
                                            );

                                            $rs->Update('Trespasser', $a_Update, 'Id=' . $TrespasserId);
                                        }

                                    }

                                    $rs_Fine = $rs->Select('Fine', "FineDate='" . $FineDate . "' AND VehiclePlate='" . $VehiclePlate . "' AND StatusTypeId=5 AND CountryId='" . $CountryId . "'");

                                    while ($r_Fine = mysqli_fetch_array($rs_Fine)) {
                                        $FineId = $r_Fine['Id'];


                                        $a_Insert = array(
                                            array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                            array('field' => 'TrespasserId', 'selector' => 'value', 'type' => 'int', 'value' => $TrespasserId, 'settype' => 'int'),
                                            array('field' => 'TrespasserTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $TresspasserTypeId, 'settype' => 'int'),
                                            array('field' => 'Note', 'selector' => 'value', 'type' => 'str', 'value' => "Inserimento da Import motorizzazione"),
                                        );
                                        $rs->Insert('FineTrespasser', $a_Insert);


                                        for($i=0; $i<count($a_ChkModelBrand) ; $i++){
                                            if (strpos($VehicleBrand, $a_ChkModelBrand[$i]) !== false && strpos($VehicleModel, $a_ChkModelBrand[$i]) !== false) {
                                                $VehicleModel = str_replace($a_ChkModelBrand[$i]." ","",$VehicleModel);
                                            }
                                        }





                                        $a_Update = array(
                                            array('field' => 'VehicleBrand', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleBrand),
                                            array('field' => 'VehicleModel', 'selector' => 'value', 'type' => 'str', 'value' => $VehicleModel),
                                            array('field' => 'VehicleMass', 'selector' => 'value', 'type' => 'flt', 'value' => $VehicleMass, 'settype' => 'flt'),
                                            array('field' => 'VehicleLastRevision', 'selector' => 'value', 'type' => 'date', 'value' => $VehicleLastRevision),
                                            array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId, 'settype' => 'int'),
                                        );

                                        $rs->Update('Fine', $a_Update, 'Id=' . $FineId);

                                        if ($VehicleMass > MASS) {
                                            $rs_rows = $rs->SelectQuery("
                                        SELECT F.FineTime, AT.ArticleId, AT.Fee, AT.MaxFee
                                               FROM Fine F JOIN FineArticle FA ON F.Id = FA.FineId
                                               JOIN ArticleTariff AT ON AT.ArticleId = FA.ArticleId
                                               WHERE AT.AdditionalMass = 1 AND FA.FineId=" . $FineId);


                                            while ($rs_row = mysqli_fetch_array($rs_rows)) {
                                                $Fee = $rs_row['Fee'] * FINE_MASS;
                                                $MaxFee = $rs_row['MaxFee'] * FINE_MASS;
                                                $FineTime = $rs_row['FineTime'];

                                                $aTime = explode(":", $FineTime);

                                                if ($aTime[0] < FINE_HOUR_START_DAY || ($aTime[0] > FINE_HOUR_END_DAY) || ($aTime[0] == FINE_HOUR_END_DAY && $aTime[1] != "00")) {
                                                    //FINE_MINUTE_START_DAY
                                                    //FINE_MINUTE_END_DAY
                                                    $Fee = $Fee + round($Fee / FINE_NIGHT, 2);
                                                    $MaxFee = $MaxFee + round($MaxFee / FINE_NIGHT, 2);
                                                }


                                                $a_Update = array(
                                                    array('field' => 'Fee', 'selector' => 'value', 'type' => 'flt', 'value' => $Fee, 'settype' => 'flt'),
                                                    array('field' => 'MaxFee', 'selector' => 'value', 'type' => 'flt', 'value' => $MaxFee, 'settype' => 'flt'),
                                                );

                                                $rs->Update('FineArticle', $a_Update, 'ArticleId=' . $rs_row['ArticleId'] . " AND FineId=" . $FineId);


                                            }

                                        }


                                    }


                                    //todo  VehicleLastRevision

                                } else {
                                    $chk_Data = 50;
                                    $str_Error = "Marca veicolo non presente";

                                }

                        }
                        if ($chk_Data != 90) {
                            $VehiclePlate = extract_substr($content, 25, 8);
                            $FineDate = substr($content, 15, 4) . "-" . substr($content, 13, 2) . "-" . substr($content, 11, 2);


                            $rs_Fine = $rs->Select('Fine', "FineDate='" . $FineDate . "' AND VehiclePlate='" . $VehiclePlate . "' AND StatusTypeId=5 AND CountryId='" . $CountryId . "'");
                            while ($r_Fine = mysqli_fetch_array($rs_Fine)) {
                                $FineId = $r_Fine['Id'];
                                $Anomaly = $str_Error;

                                $str_Error = "Id:".$FineId." - ".$str_Error;
                                $a_Insert = array(
                                    array('field' => 'FineId', 'selector' => 'value', 'type' => 'int', 'value' => $FineId, 'settype' => 'int'),
                                    array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId, 'settype' => 'int'),
                                    array('field' => 'Anomaly', 'selector' => 'value', 'type' => 'str', 'value' => $Anomaly),
                                );
                                $rs->Insert('FineAnomaly', $a_Insert);

                                $a_Update = array(
                                    array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
                                );
                                $rs->Update('Fine', $a_Update, 'Id=' . $FineId);


                            }


                        }


                        if ($str_Error != '') {
                            echo
                                '<div class="col-sm-12">
                        <div class="table_caption_H col-sm-12 alert-danger">
                             '. $r_ProcessingMCTC['CityId'] .' '. $str_Error . '
                        </div>
                        <div class="clean_row HSpace4"></div>
                    </div>';
                        } else {
                            echo
                                '<div class="col-sm-12">
                            ' . $str_Output . '
                            <div class="clean_row HSpace4"></div>
			            </div>   ';

                        }
                    }
                }
                $rs->End_Transaction();

            }
        }


        $table_rows = $rs->SelectQuery("SELECT FineDate, VehiclePlate, VehicleTypeId  FROM Fine WHERE CityId='". $r_ProcessingMCTC['CityId']. "' AND ControllerId IS NOT NULL AND CountryId='Z000' AND StatusTypeId=1 AND Id NOT IN(SELECT FineId AS Id FROM FineAnomaly) GROUP BY FineDate, VehiclePlate, VehicleTypeId ORDER BY FineDate LIMIT 1500");



        $RowNumber = mysqli_num_rows($table_rows);

        if ($RowNumber > 0) {

            $FileName = "Request_". $r_ProcessingMCTC['CityId'] ."_". date("Y-m-d_H-i");
            $OutFile = fopen(NATIONAL_REQUEST . "/" . $FileName, "w") or die("Unable to open file!");

            $rs->Start_Transaction();

            while ($table_row = mysqli_fetch_array($table_rows)) {


                if ($table_row['VehicleTypeId'] == 2) $VehicleType = 'M';
                elseif ($table_row['VehicleTypeId'] == 9) $VehicleType = 'C';
                elseif ($table_row['VehicleTypeId'] == 7) $VehicleType = 'R';
                else $VehicleType = 'A';

                $PlateOut = $table_row['VehiclePlate'];
                $FineDate = $table_row['FineDate'];

                $a_Date = explode("-", $FineDate);
                $FineDateOut = $a_Date[2] . $a_Date[1] . $a_Date[0];
                $RequestDate = date("dmY");
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

                for ($i = strlen($PlateOut); $i < 14; $i++) {
                    $PlateOut .= " ";
                }


                $NameOut = $r_ProcessingMCTC['Province'];
                $NameOut .= $r_ProcessingMCTC['Name'];

                $n_Diff = 43 - strlen($NameOut);
                for($i=1; $i<$n_Diff; $i++){
                    $NameOut .= " ";
                }
                $NameOut .= "\n";


                $txt = $plate . $PlateOut . $FineDateOut . "        " . $NameOut;

                fwrite($OutFile, $txt);


                $fines = $rs->Select('V_Violation', "VehiclePlate='" . $PlateOut . "' AND CountryId='Z000' AND StatusTypeId=1 AND FineDate='" . $FineDate . "'", 'Id');
                while ($fine = mysqli_fetch_array($fines)) {

                    $CityId = $fine['CityId'];
                    $StatusTypeId = 5;
                    $EntityId = 41;

                    $NotificationDate = date("Y-m-d");
                    $FlowDate = date("Y-m-d");
                    $SendDate = date("Y-m-d");
                    $PrintDate = date("Y-m-d");


                    $aInsert = array(
                        array('field' => 'NotificationTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $NotificationTypeId),
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
                    if ($chk == 0) {
                        echo "Poblemi con l'inserimento nello storico";
                        DIE;
                    }

                    $aUpdate = array(
                        array('field' => 'StatusTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $StatusTypeId),
                    );
                    $rs->Update('Fine', $aUpdate, 'Id=' . $fine['Id']);
                }


            }
            fclose($OutFile);

            if (file_exists(NATIONAL_REQUEST . "/".$r_ProcessingMCTC['FileFlag'])) {
                $b_Delete = @unlink(NATIONAL_REQUEST . "/".$r_ProcessingMCTC['FileFlag']);
                if (!$b_Delete) {
                    echo
                    '<div class="col-sm-12">
                    <div class="table_caption_H col-sm-12 alert-danger">
                        PROBLEMI NELLA CANCELLAZIONE OK-SA
                    </div>
                    <div class="clean_row HSpace4"></div>
                </div>';
                    DIE;
                }
            }

            $OutFile = fopen(NATIONAL_REQUEST . "/".$r_ProcessingMCTC['FileFlag'], "w") or die("Unable to open file!");
            fwrite($OutFile, "INPUT COMPLETO " . $RequestDate);
            fclose($OutFile);

            $upload = @ftp_put($conn, $r_ProcessingMCTC['FileInput'], NATIONAL_REQUEST . "/" . $FileName, FTP_BINARY);
            if ($upload) {
                echo
                '<div class="col-sm-12">
                <div class="table_caption_H col-sm-12 alert-success">
                    FILE RICHIESTA DATI motorizzazione caricato
                </div>
                <div class="clean_row HSpace4"></div>
            </div>';

                $upload = @ftp_put($conn, $r_ProcessingMCTC['FileFlag'], NATIONAL_REQUEST . "/".$r_ProcessingMCTC['FileFlag'], FTP_BINARY);
                if ($upload) {
                    $rs->End_Transaction();
                    echo
                    '<div class="col-sm-12">
                    <div class="table_caption_H col-sm-12 alert-success">
                        FILE CONTROLLO DATI motorizzazione caricato
                    </div>
                    <div class="clean_row HSpace4"></div>
                </div>';

                } else {
                    echo
                    '<div class="col-sm-12">
                    <div class="table_caption_H col-sm-12 alert-danger">
                        Problemi nel caricamento FILE CONTROLLO DATI motorizzazione
                    </div>
                    <div class="clean_row HSpace4"></div>
                </div>';

                }

            } else {
                echo
                '<div class="col-sm-12">
                <div class="table_caption_H col-sm-12 alert-danger">
                    Problemi nel caricamento FILE RICHIESTA DATI motorizzazione
                </div>
                <div class="clean_row HSpace4"></div>
            </div>';

            }

            ftp_close($conn);

        } else {
            echo '
            <div class="table_caption_H col-sm-12 alert-warning">
                Nessun dato per esportazione
            </div>
            ';
        }
    }


}



