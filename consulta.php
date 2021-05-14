<?php

if ( $consulta_vacia ) {
    $template->parse('res/consulta_vacia.html');
} else {
    $template->parse('res/consulta.html');
}
