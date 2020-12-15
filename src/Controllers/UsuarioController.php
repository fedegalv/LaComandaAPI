<?php

namespace App\Controllers;


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Firebase\JWT\JWT;


use App\Models\Usuario;
use App\Models\Encargo;

class UsuarioController
{
    public function getAll(Request $request, Response $response, $args)
    {
        //CONSULTA PARA TRAER TODOS LOS RESULTADOS EN LA TABLA
        $rta = Usuario::get();

        
        $response->getBody()->write(json_encode($rta));
        return $response;
    }
    public function GetOne(Request $request, Response $response, $args)
    {
        //FIND BUSCA POR ID, DEVUELVE UN OBJETO ECONTRADO O NULL
        $rta = Usuario::find($args['id']);
        $response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function suspender(Request $request, Response $response, $args)
    {
        //BUSCA ALUMNO POR ID

        $usuario = Usuario::find($args['id']);

        $usuario->estado = "suspendido";


        //GUARDA EN BASE DE DATO, DEVUELVE TRUE OR FALSE
        $rta = $usuario->save();
        $response->getBody()->write("USUARIO SUSPENDIDO CON EXITO");
        return $response->withStatus(200);
    }
    public function delete(Request $request, Response $response, $args)
    {
        //BUSCA ALUMNO POR ID
        $alumno = Usuario::find($args['id']);

        //BORRA DE BASE DE DATO, DEVUELVE TRUE OR FALSE
        $rta = $alumno->delete();
        $response->getBody()->write("USUARIO BORRADO CON EXITO");
        return $response->withStatus(200);
    }

    public function registro(Request $request, Response $response)
    {
        $parsedBody = $request->getParsedBody();
        $usuario = new Usuario();
        $usuario->usuario = $parsedBody['usuario'];
        $usuario->password = $parsedBody['password'];
        $usuario->estado = "activo";
        $usuario->tipo = $parsedBody['tipo'];


        $rta = $usuario->save();
        if ($rta) {
            $response->getBody()->write("USUARIO REGISTRADO CON EXITO");
        } else {
            $response->getBody()->write("HUBO UN ERROR AL REGISTRAR");
            return $response->withStatus(400);
        }
        //$response->getBody()->write(json_encode($rta));
        return $response;
    }

    public function login(Request $request, Response $response)
    {

        $parsedBody = $request->getParsedBody();
        $usuario = $parsedBody['usuario'];
        $clave = $parsedBody['password'];
        
        
            //COMPARA SI EL MAIL Y CLVE ESTAN EN BD
            $rtaUsuario = Usuario::where('usuario', '=', $usuario)
                ->where('password', '=', $clave)->first();
            if ($rtaUsuario != null || $rtaUsuario != false) {
                $tipo = $rtaUsuario->tipo;
                $payload = array(
                    "usuario" => $usuario,
                    "tipo" => $tipo,
                    "id" => $rtaUsuario->id
                );
                $jwt = JWT::encode($payload, "laComanda");
                $response->getBody()->write(json_encode($jwt));
                return $response->withStatus(200);
            } else {
                $response->getBody()->write("LOGIN INCORRECTO");
                return $response->withStatus(400);
                return $response;
            }
    }
    

    
}
