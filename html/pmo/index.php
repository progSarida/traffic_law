<?php
session_start();
const TARGET_FOLDER = 'inc/uploads/';
include 'inc/connection.php';
include_once "Librerie/XmlLanguageReader.php";

function crea_dir( $path )
{
    if (!is_dir($path)) {
        $folder = explode("/",$path);

        $control_path = $folder[0];

        for($l=1;$l<count($folder);$l++)
        {
            $control_path .= "/".$folder[$l];
            if( is_dir( $control_path ) == false )
            {
                mkdir( $control_path );
            }
        }
    }
    return $path;
}


$aLan = array('ita','eng','ger','fre','spa');
$lang = isset($_GET['l'])?$_GET['l']:"";
if(!isset($_SESSION['lan'])){
    $_SESSION['lan'] = 'ita';
}

if(isset($_REQUEST['l'])){
    $_SESSION['lan'] = (in_array($_REQUEST['l'], $aLan)) ? $_REQUEST['l'] : 'ita';
}


$strLan = "";

for ($i=0;$i<count($aLan);$i++){
    $strLan .= ($_SESSION['lan'] == $aLan[$i]) ? '<img src="img/f_'.$aLan[$i].'.png" style="width:25px">' : '<a href=?l='.$aLan[$i].'><img src="img/f_'.$aLan[$i].'.png" style="width:25px"></a>';

}

$xml = new xmlLanguageReader("language.xml");


$strLan .= '<img src="img/f_rus.png" style="width:25px">';

$aValidationError = array(
    'ita'=>'Campo obbligatorio',
    'eng'=>'Required field',
    'ger'=>'Pflichtfeld',
    'fre'=>'Champs requis',
    'spa'=>'Campo requerido',
);
$description = array(
    'ita'=>'DESCRIZIONE',
    'eng'=>'DESCRIPTION',
    'ger'=>'BESCHEREIBUNG',
    'fre'=>'DESCRIPTION',
    'spa'=>'DESCRIPTIÓN',
);
$document = array(
    'ita'=>'DOCUMENTO',
    'eng'=>'DOCUMENT',
    'ger'=>'DOKUMENT',
    'fre'=>'DOCUMENT',
    'spa'=>'DOCUMENTO',
);
$section = array(
    'ita'=>'TARATURE',
    'eng'=>'APPROVALS / TESTS',
    'ger'=>'ZULASSUNGEN / TESTS',
    'fre'=>'APPROBATIONS / TESTS',
    'spa'=>'HOMOLOGACIONES / PRUEBAS',
);
$category_o = array(
    'ita'=>'OMOLOGAZIONI',
    'eng'=>'APPROVALS',
    'ger'=>'ZULASSUNGEN',
    'fre'=>'APPROBATIONS',
    'spa'=>'HOMOLOGACIONES',
); 
$category_c = array(
    'ita'=>'TARATURE',
    'eng'=>'TESTS',
    'ger'=>'TESTS',
    'fre'=>'TESTS',
    'spa'=>'PRUEBAS',
);
////////////////
$omologazioni = array(
    'ita'=> 'APPROVAZIONI',
    'eng'=> 'VALIDATION',
    'ger'=> 'RATIFIZIERUNG',
    'fre'=> 'HOMOLOGATION',
    'spa'=> 'HOMOLOGACION',
);
$documentazione = array(
    'ita'=> 'DOCUMENTAZIONE',
    'eng'=> 'DOCUMENTATION',
    'ger'=> 'DOKUMENTATION',
    'fre'=> 'DOCUMENTATION',
    'spa'=> 'DOCUMENTACIÓN',
);
$info = array(
    'ita'=> 'INFORMAZIONI PATENTE A PUNTI: +39 848 782 782',
    'eng'=> 'INFO POINTS DRIVING LICENSE: +39 848 782 782',
    'ger'=> 'INFORMAZIONEN FüHRERSCHEIN: +39 848 782 782',
    'fre'=> 'INFO PERMIS DE COINDUIRE: +39 848 782 782',
    'spa'=> 'INFO PERMISO DE CONDUCCION: +39 848 782 782',
);
$arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);

$st = file_get_contents('https://gitco.ovunque-si.it/traffic_law/rest_richiestaDati.php?op=country',false, stream_context_create($arrContextOptions));
$st = json_decode($st);
$st = $st->Dati;
//var_dump($rs->Dati[0]->Title);
//die;
$countSt = count($st);
$optSt = "<option value=''></option>";
for($z=0; $z < $countSt; $z++){
    $optSt .= "<option value=".$st[$z]->Id.">".$st[$z]->Title."</option>";
}

/*$connTL = mysqli_connect("localhost","root","","traffic_law");
//$connTL = mysqli_connect("62.94.231.188","root","GP~0o>hU@:/:q*","traffic_law");

$query = "SELECT Id,Title FROM country ORDER BY Title ASC";
$st = mysqli_query($connTL, $query);

$optSt = "<option value=''></option>";
while($row = mysqli_fetch_array($st)){
    $optSt .= "<option value=".$row['Id'].">".$row['Title']."</option>";
}*/



$com = file_get_contents('https://gitco.ovunque-si.it/traffic_law/rest_richiestaDati.php?op=cityS',false, stream_context_create($arrContextOptions));
$com = json_decode($com);
$com = $com->Dati;
//var_dump($rs->Dati[0]->Title);
//die;
$countRs = count($com);
$optCom = "<option value=''></option>";
for($z=0; $z < $countRs; $z++){
    $optCom .= "<option value=".$com[$z]->Id.">".$com[$z]->Title."</option>";
    $arrayComuni[] = array("CC" => $com[$z]->Id, "Nome_Comune" => $com[$z]->Title);
}



/*$connSR = mysqli_connect("localhost","root","","sarida");
//$connSR = mysqli_connect("62.94.231.188","root","GP~0o>hU@:/:q*","sarida");

$query = "SELECT Id,Title FROM city ORDER BY Title ASC";
$com = mysqli_query($connSR, $query);
$arrayComuni = array();

$optCom = "<option value=''></option>";
while($row = mysqli_fetch_array($com)){
    $optCom .= "<option value=".$row['Id'].">".$row['Title']."</option>";
    $arrayComuni[] = array("CC" => $row['Id'], "Nome_Comune" => $row['Title']);
}*/


$query = "SELECT Com_CC, Com_Nome_Comune FROM comune_gestito_dettagli WHERE Com_Abilitato='S' ORDER BY Com_Nome_Comune";
$rs= mysqli_query($conn, $query);

$strCity_2 = "<option value=''></option>";
$strCity = "<select data-validation=\"required\" data-validation-error-msg=\"".$aValidationError[$_SESSION['lan']]."\" id=\"c\" name=\"c\"><option></option>";
while($row = mysqli_fetch_array($rs)){
    $strCity .= "<option value=".$row['Com_CC'].">".$row['Com_Nome_Comune']."</option>";
    $strCity_2 .= "<option value=".$row['Com_CC'].">".$row['Com_Nome_Comune']."</option>";
}
$strCity .= "</select>";

$y = date('Y');
$strAnno = "<option value=''></option>";
$strYear = "<select data-validation=\"required\" data-validation-error-msg=\"".$aValidationError[$_SESSION['lan']]."\" id=\"yv\" name=\"yv\" style=\"width:150px\"><option></option>";
for($i=2012;$i<=$y;$i++){
    $strYear .= "<option value=".$i.">".$i."</option>";
    $strAnno .= "<option value=".$i.">".$i."</option>";
}
$strYear .= "</select>";

$strGiornoV = "<option value=''></option>";
$strD= "<select data-validation=\"required\" data-validation-error-msg=\"".$aValidationError[$_SESSION['lan']]."\" name=\"dvd\" style=\"width:60px\"><option></option>";
for($i=1;$i<=31;$i++){
    $strTime = ($i<10) ? '0'.$i : $i;
    $strD .= "<option value=".$strTime.">".$strTime."</option>";
    $strGiornoV .= "<option value=".$strTime.">".$strTime."</option>";
}
$strD .= "</select>";

$strMeseV = "<option value=''></option>";
$strM= "<select data-validation=\"required\" data-validation-error-msg=\"".$aValidationError[$_SESSION['lan']]."\" name=\"dvm\" style=\"width:60px\"><option></option>";
for($i=1;$i<=12;$i++){
    $strTime = ($i<10) ? '0'.$i : $i;
    $strM .= "<option value='".$strTime."'>".$strTime."</option>";
    $strMeseV .= "<option value='".$strTime."'>".$strTime."</option>";
}
$strM .= "</select>";

$strAnnoV = "<option value=''></option>";
$strY = "<select data-validation=\"required\" data-validation-error-msg=\"".$aValidationError[$_SESSION['lan']]."\" name=\"dvy\" style=\"width:150px\"><option></option>";
for($i=2012;$i<=$y;$i++){
    $strY .= "<option value=".$i.">".$i."</option>";
    $strAnnoV .= "<option value=".$i.">".$i."</option>";
}
$strY .= "</select>";


$strOre = "<option value=''></option><option value='00'>00</option>";
$strHH= "<select data-validation=\"required\" data-validation-error-msg=\"".$aValidationError[$_SESSION['lan']]."\" name=\"hv\" style=\"width:60px\"><option></option>";
$strHH .= "<option value='00'>00</option>";
for($i=1;$i<24;$i++){
    $strTime = ($i<10) ? '0'.$i : $i;
    $strHH .= "<option value='".$strTime."'>".$strTime."</option>";
    $strOre .= "<option value='".$strTime."'>".$strTime."</option>";
}
$strHH .= "</select>";

$strMin = "<option value=''></option><option value='00'>00</option>";
$strMM= "<select data-validation=\"required\" data-validation-error-msg=\"".$aValidationError[$_SESSION['lan']]."\" name=\"mv\" style=\"width:60px\"><option></option>";
$strMM .= "<option value='00'>00</option>";
for($i=1;$i<60;$i++){
    $strTime = ($i<10) ? '0'.$i : $i;
    $strMM .= "<option value='".$strTime."'>".$strTime."</option>";
    $strMin .= "<option value='".$strTime."'>".$strTime."</option>";
}

$strMM .= "</select>";


function getDirContents($dir, &$results = array())
{
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        //var_dump($path);
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

//getDirContents('new');
$allFile = getDirContents(crea_dir($_SERVER['DOCUMENT_ROOT']."/PoliziaMunicipale/inc/uploads/Temp"));

for($i = 0; $i < count($allFile); $i++)
    unlink($allFile[$i]);



$aMenu = array(
    'ita'=>array('Verbali','Legislazione','Tarature',               'Omologazione/Approvazione',  'Documentazione', 'FAQ', 'Notizie',    'Sentenze',  'Contatti', 'Dichiarazione trasgressori'),
    'eng'=>array('Minutes','Legislation','Calibration certificate', 'Validation',    'Documentation',  'FAQ', 'News',       'Judgments', 'Contact',  'Offender statement'),
    'ger'=>array('Minutes','Gesetzgebung','Eichung Zertifikat',     'Ratifizierung', 'Dokumentation',  'FAQ', 'Nachrichten','Urteile',   'Kontakte', 'Tätererklärung'),
    'fre'=>array('Verbal','Legislation','Etalonnage',               'Homologation',  'Documentation',  'FAQ', 'Nouvelles',  'Jugements', 'Contacts', 'Déclaration du délinquant'),
    'spa'=>array('Minutos','Legislacion','Testear',                 'Homolologaciòn','Documentos',     'FAQ', 'Noticias',   'Juicios',   'Contactos','Declaración del infractor'),

);



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">

    <title>PoliziaMunicipaleOnline</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">

    <!--<script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="js/jquery.min.vegas.js"></script>-->

    <script src="js/jquery-latest.min.js"></script>


    <!--<script src="https://jaysalvat.github.io/vegas/releases/latest/vegas.js"></script>-->
    <script type="text/javascript" src="Librerie/vegas/vegas.min.js"></script>


    <link href="css/bootstrap.css" rel="stylesheet" media="all">
    <link rel="stylesheet" type="text/css" href="css/bootstrap-select.css">
    <script type="text/javascript" src="js/bootstrap-select.js"></script>


    <link href="css/bootstrap-datepicker.css" rel="stylesheet" media="screen">
    <script src="js/bootstrap-datepicker.js"></script>

    <link rel="stylesheet" href="css/site-demos.css">

    <link href="font/font-awesome_002.css" rel="stylesheet" media="screen">
    <link href="font/font-awesome.css" rel="stylesheet" media="screen">
    <link href="font/font-awesome-ie7.html" rel="stylesheet" media="screen">

    <link href="css/jquery.css" rel="stylesheet" media="screen">
    <link href="css/flexslider.css" rel="stylesheet" media="screen">
    <link href="css/style.css" rel="stylesheet" media="screen">
    <link href="Librerie/vegas/vegas.min.css" rel="stylesheet" media="screen">
    <!--<link rel="stylesheet" href="https://jaysalvat.github.io/vegas/releases/latest/vegas.min.css">-->

    <style>
        select option{
            height: auto;
            word-wrap: break-word;
            white-space: normal;
        }

        .multiSelect option{
            background-color: white;
            color: rgba(0,98,255,1.0);
        }

        .multiSelect{
            background-color: white;
        }

        .file_drag_area {
             width: 150px;
             height: 130px;
             border: 2px dashed #ccc;
             line-height: 130px;
             text-align: center;
             font-size: 16px;
             /*color: #B34F4F;*/
        }
        .file_drag_over {
            color: #000;
            border-color: #000;
        }
        .no_file {
            color: #B34F4F;
            border-color: #B34F4F;
        }
        #container_uploaded_file{
            background-color: rgba(161,161,161,0.5);
            border-radius: 10px;
            font-size: 12px;
        }
    </style>

    <script>
        var TotalNumberFile = 0;

        $(document).ready(function () {
            $('#dv').datepicker({
                format: "dd/mm/yyyy",
                autoclose: true
            });

            $.vegas('slideshow',
                {
                    delay: 10000,

                    backgrounds: [
                        {src: 'img/bg/img1.jpg', fade: 2000},
                        {src: 'img/bg/img2.jpg', fade: 2000},
                        {src: 'img/bg/img3.jpg', fade: 2000},
                        {src: 'img/bg/img4.jpg', fade: 2000},
                        {src: 'img/bg/img5.jpg', fade: 2000},
                        {src: 'img/bg/img6.jpg', fade: 2000},
                    ]


                })('overlay');



            /************************************ questo serve per cambiare l'immagine di sfondo sfuocando ************************************

            var allBackImage = ['img/bg/img1.jpg','img/bg/img2.jpg','img/bg/img3.jpg','img/bg/img4.jpg','img/bg/img5.jpg','img/bg/img6.jpg'];

            $('#ImgContent').css("background-image","url('img/bg/img1.jpg')");
            $('#ImgContent').css("background-repeat","no-repeat");
            $('#ImgContent').css("background-size",screen.width+"px "+screen.height+"px");
            $('#ImgContent').css("background-attachment","fixed");
            $('#ImgContent').css("z-index","1");
            $('#ImgContent').css("height",screen.height+"px");
            $('#ImgContent').css("width",screen.width+"px");
            $('#ImgContent').css("position","fixed");
            $('#ImgContent').css("display","block");


            ChangeImage(allBackImage,5000);*/

            $("#login").click(function () {
                $('#sfondo-login').show();
                $('#header').hide();
                $('#Verbale').hide();
                $('#Legislazione').hide();
                $('#FAQ').hide();
                $('#Contatti').hide();
                $('#footer').hide();
                $("#header_1").removeClass("active");
                $("#header_2").removeClass("active");
                $("#header_3").removeClass("active");
                $("#header_4").removeClass("active");
                $("#header_5").removeClass("active");
            });
            $(".close_login").click(function () {
                $('#sfondo-login').hide();
                $('#header').show();
                $('#Verbali').show();
                $('#Legislazione').show();
                $('#FAQ').show();
                $('#Contatti').show();
                $("#header_1").addClass("active");
            });

            $("#login_sub").click(function () {
                var user = $("#login_username").val();

                var pass = $("#login_password").val();
                if (user != "" && pass != "") {
                    $.ajax({
                        type: "GET",
                        url: "php/controlla_logIn.php",
                        data: "user=" + user + "&pass=" + pass,
                        cache: false,
                        success: function (html) {
                            $("#login_error").html(html);
                            var controllo = $("#login_error").html();

                            if (controllo != " UserName o Password errati") {
                                $('#sfondo-login').hide();
                                $('#header').show();
                                $('#Verbali').show();
                                $('#Legislazione').show();
                                $('#FAQ').show();
                                $('#Contatti').show();
                                $("#header_1").addClass("active");

                            }
                        }
                    });
                }
            });

        });



        function EliminaFile(pathFile,idDelete){
            //alert(pathFile+" --- "+idDelete);
            $.ajax({
                type: "POST",
                async: false,
                url: "ajax/deleteFile.php",
                data: {
                    path: pathFile,
                },

                success: function(value) {
                    //alert("delete "+idDelete);
                    $("#"+idDelete).remove();
                    TotalNumberFile --;
                }
            });
        }

        function Abilita (flag = true)
        {
            if(flag) {
                window.open("http://www.poliziamunicipale-online.it/doc/disclosures_<?= $_SESSION['lan'] ?>.pdf");
                $("[name=informativa]").prop("checked", true);
                $("[name=informativa]").prop("disabled", false);
                $("[name=info]").prop("disabled", true);
                $("[name=tastoinviodati]").prop("disabled", false);
            }
            else if(!flag)
            {
                window.open("http://www.poliziamunicipale-online.it/doc/disclosures_<?= $_SESSION['lan'] ?>.pdf");
            }
        }

        function ChangeImage(img,delay)
        {
            var x=1;

            setInterval(function(){
                SfuocaImg(100,img[x]);
                x++;
                if(x > (img.length-1) ) x = 0;
            }, delay);
        }

        function SfuocaImg(delay,img){
            var x = 1;
            var y = 11;
            var sfuoca = null;
            //alert("sfuoca");
            sfuoca = setInterval(function(){
                if(x<=10)
                {
                    $('#ImgContent').css("-webkit-filter","blur("+x+"px)");
                    $('#ImgContent').css("-moz-filter","blur("+x+"px)");
                    $('#ImgContent').css("-o-filter","blur("+x+"px)");
                    $('#ImgContent').css("-ms-filter","blur("+x+"px)");
                    $('#ImgContent').css("filter","blur("+x+"px)");
                    x++;
                }

                if(x>10){
                    if(y==11) $('#ImgContent').css("background-image","url('"+img+"')");

                    $('#ImgContent').css("-webkit-filter","blur("+y+"px)");
                    $('#ImgContent').css("-moz-filter","blur("+y+"px)");
                    $('#ImgContent').css("-o-filter","blur("+y+"px)");
                    $('#ImgContent').css("-ms-filter","blur("+y+"px)");
                    $('#ImgContent').css("filter","blur("+y+"px)");
                    y--;

                    id(y < 1)
                    {
                        //alert("fine");
                        clearInterval(sfuoca);
                        return;
                    }
                }
            }, delay);


            //alert("dopo");
        }

    </script>


</head>
<body>
<div id="ImgContent" style="width: 100%;"></div>

<div id="ContentIndex" style="z-index: 100; position: relative;">
<a id="back-top" href="javascript:void(0)" style="display: inline;">
    <img src="img/up.png" style="width:30px;" />
</a>




<div style="display: none;" id="sfondo-login">
    <section id="box-login" class="box" style="margin-top: 0%;">
        <div class="container">
            <div style="margin-top: 110px;" class="panel">
                <div class="row-fluid">
                    <div class="span12 header" style="background-color: rgba(0, 0, 0, 1);padding-top:0px;">
                        <a href="#/home" class="visible-desktop close_login"> <img src="img/closed_search.png"
                                                                                   style="width:5%;"> </a>
                        <a href="#/home" class="visible-tablet close_login_"> <img src="img/closed_search.png"
                                                                                   style="width:8%;"></a>
                        <a href="#/home" class="visible-phone close_login"><img src="img/closed_search.png"
                                                                                style="width:12%;"></a>
                        <hgroup>
                            <h2>LogIn</h2>

                            <h3>Accedi </h3>
                        </hgroup>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


<header style="display: block;" id="header">
    <div class="container">
        <div class="row-fluid">
            <div class="span12">
                <div class="navbar">
                    <div class="navbar-inner">
                        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </a>

                        <a class="brand" href="javascript:void(0)">
                            <h1 id="logo">Polizia Municipale Online</h1>
                        </a>
                        <div style="position:absolute; top:4px;left:85px;">
                            <?= $strLan ?>
                        </div>
                        <div class="nav-collapse collapse" style="position:absolute; top:-30px;left:240px; font-size:1.5rem">
                            <ul class="nav pull-right" id="navigation" >
                                <li><a data-nav="scroll" href="#/Minutes" class="active" id="header_1"><?= $aMenu[$_SESSION['lan']][0] ?></a></li>
                                <li><a class="" data-nav="scroll" href="#/Offenders" id="header_11"><?= $xml->getWord("titoloLinkTras",$_SESSION['lan']); ?></a></li>
                                <li><a class="" data-nav="scroll" href="#/Legislation" id="header_2"><?= $aMenu[$_SESSION['lan']][1] ?></a></li>
                                <li><a class="" data-nav="scroll" href="#/Approval" id="header_3"><?= $aMenu[$_SESSION['lan']][2] ?></a></li>
                                <li><a class="" data-nav="scroll" href="#/Omologation" id="header_4"><?= $aMenu[$_SESSION['lan']][3] ?></a></li>
                                <li><a class="" data-nav="scroll" href="#/Documentation" id="header_5"><?= $aMenu[$_SESSION['lan']][4] ?></a></li>
                                <li><a class="" data-nav="scroll" href="#/FAQ" id="header_6"><?= $aMenu[$_SESSION['lan']][5] ?></a></li>
                                <?php 
                                    if($lang == 'ita' || $lang == ''){
                                        ?>
                                            <li><a class="" data-nav="scroll" href="#/News" id="header_7"><?= $aMenu[$_SESSION['lan']][6] ?></a></li>
                                            <li><a class="" data-nav="scroll" href="#/History" id="header_8"><?= $aMenu[$_SESSION['lan']][7] ?></a></li>
                                        <?php
                                    }
                                ?>
                                <li><a class="" data-nav="scroll" href="#/Contact" id="header_9"><?= $aMenu[$_SESSION['lan']][8] ?></a></li>
                                <li><a class="" data-nav="scroll" href="#/Registrati">Registrazione</a></li>
                                <li><a class="" data-nav="scroll" href="login.php" id="header_10">Login</a></li>

                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



</header>
            <?= include('inc/menu_album.php'); ?> 
                                 
<?= include('inc/minutes_'.$_SESSION['lan'].'.php'); ?>

    <?= include('inc/offenders.php'); ?>
                                    
<?= include('inc/legislation_'.$_SESSION['lan'].'.php'); ?>
                                  
<?= include('inc/approval.php'); ?>
                                  
<?= include('inc/faq_'.$_SESSION['lan'].'.php'); ?>
<?php
                                 
    if($lang == 'ita' || $lang == ''){
        ?>
            <?= include('inc/news.php'); ?>
            <?= include('inc/historico.php'); ?>
            <?= include('inc/registrazione.php'); ?>
        <?php
    }
?>
<?= include('inc/contact_'.$_SESSION['lan'].'.php'); ?>







<div class="container">
    <div style="display: block;" id="footer">

        <div class="description">
            Sarida S.r.l. - Copyright © 2013<br/>
            Sestri Levante (GE)<br/>
            P. Iva: 01338160995<br/>

            <div class="author_small">
                <a href="mailto:informazioni@poliziamunicipale-online.it">
                    informazioni@poliziamunicipale-online.it

                </a>
            </div>
        </div>
    </div>
</div>

<div style="display: none;" id="lightbox"><img id="bigimg" src=""></div>

<script type="text/javascript" src="js/signals.js"></script>
<script type="text/javascript" src="js/crossroads.js"></script>
<script type="text/javascript" src="js/hasher.js"></script>
<script type="text/javascript" src="js/bootstrap.js"></script>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery_002.js"></script>

<script type="text/javascript" src="js/theme.js"></script>


<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-form-validator/2.2.43/jquery.form-validator.min.js"></script>
<script>
    var send_correct = false;
    var pdfScaricato = false;

    $('form').each(function(){

        $.validate({
            onSuccess : function() {
                send_correct = true;
            },
        });
    });


    $( document ).ready(function() {

        var request;
        $('#f_email').submit(function(event){
            if(!send_correct) return false;

            var $form = $(this);
            var $inputs = $form.find("input, select, button, textarea");
            var serializedData = $form.serialize();

            $inputs.prop("disabled", true);
            request = $.ajax({
                url: "ajax/send_mail.php",
                type: "post",
                data: serializedData
            });

            request.done(function (response){
                $('#response_mex').html(response);
            });

            request.fail(function (jqXHR, textStatus, errorThrown){
                $('#response_mex').html("The following error occurred: "+ textStatus);
            });

            request.always(function () {
                $inputs.prop("disabled", false);
            });

            event.preventDefault();
            send_correct = false;
        });


        $('#f_query_offenders').submit(function(event){
            if(!send_correct) return false;

            var $form = $(this);
            var $inputs = $form.find("input, select, button, textarea");
            var serializedData = $form.serialize();

            //console.log(serializedData);
            //alert();

            $inputs.prop("disabled", true);
            request = $.ajax({
                url: "ajax/declaration_offenders.php",
                type: "post",
                dataType: 'json',
                cache: false,
                data: serializedData
            });

            request.done(function (response){
                $('#'+response.id_div).html(response.content);

                $('.date').datepicker({
                    format: 'dd/mm/yyyy'
                });

                if(response.statoNascitaUtente != undefined)
                {

                    $(".file_drag_area").on("dragover", function (){
                        $(this).addClass("file_drag_over");
                        $(this).removeClass("no_file");
                        $("#errorUpload_2").css("display","none");
                        $("#errorUpload_1").css("display","none");
                        $("#errorUpload_0").css("display","none");
                        return false;
                    });
                    $(".file_drag_area").on("dragleave", function (){
                        $(this).removeClass("file_drag_over");
                        //$(this).removeClass("no_file");
                        return false;
                    });
                    $(".file_drag_area").on("drop", function (e){
                        e.preventDefault();
                        $(this).removeClass("file_drag_over");
                        var formData = new FormData();//document.getElementById("myform");
                        files_list = e.originalEvent.dataTransfer.files;
                        //console.log(files_list);
                        //return false;
                        for(var i = 0; i < files_list.length; i++){
                            formData.append('file[]', files_list[i]);
                        }
                        formData.append('folder_name', $("#folder_name").val());
                        console.log(formData);

                        $.ajax({
                            url:"ajax/saveFile.php",
                            method: "POST",
                            dataType: 'json',
                            data: formData,
                            contentType: false,
                            cache: false,
                            processData: false,
                            success: function(data){
                                console.log(data);
                                if(data.error == 0){
                                    $("#uploaded_file").html(data.content);
                                    TotalNumberFile = data.numberRow;
                                }
                                else
                                {
                                    $("#uploaded_file").html(data.content);
                                    TotalNumberFile = data.numberRow;
                                    $(".file_drag_area").addClass("no_file");

                                    if(data.error == 1){
                                        $("#errorUpload_1").css("display","block");
                                    }
                                    else{
                                        $("#errorUpload_2").css("display","block");
                                    }
                                }

                            }
                        })
                    });

                    $("#"+response.StatoRilascioPatente.key).val(response.StatoRilascioPatente.value);
                    $("#"+response.StatoRilascioPatenteTrasgressore.key).val(response.StatoRilascioPatenteTrasgressore.value);

                    $("#"+response.statoNascitaUtente.key).val(response.statoNascitaUtente.value);
                    document.getElementById(response.statoNascitaUtente.key).dispatchEvent(new Event('change'));

                    if($("#"+response.comuneNascitaUtente.key+" option[value='"+response.comuneNascitaUtente.value+"']").length > 0){
                        $("#"+response.comuneNascitaUtente.key).val(response.comuneNascitaUtente.value);
                    }else {
                        $("#"+response.comuneNascitaUtente.key_1).val(response.comuneNascitaUtente.value);
                    }

                    $("#"+response.statoResidenzaUtente.key).val(response.statoResidenzaUtente.value);
                    document.getElementById(response.statoResidenzaUtente.key).dispatchEvent(new Event('change'));

                    //if($("#"+response.comuneResidenzaUtente.key+" option[value='"+response.comuneResidenzaUtente.value+"']").length > 0){
                    if(response.comuneResidenzaUtente.value != "" && response.comuneResidenzaUtente.value != null && response.comuneResidenzaUtente.value != undefined){
                        $("#"+response.comuneResidenzaUtente.key).val(response.comuneResidenzaUtente.value);
                    }else {
                        $("#"+response.comuneResidenzaUtente.key_1).val(response.comuneResidenzaUtente.value_1);
                    }

                    $("#"+response.StatoDitta.key).val(response.StatoDitta.value);
                    document.getElementById(response.StatoDitta.key).dispatchEvent(new Event('change'));

                    //if($("#"+response.ComuneDitta.key+" option[value='"+response.ComuneDitta.value+"']").length > 0){
                    if(response.ComuneDitta.value != "" && response.ComuneDitta.value != null && response.ComuneDitta.value != undefined){
                        $("#"+response.ComuneDitta.key).val(response.ComuneDitta.value);
                    }else {
                        $("#"+response.ComuneDitta.key_1).val(response.ComuneDitta.value_1);
                    }

                    $("#"+response.StatoNascitaTrasgressore.key).val(response.StatoNascitaTrasgressore.value);
                    document.getElementById(response.StatoNascitaTrasgressore.key).dispatchEvent(new Event('change'));

                    if($("#"+response.ComuneNascitaTrasgressore.key+" option[value='"+response.ComuneNascitaTrasgressore.value+"']").length > 0){
                        $("#"+response.ComuneNascitaTrasgressore.key).val(response.ComuneNascitaTrasgressore.value);
                    }else {
                        $("#"+response.ComuneNascitaTrasgressore.key_1).val(response.ComuneNascitaTrasgressore.value);
                    }

                    $("#"+response.StatoResidenzaTrasgressore.key).val(response.StatoResidenzaTrasgressore.value);
                    document.getElementById(response.StatoResidenzaTrasgressore.key).dispatchEvent(new Event('change'));

                    //if($("#"+response.ComuneResidenzaTrasgressore.key+" option[value='"+response.ComuneResidenzaTrasgressore.value+"']").length > 0){
                    if(response.ComuneResidenzaTrasgressore.value != "" && response.ComuneResidenzaTrasgressore.value != null && response.ComuneResidenzaTrasgressore.value != undefined){
                        $("#"+response.ComuneResidenzaTrasgressore.key).val(response.ComuneResidenzaTrasgressore.value);
                    }else {
                        $("#"+response.ComuneResidenzaTrasgressore.key_1).val(response.ComuneResidenzaTrasgressore.value_1);
                    }

                    VisualizzaNascondi(document.getElementById("TipoDichiarazione"));
                    //AbilitaDisabilitaTr("trasgressoreYes","Trasgressore","si","trasgressoreNo");
                    AbilitaDisabilitaTr('DatiLR','TipoTrasgressore');



                }
            });

            request.fail(function (jqXHR, textStatus, errorThrown){
                $('#message_query').html("The following error occurred: "+ textStatus);
            });

            request.always(function () {
                $inputs.prop("disabled", false);
            });

            event.preventDefault();
            send_correct = false;
        });


        $('#f_query').submit(function(event){
            if(!send_correct) return false;

            var $form = $(this);
            var $inputs = $form.find("input, select, button, textarea");
            var serializedData = $form.serialize();

          
            $inputs.prop("disabled", true);
            request = $.ajax({
                url: "ajax/check.php",
                type: "post",
                dataType: 'json',
                cache: false,
                data: serializedData
            });

            request.done(function (response){
                $('#'+response.id_div).html(response.content);
            });

            request.fail(function (jqXHR, textStatus, errorThrown){
                $('#message_query').html("The following error occurred: "+ textStatus);
            });

            request.always(function () {
                $inputs.prop("disabled", false);
            });

            event.preventDefault();
            send_correct = false;
        });


    });

    function SetStatoCompleto(el,idSet){
        $("#"+idSet).val(el.options[el.selectedIndex].text);
    }

    function visualizzaNascondiUploadFile(element){
        if(element.value == "5" && pdfScaricato){
            $(".uploadFile").css("display","block");
        }else{
            //if(!pdfScaricato) alert("Prima scarica il pdf corretto, premi nuovamente scarica pdf");
            $(".uploadFile").css("display","none");
        }
    }

    function salvaTrasgressore(page){
        //alert(page);
        //$('#offendersForm').submit(function(event){

            var checkForm = validateForm();
            if(TotalNumberFile==0 && page=="saveOffenders.php"){
                $(".file_drag_area").addClass("no_file");
                $("#errorUpload_0").css("display","block");
                return false;
            }
            if(!checkForm) return false;

            var $form = $("#offendersForm");
            //var $inputs = $form.find("input, select, button, textarea");
            var serializedData = $form.serialize();

            //$inputs.prop("disabled", true);
            request = $.ajax({
                url: "ajax/"+page,
                type: "post",
                dataType: 'json',
                cache: false,
                data: serializedData,

                success: function(response) {
                    //alert("success");
                    if(response.page == "save") {
                        $('#' + response.id_div).html(response.content);
                        //alert("save");
                    }
                    else if(response.page == "pdf"){
                        //alert("pdf generato --- "+response.codiceAnticontraffazione);
                        if(response.flagPortale == "si") {
                            pdfScaricato = true;
                            $(".uploadFile").css("display", "block");
                            document.getElementById("ModTrasmissione").dispatchEvent(new Event('change'));
                            $("#codiceAnticontraffazione").val(response.codiceAnticontraffazione);

                            //alert("Codice input --> "+$("#codiceAnticontraffazione").val());
                            window.open(response.path, '_blank', 'fullscreen=yes');
                        }
                        //alert("pdfgen");
                    }
                }
            });

            /*request.done(function (response){
                //alert("finito "+response.id_div);
                //console.log(document.getElementById(response));

                //$('#'+response.id_div_2).html(response.content_2);
            });

            request.fail(function (jqXHR, textStatus, errorThrown){
                $('#response_mex').html("The following error occurred: "+ textStatus);
            });

            request.always(function () {
                $inputs.prop("disabled", false);
            });

            event.preventDefault();
            send_correct = false;*/
        //});



        //$('#offendersForm').submit();
    }

    var arrayComuni = <?php echo json_encode($arrayComuni); ?>;

    function ControllaComune(el,tipo,idInput){
        switch (tipo)
        {
            case 1 : if(el.value == "")$("#ComuneNascita1").prop('disabled',false);
            else {
                $("#ComuneNascita1").prop('disabled', true);
                if($("#"+idInput).val()!="") $("#"+idInput).val("");
            }
                break;
            case 2 : if(el.value == "")$("#ComuneResidenza1").prop('disabled',false);
            else {
                $("#ComuneResidenza1").prop('disabled', true);
                if($("#"+idInput).val()!="") $("#"+idInput).val("");
            }
                break;
            case 3 : if(el.value == "")$("#ComuneDitta1").prop('disabled',false);
            else {
                $("#ComuneDitta1").prop('disabled', true);
                if($("#"+idInput).val()!="") $("#"+idInput).val("");
            }
                break;
            case 4 : if(el.value == "")$("#ComuneNascitaTrasgressore1").prop('disabled',false);
            else {
                $("#ComuneNascitaTrasgressore1").prop('disabled', true);
                if($("#"+idInput).val()!="") $("#"+idInput).val("");
            }
                break;
            case 5 : if(el.value == "")$("#ComuneResidenzaTrasgressore1").prop('disabled',false);
            else {
                $("#ComuneResidenzaTrasgressore1").prop('disabled', true);
                if($("#"+idInput).val()!="") $("#"+idInput).val("");
            }
                break;

        }

    }

    /*$( document ).ready(function() {
        $('.date').datepicker({
            format: 'dd/mm/yyyy'
        });

        VisualizzaNascondi(document.getElementById("Polizia"));
        AbilitaDisabilitaTr("trasgressoreYes","Trasgressore","si","trasgressoreNo");
        AbilitaDisabilitaTr('DatiLR','TipoTrasgressore','LR','');
    });*/

    function refresch(){
        window.location.reload(false);
    }

    function checkComune(el,idSelect){
        for(var i = 0; i < arrayComuni.length; i++)
        {
            if(arrayComuni[i]["Nome_Comune"] == el.value){
                alert("L'elemento era già presente nella dropdown");
                //console.log(document.getElementById(idSelect).getElementsByTagName('option').attributes[0].value);
                //document.getElementById(idSelect).getElementsByTagName('option')[arrayComuni[i]["CC"]].selected = 'selected';
                document.getElementById(idSelect).value = arrayComuni[i]["CC"];

                if(document.getElementById(idSelect).value!="") {
                    el.disabled = true;
                    el.value = "";
                }
                //$("#"+idSelect+" select").val("'"+arrayComuni[i]["CC"]+"'");
                break;
            }
        }
    }

    function ScegliInputComune(classeEsistente,classeNonTrovato,idInputNonTrovato,idInputEsistente,stato){

        if(stato.value != "Z000" ){
            $("."+classeNonTrovato).css("display","block");
            $("#"+idInputNonTrovato).addClass("validateCustom vld_Custom_r");
            $("."+classeEsistente).css("display","none");
            $("#"+idInputEsistente).removeClass("validateCustom vld_Custom_r");
            $("#"+idInputEsistente).val("");
            /*if($("#"+idInputNonTrovato).attr('disabled'))
            {
                $("#"+idInputNonTrovato).removeAttr('disabled');
            }*/
            //console.log("ESTERO "+classeNonTrovato+" "+idInputNonTrovato+" "+classeEsistente+" "+idInputEsistente);
        }
        else
        {
            $("."+classeNonTrovato).css("display","none");
            $("#"+idInputNonTrovato).removeClass("validateCustom vld_Custom_r");
            $("."+classeEsistente).css("display","block");
            $("#"+idInputEsistente).addClass("validateCustom vld_Custom_r");
            $("#"+idInputNonTrovato).val("");

            //("ITALIA "+classeNonTrovato+" "+idInputNonTrovato+" "+classeEsistente+" "+idInputEsistente);
        }
    }

    function VisualizzaNascondi(el){

        if(el != undefined)
            switch(el.value){
                case "1": $(".Utente").css("display","block");
                    $(".CLutente").addClass("validateCustom vld_Custom_r");
                    //$("#CF").addClass("vld_Custom_CF");
                    document.getElementById("StatoNascita").dispatchEvent(new Event('change'));
                    document.getElementById("StatoResidenza").dispatchEvent(new Event('change'));
                    $(".Trasgressore").css("display","none");
                    $(".CLtrasgressore").removeClass("validateCustom vld_Custom_r");
                    //$(".firmaTR").css("display","none");
                    break;

                case "2":
                    $(".Utente").css("display","block");
                    $(".CLutente").addClass("validateCustom vld_Custom_r");
                    //$("#CF").addClass("vld_Custom_CF");
                    document.getElementById("StatoNascita").dispatchEvent(new Event('change'));
                    document.getElementById("StatoResidenza").dispatchEvent(new Event('change'));
                    $(".Trasgressore").css("display","block");
                    $(".CLtrasgressore").addClass("validateCustom vld_Custom_r");
                    //$("#CFTrasgressore").addClass("vld_Custom_CF");
                    document.getElementById("StatoNascitaTrasgressore").dispatchEvent(new Event('change'));
                    document.getElementById("StatoResidenzaTrasgressore").dispatchEvent(new Event('change'));
                    //$(".firmaTR").css("display","block");
                    break;

                case "3": $(".Utente").css("display","block");
                    $(".CLutente").addClass("validateCustom vld_Custom_r");
                    //$("#CF").addClass("vld_Custom_CF");
                    document.getElementById("StatoNascita").dispatchEvent(new Event('change'));
                    document.getElementById("StatoResidenza").dispatchEvent(new Event('change'));
                    $(".Trasgressore").css("display","block");
                    $(".CLtrasgressore").addClass("validateCustom vld_Custom_r");
                    //$("#CFTrasgressore").addClass("vld_Custom_CF");
                    document.getElementById("StatoNascitaTrasgressore").dispatchEvent(new Event('change'));
                    document.getElementById("StatoResidenzaTrasgressore").dispatchEvent(new Event('change'));
                    //$(".firmaTR").css("display","none");
                    $(".CLtrasgressoreFirma").removeClass("validateCustom vld_Custom_r");
                    break;

                case "4": $(".Utente").css("display","none");
                    $(".CLutente").removeClass("validateCustom vld_Custom_r");
                    $(".Trasgressore").css("display","block");
                    $(".CLtrasgressore").addClass("validateCustom vld_Custom_r");
                    //$("#CFTrasgressore").addClass("vld_Custom_CF");
                    document.getElementById("StatoNascitaTrasgressore").dispatchEvent(new Event('change'));
                    document.getElementById("StatoResidenzaTrasgressore").dispatchEvent(new Event('change'));
                    //$(".firmaTR").css("display","block");
                    break;

                default: $(".Utente").css("display","none"); $(".Trasgressore").css("display","none"); break;
            }
    }

    /*function AbilitaDisabilitaTr(classe,nome,value,classe2){

        if ($("input[name='"+nome+"']:checked").val() == value) {
            if(classe2 != "")
                $("."+classe2).css("display","none");
            $("."+classe).css("display","block");
        }
        else {
            if(classe2 != "")
                $("."+classe2).css("display","block");
            $("."+classe).css("display","none");
        }
    }*/

    function AbilitaDisabilitaTr(classe,nome){

        if ($("input[name='"+nome+"']:checked").val() == "NL" || $("input[name='"+nome+"']:checked").val() == "LR") {
            $("."+classe).css("display","block");
            $(".CLditta").addClass("validateCustom vld_Custom_r");
            //$("#PIDitta").addClass("vld_Custom_PI");
            document.getElementById("SedeLR").dispatchEvent(new Event('change'));
        }
        else {
            $("."+classe).css("display","none");
            $(".CLditta").removeClass("validateCustom vld_Custom_r");
        }
    }

    var allCustom = [];

    function validateForm(field, custom = true, msg = "")
    {
        if(field === undefined) $(".error").remove();
        else
        {
            if(field.id != "")
            {
                $("#error_"+field.id).remove();
            }
            else{
                var parent = field.parentNode;
                var children = parent.children;

                for(var i = 0; i< children.length; i++)
                {
                    var arrayClass = children[i].className.split(/\s+/);
                    for(var x = 0; x<arrayClass.length; x++)
                    {
                        if(arrayClass[x] === "error")
                        {
                            children[i].remove();
                        }
                    }
                }
            }
        }
//alert("2");
        if(field === undefined){ InizializzaAttributi();}
        var rec = null;
//alert("3");
        if(field!==undefined) rec = [field];
        else rec=document.getElementsByClassName('validateCustom');
//alert("4");

        var flagOK = true;
        var flagReadOnly = false;
//alert("5 "+rec.length);
        for (var i = 0; i<rec.length; i++) {
            if(rec[i].readOnly == true)
            {
                rec[i].readOnly = false;
                flagReadOnly = true;
            }
//alert("6");
            if(field === undefined )
            {
                //alert("qui checkValidityCustom");
                var result = checkValidityCustom(rec[i].className.split(/\s+/),rec[i]);
                if(result[0]!= undefined) custom = result[0];
                if(result[1]!= undefined) msg =result[1];
                //alert(custom);
            }


            if (!rec[i].checkValidity() || !custom )
            {
                //console.log(rec[i].name);
                //console.log(rec[i]);
                //alert("false dentro check validity "+rec[i].id);
                flagOK = false;
                var message;
                if(rec[i].validationMessage == "Compila questo campo.") message="Campo obbligatorio";
                else if(msg != "") message = msg;
                else message = rec[i].validationMessage;

                if(rec[i].id=="")
                {
                    var newNode = document.createElement("span");
                    newNode.innerHTML = message;
                    newNode.style.color = "#B34F4F";
                    newNode.class = "error";
                    newNode.style.fontSize = "12px";

                    var parent = rec[i].parentNode;
                    parent.classList.add("has-error");
                    parent.appendChild(newNode);
                }
                else{
                    var parent = rec[i].parentNode;
                    parent.classList.add("has-error");
                    var labElem = null;
                    labElem = parent.getElementsByTagName("label_"+rec[i].id);

                    if(labElem.length === 0)
                    {
                        var grandParent = parent.parentNode;
                        var labElem = grandParent.getElementsByTagName("label_"+rec[i].id);
                    }

                    for(var x = 0; x < labElem.length; x++)
                    {
                        labElem[x].style.color = "#B34F4F";
                    }

                    $("#"+rec[i].id).after("<span class='error' id='error_"+rec[i].id+"' style='color: #B34F4F; font-size: 12px;'>"+message+"</span>");
                }

            }
            else{
                var parent = rec[i].parentNode;
                parent.classList.remove("has-error");

                var labElem = null;
                labElem = parent.getElementsByTagName("label_"+rec[i].id);

                if(labElem.length === 0)
                {
                    var grandParent = parent.parentNode;
                    var labElem = grandParent.getElementsByTagName("label_"+rec[i].id);
                }

                for(var x = 0; x < labElem.length; x++)
                {
                    labElem[x].style.color = "black";
                }

            }
            if(flagReadOnly)
            {
                rec[i].readOnly = true;
                flagReadOnly = false;
            }
        }
        return flagOK;
    }

    function checkValidityCustom(arrayClassi,field)
    {
        for(var i = 0; i< arrayClassi.length; i++)
        {
            for(var x = 0; x < allCustom.length; x++)
            {
                if(arrayClassi[i] == allCustom[x])
                {
                    switch(allCustom[x])
                    {
                        case "vld_Custom_CustAnno": return CustAnno(field,true);
                    }
                    //field.dispatchEvent(new Event("change"));
                }
            }

        }
        return true;

    }

    function InizializzaAttributi(){

        $('.vld_Custom_n').each(function() {
            $(this).attr('pattern','[-+]{0,1}[\t\n\v\f\r \u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000]{0,}[0-9]+');
            $(this).on("change paste input keyup", function() {
                validateForm(this);
            });
        });

        $('.vld_Custom_a').each(function() {
            $(this).attr('pattern','[a-zA-Z]+');
            $(this).on("change paste input keyup", function() {
                validateForm(this);
            });
        });

        $('.vld_Custom_tel').each(function() {
            $(this).attr('pattern','[+]{0,1}[0-9\t\n\v\f\r \u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000]+');
            $(this).on("change paste input keyup", function() {
                validateForm(this);
            });
        });

        $('.vld_Custom_mail').each(function() {
            $(this).attr('pattern','[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+[.]{1}[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]{2,}');
            $(this).on("change paste input keyup", function() {
                validateForm(this);
            });
        });

        $('.vld_Custom_d').each(function() {
            $(this).attr('pattern','[-+]{0,1}[\t\n\v\f\r \u00a0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000]{0,}[0-9.]+[.,]{0,1}[0-9]{0,2}');
            $(this).on("change paste input keyup", function() {
                validateForm(this);
            });
        });

        $('.vld_Custom_date').each(function() {
            $(this).attr('pattern','[0-9]{2}[-/]{1}[0-9]{2}[-/]{1}[0-9]{4}');
            $(this).on("change paste input keyup", function() {
                validateForm(this);
            });
        });

        $('.vld_Custom_CF').each(function() {
            $(this).attr('pattern','[a-zA-Z]{6}[0-9]{2}[abcdehlmprstABCDEHLMPRST]{1}[0-9]{2}[a-zA-Z]{1}[0-9]{3}[a-zA-Z]{1}');
            $(this).on("change paste input keyup", function() {
                validateForm(this);
            });
        });

        $('.vld_Custom_PI').each(function() {
            $(this).attr('pattern','[0-9]{11}');
            $(this).on("change paste input keyup", function() {
                validateForm(this);
            });
        });

        $('.vld_Custom_r').each(function() {
            $(this).attr('required','required');
            $(this).on("change paste input keyup", function() {
                validateForm(this);
            });
        });

        $('.vld_Custom_anno').each(function() {
            $(this).attr('pattern','[0-9]{4}$');
            $(this).on("change paste input keyup", function() {
                validateForm(this);
            });
        });

        $('.vld_Custom_CustAnno').each(function() {
            //$(this).attr('required','required');
            //var presenza = false;
            /*  for(var i = 0; i< allCustom.length; i++)
              {
                if(allCustom[i]=="vld_Custom_CustAnno")
                {
                  presenza = true;
                  break;
                }
              }*/
            allCustom[0] = "vld_Custom_CustAnno";
            //allCustom.push("vld_Custom_CustAnno");

            $(this).on("change paste input keyup", function (){

                var flagValidity = true;

                control_anno = $('#anno').val();

                var regexp = /^[0-9]{4}$/;
                flagValidity = regexp.test(control_anno);

                if(flagValidity)
                {
                    for(var y=0 ; y<num_anni.length;y++)
                    {
                        if( num_anni[y] == control_anno )
                        {
                            flagValidity = false;
                        }
                    }
                }

                if(flagValidity)
                {
                    if(control_anno <= 1900)
                        flagValidity = false;
                }
                validateForm(this,flagValidity,"Anno già inserito o non valido");
            });
        });

    }

    function CustAnno(field,flag) {

//alert();
        var flagValidity = true;

        control_anno = $('#anno').val();

        var regexp = /^[0-9]{4}$/;
        flagValidity = regexp.test(control_anno);

        if(flagValidity)
        {
            for(var y=0 ; y<num_anni.length;y++)
            {
                if( num_anni[y] == control_anno )
                {
                    flagValidity = false;
                }
            }
        }

        if(flagValidity)
        {
            if(control_anno <= 1900)
                flagValidity = false;
        }
        //validateForm(field,flagValidity,"Anno già inserito o non valido");
        var arrayRet = [flagValidity,"Anno già inserito o non valido"];
        return arrayRet;
    }

</script>



</div>
</body>
</html>
