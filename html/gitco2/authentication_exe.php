<?php
require_once('_path.php');
require_once(INC."/parameter.php");
require_once(CLS."/cls_db.php");
require_once(INC."/function.php");

$rs= new CLS_DB();
$rs->SetCharset('utf8');

$user = addslashes($_REQUEST['user']);
$pass = $_REQUEST['pass'];

$rs_Login = $rs->Select("sarida.User", "UserName='".$user."' and Password='".md5($pass)."'");

if(mysqli_num_rows($rs_Login)>0) {
    $r_Login = $rs->getArrayLine($rs_Login);
    $ip = isset($_SERVER['HTTP_CLIENT_IP'])?$_SERVER['HTTP_CLIENT_IP']:isset($_SERVER['HTTP_X_FORWARDE‌​D_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];

    //TELEGRAM 
    $token = "552795312:AAF6CG15tmhOgpYrBFUdkDvoO12R6ILUiSs";
    //$chatIds = array("304222168","274038819"); // AND SOME MORE
    $chatIds = array(
    //    "304222168",
        "155176790"
    );
    foreach($chatIds as $chatId) {
        // Send Message To chat id
        $data = [
            'text' => $ip . ' - login utente ' . $user,
            'chat_id' => $chatId
        ];
    
        file_get_contents("https://api.telegram.org/bot$token/sendMessage?" . http_build_query($data));
    }

    $aUpdate = array(
        array('field' => 'LoginDate', 'selector' => 'value', 'type' => 'date', 'value' => date('Y-m-d'), 'settype' => 'date'),
        array('field' => 'LoginTime', 'selector' => 'value', 'type' => 'time', 'value' => date('H:i'), 'settype' => 'time'),
    );
    $rs->Update('sarida.User', $aUpdate, "Id=" . $r_Login['Id']);

    $_SESSION['Message']			= "";
    $_SESSION['Documentation'] 		= "";
    $_SESSION['username'] 			= $r_Login['UserName'];
    $_SESSION['usercity'] 			= $r_Login['CityId'];
    $_SESSION['userid'] 			= $r_Login['Id'];
    $_SESSION['usertype'] 			= $r_Login['UserType'];
    $_SESSION['controllerid'] 		= $r_Login['ControllerId'];
    $_SESSION['userlevel'] 			= $r_Login['UserLevel'];
    $_SESSION['UserMenuType'] 		= $r_Login['UserMenuType'];

    $a_PasswordDate = explode ("-", $r_Login['PasswordDate']);
    $n_PasswordDay =  90 - floor( (strtotime(date("m/d/Y")) - strtotime($a_PasswordDate[1]."/".$a_PasswordDate[2]."/".$a_PasswordDate[0])) / 86400 );

    $_SESSION['PasswordDay'] 		= $n_PasswordDay;

    //CITIES YEARS
    $cities = $rs->SelectQuery("SELECT CityId, CityTitle, CityYear, MainMenuId FROM sarida.V_UserCity WHERE UserId=".$_SESSION['userid']." ORDER BY MainMenuId ASC, CityId ASC, CityYear DESC;");
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
    
    //RULE TYPES
    $arrayRuleType = array();
    $ruletypes = $rs->getResults($rs->SelectQuery("SELECT Id, CityId FROM RuleType WHERE CityId != ''"));
    foreach($ruletypes as $ruletype){
        $arrayRuleType[$ruletype['CityId']][] = $ruletype['Id'];
    }

    //PAGES
    $pages = $rs->SelectQuery("SELECT app.LinkPage AS LinkPage, TitleMenu, TitleSubMenu, app.MainMenuId AS MainMenuId, Title FROM sarida.V_UserApplication as app left join sarida.V_UserPage AS page on app.LinkPage = page.LinkPage AND app.MainMenuId = page.MainMenuId AND app.UserId = page.UserId WHERE app.UserId=".$_SESSION['userid']." ORDER BY app.MainMenuId ASC;");
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
    
    $_SESSION['CityArray'] = $arrayCity;
    $_SESSION['YearArray'] = $arrayYear;
    $_SESSION['RuleTypeArray'] = $arrayRuleType;
    $_SESSION['PageArray'] = $arrayPage;
    
    //header("location: user_mainmenu.php");
    echo '<script>window.location.href="user_mainmenu.php";</script>';
}
else
{
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
