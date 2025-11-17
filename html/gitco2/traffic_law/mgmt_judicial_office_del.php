<?php
include("../_path.php");
include(INC."/parameter.php");
include(CLS."/cls_db.php");
include(INC."/function.php");

$rs = new CLS_DB();
if (isset($_POST['delete'])){

    $id = $_POST['id'];
    $city = $_POST['city'];
    $deleted = $rs->ExecuteQuery("DELETE FROM traffic_law.JudicialOffice WHERE CityId = '$city' and OfficeId = $id");
    if ($deleted){
        echo json_encode(['202'=>'Successfully Deleted']);
    }else{
        echo json_encode(['errore'=>'ERROR: Not Deleted!']);
    }

}
if (isset($_POST['search'])){

    $cityid = $_SESSION['cityid'];

    $keywords = array();

    isset($_POST['ufficio'])&&!empty($_POST['ufficio'])? array_push($keywords,"traffic_law.Office.TitleIta like '%".$_POST['ufficio']."%'") : null;
    isset($_POST['indirizio'])&&!empty($_POST['indirizio'])? array_push($keywords,"traffic_law.JudicialOffice.Address  like '%".$_POST['indirizio']."%'") : null;
    isset($_POST['email'])&&!empty($_POST['email'])? array_push($keywords,"traffic_law.JudicialOffice.Mail like '%".$_POST['email']."%'") : null;
    isset($_POST['zip'])&&!empty($_POST['zip'])? array_push($keywords,"traffic_law.JudicialOffice.ZIP =".$_POST['zip']."") : null;
    $string ="";
    foreach ($keywords as $index =>$value){
        if ($index===0){
            $string .=$value;
        }else{
            $string .= " OR $value";
        }

    }
    if ($_SESSION['usertype'] >= 50) {
        $judicalOffice = $rs->SelectQuery("select traffic_law.JudicialOffice.*,traffic_law.Office.* 
        from traffic_law.JudicialOffice 
        join traffic_law.Office on traffic_law.JudicialOffice.OfficeId = traffic_law.Office.Id where $string");
    }else{
        $judicalOffice = $rs->SelectQuery("select traffic_law.JudicialOffice.*,traffic_law.Office.* 
        from traffic_law.JudicialOffice 
        join traffic_law.Office on traffic_law.JudicialOffice.OfficeId = traffic_law.Office.Id where $string and traffic_law.JudicialOffice.CityId = '$cityid'");
    }

    $array = array();

    while ($row_results = mysqli_fetch_array($judicalOffice)) {
        array_push($array,array(
            'CityId' => $row_results['CityId'],
            'City'=>$row_results['City'],
            'Office'=> $row_results['TitleIta'],
            'Address'=> $row_results['Address'],
            'Phone'=> $row_results['Phone'],
            'Mail'=> $row_results['Mail'],
            'Fax'=>$row_results['Fax'],
            'ZIP'=>$row_results['ZIP'],
            'Provincia'=>$row_results['Province']
        ));
    }
    echo json_encode($array);
}