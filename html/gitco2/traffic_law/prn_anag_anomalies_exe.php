<?php
    include("_path.php");
    include(INC . "/parameter.php");
    include(CLS . "/cls_db.php");
    include(INC . "/function.php");
    require_once(CLS . "/cls_view.php");
    include(CLS . "/cls_message.php");
    require_once(INC."/initialization.php");
    include(PGFN."/fn_prn_anag_anomalies.php");
    
    $cls_view = new CLS_VIEW(FINETRESPASSERLIST);
    
    $str_Where = '1=1';
    $str_Where .= " AND F.CityId='{$_SESSION['cityid']}' ";
    
    //Raccoglie tutti i filtri (descrizione => valore) impostati
    $filters = array();
    
    //$Search_Type = CheckValue('AnomalyType', 'n');
    //$s_TypePlate = CheckValue('NationalityType', 's');
    
    if ($Search_FromProtocolYear != '') {
        $filters['Da anno'] = $Search_FromProtocolYear;
        $str_Where .= " AND ProtocolYear >= " . $Search_FromProtocolYear;
    }
    if ($Search_ToProtocolYear != '') {
        $filters['Ad anno'] = $Search_ToProtocolYear;
        $str_Where .= " AND ProtocolYear <= " . $Search_ToProtocolYear;
    }
    if ($Search_FromProtocolId != '') {
        $filters['Da cron'] = $Search_FromProtocolId;
        $str_Where .= " AND ProtocolId >= " . $Search_FromProtocolId;
    }
    if ($Search_ToProtocolId != '') {
        $filters['A cron'] = $Search_ToProtocolId;
        $str_Where .= " AND ProtocolId <= " . $Search_ToProtocolId;
    }
    if ($Search_FromFineDate != '') {
        $filters['Da data verbale'] = $Search_FromFineDate;
        $FormattedDate = checkDateString('d/m/Y', $Search_FromFineDate);
        $str_Where .= " AND FineDate >= '" . $FormattedDate->format('Y-m-d')."'";
    }
    if ($Search_ToFineDate != '') {
        $filters['A data verbale'] = $Search_ToFineDate;
        $FormattedDate = checkDateString('d/m/Y', $Search_ToFineDate);
        $str_Where .= " AND FineDate <= '" . $FormattedDate->format('Y-m-d')."'";
    }
    if ($Search_Ref != '') {
        $filters['Riferimento'] = $Search_Ref;
        $str_Where .= " AND Code LIKE '%" . $Search_Ref . "%'";
    }
    if ($s_TypePlate != 'Tutti') {
        if($s_TypePlate=='N'):
            $filters['Nazionalità'] = "Nazionali";
            $str_Where .= " AND CountryId = 'Z000'";
        else:
            $filters['Nazionalità'] = "Esteri";
            $str_Where .= " AND CountryId != 'Z000'";
        endif;
    }
    if ($Search_Country != '') {
            $filters['Nazione'] = $Search_Country;
            $str_Where .= " AND CountryId = ".$Search_Country;
    }
    if ($Search_FromNotificationDate != '') {
        $filters['Da data notifica'] = $Search_FromNotificationDate;
        $FormattedDate = checkDateString('d/m/Y', $Search_FromNotificationDate);
        $str_Where .= " AND NotificationDate >= '".$FormattedDate->format('Y-m-d')."'";
    }
    if ($Search_ToNotificationDate != '') {
        $filters['A data notifica'] = $Search_ToNotificationDate;
        $FormattedDate = checkDateString('d/m/Y', $Search_ToNotificationDate);
        $str_Where .= " AND NotificationDate <= '".$FormattedDate->format('Y-m-d')."'";
    }
    if ($Search_Trespasser != '') {
        $filters['Trasgressore'] = $Search_Trespasser;
        $fullName = str_replace(" ", "", $Search_Trespasser);
        $str_Where .= " AND CONCAT(Name,Surname) LIKE '%".$fullName."%'";
    }
    
    $possibleAnomalies = unserialize(TYPE_ANOMALY_LIST);
    
    $filters['Tipo anomalia'] = $possibleAnomalies[$Search_Type];
    
    $str_Where .= setSearchAnomaly($Search_Genre, $Search_Type);
      
    $query = $cls_view->generateSelect($str_Where,null,null,null);
    $rs_fineTrespasserList = $rs->SelectQuery($query);
    $RowNumber = mysqli_num_rows($rs_fineTrespasserList);
    
    $RowCounter = 0;
    
    $fileName = $_SESSION['cityid'].'_anomalie_anag_'.date('d-m-Y').'.xls';
    
    ob_start();
    ?>
    	
        <table border="1">
        	<tr>
        		<th bgcolor="yellow">
        		Filtri:
        		</th>
        	</tr>
        	<?php foreach ($filters as $description => $value):?>
        	<tr>
        		<th>
        		<?=$description?>:
        		</th>
        		<th>
        		<?=$value?>
        		</th>
        	</tr>
        	<?php endforeach;?>
            <tr>
                <th bgcolor="blue">Riga</th>
                <th bgcolor="blue">Nominativo</th>
                <th bgcolor="blue">Data di nascita</th>
                <th bgcolor="blue">Luogo di nascita</th>
                <th bgcolor="blue">C.F./P.IVA</th>
                <th bgcolor="blue">Indirizzo residenza/domicilio/sede</th>
                <th bgcolor="lightblue">Mancanza C.F./P.IVA</th>
                <th bgcolor="lightblue">Mancanza Comune</th>
                <th bgcolor="lightblue">Mancanza Provincia</th>
                <th bgcolor="lightblue">Mancanza CAP</th>
                <th bgcolor="lightblue">Mancanza Indirizzo</th>
                <th bgcolor="lightblue">Mancanza Dati residenza/sede</th>
                <th bgcolor="lightblue">Dati residenza/sede incompleti</th>
                <th bgcolor="lightblue">Mancanza data di nascita</th>
                <th bgcolor="lightblue">Mancanza luogo di nascita</th>
                <th bgcolor="lightblue">Incoerenza nome/cognome con CF</th>
                <th bgcolor="lightblue">Ultima lettera CF sbagliata</th>
                <th bgcolor="lightblue">C.F/P.IVA non conforme</th>
    		</tr>
        <?php 
            while($r_fineTrespasserList = mysqli_fetch_array($rs_fineTrespasserList)):
                $anomaliesList = manageAnomalies($r_fineTrespasserList, $r_fineTrespasserList['CountryId'] == 'Z000' ? 'N' : 'F');
                $anomaliesPresence = CheckAnomalyExistence($anomaliesList, true);
                $coherenceResult = CheckCodeInconsistence($r_fineTrespasserList, $Search_Type);
                $actualGenre = checkActualGenre($r_fineTrespasserList['Genre'], $r_fineTrespasserList['TaxCode'], $r_fineTrespasserList['VatCode']);
                
                //Il controllo sul tipo di persona è stato spostato a codice perchè il genere viene definito controllando il CF/PIVA
                if(($Search_Genre == 1 && $actualGenre != 'F') XOR ($Search_Genre == 2 && $actualGenre != 'D'))
                    continue;
                    
                //Controlla le righe da visualizzare o saltare in caso in cui l'anomalia selezionata sia quella di incoerenza nome/cognome con CF
                //Questo perchè l'incoerenza non è definibile a partire dalla query ma dev'essere controllata lato codice
                if(($Search_Type == TYPE_ANOMALY_IH_NAMESURNAME || $Search_Type == TYPE_ANOMALY_ALL) && $actualGenre!='D'):
                    //Salta la riga se il risultato del controllo di coerenza nome/cognome-CF dice di saltare la riga
                    if($Search_Type == TYPE_ANOMALY_IH_NAMESURNAME && $coherenceResult == SKIP_LINE)
                        continue;
                        //Salta la riga se si vuole vedere tutte le anomalie, non c'è incoerenza e non ci sono altre anomalie
                    elseif(($Search_Type == TYPE_ANOMALY_ALL) && ($coherenceResult == SKIP_LINE) && !$anomaliesPresence)
                        continue;
                endif;
                
                if(!$anomaliesPresence)
                    continue;
                
                $RowCounter++;
                ?>
                <tr>
                    <th><?= $RowCounter?></th>
                    <th><?php 
                    $name = "";
                    $name .= $r_fineTrespasserList['CompanyName']!=null ? $r_fineTrespasserList['CompanyName']." " : "";
                    $name .= $r_fineTrespasserList['Surname'] != null ? $r_fineTrespasserList['Surname']." " : "";
                    $name .= $r_fineTrespasserList['Name'] != null ? $r_fineTrespasserList['Name'] : "";
                    echo $name;
                    ?></th>
                    <th><?= $actualGenre!='D' ? $r_fineTrespasserList['BornDate'] : '/'?></th>
                    <th><?= $actualGenre!='D' ? $r_fineTrespasserList['BornPlace'] : '/'?></th>
                    <th><?= trim($r_fineTrespasserList['TaxCode']) != '' ? $r_fineTrespasserList['TaxCode'] : $r_fineTrespasserList['VatCode'] ?></th>
                    <?php 
                        //Parametri per la costruzione dell'indirizzo
                        $residenceAddress = $r_fineTrespasserList['Address'] != "" ? $r_fineTrespasserList['Address'].", " : "";
                        $residenceZip = $r_fineTrespasserList['ZIP'] != "" ? $r_fineTrespasserList['ZIP'].", " : "";
                        //La provincia viene mostrata solo se è presente una città
                        $residenceCityProvince = $r_fineTrespasserList['City'] != "" ? $r_fineTrespasserList['City'] : "";
                        $residenceCityProvince .= $r_fineTrespasserList['Province'] != "" ? " (".$r_fineTrespasserList['Province'].")" : "";
                    ?>
                    <th><?=  $residenceAddress.$residenceZip.$residenceCityProvince?></th>
                    <th><?= ($anomaliesList["TaxCode"] == MISSING_DATA ? "X" : "")?></th>
                    <th><?= ($anomaliesList["City"] == MISSING_DATA ? "X" : "")?></th>
                    <th><?= ($anomaliesList["Province"] == MISSING_DATA ? "X" : "")?></th>
                    <th><?= ($anomaliesList["ZIP"] == MISSING_DATA ? "X" : "")?></th>
                    <th><?= ($anomaliesList["Address"] == MISSING_DATA ? "X" : "")?></th>
                    <th><?= ($anomaliesList["Residence"] == MISSING_DATA ? "X" : "")?></th>
                    <th><?= ($anomaliesList["Residence"] == INCOMPLETE_DATA ? "X" : "")?></th>
                    <th><?= ($anomaliesList["BornDate"] == MISSING_DATA ? "X" : "")?></th>
                    <th><?= ($anomaliesList["BornPlace"] == MISSING_DATA ? "X" : "")?></th>
                    <th><?= (($anomaliesList["CodeCoherence"] == CODE_INCOHERENT || $anomaliesList["CodeCoherence"] == MISSING_DATA) ? "X":"")?></th>
                    <th><?= ($anomaliesList["TaxCodeLastLetter"] == TAX_CODE_WRONG_LL ? "X":"")?></th>
                    <th><?= ($anomaliesList["TaxCodeCompliancy"] == TAX_CODE_NOT_COMPLIANT ? "X":"")?></th>
            	</tr>
        <?php    
            endwhile;
            ?>
<?php
        $table = ob_get_clean();
        if(!is_dir(ROOT . "/doc/print")) mkdir(ROOT . "/doc/print", 0777);
        if(!is_dir(ROOT . "/doc/print/anomalie_anagrafica")) mkdir(ROOT . "/doc/print/anomalie_anagrafica", 0777);
        
        //Scrive il report in formato xls sul file system
        file_put_contents(ROOT . "/doc/print/anomalie_anagrafica/". $fileName, "\xEF\xBB\xBF".$table);
        
        $_SESSION['Documentation'] = $MainPath . "/doc/print/anomalie_anagrafica/". $fileName;
        
        $listaParametri = array('Filter' => 1, 'PageTitle' => "Ruoli/Elenco anomalie anagrafica", 'Search_CityId' => $_SESSION['cityid'], 'Search_FromProtocolYear' => $Search_FromProtocolYear, 'Search_ToProtocolYear' => $Search_ToProtocolYear, 'Search_FromProtocolId' => $Search_FromProtocolId, 'Search_ToProtocolId' => $Search_ToProtocolId, 'Search_FromFineDate' => $Search_FromFineDate, 'Search_ToFineDate' => $Search_ToFineDate, 'Search_Ref' => $Search_Ref, 'TypePlate' => $s_TypePlate, 'Search_Country' => $Search_Country, 'Search_FromNotificationDate' => $Search_FromNotificationDate, 'Search_ToNotificationDate' => $Search_ToNotificationDate, 'Search_Genre' => $Search_Genre, 'Search_Trespasser' => $Search_Trespasser, 'Search_Type' => $Search_Type);
        
        header("location: ".impostaParametriUrl($listaParametri, 'prn_anag_anomalies.php'));
?>