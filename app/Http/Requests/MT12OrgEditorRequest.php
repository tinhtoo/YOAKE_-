<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class MT12OrgEditorRequest extends BaseRequest
{
    public function rules()
    {
        return $rules = [
            'txtUpHideCd' => [
                'required',
            ],
        ];
    }
    public function messages()
    {
        return [
            'txtUpHideCd.required' => '2002',
        ];
    }
}
