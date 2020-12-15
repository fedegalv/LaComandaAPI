<?php

namespace App\Controllers;

 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT;


use App\Models\Mesa;
use App\Models\Pedido;

class MesaController{
    public function getAll(Request $request, Response $response, $args)
    {
        //CONSULTA PARA TRAER TODOS LOS RESULTADOS EN LA TABLA
        $rta = Mesa::get();

        
        $response->getBody()->write(json_encode($rta));
        return $response;
    }
    public function getByCodigoPedido(Request $request, Response $response, $args)
    {
        $parsedBody = $request->getParsedBody();
        $codigoPedido = $parsedBody['codigo'];
        //FIND BUSCA POR ID, DEVUELVE UN OBJETO ECONTRADO O NULL
        $rta = Mesa::where("codigo_pedido", "=", $codigoPedido)->first();
        $response->getBody()->write(json_encode($rta));
        return $response;
    }
    public function getByCodigoMesa(Request $request, Response $response, $args)
    {
        //FIND BUSCA POR ID, DEVUELVE UN OBJETO ECONTRADO O NULL
        $rta = Mesa::where("codigo_mesa", "=", $args['codigoMesa'])->first();
        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function clientePagandoMesa(Request $request, Response $response, $args)
    {
        $parsedBody = $request->getParsedBody();
        $codigoMesa = $parsedBody['codigo'];

        $mesa = Mesa::where("codigo_mesa", "=", $codigoMesa)->first();
        if($mesa-> estado == "con clientes comiendo")
        {
            $mesa->estado = "con clientes pagando";
            $mesa->save();
            $response->getBody()->write("Clientes de la mesa {$codigoMesa} estan pagando");
            return $response->withStatus(200);
        }
        else{
            $response->getBody()->write("Clientes de la mesa {$codigoMesa} no pueden pagar, pedido no entregado o ya se cobro anteriormente");
            return $response->withStatus(400);
        }
    }
    //SOLO SOCIOS
    public function cerrarMesa(Request $request, Response $response, $args)
    {
        $parsedBody = $request->getParsedBody();
        $codigoMesa = $parsedBody['codigo'];

        $mesa = Mesa::where("codigo_mesa", "=", $codigoMesa)->first();
        $mesa->estado = "cerrada";
        $mesa->save();
        $response->getBody()->write("La mesa {$codigoMesa} fue cerrada");
        return $response->withStatus(200);
    }
}