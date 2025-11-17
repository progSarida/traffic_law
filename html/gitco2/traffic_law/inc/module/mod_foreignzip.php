<?php
?>

<div id="new_modal_foreign" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
        	<div class="modal-header">
        		<button type="button" class="close" data-dismiss="modal">&times;</button>
        		<h4 class="modal-title">Stradario Estero</h4>
        	</div>
        	<div class="modal-body" style="background-color: #9ecee7;">
    			<div class="container" style="width:auto;">
    				<div id="foreign_zip_message" class="alert alert-danger" style="display:none;"></div>
            		<form class="form" id="foreign_f_zip" action="mgmt_foreignzipcity_add_exe.php" method="post">
                        <input type="hidden" value="1" id="foreign_row_number">
                        <div class="form-group col-lg-3">
                            <label>Seleziona Comune</label>
                            <div id="foreign_city_title"></div>
                            <input type="hidden" class="form-control" name="foreign_city_id" id="foreign_city_id">
                        </div>        
                        <div class="form-group col-lg-3">
                            <label>Seleziona Strada</label>
                            <select class="form-control" name="foreign_strada">
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
                            <select class="form-control" name="foreign_toponym_id">
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
                            <input type="text" class="form-control" name="foreign_title" id="foreign_title" required>
                        </div>
                        <div class="form-group col-lg-3">
                            <label>CAP</label>
                            <input type="number" class="form-control" name="foreign_first_zip" id="foreign_first_zip" min="1" max="99999" required>
                        </div>
                        <h2 class="pull-right">Aggiungi un frazionamento!</h2>
                
                        <div style="height:4rem;">
                            <div class="col-sm-12" style="height:4rem;">
                                <i class="fa fa-caret-up" style="position:absolute; top:3px;right:3px;font-size: 2rem; display:none" id="foreign_upAddress"></i>
                                <i class="fa fa-caret-down" style="position:absolute; bottom:3px;right:3px;font-size: 2rem;" id="foreign_downAddress"></i>
                            </div>
                        </div>
                        <div class="form-group col-lg-12" id="foreign_Addresses">
                        </div>
                        <div class="col-sm-12">
                            <div class="col-sm-12 BoxRow" style="height:6rem;">
                                <div class="col-sm-12 BoxRowButton" style="text-align:center;line-height:6rem;">
                                    <input class="btn btn-primary" type="submit" id="foreign_submitZIP" name="foreign_submitZIP" value="Salva" />
                                </div>
                            </div>
                        </div>
                	</form>
            	</div>
        	</div>
        </div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function () {

		var number = $('#foreign_row_number').val();

        var AddressesTemplate = index =>`
                <div class="foreign_content${index}">
                    <div class="form-group col-lg-3">
                        <label>Seleziona tipo</label>
                        <select class="form-control foreign_type_${index}" name="foreign_type[]" id="foreign_type${index}" required>
                            <option value="">Seleziona tipo</option>
                            <option value="1">Dispari</option>
                            <option value="2">Pari</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2">
                        <label>Da num/lettera</label>
                        <input type="text" class="form-control foreign_from_${index}" name="foreign_from[]" id="foreign_from${index}" required style="width: 100px;" step="2">
                    </div>

                    <div class="form-group col-lg-2">
                        <label>A num/lettera</label>
                        <input type="text" class="form-control foreign_to_${index}" name="foreign_to[]" id="foreign_to${index}" required style="width: 100px;" step="2">
                    </div>
                    <div class="form-group col-lg-2">
                        <label>Seleziona</label>
                        <select class="form-control foreign_nero_${index}" name="foreign_nero[]" id="foreign_nero${index}" required>
                            <option value="0" selected>Nero</option>
                            <option value="1">Rosso</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-3">
                        <label>CAP</label>
                        <input type="number" class="form-control foreign_zip_${index}" name="foreign_zip[]" id="foreign_zip${index}" min="1" max="99999" required>
                    </div>
                </div>
            `;

        $('#foreign_downAddress').click(function () {

            number++;
            $("#foreign_Addresses").append(AddressesTemplate (number))

            $(document).on('change','.foreign_type_'+number,function (e) {
                var type = $('.foreign_type_'+number).val();
                if (type ==1){
                    $('.foreign_from_'+number).attr('min',1);
                    $('.foreign_to_'+number).attr('min',1);
                }else if(type ==2){
                    $('.foreign_from_'+number).attr('min',0);
                    $('.foreign_to_'+number).attr('min',0);
                }
            });
            $(document).on('input','#foreign_Addresses input',function (e) {
                var value1 = $('.foreign_from_'+number).val();
                var value2 = $('.foreign_to_'+number).val();
                var arr1 = value1.split("/");
                var arr2 = value2.split("/");
                var val1 = parseInt(arr1[0]);
                var val2 =parseInt(arr2[0]);

                if (val1 < val2){
                    $('#foreign_submitZIP').show();
                }
                if (val1 > val2){
                    $('#foreign_submitZIP').hide();
                }
            });
            $("#foreign_upAddress").show();
            $('#foreign_row_number').val(number);
        });

        $('#foreign_upAddress').click(function () {
            $(".foreign_content"+number).remove();
            number--;
            if(number==1){
                $("#foreign_upAddress").hide();
            }else{
                $("#foreign_downAddress").show();
            }
            $('#foreign_row_number').val(number);
        });

//         $("#foreign_f_zip input").on("keyup", "[id^=foreign_from], [id^=foreign_to]", function(){
//             if ($(this).val().length == 1){
//                 $(this).val($(this).val() + "/");
//             }
//         });

        $("#foreign_f_zip").submit(function (event) {
        	var form = $("#foreign_f_zip").serialize();
        	console.log(form);
        	event.preventDefault();
            $.ajax({
                url: 'ajax/ajx_add_foreignzipcity_exe.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: form,
                success: function (data) {
                    console.log(data);
                    if (data.Answer == "OK"){
                    	$('#foreign_zip_message').hide();
                    	$('#new_modal_foreign').modal('hide');
                    	$('#foreign_Addresses').html("");
                    	$('#foreign_city_id, #foreign_strada, #foreign_toponym_id, #foreign_title, #foreign_first_zip').val("");
                    	number = 1;
                    	$('#foreign_row_number').val("1");
                    }
                    if (data.Answer == "NO"){
                    	$('#foreign_zip_message').show().html(data.Message);
                    }
                },
                error: function (result) {
                    console.log(result);
                    alert("error");
                }
            });
        });

	});
</script>

