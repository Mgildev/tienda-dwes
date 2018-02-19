//El elemento DOM con el código HTML de la máscara de cargando
var mask = $("#mask");
////El elemento dom que contiene los productos
var container = $("#data-container");
//Datos de la primera página cargados por ajax. Ver el evento popstate
var firstUrlData = null;
function attachPaginator(){
  //selector css de los enlaces de paginación
  $( ".pagination li a" ).click(function(event) {
    event.preventDefault();
    var href = $( this ).attr('href');
    //No hacerlo si no tiene href o si es la página actual.
    // <li class="active">
    //   <a href="???????>2</a>
    //  </li>
    if (("#" != href) && (!$( this ).parent().hasClass("active"))){
        //Mostrar la máscaramask.show();
        mask.show();
        //Añadir a la url el estado exclusive
        hrefExclusive = href + "&state=exclusive";
        //Crear el objeto ajax
        var jqxhr = $.get( hrefExclusive, function(data) {
          //Cuando devuelve los datos, hago un scroll animado para que la página se vea desde el principio
          //De otra forma, la página se quedaría con el scroll que tuviera en el momento de hacer la carga
          $('html, body').animate({scrollTop : 0},800);
          //Este timeout sólo lo hago porque de otra forma lo hace
          //tan rápido que no se nota el efecto. De hecho lo podéis quitar
          setTimeout(function(){
            //Actualizar el html de container con los datos devueltos
            container.html( data );
            // El navegador asocia data con este href, de tal forma que al hacer history back
            // le pasa estos datos al evento popstate
            history.pushState(data, null, href);
            //Ocultar la máscara
            mask.hide();
            //Volver a poner los eventos en el paginador
            attachPaginator();
            //Volver a poner los eventos para la ventana modal del producto
            //Hacedlo sólo si también habéis puesto la lógica para la ventana modal
            //en la página de categoria.php
            attachModalInfo();
           }, 500);

        })
        .fail(function() {
          alert( "error" );
          mask.hide();
        });

    }
  });
}
attachPaginator();
/**
Este evento va junto a pushState. Es una versión simplificada
*/
window.addEventListener('popstate', function(e) {

  $('html, body').animate({scrollTop : 0},800);
  //Cuando el usuario hace click en el botón de history, el navegador
  //pasa la información que previamente hemos añadido en pushState
  if (e.state !== null){
    //La información está ya almacenada previamente en pushState
    container.html( e.state );
  }else{
    //La primera carga de la página categorias.php no la hacemos por ajax.
    //Es por eso que la tengo que recargar ahora. Pero sólo lo voy a hacer una vez.
    //Los datos devueltos los almacenaré para usarlos en la siguiente llamada al envento
    if (firstUrlData === null){
      mask.show();
      hrefExclusive = document.location.href + "&state=exclusive";
      var jqxhr = $.get( hrefExclusive, function(data) {
        //Añadir los datos para no tener que volver a pedirlos
        firstUrlData = data;
        container.html( data );
        mask.hide();
        attachPaginator();
        attachModalInfo();
      })
      .fail(function() {
        alert( "error" );
        mask.hide();
      });
    }else{
      container.html( firstUrlData );
      attachModalInfo();
      attachPaginator();
    }
  }
});
