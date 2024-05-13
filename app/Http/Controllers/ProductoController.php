<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Models\Producto;
use Exception;

class ProductoController extends Controller
{
    public function index()
    {
        try {
            $productos = Producto::all();
            return ApiResponse::success('Lista de Productos', 200, $productos);

        } catch (Exception $e) {
            return ApiResponse::error('Error al Obtener la Lista de Productos: ' . $e->getMessage(),500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request -> validate([
                'nombre' => 'required|unique:Productos',
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id'
            ]);

            $producto = Producto::create($request->all());
            return ApiResponse::success('Producto Creado Exitosamente',201,$producto);

        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->toArray();

            if (isset($errors['categoria_id'])) {
                $errors['categoria'] = $errors['categoria_id'];
                unset($errors['categoria_id']);
            }

            if (isset($errors['marca_id'])) {
                $errors['marca'] = $errors['marca_id'];
                unset($errors['marca_id']);
            }

            return ApiResponse::error('Errores de Validación', 422,$errors);
        }
    }

    public function show($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            return ApiResponse::success('Producto Obtenido Exitosamente', 200, $producto);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto no Encontrado', 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $request -> validate([
                'nombre' => 'required|unique:Productos,nombre,'.$producto->id,
                'precio' => 'required|numeric|between:0,999999.99',
                'cantidad_disponible' => 'required|integer',
                'categoria_id' => 'required|exists:categorias,id',
                'marca_id' => 'required|exists:marcas,id'
            ]);

            $producto -> update($request->all());
            return ApiResponse::success('Producto Actualizado Exitosamente',201,$producto);

        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->toArray();

            if (isset($errors['categoria_id'])) {
                $errors['categoria'] = $errors['categoria_id'];
                unset($errors['categoria_id']);
            }

            if (isset($errors['marca_id'])) {
                $errors['marca'] = $errors['marca_id'];
                unset($errors['marca_id']);
            }

            return ApiResponse::error('Errores de Validación', 422,$errors);
        } catch (ModelNotFoundException $e){
            return ApiResponse::error('Producto no encontrado');
        }
    }

    public function destroy($id)
    {
        try {
            $producto = Producto::findOrFail($id);
            $producto-> delete();

            return ApiResponse::success('Producto Eliminado Exitosamente', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Producto No Encontrado', 404);
        }
    }
}