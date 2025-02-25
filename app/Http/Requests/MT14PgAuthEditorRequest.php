<?php

namespace App\Http\Requests;

use App\Models\MT14PgAuth;
use Illuminate\Foundation\Http\FormRequest;

class MT14PgAuthEditorRequest extends FormRequest
{

    public function rules()
    {
        return $rules = [
            'PG_AUTH_CD' =>[
                'required',
                'regex:/^[a-zA-Z0-9]{1,6}$/',
                function ($attribute, $value, $fail) {
                    $input_dt = $this->all();
                    $changeAuthCd = $input_dt['change'];

                    $check = MT14PgAuth::where('PG_AUTH_CD', $value)->exists();
                    if ($changeAuthCd == null && $check) {
                         // 2001メッセージ取得（該当データが存在します。)
                         $fail('2001');
                    }
                }
            ],
            'PG_AUTH_NAME' => [
                'required',
                'regex:/^[^|]+$/',
            ],
            'checkList'=> [
                'required',
            ],
        ];
    }
    public function messages()
    {
        // 2002メッセージ取得（必須入力項目です)
        // 4002メッセージ取得(チェックが全てオフの時は更新できません。)
        // 2025メッセージ取得(英数字以外の文字が含まれています。)
        // 4001メッセージ取得(禁則文字([|])が含まれています。)

        return [
            'PG_AUTH_CD.required' => '2002',
            'PG_AUTH_CD.regex' =>'2025',
            'PG_AUTH_NAME.required' => '2002',
            'PG_AUTH_NAME.regex'=> '4001',
            'checkList.required' => '4002',
        ];
    }
}
