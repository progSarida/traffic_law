
<?php
include 'connection.php';
session_start();
$error = "";
$empty = false;
if(isset($_POST["submit"]))
{
    if(empty($_POST["username"]) || empty($_POST["password"]))
    {
        $empty = true;
        $error = "Username & Password should not be empty";
    }
    if(!$empty)
    {
        $username= trim($_POST['username']);
        $username = strip_tags($username);
        $username = htmlspecialchars($username);
        $password=trim($_POST['password']);
        $password=strip_tags($password);
        $password = htmlspecialchars($password);
        $hash_password = md5($password);
       
        $query="SELECT Id FROM Users WHERE Username='$username' and Password='$hash_password'";
        $result = $conn->query($query);
        $row=mysqli_fetch_object($result);

        if(mysqli_num_rows($result) == 1){
            $_SESSION['user'] = $row->Id;
            header("Location: inc/document_administration.php");
        }else{
            
            $error = "Your Username or Password was incorrect!";
        }

    }
}

?>
