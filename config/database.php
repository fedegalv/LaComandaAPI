<?php
//CREAR NAMESPACE
namespace Config;
//AL CREAR NAMESPACE CAMBIAR HACER LOS CAMBIOS EN composer.json AGREgando el namespace
//AGREGAR EL DESCRIPTION Y autoload, psr4 COnfig config
//LUEGO DE AGERGAR HACER composer dump-autoload -o
use Illuminate\Database\Capsule\Manager as Capsule;
// Set the event dispatcher used by Eloquent models... (optional)
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
//CREAMOS UNA CLASE Y CONST QUE CONTENGA LA BD

class Database{
    public function __construct(){
    $capsule = new Capsule;
    $capsule->addConnection([
        'driver'    => 'mysql',
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'comando',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
    ]);
    $capsule->setEventDispatcher(new Dispatcher(new Container));

    // Make this Capsule instance available globally via static methods... (optional)
    $capsule->setAsGlobal();

    // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
    $capsule->bootEloquent(); 
    }
}