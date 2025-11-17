<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

if(isset($_GET['page'])) {
    $page = $_GET['page'];
    if ($page==0){
        $page=1;
    }
} else {
    $page = 1;
}
$year = $_SESSION['year'];
$tipo = CheckValue('Kind','s');
$marca = CheckValue('Marca','s');
$modelo = CheckValue('Modello','s');
$title = CheckValue('Title','s');;

$cityid = $_SESSION['cityid'];
$min = $page*PAGE_NUMBER-PAGE_NUMBER;

$a_UsedDetectors = array();
$rs_UsedDetectors = $rs->SelectQuery("
    SELECT DetectorId FROM FineArticle 
    WHERE CityId = '{$_SESSION['cityid']}' AND DetectorId > 0 
    GROUP BY DetectorId");
while($r_UsedDetectors = $rs->getArrayLine($rs_UsedDetectors)){
    $a_UsedDetectors[] = $r_UsedDetectors['DetectorId'];
}

echo $str_out;
?>

<div class="row-fluid">
	<form class="form-inline boletario_search" method="post" action="">
    <div class="deleted_message"></div>
        <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">

            <div class="col-md-1 form-group BoxRowLabel">
                <label>Tipo</label>
            </div>
            <div class="col-md-2 form-group BoxRowCaption">
                <input type="text" name="Kind" id="Kind" class="form-control frm_field_string">
            </div>
            <div class="col-md-1 form-group BoxRowLabel">
                <label>Marca</label>
            </div>
            <div class="col-md-2 form-group BoxRowCaption">
                <input type="text" name="Marca" id="Marca" class="form-control frm_field_string">
            </div>
            <div class="col-md-1 form-group BoxRowLabel">
                <label>Modello</label>
            </div>
            <div class="col-md-2 form-group BoxRowCaption">
                <input type="text" name="Modello" id="Modello" class="form-control frm_field_string">
            </div>
            <div class="col-md-1 form-group BoxRowLabel">
                <label>Testo Verbale</label>
            </div>
            <div class="col-md-2 form-group BoxRowCaption">
                <input type="text" name="Title" id="Title" class="form-control frm_field_string">
            </div>
        </div>
        <div class="col-sm-1 BoxRow" style="height:4.6rem;">
            <button type="submit" data-toggle="tooltip" data-placement="top" title="" class="tooltip-r btn btn-primary" id="search" style="width:100%;margin-right:0;margin-top:0rem;font-size:3rem;"" data-original-title="Cerca"><i class="glyphicon glyphicon-search" style="font-size:3rem;"></i></button>
        </div>
    </form>

	<div class="clean_row HSpace4"></div>

    <div class="col-sm-12 main_block">
        <div class="table_label_H col-sm-1">Id</div>
        <div class="table_label_H col-sm-2">Tipo</div>
        <div class="table_label_H col-sm-2">Marca</div>
        <div class="table_label_H col-sm-2">Matricola</div>
        <div class="table_label_H col-sm-1">Codice Import</div>
        <div class="table_label_H col-sm-3">Descrizione</div>
        <div class="table_add_button col-sm-1 right">
            <?php echo ChkButton($aUserButton, 'add','<a href="tbl_detector_add.php'.$str_GET_Parameter.'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Inserisci" class="glyphicon glyphicon-plus-sign add_button tooltip-r" style="margin-right:0.3rem; color: white;"></span></a>');?>
        </div>
        <div class="clean_row HSpace4"></div>
        <div class="open_results">
            <?php
            $UserNumberTotal = $rs->selectQuery("select * from Detector where CityId='$cityid' order by progressive");
            $UserNumberTotal = mysqli_num_rows($UserNumberTotal);
            if ($tipo =="") $tipo = "";
            else $tipo = "and Kind like '%".$tipo."%'";
            if ($marca =="") $marca = "";
            else $marca = "and Brand like '%".$marca."%'";
            if ($modelo =="") $modelo = "";
            else $modelo = "and Model like '%".$modelo."%'";
            if ($title =="") $title = "";
            else $title = "and TitleIta like '%".$title."%'";
    
            $detector = $rs->SelectQuery("select * from Detector where 1=1 and CityId='$cityid' $tipo $marca $modelo $title order by progressive");
            
            $Row_Number = mysqli_num_rows($detector);
            if ($Row_Number ==0){
                echo '<div class="table_caption_H col-sm-12">
                    Nessun record presente
                </div>';
            }else{
                while ($row_detector = mysqli_fetch_array($detector)){
                        $showText =  strlen($row_detector['TitleIta']) > 100 ? substr($row_detector['TitleIta'],0,100)."...":$row_detector['TitleIta'];
                    ?>
                    <div class="append_search">
                        <div class="table_caption_H col-sm-1"><?php echo $row_detector['progressive']; ?></div>
                        <div class="table_caption_H col-sm-2"><?php echo $row_detector['Kind']; ?></div>
                        <div class="table_caption_H col-sm-2"><?php echo $row_detector['Brand']; ?></div>
                        <div class="table_caption_H col-sm-2"><?php echo $row_detector['Number']; ?></div>
                        <div class="table_caption_H col-sm-1"><?php echo $row_detector['Code']; ?></div>
                        <div class="table_caption_H col-sm-3"><?php echo $showText ?></div>
                        <div class="table_caption_button col-sm-1" style="line-height:2.4rem;">
                            <?php echo ChkButton($aUserButton, 'viw','<a href="tbl_detector_viw.php'.$str_GET_Parameter.'&DetectorId='.$row_detector['Id'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Visualizza" class="glyphicon glyphicon-eye-open tooltip-r"></span></a>&nbsp;');?>
                            <?php echo ChkButton($aUserButton, 'upd','<a href="tbl_detector_upd.php'.$str_GET_Parameter.'&DetectorId='.$row_detector['Id'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Modifica" class="glyphicon glyphicon-pencil tooltip-r"></span></a>&nbsp;');?>
                            <?php if(!in_array($row_detector['Id'], $a_UsedDetectors)): ?>
                            	<?= ChkButton($aUserButton, 'del','<a href="tbl_detector_del.php'.$str_GET_Parameter.'&DetectorId='.$row_detector['Id'].'"><span data-container="body" data-toggle="tooltip" data-placement="top" title="Elimina" class="glyphicon glyphicon-remove-sign tooltip-r"></span></a>&nbsp;'); ?>
                        	<?php endif; ?>
                        </div>
                    </div>
                    <div class="clean_row HSpace4"></div>
                    <?php
                }
    
                echo CreatePagination(PAGE_NUMBER, $UserNumberTotal, $page, "tbl_detector.php".$str_GET_Parameter,"");
            }
            ?>
        </div>
    </div>
</div>

<?php
include (INC . "/footer.php");
