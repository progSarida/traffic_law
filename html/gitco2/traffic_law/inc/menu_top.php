
<body>
<div id="LoadingModal" data-backdrop="static" class="modal modal-center fade" role="dialog">
    <div class="modal-dialog">
        <div style="overflow:hidden; color:white;">
            <div class="modal-body text-center">
            	<div id="LoadingSpinner" style="width: 150px;height: 150px;display: inline-block;"></div>
				<h3 class="modal-text">Caricamento in corso...</h3>
            </div>
        </div>
                    
    </div>
</div>

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
	<div class="collapse navbar-collapse FN_Menu" id="navbar-collapse-1">
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

            //$menus = $rs->Select(MAIN_DB.".V_UserApplication", "UserId=".$_SESSION['userid']." AND MainMenuId=".MENU_ID);
            //TODO DA SPOSTARE O AGGIORNARE V_UserApplication
            $menus = $rs->SelectQuery("SELECT
                am.Title AS TitleMenu,
                asm.Title AS TitleSubMenu,
                asm.LinkPage AS LinkPage,
                ua.MainMenuId AS MainMenuId,
                ua.UserId AS UserId
            FROM
                ".MAIN_DB.".ApplicationMenu am
            JOIN ".MAIN_DB.".ApplicationSubMenu asm ON
                asm.ApplicationMenuId = am.Id
            JOIN ".MAIN_DB.".UserApplication ua ON
                asm.Id = ua.ApplicationSubMenuId
            JOIN ".MAIN_DB.".ApplicationRuleSubMenu arsm ON
                arsm.ApplicationSubMenuId = asm.Id AND arsm.RuleTypeId={$_SESSION['ruletypeid']}
            WHERE
                am.Disabled = 0
                AND asm.Disabled = 0
                AND ua.MainMenuId = ".MENU_ID."
                AND ua.UserId = ".$_SESSION['userid']."
            ORDER BY
                am.MenuOrder,
                asm.SubMenuOrder");
            
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
                if(strpos($menu['LinkPage'],"?"))
                    $row .= '<li id="sub-menu_top"><a href="'.$menu['LinkPage'].'&PageTitle='.$menu['TitleMenu'].'/'.$menu['TitleSubMenu'].'">'.$menu['TitleSubMenu'].'</a></li>';
                else
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
            $rs_Mail = $rs->Select('Mail', "UserId=".$_SESSION['userid']);

            $n_Mail = $n_Mail_Unread = 0;

            while ($app_mail = mysqli_fetch_assoc($rs_Mail)){
                $app_mail['ReadStatus'] > 0 ?: $n_Mail_Unread++;
                $n_Mail++;
            }

                echo '
                        <li style="width:auto;"><a href="admin_finequery.php?PageTitle=Ricerca/Avanzata"><span class="fa fa-search"></span>&nbsp;</a></li>
                        ';


                echo '
                        <li class="dropdown" style="width:auto;">

                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                <span class="tooltip-r" data-html="true" data-container="body" data-toggle="tooltip" data-placement="bottom" title="Registro procedure">
                                    <i class="fa fa-envelope-o"></i><span class="caret"></span>&nbsp;<span class="badge menu-badge">'. $n_Mail_Unread .'</span>
                                </span>
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

			<?php
			    $app_event_error_count = $app_event_warning_count = $app_event_success_count = 0;
    			$app_a_EventsColours = array(
    			    'INFO' => array('Class' => '', 'Text' => 'Informazione'),
    			    'SUCCESS' => array('Class' => 'table_caption_success', 'Text' => 'Successo'),
    			    'WARNING' => array('Class' => 'table_caption_warning', 'Text' => 'Avviso'),
    			    'ERROR' => array('Class' => 'table_caption_error', 'Text' => 'Errore'),
    			);
                $app_events = $rs->Select('Events', "CityId = '{$_SESSION['cityid']}' AND DATE(EventDate) > DATE(NOW() - INTERVAL 5 DAY)", 'EventDate DESC');
                while($app_event = mysqli_fetch_array($app_events)){
                    if($app_event['IsRead'] == 'N'){
                        switch($app_event['Severity']){
                            case 'SUCCESS': $app_event_success_count++; break;
                            case 'WARNING': $app_event_warning_count++; break;
                            case 'ERROR': $app_event_error_count++; break;
                        }
                    }
                }
                mysqli_data_seek($app_events, 0);
            ?>
			<li id="events_menu" class="dropdown" style="width:auto;">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
					<span class="tooltip-r" data-html="true" data-container="body" data-toggle="tooltip" data-placement="bottom" title="Notifiche eventi">
						<i class="fa fa-bell"></i><span class="caret"></span>
    					<span data-badgecounter="Error" class="badge menu-badge <?= $app_event_error_count > 0 ? 'ripple-danger' : ''; ?>">
    						<i class="fa fa-times-circle" style="color:crimson;"></i> <span><?= $app_event_error_count; ?></span>
    					</span>&nbsp;
    					<span data-badgecounter="Warning" class="badge menu-badge">
    						<i class="fa fa-exclamation-circle" style="color:orange;"></i> <span><?= $app_event_warning_count; ?></span>
    					</span>&nbsp;
    					<span data-badgecounter="Success" class="badge menu-badge">
    						<i class="fa fa-check-circle" style="color:green;"></i> <span><?= $app_event_success_count; ?></span>
    					</span>
					</span>
				</a>
				<ul class="dropdown-menu FN_Menu alert" style="min-width:75rem; max-width:100rem; color:white;">
					<div class="table_label_H col-sm-12">EVENTI PROCESSI AUTOMATICI (ultimi 5 gg)</div>
					<div class="clean_row HSpace4"></div>
                	<div class="table_label_H col-sm-2">Processo</div>
                    <div class="table_label_H col-sm-1">Esito</div>
                    <div class="table_label_H col-sm-5">Messaggio</div>
                    <div class="table_label_H col-sm-2">Data/Ora</div>
                    <div class="table_label_H col-sm-2">Segna come letto</div>
				    <div class="col-sm-12" style="height:40rem; overflow:scroll;">
    				    <?php if(mysqli_num_rows($app_events) > 0): ?>
        					<?php while($app_event = mysqli_fetch_array($app_events)): ?>
                                <li>
                                    <div class="col-sm-12 alert <?= $app_a_EventsColours[$app_event['Severity']]['Class']; ?>" style="margin:0;">
                                        <div class="col-sm-2">
                                     		<?= $app_event['Name']; ?>
                                        </div>
                                        <div class="col-sm-1">
                                    		<?= $app_a_EventsColours[$app_event['Severity']]['Text']; ?>
                                        </div>
                                        <div class="col-sm-5" style="word-wrap: break-word;">
                                        	<?= $app_event['Message']; ?>
                                        </div>
                                        <div class="col-sm-2 text-center">
                                        	<?= DateTimeOutDB($app_event['EventDate']); ?>
                                        </div>
                                        <div class="col-sm-2 text-center">
                                        	<?php if($app_event['IsRead'] == 'N'): ?>
                                            	<button data-eventid="<?= $app_event['EventId']; ?>" type="button" class="btn btn-info"><i class="fa fa-check-square"></i></button>
                                        	<?php endif; ?>
                                        </div>
                                    </div>
                                </li>
        					<?php endwhile; ?>
						<?php else: ?>
							<li>
								<div class="col-sm-12 alert-info text-center">Nessun evento da mostrare.</div>
							</li>
						<?php endif; ?>
					</div>
				</ul>
			</li>
			<li class="dropdown" style="width:auto;">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
					<span class="tooltip-r" data-html="true" data-container="body" data-toggle="tooltip" data-placement="bottom" title="<?= "<img src='".$_SESSION['blazon']."' style='height:4rem;'> ".$_SESSION['citytitle']." (".$_SESSION['cityid'].")" ?>">
						<i class="fa fa-university"></i><?= '&nbsp;'.$_SESSION['citytitle'] ?><span class="caret"></span>
					</span>
				</a>
				<ul class="dropdown-menu FN_Menu" style="width:20rem">
					<?php
                    $row ='';
					$cities = $rs->SelectQuery("SELECT CityId, CityTitle FROM ".MAIN_DB.".V_UserCity WHERE UserId=".$_SESSION['userid']." AND MainMenuId=".MENU_ID. " GROUP BY CityId, CityTitle ORDER BY CityTitle ASC");

					while($city = mysqli_fetch_array($cities)){
						if($city['CityId']!=$_SESSION['cityid']) $row .= '<li id="sub-menu_top_r"><a href="index.php?cityid='.$city['CityId'].'&citytitle='.$city['CityTitle'].'">'.substr($city['CityTitle']." ".$city['CityId'],0,30).'</a></li>';
					}
					echo $row;
					?>
				</ul>
			</li>
			<li class="dropdown" style="width:auto;">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
					<i class="fas fa-book"></i> <?= '&nbsp;'.$_SESSION['ruletypetitle'] ?><span class="caret"></span>
				</a>
				<ul class="dropdown-menu FN_Menu" >
					<?php
					$menu_ruleTypes = $rs->getResults($rs->Select("RuleType", "CityId='{$_SESSION['cityid']}' AND Id != {$_SESSION['ruletypeid']}"));
					?>
					<?php foreach ($menu_ruleTypes as $menu_ruleType):?>
						<li id="sub-menu_top_r"><a href="index.php?ruletypeid=<?= $menu_ruleType['Id'] ?>&ruletypetitle=<?= $menu_ruleType['Title']; ?>"><?= $menu_ruleType['Title']; ?></a></li>
					<?php endforeach; ?>
				</ul>
			</li>
			<li class="dropdown" style="width:auto;">
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
            <li class="dropdown" style="width:auto;">

                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <span class="glyphicon glyphicon-user tooltip-r" data-toggle="tooltip" data-placement="left" <?= $str_UserClass ?>></span> &nbsp;<?= $_SESSION['username']; ?><span class="caret"></span>
                </a>
                <ul class="dropdown-menu FN_Menu" >
                    <li id="sub-menu_top_r"><a href="#"><span class="fa fa-cogs"></span> Account</a></li>
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


require("page/title_top.php");
