<?php
include('_path.php');
include('_parameter.php');
//include('funzioni.php');

$conn = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME) or die('could not connect to database');

$query = "SELECT Description, Path FROM V_UserMenu  WHERE UserId=".$_SESSION['userid']." GROUP BY Description, Path;";
$menu = mysqli_query($conn, $query);
$N = mysqli_num_rows($menu);

if($_SESSION['userid']==8  || $_SESSION['userid']==111) {
	header('Location: http://77.81.236.68/gitco2/prime_entry/select_company.php');
	DIE;
}


if($N==1) {
    $link = mysqli_fetch_array($menu);
    header('Location: '.$MainPath.'/gitco2/'.$link['Path']);
}else{
	echo '
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>GITCO</title>

	<link rel="StyleSheet" href="'.CSS.'classi_semplici.css" type="text/css" media=screen>
	<link rel="StyleSheet" href="'.CSS.'jquery-ui-1.10.3.custom.css" type="text/css" media=screen>


</head>



<body class="sfondo_new_gitco">
    <table width="100%" border="0">
        <tr>
            <td>
                <div align="center">

                    <table width="760" border="0" cellspacing="1" cellpadding="1">
                        <tr>
                            <td width="123"><img src="'. IMAGES .'sarida_logo_medium.png" alt="Servizio Accertamento e Riscossione Imposte e Diritti Accessori" width="123" height="45" /></td>
                            <td width="503"><div align="center"><font color="#294A9C" size="6">Gestione Integrata Tributi Comunali</font></div></td>
                            <td width="124"><div align="right"><img src="'. IMAGES .'sarida_logo_medium.png" alt="Servizio Accertamento e Riscossione Imposte e Diritti Accessori" width="123" height="45" /></div></td>
                        </tr>
                    </table>

                    <br />

                    <table width="760" border="0" align="center" bgcolor="#E0F0FE">
                        <tr>
                            <td>
                                <div align="center">
                                    <p>
                                        <img src="'. IMAGES .'Gitco_titolo.png" width="500" height="150" alt="Sarida S.r.l. - Gestione Integrata Tributi Comunali" border="0" />
                                        <br />
                                        <br />
                                    </p>





                                    <table width="100%" border="0" align="center">';
                                        while($link = mysqli_fetch_array($menu)){
                                            echo '<tr class="pheight30">
                                                    <td class="width15"></td>
                                                    <td class="sfondo_blu text_center"><a class="link_menu" href="'.$MainPath.$link['Path'].'"><span class="font18"><b>'.$link['Description'].'</b></span></a></td>
                                                    <td class="width15"></td>
                                                </tr>';

                                        };
echo '
                                    </table>
                                    <br>
                                </div>
                            </td>
                        </tr>
                    <table>
                </div>
            </td>
        </tr>
    </table>
</body>';
}
