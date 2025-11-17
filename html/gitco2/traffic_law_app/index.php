
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SaridApp</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap/css/bootstrap.min.css" rel="stylesheet">



    <!-- Custom styles for this template -->
    <link href="css/signin.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/html5shiv@3.7.3/dist/html5shiv.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/respond.js@1.4.2/dest/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="container">

    <form class="form-signin" method="post" action="authentication_exe.php">
        <h2 class="form-signin-heading">Please sign in</h2>
        <label for="usr" class="sr-only">Username</label>
        <input type="username" id="usr" name="usr" class="form-control" placeholder="Username" required autofocus>
        <label for="psw" class="sr-only">Password</label>
        <input type="password" id="psw" name="psw" class="form-control" placeholder="Password" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Log in</button>
    </form>

</div>
</body>
</html>