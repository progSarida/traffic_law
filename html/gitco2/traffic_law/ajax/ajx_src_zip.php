<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");


if($_POST) {


    $CityId = CheckValue('CityId', 's');
    $Address = CheckValue('Address', 's');
    $CountryId = CheckValue('CountryId', 's');
    $StreetNumber = CheckValue('StreetNumber', 's');
    $Type = CheckValue('Type', 's');
    
    $StreetValues = explode("/",$StreetNumber);
    $number = (int)$StreetValues[0];
    $letter = isset($StreetValues[1]) ? $StreetValues[1] : "";
    $numberType = ($number % 2 == 0) ? 2 : 1;

    $str_ZIP    = "";
    $str_CSS    = "";
    $LandTitle  = "";
    $LandId     = "";

    $rs= new CLS_DB();


    if($CountryId=="Z000"){
        $a_CityId = array();

        $rs_ZIPCity = $rs->SelectQuery("SELECT DISTINCT CityId FROM sarida.ZIPCity");

        while ($r_ZIPCity = mysqli_fetch_array($rs_ZIPCity)) {
            $a_CityId[] = $r_ZIPCity['CityId'];
        }

        if (in_array($CityId, $a_CityId)) {

            $a_FullStreet = explode(" ", $Address);

            $str_Where = "";
            $str_WhereAddres = "";
            $str_WhereLetter = "";
            
            if ($StreetNumber !=""){
                if ($letter !="") $str_WhereLetter = " AND '$letter'>=FromNumberLetter AND '$letter'<=ToNumberLetter";
                $str_WhereAddres.= " AND (za.ZIP is null OR (za.ZIP is not null and ".$number.">=FromNumber AND ".$number."<=ToNumber AND GroupTypeId=".$numberType . $str_WhereLetter."))";
            }
                
                

            for ($i = 1; $i < count($a_FullStreet); $i++) {
                $str_Where .= "AND StreetName LIKE '%" . str_replace(".", "", addslashes($a_FullStreet[$i])) . "%' ";
            }

            $rs_ZIP = $rs->SelectQuery("
                SELECT zc.ZIP as ZIP, za.ZIP as ZIP2
                FROM sarida.ZIPCity zc
                LEFT OUTER JOIN sarida.ZIPAddress za ON zc.Id = za.ZIPCityId 
                WHERE CityId='" . $CityId . "' " . $str_Where.$str_WhereAddres);

            $RowNumber = mysqli_num_rows($rs_ZIP);
            if ($RowNumber == 0) $str_CSS = "txt-danger";
            else if ($RowNumber == 1) {
                $r_ZIP = mysqli_fetch_array($rs_ZIP);
                $str_ZIP = ($r_ZIP['ZIP2'] != NULL) ? $r_ZIP['ZIP2'] : $r_ZIP['ZIP'];
                $str_CSS = "txt-success";
            } else {
                $r_ZIP = mysqli_fetch_array($rs_ZIP);
                $str_ZIP = $r_ZIP['ZIP'];

                $rs_ZIP = $rs->SelectQuery("
                    SELECT zc.ZIP as ZIP, za.ZIP as ZIP2
                    FROM sarida.ZIPCity zc
                    LEFT OUTER JOIN sarida.ZIPAddress za ON zc.Id = za.ZIPCityId 
                    WHERE CityId='" . $CityId . "' " . $str_Where.$str_WhereAddres. " AND StreetName LIKE '%" . addslashes($a_FullStreet[0]) . "%'");

                $RowNumber = mysqli_num_rows($rs_ZIP);
                if ($RowNumber == 1) {
                    $r_ZIP = mysqli_fetch_array($rs_ZIP);
                    $str_ZIP = ($r_ZIP['ZIP2'] != NULL) ? $r_ZIP['ZIP2'] : $r_ZIP['ZIP'];
                    $str_CSS = "txt-success";
                } else {
                    $str_CSS = "txt-warning";
                }
            }
        } else {

            $rs_ZIP = $rs->Select(MAIN_DB.".City","Id='" . $CityId . "'");
            $RowNumber = mysqli_num_rows($rs_ZIP);
            if ($RowNumber == 0) $str_CSS = "txt-danger";
            else {
                $r_ZIP = mysqli_fetch_array($rs_ZIP);
                $str_ZIP = $r_ZIP['ZIP'];
                $str_CSS = "txt-success";
            }
        }
    } else {
        if ($Type == "Foreign"){
            
            ///////
            
            $a_ForeignCityId = array();
            
            $rs_ForeignZIPCity = $rs->SelectQuery("SELECT DISTINCT ForeignCityId FROM sarida.ForeignZIPCity");
            $rs_Land = $rs->SelectQuery('SELECT FC.LandId, L.Title LandTitle FROM ForeignCity FC LEFT JOIN sarida.Land L ON FC.LandId=L.Id WHERE FC.Id='.$CityId);
            $r_Land = mysqli_fetch_array($rs_Land);
            $LandId = $r_Land["LandId"];
            $LandTitle = $r_Land["LandTitle"];
            
            while ($r_ForeignZIPCity = mysqli_fetch_array($rs_ForeignZIPCity)) {
                $a_ForeignCityId[] = $r_ForeignZIPCity['ForeignCityId'];
            }
            
            if (in_array($CityId, $a_ForeignCityId)) {
                
                $a_FullStreet = explode(" ", $Address);
                
                $str_Where = "";
                $str_WhereAddres = "";
                $str_WhereLetter = "";
                
                if ($StreetNumber !=""){
                    if ($letter !="") $str_WhereLetter = " AND '$letter'>=FromNumberLetter AND '$letter'<=ToNumberLetter";
                    $str_WhereAddres.= " AND (za.ZIP is null OR (za.ZIP is not null and ".$number.">=FromNumber AND ".$number."<=ToNumber AND GroupTypeId=".$numberType . $str_WhereLetter."))";
                }
                
                
                
                for ($i = 1; $i < count($a_FullStreet); $i++) {
                    $str_Where .= "AND StreetName LIKE '%" . str_replace(".", "", addslashes($a_FullStreet[$i])) . "%' ";
                }
                
//                 $rs_ZIP = $rs->SelectQuery("
//                 SELECT zc.ZIP as ZIP, za.ZIP as ZIP2, la.Id as LandId, la.Title as LandTitle
//                 FROM sarida.ForeignZIPCity zc
//                 LEFT OUTER JOIN sarida.ForeignZIPAddress za ON zc.Id = za.ZIPCityId
//                 INNER JOIN traffic_law.ForeignCity fc on zc.ForeignCityId = fc.Id
//                 LEFT OUTER JOIN sarida.Land la on fc.LandId = la.Id
//                 WHERE ForeignCityId='" . $CityId . "' " . $str_Where.$str_WhereAddres);
                
                $rs_ZIP = $rs->SelectQuery("
                SELECT zc.ZIP as ZIP, za.ZIP as ZIP2
                FROM sarida.ForeignZIPCity zc
                LEFT OUTER JOIN sarida.ForeignZIPAddress za ON zc.Id = za.ZIPCityId
                WHERE ForeignCityId='" . $CityId . "' " . $str_Where.$str_WhereAddres);
                
                $RowNumber = mysqli_num_rows($rs_ZIP);
                if ($RowNumber == 0) $str_CSS = "txt-danger";
                else if ($RowNumber == 1) {
                    $r_ZIP = mysqli_fetch_array($rs_ZIP);
                    $str_ZIP = ($r_ZIP['ZIP2'] != NULL) ? $r_ZIP['ZIP2'] : $r_ZIP['ZIP'];
                    $str_CSS = "txt-success";
                } else {
                    $r_ZIP = mysqli_fetch_array($rs_ZIP);
                    $str_ZIP = $r_ZIP['ZIP'];
                    
                    $rs_ZIP = $rs->SelectQuery("
                    SELECT zc.ZIP as ZIP, za.ZIP as ZIP2
                    FROM sarida.ForeignZIPCity zc
                    LEFT OUTER JOIN sarida.ForeignZIPAddress za ON zc.Id = za.ZIPCityId
                    WHERE ForeignCityId='" . $CityId . "' " . $str_Where.$str_WhereAddres. " AND StreetName LIKE '%" . addslashes($a_FullStreet[0]) . "%'");
                    
                    $RowNumber = mysqli_num_rows($rs_ZIP);
                    if ($RowNumber == 1) {
                        $r_ZIP = mysqli_fetch_array($rs_ZIP);
                        $str_ZIP = ($r_ZIP['ZIP2'] != NULL) ? $r_ZIP['ZIP2'] : $r_ZIP['ZIP'];
                        $str_CSS = "txt-success";
                    } else {
                        $str_CSS = "txt-warning";
                    }
                }
            } else {
                
                $rs_ZIP = $rs->SelectQuery('SELECT FC.*, L.Title LandTitle FROM ForeignCity FC LEFT JOIN sarida.Land L ON FC.LandId=L.Id WHERE FC.Id='.$CityId);
                if(mysqli_num_rows($rs_ZIP)>0){
                    
                    $r_ZIP = mysqli_fetch_array($rs_ZIP);
                    $str_ZIP = $r_ZIP['Zip'];
                    $LandId = $r_ZIP['LandId'];
                    $LandTitle = $r_ZIP['LandTitle'];
                    $str_CSS = "txt-success";
                    
                } else {
                    $str_CSS = "txt-danger";
                }
            }
            
            
            ////////
            

        } else {
            $rs_ZIP = $rs->SelectQuery("SELECT COUNT(*) TOT, ZIP FROM Trespasser WHERE CountryId='".$CountryId."' AND City LIKE '%". $CityId ."%' AND ZIP IS NOT NULL AND ZIP!='' GROUP BY ZIP ORDER BY `TOT` DESC");
            
            if(mysqli_num_rows($rs_ZIP)>0){
                
                $r_ZIP = mysqli_fetch_array($rs_ZIP);
                $str_ZIP = $r_ZIP['ZIP'];
                $str_CSS = "txt-success";
                
            } else {
                $str_CSS = "txt-danger";
            }
        }
    }






    echo json_encode(
        array(
            "ZIP" => $str_ZIP,
            "LandId" => $LandId,
            "LandTitle" => $LandTitle,
            "CSS" => $str_CSS,
            "Streetnum" => $number,
            "Type" => $numberType,
            "RS" => mysqli_num_rows($rs_ZIP),
        )
    );

}
