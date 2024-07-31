## Requisitos previos
- [GIT](https://git-scm.com/downloads)
- [PHP versión 8.0+](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org/download/)

## Instalación del proyecto
- Clonar el repositorio con el comando `git clone https://github.com/MarkOsBab/votes-api`
- Instalar dependencias con el comando `composer install`
- Crear un archivo nuevo llamado **.env** en la raiz del proyecto y copiar todas las variables de entorno ubicadas en el archivo .env.example con los valores apropiados para tu entorno local
- Ejecutar el comando `php artisan key:generate` para generar el APP_KEY
- Luego ejecutar el comando `php artisan jwt:generate` para generar la clave secreta de Json Web Token para autenticar a los usuarios
- Importar (base de datos)[https://github.com/MarkOsBab/votes-api/blob/main/database/votes-api.sql] o ejecutar el comando `php artisan migrate --seed`
- Luego de instalar las dependencias y configurar base de datos y variables de entorno, ejecutar el comando `php artisan serve` para correr el servidor

## Referencias
> api-url.com: Enlace de la api de tu entorno local

## Insertar datos de prueba en la base de datos
- Para crear datos de prueba en la base de datos ejecutar el comando `php artisan db:seed`

## Automatización de votos (cargar base de datos)
- Si lo requiere puede modificar el archivo [DatabaseSeeder](https://github.com/MarkOsBab/votes-api/blob/main/database/seeders/DatabaseSeeder.php) para cargar más votantes y candidatos
    - Modificar el número de la funcion **count** ingresando la cantidad necesaria de datos
- Para cargar votos de forma masiva debe ejecutar el comando `php artisan app:generate-random-votes`

## Ejecutar pruebas de funcionalidad
- Acceder a la carpeta raíz del proyecto mediante la consola
- Ejecutar el comando `php artisan test`

## Acceso a documentación de la api
- Ingresar al enlace `http://api-url.com/api/documentation`