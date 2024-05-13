<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Marca;
use Exception;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MarcaController extends Controller
{
    public function index()
    {
        try {
            $marcas = Marca::all();
            return ApiResponse::success('Lista de marcas', 200, $marcas);

        } catch (Exception $e) {
            return ApiResponse::error('Error al Obtener la Lista de Marcas: '.$e->getMessage(),500);
        }

    }

    public function store(Request $request)
    {
        try {
            $request -> validate(['nombre' => 'required|unique:marcas']);
            $marca = Marca::create($request -> all());
            return  ApiResponse::success('Marca Creada Exitosamente', 201, $marca);

        } catch (ValidationException $e) {
            return ApiResponse::error('Error de Validación '. $e->getMessage(), 402);
        }
    }

    public function show($id)
    {
        try {
            $marca = Marca::findOrFail($id);
            return ApiResponse::success('Marca Obtenida Exitosamente', 200, $marca);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $marca = Marca::findOrFail($id);
            $request -> validate([
                'nombre' => ['required', Rule::unique('Marcas')->ignore($marca)]
            ]);
            $marca -> update($request-> all());
            return ApiResponse::success('Marca Actualizada Exitosamente', 200, $marca);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no encontrada', 404);
        } catch (Exception $e) {
            return ApiResponse::error('Error de Validacion: '. $e->getMessage(), 422);
        }
    }

    public function destroy($id)
    {
        try {
            $marca = Marca::findOrFail($id);
            $marca -> delete();
            return ApiResponse::success('Marca Eliminada Exitosamente', 200);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca No Encontrada', 404);
        }
    }

    public function productosPorMarca($id)
    {
        try {
            $marca = Marca::with('productos')->findOrFail($id);
            return ApiResponse::success('Marca y Lista de Productos', 200, $marca);

        } catch (ModelNotFoundException $e) {
            return ApiResponse::error('Marca no Encontrada', 404);
        }
    }
}