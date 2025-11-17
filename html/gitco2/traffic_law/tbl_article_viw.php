<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(INC . "/function.php");
include(INC . "/header.php");

require(INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

//TODO rimuovere quando tutte le tabelle saranno convertite in utf8mb4
$rs->SetCharset('utf8mb4');

$BackPage = strtok($str_BackPage, '?');

$a_Lan = unserialize(LANGUAGE_KEYS);
$a_UseAdditionalSanction = unserialize(ARTICLETARIFF_USEADDITIONALSANCTION);

$Id = CheckValue('Id', 'n');
$Year = CheckValue('Year', 'n');


$rs_Article = $rs->Select('V_Article', "Id=$Id AND Year=$Year");
$r_Article = mysqli_fetch_array($rs_Article);

if($r_Article['LicensePointCode1'] > 0){
    $LicensePointCode1P1 = trim(substr($r_Article['LicensePointCode1'],0,4));
    $LicensePointCode1P2 = trim(substr($r_Article['LicensePointCode1'],4,2));
    $LicensePointCode1P3 = trim(substr($r_Article['LicensePointCode1'],6,2));
} else {
    $LicensePointCode1P1 = $LicensePointCode1P2 = $LicensePointCode1P3 = '';
}

if($r_Article['LicensePointCode2'] > 0){
    $LicensePointCode2P1 = trim(substr($r_Article['LicensePointCode2'],0,4));
    $LicensePointCode2P2 = trim(substr($r_Article['LicensePointCode2'],4,2));
    $LicensePointCode2P3 = trim(substr($r_Article['LicensePointCode2'],6,2));
} else {
    $LicensePointCode2P1 = $LicensePointCode2P2 = $LicensePointCode2P3 = '';
}


$str_ArticleDescription = '';
$LangN = 0;

foreach ($a_Lan as $name => $tag){
    if ($tag != 'Ita'){
        $LangN ++;
        $str_ArticleDescription .= '
        <div class="col-sm-2 BoxRowLabel" style="height:6.4rem">
            <img src="' . IMG . '/f_' . strtolower($tag) . '.png" style="width:16px" alt="' . $tag . '" /> ' . $name . '
        </div>
        <div class="col-sm-4 BoxRowCaption" style="height:6.4rem">
            ' . $r_Article['ArticleDescription' . $tag] . '
        </div>';
        if($LangN % 2 == 0) $str_ArticleDescription .= '<div class="clean_row HSpace4"></div>';
    }
    
}


$rs_Customer = $rs->Select('Customer', "CityId='" . $r_Article['CityId'] . "'");
$r_Customer = mysqli_fetch_array($rs_Customer);


$str_out .= '
    <div class="col-sm-12">
        <div class="col-sm-12 table_label_H" style="text-align:center">
            Visualizzazione articolo
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-12">
            <div class="col-sm-12 alert alert-danger" style="display: flex;margin: 0px;align-items: center;">
                <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                <div class="col-sm-11" style="font-size: 1.2rem;">
                    <ul style="list-style-position: inside;">
                        <li>I campi con dicitura "Ente" sono campi per specificare varianti particolari degli articoli specifiche dell\'ente (usati anche nelle importazioni)</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-12 table_caption_I BoxRowLabel" style="text-align:center">
            Parametri
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-1 BoxRowLabel">
            Ente
        </div>
        <div class="col-sm-3 BoxRowCaption" style="text-overflow: ellipsis;overflow: hidden;white-space: nowrap;">
            ' . $r_Customer['ManagerName'] . '
        </div>
        <div class="col-sm-2 BoxRowLabel">
            Particella verbale
        </div>
        <div class="col-sm-1 BoxRowCaption">
            '.$r_Article['ArticleLetterAssigned'].'
        </div>            
        <div class="col-sm-1 BoxRowLabel">
            Categoria
        </div>
        <div class="col-sm-2 BoxRowCaption">
            '.$r_Article['ViolationTitle'].'
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Anno
        </div>
        <div class="col-sm-1 BoxRowCaption">
            ' . $r_Article['Year'] . '
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel" style="border-right: 1px solid #E7E7E7;">
            Articolo:                    
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Art.
        </div>    
        <div class="col-sm-1 BoxRowCaption">
            ' . $r_Article['Article'] . '
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Comma
        </div>                        
        <div class="col-sm-1 BoxRowCaption">
            ' . $r_Article['Paragraph'] . '
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Lettera
        </div>     
        <div class="col-sm-1 BoxRowCaption">
            ' . $r_Article['Letter'] . '
        </div>';
//TODO BUG 3239 rimosso in quanto sviluppo annullato
//         <div class="col-sm-1 BoxRowLabel">
//             Tipo veicolo
//         </div>
//         <div class="col-sm-1 BoxRowCaption">
//             ' . $r_Article['VehicleTypeTitle'] . '
//         </div> 

$str_out .= '
        <div class="col-sm-2 BoxRowLabel">
        </div>     
        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel table_caption_error" style="border-right: 1px solid #E7E7E7;">
            Articolo Ente:                   
        </div>
        <div class="col-sm-1 BoxRowLabel table_caption_error">
            Art.
        </div>  
        <div class="col-sm-1 BoxRowCaption">
            ' . $r_Article['Id1'] . ' 
        </div>
        <div class="col-sm-1 BoxRowLabel table_caption_error">
            Comma
        </div>                             
        <div class="col-sm-1 BoxRowCaption">
            ' . $r_Article['Id2'] . '
        </div>
        <div class="col-sm-1 BoxRowLabel table_caption_error">
            Lettera
        </div>         
        <div class="col-sm-1 BoxRowCaption">
            ' . $r_Article['Id3'] . '
        </div>  
        <div class="col-sm-1 BoxRowLabel table_caption_error">
            Codice ente
        </div>
        <div class="col-sm-1 BoxRowCaption">
            ' . $r_Article['ArtComune'] . '
        </div>


        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel">
            Importo minimo
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . number_format($r_Article['Fee'], 2, '.', '').' €' . '
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Importo massimo
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . number_format($r_Article['MaxFee'], 2, '.', '').' €' . '
        </div>                       

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel">
            Decurtazione
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . $r_Article['LicensePoint'] . '
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Decurtazione neopatentati
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . $r_Article['YoungLicensePoint'] . '
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-3 BoxRowLabel">
            Presentazione documenti (180/8) Entro:
        </div>
        <div class="col-sm-1 BoxRowCaption BoxRowLabel">
            '.($r_Article['PresentationDocument'] > 0 ? CheckbuttonOutDB($r_Article['PresentationDocument']).' giorni' : '').'
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['PresentationDocument']) . '
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel">
            Applica art. 126 bis
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['126Bis']) . '
        </div> 
        <div class="col-sm-4 BoxRowLabel">
            Add.le massa (Sup. 3,5 ton)
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['AdditionalMass']) . '
        </div>

        <div id="DIV_LicensePointCode1"'.(($r_Article['LicensePointCode1'] > 0 || $r_Article['LicensePoint'] > 0 || $r_Article['YoungLicensePoint'] > 0) && $r_Article['126Bis'] > 0 ? '' : ' style="display:none;"').'>
            
            <div class="clean_row HSpace4"></div>

            <div class="col-sm-4 BoxRowLabel">
                Codice decurtazione punti
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.$LicensePointCode1P1.' '.$LicensePointCode1P2.' '.$LicensePointCode1P3.'
            </div>
        </div>
        <div id="DIV_LicensePointCode2"'.(($r_Article['LicensePointCode1'] > 0 || $r_Article['LicensePoint'] > 0 || $r_Article['YoungLicensePoint'] > 0) && $r_Article['Habitual'] && $r_Article['126Bis'] ? '' : ' style="display:none;"').'>
            <div class="col-sm-4 BoxRowLabel">
                Codice decurtazione punti (recidiva)
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '.$LicensePointCode2P1.' '.$LicensePointCode2P2.' '.$LicensePointCode2P3.'
            </div>
        </div>

        <div class="clean_row HSpace4"></div>
                  
        <div class="col-sm-4 BoxRowLabel">
            Add.le notte (22:01 – 6:59)
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['AdditionalNight']) . '
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Sanzione fissata da Prefettura
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['PrefectureFixed']) . '
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel">
            Riduzione sanzione del 30% in caso di pagamento entro 5 giorni
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['ReducedPayment']) . '
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Gestisci la preventiva trasmissione del verbale tramite posta ordinaria
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['Amicable']) . '
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel">
            Sanzione a carattere penale
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['PenalSanction']) . '
        </div>
        <div class="col-sm-6 BoxRowLabel">
        </div>

        <div class="clean_row HSpace4"></div>
        <div>
        <div class="col-sm-3 BoxRowLabel">
            Sanzione accessoria / Provvedimento amministrativo:
        </div>
        <div class="col-sm-1 BoxRowLabel">
            ' . $a_UseAdditionalSanction[$r_Article['UseAdditionalSanction']] . '
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:auto;min-height:2.2rem;">
            ' . $r_Article['AdditionalSanctionTitleIta'] . '
        </div>
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-6 table_caption_I BoxRowLabel" style="text-align:center">
            Sanzione accessoria
        </div>
        <div class="col-sm-6 table_caption_I BoxRowLabel" style="text-align:center">
            Provvedimento Amministrativo
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel">
            Sospensione della patente - Art. 218 C.d.S.
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['SuspensionLicense']) . '
        </div>

        <div class="col-sm-4 BoxRowLabel">
            Revisione della patente - Art. 128 C.d.S.
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['RevisionLicense']) . '
        </div>

        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-4 BoxRowLabel">
            Sospensione della patente in caso di recidiva nel biennio
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['Habitual']) . '
        </div>
        
        <div class="col-sm-4 BoxRowLabel">
            Revisione della patente in caso di recidiva nel biennio
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['RevisionHabitual']) . '
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel">
            Ritiro della patente - Art. 233 C.d.S.
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['LossLicense']) . '
        </div> 
        
        <div class="col-sm-4 BoxRowLabel">
            Revoca della patente - Art. 219 C.d.S.
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['RevocationLicense']) . '
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-4 BoxRowLabel">
            Ritiro della patente in caso di recidiva nel biennio
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['LossHabitual']) . '
        </div>

        <div class="col-sm-4 BoxRowLabel">
            Revoca della patente in caso di recidiva nel biennio
        </div>
        <div class="col-sm-2 BoxRowCaption">
            ' . CheckbuttonOutDB($r_Article['RevocationHabitual']) . '
        </div>
        
        <div class="clean_row HSpace4"></div>

        <div class="col-sm-12 table_caption_I BoxRowLabel" style="text-align:center">
            Testi (Italiano)
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel" style="height:6.4rem">
            <img src="' . IMG . '/f_' . strtolower($a_Lan['Italiano']) . '.png" style="width:16px" alt="' . $a_Lan['Italiano'] . '" /> Italiano (default)' . '
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:6.4rem">
            ' . $r_Article['ArticleDescription' . $a_Lan['Italiano']] . '
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel" style="height:6.4rem">
            Testo addizionale
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:6.4rem">
            ' . StringOutDB($r_Article['AdditionalTextIta']) . '
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-4 BoxRowLabel" style="height:6.4rem">
            Note
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:6.4rem">
            ' . StringOutDB($r_Article['Note']) . '
        </div>

        <div class="clean_row HSpace4"></div>

        <div class="col-sm-12 table_caption_I BoxRowLabel" style="text-align:center">
            Testi (Estero)
        </div>

        <div class="clean_row HSpace4"></div>

        '. $str_ArticleDescription .'

        <div class="clean_row HSpace4"></div>

        <div class="table_label_H HSpace4" style="height:8rem;">
        	<button type="button" id="back" class="btn btn-default" style="margin-top:2rem;">Indietro</button>
        </div>
    </div>';

echo $str_out;
?>

<script type="text/javascript">
    $('#back').click(function () {
        window.location = "<?= $BackPage.$str_GET_Parameter ?>";
        return false;
    });
</script>

<?php
include(INC . "/footer.php");