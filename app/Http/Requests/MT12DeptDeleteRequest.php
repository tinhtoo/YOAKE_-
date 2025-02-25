<?php

namespace App\Http\Requests;

use App\Models\MT10Emp;
use App\Models\MT12Dept;
use App\Models\MT13DeptAuth;
use App\Models\MT99Msg;
use App\Http\Requests\BaseRequest;

class MT12DeptDeleteRequest extends BaseRequest
{
    public function rules()
    {
        return $rules = [
            'delOneRowCdData' => [
                function ($attribute, $value, $fail) {
                    if (!isset($value)) {
                        return;
                    }

                    // 4015,'親部門として使用されているので削除できません。'
                    $up_dept_cd_exists = MT12Dept::where('UP_DEPT_CD', $value)->exists();
                    if ($up_dept_cd_exists) {
                        $msg_4015 = MT99Msg::where('MSG_NO', '4015')->pluck('MSG_CONT')->first();
                        $fail($msg_4015);
                        return;
                    }

                    // 4016,'部門権限情報で使用されているので削除できません。'
                    $deptAuth_dept_cd_exists = MT13DeptAuth::where('DEPT_CD', $value)->exists();
                    if ($deptAuth_dept_cd_exists) {
                        $msg_4016 = MT99Msg::where('MSG_NO', '4016')->pluck('MSG_CONT')->first();
                        $fail($msg_4016);
                        return;
                    }

                    // 4017,'社員情報で使用されているので削除できません。'
                    $emp_dept_cd_exists = MT10Emp::where('DEPT_CD', $value)->exists();
                    if ($emp_dept_cd_exists) {
                        $msg_4017 = MT99Msg::where('MSG_NO', '4017')->pluck('MSG_CONT')->first();
                        $fail($msg_4017);
                        return;
                    }
                }
            ],
        ];
    }
}
