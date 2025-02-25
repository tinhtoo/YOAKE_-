<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\MT02CalendarPtn;

class MT02CalendarPtnUpdRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            'CalendarCd' => [
                'required',
                function ($attribute, $value, $fail) {
                    // チェック２処理（CD-4004）'999は使用できません。'
                    if ($value == '999') {
                        $fail('4004');
                    }
                },
                function ($attribute, $value, $fail) {
                    $changeCd = $this->request->get('changeCd');
                    $exitCheck = MT02CalendarPtn::where(['CALENDAR_CD' => $value])->exists();
                    if (!$exitCheck) {
                        // 更新で該当データなし
                        // チェック３処理（CD-2000）'該当データが存在しません。'
                        $fail('2000');
                    }
                }
            ],
        ];

        $input_data = $this->request->all();
        if (is_nullorwhitespace($input_data['CalendarPtnName'])) {
            $rules = ['CalendarPtnName' => ['required']];
            return $rules;
        }

        $workptn = $input_data['WorkPtn'];
        if ($workptn === '00' && $workptn !== '01') {
            $rules = array_merge($rules, array(
                    'ddlMonWorkPtn' => ['required'],
                    'ddlTueWorkPtn' => ['required'],
                    'ddlWedWorkPtn' => ['required'],
                    'ddlThuWorkPtn' => ['required'],
                    'ddlFriWorkPtn' => ['required'],
                    'ddlSatWorkPtn' => ['required'],
                    'ddlSunWorkPtn' => ['required'],
                    'ddlHldWorkPtn' => ['required'],
                ));
            return $rules;
        }
        return $rules;
    }

    public function messages()
    {
        // （CD-2002）'必須入力項目です。'
        return [
            'CalendarCd.required' => '2002',
            'CalendarPtnName.required' => '2002',
            'ddlMonWorkPtn.required' => '2002',
            'ddlTueWorkPtn.required' => '2002',
            'ddlWedWorkPtn.required' => '2002',
            'ddlThuWorkPtn.required' => '2002',
            'ddlFriWorkPtn.required' => '2002',
            'ddlSatWorkPtn.required' => '2002',
            'ddlSunWorkPtn.required' => '2002',
            'ddlHldWorkPtn.required' => '2002',
        ];
    }
}
