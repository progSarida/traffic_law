<?php
require_once("../_path.php");
require_once(INC . "/parameter.php");
require_once(CLS . "/cls_db.php");
include_once(INC."/function.php");
$rs = new CLS_DB();

$TrespasserId = (isset($_REQUEST['TrespasserId'])) ? $_REQUEST['TrespasserId'] : "";
$TrespasserWhere = ($TrespasserId != "") ? "AND Id<>$TrespasserId" : "";

$Name = strtoupper($_REQUEST['Name']);
$Surname = strtoupper($_REQUEST['Surname']);
$Genre = strtoupper($_REQUEST['Genre']);
$BornDate = isset($_REQUEST['BornDate']) ? DateInDB($_REQUEST['BornDate']) : "";
$BornCity = isset($_REQUEST['BornCity']) ? strtoupper($_REQUEST['BornCity']) : "";
$LicenseNumber = isset($_REQUEST['LicenseNumber']) ? $_REQUEST['LicenseNumber'] : "";

$Name_sql = mysqli_real_escape_string($rs->conn, $Name);
$Surname_sql = mysqli_real_escape_string($rs->conn, $Surname);

//CONTROLLO PATENTE
if($LicenseNumber !=""){
    $response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE LicenseNumber='".$LicenseNumber."' $TrespasserWhere");
    //Se trova un numero patente su db identico a quello inserito
    if (mysqli_num_rows($response) > 0){
        $result = mysqli_fetch_array($response);
        $str_Where = "AND Name='$Name_sql' AND Surname='$Surname_sql' AND Genre='$Genre'";
        if ($BornDate !=""){
            $str_Where.=" AND BornDate='$BornDate'";
        }
        if ($BornCity !=""){
            $str_Where.=" AND BornPlace='$BornCity'";
        }
        $response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE LicenseNumber='".$LicenseNumber."' $str_Where");
        //Se non trova risultati vuol dire che non si tratta dello stesso trasgressore
        if (mysqli_num_rows($response) == 0){
            echo json_encode(
                array(
                    "Exists" => "Exists",
                    "Message" => "Attenzione: Esiste già un trasgressore con patente n. ".$LicenseNumber."\nNominativo: ".StringOutDB($result['Surname']).' '.StringOutDB($result['Name'])."\nGenere: ".$result['Genre']."\nCodice trasgressore: ".$result['Code'],
                )
            );
        } else {
            echo json_encode(
                array(
                    "Exists" => "Not Exists",
                    "Message" => "",
                )
            );
        }
    } else {
        echo json_encode(
            array(
                "Exists" => "Not Exists",
                "Message" => "",
            )
        );
    }
}



