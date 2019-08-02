<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;
use App\Post;

class PostController extends Controller
{
    public function __construct(){
        $this->middleware('apiauth', ['except' => ['index', 'show']]);
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
                'errors' => 'Ingresa un a categoría'
            );
        }
        return response()->json($data, $data['code']);
    }
}
