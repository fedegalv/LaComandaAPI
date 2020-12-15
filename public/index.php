<?php
///1- EN CONSOLE composer init
///2- LUEGO composer require slim/slim:"4.*"
//3- LUEGO composer require slim/psr7
//4- USAR EL CODIGO DEBAJO COMO TEMPLATE
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
//AGREGAR RequestHandler;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
require dirname(__DIR__) . '/vendor/autoload.php'; // It must be called first

//10-LUEGO DE CREAR EL NAMESPACE CONFIG AGREGARLO
use Config\Database;
//LUEGO DE CREAR EL NAMESPACE APP CON LA CLASE MODELO AGREGAR
//use App\Models\Alumno;
//EL MODELO NO HACE FALTA AGREGAR YA QUE SE MANEJA DESDE CONTROLLER
use App\Controllers;
use App\Controllers\UsuarioController;
use App\Controllers\ProductoController;
use App\Controllers\PedidoController;
use App\Controllers\EncargoController;
use App\Controllers\MesaController;
use Slim\Routing\RouteCollectorProxy;

use App\Middlewares\JsonMiddleware;
use App\Middlewares\MozoAuthMiddleware;
use App\Middlewares\SocioAuthMiddleware;
use App\Middlewares\MozoYSocioAuthMiddleware;
use App\Middlewares\EmpleadoAuthMiddleware;

//require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();


$app->setBasePath('/LaComandaAPI/public');

new Database;
//PRODUCTOS
$app->group('/producto', function (RouteCollectorProxy $group) {
    $group->post('[/]', ProductoController:: class .":add");
    $group->get('[/]', ProductoController:: class .":getAll");
    $group->delete('[/{id}]', ProductoController:: class .":delete");
    
})->add(new JsonMiddleware);
//USUARIOS
$app->group('/registro', function (RouteCollectorProxy $group) {
    $group->post('[/]', UsuarioController:: class .":registro");
})->add(new JsonMiddleware);
$app->group('/usuario', function (RouteCollectorProxy $group) {
    $group->delete('[/{id}]', UsuarioController:: class .":delete")->add(new SocioAuthMiddleware);
    $group->post('[/suspender/{id}]', UsuarioController:: class .":suspender")->add(new SocioAuthMiddleware);
    
})->add(new JsonMiddleware);

//PEDIDOS
$app->group('/tomarEncargo', function (RouteCollectorProxy $group) {
    //EMPLEADO TOMA LOS ENCARGOS CORRESPONDINETES A SU TIPO Y COMIENZA PREPARACION
    $group->post('[/]', EncargoController:: class .":tomarEncargo")->add(new EmpleadoAuthMiddleware);
})->add(new JsonMiddleware);
$app->group('/pedidosPendientes', function (RouteCollectorProxy $group) {
    //VER QUE PEDIDOS PENDIENTES HAY PARA TIPO EMPLEADO, SEGUN EL TOKEN ENVIADO
    $group->get('[/]', PedidoController:: class .":getPendiente")->add(new EmpleadoAuthMiddleware);
})->add(new JsonMiddleware);
$app->group('/pedidosCompletos', function (RouteCollectorProxy $group) {
    //VER PEDIDO COMPLETOS
    $group->get('[/]', PedidoController:: class .":getCompleto")->add(new EmpleadoAuthMiddleware);
})->add(new JsonMiddleware);
$app->group('/pedidos', function (RouteCollectorProxy $group) {
    //AGREGAR PEDIDO
    $group->post('[/]', PedidoController:: class .":add")->add(new MozoAuthMiddleware);
    //VER  PEDIDOS
    $group->get('[/]', PedidoController:: class .":verEstadoPedidos")->add(new EmpleadoAuthMiddleware);
})->add(new JsonMiddleware);
$app->group('/servirPedido', function (RouteCollectorProxy $group) {
    //SERVIR PEDIDO SI ESTA LISTO PARA SERVIR
    $group->post('[/]', PedidoController:: class .":servirPedido")->add(new MozoAuthMiddleware);
})->add(new JsonMiddleware);

//ENCARGOS
$app->group('/terminarEncargo', function (RouteCollectorProxy $group) {
    $group->post('[/]', EncargoController:: class .":terminarEncargo")->add(new EmpleadoAuthMiddleware);
})->add(new JsonMiddleware);

$app->group('/login', function (RouteCollectorProxy $group) {
    $group->post('[/]', UsuarioController:: class .":login");
})->add(new JsonMiddleware);
/////
// MESAS
$app->group('/mesa', function (RouteCollectorProxy $group) {
    $group->get('[/{codigoMesa}]', MesaController:: class .":getByCodigoMesa")->add(new MozoYSocioAuthMiddleware);
})->add(new JsonMiddleware);

$app->group('/mesas', function (RouteCollectorProxy $group) {
    $group->get('[/]', MesaController:: class .":getAll")->add(new MozoYSocioAuthMiddleware);
})->add(new JsonMiddleware);

$app->group('/pagarMesa', function (RouteCollectorProxy $group) {
    $group->post('[/]', MesaController:: class .":clientePagandoMesa")->add(new MozoAuthMiddleware);
})->add(new JsonMiddleware);
$app->group('/mesaMasFacturo', function (RouteCollectorProxy $group) {
    $group->get('[/]', MesaController:: class .":mesaMasFacturo")->add(new SocioAuthMiddleware);
})->add(new JsonMiddleware);
$app->group('/mesaMenosFacturo', function (RouteCollectorProxy $group) {
    $group->get('[/]', MesaController:: class .":mesaMenosFacturo")->add(new SocioAuthMiddleware);
})->add(new JsonMiddleware);
$app->group('/cerrarMesa', function (RouteCollectorProxy $group) {
    $group->post('[/]', MesaController:: class .":cerrarMesa")->add(new SocioAuthMiddleware);
})->add(new JsonMiddleware);

///

/// INFORMES
$app->group('/masVendido', function (RouteCollectorProxy $group) {
    $group->get('[/]', PedidoController:: class .":loMasVendido")->add(new SocioAuthMiddleware);
})->add(new JsonMiddleware);
$app->group('/menosVendido', function (RouteCollectorProxy $group) {
    $group->get('[/]', PedidoController:: class .":loMenosVendido")->add(new SocioAuthMiddleware);
})->add(new JsonMiddleware);
$app->group('/operaciones', function (RouteCollectorProxy $group) {
    $group->get('[/{sector}]', EncargoController:: class .":cantidadOperacionesSector")->add(new SocioAuthMiddleware);
})->add(new JsonMiddleware);

$app->run();