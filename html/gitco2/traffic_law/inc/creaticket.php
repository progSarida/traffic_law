<?php 
 
include("../_path.php");
include("parameter.php");
include("../cls/cls_db.php");
include("../cls/cls_table.php");
include("function.php");
$userid = $_SESSION['userid'];

$rs = new CLS_DB();

$title = $_POST['title'];
$submenu = $_POST['submenu'];
$priority = $_POST['priority'];
$type = $_POST['type'];
$note = $_POST['note'];
$mainmenu = '3';

$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$charactersLength = strlen($characters);
$randomString = '';
for ($i = 0; $i < 20; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
}

$name       = date('Ymdhisa').$randomString.$_FILES['file']['name'];
$temp_name  = $_FILES['file']['tmp_name'];  
if(isset($name)){
    if(!empty($name)){      
        $location = '../../ticket/public/storage/';      
        if(move_uploaded_file($temp_name, $location.$name)){
            //do nothing
        }
    }       
}  else {
    //do nothing
}

$insMenu = array(
    array('field'=>'title','selector'=>'value','type'=>'str','value'=>$title),
    array('field'=>'code','selector'=>'value','type'=>'str','value'=>'dfgh'),
    array('field'=>'priority_id','selector'=>'value','type'=>'str','value'=>$priority),
    array('field'=>'status_id','selector'=>'value','type'=>'str','value'=>'1'),
    array('field'=>'ticket_type_id','selector'=>'value','type'=>'str','value'=>$type),
    array('field'=>'main_menu_id','selector'=>'value','type'=>'str','value'=>$mainmenu),
    array('field'=>'application_submenu_id','selector'=>'value','type'=>'str','value'=>$submenu),
    array('field'=>'note','selector'=>'value','type'=>'str','value'=>$note),
    array('field'=>'ticket_date','selector'=>'value','type'=>'str','value'=>date('Y-m-d')),
    array('field'=>'ticket_time','selector'=>'value','type'=>'str','value'=>date('H:i:s')),
    array('field'=>'user_id','selector'=>'value','type'=>'str','value'=>$userid),
    array('field'=>'imagePath','selector'=>'value','type'=>'str','value'=>'storage/'.$name)
);

$ticket = $rs->Insert("task_flow.tickets",$insMenu);

$ticket_id = (string)$ticket;
$updMenu = array(
    array('field'=>'code','selector'=>'value','type'=>'str','value'=>$ticket_id)
);
$rs->Update('task_flow.tickets',$updMenu, 'Id='.$ticket);
?>