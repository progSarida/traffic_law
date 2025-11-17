<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
if($_POST) {

    $Result = 0;

    $Tolerance = 0;
    $Description = "";
    $ArticleId = 0;
    $ViolationTypeId = 0;
    $ViolationTitle = "";
    $Fee = 0;
    $MaxFee = 0;
    $Id1 = "";
    $Id2 = "";
    $Id3 = "";
    $ReasonId = 0;
    $ReasonDescription = "";
    $PresentationDocument = 0;


    $RuleTypeId = CheckValue('RuleTypeId','n');
    $ArticleCounter = CheckValue('ArticleCounter','n');
    $NotificationType = CheckValue('NotificationType','n');



    if(isset($_POST['SpeedLimit'])){

        $DetectorId = $_POST['DetectorId'];
        $SpeedLimit = $_POST['SpeedLimit'];
        $SpeedControl = $_POST['SpeedControl'];
        $FineTime = $_POST['FineTime'];

        $detectors = $rs->Select('Detector', "Id=".$DetectorId);
        $detector = mysqli_fetch_array($detectors);

        $chkTolerance = $detector['Tolerance'];
        $chkTolerance = ($chkTolerance>FINE_TOLERANCE) ? $chkTolerance : FINE_TOLERANCE;

        $TolerancePerc = round($SpeedControl*FINE_TOLERANCE_PERC/100);
        $Tolerance = ($TolerancePerc<$chkTolerance) ? $chkTolerance : $TolerancePerc;

        $Fixed = $detector['Fixed'];

        $Speed = $SpeedControl - $Tolerance;
        $SpeedExcess = $Speed - $SpeedLimit;

        $Where = "RuleTypeId=". $RuleTypeId ." AND Disabled=0 AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $_SESSION['year'];


        if($SpeedExcess<=10){
            $Where .= " AND Article=142 AND Paragraph=7";
        }elseif($SpeedExcess<=40){
            $Where .= " AND Article=142 AND Paragraph=8";
        }elseif($SpeedExcess<=60){
            $Where .= " AND Article=142 AND Paragraph=9 AND Letter!='bis'";
        }else{
            $Where .= " AND Article=142 AND Paragraph=9 AND Letter='bis'";
        }


        $finds = $rs->Select('V_Article', $Where);
        $FindNumber = mysqli_num_rows($finds);

        if ($FindNumber == 0) {
            $Description = 'Nessun articolo trovato per quest\'anno e questo comune';
            $ReasonId = 0;
            $ReasonDescription = "";
        } else {
            $Result = 1;
            $find = mysqli_fetch_array($finds);

            $Description = StringOutDB($find['ArticleDescription'.LAN]);
            $ArticleId= $find['Id'];
            $Id1 = $find['Article'];
            $Id2 = $find['Paragraph'];
            $Id3 = $find['Letter'];


            $ViolationTypeId = $find['ViolationTypeId'];
            $ViolationTitle = $find['ViolationTitle'];

            $Fee = $find['Fee'];
            $MaxFee = $find['MaxFee'];

            $FineTime = $_POST['FineTime'];
            $aTime = explode(":",$FineTime);

            if($aTime[0]<FINE_HOUR_START_DAY ||  ($aTime[0]>FINE_HOUR_END_DAY) || ($aTime[0]==FINE_HOUR_END_DAY && $aTime[1]!="00")){
                //FINE_MINUTE_START_DAY
                //FINE_MINUTE_END_DAY
                $Fee = $Fee + round($Fee/FINE_NIGHT,2);
                $MaxFee = $MaxFee + round($MaxFee/FINE_NIGHT,2);

            }






            //FINE_MASS


            if($NotificationType==2){
                $ReasonDescription = '
                    <option value="100">
                    Notificato su strada
                    </option>
                    ';
            }else{
                $rs_Reason = $rs->Select('Reason', "ReasonTypeId=1 AND ViolationTypeId=".$find['ViolationTypeId']." AND CityId='".$_SESSION['cityid']."' AND Fixed=".$Fixed);
                if(mysqli_num_rows($rs_Reason)==0){
                    $ReasonDescription = 'Nessuna mancata contestazione trovata per quest\'articolo';

                } else {
                    $r_Reason = mysqli_fetch_array($rs_Reason);
                    $ReasonDescription = '
                    <option value="'. $r_Reason['Id'] .'">
                    '.$r_Reason['Code'] . ' ' . $r_Reason['TitleIta'] .'
                    </option>
                    ';
                }
            }




        }





        echo json_encode(
            array(
                "Result" => $Result,
                "Description" => $Description,
                "ArticleId" => $ArticleId,
                "ViolationTypeId" => $ViolationTypeId,
                "ViolationTitle" => $ViolationTitle,
                "Speed" => $Speed,
                "Id1" => $Id1,
                "Id2" => $Id2,
                "Id3" => $Id3,
                "Fee" => $Fee,
                "MaxFee" => $MaxFee,
                "ReasonId" => $ReasonId,
                "ReasonDescription" => $ReasonDescription
            )
        );



    } else {
        $search = CheckValue('search','s');


        $finds = $rs->Select('V_Article', "RuleTypeId=". $RuleTypeId ." AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $_SESSION['year'] . " AND ArtComune='$search'");
        $FindNumber = mysqli_num_rows($finds);

        if ($FindNumber == 0) {
            $Description = 'Nessun articolo trovato per quest\'anno e questo comune';
            $ReasonId = 0;
            $ReasonDescription = "";
        } else {
            $Result = 1;
            $find = mysqli_fetch_array($finds);

            $Description = $find['ArticleDescription'.LAN];
            $ArticleId= $find['Id'];
            $ViolationTypeId = $find['ViolationTypeId'];
            $ViolationTitle = $find['ViolationTitle'];
            $PresentationDocument = $find['PresentationDocument'];

            $Id1 = $find['Article'];
            $Id2 = $find['Paragraph'];
            $Id3 = $find['Letter'];
            $Fee = $find['Fee'];
            $MaxFee = $find['MaxFee'];

            $result = $rs->SelectQuery('SELECT AdditionalSanction.TitleIta, ArticleTariff.AdditionalMass FROM ArticleTariff LEFT JOIN AdditionalSanction on ArticleTariff.AdditionalSanctionId = AdditionalSanction.Id WHERE ArticleTariff.ArticleId='.$find['Id']." AND ArticleTariff.Year=".$_SESSION['year']);
            $result = mysqli_fetch_array($result);
            $AdditionalSanctionSelect = "<span>".$result['TitleIta']."</span>";
            $addMass = $result['AdditionalMass'];

            if($find['AdditionalNight']){
                $FineTime = $_POST['FineTime'];
                $aTime = explode(":",$FineTime);

                if($aTime[0]<FINE_HOUR_START_DAY ||  ($aTime[0]>FINE_HOUR_END_DAY) || ($aTime[0]==FINE_HOUR_END_DAY && $aTime[1]!="00")){
                    //FINE_MINUTE_START_DAY
                    //FINE_MINUTE_END_DAY
                    $Fee = $Fee + round($Fee/FINE_NIGHT,2);
                    $MaxFee = $MaxFee + round($MaxFee/FINE_NIGHT,2);

                }
            }


            $str_ReasonWhere = "";


            switch ($find['ViolationTypeId'])
            {
                case 4:
                case 6:
                    $str_ReasonWhere = " AND (ViolationTypeId=".$find['ViolationTypeId']. " OR ViolationTypeId=1)";
                    break;

                default:
                    $str_ReasonWhere = " AND ViolationTypeId=".$find['ViolationTypeId'];
            }




            if($NotificationType==2){
                $ReasonDescription = '
                    <option value="100">
                    Notificato su strada
                    </option>
                    ';
            }else{
                $rs_Reason = $rs->Select('Reason', "ReasonTypeId=1".$str_ReasonWhere." AND CityId='".$_SESSION['cityid']."'");
                if(mysqli_num_rows($rs_Reason)==0){
                    $ReasonDescription = '
                    <option value="">
                    Nessuna mancata contestazione trovata per quest\'articolo
                    </option>
                    ';

                } else {
                    if(mysqli_num_rows($rs_Reason)==1){
                        $r_Reason = mysqli_fetch_array($rs_Reason);

                        $ReasonDescription = '
                    <option value="'. $r_Reason['Id'] .'">
                    '.$r_Reason['Code'] . " " . $r_Reason['TitleIta'] .'
                    </option>
                    ';
                        ;
                    } else {
                        $ReasonDescription = '
                        <option value=""></option>
                    ';
                        while($r_Reason = mysqli_fetch_array($rs_Reason)){
                            $ReasonDescription .= '
                            <option value="'. $r_Reason['Id'] .'">
                            '.$r_Reason['Code'] . " " . $r_Reason['TitleIta'] .'
                            </option>
                            ';
                        }
                    }
                }
            }
        }

        echo json_encode(
            array(
                "Result" => $Result,
                "Description" => $Description,
                "ArticleId" => $ArticleId,
                "ViolationTypeId" => $ViolationTypeId,
                "ViolationTitle" => $ViolationTitle,
                "Fee" => $Fee,
                "Id1" => $Id1,
                "Id2" => $Id2,
                "Id3" => $Id3,
                "MaxFee" => $MaxFee,
                "ReasonDescription" => $ReasonDescription,
                "PresentationDocument" => $PresentationDocument,
                "AddMass" => $addMass,
                "AdditionalSanctionSelect" => $AdditionalSanctionSelect
            )
        );


    }

}









