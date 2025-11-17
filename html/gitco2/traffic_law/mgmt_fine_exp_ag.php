<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$Id= CheckValue('Id','n');

$rs_Fine = $rs->Select('V_Violation',"Id=$Id", "Id");

if(mysqli_num_rows($rs_Fine)==0){
    $rs_Fine = $rs->Select('V_ViolationRent',"Id=$Id", "Id");
    $r_Fine = mysqli_fetch_array($rs_Fine);
    $rs_TrespasserRent = $rs->Select('FineTrespasser', "TrespasserTypeId=11 AND FineId=".$Id);
    
    if(mysqli_num_rows($rs_TrespasserRent)==0){
        $rs_TrespasserRent = $rs->Select('FineTrespasser', "TrespasserTypeId=10 AND FineId=".$Id);
        
        if(mysqli_num_rows($rs_TrespasserRent)==0){
            $TrespasserId = 0;
        }else{
            $r_TrespasserRent = mysqli_fetch_array($rs_TrespasserRent);
            $TrespasserId = $r_TrespasserRent['TrespasserId'];
        }
        $rs_Trespasser = $rs->Select('V_Trespasser',"Id=".$TrespasserId);
        $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
        $TrespasserTitle = 'NOLEGGIO';
    }else{
        $r_TrespasserRent = mysqli_fetch_array($rs_TrespasserRent);
        $TrespasserId = $r_TrespasserRent['TrespasserId'];
        $rs_Trespasser = $rs->Select('V_Trespasser',"Id=".$TrespasserId);
        $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
        $TrespasserTitle = 'LOCATARIO';
    }
} else {
    $r_Fine = mysqli_fetch_array($rs_Fine);
    $TrespasserId = $r_Fine['TrespasserId'] ?? 0;
    $rs_Trespasser = $rs->Select('V_Trespasser',"Id=".$TrespasserId);
    $r_Trespasser = mysqli_fetch_array($rs_Trespasser);
    $TrespasserTitle = 'TRASGRESSORE';
}

$rs_FineOwner = $rs->Select('FineOwner',"FineId=".$Id);
$r_FineOwner = mysqli_fetch_array($rs_FineOwner);
$str_ArticleDescription = strlen(trim($r_FineOwner['ArticleDescription'.LAN]))>0 ? $r_FineOwner['ArticleDescription'.LAN] : $r_Fine['ArticleDescription' . LAN];
$str_ReasonDescription = strlen(trim($r_FineOwner['ReasonDescription'.LAN]))>0 ? $r_FineOwner['ReasonDescription'.LAN] : $r_Fine['ReasonTitle' . LAN];


$rs_Article = $rs->SelectQuery("
    SELECT A.*,AT.Fee,AT.MaxFee 
    FROM FineArticle FA 
    JOIN Article A ON FA.ArticleId=A.Id 
    JOIN ArticleTariff AT ON A.Id=AT.ArticleId AND AT.Year={$_SESSION['year']}
    WHERE FineId=$Id");
$r_Article = mysqli_fetch_array($rs_Article);

$AdditionalFee = $r_Customer['NationalTotalFee'];
$TotalFee = $r_Article['Fee'] + $AdditionalFee;

if($r_Fine['DetectorId']==0) $DetectorTitle = "";
else {
    $detectors = $rs->Select('Detector',"Id=".$r_Fine['DetectorId']);
    $detector = mysqli_fetch_array($detectors);
    
    $DetectorTitle = $detector['Title'.LAN];
}

$str_Folder = ($r_Fine['CountryId']=='Z000') ? 'doc/national/violation' : 'doc/foreign/violation';

$str_tree = "";
$str_Img = "";
$doc_rows = $rs->Select('FineDocumentation',"FineId=".$Id, "Id");
$doc_n = mysqli_num_rows($doc_rows);






if ($doc_n > 0) {
    $doc_row = mysqli_fetch_array($doc_rows);
    
    $File = $str_Folder . '/' . $_SESSION['cityid'] . '/' . $r_Fine['Id'] . '/' . $doc_row['Documentation'];
    if (strtolower(substr($doc_row['Documentation'], -3)) == "jpg") {
        $str_Img = '
            $("#preview").attr("src","' . $File . '");
            $("#preview_img").show();
        ';
    } else {
        //$str_Img = '$("#preview_img").html("<iframe style=\"width:100%; height:100%\" src=\"' . $File . '\"></iframe>");';
        $str_Img = '
            $("#preview_doc").html("<object><embed width=\"100%\" height=\"100%\" src=\"' . $File . '\" type=\"application/pdf\" /></object>");
            $("#preview_doc").show();
            ';
        
        
    }
    
    $str_tree = '
            $("#fileTreeDemo_1").fileTree({ root:\'' . $str_Folder . '/' . $_SESSION['cityid'] . '/' . $r_Fine['Id'] . '/\', script: \'jqueryFileTree.php\' }, function(file) {
                
            var FileType = file.substr(file.length - 3);
                
            if(FileType.toLowerCase()==\'pdf\' || FileType.toLowerCase()==\'doc\'){
                $("#preview_img").hide();
                $("#preview_doc").html("<iframe style=\"width:100%; height:100%\" src=\'"+file+"\'></iframe>");
                $("#preview_doc").show();
            }else{
                $("#preview_doc").hide();
                $("#preview").attr("src",file);
                $("#preview_img").show();
            }
        });
    ';
    
}

echo $str_out;
?>

<div class="row-fluid">
    <form name="f_fine_exp_ag" id="f_fine_exp_ag" method="post" action="mgmt_fine_exp_ag_exe.php">
    <input type="hidden" name="Filters" value="<?= $str_GET_Parameter; ?>">
    <input type="hidden" name="Id" value="<?= $Id; ?>">
    <input type="hidden" name="ultimate" value="1">
        
        <div class="col-sm-6">
            <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            	VERBALE
        	</div>
        	
        	<div class="clean_row HSpace4"></div>
        	
            <div class="col-sm-2 BoxRowLabel">
                Cronologico
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <?= $r_Fine['ProtocolId']; ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Riferimento
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <?= $r_Fine['Code']; ?>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
            	Data
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <?= DateOutDB($r_Fine['FineDate']); ?>
            </div>
                    
            <div class="col-sm-2 BoxRowLabel">
                Ora
            </div>
            <div class="col-sm-4 BoxRowCaption">
                <?= TimeOutDB($r_Fine['FineTime']); ?>
            </div>
                
            <div class="clean_row HSpace4"></div>
            
			<div class="col-sm-1 BoxRowLabel">
                Comune
            </div>
            <div class="col-sm-3 BoxRowCaption">
            	<?= $r_Fine['CityTitle']; ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Strada
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= $r_Fine['StreetTypeTitle']; ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Località
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <?= $r_Fine['Address']; ?>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Tipo veicolo
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <?= $r_Fine['VehicleTitleIta']; ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Nazione
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <?= $r_Fine['VehicleCountry']; ?>
            </div>
            <div class="col-sm-1 BoxRowLabel">
                Dip.
            </div>
            <div class="col-sm-1 BoxRowCaption">
                <?= ($r_Fine['CountryId'] == "Z110" ? $r_Fine['DepartmentId'] : ""); ?>
			</div>
			
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-3 BoxRowLabel">
                Targa
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <?= $r_Fine['VehiclePlate']; ?>
            </div>
            <div class="col-sm-3 BoxRowLabel">
                Massa
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <?= $r_Fine['VehicleMass']; ?>
            </div>
                        
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Colore
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= $r_Fine['VehicleColor']; ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Marca
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= $r_Fine['VehicleBrand']; ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Modello
            </div>
            <div class="col-sm-2 BoxRowCaption">
                <?= $r_Fine['VehicleModel']; ?>
            </div>
                
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-2 BoxRowLabel">
                Rilevatore
            </div>
            <div class="col-sm-5 BoxRowCaption">
                <?= $DetectorTitle; ?>
            </div>
            <div class="col-sm-2 BoxRowLabel">
                Ora
                <i data-toggle="tooltip" data-placement="top" data-container="body" class="tooltip-r glyphicon glyphicon-info-sign" style="margin-right: 1rem;line-height:2rem;float: right;" data-title="L'ora solare vige dalla fine di Ottobre alla fine di Marzo. L'ora legale vige dalla fine di Marzo alla fine di Ottobre ed è uguale all'ora solare +1 ora."></i>
            </div>
            <div class="col-sm-3 BoxRowCaption">
                <?= $a_TimeTypeId[$r_Fine['TimeTypeId']]; ?>
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <?php if($r_Fine['Speed'] > 0): ?>
    			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
    				VELOCITA
				</div>
    			<div class="col-sm-2 BoxRowLabel">
    				Limite
				</div>
				<div class="col-sm-2 BoxRowCaption">
				    <?= round($r_Fine['SpeedLimit']); ?>
				</div>
				<div class="col-sm-2 BoxRowLabel">
    				Rilevata
				</div>
				<div class="col-sm-2 BoxRowCaption">
    				<?= round($r_Fine['SpeedControl']); ?>
				</div>
				<div class="col-sm-2 BoxRowLabel">
    				Effettiva
				</div>
				<div class="col-sm-2 BoxRowCaption">
    				<?= round($r_Fine['Speed']); ?>
				</div>
				
  				<div class="clean_row HSpace4"></div>
            <?php endif; ?>
            
            <?php if($r_Fine['TimeTLightFirst'] > 0): ?>
    			<div class="col-sm-12 BoxRowLabel" style="text-align:center">
    				SEMAFORO
				</div>
    			<div class="col-sm-4 BoxRowLabel">
    				Primo fotogramma
				</div>
				<div class="col-sm-2 BoxRowCaption">
				     <?= $r_Fine['TimeTLightFirst']; ?>
				</div>
				<div class="col-sm-4 BoxRowLabel">
    				Secondo fotogramma
				</div>
				<div class="col-sm-2 BoxRowCaption">
    				<?= $r_Fine['TimeTLightSecond']; ?>
				</div>
				
  				<div class="clean_row HSpace4"></div>
            <?php endif; ?>
            
			<div class="col-sm-2 BoxRowLabel">
				Articolo
			</div>
			<div class="col-sm-2 BoxRowCaption">
			    <?= $r_Fine['Article']; ?>
			</div>
			<div class="col-sm-2 BoxRowLabel">
				Comma
			</div>
			<div class="col-sm-2 BoxRowCaption">
				<?= $r_Fine['Paragraph']; ?>
			</div>
				    
			<div class="col-sm-2 BoxRowLabel">
				Lettera
			</div>
			<div class="col-sm-2 BoxRowCaption">
				<?= $r_Fine['Letter']; ?>
			</div>
			
			<div class="clean_row HSpace4"></div>
			
			<div class="col-sm-12 BoxRowLabel" style="height:6rem;background-color: rgb(40, 114, 150);">
    			<span id="span_Article" style="font-size:1.1rem;"><?= $str_ArticleDescription; ?></span>
			</div>
    			
			<div class="clean_row HSpace4"></div>

			<div class="col-sm-4 BoxRowLabel">
				Min/Max edittale
			</div>
			<div class="col-sm-4 BoxRowCaption">
				<?= NumberDisplay($r_Article['Fee']); ?> / <?= NumberDisplay($r_Article['MaxFee']); ?>
			</div>
			<div class="col-sm-2 BoxRowLabel">
				Spese notifica
			</div>
			<div class="col-sm-2 BoxRowCaption">
		    	<?= NumberDisplay($AdditionalFee); ?>
			</div>

            <div class="clean_row HSpace4"></div>
            
			<div class="col-sm-4 BoxRowLabel">
				Importo totale
			</div>
			<div class="col-sm-8 BoxRowCaption">
				<?= NumberDisplay($TotalFee); ?>
			</div>
			
			<div class="clean_row HSpace4"></div>
			
			<div class="col-sm-2 BoxRowLabel">
				Tipo infrazione
			</div>
			<div class="col-sm-10 BoxRowCaption">
				<span id="span_ViolationTitle"><?= $r_Fine['ViolationTitle']; ?></span>
			</div>
			
			<div class="clean_row HSpace4"></div>
			
			<div class="col-sm-2 BoxRowLabel">
				Accertatore
			</div>
			<div class="col-sm-4 BoxRowCaption">
				<?= $r_Fine['ControllerCode'].' - '.StringOutDB($r_Fine['ControllerName']); ?>
			</div>
			
			<?php if($r_Fine['ControllerDate'] != ""): ?>
                <div class="col-sm-3 BoxRowLabel">
                    Data e ora accertamento
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <?= DateOutDB($r_Fine['ControllerDate']).' '.$r_Fine['ControllerTime']; ?>
                </div>
            <?php else: ?>
                <div class="col-sm-6 BoxRowCaption">
                </div>
            <?php endif; ?>
			
			<div class="clean_row HSpace4"></div>
			
			<div class="col-sm-3 BoxRowLabel" style="height:6rem;">
				Mancata contestazione
			</div>
			<div class="col-sm-9 BoxRowCaption" style="height:6rem;">
				<span id="span_ReasonDescription" style="height:6rem;width:40rem;font-size:1.1rem;"><?= StringOutDB($str_ReasonDescription); ?></span>
			</div>
				
			<div class="clean_row HSpace4"></div>
			
			<div class="col-sm-3 BoxRowLabel" style="height:6.4rem;">
				Note operatore
			</div>
			<div class="col-sm-9 BoxRowCaption" style="height:6.4rem;">
       			 <?= StringOutDB($r_Fine['Note']); ?>
			</div>
			
			<div class="clean_row HSpace4"></div>
			
            <div class="col-sm-12 BoxRowLabel table_caption_I text-center">
            	PARAMETRI DI ELABORAZIONE
        	</div>
        	
        	<div class="clean_row HSpace4"></div>
			
            <div class="col-sm-3 BoxRowLabel">
            	Destinazione stampa
        	</div>
            <div class="col-sm-6 BoxRowCaption">
                <?= CreateArraySelect(array(1 => 'Consegna in ufficio', 2 => 'Da spedire'), true, 'PrintDestination', 'PrintDestination', null, true, null, 'frm_field_required'); ?>
            </div>
			<div class="col-sm-3 BoxRowCaption">
            </div>
            
            <div class="clean_row HSpace4"></div>
            
            <?php if($r_Article['Article'] == '193' && $r_Article['Paragraph'] == '2'): ?>
                <div class="col-sm-3 BoxRowLabel">
                	Articolo
            	</div>
                <div class="col-sm-6 BoxRowCaption">
                    <?= CreateSelectQueryExtended("SELECT A.*,AT.Fee,AT.MaxFee,CONCAT_WS(' ',A.Article,A.Paragraph,A.Letter,' - Min:',AT.Fee,' Max:',AT.MaxFee) AS FullArticle FROM Article A JOIN ArticleTariff AT ON AT.ArticleId=A.Id AND AT.Year={$_SESSION['year']} WHERE Article=193 AND Paragraph=2 AND CityId='{$_SESSION['cityid']}' ORDER BY A.Article,A.Paragraph,A.Letter", 'ArticleSelect', 'ArticleSelect', 'Id', 'FullArticle', array('Fee','MaxFee','DescriptionIta'), $r_Article['Id'], true, null, 'frm_field_required'); ?>
                </div>
                <div class="col-sm-3 BoxRowCaption">
            	</div>
            	            	
	            <div class="clean_row HSpace4"></div>
            
                <div class="col-sm-3 BoxRowLabel">
                	Min/Max edittale
            	</div>
                <div id="ArticleFee" class="col-sm-9 BoxRowCaption">
            	</div>
            	
            	<div class="clean_row HSpace4"></div>
            	
                <div class="col-sm-3 BoxRowLabel" style="height:6.4rem;">
                	Descrizione Articolo
            	</div>
                <div id="ArticleDescription" class="col-sm-9 BoxRowCaption" style="height:6.4rem;">
        		</div>
    		<?php endif; ?>
    	</div>
  		<div class="col-sm-6">
            <?= DivTrespasserView($r_Trespasser, $TrespasserTitle); ?>
            
            <div class="clean_row HSpace4"></div>
            
            <div class="col-sm-12 BoxRowTitle" style="text-align:center">
            	DOCUMENTAZIONE
        	</div>
            <div class="col-sm-12" style="width:100%;">
                <div class="example">
                    <div id="fileTreeDemo_1" class="BoxRowLabel" style="height:9rem;overflow:auto"></div>
                </div>
            </div>
            <div class="col-sm-12 BoxRow" style="width:100%;height:40.2rem;">
                <div class="imgWrapper" style="height:40.2rem;overflow:auto">
                    <img id="preview" class="iZoom"  />
                </div>
            </div>
        </div>
        <div class="col-sm-12 BoxRow" style="height:6rem;">
            <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                <input type="submit" class="btn btn-success" id="update" value="Crea verbale">
                <input type="button" class="btn btn-default" id="back" value="Indietro">
             </div>
        </div>
	</form>
</div>

<script type="text/javascript">

	function fillArticleData(){
		var formatter = new Intl.NumberFormat('it-IT', {
			  minimumFractionDigits: 2,
			  maximumFractionDigits: 2,
			});
		var minFee = formatter.format($('#ArticleSelect').find(":selected").data('fee'));
		var maxFee = formatter.format($('#ArticleSelect').find(":selected").data('maxfee'));
		var description = $('#ArticleSelect').find(":selected").data('descriptionita');
		
		$('#ArticleFee').text(minFee + ' / ' + maxFee);
		$('#ArticleDescription').text(description);
	}

    $('document').ready(function(){

    	fillArticleData();
        
        var del = false;
        //$('#preview').iZoom({diameter:200});
        $('#preview').iZoom({borderColor:'#294A9C', borderStyle:'double', borderWidth: '3px'});


        <?= $str_tree ?>

        <?= $str_Img ?>

        $('#ArticleSelect').on('change', fillArticleData);

        $('#back').click(function(e) {
            e.preventDefault();
            window.location="<?= $str_BackPage.$str_GET_Parameter ?>"
            return false;
        });

        $('#f_fine_exp_ag').bootstrapValidator({
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
            }
        }).on('success.form.bv', function(){
			if(!confirm("Si sta per creare il verbale in maniera definitiva. Continuare?")){
            	return false;
        	}
        });

    });

</script>

<?php
include(INC."/footer.php");

//se stampato in ufficio, come rinotifiche per messo, se da spedire ti fermi alla creazione del verbale
