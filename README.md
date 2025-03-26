# Instalación y Configuración para Linux

Estas instrucciones te guiarán a través de la instalación y configuración necesarias para ejecutar este proyecto FilamentPHP en un entorno Linux.

## Requisitos Previos

Asegúrate de tener los siguientes requisitos instalados:

* **PHP:** Versión 8.1 o superior (recomendado).
* **Composer:** Gestor de dependencias de PHP.
* **Extensiones PHP:**
    * `intl`
    * `sqlite3` (o la base de datos que prefieras, como `mysql`, `pgsql`, etc.)

## Pasos de Instalación

1.  **Actualizar el Sistema e Instalar PHP y Extensiones:**

    Abre una terminal y ejecuta los siguientes comandos:

    ```bash
    sudo apt update
    sudo apt install php php-intl php-sqlite3
    ```

    * **Nota:** Si utilizas una distribución diferente a Debian/Ubuntu, ajusta los comandos de instalación según corresponda (ej. `yum` para CentOS/RHEL, `pacman` para Arch Linux, etc.).

2.  **Instalar Composer:**

    Descarga e instala Composer usando los siguientes comandos:

    ```bash
    php -r "copy('[https://getcomposer.org/installer](https://getcomposer.org/installer)', 'composer-setup.php');"
    php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
    php composer-setup.php
    php -r "unlink('composer-setup.php');"
    ```

3.  **Hacer Composer Globalmente Disponible:**

    Mueve el ejecutable de Composer a un directorio en tu `PATH`:

    ```bash
    sudo mv composer.phar /usr/local/bin/composer
    ```

4.  **Instalar las Dependencias del Proyecto:**

    Navega al directorio de tu proyecto y ejecuta:

    ```bash
    composer install
    ```

5.  **Configurar el Archivo `.env`:**

    Copia el archivo `.env.example` a `.env` y ajusta las configuraciones según sea necesario, especialmente la configuración de la base de datos:

    ```bash
    cp .env.example .env
    nano .env # o tu editor de texto preferido
    ```

6.  **Ejecutar las Migraciones:**

    Ejecuta las migraciones de la base de datos:

    ```bash
    php artisan migrate:fresh --seed
    ```

7.  **Crear un Usuario de Filament:**

    Crea un usuario super_admin para acceder al panel de Filament y crear otros usuarios desde el panel de Filament:

    ```bash
    php artisan filament:user-create "Super Admin" "superadmin@gmail.com" "pass1234" --super_admin
    ```

8.  **Genera la llave del servidor:**


    ```bash
    php artisan key:generate
    ```

## Ejecutar el Servidor de Desarrollo

Para iniciar el servidor de desarrollo, ejecuta:

```bash
php artisan serve
```
