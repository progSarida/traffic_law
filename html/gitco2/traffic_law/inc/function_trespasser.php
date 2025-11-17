<?php

//Viw trasgressore (vecchia, usata nei verbali)
function DivTrespasserView($r_Trespasser, $str_Txt,$finenotificationdate ="",$receivedate = ''){
    $str_TrespasserOut = '
        <div class="col-sm-12 BoxRow">
                <div class="col-sm-12 BoxRowTitle" >
                    <div class="col-sm-12 BoxRowLabel" style="text-align:center">
                        ' . $str_Txt . ' NON ASSOCIATO
                    </div>
                </div>
                <div class="clean_row HSpace4"></div>
        </div>';
    if($r_Trespasser['Id']!=null) {
        
        $rs = new CLS_DB();
        
        if($r_Trespasser['LegalFormId'] > 0){
            $LegalFormDescription = $rs->SelectQuery("SELECT Description FROM LegalForm WHERE Id=".$r_Trespasser['LegalFormId']);
            $LegalFormDescription = mysqli_fetch_array($LegalFormDescription);
            $LegalForm = '
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-2 BoxRowLabel">
                    Forma Giuridica
                </div>
                <div class="col-sm-10 BoxRowCaption">
                    '.$LegalFormDescription['Description'].'
                </div>';
        } else {
            $LegalForm = '';
        }
        
        if($finenotificationdate != ''){
            $str_FineNotificationDate = '<div class="col-sm-2 BoxRowLabel">Data Notifica</div><div class="col-sm-3 BoxRowCaption">'.$finenotificationdate.'</div>';
        } else {
            $str_FineNotificationDate = '<div class="col-sm-5 BoxRowLabel"></div>';
        }
        $str_bornDate = ($r_Trespasser['BornDate']!=null) ? DateOutDB($r_Trespasser['BornDate']) : '';
        $str_LicenseDate = ($r_Trespasser['LicenseDate']!=null) ? DateOutDB($r_Trespasser['LicenseDate']) : '';
        
        $rs_DocumentCountryId = $rs->Select('Country',"Id='".$r_Trespasser['DocumentCountryId']."'");
        $r_DocumentCountryId = mysqli_fetch_array($rs_DocumentCountryId);
        
        if(!empty($r_Trespasser['DataSourceId'])){
            $DataSourceIdTitle = $rs->getArrayLine($rs->SelectQuery('SELECT COALESCE(NULLIF(Description, ""), Title) AS Title FROM DataSource WHERE Id='.$r_Trespasser['DataSourceId']));
            $DataSourceIdTitle = $DataSourceIdTitle['Title'];
        } else $DataSourceIdTitle = 'Non identificato';
        
        $str_TrespasserOut = '
            <div class="col-sm-12 BoxRowTitle" >
                <div class="col-sm-12 BoxRowTitle" style="text-align:center">
                    ' . $str_Txt . '
                </div>
            </div>
                        
            <div class="clean_row HSpace4"></div>
                        
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                	<strong>INSERIMENTO:</strong>
                </div>
                <div class="col-sm-4 BoxRowLabel">
                	Il: '.DateOutDB($r_Trespasser['DataSourceDate']).' Da: '.$DataSourceIdTitle.'
                </div>
                <div class="col-sm-2 BoxRowLabel text-center font_small" style="background-color: #294A9C;">
                	<strong>ULTIMA MODIFICA:</strong>
                </div>
                <div class="col-sm-4 BoxRowLabel">
                	Il: '.DateOutDB($r_Trespasser['VersionDate']).' Da: '.$r_Trespasser['UserId'].'
                </div>
                	    
                <div class="clean_row HSpace4"></div>
                	    
                <div class="BoxRow col-sm-12">
                    &nbsp; Codice trasgressore: '.$r_Trespasser['Code'].'
                </div>
        	</div>
                        
            <div class="clean_row HSpace4"></div>
                        
            <div class="col-sm-12">
                <div class="col-sm-3 BoxRowLabel">
                    Data Identificazione
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    '.$receivedate.'
                </div>
                    '.$str_FineNotificationDate.'
            </div>
                        
            <div class="clean_row HSpace4"></div>
                        
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel">
                    Codice
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    ' . utf8_encode($r_Trespasser['Code']) . '
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Nominativo
                </div>
                <div class="col-sm-7 BoxRowCaption" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">
                    ' . utf8_encode($r_Trespasser['CompanyName']) . ' ' . utf8_encode($r_Trespasser['Surname']) . ' '. utf8_encode($r_Trespasser['Name']) . '
                </div>
                '.$LegalForm.'
            </div>
                    
            <div class="clean_row HSpace4"></div>
                    
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowHTitle">
                    DATI NASCITA
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Luogo di nascita
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    '.$r_Trespasser['BornPlace'].'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Data Nascita
                </div>
                <div class="col-sm-2 BoxRowCaption">
                   '. $str_bornDate .'
                </div>
            </div>
                       
            <div class="clean_row HSpace4"></div>
                       
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowHTitle">
                    DATI PATENTE
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Categoria
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    '. $r_Trespasser['LicenseCategory'] .'
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Numero
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '. $r_Trespasser['LicenseNumber'] .'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Nazionalità
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '. $r_DocumentCountryId['Title'] .'
                </div>
            </div>
                        
            <div class="clean_row HSpace4"></div>
                        
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowHTitle"></div>
                <div class="col-sm-2 BoxRowLabel">
                    Data rilascio
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '. $str_LicenseDate .'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Ente rilascio
                </div>
                <div class="col-sm-4 BoxRowCaption">
                   '. $r_Trespasser['LicenseOffice'] .'
                </div>
            </div>
                       
            <div class="clean_row HSpace4"></div>
                       
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowHTitle">
                    DATI RESIDENZA
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Nazione
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    ' . $r_Trespasser['CountryTitle'] . '
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Indirizzo
                </div>
                <div class="col-sm-5 BoxRowCaption">
                    '. $r_Trespasser['Address'] .'
                </div>
            </div>
                        
            <div class="clean_row HSpace4"></div>
                        
                        
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowHTitle"></div>
                <div class="col-sm-1 BoxRowLabel">
                    Città
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '. $r_Trespasser['City'] .'
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Provincia
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '. $r_Trespasser['Province'] .'
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    Cap
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '. $r_Trespasser['ZIP'] .'
                </div>
            </div>
                        
            <div class="clean_row HSpace4"></div>
             <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle"></div>
            <div class="col-sm-2 BoxRowLabel">
                Civico
            </div>
            <div class="col-sm-3 BoxRowCaption">
                 '. $r_Trespasser['StreetNumber'] .'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Scala
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. $r_Trespasser['Ladder'] .'
            </div>
                    
            <div class="clean_row HSpace4"></div>
        </div>
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle"></div>
                    
            <div class="col-sm-2 BoxRowLabel">
                Interno
            </div>
            <div class="col-sm-3 BoxRowCaption">
                 '. $r_Trespasser['Indoor'] .'
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Piano
            </div>
            <div class="col-sm-3 BoxRowCaption">
                '. $r_Trespasser['Plan'] .'
            </div>
                    
            <div class="clean_row HSpace4"></div>
        </div>
            <div class="col-sm-12">
                <div class="col-sm-1 BoxRowLabel">
                    C.F.
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '. $r_Trespasser['TaxCode'] .'
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    P.IVA
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '. $r_Trespasser['VatCode'] .'
                </div>
                <div class="col-sm-1 BoxRowLabel">
                    PEC
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '. $r_Trespasser['PEC'] .'
                </div>
            </div>
                        
            <div class="clean_row HSpace4"></div>
                        
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel">
                    Mail
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    '. $r_Trespasser['Mail'] .'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Telefono
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    '. $r_Trespasser['Phone'] .'
                </div>
                        
            </div>
                        
 	 	 	<div class="clean_row HSpace4"></div>
                        
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel">
                    Fax
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    '. $r_Trespasser['Fax'] .'
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Telefono 2
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    '. $r_Trespasser['Phone2'] .'
                </div>
                        
            </div>
                        
 	 	 	<div class="clean_row HSpace4"></div>
                        
            <div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel" style="height: 6.4rem;">
                    Annotazioni
                </div>
                <div class="col-sm-10 BoxRowCaption" style="height: 6.4rem;">
                    '. StringOutDB($r_Trespasser['Notes']) .'
                </div>
                        
            </div>
                        
 	 	 	<div class="clean_row HSpace4"></div>
        ';
        $rs = new CLS_DB();
        $language = $rs->SelectQuery("SELECT Title FROM traffic_law.Language WHERE Id=" . $r_Trespasser['LanguageId']);
        $language = mysqli_fetch_array($language);
        $language = $language['Title'];
        
        $str_TrespasserOut .= '
	<div class="col-sm-12">
                <div class="col-sm-2 BoxRowLabel">
                    Linguaggio
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    '. $language .'
                </div>';
        
        if($r_Trespasser['CountryId']!='Z000') $str_TrespasserOut .= '
	<div class="col-sm-2 BoxRowLabel">
                    Zona
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    '. $r_Trespasser['ZoneId'] .'
                </div>';
        else $str_TrespasserOut .= '<div class="col-sm-6 BoxRowCaption"></div>';
        $str_TrespasserOut.='
            </div>
	';
    }
    return $str_TrespasserOut;
}

//Viw trasgressore (nuova, pagina anagrafica)
function DivTrespasserViewNEW($r_Trespasser, $str_Txt,$finenotificationdate =""){
    
    $rs = new CLS_DB();
    
    $a_LegalFormIndividual = unserialize(LEGALFORM_INDIVIDUALCOMPANY);
    $Typology = ($r_Trespasser['Genre'] == "D") ? "D" : "P";
    $Genre = "M";
    $LegalFormLabel = ($r_Trespasser['Genre'] == "D") ? "Forma Giuridica" : "Impresa Individuale";
    $isIndividualCompany = false;
    $isIndividualPerson = false;
    $isForeignCountry = ($r_Trespasser['CountryId'] != "Z000") ? true : false;
    $BornCountryTitle = "";
    $LegalFormType = "";
    $DocumentTypeId2 = "";
    $ForcedTaxCode = $r_Trespasser['ForcedTaxCode'];
    $LandTitle = "";
    
    
    //Nazione di nascita
    $rs_BornCountryTitle = $rs->SelectQuery("SELECT Title FROM Country WHERE Id='".$r_Trespasser['BornCountryId']."'");
    $BornCountryTitle = StringOutDB(mysqli_fetch_array($rs_BornCountryTitle)['Title']);
    
    //Nazione di residenza
    $rs_CountryTitle = $rs->SelectQuery("SELECT Title FROM Country WHERE Id='".$r_Trespasser['CountryId']."'");
    $CountryTitle = StringOutDB(mysqli_fetch_array($rs_CountryTitle)['Title']);
    
    //Nazione patente
    $rs_DocumentCountryTitle = $rs->SelectQuery("SELECT Title FROM Country WHERE Id='".$r_Trespasser['DocumentCountryId']."'");
    $DocumentCountryTitle = StringOutDB(mysqli_fetch_array($rs_DocumentCountryTitle)['Title']);
    
    //Nazione documento identità
    $rs_DocumentCountry2Title = $rs->SelectQuery("SELECT Title FROM Country WHERE Id='".$r_Trespasser['DocumentCountryId2']."'");
    $DocumentCountry2Title = StringOutDB(mysqli_fetch_array($rs_DocumentCountry2Title)['Title']);
    
    //Forma giuridica / Impresa individuale
    $rs_LegalFormType = $rs->SelectQuery("SELECT Type, Description FROM LegalForm WHERE Id='".$r_Trespasser['LegalFormId']."'");
    $r_LegalFormType = mysqli_fetch_array($rs_LegalFormType);
    $LegalFormType = StringOutDB($r_LegalFormType['Type']);
    $LegalFormDescription = StringOutDB($r_LegalFormType['Description']);
    if ($Typology == "D")
        $isIndividualCompany = in_array($r_Trespasser['LegalFormId'], $a_LegalFormIndividual);
        else
            $isIndividualPerson = in_array($r_Trespasser['LegalFormId'], $a_LegalFormIndividual);
            
            //Tipo di documento
            switch ($r_Trespasser['DocumentTypeId2']) {
                case 2:
                    $DocumentTypeId2 = "Carta d'identità";
                    break;
                case 3:
                    $DocumentTypeId2 = "Passaporto";
                    break;
                case 4:
                    $DocumentTypeId2 = "Altro";
                    break;
                default:
                    $DocumentTypeId2 = "";
            }
            
            //Lingua
            $rs_Language = $rs->SelectQuery("SELECT Title FROM Language WHERE Id='".$r_Trespasser['LanguageId']."'");
            $LanguageTitle = StringOutDB(mysqli_fetch_array($rs_Language)['Title']);
            
            //Zona
            $a_Zone = array(
                1 => "Europa e aree mediterranee",
                2 => "Altri stati non compresi",
                3 => "Oceania",
            );
            
            //DataSourceId Title
            if(!empty($r_Trespasser['DataSourceId'])){
                $DataSourceIdTitle = $rs->getArrayLine($rs->SelectQuery('SELECT COALESCE(NULLIF(Description, ""), Title) AS Title FROM DataSource WHERE Id='.$r_Trespasser['DataSourceId']));
                $DataSourceIdTitle = $DataSourceIdTitle['Title'];
            } else $DataSourceIdTitle = 'Non identificato';
            
            //Land title
            if ($r_Trespasser['LandId'] != ""){
                $rs_getLandTitle = $rs->Select("sarida.Land", 'Id='.$r_Trespasser['LandId']);
                $r_getLandTitle = mysqli_fetch_array($rs_getLandTitle);
                $LandTitle = mysqli_num_rows($rs_getLandTitle) > 0 ? StringOutDB($r_getLandTitle['Title']) : "";
            }
            
            $CurrentDate=date("Y-m-d");
            
            if($r_Trespasser['Id']!=null) {
                
                if ($r_Trespasser['TaxCode'] != "" && strlen($r_Trespasser['TaxCode']) == 16){
                    $Genre = ((int)substr($r_Trespasser['TaxCode'], 9, 2) <= 40) ? "M" : "F";
                }
                
                //TAB_FORWARDING
                $str_TabForwarding="";
                $rs_contactForwarding = $rs->Select('TrespasserContact',"TrespasserId=".$r_Trespasser['Id']." AND ContactTypeId=1 AND Deleted=0 AND (ValidUntil > DATE_ADD(CURRENT_DATE(), interval -5  YEAR) OR ValidUntil IS NULL)", "ValidUntil DESC");
                if(mysqli_num_rows($rs_contactForwarding) > 0){
                    $toggletabForwarding = 'data-toggle="tab"';
                    $disabledForwarding = "";
                    $successForwarding = "alert-success";
                } else {
                    $toggletabForwarding = '';
                    $disabledForwarding = "disabled";
                    $successForwarding = "";
                }
                $n_ForwardingId=1;
                
                while ($r_contactForwarding = mysqli_fetch_array($rs_contactForwarding)){
                    $rs_ForwardingCountryName = $rs->Select('Country',"Id='".$r_contactForwarding['CountryId']."'");
                    $r_ForwardingCountryName = mysqli_fetch_array($rs_ForwardingCountryName);
                    $disabledField = ($r_contactForwarding['ValidUntil'] >= $CurrentDate || $r_contactForwarding['ValidUntil'] == NULL) ? false : true;
                    
                    $str_TabForwarding .=
                    '<div id="ForwardingFields'.$n_ForwardingId.'">
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-10">
                    <div class="col-sm-12">
                        <div class="forwarding_number col-sm-2 BoxRowLabel" style="background-color: #294A9C;">
                            Recapito n. '.$n_ForwardingId.($disabledField ? " (Scaduto)" : "").'
                        </div>
                        <div class="col-sm-10 BoxRowLabel">
                            Identificativo: '.$r_contactForwarding['Id'].'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Presso c/o
                        </div>
                        <div class="col-sm-6 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['Nominative']).'
                        </div>
                        <div class="col-sm-4 BoxRowHTitle">
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.StringOutDB($r_ForwardingCountryName['Title']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Città
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['City']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            <span class="col-sm-6">Prov.</span>
                            <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['Province']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Indirizzo
                        </div>
                        <div class="col-sm-6 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['Address']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Cap
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['ZIP']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Civico
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['StreetNumber']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Scala
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['Ladder']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Interno
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['Indoor']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Piano
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['Plan']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Mail
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['Mail']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Telefono
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['Phone']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Fax
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['Fax']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Telefono 2
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['Phone2']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            PEC
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactForwarding['PEC']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Valido fino al
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.DateOutDB($r_contactForwarding['ValidUntil']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="height: auto;min-height: 8rem;">
                            Annotazioni
                        </div>
                        <div class="col-sm-10 BoxRowCaption" style="height: auto;min-height: 8rem;">
                            '.StringOutDB($r_contactForwarding['Notes']).'
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                        Valido per
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12 BoxRowCaption"></div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> C.d.S</label>
                        <span class="col-sm-2 '.($r_contactForwarding['Cds'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> IMU</label>
                        <span class="col-sm-2 '.($r_contactForwarding['Imu'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> TARI</label>
                        <span class="col-sm-2 '.($r_contactForwarding['Tari'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> O.S.A.P</label>
                        <span class="col-sm-2 '.($r_contactForwarding['Osap'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> ACQUA</label>
                        <span class="col-sm-2 '.($r_contactForwarding['Water'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> Pubblicità</label>
                        <span class="col-sm-2 '.($r_contactForwarding['Advertising'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> ALTRI</label>
                        <span class="col-sm-2 '.($r_contactForwarding['Others'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption"></div>
                    <div class="col-sm-12 BoxRowCaption"></div>
                    <div class="col-sm-12 BoxRowCaption"></div>
                </div>
            </div>';
                    
                    $n_ForwardingId ++;
                }
                
                //TAB_DOMICILE
                $str_TabDomicile="";
                $rs_contactDomicile = $rs->Select('TrespasserContact',"TrespasserId=".$r_Trespasser['Id']." AND ContactTypeId=2 AND Deleted=0 AND (ValidUntil > DATE_ADD(CURRENT_DATE(), interval -5  YEAR) OR ValidUntil IS NULL)", "ValidUntil DESC");
                if(mysqli_num_rows($rs_contactDomicile) > 0){
                    $toggletabDomicile = 'data-toggle="tab"';
                    $disabledDomicile = "";
                    $successDomicile = "alert-success";
                } else {
                    $toggletabDomicile = '';
                    $disabledDomicile = "disabled";
                    $successDomicile = "";
                }
                $n_DomicileId=1;
                
                while ($r_contactDomicile = mysqli_fetch_array($rs_contactDomicile)){
                    $rs_DomicileCountryName = $rs->Select('Country',"Id='".$r_contactDomicile['CountryId']."'");
                    $r_DomicileCountryName = mysqli_fetch_array($rs_DomicileCountryName);
                    $disabledField = ($r_contactDomicile['ValidUntil'] >= $CurrentDate || $r_contactDomicile['ValidUntil'] == NULL) ? false : true;
                    
                    $str_TabDomicile .=
                    '<div id="DomicileFields'.$n_DomicileId.'">
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-10">
                    <div class="col-sm-12">
                        <div class="domicile_number col-sm-2 BoxRowLabel" style="background-color: #294A9C;">
                            Domicilio n. '.$n_DomicileId.($disabledField ? " (Scaduto)" : "").'
                        </div>
                        <div class="col-sm-10 BoxRowLabel">
                            Identificativo: '.$r_contactDomicile['Id'].'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.StringOutDB($r_DomicileCountryName['Title']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Città
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['City']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            <span class="col-sm-6">Prov.</span>
                            <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['Province']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Indirizzo
                        </div>
                        <div class="col-sm-6 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['Address']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Cap
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['ZIP']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Civico
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['StreetNumber']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Scala
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['Ladder']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Interno
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['Indoor']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Piano
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['Plan']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Mail
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['Mail']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Telefono
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['Phone']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Fax
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['Fax']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Telefono 2
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['Phone2']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            PEC
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactDomicile['PEC']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Valido fino al
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.DateOutDB($r_contactForwarding['ValidUntil']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="height: auto;min-height: 8rem;">
                            Annotazioni
                        </div>
                        <div class="col-sm-10 BoxRowCaption" style="height: auto;min-height: 8rem;">
                            '.StringOutDB($r_contactDomicile['Notes']).'
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                        Valido per
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12 BoxRowCaption"></div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> C.d.S</label>
                        <span class="col-sm-2 '.($r_contactDomicile['Cds'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> IMU</label>
                        <span class="col-sm-2 '.($r_contactDomicile['Imu'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> TARI</label>
                        <span class="col-sm-2 '.($r_contactDomicile['Tari'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> O.S.A.P</label>
                        <span class="col-sm-2 '.($r_contactDomicile['Osap'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> ACQUA</label>
                        <span class="col-sm-2 '.($r_contactDomicile['Water'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> Pubblicità</label>
                        <span class="col-sm-2 '.($r_contactDomicile['Advertising'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> ALTRI</label>
                        <span class="col-sm-2 '.($r_contactDomicile['Others'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption"></div>
                    <div class="col-sm-12 BoxRowCaption"></div>
                </div>
            </div>';
                    
                    $n_DomicileId ++;
                }
                
                //TAB_DWELLING
                $str_TabDwelling="";
                $rs_contactDwelling = $rs->Select('TrespasserContact',"TrespasserId=".$r_Trespasser['Id']." AND ContactTypeId=3 AND Deleted=0 AND (ValidUntil > DATE_ADD(CURRENT_DATE(), interval -5  YEAR) OR ValidUntil IS NULL)", "ValidUntil DESC");
                if(mysqli_num_rows($rs_contactDwelling) > 0){
                    $toggletabDwelling = 'data-toggle="tab"';
                    $disabledDwelling = "";
                    $successDwelling = "alert-success";
                } else {
                    $toggletabDwelling = '';
                    $disabledDwelling = "disabled";
                    $successDwelling = "";
                }
                $n_DwellingId=1;
                
                while ($r_contactDwelling = mysqli_fetch_array($rs_contactDwelling)){
                    $rs_DwellingCountryName = $rs->Select('Country',"Id='".$r_contactDwelling['CountryId']."'");
                    $r_DwellingCountryName = mysqli_fetch_array($rs_DwellingCountryName);
                    $disabledField = ($r_contactDwelling['ValidUntil'] >= $CurrentDate || $r_contactDwelling['ValidUntil'] == NULL) ? false : true;
                    
                    $str_TabDwelling .=
                    '<div id="DwellingFields'.$n_DwellingId.'">
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-10">
                    <div class="col-sm-12">
                        <div class="dwelling_number col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                            Dimora n. '.$n_DwellingId.($disabledField ? " (Scaduto)" : "").'
                        </div>
                        <div class="col-sm-10 BoxRowLabel">
                            Identificativo: '.$r_contactDwelling['Id'].'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.StringOutDB($r_DwellingCountryName['Title']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Città
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['City']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            <span class="col-sm-6">Prov.</span>
                            <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['Province']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Indirizzo
                        </div>
                        <div class="col-sm-6 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['Address']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Cap
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['ZIP']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Civico
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['StreetNumber']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Scala
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['Ladder']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Interno
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['Indoor']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Piano
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['Plan']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Mail
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['Mail']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Telefono
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['Phone']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            Fax
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['Fax']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Telefono 2
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['Phone2']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel">
                            PEC
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_contactDwelling['PEC']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Valido fino al
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.DateOutDB($r_contactDwelling['ValidUntil']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="height: auto;min-height: 8rem;">
                            Annotazioni
                        </div>
                        <div class="col-sm-10 BoxRowCaption" style="height: auto;min-height: 8rem;">
                            '.StringOutDB($r_contactDwelling['Notes']).'
                        </div>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                        Valido per
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12 BoxRowCaption"></div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> C.d.S</label>
                        <span class="col-sm-2 '.($r_contactDwelling['Cds'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> IMU</label>
                        <span class="col-sm-2 '.($r_contactDwelling['Imu'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> TARI</label>
                        <span class="col-sm-2 '.($r_contactDwelling['Tari'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> O.S.A.P</label>
                        <span class="col-sm-2 '.($r_contactDwelling['Osap'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> ACQUA</label>
                        <span class="col-sm-2 '.($r_contactDwelling['Water'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> Pubblicità</label>
                        <span class="col-sm-2 '.($r_contactDwelling['Advertising'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption">
                        <label class="col-sm-10"> ALTRI</label>
                        <span class="col-sm-2 '.($r_contactDwelling['Others'] == 1 ? "glyphicon glyphicon-ok text-success" : "glyphicon glyphicon glyphicon-remove text-danger").'"></span>
                    </div>
                    <div class="col-sm-12 BoxRowCaption"></div>
                    <div class="col-sm-12 BoxRowCaption"></div>
                </div>
            </div>';
                    
                    $n_DwellingId ++;
                }
                
                //DATA
                $str_TrespasserOut = '
            <div class="col-sm-12 BoxRowTitle" >
                <div class="col-sm-12 BoxRowTitle" style="text-align:center">
                    ' . $str_Txt . '
                </div>
            </div>
                        
            <div class="col-sm-12">
                <ul class="nav nav-tabs" id="mioTab">
                    <li class="active" id="tab_Subject"><a href="#Subject" data-toggle="tab">Dati Soggetto</a></li>
                    <li class="'.$disabledForwarding.'" id="tab_Forwarding"><a href="#Forwarding" class="'.$successForwarding.'" '.$toggletabForwarding.'>Recapito</a></li>
                    <li class="'.$disabledDomicile.'" id="tab_Domicile"><a href="#Domicile" class="'.$successDomicile.'" '.$toggletabDomicile.'>Domicilio</a></li>
                    <li class="'.$disabledDwelling.'" id="tab_Dwelling"><a href="#Dwelling" class="'.$successDwelling.'" '.$toggletabDwelling.'>Dimora</a></li>
                </ul>
            </div>
                        
            <div class="clean_row HSpace4"></div>
                        
            <div class="tab-content"><!-- open div tab-content -->
                        
            <!-- TAB SUBJECT -->
            <div class="tab-pane active" id="Subject">
                <div class="col-sm-12">
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                        	<strong>INSERIMENTO:</strong>
                        </div>
                        <div class="col-sm-4 BoxRowLabel">
                        	Il: '.DateOutDB($r_Trespasser['DataSourceDate']).' Da: '.$DataSourceIdTitle.'
                        </div>
                        <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                        	<strong>ULTIMA MODIFICA:</strong>
                        </div>
                        <div class="col-sm-4 BoxRowLabel">
                        	Il: '.DateOutDB($r_Trespasser['VersionDate']).' Da: '.$r_Trespasser['UserId'].'
                        </div>
                        	    
                        <div class="clean_row HSpace4"></div>
                        	    
                        <div class="BoxRow col-sm-12">
                            &nbsp; Codice trasgressore: '.$r_Trespasser['Code'].'
                        </div>
                	</div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                	<div class="col-sm-12">
	                    <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
	                    	<strong>DATI SOGGETTO</strong>
                        </div>
	                    <div class="col-sm-2 BoxRowLabel">
                            Tipologia soggetto
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.($r_Trespasser['Genre'] == "D" ? "Ditta" : "Persona fisica").'
                        </div>
                        <div id="LegalFormLabel" class="col-sm-2 BoxRowLabel">
                            '.$LegalFormLabel.'
                        </div>
                        <div class="col-sm-4 BoxRowCaption" style="text-overflow: ellipsis;white-space: nowrap;overflow: hidden;">
                            '.$LegalFormDescription.'
                        </div>
                        <div class="clean_row HSpace4"></div>
                	</div>
                                
                    <div id="PersonData" class="col-sm-12" style="display:'.(($Typology != "D" || $isIndividualCompany) ? "block" : "none").';">
	                    <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Sesso
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.($ForcedTaxCode != null ? $ForcedTaxCode : (($r_Trespasser['Genre'] == "M" || $Genre == "M") ? "M" : "F")).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Cognome
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['Surname']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Nome
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['Name']).'
                        </div>
                        <div class="clean_row HSpace4"></div>
                    </div>
                                
                    <div id="CompanyData" class="col-sm-12" style="display:'.($Typology == "D" ? "block" : "none").';">
	                    <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Ragione sociale
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['CompanyName']).'
                        </div>
                        <div class="col-sm-4 BoxRowHTitle"></div>
                        <div class="clean_row HSpace4"></div>
                	</div>
                                
                    <div id="DIV_DeathDate" style="display:'.(($Typology != "D" || $isIndividualCompany) ? "block" : "none").';">
                        <div class="col-sm-12">
                        	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                            <div class="col-sm-2 BoxRowLabel">
                                Data decesso
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                '.DateOutDB($r_Trespasser['DeathDate']).'
                            </div>
                            <div class="col-sm-7 BoxRowHTitle"></div>
                        </div>
                    </div>
                                    
                	<div class="clean_row HSpace16"></div>
                                    
                	<div id="DIV_BornData" style="display:'.(($Typology != "D" || $isIndividualCompany) ? "block" : "none").';">
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                            <strong>DATI NASCITA</strong>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-8 BoxRowCaption">
                            '.$BornCountryTitle.'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Città
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['BornPlace']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data Nascita
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.DateOutDB($r_Trespasser['BornDate']).'
                        </div>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    </div>
                                
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;font-size: 1.1rem;">
                    		<strong>RIFERIMENTI FISCALI</strong>
                    	</div>
                        <div id="DIV_TaxCode" style="display:'.(($Typology != "D" || $isIndividualCompany) ? "block" : "none").';">
                            <div class="col-sm-2 BoxRowLabel">
                                C.F
                            </div>
                            <div class="col-sm-3 BoxRowCaption">
                                '.StringOutDB($r_Trespasser['TaxCode']).'
                            </div>
                        </div>
                        <div id="DIV_CompanyTaxCode" style="display:'.(($Typology == "D" && !$isIndividualCompany) ? "block" : "none").';">
                            <div class="col-sm-2 BoxRowLabel">
                                C.F
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                '.(($Typology == "D" && !$isIndividualCompany) ? StringOutDB($r_Trespasser['TaxCode']) : "").'
                            </div>
                        </div>
                        <div id="DIV_VatCode" style="display:'.(($Typology == "D" || $isIndividualPerson) ? "block" : "none").';">
                            <div class="col-sm-2 BoxRowLabel">
                                P.IVA
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                '.StringOutDB($r_Trespasser['VatCode']).'
                            </div>
                        </div>
                        <div class="col-sm-1 BoxRowHTitle"></div>
                    </div>
                                    
                    <div class="clean_row HSpace16"></div>
                                    
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                            <strong>'.($Typology == 'D' && !$isIndividualCompany ? 'DATI SEDE' : 'DATI RESIDENZA').'</strong>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.$CountryTitle.'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Città
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['City']).'
                        </div>
                    	<div id="DIV_Province" style="display:'.(!$isForeignCountry ? "block" : "none").'">
                        <div class="col-sm-1 BoxRowLabel">
                            <span class="col-sm-6">Prov.</span>
                            <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <span id="span_Province">'.StringOutDB($r_Trespasser['Province']).'</span>
                        </div>
                        </div>
                    </div>
                                
                  	<div id="DIV_Land" style="display:'.($LandTitle != "" ? "block" : "none").'">
                  	<div class="clean_row HSpace4"></div>
                  	    
                  	<div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Land
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <span id="span_Land">'.$LandTitle.'</span>
                        </div>
                        <div class="BoxRowHTitle col-sm-4"></div>
                    </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Indirizzo
                        </div>
                        <div class="col-sm-5 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['Address']).'
                        </div>
                        <div class="BoxRowHTitle col-sm-3"></div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Civico
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['StreetNumber']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Scala
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['Ladder']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Interno
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['Indoor']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Piano
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['Plan']).'
                        </div>
                        <div class="col-sm-1 BoxRowHTitle"></div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Cap
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['ZIP']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            PEC
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['PEC']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace16"></div>
                                
                    <div id="DIV_LicenseData" style="display:'.($Typology != "D" ? "block" : "none").';">
					<div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                            <strong>DATI PATENTE</strong>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Categoria
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['LicenseCategory']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Numero
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['LicenseNumber']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.$DocumentCountryTitle.'
                        </div>
					</div>
                                
                    <div class="clean_row HSpace4"></div>
                                
					<div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data rilascio
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.DateOutDB($r_Trespasser['LicenseDate']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Ente rilascio
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['LicenseOffice']).'
                        </div>
                        <div class="col-sm-2 BoxRowHTitle"></div>
					</div>
                                
					<div class="clean_row HSpace16"></div>
                                
					<div class="col-sm-12">
                       	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;font-size: 1rem;">
                            <strong>DOCUMENTO DI IDENTITÀ</strong>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Tipo di documento
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.$DocumentTypeId2.'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.$DocumentCountry2Title.'
                        </div>
					</div>
                                
					<div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            N°
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['DocumentNumber']).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Rilasciato da
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['DocumentOffice']).'
                        </div>
                        <div class="col-sm-1 BoxRowHTitle"></div>
                    </div>
                                
					<div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            In data
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.DateOutDB($r_Trespasser['DocumentValidFrom']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Valido fino al
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.DateOutDB($r_Trespasser['DocumentValidTo']).'
                        </div>
                        <div class="col-sm-3 BoxRowHTitle"></div>
                    </div>
					<div class="clean_row HSpace16"></div>
					</div>
                                
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                    		<strong>DATI CONTATTO</strong>
                    	</div>
                        <div class="col-sm-2 BoxRowLabel">
                            Mail
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['Mail']).'
                        </div>
                        <div class="col-sm-5 BoxRowHTitle"></div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Fax
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['Fax']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Telefono
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['Phone']).'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Telefono 2
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.StringOutDB($r_Trespasser['Phone2']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;height: 6.4rem;"></div>
                        <div class="col-sm-2 BoxRowLabel" style="height: 6.4rem;">
                            Annotazioni
                        </div>
                        <div class="col-sm-8 BoxRowCaption" style="height: 6.4rem;">
                            '.StringOutDB($r_Trespasser['Notes']).'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                    		<strong>LINGUA</strong>
                    	</div>
                    	<div class="col-sm-2 BoxRowLabel">
                            Lingua
    					</div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.$LanguageTitle.'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Zona
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.$r_Trespasser['ZoneId']." - ".$a_Zone[$r_Trespasser['ZoneId']].'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                </div>
            </div>
                                
                <div class="tab-pane" id="Forwarding"><!-- open div tab-pane Forwarding -->
                <div class="col-sm-12">
                    <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                        <div class="col-sm-11" style="font-size: 1.2rem;">
                            '.TRESPASSER_CONTACT_INFO.'
                        </div>
                    </div>
                </div>
                    '.$str_TabForwarding.'
                </div><!-- close div tab-pane Forwarding -->
                        
                <div class="tab-pane" id="Domicile"><!-- open div tab-pane Domicile -->
                <div class="col-sm-12">
                    <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                        <div class="col-sm-11" style="font-size: 1.2rem;">
                            '.TRESPASSER_CONTACT_INFO.'
                        </div>
                    </div>
                </div>
                    '.$str_TabDomicile.'
                </div><!-- close div tab-pane Domicile -->
                        
                <div class="tab-pane" id="Dwelling"><!-- open div tab-pane Dwelling -->
                <div class="col-sm-12">
                    <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                        <div class="col-sm-11" style="font-size: 1.2rem;">
                            '.TRESPASSER_CONTACT_INFO.'
                        </div>
                    </div>
                </div>
                    '.$str_TabDwelling.'
                </div><!-- close div tab-pane Dwelling -->
                        
            </div><!-- close div tab-content -->
	';
            }
            return $str_TrespasserOut;
}

//Upd trasgressore (vecchia, usata nei verbali)
function DivTrespasserUpdate($r_Trespasser, $str_Txt, $finenotificationdate = '', $receivedate = ''){
    
    $str_bornDate = ($r_Trespasser['BornDate']!=null) ? DateOutDB($r_Trespasser['BornDate']) : '';
    $str_LicenseDate = ($r_Trespasser['LicenseDate']!=null) ? DateOutDB($r_Trespasser['LicenseDate']) : '';
    $str_DocumentCountryId = ($r_Trespasser['DocumentCountryId']!=null) ? $r_Trespasser['DocumentCountryId'] : 'Z000';
    
    $rs = new CLS_DB();
    
    $rs_city = $rs->Select(MAIN_DB.".City","1=1","Title ASC");
    
    if($r_Trespasser['Id']!=null && $r_Trespasser['Id']!="") {
        
        
        $str_ActiveCompany = $str_ActiveTrespasser = '';
        $chk_M = $chk_F = "";
        $displayDIV_Borndata = $displayDIV_Persondata = $displayDIV_VatCode = "none";
        
        if ($r_Trespasser['Genre'] == "D"){
            $displayDIV_VatCode = "block";
            $selected = "";
            $str_ActiveCompany = "active";
            
            $LegalForm_Select = '<select class="form-control" id="LegalFormId" name="LegalFormId"><option></option>';
            
            $getLegalform = $rs->SelectQuery("SELECT * FROM LegalForm");
            
            $type = null;
            while($row = mysqli_fetch_array($getLegalform)){
                if ($row['Type'] != $type) {
                    if ($type !== null) {
                        $LegalForm_Select.='</optgroup>';
                    }
                    $type = $row['Type'];
                    $LegalForm_Select.='<optgroup label="' . htmlspecialchars($type) . '">';
                }
                if ($r_Trespasser['LegalFormId']==$row['Id']) {
                    $selected =' SELECTED ';
                    if ($row['Type'] == "Impresa individuale") $displayDIV_Borndata = $displayDIV_Persondata = "block";
                } else $selected = "";
                $LegalForm_Select.='<option value="'.$row['Id'].'"'.$selected.'>'.$row['Description'].'</option>';
            }
            $LegalForm_Select.='</select>';
            
            $legalForm = '<div class="col-sm-2 BoxRowLabel">
                    Forma Giuridica
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    '.$LegalForm_Select.'
                </div>';
        } else {
            $displayDIV_Persondata = $displayDIV_Borndata = "block";
            $legalForm = '<div class="col-sm-5 BoxRowCaption"></div>';
            $str_ActiveTrespasser = "active";
            if ($r_Trespasser['Genre'] == "M")
                $chk_M = "CHECKED";
                else
                    $chk_F = "CHECKED";
        }
        
        
        $str_TrespasserOut = '
        <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            ' . $str_Txt . '
        </div>
                
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-12">
            <ul class="nav nav-tabs" id="mioTab">
                <li class="'. $str_ActiveTrespasser. '" id="tab_Trespasser"><a href="#Trespasser" data-toggle="tab">Persona fisica</a></li>
                <li class="'. $str_ActiveCompany. '" id="tab_Company"><a href="#Company" data-toggle="tab">Ditta</a></li>
            </ul>
        </div>
                    
        <div class="tab-content"><!-- open div tab-content -->
                    
            <div class="col-sm-12">
                <div class="col-sm-3 BoxRowLabel">
                    Data Identificazione
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    '.$receivedate.'
                </div>
                <div class="col-sm-2 BoxRowLabel">
					Data Notifica
				</div>
				<div class="col-sm-3 BoxRowCaption">
					'.$finenotificationdate.'
				</div>
            </div>
					    
            <div class="tab-pane '. $str_ActiveCompany. '" id="Company"><!-- open div tab-pane Company -->
                
                <div class="clean_row HSpace4"></div>
                
                <div class="col-sm-12">
                    <div class="col-sm-4 BoxRowLabel">
                        Ragione sociale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string frm_field_required" name="CompanyName" id="CompanyName" type="text" style="width:20rem" value="'. utf8_encode($r_Trespasser['CompanyName']).'">
                    </div>
                    '.$legalForm.'
                </div>
            </div> <!-- open div tab-pane Company -->
                        
            <div id="DIV_PersonData" style="display:'.$displayDIV_Persondata.';">
                
                <div class="clean_row HSpace4"></div>
                
                <div class="col-sm-12">
                    <div class="col-sm-1 BoxRowLabel">
                        Sesso
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input type="radio" value="M" name="Sex" id="sexM" '. $chk_M .'>M &nbsp;
                        <input type="radio" value="F" name="Sex" id="sexF" '. $chk_F .'>F
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Cognome
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string frm_field_required" type="text" id="Surname" name="Surname" value="'.utf8_encode($r_Trespasser['Surname']).'">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Nome
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string frm_field_required" type="text" id="Name" name="Name" value="'.utf8_encode($r_Trespasser['Name']).'">
                    </div>
                </div>
            </div>
                            
            <div id="DIV_BornData" style="display:'.$displayDIV_Borndata.';">
                
                <div class="clean_row HSpace4"></div>
                
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle">
                        DATI NASCITA
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Luogo di nascita
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="BornPlace" id="BornPlace" style="width:16rem" value="'.$r_Trespasser['BornPlace'].'">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Data Nascita
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_date" type="text" name="BornDate" id="BornDate" style="width:10rem" value="'. $str_bornDate .'">
                    </div>
                </div>
            </div>
                            
            <div class="tab-pane '. $str_ActiveTrespasser. '" id="Trespasser"><!-- open div tab-pane Trespasser -->
                
                <div class="clean_row HSpace4"></div>
                
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle">
                        DATI PATENTE
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Categoria
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="LicenseCategory"  style="width:5rem" value="'. $r_Trespasser['LicenseCategory'] .'">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Numero
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="LicenseNumber" style="width:14rem" value="'. $r_Trespasser['LicenseNumber'] .'">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Nazionalità
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . CreateSelect("Country", "1=1", "Title", "LicenseCountryId", "Id", "Title", $str_DocumentCountryId, false) . '
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowHTitle"></div>
                    <div class="col-sm-2 BoxRowLabel">
                        Data rilascio
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_date" type="text" name="LicenseDate"  style="width:10rem" value="'. $str_LicenseDate .'">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Ente rilascio
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="LicenseOffice" style="width:18rem" value="'. $r_Trespasser['LicenseOffice'] .'">
                    </div>
                </div>
            </div><!-- close div tab-pane Trespasser -->
                            
        </div><!-- close div tab-content -->
                            
        <div class="clean_row HSpace4"></div>
                            
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle">
                DATI RESIDENZA
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                ' . CreateSelect(MAIN_DB . ".Country", "1=1", "Title", "TrespasserCountryId", "Id", "Title", $r_Trespasser['CountryId'], true, 15) . '
            </div>
            ';
        if ($r_Trespasser['CityId'] == ""){
            $str_TrespasserOut .='
                <div class="col-sm-1 BoxRowLabel">
                    Città
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input id="CityInput" value="'.$r_Trespasser['City'].'" class="form-control frm_field_string" type="text" name="CityInput" />
                    <select id="CitySelect" class="form-control" name="CitySelect" style="display:none">
                         <option></option>';
            mysqli_data_seek($rs_city, 0);
            while($r_City = mysqli_fetch_array($rs_city)) {
                $str_TrespasserOut .= '<option value='.$r_City['Id'].'>'.$r_City['Title'].'</option>';
            }
            
            $str_TrespasserOut .='</select>
                </div>
                <div id="DIV_Province" style="display:none">
                    <div class="col-sm-1 BoxRowLabel">
                        Prov
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input id="Province" value="'.$r_Trespasser['Province'].'" class="form-control frm_field_string" type="text" name="Province" />
                    </div>
                </div>
            ';
        } else {
            $str_TrespasserOut .= '
                <div class="col-sm-1 BoxRowLabel">
                    Città
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input id="CityInput" value="" class="form-control frm_field_string" type="text" name="CityInput" style="display: none">
                    <select id="CitySelect" class="form-control" name="CitySelect">
                         <option></option>';
            mysqli_data_seek($rs_city, 0);
            while($r_City = mysqli_fetch_array($rs_city)) {
                $str_TrespasserOut .= ($r_City['Id'] == $r_Trespasser['CityId'] || strtoupper($r_City['Title']) == $r_Trespasser['City'])
                ? '<option selected value='.$r_City['Id'].'>'.$r_City['Title'].'</option>'
                    : '<option value='.$r_City['Id'].'>'.$r_City['Title'].'</option>';
            }
            
            $str_TrespasserOut .= '</select>
                </div>
                <div id="DIV_Province">
                    <div class="col-sm-1 BoxRowLabel">
                        Prov
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input id="Province" value="'.$r_Trespasser['Province'].'" class="form-control frm_field_string" type="text" name="Province" />
                    </div>
                </div>
            ';
        }
        $str_TrespasserOut .= '
        </div>
            
        <div class="clean_row HSpace4"></div>
            
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle"></div>
            <div class="col-sm-1 BoxRowLabel">
                Indirizzo
            </div>
            <div class="col-sm-9 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" id="AddressT" name="AddressT" style="width:25rem" value="'. $r_Trespasser['Address'] .'">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle"></div>
            <div class="col-sm-2 BoxRowLabel">
                Civico
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" value="'. $r_Trespasser['StreetNumber'] .'" type="text" id="StreetNumber" name="StreetNumber" style="width:4rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Scala
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" value="'. $r_Trespasser['Ladder'] .'" type="text" name="Ladder" id="Ladder" style="width:4rem">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Interno
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" value="'. $r_Trespasser['Indoor'] .'" type="text" name="Indoor" id="Indoor" style="width:4rem">
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Piano
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <input class="form-control frm_field_string" value="'. $r_Trespasser['Plan'] .'" type="text" name="Plan" id="Plan" style="width:4rem">
            </div>
        </div>
                    
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowHTitle"></div>
                    
            <div class="col-sm-2 BoxRowLabel">
                Cap
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="ZIP" id="ZIP" style="width:8rem" value="'. $r_Trespasser['ZIP'] .'">
            </div>
            <div class="col-sm-5 BoxRowCaption"></div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-1 BoxRowLabel">
                C.F.
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="TaxCode" id="TaxCode" style="width:18rem" value="'. $r_Trespasser['TaxCode'] .'">
            </div>
            <div id="DIV_VatCode" style="display:'.$displayDIV_VatCode.';">
                <div class="col-sm-1 BoxRowLabel">
                    P.IVA
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input class="form-control frm_field_string" type="text" name="VatCode" id="VatCode" style="width:18rem" value="'. $r_Trespasser['VatCode'] .'">
                </div>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                PEC
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="PEC" id="PEC" value="'. $r_Trespasser['PEC'] .'">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Mail
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Mail" value="'. $r_Trespasser['Mail'] .'">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Telefono
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Phone" value="'. $r_Trespasser['Phone'] .'">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel">
                Fax
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Fax" value="'. $r_Trespasser['Fax'] .'">
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Telefono 2
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <input class="form-control frm_field_string" type="text" name="Phone2" value="'. $r_Trespasser['Phone2'] .'">
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-12">
            <div class="col-sm-2 BoxRowLabel" style="height: 6.4rem;">
                Note
            </div>
            <div class="col-sm-10 BoxRowCaption" style="height: 6.4rem;">
                <textarea class="form-control frm_field_string" type="text" style="font-weight: bold;height: 5.4rem;" name="Notes">'. StringOutDB($r_Trespasser['Notes']) .'</textarea>
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
        ';
    } else {
        $str_TrespasserOut = '
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRowTitle" style="text-align:center">
                TRASGRESSORE NON ASSOCIATO
            </div>
        </div>';
    }
    
    return $str_TrespasserOut;
    
}

//Upd trasgressore (nuova, pagina anagrafica)
function DivTrespasserUpdateNEW($r_Trespasser, $str_Txt){
    
    $a_LegalFormIndividual = unserialize(LEGALFORM_INDIVIDUALCOMPANY);
    $Typology = ($r_Trespasser['Genre'] == "D") ? "D" : "P";
    $Genre = "M";
    $LegalFormLabel = ($r_Trespasser['Genre'] == "D") ? "Forma Giuridica" : "Impresa Individuale";
    $LandTitle = "";
    $isIndividualCompany = false;
    $isIndividualPerson = false;
    $ForcedTaxCode = $r_Trespasser['ForcedTaxCode'];
    
    $showBornCityInput = (($r_Trespasser['BornCountryId'] != "Z000" && $r_Trespasser['BornCountryId'] != "Z102" && $r_Trespasser['BornCountryId'] != "Z112") && $r_Trespasser['BornCountryId'] != "") ? true : false;
    $showCityInput = ($r_Trespasser['CountryId'] != "Z000" && $r_Trespasser['CountryId'] != "Z102" && $r_Trespasser['CountryId'] != "Z112") ? true : false;
    $showDocumentCityInput = ($r_Trespasser['DocumentCountryId2'] != "Z000" && $r_Trespasser['DocumentCountryId2'] != "Z102" && $r_Trespasser['DocumentCountryId2'] != "Z112") ? true : false;
    
    $showForeignBornCity = ($r_Trespasser['BornCountryId'] == "Z102" || $r_Trespasser['BornCountryId'] == "Z112") ? true : false;
    $showForeignCity = ($r_Trespasser['CountryId'] == "Z102" || $r_Trespasser['CountryId'] == "Z112") ? true : false;
    $showForeignDocumentCity = ($r_Trespasser['DocumentCountryId2'] == "Z102" || $r_Trespasser['DocumentCountryId2'] == "Z112") ? true : false;
    
    //     $str_bornDate = ($r_Trespasser['BornDate']!=null) ? DateOutDB($r_Trespasser['BornDate']) : '';
    //     $str_LicenseDate = ($r_Trespasser['LicenseDate']!=null) ? DateOutDB($r_Trespasser['LicenseDate']) : '';
    //     $str_DocumentCountryId = ($r_Trespasser['DocumentCountryId']!=null) ? $r_Trespasser['DocumentCountryId'] : 'Z000';
    $CurrentDate=date("Y-m-d");
    
    $rs = new CLS_DB();
    
    if($r_Trespasser['Id']!=null && $r_Trespasser['Id']!="") {
        
        if($Typology == 'D')
            $isIndividualCompany = in_array($r_Trespasser['LegalFormId'], $a_LegalFormIndividual);
            else
                $isIndividualPerson = in_array($r_Trespasser['LegalFormId'], $a_LegalFormIndividual);
                
                //DataSourceId Title
                if(!empty($r_Trespasser['DataSourceId'])){
                    $DataSourceIdTitle = $rs->getArrayLine($rs->SelectQuery('SELECT COALESCE(NULLIF(Description, ""), Title) AS Title FROM DataSource WHERE Id='.$r_Trespasser['DataSourceId']));
                    $DataSourceIdTitle = $DataSourceIdTitle['Title'];
                } else $DataSourceIdTitle = 'Non identificato';
                
                //Land title
                if ($r_Trespasser['LandId'] != ""){
                    $rs_getLandTitle = $rs->Select("sarida.Land", 'Id='.$r_Trespasser['LandId']);
                    $r_getLandTitle = mysqli_fetch_array($rs_getLandTitle);
                    $LandTitle = mysqli_num_rows($rs_getLandTitle) > 0 ? StringOutDB($r_getLandTitle['Title']) : "";
                }
                
                if ($r_Trespasser['TaxCode'] != "" && strlen($r_Trespasser['TaxCode']) == 16){
                    $Genre = ((int)substr($r_Trespasser['TaxCode'], 9, 2) <= 40) ? "M" : "F";
                }
                
                //         $str_ActiveCompany = $str_ActiveTrespasser = '';
                //         $chk_M = $chk_F = "";
                //         $displayDIV_Borndata = $displayDIV_Persondata = $displayDIV_VatCode = "none";
                
                $a_city = array();
                $rs_city = $rs->Select(MAIN_DB.".City","1=1","Title ASC");
                while($r_City = mysqli_fetch_assoc($rs_city)){
                    $a_city[strtoupper($r_City['Title'])] = $r_City['Id'];
                }
                mysqli_data_seek($rs_city, 0);
                
                //         if ($r_Trespasser['Genre'] == "D"){
                //             $displayDIV_VatCode = "block";
                //             $selected = "";
                //             $str_ActiveCompany = "active";
                
                //             $LegalForm_Select = '<select class="form-control" id="LegalFormId" name="LegalFormId"><option></option>';
                
                //             $getLegalform = $rs->SelectQuery("SELECT * FROM LegalForm");
                
                //             $type = null;
                //             while($row = mysqli_fetch_array($getLegalform)){
                //                 if ($row['Type'] != $type) {
                //                     if ($type !== null) {
                //                         $LegalForm_Select.='</optgroup>';
                //                     }
                //                     $type = $row['Type'];
                //                     $LegalForm_Select.='<optgroup label="' . htmlspecialchars($type) . '">';
                //                 }
                //                 if ($r_Trespasser['LegalFormId']==$row['Id']) {
                //                     $selected =' SELECTED ';
                //                     if ($row['Type'] == "Impresa individuale") $displayDIV_Borndata = $displayDIV_Persondata = "block";
                //                 } else $selected = "";
                //                 $LegalForm_Select.='<option value="'.$row['Id'].'"'.$selected.'>'.$row['Description'].'</option>';
                //             }
                //             $LegalForm_Select.='</select>';
                
                //             $legalForm = '<div class="col-sm-2 BoxRowLabel">
                //                     Forma Giuridica
                //                 </div>
                //                 <div class="col-sm-3 BoxRowCaption">
                //                     '.$LegalForm_Select.'
                //                 </div>';
                //         } else {
                //             $displayDIV_Persondata = $displayDIV_Borndata = "block";
                //             $legalForm = '<div class="col-sm-5 BoxRowCaption"></div>';
                //             $str_ActiveTrespasser = "active";
                //             if ($r_Trespasser['Genre'] == "M")
                //                 $chk_M = "CHECKED";
                //                 else
                //                     $chk_F = "CHECKED";
                //         }
                
                //TAB_FORWARDING
                $str_TabForwarding="";
                $rs_contactForwarding = $rs->Select('TrespasserContact',"TrespasserId=".$r_Trespasser['Id']." AND ContactTypeId=1 AND Deleted=0 AND (ValidUntil > DATE_ADD(CURRENT_DATE(), interval -5  YEAR) OR ValidUntil IS NULL)", "ValidUntil DESC");
                $n_Forwarding = (mysqli_num_rows($rs_contactForwarding) > 1) ? mysqli_num_rows($rs_contactForwarding) : 1;
                $n_ForwardingId=1;
                $isForwardingPresent="";
                
                if (mysqli_num_rows($rs_contactForwarding) > 0){
                    while ($r_contactForwarding = mysqli_fetch_array($rs_contactForwarding)){
                        
                        $displayCityInput = ($r_contactForwarding['CountryId'] == "Z000" || $r_contactForwarding['CountryId'] == "Z102"|| $r_contactForwarding['CountryId'] == "Z112") ? "none" : "block";
                        $displayCitySelect = ($r_contactForwarding['CountryId'] == "Z000") ? "block" : "none";
                        $displayCityAddButton = ($r_contactForwarding['CountryId'] == "Z000") ? false : true;
                        $CityInputValue = ($r_contactForwarding['CountryId'] == "Z000") ? "" : $r_contactForwarding['City'];
                        $isForwardingPresent = (mysqli_num_rows($rs_contactForwarding) > 0) ? "alert-success" : "";
                        $disabledField = ($r_contactForwarding['ValidUntil'] >= $CurrentDate || $r_contactForwarding['ValidUntil'] == NULL) ? false : true;
                        
                        if ($r_contactForwarding['CountryId'] == "Z102" || $r_contactForwarding['CountryId'] == "Z112"){
                            $ForeignOptions = "<option></option>";
                            $rs_ForeignCity = $rs->Select("ForeignCity", 'CountryId="'.$r_contactForwarding['CountryId'].'"');
                            while ($r_ForeignCity = mysqli_fetch_array($rs_ForeignCity)){
                                $ForeignOptions .= (StringOutDB($r_contactForwarding['City']) == StringOutDB(strtoupper($r_ForeignCity['Title'])))
                                ? '<option selected value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>'
                                    : '<option value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>';
                            }
                            $ForeignCitySelect = '<select'.($disabledField ? " disabled" : "").' id="Forwarding_ForeignCitySelect'.$n_ForwardingId.'" class="form-control frm_field_required" name="Forwarding_ForeignCitySelect[]">'.$ForeignOptions.'</select>';
                        } else $ForeignCitySelect = '<select'.($disabledField ? " disabled" : "").' id="Forwarding_ForeignCitySelect'.$n_ForwardingId.'" class="form-control frm_field_required" name="Forwarding_ForeignCitySelect[]" style="display:none"><option></option></select>';
                        
                        $str_TabForwarding .=
                        '<div id="ForwardingFields'.$n_ForwardingId.'">
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-10">
                <input type="hidden" id="ForwardingId'.$n_ForwardingId.'" name="ForwardingId[]" value="'.$r_contactForwarding['Id'].'">
                <div class="col-sm-12">
                    <div class="forwarding_number col-sm-2 BoxRowLabel" style="background-color: #294A9C;">
                        Recapito n. '.$n_ForwardingId.($disabledField ? " (Scaduto)" : "").'
                    </div>
                    <div class="forwarding_id col-sm-9 BoxRowLabel">
                        Identificativo: '.$r_contactForwarding['Id'].'
                    </div>
                    <div class="col-sm-1 BoxRowCaption table_caption_error text-center">
                        <span id="deleteForwarding'.$n_ForwardingId.'" contactid="'.$r_contactForwarding['Id'].'" data-toggle="tooltip" data-placement="top" title="Elimina" class="tooltip-r fa fa-times" style="line-height:1.8rem;color: rgb(255, 255, 255);">
                        </span>
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Presso c/o
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactForwarding['Nominative'].'" class="form-control frm_field_string" type="text" id="Forwarding_Nominative'.$n_ForwardingId.'" name="Forwarding_Nominative[]">
                    </div>
                    <div class="col-sm-4 BoxRowHTitle">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Nazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . CreateSelectExtended(MAIN_DB . ".Country", "1=1", "Title", "Forwarding_CountryId[]", "Forwarding_CountryId".$n_ForwardingId, "Id", "Title", $r_contactForwarding['CountryId'], true, $disabledField, 15) . '
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                        <span id="Forwarding_FCityAdd'.$n_ForwardingId.'" fieldid="'.($displayCityAddButton ? ($displayCityInput == "block" ? 'Forwarding_CityInput' : 'Forwarding_ForeignCitySelect').$n_ForwardingId : '').'" country="'.($displayCityAddButton ? $r_contactForwarding['CountryId'] : '').'" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:'.($displayCityAddButton ? 'block' : 'none').';margin-right: 1rem;line-height:2rem;float: right;"></span>
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$CityInputValue.'" id="Forwarding_CityInput'.$n_ForwardingId.'" class="form-control frm_field_required frm_field_string" type="text" name="Forwarding_CityInput[]" style="display:'.$displayCityInput.'; width:20rem">
                        	'.$ForeignCitySelect.'
                        <input value="'.$r_contactForwarding['LandId'].'" type="hidden" id="Forwarding_LandId'.$n_ForwardingId.'" name="Forwarding_LandId[]">
						<select'.($disabledField ? " disabled" : "").' id="Forwarding_CitySelect'.$n_ForwardingId.'" class="form-control frm_field_required" name="Forwarding_CitySelect[]" style="display:'.$displayCitySelect.';">
                             <option></option>';
                        mysqli_data_seek($rs_city, 0);
                        while($row = mysqli_fetch_array($rs_city)) {
                            if ($row['Id'] == $r_contactForwarding['CityId'])
                                $str_TabForwarding .= '<option value='.$row['Id'].' selected>'.$row['Title'].'</option>';
                                else
                                    $str_TabForwarding .= '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
                        }
                        $str_TabForwarding .='
                        </select>
                    </div>
                    <div id="Forwarding_DIV_Province'.$n_ForwardingId.'" style="display:'.$displayCitySelect.';">
                        <div class="col-sm-1 BoxRowLabel">
                            <span class="col-sm-6">Prov.</span>
                            <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <span id="Forwarding_span_Province'.$n_ForwardingId.'">'.$r_contactForwarding['Province'].'</span>
                        </div>
                    </div>
                </div>
                                
                <div class="clean_row HSpace4"></div>
                                
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Indirizzo
                    </div>
                    <div class="col-sm-5 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactForwarding['Address'].'" class="form-control frm_field_required frm_field_string" type="text" id="Forwarding_Address'.$n_ForwardingId.'" name="Forwarding_Address[]">
                    </div>
                    <div class="BoxRowLabel col-sm-1" style="text-align: center;">
                        <span id="Forwarding_RoadIcon'.$n_ForwardingId.'" data-toggle="tooltip" data-placement="right" title="Gestisci indirizzi..." class="tooltip-r glyphicon glyphicon-road'.($r_contactForwarding['CountryId'] != "Z000" ? " disabled" : "").'" style="line-height: 2rem;"></span>
                    </div>
                    <div class="col-sm-2 BoxRowLabel" style="border-left: 1px solid #E7E7E7;">
                        Cap
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactForwarding['ZIP'].'" class="form-control frm_field_string" type="text" id="Forwarding_ZIP'.$n_ForwardingId.'" name="Forwarding_ZIP[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Civico
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactForwarding['StreetNumber'].'" class="form-control frm_field_string" type="text" id="Forwarding_StreetNumber'.$n_ForwardingId.'" name="Forwarding_StreetNumber[]" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Scala
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactForwarding['Ladder'].'" class="form-control frm_field_string" type="text" name="Forwarding_Ladder[]" id="Forwarding_Ladder'.$n_ForwardingId.'" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Interno
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactForwarding['Indoor'].'" class="form-control frm_field_string" type="text" name="Forwarding_Indoor[]" id="Forwarding_Indoor'.$n_ForwardingId.'" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Piano
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactForwarding['Plan'].'" class="form-control frm_field_string" type="text" name="Forwarding_Plan[]" id="Forwarding_Plan'.$n_ForwardingId.'" style="width:6rem">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Mail
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactForwarding['Mail'].'" class="form-control frm_field_string" type="text" id="Forwarding_Mail'.$n_ForwardingId.'" name="Forwarding_Mail[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Telefono
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactForwarding['Phone'].'" class="form-control frm_field_string" type="text" id="Forwarding_Phone'.$n_ForwardingId.'" name="Forwarding_Phone[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Fax
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactForwarding['Fax'].'" class="form-control frm_field_string" type="text" id="Forwarding_Fax'.$n_ForwardingId.'" name="Forwarding_Fax[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Telefono 2
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactForwarding['Phone2'].'" class="form-control frm_field_string" type="text" id="Forwarding_Phone2'.$n_ForwardingId.'" name="Forwarding_Phone2[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        PEC
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactForwarding['PEC'].'" class="form-control frm_field_string" type="text" id="Forwarding_PEC'.$n_ForwardingId.'" name="Forwarding_PEC[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Valido fino al
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.DateOutDB($r_contactForwarding['ValidUntil']).'" class="form-control frm_field_date" type="text" id="Forwarding_ValidUntil'.$n_ForwardingId.'" name="Forwarding_ValidUntil[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel" style="height: 8rem;">
                        Annotazioni
                    </div>
                    <div class="col-sm-10 BoxRowCaption" style="height: 8rem;">
                        <textarea'.($disabledField ? " disabled" : "").' class="form-control frm_field_string" type="text" style="font-weight: bold;height: 5.4rem;" id="Forwarding_Notes'.$n_ForwardingId.'" name="Forwarding_Notes[]">'.$r_contactForwarding['Notes'].'</textarea>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                    Valido per
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" value="1" class="col-sm-2" name="Forwarding_Cds['.($n_ForwardingId-1).']" id="Forwarding_Cds'.$n_ForwardingId.'"'.($r_contactForwarding['Cds'] ? " checked" : "").'>
                    <label class="col-sm-10"> C.d.S</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" value="1" class="col-sm-2" name="Forwarding_Imu['.($n_ForwardingId-1).']" id="Forwarding_Imu'.$n_ForwardingId.'"'.($r_contactForwarding['Imu'] ? " checked" : "").'>
                    <label class="col-sm-10"> IMU</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" value="1" class="col-sm-2" name="Forwarding_Tari['.($n_ForwardingId-1).']" id="Forwarding_Tari'.$n_ForwardingId.'"'.($r_contactForwarding['Tari'] ? " checked" : "").'>
                    <label class="col-sm-10"> TARI</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" value="1" class="col-sm-2" name="Forwarding_Osap['.($n_ForwardingId-1).']" id="Forwarding_Osap'.$n_ForwardingId.'"'.($r_contactForwarding['Osap'] ? " checked" : "").'>
                    <label class="col-sm-10"> O.S.A.P</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" value="1" class="col-sm-2" name="Forwarding_Water['.($n_ForwardingId-1).']" id="Forwarding_Water'.$n_ForwardingId.'"'.($r_contactForwarding['Water'] ? " checked" : "").'>
                    <label class="col-sm-10"> ACQUA</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" value="1" class="col-sm-2" name="Forwarding_Advertising['.($n_ForwardingId-1).']" id="Forwarding_Advertising'.$n_ForwardingId.'"'.($r_contactForwarding['Advertising'] ? " checked" : "").'>
                    <label class="col-sm-10"> Pubblicità</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" value="1" class="col-sm-2" name="Forwarding_Others['.($n_ForwardingId-1).']" id="Forwarding_Others'.$n_ForwardingId.'"'.($r_contactForwarding['Others'] ? " checked" : "").'>
                    <label class="col-sm-10"> ALTRI</label>
                </div>
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption"></div>
            </div>
        </div>';
                        
                        $n_ForwardingId ++;
                    }
                } else {
                    $str_TabForwarding .=
                    '<div id="ForwardingFields'.$n_ForwardingId.'">
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-10">
                <div class="col-sm-12">
                    <div class="forwarding_number col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                        Recapito n. '.$n_ForwardingId.'
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Nominativo
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Forwarding_Nominative'.$n_ForwardingId.'" name="Forwarding_Nominative[]">
                    </div>
                    <div class="col-sm-4 BoxRowHTitle">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Nazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . CreateSelectExtended(MAIN_DB . ".Country", "1=1", "Title", "Forwarding_CountryId[]", "Forwarding_CountryId".$n_ForwardingId, "Id", "Title", "Z000", true, false, 15) . '
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                        <span id="Forwarding_FCityAdd'.$n_ForwardingId.'" fieldid="" country="" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:none;margin-right: 1rem;line-height:2rem;float: right;"></span>
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input id="Forwarding_CityInput'.$n_ForwardingId.'" class="form-control frm_field_required frm_field_string" type="text" name="Forwarding_CityInput[]" style="display:none; width:20rem">
                        <select id="Forwarding_ForeignCitySelect'.$n_ForwardingId.'" class="form-control frm_field_required" name="Forwarding_ForeignCitySelect[]" style="display:none"><option></option></select>
                        <input type="hidden" id="Forwarding_LandId'.$n_ForwardingId.'" name="Forwarding_LandId[]">
						<select id="Forwarding_CitySelect'.$n_ForwardingId.'" class="form-control frm_field_required" name="Forwarding_CitySelect[]">
                             <option></option>';
                    mysqli_data_seek($rs_city, 0);
                    while($row = mysqli_fetch_array($rs_city)) {
                        $str_TabForwarding .= '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
                    }
                    $str_TabForwarding .='
                        </select>
                    </div>
                    <div id="Forwarding_DIV_Province'.$n_ForwardingId.'">
                        <div class="col-sm-1 BoxRowLabel">
                            <span class="col-sm-6">Prov.</span>
                            <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <span id="Forwarding_span_Province'.$n_ForwardingId.'"></span>
                        </div>
                    </div>
                </div>
                                
                <div class="clean_row HSpace4"></div>
                                
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Indirizzo
                    </div>
                    <div class="col-sm-5 BoxRowCaption">
                        <input class="form-control frm_field_required frm_field_string" type="text" id="Forwarding_Address'.$n_ForwardingId.'" name="Forwarding_Address[]">
                    </div>
                    <div class="BoxRowLabel col-sm-1" style="text-align: center;">
                        <span id="Forwarding_RoadIcon'.$n_ForwardingId.'" data-toggle="tooltip" data-placement="right" title="Gestisci indirizzi..." class="tooltip-r glyphicon glyphicon-road" style="line-height: 2rem;"></span>
                    </div>
                    <div class="col-sm-2 BoxRowLabel" style="border-left: 1px solid #E7E7E7;">
                        Cap
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Forwarding_ZIP'.$n_ForwardingId.'" name="Forwarding_ZIP[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Civico
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Forwarding_StreetNumber'.$n_ForwardingId.'" name="Forwarding_StreetNumber[]" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Scala
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Forwarding_Ladder[]" id="Forwarding_Ladder'.$n_ForwardingId.'" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Interno
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Forwarding_Indoor[]" id="Forwarding_Indoor'.$n_ForwardingId.'" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Piano
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Forwarding_Plan[]" id="Forwarding_Plan'.$n_ForwardingId.'" style="width:6rem">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Mail
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Forwarding_Mail'.$n_ForwardingId.'" name="Forwarding_Mail[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Telefono
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Forwarding_Phone'.$n_ForwardingId.'" name="Forwarding_Phone[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Fax
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Forwarding_Fax'.$n_ForwardingId.'" name="Forwarding_Fax[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Telefono 2
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Forwarding_Phone2'.$n_ForwardingId.'" name="Forwarding_Phone2[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        PEC
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Forwarding_PEC'.$n_ForwardingId.'" name="Forwarding_PEC[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Valido fino al
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_date" type="text" id="Forwarding_ValidUntil'.$n_ForwardingId.'" name="Forwarding_ValidUntil[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel" style="height: 8rem;">
                        Annotazioni
                    </div>
                    <div class="col-sm-10 BoxRowCaption" style="height: 8rem;">
                        <textarea class="form-control frm_field_string" type="text" style="font-weight: bold;height: 5.4rem;" id="Forwarding_Notes'.$n_ForwardingId.'" name="Forwarding_Notes[]"></textarea>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                    Valido per
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Forwarding_Cds[0]" id="Forwarding_Cds'.$n_ForwardingId.'" checked>
                    <label class="col-sm-10"> C.d.S</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Forwarding_Imu[0]" id="Forwarding_Imu'.$n_ForwardingId.'" checked>
                    <label class="col-sm-10"> IMU</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Forwarding_Tari[0]" id="Forwarding_Tari'.$n_ForwardingId.'" checked>
                    <label class="col-sm-10"> TARI</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Forwarding_Osap[0]" id="Forwarding_Osap'.$n_ForwardingId.'" checked>
                    <label class="col-sm-10"> O.S.A.P</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Forwarding_Water[0]" id="Forwarding_Water'.$n_ForwardingId.'" checked>
                    <label class="col-sm-10"> ACQUA</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Forwarding_Advertising[0]" id="Forwarding_Advertising'.$n_ForwardingId.'" checked>
                    <label class="col-sm-10"> Pubblicità</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Forwarding_Others[0]" id="Forwarding_Others'.$n_ForwardingId.'" checked>
                    <label class="col-sm-10"> ALTRI</label>
                </div>
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption"></div>
            </div>
        </div>';
                }
                
                //TAB_DOMICILE
                $str_TabDomicile="";
                $rs_contactDomicile = $rs->Select('TrespasserContact',"TrespasserId=".$r_Trespasser['Id']." AND ContactTypeId=2 AND Deleted=0 AND (ValidUntil > DATE_ADD(CURRENT_DATE(), interval -5  YEAR) OR ValidUntil IS NULL)", "ValidUntil DESC");
                $n_Domicile = (mysqli_num_rows($rs_contactDomicile) > 1) ? mysqli_num_rows($rs_contactDomicile) : 1;
                $n_DomicileId=1;
                $isDomicilePresent="";
                
                if (mysqli_num_rows($rs_contactDomicile) > 0){
                    while ($r_contactDomicile = mysqli_fetch_array($rs_contactDomicile)){
                        
                        $displayCityInput = ($r_contactDomicile['CountryId'] == "Z000" || $r_contactDomicile['CountryId'] == "Z102"|| $r_contactDomicile['CountryId'] == "Z112") ? "none" : "block";
                        $displayCitySelect = ($r_contactDomicile['CountryId'] == "Z000") ? "block" : "none";
                        $displayCityAddButton = ($r_contactForwarding['CountryId'] == "Z000") ? false : true;
                        $CityInputValue = ($r_contactDomicile['CountryId'] == "Z000") ? "" : $r_contactDomicile['City'];
                        $isDomicilePresent = (mysqli_num_rows($rs_contactDomicile) > 0) ? "alert-success" : "";
                        $disabledField = ($r_contactDomicile['ValidUntil'] >= $CurrentDate || $r_contactDomicile['ValidUntil'] == NULL) ? false : true;
                        
                        if ($r_contactDomicile['CountryId'] == "Z102" || $r_contactDomicile['CountryId'] == "Z112"){
                            $ForeignOptions = "<option></option>";
                            $rs_ForeignCity = $rs->Select("ForeignCity", 'CountryId="'.$r_contactDomicile['CountryId'].'"');
                            while ($r_ForeignCity = mysqli_fetch_array($rs_ForeignCity)){
                                $ForeignOptions .= (StringOutDB($r_contactDomicile['City']) == StringOutDB(strtoupper($r_ForeignCity['Title'])))
                                ? '<option selected value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>'
                                    : '<option value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>';
                            }
                            $ForeignCitySelect = '<select'.($disabledField ? " disabled" : "").' id="Domicile_ForeignCitySelect'.$n_DomicileId.'" class="form-control frm_field_required" name="Domicile_ForeignCitySelect[]">'.$ForeignOptions.'</select>';
                        } else $ForeignCitySelect = '<select'.($disabledField ? " disabled" : "").' id="Domicile_ForeignCitySelect'.$n_DomicileId.'" class="form-control frm_field_required" name="Domicile_ForeignCitySelect[]" style="display:none"><option></option></select>';
                        
                        $str_TabDomicile .=
                        '<div id="DomicileFields'.$n_DomicileId.'">
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-10">
                <input type="hidden" id="DomicileId'.$n_DomicileId.'" name="DomicileId[]" value="'.$r_contactDomicile['Id'].'">
                <div class="col-sm-12">
                    <div class="domicile_number col-sm-2 BoxRowLabel" style="background-color: #294A9C;">
                        Domicilio n. '.$n_DomicileId.($disabledField ? " (Scaduto)" : "").'
                    </div>
                    <div class="domicile_id col-sm-9 BoxRowLabel">
                        Identificativo: '.$r_contactDomicile['Id'].'
                    </div>
                    <div class="col-sm-1 BoxRowCaption table_caption_error text-center">
                        <span id="deleteDomicile'.$n_DomicileId.'" contactid="'.$r_contactDomicile['Id'].'" data-toggle="tooltip" data-placement="top" title="Elimina" class="tooltip-r fa fa-times" style="line-height:1.8rem;color: rgb(255, 255, 255);">
                        </span>
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Nazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . CreateSelectExtended(MAIN_DB . ".Country", "1=1", "Title", "Domicile_CountryId[]", "Domicile_CountryId".$n_DomicileId, "Id", "Title", $r_contactDomicile['CountryId'], true, $disabledField, 15) . '
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                        <span id="Domicile_FCityAdd'.$n_DomicileId.'" fieldid="'.($displayCityAddButton ? ($displayCityInput == "block" ? 'Domicile_CityInput' : 'Domicile_ForeignCitySelect').$n_DomicileId : '').'" country="'.($displayCityAddButton ? $r_contactDomicile['CountryId'] : '').'" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:'.($displayCityAddButton ? 'block' : 'none').';margin-right: 1rem;line-height:2rem;float: right;"></span>
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$CityInputValue.'" id="Domicile_CityInput'.$n_DomicileId.'" class="form-control frm_field_required frm_field_string" type="text" name="Domicile_CityInput[]" style="display:'.$displayCityInput.'; width:20rem">
                        	'.$ForeignCitySelect.'
                        <input value="'.$r_contactDomicile['LandId'].'" type="hidden" id="Domicile_LandId'.$n_DomicileId.'" name="Domicile_LandId[]">
						<select'.($disabledField ? " disabled" : "").' id="Domicile_CitySelect'.$n_DomicileId.'" class="form-control frm_field_required" name="Domicile_CitySelect[]" style="display:'.$displayCitySelect.';">
                             <option></option>';
                        mysqli_data_seek($rs_city, 0);
                        while($row = mysqli_fetch_array($rs_city)) {
                            if ($row['Id'] == $r_contactDomicile['CityId'])
                                $str_TabDomicile .= '<option value='.$row['Id'].' selected>'.$row['Title'].'</option>';
                                else
                                    $str_TabDomicile .= '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
                        }
                        $str_TabDomicile .='
                        </select>
                    </div>
                    <div id="Domicile_DIV_Province'.$n_DomicileId.'" style="display:'.$displayCitySelect.';">
                        <div class="col-sm-1 BoxRowLabel">
                            <span class="col-sm-6">Prov.</span>
                            <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <span id="Domicile_span_Province'.$n_DomicileId.'">'.$r_contactDomicile['Province'].'</span>
                        </div>
                    </div>
                </div>
                                
                <div class="clean_row HSpace4"></div>
                                
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Indirizzo
                    </div>
                    <div class="col-sm-5 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDomicile['Address'].'" class="form-control frm_field_required frm_field_string" type="text" id="Domicile_Address'.$n_DomicileId.'" name="Domicile_Address[]">
                    </div>
                    <div class="BoxRowLabel col-sm-1" style="text-align: center;">
                        <span id="Domicile_RoadIcon'.$n_DomicileId.'" data-toggle="tooltip" data-placement="right" title="Gestisci indirizzi..." class="tooltip-r glyphicon glyphicon-road'.($r_contactDomicile['CountryId'] != "Z000" ? " disabled" : "").'" style="line-height: 2rem;"></span>
                    </div>
                    <div class="col-sm-2 BoxRowLabel" style="border-left: 1px solid #E7E7E7;">
                        Cap
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDomicile['ZIP'].'" class="form-control frm_field_string" type="text" id="Domicile_ZIP'.$n_DomicileId.'" name="Domicile_ZIP[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Civico
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDomicile['StreetNumber'].'" class="form-control frm_field_string" type="text" id="Domicile_StreetNumber'.$n_DomicileId.'" name="Domicile_StreetNumber[]" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Scala
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDomicile['Ladder'].'" class="form-control frm_field_string" type="text" name="Domicile_Ladder[]" id="Domicile_Ladder'.$n_DomicileId.'" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Interno
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDomicile['Indoor'].'" class="form-control frm_field_string" type="text" name="Domicile_Indoor[]" id="Domicile_Indoor'.$n_DomicileId.'" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Piano
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDomicile['Plan'].'" class="form-control frm_field_string" type="text" name="Domicile_Plan[]" id="Domicile_Plan'.$n_DomicileId.'" style="width:6rem">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Mail
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDomicile['Mail'].'" class="form-control frm_field_string" type="text" id="Domicile_Mail'.$n_DomicileId.'" name="Domicile_Mail[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Telefono
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDomicile['Phone'].'" class="form-control frm_field_string" type="text" id="Domicile_Phone'.$n_DomicileId.'" name="Domicile_Phone[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Fax
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDomicile['Fax'].'" class="form-control frm_field_string" type="text" id="Domicile_Fax'.$n_DomicileId.'" name="Domicile_Fax[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Telefono 2
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDomicile['Phone2'].'" class="form-control frm_field_string" type="text" id="Domicile_Phone2'.$n_DomicileId.'" name="Domicile_Phone2[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        PEC
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDomicile['PEC'].'" class="form-control frm_field_string" type="text" id="Domicile_PEC'.$n_DomicileId.'" name="Domicile_PEC[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Valido fino al
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.DateOutDB($r_contactDomicile['ValidUntil']).'" class="form-control frm_field_date" type="text" id="Domicile_ValidUntil'.$n_DomicileId.'" name="Domicile_ValidUntil[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel" style="height: 8rem;">
                        Annotazioni
                    </div>
                    <div class="col-sm-10 BoxRowCaption" style="height: 8rem;">
                        <textarea'.($disabledField ? " disabled" : "").' class="form-control frm_field_string" type="text" style="font-weight: bold;height: 5.4rem;" id="Domicile_Notes'.$n_DomicileId.'" name="Domicile_Notes[]">'.$r_contactDomicile['Notes'].'</textarea>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                    Valido per
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Domicile_Cds['.($n_DomicileId-1).']" id="Domicile_Cds'.$n_DomicileId.'"'.($r_contactDomicile['Cds'] ? " checked" : "").'>
                    <label class="col-sm-10"> C.d.S</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Domicile_Imu['.($n_DomicileId-1).']" id="Domicile_Imu'.$n_DomicileId.'"'.($r_contactDomicile['Imu'] ? " checked" : "").'>
                    <label class="col-sm-10"> IMU</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Domicile_Tari['.($n_DomicileId-1).']" id="Domicile_Tari'.$n_DomicileId.'"'.($r_contactDomicile['Tari'] ? " checked" : "").'>
                    <label class="col-sm-10"> TARI</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Domicile_Osap['.($n_DomicileId-1).']" id="Domicile_Osap'.$n_DomicileId.'"'.($r_contactDomicile['Osap'] ? " checked" : "").'>
                    <label class="col-sm-10"> O.S.A.P</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Domicile_Water['.($n_DomicileId-1).']" id="Domicile_Water'.$n_DomicileId.'"'.($r_contactDomicile['Water'] ? " checked" : "").'>
                    <label class="col-sm-10"> ACQUA</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Domicile_Advertising['.($n_DomicileId-1).']" id="Domicile_Advertising'.$n_DomicileId.'"'.($r_contactDomicile['Advertising'] ? " checked" : "").'>
                    <label class="col-sm-10"> Pubblicità</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Domicile_Others['.($n_DomicileId-1).']" id="Domicile_Others'.$n_DomicileId.'"'.($r_contactDomicile['Others'] ? " checked" : "").'>
                    <label class="col-sm-10"> ALTRI</label>
                </div>
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption"></div>
            </div>
        </div>';
                        
                        $n_DomicileId ++;
                    }
                } else {
                    $str_TabDomicile .=
                    '<div id="DomicileFields'.$n_DomicileId.'">
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-10">
                <div class="col-sm-12">
                    <div class="domicile_number col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                        Domicilio n. '.$n_DomicileId.'
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Nazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . CreateSelectExtended(MAIN_DB . ".Country", "1=1", "Title", "Domicile_CountryId[]", "Domicile_CountryId".$n_DomicileId, "Id", "Title", "Z000", true, false, 15) . '
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                        <span id="Domicile_FCityAdd'.$n_DomicileId.'" fieldid="" country="" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:none;margin-right: 1rem;line-height:2rem;float: right;"></span>
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input id="Domicile_CityInput'.$n_DomicileId.'" class="form-control frm_field_required frm_field_string" type="text" name="Domicile_CityInput[]" style="display:none; width:20rem">
                        <select id="Domicile_ForeignCitySelect'.$n_DomicileId.'" class="form-control frm_field_required" name="Domicile_ForeignCitySelect[]" style="display:none"><option></option></select>
                        <input type="hidden" id="Domicile_LandId'.$n_DomicileId.'" name="Forwarding_LandId[]">
						<select id="Domicile_CitySelect'.$n_DomicileId.'" class="form-control frm_field_required" name="Domicile_CitySelect[]">
                             <option></option>';
                    mysqli_data_seek($rs_city, 0);
                    while($row = mysqli_fetch_array($rs_city)) {
                        $str_TabDomicile .= '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
                    }
                    $str_TabDomicile .='
                        </select>
                    </div>
                    <div id="Domicile_DIV_Province'.$n_DomicileId.'">
                        <div class="col-sm-1 BoxRowLabel">
                            <span class="col-sm-6">Prov.</span>
                            <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <span id="Domicile_span_Province'.$n_DomicileId.'"></span>
                        </div>
                    </div>
                </div>
                                
                <div class="clean_row HSpace4"></div>
                                
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Indirizzo
                    </div>
                    <div class="col-sm-5 BoxRowCaption">
                        <input class="form-control frm_field_required frm_field_string" type="text" id="Domicile_Address'.$n_DomicileId.'" name="Domicile_Address[]">
                    </div>
                    <div class="BoxRowLabel col-sm-1" style="text-align: center;">
                        <span id="Domicile_RoadIcon'.$n_DomicileId.'" data-toggle="tooltip" data-placement="right" title="Gestisci indirizzi..." class="tooltip-r glyphicon glyphicon-road" style="line-height: 2rem;"></span>
                    </div>
                    <div class="col-sm-2 BoxRowLabel" style="border-left: 1px solid #E7E7E7;">
                        Cap
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Domicile_ZIP'.$n_DomicileId.'" name="Domicile_ZIP[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Civico
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Domicile_StreetNumber'.$n_DomicileId.'" name="Domicile_StreetNumber[]" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Scala
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Domicile_Ladder[]" id="Domicile_Ladder'.$n_DomicileId.'" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Interno
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Domicile_Indoor[]" id="Domicile_Indoor'.$n_DomicileId.'" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Piano
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Domicile_Plan[]" id="Domicile_Plan'.$n_DomicileId.'" style="width:6rem">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Mail
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Domicile_Mail'.$n_DomicileId.'" name="Domicile_Mail[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Telefono
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Domicile_Phone'.$n_DomicileId.'" name="Domicile_Phone[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Fax
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Domicile_Fax'.$n_DomicileId.'" name="Domicile_Fax[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Telefono 2
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Domicile_Phone2'.$n_DomicileId.'" name="Domicile_Phone2[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        PEC
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Domicile_PEC'.$n_DomicileId.'" name="Domicile_PEC[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Valido fino al
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_date" type="text" id="Domicile_ValidUntil'.$n_DomicileId.'" name="Domicile_ValidUntil[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel" style="height: 8rem;">
                        Annotazioni
                    </div>
                    <div class="col-sm-10 BoxRowCaption" style="height: 8rem;">
                        <textarea class="form-control frm_field_string" type="text" style="font-weight: bold;height: 5.4rem;" id="Domicile_Notes'.$n_DomicileId.'" name="Domicile_Notes[]"></textarea>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                    Valido per
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Domicile_Cds[0]" id="Domicile_Cds'.$n_DomicileId.'" checked>
                    <label class="col-sm-10"> C.d.S</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Domicile_Imu[0]" id="Domicile_Imu'.$n_DomicileId.'" checked>
                    <label class="col-sm-10"> IMU</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Domicile_Tari[0]" id="Domicile_Tari'.$n_DomicileId.'" checked>
                    <label class="col-sm-10"> TARI</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Domicile_Osap[0]" id="Domicile_Osap'.$n_DomicileId.'" checked>
                    <label class="col-sm-10"> O.S.A.P</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Domicile_Water[0]" id="Domicile_Water'.$n_DomicileId.'" checked>
                    <label class="col-sm-10"> ACQUA</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Domicile_Advertising[0]" id="Domicile_Advertising'.$n_DomicileId.'" checked>
                    <label class="col-sm-10"> Pubblicità</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Domicile_Others[0]" id="Domicile_Others'.$n_DomicileId.'" checked>
                    <label class="col-sm-10"> ALTRI</label>
                </div>
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption"></div>
            </div>
        </div>';
                }
                
                //TAB_DWELLING
                $str_TabDwelling="";
                $rs_contactDwelling = $rs->Select('TrespasserContact',"TrespasserId=".$r_Trespasser['Id']." AND ContactTypeId=3 AND Deleted=0 AND (ValidUntil > DATE_ADD(CURRENT_DATE(), interval -5  YEAR) OR ValidUntil IS NULL)", "ValidUntil DESC");
                $n_Dwelling = (mysqli_num_rows($rs_contactDwelling) > 1) ? mysqli_num_rows($rs_contactDwelling) : 1;
                $n_DwellingId=1;
                $isDwellingPresent="";
                
                if (mysqli_num_rows($rs_contactDwelling) > 0){
                    while ($r_contactDwelling = mysqli_fetch_array($rs_contactDwelling)){
                        
                        $displayCityInput = ($r_contactDwelling['CountryId'] == "Z000" || $r_contactDwelling['CountryId'] == "Z102"|| $r_contactDwelling['CountryId'] == "Z112") ? "none" : "block";
                        $displayCitySelect = ($r_contactDwelling['CountryId'] == "Z000") ? "block" : "none";
                        $displayCityAddButton = ($r_contactForwarding['CountryId'] == "Z000") ? false : true;
                        $CityInputValue = ($r_contactDwelling['CountryId'] == "Z000") ? "" : $r_contactDwelling['City'];
                        $isDwellingPresent = (mysqli_num_rows($rs_contactDwelling) > 0) ? "alert-success" : "";
                        $disabledField = ($r_contactDwelling['ValidUntil'] >= $CurrentDate || $r_contactDwelling['ValidUntil'] == NULL) ? false : true;
                        
                        if ($r_contactDwelling['CountryId'] == "Z102" || $r_contactDwelling['CountryId'] == "Z112"){
                            $ForeignOptions = "<option></option>";
                            $rs_ForeignCity = $rs->Select("ForeignCity", 'CountryId="'.$r_contactDwelling['CountryId'].'"');
                            while ($r_ForeignCity = mysqli_fetch_array($rs_ForeignCity)){
                                $ForeignOptions .= (StringOutDB($r_contactDwelling['City']) == StringOutDB(strtoupper($r_ForeignCity['Title'])))
                                ? '<option selected value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>'
                                    : '<option value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>';
                            }
                            $ForeignCitySelect = '<select'.($disabledField ? " disabled" : "").' id="Dwelling_ForeignCitySelect'.$n_DwellingId.'" class="form-control frm_field_required" name="Dwelling_ForeignCitySelect[]">'.$ForeignOptions.'</select>';
                        } else $ForeignCitySelect = '<select'.($disabledField ? " disabled" : "").' id="Dwelling_ForeignCitySelect'.$n_DwellingId.'" class="form-control frm_field_required" name="Dwelling_ForeignCitySelect[]" style="display:none"><option></option></select>';
                        
                        $str_TabDwelling .=
                        '<div id="DwellingFields'.$n_DwellingId.'">
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-10">
                <input type="hidden" id="DwellingId'.$n_DwellingId.'" name="DwellingId[]" value="'.$r_contactDwelling['Id'].'">
                <div class="col-sm-12">
                    <div class="dwelling_number col-sm-2 BoxRowLabel" style="background-color: #294A9C;">
                        Dimora n. '.$n_DwellingId.($disabledField ? " (Scaduto)" : "").'
                    </div>
                    <div class="dwelling_id col-sm-9 BoxRowLabel">
                        Identificativo: '.$r_contactDwelling['Id'].'
                    </div>
                    <div class="col-sm-1 BoxRowCaption table_caption_error text-center">
                        <span id="deleteDwelling'.$n_DwellingId.'" contactid="'.$r_contactDwelling['Id'].'" data-toggle="tooltip" data-placement="top" title="Elimina" class="tooltip-r fa fa-times" style="line-height:1.8rem;color: rgb(255, 255, 255);">
                        </span>
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Nazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . CreateSelectExtended(MAIN_DB . ".Country", "1=1", "Title", "Dwelling_CountryId[]", "Dwelling_CountryId".$n_DwellingId, "Id", "Title", $r_contactDwelling['CountryId'], true, $disabledField, 15) . '
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                        <span id="Dwelling_FCityAdd'.$n_DwellingId.'" fieldid="'.($displayCityAddButton ? ($displayCityInput == "block" ? 'Dwelling_CityInput' : 'Dwelling_ForeignCitySelect').$n_DwellingId : '').'" country="'.($displayCityAddButton ? $r_contactDwelling['CountryId'] : '').'" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:'.($displayCityAddButton ? 'block' : 'none').';margin-right: 1rem;line-height:2rem;float: right;"></span>
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$CityInputValue.'" id="Dwelling_CityInput'.$n_DwellingId.'" class="form-control frm_field_required frm_field_string" type="text" name="Dwelling_CityInput[]" style="display:'.$displayCityInput.'; width:20rem">
                        	'.$ForeignCitySelect.'
                        <input value="'.$r_contactDwelling['LandId'].'" type="hidden" id="Dwelling_LandId'.$n_DwellingId.'" name="Dwelling_LandId[]">
						<select'.($disabledField ? " disabled" : "").' id="Dwelling_CitySelect'.$n_DwellingId.'" class="form-control frm_field_required" name="Dwelling_CitySelect[]" style="display:'.$displayCitySelect.';">
                             <option></option>';
                        mysqli_data_seek($rs_city, 0);
                        while($row = mysqli_fetch_array($rs_city)) {
                            if ($row['Id'] == $r_contactDwelling['CityId'])
                                $str_TabDwelling .= '<option value='.$row['Id'].' selected>'.$row['Title'].'</option>';
                                else
                                    $str_TabDwelling .= '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
                        }
                        $str_TabDwelling .='
                        </select>
                    </div>
                    <div id="Dwelling_DIV_Province'.$n_DwellingId.'" style="display:'.$displayCitySelect.';">
                        <div class="col-sm-1 BoxRowLabel">
                            <span class="col-sm-6">Prov.</span>
                            <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <span id="Dwelling_span_Province'.$n_DwellingId.'">'.$r_contactDwelling['Province'].'</span>
                        </div>
                    </div>
                </div>
                                
                <div class="clean_row HSpace4"></div>
                                
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Indirizzo
                    </div>
                    <div class="col-sm-5 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDwelling['Address'].'" class="form-control frm_field_required frm_field_string" type="text" id="Dwelling_Address'.$n_DwellingId.'" name="Dwelling_Address[]">
                    </div>
                    <div class="BoxRowLabel col-sm-1" style="text-align: center;">
                        <span id="Dwelling_RoadIcon'.$n_DwellingId.'" data-toggle="tooltip" data-placement="right" title="Gestisci indirizzi..." class="tooltip-r glyphicon glyphicon-road'.($r_contactDwelling['CountryId'] != "Z000" ? " disabled" : "").'" style="line-height: 2rem;"></span>
                    </div>
                    <div class="col-sm-2 BoxRowLabel" style="border-left: 1px solid #E7E7E7;">
                        Cap
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDwelling['ZIP'].'" class="form-control frm_field_string" type="text" id="Dwelling_ZIP'.$n_DwellingId.'" name="Dwelling_ZIP[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Civico
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDwelling['StreetNumber'].'" class="form-control frm_field_string" type="text" id="Dwelling_StreetNumber'.$n_DwellingId.'" name="Dwelling_StreetNumber[]" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Scala
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDwelling['Ladder'].'" class="form-control frm_field_string" type="text" name="Dwelling_Ladder[]" id="Dwelling_Ladder'.$n_DwellingId.'" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Interno
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDwelling['Indoor'].'" class="form-control frm_field_string" type="text" name="Dwelling_Indoor[]" id="Dwelling_Indoor'.$n_DwellingId.'" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Piano
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDwelling['Plan'].'" class="form-control frm_field_string" type="text" name="Dwelling_Plan[]" id="Dwelling_Plan'.$n_DwellingId.'" style="width:6rem">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Mail
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDwelling['Mail'].'" class="form-control frm_field_string" type="text" id="Dwelling_Mail'.$n_DwellingId.'" name="Dwelling_Mail[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Telefono
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDwelling['Phone'].'" class="form-control frm_field_string" type="text" id="Dwelling_Phone'.$n_DwellingId.'" name="Dwelling_Phone[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Fax
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDwelling['Fax'].'" class="form-control frm_field_string" type="text" id="Dwelling_Fax'.$n_DwellingId.'" name="Dwelling_Fax[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Telefono 2
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDwelling['Phone2'].'" class="form-control frm_field_string" type="text" id="Dwelling_Phone2'.$n_DwellingId.'" name="Dwelling_Phone2[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        PEC
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.$r_contactDwelling['PEC'].'" class="form-control frm_field_string" type="text" id="Dwelling_PEC'.$n_DwellingId.'" name="Dwelling_PEC[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Valido fino al
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input'.($disabledField ? " disabled" : "").' value="'.DateOutDB($r_contactDwelling['ValidUntil']).'" class="form-control frm_field_date" type="text" id="Dwelling_ValidUntil'.$n_DwellingId.'" name="Dwelling_ValidUntil[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel" style="height: 8rem;">
                        Annotazioni
                    </div>
                    <div class="col-sm-10 BoxRowCaption" style="height: 8rem;">
                        <textarea'.($disabledField ? " disabled" : "").' class="form-control frm_field_string" type="text" style="font-weight: bold;height: 5.4rem;" id="Dwelling_Notes'.$n_DwellingId.'" name="Dwelling_Notes[]">'.$r_contactDwelling['Notes'].'</textarea>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                    Valido per
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Dwelling_Cds['.($n_DwellingId-1).']" id="Dwelling_Cds'.$n_DwellingId.'"'.($r_contactDwelling['Cds'] ? " checked" : "").'>
                    <label class="col-sm-10"> C.d.S</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Dwelling_Imu['.($n_DwellingId-1).']" id="Dwelling_Imu'.$n_DwellingId.'"'.($r_contactDwelling['Imu'] ? " checked" : "").'>
                    <label class="col-sm-10"> IMU</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Dwelling_Tari['.($n_DwellingId-1).']" id="Dwelling_Tari'.$n_DwellingId.'"'.($r_contactDwelling['Tari'] ? " checked" : "").'>
                    <label class="col-sm-10"> TARI</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Dwelling_Osap['.($n_DwellingId-1).']" id="Dwelling_Osap'.$n_DwellingId.'"'.($r_contactDwelling['Osap'] ? " checked" : "").'>
                    <label class="col-sm-10"> O.S.A.P</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Dwelling_Water['.($n_DwellingId-1).']" id="Dwelling_Water'.$n_DwellingId.'"'.($r_contactDwelling['Water'] ? " checked" : "").'>
                    <label class="col-sm-10"> ACQUA</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Dwelling_Advertising['.($n_DwellingId-1).']" id="Dwelling_Advertising'.$n_DwellingId.'"'.($r_contactDwelling['Advertising'] ? " checked" : "").'>
                    <label class="col-sm-10"> Pubblicità</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input'.($disabledField ? " disabled" : "").' type="checkbox" class="col-sm-2" name="Dwelling_Others['.($n_DwellingId-1).']" id="Dwelling_Others'.$n_DwellingId.'"'.($r_contactDwelling['Others'] ? " checked" : "").'>
                    <label class="col-sm-10"> ALTRI</label>
                </div>
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption"></div>
            </div>
        </div>';
                        
                        $n_DwellingId ++;
                    }
                } else {
                    $str_TabDwelling .=
                    '<div id="DwellingFields'.$n_DwellingId.'">
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-10">
                <div class="col-sm-12">
                    <div class="dwelling_number col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                        Dimora n. '.$n_DwellingId.'
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Nazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . CreateSelectExtended(MAIN_DB . ".Country", "1=1", "Title", "Dwelling_CountryId[]", "Dwelling_CountryId".$n_DwellingId, "Id", "Title", "Z000", true, false, 15) . '
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                        <span id="Dwelling_FCityAdd'.$n_DwellingId.'" fieldid="" country="" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:none;margin-right: 1rem;line-height:2rem;float: right;"></span>
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input id="Dwelling_CityInput'.$n_DwellingId.'" class="form-control frm_field_required frm_field_string" type="text" name="Dwelling_CityInput[]" style="display:none; width:20rem">
                        <select id="Dwelling_ForeignCitySelect'.$n_DwellingId.'" class="form-control frm_field_required" name="Dwelling_ForeignCitySelect[]" style="display:none"><option></option></select>
                        <input type="hidden" id="Dwelling_LandId'.$n_DwellingId.'" name="Forwarding_LandId[]">
						<select id="Dwelling_CitySelect'.$n_DwellingId.'" class="form-control frm_field_required" name="Dwelling_CitySelect[]">
                             <option></option>';
                    mysqli_data_seek($rs_city, 0);
                    while($row = mysqli_fetch_array($rs_city)) {
                        $str_TabDwelling .= '<option value='.$row['Id'].'>'.$row['Title'].'</option>';
                    }
                    $str_TabDwelling .='
                        </select>
                    </div>
                    <div id="Dwelling_DIV_Province'.$n_DwellingId.'">
                        <div class="col-sm-1 BoxRowLabel">
                            <span class="col-sm-6">Prov.</span>
                            <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <span id="Dwelling_span_Province'.$n_DwellingId.'"></span>
                        </div>
                    </div>
                </div>
                                
                <div class="clean_row HSpace4"></div>
                                
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Indirizzo
                    </div>
                    <div class="col-sm-5 BoxRowCaption">
                        <input class="form-control frm_field_required frm_field_string" type="text" id="Dwelling_Address'.$n_DwellingId.'" name="Dwelling_Address[]">
                    </div>
                    <div class="BoxRowLabel col-sm-1" style="text-align: center;">
                        <span id="Dwelling_RoadIcon'.$n_DwellingId.'" data-toggle="tooltip" data-placement="right" title="Gestisci indirizzi..." class="tooltip-r glyphicon glyphicon-road" style="line-height: 2rem;"></span>
                    </div>
                    <div class="col-sm-2 BoxRowLabel" style="border-left: 1px solid #E7E7E7;">
                        Cap
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Dwelling_ZIP'.$n_DwellingId.'" name="Dwelling_ZIP[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Civico
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Dwelling_StreetNumber'.$n_DwellingId.'" name="Dwelling_StreetNumber[]" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Scala
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Dwelling_Ladder[]" id="Dwelling_Ladder'.$n_DwellingId.'" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Interno
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Dwelling_Indoor[]" id="Dwelling_Indoor'.$n_DwellingId.'" style="width:6rem">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Piano
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" name="Dwelling_Plan[]" id="Dwelling_Plan'.$n_DwellingId.'" style="width:6rem">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Mail
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Dwelling_Mail'.$n_DwellingId.'" name="Dwelling_Mail[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Telefono
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Dwelling_Phone'.$n_DwellingId.'" name="Dwelling_Phone[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        Fax
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Dwelling_Fax'.$n_DwellingId.'" name="Dwelling_Fax[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Telefono 2
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Dwelling_Phone2'.$n_DwellingId.'" name="Dwelling_Phone2[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel">
                        PEC
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_string" type="text" id="Dwelling_PEC'.$n_DwellingId.'" name="Dwelling_PEC[]">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Valido fino al
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        <input class="form-control frm_field_date" type="text" id="Dwelling_ValidUntil'.$n_DwellingId.'" name="Dwelling_ValidUntil[]">
                    </div>
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12">
                    <div class="col-sm-2 BoxRowLabel" style="height: 8rem;">
                        Annotazioni
                    </div>
                    <div class="col-sm-10 BoxRowCaption" style="height: 8rem;">
                        <textarea class="form-control frm_field_string" type="text" style="font-weight: bold;height: 5.4rem;" id="Dwelling_Notes'.$n_DwellingId.'" name="Dwelling_Notes[]"></textarea>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="col-sm-12 BoxRowLabel" style="background-color: #294A9C;">
                    Valido per
                </div>
                            
                <div class="clean_row HSpace4"></div>
                            
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Dwelling_Cds[0]" id="Dwelling_Cds'.$n_DwellingId.'" checked>
                    <label class="col-sm-10"> C.d.S</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Dwelling_Imu[0]" id="Dwelling_Imu'.$n_DwellingId.'" checked>
                    <label class="col-sm-10"> IMU</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Dwelling_Tari[0]" id="Dwelling_Tari'.$n_DwellingId.'" checked>
                    <label class="col-sm-10"> TARI</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Dwelling_Osap[0]" id="Dwelling_Osap'.$n_DwellingId.'" checked>
                    <label class="col-sm-10"> O.S.A.P</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Dwelling_Water[0]" id="Dwelling_Water'.$n_DwellingId.'" checked>
                    <label class="col-sm-10"> ACQUA</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Dwelling_Advertising[0]" id="Dwelling_Advertising'.$n_DwellingId.'" checked>
                    <label class="col-sm-10"> Pubblicità</label>
                </div>
                <div class="col-sm-12 BoxRowCaption">
                    <input type="checkbox" class="col-sm-2" name="Dwelling_Others[0]" id="Dwelling_Others'.$n_DwellingId.'" checked>
                    <label class="col-sm-10"> ALTRI</label>
                </div>
                <div class="col-sm-12 BoxRowCaption"></div>
                <div class="col-sm-12 BoxRowCaption"></div>
            </div>
        </div>';
                }
                
                //DATA
                $str_TrespasserOut = '
        <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            ' . $str_Txt . '
        </div>
                
        <div class="clean_row HSpace4"></div>
                
       <div class="col-sm-12">
            <ul class="nav nav-tabs" id="mioTab">
                <li id="tab_Subject" class="active" ><a href="#Subject" data-toggle="tab">Dati soggetto</a></li>
                <li id="tab_Forwarding"><a class="'.$isForwardingPresent.'" href="#Forwarding" data-toggle="tab">Recapito</a></li>
                <li id="tab_Domicile"><a class="'.$isDomicilePresent.'" href="#Domicile" data-toggle="tab">Domicilio</a></li>
                <li id="tab_Dwelling"><a class="'.$isDwellingPresent.'" href="#Dwelling" data-toggle="tab">Dimora</a></li>
            </ul>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="tab-content">
                    
            <!-- TAB SUBJECT -->
            <div class="tab-pane active" id="Subject">
                <div class="col-sm-12">
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                        	<strong>INSERIMENTO:</strong>
                        </div>
                        <div class="col-sm-4 BoxRowLabel">
                        	Il: '.DateOutDB($r_Trespasser['DataSourceDate']).' Da: '.$DataSourceIdTitle.'
                        </div>
                        <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                        	<strong>ULTIMA MODIFICA:</strong>
                        </div>
                        <div class="col-sm-4 BoxRowLabel">
                        	Il: '.DateOutDB($r_Trespasser['VersionDate']).' Da: '.$r_Trespasser['UserId'].'
                        </div>
                        	    
                        <div class="clean_row HSpace4"></div>
                        	    
                        <div class="BoxRow col-sm-12">
                            &nbsp; Codice trasgressore: '.$r_Trespasser['Code'].'
                        </div>
                	</div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                	<div class="col-sm-12">
	                    <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
	                    	<strong>DATI SOGGETTO</strong>
                        </div>
	                    <div class="col-sm-2 BoxRowLabel">
                            Tipologia soggetto
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <div class="col-sm-6">
                                <input type="radio"  value="P" name="Typology" id="checkPerson"'.($Typology != "D" ? " checked" : "").'>
                                <label style="vertical-align: top;"> Persona fisica</label>
                            </div>
                            <div class="col-sm-6">
                                <input type="radio"  value="D" name="Typology" id="checkCompany"'.($Typology == "D" ? " checked" : "").'>
                                <label style="vertical-align: top;"> Ditta</label>
                            </div>
                        </div>
                        <div id="LegalFormLabel" class="col-sm-2 BoxRowLabel">
                            '.$LegalFormLabel.'
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '.CreateSelectGroup('LegalForm', '1=1', 'Type', 'CompanyLegalFormId', 'CompanyLegalFormId', 'Id', 'Description', 'Type', $r_Trespasser['LegalFormId'], false, null, $Typology == 'D' ? '' : 'hidden').'
                            '.CreateSelectGroup('LegalForm', 'Id IN(23,24)', 'Type', 'PersonLegalFormId', 'PersonLegalFormId', 'Id', 'Description', 'Type', $Typology != 'D' ? $r_Trespasser['LegalFormId'] : '', false, null, $Typology != 'D' ? '' : 'hidden').'
                        </div>
                        <div class="clean_row HSpace4"></div>
                	</div>
                                
                    <div id="PersonData" class="col-sm-12" style="display:'.(($Typology != "D" || $isIndividualCompany) ? "block" : "none").';">
	                    <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Sesso
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <div class="col-sm-6">
                                <input type="radio"  value="M" name="Genre" id="sexM"'.($ForcedTaxCode == "M" ? " checked" : (($r_Trespasser['Genre'] == "M" || $Genre == "M") ? " checked" : "")).'>M
                            </div>
                            <div class="col-sm-6">
                                <input type="radio"  value="F" name="Genre" id="sexF"'.($ForcedTaxCode == "F" ? " checked" : (($r_Trespasser['Genre'] == "F" || $Genre == "F") ? " checked" : "")).'>F
                            </div>
                            <span id="sex_code"></span>
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Cognome
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['Surname']).'" class="text-uppercase form-control frm_field_string frm_field_required" type="text" id="Surname" name="Surname">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Nome
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['Name']).'" class="text-uppercase form-control frm_field_string frm_field_required" type="text" id="Name" name="Name">
                        </div>
                        <div class="clean_row HSpace4"></div>
                    </div>
                                
                    <div id="CompanyData" class="col-sm-12" style="display:'.($Typology == "D" ? "block" : "none").';">
	                    <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Ragione sociale
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['CompanyName']).'" class="form-control frm_field_string frm_field_required" name="CompanyName" id="CompanyName" type="text">
                        </div>
                        <div class="col-sm-4 BoxRowHTitle"></div>
                        <div class="clean_row HSpace4"></div>
                	</div>
                                
                    <div id="DIV_DeathDate" style="display:'.(($Typology != "D" || $isIndividualCompany) ? "block" : "none").';">
                        <div class="col-sm-12">
                        	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                            <div class="col-sm-2 BoxRowLabel">
                                Data decesso
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input value="'.DateOutDB($r_Trespasser['DeathDate']).'" class="form-control frm_field_date" type="text" name="DeathDate">
                            </div>
                            <div class="col-sm-7 BoxRowHTitle"></div>
                        </div>
                    </div>
                                    
                	<div class="clean_row HSpace16"></div>
                                    
                	<div id="DIV_BornData" style="display:'.(($Typology != "D" || $isIndividualCompany) ? "block" : "none").';">
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                            <strong>DATI NASCITA</strong>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-8 BoxRowCaption">
                            '. CreateSelectQueryExtended("SELECT Id,Title,UPPER(Title) AS UpperTitle FROM ".MAIN_DB.".Country ORDER BY Title", "BornCountry", "BornCountry", "Id", "Title", array('UpperTitle'), $r_Trespasser['BornCountryId'] != "" ? $r_Trespasser['BornCountryId'] : "", false) .'
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Città
                            <span id="FBornCityAdd" fieldid="'.($showBornCityInput || $showForeignBornCity ? ($showBornCityInput ? 'BornCityInput' : 'ForeignBornCitySelect') : '').'" country="'.($showBornCityInput || $showForeignBornCity ? $r_Trespasser['BornCountryId'] : '').'" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:'.($showBornCityInput || $showForeignBornCity ? 'block' : 'none').';margin-right: 1rem;line-height:2rem;float: right;"></span>
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input value="'.($showBornCityInput ? StringOutDB($r_Trespasser['BornPlace']) : "").'" id="BornCityInput" class="text-uppercase form-control frm_field_string" type="text" name="BornCityInput" style="display:'.($showBornCityInput ? "block" : "none").'">
                            <select id="ForeignBornCitySelect" class="form-control" name="ForeignBornCitySelect" style="display:'.($showForeignBornCity ? "block" : "none").'">
                                <option></option>';
                $rs_ForeignCity = $rs->Select("ForeignCity", 'CountryId="'.$r_Trespasser['BornCountryId'].'"');
                while ($r_ForeignCity = mysqli_fetch_array($rs_ForeignCity)){
                    $str_TrespasserOut .= (StringOutDB($r_Trespasser['BornPlace']) == StringOutDB(strtoupper($r_ForeignCity['Title'])))
                    ? '<option selected value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>'
                        : '<option value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>';
                }
                $str_TrespasserOut .='
                            </select>
                            '.CreateSelectQueryExtended("SELECT Id,Title,UPPER(Title) AS UpperTitle FROM ".MAIN_DB.".City ORDER BY Title ASC", 'BornCitySelect', 'BornCitySelect', 'Id', 'Title', array('UpperTitle'), $a_city[$r_Trespasser['BornPlace']], false, null, null, !$showBornCityInput && !$showForeignBornCity ? array() : array('display'=>'none')).'
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data Nascita
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input value="'.DateOutDB($r_Trespasser['BornDate']).'" class="form-control frm_field_date" type="text" name="BornDate" id="BornDate">
                        </div>
                    </div>
                    <div class="clean_row HSpace16"></div>
                    </div>
                                
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;font-size: 1.1rem;">
                    		<strong>RIFERIMENTI FISCALI</strong>
                    	</div>
                        <div id="DIV_TaxCode" style="display:'.(($Typology != "D" || $isIndividualCompany) ? "block" : "none").';">
                            <div class="col-sm-2 BoxRowLabel">
                                <span class="col-sm-9">
                                    C.F
                                </span>
                                <span class="col-sm-3 text-right">
                                    <i id="DisassembleTaxCode" data-container="body" data-toggle="tooltip" data-placement="top" title="Deduci dati di nascita da C.F" class="tooltip-r fa fa-id-card" style="margin-top:0.4rem;margin-right:1rem;font-size:1.4rem;"></i>
                                </span>
                            </div>
                            <div class="col-sm-3 BoxRowCaption">
                            	<span id="span_TaxCode"'.($ForcedTaxCode != null ? ' style="display:none;"' : '').'>'.(($Typology == "D" && !$isIndividualCompany) ? "" : StringOutDB($r_Trespasser['TaxCode'])).'</span>
                                <input class="text-uppercase form-control frm_field_string" type="text" value="'.(($Typology == "D" && !$isIndividualCompany) ? "" : StringOutDB($r_Trespasser['TaxCode'])).'" name="TaxCode" id="TaxCode"'.($ForcedTaxCode != null ? '' : ' style="display:none;"').'>
                            </div>
                            <div class="col-sm-1 BoxRowLabel">
                            	Forza C.F
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input type="checkbox"'.($ForcedTaxCode != null ? ' checked value="'.$ForcedTaxCode.'"' : '').' name="ForcedTaxCode" id="ForcedTaxCode">
                            </div>
                        </div>
                        <div id="DIV_CompanyTaxCode" style="display:'.(($Typology == "D" && !$isIndividualCompany) ? "block" : "none").';">
                            <div class="col-sm-2 BoxRowLabel">
                                C.F
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input class="text-uppercase form-control frm_field_string" type="text" value="'.(($Typology == "D" && !$isIndividualCompany) ? StringOutDB($r_Trespasser['TaxCode']) : "").'" name="CompanyTaxCode" id="CompanyTaxCode">
                            </div>
                        </div>
                        <div id="DIV_VatCode" style="display:'.(($Typology == "D" || $isIndividualPerson) ? "block" : "none").';">
                            <div class="col-sm-1 BoxRowLabel">
                                P.IVA
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input value="'.StringOutDB($r_Trespasser['VatCode']).'" class="form-control frm_field_string" type="text" name="VatCode" id="VatCode">
                            </div>
                        </div>
                    </div>
                                    
                    <div class="clean_row HSpace16"></div>
                                    
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                            <strong id="LABEL_Residence">'.($Typology == 'D' && !$isIndividualCompany ? 'DATI SEDE' : 'DATI RESIDENZA').'</strong>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '. CreateSelect(MAIN_DB . ".Country", "1=1", "Title", "TrespasserCountryId", "Id", "Title", ($r_Trespasser['CountryId'] != "" ? $r_Trespasser['CountryId'] : "Z000"), true, 15) .'
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Città
                            <span id="FCityAdd" fieldid="'.($showCityInput || $showForeignCity ? ($showCityInput ? 'CityInput' : 'ForeignCitySelect') : '').'" country="'.($showCityInput || $showForeignCity ? $r_Trespasser['CountryId'] : '').'" data-toggle="tooltip" data-placement="top" title="Aggiungi città straniera" class="tooltip-r glyphicon glyphicon-plus-sign add_fcity" style="display:'.($showCityInput || $showForeignCity ? 'block' : 'none').';margin-right: 1rem;line-height:2rem;float: right;"></span>
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input value="'.($showCityInput ? StringOutDB($r_Trespasser['City']) : "").'" id="CityInput" class="text-uppercase form-control frm_field_string frm_field_required" type="text" name="CityInput" style="display:'.($showCityInput ? "block" : "none").'">
<select id="ForeignCitySelect" class="form-control frm_field_required" name="ForeignCitySelect" style="display:'.($showForeignCity ? "block" : "none").'">
                                <option></option>';
                $rs_ForeignCity = $rs->Select("ForeignCity", 'CountryId="'.$r_Trespasser['CountryId'].'"');
                while ($r_ForeignCity = mysqli_fetch_array($rs_ForeignCity)){
                    $str_TrespasserOut .= (StringOutDB($r_Trespasser['City']) == StringOutDB(strtoupper($r_ForeignCity['Title'])))
                    ? '<option selected value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>'
                        : '<option value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>';
                }
                $str_TrespasserOut .='</select>
                            <select id="CitySelect" class="form-control frm_field_required" name="CitySelect" style="display:'.(!$showCityInput && !$showForeignCity ? "block" : "none").'">
                                 <option></option>';
                
                mysqli_data_seek($rs_city, 0);
                while($r_City = mysqli_fetch_array($rs_city)) {
                    $str_TrespasserOut .= ($r_City['Id'] == $r_Trespasser['CityId'] || strtoupper($r_City['Title']) == $r_Trespasser['City'])
                    ? '<option selected value='.$r_City['Id'].'>'.$r_City['Title'].'</option>'
                        : '<option value='.$r_City['Id'].'>'.$r_City['Title'].'</option>';
                }
                
                $str_TrespasserOut .='</select>
                        </div>
                    	<div id="DIV_Province" style="display:'.(!$showCityInput && !$showForeignCity ? "block" : "none").'">
                        <div class="col-sm-1 BoxRowLabel">
                            <span class="col-sm-6">Prov.</span>
                            <i data-toggle="tooltip" data-placement="top" title="In caso di variazione dell\'associazione tra città e provincia contattare l\'amministratore di sistema." class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <span id="span_Province">'.StringOutDB($r_Trespasser['Province']).'</span>
                        </div>
                        </div>
                    </div>
                                
                  	<div id="DIV_Land" style="display:'.($showForeignCity ? "block" : "none").'">
                  	<div class="clean_row HSpace4"></div>
                  	    
                  	<div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Land
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <span id="span_Land">'.$LandTitle.'</span>
                            <input value="'.$r_Trespasser['LandId'].'" type="hidden" id="LandId" name="LandId">
                        </div>
                        <div class="BoxRowHTitle col-sm-4"></div>
                    </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Indirizzo
                        </div>
                        <div class="col-sm-5 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['Address']).'" class="form-control frm_field_string frm_field_required" type="text" id="AddressT" name="AddressT">
                        </div>
                        <div class="BoxRowLabel col-sm-1" style="text-align: center;">
                            <span id="RoadIcon" data-toggle="tooltip" data-placement="right" title="Gestisci indirizzi..." class="tooltip-r glyphicon glyphicon-road'.(!$showCityInput && !$showForeignCity ? "" : " disabled").'" style="line-height: 2rem;"></span>
                        </div>
                        <div class="BoxRowHTitle col-sm-2"></div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Civico
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['StreetNumber']).'" class="form-control frm_field_string" type="text" id="StreetNumber" name="StreetNumber">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Scala
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['Ladder']).'" class="form-control frm_field_string" type="text" name="Ladder" id="Ladder">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Interno
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['Indoor']).'" class="form-control frm_field_string" type="text" name="Indoor" id="Indoor">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Piano
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['Plan']).'" class="form-control frm_field_string" type="text" name="Plan" id="Plan">
                        </div>
                        <div class="col-sm-1 BoxRowHTitle"></div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Cap
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['ZIP']).'" class="form-control frm_field_string" type="text" name="ZIP" id="ZIP">
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            PEC
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['PEC']).'" class="form-control frm_field_string" type="text" name="PEC" id="PEC">
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace16"></div>
                                
                    <div id="DIV_LicenseData" style="display:'.($Typology != "D" ? "block" : "none").';">
					<div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                            <strong>DATI PATENTE</strong>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Categoria
                        </div>
                        <div class="col-sm-1 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['LicenseCategory']).'" class="form-control frm_field_string" type="text" name="LicenseCategory">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Numero
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['LicenseNumber']).'" class="form-control frm_field_string" type="text" name="LicenseNumber" style="width:12rem">
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            '. CreateSelect("Country", "1=1", "Title", "LicenseCountryId", "Id", "Title", ($r_Trespasser['DocumentCountryId'] != "" ? $r_Trespasser['DocumentCountryId'] : "Z000"), false) .'
                        </div>
					</div>
                                
                    <div class="clean_row HSpace4"></div>
                                
					<div class="col-sm-12">
                        <div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Data rilascio
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input value="'.DateOutDB($r_Trespasser['LicenseDate']).'" class="form-control frm_field_date" type="text" name="LicenseDate">
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Ente rilascio
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['LicenseOffice']).'" class="form-control frm_field_string" type="text" name="LicenseOffice">
                        </div>
                        <div class="col-sm-2 BoxRowHTitle"></div>
					</div>
                                
					<div class="clean_row HSpace16"></div>
                                
					<div class="col-sm-12">
                       	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;font-size: 1rem;">
                            <strong>DOCUMENTO DI IDENTITÀ</strong>
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Tipo di documento
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <select class="form-control" name="DocumentTypeId2">
                        		<option value="2"'.($r_Trespasser['DocumentTypeId2'] == 2 ? " selected" : "").'>Carta d\'identità</option>
                                <option value="3"'.($r_Trespasser['DocumentTypeId2'] == 3 ? " selected" : "").'>Passaporto</option>
                                <option value="4"'.($r_Trespasser['DocumentTypeId2'] == 4 ? " selected" : "").'>Altro</option>
                            </select>
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Nazione
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '. CreateSelect(MAIN_DB . ".Country", "1=1", "Title", "DocumentCountryId2", "Id", "Title", $r_Trespasser['DocumentCountryId2'], false, 15) .'
                        </div>
					</div>
                                
					<div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            N°
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['DocumentNumber']).'" class="text-uppercase form-control frm_field_string" type="text" name="DocumentNumber">
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Rilasciato da
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input value="'.($showDocumentCityInput ? StringOutDB($r_Trespasser['DocumentOffice']) : "").'" id="DocumentOfficeInput" class="text-uppercase form-control frm_field_string" type="text" name="DocumentOfficeInput" style="display:'.($showDocumentCityInput ? "block" : "none").'">
                            <select id="ForeignDocumentOfficeSelect" class="form-control" name="ForeignDocumentOfficeSelect" style="display:'.($showForeignDocumentCity ? "block" : "none").'">
                                <option></option>';
                $rs_ForeignCity = $rs->Select("ForeignCity", 'CountryId="'.$r_Trespasser['DocumentCountryId2'].'"');
                while ($r_ForeignCity = mysqli_fetch_array($rs_ForeignCity)){
                    $str_TrespasserOut .= (StringOutDB($r_Trespasser['DocumentOffice']) == StringOutDB(strtoupper($r_ForeignCity['Title'])))
                    ? '<option selected value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>'
                        : '<option value='.$r_ForeignCity['Id'].'>'.StringOutDB($r_ForeignCity['Title']).'</option>';
                }
                $str_TrespasserOut .='</select>
                            <select id="DocumentOfficeSelect" class="form-control" name="DocumentOfficeSelect" style="display:'.(!$showDocumentCityInput && !$showForeignDocumentCity ? "block" : "none").'">
                                 <option></option>';
                mysqli_data_seek($rs_city, 0);
                while($r_City = mysqli_fetch_array($rs_city)) {
                    $str_TrespasserOut .= (strtoupper($r_City['Title']) == $r_Trespasser['DocumentOffice'])
                    ? '<option selected value='.$r_City['Id'].'>'.$r_City['Title'].'</option>'
                        : '<option value='.$r_City['Id'].'>'.$r_City['Title'].'</option>';
                }
                $str_TrespasserOut.= '</select>
                        </div>
                        <div class="col-sm-1 BoxRowHTitle"></div>
                    </div>
                    
					<div class="clean_row HSpace4"></div>
                    
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            In data
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input value="'.DateOutDB($r_Trespasser['DocumentValidFrom']).'" class="form-control frm_field_date" type="text" name="DocumentValidFrom">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Valido fino al
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input value="'.DateOutDB($r_Trespasser['DocumentValidTo']).'" class="form-control frm_field_date" type="text" name="DocumentValidTo">
                        </div>
                        <div class="col-sm-3 BoxRowHTitle"></div>
                    </div>
					<div class="clean_row HSpace16"></div>
					</div>
                                
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel text-center" style="background-color: #294A9C;">
                    		<strong>DATI CONTATTO</strong>
                    	</div>
                        <div class="col-sm-2 BoxRowLabel">
                            Mail
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['Mail']).'" class="form-control frm_field_string" type="text" name="Mail">
                        </div>
                        <div class="col-sm-5 BoxRowHTitle"></div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;"></div>
                        <div class="col-sm-2 BoxRowLabel">
                            Fax
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['Fax']).'" class="form-control frm_field_string" type="text" name="Fax">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Telefono
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['Phone']).'" class="form-control frm_field_string" type="text" name="Phone">
                        </div>
                        <div class="col-sm-1 BoxRowLabel">
                            Telefono 2
                        </div>
                        <div class="col-sm-2 BoxRowCaption">
                            <input value="'.StringOutDB($r_Trespasser['Phone2']).'" class="form-control frm_field_string" type="text" name="Phone2">
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-12">
                    	<div class="col-sm-2 BoxRowLabel" style="background-color: #294A9C;height: 6.4rem;"></div>
                        <div class="col-sm-2 BoxRowLabel" style="height: 6.4rem;">
                            Annotazioni
                        </div>
                        <div class="col-sm-8 BoxRowCaption" style="height: 6.4rem;">
                            <textarea class="form-control frm_field_string" type="text" style="font-weight: bold;height: 5.4rem;" name="Notes">'.StringOutDB($r_Trespasser['Notes']).'</textarea>
                        </div>
                    </div>
                                
                </div>
            </div>
                                
            <!-- TAB FORWARDING -->
            <div class="tab-pane" id="Forwarding">
                <input type="hidden" id="ForwardingNumber" value="'.$n_Forwarding.'" name="ForwardingNumber">
                <div class="col-sm-12">
                    <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                        <div class="col-sm-11" style="font-size: 1.2rem;">
                            '. TRESPASSER_CONTACT_INFO .'
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRow" style="height:4rem;line-height: 4rem;text-align:center;">
                        Aggiungi recapito
                        <i class="fa fa-caret-up" style="position:absolute;top:3px;right: 10px;font-size: 2rem;display:none" id="forwardingUp"></i>
                        <i class="fa fa-caret-down" style="position:absolute;bottom:3px;right: 10px;font-size: 2rem;" id="forwardingDown"></i>
                    </div>
                </div>
                '. $str_TabForwarding .'
            </div>
                    
            <!-- TAB DOMICILE -->
            <div class="tab-pane" id="Domicile">
            <input type="hidden" id="DomicileNumber" value="'.$n_Domicile.'" name="DomicileNumber">
            <div class="col-sm-12">
                <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                    <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                    <div class="col-sm-11" style="font-size: 1.2rem;">
                        '. TRESPASSER_CONTACT_INFO .'
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:4rem;line-height: 4rem;text-align:center;">
                    Aggiungi Domicilio
                    <i class="fa fa-caret-up" style="position:absolute;top:3px;right: 10px;font-size: 2rem;display:none" id="domicileUp"></i>
                    <i class="fa fa-caret-down" style="position:absolute;bottom:3px;right: 10px;font-size: 2rem;" id="domicileDown"></i>
                </div>
            </div>
                '. $str_TabDomicile .'
            </div>
                    
            <!-- TAB DWELLING -->
            <div class="tab-pane" id="Dwelling">
            <input type="hidden" id="DwellingNumber" value="'.$n_Dwelling.'" name="DwellingNumber">
            <div class="col-sm-12">
                <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                    <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                    <div class="col-sm-11" style="font-size: 1.2rem;">
                        '. TRESPASSER_CONTACT_INFO .'
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="col-sm-12 BoxRow" style="height:4rem;line-height: 4rem;text-align:center;">
                    Aggiungi Dimora
                    <i class="fa fa-caret-up" style="position:absolute;top:3px;right: 10px;font-size: 2rem;display:none" id="dwellingUp"></i>
                    <i class="fa fa-caret-down" style="position:absolute;bottom:3px;right: 10px;font-size: 2rem;" id="dwellingDown"></i>
                </div>
            </div>
                '. $str_TabDwelling .'
            </div>
        </div>
                    
        <div class="clean_row HSpace16"></div>
                    
        <div class="col-sm-12 alert alert-warning" id="errorcf" style="display:none;">
        	Il C.F esiste già nel database
        </div>
        <div class="col-sm-12 alert alert-warning" id="errorpiva" style="display:none;">
        	La P.IVA esiste già nel database
        </div>
        ';
    } else {
        $str_TrespasserOut = '
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRowTitle" style="text-align:center">
                TRASGRESSORE NON ASSOCIATO
            </div>
        </div>';
    }
    
    return $str_TrespasserOut;
    
}

//Se ditta controlla la P.IVA, se P.IVA è vuota controlla C.F, se anche C.F è vuoto ritorna FALSO
//Se persona controlla C.F, se C.F è vuoto ritorna FALSO
function PickVatORTaxCode($genre, $vatcode, $taxcode) {
    if ($genre == "D"){
        if (empty($vatcode)){
            if (empty($taxcode)){
                return null;
            } else return $taxcode;
        } else return $vatcode;
    } else {
        if (empty($taxcode)){
            return null;
        } else return $taxcode;
    }
}