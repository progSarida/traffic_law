<?php include 'inc/controller_login.php';?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="#">

    <title>Login</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" crossorigin="anonymous">

    <link href="css/login.css" rel="stylesheet">
  </head>

  <body>
    <form class="form-signin" action="" method="post">
      <div class="text mb-4">
        <h3>Login</h3>
      </div>
      <?php echo $error;?>
      <div class="input-group form-group">
          <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-user"></i></span>
          </div>
          <input type="text" class="form-control" placeholder="username" name="username" value="<?php echo $username;?>">
          
      </div>
      <div class="input-group form-group">
          <div class="input-group-prepend">
              <span class="input-group-text"><i class="fas fa-key"></i></span>
          </div>
          <input type="password" name="password" class="form-control" placeholder="password">
      </div>
      <input type="submit" name="submit" value="Login" class="btn btn-primary">
      
    </form>

  </body>
</html>