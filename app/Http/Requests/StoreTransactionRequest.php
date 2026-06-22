<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'regex:/^-?\d+(\.\d{1,2})?$/',
                'not_in:0,0.0,0.00,-0,-0.0,-0.00',
            ],
        ];
    }
}
