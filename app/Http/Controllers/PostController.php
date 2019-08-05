<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;
use App\Post;

class PostController extends Controller
{
    public function __construct(){
        $this->middleware('apiauth', ['except' => ['index', 'show','getPostsByCategory','getPostsByUser']]);
    }
    public function index(){
        $posts = Post::all()->load('category');
        $data = array(
            'status' => 'success',
            'code' => 200,
            'posts' => $posts
        );
        return response()->json($data, $data['code']);
    }
    public function show($id){
        $post = Post::find($id);
        if(is_object($post)){
            $data = array(
                'status' => 'success',
                'code' => 200,
                'post' => $post
            );
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'La categoría no existe'
            );
        }
        return response()->json($data, $data['code']);
    }
    public function store(Request $request){
        // Recoger los datos
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);
        if(!empty($params_array)){
            
            // Obtener los datos del usuario
            $jwtAuth = new \JwtAuth();
            $token = $request->header('Token', null); // toma el token que viene en la petición
            $user = $jwtAuth->checkToken($token, true);
            // Validar datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
            ]);
            // Guardar la categoría
            if($validate->fails()){
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'errors' => $validate->errors()
                );
            }else{
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params_array['category_id'];
                $post->title = $params_array['title'];
                $post->content = $params_array['content'];
                $post->image = $params_array['image'];
                $post->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'post' => $post
                );
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'errors' => 'Ingresa los datos.'
            );
        }
        return response()->json($data, $data['code']);
    }
    public function update($id, Request $request){
        // Recoger los datos
        $json = $request->input('json', null);
        $params_array = json_decode($json, true); // En Array

        if(!empty($params_array)){
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
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
                unset($params_array['user_id']);
                unset($params_array['created_at']);
                // Actualizar usuarios en BD
                $post_updated = Post::where('id', $id)->update($params_array);
                // Devolver array con resultados
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'category_updated' => $params_array
                );
            }
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'errors' => 'Ingresa los datos correctamente.'
            );
        }
        return response()->json($data, $data['code']);
    }
    public function destroy($id, Request $request){
        // Obtener los datos del usuario
        $jwtAuth = new \JwtAuth();
        $token = $request->header('Token', null); // toma el token que viene en la petición
        $user = $jwtAuth->checkToken($token, true);
        // Obtener el post
        $post = POST::where('id', $id)->where('user_id', $user->sub)->first();

        if(!empty($post)){
            $post->delete();
            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El post se elimino.'
            );
        }else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'errors' => 'El post no existe.'
            );
        }
        return response()->json($data, $data['code']);
    }
    public function getPostsByCategory($id, Request $request){
        $posts = Post::where('category_id', $id)->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'posts' => $posts
        );
        return response()->json($data, $data['code']);
    }
    public function getPostsByUser($id, Request $request){
        $posts = Post::where('user_id', $id)->get();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'posts' => $posts
        );
        return response()->json($data, $data['code']);
    }
}
