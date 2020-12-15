<?php

namespace App\Controllers;

use App\Models\Producto;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class ProductoController
{
    public function getAll(Request $request, Response $response, $args)
    {
        //CONSULTA PARA TRAER TODOS LOS RESULTADOS EN LA TABLA
        $rta = Producto::get();

        $response->getBody()->write(json_encode($rta));
        return $response;
    }
    public function GetOne(Request $request, Response $response, $args)
    {
        //FIND BUSCA POR ID, DEVUELVE UN OBJETO ECONTRADO O NULL
        $rta = Producto::find($args['id']);
        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function update(Request $request, Response $response, $args)
    {
        $parsedBody = $request->getParsedBody();
        //BUSCA ALUMNO POR ID
        //$data = $args['id'];
        $producto = Producto::find($args['id']);

        //EL BODY DEBE VENIR COMPLETO
        $producto->descripcion = $parsedBody['descripcion'];
        $producto->precio = $parsedBody['precio'];


        //GUARDA EN BASE DE DATO, DEVUELVE TRUE OR FALSE
        $rta = $producto->save();
        $response->getBody()->write(json_encode($rta));
        return $response;
    }
    public function delete(Request $request, Response $response, $args)
    {
        //BUSCA ALUMNO POR ID
        $producto = Producto::find($args['id']);
        if ($producto == null) {
            $response->getBody()->write("Producto no encontrado");
            return $response->withStatus(400);
        }
        //BORRA DE BASE DE DATO, DEVUELVE TRUE OR FALSE
        $rta = $producto->delete();
        $response->getBody()->write("Producto {$args['id']} fue borrado con exito");
        return $response;
    }

    public function add(Request $request, Response $response)
    {
        $parsedBody = $request->getParsedBody();
        $producto = new Producto();
        $producto->descripcion = $parsedBody['descripcion'];
        $producto->precio = $parsedBody['precio'];
        $producto->sector = $parsedBody['sector'];

        $rta = $producto->save();
        if ($rta) {
            $response->getBody()->write("PRODUCTO {$producto->descripcion} REGISTRADO CON EXITO");
        } else {
            $response->getBody()->write("HUBO UN ERROR AL REGISTRAR PRODUCTO");
            return $response->withStatus(400);
        }
        //$response->getBody()->write(json_encode($rta));
        return $response;
    }

}