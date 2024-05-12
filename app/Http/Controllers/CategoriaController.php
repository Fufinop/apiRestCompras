<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\Categoria;
use Illuminate\Validation\Rule;
use App\Http\Responses\ApiResponse;
use Exception;

class CategoriaController extends Controller
{
    public function index()
    {
        try {
            $categorias = Categoria::all();
            return ApiResponse::success('Lista de Categorias',200,$categorias);

            //throw new Exception("Error al obtener las categorias");
        } catch (Exception $e) {
            return ApiResponse::error('Error al obtener la lista de categorias: '.$e->getMessage(), 500);
        }

    }

    public function store(Request $request)
    {
        try {
            $request -> validate([
                'nombre' => 'required|unique:categorias'
            ]);

            $categoria = Categoria::create($request -> all());
            return ApiResponse::success('Categoria Creada Exitosamente', 201, $categoria);

        } catch (ValidationException $e) {
            return ApiResponse::error('Error de validación: '.$e->getMessage(),423);
        }
    }

    public function show($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            return ApiResponse::success('Categoria obtenida exitosamente', 200, $categoria);

        } catch (ModelNotFoundException  $e) {
            return ApiResponse::error('Categoria no encontrada', 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $request -> validate([
                'nombre' => ['required', Rule::unique('categorias') -> ignore($categoria)]
            ]);
            $categoria -> update($request -> all());
            return ApiResponse::success('Categoria Actualizada Exitosamente', 200, $categoria);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoria No Encontrada', 404);
        } catch (Exception $e){
            return ApiResponse::error('Error: '.$e->getMessage(),402);
        }
    }

    public function destroy($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
            $categoria -> delete();
            Return ApiResponse::success('Categoria Eliminada Exitosamente', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Categoria no encontrada', 404);
        }
    }

    public function productosPorCategoria($id)
    {
        # code
    }
}