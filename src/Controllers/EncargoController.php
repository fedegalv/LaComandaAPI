<?php

namespace App\Controllers;

 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT;


use App\Models\Encargo;
use App\Models\Pedido;

class EncargoController{
    public function tomarEncargo(Request $request, Response $response)
    {
        $parsedBody = $request->getParsedBody();
        $codigoPedido = $parsedBody['codigo'];
        $tiempo_preparacion = $parsedBody['tiempo_preparacion'];
        $pedido = Pedido::get()
        ->where( 'codigo', '=', $codigoPedido)
        ->first();
        $token =  $request->getHeader('token');
        $decoded = JWT::decode($token[0], "laComanda", array('HS256'));

        switch ($decoded->tipo) {
            case 'bartender':
                $encargos = Encargo::where('sector', '=', 'bar')->where('codigo_pedido', '=', $codigoPedido)->get();
                foreach ($encargos as $encargo) {
                    $encargo->estado = "en preparacion";
                    $encargo->tiempo_preparacion += $tiempo_preparacion;
                    $encargo->save();
                }

                $pedido->tiempo_preparacion += $tiempo_preparacion;
                $pedido->estado = 'en preparacion';
                $pedido->save();
                
                $response->getBody()->write("Los encargos para {$codigoPedido} del sector BARRA TRAGOS Y VINOS estan en preparacion, llevara {$tiempo_preparacion}m");
                return $response->withStatus(200);
                break;
            
            case 'cervezero':
                $encargos = Encargo::where('sector', '=', 'cerveza')->where('codigo_pedido', '=', $codigoPedido)->get();
                foreach ($encargos as $encargo) {
                    $encargo->estado = "en preparacion";
                    $encargo->tiempo_preparacion += $tiempo_preparacion;
                    $encargo->save();
                }

                $pedido->tiempo_preparacion += $tiempo_preparacion;
                $pedido->estado = 'en preparacion';
                $pedido->save();

                $response->getBody()->write("Los encargos para {$codigoPedido} del sector BARRA CERVEZA estan en preparacion, llevara {$tiempo_preparacion}m");
                return $response->withStatus(200);
                break;
            case 'cocinero':
                $encargos = Encargo::where('sector', '=', 'cocina')->where('codigo_pedido', '=', $codigoPedido)->get();
                foreach ($encargos as $encargo) {
                    $encargo->estado = "en preparacion";
                    $encargo->tiempo_preparacion += $tiempo_preparacion;
                    $encargo->save();
                }
               

                $pedido->tiempo_preparacion += $tiempo_preparacion;
                $pedido->estado = 'en preparacion';
                $pedido->save();

                $response->getBody()->write("Los encargos para {$codigoPedido} del sector COCINA estan en preparacion, llevara {$tiempo_preparacion}m");
                return $response->withStatus(200);
                break;
                
            default:
                $response->getBody()->write("TIPO USUARIO NO RECONOCIDO");
                return $response->withStatus(400);
                return $response;
        }
    }

    public function terminarEncargo(Request $request, Response $response)
    {
        $parsedBody = $request->getParsedBody();
        $codigoPedido = $parsedBody['codigo'];
        $pedido = Pedido::get()
        ->where( 'codigo', '=', $codigoPedido)
        ->first();
        $token =  $request->getHeader('token');
        $decoded = JWT::decode($token[0], "laComanda", array('HS256'));

        switch ($decoded->tipo) {
            case 'bartender':
                $encargos = Encargo::where('sector', '=', 'bar')->where('codigo_pedido', '=', $codigoPedido)->get();
                foreach ($encargos as $encargo) {
                    $encargo->estado = "listo para servir";
                    $pedido->tiempo_preparacion -= $encargo->tiempo_preparacion;
                    $encargo->save();
                }
               
                
                $estadoPedido = $this->checkEstado($pedido, $codigoPedido);
                $pedido->save();
                $response->getBody()->write("Los encargos para {$codigoPedido} del sector BARRA TRAGOS Y VINOS ya estan listos para servir\n{$estadoPedido}");
                return $response->withStatus(200);
                break;
            
            case 'cervezero':
                $encargos = Encargo::where('sector', '=', 'cerveza')->where('codigo_pedido', '=', $codigoPedido)->get();
                foreach ($encargos as $encargo) {
                    $encargo->estado = "listo para servir";
                    $pedido->tiempo_preparacion -= $encargo->tiempo_preparacion;
                    $encargo->save();
                }

                $estadoPedido = $this->checkEstado($pedido, $codigoPedido);
                $pedido->save();
                $response->getBody()->write("Los encargos para {$codigoPedido} del sector BARRA CERVEZA ya estan listos para servir\n{$estadoPedido}");
                return $response->withStatus(200);
                break;
            case 'cocinero':
                $encargos = Encargo::where('sector', '=', 'cocina')->where('codigo_pedido', '=', $codigoPedido)->get();
                foreach ($encargos as $encargo) {
                    $encargo->estado = "listo para servir";
                    $pedido->tiempo_preparacion -= $encargo->tiempo_preparacion;
                    $encargo->save();
                }
                

                $estadoPedido = $this->checkEstado($pedido, $codigoPedido);
                $pedido->save();
                $response->getBody()->write("Los encargos para {$codigoPedido} del sector COCINA ya estan listos para servir\n{$estadoPedido}");
                return $response->withStatus(200);
                break;
                
            default:
                $response->getBody()->write("TIPO USUARIO NO RECONOCIDO");
                return $response->withStatus(400);
                return $response;
        }
    }
    public function cantidadOperacionesSector(Request $request, Response $response, $args)
    {
        $encargos = Encargo::where('sector', '=', $args['sector'])->get();
        $sector = $args['sector'];
        $operaciones = $encargos->count();
        $response->getBody()->write("Las operaciones totales para el sector {$sector} es {$operaciones}");
        return $response->withStatus(200);
    }

    public function checkEstado($pedido, $codigoPedido){
        $encargos = Encargo::where('codigo_pedido', '=', $codigoPedido)->get();
        $listo = false;
        foreach ($encargos as $encargo) {
            if( $encargo->estado == "listo para servir")
            {
                $listo= true;
            }
            else{
                $listo= false;
                break;
            }
        }

        if($listo == true && $pedido->tiempo_preparacion == 0)
        {
            $pedido->estado = "listo para servir";
            return "El pedido esta listo para servir";
        }
        else{
            return "El pedido todavia no esta completamente preparado.";
        }
    }
}
