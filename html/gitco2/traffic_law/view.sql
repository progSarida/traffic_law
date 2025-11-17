//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////
//
//
//                                        VIOLATION
//
//
//////////////////////////////////////////////////////////////////////////////////////////////$NotificationDate_<////
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_Violation
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_Violation;
CREATE VIEW V_Violation AS
SELECT
F.Id,
F.Code,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.TimeTypeId,
F.ControllerId,
F.ControllerDate,
F.ControllerTime,
F.Locality,
F.StreetTypeId,
F.Address,
F.VehicleTypeId,
F.VehiclePlate,
F.CountryId,
F.VehicleCountry,
F.DepartmentId,
F.VehicleBrand,
F.VehicleModel,
F.VehicleColor,
F.VehicleMass,
F.VehicleLastRevision,
F.PreviousId,
F.Note,
F.ExternalProtocol,
F.ExternalYear,
F.ExternalDate,
F.ExternalTime,
F.ChkControl,
F.ProtocolIdAssigned,
F.KindCreateDate,
F.KindSendDate,



VT.Title ViolationTitle,
VT.RuleTypeId,

FA.ArticleId,
FA.DetectorId,
FA.Speed,
FA.SpeedLimit,
FA.SpeedControl,
FA.ViolationTypeId,
FA.ReasonId,
FA.Fee,
FA.MaxFee,
FA.TimeTLightFirst,
FA.TimeTLightSecond,
FA.ArticleNumber,
FA.ExpirationDate,

ST.Title StatusTitle,

R.TitleIta ReasonTitleIta,
R.TitleEng ReasonTitleEng,
R.TitleGer ReasonTitleGer,
R.TitleSpa ReasonTitleSpa,
R.TitleFre ReasonTitleFre,
R.TitleRom ReasonTitleRom,
R.TitlePor ReasonTitlePor,
R.TitlePol ReasonTitlePol,
R.TitleHol ReasonTitleHol,
R.TitleAlb ReasonTitleAlb,
R.TitleDen ReasonTitleDen,

R.DescriptionIta ReasonDescriptionIta,


VET.TitleIta VehicleTitleIta,
VET.TitleEng VehicleTitleEng,
VET.TitleGer VehicleTitleGer,
VET.TitleSpa VehicleTitleSpa,
VET.TitleFre VehicleTitleFre,
VET.TitleRom VehicleTitleRom,
VET.TitlePor VehicleTitlePor,
VET.TitlePol VehicleTitlePol,
VET.TitleHol VehicleTitleHol,
VET.TitleAlb VehicleTitleAlb,
VET.TitleDen VehicleTitleDen,


A.Article,
A.Paragraph,
A.Letter,
A.Id1,
A.Id2,
A.Id3,
A.DescriptionIta ArticleDescriptionIta,
A.DescriptionEng ArticleDescriptionEng,
A.DescriptionGer ArticleDescriptionGer,
A.DescriptionSpa ArticleDescriptionSpa,
A.DescriptionFre ArticleDescriptionFre,
A.DescriptionRom ArticleDescriptionRom,
A.DescriptionPor ArticleDescriptionPor,
A.DescriptionPol ArticleDescriptionPol,
A.DescriptionHol ArticleDescriptionHol,
A.DescriptionAlb ArticleDescriptionAlb,
A.DescriptionDen ArticleDescriptionDen,



CO.Name ControllerName,
CO.Code ControllerCode,

TT.Title TimeTypeTitle,

FT.TrespasserId,
FT.TrespasserTypeId,

T.CompanyName,
T.Surname,
T.LegalFormId,

STT.Title StreetTypeTitle,

C.Title CityTitle



FROM Fine F
INNER JOIN FineArticle FA ON F.Id=FA.FineId
INNER JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
INNER JOIN StatusType ST ON F.StatusTypeId = ST.Id
INNER JOIN Reason R ON FA.ReasonId = R.Id
INNER JOIN VehicleType VET ON F.VehicleTypeId=VET.Id
INNER JOIN Article A ON A.Id = FA.ArticleId
INNER JOIN sarida.City C ON C.Id = F.Locality
LEFT JOIN Controller CO ON F.ControllerId=CO.Id
INNER JOIN TimeType TT ON TT.Id=F.TimeTypeId
LEFT JOIN StreetType STT ON F.StreetTypeId = STT.Id
LEFT JOIN FineTrespasser FT ON F.Id=FT.FineId
LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id

WHERE (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=2 OR TrespasserTypeId IS NULL)
;


//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_Validation
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_Validation;
CREATE VIEW V_Validation AS
  SELECT
    F.Id,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.TimeTypeId,
    F.ControllerId,
    F.ControllerDate,
    F.ControllerTime,
    F.Locality,
    F.StreetTypeId,
    F.Address,
    F.VehicleTypeId,
    F.VehiclePlate,
    F.CountryId,
    F.VehicleCountry,
    F.DepartmentId,
    F.VehicleBrand,
    F.VehicleModel,
    F.VehicleColor,
    F.VehicleMass,
    F.VehicleLastRevision,
    F.PreviousId,
    F.Note,
    F.ExternalProtocol,
    F.ExternalYear,
    F.ExternalDate,
    F.ExternalTime,



    VT.Title ViolationTitle,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.DetectorId,
    FA.Speed,
    FA.SpeedLimit,
    FA.SpeedControl,
    FA.ViolationTypeId,
    FA.ReasonId,
    FA.Fee,
    FA.MaxFee,
    FA.TimeTLightFirst,
    FA.TimeTLightSecond,
    FA.ArticleNumber,
    FA.ExpirationDate,

    R.TitleIta ReasonTitleIta,
    R.TitleEng ReasonTitleEng,
    R.TitleGer ReasonTitleGer,
    R.TitleSpa ReasonTitleSpa,
    R.TitleFre ReasonTitleFre,
    R.TitleRom ReasonTitleRom,
    R.TitlePor ReasonTitlePor,
    R.TitlePol ReasonTitlePol,
    R.TitleHol ReasonTitleHol,
    R.TitleAlb ReasonTitleAlb,
    R.TitleDen ReasonTitleDen,

    R.DescriptionIta ReasonDescriptionIta,


    VET.TitleIta VehicleTitleIta,
    VET.TitleEng VehicleTitleEng,
    VET.TitleGer VehicleTitleGer,
    VET.TitleSpa VehicleTitleSpa,
    VET.TitleFre VehicleTitleFre,
    VET.TitleRom VehicleTitleRom,
    VET.TitlePor VehicleTitlePor,
    VET.TitlePol VehicleTitlePol,
    VET.TitleHol VehicleTitleHol,
    VET.TitleAlb VehicleTitleAlb,
    VET.TitleDen VehicleTitleDen,


    A.Article,
    A.Paragraph,
    A.Letter,
    A.Id1,
    A.Id2,
    A.Id3,
    A.DescriptionIta ArticleDescriptionIta,
    A.DescriptionEng ArticleDescriptionEng,
    A.DescriptionGer ArticleDescriptionGer,
    A.DescriptionSpa ArticleDescriptionSpa,
    A.DescriptionFre ArticleDescriptionFre,
    A.DescriptionRom ArticleDescriptionRom,
    A.DescriptionPor ArticleDescriptionPor,
    A.DescriptionPol ArticleDescriptionPol,
    A.DescriptionHol ArticleDescriptionHol,
    A.DescriptionAlb ArticleDescriptionAlb,
    A.DescriptionDen ArticleDescriptionDen,



    CO.Name ControllerName,
    CO.Code ControllerCode,

    TT.Title TimeTypeTitle,


    STT.Title StreetTypeTitle,

    C.Title CityTitle



  FROM Fine F
    INNER JOIN FineArticle FA ON F.Id=FA.FineId
    INNER JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
    INNER JOIN Reason R ON FA.ReasonId = R.Id
    INNER JOIN VehicleType VET ON F.VehicleTypeId=VET.Id
    INNER JOIN Article A ON A.Id = FA.ArticleId
    INNER JOIN sarida.City C ON C.Id = F.Locality
    LEFT JOIN Controller CO ON F.ControllerId=CO.Id
    INNER JOIN TimeType TT ON TT.Id=F.TimeTypeId
    LEFT JOIN StreetType STT ON F.StreetTypeId = STT.Id


  WHERE F.StatusTypeId=0;
//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_ViolationArticle
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_ViolationArticle;
CREATE VIEW V_ViolationArticle AS
SELECT
F.Id,
F.Code,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.TimeTypeId,
F.ControllerId,
F.ControllerDate,
F.ControllerTime,
F.Locality,
F.Address,
F.GpsLat,
F.GpsLong,
F.VehicleTypeId,
F.VehiclePlate,
F.CountryId,
F.VehicleCountry,
F.DepartmentId,
F.VehicleBrand,
F.VehicleModel,
F.VehicleColor,
F.VehicleMass,
F.VehicleLastRevision,
F.PreviousId,
F.Note,
F.ProtocolIdAssigned,
F.FineTypeId,
F.IuvCode,
F.PagoPA1,
F.PagoPA2,
F.KindCreateDate,
F.KindSendDate,
F.FineNotificationDate ReportNotificationDate,
F.FineChiefControllerId ReportChiefControllerId,



CO.Qualification  ControllerQualification,
CO.Name ControllerName,
CO.Code ControllerCode,


TMT.Title TimeTitle,
TMT.DescriptionIta TimeDescriptionIta,
TMT.DescriptionEng TimeDescriptionEng,
TMT.DescriptionGer TimeDescriptionGer,
TMT.DescriptionSpa TimeDescriptionSpa,
TMT.DescriptionFre TimeDescriptionFre,
TMT.DescriptionRom TimeDescriptionRom,
TMT.DescriptionPor TimeDescriptionPor,
TMT.DescriptionPol TimeDescriptionPol,
TMT.DescriptionHol TimeDescriptionHol,
TMT.DescriptionAlb TimeDescriptionAlb,
TMT.DescriptionDen TimeDescriptionDen,



VT.Title ViolationTitle,
VT.RuleTypeId,

FA.ArticleId,
FA.DetectorId,
FA.Speed,
FA.SpeedLimit,
FA.SpeedControl,
FA.ViolationTypeId,
FA.ReasonId,
FA.Fee,
FA.MaxFee,
FA.TimeTLightFirst,
FA.TimeTLightSecond,
FA.ArticleNumber,
FA.TrespasserId1_180,
FA.ExpirationDate,


ST.Title StatusTitle,



A.Article,
A.Paragraph,
A.Letter,
A.Id1,
A.Id2,
A.Id3,
A.DescriptionIta ArticleDescriptionIta,
A.DescriptionEng ArticleDescriptionEng,
A.DescriptionGer ArticleDescriptionGer,
A.DescriptionSpa ArticleDescriptionSpa,
A.DescriptionFre ArticleDescriptionFre,
A.DescriptionRom ArticleDescriptionRom,
A.DescriptionPor ArticleDescriptionPor,
A.DescriptionPol ArticleDescriptionPol,
A.DescriptionHol ArticleDescriptionHol,
A.DescriptionAlb ArticleDescriptionAlb,
A.DescriptionDen ArticleDescriptionDen,
A.AdditionalTextIta ArticleAdditionalTextIta,
A.ArticleLetterAssigned,

R.TitleIta ReasonTitleIta,
R.TitleEng ReasonTitleEng,
R.TitleGer ReasonTitleGer,
R.TitleSpa ReasonTitleSpa,
R.TitleFre ReasonTitleFre,
R.TitleRom ReasonTitleRom,
R.TitlePor ReasonTitlePor,
R.TitlePol ReasonTitlePol,
R.TitleHol ReasonTitleHol,
R.TitleAlb ReasonTitleAlb,
R.TitleDen ReasonTitleDen,
R.DescriptionIta ReasonDescriptionIta,

VET.TitleIta VehicleTitleIta,
VET.TitleEng VehicleTitleEng,
VET.TitleGer VehicleTitleGer,
VET.TitleSpa VehicleTitleSpa,
VET.TitleFre VehicleTitleFre,
VET.TitleRom VehicleTitleRom,
VET.TitlePor VehicleTitlePor,
VET.TitlePol VehicleTitlePol,
VET.TitleHol VehicleTitleHol,
VET.TitleAlb VehicleTitleAlb,
VET.TitleDen VehicleTitleDen,

FT.TrespasserId,
FT.TrespasserTypeId,
FT.OwnerAdditionalFee,
FT.CustomerAdditionalFee,
FT.ReceiveDate,

FT.FineCreateDate,
FT.FineSendDate,
FT.FineNotificationDate,

TT.Title TrespasserTitle,

C.Title CityTitle,
C.ZIP CityZIP,

P.Title ProvinceTitle,
P.ShortTitle ProvinceShortTitle,

VTL.ViolationLetterAssigned




FROM Fine F
JOIN FineArticle FA ON F.Id=FA.FineId
JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
LEFT JOIN Controller CO ON F.ControllerId=CO.Id
JOIN TimeType TMT ON TMT.Id=F.TimeTypeId
JOIN StatusType ST ON F.StatusTypeId = ST.Id
JOIN Reason R ON FA.ReasonId = R.Id
JOIN Article A ON A.Id = FA.ArticleId
JOIN sarida.City C ON C.Id = F.Locality
JOIN sarida.Province P ON C.ProvinceId = P.Id
JOIN VehicleType VET ON F.VehicleTypeId=VET.Id
JOIN FineTrespasser FT ON F.Id=FT.FineId
JOIN TrespasserType TT ON FT.TrespasserTypeId=TT.Id
LEFT JOIN ViolationTypeLetter VTL ON VT.Id=VTL.ViolationTypeId aND F.CityId=VTL.CityId

WHERE FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11
OR (FT.TrespasserTypeId=2 AND FT.FineCreateDate IS NULL)
OR (FT.TrespasserTypeId=3 AND FT.FineCreateDate IS NULL)
OR (FT.TrespasserTypeId=15 AND FT.FineCreateDate IS NULL)
OR (FT.TrespasserTypeId=16 AND FT.FineCreateDate IS NULL);







DROP VIEW V_AdditionalArticle;
CREATE VIEW V_AdditionalArticle AS
SELECT
A.CityId,
A.Id,
A.Article,
A.Paragraph,
A.Letter,
A.DescriptionIta AS ArticleDescriptionIta,
A.DescriptionEng AS ArticleDescriptionEng,
A.DescriptionGer AS ArticleDescriptionGer,
A.DescriptionSpa AS ArticleDescriptionSpa,
A.DescriptionFre AS ArticleDescriptionFre,
A.ViolationTypeId,
A.Id1,
A.Id2,
A.Id3,
A.Disabled,

FAA.FineId,
FAA.ArticleId,
FAA.Fee,
FAA.MaxFee,
FAA.ExpirationDate,
FAA.ArticleOrder,
FAA.ArticleDescriptionIta AS AdditionalArticleDescriptionIta,
FAA.ArticleDescriptionEng AS AdditionalArticleDescriptionEng,
FAA.ArticleDescriptionGer AS AdditionalArticleDescriptionGer,
FAA.ArticleDescriptionSpa AS AdditionalArticleDescriptionSpa,
FAA.ArticleDescriptionFre AS AdditionalArticleDescriptionFre,

AT.ReducedPayment,
AT.LicensePoint,
AT.YoungLicensePoint,
AT.126Bis AS 126Bis
FROM  Article A JOIN  FineAdditionalArticle  FAA  ON A.Id = FAA.ArticleId
JOIN Fine F ON F.Id = FAA.FineId
JOIN ArticleTariff AT ON AT.ArticleId = A.Id AND AT.Year = F.ProtocolYear;






//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_TrespasserCommunication
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_TrespasserCommunication;
CREATE VIEW V_TrespasserCommunication  AS  
  SELECT 
    F.Id FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId VehicleCountryId,
    F.ExternalProtocol,
    F.ExternalYear,

    FT.TrespasserTypeId,
    FT.Note,
    FT.OwnerAdditionalFee,
    FT.CustomerAdditionalFee,
    FT.ReceiveDate,

    T.Id TrespasserId,
    T.Genre,
    T.Address,
    T.ZIP,
    T.DocumentTypeId,
    T.CompanyName,
    T.Surname,
    T.Name,
    T.TaxCode,
    T.VatCode,
    T.LicenseCategory,
    T.LicenseNumber,
    T.LicenseDate,
    T.DocumentCountryId,
    T.LicenseOffice,
    T.PEC,
    T.City,
    T.CountryId,
    T.IrideCode,

    C.Title CountryTitle,

    FC.CommunicationDate,
    FC.RegDate,

    T2.Genre GenreDriver,
    T2.Address AddressDriver,
    T2.ZIP ZIPDriver,
    T2.Id TrespasserIdDriver,
    T2.DocumentTypeId DocumentTypeIdDriver,
    T2.CompanyName CompanyNameDriver,
    T2.Surname SurnameDriver,
    T2.Name NameDriver,
    T2.TaxCode TaxCodeDriver,
    T2.VatCode VatCodeDrive,
    T2.LicenseCategory LicenseCategoryDriver,
    T2.LicenseNumber AS LicenseNumberDriver,
    T2.LicenseDate AS LicenseDateDriver,
    T2.DocumentCountryId AS DocumentCountryIdDriver,
    T2.LicenseOffice AS LicenseOfficeDriver,
    T2.PEC AS PECDriver,
    T2.City AS CityDriver,
    T2.CountryId AS CountryIdDriver,
    T2.IrideCode AS IrideCodeDriver,

    C3.Title AS CountryTitleDriver,
    C2.Title AS DocumentCountryTitle,
    C4.Title AS DocumentCountryTitleDriver,

    FC.TrespasserTypeId AS OwnerTypeId,
    FC2.TrespasserTypeId AS DriverTypeId,
    FC.CommunicationProtocol AS CommunicationProtocol

  FROM  Fine F JOIN FineCommunication FC ON F.Id = FC.FineId AND (FC.TrespasserTypeId = 1 OR  FC.TrespasserTypeId = 2)
    JOIN FineTrespasser FT ON F.Id = FT.FineId
    JOIN Trespasser T ON FT.TrespasserId = T.Id
    JOIN Country C ON T.CountryId = C.Id
    LEFT JOIN Country C2 ON T.DocumentCountryId = C2.Id
    LEFT JOIN FineCommunication FC2 ON F.Id = FC2.FineId  AND FC2.TrespasserTypeId = 3
    LEFT JOIN Trespasser T2 ON FC2.TrespasserId = T2.Id
    LEFT JOIN Country C3 ON T2.CountryId = C3.Id
    LEFT JOIN Country C4 ON T2.DocumentCountryId = C4.Id


  WHERE FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2;


//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_AdditionalController
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_AdditionalController;
CREATE VIEW V_AdditionalController AS
  SELECT

    FAC.FineId,
    FAC.ControllerId,

    CO.Qualification  ControllerQualification,
    CO.Name ControllerName,
    CO.Code ControllerCode

    FROM Controller CO JOIN FineAdditionalController FAC ON CO.Id = FAC.ControllerId;



//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_ViolationRent_List
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_ViolationRent_List;
CREATE VIEW V_ViolationRent_List AS
SELECT DISTINCT
F.Id,
F.Code,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.TimeTypeId,
F.ControllerId,
F.ControllerDate,
F.ControllerTime,
F.Locality,
F.StreetTypeId,
F.Address,
F.GpsLat,
F.GpsLong,
F.VehicleTypeId,
F.VehiclePlate,
F.CountryId,
F.VehicleCountry,
F.DepartmentId,
F.VehicleBrand,
F.VehicleModel,
F.VehicleColor,
F.VehicleMass,
F.VehicleLastRevision,
F.PreviousId,
F.Note,
F.ExternalProtocol,
F.ExternalYear,
F.ExternalDate,
F.ExternalTime,



VT.Title ViolationTitle,
VT.RuleTypeId,

FA.ArticleId,
FA.DetectorId,
FA.Speed,
FA.SpeedLimit,
FA.SpeedControl,
FA.ViolationTypeId,
FA.ReasonId,
FA.Fee,
FA.MaxFee,
FA.TimeTLightFirst,
FA.TimeTLightSecond,
FA.ArticleNumber,

ST.Title StatusTitle,

R.TitleIta ReasonTitleIta,
R.TitleEng ReasonTitleEng,
R.TitleGer ReasonTitleGer,
R.TitleSpa ReasonTitleSpa,
R.TitleFre ReasonTitleFre,
R.TitleRom ReasonTitleRom,
R.TitlePor ReasonTitlePor,
R.TitlePol ReasonTitlePol,
R.TitleHol ReasonTitleHol,
R.TitleAlb ReasonTitleAlb,
R.TitleDen ReasonTitleDen,

R.DescriptionIta ReasonDescriptionIta,


VET.TitleIta VehicleTitleIta,
VET.TitleEng VehicleTitleEng,
VET.TitleGer VehicleTitleGer,
VET.TitleSpa VehicleTitleSpa,
VET.TitleFre VehicleTitleFre,
VET.TitleRom VehicleTitleRom,
VET.TitlePor VehicleTitlePor,
VET.TitlePol VehicleTitlePol,
VET.TitleHol VehicleTitleHol,
VET.TitleAlb VehicleTitleAlb,
VET.TitleDen VehicleTitleDen,


A.Article,
A.Paragraph,
A.Letter,
A.Id1,
A.Id2,
A.Id3,
A.DescriptionIta ArticleDescriptionIta,
A.DescriptionEng ArticleDescriptionEng,
A.DescriptionGer ArticleDescriptionGer,
A.DescriptionSpa ArticleDescriptionSpa,
A.DescriptionFre ArticleDescriptionFre,
A.DescriptionRom ArticleDescriptionRom,
A.DescriptionPor ArticleDescriptionPor,
A.DescriptionPol ArticleDescriptionPol,
A.DescriptionHol ArticleDescriptionHol,
A.DescriptionAlb ArticleDescriptionAlb,
A.DescriptionDen ArticleDescriptionDen,

CO.Name ControllerName,
CO.Code ControllerCode,

TT.Title TimeTypeTitle,

STT.Title StreetTypeTitle,

C.Title CityTitle



FROM Fine F
INNER JOIN FineArticle FA ON F.Id=FA.FineId
INNER JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
INNER JOIN StatusType ST ON F.StatusTypeId = ST.Id
INNER JOIN Reason R ON FA.ReasonId = R.Id
INNER JOIN VehicleType VET ON F.VehicleTypeId=VET.Id
INNER JOIN Article A ON A.Id = FA.ArticleId
INNER JOIN sarida.City C ON C.Id = F.Locality
LEFT JOIN Controller CO ON F.ControllerId=CO.Id
INNER JOIN TimeType TT ON TT.Id=F.TimeTypeId
LEFT JOIN StreetType STT ON F.StreetTypeId = STT.Id
LEFT JOIN FineTrespasser FT ON F.Id=FT.FineId


WHERE FT.TrespasserTypeId=10 OR FT.TrespasserTypeId=11;




//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_ViolationAll
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_ViolationAll;
CREATE VIEW V_ViolationAll AS
SELECT
  F.Id,
  F.Code,
  F.CityId,
  F.ProtocolId,
  F.ProtocolYear,
  F.StatusTypeId,
  F.FineDate,
  F.FineTime,
  F.TimeTypeId,
  F.ControllerId,
  F.ControllerDate,
  F.ControllerTime,
  F.Locality,
  F.StreetTypeId,
  F.Address,
  F.GpsLat,
  F.GpsLong,
  F.VehicleTypeId,
  F.VehiclePlate,
  F.CountryId,
  F.VehicleCountry,
  F.DepartmentId,
  F.VehicleBrand,
  F.VehicleModel,
  F.VehicleColor,
  F.VehicleMass,
  F.VehicleLastRevision,
  F.PreviousId,
  F.Note,
  F.ExternalProtocol,
  F.ExternalYear,
  F.ExternalDate,
  F.ExternalTime,
  F.ReminderDate,
  F.KindCreateDate,
  F.KindSendDate,
  F.FineTypeId,
  F.PagoPA1,
  F.PagoPA2,

  VT.Title ViolationTitle,
  VT.RuleTypeId,

  FA.ArticleId,
  FA.DetectorId,
  FA.Speed,
  FA.SpeedLimit,
  FA.SpeedControl,
  FA.ViolationTypeId,
  FA.ReasonId,
  FA.Fee,
  FA.MaxFee,
  FA.TimeTLightFirst,
  FA.TimeTLightSecond,
  FA.ArticleNumber,

  ST.Title StatusTitle,

  R.TitleIta ReasonTitleIta,
  R.TitleEng ReasonTitleEng,
  R.TitleGer ReasonTitleGer,
  R.TitleSpa ReasonTitleSpa,
  R.TitleFre ReasonTitleFre,
  R.TitleRom ReasonTitleRom,
  R.TitlePor ReasonTitlePor,
  R.TitlePol ReasonTitlePol,
  R.TitleHol ReasonTitleHol,
  R.TitleAlb ReasonTitleAlb,
  R.TitleDen ReasonTitleDen,

  R.DescriptionIta ReasonDescriptionIta,


  VET.TitleIta VehicleTitleIta,
  VET.TitleEng VehicleTitleEng,
  VET.TitleGer VehicleTitleGer,
  VET.TitleSpa VehicleTitleSpa,
  VET.TitleFre VehicleTitleFre,
  VET.TitleRom VehicleTitleRom,
  VET.TitlePor VehicleTitlePor,
  VET.TitlePol VehicleTitlePol,
  VET.TitleHol VehicleTitleHol,
  VET.TitleAlb VehicleTitleAlb,
  VET.TitleDen VehicleTitleDen,


  A.Article,
  A.Paragraph,
  A.Letter,
  A.Id1,
  A.Id2,
  A.Id3,
  A.DescriptionIta ArticleDescriptionIta,
  A.DescriptionEng ArticleDescriptionEng,
  A.DescriptionGer ArticleDescriptionGer,
  A.DescriptionSpa ArticleDescriptionSpa,
  A.DescriptionFre ArticleDescriptionFre,
  A.DescriptionRom ArticleDescriptionRom,
  A.DescriptionPor ArticleDescriptionPor,
  A.DescriptionPol ArticleDescriptionPol,
  A.DescriptionHol ArticleDescriptionHol,
  A.DescriptionAlb ArticleDescriptionAlb,
  A.DescriptionDen ArticleDescriptionDen,

  CO.Name ControllerName,
  CO.Code ControllerCode,

  TT.Title TimeTypeTitle,

  FT.TrespasserId,
  FT.TrespasserTypeId,

  T.PEC,
  T.TaxCode,
  T.VatCode,

  STT.Title StreetTypeTitle,

  C.Title CityTitle



FROM Fine F
INNER JOIN FineArticle FA ON F.Id=FA.FineId
INNER JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
INNER JOIN StatusType ST ON F.StatusTypeId = ST.Id
INNER JOIN Reason R ON FA.ReasonId = R.Id
INNER JOIN VehicleType VET ON F.VehicleTypeId=VET.Id
INNER JOIN Article A ON A.Id = FA.ArticleId
INNER JOIN sarida.City C ON C.Id = F.Locality
LEFT JOIN Controller CO ON F.ControllerId=CO.Id
INNER JOIN TimeType TT ON TT.Id=F.TimeTypeId
INNER JOIN FineTrespasser FT ON F.Id=FT.FineId
INNER JOIN Trespasser T ON FT.TrespasserId=T.Id
LEFT JOIN StreetType STT ON F.StreetTypeId = STT.Id


WHERE FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11
OR (FT.TrespasserTypeId=2 AND FT.FineCreateDate IS NULL)
OR (FT.TrespasserTypeId=3 AND FT.FineCreateDate IS NULL)
OR (FT.TrespasserTypeId=15 AND FT.FineCreateDate IS NULL)
OR (FT.TrespasserTypeId=16 AND FT.FineCreateDate IS NULL);





DROP VIEW V_UserMenu;
CREATE VIEW V_UserMenu AS
SELECT MM.Id,
MM.Description,
MM.Path,
MM.Disabled MainMenuDisabled,
MM.MenuOrder,
MM.Icon,
UM.UserId,
UM.MainMenuID,
UM.Disabled UserMenuDisabled

FROM MainMenu MM LEFT JOIN UserMenu UM
ON MM.Id = UM.MainMenuID

WHERE (UM.Disabled=0 OR UM.Disabled IS NULL) AND MM.Disabled=0;





//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_ViolationRent
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_ViolationRent;
CREATE VIEW V_ViolationRent AS
SELECT DISTINCT
F.Id,
F.Code,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.TimeTypeId,
F.ControllerId,
F.ControllerDate,
F.ControllerTime,
F.Locality,
F.StreetTypeId,
F.Address,
F.VehicleTypeId,
F.VehiclePlate,
F.CountryId,
F.VehicleCountry,
F.DepartmentId,
F.VehicleBrand,
F.VehicleModel,
F.VehicleColor,
F.VehicleMass,
F.VehicleLastRevision,
F.PreviousId,
F.Note,
F.ExternalProtocol,
F.ExternalYear,
F.ExternalDate,
F.ExternalTime,



VT.Title ViolationTitle,
VT.RuleTypeId,

FA.ArticleId,
FA.DetectorId,
FA.Speed,
FA.SpeedLimit,
FA.SpeedControl,
FA.ViolationTypeId,
FA.ReasonId,
FA.Fee,
FA.MaxFee,
FA.TimeTLightFirst,
FA.TimeTLightSecond,
FA.ArticleNumber,


ST.Title StatusTitle,

R.TitleIta ReasonTitleIta,
R.TitleEng ReasonTitleEng,
R.TitleGer ReasonTitleGer,
R.TitleSpa ReasonTitleSpa,
R.TitleFre ReasonTitleFre,
R.TitleRom ReasonTitleRom,
R.TitlePor ReasonTitlePor,
R.TitlePol ReasonTitlePol,
R.TitleHol ReasonTitleHol,
R.TitleAlb ReasonTitleAlb,
R.TitleDen ReasonTitleDen,

R.DescriptionIta ReasonDescriptionIta,


VET.TitleIta VehicleTitleIta,
VET.TitleEng VehicleTitleEng,
VET.TitleGer VehicleTitleGer,
VET.TitleSpa VehicleTitleSpa,
VET.TitleFre VehicleTitleFre,
VET.TitleRom VehicleTitleRom,
VET.TitlePor VehicleTitlePor,
VET.TitlePol VehicleTitlePol,
VET.TitleHol VehicleTitleHol,
VET.TitleAlb VehicleTitleAlb,
VET.TitleDen VehicleTitleDen,


A.Article,
A.Paragraph,
A.Letter,
A.Id1,
A.Id2,
A.Id3,
A.DescriptionIta ArticleDescriptionIta,
A.DescriptionEng ArticleDescriptionEng,
A.DescriptionGer ArticleDescriptionGer,
A.DescriptionSpa ArticleDescriptionSpa,
A.DescriptionFre ArticleDescriptionFre,
A.DescriptionRom ArticleDescriptionRom,
A.DescriptionPor ArticleDescriptionPor,
A.DescriptionPol ArticleDescriptionPol,
A.DescriptionHol ArticleDescriptionHol,
A.DescriptionAlb ArticleDescriptionAlb,
A.DescriptionDen ArticleDescriptionDen,

CO.Name ControllerName,
CO.Code ControllerCode,

TT.Title TimeTypeTitle,


T.CompanyName,
T.Surname,

STT.Title StreetTypeTitle,

C.Title CityTitle



FROM Fine F
INNER JOIN FineArticle FA ON F.Id=FA.FineId
INNER JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
INNER JOIN StatusType ST ON F.StatusTypeId = ST.Id
INNER JOIN Reason R ON FA.ReasonId = R.Id
INNER JOIN VehicleType VET ON F.VehicleTypeId=VET.Id
INNER JOIN Article A ON A.Id = FA.ArticleId
INNER JOIN sarida.City C ON C.Id = F.Locality
LEFT JOIN Controller CO ON F.ControllerId=CO.Id
INNER JOIN TimeType TT ON TT.Id=F.TimeTypeId
LEFT JOIN StreetType STT ON F.StreetTypeId = STT.Id
LEFT JOIN FineTrespasser FT ON F.Id=FT.FineId
LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id

WHERE FT.TrespasserTypeId=10 OR FT.TrespasserTypeId=11;






//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_ViolationQuery
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_ViolationQuery;
CREATE VIEW V_ViolationQuery AS
SELECT
F.Id,
F.Code,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.ControllerId,
F.ControllerDate,
F.ControllerTime,
F.Locality,
F.Address,
F.VehicleTypeId,
F.VehiclePlate,
F.VehicleCountry,
F.CountryId,
F.VehicleBrand,
F.VehicleModel,
F.VehicleColor,
F.VehicleLastRevision,
F.PreviousId,
F.Note,

VT.Title ViolationTitle,
VT.RuleTypeId,

FA.ArticleId,
FA.DetectorId,
FA.Speed,
FA.SpeedLimit,
FA.SpeedControl,
FA.ViolationTypeId,
FA.ReasonId,
FA.Fee,
FA.MaxFee,
FA.TimeTLightFirst,
FA.TimeTLightSecond,
FA.ArticleNumber


FROM
Fine F JOIN FineArticle FA ON F.Id=FA.FineId
JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId

WHERE
StatusTypeId =1 AND FT.TrespasserId IS NULL;





























































//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////
//
//
//                                        ARTICLE
//
//
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////


DROP VIEW V_Article;
CREATE VIEW V_Article AS
  SELECT
    A.CityId,
    A.Id,
    A.ArtComune,
    A.Article,
    A.Paragraph,
    A.Letter,
    A.DescriptionIta ArticleDescriptionIta,
    A.DescriptionEng ArticleDescriptionEng,
    A.DescriptionGer ArticleDescriptionGer,
    A.DescriptionSpa ArticleDescriptionSpa,
    A.DescriptionFre ArticleDescriptionFre,
    A.DescriptionRom ArticleDescriptionRom,
    A.DescriptionPor ArticleDescriptionPor,
    A.DescriptionPol ArticleDescriptionPol,
    A.DescriptionHol ArticleDescriptionHol,
    A.DescriptionAlb ArticleDescriptionAlb,
    A.DescriptionDen ArticleDescriptionDen,
    A.AdditionalTextIta,
    A.ViolationTypeId,
    A.Id1,
    A.Id2,
    A.Id3,
    A.Disabled,
    A.Note,
    A.ArticleLetterAssigned,

    AT.Year,
    AT.Fee,
    AT.MaxFee,
    AT.ReducedPayment,
    AT.AdditionalNight,
    AT.PresentationDocument,
    AT.126Bis,
    AT.AdditionalMass,
    AT.LicensePoint,
    AT.YoungLicensePoint,
    AT.SuspensionLicense,
    AT.LossLicense,
    AT.Habitual,

    VT.Title ViolationTitle,
    VT.RuleTypeId,

    ASA.Id AdditionalSanctionId,
    ASA.TitleIta AdditionalSanctionTitleIta

FROM Article A
JOIN ArticleTariff AT ON A.Id = AT.ArticleId
JOIN ViolationType VT ON A.ViolationTypeId = VT.Id
LEFT JOIN AdditionalSanction ASA ON AT.AdditionalSanctionId = ASA.Id
;


DROP VIEW V_LicensePoint0;
CREATE VIEW V_LicensePoint0 AS
  SELECT
    F.Id,
    F.StatustypeId,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.FineDate,
    F.VehiclePlate,
    F.CountryId,
    F.FineTypeId,

    FC.TrespasserId,
    FC.TrespasserTypeId,
    FC.CommunicationProtocol,
    FC.CommunicationDate,
    FC.ReducedDate,
    FC.ReducedPoint,
    FC.CommunicationStatus,

    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter,

    AT.LicensePoint,
    AT.YoungLicensePoint,

    T.Surname,
    T.Name,
    T.TaxCode,
    T.VatCode,
    T.ZIP,
    T.LicenseNumber,
    T.DocumentTypeId,
    T.DocumentCountryId,
    T.LicenseDate,
    T.LicenseCategory,
    T.LicenseOffice,

    FN.NotificationDate,

    TIMESTAMPDIFF(YEAR, T.LicenseDate, F.FineDate) LicenseYear,

    LPM.Description LicensePointMexDescription

  FROM
    Fine F
    JOIN FineCommunication FC ON F.Id = FC.FineId
    JOIN Trespasser T ON FC.TrespasserId = T.Id
    JOIN FineArticle FA ON F.Id=FA.FineId
    JOIN Article A ON FA.ArticleId = A.Id
    JOIN ArticleTariff AT ON FA.ArticleId = AT.ArticleId AND F.ProtocolYear = AT.Year
    LEFT JOIN FineNotification FN ON F.Id = FN.FineId
    LEFT JOIN LicensePointMex LPM ON FC.LicensePointId = LPM.Id

  WHERE  FN.LicensePointProcedure>0 AND
         (FC.TrespasserTypeId =1 OR FC.TrespasserTypeId=3)
         AND F.Id NOT IN (SELECT FineId FROM TMP_LicensePointProcedure)
         AND F.Id NOT IN (SELECT FineId FROM FineDispute WHERE DisputeStatusId=1 OR DisputeStatusId=4)
        AND (TIMESTAMPDIFF(DAY, FN.NotificationDate, CURDATE())>60 OR FN.NotificationDate IS NULL)
        AND ((F.StatusTypeId>=25 AND F.StatusTypeId<=30) OR F.StatusTypeId>=40)
;

DROP VIEW V_LicensePoint1;
CREATE VIEW V_LicensePoint1 AS
  SELECT
    F.Id,
    F.StatustypeId,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.FineDate,
    F.VehiclePlate,
    F.CountryId,
    F.FineTypeId,

    FC.TrespasserId,
    FC.TrespasserTypeId,
    FC.CommunicationProtocol,
    FC.CommunicationDate,
    FC.ReducedDate,
    FC.ReducedPoint,
    FC.CommunicationStatus,

    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter,

    AT.LicensePoint,
    AT.YoungLicensePoint,

    T.Surname,
    T.Name,
    T.TaxCode,
    T.VatCode,
    T.ZIP,
    T.LicenseNumber,
    T.DocumentTypeId,
    T.DocumentCountryId,
    T.LicenseDate,
    T.LicenseCategory,
    T.LicenseOffice,

    FN.NotificationDate,

    FP.PaymentDate,

    TIMESTAMPDIFF(YEAR, T.LicenseDate, F.FineDate) LicenseYear,

    LPM.Description LicensePointMexDescription

  FROM
    Fine F
    JOIN FineCommunication FC ON F.Id = FC.FineId
    JOIN Trespasser T ON FC.TrespasserId = T.Id
    JOIN FineArticle FA ON F.Id=FA.FineId
    JOIN Article A ON FA.ArticleId = A.Id
    JOIN ArticleTariff AT ON FA.ArticleId = AT.ArticleId AND F.ProtocolYear = AT.Year
    LEFT JOIN FineNotification FN ON F.Id = FN.FineId
    LEFT JOIN FinePayment FP ON F.Id = FP.FineId
    LEFT JOIN LicensePointMex LPM ON FC.LicensePointId = LPM.Id

  WHERE  (FN.LicensePointProcedure>0 OR FN.LicensePointProcedure IS NULL) AND
         (FC.TrespasserTypeId =1 OR FC.TrespasserTypeId=3)
         AND F.Id NOT IN (SELECT FineId FROM TMP_LicensePointProcedure)
         AND F.Id NOT IN (SELECT FineId FROM FineDispute WHERE DisputeStatusId=1 OR DisputeStatusId=4)
         AND (TIMESTAMPDIFF(DAY, FN.NotificationDate, CURDATE())>60 OR FP.PaymentDate IS NOT NULL)
         AND ((F.StatusTypeId>10 AND F.StatusTypeId<=30) OR F.StatusTypeId>=40)
;

//TIMESTAMPDIFF(YEAR, date_modified, CURDATE())






DROP VIEW V_ArticleCity;
CREATE VIEW V_ArticleCity AS
SELECT
A.CityId,
A.Id,
A.ArtComune,
A.Article,
A.Paragraph,
A.Letter,
A.DescriptionIta ArticleDescriptionIta,
A.DescriptionEng ArticleDescriptionEng,
A.DescriptionGer ArticleDescriptionGer,
A.DescriptionSpa ArticleDescriptionSpa,
A.DescriptionFre ArticleDescriptionFre,
A.DescriptionRom ArticleDescriptionRom,
A.DescriptionPor ArticleDescriptionPor,
A.DescriptionPol ArticleDescriptionPol,
A.DescriptionHol ArticleDescriptionHol,
A.DescriptionAlb ArticleDescriptionAlb,
A.DescriptionDen ArticleDescriptionDen,

A.ViolationTypeId,
A.Id1,
A.Id2,
A.Id3,

A.ArticleLetterAssigned,

A.Disabled,


C.Title AS CityTitle,

VT.Title ViolationTitle,
VT.RuleTypeId

FROM Article A JOIN sarida.City C on A.CityId=C.Id
JOIN ViolationType VT ON A.ViolationTypeId = VT.Id;


























































































//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////
//
//
//                                        FINE
//
//
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////


//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_Fine
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_Fine;
CREATE VIEW V_Fine AS
SELECT
F.Id,
F.Code,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.TimeTypeId,
F.ControllerId,
F.ControllerDate,
F.ControllerTime,
F.Locality,
F.StreetTypeId,
F.Address,
F.VehicleTypeId,
F.VehiclePlate,
F.CountryId,
F.VehicleCountry,
F.DepartmentId,
F.VehicleBrand,
F.VehicleModel,
F.VehicleColor,
F.VehicleMass,
F.VehicleLastRevision,
F.PreviousId,
F.Note,
F.ExternalProtocol,
F.ExternalYear,
F.ExternalDate,
F.ExternalTime,
F.KindCreateDate,
F.KindSendDate,
F.ChkControl,
F.NoteProcedure,
F.RegDate,
F.UserId,
F.FineTypeId,
F.FineChiefControllerId,
F.FineNotificationDate,

VT.Title ViolationTitle,
VT.RuleTypeId,

FA.ArticleId,
FA.DetectorId,
FA.Speed,
FA.SpeedLimit,
FA.SpeedControl,
FA.ViolationTypeId,
FA.ReasonId,
FA.Fee,
FA.MaxFee,
FA.TimeTLightFirst,
FA.TimeTLightSecond,
FA.ArticleNumber,
FA.ExpirationDate,

ST.Title StatusTitle,

R.TitleIta ReasonTitleIta,
R.TitleEng ReasonTitleEng,
R.TitleGer ReasonTitleGer,
R.TitleSpa ReasonTitleSpa,
R.TitleFre ReasonTitleFre,
R.TitleRom ReasonTitleRom,
R.TitlePor ReasonTitlePor,
R.TitlePol ReasonTitlePol,
R.TitleHol ReasonTitleHol,
R.TitleAlb ReasonTitleAlb,
R.TitleDen ReasonTitleDen,
R.DescriptionIta ReasonDescriptionIta,


VET.TitleIta VehicleTitleIta,
VET.TitleEng VehicleTitleEng,
VET.TitleGer VehicleTitleGer,
VET.TitleSpa VehicleTitleSpa,
VET.TitleFre VehicleTitleFre,
VET.TitleRom VehicleTitleRom,
VET.TitlePor VehicleTitlePor,
VET.TitlePol VehicleTitlePol,
VET.TitleHol VehicleTitleHol,
VET.TitleAlb VehicleTitleAlb,
VET.TitleDen VehicleTitleDen,


A.Article,
A.Paragraph,
A.Letter,
A.Id1,
A.Id2,
A.Id3,
A.DescriptionIta ArticleDescriptionIta,
A.DescriptionEng ArticleDescriptionEng,
A.DescriptionGer ArticleDescriptionGer,
A.DescriptionSpa ArticleDescriptionSpa,
A.DescriptionFre ArticleDescriptionFre,
A.DescriptionRom ArticleDescriptionRom,
A.DescriptionPor ArticleDescriptionPor,
A.DescriptionPol ArticleDescriptionPol,
A.DescriptionHol ArticleDescriptionHol,
A.DescriptionAlb ArticleDescriptionAlb,
A.DescriptionDen ArticleDescriptionDen,


CO.Name ControllerName,
CO.Code ControllerCode,

TT.Title TimeTypeTitle,

FT.TrespasserId,
FT.TrespasserTypeId,

T.CompanyName,
T.Surname,

STT.Title StreetTypeTitle,

C.Title CityTitle



FROM Fine F
INNER JOIN FineArticle FA ON F.Id=FA.FineId
INNER JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
INNER JOIN StatusType ST ON F.StatusTypeId = ST.Id
INNER JOIN Reason R ON FA.ReasonId = R.Id
INNER JOIN VehicleType VET ON F.VehicleTypeId=VET.Id
INNER JOIN Article A ON A.Id = FA.ArticleId
INNER JOIN sarida.City C ON C.Id = F.Locality
INNER JOIN Controller CO ON F.ControllerId=CO.Id
INNER JOIN TimeType TT ON TT.Id=F.TimeTypeId
LEFT JOIN StreetType STT ON F.StreetTypeId = STT.Id
LEFT JOIN FineTrespasser FT ON F.Id=FT.FineId
LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id

WHERE FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=2 OR TrespasserTypeId IS NULL;










//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineAll
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FineAll;
CREATE VIEW V_FineAll AS
SELECT
F.Id,
F.Code,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.TimeTypeId,
F.ControllerId,
F.ControllerDate,
F.ControllerTime,
F.Locality,
F.StreetTypeId,
F.Address,
F.VehicleTypeId,
F.VehiclePlate,
F.CountryId,
F.VehicleCountry,
F.DepartmentId,
F.VehicleBrand,
F.VehicleModel,
F.VehicleColor,
F.VehicleMass,
F.VehicleLastRevision,
F.PreviousId,
F.Note,
F.ExternalProtocol,
F.ExternalYear,
F.ExternalDate,
F.ExternalTime,
F.ProtocolIdAssigned,
F.RegDate,

VT.Title ViolationTitle,
VT.RuleTypeId,

FA.ArticleId,
FA.DetectorId,
FA.Speed,
FA.SpeedLimit,
FA.SpeedControl,
FA.ViolationTypeId,
FA.ReasonId,
FA.Fee,
FA.MaxFee,
FA.TimeTLightFirst,
FA.TimeTLightSecond,
FA.ArticleNumber,


ST.Title StatusTitle,

R.TitleIta ReasonTitleIta,
R.TitleEng ReasonTitleEng,
R.TitleGer ReasonTitleGer,
R.TitleSpa ReasonTitleSpa,
R.TitleFre ReasonTitleFre,
R.TitleRom ReasonTitleRom,
R.TitlePor ReasonTitlePor,
R.TitlePol ReasonTitlePol,
R.TitleHol ReasonTitleHol,
R.TitleAlb ReasonTitleAlb,
R.TitleDen ReasonTitleDen,

R.DescriptionIta ReasonDescriptionIta,


VET.TitleIta VehicleTitleIta,
VET.TitleEng VehicleTitleEng,
VET.TitleGer VehicleTitleGer,
VET.TitleSpa VehicleTitleSpa,
VET.TitleFre VehicleTitleFre,
VET.TitleRom VehicleTitleRom,
VET.TitlePor VehicleTitlePor,
VET.TitlePol VehicleTitlePol,
VET.TitleHol VehicleTitleHol,
VET.TitleAlb VehicleTitleAlb,
VET.TitleDen VehicleTitleDen,


A.Article,
A.Paragraph,
A.Letter,
A.Id1,
A.Id2,
A.Id3,
A.DescriptionIta ArticleDescriptionIta,
A.DescriptionEng ArticleDescriptionEng,
A.DescriptionGer ArticleDescriptionGer,
A.DescriptionSpa ArticleDescriptionSpa,
A.DescriptionFre ArticleDescriptionFre,
A.DescriptionRom ArticleDescriptionRom,
A.DescriptionPor ArticleDescriptionPor,
A.DescriptionPol ArticleDescriptionPol,
A.DescriptionHol ArticleDescriptionHol,
A.DescriptionAlb ArticleDescriptionAlb,
A.DescriptionDen ArticleDescriptionDen,

CO.Name ControllerName,
CO.Code ControllerCode,

TT.Title TimeTypeTitle,

FT.TrespasserId,
FT.TrespasserTypeId,


STT.Title StreetTypeTitle,

C.Title CityTitle



FROM Fine F
INNER JOIN FineArticle FA ON F.Id=FA.FineId
INNER JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
INNER JOIN StatusType ST ON F.StatusTypeId = ST.Id
INNER JOIN Reason R ON FA.ReasonId = R.Id
INNER JOIN VehicleType VET ON F.VehicleTypeId=VET.Id
INNER JOIN Article A ON A.Id = FA.ArticleId
INNER JOIN sarida.City C ON C.Id = F.Locality
INNER JOIN Controller CO ON F.ControllerId=CO.Id
INNER JOIN TimeType TT ON TT.Id=F.TimeTypeId
LEFT JOIN FineTrespasser FT ON F.Id=FT.FineId
LEFT JOIN StreetType STT ON F.StreetTypeId = STT.Id


WHERE FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL;




//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_Warning
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_Warning;
CREATE VIEW V_Warning AS
  SELECT
    F.Id,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.TimeTypeId,
    F.ControllerId,
    F.ControllerDate,
    F.ControllerTime,
    F.Locality,
    F.StreetTypeId,
    F.Address,
    F.VehicleTypeId,
    F.VehiclePlate,
    F.CountryId,
    F.VehicleCountry,
    F.DepartmentId,
    F.VehicleBrand,
    F.VehicleModel,
    F.VehicleColor,
    F.VehicleMass,
    F.VehicleLastRevision,
    F.PreviousId,
    F.Note,
    F.ExternalProtocol,
    F.ExternalYear,
    F.ExternalDate,
    F.ExternalTime,



    VT.Title ViolationTitle,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.DetectorId,
    FA.Speed,
    FA.SpeedLimit,
    FA.SpeedControl,
    FA.ViolationTypeId,
    FA.ReasonId,
    FA.Fee,
    FA.MaxFee,
    FA.TimeTLightFirst,
    FA.TimeTLightSecond,
    FA.ArticleNumber,

    ST.Title StatusTitle,

    R.TitleIta ReasonTitleIta,
    R.TitleEng ReasonTitleEng,
    R.TitleGer ReasonTitleGer,
    R.TitleSpa ReasonTitleSpa,
    R.TitleFre ReasonTitleFre,
    R.TitleRom ReasonTitleRom,
    R.TitlePor ReasonTitlePor,
    R.TitlePol ReasonTitlePol,
    R.TitleHol ReasonTitleHol,
    R.TitleAlb ReasonTitleAlb,
    R.TitleDen ReasonTitleDen,

    R.DescriptionIta ReasonDescriptionIta,


    VET.TitleIta VehicleTitleIta,
    VET.TitleEng VehicleTitleEng,
    VET.TitleGer VehicleTitleGer,
    VET.TitleSpa VehicleTitleSpa,
    VET.TitleFre VehicleTitleFre,
    VET.TitleRom VehicleTitleRom,
    VET.TitlePor VehicleTitlePor,
    VET.TitlePol VehicleTitlePol,
    VET.TitleHol VehicleTitleHol,
    VET.TitleAlb VehicleTitleAlb,
    VET.TitleDen VehicleTitleDen,


    A.Article,
    A.Paragraph,
    A.Letter,
    A.Id1,
    A.Id2,
    A.Id3,
    A.DescriptionIta ArticleDescriptionIta,
    A.DescriptionEng ArticleDescriptionEng,
    A.DescriptionGer ArticleDescriptionGer,
    A.DescriptionSpa ArticleDescriptionSpa,
    A.DescriptionFre ArticleDescriptionFre,
    A.DescriptionRom ArticleDescriptionRom,
    A.DescriptionPor ArticleDescriptionPor,
    A.DescriptionPol ArticleDescriptionPol,
    A.DescriptionHol ArticleDescriptionHol,
    A.DescriptionAlb ArticleDescriptionAlb,
    A.DescriptionDen ArticleDescriptionDen,

    CO.Name ControllerName,
    CO.Code ControllerCode,

    TT.Title TimeTypeTitle,

    FT.TrespasserId,
    FT.TrespasserTypeId,


    STT.Title StreetTypeTitle,

    C.Title CityTitle



  FROM Fine F
    INNER JOIN FineArticle FA ON F.Id=FA.FineId
    INNER JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
    INNER JOIN StatusType ST ON F.StatusTypeId = ST.Id
    INNER JOIN Reason R ON FA.ReasonId = R.Id
    INNER JOIN VehicleType VET ON F.VehicleTypeId=VET.Id
    INNER JOIN Article A ON A.Id = FA.ArticleId
    INNER JOIN sarida.City C ON C.Id = F.Locality
    INNER JOIN Controller CO ON F.ControllerId=CO.Id
    INNER JOIN TimeType TT ON TT.Id=F.TimeTypeId
    LEFT JOIN FineTrespasser FT ON F.Id=FT.FineId
    LEFT JOIN StreetType STT ON F.StreetTypeId = STT.Id


  WHERE
    F.FineTypeId = 2 AND
    FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2
        OR FT.TrespasserTypeId IS NULL;








DROP VIEW V_FineTrespasserList;
CREATE VIEW V_FineTrespasserList  AS
  SELECT
    F.Id AS FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.Locality,
    F.Locality AS LocalityAddress,

    FA.Speed,
    FA.SpeedLimit,
    FA.SpeedControl,

    T.Id AS TrespasserId,
    T.Genre,
    T.CompanyName,
    T.Surname,
    T.Name,
    T.Address,
    T.ZIP,
    T.City,
    T.Province,
    T.CountryId,
    T.BornPlace,
    T.BornDate,
    T.TaxCode,
    T.VatCode,
    T.Phone,
    T.Mail,

    C.Title AS CountryTitle,
    A.Article,
    A.Paragraph,
    A.Letter

    FROM Fine F join FineTrespasser FT on F.Id = FT.FineId  join Trespasser T on FT.TrespasserId = T.Id  join Country C on T.CountryId = C.Id   join FineArticle FA on F.Id = FA.FineId
                                          join Article A on FA.ArticleId = A.Id;



//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineRent
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FineRent;
CREATE VIEW V_FineRent AS
SELECT DISTINCT
  F.Id,
  F.Code,
  F.CityId,
  F.ProtocolId,
  F.ProtocolYear,
  F.StatusTypeId,
  F.FineDate,
  F.FineTime,
  F.TimeTypeId,
  F.ControllerId,
  F.ControllerDate,
  F.ControllerTime,
  F.Locality,
  F.StreetTypeId,
  F.Address,
  F.VehicleTypeId,
  F.VehiclePlate,
  F.CountryId,
  F.VehicleCountry,
  F.DepartmentId,
  F.VehicleBrand,
  F.VehicleModel,
  F.VehicleColor,
  F.VehicleMass,
  F.VehicleLastRevision,
  F.PreviousId,
  F.Note,
  F.ExternalProtocol,
  F.ExternalYear,
  F.ExternalDate,
  F.ExternalTime,
  F.KindCreateDate,
  F.KindSendDate,
  F.NoteProcedure,
  F.ChkControl,




  VT.Title ViolationTitle,
  VT.RuleTypeId,

  FA.ArticleId,
  FA.DetectorId,
  FA.Speed,
  FA.SpeedLimit,
  FA.SpeedControl,
  FA.ViolationTypeId,
  FA.ReasonId,
  FA.Fee,
  FA.MaxFee,
  FA.TimeTLightFirst,
  FA.TimeTLightSecond,
  FA.ArticleNumber,

  ST.Title StatusTitle,

  R.TitleIta ReasonTitleIta,
  R.TitleEng ReasonTitleEng,
  R.TitleGer ReasonTitleGer,
  R.TitleSpa ReasonTitleSpa,
  R.TitleFre ReasonTitleFre,
  R.TitleRom ReasonTitleRom,
  R.TitlePor ReasonTitlePor,
  R.TitlePol ReasonTitlePol,
  R.TitleHol ReasonTitleHol,
  R.TitleAlb ReasonTitleAlb,
  R.TitleDen ReasonTitleDen,

  R.DescriptionIta ReasonDescriptionIta,


  VET.TitleIta VehicleTitleIta,
  VET.TitleEng VehicleTitleEng,
  VET.TitleGer VehicleTitleGer,
  VET.TitleSpa VehicleTitleSpa,
  VET.TitleFre VehicleTitleFre,
  VET.TitleRom VehicleTitleRom,
  VET.TitlePor VehicleTitlePor,
  VET.TitlePol VehicleTitlePol,
  VET.TitleHol VehicleTitleHol,
  VET.TitleAlb VehicleTitleAlb,
  VET.TitleDen VehicleTitleDen,


  A.Article,
  A.Paragraph,
  A.Letter,
  A.Id1,
  A.Id2,
  A.Id3,
  A.DescriptionIta ArticleDescriptionIta,
  A.DescriptionEng ArticleDescriptionEng,
  A.DescriptionGer ArticleDescriptionGer,
  A.DescriptionSpa ArticleDescriptionSpa,
  A.DescriptionFre ArticleDescriptionFre,
  A.DescriptionRom ArticleDescriptionRom,
  A.DescriptionPor ArticleDescriptionPor,
  A.DescriptionPol ArticleDescriptionPol,
  A.DescriptionHol ArticleDescriptionHol,
  A.DescriptionAlb ArticleDescriptionAlb,
  A.DescriptionDen ArticleDescriptionDen,

  CO.Name ControllerName,
  CO.Code ControllerCode,

  TT.Title TimeTypeTitle,


  T.CompanyName,
  T.Surname,

  STT.Title StreetTypeTitle,

  C.Title CityTitle



FROM Fine F
INNER JOIN FineArticle FA ON F.Id=FA.FineId
INNER JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
INNER JOIN StatusType ST ON F.StatusTypeId = ST.Id
INNER JOIN Reason R ON FA.ReasonId = R.Id
INNER JOIN VehicleType VET ON F.VehicleTypeId=VET.Id
INNER JOIN Article A ON A.Id = FA.ArticleId
INNER JOIN sarida.City C ON C.Id = F.Locality
INNER JOIN Controller CO ON F.ControllerId=CO.Id
INNER JOIN TimeType TT ON TT.Id=F.TimeTypeId
LEFT JOIN StreetType STT ON F.StreetTypeId = STT.Id
LEFT JOIN FineTrespasser FT ON F.Id=FT.FineId
LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id

WHERE FT.TrespasserTypeId=10 OR FT.TrespasserTypeId=11;











//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_ViolationQuery
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_ViolationQuery;
CREATE VIEW V_ViolationQuery AS
SELECT
  F.Id,
  F.Code,
  F.CityId,
  F.ProtocolId,
  F.ProtocolYear,
  F.StatusTypeId,
  F.FineDate,
  F.FineTime,
  F.ControllerId,
  F.ControllerDate,
  F.ControllerTime,
  F.Locality,
  F.Address,
  F.VehicleTypeId,
  F.VehiclePlate,
  F.VehicleCountry,
  F.CountryId,
  F.VehicleBrand,
  F.VehicleModel,
  F.VehicleColor,
  F.VehicleLastRevision,
  F.PreviousId,
  F.Note,

  VT.Title ViolationTitle,
  VT.RuleTypeId,

  FA.ArticleId,
  FA.DetectorId,
  FA.Speed,
  FA.SpeedLimit,
  FA.SpeedControl,
  FA.ViolationTypeId,
  FA.ReasonId,
  FA.Fee,
  FA.MaxFee,
  FA.TimeTLightFirst,
  FA.TimeTLightSecond,
  FA.ArticleNumber


FROM
Fine F JOIN FineArticle FA ON F.Id=FA.FineId
JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId

WHERE
StatusTypeId =1 AND FT.TrespasserId IS NULL;







//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineTrespasser
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FineTrespasser;
CREATE VIEW V_FineTrespasser AS
SELECT
F.Id FineId,
F.Code,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.VehiclePlate,
F.CountryId VehicleCountryId,
F.VehicleCountry,
F.ExternalProtocol,
F.ExternalYear,
F.PagoPa1,
F.PagoPa2,


FT.TrespasserTypeId,
FT.Note,
FT.OwnerAdditionalFee,
FT.CustomerAdditionalFee,
FT.ReceiveDate,
FT.FineSendDate,
FT.FineNotificationDate,
FT.FineNotificationType,


T.LegalFormId,
T.Genre,
T.Address,
T.ZIP,
T.Id TrespasserId,
T.CompanyName,
T.Surname,
T.Name,
T.BornDate,
T.BornPlace,
T.TaxCode,
T.VatCode,
T.LicenseCategory,
T.LicenseNumber,
T.LicenseDate,
T.LicenseOffice,
T.PEC,
T.DocumentCountryId,
T.City,
T.CityId TrespasserCityId,
T.CountryId,

T.IrideCode,
T.Phone2,
T.Fax,
T.Notes,
T.UserId,
T.VersionDate,

C.Title CountryTitle

FROM Fine F INNER JOIN FineTrespasser FT ON F.Id = FT.FineId
INNER JOIN Trespasser T ON FT.TrespasserId=T.Id
INNER JOIN Country C ON T.CountryId = C.Id;
















//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_Trespasser
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_Trespasser;
CREATE VIEW V_Trespasser AS
SELECT

T.Id,
T.Code,
T.CustomerId,
T.LegalFormId,
T.Genre,
T.CompanyName,
T.Surname,
T.Name,
T.Address,
T.StreetNumber,
T.Ladder,
T.Indoor,
T.Plan,
T.ZIP,
T.City,
T.CityId,
T.Province,
T.TaxCode,
T.VatCode,
T.CountryId,
T.BornPlace,
T.BornDate,
T.Phone,
T.Mail,
T.PEC,
T.ZoneId,
T.LanguageId,
T.LicenseNumber,
T.DocumentTypeId,
T.LicenseDate,
T.LicenseCategory,
T.LicenseOffice,
T.DataSourceDate,
T.IrideCode,
T.DocumentCountryId,
T.DeathDate,
T.Phone2,
T.Fax,
T.Notes,
T.UserId,
T.VersionDate,


C.Title CountryTitle

FROM Trespasser T INNER JOIN Country C ON
T.CountryId = C.Id;












//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineArticle
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FineArticle;
CREATE VIEW V_FineArticle AS
SELECT
  F.Id,
  F.Code,
  F.CityId,
  F.ProtocolId,
  F.ProtocolYear,
  F.StatusTypeId,
  F.FineDate,
  F.FineTime,
  F.TimeTypeId,
  F.ControllerId,
  F.ControllerDate,
  F.ControllerTime,
  F.Locality,
  F.Address,
  F.GpsLat,
  F.GpsLong,
  F.VehicleTypeId,
  F.VehiclePlate,
  F.CountryId,
  F.VehicleCountry,
  F.DepartmentId,
  F.VehicleBrand,
  F.VehicleModel,
  F.VehicleColor,
  F.VehicleMass,
  F.VehicleLastRevision,
  F.PreviousId,
  F.Note,
  F.FineTypeId,
  F.IuvCode,
  F.KindCreateDate,
  F.KindSendDate,
  F.ProtocolIdAssigned,
  F.PagoPA1,
  F.PagoPA2,


  CO.Qualification  ControllerQualification,
  CO.Name ControllerName,
  CO.Code ControllerCode,

  TMT.Title TimeTitle,
  TMT.DescriptionIta TimeDescriptionIta,
  TMT.DescriptionEng TimeDescriptionEng,
  TMT.DescriptionGer TimeDescriptionGer,
  TMT.DescriptionSpa TimeDescriptionSpa,
  TMT.DescriptionFre TimeDescriptionFre,
  TMT.DescriptionRom TimeDescriptionRom,
  TMT.DescriptionPor TimeDescriptionPor,
  TMT.DescriptionPol TimeDescriptionPol,
  TMT.DescriptionHol TimeDescriptionHol,
  TMT.DescriptionAlb TimeDescriptionAlb,
  TMT.DescriptionDen TimeDescriptionDen,



  VT.Title ViolationTitle,
  VT.RuleTypeId,

  FA.ArticleId,
  FA.DetectorId,
  FA.Speed,
  FA.SpeedLimit,
  FA.SpeedControl,
  FA.ViolationTypeId,
  FA.ReasonId,
  FA.Fee,
  FA.MaxFee,
  FA.TimeTLightFirst,
  FA.TimeTLightSecond,
  FA.ArticleNumber,
  FA.TrespasserId1_180,
  FA.ExpirationDate,

  ST.Title StatusTitle,



  A.Article,
  A.Paragraph,
  A.Letter,
  A.Id1,
  A.Id2,
  A.Id3,
  A.DescriptionIta ArticleDescriptionIta,
  A.DescriptionEng ArticleDescriptionEng,
  A.DescriptionGer ArticleDescriptionGer,
  A.DescriptionSpa ArticleDescriptionSpa,
  A.DescriptionFre ArticleDescriptionFre,
  A.AdditionalTextIta ArticleAdditionalTextIta,
  A.DescriptionRom ArticleDescriptionRom,
  A.DescriptionPor ArticleDescriptionPor,
  A.DescriptionPol ArticleDescriptionPol,
  A.DescriptionHol ArticleDescriptionHol,
  A.DescriptionAlb ArticleDescriptionAlb,
  A.DescriptionDen ArticleDescriptionDen,
  A.ArticleLetterAssigned,


  R.TitleIta ReasonTitleIta,
  R.TitleEng ReasonTitleEng,
  R.TitleGer ReasonTitleGer,
  R.TitleSpa ReasonTitleSpa,
  R.TitleFre ReasonTitleFre,
  R.TitleRom ReasonTitleRom,
  R.TitlePor ReasonTitlePor,
  R.TitlePol ReasonTitlePol,
  R.TitleHol ReasonTitleHol,
  R.TitleAlb ReasonTitleAlb,
  R.TitleDen ReasonTitleDen,

  R.DescriptionIta ReasonDescriptionIta,

  VET.TitleIta VehicleTitleIta,
  VET.TitleEng VehicleTitleEng,
  VET.TitleGer VehicleTitleGer,
  VET.TitleSpa VehicleTitleSpa,
  VET.TitleFre VehicleTitleFre,
  VET.TitleRom VehicleTitleRom,
  VET.TitlePor VehicleTitlePor,
  VET.TitlePol VehicleTitlePol,
  VET.TitleHol VehicleTitleHol,
  VET.TitleAlb VehicleTitleAlb,
  VET.TitleDen VehicleTitleDen,

  FT.TrespasserId,
  FT.TrespasserTypeId,
  FT.OwnerAdditionalFee,
  FT.CustomerAdditionalFee,
  FT.ReceiveDate,
  FT.FineSendDate,
  FT.FineNotificationDate,
  FT.FineNotificationType,


  TT.Title TrespasserTitle,

  C.Title CityTitle,
  C.ZIP CityZIP,

  P.Title ProvinceTitle,
  P.ShortTitle ProvinceShortTitle,

  VTL.ViolationLetterAssigned

FROM Fine F
INNER JOIN FineArticle FA ON F.Id=FA.FineId
INNER JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
INNER JOIN Controller CO ON F.ControllerId=CO.Id
INNER JOIN TimeType TMT ON TMT.Id=F.TimeTypeId
INNER JOIN StatusType ST ON F.StatusTypeId = ST.Id
INNER JOIN Reason R ON FA.ReasonId = R.Id
INNER JOIN Article A ON A.Id = FA.ArticleId
INNER JOIN sarida.City C ON C.Id = F.Locality
INNER JOIN sarida.Province P ON C.ProvinceId = P.Id
INNER JOIN VehicleType VET ON F.VehicleTypeId=VET.Id
INNER JOIN FineTrespasser FT ON F.Id=FT.FineId
INNER JOIN TrespasserType TT ON FT.TrespasserTypeId=TT.Id
LEFT JOIN ViolationTypeLetter VTL ON VT.Id=VTL.ViolationTypeId aND F.CityId=VTL.CityId;










//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineAnomaly
//////////////////////////////////////////////////////////////////////////////////////////////////


DROP VIEW V_FineAnomaly;
CREATE VIEW V_FineAnomaly  AS
SELECT
  F.Id,
  F.Code,
  F.CityId,
  F.ProtocolId,
  F.ProtocolYear,
  F.StatusTypeId,
  F.FineDate,
  F.FineTime,
  F.ControllerId,
  F.Locality,
  F.StreetTypeId,
  F.Address,
  F.VehicleTypeId,
  F.VehiclePlate,
  F.CountryId,
  F.VehicleCountry,
  F.DepartmentId,
  F.VehicleBrand,
  F.VehicleModel,
  F.VehicleColor,
  F.VehicleMass,
  F.VehicleLastRevision,
  F.PreviousId,
  F.Note,

  VT.Title AS ViolationTitle,

  FA.ArticleId,
  FA.DetectorId,
  FA.ReasonId,
  FA.Speed,
  FA.SpeedLimit,
  FA.SpeedControl,
  FA.ViolationTypeId,
  FA.Fee,
  FA.MaxFee,
  FA.ArticleNumber,

  ST.Title AS StatusTitle,

  FAN.Anomaly AS Anomaly,
  A.Article,
  A.Paragraph,
  A.Letter,
  A.Id1,
  A.Id2,
  A.Id3,
  A.DescriptionIta AS ArticleDescriptionIta,
  A.DescriptionEng AS ArticleDescriptionEng,
  A.DescriptionGer AS ArticleDescriptionGer,
  A.DescriptionSpa AS ArticleDescriptionSpa,
  A.DescriptionFre AS ArticleDescriptionFre,
  A.DescriptionRom ArticleDescriptionRom,
  A.DescriptionPor ArticleDescriptionPor,
  A.DescriptionPol ArticleDescriptionPol,
  A.DescriptionHol ArticleDescriptionHol,
  A.DescriptionAlb ArticleDescriptionAlb,
  A.DescriptionDen ArticleDescriptionDen

FROM Fine F join FineArticle FA on F.Id = FA.FineId
JOIN ViolationType VT on FA.ViolationTypeId = VT.Id
JOIN StatusType ST on F.StatusTypeId = ST.Id
JOIN Article A on A.Id = FA.ArticleId
JOIN FineAnomaly FAN on F.Id = FAN.FineId;


//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_Customer
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_Customer;
CREATE VIEW V_Customer AS
SELECT
  C.CityId,
  C.Blazon,
  C.ManagerName,
  C.ManagerAdditionalName,
  C.ManagerSector,
  C.ManagerCity,
  C.ManagerProvince,
  C.ManagerAddress,
  C.ManagerZIP,
  C.ManagerPhone,
  C.ManagerFax,
  C.ManagerMail,
  C.ManagerPEC,
  C.ManagerInfo,
  C.ManagerSignName,
  C.NationalBankOwner,
  C.NationalBankName,
  C.NationalBankAccount,
  C.NationalBankIban,
  C.NationalBankSwift,
  C.NationalBankMgmt,
  C.ForeignBankOwner,
  C.ForeignBankName,
  C.ForeignBankAccount,
  C.ForeignBankIban,
  C.ForeignBankSwift,
  C.ForeignBankMgmt,
  C.LumpSum,
  C.Reference,
  C.ManagerProcessName,
  C.ManagerDataEntryName,
  C.ForeignAnticipateCost,
  C.NationalAnticipateCost,
  C.PDFRefPrint,
  C.FifthField,
  C.DigitalSignature,
  C.ExternalRegistration,
  C.ReturnPlace,
  C.CityUnion,
  C.FinePaymentSpecificationType,
  C.FinePDFList,
  C.ChiefControllerList,
  C.MCTCUserName,
  C.MCTCPassword,
  C.MCTCDate,
  C.NationalMod23LSubject,
  C.NationalMod23LCustomerName,
  C.NationalMod23LCustomerSubject,
  C.NationalMod23LCustomerAddress,
  C.NationalMod23LCustomerCity,
  C.ForeignMod23LSubject,
  C.ForeignMod23LCustomerName,
  C.ForeignMod23LCustomerSubject,
  C.ForeignMod23LCustomerAddress,
  C.ForeignMod23LCustomerCity,
  C.LicensePointOffice,
  C.LicensePointCode,
  C.LicensePointFtpUser,
  C.LicensePointFtpPassword,
  C.LicensePointPaymentCompletion,
  C.PagoPAPayment,
  C.PagoPAAlias,
  C.PagoPaIban,
  C.ReminderAdditionalFee,
  C.RegularPostalFine,

  CI.TaxCode ManagerTaxCode,
  CI.VAT ManagerVAT,
  CI.Country ManagerCountry,
  CI.Web ManagerWeb,

  CC.CreationType,
  CC.NationalTotalFee,
  CC.NationalNotificationFee,
  CC.NationalResearchFee,
  CC.NationalPECNotificationFee,
  CC.NationalPECResearchFee,
  CC.ForeignTotalFee,
  CC.ForeignNotificationFee,
  CC.ForeignResearchFee,
  CC.ForeignPECNotificationFee,
  CC.ForeignPECResearchFee,
  CC.FromDate,
  CC.ToDate,
  CC.NationalPostalType,
  CC.NationalPostalAuthorization,
  CC.NationalSmaName,
  CC.NationalSmaAuthorization,
  CC.NationalSmaPayment,
  CC.NationalPercentualReminder,
  CC.ForeignPostalType,
  CC.ForeignPostalAuthorization,
  CC.ForeignSmaName,
  CC.ForeignSmaAuthorization,
  CC.ForeignSmaPayment,
  CC.ForeignPercentualReminder



FROM Customer C JOIN sarida.City CI ON C.CityId = CI.Id
  JOIN CustomerCharge CC ON C.CityId = CC.CityId AND CC.ToDate IS NULL;






//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_CustomerStreet
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_CustomerStreet;
CREATE VIEW V_CustomerStreet AS
SELECT
  CS.Id CustomerStreetId,
  CS.CityId,
  CS.Title CustomerStreetTitle,


  T.Id ToponymId,
  T.Title ToponymTitle,
  T.ShortTitle ToponymShortTitle

FROM CustomerStreet CS JOIN sarida.Toponym T ON CS.ToponymId = T.Id
ORDER BY T.Title, CS.Title

//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_JudicialOffice
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_JudicialOffice;
CREATE VIEW V_JudicialOffice AS
SELECT
  JO.CityId,
  JO.City,
  JO.Province,
  JO.Address,
  JO.ZIP,
  JO.Phone,
  JO.Fax,
  JO.Mail,
  JO.PEC,
  JO.Web,
  JO.Disabled,
  JO.OfficeId,

  O.TitleIta OfficeTitleIta,
  O.TitleEng OfficeTitleEng,
  O.TitleGer OfficeTitleGer,
  O.TitleSpa OfficeTitleSpa,
  O.TitleFre OfficeTitleFre,
  O.TitleRom OfficeTitleRom,
  O.TitlePor OfficeTitlePor,
  O.TitlePol OfficeTitlePol,
  O.TitleHol OfficeTitleHol,
  O.TitleAlb OfficeTitleAlb,
  O.TitleDen OfficeTitleDen



FROM JudicialOffice JO INNER JOIN Office O ON JO.OfficeId = O.Id
WHERE Disabled=0;






//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineHistory
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FineHistory;
CREATE VIEW V_FineHistory
AS
SELECT
  F.Id,
  F.Code,
  F.CityId,
  F.ProtocolId,
  F.ProtocolYear,
  F.StatusTypeId,
  F.FineDate,
  F.FineTime,
  F.ControllerId AS ViolationControllerId,
  F.Locality,
  F.Address,
  F.VehicleTypeId,
  F.VehiclePlate,
  F.CountryId,
  F.VehicleCountry,
  F.DepartmentId,
  F.VehicleBrand,
  F.VehicleModel,
  F.VehicleColor,
  F.VehicleMass,
  F.VehicleLastRevision,
  F.PreviousId,
  F.Note,
  F.ExternalProtocol,
  F.ExternalYear,
  F.ExternalDate,
  F.ExternalTime,
  F.PagoPA1,
  F.PagoPA2,

  FA.ViolationTypeId,
  FA.ReasonId,
  FA.Fee,
  FA.MaxFee,
  FA.DetectorId,
  FA.Speed,
  FA.SpeedLimit,
  FA.SpeedControl,
  FA.TimeTLightFirst,
  FA.TimeTLightSecond,
  FA.ArticleNumber,

  VT.Title ViolationTitle,
  VT.RuleTypeId,

  A.Id ArticleId,
  A.Article,
  A.Paragraph,
  A.Letter,
  A.Id1,
  A.Id2,
  A.Id3,
  A.DescriptionIta ArticleDescriptionIta,
  A.DescriptionEng ArticleDescriptionEng,
  A.DescriptionGer ArticleDescriptionGer,
  A.DescriptionSpa ArticleDescriptionSpa,
  A.DescriptionFre ArticleDescriptionFre,
  A.DescriptionRom ArticleDescriptionRom,
  A.DescriptionPor ArticleDescriptionPor,
  A.DescriptionPol ArticleDescriptionPol,
  A.DescriptionHol ArticleDescriptionHol,
  A.DescriptionAlb ArticleDescriptionAlb,
  A.DescriptionDen ArticleDescriptionDen,

  FH.NotificationTypeId,
  FH.TrespasserId,
  FH.CustomerFee,
  FH.NotificationFee,
  FH.ResearchFee,
  FH.NotificationDate,
  FH.PrintDate,
  FH.FlowDate,
  FH.FlowNumber,
  FH.SendDate,
  FH.DeliveryDate,
  FH.ResultId,
  FH.Documentation,
  FH.ControllerId FineControllerId,

  VET.TitleIta VehicleTitleIta,
  VET.TitleEng VehicleTitleEng,
  VET.TitleGer VehicleTitleGer,
  VET.TitleSpa VehicleTitleSpa,
  VET.TitleFre VehicleTitleFre,
  VET.TitleRom VehicleTitleRom,
  VET.TitlePor VehicleTitlePor,
  VET.TitlePol VehicleTitlePol,
  VET.TitleHol VehicleTitleHol,
  VET.TitleAlb VehicleTitleAlb,
  VET.TitleDen VehicleTitleDen,

  R.TitleIta ReasonTitleIta,
  R.TitleEng ReasonTitleEng,
  R.TitleGer ReasonTitleGer,
  R.TitleSpa ReasonTitleSpa,
  R.TitleFre ReasonTitleFre,
  R.TitleRom ReasonTitleRom,
  R.TitlePor ReasonTitlePor,
  R.TitlePol ReasonTitlePol,
  R.TitleHol ReasonTitleHol,
  R.TitleAlb ReasonTitleAlb,
  R.TitleDen ReasonTitleDen,

  R.DescriptionIta ReasonDescriptionIta,

  C.Title CityTitle,

  T.CompanyName,
  T.Surname,
  T.Name,

  FT.TrespasserTypeId,

  TT.Title AS TrespasserTitle

FROM Fine F join FineArticle FA on F.Id = FA.FineId
join ViolationType VT on FA.ViolationTypeId = VT.Id
join Article A on A.Id = FA.ArticleId
join Reason R on FA.ReasonId = R.Id
join FineHistory FH on F.Id = FH.FineId
join VehicleType VET on F.VehicleTypeId = VET.Id
join sarida.City C on C.Id = F.Locality
join FineTrespasser FT on F.Id = FT.FineId
join Trespasser T on T.Id = FH.TrespasserId
join TrespasserType TT on FT.TrespasserTypeId = TT.Id
where (F.ProtocolId > 0  OR ProtocolIdAssigned) AND (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2);





//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineNotification
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FineNotification;
CREATE VIEW V_FineNotification AS
SELECT

F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.CountryId,
F.Locality,

FN.FineId,
FN.SendDate,
FN.NotificationDate,
FN.LogDate,
FN.Box,
FN.Lot,
FN.Position,
FN.ReceiptNumber,
FN.LetterNumber,
FN.ResultId,
FN.ValidatedAddress,
FN.PaymentProcedure,
FN.126BisProcedure,
FN.RegDate,

R.Title,
R.Description

FROM Fine F JOIN FineNotification FN ON F.Id = FN.FineId
JOIN Result R ON FN.ResultId = R.Id;




//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineTariff
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FineTariff;
CREATE VIEW V_FineTariff  AS
SELECT


FA.FineId,

AT.Year,
AT.Fee,
AT.MaxFee,
AT.LicensePoint,
AT.YoungLicensePoint,
AT.AdditionalSanctionId,
AT.PresentationDocument,
AT.LossLicense,
AT.AdditionalMass,
AT.AdditionalNight,
AT.126Bis,
AT.Habitual,
AT.ReducedPayment,
AT.SuspensionLicense

FROM Fine F JOIN FineArticle FA ON F.Id = FA.FineId

JOIN ArticleTariff AT ON FA.ArticleId = AT.ArticleId AND F.ProtocolYear = AT.Year;





//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FinePayment
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FinePayment;
CREATE VIEW V_FinePayment
AS SELECT
     F.Id AS FineId,
     F.Code,
     F.CityId,
     F.Locality,
     F.ProtocolId,
     F.ProtocolYear,
     F.CountryId FineCountryId,
     F.StatusTypeId,
     F.FineDate,
     F.FineTime,
     F.VehiclePlate,
     F.ExternalProtocol,
     F.ExternalYear,
     F.ExternalDate,
     F.ExternalTime,


     FP.Id PaymentId,
     FP.TableId,
     FP.PaymentFee,
     FP.BankMgmt,
     FP.Name AS PaymentName,
     FP.PaymentTypeId,
     FP.PaymentDocumentId,
     FP.ImportationId,
     FP.PaymentDate,
     FP.DocumentType,
     FP.FifthField,
     FP.RefundStatus,
     FP.Documentation,
     FP.Note,
     FP.RegDate,
     FP.PaymentDirect,
     FP.Amount,
     FP.Fee,
     FP.CustomerFee,
     FP.NotificationFee,
     FP.CanFee,
     FP.CadFee,
     FP.ResearchFee,
     FP.OfficeNotificationFee,
     FP.Hidden,

     PT.Title PaymentTypeTitle,
     PT.TrafatCode,

     T.Id AS TrespasserId,
     T.Genre,
     T.CompanyName,
     T.Surname,
     T.Name,
     T.Address,
     T.ZIP,
     T.City,
     T.Province,
     T.CountryId,

     C.Title AS CountryTitle,

     FN.NotificationDate



   FROM Fine F JOIN FinePayment FP ON FP.FineId = F.Id
     JOIN sarida.PaymentType PT ON FP.PaymentTypeId = PT.Id
     LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
     LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id
     LEFT JOIN Country C ON T.CountryId = C.Id
     LEFT JOIN FineNotification FN ON F.Id=FN.FineId



   WHERE FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL;

//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FinePaymentAll
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FinePaymentAll;
CREATE VIEW V_FinePaymentAll
AS SELECT
FP.Id PaymentId,
FP.FineId,
FP.TableId,
FP.CityId,
FP.PaymentFee,
FP.BankMgmt,
FP.Name AS PaymentName,
FP.PaymentTypeId,
FP.PaymentDocumentId,
FP.ImportationId,
FP.PaymentDate,
FP.DocumentType,
FP.FifthField,
FP.RefundStatus,
FP.Documentation,
FP.Note,
FP.RegDate,
FP.Amount,
FP.Fee,
FP.CustomerFee,
FP.NotificationFee,
FP.CanFee,
FP.CadFee,
FP.ResearchFee,
FP.OfficeNotificationFee,
FP.Hidden,

F.Code,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.VehiclePlate,
F.ExternalProtocol,
F.ExternalYear,
F.ExternalDate,
F.ExternalTime


FROM FinePayment FP LEFT JOIN Fine F ON FP.FineId = F.Id;





//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineReminder
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FineReminder;
CREATE VIEW V_FineReminder
AS
  SELECT
    F.Id,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.ControllerId AS ViolationControllerId,
    F.Locality,
    F.Address,
    F.VehicleTypeId,
    F.VehiclePlate,
    F.CountryId,
    F.VehicleCountry,
    F.DepartmentId,
    F.VehicleBrand,
    F.VehicleModel,
    F.VehicleColor,
    F.VehicleMass,
    F.VehicleLastRevision,
    F.PreviousId,
    F.Note,
    F.ExternalProtocol,
    F.ExternalYear,
    F.ExternalDate,
    F.ExternalTime,
    F.ReminderDate,

    FA.ViolationTypeId,
    FA.ReasonId,
    FA.Fee,
    FA.MaxFee,
    FA.DetectorId,
    FA.Speed,
    FA.SpeedLimit,
    FA.SpeedControl,
    FA.TimeTLightFirst,
    FA.TimeTLightSecond,
    FA.ArticleNumber,

    VT.Title ViolationTitle,
    VT.RuleTypeId,

    A.Id ArticleId,
    A.Article,
    A.Paragraph,
    A.Letter,
    A.Id1,
    A.Id2,
    A.Id3,
    A.DescriptionIta ArticleDescriptionIta,
    A.DescriptionEng ArticleDescriptionEng,
    A.DescriptionGer ArticleDescriptionGer,
    A.DescriptionSpa ArticleDescriptionSpa,
    A.DescriptionFre ArticleDescriptionFre,
    A.DescriptionRom ArticleDescriptionRom,
    A.DescriptionPor ArticleDescriptionPor,
    A.DescriptionPol ArticleDescriptionPol,
    A.DescriptionHol ArticleDescriptionHol,
    A.DescriptionAlb ArticleDescriptionAlb,
    A.DescriptionDen ArticleDescriptionDen,

    FR.CustomerFee,

    FR.PrintDate,
    FR.FlowDate,
    FR.FlowNumber,
    FR.SendDate


  FROM Fine F join FineArticle FA on F.Id = FA.FineId
    join ViolationType VT on FA.ViolationTypeId = VT.Id
    join Article A on A.Id = FA.ArticleId
    join Reason R on FA.ReasonId = R.Id
    join FineReminder FR on F.Id = FR.FineId
    where F.ProtocolId > 0;





//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_StatisticFine
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_StatisticFine;
CREATE VIEW  V_StatisticFine  AS
SELECT
F.CityId,
F.ProtocolYear,
F.StatusTypeId,
F.CountryId,

VT.Title ViolationTitle,
VT.RuleTypeId,

FA.ArticleId,
FA.ViolationTypeId,
FA.Fee,
FA.MaxFee,
FA.ArticleNumber,

A.Article,
A.Paragraph,
A.Letter

FROM  Fine F JOIN FineArticle FA ON F.Id = FA.FineId
JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
JOIN Article A ON A.Id = FA.ArticleId;






//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineDispute
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FineDispute;
CREATE VIEW V_FineDispute
AS SELECT
F.Id AS FineId,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.FineDate,
F.FineTime,
F.VehiclePlate,
F.CountryId,


D.Id AS DisputeId,
D.GradeTypeId,
D.OwnerPresentation,
D.ProtocolNumber,
D.DateProtocol,
D.DateReceive,
D.DateSend,
D.DateFile,
D.OfficeId,
D.OfficeCity,
D.OfficeAdditionalData,
D.DateMeasure DisputeDateMeasure,
D.MeasureNumber,
D.FineSuspension,
D.DateProtocolEntity,
D.EntityProtocolNumber,
D.Number,
D.Division,
D.DateMerit,
D.RegDate,


O.TitleIta OfficeTitle,

FD.DisputeStatusId,

T.CompanyName,
T.Surname,
T.Name,
T.Address,
T.TaxCode,
T.VatCode,

DD.DateHearing,
DD.TimeHearing,
DD.DisputeResultId,
DD.TypeHearing,
DD.Note




FROM Fine F JOIN FineDispute FD ON F.Id = FD.FineId
JOIN Dispute D ON FD.DisputeId=D.Id
JOIN FineTrespasser FT ON F.Id = FT.FineId
JOIN Trespasser T ON FT.TrespasserId=T.Id
JOIN Office O ON O.Id=D.OfficeId
LEFT JOIN DisputeDate DD ON DD.DisputeId = D.Id AND DD.GradeTypeId = D.GradeTypeId AND DisputeActId=0
WHERE FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2;




//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_DisputeDate
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_DisputeDate;
CREATE VIEW V_DisputeDate
AS SELECT
D.Id AS DisputeId,
D.GradeTypeId,
D.OwnerPresentation,
D.ProtocolNumber,
D.DateProtocol,
D.DateReceive,
D.DateSend,
D.DateFile,
D.OfficeId,
D.OfficeCity,
D.OfficeAdditionalData,
D.DateMeasure DisputeDateMeasure,
D.MeasureNumber,
D.FineSuspension,
D.DateProtocolEntity,
D.EntityProtocolNumber,
D.Number,
D.Division,
D.DateMerit,
D.RegDate,

DD.Id AS DisputeDateId,
DD.DateHearing,
DD.TimeHearing,
DD.TypeHearing,
DD.DisputeResultId,
DD.Note,
DD.Number DisputeDateNumber,
DD.DateAction,
DD.DateMeasure,
DD.DateNotification,
DD.Amount,



DA.Title DisputeActTitle,



DR.Title DisputeResultTitle

FROM Dispute D JOIN DisputeDate DD ON D.Id=DD.DisputeId AND D.GradeTypeId = DD.GradeTypeId
JOIN DisputeAct DA ON DD.DisputeActId=DA.Id
JOIN DisputeResult DR ON DD.DisputeResultId=DR.Id;








//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineCommunication
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FineCommunication;
CREATE VIEW V_FineCommunication AS
SELECT
DISTINCT

  F.Id,
  F.Code,
  F.CityId,
  F.ProtocolId,
  F.ProtocolYear,
  F.StatusTypeId,
  F.FineDate,
  F.FineTime,
  F.CountryId,
  F.VehiclePlate,

  F.ExternalProtocol,
  FA.ArticleId,
  FA.ArticleNumber,


  A.Article,
  A.Paragraph,
  A.Letter,

  VT.RuleTypeId,


  FC.CommunicationProtocol,
  FC.CommunicationDate,
  FC.ReducedDate,
  FC.ReducedPoint,
  FC.CommunicationStatus,

  T.CompanyName,
  T.Surname,
  T.Name


FROM Fine F
JOIN FineArticle FA ON F.Id=FA.FineId
JOIN Article A ON A.Id = FA.ArticleId
JOIN ArticleTariff AT ON A.Id = AT.ArticleId AND F.ProtocolYear=AT.Year
JOIN ViolationType VT ON A.ViolationTypeId = VT.Id
LEFT JOIN FineCommunication FC ON F.Id=FC.FineId
LEFT JOIN FineTrespasser FT ON F.Id=FT.FineId
LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id


WHERE AT.126Bis=1 OR LicensePoint>0 AND (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId=10) AND F.StatusTypeId<=30

UNION

SELECT
DISTINCT
  F.Id,
  F.Code,
  F.CityId,
  F.ProtocolId,
  F.ProtocolYear,
  F.StatusTypeId,
  F.FineDate,
  F.FineTime,
  F.CountryId,
  F.VehiclePlate,

  F.ExternalProtocol,
  FA.ArticleId,
  FA.ArticleNumber,


  A.Article,
  A.Paragraph,
  A.Letter,

  VT.RuleTypeId,


  FC.CommunicationProtocol,
  FC.CommunicationDate,
  FC.ReducedDate,
  FC.ReducedPoint,
  FC.CommunicationStatus,

  T.CompanyName,
  T.Surname,
  T.Name


FROM Fine F
JOIN FineAdditionalArticle FAA ON F.Id = FAA.FineId
JOIN FineArticle FA ON FAA.FineId=FA.FineId
JOIN Article A ON A.Id = FAA.ArticleId
JOIN ArticleTariff AT ON A.Id = AT.ArticleId AND F.ProtocolYear=AT.Year
JOIN ViolationType VT ON A.ViolationTypeId = VT.Id
LEFT JOIN FineCommunication FC ON F.Id=FC.FineId
LEFT JOIN FineTrespasser FT ON F.Id=FT.FineId
LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id

WHERE AT.126Bis=1 OR LicensePoint>0;











//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FinePresentation
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FinePresentation;

CREATE VIEW V_FinePresentation AS
  SELECT
    DISTINCT
    F.Id,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.CountryId,
    F.VehiclePlate,

    F.ExternalProtocol,
    FA.ArticleId,
    FA.ArticleNumber,


    A.Article,
    A.Paragraph,
    A.Letter,

    VT.RuleTypeId,


    FP.PresentationDate,

    T.CompanyName,
    T.Surname,
    T.Name


  FROM Fine F
    JOIN FineArticle FA ON F.Id=FA.FineId
    JOIN Article A ON A.Id = FA.ArticleId
    JOIN ArticleTariff AT ON A.Id = AT.ArticleId AND F.ProtocolYear=AT.Year
    JOIN ViolationType VT ON A.ViolationTypeId = VT.Id
    LEFT JOIN FinePresentation FP ON F.Id=FP.FineId
    LEFT JOIN FineTrespasser FT ON F.Id=FT.FineId
    LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id


  WHERE AT.PresentationDocument=1 AND (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId=10) AND F.StatusTypeId<=30

  UNION

  SELECT
    DISTINCT
    F.Id,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.CountryId,
    F.VehiclePlate,

    F.ExternalProtocol,
    FA.ArticleId,
    FA.ArticleNumber,


    A.Article,
    A.Paragraph,
    A.Letter,

    VT.RuleTypeId,


    FP.PresentationDate,

    T.CompanyName,
    T.Surname,
    T.Name



  FROM Fine F
    JOIN FineAdditionalArticle FAA ON F.Id = FAA.FineId
    JOIN FineArticle FA ON FAA.FineId=FA.FineId
    JOIN Article A ON A.Id = FAA.ArticleId
    JOIN ArticleTariff AT ON A.Id = AT.ArticleId AND F.ProtocolYear=AT.Year
    JOIN ViolationType VT ON A.ViolationTypeId = VT.Id
    LEFT JOIN FinePresentation FP ON F.Id=FP.FineId
    LEFT JOIN FineTrespasser FT ON F.Id=FT.FineId
    LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id

  WHERE AT.PresentationDocument=1 ;




//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineQuery
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FineQuery;
CREATE VIEW V_FineQuery
AS
SELECT
F.Id,
F.Code,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.VehicleTypeId,
F.VehiclePlate,
F.VehicleCountry,
F.CountryId,
F.ExternalProtocol,

ST.Title StatusTypeTitle,

C.ManagerCity,


T.CompanyName,
T.Surname,
T.Name,
T.Address


FROM Fine F JOIN StatusType ST ON F.StatusTypeId = ST.Id
JOIN Customer C ON F.CityId=C.CityId
LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id;





//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_FineComplete
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_FineComplete;
CREATE VIEW V_FineComplete AS
SELECT
F.Id FineId,
F.Code,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.VehiclePlate,
F.Locality,
F.CountryId VehicleCountryId,
F.ExternalProtocol,
F.ExternalYear,
F.PreviousId,

FA.ViolationTypeId,
FA.ArticleId,
FA.ArticleNumber,

VT.Title ViolationTitle,
VT.RuleTypeId,


FT.TrespasserTypeId,
FT.Note,
FT.OwnerAdditionalFee,
FT.CustomerAdditionalFee,
FT.ReceiveDate,

T.Genre,
T.Address,
T.ZIP,
T.Id TrespasserId,
T.CompanyName,
T.Surname,
T.Name,
T.LicenseCategory,
T.LicenseNumber,
T.LicenseDate,
T.LicenseOffice,


T.City,
T.CountryId,

C.Title CountryTitle

FROM Fine F INNER JOIN FineTrespasser FT ON F.Id = FT.FineId
JOIN FineArticle FA ON F.Id = FA.FineId
JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
JOIN Trespasser T ON FT.TrespasserId=T.Id
JOIN Country C ON T.CountryId = C.Id

WHERE FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2;


DROP VIEW V_126BisProcedure;
CREATE VIEW V_126BisProcedure AS
SELECT
DISTINCT
F.Id,
F.Code,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.CountryId,
F.VehiclePlate,

F.ExternalProtocol,
FA.ArticleId,

FN.NotificationDate,

A.Article,
A.Paragraph,
A.Letter,

FC.CommunicationDate




FROM Fine F
JOIN FineArticle FA ON F.Id=FA.FineId
JOIN Article A ON A.Id = FA.ArticleId
JOIN ArticleTariff AT ON A.Id = AT.ArticleId AND F.ProtocolYear=AT.Year
JOIN FineNotification FN ON F.Id=FN.FineId
LEFT JOIN FineCommunication FC ON F.Id=FC.FineId

WHERE AT.126Bis=1 AND FN.126BisProcedure=1 AND ((F.StatusTypeId>=25 AND F.StatusTypeId<=30) OR F.StatusTypeId=12)
AND isnull(F.KindSendDate)
AND FN.NotificationDate IS NOT NULL AND
(
    FN.ResultId<=3 OR (FN.ResultId>=5 AND FN.ResultId<=9 )
    OR ( ( (FN.ResultId=21 OR FN.ResultId=4) AND FN.ValidatedAddress=1) )
    OR FN.ResultId=22
)

;






DROP VIEW V_180Procedure;
CREATE VIEW V_180Procedure AS
  SELECT
    DISTINCT
    F.Id,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.CountryId,
    F.VehiclePlate,

    F.ExternalProtocol,
    FA.ArticleId,

    FN.NotificationDate,

    A.Article,
    A.Paragraph,
    A.Letter,

    FP.PresentationDate




  FROM Fine F
    JOIN FineArticle FA ON F.Id=FA.FineId
    JOIN Article A ON A.Id = FA.ArticleId
    JOIN ArticleTariff AT ON A.Id = AT.ArticleId AND F.ProtocolYear=AT.Year
    JOIN FineNotification FN ON F.Id=FN.FineId
    LEFT JOIN FinePresentation FP ON F.Id=FP.FineId

  WHERE AT.PresentationDocument=1 AND FN.PresentationDocumentProcedure=1 AND ((F.StatusTypeId>=25 AND F.StatusTypeId<=30) OR F.StatusTypeId=12)
AND FN.NotificationDate IS NOT NULL AND
        (

          FN.ResultId<=3
          OR (FN.ResultId>=5 AND FN.ResultId<=9 )
          OR(
            ( (FN.ResultId=21 OR FN.ResultId=4) AND FN.ValidatedAddress=1)
          )
        );




DROP VIEW V_PaymentProcedure;
CREATE VIEW V_PaymentProcedure AS
SELECT
  F.Id,
  F.Code,
  F.CountryId,
  F.CityId,
  F.ProtocolId,
  F.ProtocolYear,
  F.FineDate,
  F.FineTime,
  F.VehiclePlate,

  FN.NotificationDate,

  FA.Fee,
  FA.MaxFee,

  ArT.ReducedPayment,
  FH.CustomerFee + FH.NotificationFee + FH.ResearchFee + FH.NotifierFee + FH.OtherFee AS AdditionalFee0,
  FH.CustomerFee + FH.NotificationFee + FH.ResearchFee + FH.CanFee + FH.CadFee + FH.NotifierFee + FH.OtherFee AS AdditionalFee1,

  FH.SendDate,
  FH.DeliveryDate,
  FH.ResultId

FROM Fine F
JOIN FineNotification FN ON F.Id = FN.FineId
JOIN FineArticle FA ON F.Id = FA.FineId
JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId AND ArT.Year = F.ProtocolYear
JOIN FineHistory FH ON FA.FineId = FH.FineId

WHERE FH.NotificationTypeId=6 AND FN.PaymentProcedure=1 AND F.StatusTypeId>=25 AND F.StatusTypeId<=30
AND FN.NotificationDate IS NOT NULL AND (

  FN.ResultId<=3
    OR (FN.ResultId>=5 AND FN.ResultId<=9 )
    OR(
      ( (FN.ResultId=21 OR FN.ResultId=4) AND FN.ValidatedAddress=1)
    )
);



DROP VIEW V_Search_FineTrespasser;
CREATE VIEW V_Search_FineTrespasser
AS SELECT
     F.Id AS FineId,
     F.Code ,
     F.CityId,
     F.ProtocolId,
     F.ProtocolYear,
     F.StatusTypeId,
     F.FineDate,
     F.FineTime,
     F.VehiclePlate,
     F.CountryId AS VehicleCountryId,

     FT.TrespasserTypeId,
     FT.ReceiveDate,

     T.Genre,
     T.Address,
     T.ZIP,
     T.Id AS TrespasserId,
     T.CompanyName,
     T.Surname,
     T.Name,
     T.City,
     T.CountryId

    FROM Fine F LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId

    LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id
    LEFT JOIN Country C ON T.CountryId = C.Id

    WHERE F.ProtocolId > 0 AND (TrespasserTypeId=1 OR TrespasserTypeId=11 OR TrespasserTypeId=2 OR TrespasserTypeId IS NULL)
        AND ProtocolId>0 AND StatusTypeId>=12;


DROP VIEW V_UserCity;
CREATE VIEW V_UserCity  AS  
SELECT


U.UserName,
U.UserType,
U.Mail,
U.UserLevel,


UC.MainMenuId,
UC.UserId,
UC.CityId,
UC.CityYear,

C.Title AS CityTitle 

FROM User U JOIN UserCity UC ON U.Id = UC.UserId
JOIN  City C ON UC.CityId = C.Id

ORDER BY C.Title, UC.CityYear, UC.UserId;





DROP VIEW V_RuleType;
CREATE VIEW V_RuleType
AS
SELECT
RT.Id,
RT.CityId,
RT.Title RuleTypeTitle,
RT.PrintHeaderIta,
RT.PrintHeaderEng,
RT.PrintHeaderGer,
RT.PrintHeaderSpa,
RT.PrintHeaderFre,
RT.PrintHeaderRom,
RT.PrintHeaderPor,
RT.PrintHeaderPol,
RT.PrintHeaderHol,
RT.PrintHeaderAlb,
RT.PrintHeaderDen,

RT.PrintObjectIta,
RT.PrintObjectEng,
RT.PrintObjectGer,
RT.PrintObjectSpa,
RT.PrintObjectFre,
RT.PrintObjectRom,
RT.PrintObjectPor,
RT.PrintObjectPol,
RT.PrintObjectHol,
RT.PrintObjectAlb,
RT.PrintObjectDen,

VT.Id ViolationTypeId,
VT.Title ViolationTypeTitle,
VT.NationalFormId,
VT.ForeignFormId

FROM RuleType RT JOIN ViolationType VT ON RT.Id = VT.RuleTypeId;













//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_Search_FineTrespasser
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_Search_FineTrespasser;
CREATE VIEW V_Search_FineTrespasser AS
  SELECT
    F.Id FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId VehicleCountryId,

    FT.TrespasserTypeId,
    FT.ReceiveDate,

    T.Genre,
    T.Address,
    T.ZIP,
    T.Id TrespasserId,
    T.CompanyName,
    T.Surname,
    T.Name,


    T.City,
    T.CountryId

  FROM Fine F INNER JOIN FineTrespasser FT ON F.Id = FT.FineId
    LEFT JOIN Trespasser T ON FT.TrespasserId=T.Id
    LEFT JOIN Country C ON T.CountryId = C.Id

WHERE F.ProtocolId>0 ;





//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_frm_SendFine
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_frm_SendFine;
CREATE VIEW V_frm_SendFine
AS
SELECT
F.Id,
F.Code,
F.CityId,
F.ProtocolId,
F.ProtocolYear,
F.StatusTypeId,
F.FineDate,
F.FineTime,
F.ControllerId AS ViolationControllerId,
F.Locality,
F.Address,
F.VehicleTypeId,
F.VehiclePlate,
F.CountryId,
F.VehicleCountry,
F.DepartmentId,
F.VehicleBrand,
F.VehicleModel,
F.VehicleColor,
F.VehicleMass,
F.VehicleLastRevision,
F.PreviousId,
F.Note,
F.ExternalProtocol,
F.ExternalYear,
F.ExternalDate,
F.ExternalTime,


FH.NotificationTypeId,
FH.TrespasserId,
FH.CustomerFee,
FH.NotificationFee,
FH.ResearchFee,
FH.NotificationDate,
FH.PrintDate,
FH.FlowDate,
FH.FlowNumber,
FH.SendDate,
FH.DeliveryDate,
FH.ResultId,
FH.Documentation,
FH.ControllerId FineControllerId,

T.CompanyName,
T.Surname,
T.Name,

FT.TrespasserTypeId



FROM Fine F join FineHistory FH on F.Id = FH.FineId
join FineTrespasser FT on F.Id = FT.FineId
join Trespasser T on T.Id = FH.TrespasserId
join TrespasserType TT on FT.TrespasserTypeId = TT.Id
where
  ((F.StatusTypeId=15 AND F.ProtocolId > 0) OR (F.StatusTypeId=8 AND F.ProtocolIdAssigned > 0 )) AND


  (
   FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11
OR (FT.TrespasserTypeId=2 AND FT.FineCreateDate IS NULL)
OR (FT.TrespasserTypeId=3 AND FT.FineCreateDate IS NULL)
OR (FT.TrespasserTypeId=15 AND FT.FineCreateDate IS NULL)
OR (FT.TrespasserTypeId=16 AND FT.FineCreateDate IS NULL)

  );










DROP VIEW V_prn_Assessment;
CREATE VIEW V_prn_Assessment AS
  SELECT
    F.Id FineId,
    F.Code,
    F.CityId,
    F.Address,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.Locality,
    F.CountryId VehicleCountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,

    FT.TrespasserTypeId,
    FT.Note,
    FT.OwnerAdditionalFee,
    FT.CustomerAdditionalFee,
    FT.ReceiveDate,

    FA.ArticleId,

    T.Genre,
    T.ZIP,
    T.Id TrespasserId,
    T.CompanyName,
    T.Surname,
    T.Name,

    T.City,
    T.CountryId,

    C.Title CountryTitle,

    FH.FlowDate,
    FH.SendDate,
    FH.DeliveryDate,
    FH.ResultId

  FROM Fine F INNER JOIN FineTrespasser FT ON F.Id = FT.FineId


    JOIN Trespasser T ON FT.TrespasserId=T.Id
    JOIN Country C ON T.CountryId = C.Id
    JOIN FineArticle FA ON F.Id = FA.FineId
    JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6

  WHERE (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2)













//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_mgmt_Fine
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_mgmt_Fine;
CREATE VIEW V_mgmt_Fine AS
  SELECT
    F.Id FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId VehicleCountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,


    VT.Title ViolationTitle,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    FT.TrespasserTypeId,
    FT.Note,
    FT.OwnerAdditionalFee,
    FT.CustomerAdditionalFee,
    FT.ReceiveDate,

    T.Genre,
    T.Address,
    T.ZIP,
    T.Id TrespasserId,
    T.CompanyName,
    T.Surname,
    T.Name,
    T.TaxCode,
    T.VatCode,
    T.LicenseCategory,
    T.LicenseNumber,
    T.LicenseDate,
    T.LicenseOffice,
    T.PEC,
    T.City,
    T.CountryId,
    T.IrideCode,

    C.Title CountryTitle

  FROM Fine F LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
    JOIN FineArticle FA ON F.Id = FA.FineId
    JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
    LEFT JOIN Trespasser T ON FT.TrespasserId=T.Id
    LEFT JOIN Country C ON T.CountryId = C.Id

  WHERE F.ProtocolId>0 AND F.StatusTypeId>11 AND
        (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL);


//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_mgmt_Warning
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_mgmt_Warning;
CREATE VIEW V_mgmt_Warning AS
  SELECT
    F.Id FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId VehicleCountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,


    VT.Title ViolationTitle,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter,

    FT.TrespasserTypeId,
    FT.Note,
    FT.OwnerAdditionalFee,
    FT.CustomerAdditionalFee,
    FT.ReceiveDate,

    T.Genre,
    T.Address,
    T.ZIP,
    T.Id TrespasserId,
    T.CompanyName,
    T.Surname,
    T.Name,
    T.TaxCode,
    T.VatCode,
    T.LicenseCategory,
    T.LicenseNumber,
    T.LicenseDate,
    T.LicenseOffice,
    T.PEC,
    T.City,
    T.CountryId,
    T.IrideCode,

    C.Title CountryTitle

  FROM Fine F LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
    JOIN FineArticle FA ON F.Id = FA.FineId
    JOIN Article A ON FA.ArticleId=A.id
    JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
    LEFT JOIN Trespasser T ON FT.TrespasserId=T.Id
    LEFT JOIN Country C ON T.CountryId = C.Id

  WHERE F.ProtocolId>0 AND F.StatusTypeId=13 AND F.FineTypeId=2 AND
        (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL);




//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_mgmt_Report
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_mgmt_Report;
CREATE VIEW V_mgmt_Report AS
  SELECT
    F.Id FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId VehicleCountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,

    VT.Title ViolationTitle,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter,


    FT.TrespasserTypeId,
    FT.Note,
    FT.OwnerAdditionalFee,
    FT.CustomerAdditionalFee,
    FT.ReceiveDate,

    T.Genre,
    T.Address,
    T.ZIP,
    T.Id TrespasserId,
    T.CompanyName,
    T.Surname,
    T.Name,
    T.TaxCode,
    T.VatCode,
    T.LicenseCategory,
    T.LicenseNumber,
    T.LicenseDate,
    T.LicenseOffice,
    T.PEC,
    T.City,
    T.CountryId,
    T.IrideCode,

    C.Title CountryTitle

  FROM Fine F LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
    JOIN FineArticle FA ON F.Id = FA.FineId
    JOIN Article A ON FA.ArticleId=A.id
    JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
    LEFT JOIN Trespasser T ON FT.TrespasserId=T.Id
    LEFT JOIN Country C ON T.CountryId = C.Id

  WHERE F.ProtocolId>0 AND F.StatusTypeId=14 AND (F.FineTypeId=3 OR F.FineTypeId=4) AND
        (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL);






DROP VIEW V_SafoTrespasser;
CREATE VIEW V_SafoTrespasser  AS
  SELECT
    F.Id FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.TimeTypeId,
    F.ControllerId,
    F.ControllerDate,
    F.ControllerTime,
    F.Locality,
    F.Address,
    F.GpsLat,
    F.GpsLong,
    F.VehicleTypeId,
    F.VehiclePlate,
    F.CountryId,
    F.VehicleCountry,
    F.DepartmentId,
    F.VehicleBrand,
    F.VehicleModel,
    F.VehicleColor,
    F.VehicleMass,
    F.VehicleLastRevision,
    F.PreviousId,
    F.Note,
    F.FineTypeId,
    F.IuvCode,
    F.KindCreateDate,
    F.KindSendDate,
    F.ProtocolIdAssigned,
    F.PagoPA1,
    F.PagoPA2,


    CO.Qualification  ControllerQualification,
    CO.Name ControllerName,
    CO.Code ControllerCode,

    TMT.Title TimeTitle,
    TMT.DescriptionIta TimeDescriptionIta,
    TMT.DescriptionEng TimeDescriptionEng,
    TMT.DescriptionGer TimeDescriptionGer,
    TMT.DescriptionSpa TimeDescriptionSpa,
    TMT.DescriptionFre TimeDescriptionFre,
    TMT.DescriptionRom TimeDescriptionRom,
    TMT.DescriptionPor TimeDescriptionPor,
    TMT.DescriptionPol TimeDescriptionPol,
    TMT.DescriptionHol TimeDescriptionHol,
    TMT.DescriptionAlb TimeDescriptionAlb,
    TMT.DescriptionDen TimeDescriptionDen,



    VT.Title ViolationTitle,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.DetectorId,
    FA.Speed,
    FA.SpeedLimit,
    FA.SpeedControl,
    FA.ViolationTypeId,
    FA.ReasonId,
    FA.Fee,
    FA.MaxFee,
    FA.TimeTLightFirst,
    FA.TimeTLightSecond,
    FA.ArticleNumber,
    FA.TrespasserId1_180,
    FA.ExpirationDate,

    ST.Title StatusTitle,



    A.Article,
    A.Paragraph,
    A.Letter,
    A.Id1,
    A.Id2,
    A.Id3,
    A.DescriptionIta ArticleDescriptionIta,
    A.DescriptionEng ArticleDescriptionEng,
    A.DescriptionGer ArticleDescriptionGer,
    A.DescriptionSpa ArticleDescriptionSpa,
    A.DescriptionFre ArticleDescriptionFre,
    A.AdditionalTextIta ArticleAdditionalTextIta,
    A.DescriptionRom ArticleDescriptionRom,
    A.DescriptionPor ArticleDescriptionPor,
    A.DescriptionPol ArticleDescriptionPol,
    A.DescriptionHol ArticleDescriptionHol,
    A.DescriptionAlb ArticleDescriptionAlb,
    A.DescriptionDen ArticleDescriptionDen,
    A.ArticleLetterAssigned,

    R.TitleIta ReasonTitleIta,
    R.TitleEng ReasonTitleEng,
    R.TitleGer ReasonTitleGer,
    R.TitleSpa ReasonTitleSpa,
    R.TitleFre ReasonTitleFre,
    R.TitleRom ReasonTitleRom,
    R.TitlePor ReasonTitlePor,
    R.TitlePol ReasonTitlePol,
    R.TitleHol ReasonTitleHol,
    R.TitleAlb ReasonTitleAlb,
    R.TitleDen ReasonTitleDen,

    R.DescriptionIta ReasonDescriptionIta,

    VET.TitleIta VehicleTitleIta,
    VET.TitleEng VehicleTitleEng,
    VET.TitleGer VehicleTitleGer,
    VET.TitleSpa VehicleTitleSpa,
    VET.TitleFre VehicleTitleFre,
    VET.TitleRom VehicleTitleRom,
    VET.TitlePor VehicleTitlePor,
    VET.TitlePol VehicleTitlePol,
    VET.TitleHol VehicleTitleHol,
    VET.TitleAlb VehicleTitleAlb,
    VET.TitleDen VehicleTitleDen,

    FT.TrespasserId,
    FT.TrespasserTypeId,
    FT.OwnerAdditionalFee,
    FT.CustomerAdditionalFee,
    FT.ReceiveDate,
    FT.FineSendDate,
    FT.FineNotificationDate,
    FT.FineNotificationType,


    TT.Title TrespasserTitle,

    T.CompanyName,
    T.TaxCode,
    T.VatCode,

    C.Title CityTitle,
    C.ZIP CityZIP,

    P.Title ProvinceTitle,
    P.ShortTitle ProvinceShortTitle,

    VTL.ViolationLetterAssigned

  FROM Fine F
    INNER JOIN FineArticle FA ON F.Id=FA.FineId
    INNER JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
    INNER JOIN Controller CO ON F.ControllerId=CO.Id
    INNER JOIN TimeType TMT ON TMT.Id=F.TimeTypeId
    INNER JOIN StatusType ST ON F.StatusTypeId = ST.Id
    INNER JOIN Reason R ON FA.ReasonId = R.Id
    INNER JOIN Article A ON A.Id = FA.ArticleId
    INNER JOIN sarida.City C ON C.Id = F.Locality
    INNER JOIN sarida.Province P ON C.ProvinceId = P.Id
    INNER JOIN VehicleType VET ON F.VehicleTypeId=VET.Id
    INNER JOIN FineTrespasser FT ON F.Id=FT.FineId
    INNER JOIN TrespasserType TT ON FT.TrespasserTypeId=TT.Id
    INNER JOIN Trespasser T ON FT.TrespasserId=T.Id
    LEFT JOIN ViolationTypeLetter VTL ON VT.Id=VTL.ViolationTypeId aND F.CityId=VTL.CityId

 WHERE
    (T.TaxCode IN (SELECT TaxCode FROM SafoTrespasser)
     OR T.TaxCode IN (SELECT VATCode FROM SafoTrespasser))
    AND F.StatusTypeId=10 AND F.CountryId='Z000'
    AND F.Id NOT IN (SELECT FineId FROM TMP_SafoFineUpload) AND FT.TrespasserTypeId=1;



//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_mgmt_Fine_List
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_mgmt_Fine_List;
CREATE VIEW V_mgmt_Fine_List AS
  SELECT
    F.Id FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId VehicleCountryId,
    F.ExternalProtocol,
    F.ExternalDate,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,


    VT.Title ViolationTitle,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    FH.FlowDate,
    FH.PrintDate,
    FH.SendDate,
    FH.ResultId,
    FH.DeliveryDate,

    FP.Id FinePaymentId,
    FP.PaymentDate,

    FC.CommunicationDate,

    FN.ValidatedAddress,

    FD.Documentation,

    FDis.DisputeId


  FROM Fine F JOIN FineArticle FA ON F.Id = FA.FineId
    JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
    LEFT JOIN FineHistory FH ON FH.FineId= F.Id AND FH.NotificationTypeId=6
    LEFT JOIN FinePayment FP ON FP.FineId = F.Id
    LEFT JOIN FineCommunication FC ON FC.FineId = F.Id
    LEFT JOIN FineNotification FN ON FN.FineId = F.Id
    LEFT JOIN FineDocumentation FD ON FD.FineId=F.Id AND FD.DocumentationTypeId=2
LEFT JOIN FineDispute FDis ON FDis.FineId=F.Id


  WHERE F.ProtocolId>0 AND F.StatusTypeId>11



DROP VIEW V_CustomerParameter;
CREATE VIEW V_CustomerParameter AS
  SELECT
    Ci.TaxCode ManagerTaxCode,
    Ci.VAT ManagerVAT,
    Ci.Country ManagerCountry,
    Ci.Web ManagerWeb,
    Ci.NationalProtocolLetterType1,
    Ci.ForeignProtocolLetterType1,
    Ci.NationalProtocolLetterType2,
    Ci.ForeignProtocolLetterType2,

    C.CityId,
    C.ManagerAdditionalName,
    C.ManagerName,
    C.ManagerSector,
    C.ManagerCity,
    C.ManagerProvince,
    C.ManagerAddress,
    C.ManagerZIP,
    C.ManagerPhone,
    C.ManagerFax,
    C.ManagerMail,
    C.ManagerPEC,
    C.ManagerInfo,
    C.ManagerProcessName,
    C.ManagerDataEntryName,
    C.ManagerSignName,
    C.NationalBankOwner,
    C.NationalBankName,
    C.NationalBankAccount,
    C.NationalBankIban,
    C.NationalBankSwift,
    C.NationalBankMgmt,
    C.ForeignBankOwner,
    C.ForeignBankName,
    C.ForeignBankAccount,
    C.ForeignBankIban,
    C.ForeignBankSwift,
    C.ForeignBankMgmt,
    C.Reference,
    C.LumpSum,
    C.PDFRefPrint,
    C.FifthField,
    C.DigitalSignature,
    C.ExternalRegistration,
    C.NationalAnticipateCost,
    C.ForeignAnticipateCost,
    C.ReturnPlace,
    C.CityUnion,
    C.FinePaymentSpecificationType,
    C.FinePDFList,
    C.ChiefControllerList,
    C.Validation,
    C.MCTCUserName,
    C.MCTCPassword,
    C.MCTCDate,
    C.NationalMod23LSubject,
    C.NationalMod23LCustomerName,
    C.NationalMod23LCustomerSubject,
    C.NationalMod23LCustomerAddress,
    C.NationalMod23LCustomerCity,
    C.ForeignMod23LSubject,
    C.ForeignMod23LCustomerName,
    C.ForeignMod23LCustomerSubject,
    C.ForeignMod23LCustomerAddress,
    C.ForeignMod23LCustomerCity,
    C.LicensePointOffice,
    C.LicensePointCode,
    C.LicensePointFtpUser,
    C.LicensePointFtpPassword,
    C.LicensePointPaymentCompletion,
    C.PagoPAPayment,
    C.PagoPAAlias,
    C.PagoPAIban,
    C.ReminderAdditionalFee,
    C.RegularPostalFine,


    PD126N.Automatic Data126BisNationalAutomatic,
    PD126N.ControllerId Data126BisNationalControllerId,
    PD126N.Rigid Data126BisNationalRigid,
    PD126N.WaitDay Data126BisNationalWaitDay,
    PD126N.DayAccepted Data126BisNationalDayAccepted,
    PD126N.RangeDayMin Data126BisNationalRangeDayMin,
    PD126N.RangeDayMax Data126BisNationalRangeDayMax,
    PD126N.Disabled Data126BisNationalDisabled,

    PD126F.Automatic Data126BisForeignAutomatic,
    PD126F.ControllerId Data126BisForeignControllerId,
    PD126F.Rigid Data126BisForeignRigid,
    PD126F.WaitDay Data126BisForeignWaitDay,
    PD126F.DayAccepted Data126BisForeignDayAccepted,
    PD126F.RangeDayMin Data126BisForeignRangeDayMin,
    PD126F.RangeDayMax Data126BisForeignRangeDayMax,
    PD126F.Disabled Data126BisForeignDisabled,

    PD180N.Automatic Data180NationalAutomatic,
    PD180N.ControllerId Data180NationalControllerId,
    PD180N.Rigid Data180NationalRigid,
    PD180N.WaitDay Data180NationalWaitDay,
    PD180N.DayAccepted Data180NationalDayAccepted,
    PD180N.RangeDayMin Data180NationalRangeDayMin,
    PD180N.RangeDayMax Data180NationalRangeDayMax,
    PD180N.Disabled Data180NationalDisabled,

    PD180F.Automatic Data180ForeignAutomatic,
    PD180F.ControllerId Data180ForeignControllerId,
    PD180F.Rigid Data180ForeignRigid,
    PD180F.WaitDay Data180ForeignWaitDay,
    PD180F.DayAccepted Data180ForeignDayAccepted,
    PD180F.RangeDayMin Data180ForeignRangeDayMin,
    PD180F.RangeDayMax Data180ForeignRangeDayMax,
    PD180F.Disabled Data180ForeignDisabled,

    PDPN.Automatic DataPaymentNationalAutomatic,
    PDPN.Rigid DataPaymentNationalRigid,
    PDPN.WaitDay DataPaymentNationalWaitDay,
    PDPN.RangeDayMin DataPaymentNationalRangeDayMin,
    PDPN.RangeDayMax DataPaymentNationalRangeDayMax,
    PDPN.AmountLimit DataPaymentNationalAmountLimit,
    PDPN.ReducedPaymentDayAccepted DataPaymentNationalReducedPaymentDayAccepted,
    PDPN.PaymentDayAccepted DataPaymentNationalPaymentDayAccepted,
    PDPN.Disabled DataPaymentNationalDisabled,


    PDPF.Automatic DataPaymentForeignAutomatic,
    PDPF.Rigid DataPaymentForeignRigid,
    PDPF.WaitDay DataPaymentForeignWaitDay,
    PDPF.RangeDayMin DataPaymentForeignRangeDayMin,
    PDPF.RangeDayMax DataPaymentForeignRangeDayMax,
    PDPF.AmountLimit DataPaymentForeignAmountLimit,
    PDPF.ReducedPaymentDayAccepted DataPaymentForeignReducedPaymentDayAccepted,
    PDPF.PaymentDayAccepted DataPaymentForeignPaymentDayAccepted,
    PDPF.Disabled DataPaymentForeignDisabled

    FROM sarida.City Ci JOIN Customer C ON Ci.Id = C.CityId
    LEFT JOIN ProcessingData126BisForeign PD126F ON C.CityId = PD126F.CityId
    LEFT JOIN ProcessingData126BisNational PD126N ON C.CityId = PD126N.CityId
    LEFT JOIN ProcessingData180Foreign PD180F ON C.CityId = PD180F.CityId
    LEFT JOIN ProcessingData180National PD180N ON C.CityId = PD180N.CityId
    LEFT JOIN ProcessingDataPaymentForeign PDPF ON C.CityId = PDPF.CityId
    LEFT JOIN ProcessingDataPaymentNational PDPN ON C.CityId = PDPN.CityId;



DROP VIEW V_Controller;

CREATE VIEW V_Controller AS
SELECT
  CO.Id,
  CO.Name,
  CO.Qualification,
  CO.Code,
  CO.Sign,
  CO.CityId,

  C.ManagerCity Title

FROM Controller CO JOIN Customer C
    on CO.CityId = C.CityId
;







CREATE VIEW V_CityProvince AS
  SELECT C.Id, C.Title, C.ZIP, P.Title ProvinceTitle, P.ShortTitle ProvinceShortTitle
  FROM City C JOIN Province P ON C.ProvinceId = P.Id



//WHERE FT.TrespasserTypeId!=10 AND FT.TrespasserTypeId IS NOT NULL;


/*
4 7 9 24 27 31 38 41
INSERT INTO `UserCity` (`MainMenuId`, `UserId`, `CityId`, `CityYear`) VALUES ('3', '24', 'C559', '2019'), ('3', '27', 'C559', '2019')
, ('3', '38', 'C559', '2019'), ('3', '41', 'C559', '2019');

*/


//




//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_mgmt_ReportTrespasser
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_mgmt_ReportTrespasser;
CREATE VIEW V_mgmt_ReportTrespasser AS
  SELECT
    F.Id FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId VehicleCountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,
    F.ControllerId,
    F.VehicleTypeId,

    VT.Title ViolationTitle,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter,

    GROUP_CONCAT(FT.TrespasserTypeId SEPARATOR '|') TrespasserTypeId,
    GROUP_CONCAT(IFNULL(T.Id,'') SEPARATOR '|') TrespasserId,
    GROUP_CONCAT(CONCAT(IFNULL(T.CompanyName,''),' ',IFNULL(T.Surname,''),' ',IFNULL(T.Name,'')) SEPARATOR '|') TrespasserFullName,
    GROUP_CONCAT(IFNULL(FT.FineNotificationDate,'') SEPARATOR '|') FineNotificationDate

  FROM Fine F
    JOIN FineArticle FA ON F.Id = FA.FineId
    JOIN Article A ON FA.ArticleId=A.Id
    JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
    LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
    LEFT JOIN Trespasser T ON FT.TrespasserId=T.Id

  WHERE F.ProtocolId>0 AND F.StatusTypeId=14 AND (F.FineTypeId=3 OR F.FineTypeId=4)

  GROUP BY
    F.Id,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,
    F.ControllerId,
    F.VehicleTypeId,

    VT.Title,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter
;

//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_mgmt_WarningTrespasser
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_mgmt_WarningTrespasser;
CREATE VIEW V_mgmt_WarningTrespasser AS
  SELECT
    F.Id FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId VehicleCountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,
    F.ControllerId,
    F.VehicleTypeId,

    VT.Title ViolationTitle,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter,

    GROUP_CONCAT(FT.TrespasserTypeId SEPARATOR '|') TrespasserTypeId,
    GROUP_CONCAT(IFNULL(T.Id,'') SEPARATOR '|') TrespasserId,
    GROUP_CONCAT(CONCAT(IFNULL(T.CompanyName,''),' ',IFNULL(T.Surname,''),' ',IFNULL(T.Name,'')) SEPARATOR '|') TrespasserFullName,
    GROUP_CONCAT(IFNULL(FT.FineNotificationDate,'') SEPARATOR '|') FineNotificationDate

  FROM Fine F
    JOIN FineArticle FA ON F.Id = FA.FineId
    JOIN Article A ON FA.ArticleId=A.Id
    JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
    LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
    LEFT JOIN Trespasser T ON FT.TrespasserId=T.Id

  WHERE F.ProtocolId>0 AND F.StatusTypeId=13 AND F.FineTypeId=2

  GROUP BY
    F.Id,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,
    F.ControllerId,
    F.VehicleTypeId,

    VT.Title,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter
  ;


//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_mgmt_ViolationTrespasser
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_mgmt_ViolationTrespasser;
CREATE VIEW V_mgmt_ViolationTrespasser AS
  SELECT
    F.Id FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId VehicleCountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,
    F.ControllerId,
    F.VehicleTypeId,

    VT.Title ViolationTitle,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter,

    GROUP_CONCAT(FT.TrespasserTypeId SEPARATOR '|') TrespasserTypeId,
    GROUP_CONCAT(IFNULL(T.Id,'') SEPARATOR '|') TrespasserId,
    GROUP_CONCAT(CONCAT(IFNULL(T.CompanyName,''),' ',IFNULL(T.Surname,''),' ',IFNULL(T.Name,'')) SEPARATOR '|') TrespasserFullName,
    GROUP_CONCAT(IFNULL(FT.FineNotificationDate,'') SEPARATOR '|') FineNotificationDate

  FROM Fine F
    JOIN FineArticle FA ON F.Id = FA.FineId
    JOIN Article A ON FA.ArticleId=A.Id
    JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
    LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
    LEFT JOIN Trespasser T ON FT.TrespasserId=T.Id

  WHERE F.ProtocolId=0 AND F.StatusTypeId>=1 AND F.StatusTypeId<=10

  GROUP BY
    F.Id,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,
    F.ControllerId,
    F.VehicleTypeId,

    VT.Title,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter
  ;



//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_mgmt_RegularViolationTrespasser
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_mgmt_RegularViolationTrespasser;
CREATE VIEW V_mgmt_RegularViolationTrespasser AS
  SELECT
    F.Id FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId VehicleCountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,
    F.ControllerId,
    F.VehicleTypeId,

    VT.Title ViolationTitle,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter,

    GROUP_CONCAT(FT.TrespasserTypeId SEPARATOR '|') TrespasserTypeId,
    GROUP_CONCAT(IFNULL(T.Id,'') SEPARATOR '|') TrespasserId,
    GROUP_CONCAT(CONCAT(IFNULL(T.CompanyName,''),' ',IFNULL(T.Surname,''),' ',IFNULL(T.Name,'')) SEPARATOR '|') TrespasserFullName,
    GROUP_CONCAT(IFNULL(FT.FineNotificationDate,'') SEPARATOR '|') FineNotificationDate

  FROM Fine F
    JOIN FineArticle FA ON F.Id = FA.FineId
    JOIN Article A ON FA.ArticleId=A.Id
    JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
    LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
    LEFT JOIN Trespasser T ON FT.TrespasserId=T.Id

  WHERE F.ProtocolId=0 AND F.StatusTypeId>=7 AND F.StatusTypeId<=9

  GROUP BY
    F.Id,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,
    F.ControllerId,
    F.VehicleTypeId,

    VT.Title,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter
;

//////////////////////////////////////////////////////////////////////////////////////////////////
//                                        V_mgmt_FineTrespasser
//////////////////////////////////////////////////////////////////////////////////////////////////
DROP VIEW V_mgmt_FineTrespasser;
CREATE VIEW V_mgmt_FineTrespasser AS
  SELECT
    F.Id FineId,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId VehicleCountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,
    F.ControllerId,
    F.VehicleTypeId,
    F.CountryId,

    VT.Title ViolationTitle,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter,

    GROUP_CONCAT(FT.TrespasserTypeId SEPARATOR '|') TrespasserTypeId,
    GROUP_CONCAT(IFNULL(T.Id,'') SEPARATOR '|') TrespasserId,
    GROUP_CONCAT(CONCAT(IFNULL(T.CompanyName,''),' ',IFNULL(T.Surname,''),' ',IFNULL(T.Name,'')) SEPARATOR '|') TrespasserFullName,
    GROUP_CONCAT(IFNULL(FT.FineNotificationDate,'') SEPARATOR '|') FineNotificationDate,

    PR.Id PaymentRateId

  FROM Fine F
    JOIN FineArticle FA ON F.Id = FA.FineId
    JOIN Article A ON FA.ArticleId=A.Id
    JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
    LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
    LEFT JOIN Trespasser T ON FT.TrespasserId=T.Id
    LEFT JOIN PaymentRate PR ON PR.FineId=F.Id



  WHERE F.ProtocolId>0 AND F.StatusTypeId>=12

  GROUP BY
    F.Id,
    F.Code,
    F.CityId,
    F.ProtocolId,
    F.ProtocolYear,
    F.StatusTypeId,
    F.FineDate,
    F.FineTime,
    F.VehiclePlate,
    F.CountryId,
    F.ExternalProtocol,
    F.ExternalYear,
    F.PreviousId,
    F.FineTypeId,
    F.Locality,
    F.ControllerId,
    F.VehicleTypeId,
    F.CountryId,

    VT.Title,
    VT.RuleTypeId,

    FA.ArticleId,
    FA.ViolationTypeId,
    FA.ArticleNumber,

    A.Article,
    A.Paragraph,
    A.Letter,
    PR.Id
;





DROP VIEW mgmt_FineHistory_Trespasser;
CREATE View mgmt_FineHistory_Trespasser AS
  SELECT
FH.FineId,
FH.FlowDate,
FH.PrintDate,
FH.SendDate,
FH.ResultId,
FH.DeliveryDate,

FP.Id PaymentId,
FP.PaymentDate,


FC.ReducedPoint,
FC.ReducedDate,
FC.CommunicationDate,

FD.Documentation,

D.Id AS DisputeId,
D.GradeTypeId,
D.DateFile,
D.OfficeCity,
D.OfficeId,

O.TitleIta OfficeTitle,

FDi.DisputeStatusId









FROM
FineHistory FH

LEFT JOIN FinePayment FP ON FP.FineId = FH.FineId
LEFT JOIN FineCommunication FC  ON FC.FineId = FH.FineId AND (FC.TrespasserTypeId=1 OR FC.TrespasserTypeId = 3)
LEFT JOIN FineDocumentation FD ON FD.FineId = FH.FineId AND FD.DocumentationTypeId=2
LEFT JOIN FineDispute FDi ON FH.FineId = FDi.FineId
LEFT JOIN Dispute D ON FDi.DisputeId=D.Id
LEFT JOIN Office O ON O.Id=D.OfficeId

WHERE
FH.NotificationTypeId=6
