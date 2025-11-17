<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(CLS."/cls_mail.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$a_ConnectionSecurityTypes = unserialize(MAIL_CONNECTIONSECURITY_TYPES);
$a_IncomingProtocol = unserialize(MAIL_INCOMINGPROTOCOL_TYPES);
$a_OutgoingProtocol = unserialize(MAIL_OUTGOINGPROTOCOL_TYPES);
$a_DisableAuthProperties = unserialize(MAIL_AUTH_PROPERTIES);

$rs_CMA = $rs->Select('CustomerMailAuthentication',"ConfigType=1 AND CityId='".$_SESSION['cityid']."'");
$r_CMA = $rs->getArrayLine($rs_CMA);

$rs_CustomerService = $rs->Select('CustomerService',"ServiceId=6 AND CityId='".ENTE_BASE."'");
$r_CustomerService = $rs->getArrayLine($rs_CustomerService);

$rs_CustomerParameter = $rs->Select('V_CustomerParameter',"CityId='".$_SESSION['cityid']."'");
$r_CustomerParameter = $rs->getArrayLine($rs_CustomerParameter);

//tbl_customer_pecservers_upd_exe.php
$ActiveTab = CheckValue('ActiveTab','n');

$Tabs = array();
$Tabs[] = array('Id' => 'ParamsPEC', 'Title' => 'INI-PEC');
$Tabs[] = array('Id' => 'ParamsPagoPA', 'Title' => 'PagoPA');
$Tabs[] = array('Id' => 'MCTC', 'Title' => 'MCTC');

foreach($Tabs as $key => $tab){
    $Tabs[$key]['Active'] = $key == $ActiveTab ? 'active': '';
}

echo $str_out;
?>

<div class="row-fluid">
	<form id="f_update" action="tbl_customer_pecservers_upd_exe.php" method="post" autocomplete="off">
        <input type="hidden" name="ActiveTab" id="ActiveTab" value="<?= $ActiveTab; ?>">
        <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            Parametri tecnici
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12">
            <ul class="nav nav-tabs" id="ParamsTab">
                <?php foreach($Tabs as $key => $tab): ?>
                	<li tab_position="<?= $key; ?>" class="tab_button <?= $tab['Active'] ?>" id="tab_<?= $tab['Id'] ?>"><a href="#<?= $tab['Id'] ?>" data-toggle="tab"><?= $tab['Title'] ?></a></li>
                <?php endforeach;?>
            </ul>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="tab-content">
        
        	<div class="tab-pane <?= $Tabs[0]['Active'] ?>" id="<?= $Tabs[0]['Id'] ?>">
		        <div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                    <strong>Parametri server di posta</strong>
                </div>
        
                <div class="clean_row HSpace4"></div>
        
                <div class="col-sm-6 BoxRow text-center" style="border-right: 1px solid #E7E7E7;">
                    In entrata
                </div>
                <div class="col-sm-6 BoxRow text-center">
                    In uscita
                </div>
        
                <div class="clean_row HSpace4"></div>
        
                <div class="col-sm-3 BoxRowLabel">
                    Sicurezza connessione
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <?= CreateArraySelect($a_ConnectionSecurityTypes, true, 'IncomingSecurity', 'IncomingSecurity', $r_CMA['IncomingSecurity'], true); ?>
                </div>
                <div class="col-sm-3 BoxRowLabel">
                    Sicurezza connessione
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <?= CreateArraySelect($a_ConnectionSecurityTypes, true, 'OutgoingSecurity', 'OutgoingSecurity', $r_CMA['OutgoingSecurity'], true); ?>
                </div>
        
                <div class="clean_row HSpace4"></div>
        
                <div class="col-sm-3 BoxRowLabel">
                    Server posta in entrata
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input name="IncomingMailServer" id="IncomingMailServer" type="text" class="form-control frm_field_string" value="<?= StringOutDB($r_CMA['IncomingMailServer']); ?>">
                </div>
                <div class="col-sm-3 BoxRowLabel">
                    Server posta in uscita
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input name="OutgoingMailServer" id="OutgoingMailServer" type="text" class="form-control frm_field_string" value="<?= StringOutDB($r_CMA['OutgoingMailServer']); ?>">
                </div>
        
                <div class="clean_row HSpace4"></div>
        
                <div class="col-sm-6" style="padding:0;">
                    <div class="col-sm-3 BoxRowLabel">
                        Protocollo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <?= CreateArraySelect($a_IncomingProtocol, true, 'IncomingProtocol', 'IncomingProtocol', $r_CMA['IncomingProtocol'], true); ?>
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Porta
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="IncomingPort" id="IncomingPort" type="text" class="form-control frm_field_numeric" value="<?= $r_CMA['IncomingPort']; ?>">
                    </div>
                </div>
        
                <div class="col-sm-6" style="padding:0;">
                    <div class="col-sm-3 BoxRowLabel">
                        Protocollo
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <?= CreateArraySelect($a_OutgoingProtocol, true, 'OutgoingProtocol', 'OutgoingProtocol', $r_CMA['OutgoingProtocol'], true); ?>
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Porta
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="OutgoingPort" id="OutgoingPort" type="text" class="form-control frm_field_numeric" value="<?= $r_CMA['OutgoingPort']; ?>">
                    </div>
                </div>
        
                <div class="clean_row HSpace4"></div>
                
                <div class="col-sm-3 BoxRowLabel">
                    Disabilita proprietà di autenticazione
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <?= CreateArraySelect($a_DisableAuthProperties, true, 'DisableAuthProperties', 'DisableAuthProperties', $r_CMA['DisableAuthProperties'], false); ?>
                </div>
                <div class="col-sm-6 BoxRowLabel"></div>
        	</div>
        	
			<div class="tab-pane <?= $Tabs[1]['Active'] ?>" id="<?= $Tabs[1]['Id'] ?>">
		            <div class="col-sm-12">
                        <div class="col-sm-12 alert alert-info" style="display: flex;margin: 0px;align-items: center;">
                            <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                            <div class="col-sm-11" style="font-size: 1.2rem;">
                                <ul style="list-style-position: inside;">
                                    <li>Aux code: Valore numerico che definisce la struttura del codice IUV in funzione del numero di punti di generazione dello stesso. Viene usato come prefisso nella strutturazione del codice IUV</li>
                                    <li>Application code: Valore numerico che serve ad individuare la porzione dell’archivio dei pagamenti in attesa interessata dall’operazione. Il dato è presente o meno in funzione del componente Aux digit. Viene usato come prefisso nella strutturazione del codice IUV</li>
                                </ul>
                            </div>
                        </div>
                    </div>
        
                    <div class="clean_row HSpace4"></div>
            
                    <div class="col-sm-3 BoxRowLabel">
                        Servizio PagoPA                    
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
						<?= CreateSelect('PagoPAService', '1=1', 'Id', 'PagoPAService', 'Id', 'Name', $r_CustomerParameter['PagoPAService'], false) ?>
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Password
                        <i data-targetfield="CustomerServicePassword" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi password" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                    </div>
                    <div class="col-sm-3 BoxRowCaption" id="CustomerServicePasswordField">
                        <script>
                            passwordField("CustomerServicePasswordField","CustomerServicePassword", "CustomerServicePassword",'<?= $r_CustomerParameter['PagoPAPassword']; ?>', false);
                        </script>
                    </div>      
                    
                    <div class="clean_row HSpace4"></div>
                    
                    <div class="col-sm-3 BoxRowLabel">
                        ALIAS negozio
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="PagoPAAlias" type="text" value="<?= $r_CustomerParameter['PagoPAAlias']; ?>">
                    </div>                                 
                    <div class="col-sm-3 BoxRowLabel">
                        Scadenza Password
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="frm_field_date form-control" name="PasswordExpiration" id="PasswordExpiration" type="text" value="<?= DateOutDB( explode(' ',$r_CustomerParameter['PagoPAExpiration'])[0]); ?>"/>
                    </div>       
                    
                    <div class="clean_row HSpace4"></div>
                                                      
                    <div class="col-sm-3 BoxRowLabel">
                        IBAN Nome
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="PagoPAIban" type="text" value="<?= $r_CustomerParameter['PagoPAIban']; ?>">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                    	Aux digit
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                    	<input class="form-control" name="PagoPAAuxCode" type="text" value="<?= $r_CustomerParameter['PagoPAAuxCode']; ?>">
                    </div>
                    <div class="col-sm-2 BoxRowLabel">
                    	Application code
                    </div>
                    <div class="col-sm-1 BoxRowCaption">
                    	<input class="form-control" name="PagoPAApplicationCode" type="text" value="<?= $r_CustomerParameter['PagoPAApplicationCode']; ?>">
                    </div>
                    
                    <div class="clean_row HSpace4"></div>
                    
                    <div class="col-sm-3 BoxRowLabel">
                        Servizio
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="SondrioServizio" type="text" value="<?= $r_CustomerParameter['SondrioServizio']; ?>">
                    </div>
                    <div class="col-sm-3 BoxRowLabel">
                        Considera Iuv come codice avviso
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input name="IsIuvCodiceAvviso" type="checkbox"  <?= ChkCheckButton($r_CustomerParameter['IsIuvCodiceAvviso']); ?>   />
                    </div>   
                    
                    <div class="clean_row HSpace4"></div>
                          
                    <div class="col-sm-3 BoxRowLabel">
                        Sottoservizio
                    </div>
                    <div class="col-sm-3 BoxRowCaption">
                        <input class="form-control frm_field_string" name="SondrioSottoservizio" type="text" value="<?= $r_CustomerParameter['SondrioSottoservizio']; ?>">
                    </div>
                    <div class="col-sm-6 BoxRowLabel">
                    </div>       
        	</div>
        	<div class="tab-pane <?= $Tabs[2]['Active'] ?>" id="<?= $Tabs[2]['Id'] ?>">
            	<div class="col-sm-12 BoxRowLabel text-center" style="line-height: 4rem;height: 4rem; font-size:2rem;">
                	<strong>UTENZA VPN APPLICAZIONE</strong>
                </div>
                
            	<div class="clean_row HSpace4"></div>
                
                <div class="col-sm-2 BoxRowLabel">
                    Nome utente
                    <i data-targetfield="MCTCUserVPN" data-toggle="tooltip" data-placement="top" data-container="body" title="Mostra/Nascondi Username" class="showpassword tooltip-r glyphicon glyphicon-eye-close" style="margin-right: 1rem;line-height:2rem;float: right;"></i>
                </div>
        		<div class="col-sm-2 BoxRowCaption" Id="MCTCUserVPNField">
                    <script>
                        passwordField("MCTCUserVPNField","MCTCUserVPN", "MCTCUserVPN",'<?= $r_CustomerService['UserName']; ?>', false);
                    </script>
    			</div>
                <div class="col-sm-2 BoxRowLabel">
                    Password
                </div>
    			<div class="col-sm-2 BoxRowCaption" id="MCTCPasswordVPNField">
                    <script>
                        passwordField("MCTCPasswordVPNField","MCTCPasswordVPN", "MCTCPasswordVPN",'<?= $r_CustomerService['Password']; ?>', false);
                    </script>
    			</div>
                <div class="col-sm-2 BoxRowLabel">
                    Scadenza Password
                </div>
    			<div class="col-sm-2 BoxRowCaption">
					<input class="form-control frm_field_date" name="MCTCExpirationDateVPN" type="text" value="<?= $r_CustomerService['PasswordExpiration'] ? explode(" ", DateTimeOutDB($r_CustomerService['PasswordExpiration']))[0] : "" ?>"/>
    			</div>
        	</div>
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
        $('#ActiveTab').val($(this).attr('tab_position'));
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

    $('#f_update').bootstrapValidator({
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
			PagoPAAuxCode: {
                validators: {
                    between : {
                        min: 0,
                        max: 3,
                        message : 'Valore fra 0 e 3'
                    }
                }
            },
            PagoPAApplicationCode: {
                validators: {
                    regexp: {
                        regexp: '^[0-9]{2}$',
                        message: 'Valore fra 00 e 99'
                    }
                }
    		}
        }
    });

});
</script>
    
<?php
include(INC."/footer.php");
