<?php

namespace App\Http\Requests;

use App\Spaces\InvalidSpaceFilename;
use App\Spaces\SpaceFilename;
use App\Spaces\UploadLimit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreSpaceNoteRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:180'],
            'body' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.max' => 'Keep the file name under 180 characters.',
            'body.required' => 'Write something to save.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $validator->errors()->has('body') && strlen($this->string('body')->toString()) > UploadLimit::bytes()) {
                    $validator->errors()->add('body', UploadLimit::message());
                }

                if ($validator->errors()->has('name')) {
                    return;
                }

                try {
                    SpaceFilename::noteName($this->input('name'));
                } catch (InvalidSpaceFilename $exception) {
                    $validator->errors()->add('name', $exception->getMessage());
                }
            },
        ];
    }
}
