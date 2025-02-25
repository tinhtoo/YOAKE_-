<?php

namespace App\Http\Requests;

use App\Models\MT10Emp;
use Illuminate\Foundation\Http\FormRequest;

class MT13DeptAuthDeleteRequest extends FormRequest
{
    public function rules()
    {
        return $rules = [
            'txtDeptAuthCd' => [
                function ($attribute, $value, $fail) {
                    $input_data = $this->all();
                    // チェック　4003,'社員情報で使用されているため削除できません。'
                    $dept_auth_cd = $input_data['txtDeptAuthCd'];
                    $check_emp_cd = MT10Emp::where('DEPT_AUTH_CD', $dept_auth_cd)->exists();
                    if ($check_emp_cd) {
                        $fail('4003');
                    }
                }
            ],
        ];
    }
}
