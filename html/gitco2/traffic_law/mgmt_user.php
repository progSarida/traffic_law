<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
include(INC."/header.php");

include(INC."/menu.php");

$LinkPage = curPageName();


$strWhere = null;

$userfind = CheckValue('userfind','s');
$userid = CheckValue('userid','n');

if($userid>0) $strWhere = "id=".$userid;
else if($userfind!="") $strWhere = "surname LIKE '%$userfind%' OR companyname LIKE '%$userfind%'";


$rs= new CLS_DB();

$strLedgerUser = '<div style="position:relative;width:100%;margin:0 auto;">
		<div class="user_label_H p_w61">Ragione sociale</div>
		<div class="user_label_H p_w27">CF/P.IVA</div>
		<div class="user_label_H p_w10"><span class="glyphicon glyphicon-plus-sign user_add_button"></span></div>
		<div class="clean_row HSpace4"></div>
		</div>';

$strOperationUser = '<div style="position:relative;width:100%;margin:0 auto;">
		<div class="user_label_H p_w15">Data</div>
		<div class="user_label_H p_w63">Operazione</div>
		<div class="user_label_H p_w20">Importo</div>
		<div class="clean_row HSpace4"></div>
		</div>
		<div id="OperationUser"></div>';


$page = CheckValue('page','n');
$pagelimit=$page * PAGE_NUMBER;




$ledger_users = $rs->Select('ledger_user',$strWhere,"companyname, surname, name",$pagelimit.','.PAGE_NUMBER);
$UserNumber = mysqli_num_rows($ledger_users);

if($UserNumber==0){
	$strLedgerUser .= 'Nessun utente presente';
}
else{
	while($ledger_user = mysqli_fetch_array($ledger_users)){
		if($ledger_user['type']=="D"){
			$col1 = $ledger_user['companyname'];
			$col2 = $ledger_user['vatnumber'];
			if($col2==0) $col2 = "&nbsp;";
		}else{
			$col1 = $ledger_user['surname']." ".$ledger_user['name'];
			$col2 = $ledger_user['taxcode'];
			if($col2=='') $col2 = "&nbsp;";
		}


		$strLedgerUser .= 	'<div style="position:relative;width:100%;margin:0 auto;">
			<div class="user_caption_H p_w61">'.$col1.'</div>
			<div class="user_caption_H p_w27" >'.$col2.'</div>
			<div class="user_caption_button p_w10 mgmt_user">
				<a hhref=""><span class="glyphicon glyphicon-eye-open"><input type="hidden" id="id" value="'.$ledger_user['id'].'" /> </span></a>&nbsp;
				<a hhref=""><span class="glyphicon glyphicon-pencil"><input type="hidden" id="id" value="'.$ledger_user['id'].'" /> </span></a>&nbsp;
				<a hhref=""><span class="glyphicon glyphicon-euro"><input type="hidden" id="id" value="'.$ledger_user['id'].'" /> </span></a>
			</div>
			<div class="clean_row HSpace4"></div>
		</div>';
	}
	$ledger_users_number = $rs->Select('ledger_user',$strWhere);
	$UserNumberTotal = mysqli_num_rows($ledger_users_number);


	$strLedgerUser .= CreatePagination(PAGE_NUMBER, $UserNumberTotal,$page,$LinkPage,"");
	$strLedgerUser .= '<div>
	</div>';
}


$select_company = CreateSelect("company_type",null,"shorttitle","companytypeid","id","shorttitle","",false);



include(INC."menu.php");
?>

<div class="container-fluid">
	<div class="row">
		<div class="col-xs-12 FN_Title">

			GESTIONE ANAGRAFICHE

		</div>
	</div>
	<div class="DivHSpace"></div>
	<div class="row">
		<div class="col-xs-12 FN_IN" style="height:4.2rem;">
			<form name="fins" id="fins" action="<?= $LinkPage ?>" method="post">
				<input type="hidden" name="userid" id="userid" />
			<div >
				<div class="BoxRowLabel" style="line-height:4rem;">
					Controparte
				</div>
				<div class="BoxRowCaption" style="left:10rem;position:absolute;">
					<input type="text" name="userfind" id="userfind" onkeyup="autocomplet();" style="position:relative;top:0.5rem;" />
					<ul id="UserList" class="ulUserList_U"></ul>

				</div>
				<div style="left:52rem;position:absolute;">
					<input type="submit" value="CERCA" class="btn btn-primary" style="position:relative;top:0.5rem;" />
				</div>
			</div>
				</form>

		</div>
	</div>
	<div class="clean_row HSpace4"></div>
	<div class="row">
		<div class="col-xs-6 FN_Content_IN"><?= $strLedgerUser ?></div>
		<div class="col-xs-6 FN_Content_IN"><?= $strOperationUser ?></div>

	</div>
</div>


<div class="overlay" id="overlay" style="display:none;"></div>
<div id="BoxUserInsert">
	<div id="FormUser">
		<form name="fins" id="fins" action="mgmt_trespasser_add_exe.php" method="post">
		<input type="hidden" name="type" id="type" value="D">
			<div class="BoxRow">
				<div class="BoxRowTitle" id="BoxRowTitle">
					Inserimento anagrafica
				</div>
			</div>
			<div class="clean_row HSpace4"></div>
			<ul class="nav nav-tabs" id="mioTab">
				<li class="active" id="tab_company"><a href="#company" data-toggle="tab">DITTA</a></li>
				<li id="tab_user"><a href="#user" data-toggle="tab">PERSONA</a></li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="company">
					<div class="BoxRow">
						<div class="BoxRowLabel">
							Ragione sociale
						</div>
						<div class="BoxRowCaption">
							<input type="text" name="CompanyName">
						</div>
					</div>
					<div class="clean_row HSpace4"></div>


					
				</div>
				<div class="tab-pane" id="user">
					<div class="BoxRow">
						<div class="BoxRowLabel">
							Sesso
						</div>
						<div class="BoxRowCaption">
							<input type="radio" value="M" name="sex" id="sexM" CHECKED>M &nbsp; <input type="radio" id="sexF" value="F" name="sex">F
						</div>
					</div>
					<div class="clean_row HSpace4"></div>
					<div class="BoxRow">
						<div class="BoxRowLabel">
							Cognome
						</div>
						<div class="BoxRowCaption">
							<input type="text" name="Surname">
						</div>
					</div>
					<div class="clean_row HSpace4"></div>
					<div class="BoxRow">
						<div class="BoxRowLabel">
							Nome
						</div>
						<div class="BoxRowCaption">
							<input type="text" name="Name">
						</div>
					</div>
					<div class="clean_row HSpace4"></div>
					<div class="BoxRow">
						<div class="BoxRowLabel">
							Data nascita
						</div>
						<div class="BoxRowCaption">
							<input type="text" name="BornDate" id="BornDate" style="width:12rem">
						</div>
					</div>
					<div class="clean_row HSpace4"></div>
				</div>
			</div>
			<div class="BoxRow">
				<div class="BoxRowLabel">
					Indirizzo
				</div>
				<div class="BoxRowCaption">
					<input type="text" name="Address">
				</div>
			</div>
			<div class="clean_row HSpace4"></div>
			<div class="BoxRow">
				<div class="BoxRowLabel">
					Cap
				</div>
				<div class="BoxRowCaption">
					<input type="text" name="ZIP">
				</div>
			</div>
			<div class="clean_row HSpace4"></div>
			<div class="BoxRow">
				<div class="BoxRowLabel">
					Mail
				</div>
				<div class="BoxRowCaption">
					<input type="text" name="mail">
				</div>
			</div>
			<div class="clean_row HSpace4"></div>
			<div class="BoxRow">
				<div class="BoxRowLabel">
					Telefono
				</div>
				<div class="BoxRowCaption">
					<input type="text" name="Phone">
				</div>
			</div>
			<div class="clean_row HSpace4"></div>
			<div class="BoxRow">
				<div class="BoxRowButton" id="BoxRowButton">
					<input type="submit" value="Salva" class="btn btn-primary" />
				</div>
			</div>
		</form>
	</div>
</div>

<div id="BoxUserView">
</div>
<div id="BoxUserUpdate">
</div>

<script type="text/javascript">
	// When the document is ready
	$(document).ready(function () {
		$('#tab_company').click(function(){
			$('#type').val('D');
			$( "#sexM" ).prop( "checked", true );
			$( "#sexF" ).prop( "checked", false );
		});

		$('#tab_user').click(function(){
			$('#type').val('M');
		});

		$('#sexM').click(function(){
			$('#type').val('M');
		});
		$('#sexF').click(function(){
			$('#type').val('F');
		});



		$(".user_add_button").click(function(){
			$('#overlay').fadeIn('fast');
			$('#BoxUserInsert').fadeIn('slow');

		});

		$("#overlay").click(function(){
			$(this).fadeOut('fast');
			$('#BoxUserInsert').hide();
			$('#BoxUserUpdate').hide();
			$('#BoxUserView').hide();

		});
		$(".glyphicon-eye-open").click(function(){
			$('#overlay').fadeIn('fast');
			$('#BoxUserView').fadeIn('slow');

			var id = $(this).find("#id").val();
			var post_data = {'id':id};
			$.post('ajax/user_view.php', post_data, function(data) {
				$('#BoxUserView').html(data);

			}).fail(function(err) {
				alert("NON DA CHIAMATA :"+err.statusText);
			});

		});
		$(".glyphicon-pencil").click(function(){
			$('#overlay').fadeIn('fast');
			$('#BoxUserUpdate').fadeIn('slow');

			var id = $(this).find("#id").val();
			var post_data = {'id':id};
			$.post('ajax/user_update.php', post_data, function(data) {
				$('#BoxUserUpdate').html(data);

			}).fail(function(err) {
				alert("NON DA CHIAMATA :"+err.statusText);
			});

		});
		$(".glyphicon-euro").click(function(){
			var id = $(this).find("#id").val();
			var post_data = {'id':id};
			$.post('ajax/operation_user.php', post_data, function(data) {
				$('#OperationUser').html(data);
			}).fail(function(err) {
				alert("NON DA CHIAMATA :"+err.statusText);
			});


		});

	});
	function autocomplet() {
		var min_length = 2; // min caracters to display the autocomplete
		var keyword = $('#userfind').val();
		if (keyword.length >= min_length) {
			$.ajax({
				url: 'ajax/search_user.php',
				type: 'POST',
				data: {keyword:keyword},
				success:function(data){
					$('#UserList').show();
					$('#UserList').html(data);
				}
			});
		} else {
			$('#UserList').hide();
		}
	}

	function set_item(item,id) {
		$('#userfind').val(item);
		$('#userid').val(id);
		$('#UserList').hide();

	}
</script>

<?php
include(INC."footer.php");