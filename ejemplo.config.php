<?php

$_config = [
    // Sufijo de la URL base de la aplicacion, sin http:// o https:// ; p.e. 'servidor.local/telefonos'
    'url_suffix' => 'servidor.local/telefonos',
    
    // p.e. 'Directorio Telef&oacute;nico'
    'titulo_directorio' => 'Directorio Telef&oacute;nico',
    
    // URL de la Intranet Primaria; p.e. 'https://servidor.local/intranet'
    'url_intranet_dp' => 'https://servidor.local/intranet',
    
    // URL de la Intranet Secundaria; p.e. 'https://servidor-2.local/intranet'
    'url_intranet_atria' => 'https://servidor-2.local/intranet',
    
    // Telefono por defecto
    'telefono_defecto' => 999999,
    
    // Mensaje por defecto
    'mensaje_defecto' => 'Error: Llamar a Informatica en el 999000',
    
    // Nombre del archivo CSV de origen
    'nombre_fichero' => 'telefonos.csv',
    
    // Datos de acceso al servidor LDAP
    // MUY IMPORTANTE: Poner a false en caso de usar el archivo en version sin LDAP
	// FIXME: Detectar la carga de la libreria en vez de usar un boolean
	'ldap_active' => false,
];


// Fichero de configuración opcional: extensiones presentes en el archivo CSV pero que no queremos mostrar
if ( file_exists ( 'config_ignorados.php' ) ) {
    include_once 'config_ignorados.php';
} else {
    // Si no existe el archivo de configuración, creamos una lista vacía
    $_config = array_merge ( $_config, ['lista_ignorados' => array () ] );
}

// Fichero de configuración opcional: configuración LDAP (en caso de usarse en el futuro -está en desarrollo-)
if ( file_exists ( 'config_ldap.php' ) ) {
    include_once 'config_ldap.php';
} elseif ( $_config['ldap_active'] ) {
    // Si no existe el archivo de configuración, desactivamos el soporte
    $_config['ldap_active'] = false;
}
