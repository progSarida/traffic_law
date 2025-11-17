<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/views.php");
require_once(INC."/header.php");
require_once(INC."/menu_{$_SESSION['UserMenuType']}.php");

$CityId = $_SESSION['cityid'];

$ActiveTab = CheckValue('ActiveTab', 'n');
$n_TabIndex = 0; 

$rs_CustomerParameter = $rs->Select('V_CustomerParameter', "CityId='" . $CityId . "'");
$r_CustomerParameter = mysqli_fetch_array($rs_CustomerParameter);

$rs_CustomerService = $rs->Select('CustomerService', "ServiceId=6 AND CityId='$CityId'");
$r_CustomerService = $rs->getArrayLine($rs_CustomerService);

$rs_CustomerCharge = $rs->Select('CustomerCharge',"CityId='".$CityId."' AND CreationType=1 AND ToDate IS NULL");
$r_CustomerCharge = mysqli_fetch_array($rs_CustomerCharge);

$rs_CMA = $rs->Select('CustomerMailAuthentication', "ConfigType=1 AND CityId='" . $CityId . "'");
$r_CMA = mysqli_fetch_assoc($rs_CMA);

$a_JudicialOffice = array();
for ($i = 1; $i <= 3; $i ++)
{
    $a_JudicialOffice[$i]['City'] = $a_JudicialOffice[$i]['Province'] = $a_JudicialOffice[$i]['Address'] = $a_JudicialOffice[$i]['ZIP'] = $a_JudicialOffice[$i]['Phone'] = $a_JudicialOffice[$i]['Fax'] = $a_JudicialOffice[$i]['Mail'] = $a_JudicialOffice[$i]['PEC'] = $a_JudicialOffice[$i]['Web'] = $a_JudicialOffice[$i]['Disabled'] = "";
}
$rs_JudicialOffice = $rs->Select('JudicialOffice', "CityId='" . $CityId . "'");

while ($r_JudicialOffice = mysqli_fetch_assoc($rs_JudicialOffice))
{
    $a_JudicialOffice[$r_JudicialOffice['OfficeId']] = $r_JudicialOffice;
}

if ($r_CustomerParameter['PatronalFeast'] != ""){
    list($PatronalYear, $PatronalFeastMonth, $PatronalFeastDay) = explode('-', $r_CustomerParameter['PatronalFeast']);
} else $PatronalFeastMonth = $PatronalFeastDay = '';

$a_PatronalFeastMonths = array('','Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre');

$a_InstallmentRates = $rs->getResults($rs->Select("InstallmentRates", "CityId='{$_SESSION['cityid']}'", "ToDate IS NULL DESC, Todate DESC"));

$Tabs = array(
    array('Id' => 'CustomerAddress', 'Title' => 'Indirizzo'),
    array('Id' => 'CustomerFine', 'Title' => 'Verbale'),
    array('Id' => 'CustomerIniPEC', 'Title' => 'INI-PEC'),
    array('Id' => 'CustomerBank', 'Title' => 'Pagamenti'),
    array('Id' => 'CustomerPost', 'Title' => 'Posta'),
    array('Id' => 'CustomerPrinters', 'Title' => 'Stampatori predefiniti'),
    array('Id' => 'CustomerMCTC', 'Title' => 'MCTC'),
    array('Id' => 'CustomerPagoPA', 'Title' => 'PagoPA'),
    array('Id' => 'CustomerJudicialOffice', 'Title' => 'Contenzioso'),
    array('Id' => 'CustomerPrefectComm', 'Title' => 'Com. Prefetto'),
    array('Id' => 'CustomerInstallments', 'Title' => 'Rateizzazioni')
);

$a_ToReplace = array(
    "@CityId" => $CityId,
);

$cls_view = new CLS_VIEW(TBL_CUSTOMER_PRINTERPARAMETER);
$rs_PrinterParameter = $rs->SelectQuery(strtr($cls_view->generateSelect(), $a_ToReplace));
$a_PrinterParameter = $rs->getResults($rs_PrinterParameter);
$a_PrinterParameter = array_column($a_PrinterParameter, null, 'PrinterId');

$ActiveTabPrinter = CheckValue('ActiveTabPrinter','n') > 0 ? CheckValue('ActiveTabPrinter','n') : key($a_PrinterParameter);

echo $str_out;
?>

<div class="row-fluid">
    <form name="f_upd_customer" id="f_upd_customer" action="tbl_customer_upd_exe.php" method="post" autocomplete="off">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <input type="hidden" name="ActiveTab" id="ActiveTab" value="<?= $ActiveTab; ?>">
        <input type="hidden" name="ActiveTabPrinter" id="ActiveTabPrinter" value="<?= $ActiveTabPrinter; ?>">
        <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            Parametri di gestione
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12">
            <ul class="nav nav-tabs" id="CustomerTab">
                <?php foreach($Tabs as $key => $tab): ?>
                	<li data-tab_position="<?= $key; ?>" class="tab_button<?= $ActiveTab == $key ? ' active' : '' ?>" id="tab_<?= $tab['Id'] ?>"><a href="#<?= $tab['Id'] ?>" data-toggle="tab"><?= $tab['Title'] ?></a></li>
                <?php endforeach;?>
            </ul>
        </div>
    
        <div class="clean_row HSpace4"></div>
    
        <div class="tab-content">
    		<!-- Indirizzo -->
            <div id="<?= $Tabs[$n_TabIndex]['Id'] ?>" class="tab-pane<?= $ActiveTab == $n_TabIndex++ ? ' active' : '' ?>">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Intestazione ente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string frm_field_required" name="ManagerName" id="ManagerName" type="text" value="<?= $r_CustomerParameter['ManagerName']; ?>">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Settore
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ManagerSector" id="ManagerSector" type="text" value="<?= $r_CustomerParameter['ManagerSector']; ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Indirizzo
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_string frm_field_required" name="ManagerAddress" id="ManagerAddress" type="text" value="<?= StringOutDB($r_CustomerParameter['ManagerAddress']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        CAP
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string frm_field_required" name="ManagerZIP" id="ManagerZIP" type="text" value="<?= $r_CustomerParameter['ManagerZIP']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_string frm_field_required" name="ManagerCity" id="ManagerCity" type="text" value="<?= StringOutDB($r_CustomerParameter['ManagerCity']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Provincia
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string frm_field_required" name="ManagerProvince" id="ManagerProvince" type="text" value="<?= $r_CustomerParameter['ManagerProvince']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Stato
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string frm_field_required" name="ManagerCountry" id="ManagerCountry" type="text" value="<?= $r_CustomerParameter['ManagerCountry']; ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Telefono
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ManagerPhone" id="ManagerPhone" type="text" value="<?= $r_CustomerParameter['ManagerPhone']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Fax
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ManagerFax" id="ManagerFax" type="text" value="<?= $r_CustomerParameter['ManagerFax']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Email
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ManagerMail" id="ManagerMail" type="text" value="<?= $r_CustomerParameter['ManagerMail']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        PEC
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ManagerPEC" id="ManagerPEC" type="text" value="<?= $r_CustomerParameter['ManagerPEC']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Codice IPA
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input class="form-control frm_field_string" name="IpaCode" id="IpaCode" type="text" value="<?= $r_CustomerParameter['IpaCode']; ?>">
                    </div>
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Info addizionali
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input  class="form-control frm_field_string" name="ManagerInfo" id="ManagerInfo" type="text" value="<?= StringOutDB($r_CustomerParameter['ManagerInfo']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Sito web
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input  class="form-control frm_field_string" name="ManagerWeb" id="ManagerWeb" type="text" value="<?= $r_CustomerParameter['ManagerWeb']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Festa patronale
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        Giorno/Mese
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input  class="form-control" name="PatronalFeastDay" id="PatronalFeastDay" type="text" value="<?= $PatronalFeastDay; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                    	<?= CreateArraySelect($a_PatronalFeastMonths, true, 'PatronalFeastMonth', 'PatronalFeastMonth', ltrim($PatronalFeastMonth, '0'), true); ?>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel" style="height:6.4rem">
                        Informazioni ufficio
                    </div>
                    <div class="col-sm-11 BoxRowCaption" style="height:6.4rem">
                        <textarea class="form-control frm_field_string" name="ManagerOfficeInfo" style="height:5.8rem;margin-left:0;"><?= StringOutDB($r_CustomerParameter['ManagerOfficeInfo']); ?></textarea>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Codice fiscale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string text-uppercase" name="ManagerTaxCode" id="ManagerTaxCode" type="text" value="<?= $r_CustomerParameter['ManagerTaxCode']; ?>">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Partita IVA
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ManagerVAT" id="ManagerVAT" type="text" value="<?= $r_CustomerParameter['ManagerVAT']; ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Lettera Protocollo Atto CDS Nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="NationalProtocolLetterType1" id="NationalProtocolLetterType1" type="text" value="<?= $r_CustomerParameter['NationalProtocolLetterType1']; ?>">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Lettera Protocollo Atto CDS Estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ForeignProtocolLetterType1" id="ForeignProtocolLetterType1" type="text" value="<?= $r_CustomerParameter['ForeignProtocolLetterType1']; ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Lettera Protocollo Atto extra CDS Nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="NationalProtocolLetterType2" id="NationalProtocolLetterType2" type="text" value="<?= $r_CustomerParameter['NationalProtocolLetterType1']; ?>">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Lettera Protocollo Atto extra CDS Estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ForeignProtocolLetterType2" id="ForeignProtocolLetterType2" type="text" value="<?= $r_CustomerParameter['ForeignProtocolLetterType2']; ?>">
                    </div>
                </div>
            </div>
            
            <!-- Verbale -->
            <div id="<?= $Tabs[$n_TabIndex]['Id'] ?>" class="tab-pane<?= $ActiveTab == $n_TabIndex++ ? ' active' : '' ?>">
                <div class="col-sm-12">
                    <div class="col-sm-4 BoxRowLabel">
                        Responsabile del Procedimento
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ManagerProcessName" type="text" value="<?= $r_CustomerParameter['ManagerProcessName']; ?>" style="width:28rem">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-4 BoxRowLabel">
                        Responsabile immissione dati
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ManagerDataEntryName" type="text" value="<?= $r_CustomerParameter['ManagerDataEntryName']; ?>" style="width:28rem">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-4 BoxRowLabel">
                        Località firma verbale
                    </div>
                    <div class="col-sm-8 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ManagerSignName" type="text" value="<?= $r_CustomerParameter['ManagerSignName']; ?>" style="width:28rem">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Codice assegnato per richiesta dati enti esteri
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input  class="form-control frm_field_string" name="Reference" maxlength="10" type="text" value="<?= $r_CustomerParameter['Reference']; ?>" >
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Gestione firma elettronica
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input name="DigitalSignature" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['DigitalSignature']); ?>/>
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Protocollazione ente
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                       <input name="ExternalRegistration" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['ExternalRegistration']); ?>/>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Creazione file pdf con creazione verbali
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                       <input name="FinePDFList" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['FinePDFList']); ?>/>
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Elenco verbalizzanti
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input name="ChiefControllerList" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['ChiefControllerList']); ?>/>
                    </div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Validazione ente
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="checkbox" name="Validation" <?= ChkCheckButton($r_CustomerParameter['Validation']); ?>>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Spedizione preverbali
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                       <input name="RegularPostalFine" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['RegularPostalFine']); ?>/>
                    </div>
                    <div class="col-sm-8 BoxRowCaption"></div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                        <strong>PEC</strong>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-6 BoxRowLabel">
                        L'ente gestisce l'invio degli atti tramite PEC
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="ManagePEC" id="ManagePEC" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['ManagePEC']); ?>/>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                        <strong>FIRMA DIGITALE</strong>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-6 BoxRowLabel">
                        Abilita la firma digitale di tutti i verbali
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="EnableSignAll" id="EnableSignAll" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['EnableSignAll']); ?>/>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-6 BoxRowLabel">
                        Abilita la firma digitale del verbale da notificare tramite PEC
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input<?= ($r_CustomerParameter['ManagePEC'] ? '' : ' disabled'); ?> name="EnableINIPECDigitalSignature" id="EnableINIPECDigitalSignature" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['EnableINIPECDigitalSignature']); ?>/>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-6 BoxRowLabel">
                        Abilita la creazione e la firma digitale della relata di notifica dei verbali con contestuale invio degli atti tramite PEC
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input<?= ($r_CustomerParameter['ManagePEC'] ? '' : ' disabled'); ?> name="EnableINIPECNotification" id="EnableINIPECNotification" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['EnableINIPECNotification']); ?>/>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                        <strong>PROTOCOLLO</strong>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-6 BoxRowLabel">
                        Abilita invio al protocollo dei verbali firmati digitalmente
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input name="EnableINIPECProtocol" id="EnableINIPECProtocol" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['EnableINIPECProtocol']); ?>/>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-6 BoxRowLabel">
                        <em>Abilita invio al protocollo di tutti i verbali</em>
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input type="checkbox" disabled/>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                        <strong>ARCHIVIAZIONE SOSTITUTIVA</strong>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-6 BoxRowLabel">
                        <em>Abilita invio all’archiviazione sostitutiva dei verbali firmati digitalmente</em>
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input type="checkbox" disabled/>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-6 BoxRowLabel">
                        <em>Abilita invio all’archiviazione sostitutiva dei verbali NON firmati digitalmente</em>
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input type="checkbox" disabled/>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-6 BoxRowLabel">
                        <em>Abilita invio all’archiviazione sostitutiva di tutta la documentazione presente a programma</em>
                    </div>
                    <div class="col-sm-6 BoxRowCaption">
                        <input type="checkbox" disabled/>
                    </div>
    
                </div>
            </div>
    		
    		<!-- INI-PEC -->
            <div id="<?= $Tabs[$n_TabIndex]['Id'] ?>" class="tab-pane<?= $ActiveTab == $n_TabIndex++ ? ' active' : '' ?>">
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                        <strong>Banche dati PEC</strong>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-2 BoxRowLabel">
                        Username
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="frm_field_string form-control" name="INIPECUserName" id="INIPECUserName" type="text" value="<?= $r_CustomerParameter['INIPECUserName']; ?>"/>
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Password
                        <i data-targetfield="INIPECPassword" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi password" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                    </div>
                    <div class="col-sm-2 BoxRowCaption" id="INIPECPsw1">
                        <!-- <input class="frm_field_string form-control" name="INIPECPassword" id="INIPECPassword" type="password" autocomplete="off" value="<?= $r_CustomerParameter['INIPECPassword']; ?>"/> -->
                        <script>
                            passwordField("INIPECPsw1","INIPECPassword", "INIPECPassword",'<?= $r_CustomerParameter['INIPECPassword']; ?>',false);
                        </script>
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Scadenza
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="frm_field_date form-control" name="INIPECPasswordExpiration" id="INIPECPasswordExpiration" type="text" value="<?= DateOutDB($r_CustomerParameter['INIPECPasswordExpiration']); ?>"/>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
    		        <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                        <strong>Parametri server di posta</strong>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Indirizzo mail
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="MailAddress" id="MailAddress" type="text" class="form-control frm_field_string" value="<?= StringOutDB($r_CMA['MailAddress']); ?>">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Nome visualizzato
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ShownName" id="ShownName" type="text" class="form-control frm_field_string" value="<?= StringOutDB($r_CMA['ShownName']); ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
                    
                    <div class="col-sm-3 BoxRowLabel">
                        Usa l'indirizzo PEC dell'ente come indirizzo di risposta
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ReplyToManagerPEC" id="ReplyToManagerPEC" type="checkbox" <?= ChkCheckButton($r_CMA['ReplyToManagerPEC']); ?> value="1"/>
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Utilizza autenticazione per server di posta in uscita
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="UseOutgoingAuthentication" id="UseOutgoingAuthentication" type="checkbox" <?= ChkCheckButton($r_CMA['UseOutgoingAuthentication']); ?> value="1"/>
                    </div>
    
                    <div class="col-sm-6 BoxRow text-center" style="border-right: 1px solid #E7E7E7;">
                        In entrata
                    </div>
                    <div class="col-sm-6 BoxRow text-center">
                        In uscita
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Nome utente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="IncomingUserName" id="IncomingUserName" type="text" class="form-control frm_field_string" value="<?= StringOutDB($r_CMA['IncomingUserName']); ?>">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Nome utente
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="OutgoingUserName" id="OutgoingUserName" type="text" class="form-control frm_field_string" value="<?= StringOutDB($r_CMA['OutgoingUserName']); ?>"<?= ($r_CMA['UseOutgoingAuthentication'] == 1 ? '' : ' disabled'); ?>>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Password
                        <i data-targetfield="IncomingPassword" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi password" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                    </div>
                    <div class="col-sm-3 BoxRowCaption" id="INIPECPsw2">
                        <!-- <input name="IncomingPassword" id="IncomingPassword" type="password" autocomplete="off" class="form-control frm_field_string" value="<?= StringOutDB($r_CMA['IncomingPassword']); ?>"> -->
                        <script>
                            passwordField("INIPECPsw2","IncomingPassword", "IncomingPassword",'<?= StringOutDB($r_CMA['IncomingPassword']); ?>',false);
                        </script>
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Password
                        <i data-targetfield="OutgoingPassword" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi password" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                    </div>
                    <div class="col-sm-3 BoxRowCaption" id="INIPECPsw3">
                        <!-- <input name="OutgoingPassword" id="OutgoingPassword" type="password" autocomplete="off" class="form-control frm_field_string" value="<?= StringOutDB($r_CMA['OutgoingPassword']); ?>"<?= ($r_CMA['UseOutgoingAuthentication'] == 1 ? '' : ' disabled'); ?>> -->
                        <script>
                            passwordField("INIPECPsw3","OutgoingPassword", "OutgoingPassword",'<?= StringOutDB($r_CMA['OutgoingPassword']); ?>',<?= ($r_CMA['UseOutgoingAuthentication'] == 1 ? false : true); ?>);
                        </script>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Casella di posta in entrata ricevute
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="IncomingMailbox" id="IncomingMailbox" type="text" class="form-control frm_field_string" value="<?= StringOutDB(isset($r_CMA['IncomingMailbox']) ? $r_CMA['IncomingMailbox'] : 'INBOX'); ?>">
                    </div>
                    <div class="col-sm-6 BoxRowLabel"></div>
    
                </div>
            </div>
    		
    		<!-- Pagamenti -->
            <div id="<?= $Tabs[$n_TabIndex]['Id'] ?>" class="tab-pane<?= $ActiveTab == $n_TabIndex++ ? ' active' : '' ?>">
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        C/NAZIONALE
                    </div>
                    <div class="col-sm-6 BoxRowLabel" style="line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        C/ESTERO
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Nome Banca
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="NationalBankName" type="text" value="<?= $r_CustomerParameter['NationalBankName']; ?>" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Nome Banca
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ForeignBankName" type="text" value="<?= $r_CustomerParameter['ForeignBankName']; ?>" style="width:28rem">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Intestatario CC  ITA
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="NationalBankOwner" type="text" value="<?= $r_CustomerParameter['NationalBankOwner']; ?>" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Intestatario CC
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ForeignBankOwner" type="text" value="<?= $r_CustomerParameter['ForeignBankOwner']; ?>" style="width:28rem">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        IBAN
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="NationalBankIban" type="text" value="<?= $r_CustomerParameter['NationalBankIban']; ?>" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        IBAN
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ForeignBankIban" type="text" value="<?= $r_CustomerParameter['ForeignBankIban']; ?>" style="width:28rem">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        CC
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric" name="NationalBankAccount" type="text" value="<?= $r_CustomerParameter['NationalBankAccount']; ?>" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        CC
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric" name="ForeignBankAccount" type="text" value="<?= $r_CustomerParameter['ForeignBankAccount']; ?>" style="width:28rem">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        SWIFT
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="NationalBankSwift" type="text" value="<?= $r_CustomerParameter['NationalBankSwift']; ?>" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                         SWIFT
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ForeignBankSwift" type="text" value="<?= $r_CustomerParameter['ForeignBankSwift']; ?>" style="width:28rem">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        C/Sarida
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="NationalBankMgmt" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['NationalBankMgmt']); ?> />
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        C/Sarida
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="ForeignBankMgmt" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['ForeignBankMgmt']); ?> />
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Verbale con importi CAN CAD
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                          <input name="LumpSum" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['LumpSum']); ?> />
                    </div>
    
                    <div class="col-sm-2 BoxRowLabel">
                        Tipo assegnazione importi
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
    
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                        Codice quinto campo
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <input class="form-control txt-warning" id="FifthField" name="FifthField" type="text" value="<?= $r_CustomerParameter['FifthField']; ?>" >
                    </div>
    
                    <div class="col-sm-6 BoxRow" style="border-right: 1px solid #E7E7E7;text-align:center;">
                        Dati per solleciti NAZIONALE
                    </div>
                    <div class="col-sm-6 BoxRow" style="text-align:center;">
                        Dati per solleciti ESTERO
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Nome Banca
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="NationalReminderBankName" type="text" value="<?= $r_CustomerParameter['NationalReminderBankName']; ?>" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Nome Banca
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ForeignReminderBankName" type="text" value="<?= $r_CustomerParameter['ForeignReminderBankName']; ?>" style="width:28rem">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Intestatario CC  ITA
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="NationalReminderBankOwner" type="text" value="<?= $r_CustomerParameter['NationalReminderBankOwner']; ?>" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Intestatario CC
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ForeignReminderBankOwner" type="text" value="<?= $r_CustomerParameter['ForeignReminderBankOwner']; ?>" style="width:28rem">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        IBAN
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="NationalReminderBankIban" type="text" value="<?= $r_CustomerParameter['NationalReminderBankIban']; ?>" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        IBAN
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ForeignReminderBankIban" type="text" value="<?= $r_CustomerParameter['ForeignReminderBankIban']; ?>" style="width:28rem">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        CC
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric" name="NationalReminderBankAccount" type="text" value="<?= $r_CustomerParameter['NationalReminderBankAccount']; ?>" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        CC
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_numeric" name="ForeignReminderBankAccount" type="text" value="<?= $r_CustomerParameter['ForeignReminderBankAccount']; ?>" style="width:28rem">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        SWIFT
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="NationalReminderBankSwift" type="text" value="<?= $r_CustomerParameter['NationalReminderBankSwift']; ?>" style="width:28rem">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                         SWIFT
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="ForeignReminderBankSwift" type="text" value="<?= $r_CustomerParameter['ForeignReminderBankSwift']; ?>" style="width:28rem">
                    </div>
    
                </div>
            </div>
    		
    		<!-- Posta -->
            <div id="<?= $Tabs[$n_TabIndex]['Id'] ?>" class="tab-pane<?= $ActiveTab == $n_TabIndex++ ? ' active' : '' ?>">
                <div class="col-sm-12">
                	<div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                        <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                        <div class="col-sm-11" style="font-size: 1.2rem;">
                            <ul style="list-style-position: inside;">
                                <li>Restituzione piego in caso di mancato recapito (Verbali/Solleciti): Definisce l'indirizzo di restituzione del piego in caso di mancato recapito degli atti. Se valorizzato viene incluso nelle righe contenenti le informazioni dell'ente in alto a sinistra delle stampe degli atti.</li>
                                <li>Campi Mod 23: Definiscono le informazioni riguradanti il Modello 23L. Vengono inclusi, insieme ad altre informazioni, nei file di accompagnamento degli atti contenuti nei flussi.</li>
                                <li>Campi Sma: Definiscono le informazioni riguradanti le autorizzazioni Sma. Vengono inclusi, insieme ad altre informazioni, nei file di accompagnamento degli atti contenuti nei flussi.</li>
                            </ul>
                        </div>
                    </div>
                    <ul class="nav nav-tabs" id="PrintersTab">
                        <?php foreach($a_PrinterParameter as $printerId => $data): ?>
                        	<li data-tab_position="<?= $printerId; ?>" class="tab_button_printer<?= $ActiveTabPrinter == $printerId ? ' active' : '' ?>" id="tab_Printer<?= $printerId ?>"><a href="#Printer<?= $printerId ?>" data-toggle="tab"><?= $data['Name'] ?></a></li>
                        <?php endforeach;?>
                    </ul>
                    
                    <div class="clean_row HSpace4"></div>
                    
            		<div class="col-sm-12">
        				<div class="col-sm-6 BoxRowLabel text-center" style="border-right: 1px solid #fff;line-height: 4rem;height: 4rem; font-size:2rem;">
                       		<strong>Parametri spedizione NAZIONALE</strong>
                    	</div>
        				<div class="col-sm-6 BoxRowLabel text-center" style="border-right: 1px solid #fff;line-height: 4rem;height: 4rem; font-size:2rem;">
                       		<strong>Parametri spedizione ESTERO</strong>
                    	</div>
            		</div>
            		
            		<div class="clean_row HSpace4"></div>
                    
                    <div class="tab-content">
                    	<?php foreach($a_PrinterParameter as $printerId => $data): ?>
                    		<div class="tab-pane<?= $ActiveTabPrinter == $printerId ? ' active' : '' ?>" id="Printer<?= $printerId ?>">
                            	<div class="tab-content">
            	        			<div class="col-sm-12">
            	        				<div class="BoxRow col-sm-12 text-center" style="background-color: #294A9C;"><?= $data['Name'] ?></div>
            	        				
        	        					<div class="clean_row HSpace4"></div>
            	        				
                                    	<div class="col-sm-12 BoxRow" style="border-right: 1px solid #E7E7E7;text-align:center;">
                                        	VERBALI
                                        </div>
                                        
                                        <div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Restituzione piego in caso di mancato recapito
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalFineFoldReturn[<?= $printerId; ?>]" value="<?= $data['NationalFineFoldReturn']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Restituzione piego in caso di mancato recapito
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignFineFoldReturn[<?= $printerId; ?>]" value="<?= $data['ForeignFineFoldReturn']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                    	<div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Soggetto ente Mod 23
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalMod23LCustomerSubject[<?= $printerId; ?>]" value="<?= $data['NationalMod23LCustomerSubject']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Soggetto ente Mod 23
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignMod23LCustomerSubject[<?= $printerId; ?>]" value="<?= $data['ForeignMod23LCustomerSubject']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                    	<div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Indirizzo ente Mod 23
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalMod23LCustomerAddress[<?= $printerId; ?>]" value="<?= $data['NationalMod23LCustomerAddress']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Indirizzo ente Mod 23
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignMod23LCustomerAddress[<?= $printerId; ?>]" value="<?= $data['ForeignMod23LCustomerAddress']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                    	<div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Città ente Mod 23
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalMod23LCustomerCity[<?= $printerId; ?>]" value="<?= $data['NationalMod23LCustomerCity']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Città ente Mod 23
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignMod23LCustomerCity[<?= $printerId; ?>]" value="<?= $data['ForeignMod23LCustomerCity']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                    	<div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Nome Sma
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalSmaName[<?= $printerId; ?>]" value="<?= $data['NationalSmaName']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Nome Sma
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignSmaName[<?= $printerId; ?>]" value="<?= $data['ForeignSmaName']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                    	<div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Autorizzazione Sma
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalSmaAuthorization[<?= $printerId; ?>]" value="<?= $data['NationalSmaAuthorization']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Autorizzazione Sma
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignSmaAuthorization[<?= $printerId; ?>]" value="<?= $data['ForeignSmaAuthorization']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                    	<div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Spese Sma
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalSmaPayment[<?= $printerId; ?>]" value="<?= $data['NationalSmaPayment']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Spese Sma
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignSmaPayment[<?= $printerId; ?>]" value="<?= $data['ForeignSmaPayment']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                        <div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Autorizzazione alla stampa bollettino postale
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalPostalAuthorization[<?= $printerId; ?>]" value="<?= $data['NationalPostalAuthorization']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Autorizzazione alla stampa bollettino postale
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignPostalAuthorization[<?= $printerId; ?>]" value="<?= $data['ForeignPostalAuthorization']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                    	<div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Autorizzazione alla stampa bollettino postale PA su avviso di pagamento PagoPA
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalPostalAuthorizationPagoPA[<?= $printerId; ?>]" value="<?= $data['NationalPostalAuthorizationPagoPA']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Autorizzazione alla stampa bollettino postale PA su avviso di pagamento PagoPA
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignPostalAuthorizationPagoPA[<?= $printerId; ?>]" value="<?= $data['ForeignPostalAuthorizationPagoPA']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                    	<div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-12 BoxRow" style="border-right: 1px solid #E7E7E7;text-align:center;">
                                        	SOLLECITI
                                        </div>
                                        
                                        <div class="clean_row HSpace4"></div>
                                        
                                        <div class="col-sm-3 BoxRowLabel">
                                    		Restituzione piego in caso di mancato recapito
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalReminderFoldReturn[<?= $printerId; ?>]" value="<?= $data['NationalReminderFoldReturn']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Restituzione piego in caso di mancato recapito
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignReminderFoldReturn[<?= $printerId; ?>]" value="<?= $data['ForeignReminderFoldReturn']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                    	<div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Nome Sma
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalReminderSmaName[<?= $printerId; ?>]" value="<?= $data['NationalReminderSmaName']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Nome Sma
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignReminderSmaName[<?= $printerId; ?>]" value="<?= $data['ForeignReminderSmaName']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                    	<div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Autorizzazione Sma
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalReminderSmaAuthorization[<?= $printerId; ?>]" value="<?= $data['NationalReminderSmaAuthorization']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Autorizzazione Sma
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignReminderSmaAuthorization[<?= $printerId; ?>]" value="<?= $data['ForeignReminderSmaAuthorization']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                    	<div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Spese Sma
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalReminderSmaPayment[<?= $printerId; ?>]" value="<?= $data['NationalReminderSmaPayment']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Spese Sma
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignReminderSmaPayment[<?= $printerId; ?>]" value="<?= $data['ForeignReminderSmaPayment']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                        <div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Autorizzazione alla stampa bollettino postale
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalReminderPostalAuthorization[<?= $printerId; ?>]" value="<?= $data['NationalReminderPostalAuthorization']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Autorizzazione alla stampa bollettino postale
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignReminderPostalAuthorization[<?= $printerId; ?>]" value="<?= $data['ForeignReminderPostalAuthorization']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	
                                    	<div class="clean_row HSpace4"></div>
                                    	
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Autorizzazione alla stampa bollettino postale PA su avviso di pagamento PagoPA
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="NationalReminderPostalAuthorizationPagoPA[<?= $printerId; ?>]" value="<?= $data['NationalReminderPostalAuthorizationPagoPA']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                                    	<div class="col-sm-3 BoxRowLabel">
                                    		Autorizzazione alla stampa bollettino postale PA su avviso di pagamento PagoPA
                                    	</div>
                                    	<div class="col-sm-3 BoxRowCaption">
                                    		<input name="ForeignReminderPostalAuthorizationPagoPA[<?= $printerId; ?>]" value="<?= $data['ForeignReminderPostalAuthorizationPagoPA']; ?>" type="text" class="form-control frm_field_string">
                                    	</div>
                        			</div>
                            	</div>
                    		</div>
                    	<?php endforeach; ?>
                    	
                    	<div class="clean_row HSpace4"></div>
                    	
                    	<div class="col-sm-12">
                    		<div class="BoxRow col-sm-12 text-center" style="background-color: #294A9C;">PARAMETRI IN COMUNE</div>
                    		
                    		<div class="clean_row HSpace4"></div>
                    		
		                    <div class="col-sm-3 BoxRowLabel">
                                Soggetto Mod 23
                            </div>
                            <div class="col-sm-3 BoxRowCaption">
                                <input class="form-control frm_field_string" name="NationalMod23LSubject" type="text" value="<?= $r_CustomerParameter['NationalMod23LSubject']; ?>">
                            </div>
                            <div class="col-sm-3 BoxRowLabel">
                                Soggetto Mod 23
                            </div>
                            <div class="col-sm-3 BoxRowCaption">
                                <input class="form-control frm_field_string" name="ForeignMod23LSubject" type="text" value="<?= $r_CustomerParameter['ForeignMod23LSubject']; ?>">
                            </div>
            
                            <div class="clean_row HSpace4"></div>
                            
                            <div class="col-sm-3 BoxRowLabel">
                                Nome ente Mod 23
                            </div>
                            <div class="col-sm-3 BoxRowCaption">
                                <input class="form-control frm_field_string" name="NationalMod23LCustomerName" type="text" value="<?= $r_CustomerParameter['NationalMod23LCustomerName']; ?>">
                            </div>
                            <div class="col-sm-3 BoxRowLabel">
                           		Nome ente Mod 23
                            </div>
                            <div class="col-sm-3 BoxRowCaption">
                                <input class="form-control frm_field_string" name="ForeignMod23LCustomerName" type="text" value="<?= $r_CustomerParameter['ForeignMod23LCustomerName']; ?>">
                            </div>
                    	</div>
                	</div>
                </div>
            </div>
            
    		<!-- Stampatori predefiniti -->
            <div id="<?= $Tabs[$n_TabIndex]['Id'] ?>" class="tab-pane<?= $ActiveTab == $n_TabIndex++ ? ' active' : '' ?>">
                <div class="col-sm-12">
                    <div class="col-sm-6 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        NAZIONALE
                    </div>
                    <div class="col-sm-6 BoxRowLabel" style="line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        ESTERO
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Stampatore predefinito (Verbali)
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    	<?= CreateSelect('Printer', 'Id NOT IN(1,3)', 'Id', 'NationalPrinter', 'Id', 'Name', $r_CustomerParameter['NationalPrinter'], false); ?>
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Stampatore predefinito (Verbali)
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    	<?= CreateSelect('Printer', 'Id NOT IN(1,3)', 'Id', 'ForeignPrinter', 'Id', 'Name', $r_CustomerParameter['ForeignPrinter'], false); ?>
                    </div>
    
                    <div class="clean_row HSpace4"></div>
                    
                    <div class="col-sm-3 BoxRowLabel">
                        Stampatore predefinito (Solleciti)
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    	<?= CreateSelect('Printer', 'Id NOT IN(1,3)', 'Id', 'NationalPrinterReminder', 'Id', 'Name', $r_CustomerParameter['NationalPrinterReminder'], false); ?>
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Stampatore predefinito (Solleciti)
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                    	<?= CreateSelect('Printer', 'Id NOT IN(1,3)', 'Id', 'ForeignPrinterReminder', 'Id', 'Name', $r_CustomerParameter['ForeignPrinterReminder'], false); ?>
                    </div>
                </div>
            </div>
    		
    		<!-- MCTC -->
            <div id="<?= $Tabs[$n_TabIndex]['Id'] ?>" class="tab-pane<?= $ActiveTab == $n_TabIndex++ ? ' active' : '' ?>">
                <div class="col-sm-12">
                    <div class="col-sm-6">
                        <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                            <strong>UTENZA VPN</strong>
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
        
                        <div class="col-sm-3 BoxRowLabel">
                            Nome utente
                            <i data-targetfield="MCTCUserVPN" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi Username" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                		<div class="col-sm-3 BoxRowCaption" Id="MCTCUserVPNField">
                            <script>
                                passwordField("MCTCUserVPNField","MCTCUserVPN", "MCTCUserVPN",'<?= $r_CustomerService['UserName']; ?>', false);
                            </script>
            			</div>
                        <div class="col-sm-3 BoxRowLabel">
                            Password
                        </div>
            			<div class="col-sm-3 BoxRowCaption" id="MCTCPasswordVPNField">
                            <script>
                                passwordField("MCTCPasswordVPNField","MCTCPasswordVPN", "MCTCPasswordVPN",'<?= $r_CustomerService['Password']; ?>', false);
                            </script>
            			</div>
            			
            			<div class="clean_row HSpace4"></div>
            			
                        <div class="col-sm-3 BoxRowLabel">
                            Scadenza Password
                        </div>
            			<div class="col-sm-3 BoxRowCaption">
        					<input class="form-control frm_field_date" name="MCTCExpirationDateVPN" type="text" value="<?= $r_CustomerService['PasswordExpiration'] ? explode(" ", DateTimeOutDB($r_CustomerService['PasswordExpiration']))[0] : "" ?>"/>
            			</div>
            			<div class="col-sm-6 BoxRowCaption">
            			</div>
                        
                        <div class="clean_row HSpace4"></div>
                        
                        <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                            <strong>VISURE ITALIANE</strong>
                        </div>
                        
                        <div class="clean_row HSpace4"></div>   
                                         
                        <div class="col-sm-3 BoxRowLabel">
                            Username
                            <i data-targetfield="MCTCUsername" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi Username" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-3 BoxRowCaption" id="MCTCUsn1">
                            <!-- <input id="MCTCUsername" class="form-control frm_field_string" name="MCTCUserName" type="password" value="<?= $r_CustomerParameter['MCTCUserName']; ?>"> -->
                            <script>
                                passwordField("MCTCUsn1","MCTCUserName", "MCTCUsername",'<?= $r_CustomerParameter['MCTCUserName']; ?>', false);
                            </script>
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Password
                        </div>
                        <div class="col-sm-3 BoxRowCaption" id="MCTCPsw1">
                            <!-- <input class="form-control frm_field_string" id="MCTCPassword" name="MCTCPassword" type="password" autocomplete="off" value="<?= $r_CustomerParameter['MCTCPassword']; ?>"> -->
                            <script>
                                passwordField("MCTCPsw1","MCTCPassword", "MCTCPassword",'<?= $r_CustomerParameter['MCTCPassword']; ?>', false);
                            </script>
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
        
                        <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                            <strong>VISURE MASSIVE ITALIANE</strong>
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
                        
                        <div class="col-sm-3 BoxRowLabel">
                            Server
                        </div>
                        <div class="col-sm-9 BoxRowCaption">
                            <input class="form-control frm_field_string" name="MCTCFtp" type="text" value="<?= $r_CustomerParameter['MCTCFtp']; ?>">
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
                        
                        <div class="col-sm-3 BoxRowLabel">
                            Username
                            <i data-targetfield="MCTCMassiveUsername" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi Username" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-3 BoxRowCaption" id="MCTCUsn2">
                            <!-- <input id="MCTCMassiveUsername" class="form-control frm_field_string" name="MCTCMassiveUsername" type="password" value="<?= $r_CustomerParameter['MCTCMassiveUsername']; ?>"> -->
                            <script>
                                passwordField("MCTCUsn2","MCTCMassiveUsername", "MCTCMassiveUsername",'<?= $r_CustomerParameter['MCTCMassiveUsername']; ?>', false);
                            </script>
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Password
                        </div>
                        <div class="col-sm-3 BoxRowCaption" id="MCTCPsw2">
                            <!-- <input class="form-control frm_field_string" id="MCTCMassivePassword" name="MCTCMassivePassword" type="password" autocomplete="off" value="<?= $r_CustomerParameter['MCTCMassivePassword']; ?>"> -->
                            <script>
                                passwordField("MCTCPsw2","MCTCMassivePassword", "MCTCMassivePassword",'<?= $r_CustomerParameter['MCTCMassivePassword']; ?>', false);
                            </script>
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
                        
                        <div class="col-sm-3 BoxRowLabel">
                            Scadenza Password
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_date" name="MCTCDate" id="MCTCDate" type="text" value="<?= DateOutDB($r_CustomerParameter['MCTCDate']); ?>">
                        </div>
                        <div class="col-sm-6 BoxRowLabel">
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
                        
                        <div class="col-sm-3 BoxRowLabel">
                            Posizione
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_number" name="MCTCPosition" type="text" value="<?= $r_CustomerParameter['MCTCPosition']; ?>">
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Nome Ente
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_string" name="MCTCName" type="text" value="<?= $r_CustomerParameter['MCTCName']; ?>">
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
                        
                        <div class="col-sm-3 BoxRowLabel">
                            Provincia
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_string" name="MCTCProvince" type="text" value="<?= $r_CustomerParameter['MCTCProvince']; ?>">
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Nome File Input
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_string" name="MCTCFileInput" type="text" value="<?= $r_CustomerParameter['MCTCFileInput']; ?>">
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
                        
                        <div class="col-sm-3 BoxRowLabel">
                            Nome File Output
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_string" name="MCTCFileOutput" type="text" value="<?= $r_CustomerParameter['MCTCFileOutput']; ?>">
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Nome File Ok
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_string" name="MCTCFileOk" type="text" value="<?= $r_CustomerParameter['MCTCFileFlag']; ?>">
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
                        
                        <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                            <strong>VISURE CROSS BORDER</strong>
                        </div>
                        
                        <div class="clean_row HSpace4"></div>   
                                         
                        <div class="col-sm-3 BoxRowLabel">
                            Username
                            <i data-targetfield="MCTCCrossBorderUsername" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi Username" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-3 BoxRowCaption" id="MCTCUsn3">
                            <!-- <input id="MCTCCrossBorderUsername" class="form-control frm_field_string" name="MCTCCrossBorderUserName" type="password" value="<?= $r_CustomerParameter['MCTCCrossBorderUsername']; ?>"> -->
                            <script>
                                passwordField("MCTCUsn3","MCTCCrossBorderUserName", "MCTCCrossBorderUsername",'<?= $r_CustomerParameter['MCTCCrossBorderUsername']; ?>', false);
                            </script>
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Password
                        </div>
                        <div class="col-sm-3 BoxRowCaption" id="MCTCPsw3">
                            <!-- <input class="form-control frm_field_string" id="MCTCCrossBorderPassword" name="MCTCCrossBorderPassword" type="password" autocomplete="off" value="<?= $r_CustomerParameter['MCTCCrossBorderPassword']; ?>"> -->
                            <script>
                                passwordField("MCTCPsw3","MCTCCrossBorderPassword", "MCTCCrossBorderPassword",'<?= $r_CustomerParameter['MCTCCrossBorderPassword']; ?>', false);
                            </script>
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
                    </div>
                    <div class="col-sm-6">
                        <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                            <strong>DECURTAZIONE</strong>
                        </div>
        
                        <div class="clean_row HSpace4"></div>
                        
                        <div class="col-sm-3 BoxRowLabel">
                            Server
                        </div>
                        <div class="col-sm-9 BoxRowCaption">
                            <input class="form-control frm_field_string" name="LicensePointFtpServer" type="text" value="<?= $r_CustomerParameter['LicensePointFtpServer']; ?>">
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
        
                        <div class="col-sm-3 BoxRowLabel">
                            Username
                            <i data-targetfield="LicensePointFtpUsername" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi Username" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                        </div>
                        <div class="col-sm-3 BoxRowCaption" id="MCTCUsn4">
                            <!-- <input id="LicensePointFtpUsername" class="form-control frm_field_string" name="LicensePointFtpUser" type="password" value="<?= $r_CustomerParameter['LicensePointFtpUser']; ?>"> -->
                            <script>
                                passwordField("MCTCUsn4","LicensePointFtpUser", "LicensePointFtpUsername",'<?= $r_CustomerParameter['LicensePointFtpUser']; ?>', false);
                            </script>
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Password
                        </div>
                        <div class="col-sm-3 BoxRowCaption" id="MCTCPsw4">
                            <!-- <input class="form-control frm_field_string" name="LicensePointFtpPassword" id="LicensePointFtpPassword" type="password" autocomplete="off" value="<?= $r_CustomerParameter['LicensePointFtpPassword']; ?>"> -->
                            <script>
                                passwordField("MCTCPsw4","LicensePointFtpPassword", "LicensePointFtpPassword",'<?= $r_CustomerParameter['LicensePointFtpPassword']; ?>', false);
                            </script>
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
                        
                        <div class="col-sm-3 BoxRowLabel">
                            Scadenza Password
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_date" name="LicensePointFtpPasswordExpiration" id="LicensePointFtpPasswordExpiration" type="text" value="<?= DateOutDB($r_CustomerParameter['LicensePointFtpPasswordExpiration']); ?>">
                        </div>
                        <div class="col-sm-6 BoxRowLabel">
                        </div>
                        
                        <div class="clean_row HSpace4"></div>
        
                        <div class="col-sm-3 BoxRowLabel">
                            Ufficio decurtazione
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_string" name="LicensePointOffice" type="text" value="<?= $r_CustomerParameter['LicensePointOffice']; ?>">
                        </div>
                        <div class="col-sm-3 BoxRowLabel">
                            Codice decurtazione  <i data-toggle="tooltip" data-placement="left" class="tooltip-r glyphicon glyphicon-info-sign mctchelp" style="margin-top:0.2rem;margin-left:1rem;font-size:1.8rem;"></i>
                        </div>
                        <div class="col-sm-3 BoxRowCaption">
                            <input class="form-control frm_field_string" name="LicensePointCode" type="text" value="<?= $r_CustomerParameter['LicensePointCode']; ?>">
                        </div>
                    </div>
				</div>
            </div>
    		
    		<!-- PagoPA -->
            <div id="<?= $Tabs[$n_TabIndex]['Id'] ?>" class="tab-pane<?= $ActiveTab == $n_TabIndex++ ? ' active' : '' ?>">
                <div class="col-sm-12">
                    <div class="col-sm-3 BoxRowLabel">
                        Pagamento Pago PA Nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PagoPAPayment" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['PagoPAPayment']); ?> />
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Pagamento Pago PA Estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PagoPAPaymentForeign" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['PagoPAPaymentForeign']); ?> />
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Stampa avviso di pagam. Pago PA Nazionale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PagoPAPaymentNoticeNational" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['PagoPAPaymentNoticeNational']); ?> />
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Stampa avviso di pagam. Pago PA Estero
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PagoPAPaymentNoticeForeign" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['PagoPAPaymentNoticeForeign']); ?> />
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                    
                    <div class="col-sm-3 BoxRowLabel">
                        Stampa avviso di pagam. Pago PA Nazionale PEC
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PagoPAPaymentNoticeNationalPEC" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['PagoPAPaymentNoticeNationalPEC']); ?> />
                    </div>
                    <div class="col-sm-6 BoxRowLabel">
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                    
                    <div class="col-sm-3 BoxRowLabel">
                        Stampa avviso di pagam. Pago PA Nazionale per rateizzazioni
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PagoPAPaymentNoticeNationalInstallment" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['PagoPAPaymentNoticeNationalInstallment']); ?> />
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Stampa avviso di pagam. Pago PA Estero per rateizzazioni
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PagoPAPaymentNoticeForeignInstallment" type="checkbox" <?= ChkCheckButton($r_CustomerParameter['PagoPAPaymentNoticeForeignInstallment']); ?> />
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Intestatario del CCP
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PagoPACPPOwner" type="text" class="form-control frm_field_string" value="<?= $r_CustomerParameter['PagoPACPPOwner']; ?>"/>
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Oggetto del pagamento
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PagoPAPaymentSubject" type="text" class="form-control frm_field_string" value="<?= $r_CustomerParameter['PagoPAPaymentSubject']; ?>"/>
                    </div>
                    <div class="col-sm-6 BoxRowLabel"></div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Codice CBILL
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PagoPACBILL" type="text" class="form-control frm_field_string" value="<?= $r_CustomerParameter['PagoPACBILL']; ?>"/>
                    </div>
                    <div class="col-sm-6 BoxRowLabel"></div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-3 BoxRowLabel">
                        Indicazione info di pagamento sul verbale
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="PagoPAPaymentInfo" type="text" class="form-control frm_field_string" value="<?= htmlspecialchars($r_CustomerParameter['PagoPAPaymentInfo']); ?>"/>
                    </div>
                    <div class="col-sm-6 BoxRowLabel"></div>
                </div>
            </div>
            
            <!-- Contenzioso -->
            <div id="<?= $Tabs[$n_TabIndex]['Id'] ?>" class="tab-pane<?= $ActiveTab == $n_TabIndex++ ? ' active' : '' ?>">
                <div class="col-sm-12">
                    <div class="col-sm-12 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        GIUDICE DI PACE
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="JudgeCity"  class="form-control frm_field_string" value="<?= StringOutDB($a_JudicialOffice[1]['City']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Provincia
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="JudgeProvince"  class="form-control frm_field_string text-uppercase" value="<?= StringOutDB($a_JudicialOffice[1]['Province']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Indirizzo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="JudgeAddress" class="form-control frm_field_string" value="<?= StringOutDB($a_JudicialOffice[1]['Address']); ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Zip
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="JudgeZIP" class="form-control frm_field_numeric" value="<?= $a_JudicialOffice[1]['ZIP']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Nr. Telefono
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="JudgePhone" class="form-control frm_field_numeric" value="<?= $a_JudicialOffice[1]['Phone']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Fax
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="JudgeFax" class="form-control frm_field_numeric" value="<?= $a_JudicialOffice[1]['Fax']; ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Mail
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="JudgeMail" class="form-control frm_field_string text-lowercase" value="<?= StringOutDB($a_JudicialOffice[1]['Mail']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Pec
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="JudgePEC" class="form-control frm_field_string text-lowercase" value="<?= StringOutDB($a_JudicialOffice[1]['PEC']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Web
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="JudgeWeb" class="form-control frm_field_string"  value="<?= StringOutDB($a_JudicialOffice[1]['Web']); ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Disattivato
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="checkbox" value="1" name="JudgeDisabled" <?= ChkCheckButton($a_JudicialOffice[1]['Disabled']); ?>>
                    </div>
                    <div class="col-sm-8 BoxRowLabel">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-12 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        PREFETTO
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="PrefectureCity"  class="form-control frm_field_string" value="<?= StringOutDB($a_JudicialOffice[2]['City']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Provincia
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="PrefectureProvince"  class="form-control frm_field_string text-uppercase" value="<?= StringOutDB($a_JudicialOffice[2]['Province']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Indirizzo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="PrefectureAddress" class="form-control frm_field_string" value="<?= StringOutDB($a_JudicialOffice[2]['Address']); ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Zip
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="PrefectureZIP" class="form-control frm_field_numeric" value="<?= $a_JudicialOffice[2]['ZIP']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Nr. Telefono
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="PrefecturePhone" class="form-control frm_field_numeric" value="<?= $a_JudicialOffice[2]['Phone']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Fax
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="PrefectureFax" class="form-control frm_field_numeric" value="<?= $a_JudicialOffice[2]['Fax']; ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Mail
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="PrefectureMail" class="form-control frm_field_string text-lowercase" value="<?= StringOutDB($a_JudicialOffice[2]['Mail']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Pec
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="PrefecturePEC" class="form-control frm_field_string text-lowercase" value="<?= StringOutDB($a_JudicialOffice[2]['PEC']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Web
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="PrefectureWeb" class="form-control frm_field_string"  value="<?= StringOutDB($a_JudicialOffice[2]['Web']); ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Disattivato
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="checkbox" value="1" name="PrefectureDisabled" <?= ChkCheckButton($a_JudicialOffice[2]['Disabled']); ?>>
                    </div>
                    <div class="col-sm-8 BoxRowLabel">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-12 BoxRowLabel" style="border-right: 1px solid #E7E7E7;line-height: 4rem;height: 4rem; font-size:2rem;font-weight: bold; text-align:center;">
                        TRIBUNALE
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Città
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="CourtCity"  class="form-control frm_field_string" value="<?= StringOutDB($a_JudicialOffice[3]['City']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Provincia
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="CourtProvince"  class="form-control frm_field_string text-uppercase" value="<?= StringOutDB($a_JudicialOffice[3]['Province']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Indirizzo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="CourtAddress" class="form-control frm_field_string" value="<?= StringOutDB($a_JudicialOffice[3]['Address']); ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Zip
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="CourtZIP" class="form-control frm_field_numeric" value="<?= $a_JudicialOffice[3]['ZIP']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Nr. Telefono
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="CourtPhone" class="form-control frm_field_numeric" value="<?= $a_JudicialOffice[3]['Phone']; ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Fax
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="CourtFax" class="form-control frm_field_numeric" value="<?= $a_JudicialOffice[3]['Fax']; ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Mail
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="CourtMail" class="form-control frm_field_string text-lowercase" value="<?= StringOutDB($a_JudicialOffice[3]['Mail']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Pec
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="CourtPEC" class="form-control frm_field_string text-lowercase" value="<?= StringOutDB($a_JudicialOffice[3]['PEC']); ?>">
                    </div>
                    <div class="col-sm-1 BoxRowLabel">
                        Web
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="text" name="CourtWeb" class="form-control frm_field_string"  value="<?= StringOutDB($a_JudicialOffice[3]['Web']); ?>">
                    </div>
    
                    <div class="clean_row HSpace4"></div>
    
                    <div class="col-sm-1 BoxRowLabel">
                        Disattivato
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input type="checkbox" value="1" name="CourtDisabled" <?= ChkCheckButton($a_JudicialOffice[3]['Disabled']); ?>>
                    </div>
                    <div class="col-sm-8 BoxRowLabel">
                    </div>
                </div>
            </div>
            <!-- Comunicazioni prefetto -->
            <div id="<?= $Tabs[$n_TabIndex]['Id'] ?>" class="tab-pane<?= $ActiveTab == $n_TabIndex++ ? ' active' : '' ?>">
            	<div class="col-sm-12">
            		<div class="col-sm-2 BoxRowLabel">
                        Firmatario comunicazioni prefettura
                    </div>
                    <div class="col-sm-2 BoxRowCaption">
                        <?= CreateSelectConcat("SELECT Id,Code,CONCAT_WS(' ',Code,Qualification,Name) AS Name FROM Controller WHERE CityId='$CityId' AND Disabled=0 ORDER BY CAST(Code AS UNSIGNED)","PrefectCommunicationSigner","Id","Name",$r_CustomerParameter['PrefectCommunicationSigner'],false) ?>
                    </div>
                    <div class="col-sm-8 BoxRowLabel">
                    </div>
            	</div>
            </div>
            <!-- Rateizzazioni -->
            <div id="<?= $Tabs[$n_TabIndex]['Id'] ?>" class="tab-pane<?= $ActiveTab == $n_TabIndex++ ? ' active' : '' ?>">
            	<div class="col-sm-12">
        			<div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                   		<strong>Parametri generali</strong>
                	</div>
                    
                    <div class="clean_row HSpace4"></div>
                    
            		<div class="col-sm-2 BoxRowLabel">
                        Metodo di rateizzazione
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                    	<?= CreateArraySelect(unserialize(RATEIZZAZIONE_METODI), true, "InstallmentMethod", "InstallmentMethod", $r_CustomerParameter['InstallmentMethod'], true); ?>
                    </div>
            		<div class="col-sm-2 BoxRowLabel">
                        Applicare tasso di interesse
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="checkbox" value="1" name="ApplyInstallmentRates" <?= ChkCheckButton($r_CustomerParameter['ApplyInstallmentRates']); ?>>
                    </div>
            		
                    <div class="col-sm-2 BoxRowLabel">
                        Numero di rate massimo (metodo libero)
                    </div>
                    
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_number text-right" id="InstallmentFreeRateLimit"  name="InstallmentFreeRateLimit" value="<?= $r_CustomerParameter['InstallmentFreeRateLimit']; ?>"/>
                    </div>
                    
                    <div class="col-sm-2 BoxRowLabel">
                    	Limita approv. ai soli vigili
                    </div>
                    
                    <div class="col-sm-1 BoxRowCaption">
                    	<input type="checkbox" value="1" name="InstallmentControllerApproval" <?= ChkCheckButton($r_CustomerParameter['InstallmentControllerApproval']); ?>>
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                    
                    <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                   		<strong>Parametri legislativi</strong>
                	</div>
                	
                	<div class="clean_row HSpace4"></div>
                	
                	<div class="col-sm-2 BoxRowLabel">
                        Importo min. singola rata per applicazione tasso di interesse
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_currency text-right" id="InstallmentRatesMinimumAmount" name="InstallmentRatesMinimumAmount" value="<?= $r_CustomerParameter['InstallmentRatesMinimumAmount']; ?>"/>
                    </div>
            		
            		<div class="col-sm-2 BoxRowLabel">
                        Importo minimo per poter rateizzare
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_currency text-right" id="InstallmentMinimumFeeLimit" name="InstallmentMinimumFeeLimit" value="<?= $r_CustomerParameter['InstallmentMinimumFeeLimit']; ?>"/>
                    </div>
                	
                	<div class="col-sm-2 BoxRowLabel">
                        Reddito singolo annuale massimo
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_currency text-right" id="InstallmentYearlyIncomeLimit" name="InstallmentYearlyIncomeLimit" value="<?= $r_CustomerParameter['InstallmentYearlyIncomeLimit']; ?>"/>
                    </div>
                    
                    <div class="col-sm-2 BoxRowLabel">
                        Reddito aggiuntivo per familiare convivente
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_currency text-right" id="InstallmentAdditionalIncomePerFamilyMember" name="InstallmentAdditionalIncomePerFamilyMember" value="<?= $r_CustomerParameter['InstallmentAdditionalIncomePerFamilyMember']; ?>"/>
                    </div>
                	
                	<div class="clean_row HSpace4"></div>
                	
                	<div class="col-sm-2 BoxRowLabel">
                        Primo scaglione: Importo/Numero rate massimi
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_currency text-right" id="InstallmentFeeLimit1" name="InstallmentFeeLimit1" value="<?= $r_CustomerParameter['InstallmentFeeLimit1']; ?>"/>
                    </div>
                    
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_number text-right" id="InstallmentRateLimit1" name="InstallmentRateLimit1" value="<?= $r_CustomerParameter['InstallmentRateLimit1']; ?>"/>
                    </div>
                    
                    <div class="col-sm-2 BoxRowLabel">
                        Secondo scaglione: Importo/Numero rate massimi
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_currency text-right" id="InstallmentFeeLimit2" name="InstallmentFeeLimit2" value="<?= $r_CustomerParameter['InstallmentFeeLimit2']; ?>"/>
                    </div>
                    
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_number text-right" id="InstallmentRateLimit2" name="InstallmentRateLimit2" value="<?= $r_CustomerParameter['InstallmentRateLimit2']; ?>"/>
                    </div>
                    
                    <div class="col-sm-2 BoxRowLabel">
                        Terzo scaglione: Numero rate massimi
                    </div>
                    <!--  
                    Il terzo limite di importo è stato aggiunto per eventuali sviluppi futuri ma per il momento non viene utilizzato perchè è semplicemente > importo 2 
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_currency" id="InstallmentFeeLimit3" name="InstallmentFeeLimit3" value="<?= $r_CustomerParameter['InstallmentFeeLimit3']; ?>"/>
                    </div> -->
                    <div class="col-sm-1 BoxRowCaption">
                        <input type="text" class="form-control frm_field_number text-right" id="InstallmentRateLimit3" name="InstallmentRateLimit3" value="<?= $r_CustomerParameter['InstallmentRateLimit3']; ?>"/>
                    </div>
                    
                    <div class="col-sm-1 BoxRowCaption"></div>
                    
                    
                    <div class="BoxRow col-sm-12 text-center" style="background-color: #294A9C;">PERIODICITÀ INTERESSI</div>
                    
                    <div class="clean_row HSpace4"></div>
                    
                	<div class="table_label_H col-sm-2">Da data</div>
                    <div class="table_label_H col-sm-2">A data</div>
                	<div class="table_label_H col-sm-2">Interesse %</div>
                    <div class="table_label_H col-sm-5">Norma</div>
                    <div class="table_label_H col-sm-1"></div>
                    
                    <div class="clean_row HSpace4"></div>
                    
                    <div class="BoxRowLabel col-sm-12">
                        <b>NUOVA PERIODICITÀ</b>
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                    
                    <div id="installmentRatesInsert">
                		<div class="col-sm-2 BoxRowCaption">
                            <input data-field="FromDate" type="text" class="form-control frm_field_date text-center" value=""/>
                        </div>
                		<div class="col-sm-2 BoxRowCaption">
                            <input data-field="ToDate" type="text" class="form-control frm_field_date text-center" value=""/>
                        </div>
                		<div class="col-sm-2 BoxRowCaption">
                			<div class="col-sm-3"></div>
                			<div class="col-sm-3">
                				<input data-field="Percentual" type="text" class="form-control frm_field_numeric text-right" value=""/>
                			</div>
                			<div class="col-sm-3 text-center">%</div>
                			<div class="col-sm-3"></div>
                        </div>
                		<div class="col-sm-5 BoxRowCaption">
                            <input data-field="Norm" type="text" class="form-control frm_field_string" value=""/>
                        </div>
                		<div class="col-sm-1 BoxRowCaption">
                        </div>
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                    
                    <div class="BoxRowLabel col-sm-12">
                        <b>PERIODICITÀ REGISTRATE</b>
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                    
                	<?php foreach($a_InstallmentRates as $record): ?>
                		<div class="installmentRateRow">
                			<input type="hidden" data-field="Id" value="<?= $record['Id']; ?>">
		            		<div class="col-sm-2 BoxRowCaption">
                                <input data-field="FromDate" type="text" class="form-control frm_field_date text-center" value="<?= DateOutDB($record['FromDate']); ?>"/>
                            </div>
                    		<div class="col-sm-2 BoxRowCaption">
                                <input data-field="ToDate" type="text" class="form-control frm_field_date text-center" value="<?= DateOutDB($record['ToDate']); ?>"/>
                            </div>
                    		<div class="col-sm-2 BoxRowCaption">
                    			<div class="col-sm-3"></div>
                    			<div class="col-sm-3">
                    				<input data-field="Percentual" type="text" class="form-control frm_field_numeric text-right" value="<?= $record['Percentual']; ?>"/>
                    			</div>
                				<div class="col-sm-3 text-center">%</div>
                				<div class="col-sm-3"></div>
                            </div>
                    		<div class="col-sm-5 BoxRowCaption">
                                <input data-field="Norm" type="text" class="form-control frm_field_string" value="<?= $record['Norm']; ?>"/>
                            </div>
                    		<div class="col-sm-1 BoxRowCaption text-center">
    		                    <button type="button" data-toggle="tooltip" data-placement="left" title="Elimina" data-rateinstallmentid="<?= $record['Id']; ?>" class="btn btn-danger deleteinstallmentsrate tooltip-r" style="padding:0;width:50%;height:100%;"><i class="fa fa-times"></i></button>
                            </div>
                            
                            <div class="clean_row HSpace4"></div>
                		</div>
                	<?php endforeach; ?>
        	        <div id="noInstallmentRateRow" class="col-sm-12 table_caption_H<?= !empty($a_InstallmentRates) ? " hidden" : "" ?>">
                        Nessuna periodicità trovata per l'ente in uso.
                    </div>
                </div>
            </div>
        </div>
    
        <div class="clean_row HSpace4"></div>
    
        <div class="col-sm-12">
            <div class="table_label_H HSpace4" style="height:8rem;">
            	<button id="update" type="submit" class="btn btn-success" style="margin-top:2rem;width:inherit;"><i id="UpdateIcon" class="fas fa-save fa-fw"></i> Salva</button>
            </div>
        </div>
    </form>
</div>

<script>

function compileInstallmentsRateDates(){
    var ob = {add : {}, upd : []};
    $("#installmentRatesInsert").find("input").each(function (i){
    	ob.add[$(this).data('field')] = $(this).val();
    });
    
    $(".installmentRateRow").each(function (i){
      var index = $(this).data('row-id');
      var o = {}
    	$(this).find("input").each(function (ii){
    		o[$(this).data('field')] = $(this).val();
    	});
      ob.upd.push(o);
    });
    return ob;
}

function installmentsRateAjax(data, operation){
	data.Operation = operation;
	data.CityId = "<?= $CityId; ?>"
	
    return $.ajax({
        url: 'ajax/ajx_manageInstallmentRates.php',
        type: 'POST',
        async: false,
        dataType: 'json',
        data: data,
        ContentType: "application/json; charset=UTF-8"
    });
}

function lockButtons(lock){
	$(".deleteinstallmentsrate").prop("disabled", lock);
	$("#update").prop("disabled", lock);
}

$('document').ready(function () {
    $('.tab_button').click(function () {
        $('#ActiveTab').val($(this).data('tab_position'));
    });
	$('.tab_button_printer').click(function () {
        $('#ActiveTabPrinter').val($(this).data('tab_position'));
    });
    
    $(".mctchelp").on('click', function(){
    	window.open('<?= HELP.'/Creazione_Codice_Ente_05_2016.pdf' ?>', '_blank');
    });
    
    $(".deleteinstallmentsrate").on("click", function(){
    	var row = $(this).closest(".installmentRateRow");
    	var id = $(this).data("rateinstallmentid");
    	var fromDate = row.find('[data-field="FromDate"]').val();
    	var toDate = row.find('[data-field="ToDate"]').val();
    	var percentual = row.find('[data-field="Percentual"]').val();
    	var norm = row.find('[data-field="Norm"]').val();
    
    	if(confirm("Si stà per eliminare la seguente periodicità:\n\nData inizio: "+fromDate+"\nData fine: "+toDate+"\nInteresse: "+percentual+" %\nNorma: "+norm+"\n\nContinuare?")){
    		lockButtons(true);
    		$.when(installmentsRateAjax({Id: id}, "delete")).done(function (data) {
    			if(!data.Success){
    				alert(data.Message);
    			} else {
    				row.remove();
    				$("#noInstallmentRateRow").toggleClass("hidden", $(".installmentRateRow").length > 0);
    				alert("Azione eseguita con successo.")
    			}
    			lockButtons(false);
    		}).fail(function(data){
    			alert("Errore: "+data.responseText);
    			console.log(data);
    			lockButtons(false);
    		});
    	}
    });

	$("#ManagePEC").change(function(){
		if(!$(this).prop("checked")){
			$("#EnableINIPECDigitalSignature, #EnableINIPECNotification").prop("checked", false).prop("disabled", true);
		} else {
			$("#EnableINIPECDigitalSignature, #EnableINIPECNotification").prop("disabled", false);
		}
	});

    $('.showpassword').click(function(){
        var field = '#' + $(this).data('targetfield');
        if('disc' == $(field).css("-webkit-text-security")){
             $(field).css("-webkit-text-security","none");
        }else{
             $(field).css("-webkit-text-security","disc");
        }
        $(this).toggleClass("glyphicon-eye-open glyphicon-eye-close");
    });

    $(".showpassword").hover(function(){
        $(this).css("cursor","pointer");
    },function(){
        $(this).css("cursor","");
    });

    $("#UseOutgoingAuthentication").change(function(){
        if($(this).is(":checked"))
        	$('#OutgoingUserName, #OutgoingPassword').prop('disabled', false);
    	else
    		$('#OutgoingUserName, #OutgoingPassword').prop('disabled', true);
    });

    $('#f_upd_customer').bootstrapValidator({
        live: 'disabled',
        fields: {
			frm_field_required: {
				selector: '.frm_field_required',
				validators: {
					notEmpty: {
						message: 'Richiesto'
					}
				}
			},
            PatronalFeastDay: {
                validators: {
                    regexp : {
                        regexp: '^(0?[1-9]|[12][0-9]|3[01])$',
                        message : 'Giorno non valido'
                    }
                }
            },
    		JudgeProvince: {
        		validators: {
                    stringLength: {
                        max: 2,
                        min: 2,
                        message: 'La provincia deve contenere 2 caratteri'
                    }
        		}
    		},
    		PrefectProvince: {
        		validators: {
                    stringLength: {
                        max: 2,
                        min: 2,
                        message: 'La provincia deve contenere 2 caratteri'
                    }
        		}
    		},
    		CourtProvince: {
        		validators: {
                    stringLength: {
                        max: 2,
                        min: 2,
                        message: 'La provincia deve contenere 2 caratteri'
                    }
        		}
    		},
    		RoleCityCode: {
        		validators: {
                    stringLength: {
                        max: 6,
                        min: 6,
                        message: 'Il codice deve contenere 6 cifre'
                    }
        		}
    		},
    		FifthField: {
        		validators: {
        			notEmpty: {
                        message: 'Richiesto'
                    },
                    numeric: {
                        message: 'Numero'
                    }
        		}
    		}
        }
    }).on('success.form.bv', function(e){
    	lockButtons(true);
    	var success = true;
		$.when(installmentsRateAjax(compileInstallmentsRateDates(), "update")).done(function (data) {
			if(!data.Success){
				$('[href="#CustomerInstallments"]').tab('show');
				alert(data.Message);
				success = false;
				
				lockButtons(false);//disabilita bottoni
			}
		}).fail(function(data){
			success = false;
			alert("Errore: "+data.responseText);
			console.log(data);
			lockButtons(false);
		});
		
		return success;
    });
    
    
});

//Azioni che si eseguono in base al cambio della tendina sul metodo di rateizzazione
$('#InstallmentMethod').on("change", manageInstallment).change();

function manageInstallment(){
		manageInstallmentLawTextAreas(1);	//Sempre disabilitate finche non si deciderà che va bene modificare i vincoli di legge. In tal caso impostare ad 1 l'argomento del metodo
}
//Abilita o Disabilita le textarea del vincolo legislativo
//NB per il momento sono sempre disabilitate. Questa funzione può tornare bene in caso si decidesse si renderle sbloccabili
function manageInstallmentLawTextAreas(enable){
	if(enable == 0){			//DISABILITA
		$('#InstallmentRatesMinimumAmount').prop("disabled",true);
		$('#InstallmentMinimumFeeLimit').prop("disabled",true);
		$('#InstallmentYearlyIncomeLimit').prop("disabled",true);
		$('#InstallmentAdditionalIncomePerFamilyMember').prop("disabled",true);
		$('#InstallmentFeeLimit1').prop("disabled",true);
		$('#InstallmentRateLimit1').prop("disabled",true);
		$('#InstallmentFeeLimit2').prop("disabled",true);
		$('#InstallmentRateLimit2').prop("disabled",true);
		$('#InstallmentRateLimit3').prop("disabled",true);
		}
	else if(enable == 1){	//ABILITA
		$('#InstallmentRatesMinimumAmount').prop("disabled",false);
		$('#InstallmentMinimumFeeLimit').prop("disabled",false);
		$('#InstallmentYearlyIncomeLimit').prop("disabled",false);
		$('#InstallmentAdditionalIncomePerFamilyMember').prop("disabled",false);
		$('#InstallmentFeeLimit1').prop("disabled",false);
		$('#InstallmentRateLimit1').prop("disabled",false);
		$('#InstallmentFeeLimit2').prop("disabled",false);
		$('#InstallmentRateLimit2').prop("disabled",false);
		$('#InstallmentRateLimit3').prop("disabled",false);
		}
	}
	
	
</script>

<?php
require_once(INC . "/footer.php");
