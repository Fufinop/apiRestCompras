<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use App\Models\Compra;
use App\Models\Producto;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class CompraController extends Controller
{
    public function index(){
        try {
            $compras = Compra::with('productos')->get();

            $comprasSeparadas = [];
            foreach ($compras as $compra) {
                $compraSeparada = [
                    'id' => $compra->id,
                    'subtotal' => $compra->subtotal,
                    'total' => $compra->total,
                    'productos' => $compra->productos
                ];
                $comprasSeparadas[] = $compraSeparada;
            }

            return ApiResponse::success('Lista de Compras', 200, $comprasSeparadas);

        } catch (Exception $e) {
            return ApiResponse::error('Error Inesperado', 500);
        }
    }

    public function store(Request $request)
{
    try {
        $productos = $request->input('productos');

        if (empty($productos)) {
            return ApiResponse::error('No se Proporcionaron Productos', 404);
        }

        $validator = Validator::make($request->all(),[
            'productos' => 'required|array',
            'productos.*.producto_id' => 'required|integer|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Datos Inválidos en la Lista de Productos', 400,$validator->errors());
        }

        $productoIds = array_column($productos, 'producto_id');
        if (count($productoIds) !== count(array_unique($productoIds))) {
            return ApiResponse::error('No se Permiten Productos Duplicados Para la Compra', 400);
        }

        $totalPagar = 0;
        $compraItems = [];

        foreach ($productos as $producto) {
            $productoB = Producto::find($producto['producto_id']);
            if (!$productoB) {
                return ApiResponse::error('Producto no Encontrado', 404);
            }

            if ($productoB->cantidad_disponible < $producto['cantidad']) {
                return ApiResponse::error('El Producto no Tiene Suficiente Cantidad Disponible', 404);
            }

            $subtotal = $productoB->precio * $producto['cantidad'];
            $totalPagar += $subtotal;

            $productoB->cantidad_disponible -= $producto['cantidad'];

            $compraItems[] = [
                'producto_id' => $productoB->id,
                'precio' => $productoB->precio,
                'cantidad' => $producto['cantidad'],
                'subtotal' => $subtotal
            ];
        }

        $compra = Compra::create([
            'subtotal' => $totalPagar,
            'total' => $totalPagar
        ]);

        $compra->productos()->attach($compraItems);

        return ApiResponse::success('Compra Realizada Exitosamente', 201,$compra);

    } catch (QueryException $e) {
        return ApiResponse::error('Error en la Consulta de Base de Datos', 500);
    } catch (Exception $e){
        return ApiResponse::error('Error Inesperado', 500);
    }
}


public function show($id)
{
    try {
        $compra = Compra::findOrFail($id);
        $productos = $compra->productos;

        $compraConProductos = [
            'id' => $compra->id,
            'subtotal' => $compra->subtotal,
            'total' => $compra->total,
            'productos' => $productos
        ];

        return ApiResponse::success('Detalles de la Compra', 200, $compraConProductos);

    } catch (ModelNotFoundException $e) {
        return ApiResponse::error('Compra no Encontrada', 404);
    } catch (Exception $e) {
        return ApiResponse::error('Error Inesperado', 500);
    }
}

}