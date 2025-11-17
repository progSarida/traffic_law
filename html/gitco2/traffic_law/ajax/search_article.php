<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");
if($_POST) {
    $NonExist = null;
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
    $reasonId= null;
    $artcomunali = null;

    $RuleTypeId = CheckValue('RuleTypeId','n');
    $ArticleCounter = CheckValue('ArticleCounter','n');
    //TODO BUG 3239 rimosso in quanto sviluppo annullato e la rimozione della colonna da V_Article comporta errore
//     $VehicleTypeId = CheckValue('VehicleTypeId','n');
//     $ConsiderVehicleTypeId = CheckValue('ConsiderVehicleTypeId','n');
    
    //PER mgmt_report_upd
    $SavedFineId = CheckValue('FineId','n');


    $allReason = $rs->Select('Reason',"CityId = '".$_SESSION['cityid']."' And Disabled=0");

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

		//Se all'articolo è associato un tipo di veicolo, viene aggiunto il filtro alla query
		//$ConsiderVehicleTypeId è un input nascosto nell'html che serve a dire alla maschera di continuare a
		//prelevare l'articolo con il giusto criterio quando viene aggiornato
		//TODO BUG 3239 rimosso in quanto sviluppo annullato e la rimozione della colonna da V_Article comporta errore
// 		if($VehicleTypeId > 0 && $ConsiderVehicleTypeId > 0){
// 		    $Where .= " AND VehicleTypeId=$VehicleTypeId";
// 		} else $Where .= " AND VehicleTypeId IS NULL";

		$finds = $rs->Select('V_Article', $Where);
		$FindNumber = mysqli_num_rows($finds);

		if ($FindNumber == 0) {
			$Description = 'Nessun articolo trovato per quest\'anno e questo comune';
            $ReasonId = 0;
            $ReasonDescription = "";
            $NonExist=1;
		} else {
			$Result = 1;
			$find = mysqli_fetch_array($finds);

			$Description = $find['ArticleDescription'.LAN];
			$ArticleId= $find['Id'];

//            $checkArticleTariff = $rs->SelectQuery("SELECT PresentationDocument FROM ArticleTariff WHERE ArticleId = $ArticleId and ArticleTariff.Year = ".$_SESSION['year']."");
//            $presentationDocument = mysqli_fetch_array($checkArticleTariff)['PresentationDocument'];

			$Id1 = $find['Article'];
			$Id2 = $find['Paragraph'];
			$Id3 = $find['Letter'];
            $artcomunali = $find['ArtComune'];

			$ViolationTypeId = $find['ViolationTypeId'];
			$ViolationTitle = $find['ViolationTitle'];

			$Fee = $find['Fee'];
			$MaxFee = $find['MaxFee'];
			$PrefectureFixed = $find['PrefectureFixed'];

            $FineTime = $_POST['FineTime'];
			$aTime = explode(":",$FineTime);

			if($aTime[0]<FINE_HOUR_START_DAY ||  ($aTime[0]>FINE_HOUR_END_DAY) || ($aTime[0]==FINE_HOUR_END_DAY && $aTime[1]!="00")){
				//FINE_MINUTE_START_DAY
				//FINE_MINUTE_END_DAY
				$Fee = $Fee + round($Fee/FINE_NIGHT,2);
				$MaxFee = $MaxFee + round($MaxFee/FINE_NIGHT,2);
				
			}


			$rs_ArticleTariff = $rs->SelectQuery('SELECT
                AdditionalSanction.TitleIta,
                AdditionalSanction.Progressive,
                ArticleTariff.AdditionalMass,
                ArticleTariff.UseAdditionalSanction
                FROM ArticleTariff
                LEFT JOIN AdditionalSanction on ArticleTariff.AdditionalSanctionId = AdditionalSanction.Id
                WHERE ArticleTariff.ArticleId='.$find['Id']." AND ArticleTariff.Year=".$_SESSION['year']);
			$r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);
			
			$SavedAdditionalSanction = null;
			$AdditionalSanctionSelect = $r_ArticleTariff['TitleIta'];
			$AdditionalSanctionProgressive = $r_ArticleTariff['Progressive'];
			$UseAdditionalSanction = $r_ArticleTariff['UseAdditionalSanction'];
			
			//PER mgmt_report_upd: cerco, se presente, la sanzione addizionale già inserita da sostituire a quella precompilata
			if($SavedFineId > 0){
			    $rs_FineOwner = $rs->Select('FineOwner', "FineId=$SavedFineId");
			    if(mysqli_num_rows($rs_FineOwner) > 0){
			        $r_FineOwner = $rs->getArrayLine($rs_FineOwner);
			        $SavedAdditionalSanction = $r_FineOwner['AdditionalDescriptionIta'];
			    }
			}

			//FINE_MASS


            //Omessa Contestazione
            $rs_Reason = $rs->Select('Reason', "ReasonTypeId=1 AND ViolationTypeId=".$find['ViolationTypeId']." AND CityId='".$_SESSION['cityid']."' AND Fixed=".$Fixed." AND Disabled=0");
            if(mysqli_num_rows($rs_Reason)==0){
                $ReasonDescription = '
                <option value="">
                Nessuna mancata contestazione trovata per quest\'articolo
                </option>
                ';

            } else {
                $r_Reason = mysqli_fetch_array($rs_Reason);
                $reasonId = $r_Reason['Id'];
                $ReasonDescription = '
                    <option value="'. $r_Reason['Id'] .'">
                    '.$r_Reason['Progressive'] . ' - ' . $r_Reason['TitleIta'] .'
                    </option>
                ';
                while ($all_reason = mysqli_fetch_array($allReason)){
                    if ($all_reason['Id']!= $r_Reason['Id']){
                        $ReasonDescription .= '
                            <option value="'. $all_reason['Id'] .'">
                            '.$all_reason['Progressive'] . " - " . $all_reason['TitleIta'] .'
                            </option>
                        ';
                    }
                }
            }





		}

        $getpunti = $rs->SelectQuery("select LicensePoint,YoungLicensePoint from ArticleTariff where ArticleId = '".$ArticleId."' and Year ='".$_SESSION['year']."'");
        $get_punti = mysqli_fetch_array($getpunti);
        $yangpunti = $get_punti['YoungLicensePoint'];
        $oldpunti = $get_punti['LicensePoint'];


		
		
		
		
		echo json_encode(
			array(
				"Result" => $Result,
				"Description" => $Description,
				"ArticleId" => $ArticleId,
				"ViolationTypeId" => StringOutDB($ViolationTypeId),
				"ViolationTitle" => StringOutDB($ViolationTitle),
				"Speed" => $Speed,
				"Id1" => $Id1,
				"Id2" => $Id2,
				"Id3" => $Id3,
				"Fee" => $Fee,
                "ArtComunali"=>$artcomunali,
				"MaxFee" => $MaxFee,
			    "AdditionalSanctionSelect" => $AdditionalSanctionSelect,
			    "AdditionalSanctionProgressive" => $AdditionalSanctionProgressive,
			    "UseAdditionalSanction" => $UseAdditionalSanction,
			    "SavedAdditionalSanction" => $SavedAdditionalSanction,
                "ReasonId" => $ReasonId,
                "ReasonDescription" => StringOutDB($ReasonDescription),
                'YoungLicensePoint' => $yangpunti,
                'LicensePoint' => $oldpunti,
                'NonExist'=>$NonExist,
                'reasonId'=>$reasonId,
			    'PrefectureFixed'=>$PrefectureFixed,
//                'DayNumber'=>$presentationDocument
			)
		);



	} else {

		$id1 = CheckValue('id1','n');
		$id2 = CheckValue('id2','s');
		$id3 = CheckValue('id3','s');
        $search = CheckValue('search','s');

        $str_Id3 = ($id3=="0") ? "(Letter='".$id3."' OR Letter='')" : "Letter='".$id3."'";

        if ($search !=""){
            $Where = "RuleTypeId=". $RuleTypeId ." AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $_SESSION['year'] . " AND ArtComune='$search'";
        }else{
            $Where = "RuleTypeId=". $RuleTypeId ." AND CityId='" . $_SESSION['cityid'] . "' AND Year=" . $_SESSION['year'] . " AND Article='$id1' AND Paragraph='$id2' AND ". $str_Id3;
        }
        
        //Se all'articolo è associato un tipo di veicolo, viene aggiunto il filtro alla query
        //$ConsiderVehicleTypeId è un input nascosto nell'html che serve a dire alla maschera di continuare a
        //prelevare l'articolo con il giusto criterio quando viene aggiornato
        //TODO BUG 3239 rimosso in quanto sviluppo annullato e la rimozione della colonna da V_Article comporta errore
//         if($VehicleTypeId > 0 && $ConsiderVehicleTypeId > 0){
//             $Where .= " AND VehicleTypeId=$VehicleTypeId";
//         } else $Where .= " AND VehicleTypeId IS NULL";

        $finds = $rs->Select('V_Article', $Where);
		$FindNumber = mysqli_num_rows($finds);


		if ($FindNumber == 0) {
			$Description = 'Nessun articolo trovato per quest\'anno e questo comune';
            $ReasonId = 0;
            $ReasonDescription = "";
            $NonExist = 1;
		} else {
			$Result = 1;
			$find = mysqli_fetch_array($finds);

			$Description = $find['ArticleDescription'.LAN];
			$ArticleId= $find['Id'];

//			$checkArticleTariff = $rs->SelectQuery("SELECT PresentationDocument FROM ArticleTariff WHERE ArticleId = $ArticleId and ArticleTariff.Year = ".$_SESSION['year']."");
//			$presentationDocument = mysqli_fetch_array($checkArticleTariff)['PresentationDocument'];

			$ViolationTypeId = $find['ViolationTypeId'];
			$ViolationTitle = $find['ViolationTitle'];
            $PresentationDocument = $find['PresentationDocument'];
            $Id1 = $find['Article'];
            $Id2 = $find['Paragraph'];
            $Id3 = $find['Letter'];
            $artcomunali = $find['ArtComune'];
			$Fee = $find['Fee'];
			$MaxFee = $find['MaxFee'];
			$PrefectureFixed = $find['PrefectureFixed'];

			$rs_ArticleTariff = $rs->SelectQuery('SELECT 
                AdditionalSanction.TitleIta, 
                AdditionalSanction.Progressive, 
                ArticleTariff.AdditionalMass, 
                ArticleTariff.UseAdditionalSanction 
                FROM ArticleTariff 
                LEFT JOIN AdditionalSanction on ArticleTariff.AdditionalSanctionId = AdditionalSanction.Id 
                WHERE ArticleTariff.ArticleId='.$find['Id']." AND ArticleTariff.Year=".$_SESSION['year']);
			$r_ArticleTariff = mysqli_fetch_array($rs_ArticleTariff);
			
			$SavedAdditionalSanction = null;
			$AdditionalSanctionSelect = $r_ArticleTariff['TitleIta'];
			$AdditionalSanctionProgressive = $r_ArticleTariff['Progressive'];
			$UseAdditionalSanction = $r_ArticleTariff['UseAdditionalSanction'];
			
			//PER mgmt_report_upd: cerco, se presente, la sanzione addizionale già inserita da sostituire a quella precompilata
			if($SavedFineId > 0){
			    $rs_FineOwner = $rs->Select('FineOwner', "FineId=$SavedFineId");
			    if(mysqli_num_rows($rs_FineOwner) > 0){
			        $r_FineOwner = $rs->getArrayLine($rs_FineOwner);
			        $SavedAdditionalSanction = $r_FineOwner['AdditionalDescriptionIta'];
			    }
			}
			
			
			$addMass = $r_ArticleTariff['AdditionalMass'];

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


            //Omessa Contestazione
            $rs_Reason = $rs->Select('Reason', "ReasonTypeId=1".$str_ReasonWhere." AND CityId='".$_SESSION['cityid']."' AND Disabled=0");

            if(mysqli_num_rows($rs_Reason)==0){

                $ReasonDescription = '
                <option value="">
                Nessuna mancata contestazione trovata per quest\'articolo
                </option>
                ';

            } else {
                if(mysqli_num_rows($rs_Reason) > 0) {
                    $r_Reason = mysqli_fetch_array($rs_Reason);
                    $reasonId = $r_Reason['Id'];
                    $ReasonDescription = '
                         <option value="' . $r_Reason['Id'] . '">
                            ' . $r_Reason['Progressive'] . " - " . $r_Reason['TitleIta'] . '
                         </option>
                    ';

                    while ($all_reason = mysqli_fetch_array($allReason)) {
                        if ($all_reason['Id'] != $r_Reason['Id']) {
                            $ReasonDescription .= '
                            <option value="' . $all_reason['Id'] . '">
                            ' . $all_reason['Progressive'] . " - " . $all_reason['TitleIta'] . '
                            </option>
                        ';
                        }
                    }
                }
            }

		}

        $getpunti = $rs->SelectQuery("select LicensePoint,YoungLicensePoint from ArticleTariff where ArticleId = '".$ArticleId."' and Year ='".$_SESSION['year']."'");
        $get_punti = mysqli_fetch_array($getpunti);
        $yangpunti = $get_punti['YoungLicensePoint'];
        $oldpunti = $get_punti['LicensePoint'];
        if ($FindNumber == 0){
            echo json_encode(
                array(
                    "Result" => $Result,
                    "Description" => $Description,
                    "NonExist"=>$NonExist
                )
            );
        }else{
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
                    "ArtComunali"=>$artcomunali,
                    "MaxFee" => $MaxFee,
                    "ReasonDescription" => $ReasonDescription,
                    "PresentationDocument" => $PresentationDocument,
                    "AddMass" => $addMass,
                    "AdditionalSanctionSelect" => $AdditionalSanctionSelect,
                    "AdditionalSanctionProgressive" => $AdditionalSanctionProgressive,
                    "UseAdditionalSanction" => $UseAdditionalSanction,
                    "SavedAdditionalSanction" => $SavedAdditionalSanction,
                    'YoungLicensePoint' => $yangpunti,
                    'LicensePoint' => $oldpunti,
                    'NonExist'=>$NonExist,
                    'reasonId'=>$reasonId,
                    'PrefectureFixed'=>$PrefectureFixed,
//                    'DayNumber'=>$presentationDocument
                )
            );
        }



	}

}









