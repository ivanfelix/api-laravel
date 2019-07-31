<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{

    public $key;
    public $signup;

    public function __construct(){
        $this->key = 'esta_es_la_key';
        $this->signup = false;
    }

    public function  signup($email, $password, $getToken = null){

        // Buscar si existe el usuario
        $user = User::where([
            'email' => $email,
            'password' => $password
        ])->first();

        // Comprobar si son correctas
        if(is_object($user)){
            $this->signup = true;
        }

        // Generar el token con los datos del usuario identificado
        if($this->signup){
            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' =>  $user->name,
                'surname' => $user->surname,
                'iat' => time(),
                'exp' => time() + (7*24*60*60)
            );
            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            // Devolver los datos decodificados o el token, en función de un parametro
            if(is_null($getToken)){
                $data = $jwt;
            }else{
                $data = $decoded;
            }
        }else{
            $data = array(
                'status' => 'error',
                'message' => 'Login incorrecto'
            );
        }
        return $data;
    }
    public function checkToken($jwt, $getIdentity = false){
        $auth = false;
        try{
            $token_decoded = JWT::decode($jwt, $this->key, ['HS256']);
        }catch(\UnexpectedValueException $e){
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }
        if(!empty($token_decoded) && is_object($token_decoded) && isset($token_decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }
        if($getIdentity){
            return $token_decoded;
        }
        return $auth;
    }
}