## Requisitos previos
- [GIT](https://git-scm.com/downloads)
- [PHP versión 8.0+](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org/download/)

## Instalación del proyecto
- Clonar el repositorio con el comando `git clone https://github.com/MarkOsBab/votes-api`
- Instalar dependencias con el comando `composer install`
- Crear un archivo nuevo llamado **.env** en la raiz del proyecto y copiar todas las variables de entorno ubicadas en el archivo .env.example
- Ejecutar el comando `php artisan key:generate` para generar el APP_KEY
- Luego ejecutar el comando `php artisan jwt:generate` para generar la clave secreta de Json Web Token para autenticar a los usuarios
- Luego de instalar las dependencias, ejecutar el comando `php artisan serve` para correr el servidor