<?php

// DATOS DE EJEMPLO

$_config = array_merge ( $_config, [
    // Lista de extensiones que no se deben mostrar en los listados; es importante conservar las comillas anidadas
    // FIXME: Buscar una mejor forma... (al menos hemos encontrado un sitio mejor)
    'lista_ignorados' => array(
        "'888888'", // fax antiguo a manivela
    ),
]);
