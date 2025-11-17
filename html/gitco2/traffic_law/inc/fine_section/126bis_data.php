<?php
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////
///
///                         126 Bis
///
//////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////

$str_126Bis_data = '';
$str_CSS126Bis = 'data-toggle="tab"';

if($b_126bis){
    $rs_Trespasser = $rs->Select('V_TrespasserCommunication', "FineId=" . $Id); //tolte le condizioni sul tipo di trasgressore caricato che erano
    
    if (mysqli_num_rows($rs_Trespasser) > 0){
        $NotificationType = array(
            1 => 'Su strada',
            2 => 'Messo',
            3 => 'Ufficio'
        );
        
        $a_Genre = array(
            'M' => array('Icon' => 'fa-user', 'Tooltip' => 'Persona fisica'),
            'F' => array('Icon' => 'fa-user', 'Tooltip' => 'Persona fisica'),
            'D' => array('Icon' => 'fa-building', 'Tooltip' => 'Ditta'),
            'DI' => array('Icon' => 'fa-user-tie', 'Tooltip' => 'Ditta individuale'),
        );
        
        $a_LegalFormIndividual = unserialize(LEGALFORM_INDIVIDUALCOMPANY);
        $a_IncompleteCommunication = unserialize(INCOMPLETE_COMMUNICATION);
        
        $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
        
        $Genre = in_array($r_Trespasser['LegalFormId'], $a_LegalFormIndividual) ? 'DI' : $r_Trespasser['Genre'];
        $GenreDriver = in_array($r_Trespasser['LegalFormIdDriver'], $a_LegalFormIndividual) ? 'DI' : $r_Trespasser['GenreDriver'];
        
        $rs_FineNotification = $rs->SelectQuery("SELECT * FROM FineNotification FN JOIN Result R ON FN.ResultId = R.Id WHERE FineId=$Id");
        $r_FineNotification = mysqli_fetch_array($rs_FineNotification);
        
        $LicenseDate = "";
        if($r_Trespasser['LicenseDate']!="" OR !is_null($r_Trespasser['LicenseDate']))
            $LicenseDate = DateOutDB($r_Trespasser['LicenseDate']);
            
            $LicenseDateDriver = "";
            if($r_Trespasser['LicenseDateDriver']!="" OR !is_null($r_Trespasser['LicenseDateDriver']))
                $LicenseDateDriver = DateOutDB($r_Trespasser['LicenseDateDriver']);
                
                $CommunicationDate = "";
                if($r_Trespasser['CommunicationDate']!="" OR !is_null($r_Trespasser['CommunicationDate']))
                    $CommunicationDate = DateOutDB($r_Trespasser['CommunicationDate']);
                    
                    $documentType = "";
                    $rs_TrespasserDocType = $rs->Select('TrespasserDocumentType', 'Id='.$r_Trespasser['DocumentTypeId']);
                    if(mysqli_num_rows($rs_TrespasserDocType) > 0){
                        $documentType = mysqli_fetch_assoc($rs_TrespasserDocType)['Title'];
                    }
                    
                    $ownerType = '';
                    switch($r_Trespasser['OwnerTypeId']){
                        case 1:
                            $ownerType = "Proprietario/Trasgressore";
                            $Incomplete = $r_Trespasser['Incomplete'] ?? 0;
                            break;
                        case 2:
                            $ownerType = "Obbligato";
                            $Incomplete = $r_Trespasser['DriverIncomplete'] ?? 0;
                            break;
                    }
                    
                    
                    
                    $str_126Bis_data .= '
                    <div class="col-sm-3 BoxRowLabel">
                        Data comunicazione
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        ' . $CommunicationDate . '
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Protocollo n°
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        ' . $r_Trespasser['CommunicationProtocol'] . '
                    </div>
                            
                    <div class="clean_row HSpace4"></div>
                            
                    <div class="col-sm-3 BoxRowLabel"'.($Incomplete > 0 ? ' style="height:6.3rem"' : '').'>
                        Dati incompleti
                    </div>
                    <div class="col-sm-1 BoxRowCaption"'.($Incomplete > 0 ? ' style="height:6.3rem"' : '').'>
                        '.CheckbuttonOutDB($Incomplete > 0 ? '1' : '0').'
                    </div>
                    <div class="col-sm-8 BoxRowCaption"'.($Incomplete > 0 ? ' style="height:6.3rem"' : '').'>
                        '.($Incomplete > 0 ? $a_IncompleteCommunication[$Incomplete] : '').'
                    </div>
                            
                    <div class="clean_row HSpace16"></div>
                            
                    <div class="alert alert-info col-sm-12">
                        <div class="col-sm-6 BoxRowLabel" style="background-color:#294A9C;">
                            PROPRIETARIO:
                        </div>
                        <div class="col-sm-6 table_caption_H text-center">
                            <div class="col-sm-11">
                            ' . (!empty($r_Trespasser['CompanyName']) ? $r_Trespasser['CompanyName'].' ' : '') . $r_Trespasser['Surname'] . ' '. $r_Trespasser['Name'] . '
                            </div>
                            <div class="col-sm-1">
                                <i data-toggle="tooltip" data-container="body" data-placement="top" title="'.$a_Genre[$Genre]['Tooltip'].'"  class="tooltip-r fas '.$a_Genre[$Genre]['Icon'].'" style="font-size:1.8rem;margin-top:0.2rem"></i>
                            </div>
                        </div>
                                    
                        <div class="clean_row HSpace4"></div>';
                    
                    if($r_Trespasser['Genre'] != 'D' || ($r_Trespasser['Genre'] == 'D' && in_array($r_Trespasser['LegalFormId'], $a_LegalFormIndividual))){
                        $str_126Bis_data.= '
                            
                        <div class="clean_row HSpace4"></div>
                            
                        <div class="col-sm-2 BoxRowLabel">
                            Documento
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            ' . $documentType . '
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Stato rilascio
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            ' . $r_Trespasser['DocumentCountryTitle'] . '
                        </div>
                                
                        <div class="clean_row HSpace4"></div>';
                    }
                    $str_126Bis_data.= '
                        <div class="col-sm-2 BoxRowLabel">
                            Tipo veicolo
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            '.$rs->getArrayLine($rs->Select('VehicleType', "Id=".$r_Trespasser['VehicleTypeId']))['TitleIta'].'
                        </div>
                        <div class="col-sm-6 BoxRowLabel">
                        </div>
                                
                        <div class="clean_row HSpace4"></div>
                                
                        <div class="col-sm-12 BoxRow">
                            DATI NOTIFICA:
                        </div>
                                
                        <div class="clean_row HSpace4"></div>
                                
                        <div class="col-sm-3 BoxRowLabel">
                            Modalità
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.$NotificationType[$r_Trespasser['FineNotificationType']].'
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Data notifica
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.DateOutDB($r_FineNotification['NotificationDate']).'
                        </div>
                                
                        <div class="clean_row HSpace4"></div>
                                
                        <div class="col-sm-3 BoxRowLabel">
                            Raccomandata
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.$r_FineNotification['LetterNumber'].'
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Ricevuta
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.$r_FineNotification['ReceiptNumber'].'
                        </div>
                                
                        <div class="clean_row HSpace4"></div>
                                
                        <div class="col-sm-3 BoxRowLabel">
                            Data spedizione
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.DateOutDB($r_FineNotification['SendDate']).'
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Data log
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.DateOutDB($r_FineNotification['LogDate']).'
                        </div>
                                
                        <div class="clean_row HSpace4"></div>
                                
                        <div class="col-sm-3 BoxRowLabel">
                            Esito
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            '.$r_FineNotification['Title'].'
                        </div>
                        <div class="col-sm-6 BoxRowLabel">
                        </div>';
                    
                    if($r_Trespasser['Genre'] != 'D' || ($r_Trespasser['Genre'] == 'D' && in_array($r_Trespasser['LegalFormId'], $a_LegalFormIndividual))){
                        $str_126Bis_data.= '
                        <div class="clean_row HSpace4"></div>
                            
                        <div class="col-sm-12 BoxRow">
                            DATI PATENTE:
                        </div>
                            
                        <div class="clean_row HSpace4"></div>
                            
                        <div class="col-sm-2 BoxRowLabel">
                            Data rilascio
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            ' . $LicenseDate . '
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Ente rilascio
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            ' . $r_Trespasser['LicenseOffice'] . '
                        </div>
                                
                        <div class="clean_row HSpace4"></div>
                                
                        <div class="col-sm-2 BoxRowLabel">
                            Categoria
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            ' . $r_Trespasser['LicenseCategory'] . '
                        </div>
                        <div class="col-sm-2 BoxRowLabel">
                            Numero
                        </div>
                        <div class="col-sm-4 BoxRowCaption">
                            ' . $r_Trespasser['LicenseNumber'] . '
                        </div>';
                    }
                    $str_126Bis_data.= '
                    </div>';
                    
                    if ($r_Trespasser['OwnerTypeId'] == 2)
                    {
                        
                        $str_126Bis_data .= '<div class="alert alert-info col-sm-12">
                            
                    <div class="col-sm-6 BoxRowLabel" style="background-color:#294A9C;">
                        TRASGRESSORE:
                    </div>
                    <div class="col-sm-6 table_caption_H text-center">
                        <div class="col-sm-11">
                            <span id="span_name">' . (! empty($r_Trespasser['CompanyNameDriver']) ? $r_Trespasser['CompanyNameDriver'] . ' ' : '') . $r_Trespasser['SurnameDriver'] . ' ' . $r_Trespasser['NameDriver'] . '</span>
                        </div>
                        <div class="col-sm-1">
                            <span id="span_genre"><i data-toggle="tooltip" data-container="body" data-placement="top" title="'.$a_Genre[$GenreDriver]['Tooltip'].'"  class="tooltip-r fas '.$a_Genre[$GenreDriver]['Icon'].'" style="font-size:1.8rem;margin-top:0.2rem"></i></span>
                        </div>
                    </div>
                                
                    <div class="clean_row HSpace4"></div>
                                
                    <div class="col-sm-3 BoxRowLabel">
                        Documento
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        Patente
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Stato rilascio
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        ' . $r_Trespasser['DocumentCountryTitleDriver'] . '
                    </div>
                            
                    <div class="clean_row HSpace4"></div>
                            
                    <div class="col-sm-12 BoxRowLabel">
                        DATI PATENTE:
                    </div>
                            
                    <div class="clean_row HSpace4"></div>
                            
                    <div class="col-sm-2 BoxRowLabel">
                        Data rilascio
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        ' . $LicenseDateDriver . '
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Ente rilascio
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        ' . $r_Trespasser['LicenseOfficeDriver'] . '
                    </div>
                            
                    <div class="clean_row HSpace4"></div>
                            
                    <div class="col-sm-2 BoxRowLabel">
                        Categoria
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        ' . $r_Trespasser['LicenseCategoryDriver'] . '
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Numero
                    </div>
                    <div class="col-sm-4 BoxRowCaption">
                        ' . $r_Trespasser['LicenseNumberDriver'] . '
                    </div>
                </div>';
                    }
                    
    } else $str_CSS126Bis = ' style="color:#C43A3A; cursor:not-allowed;" ';
    
    $str_126Bis_data = '
    <div class="tab-pane" id="126Bis">
        <div class="col-sm-12">
            '. $str_126Bis_data .'
        </div>
    </div>
    ';
}


