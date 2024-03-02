<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteAffiliatesRequest extends FormRequest
{
    /**
     * The URI that users should be redirected to if validation fails.
     *
     * @var string
     */
    protected $redirect = '/';

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
        return [
            'affiliates' => ['file', 'required', 'mimes:json', 'extensions:txt', 'max:1024']
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'affiliates.required' => 'Error - A file containing a JSON string of the affiliate data is required.',
            'affiliates.mimes' => 'Error - It appears the string contained in the uploaded file isn\'t of valid JSON format.',
            'affiliates.extensions' => 'Error - The uploaded file\'s extension isn\'t supported.',
            'affiliates.max' => 'Error - The uploaded file cannot be greater than 1MB.'
        ];
    }
}
