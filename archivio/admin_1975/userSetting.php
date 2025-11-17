<?php
include("common/header.php");
include("common/menu.php");
if( !isset( $_SESSION['authKey'] ) ){
	echo "<script>window.location='index.php'</script>";
}

$fieldValue = array( 0=>'', 1=>'', 2=>'', 3=>date("Y/m/d"), 4=>'' ); //Setting field value to null in case of insert data
$submitBtnType = "addUser";
$submitBtnName = "add";


$allItems = $obj->Select( "User" ); //Getting all Application Page items
$allItemCount = count( $allItems ); //Counting total number of items in the table
$totalPages = ceil( $allItemCount / 10 );
$showItemFrom = 0; //Item show from row
$showItemTo = 10; //Item show to row
if( isset($_GET['page']) ){ //If page number found from URL
	$showItemFrom = ( $_GET['page'] - 1 ) * 10;
}

$limit = $showItemFrom . "," . $showItemTo; //Setting page limit
$showItems = $obj->Select( "User" , $whereClause=null, $orderBy=null, $limit ); //Getting all Application Page items under limit

//-------------------------For Edit Portion-------------------------//
if( isset( $_GET['edit'] ) ){
	$editItem = $obj->Select( "User", "Id=".$_GET['edit'] ); //For editting a particular row
	//Putting values for update data
	$fieldValue = array(
						0=>$editItem[0]['UserName'],
						1=>$editItem[0]['Password'],
						2=>$editItem[0]['Mail'],
						3=>$editItem[0]['LoginDate'],
						4=>$editItem[0]['Id']
					);
	$submitBtnType = "updateUser";
	$submitBtnName = "update";
}
//-------------------------------------------------------------------//

//For User Page Data
$allItemsUserPage = $obj->Select( "UserPage" ); //Getting all Application Page items
$allItemCountUserPage = count( $allItemsUserPage ); //Counting total number of items in the table
$totalPagesUserPage = ceil( $allItemCountUserPage / 10 );
$showItemFromUserPage = 0; //Item show from row
$showItemToUserPage = 10; //Item show to row
if( isset($_GET['pageUserPage']) ){ //If page number found from URL
	$showItemFromUserPage = ( $_GET['pageUserPage'] - 1 ) * 10;
}

$limitUserPage = $showItemFromUserPage . "," . $showItemToUserPage; //Setting page limit
$showItemsUserPage = $obj->Select( "UserPage" , $whereClause=null, $orderBy=null, $limitUserPage ); //Getting all User Page items under limit
//------------------------//

if( isset($_SESSION['success']) ){ //After action message
	?>
	<div class="alert alert-success no-border"><?php echo $_SESSION['success'];?></div>
	<?php
	unset( $_SESSION['success'] );
}
?>
<div class="row">
	<div class="col-md-6">
		<form method="post" action="class/lib.php" class="form-horizontal">
			<div class="panel panel-flat">
				<div class="panel-heading">
					<h5 class="panel-title">Add</h5>
					<div class="heading-elements">
						<ul class="icons-list">
							<li><a data-action="collapse"></a></li>
						</ul>
					</div>
				</div>

				<div class="panel-body">
					<div class="form-group">
						<label class="col-lg-3 control-label">User:</label>
						<div class="col-lg-9">
							<input type="text" name="user[]" class="form-control" value="<?php echo $fieldValue[0]; ?>" placeholder="Enter User" required >
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-3 control-label">Password:</label>
						<div class="col-lg-9">
							<input type="password" name="user[]" class="form-control" value="<?php echo $fieldValue[1]; ?>" placeholder="Enter Password" required >
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-3 control-label">Mail:</label>
						<div class="col-lg-9">
							<input type="text" name="user[]" class="form-control" value="<?php echo $fieldValue[2]; ?>" placeholder="Enter Mail" required >
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-3 control-label">Login Date:</label>
						<div class="col-lg-9">
							<input type="text" name="user[]" class="form-control" value="<?php echo ($fieldValue[3]!='0000-00-00')?date( 'd/m/Y', strtotime( $fieldValue[3] ) ):"Not Logged In Yet"; ?>" placeholder="Enter Login Date" disabled >
						</div>
					</div>

					<div class="text-right">
						<input type="hidden" name="user[id]" value="<?php echo $fieldValue[4]; ?>">
						<button type="submit" name="<?php echo $submitBtnType; ?>" class="btn btn-primary"><?php echo strtoupper( $submitBtnName ); ?> <i class="icon-arrow-right14 position-right"></i></button>
					</div>
				</div>
			</div>
		</form>
		<?php
		if(isset( $_GET['view'] ) ){
			$viewItem = $obj->Select( "User", "Id=".$_GET['view'] ); //For viewing a particular user details
			$userPagePower = $obj->Select( "UserPage", "UserId=" . $_GET['view'] ); //Getting Users power details over pages
		?>
			<div class="panel panel-flat">
				<div class="panel-heading">
					<h5 class="panel-title">View</h5>
					<div class="heading-elements">
						<ul class="icons-list">
							<li><a data-action="collapse"></a></li>
						</ul>
					</div>
				</div>

				<div class="panel-body">
					<div class="form-group">
						<div class="col-lg-1 alert alert-primary no-border">
							Name:
						</div>
						<div class="col-lg-5 alert alert-primary no-border">
							<?php echo $viewItem[0]['UserName']; ?><!-- User Name -->
						</div>
						<div class="col-lg-1 alert alert-primary no-border">
							Email:
						</div>
						<div class="col-lg-5 alert alert-primary no-border">
							<?php echo ($viewItem[0]['Mail'])?$viewItem[0]['Mail']:"No email id exists"; ?><!-- Email Id -->
						</div>
					</div>

					<div class="form-group">
						<div class="col-lg-3 alert alert-success no-border">
							Last Logged In:
						</div>
						<div class="col-lg-9 alert alert-success no-border">
							<?php echo ($viewItem[0]['LoginDate']!="0000-00-00")?date("F j, Y", strtotime( $viewItem[0]['LoginDate'] )):"Not logged in yet"; ?>
							<!-- Last Logged in date -->
						</div>
					</div>

					<div class="form-group">
						<div class="col-lg-12 alert alert-warning no-border">
							<h5>
								User's Powers over pages:
							</h5>
							<?php
							if( !empty( $userPagePower ) ){
								foreach( $userPagePower as $power ){
									$menuName = $obj->Select("MainMenu", "Id=" . $power['MainMenuId'] );
									$submenuName = $obj->Select("ApplicationSubMenu", "Id=" . $power['ApplicationSubMenuId'] );
									$pageName = $obj->Select("ApplicationPage", "Id=" . $power['ApplicationPageId'] );
									?>
									<div class="row">
										<div class="col-md-1">
											Menu:
										</div>
										<div class="col-md-3">
											<?php echo $menuName[0]['Description']; ?>
										</div>
										<div class="col-md-2">
											Sub Menu:
										</div>
										<div class="col-md-3">
											<?php echo $submenuName[0]['Title']; ?>
										</div>
										<div class="col-md-1">
											Power:
										</div>
										<div class="col-md-2">
											<?php echo $pageName[0]['Title']; ?>
										</div>
									</div>
								<?php
								}
							}else{
							?>
								<h5>No result found!</h5>
							<?php
							}
							?>
						</div>
					</div>
				</div>
			</div>
		<?php
		}
		?>
	</div>
	<div class="col-md-6">
		<div class="panel panel-flat">
			<form method="post" action="class/lib.php" class="form-horizontal">
				<div class="panel-heading">
					<h5 class="panel-title">List</h5>
					<button type="submit" name="assign" class="btn btn-primary"><i class="icon-file-eye"></i></button>
					<div class="heading-elements">
						<ul class="icons-list">
							<li><a data-action="collapse"></a></li>
						</ul>
					</div>
				</div>
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th>User</th>
								<th>Mail</th>
								<th>Login Date</th>
								<th class="text-center">Actions</th>
							</tr>
						</thead>
						<tbody>
						<?php
						$checkBoxCount = 0; //Setting the counter to count number of rows generated
						foreach( $showItems as $eachMenuItem ){
							$checkBoxCount++;
							//Edit Link with current page number ( if Page number is found )
							$link = isset( $_GET['page'] )?$eachMenuItem['Id'] . "&page=" . $_GET['page']:$eachMenuItem['Id'];
							?>
							<tr>
								<td>
									<input type="checkbox" name="assignUser[<?php echo $eachMenuItem['Id']; ?>]" class="users" id="<?php echo $checkBoxCount; ?>">
								</td>
								<td>
									<?php echo $eachMenuItem['UserName']; ?>
								</td>
								<td>
									<?php echo $eachMenuItem['Mail']; ?>
								</td>
								<td>
									<?php echo ($eachMenuItem['LoginDate']!="0000-00-00")?
												date( "d/m/Y", strtotime( $eachMenuItem['LoginDate'] ) ):
												"Not Logged In Yet";
									?>
								</td>
								<td class="text-center">
									<ul class="icons-list">
										<li class="dropdown">
											<a href="#" class="dropdown-toggle" data-toggle="dropdown">
												<i class="icon-menu9"></i>
											</a>
											<ul class="dropdown-menu dropdown-menu-right">
												<li>
													<a href="<?php echo $_SERVER['PHP_SELF'] ."?view=" . $link; ?>">
														<i class="icon-eye"></i> View
													</a>
												</li>
												<li>
													<a href="<?php echo $_SERVER['PHP_SELF'] ."?edit=" . $link; ?>">
														<i class="icon-pencil3"></i> Edit
													</a>
												</li>
												<li>
													<a href="<?php echo "class/lib.php?deleteUser=" . $eachMenuItem['Id']; ?>" onclick="return confirm('Sure To Delete?');">
														<i class="icon-trash-alt"></i> Delete
													</a>
												</li>
											</ul>
										</li>
									</ul>
								</td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
					<input type="hidden" id="totalRows" value="<?php echo $checkBoxCount; ?>">
				</div>
			</form>
			<div class="datatable-footer">
				<div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
					<span>
						<?php
						for( $p=1; $p<=$totalPages; $p++ ){
							//Page number with edit link ( if edit link found )
							$page = isset( $_GET['edit'] )?$p . "&edit=" . $_GET['edit']:$p;
						?>
							<a class="paginate_button 
									<?php if( (isset($_GET['page']) && $p==$_GET['page'] ) || (!isset($_GET['page']) && $p==1 ) ){ ?> 
										current 
									<?php }?>" 
								href="<?php echo $_SERVER['PHP_SELF'] . '?page=' . $page; ?>" 
							>
								<?php echo $p; ?>
							</a>
						<?php
						}
						?>
					</span>
					<span>&nbsp;&nbsp;</span>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
if( isset( $_GET['users'] ) ){ //If user(s) have been chosen for access filteration
	$allUsers = json_decode( base64_decode($_GET['users']) ); //Getting user(s) details
}else if( isset( $_GET['uid'] ) ){
	$allUsers = array ( $_GET['uid']=>"on" ); //Getting user(s) details
}
if( !empty( $allUsers ) ){
	$name = ""; //Setting name variable to display
	$user_id = ""; //Setting user Id variable
	?>
	<form method="post" id="accessFilterForm" action="class/lib.php">
		<div class="row">
			<div class="col-md-2">
				<?php
				foreach( $allUsers as $userId=>$status ){
					if( $userId != 0 ){
						$userDetails = $obj->Select( "User" , $whereClause="Id=" . $userId, $orderBy=null, $limit=null );
						//Setting name to display
						$name .= '<i class="icon-check text-primary"></i>' . ucwords( strtolower($userDetails[0]['UserName']) ) . '<br/>';
						$user_id = $userId;
					}
				}
				$allMenuItems = $obj->Select( "MainMenu" ); //Getting all main menu items for dropdown
				$allPageItems = $obj->Select( "ApplicationPage" ); //Getting all application page items for dropdown
				?>
				<label class="col-lg-12 control-label">
					<h5 class="panel-title">Name</h5>
					<?php echo $name; ?>
					<!--Setting encoded user id-->
					<input type="hidden" name="filter[]" id="userId" value="<?php echo $user_id; ?>">
				</label>		
			</div>
			<div class="col-md-3">
				<label class="col-lg-12 control-label">Main Menu</label>
				<select name="filter[]" id="mainMenu" class="form-control">
					<option value="0">Choose Main Menu</option>
					<?php
					foreach( $allMenuItems as $eachMenuItem ) {
						?>
						<option value="<?php echo $eachMenuItem['Id']; ?>"
							<?php echo (isset($_GET['mid']) && $eachMenuItem['Id']==$_GET['mid'])?"selected":""; ?> 
						>
							<?php echo $eachMenuItem['Description']; ?>
						</option>
						<?php
					}
					?>
				</select>	
			</div>
			<div class="col-md-3">
				<div  id="applicationMenuResult">
					<!-- Resultant div for showing application menu by ajax -->
					<!-- For Access control Edit -->
					<?php
					if( isset( $_GET['uid'] ) ){
						//Getting application menu from main menu id - taking from url
						$applicationMenuItems = $obj->Select( "ApplicationMenu", "MainMenuId=" . $_GET['mid'] );
						//Getting submenu id for selecting option - taking from sub menu id from url
						$applicationMenuId = $obj->Select( "ApplicationSubMenu", "Id=" . $_GET['smid'] );
					?>
					<label class="col-lg-7 control-label">Application Menu</label>
					<select name="filter[]" id="applicationMenu" class="form-control">
						<option value="0">Choose Application Menu</option>
						<?php
						foreach( $applicationMenuItems as $eachapplicationMenuItems ){
							?>
							<option value="<?php echo $eachapplicationMenuItems['Id']; ?>"
								<?php echo (isset($applicationMenuId[0]['ApplicationMenuId']) && $eachapplicationMenuItems['Id']==$applicationMenuId[0]['ApplicationMenuId'])?"selected":""; ?>
							>
								<?php echo $eachapplicationMenuItems['Title']; ?>
							</option>
							<?php
						}
						?>
					</select>
					<?php
					}
					?>
					<!---->
				</div>
			</div>
			<div class="col-md-3">
				<div id="applicationSubMenuShow">
					<div id="applicationSubMenuResult">
						<!-- Resultant div for showing application sub menu by ajax -->
						<!-- For Access control Edit -->
						<?php
						if( isset( $_GET['uid'] ) ){
							//Getting sub menu items from application menu
							$applicationSubMenuId = $obj->Select( "ApplicationSubMenu", "ApplicationMenuId=" . $applicationMenuId[0]['ApplicationMenuId'] );
						?>
						<label class="col-lg-9 control-label">Application Sub Menu</label><i class="" id="appSubMenuValidation"></i>
						<select name="filter[]" id="applicationSubMenu" class="form-control">
							<option value="0">Choose Application Sub Menu</option>
							<?php
							foreach( $applicationSubMenuId as $eachapplicationSubMenuId ){
								?>
								<option value="<?php echo $eachapplicationSubMenuId['Id']; ?>"
									<?php echo (isset($_GET['smid']) && $eachapplicationSubMenuId['Id']==$_GET['smid'])?"selected":""; ?> 
								>
									<?php echo $eachapplicationSubMenuId['Title']; ?>
								</option>
								<?php
							}
							?>
						</select>
						<?php
						}
						?>
						<!---->
					</div>
				</div>
			</div>
			<div class="col-md-1">
				<label class="control-label col-sm-12">&nbsp;</label>
				<button type="submit" name="updAccessFilter" id="updFilter" class="btn btn-success <?php echo isset($_GET['smid'])?"":"hide"; ?>">
					Update <i class="icon-check position-right"></i>
				</button>
				<input type="hidden" name="hiddenAF[]" id="hiddenMid" value="<?php echo isset($_GET['mid'])?$_GET['mid']:""; ?>">
				<input type="hidden" name="hiddenAF[]" value="<?php echo isset($_GET['uid'])?$_GET['uid']:""; ?>">
				<input type="hidden" name="hiddenAF[]" value="<?php echo isset($_GET['smid'])?$_GET['smid']:""; ?>">
			</div>	
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="panel-body">
					<div id="appPageShow" class="form-group <?php echo isset($_GET['smid'])?"":"hide"; ?>">
						<label class="col-lg-12 control-label">Application Page</label><i class="" id="appPageValidation"></i>
						<div class="checkbox checkbox-switchery switchery-lg">
							<div class="row">
							<?php
							$colCounter = 0;
							$actionCount = ""; //Setting Checking counter for actions for a particular user
							foreach( $allPageItems as $eachPageItem ) {
								$colCounter++;
								if(isset( $_GET['uid'] )){
									$actionCount = $obj->Select( "UserPage" , $whereClause="UserId=" . $_GET['uid'] . " AND MainMenuId=" . $_GET['mid'] . " AND ApplicationSubMenuId=" . $_GET['smid'] . " AND ApplicationPageId=" . $eachPageItem['Id'], $orderBy=null, $limit=null );
								}
								?>
								<div class="col-sm-12 col-lg-3">
									<label><br/>
			                        	<?php echo $eachPageItem['Title']; ?> <input type="checkbox" name="filter[]" class="switchery access" <?php echo (isset($_GET['smid']) && count($actionCount) == 1)?'checked="checked"':''; ?>  value="<?php echo $eachPageItem['Id']; ?>" >
			                        </label>
								</div>
								<?php
								if( $colCounter%4 == 0 ){
									?>
									</div><br/><div class="row">
									<?php
								}
							}
							?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="bottomGap">&nbsp</div>
	</form>
	<?php
}
?>
<?php
include("common/footer.php");
?>
<script type="text/javascript">
//Single checking for checkboxes
$(".users").on('click', function(){
	var limit = $('#totalRows').val();
	for(var c=1; c<=limit; c++){ //Checks all rows
		if( c != $(this).attr('id') ){ //If row number doesn't matches checked checkbox value
			$("#"+c).prop("checked", false); //Make it unchecked
		}
	}
});

//Ajax Call - to get application menu under chosen main menu
$(document).on('change', '#mainMenu', function(){
	$.ajax( {
		url:"class/lib.php",
		data:{"menuId":$(this).val()},
		success:function(result) {
			if(result !== "null"){ //If child application menu item found
				//Setting application menu dropdown
				var application = '<label class="col-lg-7 control-label">Application Menu</label><select name="filter[]" id="applicationMenu" class="form-control"><option value="0">Choose Application Menu</option>';
			
				var applicationData = $.parseJSON(result); //JSON decoding result data
				
				for ( var a in applicationData ) { //Getting options for application menu
				    application += '<option value="' + applicationData[a].Id + '">' + applicationData[a].Title + '</option>';
				}

				application += '</select>';
				
				$("#applicationMenuResult").html(application);

			}else{ //If child application menu item not found

				var application = '<label class="col-lg-12 control-label">&nbsp;</label><label class="col-lg-12 control-label text-danger"><h5>Sorry ! No Data Found.</h5></label>';

				$("#applicationMenuResult").html(application);

				$("#applicationSubMenuShow").addClass("hide"); //hide application sub menu dropdown
				$("#appPageShow").addClass("hide"); //hide application page dropdown
				$("#updFilter").addClass("hide"); //hide Update button - In case of updation only
			}
		}
	});
});

//Ajax Call - to get application sub menu under chosen application menu
$(document).on('change', '#applicationMenu', function(){
	$.ajax( {
		url:"class/lib.php",
		data:{"appMenuId":$(this).val()},
		success:function(result) {
			if(result !== "null"){ //If child application sub menu item found
				//Setting application sub menu dropdown
				var application = '<label class="col-lg-9 control-label">Application Sub Menu</label><i class="" id="appSubMenuValidation"></i><select name="filter[]" id="applicationSubMenu" class="form-control"><option value="0">Choose Application Sub Menu</option>';
			
				var applicationData = $.parseJSON(result); //JSON decoding result data
				
				for ( var a in applicationData ) { //Getting options for application menu
				    application += '<option value="' + applicationData[a].Id + '">' + applicationData[a].Title + '</option>';
				}

				application += '</select>';

				$("#applicationSubMenuResult").html(application);

				$("#applicationSubMenuShow").removeClass("hide"); //showing application sub menu dropdown
					$("#updFilter").removeClass("hide"); //Showing Update button - In case of updation only
			}else{ //If child application sub menu item not found

				var application = '<label class="col-lg-12 control-label">&nbsp;</label><label class="col-lg-12 control-label text-danger"><h5>Sorry! No Data Found.</h5></label>';

				$("#applicationSubMenuResult").html(application);

				$("#applicationSubMenuShow").addClass("hide"); //hide application sub menu dropdown
				$("#appPageShow").addClass("hide"); //hide application page dropdown
				$("#updFilter").addClass("hide"); //hide Update button - In case of updation only
			}
		}
	});
});

$(document).on('change', '#applicationSubMenu', function(){
	if($(this).val() != 0){
		window.location="userSetting.php?mid="+$("#mainMenu").val()+"&uid="+$("#userId").val()+"&smid="+$(this).val()+"#bottomGap";	
	}
});

//submission validation - "accessFilterForm" Form
$("#accessFilterForm").submit(function(){
	if( $("#applicationSubMenu").val() == "0" ){ //For Application sub menu
		$("#appSubMenuValidation").addClass("icon-point-down text-danger");
		$("#appPageValidation").removeClass("icon-point-down text-danger");
		$("#applicationSubMenu").focus();
		return false;
	}
});
</script>