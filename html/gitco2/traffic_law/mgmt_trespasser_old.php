<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

require(INC . '/menu_'.$_SESSION['UserMenuType'].'.php');

$s_TrespasserNation            = CheckValue('TrespasserNation','s');
$Search_TrespasserCountry      = CheckValue('Search_TrespasserCountry','s');

$s_TrespasserNationN = "";
$s_TrespasserNationF = "";


$selectNazione = $rs->SelectQuery('Select * from sarida.Country');

$selectProvince = $rs->SelectQuery('Select * from sarida.Province');

$str_CurrentPage .= "&TrespasserNation=".$s_TrespasserNation."&Search_TrespasserCountry=".$Search_TrespasserCountry;

if ($s_TrespasserNation == "F"){
    $str_Where .= " AND CountryId!='Z000'";
    $s_TrespasserNationF = " SELECTED ";
    if($Search_TrespasserCountry!=""){

        $str_Where .= " AND CountryId='".$Search_TrespasserCountry."'";
    }

} else {
    $str_Where .= " AND CountryId='Z000'";
    $s_TrespasserNationN = " SELECTED ";
}

$str_Search_ViolationDisabled = "";
if($s_TrespasserNation!="F"){
    $str_Search_ViolationDisabled = "$('#Search_TrespasserCountry').prop('disabled', true);";
}

$str_BaseSearch = $str_Search_ViolationDisabled;

$str_BaseSearch .= '

$(\'.glyphicon-search\').click(function(){
    $(\'#f_Search\').submit();
});

$(\'#f_Search\').on(\'keyup keypress\', function(e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
        $("#f_Search").submit();
    }
});

$("#TrespasserNation").change(function(){
    if ($("#TrespasserNation").val()==\'F\'){
        $(\'#Search_TrespasserCountry\').prop("disabled", false);

    }else{
        $(\'#Search_TrespasserCountry\').prop("disabled", true);
    }
});
';

if (isset($_REQUEST['exists'])){
    echo '
    <div class="container">
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-4">
                <div class="alert alert-warning">
                    Il trassgressore esiste gia nella database. I dati non sono stati salvati. 
                </div>
            </div>
            <div class="col-md-4"></div>
        </div>
    </div>';
}

$str_out .='



    <div class="row-fluid">
        <form id="f_Search" action="'.$str_CurrentPage.'" method="post">
        <div class="col-sm-12" >
            <div class="col-sm-11 BoxRow" style="height:4.6rem; border-right:1px solid #E7E7E7;">
    
                <div class="col-sm-2 BoxRowLabel">
                    Ragione sociale/Cognome
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_Trespasser" type="text" style="width:8rem" value="'.$Search_Trespasser.'">
                </div>          
                <div class="col-sm-1 BoxRowLabel">
                    Prot/Ref
                </div>
                <div class="col-sm-1 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_Ref" type="text" style="width:10rem" value="'.$Search_Ref.'">
                </div>   
                <div class="col-sm-2 BoxRowLabel">
                    C.Fiscale/P.IVA
                </div>
                <div class="col-sm-3 BoxRowCaption">
                    <input class="form-control frm_field_string" name="Search_TaxCode" type="text" style="width:15rem" value="'.$Search_TaxCode.'">
                </div>  
                            
                <div class="clean_row HSpace4"></div>
            
            
                <div class="col-sm-1 BoxRowLabel">
                    Nazionalità
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <select class="form-control" name="TrespasserNation" id="TrespasserNation">
                        <option></option>
                        <option value="N"'.$s_TrespasserNationN.'>Nazionali</option>
                        <option value="F"'.$s_TrespasserNationF.'>Estere</option>								
                    </select>
                </div>  
                <div class="col-sm-1 BoxRowLabel">
                    Nazione
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    '. CreateSelectConcat("SELECT DISTINCT T.CountryId, C.Title FROM Trespasser T JOIN Country C ON T.CountryId=C.Id WHERE CountryId!='Z000' ORDER BY C.Title","Search_TrespasserCountry","CountryId","Title",$Search_TrespasserCountry,false,12) .'
                </div>  
                <div class="col-sm-6 BoxRowLabel">
                
                </div>
            </div>
            <div class="col-sm-1 BoxRow" style="height:4.6rem;">
                <div class="col-sm-12 BoxRowFilterButton" style="text-align: center">
                    <i class="glyphicon glyphicon-search" style="margin-top:0.6rem;font-size:3rem;"></i>	
                </div>
            </div>          
        </form>
	    </div>
    	<div class="row-fluid">
        	<div class="col-sm-12">
				<div class="table_label_H col-sm-1">ID</div>
				<div class="table_label_H col-sm-1">Genere</div>
				<div class="table_label_H col-sm-5">Nominativo</div>
				<div class="table_label_H col-sm-2">Citta</div>
				<div class="table_label_H col-sm-1">Paese</div>
				<div class="table_label_H col-sm-1">Verbali</div>
        		<div class="table_add_button col-sm-1 right">
        			<span class="glyphicon glyphicon-plus-sign add_button" style="margin-right:0.3rem; "></span>
				</div>
				<div class="clean_row HSpace4"></div>';


$page = CheckValue("page","n");

$pagelimit = $page * PAGE_NUMBER;

$strOrder = "CompanyName, Surname";


 
$table_rows = $rs->Select('V_Trespasser',$str_Where, $strOrder, $pagelimit . ',' . PAGE_NUMBER);
$RowNumber = mysqli_num_rows($table_rows);

if ($RowNumber == 0) {
	$str_out.= 'Nessun record presente';
} else {
	while ($table_row = mysqli_fetch_array($table_rows)) {

		$rs_Row = $rs->SelectQuery("SELECT COUNT(*) TOT FROM V_FineTrespasser WHERE CityId='".$_SESSION['cityid']."' AND TrespasserId=".$table_row['Id']);
		$r_Row = mysqli_fetch_array($rs_Row);

		$str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['Id'] .'</div>';
		$str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['Genre'] .'</div>';
		$str_out.= '<div class="table_caption_H col-sm-5">' . $table_row['CompanyName'] . $table_row['Surname'] .' '. $table_row['Name'].'</div>';
        $str_out.= '<div class="table_caption_H col-sm-2">' . $table_row['City'] .'</div>';
        $str_out.= '<div class="table_caption_H col-sm-1">' . $table_row['CountryTitle'] .'</div>';

		$str_out.= '<div class="table_caption_H col-sm-1">' . $r_Row['TOT'] .'</div>';



		$str_out.= '<div class="table_caption_button col-sm-1">';
		$str_out.= ChkButton($aUserButton, 'viw','<a href="mgmt_trespasser_viw.php?'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-eye-open" ></span></a>');
        $str_out.= '&nbsp;';
        $str_out.= ChkButton($aUserButton, 'upd','<a href="mgmt_trespasser_upd.php?'.$str_GET_Parameter.'&Id='.$table_row['Id'].'"><span class="glyphicon glyphicon-pencil" ></span></a>');

		$str_out.= '</div>
			            <div class="clean_row HSpace4"></div>';
		}

}
$table_Trespassers_number = $rs->Select('V_Trespasser',$str_Where, 'Id');
$TrespasserNumberTotal = mysqli_num_rows($table_Trespassers_number);

$str_out.= CreatePagination(PAGE_NUMBER, $TrespasserNumberTotal, $page, $str_CurrentPage,"");
$str_out.= '<div>
	</div>';


echo $str_out;
?>
<div class="overlay" id="overlay" style="display:none;"></div>

<div id="BoxTrespasserInsert">
	<div id="FormTrespasser">
		<form name="f_ins" id="f_ins" class="form-horizontal" action="mgmt_trespasser_add_exe.php" method="post">
			<input type="hidden" name="Genre" id="Genre" value="D">
			<div class="BoxRow form-group" style="height:4rem;">
				<div class="BoxRowTitle" id="BoxRowTitle">
					Inserimento anagrafica
				</div>
			</div>
			<div class="clean_row HSpace4"></div>
			<ul class="nav nav-tabs" id="mioTab">
				<li class="active" id="tab_company"><a href="#company" data-toggle="tab">DITTA</a></li>
				<li id="tab_Trespasser"><a href="#Trespasser" data-toggle="tab">PERSONA</a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="company">
					<div class="row-fluid">
						<div class="col-sm-12 BoxRow">
							<div class="col-sm-3 BoxRowLabel">
								Ragione sociale
							</div>
							<div class="col-sm-7 BoxRowCaption">
								<input class="form-control frm_field_string" name="CompanyName" type="text" style="width:20rem">
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="Trespasser">
					<div class="row-fluid">
						<div class="col-sm-12 BoxRow">
							<div class="col-sm-3 BoxRowLabel">
								Sesso
							</div>
							<div class="col-sm-7 BoxRowCaption">
								<input type="radio" value="M" name="Sex" id="sexM" CHECKED>M &nbsp;
                                <input type="radio" id="sexF" value="F" name="sex">F
							</div>
						</div>
                        <div class="clean_row HSpace4"></div>
                        <div class="col-sm-12 BoxRow">
                            <div class="col-sm-2 BoxRowLabel">
                                Cognome
                            </div>
                            <div class="col-sm-4 BoxRowCaption">
                                <input class="form-control frm_field_string" type="text" id="Surname" name="Surname" required>
                            </div>
                            <div class="col-sm-2 BoxRowLabel">
                                Nome
                            </div>
                            <div class="col-sm-4 BoxRowCaption">
                                <input class="form-control frm_field_string" type="text" id="Name" name="Name" required>
                            </div>
                        </div>
					    <div class="clean_row HSpace4"></div>

                        <div class="col-sm-12 BoxRow">
                            <div class="col-sm-2 BoxRowLabel">
                                Data Nascita
                            </div>
                            <div class="col-sm-4 BoxRowCaption">
                                <input class="form-control frm_field_date" type="text" name="BornDate" id="BornDate" style="width:12rem">
                            </div>

						<div class="col-sm-1 BoxRowLabel">
							Nazione
						</div>
                            <div class="col-sm-4 BoxRowCaption">
                             
                        <select id="CountryId" class="form-control" name="CountryId">
							<option value="ita" selected>Italy</option>
							<?php
							$out ='';

                        	while ($row = mysqli_fetch_array($selectNazione)) {
                          	$out .= '<option value="'. $row['Id'] .'" >'. $row['Title'] . '</option>';
                        	}
                        	echo $out;
							?>
                		</select>
                   
                            </div>
                        </div>
                       	<div class="col-sm-12 BoxRow">
							<div class="col-sm-1 BoxRowLabel">
							Provinca
							</div>
                       		<div class="col-sm-4 BoxRowCaption">
                       		<input id="provinceInput" class="form-control frm_field_string" type="text" name="Province" style="width:12rem">

                        	<select id="provinceSelect" class="form-control" name="Province">
								<?php
								$out ='';

                        		while ($row = mysqli_fetch_array($selectProvince)) {
                          		$out .= '<option data-id="'. $row['Id'] .'" value="'. $row['ShortTitle'] .'" >'. $row['Title'] . '</option>';
                        		}
                        		echo $out;
								?>
                			</select>
                            </div>
                        	<div class="col-sm-1 BoxRowLabel">
								Paese
							</div>
                            <div class="col-sm-4 BoxRowCaption">
                                <input id="paeseInput" class="form-control frm_field_string" type="text" name="City" style="width:12rem">
                            	<select id="paeseSelect" class="form-control" name="CountryId">
                            
                            	</select>
                            </div>

                       	</div>
                            <div class="col-sm-2 BoxRowLabel">
                                    Luogo Nascita
                            </div>
                            <div class="col-sm-4 BoxRowCaption">
                                    <input class="form-control frm_field_string" type="text" name="BornPlace" id="BornPlace" style="width:12rem">
                            </div> 
                        <div class="col-sm-12 BoxRow">
                            <div class="col-sm-1 BoxRowLabel">
                                Patente
                            </div>

                            <div class="col-sm-1 BoxRowLabel">
                                Cat.
                            </div>
                            <div class="col-sm-1 BoxRowCaption">
                                <input class="form-control frm_field_string" type="text" name="LicenseCategory"  style="width:4rem">
                            </div>
                            <div class="col-sm-1 BoxRowLabel">
                                Num.
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input class="form-control frm_field_string" type="text" name="LicenseNumber" style="width:12rem">
                            </div>

                            <div class="col-sm-1 BoxRowLabel">
                                Data
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input class="form-control frm_field_date" type="text" name="LicenseDate"  style="width:8rem">
                            </div>
                            <div class="col-sm-1 BoxRowLabel">
                                Ente
                            </div>
                            <div class="col-sm-2 BoxRowCaption">
                                <input class="form-control frm_field_string" type="text" name="LicenseOffice" style="width:12rem">
                            </div>

                        </div>
                    <div class="clean_row HSpace4"></div>
				</div>
			</div>
            <div class="col-sm-12 BoxRow">
                <div class="col-sm-1 BoxRowLabel">
                    C.F./P.IVA
                </div>
                <div class="col-sm-5 BoxRowCaption">
                    <input class="form-control frm_field_string" type="text" name="TaxCode" id="TaxCode" style="width:18rem">
                </div>
				<div class="col-sm-2 BoxRowLabel">
					Indirizzo
				</div>
				<div class="col-sm-4 BoxRowCaption">
					<input class="form-control frm_field_string" type="text" name="Address" style="width:14rem">
				</div>
            </div>
            <div class="clean_row HSpace4"></div>
   
			</div>
			

			<div class="col-sm-12 BoxRow">
				<div class="col-sm-2 BoxRowLabel">
					Mail
				</div>
				<div class="col-sm-4 BoxRowCaption">
					<input class="form-control frm_field_string" type="text" name="Mail">
				</div>
				<div class="col-sm-2 BoxRowLabel">
					Telefono
				</div>
				<div class="col-sm-4 BoxRowCaption">
					<input class="form-control frm_field_string" type="text" name="Phone">
				</div>
			</div>
            <div class="clean_row HSpace4"></div>
            <div class="col-sm-12 BoxRow">
                <div class="col-sm-2 BoxRowLabel">
                    Cap
                </div>
                <div class="col-sm-2 BoxRowCaption">
                    <input class="form-control frm_field_string" type="text" name="ZIP" style="width:8rem">
                </div>
            </div>
            <div class="clean_row HSpace4"></div>

			<div class="clean_row HSpace4"></div>





			<div class="BoxRow" style="height:4rem;">
				<div class="BoxRowButton" id="BoxRowButton">
					<input type="submit" value="Salva" class="btn btn-primary" />
				</div>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript">

	$(document).ready(function () {

        <?=  $str_BaseSearch ?>






		$(".glyphicon-filter").click(function(){
			$("#f_Search").submit();
		});

		$('#tab_company').click(function(){
			$('#Genre').val('D');
			$( "#sexM" ).prop( "checked", true );
			$( "#sexF" ).prop( "checked", false );

            $('#TaxCode').val('');
		});

		$('#tab_Trespasser').click(function(){
			$('#Genre').val('M');

            $('#TaxCode').val('');
		});

		$('#sexM').click(function(){
			$('#Genre').val('M');
			$( "#sexF" ).prop( "checked", false );

		});
		$('#sexF').click(function(){
			$('#Genre').val('F');
			$( "#sexM" ).prop( "checked", false );

		});

		$('#CompanyName').click(function () {
            alert('copmany')
			$('#Surname').val('');
			$('#Name').val('');
        });

        $('#Suername').click(function () {
            $('#CompanyName').val('');
		});


		$(".add_button").click(function(){
			$('#overlay').fadeIn('fast');
			$('#BoxTrespasserInsert').fadeIn('slow');
		});

		$("#overlay").click(function(){
			$(this).fadeOut('fast');
			$('#BoxTrespasserInsert').hide();

		});
		$("#fa_submit").click(function(){
			$("#f_Search").submit();
		});

        $('#paeseInput').hide();
        $('#provinceInput').hide().prop('disabled', true);
		
        $('#CountryId').change(function () {
			if ($(this).val() != 'ita') {
				$('#provinceSelect').hide().prop('disabled', true)
				$('#paeseSelect').hide().prop('disabled', true)
				$('#paeseInput').show()
				$('#provinceInput').show().prop('disabled', false)
			}else {
				$('#provinceSelect').show().prop('disabled', false)
				$('#paeseSelect').show().prop('disabled', false)
				$('#paeseInput').hide()
				$('#provinceInput').hide().prop('disabled', true)
			}
		});

 
		$('#provinceSelect').click(function() {
       			var provinceId =  $(this).find('option:selected').attr("data-id");
                
                console.log($(this));
       			$('#paeseSelect').html('');
       			$.ajax({
       			     type: 'GET',
       			     url: "req_new.php?provinceId="+provinceId,
       			     success:function(data){
       			         if (data) {
       			             data = ( JSON.parse(data) ).paese;
       			             var el;
       			             for(var i=0; i<data.length; i++){
       			             el = '<option value="'+ data[i][0].id +'">'+ data[i][0].title +'</option>';
       			             $('#paeseSelect').append(el);
                    }
                }
            }
        });
       //$('#paeseSelect').html('<option></option>');
    });

            $('#paeseSelect').click(function() {
                $('#paeseInput').val($(this).find('option:selected').text())
            });



		$('#f_ins').bootstrapValidator({
			fields: {

				Address: {
					validators: {
						notEmpty: {message: 'Obbligatorio'},
					}
				},
				CountryId: {
					validators: {
						notEmpty: {message: 'Obbligatorio'},
					}
				},
				City: {
					validators: {
						notEmpty: {message: 'Obbligatorio'},
					}
				},
				Name: {
					validators: {
						notEmpty: {message: 'Obbligatorio'},
					}
				},
				Surname: {
					validators: {
						notEmpty: {message: 'Obbligatorio'},
					}
				},
				CompanyName: {
					validators: {
						notEmpty: {message: 'Obbligatorio'},
					}
				},
			}
		});

	});
	function autocomplet() {
		var min_length = 2; // min caracters to display the autocomplete
		var keyword = $('#Trespasserfind').val();
		if (keyword.length >= min_length) {
			$.ajax({
				url: 'ajax/search_Trespasser.php',
				type: 'POST',
				data: {keyword:keyword},
				success:function(data){
					$('#TrespasserList').show();
					$('#TrespasserList').html(data);
				}
			});
		} else {
			$('#TrespasserList').hide();
		}
	}

	function set_item(item,id) {
		$('#Trespasserfind').val(item);
		$('#Trespasserid').val(id);
		$('#TrespasserList').hide();

	}

    function strrpos (haystack, needle, offset) {
      var i = (haystack+'').indexOf(needle, (offset || 0));
      return i === -1 ? false : i;
    }

function compute_CF(cognome, nome, tipo, data, cod_comune)
{
    alfabeto = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    vocali = "AEIOU";
    numeri = "0123456789";
    mesi = "ABCDEHLMPRST";
    alfabeto_disp = "BAKPLCQDREVOSFTGUHMINJWZYX";
    numeri_disp = "10   2 3 4   5 6 7 8 9";
    
    CF = "";
    code = 0;
    if(tipo=="D") return "Impossibile generare il Codice Fiscale per una persona giuridica.";
    
    // Determina
    for( var i=0; i<=1; i++)
    {
        if(i==0)
        {  word = cognome; }
        else
        {  word = nome; }
        
        word = word.replace(" ","");
        word = word.replace("\'","");
        word = word.replace("à","a");
        word = word.replace("è","e");
        word = word.replace("é","e");
        word = word.replace("ì","i");
        word = word.replace("ò","o");
        word = word.replace("ù","u");
        word = word.toUpperCase();
        
        extracted_cons = "";
        extracted_vocs = "";
        
        for( var j=0; j<word.length; j++)
        {
         char = word.substr(j,1);
            isthere = strrpos(vocali, char);
            if(isthere===false) // NOTA: I tre "=" sono voluti.
               extracted_cons = extracted_cons+char;
            else
               extracted_vocs = extracted_vocs+char;
        }
        
        num_cons = extracted_cons.length;
        num_vocs = extracted_vocs.length;
        
        if    ( num_cons>3 && i==1 )
        { CF = CF + extracted_cons.substr(0,1) + extracted_cons.substr(2,2); }
        else if( num_cons>2 )
        {   CF = CF + extracted_cons.substr(0,3);        }
        else if( num_cons==2 && num_vocs>0 )
        {   CF = CF + extracted_cons + extracted_vocs.substr(0,1);    }
        else if( num_cons==1 && num_vocs==1 )
        {   CF = CF + extracted_cons + extracted_vocs+"X";      }
        else if( num_cons==1 && num_vocs>1 )
        { CF = CF + extracted_cons + extracted_vocs.substr(0,2);    }
        else if( num_cons==0 && num_vocs>2 )
        { CF = CF + extracted_vocs.substr(0,3);        }
        else if( num_cons==0 && num_vocs==2)
        { CF = CF + extracted_vocs + "X";          }
        else return "Le lettere che compongono cognome e nome non sono sufficienti per la generazione del Codice Fiscale. Controllare cognome e nome.";
    }

    array_data = new Array();
    array_data = data.split("/");
    anno = array_data[2];
    
    CF = CF + anno.substr(2,2);
    
    CF = CF + mesi.substr( array_data[1]-1 , 1 );
    
    giorno = parseInt(array_data[0]);
    
    if(tipo == 'M')
    { 
     giorno += 100;
     giorno = giorno.toString();
     gg = giorno.substr(1,2); 
    }
    else
    { 
     giorno += 140;
     giorno = giorno.toString();
     gg = giorno.substr(1,2); 
    }
    
    CF = CF + gg;
 
    CF = CF + cod_comune;

    for ( var i=0; i < CF.length; i++ )
    {
        char = CF.substr(i,1);
        if((i%2)==0) // NOTA: se i   pari, cio  se la lettera   dispari.
           code = code + strrpos(numeri_disp,char) + strrpos( alfabeto_disp,char );
        else
           code = code + strrpos(numeri,char) + strrpos( alfabeto,char );
    }
    
    CF = CF + alfabeto.substr((code%26),1);
 
    if(CF.length!=16) return "Non e' stato possibile generare il Codice Fiscale.";
    
    return CF;
}

var $cognome = $('#Surname')
var $nome = $('#Name')
var $tipoM = $('#sexM')
var $tipoF = $('#sexF')
var $date = $('#BornDate')
var $country = $('#CountryId')
var $cod_comune = $('#paeseSelect')

var fillCF = function () {
    var cognome = $cognome.val()
    var nome = $nome.val()
    var tipo = $tipoM.prop('checked') ? 'M' : 'F'
    var date = $date.val()
    var cod_comune = ($country.val() == 'ita' ? $cod_comune : $country).val()

    var $TaxCode = $('#TaxCode')

    if (cognome && nome && tipo && date && cod_comune) {
        $TaxCode.val(
            compute_CF(cognome, nome, tipo, date, cod_comune)
        )
    } else {
        $TaxCode.val("Non e' stato possibile generare il Codice Fiscale.")
    }
}

$(document).on('change', $cognome, fillCF)
$(document).on('change', $nome, fillCF)
$(document).on('change', $tipoM, fillCF)
$(document).on('change', $tipoF, fillCF)
$(document).on('change', $date, fillCF)
$(document).on('change', $cod_comune, fillCF)
</script>
<?php
include(INC."/footer.php");