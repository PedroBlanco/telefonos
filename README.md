# Aplicación PHP simple de listado telefónico a partir de un archivo CSV

## Configuración

Archivos de configuración que hay que crear/renombrar y modificar:

- ```config.php```: (**obligatorio**; no presente)
  - Configuración general.
  - Se incluye un archivo genérico con valores de ejemplo que hay que modificar:
    - Modificar contenido y quitar prefijo ```ejemplo.``` de ```ejemplo.config.php```
- ```telefonos.csv```: (**obligatorio**, pero configurable en ```config.php``` como ```$_config['nombre_fichero']```; no presente)
  - Listado CSV con los datos a mostrar (_casi_ autoexplicativo)
  - Se incluye un archivo genérico de ejemplo:
    - Modificar contenido y quitar prefijo ```ejemplo.``` de ```ejemplo.telefonos.csv```
- ```config_ignorados.php```: (opcional; no presente)
  - Lista de extensiones presentes en el listado pero que no queremos mostrar.
  - Se incluye un archivo genérico con valores de ejemplo que hay que renombrar:
    - Modificar contenido y quitar ```ejemplo.``` de ```ejemplo.config_ignorados.php```
- ```config_ldap.php```: (opcional; no presente)
  - Configuración de conexión con LDAP (pendiente de terminar de implementar).
  - Se incluye un archivo genérico con valores de ejemplo que hay que renombrar:
    - Modificar contenido y quitar ```ejemplo.``` de ```ejemplo.config_ldap.php```

