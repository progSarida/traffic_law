<?php
include("common/header.php");
include("common/menu.php");
?>
<?php
$table = 'MainMenu';
if(isset($_GET['id'])){
	$id = $_GET['id'];
}

if(!empty($_POST)){
	if($_POST['action'] == 'addMailmenu'){
	$desc = stripslashes(trim($_POST['desc']));
	$path = stripslashes(trim($_POST['path']));
	
	
	$aI = array(
		/*array(
			'field' => 'id',
			'type' 	=> 'value',
			'getvaluefield' => ''
		),*/
		array(
			'field' => 'Description',
			'type' 	=> 'value',
			'getvaluefield' => $desc
		),
		array(
			'field' => 'Path',
			'type' 	=> 'value',
			'getvaluefield' => $path
		),
		array(
			'field' => 'Disabled',
			'type' 	=> 'value',
			'getvaluefield' => '0'
		)
	);
		
	$db->Insert($table, $aI);
	
}   else if($_POST['action'] == 'editMailmenu'){
	if(!$id){
	echo "not id";die;	
	}  else {

		$edit_data = $db->Select($table, $where=$id, $order=null, $limit=null);
		$get_edit_data = mysqli_fetch_assoc($edit_data);
		 $path                = $get_edit_data['Path'];
		 $description         = $get_edit_data['Description'];
	}
		
	}
}
?>

<div class="row">
	<div class="col-md-6">
		<!-- Basic layout-->
		<form action="" class="form-horizontal" method="POST">
			<div class="panel panel-flat">
				<div class="panel-heading">
					<h5 class="panel-title">main Menu</h5>
					<div class="heading-elements">
						<ul class="icons-list">
	                		<li><a data-action="collapse"></a></li>
	                		<li><a data-action="reload"></a></li>
	                		<li><a data-action="close"></a></li>
	                	</ul>
                	</div>
				</div>

				<div class="panel-body">
					<div class="form-group">
						<label class="col-lg-3 control-label">Path:</label>
						<div class="col-lg-9">
							<input type="text" class="form-control" placeholder="Your strong password" name="path" value="<?php !(empty($_POST))?$_POST['path']:$path ?>">
						</div>
					</div>
					<div class="form-group">
						<label class="col-lg-3 control-label">Description:</label>
						<div class="col-lg-9">
							<textarea rows="5" cols="5" class="form-control" placeholder="Enter your message here" name="desc"></textarea>
						</div>
					</div>

					<div class="text-right">
						<?php if(!empty($_GET['mode'] == 'edit')){ ?>
						<input type="hidden" value="editMailmenu" name="action">
						<button type="submit" class="btn btn-primary">edit form <i class="icon-arrow-right14 position-right"></i></button>

							<?php }  else { ?>

						<input type="hidden" value="addMailmenu" name="action">
						<button type="submit" class="btn btn-primary">Submit form <i class="icon-arrow-right14 position-right"></i></button>
					<?php } ?>
					</div>
				</div>
			</div>
		</form>
		<!-- /basic layout -->
	</div>
	<div class="col-md-6">
		<!-- Basic layout-->
		<form action="#" class="form-horizontal">
			<div class="panel panel-flat">
				<div class="panel-heading">
					<h5 class="panel-title">main Menu List</h5>
					<div class="heading-elements">
						<ul class="icons-list">
	                		<li><a data-action="collapse"></a></li>
	                		<li><a data-action="reload"></a></li>
	                		<li><a data-action="close"></a></li>
	                	</ul>
                	</div>
				</div>

					<table class="table datatable-basic">
							<thead>
								<tr>
									<th>Description</th>
									<th>Path</th>
									<th class="text-center">Actions</th>
								</tr>
							</thead>
							<tbody>
							<?php 
							$table = 'MainMenu';
							$res = $db->Select($table, $where=null, $order=null, $limit=null);
								while($row = mysqli_fetch_assoc($res)){  ?>
							<tr>
								<td><?php echo $row['Description']; ?> </td>
								<td><?php echo $row['Path']; ?></td>
								<td><a title="Click Here to Edit Angel Name"href="mainmenu.php?mode=edit&id=<?php echo $row['Id'];?>" class="btn btn-primary"><i class="glyphicon glyphicon-pencil"></i></a>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</form>
		<!-- /basic layout -->
	</div>
</div>