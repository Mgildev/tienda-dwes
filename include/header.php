<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo $title; ?></title>

    <!-- Bootstrap Core CSS -->
    <link href="<?php echo CSSPATH; ?>bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?php echo CSSPATH; ?>shop-homepage.css" rel="stylesheet">
	  <link href="<?php echo FONTAWESOMEPATH; ?>css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
  <div id="mask" style="position: fixed; width: 100%; height: 100%; top: 0px; left: 0px; z-index: 1040;
   background-color: white;  opacity: .9; display:none">
     <div class="alert alert-info" role="alert" style='position: absolute;  top: 30%;  left: 50%;  transform: translate(-30%, -50%);'>
         <img src='<?php echo CSSPATH;?>/loading_animation.gif'> <br>Cargando ...
     </div>
   </div>
    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="./index.php">Plantas El Caminás</a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="#">Acerca</a>
                    </li>
                    <li>
                        <a href="#">Servicios</a>
                    </li>
                    <li>
                        <a href="#">Contacto</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>

    <!-- Page Content -->
    <div class="container">

        <div class="row">
            <div class="col-md-3">
                  <div class="list-group">
                    <a class="list-group-item text-center active" data-remote="true" href="#" id="categoria_0">
          					  Todas las Categorías
          					</a>
                    <?php


                    $query = " SELECT * FROM categorias WHERE id_padre IS NULL ORDER BY nombre";

                    $statement = $connect->prepare($query);

                    $statement->execute();

                    $count = $statement->rowCount();

                    if($count > 0){
                      $result = $statement->fetchAll();
                      foreach($result as $row){
                        $query = " SELECT * FROM categorias WHERE id_padre = " . $row["id"];

                        $statement = $connect->prepare($query);

                        $statement->execute();

                        $countChildren = $statement->rowCount();
                        $childText = $data = "";
                        $href = "./categoria.php?id=" . $row["id"];
                        if($countChildren > 0){
                          $childText = "<span class='menu-ico-collapse'><i class='fa fa-chevron-down'></i></span>";
                          $data = " data-toggle='collapse'";
                          $data .= " data-parent='#sub_categoria_" . $row["id"] . "'";
                          $href = "#sub_categoria_" . $row["id"] ;
                        }
                    ?>
                        <a class="list-group-item" data-remote="true" <?php echo $data; ?> href="<?php echo $href; ?>" id="categoria_<?php echo $row["id"]; ?>" style="padding-left: 25px;">
              					  <span class="fa <?php echo $row["icon"]; ?> fa-lg fa-fw"></span>
              					  <span style="margin-left: 25px;"><?php echo $row["nombre"]; ?></span>
                          <?php echo $childText; ?>
              					</a>
                    <?php
                        if($countChildren  > 0){
                          echo "<div class='collapse list-group-submenu' id='sub_categoria_" .  $row["id"] ."'>";
                          $children = $statement->fetchAll();
                          foreach($children as $child){
                    ?>
                            <a href="./categoria.php?id=<?php echo $child["id"];?>" class="list-group-item sub-item" data-parent="#sub_categoria_<?php echo $row["id"]; ?>" style="padding-left: 78px;"><span class="fa <?php echo $child["icon"]; ?> fa-lg fa-fw"></span><?php echo $child["nombre"]; ?></a>
                    <?php
                          }
                          echo "</div>";
                        }
                      }
                    }
                    ?>
				         </div>
      </div>

            <div class="col-md-9">
