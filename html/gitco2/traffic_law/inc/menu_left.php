<body>
<?php

$str_UserClass = 'title="'.$_SESSION['PasswordDay'].' giorni al cambio password" ';

if($_SESSION['PasswordDay']>30) $str_UserClass .= 'style="color: #3c763d;"';
elseif($_SESSION['PasswordDay']<7) $str_UserClass .= 'style="color: #C43A3A;"';
else $str_UserClass .= 'style="color:#f7ecb5;"';


$ApplicationMenu = "";
$ApplicationSubMenu = "";
$n_ContApplicationMenu = 0;

$str_MenuPage = curPageName();
$b_FindPage = ($str_MenuPage=="index.php") ? true : false;

$rs_Menu = $rs->Select(MAIN_DB.".V_UserApplication", "UserId=".$_SESSION['userid']." AND MainMenuId=".MENU_ID);

$str_Menu =
'
<div id="div_menu_left">
    <div id="menu_left" style="float:left;width:248px">
        <a class="navbar-brand" style="color:#fff;margin-left:1rem;" href="index.php">
            <i class="fa fa-home"></i>
        </a>
        <ul class="tree" style="margin-top:5rem;">

';


while($r_Menu = mysqli_fetch_array($rs_Menu)){
    $ApplicationSubMenu = $r_Menu['TitleSubMenu'];
    if($r_Menu['TitleMenu']!=$ApplicationMenu){
        $n_ContApplicationMenu++;
        if($ApplicationMenu!=""){
            $str_Menu .= '	
                </ul>
            </li>
            ';
        }
        $ApplicationMenu = $r_Menu['TitleMenu'];



        $str_Menu .= '
            <li>
                <input type="checkbox" id="c'.$n_ContApplicationMenu.'" />   
                <label class="tree_label" for="c'.$n_ContApplicationMenu.'">'.$ApplicationMenu.'</label>
                    <ul>
                        <li>
                            <span class="tree_label" style="width:200px;">
                                <a href="'.$r_Menu['LinkPage'].'?PageTitle='.$r_Menu['TitleMenu'].'/'.$ApplicationSubMenu.'">
                                    '. $ApplicationSubMenu .'
                                </a>
                            </span>
                        </li>
        ';

    }else{
        $str_Menu .= '
            <li>
                <span class="tree_label" style="width:200px;">
                    <a href="'.$r_Menu['LinkPage'].'?PageTitle='.$r_Menu['TitleMenu'].'/'.$ApplicationSubMenu.'">
                        '. $ApplicationSubMenu .'
                    </a>
                </span>
            </li>
        ';
    }

}
$str_Menu .=
'	
                </ul>
            </li>
        </ul>
    </div>
    <div id="menu_left_r">
        <img src="'.$_SESSION['blazon'].'" style="height:4.5rem;width:3.7rem; position:absolute; right:0.8rem; top:0.8rem; ">
    </div>
</div>
    



';
echo $str_Menu;



$aUserButton = array();

$UserPages = $rs->Select(MAIN_DB.".V_UserPage", "MainMenuId=".MENU_ID." AND UserId=".$_SESSION['userid']." AND LinkPage='".$str_MenuPage."';");



while($UserPage = mysqli_fetch_array($UserPages)){
	$aUserButton[] = $UserPage['Title'];

}
$aLan = unserialize(IMG_LANGUAGE);


/*
if(!$b_FindPage){
    $_SESSION['Message'] = "Pagina non accessibile per l'utente ".$_SESSION['username'];
    echo '<script>window.location.href="select_customer.php";</script>';
    DIE;
}
*/


require("page/title_left.php");
