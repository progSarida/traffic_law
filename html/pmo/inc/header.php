<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">  
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="../js/jquery.min.js"></script> 
    <script src="../js/bootstrap.min.js"></script>
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>
    <script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>

</head>
<body>
<nav class="navbar navbar-default navbar-static-top" style="background-color: rgb(38, 132, 177)">
    <a class="navbar-brand" href="document_administration.php">
        <h4 style="color: white"><b>Administration Panel</b></h4>
    </a>
    <div class="container">
        <div class="navbar-header">

            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>


        </div>

        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            <ul class="nav navbar-nav">
            <li>
                <a class="navbar-brand" href="category_administration.php">
                    <h4 style="color: white"><b>Categorie</b> |</h4>
                </a>
            </li>
            <li>
                <a class="navbar-brand" href="document_administration.php">
                    <h4 style="color: white"><b>Documenti</b> |</h4>
                </a>
            </li>
            <li>
            <a class="navbar-brand" href="news_administration.php">
                <h4 style="color: white"><b>Notizie</b> |</h4>
            </a>
            </li>
            <li class="dropdpwn">
            <a href="#" class="dropdown-toggle navbar-brand" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><h4 style="color: white"><b>Notizie categorie</b> |</h4></a>
                <ul class="dropdown-menu">
                    <li><a href="tipologia.php">Tipologia</a></li>
                    <li><a href="sottotipologia.php">Sotto Tipologia</a></li>
                    <li><a href="tipoatto.php">Tipo Atto</a></li>
                    <li><a href="enteemitente.php">Ente Emittente</a></li>
                    <li><a href="clasificazione.php">Clasificazione Atto</a></li>
                </ul>
            </li>
            <li>
                <a class="navbar-brand" href="help_page.php">
                    <h4 style="color: white"><b>Help</b> |</h4>
                </a>
            </li>
            </ul>
            <ul class="nav navbar-nav pull-right">
                <li><a href="logout.php"><h5 style="color: white">Logout</h5></a>  </li>
            </ul>
        </div>
    </div>
</nav>