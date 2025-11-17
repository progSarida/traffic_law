<?php
include("common/header.php");
include("common/menu.php");
if( !isset( $_SESSION['authKey'] ) ){
	echo "<script>window.location='index.php'</script>";
}

$applicationMenuId = $obj->Select( "ApplicationMenu" ); //For Dropdown

$fieldValue = array( 0=>'', 1=>'', 2=>'', 3=>'', 4=>'', 5=>'', 6=>'' ); //Setting field value to null in case of insert data
$submitBtnType = "addAppSubMenu";
$submitBtnName = "add";

$allItems = $obj->Select( "ApplicationSubMenu" ); //Getting all application menu items
$allItemCount = count( $allItems ); //Counting total number of items in the table
$totalPages = ceil( $allItemCount / 10 );
$showItemFrom = 0; //Item show from row
$showItemTo = 10; //Item show to row
if( isset($_GET['page']) ){ //If page number found from URL
	$showItemFrom = ( $_GET['page'] - 1 ) * 10;
}

$limit = $showItemFrom . "," . $showItemTo; //Setting page limit
$showItems = $obj->Select( "ApplicationSubMenu" , $whereClause=null, $orderBy=null, $limit ); //Getting all main menu items under limit

if( isset( $_GET['edit'] ) ){
	$editItem = $obj->Select( "ApplicationSubMenu", "Id=".$_GET['edit'] ); //For editting a particular row	
	//Putting values for update data
	$fieldValue = array(
						0=>$editItem[0]['ApplicationMenuId'],
						1=>$editItem[0]['Title'],
						2=>$editItem[0]['LinkPage'],
						3=>$editItem[0]['Disabled'],
						4=>$editItem[0]['Description'],
						5=>$editItem[0]['SubMenuOrder'],
						6=>$_GET['edit']
					);
	$submitBtnType = "updateAppSubMenu";
	$submitBtnName = "update";
}
?>
<div class="row">
	<div class="col-md-6">
		<!-- Basic layout-->
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
						<label class="col-lg-3 control-label">Application Menu:</label>
						<div class="col-lg-9">
							<select name="applicationSubMenu[]" class="form-control">
								<option value="0">Choose</option>
								<?php
								foreach ($applicationMenuId as $menuId) {
								?>
									<option value="<?php echo $menuId['Id'] ?>" <?php if($menuId['Id']==$fieldValue[0]){ ?>selected<?php }?> >
										<?php echo $menuId['Title']?>
									</option>
								<?php
								}
								?>
							</select>
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-3 control-label">Title:</label>
						<div class="col-lg-9">
							<input type="text" name="applicationSubMenu[]" class="form-control" value="<?php echo $fieldValue[1]; ?>" placeholder="Enter Title" required >
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-3 control-label">Link Page:</label>
						<div class="col-lg-9">
							<input type="text" name="applicationSubMenu[]" class="form-control" value="<?php echo $fieldValue[2]; ?>" placeholder="Enter Link Page" required >
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-3 control-label">Disabled:</label>
						<div class="col-lg-9">
							<label class="radio-inline">
								<input type="radio" name="applicationSubMenu[]" class="styled" value="1" <?php if(isset($fieldValue[3]) && $fieldValue[3]==1){ ?> checked="checked" <?php }else{ ?> checked="checked" <?php } ?> >
								Yes
							</label>
							<label class="radio-inline">
								<input type="radio" name="applicationSubMenu[]" class="styled" value="0" <?php if(isset($fieldValue[3]) && $fieldValue[3]==0){ ?> checked="checked" <?php } ?> >
								No
							</label>
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-3 control-label">Description:</label>
						<div class="col-lg-9">
							<input type="text" name="applicationSubMenu[]" class="form-control" value="<?php echo $fieldValue[4]; ?>" placeholder="Enter Description" required >
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-3 control-label">Sort Order:</label>
						<div class="col-lg-9">
							<input type="text" name="applicationSubMenu[]" class="form-control" value="<?php echo $fieldValue[5]; ?>" required size="2">
						</div>
					</div>

					<div class="text-right">
						<input type="hidden" name="applicationSubMenu[id]" value="<?php echo $fieldValue[6]; ?>">
						<button type="submit" name="<?php echo $submitBtnType; ?>" class="btn btn-primary"><?php echo strtoupper( $submitBtnName ); ?> <i class="icon-arrow-right14 position-right"></i></button>
					</div>
				</div>
			</div>
		</form>
		<!-- /basic layout -->
	</div>
	<div class="col-md-6">
		<div class="panel panel-flat">
			<div class="panel-heading">
				<h5 class="panel-title">View</h5>
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
							<th>Application Menu</th>
							<th>Title</th>
							<th>Link Page</th>
							<th>Disabled</th>
							<th>Description</th>
							<th>Order</th>
							<th class="text-center">Actions</th>
						</tr>
					</thead>
					<tbody>
					<?php
					foreach( $showItems as $eachMenuItem ){
						if($eachMenuItem['Disabled']==1){
							$iconClass = "label label-danger";
							$iconText = "Yes";
						}else{
							$iconClass = "label label-success";
							$iconText = "No";
						}
						//Edit Link with current page number ( if Page number is found )
						$link = isset( $_GET['page'] )?$eachMenuItem['Id'] . "&page=" . $_GET['page']:$eachMenuItem['Id'];
					?>
						<tr>
							<td><?php echo $eachMenuItem['ApplicationMenuId']; ?></td>
							<td><?php echo $eachMenuItem['Title']; ?></td>
							<td><?php echo $eachMenuItem['LinkPage']; ?></td>
							<td><span class="<?php echo $iconClass; ?>"><?php echo $iconText; ?></span></td>
							<td><?php echo $eachMenuItem['Description']; ?></td>
							<td><?php echo $eachMenuItem['SubMenuOrder']; ?></td>
							<td class="text-center">
								<ul class="icons-list">
									<li class="dropdown">
										<a href="#" class="dropdown-toggle" data-toggle="dropdown">
											<i class="icon-menu9"></i>
										</a>
										<ul class="dropdown-menu dropdown-menu-right">
											<li>
												<a href="<?php echo $_SERVER['PHP_SELF'] ."?edit=" . $link; ?>">
													<i class="icon-pencil3"></i> Edit
												</a>
											</li>
											<li>
												<a href="<?php echo "class/lib.php?deleteApplicationSubMenu=" . $eachMenuItem['Id']; ?>" onclick="return confirm('Sure To Delete?');">
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
			</div>
			<div class="datatable-footer">
				<div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate">
					<span>
						<?php
						for( $p=1; $p<=$totalPages; $p++ ){
							$page = isset( $_GET['edit'] )?$p . "&edit=" . $_GET['edit']:$p;
						?>
							<a class="paginate_button 
									<?php if( (isset($_GET['page']) && $p==$_GET['page'] ) || (!isset($_GET['page']) && $p==1 ) ){ ?> 
										current 
									<?php }?>" href="<?php echo $_SERVER['PHP_SELF'] . '?page=' . $page; ?>" >
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
include("common/footer.php");
?>