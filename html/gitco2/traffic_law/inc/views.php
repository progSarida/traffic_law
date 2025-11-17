<?php
const LICENSPOINT_ALIASES = array('Id' => 'f.Id', 'LicensePointProcedure' => 'fn.LicensePointProcedure', '126BisProcedure' => 'fn.126BisProcedure', 'Code' => 'f.Code', 'StatusTypeId' => 'f.StatusTypeId',
    'VehicleTypeId' => 'f.VehicleTypeId', 'CityId' => 'f.CityId', 'ProtocolId' => 'f.ProtocolId',
    'ProtocolYear' => 'f.ProtocolYear', 'FineDate' => 'f.FineDate', 'FineTime' => 'f.FineTime',
    'VehiclePlate' => 'f.VehiclePlate', 'IdCountryId' => 'f.CountryId', 'CountryId' => 'f.CountryId',
    'FineTypeId' => 'f.FineTypeId', 'TrespasserId' => 'fc.TrespasserId', 'TrespasserTypeId' => 'fc.TrespasserTypeId',
    'TrespasserCode' => 't.Code', 'BornDate' => 't.BornDate', 'BornPlace' => 't.BornPlace', 'BornCountryId' => 't.BornCountryId',
    'CommunicationProtocol' => 'fc.CommunicationProtocol', 'CommunicationDate' => 'fc.CommunicationDate', 'LicensePointId' => 'fc.LicensePointId',
    'ReducedDate' => 'fc.ReducedDate', 'ReducedPoint' => 'fc.ReducedPoint',
    'CommunicationStatus' => 'fc.CommunicationStatus', 'RuleTypeId' => 'vt.RuleTypeId', 'ArticleNumber' => 'fa.ArticleNumber', 'Article' => 'a.Article',
    'ArticleId' => 'a.Id', 'Paragraph' => 'a.Paragraph', 'Letter' => 'a.Letter', 'LicensePoint' => 'at.LicensePoint',
    'YoungLicensePoint' => 'at.YoungLicensePoint', 'LicensePointCode1' => 'at.LicensePointCode1',
    'LicensePointCode2' => 'at.LicensePointCode2', 'LossLicense' => 'at.LossLicense', 'SuspensionLicense' => 'at.SuspensionLicense',
    'AdditionalSanctionId' => 'at.AdditionalSanctionId', 'Surname' => 't.Surname', 'Name' => 't.Name', 'TaxCode' => 't.TaxCode',
    'VatCode' => 't.VatCode', 'ZIP' => 't.ZIP', 'LicenseNumber' => 't.LicenseNumber',
    'DocumentTypeId' => 't.DocumentTypeId', 'DocumentCountryId' => 't.DocumentCountryId',
    'LicenseDate' => 't.LicenseDate', 'LicenseCategory' => 't.LicenseCategory', 'TrespasserCountryId' => 't.CountryId',
    'NotificationDate' => 'fn.NotificationDate', 'PaymentDate' => 'fp.PaymentDate', 'DisputeId' => 'fd.DisputeId',
    'LicenseYear' => "timestampdiff(year,t.LicenseDate,f.FineDate)", 'LicensePointMexDescription' => 't.LicenseOffice',
    'LicenseOffice' => 'lpm.Description', 'LicenseOffice' => 't.LicenseOffice', 'Habitual' => 'at.Habitual',
    'DocumentTypeTitle' => 'tpt.Title', 'Address' => 't.Address', 'City' => 't.City');

const V_SEARCH_FINETRESPASSER = array(
    'aliases' => array(
        'FineId' => 'f.Id', 
        'Code' => 'f.Code', 
        'CityId' => 'f.CityId',
        'ProtocolId' => 'f.ProtocolId', 
        'ProtocolYear' => 'f.ProtocolYear', 
        'StatusTypeId' => 'f.StatusTypeId', 
        'FineDate' => 'f.FineDate',
        'FineTime' => 'f.FineTime', 
        'VehiclePlate' => 'f.VehiclePlate',  
        'TrespasserTypeId' => 'ft.TrespasserTypeId',
        'ReceiveDate' => 'ft.ReceiveDate', 
        'RuleTypeId' => 'vt.RuleTypeId',
        'Genre' => 't.Genre', 
        'Address' => 't.Address', 
        'ZIP' => 't.ZIP', 
        'TrespasserId' => 't.id', 
        'CompanyName' => 't.CompanyName',
        'Surname' => 't.Surname', 
        'Name' => 't.Name', 
        'City' => 't.City', 
        'CountryId' => 't.CountryId', 
        'PagoPA1' => 'f.PagoPA1', 
        'PagoPA2' => 'f.PagoPA2'
    ),
    "from" => "traffic_law.Fine f 
        join traffic_law.FineArticle fa on fa.FineId = f.Id
        join traffic_law.ViolationType vt on vt.Id = fa.ViolationTypeId
        left join traffic_law.FineTrespasser ft on f.Id = ft.FineId 
        left join traffic_law.Trespasser t on ft.TrespasserId = t.Id
        left join traffic_law.Country c on (t.CountryId = convert(c.Id using utf8))",
    "where" => "(((f.ProtocolId > 0) and ((ft.TrespasserTypeId = 1) or (ft.TrespasserTypeId = 11) or (ft.TrespasserTypeId = 2) or
         isnull(ft.TrespasserTypeId)) and (f.StatusTypeId >= 12)) or (f.StatusTypeId = 9))"
);

const V_LICENSEPOINT1 = array('aliases' => LICENSPOINT_ALIASES, "from" => "(((((((((((traffic_law.Fine f
join traffic_law.FineCommunication fc on
    ((f.Id = fc.FineId)))
    join Customer c on (f.CityId=c.CityId)
join traffic_law.Trespasser t on
    ((fc.TrespasserId = t.Id)))
join traffic_law.FineArticle fa on
    ((f.Id = fa.FineId)))
join traffic_law.ViolationType vt on
    ((vt.Id = fa.ViolationTypeId)))
join traffic_law.Article a on
    ((fa.ArticleId = a.Id)))
join traffic_law.ArticleTariff at on
    (((fa.ArticleId = at.ArticleId)
    and (f.ProtocolYear = at.Year))))
left join traffic_law.FineNotification fn on
    ((f.Id = fn.FineId)))
left join traffic_law.FinePayment fp on
    ((f.Id = fp.FineId)))
left join traffic_law.FineDispute fd on
    ((f.Id = fd.FineId)))
left join traffic_law.TrespasserDocumentType tpt on
    ((t.DocumentTypeId = tpt.Id)))
left join traffic_law.LicensePointMex lpm on
    ((fc.LicensePointId = lpm.Id)))", "where" => "((fc.TrespasserTypeId in (1,3,12) or
    	(fc.TrespasserTypeId=11 and
    	f.Id not in (select FineId
    				from FineTrespasser ft1
    				join Trespasser t1
    				on (t1.Id=ft1.TrespasserId) where ft1.TrespasserTypeId=12 and ft1.FineId=f.Id)))
    and (fn.LicensePointProcedure >0 or (fn.LicensePointProcedure is null and f.Id not in (
    select
        traffic_law.TMP_LicensePointProcedure.FineId
    from
        traffic_law.TMP_LicensePointProcedure)))
    and (not(f.Id in (
    select
        traffic_law.FineDispute.FineId
    from
        traffic_law.FineDispute
    where
        ((traffic_law.FineDispute.DisputeStatusId = 1)
        or (traffic_law.FineDispute.DisputeStatusId = 4)))))
    and ((timestampdiff(day,
    fn.NotificationDate,
    curdate()) > 60+coalesce(c.LicensePointDecurtationDays,0))
    or (fp.PaymentDate is not null))
    and (((f.StatusTypeId > 10)
    and (f.StatusTypeId <= 30))
    or (f.StatusTypeId >= 40))
    and (t.Genre in ('M', 'F') or (t.Genre = 'D' and LegalFormId in (20,21,22,23,24,36))))");

const V_LICENSEPOINT0 = array('aliases' => LICENSPOINT_ALIASES, "from" => "traffic_law.Fine f
join traffic_law.FineCommunication fc on
    (f.Id = fc.FineId)
    join Customer c on (f.CityId=c.CityId)
join traffic_law.FinePayment fp on
  (f.Id=fp.FineId)
join traffic_law.Trespasser t on
    (fc.TrespasserId = t.Id)
join traffic_law.FineArticle fa on
    (f.Id = fa.FineId)
join traffic_law.ViolationType vt on
    (fa.ViolationTypeId = vt.Id)
join traffic_law.Article a on
    (fa.ArticleId = a.Id)
join traffic_law.ArticleTariff at on
    (fa.ArticleId = at.ArticleId)
    and (f.ProtocolYear = at.Year)
left join traffic_law.FineNotification fn on
    (f.Id = fn.FineId)
left join traffic_law.FineDispute fd on
    (f.Id = fd.FineId)
left join traffic_law.TrespasserDocumentType tpt on
    (t.DocumentTypeId = tpt.Id)
left join traffic_law.LicensePointMex lpm on
    (fc.LicensePointId = lpm.Id)",
    "where" => "((fc.TrespasserTypeId in (1,3,12) or
    	(fc.TrespasserTypeId=11 and
    	f.Id not in (select FineId
    				from FineTrespasser ft1
    				join Trespasser t1
    				on (t1.Id=ft1.TrespasserId) where ft1.TrespasserTypeId=12 and ft1.FineId=f.Id)))
    and (fn.LicensePointProcedure >0 or (fn.LicensePointProcedure is null and f.Id not in (
    select
        traffic_law.TMP_LicensePointProcedure.FineId
    from
        traffic_law.TMP_LicensePointProcedure)))
    and (not(f.Id in (
    select
        traffic_law.FineDispute.FineId
    from
        traffic_law.FineDispute
    where
        ((traffic_law.FineDispute.DisputeStatusId = 1)
        or (traffic_law.FineDispute.DisputeStatusId = 4)))))
    and ((timestampdiff(day,
    fn.NotificationDate,
    curdate()) > 60+coalesce(c.LicensePointDecurtationDays,0))
    or isnull(fn.NotificationDate))
    and (((f.StatusTypeId >= 25)
    and (f.StatusTypeId <= 30))
    or (f.StatusTypeId >= 40))
    and (t.Genre in ('M', 'F') or (t.Genre = 'D' and LegalFormId in (20,21,22,23,24,36)))) ");

const MGMT_FINETRESPASSER = array(
    'aliases' => array(
        'FineId' => 'F.Id',
        'Code' => 'F.Code',
        'CityId' => 'F.CityId',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'StatusTypeId' => 'F.StatusTypeId',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'VehiclePlate' => 'F.VehiclePlate',
        'VehicleCountryId' => 'F.CountryId',
        'ExternalProtocol' => 'F.ExternalProtocol',
        'ExternalYear' => 'F.ExternalYear',
        'PreviousId' => 'F.PreviousId',
        'FineTypeId' => 'F.FineTypeId',
        'Locality' => 'F.Locality',
        'ControllerId' => 'F.ControllerId',
        'VehicleTypeId' => 'F.VehicleTypeId',
        'CountryId' => 'F.CountryId',
        'PagoPA1' => 'F.PagoPA1',
        'PagoPA2' => 'F.PagoPA2',
        'RegDate' => 'F.RegDate',
        'RegTime' => 'F.RegTime',
        'KindCreateDate' => 'F.KindCreateDate',
        'KindSendDate' => 'F.KindSendDate',
        'ViolationTitle' => 'VT.Title',
        'RuleTypeId' => 'VT.RuleTypeId',
        'ArticleId' => 'FA.ArticleId',
        'ViolationTypeId' => 'FA.ViolationTypeId',
        'ArticleNumber' => 'FA.ArticleNumber',
        'Article' => 'A.Article',
        'Paragraph' => 'A.Paragraph',
        'Letter' => 'A.Letter',
        'TrespasserTypeId' => "group_concat(FT.TrespasserTypeId separator '|')",
        'TrespasserId' => "group_concat(ifnull(T.Id,'') separator '|')",
        'TrespasserFullName' => "group_concat(concat(ifnull(T.CompanyName,''),' ',ifnull(T.Surname,''),' ',ifnull(T.Name,'')) separator '|')",
        'PEC' => "group_concat(ifnull(T.PEC,'') separator '|')",
        'FineNotificationDate' => "group_concat(ifnull(FT.FineNotificationDate,'') separator '|')",
        'FineNotificationType' => "group_concat(ifnull(FT.FineNotificationType,'') separator '|')",
        'PaymentRateId' => 'PR.Id',
        'StatusRateId' => 'PR.StatusRateId',
        '126Bis' => 'at.126Bis',
        'LicensePoint' => 'at.LicensePoint',
        'PresentationDocument' => 'at.PresentationDocument',
        'SuspensionLicense' => 'at.SuspensionLicense',
        'Habitual' => 'at.Habitual',
        'PrefectCommRegDate' => 'FP.RegDate',
        'PrefectCommNotificationDate' => 'FP.NotificationDate',
        'PrefectCommSendDate' => 'FP.SendDate',
        'PrefectCommResultId' => 'FP.ResultId',
        'ResultId' => 'FN.ResultId',
        'NotificationDate' => 'FN.NotificationDate'),
    
    "from" => "Fine F
	join FineArticle FA on F.Id = FA.FineId
	join Article A on FA.ArticleId = A.Id
	join ViolationType VT on FA.ViolationTypeId = VT.Id
	join ArticleTariff at on at.ArticleId = FA.ArticleId AND at.Year = F.ProtocolYear
	left join FineTrespasser FT on F.Id = FT.FineId
    left join FinePrefectCommunication FP on F.Id = FP.FineId
	left join Trespasser T on FT.TrespasserId = T.Id
	left join PaymentRate PR on PR.FineId = F.Id AND PR.Id = (SELECT MAX(JPR.Id) FROM PaymentRate JPR WHERE JPR.FineId = F.Id)
    left join FineNotification FN on FN.FineId = F.Id",
    
    "where" => ' ((F.ProtocolId != 0) and (F.StatusTypeId >= 12 OR F.StatusTypeId IN (8,9))) ',
    
    'groupBy' => 'F.Id,F.Code,F.CityId,F.ProtocolId,F.ProtocolYear,F.StatusTypeId,
	F.FineDate,F.FineTime,F.VehiclePlate,F.CountryId,F.PagoPA1,F.PagoPA2,F.ExternalProtocol,
	F.ExternalYear,F.PreviousId,F.FineTypeId,F.Locality,F.ControllerId,
	F.VehicleTypeId,F.CountryId,
	VT.Title,VT.RuleTypeId,
	FA.ArticleId,FA.ViolationTypeId,FA.ArticleNumber,
	A.Article,A.Paragraph,A.Letter,PR.Id');

const INJ_FINE = array(
    'aliases' => array(
        'FineId' => 'F.Id',
        'Code' => 'F.Code',
        'CityId' => 'F.CityId',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'StatusTypeId' => 'F.StatusTypeId',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'VehiclePlate' => 'F.VehiclePlate',
        'VehicleCountryId' => 'F.CountryId',
        'ExternalProtocol' => 'F.ExternalProtocol',
        'ExternalYear' => 'F.ExternalYear',
        'PreviousId' => 'F.PreviousId',
        'FineTypeId' => 'F.FineTypeId',
        'Locality' => 'F.Locality',
        'ControllerId' => 'F.ControllerId',
        'VehicleTypeId' => 'F.VehicleTypeId',
        'CountryId' => 'F.CountryId',
        'PagoPA1' => 'F.PagoPA1',
        'PagoPA2' => 'F.PagoPA2',
        'RegDate' => 'F.RegDate',
        'RegTime' => 'F.RegTime',
        'KindCreateDate' => 'F.KindCreateDate',
        'KindSendDate' => 'F.KindSendDate',
        'ViolationTitle' => 'VT.Title',
        'RuleTypeId' => 'VT.RuleTypeId',
        'ArticleId' => 'FA.ArticleId',
        'ViolationTypeId' => 'FA.ViolationTypeId',
        'ArticleNumber' => 'FA.ArticleNumber',
        'Article' => 'A.Article',
        'Paragraph' => 'A.Paragraph',
        'Letter' => 'A.Letter',
        'TrespasserTypeId' => 
            "NULLIF(GROUP_CONCAT(FT.TrespasserTypeId SEPARATOR '|'), '|')",
        'TrespasserId' => 
            "NULLIF(GROUP_CONCAT(IFNULL(T.id,'') SEPARATOR '|'), '|')",
        'TrespasserFullName' => 
            "NULLIF(GROUP_CONCAT(CONCAT_WS(' ', NULLIF(TRIM(T.CompanyName),''), NULLIF(TRIM(T.Surname),''), NULLIF(TRIM(T.Name),'')) SEPARATOR '|'), '|')",
        'PEC' => 
            "NULLIF(GROUP_CONCAT(COALESCE(TRIM(T.PEC),'') SEPARATOR '|'), '|')",
        'FineNotificationDate' => 'FN.NotificationDate',
        //Come data di notifica prendiamo la FineCreateDate di FineTrespasser per i notificati su strada o alla registrazione del verbale, e la
        //Delivery date su FineHistory per quelli notificati in differita con flusso o via messo
        'DeliveryDate' =>
            "NULLIF(NULLIF(GROUP_CONCAT(COALESCE(FH.DeliveryDate, FT.FineCreateDate, FT.ReceiveDate, '') SEPARATOR '|'), '|'), '')",
        'FlowDate' => "FH.FlowDate",
        'PaymentId' => "JFP.PaymentId",
        'DisputeId' => "FD.DisputeId",
        'DisputeStatusId' => "FD.DisputeStatusId",
        'DisputeOfficeCity' => "D.OfficeCity",
        'DisputeOfficeTitleIta' => "O.TitleIta",
        'DisputeDateFile' => "D.DateFile",
        'DisputeGradeTypeId' => "D.GradeTypeId",
        'PaymentRateId' => 'PR.Id',
        '126Bis' => 'at.126Bis',
        'LicensePoint' => 'at.LicensePoint',
        'PresentationDocument' => 'at.PresentationDocument',
        'SuspensionLicense' => 'at.SuspensionLicense',
        'Habitual' => 'at.Habitual',
        'ResultId' => 'FN.ResultId'
    ),
    
    "from" => "Fine F
	   LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
	   LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id
	   LEFT JOIN PaymentRate PR ON PR.FineId = F.Id AND PR.Id = (SELECT MAX(JPR.Id) FROM PaymentRate JPR WHERE JPR.FineId = F.Id AND JPR.StatusRateId != ".RATEIZZAZIONE_CHIUSA.")
       LEFT JOIN FineNotification FN ON FN.FineId = F.Id
       LEFT JOIN (SELECT FP.FineId, GROUP_CONCAT(FP.Id separator '|') AS PaymentId FROM FinePayment FP GROUP BY FP.FineId) JFP ON F.Id = JFP.FineId
       LEFT JOIN FineDispute FD ON FD.FineId = F.Id
       LEFT JOIN Dispute D ON D.Id = FD.DisputeId
       LEFT JOIN Office O ON O.Id = D.OfficeId
	   LEFT JOIN FineHistory FH ON FH.FineId = FT.FineId AND FH.NotificationTypeId = 6 AND FH.TrespasserId = FT.TrespasserId AND FT.FineCreateDate IS NULL
       LEFT JOIN Flow FLO ON FLO.Id = FH.FlowId
       JOIN FineArticle FA ON F.Id = FA.FineId
	   JOIN Article A ON FA.ArticleId = A.Id
	   JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
	   JOIN ArticleTariff AT ON AT.ArticleId = FA.ArticleId AND AT.Year = F.ProtocolYear",
    
    "where" => 'F.ProtocolId != 0 AND (F.StatusTypeId >= 12 OR F.StatusTypeId IN (8,9)) ',
    "groupBy" => 'F.Id'
);

/*const MGMT_FINETRESPASSER_PREFECT = array(
 'aliases' => array(
 'MaxFineNotificationDate' => 'max(FT.FineNotificationDate)',
 'MinFineNotificationDate' => 'min(FT.FineNotificationDate)',
 'FineId' => 'F.Id',
 'Code' => 'F.Code',
 'CityId' => 'F.CityId',
 'ProtocolId' => 'F.ProtocolId',
 'ProtocolYear' => 'F.ProtocolYear',
 'StatusTypeId' => 'F.StatusTypeId',
 'FineDate' => 'F.FineDate',
 'FineTime' => 'F.FineTime',
 'VehiclePlate' => 'F.VehiclePlate',
 'VehicleCountryId' => 'F.CountryId',
 'ExternalProtocol' => 'F.ExternalProtocol',
 'ExternalYear' => 'F.ExternalYear',
 'PreviousId' => 'F.PreviousId',
 'FineTypeId' => 'F.FineTypeId',
 'Locality' => 'F.Locality',
 'ControllerId' => 'F.ControllerId',
 'VehicleTypeId' => 'F.VehicleTypeId',
 'CountryId' => 'F.CountryId',
 'PagoPA1' => 'F.PagoPA1',
 'PagoPA2' => 'F.PagoPA2',
 'RegDate' => 'F.RegDate',
 'RegTime' => 'F.RegTime',
 'ViolationTitle' => 'VT.Title',
 'RuleTypeId' => 'VT.RuleTypeId',
 'ArticleId' => 'FA.ArticleId',
 'ViolationTypeId' => 'FA.ViolationTypeId',
 'ArticleNumber' => 'FA.ArticleNumber',
 'Article' => 'A.Article',
 'Paragraph' => 'A.Paragraph',
 'Letter' => 'A.Letter',
 'TrespasserTypeId' => "group_concat(FT.TrespasserTypeId separator '|')",
 'TrespasserId' => "group_concat(ifnull(T.Id,'') separator '|')",
 'TrespasserFullName' => "group_concat(distinct concat(ifnull(T.CompanyName,''),' ',ifnull(T.Surname,''),' ',ifnull(T.Name,'')) separator '|')",
 'FineNotificationDate' => "group_concat(distinct ifnull(FT.FineNotificationDate,'') separator '|')",
 'PaymentRateId' => 'PR.Id',
 '126Bis' => 'at.126Bis',
 'LicensePoint' => 'at.LicensePoint',
 'PresentationDocument' => 'at.PresentationDocument',
 'SuspensionLicense' => 'at.SuspensionLicense',
 'Habitual' => 'at.Habitual',
 'PrefectCommRegDate' => 'FP.RegDate',
 'PrefectCommNotificationDate' => 'FP.NotificationDate',
 'PrefectCommSendDate' => 'FP.SendDate',
 'PrefectCommResultId' => 'FP.ResultId'),
 
 "from" => "
 Fine F INNER JOIN
 FineArticle FA on F.Id = FA.FineId INNER JOIN
 Article A on FA.ArticleId = A.Id INNER JOIN
 ArticleTariff at on at.ArticleId = FA.ArticleId AND at.Year = F.ProtocolYear INNER JOIN
 ViolationType VT on FA.ViolationTypeId = VT.Id  LEFT OUTER JOIN
 FineTrespasser FT on F.Id = FT.FineId LEFT JOIN
 FinePrefectCommunication FP on F.Id = FP.FineId LEFT JOIN
 Trespasser T on FT.TrespasserId = T.Id LEFT JOIN
 PaymentRate PR on PR.FineId = F.Id LEFT OUTER JOIN
 FineTrespasser FT2 ON FT2.TrespasserId = FT.TrespasserId LEFT OUTER JOIN
 Fine F2 ON F2.Id = FT2.FineId AND F2.Id < F.Id AND F2.FineDate > DATE_SUB(F.FineDate, INTERVAL 2 YEAR) AND F2.CityId = F.CityId LEFT OUTER JOIN
 FineArticle FA2 ON F2.Id = FA2.FineId AND FA2.ArticleId = FA.ArticleId ",
 
 "where" => ' (F.ProtocolId > 0) AND
 (F.StatusTypeId >= 12 OR F.StatusTypeId IN (8,9)) AND
 (F2.Id IS NULL OR FA2.FineId IS NOT NULL)',
 
 'groupBy' => 'F.Id,F.Code,F.CityId,F.ProtocolId,F.ProtocolYear,F.StatusTypeId,
 F.FineDate,F.FineTime,F.VehiclePlate,F.CountryId,F.PagoPA1,F.PagoPA2,F.ExternalProtocol,
 F.ExternalYear,F.PreviousId,F.FineTypeId,F.Locality,F.ControllerId,
 F.VehicleTypeId,F.CountryId,
 VT.Title,VT.RuleTypeId,
 FA.ArticleId,FA.ViolationTypeId,FA.ArticleNumber,
 A.Article,A.Paragraph,A.Letter,PR.Id');
 */

const MGMT_DISPUTE = array(
    'aliases' => array(
        'FineId' => 'F.Id',
        'CityId' => 'F.CityId',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'VehiclePlate' => 'F.VehiclePlate',
        'CountryId' => 'F.CountryId',
        'DisputeId' => 'D.Id',
        'GradeTypeId' => 'D.GradeTypeId',
        'OwnerPresentation' => 'D.OwnerPresentation',
        'ProtocolNumber' => 'D.ProtocolNumber',
        'DateProtocol' => 'D.DateProtocol',
        'DateReceive' => 'D.DateReceive',
        'DateSend' => 'D.DateSend',
        'DateFile' => 'D.DateFile',
        'OfficeId' => 'D.OfficeId',
        'OfficeTitle' => 'O.TitleIta',
        'OfficeCity' => 'D.OfficeCity',
        'OfficeAdditionalData' => 'D.OfficeAdditionalData',
        'DisputeDateMeasure' => 'D.DateMeasure',
        'MeasureNumber' => 'D.MeasureNumber',
        'FineSuspension' => 'D.FineSuspension',
        'DateProtocolEntity' => 'D.DateProtocolEntity',
        'EntityProtocolNumber' => 'D.EntityProtocolNumber',
        'FineSuspension' => 'D.FineSuspension',
        'SuspensiveDate' => 'D.SuspensiveDate',
        'SuspensiveNumber' => 'D.SuspensiveNumber',
        'Number' => 'D.Number',
        'Division' => 'D.Division',
        'DateMerit' => 'D.DateMerit',
        'RegDate' => 'D.RegDate',
        'DisputeStatusId' => 'FD.DisputeStatusId',
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
        'Address' => 'T.Address',
        'TaxCode' => 'T.TaxCode',
        'VatCode' => 'T.VatCode',
        'Amount' => 'DA.Amount'
    ),
    "where" => "(D.Id, D.GradeTypeId) IN
    ( SELECT Id, MAX(GradeTypeId)
      FROM Dispute DTEMP
      GROUP BY Id
    ) ",
    "from" => "Dispute D
    join FineDispute FD on D.Id = FD.DisputeId
    join Fine F on F.Id = FD.FineId
	join FineTrespasser FT on F.Id = FT.FineId AND (FT.TrespasserTypeId = 1 OR FT.TrespasserTypeId = 2 OR FT.TrespasserTypeId = 11)
	join Trespasser T on T.Id = FT.TrespasserId
	join Office O on D.OfficeId = O.Id
	left join DisputeDate DD ON DD.DisputeId=D.Id
    left join DisputeAmount DA ON DA.DisputeId=D.Id
	"
);  //TODO 28/02/24 Modificare la left join con DisputeAmount aggiungendo anche "AND DA.GradeTypeId = D.GradeTypeId" perchè, in caso di importi fissati su più gradi di giudizio, ad ora prende quello col grado più basso. E' stato deciso di non modificarlo ora perchè la cosa andrà oggettivata con un nuovo baco appena possibile.

const MGMT_FINE_DISPUTE_AMOUNT = array(
    'aliases' => array(
        'FineId' => 'F.Id',
        'CityId' => 'F.CityId',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'VehiclePlate' => 'F.VehiclePlate',
        'CountryId' => 'F.CountryId',
        'DisputeAmountId' => 'DA.Id',
        'DisputeId' => 'DA.DisputeId',
        'GradeTypeId' => 'DA.GradeTypeId',
        'DisputeDateId' => 'DA.DisputeDateId',
        'JudgementAmount' => 'DA.Amount',
        'JudgementAmountNotes' => 'DA.Note',
        'JudgementTotalAmount' => 'DD.Amount',
        'JudgmentNumber' => 'DD.Number',
        'JudgmentMeasureDate' => 'DD.DateMeasure',
        'JudgmentActionDate' => 'DD.DateAction',
        'JudgmentNotificationDate' => 'DD.DateNotification',
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
    ),
    "from" => "
        FineDispute FD
        join DisputeDate DD on DD.DisputeId = FD.DisputeId
        join Fine F on F.Id = FD.FineId
        join FineTrespasser FT on F.Id = FT.FineId AND (FT.TrespasserTypeId = 1 OR FT.TrespasserTypeId = 2 OR FT.TrespasserTypeId = 11)
        join Trespasser T on T.Id = FT.TrespasserId
        left join FineDisputeAmount FDA ON FDA.FineId = FD.FineId AND FDA.DisputeDateId=DD.Id
        left join DisputeAmount DA ON DA.Id=FDA.DisputeAmountId
        "
);

const SEARCH_DISPUTE_FINE_TRESPASSER = array(
    'aliases' => array(
        'FineId' => 'F.Id',
        'CityId' => 'F.CityId',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'VehiclePlate' => 'F.VehiclePlate',
        'CountryId' => 'F.CountryId',
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
        'Address' => 'T.Address',
        'TaxCode' => 'T.TaxCode',
        'VatCode' => 'T.VatCode',
    ),
    "where"=> "F.ProtocolId>0 AND (F.StatusTypeId=12 OR F.StatusTypeId>=20) AND F.Id NOT IN(SELECT FineId FROM FineDispute)",
    "from" => "Fine F
        join FineTrespasser FT on F.Id = FT.FineId AND (FT.TrespasserTypeId = 1 OR FT.TrespasserTypeId = 2 OR FT.TrespasserTypeId = 11)
        join Trespasser T on T.Id = FT.TrespasserId
        "
);

const MGMT_DISPUTE_FINE_TRESPASSER = array(
    'aliases' => array(
        'FineId' => 'F.Id',
        'CityId' => 'F.CityId',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'VehiclePlate' => 'F.VehiclePlate',
        'CountryId' => 'F.CountryId',
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
        'Address' => 'T.Address',
        'TaxCode' => 'T.TaxCode',
        'VatCode' => 'T.VatCode',
    ),
    "from" => "Fine F
        join FineDispute FD ON FD.FineId=F.Id
        join FineTrespasser FT on F.Id = FT.FineId AND (FT.TrespasserTypeId = 1 OR FT.TrespasserTypeId = 2 OR FT.TrespasserTypeId = 11)
        join Trespasser T on T.Id = FT.TrespasserId
        "
);

const FINE_FEE = array(
    'aliases' => array(
        "FineId"=>"FA.FineId",
        "ReducedFee"=>"(IFNULL(SUM(IF(ArT2.ReducedPayment>0,FAA.Fee*0.7,FAA.Fee)),0)+IF(ArT.ReducedPayment>0,FA.Fee*0.7,FA.Fee)+FH.NotificationFee+FH.ResearchFee)",
        "TotalFee"=>"(IFNULL(SUM(FAA.Fee),0)+FA.Fee+FH.NotificationFee+FH.ResearchFee)",
        "TotalMaxFee"=>"(IFNULL(SUM(FAA.MaxFee/2),0)+FA.MaxFee/2+FH.NotificationFee+FH.ResearchFee)"
    ),
    "from" => "Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId AND ArT.Year = F.ProtocolYear
        LEFT JOIN FineHistory FH ON FA.FineId = FH.FineId AND FH.NotificationTypeId=6
        LEFT JOIN FineAdditionalArticle FAA ON FAA.FineId=F.Id
        LEFT JOIN ArticleTariff ArT2 ON FAA.ArticleId = ArT2.ArticleId AND ArT2.Year = F.ProtocolYear"
);

const FINE_NOT_PAYED = array(
    'aliases' => array(
        "InjunctionProcedure" => "FN.InjunctionProcedure",
        "FineId"=>"F.Id",
        "Code"=>"F.Code",
        "CountryId"=>"F.CountryId",
        "CityId"=>"F.CityId",
        "ProtocolId"=>"F.ProtocolId",
        "ProtocolYear"=>"F.ProtocolYear",
        "FineDate"=>"F.FineDate",
        "FineTime"=>"F.FineTime",
        "VehiclePlate"=>"F.VehiclePlate",
        "VehicleMass"=>"F.VehicleMass",
        "Locality"=>"F.Locality",
        "KindSendDate"=>"F.KindSendDate",
        
        "NotificationDate"=>"FN.NotificationDate",
        "ReminderAdditionalFeeProcedure"=>"FN.ReminderAdditionalFeeProcedure",
        
        "Fee"=>"FA.Fee",
        "MaxFee"=>"FA.MaxFee",
        "PrefectureFee"=>"FA.PrefectureFee",
        "ViolationTypeId"=>"FA.ViolationTypeId",
        
        "ReducedPayment"=>"ArT.ReducedPayment",
        "PrefectureFixed"=>"ArT.PrefectureFixed",
        "AdditionalMass"=>"ArT.AdditionalMass",
        "AdditionalNight"=>"ArT.AdditionalNight",
        
        "AdditionalFee0"=>"(FH.CustomerFee + FH.NotificationFee + FH.ResearchFee + FH.NotifierFee + FH.OtherFee)",
        "AdditionalFee1"=>"(FH.CustomerFee + FH.NotificationFee + FH.ResearchFee + FH.CanFee + FH.CadFee + FH.NotifierFee + FH.OtherFee)",
        "ResearchFee"=>"FH.ResearchFee",
        "NotificationFee"=>"FH.NotificationFee",
        "ControllerId"=>"FH.ControllerId",
        "SendDate"=>"FH.SendDate",
        "DeliveryDate"=>"FH.DeliveryDate",
        "ResultId"=>"FH.ResultId",
        "TrespasserId"=>"FH.TrespasserId",
        "TrespasserTypeId"=>"FH.TrespasserTypeId",
        
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
        'Genre' => 'T.Genre',
        'ForcedTaxCode' => 'ForcedTaxCode',
        'LegalFormSign' => 'L.Sign',
        'TaxCode' => 'T.TaxCode',
        'VatCode' => 'T.VatCode',
        'BornDate' => 'T.BornDate',
        'BornPlace' => 'T.BornPlace',
        'BornCountryId' => 'T.BornCountryId',
        'Address' => 'T.Address',
        'City' => 'T.City',
        'ZIP' => 'T.ZIP',
        'Province' => 'T.Province',
        'ZoneId' => 'T.ZoneId',
        'TrespasserCityId' => 'T.CityId',
        'TrespasserCountryId' => 'T.CountryId',
        'TrespasserCode' => 'T.Code',
        
        'DisputeId' => 'FD.DisputeId',
        'DisputeStatusId' => 'FD.DisputeStatusId',
        'GradeTypeId' => 'D.GradeTypeId',
        
        'DocumentationIdNot' => 'FDO.Id',
        'DocumentationIdCAD' => 'FDOCAD.Id',
    ),
    "from" => "Fine F
        JOIN FineNotification FN on F.Id = FN.FineId
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId AND ArT.Year = F.ProtocolYear
        JOIN FineHistory FH ON FA.FineId = FH.FineId
        JOIN Trespasser T on FH.TrespasserId = T.Id
        LEFT JOIN LegalForm L ON L.Id = T.LegalFormId
        LEFT JOIN FinePayment FP ON FP.FineId = F.Id
        LEFT JOIN FineDispute FD ON FD.FineId = F.Id
        LEFT JOIN Dispute D ON D.Id = FD.DisputeId AND (D.Id, D.GradeTypeId) IN
        (SELECT Id, MAX(GradeTypeId)
          FROM Dispute DTEMP
          WHERE Id=D.Id
          GROUP BY Id
        )
        LEFT JOIN FineDocumentation FDO ON FDO.Id =
        (SELECT MAX(FDOTMP1.Id)
          FROM FineDocumentation FDOTMP1
          WHERE FDOTMP1.FineId= F.Id AND FDOTMP1.DocumentationTypeId IN(10,11,82)
        )
        LEFT JOIN FineDocumentation FDOCAD ON FDOCAD.Id =
        (SELECT MAX(FDOTMP2.Id)
          FROM FineDocumentation FDOTMP2
          WHERE FDOTMP2.FineId= F.Id AND FDOTMP2.DocumentationTypeId IN(12)
        )",
    //la condizione su KindSendDate serve ad escludere gli inviti ag, però includendo, se mai venisse introdotta la valorizzazione di tale data per essi, gli avvisi bonari che sono diventati verbali
    "where"=> "FN.InjunctionProcedure > 0 
        AND FH.NotificationTypeId = 6 
        AND FN.NotificationDate is not null 
        AND F.StatusTypeId >= 25 AND F.StatusTypeId <= 28
        AND (F.KindSendDate IS NULL OR (F.KindSendDate IS NOT NULL AND F.Id IN(SELECT FineId FROM FineHistory WHERE NotificationTypeId = 30)))
        AND (FP.Id = (SELECT MAX(Id) FROM FinePayment WHERE FineId=F.Id) OR FP.Id IS NULL)",
);

const FINE_NOT_PAYED_WITHOUT_NOTIFICATION_CONSTRAINTS = array(
    'aliases' => array(
        "InjunctionProcedure" => "
            FN.InjunctionProcedure IS NOT NULL
            AND FN.InjunctionProcedure <> 0 OR FN.InjunctionProcedure IS NULL
            AND TINJ.InjunctionProcedure IS NULL",
        "FineId"=>"F.Id",
        "Code"=>"F.Code",
        "CountryId"=>"F.CountryId",
        "CityId"=>"F.CityId",
        "ProtocolId"=>"F.ProtocolId",
        "ProtocolYear"=>"F.ProtocolYear",
        "FineDate"=>"F.FineDate",
        "FineTime"=>"F.FineTime",
        "VehiclePlate"=>"F.VehiclePlate",
        "VehicleMass"=>"F.VehicleMass",
        "Locality"=>"F.Locality",
        "KindSendDate"=>"F.KindSendDate",
        
        "NotificationDate"=>"FN.NotificationDate",
        "ReminderAdditionalFeeProcedure"=>"FN.ReminderAdditionalFeeProcedure",
        
        "Fee"=>"FA.Fee",
        "MaxFee"=>"FA.MaxFee",
        "PrefectureFee"=>"FA.PrefectureFee",
        "ViolationTypeId"=>"FA.ViolationTypeId",
        
        "ReducedPayment"=>"ArT.ReducedPayment",
        "PrefectureFixed"=>"ArT.PrefectureFixed",
        "AdditionalMass"=>"ArT.AdditionalMass",
        "AdditionalNight"=>"ArT.AdditionalNight",
        
        "AdditionalFee0"=>"(FH.CustomerFee + FH.NotificationFee + FH.ResearchFee + FH.NotifierFee + FH.OtherFee)",
        "AdditionalFee1"=>"(FH.CustomerFee + FH.NotificationFee + FH.ResearchFee + FH.CanFee + FH.CadFee + FH.NotifierFee + FH.OtherFee)",
        "ResearchFee"=>"FH.ResearchFee",
        "NotificationFee"=>"FH.NotificationFee",
        "ControllerId"=>"FH.ControllerId",
        "SendDate"=>"FH.SendDate",
        "DeliveryDate"=>"FH.DeliveryDate",
        "ResultId"=>"FH.ResultId",
        "TrespasserId"=>"FH.TrespasserId",
        "TrespasserTypeId"=>"FH.TrespasserTypeId",
        
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
        'Genre' => 'T.Genre',
        'ForcedTaxCode' => 'ForcedTaxCode',
        'LegalFormSign' => 'L.Sign',
        'TaxCode' => 'T.TaxCode',
        'VatCode' => 'T.VatCode',
        'BornDate' => 'T.BornDate',
        'BornPlace' => 'T.BornPlace',
        'BornCountryId' => 'T.BornCountryId',
        'Address' => 'T.Address',
        'City' => 'T.City',
        'ZIP' => 'T.ZIP',
        'Province' => 'T.Province',
        'ZoneId' => 'T.ZoneId',
        'TrespasserCityId' => 'T.CityId',
        'TrespasserCountryId' => 'T.CountryId',
        'TrespasserCode' => 'T.Code',
        
        'DisputeId' => 'FD.DisputeId',
        'DisputeStatusId' => 'FD.DisputeStatusId',
        'GradeTypeId' => 'D.GradeTypeId',
        
        'DocumentationIdNot' => 'FDO.Id',
        'DocumentationIdCAD' => 'FDOCAD.Id',
    ),
    "from" => "Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN ArticleTariff ArT ON FA.ArticleId = ArT.ArticleId AND ArT.Year = F.ProtocolYear
        JOIN FineHistory FH ON FA.FineId = FH.FineId
        JOIN Trespasser T on FH.TrespasserId = T.Id
        LEFT JOIN LegalForm L ON L.Id = T.LegalFormId
        LEFT JOIN FinePayment FP ON FP.FineId = F.Id
        LEFT JOIN FineNotification FN on F.Id = FN.FineId
        LEFT JOIN TMP_InjunctionProcedure TINJ on TINJ.FineId = F.Id
        LEFT JOIN FineDispute FD ON FD.FineId = F.Id
        LEFT JOIN Dispute D ON D.Id = FD.DisputeId AND (D.Id, D.GradeTypeId) IN
        (SELECT Id, MAX(GradeTypeId)
          FROM Dispute DTEMP
          WHERE Id=D.Id
          GROUP BY Id
        )
        LEFT JOIN FineDocumentation FDO ON FDO.Id =
        (SELECT MAX(FDOTMP1.Id)
          FROM FineDocumentation FDOTMP1
          WHERE FDOTMP1.FineId= F.Id AND FDOTMP1.DocumentationTypeId IN(10,11,82)
        )
        LEFT JOIN FineDocumentation FDOCAD ON FDOCAD.Id =
        (SELECT MAX(FDOTMP2.Id)
          FROM FineDocumentation FDOTMP2
          WHERE FDOTMP2.FineId= F.Id AND FDOTMP2.DocumentationTypeId IN(12)
        )",
    
    //la condizione su KindSendDate serve ad escludere gli inviti ag, però includendo, se mai venisse introdotta la valorizzazione di tale data per essi, gli avvisi bonari che sono diventati verbali
    "where" => "FH.NotificationTypeId = 6 
        AND F.StatusTypeId >= 20 AND F.StatusTypeId <= 28 
        AND (F.KindSendDate IS NULL OR (F.KindSendDate IS NOT NULL AND F.Id IN(SELECT FineId FROM FineHistory WHERE NotificationTypeId = 30)))
        AND (FP.Id = (SELECT MAX(Id) FROM FinePayment WHERE FineId=F.Id) OR FP.Id IS NULL)",
    "having" => "InjunctionProcedure > 0"
);

const V_FINETARIFF = array(
    'aliases' => array(
        "Id" => "F.Id",
        "LicensePoint" => "(A.LicensePoint+COALESCE(SUM(AA.LicensePoint),0))",
        "YoungLicensePoint" => "(A.YoungLicensePoint+COALESCE(SUM(AA.YoungLicensePoint),0))",
        "126Bis" => "(select A.126Bis+COALESCE(SUM(AA.126Bis),0) > 0)",
        "PresentationDocument" => "(select A.PresentationDocument+COALESCE(SUM(AA.PresentationDocument),0) > 0)",
        "Habitual" => "(select A.Habitual+COALESCE(SUM(AA.Habitual),0) > 0)",
        "SuspensionLicense" => "(select A.SuspensionLicense+COALESCE(SUM(AA.SuspensionLicense),0) > 0)",
        "LossLicense" => "(select A.LossLicense+COALESCE(SUM(AA.LossLicense),0) > 0)"
    ),
    "from" => "Fine F
        INNER JOIN FineArticle FA ON FA.FineId = F.Id
        INNER JOIN ArticleTariff A on FA.ArticleId = A.ArticleId and A.Year = F.ProtocolYear
        LEFT OUTER JOIN FineAdditionalArticle FAA  ON FAA.FineId = F.Id
        LEFT OUTER JOIN ArticleTariff AA on FAA.ArticleId = AA.ArticleId and AA.Year = F.ProtocolYear",
    'where' => '1=1',
    'groupBy' => "F.id, A.LicensePoint, A.YoungLicensePoint, A.126Bis, A.PresentationDocument, A.Habitual, A.SuspensionLicense, A.LossLicense"
);

const MGMT_FINEDOCUMENTATION = array(
    'aliases' => array(
        'FineId' => 'F.Id',
        'Code' => 'F.Code',
        'CityId' => 'F.CityId',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'StatusTypeId' => 'F.StatusTypeId',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'VehiclePlate' => 'F.VehiclePlate',
        'VehicleTypeId' => 'F.VehicleTypeId',
        'VehicleCountryId' => 'F.CountryId',
        'ExternalProtocol' => 'F.ExternalProtocol',
        'ExternalYear' => 'F.ExternalYear',
        'PreviousId' => 'F.PreviousId',
        'FineTypeId' => 'F.FineTypeId',
        'Locality' => 'F.Locality',
        'ViolationTitle' => 'VT.Title',
        'RuleTypeId' => 'VT.RuleTypeId',
        'ArticleId' => 'FA.ArticleId',
        'ViolationTypeId' => 'FA.ViolationTypeId',
        'ArticleNumber' => 'FA.ArticleNumber',
        'TrespasserTypeId' => 'FT.TrespasserTypeId',
        'Note' => 'FT.Note',
        'OwnerAdditionalFee' => 'FT.OwnerAdditionalFee',
        'CustomerAdditionalFee' => 'FT.CustomerAdditionalFee',
        'ReceiveDate' => 'FT.ReceiveDate',
        'Genre' => 'T.Genre',
        'Address' => 'T.Address',
        'ZIP' => 'T.ZIP',
        'TrespasserId' => 'T.Id',
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
        'TaxCode' => 'T.TaxCode',
        'VatCode' => 'T.VatCode',
        'LicenseCategory' => 'T.LicenseCategory',
        'LicenseNumber' => 'T.LicenseNumber',
        'LicenseDate' => 'T.LicenseDate',
        'LicenseOffice' => 'T.LicenseOffice',
        'PEC' => 'T.PEC',
        'City' => 'T.City',
        'CountryId' => 'T.CountryId',
        'IrideCode' => 'T.IrideCode',
        'CountryTitle' => 'C.Title',
    ),
    'from' =>
    'Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
        LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
        LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id
        LEFT JOIN Country C ON T.CountryId = C.Id',
    'where' =>
    '(FT.TrespasserTypeId = 1
        or FT.TrespasserTypeId = 11
        or FT.TrespasserTypeId = 2
        or ISNULL(FT.TrespasserTypeId))',
);

const EXP_MAGGIOLI_INVII = array(
    'aliases' => array(
        'Id' => 'F.Id',
        '"Numero_Multa"' => 'UPPER(F.Code)',
        '"Data_multa"' => 'F.FineDate',
        '"Targa"' => 'F.VehiclePlate',
        '"Data accertamento"' => 'F.ControllerDate',
        '"Nominativo"' => 'CONCAT_WS(" ",T.CompanyName,T.Surname,T.Name)',
        '"Indirizzo"' => 'T.Address',
        '"Indirizzo1"' => '""',
        '"Indirizzo2"' => '""',
        '"Citta"' => 'T.City',
        '"CAP"' => 'T.ZIP',
        '"Provincia"' => 'T.Province',
        '"Nazione"' => 'CT.ONUCode',
        '"Data Nascita"' => 'T.BornDate',
        '"Luogo Nascita"' => 'T.BornPlace',
        '"Stato Nascita"' => 'CB.ONUCode',
        '"Conducente/Proprietario"' => '(CASE WHEN FT.TrespasserTypeId in(1,2) THEN "P" ELSE "C" END)',
        '"Sollecito/Notifica"' => '"NOT"',
        '"Data Lettera"' => 'FH.NotificationDate',
        '"Immagine"' => 'FD.Documentation',
        '"patente"' => 'T.LicenseNumber',
    ),
    'from' => 'Fine F
        inner join FineTrespasser FT on F.Id = FT.FineId
        inner join Trespasser T on FT.TrespasserId = T.Id
        inner join FineHistory FH on F.Id = FH.FineId and FH.NotificationTypeId in (2,15)
        inner join FineHistory FH6 on F.Id = FH6.FineId and FH6.NotificationTypeId = 6
        inner join FineDocumentation FD on F.Id = FD.FineId and FD.DocumentationTypeId = 2
        inner join Country CT ON T.CountryId = CT.Id
        inner join Country CB ON T.BornCountryId = CB.Id',
    'where' => 'F.StatusTypeId>=20 and F.StatusTypeId<=30'
);

const EXP_MAGGIOLI_CARTOLINE = array(
    'aliases' => array(
        'Id' => 'F.Id',
        '"Numero_Multa"' => 'UPPER(F.Code)',
        '"Data_multa"' => 'F.FineDate',
        '"Targa"' => 'F.VehiclePlate',
        '"Data Notifica"' => 'FN.NotificationDate',
        '"Data Spedizione"' => 'FN.SendDate',
        '"Numero Raccomandata"' => 'FN.ReceiptNumber',
        '"Immagine"' => 'FD.Documentation',
        '"esito"' => 'FN.ResultId'
    ),
    'from' => 'Fine F
        inner join FineHistory FH on F.Id = FH.FineId and FH.NotificationTypeId = 6
        inner join FineDocumentation FD on F.Id = FD.FineId and FD.DocumentationTypeId in (10,11,82)
        inner join FineNotification FN on F.Id = FN.FineId',
    'where' => 'F.StatusTypeId>=20 and F.StatusTypeId<=30'
);

const EXP_MAGGIOLI_PAGAMENTI = array(
    'aliases' => array(
        'Id' => 'F.Id',
        '"Numero_Multa"' => 'UPPER(F.Code)',
        '"Data_multa"' => 'F.FineDate',
        '"Targa"' => 'F.VehiclePlate',
        '"Importo Sanzione e spese Comando affidato"' => 'FORMAT((FP.Fee + FP.NotificationFee+FP.ResearchFee+FP.CanFee+FP.CadFee+FP.CustomerFee+FP.OfficeNotificationFee), 2, "it_IT")',
        '"Importo sanzione e spese Comando pagato"' => 'FORMAT(FP.Amount, 2, "it_IT")',
        '"Importo provvigioni"' => '0',
        '"Data pagamento"' => 'FP.PaymentDate',
        '"Spese notifica pagate"' => '0',
        '"Spese postali pagate"' => 'FORMAT(FP.NotificationFee, 2, "it_IT")',
        '"commissioni ON-LINE"' => '0',
        '"ECCEDENZA"' => 'FORMAT((FP.Amount + ( -1 * (FP.Fee+FP.NotificationFee+FP.ResearchFee+FP.CanFee+FP.CadFee+FP.CustomerFee+FP.OfficeNotificationFee))), 2, "it_IT")',
        '"Metodo di pagamento"' => 'PT.Title',
        '"Tipo di pagamento"' => '(CASE WHEN ART.ReducedPayment = 1 THEN "70%" ELSE "100%" END)',
        '"Riferimento NIVI"' => '""',
        '"Num. Univoco NIVI"' => '""'
    ),
    'from' => 'Fine F
        inner join FineHistory FH on F.Id = FH.FineId and FH.NotificationTypeId = 6
        inner join FinePayment FP on F.Id = FP.FineId
        inner join FineArticle FA on F.Id = FA.FineId
        inner join ArticleTariff ART on FA.ArticleId = ART.ArticleId and ART.Year = F.ProtocolYear
        inner join sarida.PaymentType PT on FP.PaymentTypeId = PT.Id',
    'where' => 'F.StatusTypeId>=20 and F.StatusTypeId<=30'
);

const EXP_MAGGIOLI_ANAGRAFICHE_XML = array(
    'aliases' => array(
        'QUERY_Id' => 'F.Id',
        'QUERY_ProtocolId' => 'F.ProtocolId',
        'QUERY_ProtocolYear' => 'F.ProtocolYear',
        'QUERY_VehiclePlate' => 'F.VehiclePlate',
        'QUERY_FineDate' => 'DATE_FORMAT(F.FineDate, "%d/%m/%Y")',
        'QUERY_RegDate' => 'DATE_FORMAT(COALESCE(FT.ReceiveDate, FT.RegDate), "%d/%m/%Y")',
        'QUERY_TrespasserId' => 'T.Id',
        'CodiceCliente' => '"@MaggioliCode"',
        'CodiceUID' => '
            CASE
                WHEN F.Code REGEXP "@Regexp" THEN
                UPPER(CONCAT(
                    "@UidPrefix",
                    SUBSTR(F.Code,1,1),
                    SUBSTR(F.Code,-4,4),
                    SUBSTR(F.Code,-6,1),
                    LPAD(SUBSTR(F.Code,2,LENGTH(F.Code)-7), 10, 0)
                ))
                ELSE F.Code
            END',
        'Targa' => 'UPPER(F.VehiclePlate)',
        'Nominativo' => 'CONCAT_WS(" ",T.CompanyName,T.Surname,T.Name)',
        'Indirizzo' => 'UPPER(CONCAT_WS(" ",T.Address,T.StreetNumber,T.Ladder,T.Indoor,T.Plan))',
        'Localita' => 'T.City',
        'Nazione' => 'CT.ISO3',
        'DataNascita' => 'DATE_FORMAT(T.BornDate, "%d/%m/%Y")',
        'LocalitaNascita' => 'T.BornPlace',
        'Cap' => 'T.ZIP',
        'TipologiaAnagrafica' => '
            CASE
                WHEN FT.TrespasserTypeId IN(1,2,10,15) THEN "P"
                WHEN FT.TrespasserTypeId IN(3,16) THEN "T"
                WHEN FT.TrespasserTypeId=11 THEN "L"
                ELSE ""
            END',
        'DataAnagrafica' => 'DATE_FORMAT(COALESCE(FT.ReceiveDate, FT.RegDate), "%d/%m/%Y")',
    ),
    'from' => 'Fine F
        inner join FineTrespasser FT on F.Id = FT.FineId
        inner join Trespasser T on FT.TrespasserId = T.Id
        inner join Country CT ON T.CountryId = CT.Id',
    'where' => 'F.StatusTypeId>=20 and F.StatusTypeId<=37 AND FT.AssociatedOnImport = 0'
);

const EXP_MAGGIOLI_NOTIFICHE_XML = array(
    'aliases' => array(
        'QUERY_Id' => 'F.Id',
        'QUERY_ProtocolId' => 'F.ProtocolId',
        'QUERY_ProtocolYear' => 'F.ProtocolYear',
        'QUERY_VehiclePlate' => 'F.VehiclePlate',
        'QUERY_FineDate' => 'DATE_FORMAT(F.FineDate, "%d/%m/%Y")',
        'QUERY_RegDate' => 'DATE_FORMAT(FN.RegDate, "%d/%m/%Y")',
        'CodiceCliente' => '"@MaggioliCode"',
        'CodiceUID' => '
            CASE
                WHEN F.Code REGEXP "@Regexp" THEN
                UPPER(CONCAT(
                    "@UidPrefix",
                    SUBSTR(F.Code,1,1),
                    SUBSTR(F.Code,-4,4),
                    SUBSTR(F.Code,-6,1),
                    LPAD(SUBSTR(F.Code,2,LENGTH(F.Code)-7), 10, 0)
                ))
                ELSE F.Code
            END',
        'Targa' => 'UPPER(F.VehiclePlate)',
        'RiferimentoRaccomandata' => 'FN.LetterNumber',
        'DataNotifica' => 'DATE_FORMAT(FN.NotificationDate, "%d/%m/%Y")',
        'EsitoNotifica' => '
            CASE
                WHEN FN.ResultId IN(11,20) THEN 1
                WHEN FN.ResultId IN(19,23) THEN 2
                WHEN FN.ResultId IN(10,17,18) THEN 3
                WHEN FN.ResultId IN(13) THEN 4
                WHEN FN.ResultId IN(12) THEN 5
                WHEN FN.ResultId IN(5) THEN 7
                WHEN FN.ResultId IN(4,21) THEN 8
                WHEN FN.ResultId IN(1,2,3,6,7,8,9,22,24) THEN 9
                ELSE ""
            END',
    ),
    'from' => 'Fine F
        inner join FineHistory FH on F.Id = FH.FineId and FH.NotificationTypeId = 6
        inner join FineNotification FN on F.Id = FN.FineId',
    'where' => 'F.StatusTypeId>=20 and F.StatusTypeId<=37'
);

const EXP_MAGGIOLI_PAGAMENTI_XML = array(
    'aliases' => array(
        'QUERY_Id' => 'F.Id',
        'QUERY_ProtocolId' => 'F.ProtocolId',
        'QUERY_ProtocolYear' => 'F.ProtocolYear',
        'QUERY_VehiclePlate' => 'F.VehiclePlate',
        'QUERY_FineDate' => 'DATE_FORMAT(F.FineDate, "%d/%m/%Y")',
        'QUERY_RegDate' => 'DATE_FORMAT(FP.RegDate, "%d/%m/%Y")',
        'CodiceCliente' => '"@MaggioliCode"',
        'CodiceUID' => '
            CASE
                WHEN F.Code REGEXP "@Regexp" THEN
                UPPER(CONCAT(
                    "@UidPrefix",
                    SUBSTR(F.Code,1,1),
                    SUBSTR(F.Code,-4,4),
                    SUBSTR(F.Code,-6,1),
                    LPAD(SUBSTR(F.Code,2,LENGTH(F.Code)-7), 10, 0)
                ))
                ELSE F.Code
            END',
        'Targa' => 'UPPER(F.VehiclePlate)',
        'Tipologia' => '1',
        'ImportoPagatoSanzione' => 'ROUND(FP.Fee, 2)',
        'ImportoPagatoSpNotifica' => 'ROUND(FP.NotificationFee, 2)',
        'ImportoPagatoSpProcedurali' => 'ROUND(FP.ResearchFee, 2)',
        'ImportoPagatoSpNetwork' => 'ROUND(
            FP.CanFee +
            FP.CadFee +
            FP.CustomerFee +
            FP.OfficeNotificationFee, 2)',
        'ImportoPagato' => 'ROUND(FP.Amount, 2)',
        'DataRegistrazione' => 'DATE_FORMAT(FP.RegDate, "%d/%m/%Y")',
        'DataPagamento' => 'DATE_FORMAT(FP.PaymentDate, "%d/%m/%Y")',
        'PagamentoComplessivo' => '"N"',
    ),
    'from' => 'Fine F
        inner join FineHistory FH on F.Id = FH.FineId and FH.NotificationTypeId = 6
        inner join FinePayment FP on F.Id = FP.FineId',
    'where' => 'F.StatusTypeId>=20 and F.StatusTypeId<=37'
);

//nota SpeseNotificaPN mettiamo per ora le sole spese di notifica
//se vorranno qui tutte le spese le metteremo con questo
// 'SpeseNotificaPN' => 'FORMAT(
//             FH.CustomerFee +
//             FH.NotificationFee +
//             FH.ResearchFee +
//             FH.CanFee +
//             FH.CadFee +
//             FH.NotifierFee +
//             FH.OtherFee, 2, "it_IT")',

const EXP_MAGGIOLI_SPESENOTIFICA_XML = array(
    'aliases' => array(
        'QUERY_Id' => 'F.Id',
        'QUERY_ProtocolId' => 'F.ProtocolId',
        'QUERY_ProtocolYear' => 'F.ProtocolYear',
        'QUERY_VehiclePlate' => 'F.VehiclePlate',
        'QUERY_FineDate' => 'DATE_FORMAT(F.FineDate, "%d/%m/%Y")',
        'QUERY_RegDate' => 'DATE_FORMAT(FH.SendDate, "%d/%m/%Y")', //data di consegna del flusso
        'CodiceCliente' => '"@MaggioliCode"',
        'CodiceUID' => '
            CASE
                WHEN F.Code REGEXP "@Regexp" THEN
                UPPER(CONCAT(
                    "@UidPrefix",
                    SUBSTR(F.Code,1,1),
                    SUBSTR(F.Code,-4,4),
                    SUBSTR(F.Code,-6,1),
                    LPAD(SUBSTR(F.Code,2,LENGTH(F.Code)-7), 10, 0)
                ))
                ELSE F.Code
            END',
        'Targa' => 'UPPER(F.VehiclePlate)',
        'RiferimentoRaccomandata' => 'coalesce(FFI.LetterNumber, FN.LetterNumber)',
        'SpeseNotificaPN' => '
			CASE 
				WHEN F.PreviousId > 0 THEN
					ROUND(FH2.NotificationFee + FH2.ResearchFee, 2)
				ELSE
					ROUND(FH2.NotificationFee + FH2.ResearchFee -
					(select FT.OwnerAdditionalFee from FineTrespasser FT where FT.FineId = F.Id and FT.TrespasserTypeId in (1,3,11)) , 2)
			END',
    ),
    'from' => 'Fine F
        inner join FineHistory FH on F.Id = FH.FineId and FH.NotificationTypeId = 6
        inner join FineHistory FH2 on F.Id = FH2.FineId and FH2.NotificationTypeId = 2 and FH2.TrespasserId = FH.TrespasserId
        left join FineFlowInfo FFI on FFI.FineId = FH.FineId and FFI.TrespasserId = FH.TrespasserId
        left join FineNotification FN on FN.FineId = F.Id',
    'where' => 'F.StatusTypeId>=20 and F.StatusTypeId<=37 and ((FN.RegDate > "@ExclusionDate") or FN.RegDate IS NULL)'
);

const EXP_MAGGIOLI_DOCUMENTALE_XML = array(
    'aliases' => array(
        'QUERY_Id' => 'F.Id',
        'QUERY_ProtocolId' => 'F.ProtocolId',
        'QUERY_ProtocolYear' => 'F.ProtocolYear',
        'QUERY_VehiclePlate' => 'F.VehiclePlate',
        'QUERY_FineDate' => 'DATE_FORMAT(F.FineDate, "%d/%m/%Y")',
        'QUERY_RegDate' => 'DATE_FORMAT(FD.VersionDate, "%d/%m/%Y")',
        'CodiceCliente' => '"@MaggioliCode"',
        'CodiceUID' => '
            CASE
                WHEN F.Code REGEXP "@Regexp" THEN
                UPPER(CONCAT(
                    "@UidPrefix",
                    SUBSTR(F.Code,1,1),
                    SUBSTR(F.Code,-4,4),
                    SUBSTR(F.Code,-6,1),
                    LPAD(SUBSTR(F.Code,2,LENGTH(F.Code)-7), 10, 0)
                ))
                ELSE F.Code
            END',
        'Tipologia' => '
            CASE
                WHEN FD.DocumentationTypeId IN(2) THEN "NOT"
                WHEN FD.DocumentationTypeId IN(10,11,12,16,17) THEN "CARN"
                WHEN FD.DocumentationTypeId IN(15) THEN "PAG"
                ELSE "GEN"
            END',
        'Descrizione' => 'DT.Title',
        'Documento' => 'CONCAT("DOCUMENTALE","\\\",FD.Documentation)'
    ),
    'from' => 'Fine F
        inner join FineDocumentation FD on F.Id = FD.FineId
        inner join DocumentationType DT on FD.DocumentationTypeId = DT.Id',
    'where' => 'F.StatusTypeId>=20 AND F.StatusTypeId<=37 AND FD.DocumentationTypeId NOT IN(1,3,13,14,43) AND COALESCE(FD.Documentation, "") != ""',
    'union' => array(
        'FinePayment' => array(
            'aliases' => array(
                'QUERY_Id' => 'F.Id',
                'QUERY_ProtocolId' => 'F.ProtocolId',
                'QUERY_ProtocolYear' => 'F.ProtocolYear',
                'QUERY_VehiclePlate' => 'F.VehiclePlate',
                'QUERY_FineDate' => 'DATE_FORMAT(F.FineDate, "%d/%m/%Y")',
                'QUERY_RegDate' => 'DATE_FORMAT(FP.RegDate, "%d/%m/%Y")',
                'CodiceCliente' => '"@MaggioliCode"',
                'CodiceUID' => '
                    CASE
                        WHEN F.Code REGEXP "@Regexp" THEN
                        UPPER(CONCAT(
                            "@UidPrefix",
                            SUBSTR(F.Code,1,1),
                            SUBSTR(F.Code,-4,4),
                            SUBSTR(F.Code,-6,1),
                            LPAD(SUBSTR(F.Code,2,LENGTH(F.Code)-7), 10, 0)
                        ))
                        ELSE F.Code
                    END',
                'Tipologia' => '"PAG"',
                'Descrizione' => 'PT.Title',
                'Documento' => 'CONCAT("DOCUMENTALE","\\\",FP.Documentation)'
            ),
            'from' => 'Fine F
                inner join FinePayment FP on F.Id = FP.FineId
                inner join sarida.PaymentType PT on FP.PaymentTypeId = PT.Id',
            'where' => 'F.StatusTypeId>=20 AND F.StatusTypeId<=37 AND COALESCE(FP.Documentation, "") != ""',
        )
    )
);

const MGMT_PRINTERPARAMETER = array(
    'aliases' => array(
        'PrinterId' => 'P.Id',
        'Name' => 'P.Name',
        
        'NationalFineFoldReturn' => 'PP.NationalFineFoldReturn',
        'NationalMod23LCustomerSubject' => 'PP.NationalMod23LCustomerSubject',
        'NationalMod23LCustomerAddress' => 'PP.NationalMod23LCustomerAddress',
        'NationalMod23LCustomerCity' => 'PP.NationalMod23LCustomerCity',
        'NationalSmaName' => 'PP.NationalSmaName',
        'NationalSmaAuthorization' => 'PP.NationalSmaAuthorization',
        'NationalSmaPayment' => 'PP.NationalSmaPayment',
        'NationalPostalAuthorization' => 'PP.NationalPostalAuthorization',
        'NationalPostalAuthorizationPagoPA' => 'PP.NationalPostalAuthorizationPagoPA',
        'ForeignFineFoldReturn' => 'PP.ForeignFineFoldReturn',
        'ForeignMod23LCustomerSubject' => 'PP.ForeignMod23LCustomerSubject',
        'ForeignMod23LCustomerAddress' => 'PP.ForeignMod23LCustomerAddress',
        'ForeignMod23LCustomerCity' => 'PP.ForeignMod23LCustomerCity',
        'ForeignSmaName' => 'PP.ForeignSmaName',
        'ForeignSmaAuthorization' => 'PP.ForeignSmaAuthorization',
        'ForeignSmaPayment' => 'PP.ForeignSmaPayment',
        'ForeignPostalAuthorization' => 'PP.ForeignPostalAuthorization',
        'ForeignPostalAuthorizationPagoPA' => 'PP.ForeignPostalAuthorizationPagoPA',

        'NationalReminderFoldReturn' => 'PP.NationalReminderFoldReturn',
        'NationalReminderSmaName' => 'PP.NationalReminderSmaName',
        'NationalReminderSmaAuthorization' => 'PP.NationalReminderSmaAuthorization',
        'NationalReminderSmaPayment' => 'PP.NationalReminderSmaPayment',
        'NationalReminderPostalAuthorization' => 'PP.NationalReminderPostalAuthorization',
        'NationalReminderPostalAuthorizationPagoPA' => 'PP.NationalReminderPostalAuthorizationPagoPA',
        'ForeignReminderFoldReturn' => 'PP.ForeignReminderFoldReturn',
        'ForeignReminderSmaName' => 'PP.ForeignReminderSmaName',
        'ForeignReminderSmaAuthorization' => 'PP.ForeignReminderSmaAuthorization',
        'ForeignReminderSmaPayment' => 'PP.ForeignReminderSmaPayment',
        'ForeignReminderPostalAuthorization' => 'PP.ForeignReminderPostalAuthorization',
        'ForeignReminderPostalAuthorizationPagoPA' => 'PP.ForeignReminderPostalAuthorizationPagoPA',
    ),
    'from' => 'Printer P
        LEFT JOIN PrinterParameter PP ON P.Id = PP.PrinterId',
    'where' => 'P.Id NOT IN(1,3)'//1 = Ufficio del comune, 3 = Ufficio del comune via PEC
);

const TBL_CUSTOMER_PRINTERPARAMETER = array(
    'aliases' => array(
        'PrinterId' => 'P.Id',
        'Name' => 'P.Name',
        
        'NationalFineFoldReturn' => 'PP.NationalFineFoldReturn',
        'NationalMod23LCustomerSubject' => 'PP.NationalMod23LCustomerSubject',
        'NationalMod23LCustomerAddress' => 'PP.NationalMod23LCustomerAddress',
        'NationalMod23LCustomerCity' => 'PP.NationalMod23LCustomerCity',
        'NationalSmaName' => 'PP.NationalSmaName',
        'NationalSmaAuthorization' => 'PP.NationalSmaAuthorization',
        'NationalSmaPayment' => 'PP.NationalSmaPayment',
        'NationalPostalAuthorization' => 'PP.NationalPostalAuthorization',
        'NationalPostalAuthorizationPagoPA' => 'PP.NationalPostalAuthorizationPagoPA',
        'ForeignFineFoldReturn' => 'PP.ForeignFineFoldReturn',
        'ForeignMod23LCustomerSubject' => 'PP.ForeignMod23LCustomerSubject',
        'ForeignMod23LCustomerAddress' => 'PP.ForeignMod23LCustomerAddress',
        'ForeignMod23LCustomerCity' => 'PP.ForeignMod23LCustomerCity',
        'ForeignSmaName' => 'PP.ForeignSmaName',
        'ForeignSmaAuthorization' => 'PP.ForeignSmaAuthorization',
        'ForeignSmaPayment' => 'PP.ForeignSmaPayment',
        'ForeignPostalAuthorization' => 'PP.ForeignPostalAuthorization',
        'ForeignPostalAuthorizationPagoPA' => 'PP.ForeignPostalAuthorizationPagoPA',
        
        'NationalReminderFoldReturn' => 'PP.NationalReminderFoldReturn',
        'NationalReminderSmaName' => 'PP.NationalReminderSmaName',
        'NationalReminderSmaAuthorization' => 'PP.NationalReminderSmaAuthorization',
        'NationalReminderSmaPayment' => 'PP.NationalReminderSmaPayment',
        'NationalReminderPostalAuthorization' => 'PP.NationalReminderPostalAuthorization',
        'NationalReminderPostalAuthorizationPagoPA' => 'PP.NationalReminderPostalAuthorizationPagoPA',
        'ForeignReminderFoldReturn' => 'PP.ForeignReminderFoldReturn',
        'ForeignReminderSmaName' => 'PP.ForeignReminderSmaName',
        'ForeignReminderSmaAuthorization' => 'PP.ForeignReminderSmaAuthorization',
        'ForeignReminderSmaPayment' => 'PP.ForeignReminderSmaPayment',
        'ForeignReminderPostalAuthorization' => 'PP.ForeignReminderPostalAuthorization',
        'ForeignReminderPostalAuthorizationPagoPA' => 'PP.ForeignReminderPostalAuthorizationPagoPA',
    ),
    'from' => 'Printer P
        LEFT JOIN PrinterParameter PP ON P.Id = PP.PrinterId AND PP.CityId="@CityId"',
    'where' => 'P.Id NOT IN(3)'//3 = Ufficio del comune via PEC
);

const MGMT_FLOW_DETAIL = array(
    'aliases' => array(
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'NotificationDate' => 'FN.NotificationDate',
        'LetterNumber' => 'FN.LetterNumber',
        'ReceiptNumber' => 'FN.ReceiptNumber',
        'ResultId' => 'FN.ResultId',
        'ValidatedAddress' => 'FN.ValidatedAddress',
        'ResultTitle' => 'R.Title',
    ),
    'from' => 'FineHistory FH
        JOIN Fine F ON FH.FineId=F.Id
        LEFT JOIN FineNotification FN ON F.Id = FN.FineId
        LEFT JOIN Result R ON FN.ResultId = R.Id',
    'where' => 'FH.NotificationTypeId=6'
);

//Query per numeri di raccomandata Maggioli
const MGMT_FLOW_ADDITIONAL_INFO = array(
    'aliases' => array(
        'FineId' => 'F.Id',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'CityId' => 'F.CityId',
        'CountryId' => 'F.CountryId',
        'TrespasserId' => 'FH.TrespasserId',
        'LetterNumber' => 'COALESCE(FFI.LetterNumber, FN.LetterNumber)',
    ),
    'from' => 'FineHistory FH
        JOIN Fine F ON FH.FineId=F.Id
        JOIN FineTrespasser FT ON FT.TrespasserId = FH.TrespasserId AND FT.FineId = F.Id
        LEFT JOIN FineFlowInfo FFI ON FH.FineId = FFI.FineId AND FH.TrespasserId = FFI.TrespasserId
        LEFT JOIN FineNotification FN ON FN.FineId = F.Id',
    'where' => 'FH.NotificationTypeId=6'
);

//query per statistiche riscossioni

//NOTA: consideriamo le sanzioni al minimo edittale

const STAT_FINE_COLLECTION_ACCERTATE = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")',
        'Count' => 'count(*)',
        'Min' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Max' => 'sum(FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Sum' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))'
    ),
    'from' => 'Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN Article A ON A.Id = FA.ArticleId
        JOIN ViolationType V ON V.Id = A.ViolationTypeId
        JOIN FineTrespasser FT ON F.Id = FT.FineId
        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        LEFT JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
        LEFT JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Result R ON FN.ResultId=R.Id',
    'where' => 'F.CityId = "@Ente"
        and coalesce(F.ControllerDate, F.FineDate) >= "@FromDate"
        and coalesce(F.ControllerDate, F.FineDate) <= "@ToDate"
		and F.ProtocolYear = @ProtocolYear
        and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
        and V.RuleTypeId = 1',
    'groupBy' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")'
);

const STAT_FINE_COLLECTION_INVIATE = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")',
        'Count' => 'count(*)',
        'Min' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Max' => 'sum(FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Sum' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))'
    ),
    'from' => 'Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN Article A ON A.Id = FA.ArticleId
        JOIN ViolationType V ON V.Id = A.ViolationTypeId
        JOIN FineTrespasser FT ON F.Id = FT.FineId
        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
        LEFT JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Result R ON FN.ResultId=R.Id',
    'where' => 'F.CityId = "@Ente"
        and coalesce(F.ControllerDate, F.FineDate) >= "@FromDate"
        and coalesce(F.ControllerDate, F.FineDate) <= "@ToDate"
		and F.ProtocolYear = @ProtocolYear
        and F.ProtocolId > 0
        and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
        and V.RuleTypeId = 1',
    'groupBy' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")'
);

//si sottrae la sanzione solo per quelli effettivamente pagati
const STAT_FINE_COLLECTION_RIDUZIONE30 = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")',
        'Count' => 'count(*)',
        'Min' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Max' => 'sum(FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Sum' => 'sum(case
            when
                (select COUNT(FP.PaymentDate) From FinePayment FP where FP.FineId = F.Id)>0
                && (DATEDIFF(coalesce((select min(FP.PaymentDate) as DataP From FinePayment FP where FP.FineId = F.Id), now()), coalesce(FN.NotificationDate, coalesce(FN.NotificationDate, coalesce((select min(FP.PaymentDate) as DataP From FinePayment FP where FP.FineId = F.Id), now()))))
                <= 5)
            then (FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0)) * 0.30
            else 0
        end)'
    ),
    'from' => 'Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
		JOIN ArticleTariff TAR ON TAR.ArticleId = FA.ArticleId and TAR.Year = F.ProtocolYear
        JOIN Article A ON A.Id = FA.ArticleId
        JOIN ViolationType V ON V.Id = A.ViolationTypeId
        JOIN FineTrespasser FT ON F.Id = FT.FineId
        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
        LEFT JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Result R ON FN.ResultId=R.Id',
    'where' => 'F.CityId = "@Ente"
        and coalesce(F.ControllerDate, F.FineDate) >= "@FromDate"
        and coalesce(F.ControllerDate, F.FineDate) <= "@ToDate"
		and F.ProtocolYear = @ProtocolYear
        and F.ProtocolId > 0
        and F.StatusTypeId NOT in (32,33,34,35,36,37,40,90,91)
        and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
		and TAR.ReducedPayment = 1
        and V.RuleTypeId = 1',
    'groupBy' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")'
);

//dividi gli archiviati definitivi
const STAT_FINE_COLLECTION_ARCHIVIATI = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")',
        'Count' => 'count(*)',
        'Min' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Max' => 'sum(FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Sum' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))'
    ),
    'from' => 'Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN Article A ON A.Id = FA.ArticleId
        JOIN ViolationType V ON V.Id = A.ViolationTypeId
        JOIN FineTrespasser FT ON F.Id = FT.FineId
        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
        LEFT JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Result R ON FN.ResultId=R.Id',
    'where' => 'F.CityId = "@Ente"
        and coalesce(F.ControllerDate, F.FineDate) >= "@FromDate"
        and coalesce(F.ControllerDate, F.FineDate) <= "@ToDate"
		and F.ProtocolYear = @ProtocolYear
        and F.ProtocolId > 0
        and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
		and F.StatusTypeId in (32,34,35,37,40,90,91)
        and V.RuleTypeId = 1',
    'groupBy' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")'
);

//dai rinotificati
const STAT_FINE_COLLECTION_RINOTIFICATI = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")',
        'Count' => 'count(*)',
        'Min' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Max' => 'sum(FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Sum' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))'
    ),
    'from' => 'Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN Article A ON A.Id = FA.ArticleId
        JOIN ViolationType V ON V.Id = A.ViolationTypeId
        JOIN FineTrespasser FT ON F.Id = FT.FineId
        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
        LEFT JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Result R ON FN.ResultId=R.Id',
    'where' => 'F.CityId = "@Ente"
        and coalesce(F.ControllerDate, F.FineDate) >= "@FromDate"
        and coalesce(F.ControllerDate, F.FineDate) <= "@ToDate"
		and F.ProtocolYear = @ProtocolYear
        and F.ProtocolId > 0
        and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
		and F.StatusTypeId in (33,36)
        and V.RuleTypeId = 1',
    'groupBy' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")'
);

//in attesa stranieri
const STAT_FINE_COLLECTION_NONOT_STRANIERI = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")',
        'Count' => 'count(*)',
        'Min' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Max' => 'sum(FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Sum' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))'
    ),
    'from' => 'Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN Article A ON A.Id = FA.ArticleId
        JOIN ViolationType V ON V.Id = A.ViolationTypeId
        JOIN FineTrespasser FT ON F.Id = FT.FineId
        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
        LEFT JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Result R ON FN.ResultId=R.Id',
    'where' => 'F.CityId = "@Ente"
        and coalesce(F.ControllerDate, F.FineDate) >= "@FromDate"
        and coalesce(F.ControllerDate, F.FineDate) <= "@ToDate"
		and F.ProtocolYear = @ProtocolYear
        and F.ProtocolId > 0
        and F.StatusTypeId NOT in (32,33,34,35,36,37,40,90,91)
        and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
		and F.CountryId != "Z000"
		AND FN.NotificationDate is null
        and V.RuleTypeId = 1',
    'groupBy' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")'
);

const STAT_FINE_COLLECTION_NONOT_STAM_RIST = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")',
        'Count' => 'count(*)',
        'Min' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Max' => 'sum(FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Sum' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))'
    ),
    'from' => 'Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN Article A ON A.Id = FA.ArticleId
        JOIN ViolationType V ON V.Id = A.ViolationTypeId
        JOIN FineTrespasser FT ON F.Id = FT.FineId
        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
        LEFT JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Result R ON FN.ResultId=R.Id',
    'where' => 'F.CityId = "@Ente"
        and coalesce(F.ControllerDate, F.FineDate) >= "@FromDate"
        and coalesce(F.ControllerDate, F.FineDate) <= "@ToDate"
		and F.ProtocolYear = @ProtocolYear
        and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
		AND F.PreviousId>0
        and F.ProtocolId > 0
        AND F.CountryId = "Z000"
        and F.StatusTypeId NOT in (32,33,34,35,36,37,40,90,91)
        AND F.ProtocolId IN(
        	SELECT FMulti.ProtocolId FROM (
        		SELECT F2.ProtocolId,COUNT(F2.Id)
        		FROM Fine F2
        		JOIN FineHistory FH6 ON F2.Id = FH6.FineId AND FH6.NotificationTypeId = 6
        		WHERE F2.CityId="@Ente" AND F2.Code=Code AND F2.ProtocolId=ProtocolId AND ((FH6.ResultId > 9 and FH6.ResultId < 21) or FH6.ResultId=23)
        		GROUP BY F2.ProtocolId HAVING COUNT(F2.Id) >= 2)
        	AS FMulti)
        and V.RuleTypeId = 1',
    'groupBy' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")'
);

//in attesa da meno di 90 gg nazionali
const STAT_FINE_COLLECTION_WAITING = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")',
        'Count' => 'count(*)',
        'Min' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Max' => 'sum(FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Sum' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))'
    ),
    'from' => 'Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN Article A ON A.Id = FA.ArticleId
        JOIN ViolationType V ON V.Id = A.ViolationTypeId
        JOIN FineTrespasser FT ON F.Id = FT.FineId
        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
        LEFT JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Result R ON FN.ResultId=R.Id',
    'where' => 'F.CityId = "@Ente"
        and coalesce(F.ControllerDate, F.FineDate) >= "@FromDate"
        and coalesce(F.ControllerDate, F.FineDate) <= "@ToDate"
		and F.ProtocolYear = @ProtocolYear
        and F.ProtocolId > 0
        and F.StatusTypeId NOT in (32,33,34,35,36,37,40,90,91)
        and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
		and FN.FineId is null and DATEDIFF(now(),FH.SendDate)<=90
        and F.CountryId = "Z000"
        and V.RuleTypeId = 1',
    'groupBy' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")'
);

//INESITATI in attesa da oltre  90 gg nazionali
const STAT_FINE_COLLECTION_EXPIRED = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")',
        'Count' => 'count(*)',
        'Min' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Max' => 'sum(FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Sum' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))'
    ),
    'from' => 'Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN Article A ON A.Id = FA.ArticleId
        JOIN ViolationType V ON V.Id = A.ViolationTypeId
        JOIN FineTrespasser FT ON F.Id = FT.FineId
        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
        LEFT JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Result R ON FN.ResultId=R.Id',
    'where' => 'F.CityId = "@Ente"
        and coalesce(F.ControllerDate, F.FineDate) >= "@FromDate"
        and coalesce(F.ControllerDate, F.FineDate) <= "@ToDate"
		and F.ProtocolYear = @ProtocolYear
        and F.ProtocolId > 0
        and F.StatusTypeId NOT in (32,33,34,35,36,37,40,90,91)
        and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
		and FN.FineId is null and DATEDIFF(now(),FH.SendDate)>90
        and F.CountryId = "Z000"
        and V.RuleTypeId = 1',
    'groupBy' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")'
);

//da RIVEDERE
//anno di accertamento 2022 e anno di notifica 2023
const STAT_FINE_COLLECTION_ACC2022_NOT2023 = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")',
        'Count' => 'count(*)',
        'Min' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Max' => 'sum(FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Sum' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))'
    ),
    'from' => "Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN Article A ON A.Id = FA.ArticleId
        JOIN ViolationType V ON V.Id = A.ViolationTypeId
        JOIN FineTrespasser FT ON F.Id = FT.FineId
        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
        JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Result R ON FN.ResultId=R.Id",
    'where' => "F.CityId = '@Ente'
        and coalesce(F.ControllerDate, F.FineDate) >= '@FromDate'
        and coalesce(F.ControllerDate, F.FineDate) <= '@ToDate'
        and F.ProtocolId > 0
        and F.StatusTypeId NOT in (32,33,34,35,36,37,40,90,91)
		and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
		and F.ProtocolYear = DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), '%Y')
        and FN.NotificationDate is not null && DATE_FORMAT(FN.NotificationDAte, '%Y') = (DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), '%Y') + 1)
        and V.RuleTypeId = 1",
    'groupBy' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")'
);


//forse va aggiunto un vincolo sul pagamento presente
//ridotto
//PAGATO ENTRO 5 GG --> 0
//PAGATO OLTRE TRA 6 E 60 --> DIFF TRA MIN E RIDOTTO
//PAGATO OLTRE 60 GG --> DIFF TRA META MAX E MIN
// sottraggo anche il pagato

//NON PAGATO diff tra oggi e notifica e rende quanto sopra

//normale
//pagato entro 60 gg --> 0
//pagato tra 60 e 180 --> --> DIFF TRA META MAX E MIN
// sottraggo anche il pagato
//NON PAGATO diff tra oggi e notifica e rende quanto sopra

//default metà del max - pagato

//NOTA: 09/09/2022 considerare il ruolo solo per quelli per cui abbiamo la data di notifca


//Secondo accordi con l'ente Formigine del 08/09/2022
// calcoliamo l'importo a ruolo solo per quelli che risultano notificati
// e che hanno pagamento
// Nel default sottraggo comunque il minimo edittale che è la base che ho contato per tutti
const STAT_FINE_COLLECTION_DIFFSANZ_RID_RUOLO = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")',
        'Count' => 'count(*)',
        'Min' => 'sum(FA.Fee +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Max' => 'sum(FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))',
        'Sum' => 'sum((case
    when TAR.ReducedPayment = 1 &&
        (select COUNT(FP.PaymentDate) From FinePayment FP where FP.FineId = F.Id)>0
        && (DATEDIFF(coalesce((select min(FP.PaymentDate) as DataP From FinePayment FP where FP.FineId = F.Id), now()), FN.NotificationDate)
        <= 5)
    then 0
        
    when TAR.ReducedPayment = 1 &&
        (select COUNT(FP.PaymentDate) From FinePayment FP where FP.FineId = F.Id)>0
        && (DATEDIFF(coalesce((select min(FP.PaymentDate) as DataP From FinePayment FP where FP.FineId = F.Id), now()), FN.NotificationDate)
        between 6 and 60)
    then (FA.Fee +coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))
       - ((FA.Fee +coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0)) * 0.70
            + (select sum(FP.Amount) From FinePayment FP where FP.FineId = F.Id))
        
    when TAR.ReducedPayment = 1 &&
        (select COUNT(FP.PaymentDate) From FinePayment FP where FP.FineId = F.Id)>0
        && (DATEDIFF(coalesce((select min(FP.PaymentDate) as DataP From FinePayment FP where FP.FineId = F.Id), now()), FN.NotificationDate)
        > 60)
    then (FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0) )
       - (FA.Fee +coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0)
            + (select sum(FP.Amount) From FinePayment FP where FP.FineId = F.Id))
        
    when TAR.ReducedPayment = 1 &&
        (select COUNT(FP.PaymentDate) From FinePayment FP where FP.FineId = F.Id)=0
        && (DATEDIFF(coalesce((select min(FP.PaymentDate) as DataP From FinePayment FP where FP.FineId = F.Id), now()), FN.NotificationDate)
        <= 5)
    then 0
        
    when TAR.ReducedPayment = 1 &&
        (select COUNT(FP.PaymentDate) From FinePayment FP where FP.FineId = F.Id)=0
        && (DATEDIFF(coalesce((select min(FP.PaymentDate) as DataP From FinePayment FP where FP.FineId = F.Id), now()), FN.NotificationDate)
        between 6 and 60)
    then (FA.Fee +coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))
       - ((FA.Fee +coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0)) * 0.70)
        
    when TAR.ReducedPayment = 1 &&
        (select COUNT(FP.PaymentDate) From FinePayment FP where FP.FineId = F.Id)=0
        && (DATEDIFF(coalesce((select min(FP.PaymentDate) as DataP From FinePayment FP where FP.FineId = F.Id), now()), FN.NotificationDate)
        > 60)
    then (FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0) )
       - (FA.Fee +coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))
        
        
    when TAR.ReducedPayment = 0 &&
        (select COUNT(FP.PaymentDate) From FinePayment FP where FP.FineId = F.Id)>0
        && (DATEDIFF(coalesce((select min(FP.PaymentDate) as DataP From FinePayment FP where FP.FineId = F.Id), now()), FN.NotificationDate)
        <= 60)
    then 0
        
    when TAR.ReducedPayment = 0 &&
        (select COUNT(FP.PaymentDate) From FinePayment FP where FP.FineId = F.Id)>0
        && (DATEDIFF(coalesce((select min(FP.PaymentDate) as DataP From FinePayment FP where FP.FineId = F.Id), now()), FN.NotificationDate)
        between 61 and 180)
    then (FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))
       - (FA.Fee +coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0)
          + (select sum(FP.Amount) From FinePayment FP where FP.FineId = F.Id))
        
    when TAR.ReducedPayment = 0
         && (select COUNT(FP.PaymentDate) From FinePayment FP where FP.FineId = F.Id)=0
         && (DATEDIFF(coalesce((select min(FP.PaymentDate) as DataP From FinePayment FP where FP.FineId = F.Id), now()), FN.NotificationDate)
         <= 60)
    then 0
        
    when TAR.ReducedPayment = 0
        && (select COUNT(FP.PaymentDate) From FinePayment FP where FP.FineId = F.Id)=0
        && (DATEDIFF(coalesce((select min(FP.PaymentDate) as DataP From FinePayment FP where FP.FineId = F.Id), now()), FN.NotificationDate)
        between 61 and 180)
    then (FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))
       - (FA.Fee +coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0))
        
    else
        FA.MaxFee/2 +  coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0)
        - (FA.Fee +coalesce(FH.CustomerFee,0)+coalesce(FH.ResearchFee,0)+coalesce(FH.NotificationFee,0) + coalesce(FT.OwnerAdditionalFee,0)+coalesce(FT.CustomerAdditionalFee,0)
            + (select sum(FP.Amount) From FinePayment FP where FP.FineId = F.Id))
end))'
    ),
    'from' => 'Fine F
JOIN FineArticle FA ON F.Id = FA.FineId
JOIN ArticleTariff TAR ON TAR.ArticleId = FA.ArticleId and TAR.Year = F.ProtocolYear
JOIN Article A ON A.Id = FA.ArticleId
JOIN ViolationType V ON V.Id = A.ViolationTypeId
JOIN FineTrespasser FT ON F.Id = FT.FineId
JOIN Trespasser T ON FT.TrespasserId=T.Id
JOIN Country C ON T.CountryId = C.Id
JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
JOIN FineNotification FN ON F.Id=FN.FineId
LEFT JOIN Result R ON FN.ResultId=R.Id',
    'where' => 'F.CityId = "@Ente"
and coalesce(F.ControllerDate, F.FineDate) >= "@FromDate"
and coalesce(F.ControllerDate, F.FineDate) <= "@ToDate"
and F.ProtocolYear = @ProtocolYear
and F.ProtocolId > 0
and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
and FN.NotificationDate is not null
and V.RuleTypeId = 1',
    'groupBy' => 'DATE_FORMAT(coalesce(F.ControllerDate, F.FineDate), "%m")'
);

const STAT_FINE_COLLECTION_SANZ_INC_ANNO_COMP = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(@PaymentDateColumn, "%m")',
        'Count' => 'count(*)',
        'Min' => '"0"',
        'Max' => '"0"',
        'Sum' => 'sum(FP.Amount)'
    ),
    'from' => 'Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN FinePayment FP on F.Id = FP.FineId
        JOIN Article A ON A.Id = FA.ArticleId
        JOIN ViolationType V ON V.Id = A.ViolationTypeId
        JOIN FineTrespasser FT ON F.Id = FT.FineId
        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
        LEFT JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Result R ON FN.ResultId=R.Id',
    'where' => 'F.CityId = "@Ente"
        and coalesce(F.ControllerDate, F.FineDate) >= "@FromDate"
        and coalesce(F.ControllerDate, F.FineDate) <= "@ToDate"
		and F.ProtocolYear = @ProtocolYear
		and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
		and DATE_FORMAT(@PaymentDateColumn, "%Y") = @ProtocolNextYear
        and V.RuleTypeId = 1',
    'groupBy' => 'DATE_FORMAT(@PaymentDateColumn, "%m")'
);

const STAT_FINE_COLLECTION_SANZ_INC = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(@PaymentDateColumn, "%m")',
        'Count' => 'count(*)',
        'Min' => '"0"',
        'Max' => '"0"',
        'Sum' => 'sum(FP.Amount)'
    ),
    'from' => 'Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN FinePayment FP on F.Id = FP.FineId
        JOIN Article A ON A.Id = FA.ArticleId
        JOIN ViolationType V ON V.Id = A.ViolationTypeId
        JOIN FineTrespasser FT ON F.Id = FT.FineId
        JOIN Trespasser T ON FT.TrespasserId=T.Id
        JOIN Country C ON T.CountryId = C.Id
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        LEFT JOIN FineHistory FH ON F.Id= FH.FineId AND FH.NotificationTypeId=6
        LEFT JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Result R ON FN.ResultId=R.Id',
    'where' => 'F.CityId = "@Ente"
		and (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
		and DATE_FORMAT(@PaymentDateColumn, "%Y") = @ProtocolNextYear
        and V.RuleTypeId = 1',
    'groupBy' => 'DATE_FORMAT(@PaymentDateColumn, "%m")'
);

const STAT_FINE_COLLECTION_SANZ_INC_NON_ASSOC = array(
    'aliases' => array(
        'Month' => 'DATE_FORMAT(@PaymentDateColumn, "%m")',
        'Count' => 'count(*)',
        'Min' => '"0"',
        'Max' => '"0"',
        'Sum' => 'sum(FP.Amount)'
    ),
    'from' => 'FinePayment FP',
    'where' => 'FP.CityId = "@Ente"
        and @PaymentDateColumn >= "@FromDate"
        and @PaymentDateColumn <= "@ToDate"
        and FP.FineId = 0',
    'groupBy' => 'DATE_FORMAT(@PaymentDateColumn, "%m")'
);

const MGMT_PEC_DOWNLOAD = array(
    'aliases' => array(
        'Id' => 'DE.Id',
        'MailSubject' => 'DE.MailSubject',
        'MailFrom' => 'DE.MailFrom',
        'Status' => 'DE.Status',
        'ReceiveDate' => 'DE.ReceiveDate',
        'ReceiveTime' => 'DE.ReceiveTime',
        'VersionDate' => 'DE.VersionDate',
        'VersionTime' => 'DE.VersionTime',
        'UserId' => 'DE.UserId',
    ),
    "from" => "DownloadEmail DE
    	left join DownloadEmailCategories DECA on DECA.DownloadEmailId = DE.Id",
    
    "where" => '1=1',
    'groupBy' => 'DE.Id'
);

const PRN_NOTIFICATION_MISSING = array(
    'aliases' => array(
        'FlowId' => 'fl.Id',
        'FlowNumber' => 'fl.Number',
        'FlowFileName' => 'fl.FileName',
        'PrinterId' => 'fl.PrinterId',
        'PrintTypeId' => 'fl.PrintTypeId',
        'SendDate' => 'fl.SendDate',
        'PaymentDate' => 'fl.PaymentDate',
        'ProcessingDate' => 'fl.ProcessingDate',
        'UploadDate' => 'fl.UploadDate',
        'CreationDate' => 'fl.CreationDate',
        'RuleTypeId' => 'fl.RuleTypeId',
        'FineHistoryId' => 'fh.Id',
        'NotificationTypeId' => 'fh.NotificationTypeId',
        'ResultId' => 'fh.ResultId',
        'FineId' => 'fi.Id',
        'ProtocolId' => 'fi.ProtocolId',
        'ProtocolYear' => 'fi.ProtocolYear',
        'CityId' => 'ci.Id',
        'CityName' => 'ci.Title',
    ),
    "from" => "traffic_law.Flow fl
            JOIN
            traffic_law.FineHistory fh ON fl.Id = fh.FlowId
            JOIN
            traffic_law.Fine fi ON fh.FineId = fi.Id
            JOIN
            sarida.City ci ON fi.CityId = ci.Id",
    
    "where" => '1=1 AND
            fh.NotificationTypeId = 6
            AND
            fh.ResultId IS NULL'
);

//INIZIO prn_useractivity/////////////////////////
const USERACTIVITY_ANOMALY_BRANDMODEL = array(
    'aliases' => array(
        'QUERY_Id' => 'ABMH.AnomalyBrandModelId',
        'QUERY_RegDate' => 'TIMESTAMP(ABMH.UpdateDate, COALESCE(ABMH.UpdateTime, TIME("00:00:00")))',
        'QUERY_UserName' => 'ABMH.UpdateUserId',
        '"Fonte"' => 'DS.Description',
        '"Data acquisizione fonte"' => 'DATE_FORMAT(ABMH.DataSourceDate, "%d/%m/%Y")',
        '"Ora acquisizione fonte"' => 'ABMH.DataSourceTime',
        '"Marca originale"' => 'ABMH.Brand',
        '"Modello originale"' => 'ABMH.Model',
        '"Tipo veicolo originale"' => 'VT.TitleIta',
        '"Data reg."' => 'DATE_FORMAT(ABMH.UpdateDate, "%d/%m/%Y")',
        '"Ora reg."' => 'ABMH.UpdateTime',
        '"Operatore reg."' => 'ABMH.UpdateUserId'
    ),
    "from" => "AnomalyBrandModelHistory ABMH
        LEFT JOIN DataSource DS ON ABMH.DataSourceId = DS.Id
        LEFT JOIN VehicleType VT ON ABMH.VehicleTypeId = VT.Id",
    "where" => '1=1 AND ABMH.UpdateDataSourceId = 1');

const USERACTIVITY_DOCUMENT = array(
    'aliases' => array(
        'QUERY_Id' => 'FD.Id',
        'QUERY_RegDate' => 'FD.VersionDate',
        'QUERY_UserName' => 'FD.UserId',
        '"Cron. | Anno | Targa"' => 'CONCAT(F.ProtocolId, " | ", F.ProtocolYear, " | ", F.VehiclePlate)',
        '"Data verb."' => 'DATE_FORMAT(F.FineDate, "%d/%m/%Y")',
        '"Nome Documento"' => 'FD.Documentation',
        '"Tipo Documento"' => 'DT.Title',
        '"Note"' => 'FD.Note',
        '"Data reg."' => 'DATE_FORMAT(DATE(FD.VersionDate), "%d/%m/%Y")',
        '"Ora reg."' => 'TIME(FD.VersionDate)',
        '"Operatore reg."' => 'FD.UserId'
    ),
    "from" => "Fine F
        JOIN FineDocumentation FD ON FD.FineId = F.Id
        JOIN DocumentationType DT ON FD.DocumentationTypeId = DT.Id
        LEFT JOIN VehicleType VT ON F.VehicleTypeId = VT.Id",
    //Atti non rinotificati ma registrati su programma
    "where" => '1=1 AND FD.VersionDate IS NOT NULL AND FD.UserId IS NOT NULL AND FD.DocumentationTypeId IN(@DocumentationTypes)',
);

const USERACTIVITY_COMMUNICATION_126BIS = array(
    'aliases' => array(
        'QUERY_Id' => 'FC.FineId',
        'QUERY_RegDate' => 'TIMESTAMP(FC.RegDate, COALESCE(FC.RegTime, TIME("00:00:00")))',
        'QUERY_UserName' => 'FC.UserId',
        '"Cron. | Anno | Targa"' => 'CONCAT(F.ProtocolId, " | ", F.ProtocolYear, " | ", F.VehiclePlate)',
        '"Data verb."' => 'DATE_FORMAT(F.FineDate, "%d/%m/%Y")',
        '"Trasgressore | Obbligato in solido"' => "CONCAT(CONCAT_WS(' ', nullif(T.CompanyName,''),nullif(T.Surname,''),nullif(T.Name,'')), ' | ', COALESCE(NULLIF(T.VatCode,''), NULLIF(T.TaxCode, ''), ''))",
        '"Data comunicazione"' => 'DATE_FORMAT(FC.CommunicationDate, "%d/%m/%Y")',
        '"N. Protocollo com."' => 'CommunicationProtocol',
        '"Incompleta"' => 'CASE WHEN FC.Incomplete = 0 THEN "NO" ELSE "SI" END',
        '"Data comunicazione"' => 'DATE_FORMAT(FC.CommunicationDate, "%d/%m/%Y")',
        '"Data reg."' => 'DATE_FORMAT(FC.RegDate, "%d/%m/%Y")',
        '"Ora reg."' => 'FC.RegTime',
        '"Operatore reg."' => 'FC.UserId'
    ),
    "from" => "FineCommunication FC
        JOIN Fine F ON FC.FineId = F.Id
        LEFT JOIN Trespasser T ON FC.TrespasserId = T.Id",
    "where" => '1=1 AND FC.RegDate IS NOT NULL AND FC.UserId IS NOT NULL');

const USERACTIVITY_ARCHIVED = array(
    'aliases' => array(
        'QUERY_Id' => 'F.Id',
        'QUERY_RegDate' => 'TIMESTAMP(FAR.RegDate, COALESCE(FAR.RegTime, TIME("00:00:00")))',
        'QUERY_UserName' => 'FAR.UserId',
        '"Id | Anno"' => 'CONCAT(F.Id, " | ", F.ProtocolYear)',
        '"Cron. | Targa"' => 'CONCAT(F.ProtocolId, " | ", F.VehiclePlate)',
        '"Tipo veicolo/Articolo | Luogo Infrazione"' => 'CONCAT(VT.TitleIta," / ",CONCAT_WS(" ", A.Article, COALESCE(NULLIF(A.Paragraph, ""), "-"), NULLIF(A.Letter, ""))," | ",F.Address)',
        '"Data verb."' => 'DATE_FORMAT(F.FineDate, "%d/%m/%Y")',
        '"Note"' => 'FAR.Note',
        '"Data reg."' => 'DATE_FORMAT(FAR.RegDate, "%d/%m/%Y")',
        '"Ora reg."' => 'FAR.RegTime',
        '"Operatore reg."' => 'FAR.UserId'
    ),
    "from" => "Fine F
        JOIN FineArticle FA ON F.Id = FA.FineId
        JOIN Article A ON FA.ArticleId = A.Id
        JOIN FineArchive FAR ON F.Id = FAR.FineId
        LEFT JOIN VehicleType VT ON F.VehicleTypeId = VT.Id",
    //Atti non rinotificati ma registrati su programma
    "where" => 'F.StatusTypeId = 35',
);

const USERACTIVITY_FINE_REGISTERED = array(
    'aliases' => array(
        'QUERY_Id' => 'F.Id',
        'QUERY_RegDate' => 'TIMESTAMP(F.RegDate, COALESCE(F.RegTime, TIME("00:00:00")))',
        'QUERY_UserName' => 'F.UserId',
        '"Id | Anno"' => 'CONCAT(F.Id, " | ", F.ProtocolYear)',
        '"Tipologia atto"' => '
            CASE
                WHEN F.FineTypeId = 3 THEN "VERBALE"
                WHEN F.FineTypeId = 4 THEN "VERBALE CONTRATTO"
                WHEN F.FineTypeId = 5 THEN "VERBALE D\'UFFICIO"
                ELSE F.Code
            END',
        '"Trasgressore | Obbligato in solido"' => "CONCAT(CONCAT_WS(' ', nullif(T.CompanyName,''),nullif(T.Surname,''),nullif(T.Name,'')), ' | ', COALESCE(NULLIF(T.VatCode,''), NULLIF(T.TaxCode, ''), ''))",
        '"Cron. | Targa"' => 'CONCAT(F.ProtocolId, " | ", F.VehiclePlate)',
        '"Tipo veicolo/Articolo | Luogo Infrazione"' => 'CONCAT(VT.TitleIta," / ",CONCAT_WS(" ", A.Article, COALESCE(NULLIF(A.Paragraph, ""), "-"), NULLIF(A.Letter, ""))," | ",F.Address)',
        '"Data verb. | Data not."' => 'CONCAT_WS(" | ", DATE_FORMAT(F.FineDate, "%d/%m/%Y"), DATE_FORMAT(FN.NotificationDate, "%d/%m/%Y"))',
        '"Data reg."' => 'DATE_FORMAT(F.RegDate, "%d/%m/%Y")',
        '"Ora reg."' => 'F.RegTime',
        '"Operatore reg."' => 'F.UserId'
    ),
    "from" => "Fine F 
        JOIN FineTrespasser FT ON F.Id = FT.FineId AND FT.TrespasserTypeId in(1,3,11) 
        JOIN Trespasser T ON FT.TrespasserId = T.Id 
        JOIN FineArticle FA ON F.Id = FA.FineId 
        JOIN Article A ON FA.ArticleId = A.Id
        LEFT JOIN FineNotification FN ON F.Id = FN.FineId
        LEFT JOIN VehicleType VT ON F.VehicleTypeId = VT.Id",
    //Atti non rinotificati ma registrati su programma
    "where" => 'F.StatusTypeId >= 10 AND F.FineTypeId IN (3,4,5) AND F.PreviousId = 0',
    
    'union' => array(
        'Rinotifiche' => array(
            'aliases' => array(
                'QUERY_Id' => 'F.Id',
                'QUERY_RegDate' => 'TIMESTAMP(F.RegDate, COALESCE(F.RegTime, TIME("00:00:00")))',
                'QUERY_UserName' => 'F.UserId',
                '"Id | Anno"' => 'CONCAT(F.Id, " | ", F.ProtocolYear)',
                '"Tipologia atto"' => '"RINOTIFICA"',
                '"Trasgressore | Obbligato in solido"' => "CONCAT(CONCAT_WS(' ', nullif(T.CompanyName,''),nullif(T.Surname,''),nullif(T.Name,'')), ' | ', COALESCE(NULLIF(T.VatCode,''), NULLIF(T.TaxCode, ''), ''))",
                '"Cron. | Targa"' => 'CONCAT(F.ProtocolId, " | ", F.VehiclePlate)',
                '"Tipo veicolo/Articolo | Luogo Infrazione"' => 'CONCAT(VT.TitleIta," / ",CONCAT_WS(" ", A.Article, COALESCE(NULLIF(A.Paragraph, ""), "-"), NULLIF(A.Letter, ""))," | ",F.Address)',
                '"Data verb. | Data not."' => 'CONCAT_WS(" | ", DATE_FORMAT(F.FineDate, "%d/%m/%Y"), DATE_FORMAT(FN.NotificationDate, "%d/%m/%Y"))',
                '"Data reg."' => 'DATE_FORMAT(F.RegDate, "%d/%m/%Y")',
                '"Ora reg."' => 'F.RegTime',
                '"Operatore reg."' => 'F.UserId'
            ),
            "from" => "Fine F
                JOIN FineTrespasser FT ON F.Id = FT.FineId AND FT.TrespasserTypeId in(1,3,11)
                JOIN Trespasser T ON FT.TrespasserId = T.Id
                JOIN FineArticle FA ON F.Id = FA.FineId
                JOIN Article A ON FA.ArticleId = A.Id
                LEFT JOIN FineNotification FN ON F.Id = FN.FineId
                LEFT JOIN VehicleType VT ON F.VehicleTypeId = VT.Id",
            //Atti rinotificati ma non 126bis
            "where" => "F.StatusTypeId >= 10 AND F.PreviousId > 0 and FA.ViolationTypeId != 5"
        )
    )
);

const USERACTIVITY_ANAG_REGISTERED = array(
    'aliases' => array(
        'QUERY_Id' => 'T.Code',
        'QUERY_RegDate' => 'TIMESTAMP(COALESCE(T.DataSourceDate, T.VersionDate), COALESCE(T.DataSourceTime, TIME("00:00:00")))',
        'QUERY_UserName' => 'T.UserId',
        '"Cod. anagr."' => 'T.Code',
        '"Contribuente | Indirizzo Residenza | Cap Comune (Prov.)"' => "
            CONCAT(
                CONCAT_WS(' ', nullif(T.CompanyName,''),nullif(T.Surname,''),nullif(T.Name,''),COALESCE(NULLIF(T.VatCode,''), NULLIF(T.TaxCode, ''), '')), 
                ' | ',
                CONCAT_WS(' ', T.Address, T.StreetNumber),
                ' | ',
                CONCAT_WS(' ', T.ZIP, T.City, CONCAT('(', T.Province, ')'))
            )",
        '"Azione"' => '"INSERIMENTO"',
        '"Data reg."' => 'DATE_FORMAT(COALESCE(T.DataSourceDate, T.VersionDate), "%d/%m/%Y")',
        '"Ora reg."' => 'T.DataSourceTime',
        '"Operatore reg."' => 'T.UserId'
    ),
    
    "from" => "Trespasser T",
    
    "where" => 'T.DataSourceId = 1 AND T.DataSourceDate = T.VersionDate',
    
    "union" => array(
        'Modifiche' => array(
            'aliases' => array(
                'QUERY_Id' => 'T.Code',
                'QUERY_RegDate' => 'TIMESTAMP(TH.UpdateDataSourceDate, COALESCE(TH.UpdateDataSourceTime, TIME("00:00:00")))',
                'QUERY_UserName' => 'T.UserId',
                '"Cod. anagr."' => 'T.Code',
                '"Contribuente | Indirizzo Residenza | Cap Comune (Prov.)"' => "
            CONCAT(
                CONCAT_WS(' ', nullif(T.CompanyName,''),nullif(T.Surname,''),nullif(T.Name,''),COALESCE(NULLIF(T.VatCode,''), NULLIF(T.TaxCode, ''), '')),
                ' | ',
                CONCAT_WS(' ', T.Address, T.StreetNumber),
                ' | ',
                CONCAT_WS(' ', T.ZIP, T.City, CONCAT('(', T.Province, ')'))
            )",
                '"Azione"' => '"MODIFICA"',
                '"Data reg."' => 'DATE_FORMAT(UpdateDataSourceDate, "%d/%m/%Y")',
                '"Ora reg."' => 'TH.UpdateDataSourceTime',
                '"Operatore reg."' => 'TH.UpdateUserId',
            ),
            
            "from" => "Trespasser T
                JOIN TrespasserHistory TH ON TH.TrespasserId = T.Id",
            
            "where" => 'TH.UpdateDataSourceId = 1',
        )
    )
);

const USERACTIVITY_NOTIFICATION_REGISTERED = array(
    'aliases' => array(
        'QUERY_Id' => 'F.Id',
        'QUERY_RegDate' => 'TIMESTAMP(FN.RegDate, COALESCE(FN.RegTime, TIME("00:00:00")))',
        'QUERY_UserName' => 'FN.UserId',
        '"Id | Anno"' => 'CONCAT(F.Id, " | ", F.ProtocolYear)',
        '"Trasgressore | Obbligato in solido"' => "CONCAT(CONCAT_WS(' ', nullif(T.CompanyName,''),nullif(T.Surname,''),nullif(T.Name,'')), ' | ', COALESCE(NULLIF(T.VatCode,''), NULLIF(T.TaxCode, ''), ''))",
        '"Cron. | Targa"' => 'CONCAT(F.ProtocolId, " | ", F.VehiclePlate)',
        '"Tipo veicolo/Articolo | Luogo Infrazione"' => 'CONCAT(VT.TitleIta," / ",CONCAT_WS(" ", A.Article, COALESCE(NULLIF(A.Paragraph, ""), "-"), NULLIF(A.Letter, ""))," | ",F.Address)',
        '"Data verb. | Data not."' => 'CONCAT_WS(" | ", DATE_FORMAT(F.FineDate, "%d/%m/%Y"), COALESCE(DATE_FORMAT(FN.NotificationDate, "%d/%m/%Y") ,"MANCATO RECAPITO"))',
        '"Data reg."' => 'DATE_FORMAT(FN.RegDate, "%d/%m/%Y")',
        '"Ora reg."' => 'FN.RegTime',
        '"Operatore reg."' => 'FN.UserId'
    ),
    "from" => "Fine F 
        JOIN FineNotification FN ON F.Id = FN.FineId
        JOIN FineTrespasser FT ON F.Id = FT.FineId AND FT.TrespasserTypeId in(1,3,11) 
        JOIN Trespasser T ON FT.TrespasserId = T.Id 
        JOIN FineArticle FA ON F.Id = FA.FineId 
        JOIN Article A ON FA.ArticleId = A.Id
        LEFT JOIN VehicleType VT ON F.VehicleTypeId = VT.Id",
    
    "where" => 'Box IS NULL AND Lot IS NULL'
);

const USERACTIVITY_TRESPASSER_REGISTERED = array(
    'aliases' => array(
        'QUERY_Id' => 'FT.FineId',
        'QUERY_RegDate' => 'TIMESTAMP(FT.RegDate, COALESCE(FT.RegTime, TIME("00:00:00")))',
        'QUERY_UserName' => 'FN.UserId',
        '"Cod. anagr. | Anno"' => 'CONCAT(T.Code, " | ", F.ProtocolYear)',
        '"Trasgressore | Obbligato in solido"' => "CONCAT(CONCAT_WS(' ', nullif(T.CompanyName,''),nullif(T.Surname,''),nullif(T.Name,'')), ' | ', COALESCE(NULLIF(T.VatCode,''), NULLIF(T.TaxCode, ''), ''))",
        '"Cron. | Targa"' => 'CONCAT(F.ProtocolId, " | ", F.VehiclePlate)',
        '"Tipo veicolo/Articolo | Luogo Infrazione"' => 'CONCAT(VT.TitleIta," / ",CONCAT_WS(" ", A.Article, COALESCE(NULLIF(A.Paragraph, ""), "-"), NULLIF(A.Letter, ""))," | ",F.Address)',
        '"Data verb. | Data not."' => 'CONCAT_WS(" | ", DATE_FORMAT(F.FineDate, "%d/%m/%Y"), COALESCE(DATE_FORMAT(FN.NotificationDate, "%d/%m/%Y") ,"MANCATO RECAPITO"))',
        '"Data reg."' => 'DATE_FORMAT(FT.RegDate, "%d/%m/%Y")',
        '"Ora reg."' => 'FT.RegTime',
        '"Operatore reg."' => 'FT.UserId'
    ),
    "from" => "FineTrespasser FT
        JOIN Fine F ON FT.FineId = F.Id 
        JOIN FineArticle FA ON FT.FineId = FA.FineId 
        JOIN Article A ON FA.ArticleId = A.Id
        LEFT JOIN Trespasser T ON FT.TrespasserId = T.Id
        LEFT JOIN FineNotification FN ON FT.FineId = FN.FineId
        LEFT JOIN VehicleType VT ON F.VehicleTypeId = VT.Id", 
    "where" => '1=1 AND FT.RegDate IS NOT NULL AND FT.UserId IS NOT NULL');

const QUERY_LOG = array(
    'aliases' => array(
        'UserId' => 'ql.UserId',
        'Type' => 'ql.Type',
        'RegDate' => 'ql.RegDate',
        'RegTime' => 'ql.RegTime',
        'Query' => 'ql.Query',
    ),
    "from" => "QueryLog ql",
    
    "where" => '1=1'
);
//FINE prn_useractivity/////////////////////////
    
const MGMT_BRANDMODEL = array(
    'distinct' => true,
    'aliases' => array(
        'Id' => 'ABM.Id',
        'Brand' => 'ABM.Brand',
        'Model' => 'ABM.Model',
        'VehicleTypeId' => 'ABM.VehicleTypeId',
        'VehicleTypeTitle' => 'VT.TitleIta',
        'CorrectBrand' => 'ABM.CorrectBrand',
        'CorrectModel' => 'ABM.CorrectModel',
        'CorrectVehicleTypeId' => 'ABM.CorrectVehicleTypeId',
        'Valid' => 'ABM.Valid',
        'DataSourceDate' => 'ABM.DataSourceDate',
    ),
    'from' => 'AnomalyBrandModel ABM
        JOIN FineAnomaly FA ON FA.AnomalyBrandModelId = ABM.Id
        LEFT JOIN VehicleType VT ON VT.Id = ABM.VehicleTypeId',
    'where' => 'Valid = 0'
);

//Simile alla view V_FineTrespasserList
const FINETRESPASSERLIST = array(
    'distinct' => true,
    'aliases' => array(
        'TrespasserId' => 'T.Id',
        'Genre' => 'T.Genre',
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
        'Address' => 'T.Address',
        'ZIP' => 'T.ZIP',
        'City' => 'T.City',
        'Province' => 'T.Province',
        'CountryId' => 'T.CountryId',
        'BornPlace' => 'T.BornPlace',
        'BornCountryId' => 'T.BornCountryId',
        'BornDate' => 'T.BornDate',
        'ForcedTaxCode' => 'T.ForcedTaxCode',
        'TaxCode' => 'T.TaxCode',
        'VatCode' => 'T.VatCode',
        'Phone' => 'T.Phone',
        'Mail' => 'T.Mail',
        'UserId' => 'T.UserId',
        'Fax' => 'T.Fax',
        'Phone2' => 'T.Phone2',
        'Notes' => 'T.Notes',
    ),
    'from' => 'Fine F JOIN FineTrespasser FT ON F.Id = FT.FineId
            JOIN Trespasser T ON FT.TrespasserId = T.Id
            JOIN Country C ON T.CountryId = C.Id
            JOIN FineArticle FA ON F.Id = FA.FineId
            JOIN Article A ON FA.ArticleId = A.Id
            LEFT JOIN FineNotification FN ON FN.FineId = F.Id');

const FRM_SENDFINE = array(
    'aliases' => array(
        'Id' => 'F.Id',
        'Code' => 'F.Code',
        'CityId' => 'F.CityId',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'StatusTypeId' => 'F.StatusTypeId',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'FineTypeId' => 'F.FineTypeId',
        'ViolationControllerId' => 'F.ControllerId',
        'Locality' => 'F.Locality',
        'Address' => 'F.Address',
        'VehicleTypeId' => 'F.VehicleTypeId',
        'VehiclePlate' => 'F.VehiclePlate',
        'CountryId' => 'F.CountryId',
        'VehicleCountry' => 'F.VehicleCountry',
        'DepartmentId' => 'F.DepartmentId',
        'VehicleBrand' => 'F.VehicleBrand',
        'VehicleModel' => 'F.VehicleModel',
        'VehicleColor' => 'F.VehicleColor',
        'VehicleMass' => 'F.VehicleMass',
        'VehicleLastRevision' => 'F.VehicleLastRevision',
        'PreviousId' => 'F.PreviousId',
        'Note' => 'F.Note',
        'ExternalProtocol' => 'F.ExternalProtocol',
        'ExternalYear' => 'F.ExternalYear',
        'ExternalDate' => 'F.ExternalDate',
        'ExternalTime' => 'F.ExternalTime',
        'RuleTypeId' => 'VT.RuleTypeId',
        'NotificationTypeId' => 'FH.NotificationTypeId',
        'TrespasserId' => 'FH.TrespasserId',
        'CustomerFee' => 'FH.CustomerFee',
        'NotificationFee' => 'FH.NotificationFee',
        'ResearchFee' => 'FH.ResearchFee',
        'NotificationDate' => 'FH.NotificationDate',
        'PrintDate' => 'FH.PrintDate',
        'FlowDate' => 'FH.FlowDate',
        'FlowNumber' => 'FH.FlowNumber',
        'SendDate' => 'FH.SendDate',
        'DeliveryDate' => 'FH.DeliveryDate',
        'ResultId' => 'FH.ResultId',
        'Documentation' => 'FH.Documentation',
        'FineControllerId' => 'FH.ControllerId',
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
        'TrespasserTypeId' => 'FT.TrespasserTypeId'
    ),
    'from' => 'Fine F
        JOIN FineArticle FA ON FA.FineId = F.Id
        JOIN ViolationType VT ON FA.ViolationTypeId = VT.Id
        JOIN FineTrespasser FT ON FT.FineId = F.Id
        JOIN FineHistory FH ON FH.FineId = F.Id AND FH.TrespasserId = FT.TrespasserId AND FH.NotificationTypeId = 2
        JOIN Trespasser T ON T.Id = FH.TrespasserId
        JOIN TrespasserType TT ON TT.Id = FH.TrespasserTypeId',
    'where' => 'F.StatusTypeId IN(8,15) AND F.ProtocolId > 0 AND (
          (FT.TrespasserTypeId = 1)  or (FT.TrespasserTypeId = 11) 
          or ((FT.TrespasserTypeId = 2) and (FT.FineCreateDate is null)) 
          or ((FT.TrespasserTypeId = 3) and (FT.FineCreateDate is null)) 
          or ((FT.TrespasserTypeId = 15) and (FT.FineCreateDate is null)) 
          or ((FT.TrespasserTypeId = 16) and (FT.FineCreateDate is null)))'
);

const TBL_FORM = array(
    'aliases' => array(
        'NationalityId' => 'FD.NationalityId',
        'RuleTypeId' => 'FD.RuleTypeId',
        'FormTypeId' => 'FD.FormTypeId',
        'CityId' => 'FD.CityId',
        'LanguageId' => 'FD.LanguageId',
        'Content' => 'FD.Content',
        'VersionDate' => 'FD.VersionDate',
        'UserId' => 'FD.UserId',
        'Title' => 'FD.Title',
        'Deleted' => 'FD.Deleted',
        'LanguageTitle' => 'L.Title',
        'FormTypeTitle' => 'FT.Title'
    ),
    'from' => 'FormDynamic FD
        JOIN Language L ON FD.LanguageId = L.Id
        JOIN FormType FT ON FD.FormTypeId = FT.Id',
    'where' => '1=1'
);

const PRN_VALIDATEDADDRESS = array(
    'aliases' => array(
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'Code' => 'F.Code',
        'CityId' => 'F.CityId',
        'VehiclePlate' => 'F.VehiclePlate',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'FineTypeId' => 'F.FineTypeId',
        'VehicleTypeId' => 'F.VehicleTypeId',
        'ResultId' => 'FN.ResultId',
        'ResultTitle' => 'R.Title',
        'ValidatedAddress' => 'FN.ValidatedAddress',
    ),
    'from' => 'FineNotification FN
        JOIN Fine F ON FN.FineId = F.Id
        JOIN Result R ON R.Id = FN.ResultId',
    'where' => ''
); 

const MGMT_PREFECTCOMMUNICATION = array(
    'aliases' => array(
        "FineId" => "F.Id",
        "SendDate" => "FPC.SendDate",
        "PrefectNotificationDate" => "FPC.NotificationDate",
        "ReceiptNumber" => "FPC.ReceiptNumber",
        "LetterNumber" => "FPC.LetterNumber",
        "SendType" => "FPC.SendType",
        "RegDate" => "FPC.RegDate",
        "RegTime" => "FPC.RegTime",
        "UserId" => "FPC.UserId",
        
        "SuspensionLicense" => "AT.SuspensionLicense",
        "Habitual" => "AT.Habitual",
        "RevisionLicense" => "AT.RevisionLicense",
        "RevisionHabitual" => "AT.RevisionHabitual",
        "RevocationLicense" => "AT.RevocationLicense",
        "RevocationHabitual" => "AT.RevocationHabitual",
        "LossLicense" => "AT.LossLicense",
        "LossHabitual" => "AT.LossHabitual",
        
        "CityId" => "F.CityId",
        "FineTypeId" => "F.FineTypeId",
        "VehiclePlate" => "F.VehiclePlate",
        "VehicleTypeId" => "F.VehicleTypeId",
        "ProtocolId" => "F.ProtocolId",
        "ProtocolYear" => "F.ProtocolYear",
        "FineDate" => "F.FineDate",
        "FineTime" => "F.FineTime",
        "Code" => "F.Code",
        
        "Article" => "A.Article",
        "Paragraph" => "A.Paragraph",
        "Letter" => "A.Letter",
        
        "ResultTitle" => "R.Title",
        "FineDocumentationId" => "FD.Id",
        "ArticleId" => "FA.ArticleId",
        "TrespasserId" => "T.Id",
        'DisputeId' => 'FDI.DisputeId',
        "NotificationDate" => "FN.NotificationDate",
        "FineNotificationType" => "FT.FineNotificationType",
        
        "LicenseDate" => "T.LicenseDate"
    ),
    'from' => 'Fine F
        JOIN FineTrespasser FT ON FT.FineId = F.Id
        JOIN Trespasser T ON FT.TrespasserId = T.Id
        JOIN FineCommunication FC ON FC.FineId = F.Id AND FC.TrespasserId = T.Id
        JOIN FineArticle FA ON FA.FineId = F.Id
        JOIN Article A ON FA.ArticleId = A.Id 
        JOIN ArticleTariff AT ON AT.ArticleId = A.Id
            AND AT.Year = F.ProtocolYear
            AND UseAdditionalSanction IN("fissa","variabile")
            AND (
                SuspensionLicense > 0 OR 
                Habitual > 0 OR
                RevisionLicense > 0 OR
                RevisionHabitual > 0 OR
                RevocationLicense > 0 OR
                RevocationHabitual > 0 OR
                LossLicense > 0 OR
                LossHabitual > 0)
        JOIN FineNotification FN ON FN.FineId = F.Id
        LEFT JOIN FineDispute FDI ON FDI.FineId = F.Id
        LEFT JOIN FinePrefectCommunication FPC ON FPC.FineId = F.Id
        LEFT JOIN FineDocumentation FD ON FD.FineId = F.Id AND FD.DocumentationTypeId=18
        LEFT JOIN Result R ON R.Id = FPC.ResultId',
    'where' => '
        F.StatusTypeId > 10 AND 
        F.ProtocolId > 0 AND 
        FC.CommunicationDate IS NOT NULL AND
        T.Genre IN("F","M") AND
        FT.TrespasserTypeId IN(1,3,11,15,16)', //DA SISTEMARE
    
    'union' => array(
        'ArticoliAdd' => array(
            'aliases' => array(
                "FineId" => "F.Id",
                "SendDate" => "FPC.SendDate",
                "PrefectNotificationDate" => "FPC.NotificationDate",
                "ReceiptNumber" => "FPC.ReceiptNumber",
                "LetterNumber" => "FPC.LetterNumber",
                "SendType" => "FPC.SendType",
                "RegDate" => "FPC.RegDate",
                "RegTime" => "FPC.RegTime",
                "UserId" => "FPC.UserId",
                
                "SuspensionLicense" => "AT.SuspensionLicense",
                "Habitual" => "AT.Habitual",
                "RevisionLicense" => "AT.RevisionLicense",
                "RevisionHabitual" => "AT.RevisionHabitual",
                "RevocationLicense" => "AT.RevocationLicense",
                "RevocationHabitual" => "AT.RevocationHabitual",
                "LossLicense" => "AT.LossLicense",
                "LossHabitual" => "AT.LossHabitual",
                
                "CityId" => "F.CityId",
                "FineTypeId" => "F.FineTypeId",
                "VehiclePlate" => "F.VehiclePlate",
                "VehicleTypeId" => "F.VehicleTypeId",
                "ProtocolId" => "F.ProtocolId",
                "ProtocolYear" => "F.ProtocolYear",
                "FineDate" => "F.FineDate",
                "FineTime" => "F.FineTime",
                "Code" => "F.Code",
                
                "Article" => "A.Article",
                "Paragraph" => "A.Paragraph",
                "Letter" => "A.Letter",
                
                "ResultTitle" => "R.Title",
                "FineDocumentationId" => "FD.Id",
                "ArticleId" => "FAA.ArticleId",
                "TrespasserId" => "T.Id",
                "DisputeId" => "FDI.DisputeId",
                "NotificationDate" => "FN.NotificationDate",
                "FineNotificationType" => "FT.FineNotificationType",
                
                "LicenseDate" => "T.LicenseDate"
            ),
            'from' => 'Fine F
                JOIN FineTrespasser FT ON FT.FineId = F.Id
                JOIN Trespasser T ON FT.TrespasserId = T.Id
                JOIN FineCommunication FC ON FC.FineId = F.Id AND FC.TrespasserId = T.Id
                JOIN FineAdditionalArticle FAA ON FAA.FineId = F.Id
                JOIN Article A ON FAA.ArticleId = A.Id
                JOIN ArticleTariff AT ON AT.ArticleId = A.Id
                    AND AT.Year = F.ProtocolYear
                    AND UseAdditionalSanction IN("fissa","variabile")
                    AND (
                        SuspensionLicense > 0 OR 
                        Habitual > 0 OR
                        RevisionLicense > 0 OR
                        RevisionHabitual > 0 OR
                        RevocationLicense > 0 OR
                        RevocationHabitual > 0 OR
                        LossLicense > 0 OR
                        LossHabitual > 0)
                JOIN FineNotification FN ON FN.FineId = F.Id
                LEFT JOIN FineDispute FDI ON FDI.FineId = F.Id
                LEFT JOIN FinePrefectCommunication FPC ON FPC.FineId = F.Id
                LEFT JOIN FineDocumentation FD ON FD.FineId = F.Id AND FD.DocumentationTypeId=18
                LEFT JOIN Result R ON R.Id = FPC.ResultId',
            'where' => '
                F.StatusTypeId > 10 AND 
                F.ProtocolId > 0 AND 
                FC.CommunicationDate IS NOT NULL AND
                T.Genre IN("F","M") AND
                FT.TrespasserTypeId IN(1,3,11,15,16)' //DA SISTEMARE
        )
    )
);

//Per sostituire la V_PaymentProcedure nella classe cls_pagamenti
const V_PAYMENTPROCEDURE_WITHOUT_NOTIFICATION_CONSTRAINT = array(
    'aliases' => array(
        'Id' => 'F.Id',
        'Code' => 'F.Code',
        'FineCountry' => 'F.CountryId',
        'CityId' => 'F.CityId',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'VehiclePlate' => 'F.VehiclePlate',
        'VehicleMass' => 'F.VehicleMass',
        'ReminderDate' => 'F.ReminderDate',
        'FineAddress' => 'F.Address',
        'KindSendDate' => 'F.KindSendDate',
        'PrintDate' => 'FR.PrintDate',
        'NotificationDate' => 'FN.NotificationDate',
        'ReminderAdditionalFeeProcedure' => 'FN.ReminderAdditionalFeeProcedure',
        'Fee' => 'FA.Fee',
        'MaxFee' => 'FA.MaxFee',
        'PrefectureFee' => 'FA.PrefectureFee',
        'ReducedPayment' => 'ART.ReducedPayment',
        'PrefectureFixed' => 'ART.PrefectureFixed',
        'AdditionalMass' => 'ART.AdditionalMass',
        'AdditionalNight' => 'ART.AdditionalNight',
        'AdditionalFee0' => '((((FH.CustomerFee + FH.NotificationFee) + FH.ResearchFee) + FH.NotifierFee) + FH.OtherFee)',
        'AdditionalFee1' => '((((((FH.CustomerFee + FH.NotificationFee) + FH.ResearchFee) + FH.CanFee) + FH.CadFee) + FH.NotifierFee) + FH.OtherFee)',
        'SendDate' => 'FH.SendDate',
        'DeliveryDate' => 'FH.DeliveryDate',
        'ResultId' => 'FH.ResultId',
        'TrespasserId' => 'FH.TrespasserId',
        'TrespasserTypeId' => 'FH.TrespasserTypeId',
        'NotificationTypeId' => 'FH.NotificationTypeId',
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
        'ZoneId' => 'T.ZoneId',
        'TrespasserCountry' => 'T.CountryId',
        'TrespasserAddress' => 'T.Address',
        'TrespasserCity' => 'T.City',
        'ZIP' => 'T.ZIP',
        'Province' => 'T.Province',
        'BornCountry' => 'T.BornCountryId',
        'BornPlace' => 'T.BornPlace',
        'BornDate' => 'T.BornDate',
        'StatusRateId' => 'PR.StatusRateId',
    ),
    'from' =>
    '(
    (
      (
        (
          (
            (
              (
                traffic_law.Fine F 
                left join traffic_law.FineNotification FN on(
                  (F.Id = FN.FineId)
                )
              ) 
              join traffic_law.FineArticle FA on(
                (F.Id = FA.FineId)
              )
            ) 
            join traffic_law.ArticleTariff ART on(
              (
                (
                  FA.ArticleId = ART.ArticleId
                ) 
                and (
                  ART.Year = F.ProtocolYear
                )
              )
            )
          ) 
          join traffic_law.FineHistory FH on(
            (FA.FineId = FH.FineId)
          )
        ) 
        join traffic_law.Trespasser T on(
          (FH.TrespasserId = T.Id)
        )
      ) 
      left join traffic_law.FineReminder FR on(
        (
          (F.Id = FR.FineId) 
          and (
            F.ReminderDate = FR.PrintDate
          )
        )
      )
    ) 
    left join traffic_law.PaymentRate PR on(
      (
        (F.Id = PR.FineId) 
        and (PR.StatusRateId <> 1)
      )
    )
    left join traffic_law.TMP_PaymentProcedure TMPPP on(
      (
        (F.Id = TMPPP.FineId)
      )
    )
  )',
    'where' =>
    '(
      (FH.NotificationTypeId = 6)
      )'
);

const MGMT_INSTALLMENTS = array(
    'aliases' => array(
        "Id" => "PR.Id",
        "StatusRateId" => "PR.StatusRateId",
        "FineId" => "PR.FineId",
        "ReferenceId" => "PR.ReferenceId",
        "TrespasserId" => "PR.TrespasserId",
        "RateName" => "PR.RateName",
        "DocumentTypeId" => "PR.DocumentTypeId",
        "Position" => "PR.Position",
        "Note" => "PR.Note",
        "RegDate" => "PR.RegDate",
        "RegTime" => "PR.RegTime",
        "UserId" => "PR.UserId",
        "RequestStatusId" => "PR.RequestStatusId",
        "ResponseStatusId" => "PR.ResponseStatusId",
        "BillStatusId" => "PR.BillStatusId",
        "RequestOutcome" => "PR.RequestOutcome",
        "InstalmentNumber" => "PR.InstalmentNumber",
        "InstalmentAmount" => "PR.InstalmentAmount",
        "ResponseReason" => "PR.ResponseReason",
        "InstallmentMethod" => "PR.InstallmentMethod",
        "Income" => "PR.Income",
        "FamilyMembers" => "PR.FamilyMembers",
        "InterestsPercentual" => "PR.InterestsPercentual",
        "RequestDate" => "PR.RequestDate",
        "StartDate" => "PR.StartDate",
        "ClosingDate" => "PR.ClosingDate",
        "SignedRequestDocumentId" => "PR.SignedRequestDocumentId",
        
        "ProtocolId" => "F.ProtocolId",
        "ProtocolYear" => "F.ProtocolYear",
        "CityId" => "F.CityId",
        
        "TrespasserFullName" => "CONCAT_WS(' ', T.CompanyName, T.Name, T.Surname)",
        "DocumentTypeTitle" => "DT.Description"
    ),
    'from' => "PaymentRate PR
        JOIN Fine F ON F.Id = PR.FineId
        LEFT JOIN Trespasser T ON T.Id = PR.TrespasserId
        LEFT JOIN Document_Type DT ON DT.Id = PR.DocumentTypeId",
    'where' => '1=1'
);

//Unificazione query registro cronologico
const PRN_REGISTRY = array(
    'aliases' => array(
        'FineId' => 'F.Id',
        'Code' => 'F.Code',
        'CityId' => 'F.CityId',
        'Address' => 'F.Address',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'StatusTypeId' => 'F.StatusTypeId',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'VehiclePlate' => 'F.VehiclePlate',
        'Locality' => 'F.Locality',
        'VehicleCountryId' => 'F.CountryId',
        'ExternalProtocol' => 'F.ExternalProtocol',
        'ExternalYear' => 'F.ExternalYear',
        'PreviousId' => 'F.PreviousId',
        'ReminderDate' => 'F.ReminderDate',
        'KindSendDate' => 'F.KindSendDate',
        'TrespasserTypeId' => 'FT.TrespasserTypeId',
        'Note' => 'FT.Note',
        'OwnerAdditionalFee' => 'FT.OwnerAdditionalFee',
        'CustomerAdditionalFee' => 'FT.CustomerAdditionalFee',
        'ReceiveDate' => 'FT.ReceiveDate',
        'FineNotificationType' => 'FT.FineNotificationType',
        'ArticleId' => 'FA.ArticleId',
        'Fee' => 'FA.Fee',
        'MaxFee' => 'FA.MaxFee',
        'ArticleNumber' => 'FA.ArticleNumber',
        'ViolationTypeId' => 'FA.ViolationTypeId',
        'FullArticle' => "CONCAT(A.Article,' ',A.Letter,' ',A.Paragraph)",
        'NotificationDate' => 'FN.NotificationDate',
        'Code' => 'T.Code',
        'Genre' => 'T.Genre',
        'ZIP' => 'T.ZIP',
        'TrespasserId' => 'T.Id',
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
        'TaxCode' => 'T.TaxCode',
        'VatCode' => 'T.VatCode',
        'City' => 'T.City',
        'CountryId' => 'T.CountryId',
        'ZoneId' => 'T.ZoneId',
        'CountryTitle' => 'C.Title',
        'VehicleTitle' => 'VT.TitleIta',
        'ResultTitle' => 'R.Title',
        'ReminderId' => 'FR.Id',
        'Amount' => 'FR.Amount',
        'TotalAmount' => 'FR.TotalAmount',
        'ReminderNotificationFee' => 'FR.NotificationFee',
        'TotalNotification' => 'FR.TotalNotification',
        'PrintDate' => 'FR.PrintDate',
        'FlowDate' => 'FR.FlowDate',
        'Amount' => 'FR.Amount',
        'TotalAmount' => 'FR.TotalAmount',
        'ReminderNotificationFee' => 'FR.NotificationFee',
        'TotalNotification' => 'FR.TotalNotification',
        'PrintDate' => 'FR.PrintDate',
        'FlowDate' => 'FR.FlowDate',
        'ArchiveDate' => 'FAR.ArchiveDate',
        'ArchiveNote' => 'FAR.Note',
        'PreviousArchiveNote' => 'FAR.PreviousNote',
        'NotificationDate' => 'FN.NotificationDate',
        'DocumentTypeId' => 'FLW.DocumentTypeId',
        'DocumentTypeId' => 'FLW.DocumentTypeId'
    ),
    'from' => '
        Fine F 
        JOIN FineArticle FA ON F.Id = FA.FineId 
        @JoinArticle
        @JoinFineHistory
        LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
        LEFT JOIN Trespasser T ON FT.TrespasserId=T.Id
        LEFT JOIN Country C ON T.CountryId = C.Id
        LEFT JOIN FineReminder FR ON F.Id = FR.FineId AND FR.FlowDate IS NOT NULL
        LEFT JOIN FineArchive FAR ON F.Id = FAR.FineId
        LEFT JOIN FineNotification FN ON F.Id=FN.FineId
        LEFT JOIN Flow FLW ON FH.FlowId = FLW.Id
        LEFT JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        LEFT JOIN Result R ON FN.ResultId=R.Id
        LEFT JOIN TMP_PaymentProcedure TPP ON F.Id = TPP.FineId
        LEFT JOIN TMP_ReminderAdditionalFeeProcedure TRA ON F.Id = TRA.FineId
        LEFT JOIN TMP_126BisProcedure T126 ON F.Id = T126.FineId
        LEFT JOIN TMP_PresentationDocumentProcedure TPD ON F.Id = TPD.FineId
        LEFT JOIN TMP_LicensePointProcedure TLP ON F.Id = TLP.FineId
        LEFT JOIN TMP_InjunctionProcedure TIP ON F.Id = TIP.FineId',
    'where' => '
        (FT.TrespasserTypeId=1 OR FT.TrespasserTypeId=11 OR FT.TrespasserTypeId=2 OR FT.TrespasserTypeId IS NULL)
        '
);

const FRM_REMINDER_LIST = array(
    'aliases' => array(
        'FineReminderId' => 'FR.Id',
        'FineId' => 'FR.FineId',
        'PaymentDays' => 'FR.PaymentDays',
        'PaymentDate' => 'FR.PaymentDate',
        'PrintDate' => 'FR.PrintDate',
        'DaysFromNotificationDate' => 'FR.DaysFromNotificationDate',
        'DelayDays' => 'FR.DelayDays',
        'Semester' => 'FR.Semester',
        'Fee' => 'FR.Fee',
        'FlowDate' => 'FR.FlowDate',
        'FlowNumber' => 'FR.FlowNumber',
        'MaxFee' => 'FR.MaxFee',
        'HalfMaxFee' => 'FR.HalfMaxFee',
        'TotalNotification' => 'FR.TotalNotification',
        'Amount' => 'FR.Amount',
        'TotalAmount' => 'FR.TotalAmount',
        'Percentual' => 'FR.Percentual',
        'PercentualAmount' => 'FR.PercentualAmount',
        'NotificationFee' => 'FR.NotificationFee',
        'SendDate' => 'FR.SendDate',
        
        'Code' => 'F.Code',
        'ProtocolId' => 'F.ProtocolId',
        'ProtocolYear' => 'F.ProtocolYear',
        'Locality' => 'F.Locality',
        'Address' => 'F.Address',
        'VehiclePlate' => 'F.VehiclePlate',
        'FineDate' => 'F.FineDate',
        'FineTime' => 'F.FineTime',
        'CityId' => 'F.CityId',
        'PagoPA1' => 'F.PagoPA1',
        'PagoPA2' => 'F.PagoPA2',
        'StatusTypeId' => 'F.StatusTypeId',
        'VehicleTypeId' => 'F.VehicleTypeId',
        
        'NotificationTypeId' => 'FH.NotificationTypeId',
        'TrespasserId' => 'FH.TrespasserId',
        'TrespasserTypeId' => 'FH.TrespasserTypeId',
        'DeliveryDate' => 'FH.DeliveryDate',
        
        'NotificationDate' => 'FN.NotificationDate',
        
        'ViolationTypeId' => 'FA.ViolationTypeId',
        'ArticleId' => 'FA.ArticleId',
        
        'ReducedPayment' => 'TAR.ReducedPayment',
        
        'Genre' => 'T.Genre',
        'CompanyName' => 'T.CompanyName',
        'Surname' => 'T.Surname',
        'Name' => 'T.Name',
        'TrespasserAddress' => 'T.Address',
        'ZIP' => 'T.ZIP',
        'City' => 'T.City',
        'Province' => 'T.Province',
        'TaxCode' => 'T.TaxCode',
        'ZoneId' => 'T.ZoneId',
        'LanguageId' => 'T.LanguageId',
        'BornPlace' => 'T.BornPlace',
        'BornDate' => 'T.BornDate',
        
        'BornCountry' => 'CTR.Title',
        'TrespasserTypeDesc' => 'TT.Description',
        'CityTitle' => 'C.Title',
        'VehicleType' => 'VT.TitleIta',
        
        'PaymentId' => 'FP.Id'
    ),
    'from' => 'FineReminder FR
        JOIN Fine F ON FR.FineId = F.Id
        JOIN FineNotification FN ON FR.FineId = FN.FineId
        JOIN Trespasser T ON FR.TrespasserId = T.Id
        JOIN FineHistory FH ON FR.FineId=FH.FineId AND FH.NotificationTypeId=6
        JOIN FineArticle FA ON FR.FineId=FA.FineId
        JOIN ArticleTariff TAR ON TAR.ArticleId=FA.ArticleId and TAR.Year = F.ProtocolYear
        JOIN TrespasserType TT ON FH.TrespasserTypeId = TT.Id
        JOIN sarida.City C on C.Id = F.Locality
        JOIN VehicleType VT ON F.VehicleTypeId = VT.Id
        LEFT JOIN Country CTR on (CTR.Id=T.BornCountryId)
        LEFT JOIN FineDispute FD ON F.Id = FD.FineId
        LEFT JOIN FinePayment FP ON FP.Id = (SELECT MIN(FP2.Id) FROM FinePayment FP2 WHERE FP2.FineId = F.Id)',
    'where' => 'F.ProtocolId > 0'
);

?>