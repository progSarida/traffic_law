<?php
?>
<div id="new_modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div class="modal-content">
        	<div class="modal-header">
        		<button type="button" class="close" data-dismiss="modal">&times;</button>
        		<h4 class="modal-title">Stradario</h4>
        	</div>
        	<div class="modal-body" style="background-color: #9ecee7;">
    			<div class="container" style="width:auto;">
    				<div id="zip_message" class="alert alert-danger" style="display:none;"></div>
            		<form class="form" id="f_zip" action="mgmt_zipcity_add_exe.php" method="post">
                        <input type="hidden" value="1" id="row_number">
                        <div class="form-group col-lg-3">
                            <label>Seleziona Comune</label>
                            <div id="city_title"></div>
                            <input type="hidden" class="form-control" name="city_id" id="city_id">
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
                            <select class="form-control" name="toponym_id" required>
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
                            <input type="number" class="form-control" name="first_zip" id="first_zip" min="1" max="99999" required>
                        </div>
                        <h2 class="pull-right">Aggiungi un frazionamento!</h2>
                
                        <div style="height:4rem;">
                            <div class="col-sm-12" style="height:4rem;">
                                <i class="fa fa-caret-up" style="position:absolute; top:3px;right:3px;font-size: 2rem; display:none" id="upAddress"></i>
                                <i class="fa fa-caret-down" style="position:absolute; bottom:3px;right:3px;font-size: 2rem;" id="downAddress"></i>
                            </div>
                        </div>
                        <div class="form-group col-lg-12" id="Addresses">
                        </div>
                        <div class="col-sm-12">
                            <div class="col-sm-12 BoxRow" style="height:6rem;">
                                <div class="col-sm-12 BoxRowButton" style="text-align:center;line-height:6rem;">
                                    <input class="btn btn-primary" type="submit" id="submitZIP" name="submitZIP" value="Salva" />
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

		var number = $('#row_number').val();

        var AddressesTemplate = index =>`
                <div class="content${index}">
                    <div class="form-group col-lg-3">
                        <label>Seleziona tipo</label>
                        <select class="form-control type_${index}" name="type[]" id="type${index}" required>
                            <option value="">Seleziona tipo</option>
                            <option value="1">Dispari</option>
                            <option value="2">Pari</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-2">
                        <label>Da num/lettera</label>
                        <input type="text" class="form-control from_${index}" name="from[]" id="from${index}" required style="width: 100px;" step="2">
                    </div>

                    <div class="form-group col-lg-2">
                        <label>A num/lettera</label>
                        <input type="text" class="form-control to_${index}" name="to[]" id="to${index}" required style="width: 100px;" step="2">
                    </div>
                    <div class="form-group col-lg-2">
                        <label>Seleziona</label>
                        <select class="form-control nero_${index}" name="nero[]" id="nero${index}" required>
                            <option value="0" selected>Nero</option>
                            <option value="1">Rosso</option>
                        </select>
                    </div>
                    <div class="form-group col-lg-3">
                        <label>CAP</label>
                        <input type="number" class="form-control zip_${index}" name="zip[]" id="zip${index}" min="1" max="99999" required>
                    </div>
                </div>
            `;

        $('#downAddress').click(function () {

            number++;
            $("#Addresses").append(AddressesTemplate (number))

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
            $(document).on('input','#Addresses input',function (e) {
                var value1 = $('.from_'+number).val();
                var value2 = $('.to_'+number).val();
                var arr1 = value1.split("/");
                var arr2 = value2.split("/");
                var val1 = parseInt(arr1[0]);
                var val2 =parseInt(arr2[0]);

                if (val1 < val2){
                    $('#submitZIP').show();
                }
                if (val1 > val2){
                    $('#submitZIP').hide();
                }
            });
            $("#upAddress").show();
            $('#row_number').val(number);
        });

        $('#upAddress').click(function () {
            $(".content"+number).remove();
            number--;
            if(number==1){
                $("#upAddress").hide();
            }else{
                $("#downAddress").show();
            }
            $('#row_number').val(number);
        });

//         $("#f_zip input").on("keyup", "[id^=from], [id^=to]", function(){
//             if ($(this).val().length == 1){
//                 $(this).val($(this).val() + "/");
//             }
//         });

        $("#f_zip").submit(function (event) {
        	var form = $("#f_zip").serialize();
        	console.log(form);
        	event.preventDefault();
            $.ajax({
                url: 'ajax/ajx_add_zipcity_exe.php',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: form,
                success: function (data) {
                    console.log(data);
                    if (data.Answer == "OK"){
                    	$('#zip_message').hide();
                    	$('#new_modal').modal('hide');
                    	$('#Addresses').html("");
                    	$('#city_id, #strada, #toponym_id, #title, #first_zip').val("");
                    	number = 1;
                    	$('#row_number').val("1");
                    }
                    if (data.Answer == "NO"){
                    	$('#zip_message').show().html(data.Message);
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

