<?php
require_once("_path.php");
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");
require_once(CLS."/cls_message.php");
require_once(PGFN."/fn_imp_beta.php");
require_once(INC."/header.php");
require_once(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$ImportFile = CheckValue('ImportFile','s');

$a_VehicleTypes = getImpBetaVehicleTypes();
$a_Controllers = getImpBetaControllers();

$Cont = 0;
$message = new CLS_MESSAGE();
$b_Error = false;

echo $str_out;
?>
    <div class="row-fluid">
        <div class="col-sm-12">
            <div class="table_label_H col-sm-11">ELENCO FILE</div>
            <div class="table_add_button col-sm-1 right">
            </div>
            <div class="clean_row HSpace4"></div>
        </div>

<?php if ($directory_handle = opendir(IMP_BETA_CSV_PATH)):
    while (($file = readdir($directory_handle)) !== false):
        $aFile = explode(".","$file");
        if(strtolower($aFile[count($aFile)-1])=="csv"):
        $Cont++; ?>
        <div class="col-sm-12">
        <div class="table_caption_H col-sm-1"><?=$Cont?></div>
        <div class="table_caption_H col-sm-10"><?=$file?></div>
        <div class="table_caption_button col-sm-1">
           <a href="imp_beta.php?ImportFile=<?=$file?>"><span class="fa fa-upload"></span></a>
            &nbsp;
        </div>
        <div class="clean_row HSpace4"></div>
    </div>
    <?php endif;
    endwhile;
        closedir($directory_handle);
endif;?>

<?php if($Cont==0):?>
        <div class="col-sm-12">
            <div class="table_caption_H col-sm-11">Nessun file presente</div>
            <div class="table_caption_button col-sm-1"></div>
            <div class="clean_row HSpace4"></div>
        </div>
<?php endif;?>
<?php
$cont=0;
$rowArray=array();
if ($ImportFile!='') {
    $file = fopen(IMP_BETA_CSV_PATH . $ImportFile, "r");
    $row = fgets($file);
    if (is_resource($file))
        while ($r = fgetcsv($file, 0, ',', '"')) {
            //Rimuove gli spazi bianchi dalla riga
            $r = array_map('trim', $r);
            
            $rowArray[] = $r;
            $SpeedLimit = $r[52];
            $cont++;
            $detectors = $rs->Select('Detector', "CityId='" . $_SESSION['cityid'] . "' AND Code=" . $r[48]);
            $detector=mysqli_fetch_array($detectors);
            $fines = $rs->Select('Fine', "CityId='".$_SESSION['cityid']."' AND FineDate='".DateInDB($r[3])."' AND FineTime='".$r[4]."' AND REPLACE(VehiclePlate,'  ','')='".$r[7]. "'");

            $fineNumber = mysqli_num_rows($fines);
            if($fineNumber > 0){
                $message->addError('<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i> '.$cont.' : Verbale già presente per '.$r[7]);
                $b_Error = true;
            }
            if ($SpeedLimit > 0) {
                if ($detector==null){
                    $b_Error = true;
                    $message->addError('<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i> '.$cont.' :Rilevatore con cod ' . $r[48] . ' non presente');
                }
                    
                $article=getArticleFromBetaString($r[14],$r[2]);
                if ($article==null){
                    $b_Error = true;
                    $message->addError('<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i> '.$cont.' : Articolo '.$r[14].' con Anno '.$r[2].' non presente');
                }
                    
                $rs_reason = getReasonRs($detector['ReasonId'], $_SESSION['cityid'], $article['ViolationTypeId'], $r[48]);
                if (mysqli_num_rows($rs_reason) == 0){
                    $b_Error = true;
                    $message->addError('<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i> '.$cont.' : Mancata contestazione non presente');
                }
                    
                $controllerCode= ltrim(explode(")",explode("matr.",$r[20])[1])[0],'0');
                $controller=getControllerByCode($a_Controllers, $r[3],$controllerCode );
                
                if($controller==null){
                    $b_Error = true;
                    $message->addError('<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i> '.$cont.' : Accertatore '.$controllerCode.' non presente');
                }
                    
                $VehicleTypeId = decodeVehicleType($r[8]);
                
                if (!isset($a_VehicleTypes[$VehicleTypeId])){
                    $b_Error = true;
                    $message->addError('<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i> '.$cont.' : Veicolo '.$VehicleTypeId.' non presente');
                }
            } else {
                $b_Error = true;
                $message->addError('<i class="glyphicon glyphicon-exclamation-sign" style="color:red"></i> '.$cont.' : Limite velocità non presente');
            }
        }
}
if(count($rowArray)>0):?>
        <div class="col-sm-12">
            <div class="table_label_H col-sm-1">Riga</div>
            <div class="table_label_H col-sm-1">Targa</div>
            <div class="table_label_H col-sm-2">Data</div>
            <div class="table_label_H col-sm-2">Luogo</div>
            <div class="table_label_H col-sm-2">Ril./Data scad</div>
            <div class="table_label_H col-sm-1">Velocità</div>
            <div class="table_label_H col-sm-1">Limite</div>
            <div class="table_label_H col-sm-1">Articolo</div>
            <div class="table_label_H col-sm-1">Sanzione</div>
            <div class="clean_row HSpace4"></div>
        </div>
<?php endif;
$cont=0;
foreach ($rowArray as $r):
//$amount=str_replace(',', '.', $r[16]) + str_replace(',', '.', $r[17]); 
$cont++;?>
    <div class="col-sm-12">
        <div class="table_caption_H col-sm-1"><?=$cont?></div>
        <div class="table_caption_H col-sm-1"><?=$r[7]?></div>
        <div class="table_caption_H col-sm-2"><?=$r[3]?></div>
        <div class="table_caption_H col-sm-2"><?=$r[47]?></div>
        <div class="table_caption_H col-sm-2"><?=substr($r[49],0,60)?></div>
        <div class="table_caption_H col-sm-1"><?=$r[11]?></div>
        <div class="table_caption_H col-sm-1"><?=$r[52]?></div>
        <div class="table_caption_H col-sm-1"><?=$r[14]?></div>
        <div class="table_caption_H col-sm-1"></div>
        <div class="clean_row HSpace4"></div>
    </div>
<?php
endforeach; ?>

<?=$message->getMessagesString();?>

<?php if(!$b_Error && $ImportFile!=''):?>
        <div class="col-sm-12">
            <form name="f_import" action="imp_beta_exe.php">
                <input type="hidden" name="P" value="imp_beta.php">
                <input type="hidden" name="ImportFile" value="<?=$ImportFile?>">
                <div class="table_label_H col-sm-12">
                    Comprimi immagini
                    <select name="compress">
                        <option value="0">NO</option>
                        <option value="1">SI</option>
                    </select>
                    <input type="submit" value="Importa" >
                </div >
            </form>
        </div >
<?php endif;?>
</div>
<?php
require_once(INC."/footer.php");