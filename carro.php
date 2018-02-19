<?php
  session_start();
  include("./include/funciones.php");
  $connect = connect_db();

  $title = "Plantas el Caminàs -> ";
  include("./include/header.php");
  require './include/ElCaminas/Carrito.php';
  require './include/ElCaminas/Producto.php';
  require './include/ElCaminas/Productos.php';
  use ElCaminas\Carrito;


  $carrito = new Carrito();
  $action = "view";
if (($_SERVER["REQUEST_METHOD"] == "POST")){
  //Por javascript siempre llamamos con método POST
  if (isset($_POST["action"])){
      $action = $_POST["action"];
  }
  if ($action == "add"){
    //Pero accedo al 'id' mediante GET (ya que está en la url)
    if (!$carrito->itemExists($_GET["id"])){
      //Cuando es add, siempre añadimos 1 pero sólo si no estaba ya antes en el carro
      $carrito->addItem($_GET["id"], 1);
    }
    //Siempre devolvemos en itemCount los que ya hay ahora en el carro de ese producto
    echo json_encode(array("HOME"=> "./", "itemCount"=>$carrito->getItemCount($_GET["id"]), "cuantos"=> $carrito->howMany(), "total"=> $carrito->getTotal()));
    exit();
  }elseif ($action == "update"){
      //Cuando es update (desde el botón de actualizar de la ventana modal, la cantidad es la que introduce el usuario)
      $carrito->addItem($_GET["id"], $_POST["cantidad"]);
      echo json_encode(array("HOME"=> "./", "itemCount"=>$carrito->getItemCount($_GET["id"]), "cuantos"=> $carrito->howMany(), "total"=> $carrito->getTotal()));
      exit();
  }
}else {
  //Falta comprobar qué acción: add, delete, empty
  if($_GET['action'] == "add"){
    $carrito->addItem($_GET["id"], 1);
  }
  if($_GET['action'] == "delete"){
    $carrito->deleteItem($_GET["id"], 1);
  }
  if($_GET['action'] == "empty"){
    $carrito->empty();
  }

?>
  <div class="row carro">
    <h2 class='subtitle' style='margin:0'>Carrito de la compra</h2>
    <?php  echo $carrito->toHtml();?>
    <script src="https://www.paypalobjects.com/api/checkout.js"></script>

<div id="paypal-button-container"></div>
 <script>
         paypal.Button.render({

             env: 'sandbox', // sandbox | production

             // PayPal Client IDs - replace with your own
             // Create a PayPal app: https://developer.paypal.com/developer/applications/create
             client: {
                 sandbox:    'AURtFahgo3cuV-8J35gOhzh0AhTk36fnkHRkuGs-ZBiDoRdzd4NGvRDFFvzkCqmoU3puoZ3FOyS2zkDX',
                 production: '<insert production client id>'
             },

             // Show the buyer a 'Pay Now' button in the checkout flow
             commit: true,

             // payment() is called when the button is clicked
             payment: function(data, actions) {

                 // Make a call to the REST api to create the payment
                 return actions.payment.create({
                     payment: {
                         transactions: [
                             {
                                 amount: { total:  <?php echo "'".$_SESSION['total']."'"; ?>, currency: 'EUR' }
                             }
                         ]
                     }
                 });
             },

             // onAuthorize() is called when the buyer approves the payment
             onAuthorize: function(data, actions) {
                // Make a call to the REST api to execute the payment
                return actions.payment.execute().then(function() {
                    window.alert('Pago Completado!');
                    document.location.href = 'gracias.php';
                });
              }

         }, '#paypal-button-container');

     </script>

  </div>

  <div class="row carro">
    <a class="btn btn-default" href="./carro.php?action=empty">Vaciar carro</a>
    <a class="btn btn-default" href=".<?php echo isset($_GET['redirect']) ? urldecode($_GET['redirect']) : "/index.php"; ?>">Seguir comprando</a>
    <a class="btn btn-default" href="">Comprar</a>
  </div>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Detalle del producto</h4>
      </div>
      <div class="modal-body">
        <iframe src='#' width="100%" height="600px" frameborder=0 style='padding:8px'></iframe>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="infoCarroProducto" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Producto añadido correctamente a su carrito</h4>
      </div>
      <div class="modal-body">
        <div id='data-container'>
          <div class="row">
            <div class="col-md-3">
                <img class='img-thumbnail img-responsive' style='max-width:128px' src=''>
            </div>
            <div class="col-md-9">
                <h4 class='title'></h4>
                <p class='desc'></p>
                <input type='number' min='1' id='cantidad' value=1><button class='update' class='btn'>Actualizar</button>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-4" >
              <a href="#" class="btn btn-primary">Ver carrito</a>
            </div>
            <div class="col-md-4 col-md-offset-4">
                <div class='pull-right'>
                  <b>Total carrito: <span class='total'></span> €</b>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$bottomScripts = array();
$bottomScripts[] = "modalIframeProducto.js";
include("./include/footer.php");
}
?>
