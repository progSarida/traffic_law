<?php
//Costanti
define("PERSON_PHYSICAL","1");
define("PERSON_BOTH","0");
define("PERSON_JURIDICAL","2");

define("ANOMALY_EXCLUDE","1");
define("ANOMALY_BOTH","0");
define("ANOMALY_ONLY","2");

define("TYPE_ANOMALY_ALL","0");             //Tutte
define("TYPE_ANOMALY_M_CITY","1");          //Assenza comune residenza
define("TYPE_ANOMALY_M_ZIP","2");           //Assenza CAP residenza
define("TYPE_ANOMALY_M_ADDRESS","3");       //Assenza indirizzo residenza
define("TYPE_ANOMALY_M_TAX","4");           //Assenza CF
define("TYPE_ANOMALY_M_BDATE","5");         //Assenza data di nascita
define("TYPE_ANOMALY_M_BPLACE","6");        //Assenza luogo di nascita
define("TYPE_ANOMALY_M_BCOUNTRY","7"); //Assenza stato nascita
define("TYPE_ANOMALY_M_RESPLACE","8");      //Assenza dati residenza
define("TYPE_ANOMALY_IH_NAMESURNAME","9");  //Incoerenza nome/cognome - CF
define("TYPE_ANOMALY_I_RESPLACE","10");      //Dati residenza incompleti
define("TYPE_ANOMALY_M_COMPANYPLACE","11"); //Assenza dati sede
define("TYPE_ANOMALY_I_COMPANYPLACE","12"); //Dati sede incompleti

define("TYPE_ANOMALY_LIST", serialize(array(
    TYPE_ANOMALY_ALL => "Tutte",
    TYPE_ANOMALY_M_CITY => "Assenza comune domicilio/residenza/sede",
    TYPE_ANOMALY_M_ZIP => "Assenza CAP domicilio/residenza/sede",
    TYPE_ANOMALY_M_ADDRESS => "Assenza via/strada domicilio/residenza/sede",
    TYPE_ANOMALY_M_TAX => "Assenza C.F./P.IVA",
    TYPE_ANOMALY_M_BDATE => "Assenza data di nascita",
    TYPE_ANOMALY_M_BPLACE => "Assenza comune di nascita",
    TYPE_ANOMALY_M_RESPLACE => "Assenza dati domicilio/residenza",
    TYPE_ANOMALY_IH_NAMESURNAME => "Incoerenza nome/cognome con C.F.",
    TYPE_ANOMALY_I_RESPLACE => "Dati domicilio/residenza non completi",
    TYPE_ANOMALY_M_COMPANYPLACE => "Assenza dati sede",
    TYPE_ANOMALY_I_COMPANYPLACE => "Dati sede non completi",
    TYPE_ANOMALY_M_BCOUNTRY => "Assenza stato di nascita",
)));

define("TYPE_ANOMALY_LIST_COMMON", serialize(array(
    TYPE_ANOMALY_ALL,
    TYPE_ANOMALY_M_TAX,
    TYPE_ANOMALY_M_CITY,
    TYPE_ANOMALY_M_ADDRESS,
    TYPE_ANOMALY_M_ZIP
)));
define("TYPE_ANOMALY_LIST_PERSON", serialize(array(
    TYPE_ANOMALY_M_BDATE,
    TYPE_ANOMALY_M_BPLACE,
    TYPE_ANOMALY_M_BCOUNTRY,
    TYPE_ANOMALY_M_RESPLACE,
    TYPE_ANOMALY_IH_NAMESURNAME,
    TYPE_ANOMALY_I_RESPLACE
)));
define("TYPE_ANOMALY_LIST_COMPANY", serialize(array(
    TYPE_ANOMALY_I_COMPANYPLACE,
    TYPE_ANOMALY_M_COMPANYPLACE,
)));

define("CODE_COHERENT", "coherent");
define("CODE_INCOHERENT", "incoherent");
define("SKIP_LINE", true);
define("UNSKIP_LINE", false);
define("TAX_CODE_WRONG_LL", "wrong tax code last letter");
define("TAX_CODE_NOT_COMPLIANT", "wrong tax code format");

define("MISSING_DATA", "missing");
define("INCOMPLETE_DATA", "incomplete");
define("NO_ISSUE", false);

define("MESSAGE_MISSING_TAXCODE", "C.F. o P.IVA mancante");
define("MESSAGE_MISSING_CITY", "Comune %s mancante");
define("MESSAGE_MISSING_PROVINCE", "Provincia %s mancante");
define("MESSAGE_MISSING_ZIP", "CAP %s mancante");
define("MESSAGE_MISSING_ADDRESS", "Indirizzo %s mancante");
define("MESSAGE_MISSING_RESIDENCEDATA", "Dati %s mancanti");
define("MESSAGE_INCOMPLETE_RESIDENCEDATA", "Dati %s incompleti");
define("MESSAGE_MISSING_BORNDATE", "Data di nascita mancante");
define("MESSAGE_MISSING_BORNPLACE", "Luogo di nascita mancante");
define("MESSAGE_MISSING_BORNCOUNTRY", "Stato di nascita mancante");
define("MESSAGE_INCOHERENT_TAXCODE", "C.F. incoerente con il nome/cognome");
define("MESSAGE_WRONG_TAXCODE_LASTLETTER", "Ultima lettera C.F. errata");
define("MESSAGE_WRONG_TAXCODE", "C.F. o P.IVA non conforme");

/**Imposta i filtri per le anomalie. Se è impostato "Includi" non fa niente
 * @param string | Tipo di persona (Fisica, Entrambe,Giuridica)
 * @param string | Inclusione/Esclusione anomalie
 * @param string | Tipo di anomalia
 * **/
function setSearchAnomaly($personType, $anomalyType){
    //In caso venga selezionato "Escludi" anomalie
    $append_Where = "";
    //Mostra solo TUTTE le anomalie
    if($anomalyType == TYPE_ANOMALY_ALL){
        //Entrambe le persone //Persone fisiche
        if($personType == PERSON_BOTH || $personType == PERSON_PHYSICAL):
        $append_Where .= " AND (
                                    (COALESCE(TRIM(T.TaxCode), '') != '' OR COALESCE(TRIM(T.VatCode), '') != '')
                                    OR
                                    (COALESCE(TRIM(T.TaxCode), '') = '' OR COALESCE(TRIM(T.VatCode), '') = '')
                                    OR
                                    BornDate IS NULL
                                    OR
                                    COALESCE(TRIM(T.BornPlace), '') = ''
                                    OR
                                    COALESCE(TRIM(T.BornCountryId), '') = ''
                                    OR
                                    COALESCE(TRIM(T.City), '') = ''
                                    OR
                                    COALESCE(TRIM(T.Province), '') = ''
                                    OR
                                    COALESCE(TRIM(T.ZIP), '') = ''
                                    OR
                                    COALESCE(TRIM(T.Address), '') = ''
                            )";
        //Persone giuridiche
        elseif($personType == PERSON_JURIDICAL):
        $append_Where .= " AND (
                            (COALESCE(TRIM(T.TaxCode), '') != '' OR COALESCE(TRIM(T.VatCode), '') != '')
                            OR
                            (COALESCE(TRIM(T.TaxCode), '') = '' OR COALESCE(TRIM(T.VatCode), '') = '')
                            OR
                            COALESCE(TRIM(T.City), '') = ''
                            OR
                            COALESCE(TRIM(T.City), '') = ''
                            OR
                            COALESCE(TRIM(T.Province), '') = ''
                            OR
                            COALESCE(TRIM(T.ZIP), '') = ''
                            OR
                            COALESCE(TRIM(T.Address), '') = ''
                         )";
        endif;
    }
    //Mostra solo un tipo di anomalia specifica
    else{
        switch($anomalyType):
            //Mancanza comune
            case TYPE_ANOMALY_M_CITY:{$append_Where .= " AND COALESCE(TRIM(T.City), '') = ''"; break;}
            //Mancanza CAP
            case TYPE_ANOMALY_M_ZIP:{$append_Where .= " AND COALESCE(TRIM(T.ZIP), '') = ''"; break;}
            //Mancanza indirizzo
            case TYPE_ANOMALY_M_ADDRESS:{$append_Where .= " AND COALESCE(TRIM(T.Address), '') = ''"; break;}
            //Mancanza CF (solo persone fisiche)
            case TYPE_ANOMALY_M_TAX:{
                $append_Where .= " AND (COALESCE(TRIM(T.TaxCode), '') = '' AND COALESCE(TRIM(T.VatCode), '') = '')"; break;}
                //Mancanza data di nascita
            case TYPE_ANOMALY_M_BDATE:{
                $append_Where .= " AND (T.BornDate IS NULL)"; break;}
                //Mancanza luogo di nascita
            case TYPE_ANOMALY_M_BPLACE:{
                $append_Where .= " AND COALESCE(TRIM(T.BornPlace), '') = ''"; break;}
                //Mancanza stato di nascita
            case TYPE_ANOMALY_M_BCOUNTRY:{
                $append_Where .= " AND COALESCE(TRIM(T.BornCountryId), '') = ''"; break;}
                //Mancanza TOTALE dati di residenza/sede
            case TYPE_ANOMALY_M_COMPANYPLACE:
            case TYPE_ANOMALY_M_RESPLACE:{
                $append_Where .= " AND (
                                        COALESCE(TRIM(T.City), '') = ''
                                        AND
                                        COALESCE(TRIM(T.Province), '') = ''
                                        AND
                                        COALESCE(TRIM(T.ZIP), '') = ''
                                        AND
                                        COALESCE(TRIM(T.Address), '') = ''
                                        )"; break;}
            //Mancanza PARZIALE dati di residenza/sede
            case TYPE_ANOMALY_I_COMPANYPLACE:
            case TYPE_ANOMALY_I_RESPLACE:{
                $append_Where .= " AND (
                                        COALESCE(TRIM(T.City), '') = ''
                                        OR
                                        COALESCE(TRIM(T.Province), '') = ''
                                        OR
                                        COALESCE(TRIM(T.ZIP), '') = ''
                                        OR
                                        COALESCE(TRIM(T.Address), '') = ''
                                        )"; break;}
        endswitch;
    }
    return $append_Where;
}

/**
 * @desc Verifica delle anomalie presenti per ogni risultato della query.
 * @param array | lista result set
 * @param string | nazionalità: N (nazionali) o F (esteri)
 * **/
function manageAnomalies($r_List){
    //Formattazione stringa "città (provincia)"
    //TODO vedere se $cityProvince serve ancora
    //$cityProvince = ($r_List['City'] != "" && $r_List['City'] != null) ? $r_List['City'] : "";
    //$cityProvince .= ($r_List['Province'] != "" && $r_List['Province'] != null) ? " (".$r_List['Province'].")" : "";
    
    $City = trim($r_List['City']);
    $Province = trim($r_List['Province']);
    $ZIP = trim($r_List['ZIP']);
    $Address = trim($r_List['Address']);
    $Genre = trim($r_List['Genre']);
    $TaxCode = trim($r_List['TaxCode']);
    $VatCode = trim($r_List['VatCode']);
    $actualGenre = checkActualGenre($Genre, $TaxCode, $VatCode); //Restituisce il vero genere basandosi su CF o PIVA
    $BornDate = trim($r_List['BornDate']);
    $BornCountryId = trim($r_List['BornCountryId']);
    $BornPlace = trim($r_List['BornPlace']);
    $Name = trim($r_List['Name']);
    $Surname = trim($r_List['Surname']);
    $ForcedTaxCode = trim($r_List['ForcedTaxCode']);
    
    //Assenza C.F/P.IVA
    $taxCode = !empty($TaxCode) ? $TaxCode : (!empty($VatCode) ? $VatCode : MISSING_DATA);
    
    //Effettua il controllo solo se c'è il codice fiscale
    if($taxCode != MISSING_DATA && empty($ForcedTaxCode)){   //Se è compilato il TaxCode e non è forzato
        $codeType = checkCodeCompliancy($TaxCode, $VatCode);
        if($codeType == 1){ //E' rilevato come CF dal formato giusto
            //Controlla che l'ultima lettera sia corretta
            $taxCodeLastLetter = checkTaxCodeLastLetter($TaxCode) ? TAX_CODE_WRONG_LL : NO_ISSUE;
            $taxCodeCompliancy = NO_ISSUE; //Non segnala anomalie di CF
            }
        elseif($codeType == 2){ //E' rilevato come PIVA dal formato giusto
            $taxCodeLastLetter = NO_ISSUE; //Non controlla l'ultima lettera e non lo segnala come anomalo
            $taxCodeCompliancy = NO_ISSUE; //Non segnala anomalie da CF
            }
        else{   //In tutti gli altri casi c'è qualcosa che non va con il codice presente (Vedi il commento sulla funzione per maggiori dettagli)
            $taxCodeLastLetter = NO_ISSUE; //Non effettua il controllo sull'ultima lettera
            $taxCodeCompliancy = TAX_CODE_NOT_COMPLIANT; //Segnala CF/P.IVA sbagliato in quanto malformato
            }
    }else{ //Se entrambi i campi sono vuoti segnalo anomalia
        $taxCodeLastLetter = NO_ISSUE; //Non controlla l'ultima lettera e non lo segnala come anomalo
        $taxCodeCompliancy = NO_ISSUE; //Non segnala anomalie di CF
    }
    
    //Assenza comune di residenza/domicilio/sede
    $city = ($City != "" && $City != null) ? $City : MISSING_DATA;
    //Assenza provincia di residenza/domicilio/sede
    if(!empty($BornCountryId) && $BornCountryId == "Z000"){
        $province = !empty($Province) ? $Province : MISSING_DATA;
    } else $province = '';
    //Assenza CAP
    $zip = ($ZIP != "" && $ZIP != null) ? $ZIP : MISSING_DATA;
    //Assenza via/strada
    $address = ($Address != "" && $Address != null) ? $Address : MISSING_DATA;
    $residence = "";
    //Assenza dati residenza/domicilio/sede
    //...mancanti
    if($address == MISSING_DATA && $zip == MISSING_DATA && $city == MISSING_DATA && $province == MISSING_DATA)
        $residence = MISSING_DATA;
    //...parziali
    elseif($address == MISSING_DATA || $zip == MISSING_DATA || $city == MISSING_DATA || $province == MISSING_DATA)
        $residence = INCOMPLETE_DATA;
    
    $bornDate = "";
    $bornPlace = "";
    $bornCountry = "";
    $codeCoherence = "";
    
    //Persona fisica
    if($actualGenre != 'D'){
        //Assenza data di nascita
        $bornDate = ($BornDate != "" && $BornDate != null) ? $BornDate : MISSING_DATA;
        //Assenza luogo di nascita
        if(!empty($BornCountryId) && $BornCountryId == "Z000"){
            $bornPlace = !empty($BornPlace) ? $BornPlace : MISSING_DATA;
        } else $bornPlace = '';
        //Assenza statp di nascita
        $bornCountry = !empty($BornCountryId) ? $BornCountryId : MISSING_DATA;
        //Incoerenza nome+cognome codice fiscale
        if(!empty($TaxCode) && !empty($Name) && !empty($Surname) && empty($ForcedTaxCode)):
            if(MatchCodeCoherence($Name, $Surname, $TaxCode) == CODE_COHERENT):
                    $codeCoherence = CODE_COHERENT;
                else:
                    $codeCoherence = CODE_INCOHERENT;
                    $taxCodeLastLetter = NO_ISSUE;  //Se il CF è incoerente non dò anomalia sull'ultima lettera
                endif;
        else:
            $taxCodeLastLetter = NO_ISSUE; //Se il CF è mancante non dò anomalia sull'ultima lettera
        endif;
        }
        
    //Array con i vari dati o con segnalazione di mancanze
    $anomalies = array(
        "ActualGenre" => $actualGenre,
        "ForcedTaxCode" => $ForcedTaxCode,
        "TaxCode" => $taxCode, 
        "TaxCodeLastLetter" => $taxCodeLastLetter, 
        "TaxCodeCompliancy" => $taxCodeCompliancy, 
        "City" => $city, 
        "Province" => $province, 
        "ZIP" => $zip, 
        "Address" => $address, 
        "Residence" => $residence, 
        "BornDate" => $bornDate, 
        "BornPlace" => $bornPlace, 
        "BornCountry" => $bornCountry, 
        "CodeCoherence" => $codeCoherence
    );
    
    return $anomalies;
}



/**Controlla che nome+cognome corrispondano alla codifica delle prime 6 cifre del codice fiscale passato
 * @param string | nome
 * @param string | cognome
 * @param string | Codice Fiscale
 * @return string | CODE_COHERENT/CODE_INCOHERENT
 * **/
function MatchCodeCoherence($name, $surname, $taxcode)
{
    //Se uno dei dati è vuoto viene restituita l'incoerenza
    if($name == null || $name == "" || $surname == null || $surname == "" || $taxcode == null || $taxcode == "")
        return CODE_INCOHERENT;
        //Vengono tolti gli spazi e rese maiuscole le stringhe
        $name = str_replace(" ", "", $name);
        $name = strtoupper($name);
        $surname = str_replace(" ", "", $surname);
        $surname = strtoupper($surname);
        
        $taxcode = strtoupper($taxcode);
        
        $finalSurname;
        $finalName;
        
        $consonantsPattern = '/B|C|D|F|G|H|J|K|L|M|N|P|Q|R|S|T|V|W|X|Y|Z/';
        $vocalPattern = '/A|E|I|O|U/';
        
        //Carica le consonanti di cognome e nome in 2 rispettivi array ($...ConsonantsArray)
        preg_match_all($consonantsPattern, $surname, $surnameConsonantsArray);
        preg_match_all($consonantsPattern, $name, $nameConsonantsArray);
        //Carica le vocali di cognome e nome in 2 rispettivi array ($...VocalsArray)
        preg_match_all($vocalPattern, $surname, $surnameVocalsArray);
        preg_match_all($vocalPattern, $name, $nameVocalsArray);
        
        //Si fa l'implode sulla posizione 0 perchè la funzione preg_match_all crea un array di array
        $surnameConsonants = implode("",$surnameConsonantsArray[0]);
        $surnameVocals = implode("", $surnameVocalsArray[0]);
        $nameConsonants = implode("", $nameConsonantsArray[0]);
        $nameVocals = implode("", $nameVocalsArray[0]);
        
        //Le prime 6 lettere del codice fiscale sono composte da
        //Le prime 3 consonanti cognome
        //Se le consonanti sono minori di 3 si rimpiazzano le mancanti con le vocali
        if(count($surnameConsonantsArray[0]) >= 3)
            $finalSurname = substr($surnameConsonants,0,3);
            else
                $finalSurname = $surnameConsonants.substr($surnameVocals, 0,3-count($surnameConsonantsArray[0]));
                //Le seconde 3 consonanti nome
                //Se ci sono più di 3 consonanti, si prende la 1-3-4
                //Se ci sono 3 consonanti, si prendono tutte
                //Se le consonanti sono minori di 3, si prendono tutte le consonanti e si rimpiazzano le mancanti con le vocali
                if(count($nameConsonantsArray[0]) >= 3){
                    if(count($nameConsonantsArray[0]) > 3):
                    $finalName = substr($nameConsonants, 0,1);
                    $finalName .= substr($nameConsonants, 2,2);
                    else:
                    $finalName = substr($nameConsonants, 0,3);
                    endif;
                }
                else
                    $finalName = $nameConsonants.substr($nameVocals, 0,3-count($nameConsonantsArray[0]));
                    
                    //Se nemmeno le vocali sono sufficienti a raggiungere il numero, si rimpiazzano le mancanti con delle "X"
                    $finalSurname = str_pad($finalSurname,3,"x");
                    $finalname = str_pad($finalName,3,"x");
                    if(strtoupper(substr($taxcode,0,6)) == strtoupper($finalSurname.$finalName)):
                        return CODE_COHERENT;
                    else:
                        return CODE_INCOHERENT;
                    endif;
}

/**Controlla le incoerenze nome/cognome <-> CF. Restituisce "true" se la riga corrente é da saltare o "false" se non è da saltare.
 * E' necessario usare un metodo da ciclare sul result set perchè non è possibile filtrare questa specifica anomalia tramite query
 * @param array
 * @param integer
 * @param integer
 * @return boolean
 * **/
function CheckCodeInconsistence($rowArray, $anomalyType)
{
    //Controllo incoerenze nome/cognome con il codice fiscale (vale solo per le persone fisiche)
    if($anomalyType == TYPE_ANOMALY_IH_NAMESURNAME || $anomalyType == TYPE_ANOMALY_ALL):    //Incoerenza nome/cognome con CF
        $codeCoherence = MatchCodeCoherence($rowArray['Name'], $rowArray['Surname'], $rowArray['TaxCode']);
            //Se il controllo di coerenza è positivo
            if($codeCoherence == CODE_COHERENT):
                return SKIP_LINE;   //Salta la riga
            else:
                return UNSKIP_LINE; //Altrimenti non salta la riga
            endif;
    endif;
}

//Controlla che l'ultima lettera del codice fiscale sia coerente
function checkTaxCodeLastLetter($taxCode){
    
    $alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $alfabeto_disp = "BAKPLCQDREVOSFTGUHMINJWZYX";
    $numeri = "0123456789";
    $numeri_disp = "10   2 3 4   5 6 7 8 9";
    
    $sommaCod = 0;
    
    for($i=0;$i<strlen($taxCode)-1;$i++){
        $char = substr($taxCode,$i,1);
        if(($i%2)==0)
            $sommaCod+= strrpos($numeri_disp,$char) + strrpos($alfabeto_disp,$char);
            else
                $sommaCod+= strrpos($numeri,$char) + strrpos($alfabeto,$char);
    }
    
    
    $codiceControllo = substr($alfabeto,($sommaCod%26),1);
    
    if($codiceControllo != substr($taxCode,15,1)) //Anomalo
        return true;
    else                                          //Non anomalo
        return false;
}

/**Controlla se è presente una qualsiasi anomalia
 * @param list
 * @return string
 * **/
function CheckAnomalyExistence($anomaliesList, $coherenceCheck = false)
{
    $anomalies = false;
    
    $anomaliesList["TaxCode"] == MISSING_DATA ? $anomalies = true : $anomalies = $anomalies;
    $anomaliesList["City"] == MISSING_DATA ? $anomalies = true : $anomalies = $anomalies;
    $anomaliesList["Province"] == MISSING_DATA ? $anomalies = true : $anomalies = $anomalies;
    $anomaliesList["ZIP"] == MISSING_DATA ?  $anomalies = true : $anomalies = $anomalies;
    $anomaliesList["Address"] == MISSING_DATA ?  $anomalies = true : $anomalies = $anomalies;
    $anomaliesList["Residence"] == MISSING_DATA ?  $anomalies = true : $anomalies = $anomalies;
    $anomaliesList["Residence"] == INCOMPLETE_DATA ?  $anomalies = true : $anomalies = $anomalies;
    $anomaliesList["BornDate"] == MISSING_DATA ?  $anomalies = true : $anomalies = $anomalies;
    $anomaliesList["BornPlace"] == MISSING_DATA ?  $anomalies = true : $anomalies = $anomalies;
    $anomaliesList["BornCountry"] == MISSING_DATA ?  $anomalies = true : $anomalies = $anomalies;
    //Viene passato l'esito del controllo di coerenza nome/cognome <-> Codice fiscale. Disattivabile
    if($coherenceCheck == true){
        $anomalies = ($anomaliesList["CodeCoherence"] == CODE_INCOHERENT) ?  $anomalies = true : $anomalies = $anomalies;
        }
    if(!($anomaliesList["CodeCoherence"] == CODE_INCOHERENT) || ($anomaliesList["CodeCoherence"] == MISSING_DATA)){
        $anomaliesList["TaxCodeLastLetter"] == TAX_CODE_WRONG_LL ?  $anomalies = true : $anomalies = $anomalies;
        }
    $anomaliesList["TaxCodeCompliancy"] == TAX_CODE_NOT_COMPLIANT ?  $anomalies = true : $anomalies = $anomalies;
        
    return $anomalies;
}

/**
 *Controlla la correttezza del C.F. o P.IVA
 *Codici risposta
 * 1 - CF
 * 2 - PIVA
 * 3 - CF malformato
 * 4 - PIVA malformato
 * 5 - Lunghezza diversa da 16 o 11
 **/
function checkCodeCompliancy($taxcode, $vatcode){
    $onlyVatCode = false;
    if(!empty($taxcode)){
        $TaxVatCode = $taxcode;
    } else if(!empty($vatcode)){
        $TaxVatCode = $vatcode;
        $onlyVatCode = true;
    } else $TaxVatCode = '';
    
    $result = -1;
    $length = strlen(trim($TaxVatCode));
    if($length == 16 && !$onlyVatCode){
        if(preg_match("/^[A-Z0-9]*$/", $TaxVatCode)){
            $result = 1;
        }
        else
        {
            $result = 3;
        }
    }
    elseif($length == 11){
        if(preg_match("/^[0-9]*$/", $TaxVatCode)){
            $result = 2;
        }
        else
        {
            $result = 4;
        }
    }
    else{
        $result = 5;
    }
    return $result;
}

//Restituisce il genere del trasgressore in base al CF o PIVA
function checkActualGenre($genre, $taxcode, $vatcode){
    if(!empty($taxcode)){
        $codeCompliancy = checkCodeCompliancy($taxcode, $vatcode);
        if($codeCompliancy == 1 || $codeCompliancy == 3)
            return 'F';
        elseif($codeCompliancy == 2 || $codeCompliancy == 4)
            return 'D';
        else 
            return ($genre == 'D' ? 'D' : 'F');
    } else return ($genre == 'D' ? 'D' : 'F');
}

//Ritorna i messaggi delle anomalie
//NOTA: prevede in input un vettore ritornato da manageAnomalies
function getAnomaliesMessages($anomaliesList){
    $messages = array();
    
    $residence = $anomaliesList['ActualGenre'] == 'D' ? 'sede' : 'residenza';
    
    ($anomaliesList["TaxCode"] ?? '')               == MISSING_DATA ? $messages[] = MESSAGE_MISSING_TAXCODE : null;
    ($anomaliesList["City"] ?? '')                  == MISSING_DATA ? $messages[] = sprintf(MESSAGE_MISSING_CITY, $residence) : null;
    ($anomaliesList["Province"] ?? '')              == MISSING_DATA ? $messages[] = sprintf(MESSAGE_MISSING_PROVINCE, $residence) : null;
    ($anomaliesList["ZIP"] ?? '')                   == MISSING_DATA ? $messages[] = sprintf(MESSAGE_MISSING_ZIP, $residence) : null;
    ($anomaliesList["BornDate"] ?? '')              == MISSING_DATA ? $messages[] = MESSAGE_MISSING_BORNDATE : null;
    ($anomaliesList["BornPlace"] ?? '')             == MISSING_DATA ? $messages[] = MESSAGE_MISSING_BORNPLACE : null;
    ($anomaliesList["BornCountry"] ?? '')           == MISSING_DATA ? $messages[] = MESSAGE_MISSING_BORNCOUNTRY : null;
    ($anomaliesList["TaxCodeLastLetter"] ?? '')     == TAX_CODE_WRONG_LL ? $messages[] = MESSAGE_WRONG_TAXCODE_LASTLETTER : null;
    ($anomaliesList["TaxCodeCompliancy"] ?? '')     == TAX_CODE_NOT_COMPLIANT ? $messages[] = MESSAGE_WRONG_TAXCODE : null;
    ($anomaliesList["CodeCoherence"] ?? '')         == CODE_INCOHERENT ? $messages[] = MESSAGE_INCOHERENT_TAXCODE : null;
    
    switch($anomaliesList["Residence"] ?? ''){
        case MISSING_DATA: $messages[] = sprintf(MESSAGE_MISSING_RESIDENCEDATA, $residence); break;
        case INCOMPLETE_DATA: $messages[] = sprintf(MESSAGE_INCOMPLETE_RESIDENCEDATA, $residence); break;
    }
    
    return $messages;
}
