<?php
require("_path.php");
require(INC . "/parameter.php");
require(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
$rs= new CLS_DB();

$Search_CityTitle = CheckValue('Search_CityTitle','s');
$Search_Title = CheckValue('Search_Title','s');
$Search_ZIP = CheckValue('Search_ZIP','s');
$Search_Street = CheckValue('Search_Street','s');
$zipcity_id = $_GET['zipcity'];
$answer = "";

$str_GET_Parameter .= "&Search_CityTitle=$Search_CityTitle&Search_Title=$Search_Title&Search_ZIP=$Search_ZIP&Search_Street=$Search_Street";

// $str_PageTitle = $_GET['PageTitle'];
// $a_PageTitle = explode("/",$str_PageTitle);
echo $str_out;
?>




<div class="container" id="add_new_zipcity">
    <?php if($answer!=''){
        ?>
        <div class="alert alert-warning" role="alert">
            <strong>Warning!</strong> <?php echo $answer;?>
        </div>
        <?php
    }
    $zipcity = $rs->SelectQuery("SELECT sarida.ZIPCity.*,City.Id as C_Id,sarida.City.Title as CityName,sarida.Toponym.Title as ToponymName 
    from sarida.ZIPCity left JOIN sarida.City on sarida.ZIPCity.CityId = sarida.City.Id left JOIN sarida.Toponym ON sarida.ZIPCity.ToponymId=sarida.Toponym.Id where sarida.ZIPCity.Id =$zipcity_id");
    $zipaddress = $rs->SelectQuery("SELECT * FROM sarida.ZIPAddress where sarida.ZIPAddress.ZIPCityId =$zipcity_id");
    while($row=mysqli_fetch_array($zipcity)){
    ?>

    <form class="form" action="mgmt_zipcity_add_exe.php" method="post">
        <input type="hidden" value="<?php echo $zipcity_id;?>" id="Zcity_id" name="Zcity_id">
        <div class="form-group col-lg-3">
            <label>Seleziona Comune</label>
            <select class="form-control" name="city_id" required>
                <option value="">Scegliere</option>
                <?php
                $selected = "";
                $city = $rs->SelectQuery("SELECT Id,Title FROM sarida.City order by Title");
                while ($city_row = mysqli_fetch_array($city)) {
                    $test =$row['C_Id']==$city_row['Id']?$selected='selected':'';
                    ?>
                    <option value="<?php echo $city_row['Id']; ?>" <?php echo $test;?>><?php echo $city_row['Title']; ?></option>
                    <?php
                }
                ?>
            </select>
        </div>
        <div class="form-group col-lg-3">
            <label>Seleziona Strada</label>
            <select class="form-control" name="strada">
                <option value="">Seleziona Strada</option>
                <?php
                $Strada = $rs->SelectQuery("SELECT Id,Title FROM StreetType");
                while ($Strada_row = mysqli_fetch_array($Strada)) {

                    $test =$row['Strada']==$Strada_row['Title']?$selected='selected':'';
                    ?>
                    <option value="<?php echo $Strada_row['Title'];?>" <?php echo $test;?>><?php echo $Strada_row['Title']; ?></option>
                    <?php
                }
                ?>

            </select>
        </div>
        <div class="form-group col-lg-3">
            <label>Seleziona il toponimo</label>
            <select class="form-control" name="toponym_id" required>
                <option value="">Scegliere</option>
                <?php
                $toponym = $rs->SelectQuery("SELECT * FROM sarida.Toponym");
                while ($toponym_row = mysqli_fetch_array($toponym)) {

                    $test =$row['ToponymId']==$toponym_row['Id']?$selected='selected':'';
                    ?>
                    <option value="<?php echo $toponym_row['Id'].','.$toponym_row['Title'];?>" <?php echo $test;?>><?php echo $toponym_row['Title']; ?></option>
                    <?php
                }
                ?>
            </select>
        </div>
        <div class="form-group col-lg-3">
            <label>Nome della strada</label>
            <input type="text" class="form-control" name="title" id="title" value="<?php
            $replace = strtoupper($row['ToponymName']);
            $name = str_replace($replace,"", $row['StreetName']);
            echo $name;?>" required>
        </div>
        <div class="form-group col-lg-3">
            <label>CAP</label>
            <input type="number" class="form-control" name="first_zip" id="first_zip" value="<?php echo $row['ZIP'];?>" required>
        </div>
        <h2 class="pull-right">Indirizio</h2>

        <div style="height:4rem;">
            <div class="col-sm-12" style="height:4rem;">
                <i class="fa fa-caret-up" style="position:absolute; top:3px;right:3px;font-size: 2rem; display:none"
                   id="up"></i>
                <i class="fa fa-caret-down" style="position:absolute; bottom:3px;right:3px;font-size: 2rem;"
                   id="down"></i>

            </div>
        </div>
        <div class="form-group col-lg-12" id="Indirizio">
            <?php $nr = mysqli_num_rows($zipaddress);?>
            <input type="hidden" value="<?php echo $nr;?>" id="row_number">
            <table class="table table_label_H">
                <tr>
                    <th class="table_label_H">Seleziona</th>
                    <th class="table_label_H">Da</th>
                    <th class="table_label_H">A</th>
                    <th class="table_label_H">Nero/Roso</th>
                    <th class="table_label_H">CAP</th>
                    <th class="table_label_H">Elimina</th>
                </tr>
                <tbody class="content">
                <?php
                if ($nr==0){
                ?>
                <tr class="table_caption_H" id="when_0">
                    <td colspan="5">0 Results</td>
                </tr>
                </tbody>
                <?php
                }else {
                    $count = 0;
                    while ($row_zp = mysqli_fetch_array($zipaddress)) {
                        $count++;
                        ?>

                        <tr class="table_caption_H" id="num_row_<?php echo $count; ?>">
                            <td>
                                <select class="form-control type_<?php echo $count; ?>" name="type[]"
                                        id="type[]" required>

                                    <option value="<?php echo $row_zp['GroupTypeId']; ?>" selected><?php echo $word = $row_zp['GroupTypeId'] == 1 ? 'Dispari' : 'Pari'; ?></option>
                                    <option value="<?php echo $grup_id = $row_zp['GroupTypeId'] == 1 ? 2 : 1; ?>"><?php echo $word = $row_zp['GroupTypeId'] == 1 ? 'Pari' : 'Dispari'; ?></option>

                                </select>
                                <input type="hidden" value="<?php echo $row_zp['Id']; ?>" name="zipaddress_id[]" id="zipaddress_id[]">
                            </td>
                            <td>
                                <input type="text" class="form-control from_<?php echo $count; ?>" name="from[]" id="from[]" required step="2" value="<?php echo $row_zp['FromNumber']."/".$row_zp['FromNumberLetter']; ?>">
                            </td>
                            <td>
                                <input type="text" class="form-control to_<?php echo $count; ?>" name="to[]" id="to[]" required value="<?php echo $row_zp['ToNumber']."/".$row_zp['ToNumberLetter']; ?>" step="2">
                            </td>
                            <td>
                                <select class="form-control type_<?php echo $count; ?>" name="nero[]" id="nero[]" required>

                                    <option value="<?php echo $row_zp['NumberType']; ?>" selected><?php echo $word = $row_zp['NumberType'] == 1 ? 'Roso' : 'Nero'; ?></option>
                                    <option value="<?php echo $grup_id = $row_zp['NumberType'] == 1 ? 0 : 1; ?>"><?php echo $word = $row_zp['NumberType'] == 1 ? 'Nero' : 'Rosso'; ?></option>

                                </select>
                            </td>
                            <td>
                                <input type="number" class="form-control zip_<?php echo $count; ?>" name="zip[]" id="zip[]" required value="<?php echo $row_zp['ZIP']; ?>">
                            </td>
                            <td>
                                <button type="button" class="delete_zpadress btn btn-danger" id="<?php echo $row_zp['Id']; ?>">Elimina
                                </button>
                            </td>
                        </tr>

                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    <input class="btn btn-default" type="submit" id="edit" name="edit" value="Salva" />
                    <button class="btn btn-default" id="back">Indietro</button>
                </div>
            </div>
        </div>
</div>
    </form>
<?php

}

?>
</div>
<script>
    $(document).ready(function(){
        var number = $('#row_number').val();
        for (var i = 1; i <= number;i++){
            $('.type_'+i).on('change',{internal_i : i},function (event) {
                var i = event.data.internal_i;
                var type = $('.type_'+i).val();
                if (type ==1){
                    $('.from_'+i).attr('min',1);
                    $('.to_'+i).attr('min',1);
                }else if(type ==2){
                    $('.from_'+i).attr('min',0);
                    $('.to_'+i).attr('min',0);
                }
            });
        }
        var tableTemplate = index => `
                <tr class="table_caption_H" id="num_row_${index}"">
                    <td>
                        <select class="form-control type_${index}" name="type[]" id="type[]" required>
                            <option value="">Seleziona tipo</option>
                            <option value="1">Dispari</option>
                            <option value="2">Pari</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control from_${index}" name="from[]" id="from[]" required step="2">
                    </td>
                    <td>
                        <input type="text" class="form-control to_${index}" name="to[]" id="to[]" required  step="2">
                    </td>
                    <td>
                        <select class="form-control nero_${index}" name="nero[]" id="nero[]" required>
                            <option value="0" selected>Nero</option>
                            <option value="1">Rosso</option>
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control zip_" name="zip[]" id="zip[]" required ">
                    </td>
                    <td>

                    </td>
                </tr>
                `;
        $('#down').click(function () {
            $('#when_0').hide();
            number++;
            $(".content").append( tableTemplate(number))
            $(document).on('change','.type_'+number,function (e) {
                var type = $('.type_'+number).val();
                if (type ==1){
                    $('.from_'+number).attr('min',1);
                    $('.to_'+number).attr('min',1);
                }else if(type ==2){
                    $('.from_'+number).attr('min',0);
                    $('.to_'+number).attr('min',0);
                }
            });

            $(document).on('keyup','.table_caption_H input',function (e) {
                var value1 = $('.from_'+number).val();
                var value2 = $('.to_'+number).val();

                var arr1 = value1.split("/");
                var arr2 = value2.split("/");

                var val1 = parseInt(arr1[0]);
                var val2 =parseInt(arr2[0]);


                if (val1 < val2){
                    $('#edit').show();
                }
                if (val1 > val2){
                    $('#edit').hide();
                }
            });

            $("#up").show();
            $('#row_number').val(number);
        });

        $('#back').click(function () {
            window.location = "<?= "mgmt_zipcity.php".$str_GET_Parameter ?>";
            return false;
        });

        $('#up').click(function () {

            $("#num_row_"+number).remove();
            number--;

            if(number==0){
                $("#up").hide();
            }else{
                $("#down").show();
            }
            $('#row_number').val(number);


        });
    });
</script>
<script>


    $('.delete_zpadress').click(function(){
        var id = $(this).attr('id');
        if (!confirm("Sei sicuro do procedere?")){
            return false;
        }
        var myData = {"delete_zipadress":"delete","id":id};
        $.ajax({
            url: 'mgmt_zipcity_del.php',
            type: 'POST',
            data: myData,
            success: function(data){
                $.each(JSON.parse(data), function(i, item) {
                    if(i=='202'){
                        location.reload();
                    }else if(i=='errore'){
                        alert(item);
                    }
                });

            },

        });
    })
</script>