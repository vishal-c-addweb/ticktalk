<?php

namespace App\Http\Requests\Discussion;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'discussion_category' => 'required',
            'title' => 'required',
            'description' => [
                'required',
                function ($attribute, $value, $fail) {
                    $commnet = trim(str_replace('<p><br></p>', '', $value));

                    if ($commnet == '') {
                        $fail(ucfirst($attribute) . ' ' . __('app.required'));
                    }
                }
            ]
        ];
    }

}