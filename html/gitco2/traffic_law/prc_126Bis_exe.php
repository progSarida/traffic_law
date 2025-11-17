<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
include(CLS."/cls_progressbar.php");

$CurrentDate = DateInDB(CheckValue('ElaborationDate','s'));
$CurrentTime =CheckValue('ElaborationTime','s');
$CurrentYear = substr($CurrentDate,0,4);
$ElaborationType = CheckValue('ElaborationType','n');
$Search_FromNotificationDate = CheckValue('Search_FromNotificationDate','s');
$Search_ToNotificationDate = CheckValue('Search_ToNotificationDate','s');
$n_ControllerId = CheckValue('ControllerId','n');
$Search_FromFineDate            = CheckValue('Search_FromFineDate','s');
$Search_ToFineDate              = CheckValue('Search_ToFineDate','s');
$Search_FromProtocolId          = CheckValue('Search_FromProtocolId','n');
$Search_ToProtocolId            = CheckValue('Search_ToProtocolId','n');
$s_TypePlate                    = CheckValue('TypePlate','s');
$Search_Dispute = CheckValue('Search_Dispute','s');
$Search_Outcome = CheckValue('Search_Outcome','s');

$r_ProcessingNational = $rs->getArrayLine($rs->Select('ProcessingData126BisNational', "CityId='".$_SESSION['cityid']."' AND Disabled=0 AND Automatic=0"));
$r_ProcessingForeign = $rs->getArrayLine($rs->Select('ProcessingData126BisForeign', "CityId='".$_SESSION['cityid']."' AND Disabled=0 AND Automatic=0"));

switch($s_TypePlate){
    case "N":
        $r_ProcessingData = $r_ProcessingNational;
        $str_WhereCountry = " AND CountryId='Z000'";
        $str_ProcessingTable = "National";
        break;
    case "F":
        $r_ProcessingData = $r_ProcessingForeign;
        $str_WhereCountry = " AND CountryId!='Z000'";
        $str_ProcessingTable = "Foreign";
        break;
    default:
        $r_ProcessingData = null;
        $str_WhereCountry = "";
        $str_ProcessingTable = "";
        break;
}


$str_UserId = $_SESSION['username'];

if($Search_FromFineDate != "")      $str_Where .= " AND FineDate>='".DateInDB($Search_FromFineDate)."'";
if($Search_ToFineDate != "")        $str_Where .= " AND FineDate<='".DateInDB($Search_ToFineDate)."'";
if($Search_FromProtocolId>0)        $str_Where .= " AND ProtocolId>=".$Search_FromProtocolId;
if($Search_ToProtocolId>0)          $str_Where .= " AND ProtocolId<=".$Search_ToProtocolId;
if($Search_FromNotificationDate!="")
    $str_Where .= " AND NotificationDate>='".DateInDB($Search_FromNotificationDate)."' ";
if($Search_ToNotificationDate!="")
    $str_Where .= " AND NotificationDate<='".DateInDB($Search_ToNotificationDate)."' ";
if($Search_Year!="")                $str_Where .= " AND ProtocolYear=".$Search_Year;

$CityId = $_SESSION['cityid'];


$rs_Article = $rs->Select('V_Article', "Article=126 AND Paragraph='0' AND Letter='bis' AND CityId='".$CityId."' AND Year=".$CurrentYear);

if(mysqli_num_rows($rs_Article)>0){
    $r_Article = mysqli_fetch_array($rs_Article);


    $ProcessingDate= date("Y-m-d");
    $ProcessingStartTime= date("H:i:s");



    $rs_Reason = $rs->Select('Reason', "ViolationTypeId=".$r_Article['ViolationTypeId']." AND CityId='".$CityId."'");
    $r_Reason = mysqli_fetch_array($rs_Reason);

    $ReasonId = $r_Reason['Id'];

    //$rs_FineProcedure = $rs->Select('V_126BisProcedure',$str_Where.$str_WhereCountry." AND CityId='".$CityId."' AND NotificationDate<='2019-01-31'");
    $rs_FineProcedure = $rs->Select('V_126BisProcedure',$str_Where.$str_WhereCountry." AND CityId='".$CityId."'");
    
    $n_ContFine = 0;

    $n_ContFineOutDate = 0;
    $n_ContFineDispute = 0;
    $str_FineOutDate = "";
    $str_Content = "";

    $totalRows = mysqli_num_rows($rs_FineProcedure);

    $ProgressFileName = CheckValue("ProgressFile", "s");
    $ProgressFile = TMP . "/".$ProgressFileName;
    $progress = new CLS_PROGRESSBAR($totalRows);
    $cont=1;
    $str_Message = "";

    while($r_FineProcedure = mysqli_fetch_array($rs_FineProcedure)){

        if($s_TypePlate=="" || $r_ProcessingData==null){
            if($r_FineProcedure['CountryId']!="Z000")
                $r_ProcessingData = $r_ProcessingForeign;
            else
                $r_ProcessingData = $r_ProcessingNational;
        }

        $rs->Start_Transaction();

        $rs_Fine = $rs->Select('Fine', "Id=".$r_FineProcedure['Id']);
        $r_Fine = mysqli_fetch_array($rs_Fine);
        $str_Output = "";

        $b_FineDispute = false;
        $n_DisputeDay = 0;
        $str_Dispute = "";
        $NotificationDate = $r_FineProcedure['NotificationDate'];
        $CommunicationDate = $r_FineProcedure['CommunicationDate'];


        $rs_FineDispute = $rs->Select('V_FineDispute',"FineId=".$r_FineProcedure['Id']);
        if(mysqli_num_rows($rs_FineDispute)>0){
            $RG = "";
            $dateHearing = "";
            if($Search_Dispute=="without"){
                continue;
            }


            $r_FineDispute = mysqli_fetch_array($rs_FineDispute);


            if($r_FineDispute['Number']!=null)
                $RG.= "RG ".$r_FineDispute['Number'];
            if($r_FineDispute['TypeHearing']!=null)
                $dateHearing = $r_FineDispute['TypeHearing'];
            if($r_FineDispute['DateHearing']!=null){
                if($dateHearing=="")
                    $dateHearing = "UDIENZA";
                $dateHearing.= " ".DateOutDB($r_FineDispute['DateHearing']);
            }
            $addToDispute = "";
            if($RG!="" && $dateHearing!="")
                $addToDispute = " - ".$RG." ".$dateHearing;
            else if($RG!="" || $dateHearing!="")
                $addToDispute = " - ".$RG.$dateHearing;

            //DisputeStatusId presente nella tabella FineDispute viene settato come segue
            // 1 per ricorso in attesa o rinviato
            // 2 per ricorso positivo per il trasgressore
            // 3 per ricorso positivo per Sarida
            if($r_ProcessingData['DisputeCheckType']==0){
                switch ($r_FineDispute['DisputeStatusId']){
                    case 1:
                        $str_Result = "Esito negativo: Ricorso in attesa o rinviato".$addToDispute;
                        $b_FineDispute = true;
                        break;
                    case 2:
                        $str_Dispute = " - Ricorso respinto/inammissibile".$addToDispute;
                        $n_DisputeDay = DateDiff("D", $r_FineDispute['DateFile'], $r_FineDispute['DateMerit'])+1;
                        break;
                    case 3:
                        $str_Result = "Esito negativo: Ricorso accolto".$addToDispute;
                        $b_FineDispute = true;

                        $BisProcedure = 0;
                        $a_FineNotification = array(
                            array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>$BisProcedure,'settype'=>'int'),
                        );
                        $rs->Update('FineNotification',$a_FineNotification,"FineId=".$r_FineProcedure['Id']);

                        break;
                }
            }
            else if($r_ProcessingData['DisputeCheckType']==1){
                //TODO Data provvedimento sospensiva da aggiungere nei ricorsi per controllo
            }

        }
        else{
            if($Search_Dispute=="with"){
                continue;
            }
        }

        if($b_FineDispute){
            if($Search_Outcome=="positive")
                continue;
            $n_ContFineDispute++;
        }
        else if($CommunicationDate==""){

            $n_Day = DateDiff("D", $NotificationDate, $CurrentDate) + 1;
            $covidDays = 0;
            $covidDateStart = "2020-02-23";
            $covidDateStop = "2020-04-15";
            $limitDate = date('Y-m-d', strtotime($NotificationDate. ' + '.($r_ProcessingData['RangeDayMax'] + $n_DisputeDay).' days'));
            if($NotificationDate<=$covidDateStop && $limitDate>=$covidDateStart){
                if($NotificationDate>=$covidDateStart)
                    $startDate = $NotificationDate;
                else
                    $startDate = $covidDateStart;
                if($limitDate<=$covidDateStop)
                    $endDate = $limitDate;
                else
                    $endDate = $covidDateStop;
                $covidDays = (int)DateDiff("D", $startDate, $endDate) + 1;
            }

            $n_CalcDay = $n_Day - $r_ProcessingData['RangeDayMin'];
            $RangeDayMax = $r_ProcessingData['RangeDayMax'] + $n_DisputeDay + $covidDays;

            if($n_CalcDay>$r_ProcessingData['WaitDay'] && $n_CalcDay<=$RangeDayMax){
                if($Search_Outcome=="negative")
                    continue;

                $n_ContFine++;
                $str_ok = "Limite ".($RangeDayMax+$r_ProcessingData['RangeDayMin']);
                if($n_DisputeDay>0 || $covidDays>0)
                    $str_ok.= " ( ".($r_ProcessingData['RangeDayMax']+$r_ProcessingData['RangeDayMin']);
                if($n_DisputeDay>0)
                    $str_ok.= " + ricorso ".$n_DisputeDay;
                if($covidDays>0)
                    $str_ok.= " + covid ".$covidDays;
                if($n_DisputeDay>0 || $covidDays>0)
                    $str_ok.= " )";
                if($_SESSION['cityid']=="U480")
                    $str_extra = "";
                else
                    $str_extra = "\nTrascorsi ".$n_Day." - ".$str_ok;
                $str_Result = "Esito positivo ".$str_Dispute.$str_extra;

                $ControllerId = ($n_ControllerId>0) ? $n_ControllerId : $r_Fine['ControllerId'];

                if($ElaborationType){
                    $StatusTypeId = 14;
                    $FineTypeId = 3;

                    $rs_Protocol = $rs->SelectQuery("SELECT IFNULL(MAX(ProtocolId)+1, 1) ProtocolId, IFNULL(MAX(ProtocolIdAssigned)+1, 1) ProtocolIdAssigned FROM Fine WHERE CityId='" . $r_Fine['CityId'] . "' AND ProtocolYear=" . $CurrentYear);
                    $r_Protocol = mysqli_fetch_array($rs_Protocol);
                    $ProtocolId = ($r_Protocol['ProtocolId']>$r_Protocol['ProtocolIdAssigned']) ? $r_Protocol['ProtocolId'] : $r_Protocol['ProtocolIdAssigned'];

                } else {
                    $StatusTypeId = 10;
                    $FineTypeId = 1;
                    $ProtocolId = 0;
                }


                $a_Fine = array(
                    array('field'=>'Code','selector'=>'value','type'=>'str','value'=>$r_FineProcedure['Id']."BIS/".$r_Fine['ProtocolYear']),
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$r_Fine['CityId']),
                    array('field'=>'StatusTypeId','selector'=>'value','type'=>'int','value'=>$StatusTypeId,'settype'=>'int'),
                    array('field'=>'ProtocolYear','selector'=>'value','type'=>'year','value'=>$CurrentYear,'settype'=>'int'),
                    array('field'=>'FineTypeId','selector'=>'value','type'=>'int','value'=>$FineTypeId,'settype'=>'int'),
                    array('field'=>'ProtocolId','selector'=>'value','type'=>'int','value'=>$ProtocolId,'settype'=>'int'),
                    array('field'=>'FineDate','selector'=>'value','type'=>'str','value'=>$CurrentDate),
                    array('field'=>'FineTime','selector'=>'value','type'=>'str','value'=>$CurrentTime),
                    array('field'=>'ControllerId','selector'=>'value','type'=>'int','value'=>$ControllerId,'settype'=>'int'),
                    array('field'=>'Locality','selector'=>'value','type'=>'str','value'=>$r_Fine['Locality']),
                    array('field'=>'StreetTypeId','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                    array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$r_ProcessingData['Address']),
                    array('field'=>'VehicleTypeId','selector'=>'value','type'=>'int','value'=>$r_Fine['VehicleTypeId'],'settype'=>'int'),
                    array('field'=>'VehiclePlate','selector'=>'value','type'=>'str','value'=>$r_Fine['VehiclePlate']),
                    array('field'=>'VehicleCountry','selector'=>'value','type'=>'str','value'=>$r_Fine['VehicleCountry']),
                    array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$r_Fine['CountryId']),
                    array('field'=>'DepartmentId','selector'=>'value','type'=>'int','value'=>$r_Fine['DepartmentId'],'settype'=>'int'),
                    array('field'=>'VehicleBrand','selector'=>'value','type'=>'str','value'=>$r_Fine['VehicleBrand']),
                    array('field'=>'VehicleModel','selector'=>'value','type'=>'str','value'=>$r_Fine['VehicleModel']),
                    array('field'=>'VehicleColor','selector'=>'value','type'=>'str','value'=>$r_Fine['VehicleColor']),
                    array('field'=>'VehicleMass','selector'=>'value','type'=>'flt','value'=>$r_Fine['VehicleMass'],'settype'=>'flt'),
                    array('field'=>'RegDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                    array('field'=>'RegTime','selector'=>'value','type'=>'str','value'=>date("H:i")),
                    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$str_UserId),
                    array('field'=>'Note','selector'=>'value','type'=>'str','value'=>'Creazione automatica 126 BIS'),
                    array('field'=>'PreviousId','selector'=>'value','type'=>'int','value'=>$r_FineProcedure['Id'],'settype'=>'int'),
                );
                $FineId = $rs->Insert('Fine',$a_Fine);

                $str_Output.= " ID ".$FineId." CRONO ".$ProtocolId."/".$CurrentYear;
                $str_Output.= " - REF. ".$r_FineProcedure['Id']."BIS/".$r_Fine['ProtocolYear']." - ";

                $a_FineArticle = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'ArticleId','selector'=>'value','type'=>'int','value'=>$r_Article['Id'],'settype'=>'int'),
                    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$r_Fine['CityId']),
                    array('field'=>'ViolationTypeId','selector'=>'value','type'=>'int','value'=>$r_Article['ViolationTypeId'],'settype'=>'int'),
                    array('field'=>'ReasonId','selector'=>'value','type'=>'int','value'=>$ReasonId,'settype'=>'int'),
                    array('field'=>'Fee','selector'=>'value','type'=>'flt','value'=>$r_Article['Fee'],'settype'=>'flt'),
                    array('field'=>'MaxFee','selector'=>'value','type'=>'flt','value'=>$r_Article['MaxFee'],'settype'=>'flt'),

                );
                $rs->Insert('FineArticle',$a_FineArticle);

                $rs_Trespasser = $rs->Select('FineTrespasser', "FineId=".$r_FineProcedure['Id']." AND (TrespasserTypeId=1 OR TrespasserTypeId=11)");
                $r_Trespasser = mysqli_fetch_array($rs_Trespasser);

                $TrespasserTypeId = 1;

                $a_FineTrespasser = array(
                    array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Trespasser['TrespasserId'],'settype'=>'int'),
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                    array('field'=>'TrespasserTypeId','selector'=>'value','type'=>'int','value'=>$TrespasserTypeId,'settype'=>'int'),
                    array('field'=>'Note','selector'=>'value','type'=>'str','value'=>'Contravventore già esistente 126 BIS'),
                );

                $rs->Insert('FineTrespasser',$a_FineTrespasser);


                $BisProcedure = 0;
                $a_FineNotification = array(
                    array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>$BisProcedure,'settype'=>'int'),
                );

                $rs->Update('FineNotification',$a_FineNotification,"FineId=".$r_FineProcedure['Id']);

            } else{

                if($Search_Outcome=="positive")
                    continue;

                if($n_CalcDay>$RangeDayMax){

                    $n_ContFineOutDate++;
                    $str_max = "Limite ".($RangeDayMax+$r_ProcessingData['RangeDayMin']);
                    if($n_DisputeDay>0 || $covidDays>0)
                        $str_max.= " ( ".($r_ProcessingData['RangeDayMax']+$r_ProcessingData['RangeDayMin']);
                    if($n_DisputeDay>0)
                        $str_max.= " + ricorso ".$n_DisputeDay;
                    if($covidDays>0)
                        $str_max.= " + covid ".$covidDays;
                    if($n_DisputeDay>0 || $covidDays>0)
                        $str_max.= " )";
                    if($_SESSION['cityid']=="U480")
                        $str_extra = "";
                    else
                        $str_extra = "\nTrascorsi ".$n_Day." - ".$str_max;
                    $str_Result = "Esito negativo: Scadenza termini in giorni".$str_extra;

                    $str_FineOutDate .= $r_FineProcedure['ProtocolId'].'/'.$r_FineProcedure['ProtocolYear']. " ";

                    $BisProcedure = 0;
                    $a_FineNotification = array(
                        array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>$BisProcedure,'settype'=>'int'),
                    );

                    $rs->Update('FineNotification',$a_FineNotification,"FineId=".$r_FineProcedure['Id']);
                }
                else{
                    $str_min = "Limite Min ".($r_ProcessingData['RangeDayMin']+$r_ProcessingData['WaitDay']);
                    if($r_ProcessingData['WaitDay']>0)
                        $str_min.= " ( Minimo ".$r_ProcessingData['RangeDayMin']. " + Attesa ".$r_ProcessingData['WaitDay'].")";
                    if($n_Day<0)
                        $n_Day = 0;
                    if($_SESSION['cityid']=="U480")
                        $str_extra = "";
                    else
                        $str_extra = "\nTrascorsi ".$n_Day." - ".$str_min;
                    $str_Result = "Esito negativo: Anticipo termini in giorni".$str_extra;
                }

            }

        }else{
            if($Search_Outcome=="positive")
                continue;
            $str_Result = "Esito negativo: Comunicazione presente";
            $BisProcedure = 0;
            $a_FineNotification = array(
                array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>$BisProcedure,'settype'=>'int'),
            );

            $rs->Update('FineNotification',$a_FineNotification,"FineId=".$r_FineProcedure['Id']);

        }


        $str_Output.= "VERBALE ORIGINALE ".$r_FineProcedure['ProtocolId']."/".$r_FineProcedure['ProtocolYear']." - ".$str_Result;
        $str_Message .= '
                        <div class="col-sm-12">
                            ' . $str_Output . '
                            <div class="clean_row HSpace4"></div>
                        </div>
                        ';

        $progress->writeJSON($cont, $ProgressFile);
        $cont++;



    }
    $ProcessingEndTime= date("H:i:s");



    if($n_ContFine>0){


        $rs_UserMail = $rs->SelectQuery("SELECT DISTINCT UserId,CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserLevel>=3 AND CityId='".$CityId."'");
        while($r_UserMail = mysqli_fetch_array($rs_UserMail)){

            $str_Content = $r_UserMail['CityTitle'].": sono stati elaborati n. ".$n_ContFine." verbali";

            if($n_ContFineOutDate>0){
                $str_Content .= "<br />VERBALI OLTRE TEMPO MASSIMO NON ELABORATI: ".$str_FineOutDate." <br />";
            }
            if($n_ContFineDispute>0){
                $str_Content .= "<br />VERBALI IMPUGNATI IN ATTESA: ".$n_ContFineDispute." <br />";
            }



            $a_Mail = array(
                array('field'=>'SendDate','selector'=>'value','type'=>'date','value'=>date("Y-m-d")),
                array('field'=>'SendTime','selector'=>'value','type'=>'str','value'=>date("H:i:s")),
                array('field'=>'Object','selector'=>'value','type'=>'str','value'=>"Elaborazione 126 Bis"),
                array('field'=>'Content','selector'=>'value','type'=>'str','value'=>$str_Content),
                array('field'=>'UserId','selector'=>'value','type'=>'int','value'=>$r_UserMail['UserId'],'settype'=>'int'),
                array('field'=>'Sender','selector'=>'value','type'=>'str','value'=>"Server"),
            );

            $rs->Insert('Mail',$a_Mail);

        }



    }

    $rs->End_Transaction();
}
else{
    header("location: prc_126Bis.php?".$str_get."&error=Articolo 126Bis non trovato per l'anno ".$CurrentYear);
    die;
}


echo json_encode(
    array(
        "Esito" => 1,
        "Messaggio" => trim($str_Message),
    )
);