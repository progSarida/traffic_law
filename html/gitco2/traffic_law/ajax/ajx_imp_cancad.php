<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$rs = new CLS_DB();
$rs->SetCharset("utf8");

$Action = CheckValue("Action", "s");
$Search_CityId = CheckValue("Search_CityId", "s");
$Seach_LetterNumber = CheckValue("Search_LetterNumber", "n");
$Search_Trespasser = CheckValue("Search_Trespasser", "s");
$Search_ProtocolYear = CheckValue("Search_ProtocolYear", "s");

$filesFolder = "public/DA_IMPORTARE/CAN_CAD";

if($Action == "search"){
    $a_Results = array();
    $str_Where = "1=1";
    
    if($Seach_LetterNumber > 0){
        $str_Where .= " AND FN.LetterNumber = $Seach_LetterNumber";
    }
    if($Search_Trespasser != ""){
        $str_Where .= " AND CONCAT_WS(' ',T.CompanyName,T.Surname,T.Name) like '%{$Search_Trespasser}%'";
    }
    if($Search_CityId != ""){
        $str_Where .= " AND F.CityId = '$Search_CityId'";
    }
    if($Search_ProtocolYear != ""){
        $str_Where .= " AND F.ProtocolYear = $Search_ProtocolYear";
    }
    
    if(!empty($Search_CityId)){
        
        //A seguito del Bug2846 modifico la query eliminando la stretta necessità di avere una notifica
        $a_Results = $rs->getResults($rs->SelectQuery("
        SELECT
        F.Id as FineId,
        NULLIF(GROUP_CONCAT(DISTINCT CONCAT_WS(' ', NULLIF(TRIM(T.CompanyName),''), NULLIF(TRIM(T.Surname),''), NULLIF(TRIM(T.Name),'')) SEPARATOR ' | '), ' | ') TrespasserName,
        FN.LetterNumber,
        F.ProtocolId,
        F.ProtocolYear
        FROM Fine F 
        LEFT JOIN FineNotification FN ON F.Id = FN.FineId
        LEFT JOIN FineTrespasser FT ON F.Id = FT.FineId
        LEFT JOIN FineDocumentation FD ON F.Id = FD.FineId AND FD.DocumentationTypeId = 12
        JOIN Trespasser T ON FT.TrespasserId = T.Id
        WHERE FD.Id IS NULL AND $str_Where GROUP BY F.Id ORDER BY F.ProtocolId DESC, F.ProtocolYear DESC"), "object");
        
        echo json_encode(
            array(
                "Result" => $a_Results,
            )
        );
    }
} else if($Action == "assign"){
    $FineId = CheckValue("FineId", "s");
    $DocumentName = CheckValue("DocumentName", "s");
    trigger_error("***FineId: ".$FineId." Document name: ".$DocumentName);
    $message = "";
    $b_success = false;
    
    if(file_exists(ARCHIVIO."/$filesFolder/$DocumentName")){
        $Fine = $rs->getArrayLine($rs->Select("Fine", "Id=$FineId"));
        
        if($Fine){
            $destinationFolder = $Fine['CountryId'] == 'Z000' ? NATIONAL_FINE : FOREIGN_FINE;
            
            if (!is_dir("$destinationFolder/{$Fine['CityId']}/$FineId")) {
                mkdir("$destinationFolder/{$Fine['CityId']}/$FineId", 0770);
                chmod("$destinationFolder/{$Fine['CityId']}/$FineId", 0770);
            }
            
            if(copy(ARCHIVIO."/$filesFolder/$DocumentName", "$destinationFolder/{$Fine['CityId']}/$FineId/$DocumentName")){
                $a_FineDocumentation = array(
                    array('field'=>'FineId','selector'=>'value','type'=>'int','value'=>$FineId, 'settype'=>'int'),
                    array('field'=>'DocumentationTypeId','selector'=>'value','type'=>'int','value'=>12,'settype'=>'int'),
                    array('field'=>'Documentation','selector'=>'value','type'=>'str','value'=>$DocumentName),
                    array('field'=>'Note','selector'=>'value','type'=>'str','value'=>null),
                    array('field'=>'VersionDate','selector'=>'value','type'=>'str','value'=>date("Y-m-d H:i:s")),
                    array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username'])
                );
                
                $rs->Insert("FineDocumentation", $a_FineDocumentation);
                
                if(unlink(ARCHIVIO."/$filesFolder/$DocumentName")){
                    $b_success = true;
                } else $message = "Errore nell'eliminazione del file dalla cartella";
            } else $message = "Errore nella copia del file.";
        } else $message = "Impossibile trovare verbale con Id: $FineId";
    } else $message = "Il file ".ARCHIVIO."/$filesFolder/$DocumentName non è stato trovato.";
    
    echo json_encode(
        array(
            "Success" => $b_success,
            "Message" => $message
        )
    );
}