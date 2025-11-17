<?php
include('_path.php');
include('inc/parameter.php');
include(CLS."/cls_db.php");

$rs= new CLS_DB();

$user = addslashes($_REQUEST['user']);
$pass = addslashes($_REQUEST['pass']);


$rs_Login = $rs->SelectQuery("SELECT * FROM `User` WHERE UserName='".$user."' and Password='".md5($pass)."';");




//TELEGRAM 
$token = "552795312:AAF6CG15tmhOgpYrBFUdkDvoO12R6ILUiSs";
$chatIds = array("304222168");




if(mysqli_num_rows($rs_Login)>0) {

	
	foreach($chatIds as $chatId) {

        $data = [
            'text' => 'Correct user login ' . $user,
            'chat_id' => $chatId
        ];

    }
        file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data) );



        $r_Login = mysqli_fetch_array($rs_Login);


        $d_LoginDate = date('Y-m-d');
        $t_LoginTime = date('H:i');

        $aUpdate = array(
            array('field' => 'LoginDate', 'selector' => 'value', 'type' => 'date', 'value' => $d_LoginDate, 'settype' => 'date'),
            array('field' => 'LoginTime', 'selector' => 'value', 'type' => 'time', 'value' => $t_LoginTime, 'settype' => 'time'),
        );
        $rs->Update('User', $aUpdate, "Id=" . $r_Login['Id']);

        $_SESSION['Message']			= "";
        $_SESSION['Documentation'] 		= "";
        $_SESSION['username'] 			= $r_Login['UserName'];
        $_SESSION['userid'] 			= $r_Login['Id'];
        $_SESSION['usertype'] 			= $r_Login['UserType'];
        $_SESSION['controllerid'] 		= $r_Login['ControllerId'];
        $_SESSION['userlevel'] 			= $r_Login['UserLevel'];
        $_SESSION['UserMenuType'] 		= $r_Login['UserMenuType'];


        $a_PasswordDate = explode ("-", $r_Login['PasswordDate']);


        $n_PasswordDay =  90 - floor( (strtotime(date("m/d/Y")) - strtotime($a_PasswordDate[1]."/".$a_PasswordDate[2]."/".$a_PasswordDate[0])) / 86400 );

        $_SESSION['PasswordDay'] 		= $n_PasswordDay;






        //CITIES YEARS
        $cities = $rs->SelectQuery("SELECT CityId, CityTitle, CityYear, MainMenuId FROM V_UserCity WHERE UserId=".$_SESSION['userid']." ORDER BY MainMenuId ASC, CityId ASC, CityYear DESC;");
        $tempCity = "";
        $tempMenuId = "";
        $countCity = 0;
        $arrayCity = array();
        $arrayYear = array();
        while($city = mysqli_fetch_array($cities)){

            $arrayYear[$city['MainMenuId']][$city['CityId']][]= $city['CityYear'];

            if($tempMenuId!=$city['MainMenuId'] && $tempMenuId!=""){
                $countCity = 0;
                $tempCity = "";
            }

            if($tempCity!=$city['CityId']){
                if($tempCity!="" && $tempMenuId!="")
                    $countCity++;

                $arrayCity[$city['MainMenuId']][$countCity]['CityId'] = $city['CityId'];
                $arrayCity[$city['MainMenuId']][$countCity]['CityTitle'] = $city['CityTitle'];
            }

            $tempCity = $city['CityId'];
            $tempMenuId = $city['MainMenuId'];

        }

        if(isset($arrayCity))
            $_SESSION['CityArray'] = $arrayCity;
        if(isset($arrayYear))
            $_SESSION['YearArray'] = $arrayYear;


        //PAGES
        $pages = $rs->SelectQuery("SELECT app.LinkPage AS LinkPage, TitleMenu, TitleSubMenu, app.MainMenuId AS MainMenuId, Title FROM V_UserApplication as app left join V_UserPage AS page on app.LinkPage = page.LinkPage AND app.MainMenuId = page.MainMenuId AND app.UserId = page.UserId WHERE app.UserId=".$_SESSION['userid']." ORDER BY app.MainMenuId ASC;");
        $tempPage = "";
        $countPage = 0;
        $arrayPage = array();
        while($page = mysqli_fetch_array($pages)){

            $pageExplode = explode(".",$page['LinkPage']);
            $pageName = $pageExplode[0];

            if($tempPage!=$pageName){
                if($tempPage!="")
                    $countPage++;

                $arrayPage[$page['MainMenuId']][$countPage]['Name']=$page['LinkPage'];
                $arrayPage[$page['MainMenuId']][$countPage]['TitleMenu']=$page['TitleMenu'];
                $arrayPage[$page['MainMenuId']][$countPage]['TitleSubMenu']=$page['TitleSubMenu'];
                $arrayPage[$page['MainMenuId']][$countPage]['Application'][]=$pageName."_exe.php";
            }

            if($page['Title']!="") {
                $arrayPage[$page['MainMenuId']][$countPage]['Application'][]=$pageName."_".$page['Title'].".php";
                $arrayPage[$page['MainMenuId']][$countPage]['Application'][]=$pageName."_".$page['Title']."_exe.php";
            }


            $tempPage=$pageName;
        }
        if(isset($arrayPage))
            $_SESSION['PageArray'] = $arrayPage;

        echo '
    <script>
    window.location.href="mainmenu.php";
    </script>';


    }
else
{



	foreach($chatIds as $chatId) {

        $data = [
            'text' => 'Wrong user login ' . $user,
            'chat_id' => $chatId
        ];
    }

        file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data) );



        if (isset($_SESSION['count_theip']))	{	$_SESSION['count_theip']++;		}
        else									{	$_SESSION['count_theip'] = 1;	}

        if ($_SESSION['count_theip'] > 3)
            echo "<script>alert('Nome utente o Password errati. Numero massimo di tentativi esaurito'); self.close();</script>";
        else
            echo "<script>alert('Nome utente o Password errati.'); history.back();</script>";

    }
?>
</body>
</html>
