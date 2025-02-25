<?php

namespace App\Http\Requests;

use App\Models\MT16DeptShiftCalendar;
use Illuminate\Foundation\Http\FormRequest;

class MT05WorkPtnDeleteRequest extends FormRequest
{
    public function rules()
    {
        return $rules = [
            'WORKPTN_CD' => [
                function ($attribute, $value, $fail) {
                    $input_data = $this->all();
                    // 4030カレンダー情報で使用されているため削除できません。
                    $work_ptn_cd = $input_data['WORKPTN_CD'];
                    $check_work_ptn_cd = MT16DeptShiftCalendar::where('WORKPTN_CD', $work_ptn_cd)->exists();
                    if ($check_work_ptn_cd) {
                        $fail('4030');
                    }
                }
            ],
        ];
    }
}
