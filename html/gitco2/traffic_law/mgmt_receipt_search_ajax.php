<?php
include("_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");
$rs = new CLS_DB();
$cityid = $_SESSION['cityid'];

$controller = $_POST['controller'];
$prefix = $_POST['prefix'];
$number = $_POST['number'];
$tipoatto = $_POST['tipo'];

if ($tipoatto == 0){
    $tipo = null;
}else{
    $tipo = isset($_POST['tipo']) ? 'and Receipt.TipoAtto = ' . $_POST['tipo'] . '' : '';
}
if ($controller ==""){
    $cont = null;
}else{
    $cont = isset($_POST['controller']) ? 'and Receipt.ControllerId = ' . $controller . '' : '';
}


$pre = !empty($_POST['prefix']) ? 'and Receipt.Preffix = "' . $prefix . '"' : '';
$first = !empty($_POST['number']) ? 'and Receipt.StartNumber <= ' . $number . ' and Receipt.EndNumber > ' . $number . '' : '';

$boletario = $rs->SelectQuery("select Receipt.*,Controller.Id as ContId,Controller.Name,Controller.Code 
    FROM Receipt LEFT JOIN Controller on Receipt.ControllerId = Controller.Id 
    WHERE Receipt.CityId = '" . $cityid . "' " . $cont . " " . $pre . " " . $first . " " . $tipo . " ");

$array = array();

while ($row_boletario = mysqli_fetch_array($boletario)) {
   array_push($array,array(
       'Id' => $row_boletario['Id'],
       'Name'=> $row_boletario['Name'],
       'Data'=>$row_boletario['Date'],
       'Code'=>$row_boletario['Code'],
       'Block'=>$row_boletario['Numero_blocco'],
       'Prefix'=>$row_boletario['Preffix'],
       'Extrems'=>$row_boletario['StartNumber'].'/'.$row_boletario['EndNumber']
   )
   );
}
if (sizeof($array) == 0){
    echo "NO";
}else{
    echo json_encode($array);
}

