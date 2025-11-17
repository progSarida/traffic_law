<?php

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");

if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
    if ($r_Locked['Locked'] == 1) {
        $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
        header("location: frm_send_kindfine.php".$Filters);
        DIE;
    } else {
        $UpdateLockedPage = array(
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
        $rs->Update('LockedPage', $UpdateLockedPage, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    }
} else {
    $InsertLockedPage = array(
        array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}"),
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
    $rs->Insert('LockedPage', $InsertLockedPage);
}

$rs->UnlockTables();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$n_LanguageId = 1;
$str_Speed = "";

$a_Lan = unserialize(LANGUAGE);

$FormTypeId = 4;

$ZoneId=0;


$PrinterId = 1;
//$PrinterId = 9; stampa distinta per la posta

// todo gestione stampatore


$a_DocumentationFineZip = array();


$a_GenreLetter = array("D"=>"Spett.le","M"=>"Sig.","F"=>"Sig.ra");


$PrintTypeId = 4;
$DocumentTypeId = 1;
$KindCreateDate = date("Y-m-d");
$P = "frm_send_kindfine.php";

//Parametri stampatore////////////////////////////
$str_Mod23LSubject          = $r_Customer['NationalMod23LSubject'];
$str_Mod23LCustomerName     = $r_Customer['NationalMod23LCustomerName'];

$rs_PrinterParameter = $rs->Select('PrinterParameter', "PrinterId=1 AND CityId='{$_SESSION['cityid']}'");
$r_PrintParameter = $rs->getArrayLine($rs_PrinterParameter);

$str_SmaName                = $r_PrintParameter['NationalSmaName'] ?? '';
$str_SmaAuthorization       = $r_PrintParameter['NationalSmaAuthorization'] ?? '';
$str_SmaPayment             = $r_PrintParameter['NationalSmaPayment'] ?? '';

$str_Mod23LCustomerSubject  = $r_PrintParameter['NationalMod23LCustomerSubject'] ?? '';
$str_Mod23LCustomerAddress  = $r_PrintParameter['NationalMod23LCustomerAddress'] ?? '';
$str_Mod23LCustomerCity     = $r_PrintParameter['NationalMod23LCustomerCity'] ?? '';
/////////////////////////////////////////////////

if(isset($_POST['checkbox'])) {

    $ultimate = CheckValue('ultimate','s');
    
    $rs->Start_Transaction();

    if($ultimate){

        $flows = $rs->SelectQuery("SELECT MAX(Number) Number FROM Flow WHERE CityId='".$_SESSION['cityid']."' AND RuleTypeId={$_SESSION['ruletypeid']} AND Year=".date('Y'));
        $flow = mysqli_fetch_array($flows);

        $int_FlowNumber = $flow['Number']+1;

        $FileNameDoc = "Flusso_".$int_FlowNumber."_BONARIO_Ita_".$_SESSION['cityid']."_".date("Y-m-d")."_".date("H-i-s")."_".count($_POST['checkbox']);
    }
    else{
        $FileNameDoc = "Flusso_BONARIO_Ita_".$_SESSION['cityid']."_PROVVISORIO";
    }

    $Documentation = $FileNameDoc.".txt";
    $DocumentationZip = $FileNameDoc.".zip";
    $path = NATIONAL_FLOW."/".$_SESSION['cityid']."/";
    $myfile = fopen($path.$Documentation, "w") or die("Unable to open file!");

    






    $checkFlowHeader = 0;
    foreach($_POST['checkbox'] as $FineId) {
        $rs_Fine = $rs->Select('V_FineArticle', "Id=" . $FineId. " AND TrespasserTypeId=1");

        $r_Fine = mysqli_fetch_array($rs_Fine);
        $ViolationTypeId = $r_Fine['ViolationTypeId'];



        if ($r_Fine['StatusTypeId'] != 10) {
            $_SESSION['Message']['Error'] = "Problemi con la creazione del flusso con verbale ID ." . $FineId . ". Controllare e riprovare.";
            header("location: frm_send_kindfine.php".$Filters);
            DIE;
        }


        $trespassers = $rs->Select('V_Trespasser', "Id=" . $r_Fine['TrespasserId']);
        $trespasser = mysqli_fetch_array($trespassers);


        $FineTime = $r_Fine['FineTime'];


        $forms = $rs->Select('Form', "FormTypeId=" . $FormTypeId . " AND CityId='" . $_SESSION['cityid'] . "' AND LanguageId=" . $n_LanguageId);
        $form = mysqli_fetch_array($forms);

        $Content = $form['Content'];


        $Content = str_replace("<h3>", "", $Content);
        $Content = str_replace("</h3>", "", $Content);
        $Content = str_replace("<h4>", "", $Content);
        $Content = str_replace("</h4>", "", $Content);
        $Content = str_replace("<h5>", "", $Content);
        $Content = str_replace("</h5>", "", $Content);
        $Content = str_replace("<b>", "", $Content);
        $Content = str_replace("</b>", "", $Content);

        $Content = str_replace("{Code}", $r_Fine['Code'], $Content);

        $Content = str_replace("{FineDate}", DateOutDB($r_Fine['FineDate']), $Content);
        $Content = str_replace("{FineTime}", TimeOutDB($FineTime), $Content);
        $Content = str_replace("{ProtocolYear}", $r_Fine['ProtocolYear'], $Content);
        $Content = str_replace("{VehicleTypeId}", $r_Fine['VehicleTitle' . $a_Lan[$n_LanguageId]], $Content);
        $Content = str_replace("{VehiclePlate}", $r_Fine['VehiclePlate'], $Content);

        $Content = str_replace("{VehicleBrand}", $r_Fine['VehicleBrand'], $Content);
        $Content = str_replace("{VehicleModel}", $r_Fine['VehicleModel'], $Content);
        $Content = str_replace("{VehicleColor}", $r_Fine['VehicleColor'], $Content);

        $Content = str_replace("{TrespasserName}", $trespasser['CompanyName'] . ' ' . $trespasser['Surname'] . ' ' . $trespasser['Name'], $Content);
        $Content = str_replace("{TrespasserCity}", $trespasser['City'], $Content);
        $Content = str_replace("{TrespasserProvince}", $trespasser['Province'], $Content);

        $Content = str_replace("{TrespasserAddress}", $trespasser['Address'] . " " . $trespasser['ZIP'], $Content);
        $Content = str_replace("{TrespasserCountry}", $trespasser['CountryTitle'], $Content);

        if(strlen($trespasser['BornDate']) == 10 && strlen(trim($trespasser['BornPlace'])) > 0){
            $Content = str_replace("{TrespasserBornDate}", DateOutDB($trespasser['BornDate']), $Content);

            $str_BornPlace = preg_replace('#\040{2,}#', ' ', $trespasser['BornPlace']);

            $Content = str_replace("{TrespasserBornCity}", $str_BornPlace, $Content);
        } else {
            $Content = str_replace(" il {TrespasserBornDate}", '', $Content);
            $Content = str_replace(" Nato/a a {TrespasserBornCity}", "", $Content);
        }

        if ($r_Customer['ManagerSignName'] == "") {
            if ($r_Customer['CityUnion'] > 1) {
                $Content = str_replace("{Date}", $r_Fine['CityTitle'] . ", " . date("d/m/Y"), $Content);
            } else {
                $Content = str_replace("{Date}", $r_Customer['ManagerName'] . ", " . date("d/m/Y"), $Content);
            }
        } else {
            $Content = str_replace("{Date}", $r_Customer['ManagerSignName'] . ", " . date("d/m/Y"), $Content);
        }
        
        $Content = str_replace("{ManagerDataEntryName}", $r_Customer['ManagerDataEntryName'], $Content);





        $Content = str_replace(array("\n", "\r"), "", $Content);

        $a_Flow = array(
            "FineId" => $FineId,
            "TIPOLOGIA_STAMPA" => "Lettera",
            "TIPOLOGIA_ATTO" => "AVVISO BONARIO",
            "TIPOLOGIA_FLUSSO" => $FormTypeId,
            "CodiceComune" => $_SESSION['cityid'],

            "HeaderRow1" => $r_Customer['ManagerName'],
            "HeaderRow1_RichiestaDati" => $r_Customer['ManagerAdditionalName'] . ' ' . $r_Customer['ManagerName'],
            "HeaderRow2" => "POLIZIA LOCALE",
            "HeaderRow3" => $r_Customer['ManagerAddress'],
            "HeaderRow4" => $r_Customer['ManagerZIP'] . " " . $r_Customer['ManagerCity'] . " (" . $r_Customer['ManagerProvince'] . ")",
            "HeaderRow5" => $r_Customer['ManagerPhone'],

            "HeaderRow1_Col2" => "",
            "HeaderRow2_Col2" => "",
            "HeaderRow3_Col2" => "",
        );


        $a_Flow["Richiesta_Dati"] = "NO";


        $a_Flow["Spese_Anticipate"] = $str_SmaPayment;

        $a_Flow["Intestatario_SMA"] = $str_SmaName;
        $a_Flow["Numero_SMA"] = $str_SmaAuthorization;


        $a_Flow["Mod23_Soggetto_Mittente"]  = $str_Mod23LSubject;
        $a_Flow["Mod23_Ente_Gestito"]       = $str_Mod23LCustomerName;
        $a_Flow["Mod23_Recapito_Soggetto"]  = $str_Mod23LCustomerSubject;
        $a_Flow["Mod23_Indirizzo_Soggetto"] = $str_Mod23LCustomerAddress;
        $a_Flow["Mod23_Citta_Soggetto"]     = $str_Mod23LCustomerCity;


        $aMainPart = explode("<main_part>", $Content);
        $aRow = explode("<row>", $aMainPart[1]);

        //Arial 9 minuscolo
        $a_Flow["Page1_Row1"] = '';


        $a_Flow["Page1_Row2"] = $aRow[2];


        $a_Flow["Recipient_Row1"] = $a_GenreLetter[$trespasser['Genre']] . " " . $trespasser['CompanyName'] . " " . $trespasser['Surname'] . " " . $trespasser['Name'];
        $a_Flow["Recipient_Row2"] = $trespasser['Address'];//L
        $a_Flow["Recipient_Row3"] = $trespasser['ZIP'] . ' ' . $trespasser['City'] . ' ' . "(" . $trespasser['Province'] . ")";

        //Arial 8 minuscolo



        $a_Flow["Page1_Row3_Col1"] = $aRow[3];//J
        $a_Flow["Page1_Row3_Col2"] = "";//J

        $a_Flow["Page1_Row4"] = $aRow[4];//C
        $exp_aRow = explode("<br />", $aRow[5]);
        for ($i = 0; $i < 4; $i++) {
            if (isset($exp_aRow[$i]))
                $a_Flow["Page1_Row5_" . ($i + 1)] = $exp_aRow[$i];
            else
                $a_Flow["Page1_Row5_" . ($i + 1)] = "";
        }

        //$a_Flow["Page1_Row5"] = $aRow[5];//J


        $a_FifthField = array("Table" => 1, "Id" => $r_Fine['Id']);

        $a_FifthField['PaymentType'] = "";
        $FifthField1 = "";
        $a_FifthField['PaymentType'] = "";
        $FifthField2 = "";

        $Bollettino1Fee = "";
        $Bollettino2Fee = "";

        $scadenzaBoll1 = "";
        $scadenzaBoll2 = "";

        $a_Flow["Page1_RowPay_Reduced1"] = $aRow[6];//C Bold

        $a_Flow["Page1_RowPay_Reduced2"] = $aRow[7];//J
        $a_Flow["Page1_RowPay_Reduced3"] = $aRow[8];
        $a_Flow["Page1_RowPay_Reduced4"] = $aRow[9];


        $FifthFieldFee1 = "";
        $FifthFieldFee2 = "";

        $a_Flow["Page1_RowPay_Normal1"] = "";
        $a_Flow["Page1_RowPay_Normal2"] = "";
        $a_Flow["Page1_RowPay_Normal3"] = "";
        $a_Flow["Page1_RowPay_Normal4"] = "";


        $a_Flow["Page1_RowPay_Max1"] = "";
        $a_Flow["Page1_RowPay_Max2"] = "";
        $a_Flow["Page1_RowPay_Max3"] = "";
        $a_Flow["Page1_RowPay_Max4"] = "";

        $a_Flow["Page1_Row8"] = "";//J
        $a_Flow["Page1_Row9"] = "";//J


        $a_Flow["Page1_Row10_Col1"] = "";
        $a_Flow["Page1_Row10_Col2"] = "";//J

        $a_Flow["Page1_Row11_Col1"] = $aRow[11];//J
        $a_Flow["Page1_Row11_Col2"] = "";//J

        $a_Flow["Page1_Row12_Col1"] = $aRow[12];//J
        $a_Flow["Page1_Row12_Col2"] = "";//J





        $a_Flow["Page1_Row13"] = $aRow[13];//J

        $aCol = explode("<col>", $aRow[14]);

        $a_Flow["Page1_Row14_Col1"] = $aCol[0];//L
        $a_Flow["Page1_Row14_Col2"] = $aCol[1];//L



        $a_Flow["Page1_Row15"] = $aRow[15];//J
        $a_Flow["Page1_Row16"] = $aRow[16];//J
        $a_Flow["Page1_Row17"] = $aRow[17];//J


        $a_Flow["Page1_Row18"] = $aRow[18];//J
        $a_Flow["Page1_Row19"] = $aRow[19];//C


        $a_Flow["Page1_Row20_1"] = $aRow[20];
        $a_Flow["Page1_Row20_2"] = "";
        $a_Flow["Page1_Row20_3"] = "";



        //////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        ////
        ////
        ////    Fine page 2
        ////
        ////
        //////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////
        $aRow = explode("<row>", $aMainPart[2]);

        //Arial 7 minuscolo
        $a_Flow["Page2_Row1"] = $aRow[1];//L
        $a_Flow["Page2_Row2"] = $aRow[2];//C
        $a_Flow["Page2_Row3"] = $aRow[3];//J
        $a_Flow["Page2_Row4"] = $aRow[4];//C
        $a_Flow["Page2_Row5"] = $aRow[5];//J
        $a_Flow["Page2_Row6"] = $aRow[6];//C
        $a_Flow["Page2_Row7"] = $aRow[7];//J
        $a_Flow["Page2_Row8"] = $aRow[8];//J
        $a_Flow["Page2_Row9"] = $aRow[9];//C
        $a_Flow["Page2_Row10"] = $aRow[10];//J
        $a_Flow["Page2_Row11_1"] = $aRow[11];//C
        $a_Flow["Page2_Row11_2"] = "";//C
        $a_Flow["Page2_Row11_3"] = "";//C
        $a_Flow["Page2_Row11_4"] = "";//C
        $a_Flow["Page2_Row11_5"] = "";//C

        $a_Flow["Page2_Row12"] = $aRow[12];//J
        $a_Flow["Page2_Row13"] = $aRow[13];//C
        $a_Flow["Page2_Row14"] = $aRow[14];//J


        $a_Flow["ScadenzaBoll1"] = "";
        $a_Flow["ScadenzaBoll2"] = "";


        if ($checkFlowHeader == 0) {
            foreach ($a_Flow as $key => $value) {
                fwrite($myfile, $key . Chr(9));  //  TAB
            }
            fwrite($myfile, Chr(13) . Chr(10));  //  fine riga
            $checkFlowHeader = 1;
        }

        foreach ($a_Flow as $value) {
            fwrite($myfile, trim($value) . Chr(9));  //  TAB
        }
        fwrite($myfile, Chr(13) . Chr(10));  //  fine riga
        $a_Flow = null;

        if ($ultimate) {

            $aUpdate = array(
                array('field' => 'KindCreateDate', 'selector' => 'value', 'type' => 'date', 'value' => $KindCreateDate)
            );
            $rs->Update('Fine', $aUpdate, 'Id=' . $r_Fine['Id']);
        }

    }
    fclose($myfile);


    $a_Blazon = explode(".",$_SESSION['blazon']);

    $zip = new ZipArchive();
    if ($zip->open($path.$DocumentationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $zip->addFile($path.$Documentation,$Documentation);
        $zip->addFile($_SESSION['blazon'],'blazon.'.$a_Blazon[1]);
        $zip->close();
        $_SESSION['Documentation'] = $MainPath.'/doc/national/flow/'.$_SESSION['cityid'].'/'.$DocumentationZip;
    }

    if($ultimate){

        $Zone0Number=count($_POST['checkbox']);
        
        $aInsert = array(
            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
            array('field'=>'Year','selector'=>'value','type'=>'year','value'=>date('Y')),
            array('field'=>'Number','selector'=>'value','type'=>'int','value'=>$int_FlowNumber,'settype'=>'int'),
            array('field'=>'PrintTypeId','selector'=>'value','type'=>'int','value'=>$PrintTypeId,'settype'=>'int'),
            array('field'=>'DocumentTypeId','selector'=>'value','type'=>'int','value'=>$DocumentTypeId,'settype'=>'int'),
            array('field'=>'RecordsNumber','selector'=>'value','type'=>'int','value'=>count($_POST['checkbox'])),
            array('field'=>'CreationDate','selector'=>'value','type'=>'date','value'=>$KindCreateDate),
            array('field'=>'FileName','selector'=>'value','type'=>'str','value'=>$DocumentationZip),
            array('field'=>'PrinterId','selector'=>'value','type'=>'int','value'=>$PrinterId,'settype'=>'int'),
            array('field'=>'Zone0Number','selector'=>'value','type'=>'int','value'=>$Zone0Number,'settype'=>'int'),
            array('field' => 'RuleTypeId', 'selector' => 'value', 'type' => 'int', 'value' => $_SESSION['ruletypeid'], 'settype' => 'int'),
        );

        $rs->Insert('Flow',$aInsert);
    }
    
    $aUpdate = array(
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 0, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => ''),
    );
    $rs->Update('LockedPage', $aUpdate, "Title='".FLOW_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    
    $rs->End_Transaction();
}

