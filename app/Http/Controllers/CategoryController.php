<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller
{
    public function __construct(){
        $this->middleware('apiauth', ['except' => ['index', 'show']]);
    }
    public function index(){
        $categories = Category::all();
        $data = array(
            'status' => 'success',
            'code' => 200,
            'categories' => $categories
        );
        return response()->json($data, $data['code']);
    }
    public function show($id){
        $category = Category::find($id);
        if(is_object($category)){
            $data = array(
                'status' => 'success',
                'code' => 200,
                'category' => $category
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
        // Validar datos
        $validate = \Validator::make($params_array, [
            'name' => 'required'
        ]);
            // Guardar la categoría
            if($validate->fails()){
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'errors' => $validate->errors()
                );
            }else{
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();
                $data = array(
                    'status' => 'success',
                    'code' => 200,
                    'category' => $category
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
    public function update($id, Request $request){
        // Recoger los datos
        $json = $request->input('json', null);
        $params_array = json_decode($json, true); // En Array

        if(!empty($params_array)){
            $validate = \Validator::make($params_array, [
                'name' => 'required|alpha'
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
                unset($params_array['created_at']);
                // Actualizar usuarios en BD
                $category_updated = Category::where('id', $id)->update($params_array);
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
                'errors' => 'Ingresa un a categoría'
            );
        }
        return response()->json($data, $data['code']);
    }
}
