<?php
include("config.php");
include("./include/funciones.php");
/*
	******************************** PROCESO Y MODELO ***************************************
	Aquí empieza la parte de proceso de la página y de conexión con el modelo de la bd

	Como podéis observar en esta parte NO se escribe nada de HTML!!!

	Esto es así porque en esta parte, podemos iniciar sesiones, hacer redirecciones, etc. y para ello es obligatorio
	no haber producido salida HTML

	De hecho, toda esta parte podría estar perfectamente en otro script php. Sólo está aquí para que os sea más sencillo
*/
function sanitize_input($data) {
/*
	La variable $db se define fuera de esta función. Para no ir pasando la conexión a la bd en cada función se usa
	global $db;  que indica que esta variable tiene ámbito (scope) global y está definida en otra parte
*/
global $db;
	$data = trim($data);
	//Quitar las comillas escapadas \' y \ ""
	$data = stripslashes($data);
	//Prevenir la introducción de scripts en los campos
	$data = htmlspecialchars($data);

	return mysqli_real_escape_string($db, $data);
}
function connect_db(){

    //Conexión con la bd
    $db = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$db) {
        echo "Error: No se pudo conectar a MySQL." . PHP_EOL;
        echo "errno de depuración: " . mysqli_connect_errno() . PHP_EOL;
        echo "error de depuración: " . mysqli_connect_error() . PHP_EOL;
        exit;
    }
    //IMPORTANTE. Siempre que os conectéis fijad la codificación de caracteres a utf8
    mysqli_query($db, "SET NAMES 'utf8'");
    return $db;
}

	/* Este array lo uso para mostrar la información del Method, action, view_mode y parámetros
		Solo es para pruebas
	*/
	$debug = array();
	array_push($debug, "Method: " . $_SERVER["REQUEST_METHOD"]);

	// Conectar con la base de datos
	$db = connect_db();

	//Inicializar variables (SIEMPRE!!!)
	$id = -1; /* Así identificamos el valor NULL, con un valor que NUNCA pueda tener ese campo */
	$id_categoria = -1;
	$nombre = "";
	$nombreErr = false;
	$descripcion = "";
	$descripcionErr = false;
	$precio = 0;
	$precioErr = false;

	$stock = 0;
	$descuento = 0;
	$destacado = 0;
	$foto = "";
	$carrusel = "";
	$errors = array();
	$info = array();
	$error = false;
	if (($_SERVER["REQUEST_METHOD"] == "POST")){
		/*
			Recordad que todas las opciones que implican modificación de BD se hacen con post
			Las posibles opciones son:
				update, delete e insert

			Dependiendo del método uno de los `button submit` se llama de una forma u otra (mirad el form)
			De esta forma sabemos qué acción ha realizado el usuario
		*/
		if (isset($_POST['update'])) {
			$action = "update";
		}else if (isset($_POST['delete'])) {
			$action = "delete";
		}else if (isset($_POST['insert'])) {
			$action = "insert";
		}

		//Siempre estamos en view_mode edit al hacer post, a no ser que se cambie por show_message (ver más abajo la explicación)
		$view_mode = "edit";
		switch ($action) {
		case "update":
			//Hacemos comprobaciones
			$id = sanitize_input($_POST['id']);
			$nombre = sanitize_input($_POST['nombre']);
			$descripcion = sanitize_input($_POST['descripcion']);
			$precio = sanitize_input($_POST['precio']);
			$stock = sanitize_input($_POST['stock']);
			$descuento = sanitize_input($_POST['descuento']);
			$id_categoria = sanitize_input($_POST['id_categoria']);
			$foto_actual = sanitize_input($_POST['foto_actual']);
			$carrusel_actual = sanitize_input($_POST['carrusel_actual']);

			if (isset($_POST['destacado'])){
				$destacado  = 1;
			}
			if (empty($nombre)) {
				array_push($errors, "El nombre del producto es obligatorio");
				$nombreErr = true;
			}
			if (empty($descripcion)) {
				array_push($errors, "La descripción del producto es obligatoria");
				$descripcionErr = true;
			}
			if (empty($precio)) {
				array_push($errors, "El precio del producto es obligatorio");
				$precioErr = true;
			}

			if (empty($id_categoria)) {
				array_push($errors, "La categoría del producto es obligatoria");
			}

			if (count($errors) == 0){
				$foto = getFoto($errors);
				// Si no han subido ninguna foto, dejamos la actual (está en un campo hidden)
				if (empty($foto))
					$foto = $foto_actual;
			}
			if (count($errors) == 0){
				$carrusel = getCarrusel($errors);
				// Si no han subido ningun carrusel, dejamos el actual (está en un campo hidden)
				if (empty($carrusel))
					$carrusel = $carrusel_actual;
			}
			if (count($errors) == 0) {
				//todo bien
				$query = "UPDATE productos set nombre = '$nombre', id_categoria = $id_categoria, descripcion = '$descripcion',";
				$query .= " precio = $precio,  ";
				$query .= " descuento = $descuento,  ";
				$query .= " stock = $stock, ";
				$query .= " destacado = $destacado, ";
				$query .= " foto = '$foto', ";
				$query .= " carrusel = '$carrusel' ";
				$query .= " where id=$id";

				if (!mysqli_query($db, $query)){
					// Si se produce algún error, informamos
					array_push($errors, mysqli_error($db));
				}

				/* Sólo pruebas */
				array_push($debug, "Query: " . $query);
			}
			break;
		case "delete":
			$id = sanitize_input($_POST['id']);
			$query = "DELETE FROM productos where id=$id";
			mysqli_query($db, $query);
			if (!mysqli_query($db, $query)){

				//IMPORTANTE: Si se produce algún error, pasamos a action update
				array_push($errors, mysqli_error($db));
				$action = "update";
				$nombre = sanitize_input($_POST['nombre']);
				$descripcion = sanitize_input($_POST['descripcion']);
				$precio = sanitize_input($_POST['precio']);
				$stock = sanitize_input($_POST['stock']);
				$descuento = sanitize_input($_POST['descuento']);
				$id_categoria = sanitize_input($_POST['id_categoria']);
				$color = sanitize_input($_POST['color']);
				if (isset($_POST['destacado'])){
					$destacado  = 1;
				}
				$error = true;
			}else{
				//Cuando va bien sólo muestro un mensaje. Mirad más abajo
				$view_mode = "show_message";
			}
			/* Sólo pruebas */
			array_push($debug, "Query: " . $query);

			break;
		case "insert":
			$id = "";
			$nombre = sanitize_input($_POST['nombre']);
			$descripcion = sanitize_input($_POST['descripcion']);
			$precio = sanitize_input($_POST['precio']);
			$id_categoria = sanitize_input($_POST['id_categoria']);
			$stock = sanitize_input($_POST['stock']);
			$descuento = sanitize_input($_POST['descuento']);
			if (isset($_POST['destacado'])){
				$destacado  = 1;
			}

			if (empty($nombre)) {
				array_push($errors, "El nombre del producto es obligatorio");
				$nombreErr = true;
			}
			if (empty($descripcion)) {
				array_push($errors, "La descripción del producto es obligatoria");
				$descripcionErr = true;
			}
			if (empty($precio)) {
				array_push($errors, "El precio del producto es obligatorio");
				$precioErr = true;
			}

			if (empty($id_categoria)) {
				array_push($errors, "La categoría del producto es obligatoria");
			}


			if (count($errors) == 0)
				$foto = getFoto($errors);

			if (count($errors) == 0){
				$carrusel = getCarrusel($errors);
			}
			if (count($errors) == 0) {
				$query = "INSERT INTO productos (nombre, descripcion, precio, id_categoria, stock, descuento, destacado, foto, carrusel)";
				$query .= "  VALUES('$nombre', '$descripcion', $precio, $id_categoria, $stock, $descuento, $destacado, '$foto' , '$carrusel')";
				array_push($debug, "Query: " . $query);
				if (mysqli_query($db, $query)){
					/*
						IMPORTANTE: Si todo va bien pasamos a action update de la categoría insertada
						La función mysqli_insert_id devuelve el último id automático insertado
					*/
					$id = mysqli_insert_id($db);
					$action = "update";
				}else{
					//Si se produce algún error, seguiremos en action insert para que el usuario lo pueda corregir
					array_push($errors, mysqli_error($db));
					//La foto la tiene que volver a subir!
					$foto = "";

				}
			}
			if (count($errors) > 0) {
				$view_mode = "add_new";
			}
			break;
		default:
			array_push($errors, "Opción incorrecta");
			/* Este action y view_mode son especiales. Los uso para contemplar casos incongruentes */
			$action = "incorrect";
			$view_mode = "show_message";
		}
	}
	if (($_SERVER["REQUEST_METHOD"] == "GET")){
		//Ver en qué estado hemos de mostrar el form
		//Por defecto, la opción es editar un ítem
		$view_mode = "edit";
		/*
			En el GET sólo pueden venir los modos edit, add_new
			Dependiendo de este parámetro las "action" posibles son:
			edit -> update o delete
			add_new -> insert

			NOTA. El modo "show_message" es especial. No se usa como parámetro en la URL, sino que se usa para indicar que no
			queremos mostrar el formulario (por ejemplo, una vez borrado un elemento, cuando no se encuentra el elemento, etc)

			Si "view_mode" es:
				* edit: Muestra los datos del elemento junto con Guardar, Eliminar y Nuevo
				* add_new: Muestra un formulario con los datos en blanco y los botones Guardar y Cancelar
				* show_message: No muestra el formulario. Se usa en casos especiales (buscad en el código)
		*/
		if (isset($_GET['view_mode'])) {
			$view_mode = strtolower($_GET['view_mode']);
		}
		if ($view_mode == "edit"){
			/* Obtener los datos del elemento */
			$id = -1; //Suponemos que es NULL
			if (isset($_GET["id"])){
				$id = $_GET["id"];
			}
			if ($id != -1){
				$query = "SELECT * from productos where id = $id";
			}else{
				/*
					Si no hay id es porque visitamos la página sin querystring.
					Nos quedamos con la primera que haya. Fijáos en "limit 1"
				*/
				$query = "SELECT * from productos order by id asc limit 1";
			}
			array_push($debug, "Query: " . $query);

			$results = mysqli_query($db, $query);
			if ($results->num_rows > 0) {
				//Rellenar las variables con las obtenidas de la BD
				$row = mysqli_fetch_assoc($results);
				$id = $row["id"];
				$nombre = $row["nombre"];
				$descripcion = $row["descripcion"];
				$precio = $row["precio"];
				$descuento = $row["descuento"];
				$stock = $row["stock"];
				$destacado = $row["destacado"];
				$id_categoria = $row["id_categoria"];
				$foto = $row["foto"];
				$carrusel = $row["carrusel"];
				//Si el elemento existe, la action ahora es update
				$action = "update";
			}else{
				//Si no obtenemos ningún elemento es porque está vacía la tabla o no existe tal elemento
				if ($id == -1){
					//No hay ninguno, pasar automáticamente a modo add_new
					$view_mode = "add_new";
				}else{
					//El elemento no existe
					$view_mode = "show_message";
					array_push($errors, "El producto $id no existe");
				}
			}
		}
		/*
			********** CUIDADO ********************
			NO HACER
				else if ($view_mode == "add_new"){
			porque cuando estamos en modo "edit" podemos cambiar a "add_new"
		*/
		if ($view_mode == "add_new"){
			$id = "";
			$action = "insert";
		}
		//Si no son estos modos no mostramos el formulario
		if (($view_mode != "add_new") && ($view_mode != "edit") && ($view_mode != "show_message")){
			$view_mode = "show_message";
			$action = "incorrect";
			array_push($errors, "Opción incorrecta");
		}
	}

?>
<?php

/*	******************************** VISTA ***************************************
	Aquí empieza la parte que muestra la página. Ahora ya se puede escribir HTML porque toda la lógica se ha hecho antes
*/
function imprimeArbol($idCategoria, $nivel, $currentId){
global $db;
	if ( $idCategoria == -1)
		$sql = "SELECT * from categorias where id_padre is null";
	else
		$sql = "SELECT * from categorias where id_padre = $idCategoria";

	$results = mysqli_query($db, $sql);

	if ($results->num_rows > 0) {
		$nivel = $nivel + 1;
		while($row = mysqli_fetch_assoc($results)){
			echo "<option value='" . $row["id"] . "' " . ($currentId == $row["id"] ? "selected" : "") . ">";
			    echo str_pad("", $nivel * 4 * 6, "&nbsp;") . $row["nombre"];
			echo "</option>";
			imprimeArbol($row["id"], $nivel, $currentId);
		}

	}
}
function imprimeMenu(){
global $db, $id;

	$sql = "SELECT * from productos";

	$results = mysqli_query($db, $sql);

	if ($results->num_rows > 0) {
		echo "<ul>";
		while($row = mysqli_fetch_assoc($results)){
			echo "<li>";
			if ($id == $row["id"]){
				echo $row["nombre"];
			}else{
				echo "<a href='productos.php?id=" . $row["id"] . "&view_mode=edit'>" . $row["nombre"] . "</a>";
			}
			echo "</li>";
		}
		echo "</ul>";
	}
}

$pageheader = "Productos";
include("./include/header.php");

?>
<script>
function checkDelete(){
	//Siempre que una acción no se pueda deshacer hay que pedir confirmación al usuario
	if (confirm("¿Seguro que desea borrar este producto?"))
		return true;
	else
		return false;
}
function getHTML(){
	var htmldata = editorElement.document.getBody().getHtml();
	prompt("s", htmldata);
	return false;
}
</script>
<div class="row">
	<div class="col-lg-4">
		<?php
			if (($_SERVER["REQUEST_METHOD"] == "POST")){
			echo "<pre>";
				print_r($_POST);
			echo "</pre>";
		}
		?>

		<?php imprimeMenu(); ?>
	</div>
	<div class="col-lg-8">
<?php
	//Mensajes de depuración
	echo "<pre>";
	for ($i = 0; $i < sizeOf($debug); $i++)
	    echo $debug[$i] . "\n";
	echo "view_mode: $view_mode | action: $action | id: $id";
	echo "</pre>";

	//Mostrar todos los mensajes de error
	for ($i = 0; $i < sizeOf($errors); $i++)
	    echo "<div class='alert alert-danger' role='alert'>$errors[$i]</div>";

	/*
		Cuando llegamos aquí ya sabemos en qué modo estamos porque se ha hecho en el controlador
		Todas las variables también se han informado allí
	*/
	if (($view_mode == "edit") || ($view_mode == "add_new")){
		?>
		<form action="productos.php" method="post" enctype = "multipart/form-data">
			<div class="form-group">
				<label for="id">ID</label>
				<!-- Cuidado: Si un campo está disabled NO se envía en el POST, por tanto hay que duplicarlo en uno oculto -->
				<input disabled type="text" class="form-control" id="id"  value="<?php echo $id; ?>" placeholder="Campo automático">
				<input type="hidden" name="id" value="<?php echo $id; ?>">
			</div>
			<div class="form-group <?php echo ($nombreErr ? " has-error" : "");?> ">
				<label for="nombre">Nombre</label>
				<input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $nombre; ?>">
			</div>
			<div class="form-group <?php echo ($descripcionErr ? " has-error" : "");?> ">
				<label for="descripcion">Descripción</label>
				<textarea class="form-control" id="descripcion" name="descripcion"><?php echo $descripcion; ?></textarea>
			</div>
			<div class="form-group <?php echo ($precioErr ? " has-error" : "");?> ">
				<label for="precio">Precio</label>
				<input type="text" class="form-control" id="precio" name="precio" value="<?php echo $precio; ?>">
			</div>
			<div class="form-group">
				<label for="descuento">Descuento</label>
				<input type="text" class="form-control" id="descuento" name="descuento" value="<?php echo $descuento; ?>">
			</div>

			<div class="form-group">
				<label for="stock">Stock</label>
				<input type="text" class="form-control" id="stock" name="stock" value="<?php echo $stock; ?>">
			</div>
			<div class="form-group">
				<label for="destacado">Destacado</label>
				<input type="checkbox" id="destacado" name="destacado" value="1" <?php echo (($destacado == 1) ? "checked" : "");?>>
			</div>

			<div class="form-group">
				<label for="id_categoria">Categoría</label>
				<?php
					echo "<select name='id_categoria' id='id_categoria'>";
					imprimeArbol(-1, -1, $id_categoria);
					echo "</select>";
				?>
			</div>
			<div class="form-group">
				<label for="foto">Foto</label>
				<input type = "file" id="foto" name = "foto" accept="image/*">
				<!-- Guardamos la foto actual -->
				<input type="hidden" name="foto_actual" value="<?php echo $foto;?>">
			<?php
				if (!empty($foto)){
					echo "<img class='center-block img-thumbnail' src='./img/256_$foto'>";
				}
			?>
			</div>
			<div class="form-group">
				<label for="carrusel">Carrusel</label>
				<input type = "file" id="carrusel" name = "carrusel" accept="image/*">
				<!-- Guardamos el carrusel actual -->
				<input type="hidden" name="carrusel_actual" value="<?php echo $carrusel;?>">
			<?php
				if (!empty($carrusel)){
					echo "<img class='center-block img-thumbnail' src='./img/$carrusel'>";
				}
			?>
			</div>
			<button type="submit" name='<?php echo $action; ?>' class="btn btn-primary">Guardar</button>
		<?php if ($view_mode == "edit"){?>
			<button type="submit" name='delete' class="btn btn-default" onclick='return checkDelete();'>Eliminar</button>
			<hr>
			<a class='btn btn-default' href='productos.php?view_mode=add_new'>Nueva</a>
		<?php }else{ ?>
			<a class='btn btn-default' href='productos.php?view_mode=edit'>Cancelar</a>
		<?php } ?>
		</form>
	<?php
	}else{
		//En este caso sólo hay una posibilidad. En otros forms, tal vez haya más opciones
		if (($action == "delete") && (sizeOf($errors) == 0)){
			echo "<div class='alert alert-info'>Producto borrado satisfactoriamente <a class='btn btn-default' href='./productos.php'>Continuar</a></div>";
		}
	}//if (($view_mode == "edit") || ($view_mode == "add_new")){

	?>
	</div>

</div>

<?php
	//En principio no hace falta cerrar la conexión, pero no está de más
	mysqli_close($db);
	include("./include/footer.php");
?>
