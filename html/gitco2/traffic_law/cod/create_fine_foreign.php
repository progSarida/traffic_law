<?php
include(INC."/function_postalCharge.php");

$rs->SetCharset('utf8');

//BLOCCA LA PROCEDURA SE GIà IN CORSO O è ANDATA IN ERRORE/////////////////////////////////////////////////////////////
//NOTA BENE: il blocco della tabella serve ad impedire di creare atti con cronologici duplicati
//es: si creano verbali dinamici nello stesso momento in cui si creano verbali PEC
$a_LockTables = array("LockedPage WRITE");
$rs->LockTables($a_LockTables);

$rs_Locked = $rs->Select('LockedPage', "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");

if($r_Locked = mysqli_fetch_assoc($rs_Locked)){
    if ($r_Locked['Locked'] == 1) {
        $_SESSION['Message']['Error'] = "Pagina bloccata dall'utente " . $r_Locked['UserName'] . ".<br /> Attendere qualche minuto prima di creare i verbali.";
        header("location: ".$P);
        DIE;
    } else {
        $UpdateLockedPage = array(
            array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
            array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
        );
        $rs->Update('LockedPage', $UpdateLockedPage, "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");
    }
} else {
    $InsertLockedPage = array(
        array('field' => 'Title', 'selector' => 'value', 'type' => 'str', 'value' => CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}"),
        array('field' => 'Locked', 'selector' => 'value', 'type' => 'int', 'value' => 1, 'settype' => 'int'),
        array('field' => 'UserName', 'selector' => 'value', 'type' => 'str', 'value' => $_SESSION['username']),
    );
    $rs->Insert('LockedPage', $InsertLockedPage);
}

$rs->UnlockTables();
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$strCode = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

$ProtocolNumber = 0;

$StatusTypeId = 15;
$NotificationTypeId = 2;

if($_SESSION['usertype']==2){
    $rs_Time = $rs->SelectQuery("SELECT MAX( ControllerTime ) ControllerTime FROM Fine WHERE ControllerDate='".date("Y-m-d")."'");
    $r_Time =  mysqli_num_rows($rs_Time);
    $Time = ($r_Time['ControllerTime']=="") ? "08:00:11" : $r_Time['ControllerTime'];
}

$NotificationDate = date("Y-m-d");





$a_Lan = unserialize(LANGUAGE);
$a_Rent = unserialize(RENT);
$a_AdditionalNight = unserialize(ADDITIONAL_NIGHT);
$a_AdditionalMass = unserialize(ADDITIONAL_MASS);

if(isset($_POST['checkbox'])) {
    $chk_warning = false;
    foreach($_POST['checkbox'] as $FineId) {
        $rs_Fine = $rs->Select('V_ViolationArticle', "Id=".$FineId);
        $r_Fine = mysqli_fetch_array($rs_Fine);


        if($r_Fine['StatusTypeId']!=10 && $r_Fine['StatusTypeId']!=14) {
            $chk_warning = true;
            $_SESSION['Message'] = "Problemi con l'elaborazione del verbale con ID " . $FineId . " - Controllare e riprovare.";
        }

        $trespassers = $rs->Select('V_Trespasser', "Id=".$r_Fine['TrespasserId']);
        $trespasser = mysqli_fetch_array($trespassers);

        if($trespasser['CountryId']=='ZZZZ'){
            $chk_warning = true;
            $_SESSION['Message'] = "Problemi con l'elaborazione del verbale con ID " . $FineId . " - Stato del trasgressore non presente.";

        }

        if($r_Fine['ControllerId']=="" && $_SESSION['usertype']!=2) {
            $chk_warning = true;
            $_SESSION['Message'] = "Problemi con l'elaborazione del verbale con ID " . $FineId . " - Manca il verbalizzante.";
        } else if($r_Fine['ControllerId']=="" &&  $_SESSION['usertype']==2 && $ultimate){
            $ControllerDate = date("Y-m-d");
            $ControllerTime = date("H:i:s", strtotime('+41 seconds',strtotime($Time)));

            $Time = $ControllerTime;

            $a_Fine = array(
                array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$_SESSION['controllerid'],'settype'=>'int'),
                array('field'=>'ControllerDate','selector'=>'value','type'=>'date','value'=>$ControllerDate),
                array('field'=>'ControllerTime','selector'=>'value','type'=>'str','value'=>$ControllerTime),
            );

            $rs->Update('Fine',$a_Fine,"Id=".$FineId);
        }


        if($chk_warning){
            $aUpdate = array(
                array('field'=>'Locked','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>''),
            );
            $rs->Update('LockedPage',$aUpdate, "Title='create_fine_foreign'");


            header("location: ".$P);
            DIE;
        }
    }




    $ultimate = CheckValue('ultimate','s');




    $FinePDFList = $r_Customer['FinePDFList'];

    $str_WhereCity = ($r_Customer['CityUnion']>=1) ? "UnionId='" . $_SESSION['cityid'] . "'" : "Id='" . $_SESSION['cityid'] . "'";
    $rs_ProtocolLetter = $rs->Select(MAIN_DB.'.City', $str_WhereCity);
    $a_ProtocolLetterLocality = array();
    while($r_ProtocolLetter = mysqli_fetch_array($rs_ProtocolLetter)){
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType1'] = $r_ProtocolLetter['ForeignProtocolLetterType1'];
        $a_ProtocolLetterLocality[$r_ProtocolLetter['Id']]['ForeignProtocolLetterType2'] = $r_ProtocolLetter['ForeignProtocolLetterType2'];
    }


    $rs_ChiefController = $rs->Select('Controller',"Id=".$SelectChiefControllerId);
    $r_ChiefController = mysqli_fetch_array($rs_ChiefController);


    $pdf = new PDF_HANDLE('P','mm','A4', true,'UTF-8',false,true);

    if($FinePDFList)  $pdf_union = new FPDI();


    $pdf->TemporaryPrint= $ultimate;
    $pdf->NationalFine= 0;
    $pdf->CustomerFooter = 0;

    //$pdf->SetProtection(array('print', 'copy'), '1234', null, 3, null);


    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($_SESSION['citytitle']);
    $pdf->SetTitle('Violation');
    $pdf->SetSubject('');
    $pdf->SetKeywords('');
    $pdf->setHeaderFont(array('helvetica', '', 8));
    $pdf->setFooterFont(array('helvetica', '', 8));
    $pdf->SetMargins(10,10,10);



    foreach($_POST['checkbox'] as $FineId) {

        $NotificationFee    = 0;
        $ChargeTotalFee     = 0;
        $ResearchFee        = 0;
        $n_TotFee           = 0;
        $n_TotMaxFee        = 0;
        $chk_126Bis         = false;
        $chk_ReducedPayment = false;
        $n_LicensePoint     = 0;
        $n_TotPartialFee    = 0;

        if($r_Customer['ForeignTotalFee']>0) $ChargeTotalFee = $r_Customer['ForeignTotalFee'];
        else{
            if($r_Customer['ForeignNotificationFee']>0){
                $NotificationFee = $r_Customer['ForeignNotificationFee'];
            }
            $ResearchFee = $r_Customer['ForeignResearchFee'];
        }




        if($ultimate && $ProtocolNumber>0){
            $pdf = new PDF_HANDLE('P','mm','A4', true,'UTF-8',false,true);


            $pdf->TemporaryPrint= $ultimate;
            $pdf->NationalFine= 0;
            $pdf->CustomerFooter = 0;
            //$pdf->SetProtection(array('print', 'copy'), '1234', null, 3, null);


            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor($_SESSION['citytitle']);
            $pdf->SetTitle('Violation');
            $pdf->SetSubject('');
            $pdf->SetKeywords('');
            $pdf->setHeaderFont(array('helvetica', '', 8));
            $pdf->setFooterFont(array('helvetica', '', 8));

            $pdf->SetMargins(10,10,10);

        }

        $pdf->Temporary();
        $pdf->AddPage();


        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->Image($_SESSION['blazon'], 10, 10, 15, 23);

        $ManagerName = $r_Customer['ManagerName'];
        $pdf->customer = $ManagerName;




        $rs_Fine = $rs->Select('V_ViolationArticle', "Id=".$FineId);
        $r_Fine = mysqli_fetch_array($rs_Fine);
        $ViolationTypeId = $r_Fine['ViolationTypeId'];
        $ProtocolYear = $r_Fine['ProtocolYear'];

        $ForeignProtocolLetterType1 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['ForeignProtocolLetterType1'];
        $ForeignProtocolLetterType2 = $a_ProtocolLetterLocality[$r_Fine['Locality']]['ForeignProtocolLetterType2'];


        //In questo caso "Id" corrisponde al RuleTypeId
        $rs_RuleType = $rs->Select('V_RuleType', "ViolationTypeId=".$ViolationTypeId." AND CityId='".$_SESSION['cityid']."' AND Id=".$_SESSION['ruletypeid']);
        $r_RuleType = mysqli_fetch_array($rs_RuleType);


        $RuleTypeId = $r_RuleType['Id'];
        $rs_ArticleTariff = $rs->Select('ArticleTariff', "ArticleId=".$r_Fine['ArticleId']." AND Year=".$r_Fine['ProtocolYear']);
        $r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);

        $trespassers = $rs->Select('V_Trespasser', "Id=".$r_Fine['TrespasserId']);
        $trespasser = mysqli_fetch_array($trespassers);

        $n_LanguageId = $trespasser['LanguageId'];
        $ZoneId= $trespasser['ZoneId'];

        $ManagerSubject = $r_RuleType['PrintHeader'.$a_Lan[$n_LanguageId]];
        $FormTypeId = $r_RuleType['ForeignFormId'];

        $a_PrintObject = explode("*",$r_RuleType['PrintObject'.$a_Lan[$n_LanguageId]]);



        $pdf->SetFont('arial', '', 10, '', true);
        // writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)

        $pdf->Line(7, 9, 200, 9);
        $pdf->writeHTMLCell(150, 0, 30, '', '<h3>'.$r_Customer['ManagerName'].'</h3>', 0, 0, 1, true, 'L', true);
        $pdf->LN(5);
        $pdf->writeHTMLCell(150, 0, 30, '', $ManagerSubject, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerZIP']." ".$r_Customer['ManagerCity']." (".$r_Customer['ManagerProvince'].")", 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerCountry'], 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerMail'], 0, 0, 1, true, 'L', true);
        $pdf->Line(7, 36, 200, 36);

        if($RuleTypeId==1){
            $pdf->LN(20);
        } else {
            $pdf->LN(10);
        }


        $str_TrespasserAddress =  trim(
            $trespasser['Address'] ." ".
            $trespasser['StreetNumber'] ." ".
            $trespasser['Ladder'] ." ".
            $trespasser['Indoor'] ." ".
            $trespasser['Plan']
        );

        $pdf->writeHTMLCell(100, 0, 110, '', '<h4>'.$trespasser['CompanyName'].$trespasser['Surname'].' '.$trespasser['Name'].'</h4>', 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(100, 0, 110, '', $str_TrespasserAddress, 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(100, 0, 110, '', $trespasser['ZIP'].' '.$trespasser['City'], 0, 0, 1, true, 'L', true);
        $pdf->LN(4);
        $pdf->writeHTMLCell(100, 0, 110, '', strtoupper($trespasser['CountryTitle']), 0, 0, 1, true, 'L', true);
        $pdf->LN(15);


        $str_AdditionalNight = "";
        if($r_ArticleTariff['AdditionalNight']==1){

            $a_Time = explode(":",$r_Fine['FineTime']);

            if($a_Time[0]<FINE_HOUR_START_DAY ||  ($a_Time[0]>FINE_HOUR_END_DAY) || ($a_Time[0]==FINE_HOUR_END_DAY && $a_Time[1]!="00")){
                $str_AdditionalNight = $a_AdditionalNight[$n_LanguageId];
            }
        }

        $str_AdditionalMass = "";
        if($r_ArticleTariff['AdditionalMass']==1){
            if($r_Fine['VehicleMass'] > 3.5) $str_AdditionalMass = $a_AdditionalMass[$n_LanguageId];
        }


        if($r_ArticleTariff['126Bis']==1){
            $chk_126Bis = true;
            $n_LicensePoint += $r_ArticleTariff['LicensePoint'];
        }

        //Nel caso in cui l'articolo deve cambiare ed è diverso da quello del verbale, vengono ricalcolati e aggiornati gli importi
        //ArticleSelect viene passato da mgmt_violation_act.php
        $NewArticle = CheckValue('ArticleSelect','n');
        if($NewArticle > 0 && $NewArticle != $r_Fine['ArticleId']){
            $rs_NewArticle = $rs->SelectQuery("SELECT * FROM Article A JOIN ArticleTariff AT ON A.Id=AT.ArticleId AND AT.Year={$r_Fine['ProtocolYear']} WHERE A.Id=$NewArticle");
            $r_NewArticle = mysqli_fetch_assoc($rs_NewArticle);
            
            //Se prevista riapplico la maggiorazione
            if($r_NewArticle['AdditionalNight']){
                $aTime = explode(":",$r_Fine['FineTime']);
                if($aTime[0]<FINE_HOUR_START_DAY ||  ($aTime[0]>FINE_HOUR_END_DAY) || ($aTime[0]==FINE_HOUR_END_DAY && $aTime[1]!="00")){
                    $r_NewArticle['Fee'] = $r_NewArticle['Fee'] + round($r_NewArticle['Fee']/FINE_NIGHT,2);
                    $r_NewArticle['MaxFee'] = $r_NewArticle['MaxFee'] + round($r_NewArticle['MaxFee']/FINE_NIGHT,2);
                }
            }
            
            $n_TotFee += $r_NewArticle['Fee'];
            $n_TotMaxFee += $r_NewArticle['MaxFee'];
            
            if ($r_NewArticle['ReducedPayment'] == 1) {
                $chk_ReducedPayment = true;
                $n_TotPartialFee += $r_NewArticle['Fee'] * FINE_PARTIAL;
            } else {
                $n_TotPartialFee += $r_NewArticle['Fee'];
            }
            
            if($ultimate){
                $a_UpdateFineArticle = array(
                    array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$NewArticle,'settype'=>'int'),
                    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$r_NewArticle['Fee'],'settype'=>'flt'),
                    array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$r_NewArticle['MaxFee'],'settype'=>'flt'),
                );
                
                $rs->Update('FineArticle', $a_UpdateFineArticle, "FineId=$FineId");
            }
        } else {
            $n_TotFee += $r_Fine['Fee'];
            $n_TotMaxFee += $r_Fine['MaxFee'];
            if ($r_ArticleTariff['ReducedPayment'] == 1) {
                $chk_ReducedPayment = true;
                $n_TotPartialFee += $r_Fine['Fee'] * FINE_PARTIAL;
            } else {
                $n_TotPartialFee += $r_Fine['Fee'];
            }
        }

        if(($r_Fine['Article']==126 AND $r_Fine['Paragraph']=='0' AND $r_Fine['Letter']=='bis') || ($r_Fine['Article']==180 AND $r_Fine['Paragraph']=='8' AND $r_Fine['Letter']=='')){

            $PreviousId = $r_Fine['PreviousId'];

            $rs_PreviousFine = $rs->Select('Fine', "Id=".$PreviousId);
            $r_PreviousFine = mysqli_fetch_array($rs_PreviousFine);

            $PreviousProtocolId = $r_PreviousFine['ProtocolId'];
            $PreviousProtocolYear = $r_PreviousFine['ProtocolYear'];
            $PreviousFineDate = DateOutDB($r_PreviousFine['FineDate']);



        } else {


            $PreviousProtocolId = "";
            $PreviousProtocolYear = "";
            $PreviousFineDate = "";


        }

        $postalcharge=getPostalCharge($_SESSION['cityid'],$NotificationDate);
        
        if($ChargeTotalFee>0){
            $ResearchFee = $ChargeTotalFee - $postalcharge['Zone'.$ZoneId];
            $NotificationFee = $postalcharge['Zone'.$ZoneId];
        }else{
            if($NotificationFee==0){
                $NotificationFee = $postalcharge['Zone'.$ZoneId];
            }
        }


        $CustomerFee = $r_Fine['CustomerAdditionalFee'];
        $NotificationFee += $r_Fine['OwnerAdditionalFee']+$CustomerFee;

        $AdditionalFee =  $NotificationFee + $ResearchFee;

        $FineTime = $r_Fine['FineTime'];

        if($r_Fine['DetectorId']==0){
            $DetectorTitle = "";
            $SpeedLengthAverage = 0;
        } else {
            $detectors = $rs->Select('Detector',"Id=".$r_Fine['DetectorId']);
            $detector = mysqli_fetch_array($detectors);

            $DetectorTitle = $detector['Title'.$a_Lan[$n_LanguageId]];
            $SpeedLengthAverage = $detector['SpeedLengthAverage'];


        }

        $str_ProtocolLetter = ($RuleTypeId==1) ? $ForeignProtocolLetterType1 : $ForeignProtocolLetterType2;
        $Content = getStaticContent($FormTypeId,$_SESSION['cityid'],2, $n_LanguageId);

        $Content = str_replace("{PrintObjectRow1}",$a_PrintObject[0],$Content);
        $Content = str_replace("{PrintObjectRow2}",$a_PrintObject[1],$Content);
        $Content = str_replace("{PrintObjectRow3}",$a_PrintObject[2],$Content);



        $Content = str_replace("{FineDate}",DateOutDB($r_Fine['FineDate']),$Content);
        $Content = str_replace("{FineTime}",TimeOutDB($FineTime),$Content);
        $Content = str_replace("{VehicleTypeId}",$r_Fine['VehicleTitle'.$a_Lan[$n_LanguageId]],$Content);
        $Content = str_replace("{VehiclePlate}",$r_Fine['VehiclePlate'],$Content);

        $Content = str_replace("{VehicleBrand}",$r_Fine['VehicleBrand'],$Content);
        $Content = str_replace("{VehicleModel}",$r_Fine['VehicleModel'],$Content);
        $Content = str_replace("{VehicleColor}",$r_Fine['VehicleColor'],$Content);

        $Content = str_replace("{IuvCode}", $r_Fine['IuvCode'], $Content);

        $Paragraph = ($r_Fine['Paragraph']=="0" || $r_Fine['Paragraph']=="") ? "" : " / ".$r_Fine['Paragraph'];
        $Letter = ($r_Fine['Letter']=="0") ? "" : $r_Fine['Letter'];

        $Content = str_replace("{ArticleId}",$r_Fine['Article'].$Paragraph." ".$Letter,$Content);



        /////////////////////////////////////////////
        //Article Owner
        /////////////////////////////////////////////
        $rs_FineOwner = $rs->Select('FineOwner',"FineId=".$FineId);
        $r_FineOwner = mysqli_fetch_array($rs_FineOwner);

        $str_ArticleDescription = (strlen(trim($r_FineOwner['ArticleDescription'.$a_Lan[$n_LanguageId]]))>0) ?  $r_FineOwner['ArticleDescription'.$a_Lan[$n_LanguageId]] : $r_Fine['ArticleDescription'.$a_Lan[$n_LanguageId]] ;


        /////////////////////////////////////////////
        //Additional Article
        /////////////////////////////////////////////

        if($r_Fine['ArticleNumber']>1){
            $rs_AdditionalArticle = $rs->Select('V_AdditionalArticle',"FineId=".$FineId, "ArticleOrder");
            while($r_AdditionalArticle=mysqli_fetch_array($rs_AdditionalArticle)){

                if($r_ArticleTariff['126Bis']==1){
                    $chk_126Bis = true;
                    $n_LicensePoint += $r_AdditionalArticle['LicensePoint'];
                }

                $n_TotFee += $r_AdditionalArticle['Fee'];
                $n_TotMaxFee += $r_AdditionalArticle['MaxFee'];
                if($r_AdditionalArticle['ReducedPayment']==1){
                    $chk_ReducedPayment = true;
                    $n_TotPartialFee +=  $r_AdditionalArticle['Fee']*FINE_PARTIAL;
                } else {
                    $n_TotPartialFee +=  $r_AdditionalArticle['Fee'];
                }

                $str_AdditionalArticleDescription = (strlen(trim($r_AdditionalArticle['AdditionalArticleDescription'.$a_Lan[$n_LanguageId]]))>0)  ? $r_AdditionalArticle['AdditionalArticleDescription'.$a_Lan[$n_LanguageId]] : $r_AdditionalArticle['ArticleDescription' . $a_Lan[$n_LanguageId]];


                $Paragraph = ($r_AdditionalArticle['Paragraph']=="0") ? "" : $r_AdditionalArticle['Paragraph']." ";
                $Letter = ($r_AdditionalArticle['Letter']=="0") ? "" : $r_AdditionalArticle['Letter'];


                $str_ArticleDescription .= " Art. ". $r_AdditionalArticle['Article']."/".$Paragraph.$Letter;
                $str_ArticleDescription .= $str_AdditionalArticleDescription;



            }

        }




        $Content = str_replace("{ArticleDescription}",$str_ArticleDescription, $Content);

        $str_ControllerName = trim($r_Fine['ControllerQualification']." ".$r_Fine['ControllerName']);
        $str_ControllerCode = trim($r_Fine['ControllerCode']);
        /////////////////////////////////////////////
        //Additional controller
        /////////////////////////////////////////////

        $rs_FineAdditionalController = $rs->Select('V_AdditionalController', "FineId=" . $FineId);

        while ($r_FineAdditionalController = mysqli_fetch_array($rs_FineAdditionalController)){
            $str_ControllerCode="";
            $str_ControllerName .= ", ".trim($r_FineAdditionalController['ControllerQualification']." ".$r_FineAdditionalController['ControllerName']);
        }



        $str_ReasonDescription = (strlen(trim($r_FineOwner['ReasonDescription'.$a_Lan[$n_LanguageId]]))>0) ?  $r_FineOwner['ReasonDescription'.$a_Lan[$n_LanguageId]] : $r_Fine['ReasonTitle'.$a_Lan[$n_LanguageId]] ;
        $Content = str_replace("{ReasonId}", $str_ReasonDescription, $Content);


        if($SpeedLengthAverage>0) {
            $SpeedTimeAverage = $SpeedLengthAverage*3.6/$r_Fine['SpeedControl'];

            $Content = str_replace("{SpeedTimeAverage}", NumberDisplay($SpeedTimeAverage), $Content);
            $Content = str_replace("{SpeedLengthAverage}", $SpeedLengthAverage, $Content);
        }


        /////////////////////////////////////////////
        //126 BIS
        /////////////////////////////////////////////
        $Content = str_replace("{PreviousProtocolId}",$PreviousProtocolId,$Content);
        $Content = str_replace("{PreviousProtocolYear}",$PreviousProtocolYear,$Content);
        $Content = str_replace("{PreviousFineDate}",$PreviousFineDate,$Content);

        $TotalFee = $n_TotFee+$AdditionalFee;


        $Content = str_replace("{Speed}",$r_Fine['Speed'],$Content);
        $Content = str_replace("{SpeedLimit}",NumberDisplay($r_Fine['SpeedLimit']),$Content);
        $Content = str_replace("{SpeedControl}",NumberDisplay($r_Fine['SpeedControl']),$Content);

        $Content = str_replace("{Locality}",$r_Fine['CityTitle'],$Content);
        $Content = str_replace("{Address}",$r_Fine['Address'],$Content);

        $Content = str_replace("{DetectorId}",$DetectorTitle,$Content);

        $Content = str_replace("{BankOwner}",$r_Customer['ForeignBankOwner'],$Content);
        $Content = str_replace("{BankName}",$r_Customer['ForeignBankName'],$Content);
        $Content = str_replace("{BankAccount}",$r_Customer['ForeignBankAccount'],$Content);
        $Content = str_replace("{BankSwift}",$r_Customer['ForeignBankSwift'],$Content);
        $Content = str_replace("{BankIban}",$r_Customer['ForeignBankIban'],$Content);



        $Content = str_replace("{Fee}",NumberDisplay($n_TotFee),$Content);
        $Content = str_replace("{TotalFee}",NumberDisplay($TotalFee),$Content);



        $PartialFee = number_format($n_TotFee*FINE_PARTIAL,2);
        $TotalDiscountFee = $n_TotPartialFee + $ResearchFee + $NotificationFee;

        $Content = str_replace("{TotalDiscountFee}",NumberDisplay($TotalDiscountFee),$Content);

        $Content = str_replace("{PartialFee}",NumberDisplay($PartialFee),$Content);
        $Content = str_replace("{ResearchFee}",NumberDisplay($ResearchFee),$Content);
        $Content = str_replace("{NotificatioFee}",NumberDisplay($NotificationFee),$Content);
        $Content = str_replace("{ChargeTotalFee}",NumberDisplay($ChargeTotalFee),$Content);



        $Content = str_replace("{ControllerName}", $str_ControllerName, $Content);
        $Content = str_replace("{ControllerCode}", $str_ControllerCode, $Content);

        $Content = str_replace("{ChiefControllerName}",$r_ChiefController['Name'],$Content);
        $Content = str_replace("{AdditionalSanction}",$str_AdditionalNight.$str_AdditionalMass,$Content);

        $Content = str_replace("{ProtocolYear}",$ProtocolYear,$Content);
        $Content = str_replace("{ProtocolLetter}",$str_ProtocolLetter,$Content);

        if($r_Fine['TrespasserTypeId']==11){
            $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=".$FineId." AND TrespasserTypeId=10");
            $r_trespasser = mysqli_fetch_array($rs_trespasser);

            $Content = str_replace("{TrespasserName}",$r_trespasser['CompanyName'].' '.$r_trespasser['Surname'].' '.$r_trespasser['Name'],$Content);

            $Content = str_replace("{TrespasserAddress}",$trespasser['Address']." ".$trespasser['ZIP'].' '.$trespasser['City'],$Content);
            $Content = str_replace("{TrespasserCountry}",$trespasser['CountryTitle'],$Content);

        } else {

            if ($r_Fine['TrespasserTypeId'] == 2 || $r_Fine['TrespasserTypeId'] == 3 || $r_Fine['TrespasserTypeId'] == 15 || $r_Fine['TrespasserTypeId'] == 16) {
                $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=2");
                $r_trespasser = mysqli_fetch_array($rs_trespasser);
                $Content = str_replace("{TrespasserName}",$r_trespasser['CompanyName'].' '.$r_trespasser['Surname'].' '.$r_trespasser['Name'],$Content);

                if (strlen($r_trespasser['BornDate'])==10){
                    $Content = str_replace("{TrespasserBornDate}",DateOutDB($r_trespasser['BornDate']),$Content);
                }else{
                    $Content = str_replace("{TrespasserBornDate}","",$Content);
                }
                $Content = str_replace("{TrespasserAddress}",$r_trespasser['Address']." ".$r_trespasser['ZIP'].' '.$r_trespasser['City'],$Content);
                $Content = str_replace("{TrespasserCountry}",$r_trespasser['CountryTitle'],$Content);

            } else {

                $Content = str_replace("{TrespasserName}",$trespasser['CompanyName'].' '.$trespasser['Surname'].' '.$trespasser['Name'],$Content);

                if (strlen($trespasser['BornDate'])==10){
                    $Content = str_replace("{TrespasserBornDate}",DateOutDB($trespasser['BornDate']),$Content);
                }else{
                    $Content = str_replace("{TrespasserBornDate}","",$Content);
                }
                $Content = str_replace("{TrespasserAddress}",$trespasser['Address']." ".$trespasser['ZIP'].' '.$trespasser['City'],$Content);
                $Content = str_replace("{TrespasserCountry}",$trespasser['CountryTitle'],$Content);

            }

        }








        $offices = $rs->Select('V_JudicialOffice', "CityId='".$_SESSION['cityid']."'");
        while ($office = mysqli_fetch_array($offices)){
            if($office['OfficeId']==1){
                $Content = str_replace("{Judge}",$office['OfficeTitle'.$a_Lan[$n_LanguageId]],$Content);
                $Content = str_replace("{JudgeCity}",$office['City'],$Content);
                $Content = str_replace("{JudgeProvince}",$office['Province'],$Content);
            }
            if($office['OfficeId']==2){
                $Content = str_replace("{Court}",$office['OfficeTitle'.$a_Lan[$n_LanguageId]],$Content);
                $Content = str_replace("{CourtCity}",$office['City'],$Content);
                $Content = str_replace("{CourtProvince}",$office['Province'],$Content);

            }

        }


        $Content = str_replace("{Code}",$r_Fine['Code'],$Content);
        if($ultimate){

            $RndCode = "";
            for($i=0;$i<5;$i++){
                $n = rand(1, 24);
                $RndCode .= substr($strCode,$n,1);
                $n = rand(0, 9);
                $RndCode .= $n;
            }

            if($ProtocolNumber==0) $rs->Start_Transaction();

            if($r_Fine['ProtocolId'] > 0) $ProtocolNumber = $r_Fine['ProtocolId'];
            else if($r_Fine['ProtocolIdAssigned'] == 0){
                $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId, MAX(ProtocolIdAssigned) ProtocolIdAssigned FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " AND RuleTypeId=" . $RuleTypeId);

                $r_Protocol = mysqli_fetch_array($rs_Protocol);

                $n_Protocol = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];

                $ProtocolNumber = $n_Protocol + 1;

            } else $ProtocolNumber = $r_Fine['ProtocolIdAssigned'];

            $Content = str_replace("{ProtocolId}",$ProtocolNumber,$Content);



            $strProtocolNumber = "";
            for($k=strlen((string)$ProtocolNumber);$k<9;$k++)
            {
                $strProtocolNumber.= "0";
            }
            $strProtocolNumber.=$ProtocolNumber;

            $Documentation = $ProtocolYear."_".$strProtocolNumber."_".date("Y-m-d")."_".$_SESSION['cityid']."_".$RndCode.".pdf";
        }else{
            if($ProtocolNumber==0){
                $rs_Protocol = $rs->SelectQuery("SELECT MAX(ProtocolId) ProtocolId, MAX(ProtocolIdAssigned) ProtocolIdAssigned FROM V_FineAll WHERE CityId='" . $_SESSION['cityid'] . "' AND ProtocolYear=" . $_SESSION['year'] . " AND RuleTypeId=" . $RuleTypeId);

                $r_Protocol = mysqli_fetch_array($rs_Protocol);

                $n_Protocol = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];

                $ProtocolNumber = $n_Protocol;
            }
            $ProtocolNumber++;
            $Content = str_replace("{ProtocolId}",$ProtocolNumber." - PROVV",$Content);
        }


        $aMainPart = explode("<main_part>",$Content);
        $aRow = explode("<row>",$aMainPart[1]);

        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[1], 0, 0, 1, true, 'L', true);
        $pdf->LN(10);



        if ($r_Customer['PDFRefPrint']) {
            $pdf->writeHTMLCell(180, 0, 10, '', $aRow[2], 0, 0, 1, true, 'L', true);
            $pdf->LN(10);
        } else {
            $pdf->writeHTMLCell(190, 0, 10, '', '', 0, 0, 1, true, 'L', true);
            $pdf->LN(10);
        }



        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[3], 0, 0, 1, true, 'C', true);
        $pdf->LN(5);
        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[4], 0, 0, 1, true, 'C', true);
        $pdf->LN(5);
        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[5], 0, 0, 1, true, 'C', true);

        if($RuleTypeId==1){
            $pdf->LN(10);
        } else {
            $pdf->LN(5);

        }


        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[6], 0, 0, 1, true, 'J', true);
        if($RuleTypeId==1) {
            $pdf->LN(15);
        } else {
            $pdf->LN(0);
        }


        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[7], 0, 0, 1, true, 'J', true);
        if($RuleTypeId==1){
            $pdf->LN(30);
        } else {
            $pdf->LN(15);
        }

        $pdf->SetFont('arial', '', 8, '', true);
        for($i=8; $i<21; $i++){
            $aCol = explode("<col>",$aRow[$i]);
            if($i==18){
                if($r_Fine['TrespasserTypeId']==11){

                    $aCol[0] = $a_Rent[$n_LanguageId];
                    $aCol[1] = $trespasser['CompanyName'].' '.$trespasser['Surname'].' '.$trespasser['Name'];

                }else if($r_Fine['TrespasserTypeId'] == 2 || $r_Fine['TrespasserTypeId'] == 3 || $r_Fine['TrespasserTypeId'] == 15 || $r_Fine['TrespasserTypeId'] == 16){
                    $rs_trespasser = $rs->Select('V_FineTrespasser', "FineId=" . $FineId . " AND TrespasserTypeId=3");
                    $r_trespasser = mysqli_fetch_array($rs_trespasser);

                    $aCol[0] = $a_Rent[$n_LanguageId];
                    $aCol[1] = $r_trespasser['CompanyName'].' '.$r_trespasser['Surname'].' '.$r_trespasser['Name'];
                }
            }

            if(! (trim($aCol[1])=="0" || trim($aCol[1])=="" || trim($aCol[1])=="//")){

                $y = $pdf->getY();
                $height = 4;

                $pdf->writeHTMLCell(60, $height, 20, $y, $aCol[0], 0, 0, 1, true, 'L', true);

                if($i==13){
                    if($r_Fine['Speed']>0)
                        $aCol[1].= "<br>".$aCol[2];
                }
                $height += $height*(floor(strlen($aCol[1])/60));
                $pdf->writeHTMLCell(100, $height, 80, $y, $aCol[1], 0, 0, 1, true, 'L', true);
                $pdf->LN();

            }
        }

        $pdf->LN(10);
        $pdf->SetFont('arial', '', 9, '', true);
        $y = $pdf->getY();
        $pdf->writeHTMLCell(180, 0, 10, $y, $aRow[21], 0, 0, 1, true, 'J', true);
        if($RuleTypeId==1){
            $pdf->LN(10);
        } else {
            $pdf->LN(10);
        }


        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[22], 0, 0, 1, true, 'R', true);
        $pdf->LN(5);
        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[23], 0, 0, 1, true, 'R', true);

        if ($r_ArticleTariff['ReducedPayment']) {
            $pdf->LN(5);
            $pdf->writeHTMLCell(180, 0, 10, '', $aRow[24], 0, 0, 1, true, 'L', true);
        }

        
        if($RuleTypeId==1){
            $pdf->Temporary();
            $pdf->RightHeader = false;

            $pdf->AddPage();
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('arial', '', 10, '', true);

            $pdf->SetFillColor(255, 255, 255);

            $pdf->Image($_SESSION['blazon'], 10, 10, 15, 23);

            $ManagerName = $r_Customer['ManagerName'];

            // writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)
            $pdf->Line(7, 9, 200, 9);
            $pdf->writeHTMLCell(150, 0, 30, '', '<h3>'.$r_Customer['ManagerName'].'</h3>', 0, 0, 1, true, 'L', true);
            $pdf->LN(5);
            $pdf->writeHTMLCell(150, 0, 30, '', $ManagerSubject, 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerZIP']." ".$r_Customer['ManagerCity']." (".$r_Customer['ManagerProvince'].")", 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerCountry'], 0, 0, 1, true, 'L', true);
            $pdf->Line(7, 34, 200, 34);
            $pdf->LN(20);
        }else{
            $pdf->LN(5);
        }


        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[25], 0, 0, 1, true, 'J', true);


        if($RuleTypeId==1) {
            $pdf->LN(40);
        } else {
            $pdf->LN(18);
        }

        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[26], 0, 0, 1, true, 'J', true);

        if($r_ArticleTariff['126Bis']==1){
            $query = "Select ART.Fee, ART.MaxFee from Article AS A join ArticleTariff AS ART on A.Id = ART.ArticleId ";
            $query.= "WHERE A.CityId='".$_SESSION['cityid']."' AND A.Article=126 AND A.Letter='bis' AND ART.Year = ".$_SESSION['year'];

            $articles126bis = $rs->SelectQuery($query);
            $article126bis = mysqli_fetch_array($articles126bis);
            $pdf->LN(55);

            $aRow[27] = str_replace("{DecurtationPoints}",$r_ArticleTariff['LicensePoint'],$aRow[27]);
            $aRow[27] = str_replace("{Fee126bis}",NumberDisplay($article126bis['Fee']),$aRow[27]);
            $aRow[27] = str_replace("{MaxFee126bis}",NumberDisplay($article126bis['MaxFee']),$aRow[27]);
            $pdf->writeHTMLCell(180, 0, 10, '', $aRow[27], 0, 0, 1, true, 'J', true);
            $pdf->LN(30);
        }
        else
            if($RuleTypeId==1) {
                $pdf->LN(60);
            } else {
                $pdf->LN(7);
            }

        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[28], 0, 0, 1, true, 'J', true);

        if($RuleTypeId==1) {
            $pdf->LN(30);
        } else {
            $pdf->LN(5);
        }

        if ($r_ArticleTariff['ReducedPayment']) {
            $pdf->writeHTMLCell(180, 60, 10, '', $aRow[29], 0, 0, 1, true, 'J', true);
            $pdf->LN(15);
        }
        $pdf->writeHTMLCell(180, 0, 10, '', $aRow[30], 0, 0, 1, true, 'L', true);
        $pdf->LN(10);


        if($r_Customer['ManagerSignName']==""){
            if($r_Customer['CityUnion']>1){
                $pdf->writeHTMLCell(180, 60, 10, '', $r_Fine['CityTitle'].", ".$CreationDate, 0, 0, 1, true, 'L', true);
            }else{
                $pdf->writeHTMLCell(180, 60, 10, '', $r_Customer['ManagerName'].", ".$CreationDate, 0, 0, 1, true, 'L', true);
            }
        }else{
            $pdf->writeHTMLCell(180, 60, 10, '', $r_Customer['ManagerSignName'].", ".$CreationDate, 0, 0, 1, true, 'L', true);
        }





        if($RuleTypeId==1){
            $pdf->LN(10);
        } else {
            $pdf->LN(5);
        }


        if(strlen($r_ChiefController['Name'])>0){


            if($RuleTypeId==1){
                $pdf->writeHTMLCell(90, 60, 90, '', $aRow[31], 0, 0, 1, true, 'C', true);
                $pdf->LN(4);
                $pdf->writeHTMLCell(90, 60, 90, '', $aRow[32], 0, 0, 1, true, 'C', true);
                $pdf->LN(4);

                $y = $pdf->getY();

                if (file_exists(ROOT."/img/sign/".$_SESSION['cityid']."/".$r_ChiefController['Sign'])) {
                    $pdf->Image(SIGN."/".$_SESSION['cityid']."/".$r_ChiefController['Sign'], 90, $y, 80, 30);
                }else{
                    if(!empty($aRow[34]))
                    {
                        $pdf->writeHTMLCell(90, 60, 90, '', $aRow[33], 0, 0, 1, true, 'C', true);
                        $pdf->LN(4);
                        $pdf->writeHTMLCell(90, 60, 90, '', $aRow[34], 0, 0, 1, true, 'C', true);
                        $pdf->LN(0);
                    }
                    else {
                        $pdf->writeHTMLCell(120, 60, 90, '', $r_ChiefController['Name'], 0, 0, 1, true, 'C', true);
                    }
                }


            } else if($RuleTypeId==3){
                $pdf->writeHTMLCell(90, 60, 90, '', $aRow[31], 0, 0, 1, true, 'C', true);
                $pdf->LN(4);
                $pdf->writeHTMLCell(90, 60, 90, '', $aRow[32], 0, 0, 1, true, 'C', true);
                $pdf->LN(0);

                $y = $pdf->getY();

                if (file_exists(ROOT."/img/sign/".$_SESSION['cityid']."/".$r_ChiefController['Sign'])) {
                    $pdf->Image(SIGN."/".$_SESSION['cityid']."/".$r_ChiefController['Sign'], 110, $y, 80, 25);
                }else{
                    if(!empty($aRow[34]))
                    {
                        $pdf->writeHTMLCell(90, 60, 90, '', $aRow[33], 0, 0, 1, true, 'C', true);
                        $pdf->LN(4);
                        $pdf->writeHTMLCell(90, 60, 90, '', $aRow[34], 0, 0, 1, true, 'C', true);
                        $pdf->LN(0);
                    }
                    else {
                    $pdf->writeHTMLCell(120, 60, 90, '', $r_ChiefController['Name'], 0, 0, 1, true, 'C', true);
                    }
                }
            } else {
                $pdf->writeHTMLCell(120, 60, 90, '', $aRow[31], 0, 0, 1, true, 'C', true);
                $pdf->LN(4);
                $pdf->writeHTMLCell(120, 60, 90, '', $aRow[32], 0, 0, 1, true, 'C', true);
                $pdf->LN(4);
                $pdf->writeHTMLCell(120, 60, 90, '', $r_ChiefController['Name'], 0, 0, 1, true, 'C', true);
            }
        }

        if($chk_126Bis) {
            //////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////
            ////
            ////
            ////    LicensePoint page 1
            ////
            ////
            //////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////
            $pdf->Temporary();
            $pdf->RightHeader = false;
            $pdf->AddPage();
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('arial', '', 10, '', true);

            $aRow = explode("<row>", $aMainPart[2]);
            $aRow[1] = $ManagerSubject;

            // writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)
            $pdf->Image($_SESSION['blazon'], 10, 10, 15, 23);
            $pdf->Line(7, 9, 200, 9);
            $pdf->writeHTMLCell(150, 0, 30, '', '<h3>'.$r_Customer['ManagerName'].'</h3>', 0, 0, 1, true, 'L', true);
            $pdf->LN(5);
            $pdf->writeHTMLCell(150, 0, 30, '', strtoupper($aRow[1]), 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerZIP']." ".$r_Customer['ManagerCity']." (".$r_Customer['ManagerProvince'].")", 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerCountry'], 0, 0, 1, true, 'L', true);
            $pdf->Line(7, 34, 200, 34);
            $pdf->LN(25);

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->writeHTMLCell(190, 0, 10, '', strtoupper($aRow[2]), 0, 0, 1, true, 'C', true);
            $pdf->LN();
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->writeHTMLCell(190, 0, 10, '', $aRow[3], 0, 0, 1, true, 'C', true);
            $pdf->LN();
            $pdf->LN(5);

            $pdf->SetFont('Arial', '', 10);


            $aCol = explode("<col>", $aRow[4]);
            $y = $pdf->getY();
            $pdf->writeHTMLCell(30, 5, 10, $y, $aCol[0], 0, 0, 1, true, 'L', true);
            $pdf->LN();
            $pdf->writeHTMLCell(150, 5, 30, $y, $aCol[1], 0, 0, 1, true, 'J', true);
            $pdf->LN();
            $y = $pdf->getY();
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->writeHTMLCell(150, 5, 30, $y, strtoupper($aCol[2]), 0, 0, 1, true, 'J', true);
            $pdf->LN();
            $pdf->LN(5);

            $pdf->SetFont('Arial', '', 10);
            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[5], 0, 0, 1, true, 'L', true);
            $pdf->LN();
            $pdf->LN(5);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[6], 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[7], 0, 0, 1, true, 'L', true);
            $pdf->LN();
            $pdf->LN(5);

            $y = $pdf->getY();
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[8], 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $y = $pdf->getY();
            $pdf->SetFont('Arial', '', 10);
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[9], 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[10], 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[11], 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[12], 0, 0, 1, true, 'L', true);
            $pdf->LN();

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[13], 0, 0, 1, true, 'L', true);
            $pdf->LN();
            $pdf->LN(5);
            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[14], 0, 0, 1, true, 'L', true);
            $pdf->LN();
            $pdf->LN(10);
            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[15], 0, 0, 1, true, 'L', true);
            $pdf->LN();
            $pdf->LN(10);
            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[16], 0, 0, 1, true, 'J', true);
            $pdf->LN();
            $pdf->LN(5);
            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 5, 10, $y, $aRow[17], 0, 0, 1, true, 'J', true);
            $pdf->LN();



            //////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////
            ////
            ////
            ////    LicensePoint page 2
            ////
            ////
            //////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////////////
            $pdf->Temporary();
            $pdf->RightHeader = false;
            $pdf->AddPage();
            $pdf->SetTextColor(0, 0, 0);

            $pdf->SetFont('arial', '', 10, '', true);
            // writeHTMLCell($w, $h, $x, $y, $html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)
            $pdf->Image($_SESSION['blazon'], 10, 10, 15, 23);
            $pdf->Line(7, 9, 200, 9);
            $pdf->writeHTMLCell(150, 0, 30, '', '<h3>'.$r_Customer['ManagerName'].'</h3>', 0, 0, 1, true, 'L', true);
            $pdf->LN(5);
            $pdf->writeHTMLCell(150, 0, 30, '', strtoupper($aRow[1]), 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerAddress'], 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerZIP']." ".$r_Customer['ManagerCity']." (".$r_Customer['ManagerProvince'].")", 0, 0, 1, true, 'L', true);
            $pdf->LN(4);
            $pdf->writeHTMLCell(150, 0, 30, '', $r_Customer['ManagerCountry'], 0, 0, 1, true, 'L', true);
            $pdf->Line(7, 34, 200, 34);
            $pdf->LN(25);

            $aRow = explode("<row>", $aMainPart[3]);

            $pdf->SetFont('Arial', 'B', 10);

            $pdf->writeHTMLCell(190, 0, 10, '', $aRow[1], 0, 0, 1, true, 'C', true);
            $pdf->LN();
            $pdf->LN(5);

            $pdf->SetFont('Arial', '', 10);
            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[2], 0, 0, 1, true, 'J', true);
            $pdf->LN();
            $pdf->LN(5);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[3], 0, 0, 1, true, 'J', true);
            $pdf->LN();
            $pdf->LN(5);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[4], 0, 0, 1, true, 'J', true);
            $pdf->LN();
            $pdf->LN(2);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[5], 0, 0, 1, true, 'J', true);
            $pdf->LN();
            $pdf->LN(2);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[6], 0, 0, 1, true, 'J', true);
            $pdf->LN();
            $pdf->LN(2);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[7], 0, 0, 1, true, 'J', true);
            $pdf->LN();
            $pdf->LN(2);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[8], 0, 0, 1, true, 'J', true);
            $pdf->LN();
            $pdf->LN(2);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[9], 0, 0, 1, true, 'J', true);
            $pdf->LN();
            $pdf->LN(2);

            $y = $pdf->getY();
            $pdf->writeHTMLCell(190, 0, 10, $y, $aRow[10], 0, 0, 1, true, 'J', true);




        }
        if ($ultimate){

            $aInsert = array(
                array('field'=>'NotificationTypeId','selector'=>'value','type'=>'int','value'=>$NotificationTypeId,'settype'=>'int'),
                array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$_SESSION['cityid']),
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Fine['TrespasserId'],'settype'=>'int'),
                array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$r_Fine['TrespasserTypeId'],'settype'=>'int'),
                array('field'=>'CustomerFee','selector'=>'value','type'=>'flt','value'=>$CustomerFee,'settype'=>'flt'),
                array('field'=>'NotificationFee','selector'=>'value','type'=>'flt','value'=>$NotificationFee,'settype'=>'flt'),
                array('field'=>'ResearchFee','selector'=>'value','type'=>'flt','value'=>$ResearchFee,'settype'=>'flt'),
                array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$SelectChiefControllerId,'settype'=>'int'),
                array('field'=>'NotificationDate','selector'=>'value','type'=>'date','value'=>$NotificationDate),
                array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
            );
            $rs->Insert('FineHistory',$aInsert);

            $aUpdate = array(
                array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId, 'settype' => 'int'),
                array('field'=>'ProtocolId','selector'=>'value','type'=>'int','value'=>$ProtocolNumber, 'settype' => 'int'),
                array('field'=>'UIFineChiefControllerId','selector'=>'value','type'=>'int','value'=>$SelectChiefControllerId,'settype' => 'int'),
            );
            $rs->Update('Fine',$aUpdate, 'Id='.$FineId);

            $aInsert = array(
                array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$Documentation),
                array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>2),
            );
            $rs->Insert('FineDocumentation',$aInsert);

            if (!is_dir(FOREIGN_FINE."/".$_SESSION['cityid']."/".$FineId)) {
                mkdir(FOREIGN_FINE."/".$_SESSION['cityid']."/".$FineId, 0777);
            }

            $FileName = $Documentation;

            $pdf->Output(FOREIGN_FINE."/".$_SESSION['cityid']."/".$FineId."/".$FileName, "F");


            if($FinePDFList){
                $n_PageCount = $pdf_union->setSourceFile(FOREIGN_FINE."/".$_SESSION['cityid']."/".$FineId."/".$FileName);
                for($p=1;$p<=$n_PageCount;$p++){

                    $tmp_Page = $pdf_union->ImportPage($p);
                    $tmp_Size = $pdf_union->getTemplatesize($tmp_Page);

                    $str_Format = ($tmp_Size['w']>$tmp_Size['h']) ? 'L' : 'P';

                    $pdf_union->AddPage($str_Format, array($tmp_Size['w'],$tmp_Size['h']),false);
                    $pdf_union->useTemplate($tmp_Page);
                }
            }



        }else{
            $FileName = 'export.pdf';
        }
    }

    if ($ultimate){
        $rs->End_Transaction();


        if (!is_dir(FOREIGN_FINE."/".$_SESSION['cityid']."/create")) {
            mkdir(FOREIGN_FINE."/".$_SESSION['cityid']."/create", 0777);
        }

        $FileName = $_SESSION['cityid']."_".date("Y-m-d_H-i-s").".pdf";
        if($FinePDFList){
            $pdf_union->Output(FOREIGN_FINE."/".$_SESSION['cityid'].'/create/'.$FileName, "F");
            $_SESSION['Documentation'] = $MainPath.'/doc/foreign/fine/'.$_SESSION['cityid'].'/create/'.$FileName;
        }
    }
    else{
        if (!is_dir(FOREIGN_FINE."/".$_SESSION['cityid'])) {
            mkdir(FOREIGN_FINE."/".$_SESSION['cityid'], 0777);
        }

        $pdf->Output(FOREIGN_FINE."/".$_SESSION['cityid'].'/'.$FileName, "F");
        $_SESSION['Documentation'] = $MainPath.'/doc/foreign/fine/'.$_SESSION['cityid'].'/'.$FileName;
    }

}

$aUpdate = array(
    array('field'=>'Locked','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
    array('field'=>'UserName','selector'=>'value','type'=>'str','value'=>''),
);
$rs->Update('LockedPage',$aUpdate, "Title='".CREATE_LOCKED_PAGE."_{$_SESSION['cityid']}'");
$rs->End_Transaction();