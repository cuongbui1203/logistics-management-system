<?php

namespace App\Http\Requests\WorkPlate;

use App\Enums\AddressTypeEnum;
use App\Http\Requests\FormRequest;
use App\Rules\VungRule;
use Illuminate\Validation\Rule;

class CreateWPRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // dd('s');
        return [
            'name' => 'required',
            'address_id' => ['required', Rule::exists("sqlite_vn_map.wards", "code")],
            'typeId' => 'required|exists:types,id',
            'vung' => ['required', new VungRule()],
        ];
    }
}
