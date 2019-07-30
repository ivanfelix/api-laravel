<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;


class UserController extends Controller
{
    public function register(Request $request){
        // Recoger los datos del usuario
        $json = $request->input('json', null);
        $params = json_decode($json); // En objeto
        $params_array = json_decode($json, true); // En Array

        // Validar datos. Se validad que el usuario sea único.
        $validate = \Validator::make($params_array, [
            'name' => 'required|alpha',
            'surname' => 'required|alpha',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);
        if($validate->fails()){
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha creado',
                'errors' => $validate->errors()
            );
        }else{
            
            // Cifrar la contraseña
            $pwd = hash('sha256', $params->password);

            // Crear el usuario
            $user = new User();
            $user->name = $params_array['name'];
            $user->surname = $params_array['surname'];
            $user->email = $params_array['email'];
            $user->password = $pwd;
            $user->role = 'ROLE_USER';

            // Guardar el usuario
            $user->save();

            // Validación correcta
            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El usuario se creó correctamente',
                'request' => $params_array
            );
        }

        
        return response()->json($data, $data['code']);
    }
    
    public function login(Request $request){
        
        $jwtAuth = new \JwtAuth();

        $email = 'ivan@ivan.com';
        $password = '123';
        $pwd = hash('sha256', $password);

        return response()->json($jwtAuth->signup($email, $pwd));
    }
}
