<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
require(INC."/initialization.php");

$Data = json_decode(CheckValue('Data', 's'));
$a_Results = array();
$a_Discrepancies = array();
$ReportFile = false;

//Funzione per colorare le celle nel report dove si sono verificate discrepanze
function disprecancyCell($array, $name){
    return in_array($name, $array) ? ' bgcolor="yellow"' : '';
}

$rs->Start_Transaction();

if($Data){
    //Cicla gli oggetti contenenti i dati da aggiornare
    foreach($Data as $obj){
        //Si assicura che l'id del trasgressore non sia nullo
        if(!is_null($obj->id)){
            $a_Trespasser = array();
            $a_TrespasserHistory = array();
            $o_Result = new stdClass();
            $TrespasserId = $obj->id;
            
            //Controlla i dati da aggiornare
            if(!is_null($obj->borndate)){
                $a_Trespasser[] = array('field'=>'BornDate','selector'=>'value','type'=>'date','value'=>DateInDB($obj->borndate));
            }
            if(!is_null($obj->borncountry)){
                $a_Trespasser[] = array('field'=>'BornCountryId','selector'=>'value','type'=>'str','value'=>$obj->borncountry);
            }
            if(!is_null($obj->bornplace)){
                $a_Trespasser[] = array('field'=>'BornPlace','selector'=>'value','type'=>'str','value'=>$obj->bornplace);
            }
            
            //Entra se c'è almeno un dato da aggiornare
            if(!empty($a_Trespasser)){
                //Prepara i dati da inserire nello storico
                $rs_Trespasser = $rs->Select('Trespasser',"Id=".$TrespasserId);
                $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
                
                $AddressFH = isset($r_Trespasser['Address']) ? $r_Trespasser['Address'] : '';
                $CityFH = isset($r_Trespasser['City']) ? $r_Trespasser['City'] : '';
                $CountryIdFH = isset($r_Trespasser['CountryId']) ? $r_Trespasser['CountryId'] : '';
                $BornPlaceFH = isset($r_Trespasser['BornPlace']) ? $r_Trespasser['BornPlace'] : '';
                $ZoneIdFH = isset($r_Trespasser['ZoneId']) ? $r_Trespasser['ZoneId'] : -1;
                $LanguageIdFH = isset($r_Trespasser['LanguageId']) ? $r_Trespasser['LanguageId'] : -1;
                $a_TrespasserHistory = array(
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
                    array('field'=>'UserId','selector'=>'value','type'=>'str', 'value'=>$r_Trespasser['UserId']),
                    array('field'=>'VersionDate','selector'=>'value','type'=>'str', 'value'=>$r_Trespasser['VersionDate']),
                );
                
                //Inserisce i dati nello storico
                $rs->Insert('TrespasserHistory',$a_TrespasserHistory);
                
                $a_Trespasser[] = array('field'=>'UserId','selector'=>'value','type'=>'str','value'=>$_SESSION['username']);
                $a_Trespasser[] = array('field'=>'VersionDate','selector'=>'value','type'=>'str','value'=>date('Y-m-d'));
                
                //Aggiorna i dati del trasgressore
                $rs->Update('Trespasser', $a_Trespasser, "Id=$TrespasserId");
                
                $o_Result->id = $TrespasserId;
                
                //Verifica se l'oggetto corrente contiene discrepanze, se si inserisce l'id del trasgressore e le discrepanze trovate in un vettore
                //e colora la riga di giallo, altrimenti verde
                if(!empty($obj->discrepancy)){
                    $o_Result->status = 'warning';
                    $a_Discrepancies[$TrespasserId] = $obj->discrepancy;
                }
                else
                    $o_Result->status = 'success';
            }
            
            array_push($a_Results, $o_Result);
        }
    }
    
    //Verifica se il vettore delle discrepanze contiene qualcosa da processare, se si prepara i dati per creare un report
    if(!empty($a_Discrepancies)){
        $n_Row = 1;
        $rs_Trespassers = $rs->Select('Trespasser',"Id IN(".implode(',', array_keys($a_Discrepancies)).")");
        
        if(mysqli_num_rows($rs_Trespassers) > 0){
            $FileName = $_SESSION['cityid'].'_correggiDatiNascita_'.date("Y-m-d_H-i").'.xls';
            $str_Csv = '
                <table border="1">
                    <tbody>
                        <tr>
                            <th></th>
                            <th>Id</th>
                            <th>Trasgressore</th>
                            <th>Genere</th>
                            <th>Codice Fiscale</th>
                            <th>Data ins.</th>
                            <th>Data nascita</th>
                            <th>Nazione nascita</th>
                            <th>Città nascita</th>
                        </tr>';
            
            while($r_Trespassers = $rs->getArrayLine($rs_Trespassers)){
                $Id = $r_Trespassers['Id'];
                $str_Csv .= "
                        <tr>
                            <td>".$n_Row++."</td>
                            <td>{$Id}</td>
                            <td>".StringOutDB($r_Trespassers['Name'].' '.$r_Trespassers['Surname'])."</td>
                            <td".disprecancyCell($a_Discrepancies[$Id], 'genre').">{$r_Trespassers['Genre']}</td>
                            <td>{$r_Trespassers['TaxCode']}</td>
                            <td>".DateOutDB($r_Trespassers['DataSourceDate'])."</td>
                            <td".disprecancyCell($a_Discrepancies[$Id], 'borndate').">".DateOutDB($r_Trespassers['BornDate'])."</td>
                            <td".disprecancyCell($a_Discrepancies[$Id], 'borncountry').">{$r_Trespassers['BornCountryId']}</td>
                            <td".disprecancyCell($a_Discrepancies[$Id], 'bornplace').">".StringOutDB($r_Trespassers['BornPlace'])."</td>
                        </tr>";
            }
            $str_Csv .= "</tbody></table>";
            
            if (! is_dir(ROOT . "/doc/print")) mkdir(ROOT . "/doc/print", 0777);
            if (! is_dir(ROOT . "/doc/print/correggiDatiNascita")) mkdir(ROOT . "/doc/print/correggiDatiNascita", 0777);
            
            //Scrive il report in formato xls sul file system
            file_put_contents(ROOT . "/doc/print/correggiDatiNascita/". $FileName, "\xEF\xBB\xBF".$str_Csv);
            
            $ReportFile = $MainPath . "/doc/print/correggiDatiNascita/". $FileName;
        }
    }
    
    echo json_encode(
        array(
            "Result" => $a_Results,
            "ReportFile" => $ReportFile
        )
    );
}

$rs->End_Transaction();
