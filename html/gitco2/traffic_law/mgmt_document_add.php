<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
require_once(CLS . "/cls_view.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
include(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$a_AllowedExtensions = unserialize(GENERIC_DOCUMENT_EXT);
$a_GenericDocumentTypes = unserialize(GENERIC_DOCUMENT_TYPES);

$Id= CheckValue('Id','n');
$Note = CheckValue('Note','s');
$DocumentTypeId = CheckValue('DocumentationTypeId','n');

$viw_Mgmt_FineDocumentation = new CLS_VIEW(MGMT_FINEDOCUMENTATION);
$str_Query = $viw_Mgmt_FineDocumentation->generateSelect(" FineId=".$Id);
$rs_Fine = $rs->SelectQuery($str_Query);
$r_Fine = mysqli_fetch_array($rs_Fine);

echo $str_out;
?>

<div class="row-fluid">
    <div class="col-sm-12">
        <div class="col-sm-12 alert alert-danger" style="display: flex;margin: 0px;align-items: center;">
            <i class="fas fa-2x fa-info-circle col-sm-1" style="text-align:center;"></i>
            <div class="col-sm-11" style="font-size: 1.2rem;">
                Questo punto menù è da usare solo per inserire documenti generici non associati a procedure specifiche. 
				A titolo esemplificativo questa pagina non deve essere usata per inserimento di immagini relative a notifiche, ricorsi e pagamenti in quanto le immagini qui inserite non verrebbero associati ai specifici punti della procedura.
            </div>
        </div>
    </div>

    <div class="clean_row HSpace4"></div>

    <form id="f_document" method="post" action="mgmt_document_add_exe.php" enctype="multipart/form-data">
    	<input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
        <input type="hidden" name="FineId" value="<?= $Id; ?>">
        <input type="hidden" name="CountryId" value="<?= $r_Fine['VehicleCountryId']; ?>">
        <div class="BoxRowTitle col-sm-12">
           Inserisci nuovo documento
        </div>

        <div class="clean_row HSpace4"></div>
        
    	<div class="BoxRowLabel col-sm-12 table_caption_I">Parametri documento</div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <div class="BoxRowLabel col-sm-2">
            Riferimento
        </div>
        <div class="BoxRowCaption col-sm-2">
        	<?= $r_Fine['Code']; ?>
        </div>
        <div class="BoxRowLabel col-sm-1">
            Cronologico
        </div>
        <div class="BoxRowCaption col-sm-1">
        	<?= $r_Fine['ProtocolId'].'/'.$r_Fine['ProtocolYear']; ?>
        </div>
        <div class="BoxRowLabel col-sm-1">
            Targa
        </div>
        <div class="BoxRowCaption col-sm-1">
        	<?= $r_Fine['VehiclePlate']; ?>
        </div>
        <div class="BoxRowLabel col-sm-1">
            Data verbale
        </div>
        <div class="BoxRowCaption col-sm-1">
        	<?= DateOutDB($r_Fine['FineDate']); ?>
        </div>
        <div class="BoxRowLabel col-sm-1">
            Ora verbale
        </div>
        <div class="BoxRowCaption col-sm-1">
        	<?= TimeOutDB($r_Fine['FineTime']); ?>
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="BoxRowLabel col-sm-2">
            Tipo di documento
        </div>
        <div class="BoxRowCaption col-sm-3">
        	<?= CreateSelect('DocumentationType', 'Id IN('.implode(',', $a_GenericDocumentTypes).')', 'Id DESC', 'DocumentationTypeId', 'Id', 'Title', $DocumentTypeId, true); ?>
        </div>
        <div class="BoxRowLabel col-sm-7">
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="col-sm-2 BoxRowLabel" style="height:9rem;">
            Carica Documento
        </div>
        <div class="col-sm-10 BoxRowCaption" style="height:9rem;">
        	<input type="file" class="txt-warning" name="image" id="image" style="margin-bottom:10px; margin-top:10px;">
        	Estensioni consentite: <?= implode(' ', array_keys($a_AllowedExtensions)); ?><br>
        	Dimensione massima: <?= GENERIC_DOCUMENT_MAX_FILE_SIZE; ?> MB
        </div>
        
        <div class="clean_row HSpace4"></div>
        
        <div class="BoxRowLabel col-sm-2" style="height:6.4rem">
            Note
        </div>
        <div class="BoxRowCaption col-sm-10" style="height:6.4rem">
        	<textarea name="Note" class="form-control frm_field_string frm_field_required" style="height:5.8rem;margin-left:0;resize:none;"><?= $Note; ?></textarea>
        </div>
    	
    	<div class="clean_row HSpace4"></div>
    	
        <div class="col-sm-12 BoxRow" style="height:6rem;">
            <div class="col-sm-12 text-center" style="line-height:6rem;">
                <button class="btn btn-success" type="submit"><i class="fa fa-plus fa-fw"></i> Carica</button>
                <button class="btn btn-default" id="back" type="button">Indietro</button>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">

$('document').ready(function(){

    $('#f_document').bootstrapValidator({
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
            image: {
                validators: {
                    file: {
                        maxSize: (<?= GENERIC_DOCUMENT_MAX_FILE_SIZE; ?> * 1024) * 1024,
                        message: 'Il file è troppo grande'
                    },
                    notEmpty: {
                        message: 'Richiesto'
                    }
                }
            }
        }
    });
    
    $('#back').click(function () {
        window.location = "<?= $str_BackPage; ?>";
        return false;
    });
});
</script>
<?php
include(INC."/footer.php");
