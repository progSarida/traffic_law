<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(CLS."/cls_view.php");
require_once(INC."/function.php");
require_once(INC."/views.php");
require_once(INC."/header.php");
require_once(INC."/menu_{$_SESSION['UserMenuType']}.php");

$cls_view = new CLS_VIEW(MGMT_PRINTERPARAMETER);
$rs_Table = $rs->SelectQuery($cls_view->generateSelect());
$a_PrinterParameter = $rs->getResults($rs_Table);
$a_PrinterParameter = array_column($a_PrinterParameter, null, 'PrinterId');

//mgmt_printerparameter_upd_exe.php
$ActiveTab = CheckValue('ActiveTab','n') > 0 ? CheckValue('ActiveTab','n') : key($a_PrinterParameter);

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_update" action="mgmt_printerparameter_upd_exe.php" method="post" autocomplete="off">
        <input type="hidden" name="ActiveTab" id="ActiveTab" value="<?= $ActiveTab; ?>">
        <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        
        <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            Parametri stampatori
        </div>
        
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
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12">
            <ul class="nav nav-tabs" id="PrintersTab">
                <?php foreach($a_PrinterParameter as $printerId => $data): ?>
                	<li data-tab_position="<?= $printerId; ?>" class="tab_button<?= $ActiveTab == $printerId ? ' active' : '' ?>" id="tab_Printer<?= $printerId ?>"><a href="#Printer<?= $printerId ?>" data-toggle="tab"><?= $data['Name'] ?></a></li>
                <?php endforeach;?>
            </ul>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="tab-content">
        	<?php foreach($a_PrinterParameter as $printerId => $data): ?>
        		<div class="tab-pane<?= $ActiveTab == $printerId ? ' active' : '' ?>" id="Printer<?= $printerId ?>">
                	<div class="tab-content">
	        			<div class="col-sm-12">
            				<div class="col-sm-6 BoxRowLabel text-center" style="border-right: 1px solid #fff;line-height: 4rem;height: 4rem; font-size:2rem;">
                           		<strong>Parametri spedizione NAZIONALE</strong>
                        	</div>
            				<div class="col-sm-6 BoxRowLabel text-center" style="border-right: 1px solid #fff;line-height: 4rem;height: 4rem; font-size:2rem;">
                           		<strong>Parametri spedizione ESTERO</strong>
                        	</div>
                        	
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
                            
                			<div class="table_caption_H col-sm-12 alert-warning">
                                <i class="fas fa-fw fa-info-circle col-sm-1" style="margin-top: 0.5rem;"></i>&nbsp;&nbsp;&nbsp;NOTA BENE: nel caso in cui le autorizzazioni alla stampa bollettino dello stampatore non siano definite, verrano usate quelle dell'ente definite in Ente > Gestione Ente > Posta.
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
                            
                			<div class="table_caption_H col-sm-12 alert-warning">
                                <i class="fas fa-fw fa-info-circle col-sm-1" style="margin-top: 0.5rem;"></i>&nbsp;&nbsp;&nbsp;NOTA BENE: nel caso in cui le autorizzazioni alla stampa bollettino dello stampatore non siano definite, verrano usate quelle dell'ente definite in Ente > Gestione Ente > Posta.
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
    	</div>
	
        <div class="clean_row HSpace4"></div>
        
        <div class="table_label_H HSpace4" style="height:8rem;">
        	<?= ChkButton($aUserButton, 'upd','<button id="update" type="submit" class="btn btn-info" style="margin-top:2rem;width:inherit;"><i id="UpdateIcon" class="fas fa-sync-alt fa-fw"></i> Aggiorna</button>'); ?>
        </div>
	</form>
</div>

<script type="text/javascript">
$('document').ready(function () {

    $('.tab_button').click(function () {
        $('#ActiveTab').val($(this).data('tab_position'));
    });

});
</script>
    
<?php
include(INC."/footer.php");
