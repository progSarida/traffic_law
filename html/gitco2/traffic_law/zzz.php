<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
ini_set('max_execution_time', 0);



$rs_Trespasser = $rs->SelectQuery("SELECT Id FROM Trespasser WHERE CustomerId IS NULL AND Id IN (SELECT TrespasserId FROM FineTrespasser) ORDER By Id");
$n_Id = 0;
while ($r_Trespasser = mysqli_fetch_array($rs_Trespasser)) {
    if ($n_Id != $r_Trespasser['Id']) {

        $n_Id = $r_Trespasser['Id'];
        $rs_City = $rs->SelectQuery("SELECT DISTINCT CityId FROM Fine WHERE Id IN (SELECT FineId FROM FineTrespasser WHERE TrespasserId=" . $n_Id . ")");


        /*
                INSERT INTO lettori_mp3
                SELECT * FROM lettori_mp3 WHERE id = 123;
        */


        $n_Count = 1;
        while ($r_City = mysqli_fetch_array($rs_City)) {

            if ($n_Count == 1) {
                echo $n_Id . " " .$r_City['CityId']  ."<br />";

                $aUpdate = array(
                    array('field' => 'CustomerId', 'selector' => 'value', 'type' => 'str', 'value' => $r_City['CityId'], 'settype' => 'str'),
                );
                $rs->Update('Trespasser', $aUpdate, "Id=" . $n_Id);



            } else {


                $rs->SelectQuery("
                    INSERT INTO 
                    Trespasser (
                    CustomerId,
                    Code,
                    Genre,
                    CompanyName,
                    Surname,
                    Name,
                    Address,
                    ZIP,
                    City,
                    CityId,
                    Province,
                    CountryId,
                    BornPlace,
                    BornDate,
                    TaxCode,
                    LicenseNumber,
                    DocumentTypeId,
                    DocumentCountryId,
                    LicenseDate,
                    LicenseCategory,
                    LicenseOffice,
                    Phone,
                    Mail,
                    PEC,
                    ZoneId,
                    LanguageId,
                    DataSourceId,
                    DataSourceDate,
                    IrideCode,
                    DeathDate)
                    SELECT 
                    '". $r_City['CityId'] ."',
                    Code,
                    Genre,
                    CompanyName,
                    Surname,
                    Name,
                    Address,
                    ZIP,
                    City,
                    CityId,
                    Province,
                    CountryId,
                    BornPlace,
                    BornDate,
                    TaxCode,
                    LicenseNumber,
                    DocumentTypeId,
                    DocumentCountryId,
                    LicenseDate,
                    LicenseCategory,
                    LicenseOffice,
                    Phone,
                    Mail,
                    PEC,
                    ZoneId,
                    LanguageId,
                    DataSourceId,
                    DataSourceDate,
                    IrideCode,
                    DeathDate
                    FROM Trespasser
                    WHERE 
                    Id=" . $n_Id


                  );

                $n_TrespasserId = $rs->LastId();

                echo "------------" . $n_Id . " " .$r_City['CityId']  ." ".$n_TrespasserId."<br />";

                $rs->SelectQuery("UPDATE FineTrespasser SET TrespasserId=".$n_TrespasserId." WHERE TrespasserId=" . $n_Id. " AND FineId IN(SELECT Id FROM Fine WHERE CityId='".$r_City['CityId']."')");

            }
            $n_Count++;
        }

    }
}