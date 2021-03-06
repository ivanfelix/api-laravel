<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
        // Recibir datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json); // En objeto
        $params_array = json_decode($json, true); // En Array
        // Validar esos datos
        $validate = \Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if($validate->fails()){
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'Los datos no son validos.',
                'errors' => $validate->errors()
            );
        }else{
            // Cifrar la password
            $pwd = hash('sha256', $params->password);
            // Devolver token
            $data = array(
                'status' => 'success',
                'code' => 200,
                'token' => $jwtAuth->signup($params->email, $pwd)
            );
        }
        return response()->json($data, $data['code']);
    }
    public function update(Request $request){
        // Comprobar si el usuario está identificado
        $token = $request->header('Token');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        // Recoger los datos
        $json = $request->input('json', null);
        $params_array = json_decode($json, true); // En Array

        if($checkToken && !empty($params_array)){
        // Actualizar usuario
            // Obtener usuario validado desde el token -- Se obtiene porque se manda True al la función
            $user = $jwtAuth->checkToken($token, true);
            // Validar datos
            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|unique:users,id,' . $user->sub
            ]);
            if($validate->fails()){
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Los datos no son validos.',
                    'errors' => $validate->errors(),
                );
            }else{
                // Quitar los campos que no quiero actualizar
                unset($params_array['id']);
                unset($params_array['role']);
                unset($params_array['password']);
                unset($params_array['created_at']);
                unset($params_array['remember_token']);
                // Actualizar usuarios en BD
                $user_updated = User::where('id', $user->sub)->update($params_array);
                // Devolver array con resultados
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'user_updated' => $params_array,
                    'user' => $user
                );
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no esta identificado.'
            );
        }
        return response()->json($data, $data['code']);
    }
    public function upload(Request $request){
        // Recoger datos de la peticion
        $image = $request->file('file0');
        // Validar la imagen
        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);
        // Guardar imagen
        if(!$image || $validate->fails()){
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen.',
                'errors' => $validate->errors(),
            );
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));
            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }
        return response()->json($data, $data['code']);
    }
    public function getImage($filename){
        $isset = \Storage::disk('users')->exists($filename);
        if($isset){
            $file = \Storage::disk('users')->get($filename);
            return new Response($file, 200);
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe.'
            );
        }
        return response()->json($data, $data['code']);
    }
    public function getUser($id){
        $user = User::find($id);
        if(is_object($user)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe.'
            );
        }
        return response()->json($data, $data['code']);
    }
}