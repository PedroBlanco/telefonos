<?php

// DATOS DE EJEMPLO

// Configuramos en un archivo aparte la conexion con el servidor LDAP (puede ser AD)
$_config = array_merge ($_config, [
	'ldap_server' => 'ldap-server.local',
    'ldap_user' => 'user',
    'ldap_password' => 'password',
    'ldap_root' => 'DC=myorg,DC=example,DC=org',
    // Ramas LDAP a procesar (por lo menos funciona con 2)
    'ldap_branches' => [
                        [
                            'id' => 'Primera',
                            'title' => 'Rama primera',
                            'ou' => 'OU=Users,OU=Primera'
                        ],
                        [
                            'id' => 'Segunda',
                            'title' => 'Rama segunda',
                            'ou' => 'OU=Users,OU=Segunda'
                        ]
    ]
]);

// Seguramente haya un sitio mejor...
if ( $_config['ldap_active'] ) {
    // De esta forma hacemos compatible hacia atras el archivo de configuracion (asignando $_config['ldap_active'] a false)
    // Da error 500 si no lo ponemos a false
    $_config['ldap_link'] = new ADX\Core\Link( $_config['ldap_server'], 389 ); // Connect to server on default port
    $_config['ldap_link']->bind( $_config['ldap_user'], $_config['ldap_password'] ); // Authenticate to the server
}
