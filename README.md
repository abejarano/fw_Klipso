# fw_klipso framework de programación web.

fw_klipso es un framework para el desarrollo de aplicaciones web con el lenguaje PHP. En fw_klipso framework la organización del proyecto, el rendimiento de la aplicación y la versatilidad 
en el desarrollo son en este momento su principal fuerte.

#Requerimientos

1-.) PHP version mayor a 5.4

2-.) liberias de PHP: pdo, mbstring

3-.) Composer

4-.) Habilitar mod_rewrite (si tú servidor web es apache)


# Instalación

Para realizar una instalación basta con realizar un git clone de este repositorio en la carpta donde tendras tú aplicación. Luego de clonar debes tener hacer composer update dentro 
del directorio fw_klipso. OJO si tiene version de php es menor a 7 y mayor a 5.4 debes modificar el archivo composer.json y colocar el siguiente valor. y posteriormente hacer composer update.

{

    "require": {
        "twig/twig": "~1.0"
    }
}

# Iniciar un proyeto con fw_klipso framework.

fw_klipso provee una interfaz por linea de comando que permite crear un proyecto y sincronizar tus modelos con la db. Todos los comando debe ser ejecutados dentro del directorio fw_klipso

1-.) crear un proyecto
    php manager startproject nombre_del_proyecto
    
2-.) crear una app
    php manager startapp nombre_del_app
    
    Las app son en realidad modulos de tu proyecto.
    

