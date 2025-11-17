<?php
include ("_path.php");
include (INC . "/parameter.php");
include (CLS . "/cls_db.php");
include (INC . "/function.php");
include (INC . "/header.php");

require (INC . '/menu_' . $_SESSION['UserMenuType'] . '.php');

$BackPage = strtok($str_BackPage, '?');

$CityId = $_SESSION['cityid'];
$RuleTypeId = $_SESSION['ruletypeid'];

$a_UseAdditionalSanction = unserialize(ARTICLETARIFF_USEADDITIONALSANCTION);

// File pdf della tabella codici decurtazione punti
$LicensePointTableFile = 'TabellaPatentePuntiMCTC-18.01.2019.pdf';

// Se l'utente ha un valore di permessi >50 oppure la pagina di provenienza è tbl_article_city.php
if ($_SESSION['usertype'] <= 50 || $BackPage == 'tbl_article_city.php')
{
    $rs_Customer = $rs->Select('Customer', "CityId='" . $CityId . "'");
    $r_Customer = mysqli_fetch_array($rs_Customer);
    $str_CustomerSearch = $r_Customer['ManagerName'] . '<input type="hidden" name="CityId" value="' . $CityId . '">';
}
// Se la pagina di provenienza è tbl_article_base.php
else if ($BackPage == 'tbl_article_base.php')
{
    $rs_Customer = $rs->Select('Customer', "CityId='" . ENTE_BASE . "'");
    $r_Customer = mysqli_fetch_array($rs_Customer);
    $str_CustomerSearch = $r_Customer['ManagerName'] . '<input type="hidden" name="CityId" value="' . ENTE_BASE . '">';
}
else
{
    $str_CustomerSearch = CreateSelect("Customer", "1=1", "ManagerName", "CityId", "CityId", "ManagerName", $CityId, true);
}


$a_Lan = unserialize(LANGUAGE_KEYS);
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
        <textarea class="form-control frm_field_string" id="Description_' . $tag . '" name="Description' . $tag . '" style="height:5.8rem;margin-left:0;"></textarea>
    </div>';
        if($LangN % 2 == 0) $str_ArticleDescription .= '<div class="clean_row HSpace4"></div>';
    }
    
}

$str_out .= '
<div class="col-sm-12">
    <form method="post" enctype="multipart/form-data name="f_article" id="f_article" action="tbl_article_add_exe.php' . $str_GET_Parameter . '&BackPage=' . $BackPage . '">
        <input type="hidden" name="Id" value="0">
        <div class="col-sm-12 table_label_H" style="text-align:center">
            Inserisci articolo
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-12">
            <div class="col-sm-12 alert alert-danger" style="display: flex;margin: 0px;align-items: center;">
                <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
                <div class="col-sm-11" style="font-size: 1.2rem;">
                    <ul>
                        <li>I campi con dicitura "Ente" sono campi per specificare varianti particolari degli articoli specifiche dell\'ente (usati anche nelle importazioni)</li>
                        <li>
                            La scelta della tipologia di sanzione accessoria determina diversi comportamenti nella procedura di registrazione degli atti da applicazione quando viene selezionto l\'articolo:
                            <ul style="list-style-position: inside;">
                                <li>Non prevista: non verrà mostrato alcun dato</li>
                                <li>Fissa: verrà precaricato il testo della sanzione accessoria definita nell\'articolo. Il campo non è modificabile</li>
                                <li>Variabile: verrà precaricato il testo della sanzione accessoria definita nell\'articolo. Il campo è modificabile e dovrà essere variato dall\'utente</li>
                            </ul>
                        </li>
        
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
            Genere
        </div>
        <div class="col-sm-3 BoxRowCaption" style="text-overflow: ellipsis;overflow: hidden;white-space: nowrap;">
           ' . $_SESSION['ruletypetitle'] . '
        </div>
        <div class="col-sm-8 BoxRowLabel">
        </div>
               
        <div class="clean_row HSpace4"></div>
               
        <div class="col-sm-1 BoxRowLabel">
            Ente
        </div>
        <div class="col-sm-3 BoxRowCaption" style="text-overflow: ellipsis;overflow: hidden;white-space: nowrap;">
           ' . $str_CustomerSearch . '
        </div>
        <div class="col-sm-2 BoxRowLabel">
            Particella verbale
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_string" name="ArticleLetterAssigned" id="ArticleLetterAssigned" type="text" value="" style="width:5rem">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Categoria
        </div>
        <div class="col-sm-2 BoxRowCaption">
           ' . CreateSelect("ViolationType", "RuleTypeId=$RuleTypeId", "Id", "ViolationTypeId", "Id", "Title", "", true) . '
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Anno
        </div>
        <div class="col-sm-1 BoxRowCaption">
            ' . $_SESSION['year'] . '
            <input type="hidden" name="Year" value="' . $_SESSION['year'] . '">
        </div>
                
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-4 BoxRowLabel" style="border-right: 1px solid #E7E7E7;">
            Articolo:
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Art.
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control txt-warning frm_field_string" type="text" name="Article" id="Article" value="">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Comma
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_string" type="text" name="Paragraph" id="Paragraph" value="">
        </div>
        <div class="col-sm-1 BoxRowLabel">
            Lettera
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_string" type="text" name="Letter" value="">
        </div>';
        //TODO BUG 3239 rimosso in quanto sviluppo annullato
//         <div class="col-sm-1 BoxRowLabel">
//             Tipo veicolo
//         </div>
//         <div class="col-sm-1 BoxRowCaption">
//             '.CreateSelect('VehicleType', '1=1', 'TitleIta', 'VehicleTypeId', 'Id', 'TitleIta', '', false).'
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
            <input class="form-control frm_field_numeric" type="text" name="Id1" value="">
        </div>
        <div class="col-sm-1 BoxRowLabel table_caption_error">
            Comma
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_string" type="text" name="Id2" value="">
        </div>
        <div class="col-sm-1 BoxRowLabel table_caption_error">
            Lettera
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_string" type="text" name="Id3" value="">
        </div>
        <div class="col-sm-1 BoxRowLabel table_caption_error">
            Codice ente
        </div>
        <div class="col-sm-1 BoxRowCaption">
            <input class="form-control frm_field_string" name="ArtComune" type="text" value="" style="width:8rem">
        </div>
                
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-4 BoxRowLabel">
            Importo minimo
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input class="form-control frm_field_currency frm_field_required" name="Fee" id="Fee" type="text" value="" style="width:10rem">
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Importo massimo
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input  class="form-control frm_field_currency frm_field_required" name="MaxFee" id="MaxFee" type="text" value="" style="width:10rem">
        </div>
                
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-4 BoxRowLabel">
            Decurtazione
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input class="form-control frm_field_numeric frm_field_required" id="LicensePoint" name="LicensePoint" type="text" value="0" style="width:10rem">
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Decurtazione neopatentati
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input class="form-control frm_field_numeric frm_field_required" id="YoungLicensePoint" name="YoungLicensePoint" type="text" value="0" style="width:10rem">
        </div>
                
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-3 BoxRowLabel">
            Presentazione documenti (180/8) Entro:
        </div>
        <div class="col-sm-1 BoxRowCaption BoxRowLabel">
            <div class="col-sm-6">
                <input type="text" disabled class="form-control frm_field_numeric"/>&nbsp;
            </div>
            <div class="col-sm-6">
                &nbsp;giorni
            </div>
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input id="PresentationDocument" name="PresentationDocument" type="checkbox" />
        </div>
        <div class="col-sm-6 BoxRowLabel">
        </div>
                
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-4 BoxRowLabel">
            Applica art. 126 bis
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input id="126Bis" name="126Bis" type="checkbox" />
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Add.le massa (Sup. 3,5 ton)
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input name="AdditionalMass" type="checkbox" />
        </div>
                
        <div id="DIV_LicensePointCode1" style="display:none;">
                
            <div class="clean_row HSpace4"></div>
                
            <div class="col-sm-4 BoxRowLabel">
                Codice decurtazione punti
                <i data-toggle="tooltip" data-placement="top" data-container="body" data-html="true" title="'.LICENSE_POINT_REDUCTION_CODE_TOOLTIP.'" class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem; line-height: 2rem; float: right;"></i>
                <i data-toggle="tooltip" data-placement="top" data-container="body" title="Fare click per aprire la tabella punti patente MCTC" class="licensepointcodetable tooltip-r fa fa-file" style="margin-right: 1rem; line-height: 2rem; float: right;"></i>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <div class="col-sm-4" style="padding-left:0;">
                    <input data-licensecodenumber="1" id="LicensePointCode1P1" name="LicensePointCode1P1" type="text" class="text-uppercase form-control frm_field_string txt-warning" />
                </div>
                <div class="col-sm-4">
                    <input data-licensecodenumber="1" id="LicensePointCode1P2" name="LicensePointCode1P2" type="text" class="text-uppercase form-control frm_field_string txt-warning" />
                </div>
                <div class="col-sm-4">
                    <input data-licensecodenumber="1" id="LicensePointCode1P3" name="LicensePointCode1P3" type="text" class="text-uppercase form-control frm_field_string txt-warning" />
                </div>
            </div>
        </div>
        <div id="DIV_LicensePointCode2" style="display:none;">
            <div class="col-sm-4 BoxRowLabel">
                Codice decurtazione punti (recidiva)
                <i data-toggle="tooltip" data-placement="top" data-container="body" data-html="true" title="'.LICENSE_POINT_REDUCTION_CODE_TOOLTIP.'" class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem; line-height: 2rem; float: right;"></i>
                <i data-toggle="tooltip" data-placement="top" data-container="body" title="Fare click per aprire la tabella codici decurtazione" class="licensepointcodetable tooltip-r fa fa-file" style="margin-right: 1rem; line-height: 2rem; float: right;"></i>
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <div class="col-sm-4" style="padding-left:0;">
                    <input data-licensecodenumber="2" id="LicensePointCode2P1" name="LicensePointCode2P1" type="text" class="text-uppercase form-control frm_field_string txt-warning" />
                </div>
                <div class="col-sm-4">
                    <input data-licensecodenumber="2" id="LicensePointCode2P2" name="LicensePointCode2P2" type="text" class="text-uppercase form-control frm_field_string txt-warning" />
                </div>
                <div class="col-sm-4">
                    <input data-licensecodenumber="2" id="LicensePointCode2P3" name="LicensePointCode2P3" type="text" class="text-uppercase form-control frm_field_string txt-warning" />
                </div>
            </div>
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-4 BoxRowLabel">
            Add.le notte (22:01 – 6:59)
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input name="AdditionalNight" type="checkbox" />
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Sanzione fissata da Prefettura
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input name="PrefectureFixed" id="PrefectureFixed" type="checkbox" />
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-4 BoxRowLabel">
            Riduzione sanzione del 30% in caso di pagamento entro 5 giorni
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input id="ReducedPayment" name="ReducedPayment" type="checkbox" />
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Gestisci la preventiva trasmissione del verbale tramite posta ordinaria
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input name="Amicable" id="Amicable" type="checkbox" />
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-4 BoxRowLabel">
            Sanzione a carattere penale
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input id="PenalSanction" name="PenalSanction" type="checkbox" />
        </div>
        <div class="col-sm-6 BoxRowLabel">
        </div>
                    
        <div class="clean_row HSpace4"></div>
                    
        <div class="col-sm-3 BoxRowLabel">
            Sanzione accessoria / Provvedimento amministrativo:
        </div>
        <div class="col-sm-1 BoxRowCaption BoxRowLabel">
            ' . CreateArraySelect($a_UseAdditionalSanction, true, 'UseAdditionalSanction', 'UseAdditionalSanction', USEADDITIONALSANCTION_NON_PREVISTA, true) . '
        </div>
        <div class="col-sm-8 BoxRowCaption">
            ' . CreateSelectConcat("SELECT A.Id,CONCAT_WS(' - ',A.Progressive,A.TitleIta) Title FROM AdditionalSanction A JOIN AdditionalSanctionType AST ON A.AdditionalSanctionTypeId = AST.Id WHERE A.CityId IN(".($BackPage == 'tbl_article_base.php' ? "''" : "'','$CityId'").") AND AST.RuleTypeId=$RuleTypeId AND Disabled=0 ORDER BY A.Progressive", "AdditionalSanctionId", "Id", "Title", "", false, null, null, true) . '
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
            <input id="SuspensionLicense" name="SuspensionLicense" type="checkbox" />
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Revisione della patente - Art. 128 C.d.S.
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input id="RevisionLicense" name="RevisionLicense" type="checkbox" />
        </div>
                
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-4 BoxRowLabel">
            Sospensione della patente in caso di recidiva nel biennio
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input id="Habitual" name="Habitual" type="checkbox" />
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Revisione della patente in caso di recidiva nel biennio
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input id="RevisionHabitual" name="RevisionHabitual" type="checkbox" />
        </div>
                
                
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-4 BoxRowLabel">
            Ritiro della patente - Art. 233 C.d.S.
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input id="LossLicense" name="LossLicense" type="checkbox" />
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Revoca della patente - Art. 219 C.d.S.
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input id="RevocationLicense" name="RevocationLicense" type="checkbox" />
        </div>
                
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-4 BoxRowLabel">
            Ritiro della patente in caso di recidiva nel biennio
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input id="LossHabitual" name="LossHabitual" type="checkbox" />
        </div>
        <div class="col-sm-4 BoxRowLabel">
            Revoca della patente in caso di recidiva nel biennio
        </div>
        <div class="col-sm-2 BoxRowCaption">
            <input id="RevocationHabitual" name="RevocationHabitual" type="checkbox" />
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
            <textarea class="frm_field_required form-control frm_field_string" name="Description' . $a_Lan['Italiano'] . '" style="height:5.8rem;margin-left:0;"></textarea>
        </div>
                
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-4 BoxRowLabel" style="height:6.4rem">
            Testo addizionale
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:6.4rem">
            <textarea class="frm_field_string form-control" name="AdditionalTextIta" style="height:5.8rem;margin-left:0;"></textarea>
        </div>
                
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-4 BoxRowLabel" style="height:6.4rem">
            Note
        </div>
        <div class="col-sm-8 BoxRowCaption" style="height:6.4rem">
            <textarea class="frm_field_string form-control" name="Note" style="height:5.8rem;margin-left:0;"></textarea>
        </div>
                
        <div class="clean_row HSpace4"></div>
                
        <div class="col-sm-12 table_caption_I BoxRowLabel" style="text-align:center">
            Testi (Estero)
        </div>
                
        <div class="clean_row HSpace4"></div>
                
        ' . $str_ArticleDescription . '
            
        <div class="clean_row HSpace4"></div>
            
        <div class="table_label_H HSpace4" style="height:8rem;">
            <button type="submit" id="insert" class="btn btn-success" style="margin-top:2rem;width:inherit;"><i class="fa fa-plus fa-fw"></i> Inserisci</button>
        	<button type="button" id="back" class="btn btn-default" style="margin-top:2rem;">Indietro</button>
        </div>
    </form>
</div>
            
';

echo $str_out;
?>

<script type="text/javascript">
        var REGEX1 = new RegExp('<?= LICENSE_POINT_REDUCTION_CODE_REGEXP1; ?>');
        var REGEX2 = new RegExp('<?= LICENSE_POINT_REDUCTION_CODE_REGEXP2; ?>');
        var REGEX3 = new RegExp('<?= LICENSE_POINT_REDUCTION_CODE_REGEXP3; ?>');

		$('document').ready(function(){
            setTimeout(function(){
    			$('#f_article input,textarea').siblings('.help-block').css({"top": "0.3rem", "left": "-4.5rem"});
    			$('#f_article #LicensePointCode1P1,#LicensePointCode2P1').siblings('.help-block[data-bv-validator="callback"]').css({"top": "0rem", "left": "-18.5rem"});
            }, 100);
		});

        $(".licensepointcodetable").hover(function(){
            $(this).css("cursor","pointer");
        },function(){
            $(this).css("cursor","");
        });

        $(".licensepointcodetable").on('click', function(){
        	window.open('<?= $MainPath."/doc/".$LicensePointTableFile; ?>', '_blank');
        });

        $('#back').click(function () {
            window.location = "<?=$BackPage . $str_GET_Parameter?>";
            return false;
        });
		$("#PrefectureFixed").change(function(){
			if($(this).prop("checked")){
				$("#f_article").bootstrapValidator('removeField', $('#Fee'));
				$("#f_article").bootstrapValidator('removeField', $('#MaxFee'));
				$("#Fee").removeClass("frm_field_required");
				$("#MaxFee").removeClass("frm_field_required");
			} else{
				$("#f_article").bootstrapValidator('addField', $('#Fee'));
				$("#f_article").bootstrapValidator('addField', $('#MaxFee'));
				$("#Fee").addClass("frm_field_required");
				$("#MaxFee").addClass("frm_field_required");
			}
			$('#f_article').data('bootstrapValidator').resetForm();
		});
		$("#LicensePoint, #YoungLicensePoint, #126Bis").change(function(){
			if($('#126Bis').prop("checked")){
				if($('#LicensePoint').val() <= 0 && $('#YoungLicensePoint').val() <= 0){
					$("#DIV_LicensePointCode1").hide();
					$("#DIV_LicensePointCode2").hide();
				} else {
					$("#DIV_LicensePointCode1").show();
					if($('#Habitual').prop("checked")){
						$("#DIV_LicensePointCode2").show();
					}
				}
			} else {
				$("#DIV_LicensePointCode1").hide();
				$("#DIV_LicensePointCode2").hide();
			}
		});
		$("#Habitual").change(function(){
			if($(this).prop("checked") && ($('#LicensePoint').val() > 0 || $('#YoungLicensePoint').val() > 0)){
				$("#DIV_LicensePointCode2").show();
			} else {
				$("#DIV_LicensePointCode2").hide();
			}
		});
		$("#UseAdditionalSanction").change(function(){
			if($(this).val() == '<?= USEADDITIONALSANCTION_NON_PREVISTA; ?>'){
				$("#AdditionalSanctionId").val('').prop('disabled', true);
			} else {
				$("#AdditionalSanctionId").prop('disabled', false);
			}
		});
		

	    $(document).on('change', "#LicensePointCode1P1, #LicensePointCode1P2, #LicensePointCode1P3, #LicensePointCode2P1, #LicensePointCode2P2, #LicensePointCode2P3", function () {
	    	var n = $(this).data('licensecodenumber');
			var fields = $('#LicensePointCode'+n+'P1, #LicensePointCode'+n+'P2, #LicensePointCode'+n+'P3');
            var LicensePointCodeP1 = $('#LicensePointCode'+n+'P1').val();
            var LicensePointCodeP2 = $('#LicensePointCode'+n+'P2').val();
            var LicensePointCodeP3 = $('#LicensePointCode'+n+'P3').val();
            
	    	$(fields).removeClass("txt-warning");

	        if (REGEX1.test(LicensePointCodeP1) && REGEX2.test(LicensePointCodeP2) && REGEX3.test(LicensePointCodeP3)) {
	        	$(fields).addClass("txt-success");
	        	$(fields).removeClass("txt-danger");
	        	$('#LicensePointCode'+n+'P1').siblings('.help-block[data-bv-validator="callback"]').hide();
	        } else {
	        	$(fields).addClass("txt-danger");
	        	$(fields).removeClass("txt-success");
	        	$('#LicensePointCode'+n+'P1').siblings('.help-block[data-bv-validator="callback"]').show();
	        }
	    });

	    $("#LicensePointCode1P1, #LicensePointCode1P2, #LicensePointCode1P3, #LicensePointCode2P1, #LicensePointCode2P2, #LicensePointCode2P3").on({
	    	  keydown: function(e) {
	    	    if (e.which === 32)
	    	      return false;
	    	  },
	    	  change: function() {
	    	    this.value = this.value.replace(/\s/g, "");
	    	  }
	    	});

	    $("#LicensePointCode1P1, #LicensePointCode1P2, #LicensePointCode1P3").on('change', function(){
	    	$('#f_article').bootstrapValidator('updateStatus', $("#LicensePointCode1P1"), 'NOT_VALIDATED');
	    });
	    $("#LicensePointCode2P1, #LicensePointCode2P2, #LicensePointCode2P3").on('change', function(){
	    	$('#f_article').bootstrapValidator('updateStatus', $("#LicensePointCode2P1"), 'NOT_VALIDATED');
	    });

        $('#f_article').bootstrapValidator({
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

                frm_field_currency: {
                    selector: '.frm_field_currency',
                    validators: {
                        numeric: {
                            message: 'Valuta'
                        }
                    }
                },


                frm_field_numeric: {
                    selector: '.frm_field_numeric',
                    validators: {
                        numeric: {
                            message: 'Numero'
                        }
                    }
                },

        		NumericNotEmpty: {
        			selector:"#Article",
            		validators: {
            			numeric: {
            				message: 'Numero'
                        },
                        notEmpty: {
                            message: 'Richiesto'
                        }
            		}
        		},

                LicensePointCode1: {
                	selector:"#LicensePointCode1P1",
                    validators: {
                        callback:{
                            message: 'Uno o più campi non sono validi',
                            callback: function(value, password, $field){
                                var LicensePointCode1P1 = $('#LicensePointCode1P1').val();
                                var LicensePointCode1P2 = $('#LicensePointCode1P2').val();
                                var LicensePointCode1P3 = $('#LicensePointCode1P3').val();
                                if(!$('#LicensePointCode1P1').val() || !$('#LicensePointCode1P2').val() || !$('#LicensePointCode1P3').val()){
                                    return {
                                        valid: false,
                                        message: 'Richiesto'
                                    };
                                 }

                                if (REGEX1.test(LicensePointCode1P1) && REGEX2.test(LicensePointCode1P2) && REGEX3.test(LicensePointCode1P3)) {
                                	return true;
                                } else {
                                    return {
                                        valid: false,
                                        message: 'Uno o più campi non sono validi'
                                    };
                                }
                                return true;
                            }           
                        }    
                            
                    }
        		},

                LicensePointCode2: {
                	selector:"#LicensePointCode2P1",
                    validators: {
                        callback:{
                            message: 'Uno o più campi non sono validi',
                            callback: function(value, password, $field){
                                var LicensePointCode2P1 = $('#LicensePointCode2P1').val();
                                var LicensePointCode2P2 = $('#LicensePointCode2P2').val();
                                var LicensePointCode2P3 = $('#LicensePointCode2P3').val();
                                if(!$('#LicensePointCode2P1').val() || !$('#LicensePointCode2P2').val() || !$('#LicensePointCode2P3').val()){
                                    return {
                                        valid: false,
                                        message: 'Richiesto'
                                    };
                                 }

                                if (REGEX1.test(LicensePointCode2P1) && REGEX2.test(LicensePointCode2P2) && REGEX3.test(LicensePointCode2P3)) {
                                	return true;
                                } else {
                                    return {
                                        valid: false,
                                        message: 'Uno o più campi non sono validi'
                                    };
                                }
                                return true;
                            }           
                        }    
                            
                    }
        		},

            }
        }).on('success.form.bv', function(e){
        	var fee = parseFloat($('#Fee').val());
        	var maxFee = parseFloat($('#MaxFee').val());
        	var licensePoint = parseInt($('#LicensePoint').val());
        	var youngLicensePoint = parseInt($('#YoungLicensePoint').val());
        	var foreigncheck = true;

        	if($('#Amicable').is(":checked")){
            	if (
                	$('#LicensePoint').val() > 0 ||
                	$('#YoungLicensePoint').val() > 0 ||
                	$('#AdditionalSanctionId').val() != '' ||
                	!$('#ReducedPayment').is(":checked") ||
                	$('#PresentationDocument').is(":checked") ||
                	$('#SuspensionLicense').is(":checked") ||
                	$('#Habitual').is(":checked") ||
                	$('#126Bis').is(":checked") ||
                	$('#PrefectureFixed').is(":checked")
                	)
            	{
                	alert('"Gestisci la preventiva trasmissione del verbale tramite posta" è permesso solo se:\n- la violazione non prevede la decurtazione di punti dalla patente di guida\n- non prevede l\'applicazione di sanzioni complementari o accessorie\n- prevede la riduzione della sanzione del 30% in caso di pagamento entro 5 giorni\n- nessuna delle opzioni è selezionata eccetto add.le massa e notte');
                	e.preventDefault();
                	return false;
            	}
        	}

        	<?php if ($r_Customer['EnableKindOldProcedure'] != 0): ?>
			if($('#Amicable').is(":checked")){
            	var article = $('#Article').val();
            	var paragraph = $('#Paragraph').val();

            	if ((article == 80 && paragraph == 14) || (article == 193 && paragraph == 2)){
                	alert('"Gestisci la preventiva trasmissione del verbale tramite posta" non è permesso se gli articoli corrispondono a 193/2 e 80/14 e "Attivare procedura speciale per art. 193/2 e 80/14" è abilitato su Ente/Procedure Ente');
                	e.preventDefault();
                	return false;
            	}
        	}
        	<?php endif; ?>

        	if ($('#PenalSanction').is(":checked")){
            	if (fee != maxFee){
                	alert('Gli importi devono corrispondere quando "Sanzione a carattere penale" è selezionato');
                	e.preventDefault();
                	return false;
            	}
            	if ($('#ReducedPayment').is(":checked")){
                	alert('Il pagamento ridotto non è applicabile alle sanzioni aventi carattere penale');
                	e.preventDefault();
                	return false;
            	}
        	} else {
            	if (fee >= maxFee || maxFee <= fee){
                	alert('"Importo minimo" non può essere superiore o uguale a "Importo massimo"');
                	e.preventDefault();
                	return false;
            	}
        	}

        	if ($('#126Bis').is(":checked")){
            	if (licensePoint < 1 || youngLicensePoint < 1){
            		alert('I punti decurtati (neopatentati inclusi) devono essere maggiori di 0 quando "Applica art. 126 bis" è selezionato');
                	e.preventDefault();
                	return false;
            	}
        	}

        	$("[id^=Description_]").each(function() {
            	var valid = true;
            	if($(this).val()){
            		$('[id^=Description_]').not(this).each(function() {
            			if(!$( this ).val()){
                			valid = false;
                			foreigncheck = false;
                			return false;
            			}
            		});
            		return valid;
            	}
    		});

    		if (!foreigncheck){
    			alert('I testi esteri devono essere tutti compilati se almeno uno di essi è stato definito');
            	e.preventDefault();
            	return false;
    		}
        });

    </script>



<?php
include (INC . "/footer.php");

