<?php
namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
//AGREGAR RequestHandler;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
class JsonMiddleware{
    public function __invoke(Request $request, RequestHandler $handler){
        //obtiene la respuesta PARA PROCESAR
        $response = $handler->handle($request);
        //OBTIENE EL BODY
        //$existingContent = (string) $response->getBody();
    
        //$response = new Response();
        //$response->getBody()->write('BEFORE'.$existingContent);
        // DE ESTA MANERA CONVERTIRMOS AL RESPONS EN JSON
        $response= $response->withHeader('Content-type','application/json');
        return $response;
    }
}