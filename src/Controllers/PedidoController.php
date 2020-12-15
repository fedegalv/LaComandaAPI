<?php

namespace App\Controllers;

 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Encargo;
use App\Models\Auxiliar;
use App\Models\Mesa;

class PedidoController{
    public function getAll(Request $request, Response $response, $args)
    {
        //CONSULTA PARA TRAER TODOS LOS RESULTADOS EN LA TABLA
        $rta = Pedido::get();

        
        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function getPendiente(Request $request, Response $response)
    {
        $token =  $request->getHeader('token');
        $decoded = JWT::decode($token[0], "laComanda", array('HS256'));

        switch ($decoded->tipo) {
            case 'bartender':
                $encargos = Encargo::where('sector', '=', 'bar')->where('estado', '=', 'pendiente')->get();
                $response->getBody()->write(json_encode($encargos));
                return $response->withStatus(200);
                break;
            
            case 'cervezero':
                $encargos = Encargo::where('sector', '=', 'cerveza')->where('estado', '=', 'pendiente')->get();
                $response->getBody()->write(json_encode($encargos));
                return $response->withStatus(200);
                break;
            case 'cocinero':
                $encargos = Encargo::where('sector', '=', 'cocina')->where('estado', '=', 'pendiente')->get();
                $response->getBody()->write(json_encode($encargos));
                return $response->withStatus(200);
                break;
                
            default:
                $response->getBody()->write("TIPO USUARIO NO RECONOCIDO");
                return $response->withStatus(400);
                return $response;
        }
    }
    public function add(Request $request, Response $response)
    {
        $parsedBody = $request->getParsedBody();
        $facturaTotal= 0;
        $codigo = Auxiliar::generarCodigo();
        $codigoMesa = Auxiliar::generarCodigo();
        //echo $codigo;
        
        $pedidoTexto= "Se pidio: \n";
        //$encargos = $request->getParsedBody()['encargos'];
        $encargos = json_decode($request->getParsedBody()['encargos']);
        //var_dump($comidas);
        foreach ($encargos as $item) {
            $idProducto = $item->id_producto;

            $encargo = new Encargo();
            
            //
            $producto = Producto::get()
            ->where( 'id', '=', $idProducto )
            ->first();
            //AGREGAR VERIFICACION QUE EXISTE

            $encargo->codigo_pedido = $codigo;
            $encargo->id_producto =  $producto['id'];
            $encargo->cantidad = $item->cantidad;
            $encargo->estado = "pendiente";
            $encargo->sector = $producto['sector'];
            $encargo->tiempo_preparacion = 0;
            $encargo->save();

            $pedidoTexto .= "{$producto->descripcion} - {$encargo->cantidad} unidades - PRECIO x U: {$producto->precio}\n";

            //TOTAL ENCARGO
            $totalEncargo = $producto->precio * $encargo->cantidad;
            //SUMA FACTURA
            $facturaTotal += $totalEncargo;
        }
        //DECODE DE TOKEN PARA SACAR EL ID DEL MOZO
        $token =  $request->getHeader('token');
        $decoded = JWT::decode($token[0], "laComanda", array('HS256'));

        //PEDIDO
        $pedido = new Pedido;
        $pedido->estado = 'pendiente';
        $pedido->tiempo_preparacion = 0;
        $pedido->codigo = $codigo;
        $pedido->factura =  $facturaTotal;
        $pedido->id_mozo = $decoded->id;
        $pedido->nombre_cliente = $parsedBody['nombre_cliente'];

        //MESA
        $mesa = new Mesa();
        $mesa->codigo_mesa = $codigoMesa;
        $mesa->codigo_pedido = $codigo;
        $mesa->estado = "con clientes esperando pedido";
        $mesa->facturacion = $facturaTotal;
        $mesa->save();

        $rta = $pedido->save();
        if ($rta) {
            $response->getBody()->write("PEDIDO CODIGO:'{$pedido->codigo}' REGISTRADO CON EXITO, ASIGNADO A MESA CODIGO: '{$mesa->codigo_mesa}'\n{$pedidoTexto}\nTOTAL: {$facturaTotal}");

        } else {
            $response->getBody()->write("HUBO UN ERROR AL REGISTRAR EL PEDIDO");
            return $response->withStatus(400);
        }
        //$response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function verEstadoPedidos(Request $request, Response $response)
    {
        $rta = Pedido::get();

        $response->getBody()->write(json_encode($rta));
        return $response;
    }
    public function getCompleto(Request $request, Response $response)
    {
        $rta = Pedido::where('estado', '=', 'listo para servir')->get();
        $response->getBody()->write(json_encode($rta));
        return $response;
    }
    public function servirPedido(Request $request, Response $response)
    {
        $parsedBody = $request->getParsedBody();
        $codigoPedido = $parsedBody['codigo'];
        $pedido = Pedido::where('codigo', '=', $codigoPedido)->first();
        if($pedido->estado == "listo para servir")
        {
            $encargos = Encargo::where('codigo_pedido', '=', $codigoPedido)->get();
            foreach ($encargos as $encargo) {
                $encargo->estado = "servido";
                $encargo->save();
            }
            $mesa = Mesa::where('codigo_pedido', '=', $codigoPedido)->first();
            $mesa->estado = "con clientes comiendo";
            $pedido->estado = "servido";
            $mesa->save();
            $pedido->save();
            $response->getBody()->write("PEDIDO CODIGO: '{$codigoPedido}' SERVIDO A MESA CODIGO: '{$mesa->codigo_mesa}'");
            return $response->withStatus(200);
        }
        else{
            $response->getBody()->write("NO SE PUDO SERVIR EL PEDIDO, AUN NO ESTA COMPLETO");
            return $response->withStatus(400);
        }
    }

    public function loMasVendido(Request $request, Response $response)
    {
        $productos = Producto::get();
        $encargos = Encargo::get();
        $cont = 0;
        $cantMax= 0;
        foreach ($productos as $producto) {
            foreach ($encargos as $encargo) {
                if($encargo->id_producto == $producto->id)
                {
                    $cont += $encargo->cantidad;
                }
                
            }
            if($cont > $cantMax)
                {
                    $cantMax = $cont;
                    $descripcionMasVendido = $producto->descripcion;
                }
            $cont = 0;
        }
        $response->getBody()->write("El producto mas pedido es: '{$descripcionMasVendido}' con: {$cantMax} unidades");
        return $response->withStatus(200);
       
    }
    public function loMenosVendido(Request $request, Response $response)
    {
        $productos = Producto::get();
        $encargos = Encargo::get();
        $cont = 0;
        $cantMin= PHP_INT_MAX;
        foreach ($productos as $producto) {
            foreach ($encargos as $encargo) {
                if($encargo->id_producto == $producto->id)
                {
                    $cont += $encargo->cantidad;
                }
                
            }
            if($cont < $cantMin)
                {
                    $cantMin = $cont;
                    $descripcionMenosVendido = $producto->descripcion;
                }
            $cont = 0;
        }
        $response->getBody()->write("El producto menos pedido es: '{$descripcionMenosVendido}' con: {$cantMin} unidades");
        return $response->withStatus(200);
       
    }
    


}