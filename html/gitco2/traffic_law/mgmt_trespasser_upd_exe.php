<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

// echo "<pre>";
// print_r($_POST);
// echo "</pre>";

// DIE;

$a_LegalFormIndividual = unserialize(LEGALFORM_INDIVIDUALCOMPANY);

$Parameters = "";
$n_Params = 0;

foreach ($_GET as $key => $value){
    $Parameters .= ($n_Params == 0 ? "?" : "&").$key."=".$value;
    ++$n_Params;
}

$BackPage = CheckValue('BackPage','s');

$Overwrite = CheckValue('Overwrite','s');

$Typology = CheckValue('Typology','s');
$Genre = ($Typology == "D") ? "D" : CheckValue('Genre','s');
$DocumentCountryId = CheckValue('LicenseCountryId','s');
$CountryId = CheckValue('TrespasserCountryId','s');
$BornCountry = strtoupper(CheckValue('BornCountry','s'));
$DocumentCountryId2 = strtoupper(CheckValue('DocumentCountryId2','s'));
$TrespasserId = CheckValue('TrespasserId','n');
$DeathDate = CheckValue('DeathDate','s');
$LanguageId = CheckValue('LanguageId','n');

$StreetNumber = CheckValue('StreetNumber','s');
$Ladder = CheckValue('Ladder','s');
$Indoor = CheckValue('Indoor','s');
$Plan = CheckValue('Plan','s');
$ZIP = CheckValue('ZIP','s');
$PEC = CheckValue('PEC','s');

$ZoneId = CheckValue('ZoneId','n');
$Address = strtoupper(CheckValue('AddressT','s'));

$CompanyName = strtoupper(CheckValue('CompanyName','s'));

$CompanyLegalFormId = CheckValue('CompanyLegalFormId','n');
$PersonLegalFormId = CheckValue('PersonLegalFormId','n');
$LegalFormId = ($Typology == "D") ? $CompanyLegalFormId : $PersonLegalFormId;

$TaxCode = strtoupper(CheckValue('TaxCode','s'));
$ForcedTaxCode = strtoupper(CheckValue('ForcedTaxCode','s'));
$VatCode = strtoupper(CheckValue('VatCode','s'));
$CompanyTaxCode = strtoupper(CheckValue('CompanyTaxCode','s'));
$Surname = strtoupper(CheckValue('Surname','s'));
$Name = strtoupper(CheckValue('Name','s'));

$DocumentNumber = strtoupper(CheckValue('DocumentNumber','s'));

$CurrentDate=date("Y-m-d");

//"Filter" è un parametro usato da diverse pagine per capire che è stata effettuata la ricerca
$Filter = CheckValue('Filter', 'n');

$rs->Start_Transaction();

//Città residenza
$CityId = strtoupper(CheckValue('CitySelect','s'));

if($CountryId=="Z000" && $CityId!=""){
    
    $rs_City = $rs->SelectQuery("
    SELECT C.Title CityTitle, P.ShortTitle ProvinceTitle
    FROM ". MAIN_DB.".City C JOIN ". MAIN_DB.".Province P ON C.ProvinceId=P.Id
    WHERE C.Id='".$CityId."'
    ");
    $r_City = mysqli_fetch_array($rs_City);
    
    $City = strtoupper($r_City['CityTitle']);
    $Province = $r_City['ProvinceTitle'];
    $LandId = null;
    
} else if ($CountryId=="Z102" || $CountryId=="Z112" ){
    
    $CityId = CheckValue('ForeignCitySelect','n');
    if($CityId > 0){
        $rs_City = $rs->Select('ForeignCity', 'Id='.$CityId);
        $r_City = mysqli_fetch_array($rs_City);
        $City = strtoupper($r_City['Title']);
    } else {
        $City = null;
    }
    
    $Province = "";
    $CityId = null;
    $LandId = CheckValue('LandId','s');
    
} else {
    
    $City = strtoupper(CheckValue('CityInput','s'));
    $Province = "";
    $CityId = "";
    $LandId = null;
    
}
//

//Città nascita
$BornCityId = strtoupper(CheckValue('BornCitySelect','s'));

if($BornCountry=="Z000" && $BornCityId!=""){
    
    $rs_City = $rs->SelectQuery("
    SELECT C.Title CityTitle, P.ShortTitle ProvinceTitle
    FROM ". MAIN_DB.".City C JOIN ". MAIN_DB.".Province P ON C.ProvinceId=P.Id
    WHERE C.Id='".$BornCityId."'
    ");
    $r_City = mysqli_fetch_array($rs_City);
    $BornCity = strtoupper($r_City['CityTitle']);
    
} else if ($BornCountry=="Z102" || $BornCountry=="Z112" ){
    
    $BornCityId = CheckValue('ForeignBornCitySelect','n');
    if($BornCityId > 0){
        $rs_City = $rs->Select('ForeignCity', 'Id='.$BornCityId);
        $r_City = mysqli_fetch_array($rs_City);
        $BornCity = strtoupper($r_City['Title']);
    }
    
} else {
    $BornCity = strtoupper(CheckValue('BornCityInput','s'));
}
//

//Città documento identità
$DocumentOfficeId = strtoupper(CheckValue('DocumentOfficeSelect','s'));

if($DocumentCountryId2=="Z000" && $DocumentOfficeId!=""){
    
    $rs_City = $rs->SelectQuery("
    SELECT C.Title CityTitle, P.ShortTitle ProvinceTitle
    FROM ". MAIN_DB.".City C JOIN ". MAIN_DB.".Province P ON C.ProvinceId=P.Id
    WHERE C.Id='".$DocumentOfficeId."'
    ");
    $r_City = mysqli_fetch_array($rs_City);
    $DocumentOffice = strtoupper($r_City['CityTitle']);
    
} else if ($DocumentCountryId2=="Z102" || $DocumentCountryId2=="Z112" ){
    $DocumentOfficeId = strtoupper(CheckValue('ForeignDocumentOfficeSelect','s'));
    
    $rs_City = $rs->Select('ForeignCity', 'Id='.$DocumentOfficeId);
    $r_City = mysqli_fetch_array($rs_City);
    $DocumentOffice = strtoupper($r_City['Title']);
    $DocumentOfficeId = null;
    
} else {
    $DocumentOffice = strtoupper(CheckValue('DocumentOfficeInput','s'));
}
//

/*---------------------------------------------------------------------
 GESTIONE STORICO
 *-------------------------------------------------------------------*/

//TRAGSRESSORE
$rs_Trespasser = $rs->Select('Trespasser',"Id=".$TrespasserId);
$r_Trespasser = mysqli_fetch_array($rs_Trespasser);

$AddressFH = isset($r_Trespasser['Address']) ? $r_Trespasser['Address'] : '';
$CityFH = isset($r_Trespasser['City']) ? $r_Trespasser['City'] : '';
$CountryIdFH = isset($r_Trespasser['CountryId']) ? $r_Trespasser['CountryId'] : '';
$BornPlaceFH = isset($r_Trespasser['BornPlace']) ? $r_Trespasser['BornPlace'] : '';
$ZoneIdFH = isset($r_Trespasser['ZoneId']) ? $r_Trespasser['ZoneId'] : -1;
$LanguageIdFH = isset($r_Trespasser['LanguageId']) ? $r_Trespasser['LanguageId'] : -1;
$a_Trespasser = array(
    array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Trespasser['Id'],'settype'=>'int'),
    array('field'=>'Genre','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Genre']),
    array('field'=>'CompanyName','selector'=>'value','type'=>'str','value'=>$r_Trespasser['CompanyName']),
    array('field'=>'Surname','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Surname']),
    array('field'=>'Name','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Name']),
    array('field'=>'BornDate','selector'=>'value','type'=>'str','value'=>$r_Trespasser['BornDate']),
    array('field'=>'BornPlace','selector'=>'value','type'=>'str','value'=>$BornPlaceFH),
    array('field'=>'BornCountryId','selector'=>'value','type'=>'str','value'=>$r_Trespasser['BornCountryId']),
    array('field'=>'TaxCode','selector'=>'value','type'=>'str','value'=>$r_Trespasser['TaxCode']),
    array('field'=>'ForcedTaxCode','selector'=>'value','type'=>'str','value'=>$r_Trespasser['ForcedTaxCode']),
    array('field'=>'VatCode','selector'=>'value','type'=>'str','value'=>$r_Trespasser['VatCode']),
    array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$AddressFH),
    array('field'=>'StreetNumber','selector'=>'value','type'=>'str','value'=>$r_Trespasser['StreetNumber']),
    array('field'=>'Ladder','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Ladder']),
    array('field'=>'Indoor','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Indoor']),
    array('field'=>'Plan','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Plan']),
    array('field'=>'ZIP','selector'=>'value','type'=>'str','value'=>$r_Trespasser['ZIP']),
    array('field'=>'City','selector'=>'value','type'=>'str','value'=>$CityFH),
    array('field'=>'Province','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Province']),
    array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$CountryIdFH),
    array('field'=>'PEC','selector'=>'value','type'=>'str','value'=>$r_Trespasser['PEC']),
    array('field'=>'Mail','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Mail']),
    array('field'=>'Phone','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Phone']),
    array('field'=>'Phone2','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Phone2']),
    array('field'=>'Fax','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Fax']),
    array('field'=>'Notes','selector'=>'value','type'=>'str','value'=>$r_Trespasser['Notes']),
    array('field'=>'ZoneId','selector'=>'value','type'=>'int','value'=>$ZoneIdFH,'settype'=>'int'),
    array('field'=>'LanguageId','selector'=>'value','type'=>'int','value'=>$LanguageIdFH,'settype'=>'int'),
    array('field'=>'DeathDate','selector'=>'value','type'=>'date','value'=>$r_Trespasser['DeathDate']),
    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$r_Trespasser['UserId']),
    array('field'=>'VersionDate','selector'=>'value','type'=>'str','value'=>$r_Trespasser['VersionDate']),
    array('field'=>'UpdateUserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']),
    array('field'=>'UpdateDataSourceId','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
    array('field'=>'UpdateDataSourceDate','selector'=>'value','type'=>'date','value'=>date('Y-m-d')),
    array('field'=>'UpdateDataSourceTime','selector'=>'value','type'=>'str','value'=>date('H:i:s')),
);

$a_oldTrespasserData = array(
    "Surname" => StringOutDB($r_Trespasser['Surname']),
    "Name" => StringOutDB($r_Trespasser['Name']),
    "CompanyName" => StringOutDB($r_Trespasser['CompanyName']),
    "CountryId" => StringOutDB($r_Trespasser['CountryId']),
    "City" => StringOutDB($r_Trespasser['City']),
    "Province" => StringOutDB($r_Trespasser['Province']),
    "Address" => StringOutDB($r_Trespasser['Address']),
    "StreetNumber" => StringOutDB($r_Trespasser['StreetNumber']),
    "Ladder" => StringOutDB($r_Trespasser['Ladder']),
    "Indoor" => StringOutDB($r_Trespasser['Indoor']),
    "Plan" => StringOutDB($r_Trespasser['Plan']),
    "ZIP" => StringOutDB($r_Trespasser['ZIP']),
    "PEC" => StringOutDB($r_Trespasser['PEC']),
);

$a_newTrespasserData = array(
    "Surname" => $Surname,
    "Name" => $Name,
    "CompanyName" => $CompanyName,
    "CountryId" => $CountryId,
    "City" => $City,
    "Province" => $Province,
    "Address" => $Address,
    "StreetNumber" => $StreetNumber,
    "Ladder" => $Ladder,
    "Indoor" => $Indoor,
    "Plan" => $Plan,
    "ZIP" => $ZIP,
    "PEC" => $PEC,
);

if($a_oldTrespasserData != $a_newTrespasserData)
    $rs->Insert('TrespasserHistory',$a_Trespasser);


/*---------------------------------------------------------------------
 GESTIONE TRASGRESSORE
 *-------------------------------------------------------------------*/

$a_Trespasser = array(
	array('field'=>'Genre','selector'=>'value','type'=>'str','value'=>$Genre),
    array('field'=>'Surname','selector'=>'value','type'=>'str', 'value'=>NULL),
    array('field'=>'Name','selector'=>'value','type'=>'str', 'value'=>NULL),
    array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$Address),
    array('field'=>'TaxCode','selector'=>'value','type'=>'str','value'=>NULL),
    array('field'=>'ForcedTaxCode','selector'=>'value','type'=>'str','value'=>NULL),
    array('field'=>'VatCode','selector'=>'value','type'=>'str','value'=>NULL),
    array('field'=>'ZIP','selector'=>'field','type'=>'str'),
    array('field'=>'City','selector'=>'value','type'=>'str','value'=>$City),
    array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$CityId),
    array('field'=>'Province','selector'=>'value','type'=>'str','value'=>$Province),
	array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$CountryId),
    array('field'=>'PEC','selector'=>'field','type'=>'str'),
	array('field'=>'Mail','selector'=>'field','type'=>'str','value'=>'Mail'),
	array('field'=>'Phone','selector'=>'field','type'=>'str','value'=>'Phone'),
    array('field'=>'Phone2','selector'=>'field','type'=>'str'),
    array('field'=>'Fax','selector'=>'field','type'=>'str'),
    array('field'=>'Notes','selector'=>'field','type'=>'str'),
	array('field'=>'ZoneId','selector'=>'value','type'=>'int','value'=>$ZoneId,'settype'=>'int'),
    array('field'=>'LanguageId','selector'=>'value','type'=>'int','value'=>$LanguageId,'settype'=>'int'),
    array('field'=>'StreetNumber','selector'=>'field','type'=>'str'),
    array('field'=>'Ladder','selector'=>'field','type'=>'str'),
    array('field'=>'Indoor','selector'=>'field','type'=>'str'),
    array('field'=>'Plan','selector'=>'field','type'=>'str'),
    array('field'=>'LicenseCategory','selector'=>'field','type'=>'str','value'=>NULL),
    array('field'=>'LicenseDate','selector'=>'field','type'=>'date','value'=>NULL),
    array('field'=>'LicenseNumber','selector'=>'field','type'=>'str','value'=>NULL),
    array('field'=>'LicenseOffice','selector'=>'field','type'=>'str','value'=>NULL),
    array('field'=>'DocumentTypeId2','selector'=>'field','type'=>'int','value'=>NULL,'settype'=>'int'),
    array('field'=>'DocumentNumber','selector'=>'field','type'=>'str','value'=>NULL),
    array('field'=>'DocumentValidFrom','selector'=>'field','type'=>'date','value'=>NULL),
    array('field'=>'DocumentValidTo','selector'=>'field','type'=>'date','value'=>NULL),
    array('field'=>'DocumentCountryId2','selector'=>'value','type'=>'str','value'=>NULL),
    array('field'=>'DocumentOffice','selector'=>'value','type'=>'str','value'=>NULL),
    array('field'=>'DocumentCountryId','selector'=>'value','type'=>'str', 'value'=>NULL),
    array('field'=>'LegalFormId','selector'=>'value','type'=>'int','value'=>$LegalFormId,'settype'=>'int'),
    array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$_SESSION['username']),
    array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>date("Y-m-d")),
    array('field'=>'InipecLoaded','selector'=>'value','type'=>'date', 'value'=>NULL ),
);

if ($LandId != null)
    $a_Trespasser[] = array('field'=>'LandId','selector'=>'value','type'=>'int','value'=>$LandId,'settype'=>'int');
else
    $a_Trespasser[] = array('field'=>'LandId','selector'=>'value','type'=>'int','value'=>NULL);
    
    

if($Genre=="D"){
//DITTA
    $a_Trespasser[] = array('field'=>'CompanyName','selector'=>'value','type'=>'str', 'value'=>$CompanyName);
    $a_Trespasser[] = array('field'=>'VatCode','selector'=>'value','type'=>'str', 'value'=>$VatCode);
	
    if (in_array($CompanyLegalFormId, $a_LegalFormIndividual)) {
    //DITTA INDIVIDUALE
        $a_Trespasser[] = array('field'=>'TaxCode','selector'=>'value','type'=>'str','value'=>$TaxCode);
        
        if ($ForcedTaxCode != ""){
            $a_Trespasser[] = array('field'=>'ForcedTaxCode','selector'=>'value','type'=>'str', 'value'=>$ForcedTaxCode);
        }
        
        $a_Trespasser[] = array('field'=>'BornPlace','selector'=>'value','type'=>'str', 'value'=>$BornCity);
        $a_Trespasser[] = array('field'=>'BornCountryId','selector'=>'value','type'=>'str','value'=>$BornCountry);
        $a_Trespasser[] = array('field'=>'Surname','selector'=>'value','type'=>'str', 'value'=>$Surname);
        $a_Trespasser[] = array('field'=>'Name','selector'=>'value','type'=>'str', 'value'=>$Name);
    } else {
        $a_Trespasser[] = array('field'=>'TaxCode','selector'=>'value','type'=>'str', 'value'=>$CompanyTaxCode);
    }
}else{
//PERSONA FISICA

	$BornDate = CheckValue('BornDate','s');
	if($BornDate!= ""){
        $a_Trespasser[] = array('field'=>'BornDate','selector'=>'value','type'=>'date','value'=>$BornDate);
	}

	$a_Trespasser[] = array('field'=>'TaxCode','selector'=>'value','type'=>'str','value'=>$TaxCode);
	
	if ($ForcedTaxCode != ""){
	    $a_Trespasser[] = array('field'=>'ForcedTaxCode','selector'=>'value','type'=>'str', 'value'=>$ForcedTaxCode);
	}
	
	$a_Trespasser[] = array('field'=>'BornPlace','selector'=>'value','type'=>'str', 'value'=>$BornCity);
    $a_Trespasser[] = array('field'=>'BornCountryId','selector'=>'value','type'=>'str','value'=>$BornCountry);
    $a_Trespasser[] = array('field'=>'Surname','selector'=>'value','type'=>'str', 'value'=>$Surname);
    $a_Trespasser[] = array('field'=>'Name','selector'=>'value','type'=>'str', 'value'=>$Name);
    $a_Trespasser[] = array('field'=>'LicenseCategory','selector'=>'field','type'=>'str');
    $a_Trespasser[] = array('field'=>'LicenseDate','selector'=>'field','type'=>'date');
    $a_Trespasser[] = array('field'=>'LicenseNumber','selector'=>'field','type'=>'str');
    $a_Trespasser[] = array('field'=>'LicenseOffice','selector'=>'field','type'=>'str');
    $a_Trespasser[] = array('field'=>'DocumentTypeId2','selector'=>'field','type'=>'int','settype'=>'int');
    $a_Trespasser[] = array('field'=>'DocumentNumber','selector'=>'field','type'=>'str','value'=>$DocumentNumber);
    $a_Trespasser[] = array('field'=>'DocumentValidFrom','selector'=>'field','type'=>'date');
    $a_Trespasser[] = array('field'=>'DocumentValidTo','selector'=>'field','type'=>'date');
    $a_Trespasser[] = array('field'=>'DocumentCountryId2','selector'=>'value','type'=>'str','value'=>$DocumentCountryId2);
    $a_Trespasser[] = array('field'=>'DocumentOffice','selector'=>'value','type'=>'str','value'=>$DocumentOffice);
    $a_Trespasser[] = array('field'=>'DocumentCountryId','selector'=>'value','type'=>'str', 'value'=>$DocumentCountryId);
    
    if ($PersonLegalFormId != "") {
        //IMPRESA INDIVIDUALE
        $a_Trespasser[] = array('field'=>'VatCode','selector'=>'value','type'=>'str', 'value'=>$VatCode);
    }
}




if($DeathDate!=""){
    $rs_FineTrespasser = $rs->Select("FineTrespasser", "TrespasserId=".$TrespasserId);

    while ($r_FineTrespasser = mysqli_fetch_array($rs_FineTrespasser)){
        $FineId = $r_FineTrespasser['FineId'];

        $rs_FineNotification = $rs->Select("FineNotification","FineId=".$FineId);
        if(mysqli_num_rows($rs_FineNotification)>0){


            $a_Notification = array(
                array('field'=>'PaymentProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                array('field'=>'126BisProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                array('field'=>'PresentationDocumentProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                array('field'=>'LicensePointProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                array('field'=>'HabitualProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                array('field'=>'SuspensionLicenseProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                array('field'=>'LossLicenseProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                array('field'=>'InjunctionProcedure','selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                );
            $rs->Update('FineNotification',$a_Notification, "FineId=".$FineId);


        } else {
            $a_TmpTable = array("TMP_126BisProcedure","TMP_InjunctionProcedure","TMP_LicensePointProcedure","TMP_PaymentProcedure","TMP_PresentationDocumentProcedure");


            for($i=0; $i<count($a_TmpTable); $i++){
                $chk_Tmp = $rs->Select($a_TmpTable[$i],"FineId=".$FineId);

                if(mysqli_num_rows($chk_Tmp)==0){
                    $a_TmpInsert = array(
                        array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId,'settype'=>'int'),
                        array('field'=>substr($a_TmpTable[$i],4),'selector'=>'value','type'=>'int','value'=>0,'settype'=>'int'),
                    );
                    $rs->Insert($a_TmpTable[$i],$a_TmpInsert);
                }


            }
        }
    }
    $a_Trespasser[] = array('field'=>'DeathDate','selector'=>'field','type'=>'date');

}


$rs->Update('Trespasser',$a_Trespasser, "Id=".$TrespasserId);

//Attribuisce il codice progressivo al trasgressore, se il trasgressore non appartiene ad un ente, viene preso quello in sessione
addCustomIdCode($rs,$TrespasserId, $r_Trespasser['CustomerId'] ?? $_SESSION['cityid']);

/*---------------------------------------------------------------------
CONTATTI
 *-------------------------------------------------------------------*/

//RECAPITi

$ForwardingNumber = CheckValue('ForwardingNumber','n');
$ForwardingId = $_POST['ForwardingId'];
$Forwarding_Nominative = $_POST['Forwarding_Nominative'];
$Forwarding_CountryId = $_POST['Forwarding_CountryId'];
$Forwarding_CitySelect = $_POST['Forwarding_CitySelect'];
$Forwarding_LandId = $_POST['Forwarding_LandId'];
$Forwarding_ForeignCitySelect = $_POST['Forwarding_ForeignCitySelect'];
$Forwarding_CityInput = $_POST['Forwarding_CityInput'];
$Forwarding_Address = $_POST['Forwarding_Address'];
$Forwarding_ZIP = $_POST['Forwarding_ZIP'];
$Forwarding_StreetNumber = $_POST['Forwarding_StreetNumber'];
$Forwarding_Ladder = $_POST['Forwarding_Ladder'];
$Forwarding_Indoor = $_POST['Forwarding_Indoor'];
$Forwarding_Plan = $_POST['Forwarding_Plan'];
$Forwarding_Mail = $_POST['Forwarding_Mail'];
$Forwarding_Phone = $_POST['Forwarding_Phone'];
$Forwarding_Fax = $_POST['Forwarding_Fax'];
$Forwarding_Phone2 = $_POST['Forwarding_Phone2'];
$Forwarding_PEC = $_POST['Forwarding_PEC'];
$Forwarding_Notes = $_POST['Forwarding_Notes'];
$Forwarding_ValidUntil = $_POST['Forwarding_ValidUntil'];


for ($i=0; $i<$ForwardingNumber; $i++){

    if($Forwarding_CountryId[$i]=="Z000"){
        $ForwardingCityId = $Forwarding_CitySelect[$i];
        $rs_ForwardingCity = $rs->SelectQuery("
                SELECT C.Title CityTitle, P.ShortTitle ProvinceTitle
                FROM ". MAIN_DB.".City C JOIN ". MAIN_DB.".Province P ON C.ProvinceId=P.Id
                WHERE C.Id='".$ForwardingCityId."'
            ");
        $r_ForwardingCity = mysqli_fetch_array($rs_ForwardingCity);
        
        $ForwardingCity = strtoupper($r_ForwardingCity['CityTitle']);
        $ForwardingProvince = $r_ForwardingCity['ProvinceTitle'];
        $ForwardingLandId = null;
    } else if ($Forwarding_CountryId[$i]=="Z102" || $Forwarding_CountryId[$i]=="Z112"){
        $ForwardingCityId = $Forwarding_ForeignCitySelect[$i];
        $rs_ForwardingCity = $rs->Select('ForeignCity', 'Id='.$ForwardingCityId);
        $r_ForwardingCity = mysqli_fetch_array($rs_ForwardingCity);
        
        $ForwardingCity = strtoupper($r_ForwardingCity['Title']);
        $ForwardingCityId = null;
        $ForwardingProvince = null;
        $ForwardingLandId = $Forwarding_LandId[$i];
    } else {
        
        $ForwardingCity = $Forwarding_CityInput[$i];
        $ForwardingCityId = null;
        $ForwardingProvince = null;
        $ForwardingLandId = null;
    }
    
    //Controlla che siano inseriti indirizzo e città per inserire su db
    if ($Forwarding_Address[$i] != "" && $ForwardingCity != ""){
        $a_TrespasserContact = array(
            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId,'settype'=>'int'),
            array('field'=>'ContactTypeId','selector'=>'value','type'=>'int','value'=>1,'settype'=>'int'),
            array('field'=>'Nominative','selector'=>'value','type'=>'str','value'=>($Forwarding_Nominative[$i] != "") ? $Forwarding_Nominative[$i] : NULL),
            array('field'=>'Address','selector'=>'value','type'=>'str','value'=>($Forwarding_Address[$i] != "") ? $Forwarding_Address[$i] : NULL),
            array('field'=>'StreetNumber','selector'=>'value','type'=>'str','value'=>($Forwarding_StreetNumber[$i] != "") ? $Forwarding_StreetNumber[$i] : NULL),
            array('field'=>'Ladder','selector'=>'value','type'=>'str','value'=>($Forwarding_Ladder[$i] != "") ? $Forwarding_Ladder[$i] : NULL),
            array('field'=>'Indoor','selector'=>'value','type'=>'str','value'=>($Forwarding_Indoor[$i] != "") ? $Forwarding_Indoor[$i] : NULL),
            array('field'=>'Plan','selector'=>'value','type'=>'str','value'=>($Forwarding_Plan[$i] != "") ? $Forwarding_Plan[$i] : NULL),
            array('field'=>'ZIP','selector'=>'value','type'=>'str','value'=>($Forwarding_ZIP[$i] != "") ? $Forwarding_ZIP[$i] : NULL),
            array('field'=>'City','selector'=>'value','type'=>'str','value'=>$ForwardingCity),
            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$ForwardingCityId),
            array('field'=>'Province','selector'=>'value','type'=>'str','value'=>$ForwardingProvince),
            array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>($Forwarding_CountryId[$i] != "") ? $Forwarding_CountryId[$i] : NULL),
            array('field'=>'Mail','selector'=>'value','type'=>'str','value'=>($Forwarding_Mail[$i] != "") ? $Forwarding_Mail[$i] : NULL),
            array('field'=>'Phone','selector'=>'value','type'=>'str','value'=>($Forwarding_Phone[$i] != "") ? $Forwarding_Phone[$i] : NULL),
            array('field'=>'Phone2','selector'=>'value','type'=>'str','value'=>($Forwarding_Phone2[$i] != "") ? $Forwarding_Phone2[$i] : NULL),
            array('field'=>'Fax','selector'=>'value','type'=>'str','value'=>($Forwarding_Fax[$i] != "") ? $Forwarding_Fax[$i] : NULL),
            array('field'=>'PEC','selector'=>'value','type'=>'str','value'=>($Forwarding_PEC[$i] != "") ? $Forwarding_PEC[$i] : NULL),
            array('field'=>'Notes','selector'=>'value','type'=>'str','value'=>$Forwarding_Notes[$i]),
            array('field'=>'ValidUntil','selector'=>'value','type'=>'date','value'=>($Forwarding_ValidUntil[$i] != "") ? $Forwarding_ValidUntil[$i] : NULL),
            array('field'=>'Cds','selector'=>'value','type'=>'int','value'=>isset($_POST['Forwarding_Cds'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Imu','selector'=>'value','type'=>'int','value'=>isset($_POST['Forwarding_Imu'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Tari','selector'=>'value','type'=>'int','value'=>isset($_POST['Forwarding_Tari'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Osap','selector'=>'value','type'=>'int','value'=>isset($_POST['Forwarding_Osap'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Water','selector'=>'value','type'=>'int','value'=>isset($_POST['Forwarding_Water'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Advertising','selector'=>'value','type'=>'int','value'=>isset($_POST['Forwarding_Advertising'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Others','selector'=>'value','type'=>'int','value'=>isset($_POST['Forwarding_Others'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$_SESSION['username']),
            array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>date("Y-m-d H:i:s")),
        );
        
        if ($ForwardingLandId != null)
            $a_TrespasserContact[] = array('field'=>'LandId','selector'=>'value','type'=>'int','value'=>$ForwardingLandId,'settype'=>'int');
        else
            $a_TrespasserContact[] = array('field'=>'LandId','selector'=>'value','type'=>'int','value'=>NULL);
            
        //Se trova id pieno nella dimora tra i campi, viene eseguito un update, altrimenti insert
        if ($ForwardingId[$i] != "" && $Overwrite == ""){
            
            //STORICO
            $rs_Forwarding = $rs->Select('TrespasserContact',"Id=".$ForwardingId[$i]);
            $r_Forwarding = (mysqli_fetch_assoc($rs_Forwarding));
            
            $a_oldContact = array(
                "Nominative" => StringOutDB($r_Forwarding['Nominative']),
                "Address" => StringOutDB($r_Forwarding['Address']),
                "StreetNumber" => StringOutDB($r_Forwarding['StreetNumber']),
                "Ladder" => StringOutDB($r_Forwarding['Ladder']),
                "Indoor" => StringOutDB($r_Forwarding['Indoor']),
                "Plan" => StringOutDB($r_Forwarding['Plan']),
                "ZIP" => StringOutDB($r_Forwarding['ZIP']),
                "City" => StringOutDB($r_Forwarding['City']),
                "CityId" => StringOutDB($r_Forwarding['CityId']),
                "CountryId" => StringOutDB($r_Forwarding['CountryId']),
                "Mail" => StringOutDB($r_Forwarding['Mail']),
                "Phone" => StringOutDB($r_Forwarding['Phone']),
                "Phone2" => StringOutDB($r_Forwarding['Phone2']),
                "Fax" => StringOutDB($r_Forwarding['Fax']),
                "PEC" => StringOutDB($r_Forwarding['PEC']),
                "Notes" => StringOutDB($r_Forwarding['Notes']),
                "ValidUntil" => DateOutDb($r_Forwarding['ValidUntil']),
                "Cds" => $r_Forwarding['Cds'],
                "Imu" => $r_Forwarding['Imu'],
                "Tari" => $r_Forwarding['Tari'],
                "Osap" => $r_Forwarding['Osap'],
                "Water" => $r_Forwarding['Water'],
                "Advertising" => $r_Forwarding['Advertising'],
                "Others" => $r_Forwarding['Others'],
            );
            
            $a_newContact = array(
                "Nominative" => $Forwarding_Nominative[$i],
                "Address" => $Forwarding_Address[$i],
                "StreetNumber" => $Forwarding_StreetNumber[$i],
                "Ladder" => $Forwarding_Ladder[$i],
                "Indoor" => $Forwarding_Indoor[$i],
                "Plan" => $Forwarding_Plan[$i],
                "ZIP" => $Forwarding_ZIP[$i],
                "City" => $ForwardingCity,
                "CityId" => $ForwardingCityId,
                "CountryId" => $Forwarding_CountryId[$i],
                "Mail" => $Forwarding_Mail[$i],
                "Phone" => $Forwarding_Phone[$i],
                "Phone2" => $Forwarding_Phone2[$i],
                "Fax" => $Forwarding_Fax[$i],
                "PEC" => $Forwarding_PEC[$i],
                "Notes" => $Forwarding_Notes[$i],
                "ValidUntil" => $Forwarding_ValidUntil[$i],
                "Cds" => isset($_POST['Forwarding_Cds'][$i]) ? 1 : 0,
                "Imu" => isset($_POST['Forwarding_Imu'][$i]) ? 1 : 0,
                "Tari" => isset($_POST['Forwarding_Tari'][$i]) ? 1 : 0,
                "Osap" => isset($_POST['Forwarding_Osap'][$i]) ? 1 : 0,
                "Water" => isset($_POST['Forwarding_Water'][$i]) ? 1 : 0,
                "Advertising" => isset($_POST['Forwarding_Advertising'][$i]) ? 1 : 0,
                "Others" => isset($_POST['Forwarding_Others'][$i]) ? 1 : 0,
            );
            
            if ($a_oldContact != $a_newContact){
                //Se La data di validità (se esistente) supera quella corrente
                if($r_Forwarding['ValidUntil'] >= $CurrentDate || $r_Forwarding['ValidUntil'] == NULL){
                    $a_ForwardingHistory = array(
                        array('field'=>'TrespasserContactId','selector'=>'value','type'=>'int','value'=>$r_Forwarding['Id'],'settype'=>'int'),
                        array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Forwarding['TrespasserId'],'settype'=>'int'),
                        array('field'=>'ContactTypeId','selector'=>'value','type'=>'int','value'=>$r_Forwarding['ContactTypeId'],'settype'=>'int'),
                        array('field'=>'Nominative','selector'=>'value','type'=>'str','value'=>$r_Forwarding['Nominative']),
                        array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$r_Forwarding['Address']),
                        array('field'=>'StreetNumber','selector'=>'value','type'=>'str','value'=>$r_Forwarding['StreetNumber']),
                        array('field'=>'Ladder','selector'=>'value','type'=>'str','value'=>$r_Forwarding['Ladder']),
                        array('field'=>'Indoor','selector'=>'value','type'=>'str','value'=>$r_Forwarding['Indoor']),
                        array('field'=>'Plan','selector'=>'value','type'=>'str','value'=>$r_Forwarding['Plan']),
                        array('field'=>'ZIP','selector'=>'value','type'=>'str','value'=>$r_Forwarding['ZIP']),
                        array('field'=>'City','selector'=>'value','type'=>'str','value'=>$r_Forwarding['City']),
                        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$r_Forwarding['CityId']),
                        array('field'=>'Province','selector'=>'value','type'=>'str','value'=>$r_Forwarding['Province']),
                        array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$r_Forwarding['CountryId']),
                        array('field'=>'Mail','selector'=>'value','type'=>'str','value'=>$r_Forwarding['Mail']),
                        array('field'=>'Phone','selector'=>'value','type'=>'str','value'=>$r_Forwarding['Phone']),
                        array('field'=>'Phone2','selector'=>'value','type'=>'str','value'=>$r_Forwarding['Phone2']),
                        array('field'=>'Fax','selector'=>'value','type'=>'str','value'=>$r_Forwarding['Fax']),
                        array('field'=>'PEC','selector'=>'value','type'=>'str','value'=>$r_Forwarding['PEC']),
                        array('field'=>'Notes','selector'=>'value','type'=>'str','value'=>$r_Forwarding['Notes']),
                        array('field'=>'ValidUntil','selector'=>'value','type'=>'str', 'value'=>$r_Forwarding['ValidUntil']),
                        array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$r_Forwarding['UserId']),
                        array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>$r_Forwarding['VersionDate']),
                    );
                    
                    $rs->Insert('TrespasserContactHistory',$a_ForwardingHistory);
                }
            }
            
            $rs->Update('TrespasserContact',$a_TrespasserContact,"Id=".$ForwardingId[$i]." AND TrespasserId=$TrespasserId AND ContactTypeId=1");
        } else {
            $rs->Insert('TrespasserContact',$a_TrespasserContact);
        }
    }
}

//DOMICILIO

$DomicileNumber = CheckValue('DomicileNumber','n');
$DomicileId = $_POST['DomicileId'];
$Domicile_CountryId = $_POST['Domicile_CountryId'];
$Domicile_CitySelect = $_POST['Domicile_CitySelect'];
$Domicile_LandId = $_POST['Domicile_LandId'];
$Domicile_ForeignCitySelect = $_POST['Domicile_ForeignCitySelect'];
$Domicile_CityInput = $_POST['Domicile_CityInput'];
$Domicile_Address = $_POST['Domicile_Address'];
$Domicile_ZIP = $_POST['Domicile_ZIP'];
$Domicile_StreetNumber = $_POST['Domicile_StreetNumber'];
$Domicile_Ladder = $_POST['Domicile_Ladder'];
$Domicile_Indoor = $_POST['Domicile_Indoor'];
$Domicile_Plan = $_POST['Domicile_Plan'];
$Domicile_Mail = $_POST['Domicile_Mail'];
$Domicile_Phone = $_POST['Domicile_Phone'];
$Domicile_Fax = $_POST['Domicile_Fax'];
$Domicile_Phone2 = $_POST['Domicile_Phone2'];
$Domicile_PEC = $_POST['Domicile_PEC'];
$Domicile_Notes = $_POST['Domicile_Notes'];
$Domicile_ValidUntil = $_POST['Domicile_ValidUntil'];


for ($i=0; $i<$DomicileNumber; $i++){
    
    if($Domicile_CountryId[$i]=="Z000"){
        $DomicileCityId = $Domicile_CitySelect[$i];
        $rs_DomicileCity = $rs->SelectQuery("
                SELECT C.Title CityTitle, P.ShortTitle ProvinceTitle
                FROM ". MAIN_DB.".City C JOIN ". MAIN_DB.".Province P ON C.ProvinceId=P.Id
                WHERE C.Id='".$DomicileCityId."'
            ");
        $r_DomicileCity = mysqli_fetch_array($rs_DomicileCity);
        
        $DomicileCity = strtoupper($r_DomicileCity['CityTitle']);
        $DomicileProvince = $r_DomicileCity['ProvinceTitle'];
        $DomicileLandId = null;
    } else if ($Domicile_CountryId[$i]=="Z102" || $Domicile_CountryId[$i]=="Z112"){
        $DomicileCityId = $Domicile_ForeignCitySelect[$i];
        $rs_DomicileCity = $rs->Select('ForeignCity', 'Id='.$DomicileCityId);
        $r_DomicileCity = mysqli_fetch_array($rs_DomicileCity);
        
        $DomicileCity = strtoupper($r_DomicileCity['Title']);
        $DomicileCityId = null;
        $DomicileProvince = null;
        $DomicileLandId = $Domicile_LandId[$i];
    } else {
        
        $DomicileCity = $Domicile_CityInput[$i];
        $DomicileCityId = null;
        $DomicileProvince = null;
        $DomicileLandId = null;
    }
    
    //Controlla che siano inseriti indirizzo e città per inserire su db
    if ($Domicile_Address[$i] != "" && $DomicileCity != ""){
        $a_TrespasserContact = array(
            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId,'settype'=>'int'),
            array('field'=>'ContactTypeId','selector'=>'value','type'=>'int','value'=>2,'settype'=>'int'),
            array('field'=>'Address','selector'=>'value','type'=>'str','value'=>($Domicile_Address[$i] != "") ? $Domicile_Address[$i] : NULL),
            array('field'=>'StreetNumber','selector'=>'value','type'=>'str','value'=>($Domicile_StreetNumber[$i] != "") ? $Domicile_StreetNumber[$i] : NULL),
            array('field'=>'Ladder','selector'=>'value','type'=>'str','value'=>($Domicile_Ladder[$i] != "") ? $Domicile_Ladder[$i] : NULL),
            array('field'=>'Indoor','selector'=>'value','type'=>'str','value'=>($Domicile_Indoor[$i] != "") ? $Domicile_Indoor[$i] : NULL),
            array('field'=>'Plan','selector'=>'value','type'=>'str','value'=>($Domicile_Plan[$i] != "") ? $Domicile_Plan[$i] : NULL),
            array('field'=>'ZIP','selector'=>'value','type'=>'str','value'=>($Domicile_ZIP[$i] != "") ? $Domicile_ZIP[$i] : NULL),
            array('field'=>'City','selector'=>'value','type'=>'str','value'=>$DomicileCity),
            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$DomicileCityId),
            array('field'=>'Province','selector'=>'value','type'=>'str','value'=>$DomicileProvince),
            array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>($Domicile_CountryId[$i] != "") ? $Domicile_CountryId[$i] : NULL),
            array('field'=>'Mail','selector'=>'value','type'=>'str','value'=>($Domicile_Mail[$i] != "") ? $Domicile_Mail[$i] : NULL),
            array('field'=>'Phone','selector'=>'value','type'=>'str','value'=>($Domicile_Phone[$i] != "") ? $Domicile_Phone[$i] : NULL),
            array('field'=>'Phone2','selector'=>'value','type'=>'str','value'=>($Domicile_Phone2[$i] != "") ? $Domicile_Phone2[$i] : NULL),
            array('field'=>'Fax','selector'=>'value','type'=>'str','value'=>($Domicile_Fax[$i] != "") ? $Domicile_Fax[$i] : NULL),
            array('field'=>'PEC','selector'=>'value','type'=>'str','value'=>($Domicile_PEC[$i] != "") ? $Domicile_PEC[$i] : NULL),
            array('field'=>'Notes','selector'=>'value','type'=>'str','value'=>$Domicile_Notes[$i]),
            array('field'=>'ValidUntil','selector'=>'value','type'=>'date','value'=>($Domicile_ValidUntil[$i] != "") ? $Domicile_ValidUntil[$i] : NULL),
            array('field'=>'Cds','selector'=>'value','type'=>'int','value'=>isset($_POST['Domicile_Cds'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Imu','selector'=>'value','type'=>'int','value'=>isset($_POST['Domicile_Imu'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Tari','selector'=>'value','type'=>'int','value'=>isset($_POST['Domicile_Tari'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Osap','selector'=>'value','type'=>'int','value'=>isset($_POST['Domicile_Osap'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Water','selector'=>'value','type'=>'int','value'=>isset($_POST['Domicile_Water'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Advertising','selector'=>'value','type'=>'int','value'=>isset($_POST['Domicile_Advertising'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Others','selector'=>'value','type'=>'int','value'=>isset($_POST['Domicile_Others'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$_SESSION['username']),
            array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>date("Y-m-d H:i:s")),
        );
        
        if ($DomicileLandId != null)
            $a_TrespasserContact[] = array('field'=>'LandId','selector'=>'value','type'=>'int','value'=>$DomicileLandId,'settype'=>'int');
        else
            $a_TrespasserContact[] = array('field'=>'LandId','selector'=>'value','type'=>'int','value'=>NULL);
        
        //Se trova id pieno nella dimora tra i campi, viene eseguito un update, altrimenti insert
        if ($DomicileId[$i] != "" && $Overwrite == ""){
            
            //STORICO
            $rs_Domicile = $rs->Select('TrespasserContact',"Id=".$DomicileId[$i]);
            $r_Domicile = (mysqli_fetch_assoc($rs_Domicile));
            
            $a_oldContact = array(
                "Address" => StringOutDB($r_Domicile['Address']),
                "StreetNumber" => StringOutDB($r_Domicile['StreetNumber']),
                "Ladder" => StringOutDB($r_Domicile['Ladder']),
                "Indoor" => StringOutDB($r_Domicile['Indoor']),
                "Plan" => StringOutDB($r_Domicile['Plan']),
                "ZIP" => StringOutDB($r_Domicile['ZIP']),
                "City" => StringOutDB($r_Domicile['City']),
                "CityId" => StringOutDB($r_Domicile['CityId']),
                "CountryId" => StringOutDB($r_Domicile['CountryId']),
                "Mail" => StringOutDB($r_Domicile['Mail']),
                "Phone" => StringOutDB($r_Domicile['Phone']),
                "Phone2" => StringOutDB($r_Domicile['Phone2']),
                "Fax" => StringOutDB($r_Domicile['Fax']),
                "PEC" => StringOutDB($r_Domicile['PEC']),
                "Notes" => StringOutDB($r_Domicile['Notes']),
                "ValidUntil" => DateOutDb($r_Domicile['ValidUntil']),
                "Cds" => $r_Domicile['Cds'],
                "Imu" => $r_Domicile['Imu'],
                "Tari" => $r_Domicile['Tari'],
                "Osap" => $r_Domicile['Osap'],
                "Water" => $r_Domicile['Water'],
                "Advertising" => $r_Domicile['Advertising'],
                "Others" => $r_Domicile['Others'],
            );
            
            $a_newContact = array(
                "Address" => $Domicile_Address[$i],
                "StreetNumber" => $Domicile_StreetNumber[$i],
                "Ladder" => $Domicile_Ladder[$i],
                "Indoor" => $Domicile_Indoor[$i],
                "Plan" => $Domicile_Plan[$i],
                "ZIP" => $Domicile_ZIP[$i],
                "City" => $DomicileCity,
                "CityId" => $DomicileCityId,
                "CountryId" => $Domicile_CountryId[$i],
                "Mail" => $Domicile_Mail[$i],
                "Phone" => $Domicile_Phone[$i],
                "Phone2" => $Domicile_Phone2[$i],
                "Fax" => $Domicile_Fax[$i],
                "PEC" => $Domicile_PEC[$i],
                "Notes" => $Domicile_Notes[$i],
                "ValidUntil" => $Domicile_ValidUntil[$i],
                "Cds" => isset($_POST['Domicile_Cds'][$i]) ? 1 : 0,
                "Imu" => isset($_POST['Domicile_Imu'][$i]) ? 1 : 0,
                "Tari" => isset($_POST['Domicile_Tari'][$i]) ? 1 : 0,
                "Osap" => isset($_POST['Domicile_Osap'][$i]) ? 1 : 0,
                "Water" => isset($_POST['Domicile_Water'][$i]) ? 1 : 0,
                "Advertising" => isset($_POST['Domicile_Advertising'][$i]) ? 1 : 0,
                "Others" => isset($_POST['Domicile_Others'][$i]) ? 1 : 0,
            );
            
            if ($a_oldContact != $a_newContact){
                //Se La data di validità (se esistente) supera quella corrente
                if($r_Domicile['ValidUntil'] >= $CurrentDate || $r_Domicile['ValidUntil'] == NULL){
                    $a_DomicileHistory = array(
                        array('field'=>'TrespasserContactId','selector'=>'value','type'=>'int','value'=>$r_Domicile['Id'],'settype'=>'int'),
                        array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Domicile['TrespasserId'],'settype'=>'int'),
                        array('field'=>'ContactTypeId','selector'=>'value','type'=>'int','value'=>$r_Domicile['ContactTypeId'],'settype'=>'int'),
                        array('field'=>'Nominative','selector'=>'value','type'=>'str','value'=>$r_Domicile['Nominative']),
                        array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$r_Domicile['Address']),
                        array('field'=>'StreetNumber','selector'=>'value','type'=>'str','value'=>$r_Domicile['StreetNumber']),
                        array('field'=>'Ladder','selector'=>'value','type'=>'str','value'=>$r_Domicile['Ladder']),
                        array('field'=>'Indoor','selector'=>'value','type'=>'str','value'=>$r_Domicile['Indoor']),
                        array('field'=>'Plan','selector'=>'value','type'=>'str','value'=>$r_Domicile['Plan']),
                        array('field'=>'ZIP','selector'=>'value','type'=>'str','value'=>$r_Domicile['ZIP']),
                        array('field'=>'City','selector'=>'value','type'=>'str','value'=>$r_Domicile['City']),
                        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$r_Domicile['CityId']),
                        array('field'=>'Province','selector'=>'value','type'=>'str','value'=>$r_Domicile['Province']),
                        array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$r_Domicile['CountryId']),
                        array('field'=>'Mail','selector'=>'value','type'=>'str','value'=>$r_Domicile['Mail']),
                        array('field'=>'Phone','selector'=>'value','type'=>'str','value'=>$r_Domicile['Phone']),
                        array('field'=>'Phone2','selector'=>'value','type'=>'str','value'=>$r_Domicile['Phone2']),
                        array('field'=>'Fax','selector'=>'value','type'=>'str','value'=>$r_Domicile['Fax']),
                        array('field'=>'PEC','selector'=>'value','type'=>'str','value'=>$r_Domicile['PEC']),
                        array('field'=>'Notes','selector'=>'value','type'=>'str','value'=>$r_Domicile['Notes']),
                        array('field'=>'ValidUntil','selector'=>'value','type'=>'str', 'value'=>$r_Domicile['ValidUntil']),
                        array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$r_Domicile['UserId']),
                        array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>$r_Domicile['VersionDate']),
                    );
                    
                    $rs->Insert('TrespasserContactHistory',$a_DomicileHistory);
                }
            }
            
            $rs->Update('TrespasserContact',$a_TrespasserContact,"Id=".$DomicileId[$i]." AND TrespasserId=$TrespasserId AND ContactTypeId=2");
        } else {
            $rs->Insert('TrespasserContact',$a_TrespasserContact);
        }
    }
}


//DIMORA

$DwellingNumber = CheckValue('DwellingNumber','n');
$DwellingId = $_POST['DwellingId'];
$Dwelling_CountryId = $_POST['Dwelling_CountryId'];
$Dwelling_CitySelect = $_POST['Dwelling_CitySelect'];
$Dwelling_LandId = $_POST['Dwelling_LandId'];
$Dwelling_ForeignCitySelect = $_POST['Dwelling_ForeignCitySelect'];
$Dwelling_CityInput = $_POST['Dwelling_CityInput'];
$Dwelling_Address = $_POST['Dwelling_Address'];
$Dwelling_ZIP = $_POST['Dwelling_ZIP'];
$Dwelling_StreetNumber = $_POST['Dwelling_StreetNumber'];
$Dwelling_Ladder = $_POST['Dwelling_Ladder'];
$Dwelling_Indoor = $_POST['Dwelling_Indoor'];
$Dwelling_Plan = $_POST['Dwelling_Plan'];
$Dwelling_Mail = $_POST['Dwelling_Mail'];
$Dwelling_Phone = $_POST['Dwelling_Phone'];
$Dwelling_Fax = $_POST['Dwelling_Fax'];
$Dwelling_Phone2 = $_POST['Dwelling_Phone2'];
$Dwelling_PEC = $_POST['Dwelling_PEC'];
$Dwelling_Notes = $_POST['Dwelling_Notes'];
$Dwelling_ValidUntil = $_POST['Dwelling_ValidUntil'];


for ($i=0; $i<$DwellingNumber; $i++){
    
    if($Dwelling_CountryId[$i]=="Z000"){
        $DwellingCityId = $Dwelling_CitySelect[$i];
        $rs_DwellingCity = $rs->SelectQuery("
                SELECT C.Title CityTitle, P.ShortTitle ProvinceTitle
                FROM ". MAIN_DB.".City C JOIN ". MAIN_DB.".Province P ON C.ProvinceId=P.Id
                WHERE C.Id='".$DwellingCityId."'
            ");
        $r_DwellingCity = mysqli_fetch_array($rs_DwellingCity);
        
        $DwellingCity = strtoupper($r_DwellingCity['CityTitle']);
        $DwellingProvince = $r_DwellingCity['ProvinceTitle'];
        $DwellingLandId = null;
    } else if ($Dwelling_CountryId[$i]=="Z102" || $Dwelling_CountryId[$i]=="Z112"){
        $DwellingCityId = $Dwelling_ForeignCitySelect[$i];
        $rs_DwellingCity = $rs->Select('ForeignCity', 'Id='.$DwellingCityId);
        $r_DwellingCity = mysqli_fetch_array($rs_DwellingCity);
        
        $DwellingCity = strtoupper($r_DwellingCity['Title']);
        $DwellingCityId = null;
        $DwellingProvince = null;
        $DwellingLandId = $Dwelling_LandId[$i];
    } else {
        
        $DwellingCity = $Dwelling_CityInput[$i];
        $DwellingCityId = null;
        $DwellingProvince = null;
        $DwellingLandId = null;
    }
    
    //Controlla che siano inseriti indirizzo e città per inserire su db
    if ($Dwelling_Address[$i] != "" && $DwellingCity != ""){
        $a_TrespasserContact = array(
            array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$TrespasserId,'settype'=>'int'),
            array('field'=>'ContactTypeId','selector'=>'value','type'=>'int','value'=>3,'settype'=>'int'),
            array('field'=>'Address','selector'=>'value','type'=>'str','value'=>($Dwelling_Address[$i] != "") ? $Dwelling_Address[$i] : NULL),
            array('field'=>'StreetNumber','selector'=>'value','type'=>'str','value'=>($Dwelling_StreetNumber[$i] != "") ? $Dwelling_StreetNumber[$i] : NULL),
            array('field'=>'Ladder','selector'=>'value','type'=>'str','value'=>($Dwelling_Ladder[$i] != "") ? $Dwelling_Ladder[$i] : NULL),
            array('field'=>'Indoor','selector'=>'value','type'=>'str','value'=>($Dwelling_Indoor[$i] != "") ? $Dwelling_Indoor[$i] : NULL),
            array('field'=>'Plan','selector'=>'value','type'=>'str','value'=>($Dwelling_Plan[$i] != "") ? $Dwelling_Plan[$i] : NULL),
            array('field'=>'ZIP','selector'=>'value','type'=>'str','value'=>($Dwelling_ZIP[$i] != "") ? $Dwelling_ZIP[$i] : NULL),
            array('field'=>'City','selector'=>'value','type'=>'str','value'=>$DwellingCity),
            array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$DwellingCityId),
            array('field'=>'Province','selector'=>'value','type'=>'str','value'=>$DwellingProvince),
            array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>($Dwelling_CountryId[$i] != "") ? $Dwelling_CountryId[$i] : NULL),
            array('field'=>'Mail','selector'=>'value','type'=>'str','value'=>($Dwelling_Mail[$i] != "") ? $Dwelling_Mail[$i] : NULL),
            array('field'=>'Phone','selector'=>'value','type'=>'str','value'=>($Dwelling_Phone[$i] != "") ? $Dwelling_Phone[$i] : NULL),
            array('field'=>'Phone2','selector'=>'value','type'=>'str','value'=>($Dwelling_Phone2[$i] != "") ? $Dwelling_Phone2[$i] : NULL),
            array('field'=>'Fax','selector'=>'value','type'=>'str','value'=>($Dwelling_Fax[$i] != "") ? $Dwelling_Fax[$i] : NULL),
            array('field'=>'PEC','selector'=>'value','type'=>'str','value'=>($Dwelling_PEC[$i] != "") ? $Dwelling_PEC[$i] : NULL),
            array('field'=>'Notes','selector'=>'value','type'=>'str','value'=>$Dwelling_Notes[$i]),
            array('field'=>'ValidUntil','selector'=>'value','type'=>'date','value'=>($Dwelling_ValidUntil[$i] != "") ? $Dwelling_ValidUntil[$i] : NULL),
            array('field'=>'Cds','selector'=>'value','type'=>'int','value'=>isset($_POST['Dwelling_Cds'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Imu','selector'=>'value','type'=>'int','value'=>isset($_POST['Dwelling_Imu'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Tari','selector'=>'value','type'=>'int','value'=>isset($_POST['Dwelling_Tari'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Osap','selector'=>'value','type'=>'int','value'=>isset($_POST['Dwelling_Osap'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Water','selector'=>'value','type'=>'int','value'=>isset($_POST['Dwelling_Water'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Advertising','selector'=>'value','type'=>'int','value'=>isset($_POST['Dwelling_Advertising'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'Others','selector'=>'value','type'=>'int','value'=>isset($_POST['Dwelling_Others'][$i]) ? 1 : 0,'settype'=>'int'),
            array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$_SESSION['username']),
            array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>date("Y-m-d H:i:s")),
        );
        
        if ($DwellingLandId != null)
            $a_TrespasserContact[] = array('field'=>'LandId','selector'=>'value','type'=>'int','value'=>$DwellingLandId,'settype'=>'int');
        else
            $a_TrespasserContact[] = array('field'=>'LandId','selector'=>'value','type'=>'int','value'=>NULL);
        
        //Se trova id pieno nella dimora tra i campi, viene eseguito un update, altrimenti insert
        if ($DwellingId[$i] != "" && $Overwrite == ""){
            
            //STORICO
            $rs_Dwelling = $rs->Select('TrespasserContact',"Id=".$DwellingId[$i]);
            $r_Dwelling = (mysqli_fetch_assoc($rs_Dwelling));
            
            $a_oldContact = array(
                "Address" => StringOutDB($r_Dwelling['Address']),
                "StreetNumber" => StringOutDB($r_Dwelling['StreetNumber']),
                "Ladder" => StringOutDB($r_Dwelling['Ladder']),
                "Indoor" => StringOutDB($r_Dwelling['Indoor']),
                "Plan" => StringOutDB($r_Dwelling['Plan']),
                "ZIP" => StringOutDB($r_Dwelling['ZIP']),
                "City" => StringOutDB($r_Dwelling['City']),
                "CityId" => StringOutDB($r_Dwelling['CityId']),
                "CountryId" => StringOutDB($r_Dwelling['CountryId']),
                "Mail" => StringOutDB($r_Dwelling['Mail']),
                "Phone" => StringOutDB($r_Dwelling['Phone']),
                "Phone2" => StringOutDB($r_Dwelling['Phone2']),
                "Fax" => StringOutDB($r_Dwelling['Fax']),
                "PEC" => StringOutDB($r_Dwelling['PEC']),
                "Notes" => StringOutDB($r_Dwelling['Notes']),
                "ValidUntil" => DateOutDb($r_Dwelling['ValidUntil']),
                "Cds" => $r_Dwelling['Cds'],
                "Imu" => $r_Dwelling['Imu'],
                "Tari" => $r_Dwelling['Tari'],
                "Osap" => $r_Dwelling['Osap'],
                "Water" => $r_Dwelling['Water'],
                "Advertising" => $r_Dwelling['Advertising'],
                "Others" => $r_Dwelling['Others'],
            );
            
            $a_newContact = array(
                "Address" => $Dwelling_Address[$i],
                "StreetNumber" => $Dwelling_StreetNumber[$i],
                "Ladder" => $Dwelling_Ladder[$i],
                "Indoor" => $Dwelling_Indoor[$i],
                "Plan" => $Dwelling_Plan[$i],
                "ZIP" => $Dwelling_ZIP[$i],
                "City" => $DwellingCity,
                "CityId" => $DwellingCityId,
                "CountryId" => $Dwelling_CountryId[$i],
                "Mail" => $Dwelling_Mail[$i],
                "Phone" => $Dwelling_Phone[$i],
                "Phone2" => $Dwelling_Phone2[$i],
                "Fax" => $Dwelling_Fax[$i],
                "PEC" => $Dwelling_PEC[$i],
                "Notes" => $Dwelling_Notes[$i],
                "ValidUntil" => $Dwelling_ValidUntil[$i],
                "Cds" => isset($_POST['Dwelling_Cds'][$i]) ? 1 : 0,
                "Imu" => isset($_POST['Dwelling_Imu'][$i]) ? 1 : 0,
                "Tari" => isset($_POST['Dwelling_Tari'][$i]) ? 1 : 0,
                "Osap" => isset($_POST['Dwelling_Osap'][$i]) ? 1 : 0,
                "Water" => isset($_POST['Dwelling_Water'][$i]) ? 1 : 0,
                "Advertising" => isset($_POST['Dwelling_Advertising'][$i]) ? 1 : 0,
                "Others" => isset($_POST['Dwelling_Others'][$i]) ? 1 : 0,
            );
            
            if ($a_oldContact != $a_newContact){
                //Se La data di validità (se esistente) supera quella corrente
                if($r_Dwelling['ValidUntil'] >= $CurrentDate || $r_Dwelling['ValidUntil'] == NULL){
                    $a_DwellingHistory = array(
                        array('field'=>'TrespasserContactId','selector'=>'value','type'=>'int','value'=>$r_Dwelling['Id'],'settype'=>'int'),
                        array('field'=>'TrespasserId','selector'=>'value','type'=>'int','value'=>$r_Dwelling['TrespasserId'],'settype'=>'int'),
                        array('field'=>'ContactTypeId','selector'=>'value','type'=>'int','value'=>$r_Dwelling['ContactTypeId'],'settype'=>'int'),
                        array('field'=>'Nominative','selector'=>'value','type'=>'str','value'=>$r_Dwelling['Nominative']),
                        array('field'=>'Address','selector'=>'value','type'=>'str','value'=>$r_Dwelling['Address']),
                        array('field'=>'StreetNumber','selector'=>'value','type'=>'str','value'=>$r_Dwelling['StreetNumber']),
                        array('field'=>'Ladder','selector'=>'value','type'=>'str','value'=>$r_Dwelling['Ladder']),
                        array('field'=>'Indoor','selector'=>'value','type'=>'str','value'=>$r_Dwelling['Indoor']),
                        array('field'=>'Plan','selector'=>'value','type'=>'str','value'=>$r_Dwelling['Plan']),
                        array('field'=>'ZIP','selector'=>'value','type'=>'str','value'=>$r_Dwelling['ZIP']),
                        array('field'=>'City','selector'=>'value','type'=>'str','value'=>$r_Dwelling['City']),
                        array('field'=>'CityId','selector'=>'value','type'=>'str','value'=>$r_Dwelling['CityId']),
                        array('field'=>'Province','selector'=>'value','type'=>'str','value'=>$r_Dwelling['Province']),
                        array('field'=>'CountryId','selector'=>'value','type'=>'str','value'=>$r_Dwelling['CountryId']),
                        array('field'=>'Mail','selector'=>'value','type'=>'str','value'=>$r_Dwelling['Mail']),
                        array('field'=>'Phone','selector'=>'value','type'=>'str','value'=>$r_Dwelling['Phone']),
                        array('field'=>'Phone2','selector'=>'value','type'=>'str','value'=>$r_Dwelling['Phone2']),
                        array('field'=>'Fax','selector'=>'value','type'=>'str','value'=>$r_Dwelling['Fax']),
                        array('field'=>'PEC','selector'=>'value','type'=>'str','value'=>$r_Dwelling['PEC']),
                        array('field'=>'Notes','selector'=>'value','type'=>'str','value'=>$r_Dwelling['Notes']),
                        array('field'=>'ValidUntil','selector'=>'value','type'=>'str', 'value'=>$r_Dwelling['ValidUntil']),
                        array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$r_Dwelling['UserId']),
                        array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>$r_Dwelling['VersionDate']),
                    );
                    
                    $rs->Insert('TrespasserContactHistory',$a_DwellingHistory);
                }
            }
            
            $rs->Update('TrespasserContact',$a_TrespasserContact,"Id=".$DwellingId[$i]." AND TrespasserId=$TrespasserId AND ContactTypeId=3");
        } else {
            $rs->Insert('TrespasserContact',$a_TrespasserContact);
        }
    }
}


$rs->End_Transaction();

$_SESSION['Message']['Success'] = 'Azione eseguita con successo.';

header("location: ".impostaParametriUrl(array("Filter" => 1), $BackPage.$Parameters));


