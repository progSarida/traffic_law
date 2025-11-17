<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
if (isset($_GET['image_pointer'])) $image_pointer = $_GET['image_pointer'];
else $image_pointer = 0;
$cityId = $_SESSION['cityid'];
$url = $_SERVER['PHP_SELF'].'?PageTitle=Importazione/Contravvenzioni Autovelox';

$FileList = "";
$Cont = 0;
$title = "";
$path_SEM = VALIDATE_FOLDER."/".$_SESSION['cityid']."/SEMAFORO/";
$path_VEL = VALIDATE_FOLDER."/".$_SESSION['cityid']."/VELOCITA/";

$all_files_SEM = glob($path_SEM."*.*");
$all_files_VEL = glob($path_VEL."*.*");
$nr_images_SEM = count($all_files_SEM);
$nr_images_VEL = count($all_files_VEL);

@$image_name_sem = $all_files_SEM[$image_pointer];
@$image_name_vel = $all_files_VEL[$image_pointer];

$new_semName = explode("/",$image_name_sem);
$new_velName = explode("/",$image_name_vel);

echo $str_out;
if (isset($_GET['validate'])){
    $title = $_GET['validate'];
    if($title=='semaforo'){
        $parameter = '&validate=semaforo';
        $all_files = glob($path_SEM."*.*");
        $get_upladImageNumber  = $rs->SelectQuery("select UploadImageNumber from Detector where  CityId='$cityId' and DetectorTypeId = 2");
    }
    if($title=='velocita'){
        $parameter = '&validate=velocita';
        $all_files = glob($path_VEL."*.*");
        $get_upladImageNumber  = $rs->SelectQuery("select UploadImageNumber from Detector where  CityId='$cityId' and DetectorTypeId = 1");
    }
    $upladImageNumber = mysqli_fetch_array($get_upladImageNumber)['UploadImageNumber'];

    $image_name = $all_files[$image_pointer];

    $nr_image = count($all_files);
    if ($nr_image > 0) {
        $img_name = explode("/",$image_name);
        $imgName = $img_name[count($img_name)-1];
        $supported_format = array('gif', 'jpg', 'jpeg', 'png');
        $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
    }

    foreach ($all_files as $file){
        $file = explode("/",$file);
        $arr[] =  $file[count($file)-1];
    }
    ;
    $newimagename = explode("/",$image_name);
    $FinalImageName = $newimagename[count($newimagename)-1];
    $display_left = $image_pointer == 0 ? "display:none" : "";
    $previous = $image_pointer -1;
    $next = $image_pointer +1;

    $display_right = $image_pointer == $nr_images_VEL - 1 ? "display:none" : "";
    if (isset($_GET['validate']) && $_GET['validate']=='semaforo') {
        $display_right = $image_pointer == $nr_images_SEM - 2 ? "display:none" : "";
        if (strpos($FinalImageName, "_") == true) {
            $thisName = explode("_", $FinalImageName);
            $parts = preg_split("/(,?\s+)|((?<=[a-z])(?=\d))|((?<=\d)(?=[a-z]))/i", $thisName[3]);
            $num = (int)$parts[0] + 1;
            $num_of_zeros = substr_count($parts[0], "0");
            $str_zero = "";
            for ($i = 0; $i < 5; $i++) $str_zero .= 0;
            $first = $thisName[0] . "_" . $thisName[1];
            $get = explode(".", $parts[1]);
            $character = $get[0];
            $letterAscii = ord($character);
            $letterAscii++;
            $letter = chr($letterAscii);
            $thirdPart = $str_zero . $num . $letter . "." . $get[1];
            $next = $image_pointer + 1;
            $previous = $image_pointer - 1;
            $array_files = array();
            foreach ($arr as $value) {
                $names = explode("_", $value);
                @$first_part = $names[0] . "_" . $names[1];
                if ($first_part == $first && $names[3] == $thirdPart) {
                    $newName = $thisName[0] . "_" . $thisName[1] . "_" . $names[2] . "_" . $str_zero . $num . $letter . "." . $get[1];
                    array_push($array_files, VALIDATE_FOLDER."/".$_SESSION['cityid']."/SEMAFORO/".$FinalImageName, VALIDATE_FOLDER."/".$_SESSION['cityid']."/SEMAFORO/".$newName);
                    $next = $image_pointer + 2;
                    $previous = $image_pointer - 2;
                }
            }

        }else if (strpos($FinalImageName, "-") == true){

            $thisName = explode("-", $FinalImageName);
            $parts = preg_split("/(,?\s+)|((?<=[a-z])(?=\d))|((?<=\d)(?=[a-z]))/i", $thisName[3]);
            if ((int)($thisName[2]) < 10) $secondPart = '0' . ($thisName[2] + 1);
            else $secondPart = $thisName[2]+1;

            $thirdPart = $parts[0] - 1;

            $newName = $thisName[0] . "-" . $thisName[1] . "-" . $secondPart . "-" . $thirdPart . $parts[1];
            $array_files = array();
            $next = $image_pointer + 1;
            $previous = $image_pointer - 1;
            if (in_array($newName, $arr)) {
                array_push($array_files, $MainPath."/public/_VALIDATION_/".$_SESSION['cityid']."/SEMAFORO/".$FinalImageName, $MainPath."/public/_VALIDATION_/".$_SESSION['cityid']."/SEMAFORO/".$newName);
                $next = $image_pointer + 2;
                $previous = $image_pointer - 2;
            }else{
                array_push($array_files, $MainPath."/public/_VALIDATION_/".$_SESSION['cityid']."/SEMAFORO/".$FinalImageName);
                $next = $image_pointer + 1;
                $previous = $image_pointer - 1;
            }

        }


    }else{

        $array_files = array();

        if (in_array($FinalImageName, $arr)){
            array_push($array_files, $MainPath."/public/_VALIDATION_/".$_SESSION['cityid']."/VELOCITA/".$FinalImageName);
        }
    }
    $image = $all_files[$image_pointer];
    if (isset($_GET['image_pointer'])){

        ?>
        <div class="col-sm-12 col-md-12 BoxRowCaption" style="height: 4.2rem;">
            <div class="col-sm-1"><a href="<?=$url.$parameter.'&image_pointer='.$previous?>"><i class="glyphicon glyphicon-arrow-left" style="font-size:3.8rem;color:#fff;<?=$display_left?>"></i></a> </div>
            <div class="col-sm-10"></div>
            <div class="col-sm-1"><a href="<?=$url.$parameter.'&image_pointer='.$next?>"><i class="glyphicon glyphicon-arrow-right" style="font-size:3.8rem;color:#fff; float: right;<?=$display_right?>"></i></a> </div>
        </div>
        <div class="col-sm-12 col-md-12" style="height: 4.2rem;">

            <?php if($title=='semaforo'){
                echo '<center><h3>Contravvenzioni Semaforo</h3></center><hr>';
            }elseif ($title=='velocita'){
                echo '<center><h3>Contravvenzioni Autovelox</h3></center>';
            }else{
                echo $title;
            }?>
        </div>

        <div class="col-sm-12 col-md-12">
            <br><br><br><br>

            <?php
            if (isset($_GET['validate']) && $_GET['validate'] =='velocita'){

                ?>
                <div class="col-sm-2 col-md-2"></div>
                <div class="col-sm-7 col-md-7">
                    <?php
                    echo '<img id="thumb" class="img-responsive col-xs-12" src="'.$array_files[0] .'" width="800px;" style="height:auto;" style="border-right:2px solid white;;"/>'."";
                    ?>
                    <script>
                        var evt = new Event(),
                            first = new Magnifier(evt);
                        first.attach({
                            thumb: '#thumb',
                            large: '<?php echo $array_files[0];?>',
                            largeWrapper: 'preview',
                            mode:'inside',
                            zoomable: true,
                            zoom:4,
                            zIndex:-1
                        });
                    </script>
                    <?php
                    ?>
                </div>
                <?php

            }else{
                $i=0;
                ?>
                <div class="col-sm-1 col-md-1"></div>
                <div class="col-sm-8 col-md-8">
                    <div class="col-sm-6 col-md-6">
                        <?php
                        echo '<img id="thumb_1" class="img-responsive col-xs-12" src="'.$array_files[0] .'" width="620px;" style="height:auto;" style="border-right:2px solid white;z-index: 1;"/>'."";
                        ?>
                        <script>
                            var evt = new Event(),
                                first = new Magnifier(evt);
                            first.attach({
                                thumb: '#thumb_1',
                                large: '<?php echo $array_files[0];?>',
                                largeWrapper: 'preview',
                                mode:'inside',
                                zoomable: true,
                                zoom: 3
                            });
                        </script>
                        <?php

                        ?>
                    </div>
                    <div class="col-sm-6  col-md-6">
                        <?php
                        echo '<img id="test_2" class="img-responsive col-xs-12" src="'.$array_files[1] .'" width="620px;" style="height:auto;" style="border-right:2px solid white;"/>'."";
                        ?>
                        <script>
                            var e = new Event(),
                                sec = new Magnifier(e);
                            first.attach({
                                thumb: '#test_2',
                                large: '<?php echo $array_files[1];?>',
                                largeWrapper: 'preview',
                                mode:'inside',
                                zoomable: true
                            });
                        </script>
                        <?php

                        ?>
                    </div>
                </div>
                <?php
            }

            ?>
            <div class="col-sm-3 col-md-3">
                <?php
                if (isset($_GET['validate'])) {
                    $message = null;
                    $disable=null;
                    $validate = $_GET['validate'];
                    if ($validate=='semaforo'){
                        if ($upladImageNumber=2 && sizeof($array_files) < $upladImageNumber){
                            $disable='disabled';
                            $message = "Devono essere due immagini da validare!";
                        }else{
                            $disable=null;
                            $message = null;
                        }
                    }


                    if (isset($_GET['image'])) $image = $_GET['image'];
                    ?>
                    <form method="post" action="imp_autovelox_exe.php">
                        <div class="col-sm-12" style="height: 100px;"></div>
                        <input id="validate" type="hidden" name="validate" value="<?=$validate?>">
                        <input id="image_pointer" type="hidden" name="image_pointer" value="<?=$_GET['image_pointer']?>">
                        <input id="first" type="hidden" name="first" value="<?=$array_files[0]?>">
                        <input id="second" type="hidden" name="second" value="<?=sizeof($array_files)==2?$array_files[1]:null?>">
                        <h4><?=$message?></h4>
                        <button type="submit" class="btn btn-success" id="validate" <?=$disable?>>Mantieni Foto</button>
                        <button type="button" class="btn btn-danger delete">Cancella</button>
                        <button class="btn btn-default" id="back">Esci</button>
                    </form>
                    <?php

                }
                ?>
            </div>
        </div>
        <?php
    }

}else{
    ?>
    <center>

        <p>Immagini Semaforo da validare: <?=$nr_images_SEM?></p>
        <?php if ($nr_images_SEM >0){
            ?>
            <h3> <a href="<?=$url.'&image_pointer=0'.'&validate=semaforo&image='.$new_semName[4]?>">Validate</a></h3>
            <?php
        }?>
        <div class="clean_row HSpace4"></div>
        <p>Immagini Velocita da validare: <?=$nr_images_VEL?></p>
        <?php if ($nr_images_VEL >0){
            ?>
            <h3> <a href="<?=$url.'&image_pointer=0'.'&validate=velocita&image='.$new_velName[4]?>">Validate</a></h3>
            <?php
        }?>


    </center>
    <?php
}
?>



<script type="text/javascript">
    $('#back').click(function () {
        window.location = "imp_autovelox.php?PageTitle=Importazione/Contravvenzioni%20Autovelox";
        return false;
    });
    //var e = new Event(),
    //    second = new Magnifier(e);
    //second.attach({
    //    thumb: '#test_2',
    //    large: '<?php //echo $array_files[1];?>//',
    //    largeWrapper: 'preview',
    //    mode:'inside',
    //    zoomable: true
    //});
    $(document).on('click','.delete',function(e){
        var image_pointer = '<?=$image_pointer?>';
        var validate = $("#validate").val();
        var first = $("#first").val();
        var second = $("#second").val();
        if (!confirm("Sei sicuro di voler procedere con la cancellazione?")){
            return false;
        }
        var myData = {"delete":"delete","image_pointer":image_pointer, "validation":validate,"first":first,"second":second};
        $.ajax({
            url: 'imp_autovelox_del.php',
            type: 'POST',
            data: myData,
            success: function(data){
                if(data==200){
                   location.reload();
                }
                if (data==300){
                    window.location = "imp_autovelox.php?PageTitle=Importazione/Contravvenzioni%20Autovelox";
                    return false;
                }

            },

        });
    })
</script>


