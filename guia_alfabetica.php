<?php

// TODO: Utilizar plantillas (habria que ver si funciona Template.php para bucles anidados)

print '<tr><td>';
print '<div class="center-content">';

$html_tabs = '<div class="tab">';
$html_start_content = '';
$html_end_content = '';

// anadimos una pestana de busqueda
$html_tabs .=  '<button id="b_busqueda" class="tablinks" onclick="openTab(event, '."'t_busqueda'".');">Buscar...</button>';
$html_start_content .= '<div id="t_busqueda" class="tabcontent"><table class="center-content">';

$html_end_content .= '</table></div>';

foreach ($indice as $key => &$value) {
    if ( ! empty( $value ) ) {
        // Primero ordenamos cada array de letra
        ksort ( $value );
        
        $html_tabs .=  '<button id="b_'.$key.'" class="tablinks" onclick="openTab(event, ';
        $html_tabs .=  "'".$key."'";
        $html_tabs .=  ')">'.$key.'</button>
';
        
        $html_end_content .= '<div id="'.$key.'" class="tabcontent"><table class="contactos">
';
        foreach ( $value as $nombre_contacto => $numero_contacto) {
            $html_end_content .= '<tr><td class="nombre">'.$nombre_contacto.'</td><td>'.$numero_contacto.'</td></tr>
';
        }
        $html_end_content .= '</table></div>
';
    }
}
$html_tabs .= '</div>';

print $html_tabs;
print $html_start_content;

$template->parse ('res/formulario.html');
$template->parse ('res/consulta_vacia.html');

print $html_end_content;

print '</div>';
print '</td></tr>';

