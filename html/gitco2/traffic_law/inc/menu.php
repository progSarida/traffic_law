
<body>


<nav class="navbar navbar-default">

	<!-- Brand and toggle get grouped for better mobile display -->
	<div class="navbar-header FN_Menu">
		<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1" aria-expanded="false">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<a class="navbar-brand" style="color:#fff;" href="index.php">
            <i class="fa fa-home"></i>
		</a>
	</div>

	<!-- Collect the nav links, forms, and other content for toggling -->
	<div class="collapse navbar-collapse FN_Menu">
		<ul class="nav navbar-nav" id="menu">

			<?php




            $str_UserClass = 'title="'.$_SESSION['PasswordDay'].' giorni al cambio password" ';

            if($_SESSION['PasswordDay']>30) $str_UserClass .= 'style="color: #3c763d;"';
            elseif($_SESSION['PasswordDay']<7) $str_UserClass .= 'style="color: #C43A3A;"';
            else $str_UserClass .= 'style="color:#f7ecb5;"';


            $ApplicationMenu = "";
            $ApplicationSubMenu = "";


            $str_MenuPage = curPageName();
            $b_FindPage = ($str_MenuPage=="index.php") ? true : false;

            $menus = $rs->Select(MAIN_DB.".V_UserApplication", "UserId=".$_SESSION['userid']." AND MainMenuId=".MENU_ID);
			$row ='';


			while($menu = mysqli_fetch_array($menus)){
				if($menu['TitleMenu']!=$ApplicationMenu){
					if($ApplicationMenu!=""){
						$row .= '	</ul>
								</li>';
					}
					$ApplicationMenu = $menu['TitleMenu'];
					$row .= '<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$ApplicationMenu.'<span class="caret"></span></a>
								<ul class="dropdown-menu FN_Menu">';
					
				}
				if(strpos($menu['LinkPage'],"?")>=0)
				    $row .= '<li id="sub-menu"><a href="'.$menu['LinkPage'].'&PageTitle='.$menu['TitleMenu'].'/'.$menu['TitleSubMenu'].'">'.$menu['TitleSubMenu'].'</a></li>';
                else
                    $row .= '<li id="sub-menu"><a href="'.$menu['LinkPage'].'?PageTitle='.$menu['TitleMenu'].'/'.$menu['TitleSubMenu'].'">'.$menu['TitleSubMenu'].'</a></li>';
				if($str_MenuPage==$menu['LinkPage']){
                    $b_FindPage=true;
                }



			}


			$row .= '	</ul>
					</li>';
			echo $row;

			?>
			
		</ul>

		<ul class="nav navbar-nav navbar-right" id="menu_r">
            <li>
                <a href="#" class="apriticket">Apri Ticket</a>
            </li>
            <?php

            if($_SESSION['userlevel']>=3){
            $rs_Mail = $rs->Select('Mail', "ReadStatus=0 AND UserId=".$_SESSION['userid']);
                $str_Mail = (mysqli_num_rows($rs_Mail)>0) ? '<div class="span_envelope">'.mysqli_num_rows($rs_Mail).'</div>' : "";



                echo '
                        <li style="width:5rem;"><a href="admin_finequery.php?PageTitle=Ricerca/Avanzata"><span class="fa fa-search"></span>&nbsp;</a></li>
                        <li style="width:5rem;"><a href="mgmt_mail.php?PageTitle=Gestione/Posta"><span class="fa fa-envelope-o"></span>&nbsp;
                        '.$str_Mail.'   
                           </a></li>
                        ';
            }
            ?>


			<li class="dropdown">

				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
					<span class="fa fa-university"></span><?= '&nbsp;'.substr($_SESSION['citytitle'],0,8) ?><span class="caret"></span>
				</a>
				<ul class="dropdown-menu FN_Menu" >
					<?php
                    $row ='';
					$cities = $rs->SelectQuery("SELECT CityId, CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserId=".$_SESSION['userid']." AND MainMenuId=".MENU_ID. " GROUP BY CityId, CityTitle;");

					while($city = mysqli_fetch_array($cities)){
						if($city['CityId']!=$_SESSION['cityid']) $row .= '<li id="sub-menu_r"><a href="index.php?cityid='.$city['CityId'].'&citytitle='.$city['CityTitle'].'">'.substr($city['CityId']." ".$city['CityTitle'],0,15).'</a></li>';
					}
					echo $row;
					?>
				</ul>
			</li>
			<li class="dropdown">

				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
					<span class="glyphicon glyphicon-calendar"></span><?= '&nbsp;'.$_SESSION['year'] ?><span class="caret"></span>
				</a>
				<ul class="dropdown-menu FN_Menu" >
					<?php
					$rs= new CLS_DB();
					$years = $rs->SelectQuery("SELECT CityYear FROM ".MAIN_DB.".V_UserCity WHERE CityId='".$_SESSION['cityid']."' AND UserId=".$_SESSION['userid']." ORDER BY CityYear DESC;");
					$row ='';
					while($year = mysqli_fetch_array($years)){
						if($year['CityYear']!=$_SESSION['year']) $row .= '<li id="sub-menu_r"><a href="index.php?year='.$year['CityYear'].'">'.$year['CityYear'].'</a></li>';
					}
					echo $row;
					?>
				</ul>
			</li>
            <li class="dropdown">

                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <span class="glyphicon glyphicon-user tooltip-r" data-toggle="tooltip" data-placement="left" <?= $str_UserClass ?>></span> &nbsp;Account<span class="caret"></span>
                </a>
                <ul class="dropdown-menu FN_Menu" >
                    <li id="sub-menu_r"><a href="#"><span class="fa fa-cogs"></span> Account</a></li>
                    &nbsp;
                    <li id="sub-menu_r"><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
                </ul>
            </li>






		</ul>
	</div><!-- /.navbar-collapse -->

</nav>
<?php
$aUserButton = array();



$UserPages = $rs->Select(MAIN_DB.".V_UserPage", "MainMenuId=".MENU_ID." AND UserId=".$_SESSION['userid']." AND LinkPage='".$str_MenuPage."';");



while($UserPage = mysqli_fetch_array($UserPages)){
	$aUserButton[] = $UserPage['Title'];

}
$aLan = unserialize(IMG_LANGUAGE);


/*
if(!$b_FindPage){
    $_SESSION['Message'] = "Pagina non accessibile per l'utente ".$_SESSION['username'];
    echo '<script>window.location.href="select_customer.php";</script>';
    DIE;
}
*/


require("page/title.php");
?>
<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header" style="background-color: rgb(99, 151, 226); color: white">
                <button type="button" class="close" data-dismiss="modal" style="background-color: white">&times;</button>
                <h4 class="modal-title"><center>Crea nuovo Ticket</center></h4>
            </div>
            <div class="modal-body" style="background-color: rgba(132, 212, 251, 0.78);">
                <form id="data" method="post" enctype="multipart/form-data">
                    <label for="name" class="labelcol">Name:  <i class="fas fa-pencil-alt"></i>
                        <small style="color: red; font-size: 9px; "> *</small>
                    </label>
                    <input type="text" class='form-control' name="title" id="title" required>
                    <span style="color: red; display:none" id="validate_name">Name is required!</span><br>
                    <label for="SubMenu" class="labelcol">SubMenu:
                        <small style="color: red; font-size: 9px; "> *</small>
                    </label>
                    <?php
                    $menus = $rs->Select(MAIN_DB.".ApplicationSubMenu");
                    $pagetitle = @$_REQUEST["PageTitle"];
                    $submenu = strpos($pagetitle, '/');
                    $submenuval = substr($pagetitle, $submenu+1, 100);
                    $submenuval = urldecode($submenuval);
                    echo '<select class="form-control" name="submenu" id="submenu" required>';
                    foreach ($menus as $key) {
                        $selectedText = $submenuval === $key['Title'] ? 'selected': '';

                        echo '<option '.$selectedText.' value='.$key['Id'].'>'.$key['Title'].'</option>';
                    }
                    echo '</select>';
                    ?>
                    <span style="color: red; display:none" id="validate_submenu">Please choose an option!</span><br>
                    <label for="Priority" class="labelcol">Priority:
                        <small style="color: red; font-size: 9px; "> *</small>
                    </label>
                    <select id="priority" name="priority" class="form-control" required>
                        <option selected="selected" value="Choose">Choose...</option>
                        <option value="1">bassa</option>
                        <option value="2">media</option>
                        <option value="3">alta</option>
                    </select>
                    <span style="color: red; display:none" id="validate_priority">Please choose an option!</span><br>
                    <label for="Type" class="labelcol">Type:
                        <small style="color: red; font-size: 9px; "> *</small>
                    </label>
                    <select class="form-control" name="type" id="type" required>
                        <option selected="selected" value="Choose">Choose...</option>
                        <option value="1">programmazione</option>
                        <option value="2">elaborazione</option>
                        <option value="3">altro </option>
                    </select>
                    <input type="hidden" name="_token" id="token" value="9wEuMJEWKRoBdd6a7flD0isnefY6wtN2JBAHEQco">
                    <span style="color: red; display:none" id="validate_type">Please choose an option!</span><br>
                    <label for="Description" class="labelcol">Description: <i class="fas fa-pencil-alt"></i>
                        <small style="color: red; font-size: 9px; "> *</small>
                    </label>
                    <textarea class="form-control" rows="5" name="note" id="note" required></textarea>
                    <span style="color: red; display:none" id="validate_description">This field is required!</span><br><br>
                    <input type="file" name="file" id="files">
                    <small>Image Upload is optional.</small><br><br>
                    <button class="btn btn-primary">Crea Ticket</button>
                    <button type="button" class="btn btn-default" id="close" data-dismiss="modal">Cancela</button>
                    <br><br>
            </div>
            </form>
        </div>

    </div>
</div>

</div>

<script>
    $('.apriticket').on('click',function(){
        $('#myModal').modal('show');
    });
    $("#title").change(function(){
        var title = $("#title").val();
        if(title == '') {
            $("#validate_name").css("display", "");
        } else {
            $("#validate_name").css("display", "none");
        }
    });

    $("#submenu").change(function(){
        var submenu = $("#submenu").val();
        if(submenu == 'Choose') {
            $("#validate_submenu").css("display", "");
        } else {
            $("#validate_submenu").css("display", "none");
        }
    });

    $("#priority").change(function(){
        var priority = $("#priority").val();
        if(priority == 'Choose') {
            $("#validate_priority").css("display", "");
        } else {
            $("#validate_priority").css("display", "none");
        }
    });

    $("#type").change(function(){
        var type = $("#type").val();
        if(type == 'Choose') {
            $("#validate_type").css("display", "");
        } else {
            $("#validate_type").css("display", "none");
        }
    });

    $("#note").change(function(){
        var note = $("#note").val();
        if(note == '') {
            $("#validate_description").css("display", "");
        } else {
            $("#validate_description").css("display", "none");
        }
    });

    $("form#data").submit(function(e){
        e.preventDefault();
        var formData = new FormData(this);
        var title = $("#title").val();
        var submenu = $("#submenu").val();
        var priority = $("#priority").val();
        var type = $("#type").val();
        var note = $("#note").val();
        if(title == ''){
            $("#validate_name").css("display", "");
        } else if(submenu == 'Choose') {
            $("#validate_submenu").css("display", "");
        } else if(priority == 'Choose') {
            $("#validate_priority").css("display", "");
        } else if(type == 'Choose') {
            $("#validate_type").css("display", "");
        } else if(note == '') {
            $("#validate_description").css("display", "");
        } else
        {
            $.ajax({
                url: 'inc/creaticket.php',
                type: 'POST',
                data: formData,
                success: function(data){
                    alert('Il ticket e stato aperto');
                    $("#close").trigger("click");
                    $("#title").val(" ");
                    $("#submenu option:selected").val();
                    $("#priority").val(" ");
                    $("#type").val(" ");
                    $("#note").val(" ");
                    $("#token").val(" ");
                    $("#files").val(" ");
                },
                error: function(){
                    if(title == '') {
                        $("#validate_name").css("display", "");
                    }
                    if(submenu == "Choose") {
                        $("#validate_submenu").css("display", "");
                    }
                    if(priority == "Choose"){
                        $("#validate_priority").css("display", "");
                    }
                    if(type == "Choose"){
                        $("#validate_type").css("display", "");
                    }
                    if(note == ''){
                        $("#validate_description").css("display", "");
                    }
                },
                cache: false,
                contentType: false,
                processData: false
            });
        }
    });
</script>
