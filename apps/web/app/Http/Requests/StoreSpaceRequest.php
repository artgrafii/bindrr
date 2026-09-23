<?php

namespace App\Http\Requests;

use App\Rules\ProducesSpaceSlug;
use App\Spaces\SpaceStore;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80', new ProducesSpaceSlug],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Give this space a name.',
            'name.max' => 'Keep the name under 80 characters.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('name')) {
                    return;
                }

                $slug = Str::slug($this->string('name')->toString());

                if (app(SpaceStore::class)->find($slug) !== null) {
                    $validator->errors()->add('name', 'You already have a space with that name.');
                }
            },
        ];
    }
}
