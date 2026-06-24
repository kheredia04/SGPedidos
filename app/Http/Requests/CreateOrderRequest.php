<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }


    public function messages(): array{

        return [
            'items.required' => 'Se requiere al menos un artículo en el pedido.',
            'items.array' => 'Los artículos deben ser un arreglo.',
            'items.min' => 'Se requiere al menos un artículo en el pedido.',
            'items.*.product_id.required' => 'El ID del producto es obligatorio para cada artículo.',
            'items.*.product_id.integer' => 'El ID del producto debe ser un número entero.',
            'items.*.product_id.exists' => 'El producto especificado no existe.',
            'items.*.quantity.required' => 'La cantidad es obligatoria para cada artículo.',
            'items.*.quantity.integer' => 'La cantidad debe ser un número entero.',
            'items.*.quantity.min' => 'La cantidad debe ser al menos 1.',
        ];
    }
}
