<?php

namespace App\Http\Requests;

use App\Models\MT02CalendarPtn;
use Illuminate\Foundation\Http\FormRequest;

class MT02CalendarPtnRequest extends FormRequest
{
    public function rules()
    {
        $rules = [];

        $input_data = $this->request->all();
        $workptn = $input_data['WorkPtn'];
        $exit_check = MT02CalendarPtn::where(['CALENDAR_CD' => $input_data['CalendarCd']])->exists();

        if (!isset($input_data['CalendarCd'])
                || !isset($input_data['CalendarPtnName'])
                || $input_data['CalendarCd'] === '999'
                || $exit_check) {
            $rules = [
                    'CalendarCd' => [
                        'required',
                        function ($attribute, $value, $fail) {
                            // チェック２処理（CD-4004）'999は使用できません。'
                            if ($value === '999') {
                                $fail('4004');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            $exit_check = MT02CalendarPtn::where(['CALENDAR_CD' => $value])->exists();

                            if ($exit_check) {
                                // 新規登録で該当データあり
                                // チェック３処理（CD-2001）'該当データが存在します。'
                                $fail('2001');
                            }
                        }
                    ],
                    'CalendarPtnName' => ['required'],
                ];
            return $rules;
        }

        if ($workptn === '00' && $workptn !== '01') {
            $rules = [
                'ddlMonWorkPtn' => ['required'],
                'ddlTueWorkPtn' => ['required'],
                'ddlWedWorkPtn' => ['required'],
                'ddlThuWorkPtn' => ['required'],
                'ddlFriWorkPtn' => ['required'],
                'ddlSatWorkPtn' => ['required'],
                'ddlSunWorkPtn' => ['required'],
                'ddlHldWorkPtn' => ['required'],
            ];
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
