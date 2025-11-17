
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
		<ul class="nav navbar-nav" id="menu_top">

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
				$row .= '<li id="sub-menu_top"><a href="'.$menu['LinkPage'].'?PageTitle='.$menu['TitleMenu'].'/'.$menu['TitleSubMenu'].'">'.$menu['TitleSubMenu'].'</a></li>';
				if($str_MenuPage==$menu['LinkPage']){
                    $b_FindPage=true;
                }



			}


			$row .= '




	
	                    </ul>
					</li>';
			echo $row;

			?>
			
		</ul>

		<ul class="nav navbar-nav navbar-right" id="menu_top_r">
            <?php

            if($_SESSION['userlevel']>=3){
            $rs_Mail = $rs->Select('Mail', "ReadStatus=0 AND UserId=".$_SESSION['userid']);

                $n_Mail = (mysqli_num_rows($rs_Mail)>0) ? mysqli_num_rows($rs_Mail) : '';


                $str_Mail = ($n_Mail>0) ? '<div class="span_envelope">'. $n_Mail .'</div>' : '';

                $rs_Mail = $rs->Select('Mail', "UserId=".$_SESSION['userid']);
                $n_Mail = (mysqli_num_rows($rs_Mail)>0) ? mysqli_num_rows($rs_Mail) : '';


                echo '
                        <li style="width:5rem;"><a href="admin_finequery.php?PageTitle=Ricerca/Avanzata"><span class="fa fa-search"></span>&nbsp;</a></li>
                        ';


                echo '
                        <li class="dropdown" style="margin-top:0.4rem;">

                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <span class="fa fa-envelope-o"></span><span class="caret"></span>'.$str_Mail.'
                            </a>
                            <ul class="dropdown-menu FN_Menu" >
                                <li id="sub-menu_top_r">
                                    <a href="#">
                                        <span title="Criticità" class="fa fa-envelope-o btn-danger"></span>
                                    </a>
                                </li>
                                &nbsp;
                                <li id="sub-menu_top_r">
                                    <a href="#">
                                        <span title="Avvisi" class="fa fa-envelope-o btn-warning"></span>
                                    </a>
                                </li>
                                &nbsp;
                                <li id="sub-menu_top_r">
                                    <a href="mgmt_mail.php?PageTitle=Gestione/Posta">
                                        <span title="Notifiche" class="fa fa-envelope-o btn-success"></span> '.$n_Mail.'
                                    </a>
                                </li> 
                            </ul>
                        </li>
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
						if($city['CityId']!=$_SESSION['cityid']) $row .= '<li id="sub-menu_top_r"><a href="index.php?cityid='.$city['CityId'].'&citytitle='.$city['CityTitle'].'">'.substr($city['CityTitle']." ".$city['CityId'],0,15).'</a></li>';
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
						if($year['CityYear']!=$_SESSION['year']) $row .= '<li id="sub-menu_top_r"><a href="index.php?year='.$year['CityYear'].'">'.$year['CityYear'].'</a></li>';
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
                    <li id="sub-menu_top_r"><a href="#"><span class="fa fa-cogs"></span> Account</a></li>
                    &nbsp;
                    <li id="sub-menu_top_r"><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>


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




$a_ImgLanguage  = unserialize(IMG_LANGUAGE);
$a_Language     = unserialize(LANGUAGE);

/*
if(!$b_FindPage){
    $_SESSION['Message'] = "Pagina non accessibile per l'utente ".$_SESSION['username'];
    echo '<script>window.location.href="select_customer.php";</script>';
    DIE;
}
*/


require("page/title_top_forprint.php");
