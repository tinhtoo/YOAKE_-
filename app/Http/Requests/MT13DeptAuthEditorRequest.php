<?php

namespace App\Http\Requests;

use App\Models\MT13DeptAuth;
use Illuminate\Foundation\Http\FormRequest;

class MT13DeptAuthEditorRequest extends FormRequest
{

    public function rules()
    {
        return $rules = [
            'txtDeptAuthCd' => [
                'required',
                'regex:/^[a-zA-Z0-9]{1,6}$/',
                function ($attribute, $value, $fail) {
                    $input_data = $this->all();
                    // チェック　2001,'該当データが存在します。'
                    $hide_dept_auth_cd = $input_data['hideDeptAuthCd'];
                    if ($hide_dept_auth_cd == null) {
                        $check_dept_auth_cd = MT13DeptAuth::where('DEPT_AUTH_CD', $value)->exists();
                        if ($check_dept_auth_cd) {
                            $fail('2001');
                        }
                    }
                }
            ],
            'txtDeptAuthName' => [
                'required',
                'regex:/^[^|]+$/',
            ],
            'chkListSelect' => [
                'required',
            ],
        ];
    }
    public function messages()
    {
        // 2002メッセージ取得（必須入力項目です。)
        // 2025メッセージ取得(英数字以外の文字が含まれています。)
        // 4001メッセージ取得(禁則文字([|])が含まれています。)
        // 4002メッセージ取得(チェックが全てオフの時は更新できません。)

        return [
            'txtDeptAuthCd.required' => '2002',
            'txtDeptAuthCd.regex' => '2025',
            'txtDeptAuthName.required' => '2002',
            'txtDeptAuthName.regex' => '4001',
            'chkListSelect.required' => '4002',
        ];
    }
}
