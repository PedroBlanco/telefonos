<?php

// Tareas de Backend
// TODO: Estudiar la posibilidad de tomar como fuente de los telefonos el ultimo fichero csv que copiemos al directorio
// WISH: Estudiar la union de los datos de telefonos con los del Directorio Activo (correo electronico) a traves del nombre y apellidos
// WISH: Estudiar poder ofrecer un interfaz LDAP para poder consultarlo a traves de Thunderbird

// Tareas de Interfaz
// TODO: Estudiar la posibilidad de mostrar la fecha de actualizacion del archivo CSV (p.e. ver fecha de modificacion/creacion)
// TODO: Estudiar la busqueda incremental automatica en la opcion simple de consulta
// TODO: Estudiar poner instrucciones para configurar la opcion por pestañas en Thunderbird
// TODO: Mostrar advertencias del uso de acentos
// TODO: Mostrar botones para acceder a los diferentes tipos de busqueda (simple, pestañas, añadir bookmarklet)
// TODO: En Internet Explorer < 9 no se marca el indice de la pestaña activa
// TODO: No mostrar el boton de Añadir a marcadores cuando se muestre en Thunderbird
// TODO: La pestaña de busqueda a veces no funciona correctamente en Internet Explorer (en el servidor); vamos a ir convirtiendo a jQuery todo el código

// Tareas de Procesado
// TODO: Mejorar la forma de exclusion (numeros excluidos fijos y variables)
// TODO: La consulta Ajax de pestañas no muestra los resultados en Thunderbird (aunque genera el numero correcto de filas vacias de la tabla)
// TODO: Cuando se pasa el parametro consulta=... ¿hay que limpiar los espacios iniciales y finales de la cadena? o al menos deben mostrarse esos espacios cuando mostramos la cadena buscada
// TODO: Comprobar en los parametros $_GET que usamos si aparecen =true o no (por ahora solo miramos que nos los pasan y los asumimos true)
// WISH: Estudiar si hace falta una opcion sin ajax, pues la opcion simple tambien usa ajax para recuperar los resultados
// WISH: Estudiar poder mostrar resultados de busqueda mediante consulta $_GET en el modo de pestañas
// WISH: Usar Smarty u otro sistema de plantillas mas avanzado (lo que permitiria cachear la pagina por defecto)

// Tareas Generales
// TODO: Crear paginas index.html o index.php en los directorios para no mostrar el contenido de los directorios
// WISH: Usar la pagina test.php como comprobacion de instalacion correcta: configuracion, componentes, plantillas, archivos, ...
// WISH: Buscar un mejor sistema para gestionar las tareas



require_once 'config.php';
require_once 'res/Template.php';

$template = new Template();

$template->assign( 'url_suffix', $_config['url_suffix'] );
$template->assign( 'titulo_directorio', $_config['titulo_directorio'] );
$template->assign( 'url_intranet_dp', $_config['url_intranet_dp'] );
$template->assign( 'url_intranet_atria', $_config['url_intranet_atria'] );



// Telefono por defecto
$telefono = $_config['telefono_defecto'];

// Mensaje por defecto
$mensaje = $_config['mensaje_defecto'];

// Nombre del archivo CSV de origen
$nombre_fichero = $_config['nombre_fichero'];

// Las bookmarklet (marcadores inteligentes) consultan siempre por https para evitar errores por Mixed Content
$bookmarklet_generica = <<<'EOD'
javascript: (function () {
	var jsCode = document.createElement('script');
	jsCode.setAttribute('src', 'https://<url_suffix>/phone.php?bookmarklet=true');
	document.body.appendChild(jsCode);
}());
EOD;

// Version abreviada para la url del marcador inteligente
// TODO: Buscar la forma de abreviarla de forma automatica y usar solo una version
$bookmarklet_generica_abreviada = "javascript:(function(){var%20jsCode=document.createElement('script');jsCode.setAttribute('src','https://<url_suffix>/phone.php?script=true');document.body.appendChild(jsCode);}());";

$bookmarklet_especifica = <<<'EOD'
javascript:(function() {
	var text = prompt( 'Persona a buscar','' ).toUpperCase()
	.replace(/Á/gi,"A")
	.replace(/É/gi,"E")
	.replace(/Í/gi,"I")
	.replace(/Ó/gi,"O")
	.replace(/Ú/gi,"U")
	;
	var request = new XMLHttpRequest();
	request.open("GET", "https://<url_suffix>/phone.php?xhr=true&consulta="+text, true);
	request.onreadystatechange = function() {
	  var done = 4, ok = 200;
	  if (request.readyState == done && request.status == ok) {
	    if (request.responseText) {
	      alert ( request.responseText.replace(/(^"|\$"$|"$)/g, '' ).trim().replace(/\=\>$/gm, '' ).replace(/\$/gm, '\n' ) );
	    }
	  }
	};
	request.send(null);
})();
EOD;

$bookmarklet_generica = str_replace ( '<url_suffix>', $_config['url_suffix'], $bookmarklet_generica );
$bookmarklet_generica_abreviada = str_replace ( '<url_suffix>', $_config['url_suffix'], $bookmarklet_generica_abreviada );
$bookmarklet_especifica = str_replace ( '<url_suffix>', $_config['url_suffix'], $bookmarklet_especifica );

$template->assign( 'url_bookmarklet', $bookmarklet_generica_abreviada );

// Filtramos (a mano):
// "SIN ASIGNAR ..."
// "H LINE ATRIAN 901 500 501"
// "fax antiguo delegacion"
// Cadena vacia?
// TODO: Mejorar la forma de exclusion (numeros excluidos fijos y variables)

// Peticiones excluyentes (por orden):
// 1- ?xhr=cadena -> buscamos cadena en los nombres de personas y devolvemos los resultados en JSON
// 2-?script=true -> devolvemos el script generico de bookmarklet para no necesitar actualizar todos los navegadores
// 3- ?bookmarklet=true -> devolvemos el script de bookmarklet a ejecutar 
// 4- ?thunder=true -> devolvemos todos los resultados en pestañas, con una pestaña adicional de busqueda xhr
// 6- ?simple=true -> mostramos el formulario de busqueda simple
// 7- <vacio> -> la opcion por defecto es mostrar el modo pestañas

// Peticiones no excluyentes
// ?consulta=cadena -> buscamos cadena en los nombres de personas; si va sola devolvemos los resultados en HTML (consulta simple)
// ?upd=true -> muestra en el modo simple y pestañas el desplegable de añadir bookmarklet 

// Antes de configurar el tipo de entrada, comprobamos si deberemos mostrar el enlace de bookmarklet con $_GET['upd']
if (isset($_GET['upd'])) {
    $upd = true;
} else {
    $upd = false;
}

// Ejecutamos un bucle para poder elegir una opcion por defecto en caso de no ser proporcionada en $_GET
$terminado = false;
while ( ! $terminado ) {
    if (isset($_GET['xhr'])) { // Consulta para devolver sólo los resultados cuando se accede por XMLHttpRequest
        $terminado = true;
        if (isset($_GET['consulta'])) {
            $consulta = trim(rtrim($_GET['consulta'], '"'), '"');
            $consulta = trim(rtrim($_GET['consulta'], ' '), ' ');
            $consulta = strtoupper($consulta);
            $sustituciones = array ( 'ñ' => 'Ñ',
                'Á' => 'A',
                'É' => 'E',
                'Í' => 'I',
                'Ó' => 'O',
                'Ú' => 'U'
            );
            $consulta = strtr ( $consulta, $sustituciones );
            $consulta = filter_var($consulta, FILTER_SANITIZE_STRING);
            
            if ( empty ( $consulta ) || ($consulta==",")) {
                $consulta = '';
                $mensaje = 'No se puede buscar una cadena vacía o la coma =>  ';
            } else {
                
                // TODO: Tal vez haya que hacer un par de comprobaciones
                $fichero_csv = fopen ( $nombre_fichero, 'r' );
                
                $mensaje = '';
                $array_mensaje = array();
                $fila_csv = fgetcsv($fichero_csv, 0, ';');
                for ( ; $fila_csv = fgetcsv($fichero_csv, 0, ';') ; )
                {
                    if ( in_array ( $fila_csv[1], $_config['lista_ignorados'] ) ) { //No hace nada...
                        continue;
                    } else {
                        $posicion = strpos ( $fila_csv[2], $consulta);
                        if ($posicion === false) {
                            continue;
                        } else {
                            // Para obtener los resultados ordenados deberiamos hacer una insercion ordenada o crear un array, ordenarlo y pasarlo a cadena
                            // $fila_csv[2]: Apellidos, Nombre
                            // $fila_csv[3]: Servicio
                            // $fila_csv[4]: Organismo
                            // $fila_csv[1]: extensión
                            $array_mensaje[] = $fila_csv[2].';'.$fila_csv[3].';'.$fila_csv[4].' => '.trim($fila_csv[1],"'");
                             
                        }
                    }
                }
                
                // En vez de hacer una insercion ordenada, vamos a ordenar el array de resultados
                natsort ( $array_mensaje );
                
                fclose($fichero_csv);
                
                if (empty ( $array_mensaje ) ) {
                    $mensaje = 'No se ha encontrado a nadie con ' . $consulta . ' en el nombre. =>  ';
                } else {
                    // Usamos $ como separador de lineas para que en el bookmarklet lo sustituyan por \n
                    $mensaje = implode ( '$', $array_mensaje );
                }
            }
        } else {
            $mensaje = 'No se ha encontrado a nadie con ' . $consulta . ' en el nombre. =>  ';
        }
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: text/javascript');
        
        print json_encode($mensaje, JSON_UNESCAPED_UNICODE);
        
    } elseif (isset($_GET['script'])) { // Consulta para devolver el código de Bookmarklet a añadir como Marcador/Favorito
        $terminado = true;
        
        /* Devolvemos el codigo de bookmarklet generica para poder añadirlo como marcador */
        $mensaje = $bookmarklet_generica;

        header("Content-Type: text/plain");
        print $mensaje;
        
    } elseif (isset($_GET['bookmarklet'])) { // Consulta para devolver el código de Bookmarklet a añadir como Marcador/Favorito
        $terminado = true;

        /* Devolvemos el codigo de bookmarklet especifica para ejecutarlo */
        $mensaje = $bookmarklet_especifica;
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type');
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header('Content-Type: text/javascript');
        
        //header("Content-Type: text/plain");
        print $mensaje;
        
    } elseif (isset($_GET['thunder'])) { // Consulta desde Mozilla Thunderbird (cuando está configurado para abrir esta página como inicio) para devolver la página de búsqueda
        $terminado = true;
        
        // Creamos array de indices
        $indice = array ();
        $letras = array ('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        foreach ($letras as $clave) {
            $indice[$clave] = array();
        }
        
        // TODO: Tal vez haya que hacer un par de comprobaciones
        $fichero_csv = fopen ( $nombre_fichero, 'r' );
        
        $fila_csv = fgetcsv($fichero_csv, 0, ';');
        for ( ; $fila_csv = fgetcsv($fichero_csv, 0, ';') ; )
        {
            if ( in_array ( $fila_csv[1], $_config['lista_ignorados'] ) ) {
                continue;
            } else {
                // Metemos en el índice alfabético las entradas no ignoradas del archivo csv
                $indice[$fila_csv[2][0]][$fila_csv[2]] = trim($fila_csv[1],"'");
            }
        }
        
        fclose($fichero_csv);
        
        include 'cabecera.php';
        include 'guia_alfabetica.php';
        include 'pie.php';
    } elseif (isset($_GET['consulta'])) { // Consulta para devolver el código de Bookmarklet a añadir como Marcador/Favorito
        // En el caso de $_GET['consulta'] sin ejecutarse alguna otra rama anterior, suponemos que debemos mostrar la consulta simple,
        // pues la opcion de pestañas no soporta la busqueda directa por $_GET y el resto bien la ignora bien requiere otros parametros
        // ...aunque la consulta simple utiliza Ajax cuando se usa el formulario
        $terminado = true;
        $consulta_vacia = false;
        
        $consulta = trim(rtrim($_GET['consulta'], '"'), '"');
        $consulta = strtoupper($consulta);
        $sustituciones = array (
            'ñ' => 'Ñ',
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U'
        );
        $consulta = strtr ( $consulta, $sustituciones );
        $consulta = filter_var($consulta, FILTER_SANITIZE_STRING);
        
        $mensaje = '';
        $resultado = array();
        $resultado_2 = array();
        
        // TODO: Tal vez haya que hacer un par de comprobaciones
        $fichero_csv = fopen ( $nombre_fichero, 'r' );
        
        $fila_csv = fgetcsv($fichero_csv, 0, ';');
        for ( ; $fila_csv = fgetcsv($fichero_csv, 0, ';') ; )
        {
            if ( in_array ( $fila_csv[1], $_config['lista_ignorados'] ) ) { //No hace nada...
                continue;
            } else {
                $posicion = strpos ( $fila_csv[2], $consulta);
                if ($posicion === false) {
                    continue;
                } else {
                    // $resultado[] = array ( $fila_csv[2] => $fila_csv[10] );
                    $resultado[$fila_csv[2]] = trim($fila_csv[1],"'");
                    //$resultado_2[] = array ( 'name' => $fila_csv[2], 'phone' => $fila_csv[1] );
                    $resultado_2[] = array ( 'name' => $fila_csv[2],'service'=>$fila_csv[3],'organism'=>$fila_csv[4], 'phone' => $fila_csv[1] );
                    

                    $mensaje .= $fila_csv[2].' => '.trim($fila_csv[1],"'").'$';
                }
            }
        }
        
        fclose($fichero_csv);
        
        $template->assign ( 'consulta', $consulta );
        $template->assign ( 'resultado', $resultado_2 );
        
        include 'cabecera.php';
        include 'formulario.php';
        if ($mensaje == '') {
            include 'sin_resultados.php';            
        } else {
            ksort ( $resultado );
            include 'consulta.php';
        }
        include 'pie.php';
        
    } elseif (isset($_GET['simple'])) {
        $terminado = true;
        $consulta_vacia = true;
        $consulta = '';
    
        $template->assign ( 'consulta', $consulta );
        
        //Mostramos la pagina de consulta simple
        include 'cabecera.php';
        include 'formulario.php';
        include 'consulta.php';
        include 'pie.php';
    } else {
        // Para elegir una opcion por defecto, volvemos a ejecutar el if, eligiendo una de las opciones que nos pasan
        // TODO: Esto es un poco sucio, habria que hacerlo de otra forma
        $_GET['thunder'] = 'true';
    }
}

