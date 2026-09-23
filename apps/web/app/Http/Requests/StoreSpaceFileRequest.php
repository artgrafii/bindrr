<?php

namespace App\Http\Requests;

use App\Spaces\SpaceFilename;
use App\Spaces\UploadLimit;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class StoreSpaceFileRequest extends FormRequest
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
            'file' => ['required', 'file', 'max:'.UploadLimit::kilobytes()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Choose a file to upload.',
            'file.max' => UploadLimit::message(),
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $file = $this->file('file');

                if (! $file instanceof UploadedFile) {
                    return;
                }

                $name = $file->getClientOriginalName();
                $path = str_replace('\\', '/', $file->getClientOriginalPath());

                if ($path !== $name || ! SpaceFilename::isSafe($name)) {
                    $validator->errors()->add('file', SpaceFilename::rejectionMessage($path));
                }
            },
        ];
    }
}
