//El elemento DOM con el código HTML de la ventana
var myModal = $("#myModal");
//Encontrar el iframe dentro de la misma
var iframe = $( myModal ).find( "iframe" );
function attachModal(){
  /*  En mi caso los elementos dom que cargan la ventana modal
  *   tienen el siguiente selector css
  *         a.open-modal
  */
  $( "a.open-modal" ).click(function(event) {
    event.preventDefault();
    //Busco el attributo href y le añado el state=popup
    var href = $( this ).attr('href') + "&state=popup";
    //Fijar el atributo src del iframe
    iframe.attr("src", href);
    //Mostrar la ventana modal
    myModal.modal();
  });;
}
//Inicializar los eventos
attachModal();
