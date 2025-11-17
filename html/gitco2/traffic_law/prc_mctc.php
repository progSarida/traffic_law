<?php
include("_path.php");
include(INC . "/parameter.php");
include(CLS . "/cls_db.php");
include(CLS . "/cls_message.php");
include(INC . "/function.php");
require(INC . "/initialization.php");
include(INC . "/header.php");
require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$message=new CLS_MESSAGE();
checkPasswordExpiration($rs,'XXXX','MCTC VPN',$message,$str_out);
checkPasswordExpiration($rs,$_SESSION['cityid'],'MCTC Visure',$message,$str_out);

if(!PRODUCTION){
    $message->addWarning("Il collegamento FTP è disponibile solo in ambiente di produzione, lettura/scrittura su cartella locale TESTVISURE_FOLDER");
}

$str_out .='
<div class="row-fluid">'.$message->getMessagesString().'
   <form name="f_Search" id="f_Search" method="post" action="">
            <div class="col-sm-3 BoxRowLabel">
                Ricerca trasgressore
            </div>
            <div class="col-sm-2 BoxRowLabel" style="text-align:right;padding-right:2rem;">
                Tipo veicolo
            </div>
            <div class="col-sm-2 BoxRowCaption">
                '. CreateSelect("VehicleType","Disabled=0","Title".LAN,"VehicleTypeId","Id","Title".LAN,1,true,14) .'
            </div>              
            <div class="col-sm-2 BoxRowLabel" style="text-align:right;padding-right:2rem;">
                Targa veicolo
            </div>               
            <div class="col-sm-2 BoxRowCaption">
                <input class="form-control frm_field_text frm_field_required" type="text" name="Plate" id="Plate" style="width:10rem" />
            </div>          
       
            <div class="col-sm-1 BoxRowLabel" style="text-align: center">
                <i class="glyphicon glyphicon-search" style="margin-top:0.3rem;font-size:1.6rem;"></i>
            </div>    	
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12 BoxRowLabel">
                Dati Trasgressore
        </div>
        <div class="clean_row HSpace4"></div>

		<input type="hidden" name="Genre" id="Genre">    
		<div id="Div_Trespasser_Company" style="display:none">
			<div class="col-sm-3 BoxRowLabel">
				Ragione sociale
			</div>
			<div class="col-sm-9 BoxRowCaption">
				<input name="CompanyName" id="CompanyName" type="text" style="width:40rem">
			</div>
		</div>
		<div id="Div_Trespasser_Person" style="display:none">
			<div class="col-sm-2 BoxRowLabel">
				Cognome
			</div>
			<div class="col-sm-4 BoxRowCaption">
				<input class="form-control frm_field_str" type="text" name="Surname" id="Surname" style="width:20rem">
			</div>
			<div class="col-sm-2 BoxRowLabel">
				Nome
			</div>
			<div class="col-sm-4 BoxRowCaption">
				<input class="form-control frm_field_str" type="text" name="Name" id="Name" style="width:20rem">
			</div>
			<div class="clean_row HSpace4"></div>
			<div class="col-sm-2 BoxRowLabel">
				Data Nascita
			</div>
			<div class="col-sm-3 BoxRowCaption">
				<input class="form-control frm_field_date" type="text" name="BornDate" id="BornDate" style="width:20rem">
			</div>
			<div class="col-sm-2 BoxRowLabel">
				Luogo Nascita
			</div>
			<div class="col-sm-5 BoxRowCaption">
				<input class="form-control frm_field_str" type="text" name="BornPlace" id="BornPlace" style="width:35rem">
			</div>
			<div class="clean_row HSpace4"></div>

			<div class="col-sm-3 BoxRowLabel">
				Codice fiscale
			</div>
			<div class="col-sm-9 BoxRowCaption">
				<input type="text" name="TaxCode" id="TaxCode" style="width:30rem">
			</div>
                                        
		</div>
		<div id="Div_Trespasser_Address" style="display:none">
			<div class="clean_row HSpace4"></div>
			<div class="col-sm-3 BoxRowLabel">
				Indirizzo
			</div>
			<div class="col-sm-9 BoxRowCaption">
				<input class="form-control" type="text" name="Address" id="Address" style="width:41rem">
			</div>
		
			<div class="clean_row HSpace4"></div>

			<div class="col-sm-1 BoxRowLabel">
				Cap
			</div>
			<div class="col-sm-1 BoxRowCaption">
				<input type="text" id="ZIP" name="ZIP" style="width:8rem">
			</div>

			<div class="col-sm-2 BoxRowLabel">
				Città
			</div>
			<div class="col-sm-4 BoxRowCaption">
				<input class="form-control" type="text" name="City" id="City" style="width:30rem">
			</div>
			<div class="col-sm-2 BoxRowLabel">
				Provincia
			</div>
			<div class="col-sm-2 BoxRowCaption">
				<input class="form-control" type="text" name="Province" id="Province" style="width:12rem">
			</div>
		</div>
        
        <div class="clean_row HSpace4"></div>
        <div class="col-sm-12 BoxRowLabel">
                Dati Veicolo
        </div>
        <div class="clean_row HSpace4"></div>
        <div style="height:12rem;">
            <div id="Div_Vehicle" style="display:none">
                <div class="col-sm-2 BoxRowLabel">
                    Targa
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" class="form-control frm_field_string frm_field_required" name="VehiclePlate" id="VehiclePlate" style="width:10rem; text-transform:uppercase">
                </div>			
                <div class="col-sm-2 BoxRowLabel">
                    Massa
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" class="form-control frm_field_numeric" name="VehicleMass" id="VehicleMass" style="width:8rem">	
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Colore
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input type="text" class="form-control frm_field_string" name="VehicleColor" id="VehicleColor"  style="width:8rem">	
                </div>
                
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-3 BoxRowLabel">
                    Marca
                </div>
                <div class="col-sm-9 BoxRowCaption">
                    <input type="text" class="form-control frm_field_string" name="VehicleBrand" id="VehicleBrand" style="width:40rem">	
                </div>
                
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-3 BoxRowLabel">
                    Modello
                </div>
                <div class="col-sm-9 BoxRowCaption">
                    <input type="text" class="form-control frm_field_string" name="VehicleModel" id="VehicleModel"  style="width:40rem">	
                </div>
                <div class="clean_row HSpace4"></div>
                <div class="col-sm-2 BoxRowLabel">
                    Ultima revisione
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    <input class="form-control frm_field_date" type="text" name="VehicleLastRevisionDate" id="VehicleLastRevisionDate" style="width:20rem">
                </div>
                <div class="col-sm-2 BoxRowLabel">
                    Esito
                </div>
                <div class="col-sm-4 BoxRowCaption">
                    <input class="form-control frm_field_string" type="text" name="VehicleLastRevisionResult" id="VehicleLastRevisionResult" style="width:40rem">
                </div>                
        </div>         

';



echo $str_out;
?>
    <script type="text/javascript">
        $('document').ready(function(){

            $(".glyphicon-search").hover(function(){
                $(this).css("color","#2684b1");
                $(this).css("cursor","pointer");
            },function(){
                $(this).css("color","#fff");
                $(this).css("cursor","");
            });



            $('.glyphicon-search').click(function () {
                $('.glyphicon-search').hide();

                var VehicleTypeId = $("#VehicleTypeId").val();
                var VehiclePlate = $("#Plate").val();
                if(VehiclePlate.startsWith("X") && VehicleTypeId==12)
                    VehicleTypeId=7;

                $.ajax({
                    url: 'ajax/ajx_src_trespasser_with_plate_exe.php',
                    type: 'POST',
                    dataType: 'json',
                    cache: false,
                    data: {VehicleTypeId: VehicleTypeId, VehiclePlate: VehiclePlate},
                    success: function (data) {
                        var Genre           = data.Genre;
                        var a_Trespasser    = data.Trespasser;
                        var a_Vehicle       = data.Vehicle;
                        var a_Error         = data.Error;

                        if(a_Error.length === 0){
                            $('#Genre').val(Genre);
                            if(Genre=="D"){
                                $('#CompanyName').val(a_Trespasser['CompanyName']);

                                $('#Div_Trespasser_Person').hide();
                                $('#Div_Trespasser_Company').show();


                            }else{
                                $('#Surname').val(a_Trespasser['Surname']);
                                $('#Name').val(a_Trespasser['Name']);
                                $('#BornDate').val(a_Trespasser['BornDate']);
                                $('#TaxCode').val(a_Trespasser['TaxCode']);
                                $('#BornPlace').val(a_Trespasser['BornPlace']);

                                $('#Div_Trespasser_Company').hide();
                                $('#Div_Trespasser_Person').show();
                            }


                            $('#Province').val(a_Trespasser['Province']);
                            $('#City').val(a_Trespasser['City']);
                            $('#Address').val(a_Trespasser['Address']);
                            $('#ZIP').val(a_Trespasser['ZIP']);
                            $('#Div_Trespasser_Address').show();


                            $('#VehiclePlate').val(a_Vehicle['VehiclePlate']);
                            $('#VehicleMass').val(a_Vehicle['VehicleMass']);



                            $('#VehicleBrand').val(a_Vehicle['VehicleBrand']);
                            $('#VehicleModel').val(a_Vehicle['VehicleModel']);
                            $('#VehicleLastRevisionDate').val(a_Vehicle['VehicleLastRevisionDate']);
                            $('#VehicleLastRevisionResult').val(a_Vehicle['VehicleLastRevisionResult']);
                            $('#Div_Vehicle').show();

                        } else {
                            alert(a_Error['ErrorCode'] + ': ' +a_Error['ErrorDescription']);
                        }
                        $('.glyphicon-search').show();
                    },
                    error: function (data) {
                    	$('.glyphicon-search').show();
                        console.log(data);
                        alert("error: " + data.responseText);
                    }



                });
            });






            $('#f_Search').bootstrapValidator({
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
            });
        });

    </script>
<?php

include(INC."/footer.php");
