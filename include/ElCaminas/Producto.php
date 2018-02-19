<?php

namespace ElCaminas;
use \PDO;
class Producto
{
    protected $connect;
    protected $id;
    protected $nombre;
    protected $descripcion;
    protected $id_categoria;
    protected $precio;
    protected $precioReal;
    protected $foto;
    protected $destacado;
    protected $descuento;
    protected $stock;
    protected $fecha;
    protected $carrusel;
    protected $url;
    public function __construct($params)
    {
        global $connect;
        $this->connect = $connect;
        if (is_array($params)){
          	foreach ($params as $key=>$param){
      				$this->$key = $param;
      			}
        }else{
          //sólo se pasa el id;
          $query = "SELECT * FROM productos WHERE id = :id";
          $statement = $this->connect->prepare($query);
          $statement->bindParam(':id', $params, PDO::PARAM_INT);
          $statement->execute();
          $producto = $statement->fetch(PDO::FETCH_ASSOC);
          foreach ($producto as $key=>$param){
            $this->$key = $param;
          }
        }
        $this->setPrecioReal();
        $this->setUrl();
    }

    public function getId(){
      return $this->id;
    }
    public function getIdCategoria(){
      return $this->id_categoria;
    }
    public function setUrl(){
      $this->url =  "./producto.php?id=" . $this->getId();
    }
    public function getUrl(){
      return $this->url;
    }
    public function setPrecioReal(){
      if ( $this->descuento > 0){
        $precioReal = $this->precio - ($this->precio * $this->descuento / 100);
      }else{
        $precioReal =  $this->precio;
      }
      $this->precioReal = $precioReal;
    }
    public function getPrecioReal(){
      return   $this->precioReal;
    }
    public function getNombre(){
      return   $this->nombre;
    }
    public function getHtmlPrecio(){
      $precioTexto = "";
      if ( $this->precio != $this->precioReal){
        $precioTexto = "<span class='text text-danger'><s>" . number_format($this->precio, 2, ',', ' ') . " €</s></span> <span class='text text-success'>" . number_format($this->precioReal , 2, ',', ' ') . "€</span>";
      }else{
        $precioTexto =  "<span class='text text-danger'>" .number_format($this->precio, 2, ',', ' ') . " €</span>";
      }
      return $precioTexto;
    }

    public function getJson(){
         return json_encode(array("HOME"=> "./", "id" => $this->id, "nombre" => $this->nombre, "foto" => $this->foto, "descripcion" =>  $this->descripcion, "precio" => $this->getHtmlPrecio()));
       }

    public function getHtml(){
      $redirect=str_replace("state=exclusive","state=normal",$_SERVER["REQUEST_URI"]);
      $str =  "<h2 class='subtitle' style='margin:0'>" . $this->nombre ."</h2>";
      $str .= "<img class='center-block img-responsive img-thumbnail' src='./basededatos/img/600_" . $this->foto . "' alt=''>";
    	$str .= "<div class='caption'>";
    	$str .= "<p>" . $this->descripcion . "</p>";
    	$str .= "</div>";
    	$str .= "<h4 class='pull-right' style='position:absolute; bottom:4px; left:4px'>". $this->getHtmlPrecio() . "</h4>";
      $str .= "<a href='./carro.php?action=add&id=" . $this->id . "&cantidad=1&redirect=". urlencode($redirect)."' class='btn btn-danger' style='position:absolute; bottom:4px; right:4px'>Comprar</a>";

      return $str;

    }
    public function getHtmlPopup(){

      $str =  "<h2 class='subtitle' style='margin:0'>" . $this->nombre ."</h2>";
      $str .= "<img class='center-block img-responsive img-thumbnail' src='./basededatos/img/600_" . $this->foto . "' alt=''>";
      $str .= "<div class='caption'>";
      $str .= "<p>" . $this->descripcion . "</p>";
      $str .= "</div>";

      return $str;

    }
    public function getThumbnailHtml(){

      $str = "<div class='col-sm-4 col-lg-4 col-md-4'>";
        $str .= "<div class='thumbnail' style='position:relative'>";
          $str .= "<a href='" .  $this->url . "'><img src='./basededatos/img/256_" . $this->foto . "' alt=''></a>";
          $str .= "<div class='caption'>";
            $str .= "<h4><a href='" .  $this->url . "'>". $this->nombre . "</a> <a class='open-modal' href='$this->url'><span style='color:#000' class='fa fa-external-link'></span></a></h4>";
            $str .= "<p>". $this->descripcion . "</p>";
          $str .= "</div>";
          $str .= "<h4 class='pull-right'>" . $this->getHtmlPrecio() . "</h4>";
          $str .= "<a href='./carro.php?action=add&id=" . $this->id . "&cantidad=1&redirect=". urlencode($_SERVER['REQUEST_URI'])."' class='btn btn-danger'>Comprar</a>";
        $str .= "</div>";
      $str .= "</div>";
      return $str;

    }
}
