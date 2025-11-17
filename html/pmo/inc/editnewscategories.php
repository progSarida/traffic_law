<?php
include("document_functions.php");
checkLogin();
switch($_POST['name']){
    case 'name_tipologia':
        $query = "UPDATE Tipologia set Description_Tipologia = '".$_POST["value"]."' WHERE Id = '".$_POST["pk"]."'";
        mysqli_query($conn, $query);
    break;
    case 'name_tipoatto':
        $query = "UPDATE tipoAtto set Description_TipoAtto = '".$_POST["value"]."' WHERE Id = '".$_POST["pk"]."'";
        mysqli_query($conn, $query);
    break;
    case 'select_tipologia':
        $query = "UPDATE sottoTipologia set tipologia_id = '".$_POST["value"]."' WHERE Id = '".$_POST["pk"]."'";
        mysqli_query($conn, $query);
    break;
    case 'name_sototipologia':
        $query = "UPDATE sottoTipologia set Description_SottoTipologia = '".$_POST["value"]."' WHERE Id = '".$_POST["pk"]."'";
        mysqli_query($conn, $query);
    break;
    case 'name_enteemitente':
        $query = "UPDATE enteEmittente set Description_EnteEmittente = '".$_POST["value"]."' WHERE Id = '".$_POST["pk"]."'";
        mysqli_query($conn, $query);
    break;
    case 'name_clasificazione':
        $query = "UPDATE clasificazioneAtto set Description_Clasificazione = '".$_POST["value"]."' WHERE Id = '".$_POST["pk"]."'";
        mysqli_query($conn, $query);
    break;
    default:
    echo "Can not change something without select a category!";
}
?>