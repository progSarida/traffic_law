<?php
$menu = $obj->Select( "MainMenu", "Disabled=0" ); //Getting all menu items
$menuIcons = array(1=>'icon-home4',2=>'icon-copy',3=>'icon-copy',4=>'icon-copy',5=>'icon-copy',6=>'icon-copy',7=>'icon-copy',8=>'icon-copy');
?>
<!-- Main sidebar -->
	<div class="sidebar sidebar-main <?php echo $hideClass; ?>">
		<div class="sidebar-content">

			<!-- User menu -->
			<div class="sidebar-user">
				<div class="category-content">
					<div class="media">
						<a href="#" class="media-left"><img src="assets/images/placeholder.jpg" class="img-circle img-sm" alt=""></a>
						<div class="media-body">
							<span class="media-heading text-semibold"><?php echo $_SESSION['authName']; ?></span>
						</div>
					</div>
				</div>
			</div>
			<!-- /user menu -->

			<!-- Main navigation -->
			<div class="sidebar-category sidebar-category-visible">
				<div class="category-content no-padding">
					<ul class="navigation navigation-main navigation-accordion">
						<li class="navigation-header"><span>PAGES</span> <i class="icon-menu" title="Main pages"></i></li>
						<li class="<?php if($pageUrl[2] == "mainMenuSetting.php" ){ ?> active <?php }?>">
							<a href="mainMenuSetting.php">
								<i class="icon-copy"></i> <span>Main Menu Setting</span>
							</a>
						</li>
						<li class="<?php if($pageUrl[2] == "applicationMenuSetting.php" ){ ?> active <?php }?>">
							<a href="applicationMenuSetting.php">
								<i class="icon-copy"></i> <span>Application Menu Setting</span>
							</a>
						</li>
						<li class="<?php if($pageUrl[2] == "applicationSubMenuSetting.php" ){ ?> active <?php }?>">
							<a href="applicationSubMenuSetting.php">
								<i class="icon-copy"></i> <span>Application SubMenu Setting</span>
							</a>
						</li>
						<li class="<?php if($pageUrl[2] == "applicationPageSetting.php" ){ ?> active <?php }?>">
							<a href="applicationPageSetting.php">
								<i class="icon-copy"></i> <span>Application Page Setting</span>
							</a>
						</li>
						<li class="navigation-header"><span>USERS</span> <i class="icon-menu" title="Main pages"></i></li>
						<li class="<?php if($pageUrl[2] == "userSetting.php" ){ ?> active <?php }?>">
							<a href="userSetting.php">
								<i class="icon-copy"></i> <span>Users Setting</span>
							</a>
						</li>
						<!-- For sub users this will be applied later -->
						<!-- <?php
						$count = 0;
						foreach( $menu as $eachMenuItem ){
							$count++;
						?>
							<li <?php if($count == 1) {?>class="active"<?php }?> >
								<a href="<?php echo PATH . $eachMenuItem['Path']; ?>">
									<i class="<?php echo $menuIcons[$count]; ?>"></i> <span><?php echo $eachMenuItem['Description']; ?></span>
								</a>
							</li>
						<?php
						}
						?> -->
					</ul>
				</div>
			</div>
			<!-- /main navigation -->
		</div>
	</div>
<!-- /main sidebar -->

<!-- Main content -->
	<div class="content-wrapper">
		<!-- Content area -->
		<div class="content">