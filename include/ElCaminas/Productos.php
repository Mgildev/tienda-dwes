<?php
namespace ElCaminas;
use \PDO;
use \Exception;
use ElCaminas\Producto;
class Productos
{
    protected $connect;
    protected $productos = array();
    public function __construct()
    {
        global $connect;
        $this->connect = $connect;
    }
    public function getProductoById($id)
    {
      $query = "SELECT * FROM productos WHERE id = :id";
      $statement = $this->connect->prepare($query);
      $statement->bindParam(':id', $id, PDO::PARAM_INT);
      $statement->execute();
      $producto = $statement->fetch(PDO::FETCH_ASSOC);
      if (false === $producto){
         throw new Exception('El producto no existe');
      }else {
        return new Producto($producto);
      }
    }
    private function getProductos($query){
      $this->productos = array();
      $statement = $this->connect->prepare($query);
      $statement->execute();

      $result = $statement->fetchAll();

      foreach($result as $row){
          $this->productos[] =  new Producto($row);
      }
      return $this->productos;
    }
    public function getDestacados(){
      $query = " SELECT * FROM productos WHERE destacado = 1 order by fecha desc LIMIT 6";
      return $this->getProductos($query);
    }
    public function getNovedades(){
      $query = " SELECT * FROM productos order by fecha desc LIMIT 6";
      return $this->getProductos($query);
    }
    public function getRelacionados($id, $id_categoria){
      $query = " SELECT * FROM productos WHERE id_categoria = $id_categoria AND id !=  $id  LIMIT 6";
      return $this->getProductos($query);
    }

    public function getProductosByCategoria($id_categoria, $itemsPerPage, $currentPage){
      $query = " SELECT * FROM productos WHERE id_categoria = $id_categoria LIMIT $itemsPerPage OFFSET " . $itemsPerPage*($currentPage-1);
      return $this->getProductos($query);
    }

    public function getCountProductosByCategoria($id_categoria){
      $query = " SELECT COUNT(*) as cuenta FROM productos WHERE id_categoria = :id_categoria";
      $statement = $this->connect->prepare($query);
      $statement->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
      $statement->execute();
      return $statement->fetch(PDO::FETCH_ASSOC)["cuenta"];
    }
}
