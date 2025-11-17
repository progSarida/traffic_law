<?php
require("_path.php");
require(INC . "/parameter.php");
require(CLS . "/cls_db.php");
include(INC . "/function.php");
require(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');
$rs= new CLS_DB();
$answer= "";
// $str_PageTitle = $_GET['PageTitle'];
// $a_PageTitle = explode("/",$str_PageTitle);

$Search_CountryId = CheckValue('Search_CountryId','s');
$Search_CityId = CheckValue('Search_CityId','s');
$Search_Title = CheckValue('Search_Title','s');
$Search_ZIP = CheckValue('Search_ZIP','s');
$Search_Street = CheckValue('Search_Street','s');
$answer = "";

$str_GET_Parameter .= "&Search_CountryId=$Search_CountryId&Search_CityId=$Search_CityId&Search_Title=$Search_Title&Search_ZIP=$Search_ZIP&Search_Street=$Search_Street";

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
    ?>
    <form class="form" action="mgmt_foreignzipcity_add_exe.php" method="post">
        <input type="hidden" value="1" id="row_number">
        <div class="form-group col-lg-3">
        	<label>Seleziona Nazione</label>
        	<?php echo CreateSelect(MAIN_DB . ".Country", 'Id IN("Z112","Z102")', "Title", "country_id", "Id", "Title", "", false); ?>
        </div>
        <div class="form-group col-lg-3">
            <label>Seleziona Comune</label>
            <select class="form-control" name="city_id" id="city_id" required>
                <option value="">Seleziona Comune</option>
            </select>
        </div>        
        <div class="form-group col-lg-3">
            <label>Seleziona Strada</label>
            <select class="form-control" name="strada">
                <option value="">Seleziona Strada</option>
                <?php
                $Strada = $rs->SelectQuery("SELECT Id,Title FROM StreetType");
                while($Strada_row = mysqli_fetch_array($Strada)){
                    ?>
                    <option value="<?php echo $Strada_row['Title'];?>"><?php echo $Strada_row['Title'];?></option>
                    <?php
                }
                ?>

            </select>
        </div>
        <div class="form-group col-lg-3">
            <label>Seleziona Toponimo</label>
            <select class="form-control" name="toponym_id">
                <option value="">Seleziona Toponimo</option>
                <?php
                $toponym = $rs->SelectQuery("SELECT * FROM sarida.Toponym");
                while($toponym_row = mysqli_fetch_array($toponym)){
                    ?>
                    <option value="<?php echo $toponym_row['Id'].','.$toponym_row['Title'];?>"><?php echo $toponym_row['Title'];?></option>
                    <?php
                }
                ?>
            </select>
        </div>
        <div class="form-group col-lg-3">
            <label>Denominazione</label>
            <input type="text" class="form-control" name="title" id="title" required>
        </div>
        <div class="form-group col-lg-3">
            <label>CAP</label>
            <input type="number" class="form-control" name="first_zip" id="first_zip" required>
        </div>
        <h2 class="pull-right">Aggiungi un frazionamento!</h2>

        <div style="height:4rem;">
            <div class="col-sm-12" style="height:4rem;">
                <i class="fa fa-caret-up" style="position:absolute; top:3px;right:3px;font-size: 2rem; display:none" id="up"></i>
                <i class="fa fa-caret-down" style="position:absolute; bottom:3px;right:3px;font-size: 2rem;" id="down"></i>
            </div>
        </div>
        <div class="form-group col-lg-12" id="Indirizio">
        </div>
        <div class="col-sm-12">
            <div class="col-sm-12 BoxRow" style="height:6rem;">
                <div class="col-sm-12" style="text-align:center;line-height:6rem;">
                    <input class="btn btn-default" type="submit" id="submit" name="submit" value="Salva" />
                    <button class="btn btn-default" id="back">Indietro</button>
                </div>
            </div>
        </div>
</div>
</form>
</div>
<script>
    $(document).ready(function(){
        var number = $('#row_number').val();

        var IndirizioTemplate = index =>`
                <div class="content${index}">
                    <div class="form-group col-lg-3">
                        <label>Seleziona tipo</label>
                        <select class="form-control type_${index}" name="type[]" id="type[]" required>
                            <option value="">Seleziona tipo</option>
                            <option value="1">Dispari</option>
                            <option value="2">Pari</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2">
                        <label>Da</label>
                        <input type="text" class="form-control from_${index}" name="from[]" id="from[]" required style="width: 100px;" step="2">
                    </div>

                    <div class="form-group col-lg-2">
                        <label>A</label>
                        <input type="text" class="form-control to_${index}" name="to[]" id="to[]" required style="width: 100px;" step="2">
                    </div>
                    <div class="form-group col-lg-2">
                        <label>Seleziona</label>
                        <select class="form-control nero_${index}" name="nero[]" id="nero[]" required>
                            <option value="0" selected>Nero</option>
                            <option value="1">Rosso</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-3">
                        <label>CAP</label>
                        <input type="number" class="form-control zip_${index}" name="zip[]" id="zip[]" required>
                    </div>
                </div>
            `;

        $('#down').click(function () {

            number++;
            $("#Indirizio").append(IndirizioTemplate (number))

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
            $(document).on('input','#Indirizio input',function (e) {
                var value1 = $('.from_'+number).val();
                var value2 = $('.to_'+number).val();
                var arr1 = value1.split("/");
                var arr2 = value2.split("/");
                var val1 = parseInt(arr1[0]);
                var val2 =parseInt(arr2[0]);

                if (val1 < val2){
                    $('#submit').show();
                }
                if (val1 > val2){
                    $('#submit').hide();
                }
            });
            $("#up").show();
            $('#row_number').val(number);
        });

        $('#up').click(function () {
            $(".content"+number).remove();
            number--;
            if(number==1){
                $("#up").hide();
            }else{
                $("#down").show();
            }
            $('#row_number').val(number);
        });

        $("#country_id").change(function () {
        	var CountryId = $(this).val();
	        $.ajax({
	            url: 'ajax/ajx_get_foreignCities.php',
	            type: 'POST',
	            dataType: 'json',
	            data: {CountryId:CountryId},
	            success: function (data) {
		            //console.log(data);
		            $("#city_id").html(data.Options);
		            $('#city_id option:first-child').text('Seleziona Comune');
	            },
	            error: function (result) {
	                console.log(result);
	                alert("error: " + result.responseText);
	            }
	        });
        });
        
        $('#back').click(function () {
            window.location = "<?= "mgmt_foreignzipcity.php".$str_GET_Parameter ?>";
            return false;
        });
    });
</script>