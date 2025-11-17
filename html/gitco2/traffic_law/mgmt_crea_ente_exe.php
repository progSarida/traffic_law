<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

function createDir($dirName,$cityId){
    mkdir(ROOT.'/'.$dirName.'/'.$cityId, 0777);
    chmod(ROOT.'/'.$dirName.'/'.$cityId, 0777);
}

function creaEnteDaEnte($cityId,$oldCityId, $oldCityIdForm){
    $rs= new CLS_DB();
    $rs->Start_Transaction();
    $a_customer = array(
        array('field'=>'CityId', 'selector'=>'value', 'type'=>'str','value'=>$cityId),
        array('field'=>'ManagerName', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'ManagerAddress', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'ManagerZIP', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'ManagerCity', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'ManagerProvince', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'ManagerCountry', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'ManagerPhone', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'ManagerFax', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'ManagerMail', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'ManagerPEC', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'ManagerProcessName', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'ManagerDataEntryName', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'ForeignBankOwner', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'ForeignBankName', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'ForeignBankAccount', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'ForeignBankIban', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'ForeignBankSwift', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'Reference', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'FifthField', 'selector'=>'value', 'type'=>'int', 'value'=>-1),
        array('field'=>'ReturnPlace ', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'NationalMod23LSubject', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'NationalMod23LCustomerName', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'NationalMod23LCustomerSubject', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'NationalMod23LCustomerAddress', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'NationalMod23LCustomerCity', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'ForeignMod23LSubject', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'ForeignMod23LCustomerName', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'ForeignMod23LCustomerSubject', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'ForeignMod23LCustomerAddress', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'ForeignMod23LCustomerCity', 'selector'=>'value', 'type'=>'str', 'value'=>''),
        array('field'=>'IpaCode', 'selector'=>'field', 'type'=>'str'),
        array('field'=>'FinePaymentSpecificationType ', 'selector'=>'value', 'type'=>'int', 'value'=>1)
        );
    $rs->Insert('Customer',$a_customer);

    $rsCurrentUser=$rs->select(MAIN_DB.".User","UserName='".$_SESSION['username']."'");
    $currentUser=mysqli_fetch_array($rsCurrentUser);
    $currentUserId=$currentUser['Id'];
    $a_userCity = array(
        array('field'=>'CityId', 'selector'=>'value', 'type'=>'str','value'=>$cityId),
        array('field'=>'MainMenuId', 'selector'=>'value', 'type'=>'int', 'value'=>'3', 'settype'=>'int'),
        array('field'=>'UserId', 'selector'=>'value', 'type'=>'int', 'value'=>$currentUserId, 'settype'=>'int'),
        array('field'=>'CityYear', 'selector'=>'value', 'type'=>'int', 'value'=>date("Y"), 'settype'=>'int')
        );
    $rs->insert(MAIN_DB.".UserCity",$a_userCity);
    if($currentUser['UserName']!='admin'){
        $rsCurrentUserAdm=$rs->select(MAIN_DB.".User","UserName='admin'");
        $currentUserAdm=mysqli_fetch_array($rsCurrentUserAdm);
        $a_userCity[2]['value']=$currentUserAdm['Id'];
        $rs->insert(MAIN_DB.".UserCity",$a_userCity);
    }

    $insert="INSERT INTO RuleType 
    (Id, CityId, Title, 
    PrintHeaderIta, PrintHeaderEng, PrintHeaderGer, PrintHeaderSpa, PrintHeaderFre, 
    PrintHeaderRom, PrintHeaderPor, PrintHeaderPol, PrintHeaderHol, PrintHeaderAlb, PrintHeaderDen, 
    PrintObjectIta, PrintObjectEng, PrintObjectGer, PrintObjectSpa, PrintObjectFre, 
    PrintObjectRom, PrintObjectPor, PrintObjectPol, PrintObjectHol, PrintObjectAlb, PrintObjectDen) 
    SELECT 
    Id, '".$cityId."', Title, 
    PrintHeaderIta, PrintHeaderEng, PrintHeaderGer, PrintHeaderSpa, PrintHeaderFre, 
    PrintHeaderRom, PrintHeaderPor, PrintHeaderPol, PrintHeaderHol, PrintHeaderAlb, PrintHeaderDen, 
    PrintObjectIta, PrintObjectEng, PrintObjectGer, PrintObjectSpa, PrintObjectFre, 
    PrintObjectRom, PrintObjectPor, PrintObjectPol, PrintObjectHol, PrintObjectAlb, PrintObjectDen 
    FROM 
    RuleType RT2 where RT2.CityId = '".$oldCityId."'";

    $rs->ExecuteQuery($insert);

    $insert="INSERT INTO Reason 
    (Progressive, ViolationTypeId, Fixed, ReasonTypeId, CityId, 
    TitleIta, TitleEng, TitleGer, TitleSpa, TitleFre, TitleRom, TitlePor, TitlePol, TitleHol, TitleAlb, TitleDen, 
    DescriptionIta, Disabled, Code) 
    SELECT 
    Progressive, ViolationTypeId, Fixed, ReasonTypeId, '".$cityId."', 
    TitleIta, TitleEng, TitleGer, TitleSpa, TitleFre, TitleRom, TitlePor, TitlePol, TitleHol, TitleAlb, TitleDen, 
    DescriptionIta, Disabled, Code
    FROM Reason R2 where R2.CityId = '".$oldCityId."' order by R2.Id";
    $rs->ExecuteQuery($insert);

    $insert="INSERT INTO Article 
    (CityId, ArtComune, 
    DescriptionIta, DescriptionEng, DescriptionGer, DescriptionSpa, DescriptionFre, DescriptionRom, 
    DescriptionPor, DescriptionPol, DescriptionHol, DescriptionAlb, DescriptionDen, 
    Article, Paragraph, Letter, ViolationTypeId, Id1, Id2, Id3, Note, AdditionalTextIta, 
    ReasonId, DetectorId, Disabled, Id1Megasp, Id2Megasp, ArticleLetterAssigned) 
    SELECT 
    '".$cityId."', ArtComune,
    DescriptionIta, DescriptionEng, DescriptionGer, DescriptionSpa, DescriptionFre, DescriptionRom, 
    DescriptionPor, DescriptionPol, DescriptionHol, DescriptionAlb, DescriptionDen, 
    Article, Paragraph, Letter, ViolationTypeId, Id1, Id2, Id3, Note, AdditionalTextIta, 
    ReasonId, DetectorId, Disabled, Id1Megasp, Id2Megasp, ArticleLetterAssigned
    FROM Article A2 where A2.CityId = '".$oldCityId."' order by A2.Id";
    $rs->ExecuteQuery($insert);

    $insert="INSERT INTO ArticleTariff 
    (ArticleId, Year, Fee, MaxFee, LicensePoint, YoungLicensePoint, LicensePointCode1, LicensePointCode2, 
        AdditionalSanctionId, UseAdditionalSanction, PresentationDocument, LossLicense,
        AdditionalMass, AdditionalNight, 126Bis, Habitual, ReducedPayment, SuspensionLicense,
        PrefectureFixed, PenalSanction)
    SELECT Anew.Id, Year, Fee, MaxFee, LicensePoint, YoungLicensePoint, LicensePointCode1, LicensePointCode2,
    AdditionalSanctionId, UseAdditionalSanction, PresentationDocument, LossLicense,
    AdditionalMass, AdditionalNight, 126Bis, Habitual, ReducedPayment, SuspensionLicense, 
    PrefectureFixed, PenalSanction
    FROM ArticleTariff ArtT 
    inner join Article A on ArtT.ArticleId = A.Id 
    inner join Article Anew 
    on Anew.DescriptionIta =A.DescriptionIta and Anew.Article = A.Article and Anew.Paragraph = A.Paragraph and Anew.Letter = A.Letter and Anew.ViolationTypeId = A.ViolationTypeId and Anew.Id1 = A.Id1 and Anew.Id2 = A.Id2 and Anew.Id3 = A.Id3
    where A.CityId = '".$oldCityId."' and Anew.CityId = '".$cityId."' and ArtT.Year ='".date("Y")."'";
    $rs->ExecuteQuery($insert);
	
	$insert="INSERT INTO Form
	(FormTypeId, CityId, LanguageId, Content)
	select FormTypeId, '".$cityId."', LanguageId, Content 
	from  Form 
	where CityId ='".$oldCityIdForm."'" ;
	$rs->ExecuteQuery($insert);

	$insert="INSERT INTO FormDynamic
	(NationalityId, RuleTypeId, FormTypeId, CityId, LanguageId, Content, VersionDate, UserId, Title, Deleted)
	select NationalityId, RuleTypeId, FormTypeId, '".$cityId."', LanguageId, Content, VersionDate, UserId, Title, Deleted 
	from FormDynamic
	where CityId='".$oldCityIdForm."'";
	$rs->ExecuteQuery($insert);
	
	$insert="INSERT INTO FormVariable
	( Id,NationalityId, FormTypeId, RuleTypeId, CityId, LanguageId, `Type`, Description, Content, Disabled, VersionDate, UserId)
	select Id,NationalityId, FormTypeId, RuleTypeId, '".$cityId."', LanguageId, `Type`, Description, Content, Disabled, VersionDate, UserId
	from FormVariable
	where CityId='".$oldCityIdForm."'";
	$rs->ExecuteQuery($insert);

	$insert="INSERT INTO FormKeyword
	(NationalityId, RuleTypeId, FormTypeId, CityId, LanguageId, Title, Description, Notes, Disabled, VersionDate, UserId, Deleted)
	select NationalityId, RuleTypeId, FormTypeId, '".$cityId."', LanguageId, Title, Description, Notes, Disabled, VersionDate, UserId, Deleted
	from FormKeyword
	where CityId='".$oldCityIdForm."'";
	$rs->ExecuteQuery($insert);
    $rs->End_Transaction();

}
$cityId = CheckValue('ManagerCityId','s');
$Filters = CheckValue('Filters', 's');

creaEnteDaEnte($cityId,ENTE_BASE, ENTE_BASE_TESTI_DINAMICI);

createDir("doc/foreign/dispute",$cityId);
createDir("doc/foreign/fine",$cityId);
createDir("doc/foreign/flow",$cityId);
createDir("doc/foreign/pec",$cityId);
createDir("doc/foreign/rate",$cityId);
createDir("doc/foreign/request",$cityId);
createDir("doc/foreign/violation",$cityId);
createDir("doc/national",$cityId);
createDir("doc/national/communication",$cityId);
createDir("doc/national/dispute",$cityId);
createDir("doc/national/fine",$cityId);
createDir("doc/national/flow",$cityId);
createDir("doc/national/injunction",$cityId);
createDir("doc/national/pec",$cityId);
createDir("doc/national/rate",$cityId);
createDir("doc/national/request",$cityId);
createDir("doc/national/violation",$cityId);
createDir("public/_PAYMENT_",$cityId);
createDir("public/_REPORT_",$cityId);
createDir("public/_VIOLATION_",$cityId);
createDir("public/_WARNING_",$cityId);
createDir("doc/reclaim/payment",$cityId);
createDir("img/blazon",$cityId);
createDir("img/index",$cityId);
createDir("img/sign",$cityId);

$_SESSION['Message']['Success'] = "Ente $cityId creato correttamente. Aggiungere il logo in img/blazon e l'immagine della home page in img/index. Fare loguot e ripetere il login per vedere il nuovo ente tra le possibili scelte.";

header("location: mgmt_crea_ente.php".$Filters);
?>