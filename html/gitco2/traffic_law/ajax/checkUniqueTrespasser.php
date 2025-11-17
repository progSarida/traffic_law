<?php
require_once("../_path.php");
require_once(INC . "/parameter.php");
require_once(CLS . "/cls_db.php");
include_once(INC."/function.php");
$rs = new CLS_DB();

$Typology = strtoupper($_REQUEST['Typology']);
$TrespasserId = (isset($_REQUEST['TrespasserId'])) ? $_REQUEST['TrespasserId'] : "";
$TrespasserWhere = ($TrespasserId != "") ? "AND Id<>$TrespasserId" : "";

if ($Typology == "D"){
    //CONTROLLO DITTA
    $trovataDitta = false;
    
    $VatCode = strtoupper($_REQUEST['VatCode']);
    $CompanyName = strtoupper($_REQUEST['CompanyName']);
    $Name = strtoupper($_REQUEST['Name']);
    $Surname = strtoupper($_REQUEST['Surname']);
    $VatCodeText = "";
    $NominativeText = "";
    
    $CompanyName_sql = mysqli_real_escape_string($rs->conn, $CompanyName);
    $Name_sql = mysqli_real_escape_string($rs->conn, $Name);
    $Surname_sql = mysqli_real_escape_string($rs->conn, $Surname);
    
    if ($VatCode != ""){
        $str_Where = " AND VatCode ='$VatCode'";
        
        //CONTROLLO CON P.IVA
        // echo "SELECT * FROM Trespasser WHERE Genre='D' $TrespasserWhere AND CustomerId='".$_SESSION['cityid']."'".$str_Where;
        $response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE Genre='D' $TrespasserWhere AND CustomerId='".$_SESSION['cityid']."'".$str_Where);
        $row = mysqli_num_rows($response);
        $result = mysqli_fetch_array($response);
        if ($row > 0) {
            if ($result['VatCode'] != "" && $result['VatCode'] == $VatCode) {
                $VatCodeText = " con P.IVA: ".$result['VatCode'];
                echo json_encode(
                    array(
                        "Exists" => "Exists",
                        "Message" => 'Esiste già una ditta '.StringOutDB($result['CompanyName']).$VatCodeText.' con ID Utente: '.$result['Code'],
                        "TrespasserId" => $result['Id'],
                    )
                    );
                $trovataDitta = true;
            }
        }
        
        
        if(!$trovataDitta)
        {
            //CONTROLLO CON REGIONE SOCIALE, P.IVA E NOME/COGNOME (SE PRESENTE)
            $str_Where = " AND VatCode ='$VatCode'";
            $str_Where.=" AND CompanyName='$CompanyName_sql'";
            if ($Name !="" && $Surname !=""){
                $str_Where.=" AND Name='$Name_sql' AND Surname='$Surname_sql'";
            }
            // echo "SELECT * FROM Trespasser WHERE Genre='D' $TrespasserWhere AND CustomerId='".$_SESSION['cityid']."'".$str_Where;
            $response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE Genre='D' $TrespasserWhere AND CustomerId='".$_SESSION['cityid']."'".$str_Where);
            $row = mysqli_num_rows($response);
            $result = mysqli_fetch_array($response);
            if ($row > 0) {
                if ($result['VatCode'] != "" && $result['VatCode'] == $VatCode) {
                    $VatCodeText = " con P.IVA: ".$result['VatCode'];
                    if ($result['Name'] != "" && $result['Surname'] != "") $NominativeText = " di ".$result['Surname']." ".$result['Name'];
                    echo json_encode(
                        array(
                            "Exists" => "Exists",
                            "Message" => 'Esiste già una ditta '.StringOutDB($result['CompanyName']).$NominativeText.$VatCodeText.' con ID Utente: '.$result['Code'],
                            "TrespasserId" => $result['Id'],
                        )
                        );
                    $trovataDitta = true;
                }
            }             
        }
        
        if (!$trovataDitta) {
            
            $str_Where = " AND VatCode ='$VatCode'";
            $str_Where.=" AND CompanyName='$CompanyName_sql'";
            
            //CONTROLLO CON REGIONE SOCIALE E P.IVA
            //echo "SELECT * FROM Trespasser WHERE Genre='D' $TrespasserWhere AND CustomerId='".$_SESSION['cityid']."'".$str_Where;
            $response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE Genre='D' $TrespasserWhere AND CustomerId='".$_SESSION['cityid']."'".$str_Where);
            $row = mysqli_num_rows($response);
            $result = mysqli_fetch_array($response);
            if ($row > 0) {
                if ($result['VatCode'] != "" && $result['VatCode'] == $VatCode) {
                    $VatCodeText = " con P.IVA: ".$result['VatCode'];
                    
                    echo json_encode(
                        array(
                            "Exists" => "Exists",
                            "Message" => 'Esiste già una ditta '.StringOutDB($result['CompanyName']).$VatCodeText.' con ID Utente: '.$result['Code'],
                            "TrespasserId" => $result['Id'],
                        )
                        );
                    $trovataDitta = true;
                }
            }       
            
            
        }
        
    }
    
    if (!$trovataDitta) {
        $str_Where = " AND CompanyName='$CompanyName_sql'";
        if ($Name !="" && $Surname !=""){
            $str_Where.=" AND Name='$Name_sql' AND Surname='$Surname_sql'";
        }
        
        //CONTROLLO CON REGIONE SOCIALE E NOME/COGNOME (SE PRESENTE)
        $response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE Genre='D' $TrespasserWhere AND CustomerId='".$_SESSION['cityid']."'".$str_Where);
        $row = mysqli_num_rows($response);
        $result = mysqli_fetch_array($response);
        if ($row > 0) {
            if ($result['Name'] != "" && $result['Surname'] != "") $NominativeText = " di ".$result['Surname']." ".$result['Name'];
            echo json_encode(
                array(
                    "Exists" => "Exists",
                    "Message" => 'Esiste già una ditta '.StringOutDB($result['CompanyName']).$NominativeText.' con ID Utente: '.$result['Code'],
                    "TrespasserId" => $result['Id'],
                )
                );
            $trovataDitta = true;
        }
    }
    
    if (!$trovataDitta){
        $str_Where = " AND CompanyName='$CompanyName_sql'";
        
        //CONTROLLO CON REGIONE SOCIALE
        $response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE Genre='D' $TrespasserWhere AND CustomerId='".$_SESSION['cityid']."'".$str_Where);
        $row = mysqli_num_rows($response);
        $result = mysqli_fetch_array($response);
        if ($row > 0) {
            echo json_encode(
                array(
                    "Exists" => "Exists",
                    "Message" => 'Esiste già una ditta '.StringOutDB($result['CompanyName']).' con ID Utente: '.$result['Code'],
                    "TrespasserId" => $result['Id'],
                )
                );
            $trovataDitta = true;
        }
    }

    if (!$trovataDitta){
        echo json_encode(
            array(
                "Exists" => "Not Exists",
                "Message" => "",
            )
        );
    }
    
} else if ($Typology == "P") {
    //CONTROLLO PERSONA
    $trovataPersona = false;
    
    $TaxCode = isset($_REQUEST['TaxCode']) ? strtoupper($_REQUEST['TaxCode']) : "";
    $Name = strtoupper($_REQUEST['Name']);
    $Surname = strtoupper($_REQUEST['Surname']);
    $Genre = strtoupper($_REQUEST['Genre']);
    $BornDate = isset($_REQUEST['BornDate']) ? DateInDB($_REQUEST['BornDate']) : "";
    $BornCountry = isset($_REQUEST['BornCountry']) ? strtoupper($_REQUEST['BornCountry']) : "";
    $BornCity = isset($_REQUEST['BornCity']) ? strtoupper($_REQUEST['BornCity']) : "";
    $TaxCodeText = "";
    
    //echo $BornDate."<br>";
    //echo $BornCountry."<br>";
    //echo $BornCity."<br>";
    $Name_sql = mysqli_real_escape_string($rs->conn, $Name);
    $Surname_sql = mysqli_real_escape_string($rs->conn, $Surname);
    $BornCity_sql = mysqli_real_escape_string($rs->conn, $BornCity);
    
    if ($TaxCode != ""){
        $str_Where = "AND Name='$Name_sql' AND Surname='$Surname_sql' AND Genre='$Genre'";
        if ($BornDate !=""){
            $str_Where.=" AND BornDate='$BornDate'";
        }
        if ($BornCountry !=""){
            $str_Where.=" AND BornCountryId='$BornCountry'";
        }
        if ($BornCity !=""){
            $str_Where.=" AND BornPlace='$BornCity_sql'";
        }
        if ($TaxCode != "") {
            $str_Where.=" AND TaxCode='$TaxCode'";
        }
        
        //CONTROLLO CON C.F E DATI
        //echo "SELECT * FROM Trespasser WHERE CustomerId='".$_SESSION['cityid']."' $TrespasserWhere $str_Where  <br>";
        $response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE CustomerId='".$_SESSION['cityid']."' $TrespasserWhere $str_Where");
        $row = mysqli_num_rows($response);
        $result = mysqli_fetch_array($response);
        if ($row > 0) {
            //esiste già lo stesso nominativo (fondere in inserimento)
            if ($result['TaxCode'] != "" && $result['TaxCode'] == $TaxCode) {
                $TaxCodeText = " con C.F: ".$result['TaxCode'];
                echo json_encode(
                    array(
                        "Exists" => "Exists",
                        "Message" => 'Esiste già una persona con nominativo 1 '.StringOutDB($result['Surname']).' '.StringOutDB($result['Name']).' di genere: '.$result['Genre'].$TaxCodeText.' e ID Utente: '.$result['Code'],
                        "TrespasserId" => $result['Id'],
                    )
                 );
                $trovataPersona = true;
            }           
        }   
                       
        if (!$trovataPersona) {
            //CONTROLLO CON C.F
            $str_Where = " AND TaxCode='$TaxCode'";
            $response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE CustomerId='".$_SESSION['cityid']."' $TrespasserWhere $str_Where");
            //echo "SELECT * FROM Trespasser WHERE CustomerId='".$_SESSION['cityid']."' $TrespasserWhere $str_Where <br>";
            $row = mysqli_num_rows($response);
            $result = mysqli_fetch_array($response);
            if ($row > 0) {
                if ($result['TaxCode'] != "" && $result['TaxCode'] == $TaxCode) {
                    $TaxCodeText = " con C.F: ".$result['TaxCode'];
                    echo json_encode(
                        array(
                            "Exists" => "Exists",
                            "Message" => 'Esiste già una persona con nominativo 2 '.StringOutDB($result['Surname']).' '.StringOutDB($result['Name']).' di genere: '.$result['Genre'].$TaxCodeText.' e ID Utente: '.$result['Code'],
                            "TrespasserId" => $result['Id'],
                        )
                    );
                    $trovataPersona = true;
                }
            }
        }
         
 /*       
        //CONTROLLO CON DATI
        $str_Where = "AND Name='$Name_sql' AND Surname='$Surname_sql' AND Genre='$Genre'";
        if ($BornDate !=""){
            $str_Where." AND BornDate='$BornDate'";
        }
        if ($BornCountry !=""){
            $str_Where." AND BornCountry='$BornCountry'";
        }
        if ($BornCity !=""){
            $str_Where." AND BornPlace='$BornCity'";
        }
                $response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE CustomerId='".$_SESSION['cityid']."' $TrespasserWhere $str_Where");
                $row = mysqli_num_rows($response);
                $result = mysqli_fetch_array($response);
                if ($row > 0) {
                    echo json_encode(
                        array(
                            "Exists" => "Exists",
                            "Message" => 'Esiste già una persona con nominativo '.StringOutDB($result['Surname']).' '.StringOutDB($result['Name']).' di genere: '.$result['Genre'].' e ID Utente: '.$result['Code'].' con gli stessi dati anagrafici.',
                            "TrespasserId" => $result['Id'],
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
            }
            
        }
  */
       
        
        
    }
    
    
    if (!$trovataPersona) {
        //Se non c'è CF o non ho trovato nulla con quello cerco solo per dati
        //CONTROLLO CON DATI
        $str_Where = "AND Name='$Name_sql' AND Surname='$Surname_sql' AND Genre='$Genre'";
        if ($BornDate !=""){
            $str_Where.=" AND BornDate='$BornDate'";
        }
        if ($BornCountry !=""){
            $str_Where.=" AND BornCountryId='$BornCountry'";
        }
        if ($BornCity !=""){
            $str_Where.=" AND BornPlace='$BornCity_sql'";
        }
        //echo "SELECT * FROM Trespasser WHERE CustomerId='".$_SESSION['cityid']."' $TrespasserWhere $str_Where <br>";
        $response = $rs->SelectQuery("SELECT * FROM Trespasser WHERE CustomerId='".$_SESSION['cityid']."' $TrespasserWhere $str_Where");
        $row = mysqli_num_rows($response);
        $result = mysqli_fetch_array($response);
        if ($row > 0) {
            echo json_encode(
                array(
                    "Exists" => "Exists",
                    "Message" => 'Esiste già una persona con nominativo 3 '.StringOutDB($result['Surname']).' '.StringOutDB($result['Name']).' di genere: '.$result['Genre'].' e ID Utente: '.$result['Code'],
                    "TrespasserId" => $result['Id'],
                )
            );
            $trovataPersona = true;
        }
    }
    
    if (!$trovataPersona)
    {
        echo json_encode(
            array(
                "Exists" => "Not Exists",
                "Message" => "",
            )
        );
    }
    
    
} else {
    //NEL CASO DI NON IDENTIFICAZIONE DITTA/PERSONA
    echo json_encode(
        array(
            "Exists" => "Error",
        )
    );
}



