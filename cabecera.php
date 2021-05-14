<?php

$template->parse ( 'res/inicio_cabecera.html' );

// Mostrar marcadores avanzados o no
if ( $upd ) {
    $template->parse ( 'res/marcadores_upd.html' );
} else {
    $template->parse ( 'res/marcadores.html' );
}

$template->parse ( 'res/fin_cabecera.html' );
