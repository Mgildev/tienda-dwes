//El elemento DOM con el código HTML de la ventana
var myModal = $("#myModal");
//Encontrar el contenedor dentro de la misma
var dataContainer = $( myModal ).find( "#data-container" );
function attachModalInfo(){
  /*  En mi caso los elementos dom que cargan la ventana modal
  *   tienen el siguiente selector css
  *         a.open-modal
  */
  $( "a.open-modal" ).click(function(event) {
    event.preventDefault();
      //Busco el attributo href y le añado el state=exclusive
    var href = $( this ).attr('href');
    href = href + "&state=exclusive";
    //Llamada a ajax, simplificada sin tratamiento de errores
    var jqxhr = $.get( href, function(data) {
      //Fijo el contenido del contenedor al devuelto por ajax
      dataContainer.html(data);
      //Mostrar la ventana modal
      myModal.modal();
    })
    .fail(function() {});
  });;
}
//Inicializar los eventos
attachModalInfo();
