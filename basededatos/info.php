<?php
$pageheader = "Formulario completo con Base de Datos"; 
include("./include/header.php");
?>
<h2>Funcionamiento básico</h2>
<p>Siempre que se accede a una página dinámica debemos controlar cómo se debe mostrar esta dependiendo de una lógica de negocio concreta que se debe procesar antes de mostrarla.<br> Por ejemplo, en el caso del mantenimiento de una tabla, la página será distinta si estamos modificando un registro o lo estamos añadiendo. <br>
Vamos a verlo con un ejemplo concreto: mantenimiento de una tabla de categorías.
</p>
<pre>
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
  `id_padre` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`) USING BTREE,
  KEY `categorias_ibfk_1` (`id_padre`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;
ALTER TABLE `categorias`
  ADD CONSTRAINT `categorias_ibfk_1` FOREIGN KEY (`id_padre`) REFERENCES `categorias` (`id`);
</pre>
<h2>Controlador</h2>
<p>El modo de visualización se gestiona dentro de un controlador.<br>
Cuando se pide una página por GET, le pasamos un parámetro <code>view_mode</code>, que puede tener dos valores <code>edit</code> o <code>add_new</code>. Si es <code>edit</code>, mostramos el formulario de modificación; si es <code>add_new</code>, mostramos el formulario de añadir. Existe un modo que no se controla mediante un parámetro en la url y que usamos internamente para indicar que no queremos mostrar el formulario. Este tiene como valor <code>show_message</code>. Se usa para mostrar información, por ejemplo al eliminar una categoría. Además, este parámetro puede variar dentro de la lógica del <a href="#acciones">procesado de las acciones</a>
</p>
<h4>edit</h4>
<img src='./img/modificar.png' class="img-responsive center-block img-thumbnail" >
<h4>add_new</h4>
<img src='./img/nueva.png' class="img-responsive center-block img-thumbnail" >
<h4>show_message</h4>
<img src='./img/showMessage.png' class="img-responsive center-block img-thumbnail" >
<p>Dependiendo del modo de visualización, se controlan también las acciones que puede realizar el usuario con el formulario: <code>insert</code>, <code>update</code> o <code>delete</code>. Estas acciones se definen en los botones <code>submit</code> del formulario.
En el caso que el formulario esté en modo <code>edit</code>, existen dos valores de acción: <code>update</code> y <code>delete</code>. Si es <code>add_new</code> la única acción posible es <code>insert</code>
 </p>
<h4>view_mode igual a <code>edit</code></h4>
<pre>
&lt;button type="submit" name="update" class="btn btn-primary"&gt;Guardar&lt;/button&gt;
&lt;button type="submit" name="delete" class="btn btn-primary"&gt;Eliminar&lt;/button&gt;
</pre>
<p>
	Además, se muestra un enlace para Añadir (es un enlace y no un submit porque no va a modificar la base de datos)
</p>
<pre>
&lt;a class="btn btn-default" href="categorias.php?view_mode=add_new"&gt;Nueva&lt;/a&gt;
</pre>

<h4>view_mode igual a <code>add_new</code></h4>
<pre>
&lt;button type="submit" name="insert" class="btn btn-primary"&gt;Guardar&lt;/button&gt;
</pre>
<h2 id='acciones'>Procesado de las acciones</h2>
<p>
	Ahora que ya hemos mostrado el formulario hay que implementar las acciones. Recordad que todas las acciones que supongan una modificación en el estado de la base de datos se hacen mediante post. Por tanto, cuando el método sea POST, hemos de implementar las acciones y dejar el formulario en uno de los modos de visualización: <code>edit</code>, <code>add_new</code> o <code>show_message</code>. 
</p>
<p>
En el procesado de las acciones, se puede alterar el valor de <code>view_mode</code>. Por ejemplo, si <code>view_mode</code> es <var>add_new</var>, se debe pasar a <var>edit</var>, en el caso que se haya podido insertar la categoría correctamente porque ahora el formulario debe mostrar la categoría recién insertada. A este proceso se le llama <strong>transición entre estados</strong>
</p>
<h2>Transiciones de estados</h2>
Además de la transición de estado vista antes, existen otras posibles transiciones:
<ul>Transiciones para el modo de visualización
	<li>Si no se pasa este parámetro en la URL, este se convierte en <code>edit</code></li>
	<li>Si es <code>edit</code>, se pasa a <code>add_new</code> cuando no hay ningún registro en la base de datos</li>
	<li>Si es <code>edit</code>, se pasa a <code>show_message</code> cuando el elemento no existe en la base de datos</li>
	<li>Cuando la acción es <code>insert</code>, se pasa a <code>edit</code> una vez se ha insertado el elemento en la BD</li>
	<li>Cuando la acción es <code>delete</code>, se pasa a <code>show_message</code> una vez se ha eliminado el elemento en la BD</li>
</ul>
<ul>Transiciones para las acciones
	<li>Si la acción es <code>delete</code>, se pasa a <code>update</code>, si se ha producido algún error</li>
	<li>Si la acción es <code>insert</code>, se pasa a <code>update</code>, si <b>no</b> se ha producido ningún error</li>
</ul>
<h2>Modo <code>edit</code></h2>
Para poder editar las categorías existentes se crea un listado con todas y un enlace a la página de edición.
<pre>
&lt;a href="categorias.php?id=57&amp;view_mode=edit"&gt;Sobremesa&lt;/a&gt;
</pre>
<img src='./img/completa.png' class="img-responsive center-block img-thumbnail" >
<h4>Código fuente <code>categorias.php</code></h4>
<?php
	show_source("categorias.php");
	include("./include/footer.php");
?> 

