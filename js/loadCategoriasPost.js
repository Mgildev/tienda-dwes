//El elemento del header que muestra la cantidad de productoss
var cuantosEl = $(".nav.navbar-nav.navbar-right .badge");
//El elemento dom de la máscara
var mask = $("#mask");
//El elemento dom con la información del producto añadido al carro
var infoCarro = $("#infoCarroProducto");
function attachCarro(){
  //selector css del botón comprar del producto
  $( ".thumbnail .btn.btn-danger" ).click(function(event) {
    event.preventDefault();
    mask.show();
    //Encontrar el elemeto con class 'thumbnail' que contiene el enlace clicado
    var thumbnail = $( this ).parent();
    while (!thumbnail.hasClass("thumbnail")){
      thumbnail.parent();
    }
    //Referencia a la url carro
    var href = $( this ).attr('href');
    //Siempre que lo hago siempre añado 1 al carro. Ver carro.php para una explicación detallada
    var jqxhr = $.post( href, { action: "add", cantidad: 1 }, function(data) {
        alert(data);
        jData = JSON.parse(data);
        //Actualizar el contador de items del header
        cuantosEl.html(jData.cuantos);
        //Actualizar los datos de la ventana modal
        infoCarro.find("#data-container .total").html(jData.total);
        infoCarro.find("#data-container .title").html(thumbnail.find(".caption a").html());
        infoCarro.find("#data-container .desc").html(thumbnail.find(".caption p").html());
        infoCarro.find("#data-container a").attr("href", jData.HOME + "carro.php?action=view&redirect="
        + encodeURIComponent(document.location.href));
        infoCarro.find("#data-container img").attr("src", thumbnail.find("img").attr("src"));
        infoCarro.find("#data-container #cantidad").val(jData.itemCount);
        //Poner un listener en el evento click del botón Actualizar.
        infoCarro.find("#data-container .update").click(function(event){
            event.preventDefault();
            //Hacer un post con action = 'update' y la cantidad introducida por el usuario
            var actualizar = $.post( href, { action: "update",
                  cantidad:  infoCarro.find("#data-container #cantidad").val() }, function(data) {
                    jData = JSON.parse(data);
                    //Actualizar los datos
                    infoCarro.find("#data-container .total").html(jData.total);
                    cuantosEl.html(jData.cuantos);
              }
            );
          }
        );
        //Ocultar la máscara y mostrar la información
        setTimeout(function(){
          mask.hide();
          infoCarro.modal();
        }, 500);
      }
    );
  });
}
attachCarro();
