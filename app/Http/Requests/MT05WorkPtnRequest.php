<?php

namespace App\Http\Requests;

use App\Models\MT05Workptn;
use App\Models\MT99Msg;
use App\Models\MT94WorkDesc;
use Illuminate\Foundation\Http\FormRequest;

class MT05WorkPtnRequest extends FormRequest
{
    public function rules()
    {
        return $rules = [
            'workPtnCd' =>[
                'required',
                function ($attribute, $value, $fail) {
                    $input_dt = $this->all();
                    $hideCdAddNew = $input_dt['hideCdAddNew'];
                    // 4004,'999は使用できません。'
                    if ($hideCdAddNew == null && $value == '999') {
                        $msg_4004 = MT99Msg::where('MSG_NO', '4004')->pluck('MSG_CONT')->first();
                        $fail($msg_4004);
                        return;
                    }
                    // 2001(該当データが存在します。)
                    $check = MT05Workptn::where('WORKPTN_CD', $value)->exists();
                    if ($hideCdAddNew == null && $check) {
                         $msg_2001 = MT99Msg::where('MSG_NO', '2001')->pluck('MSG_CONT')->first();
                         $fail($msg_2001);
                         return;
                    }
                }
            ],
            'workPtnName' => ['required'],
            'workPtnAbrName'=> ['required'],

            // {時間帯設定}
            'pTime.0' => [
                function ($attribute, $value, $fail) {
                    $input_dt = $this->all();
                    $pTimeCds = $input_dt['pTime'];
                    foreach ($pTimeCds as $pTimeCd) {
                        $pTmCd[] = MT94WorkDesc::where('WORK_DESC_CD', $pTimeCd['pTimeCds'])
                                                    ->pluck('WORK_DESC_CLS_CD')->first();
                    }
                    if (!(in_array("00", $pTmCd))) {
                        // 4014,'就業時間が設定されていません。'
                        $msg_4014 = MT99Msg::where('MSG_NO', '4014')->pluck('MSG_CONT')->first();
                        $fail($msg_4014);
                        return;
                    }
                }
            ],
            'pTime.*' => [
                function ($attribute, $value, $fail) {
                    if (!is_nullorwhitespace($value['pTimeCds'].
                                            $value['StrHH'].
                                            $value['StrMI'].
                                            $value['EndHH'].
                                            $value['EndMI'])) {
                        // 必須入力項目です。
                        if (in_array(null, $value, true)) {
                            $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                            $fail($msg_2002);
                            return;
                        }

                        // 2009開始値と終了値の大小関係が不正です。
                        $valStr = substr('0' .$value['StrHH'], -2).substr('0' .$value['StrMI'], -2);
                        $valEnd = substr('0' .$value['EndHH'], -2).substr('0' .$value['EndMI'], -2);
                        if ($valEnd <= $valStr) {
                            $msg_2009 = MT99Msg::where('MSG_NO', '2009')->pluck('MSG_CONT')->first();
                            $fail($msg_2009);
                            return;
                        }
                    }

                    // 4019,'項目が重複しています。'
                    $input_dt = $this->all();
                    $pTmCdData = $input_dt['pTime'];
                    $count = 0;
                    foreach ($pTmCdData as $pTmCd) {
                        if (!empty($value['pTimeCds'])) {
                            if ($pTmCd['pTimeCds'] === $value['pTimeCds']) {
                                $count++;
                                if ($count > 1) {
                                    $msg_4019 = MT99Msg::where('MSG_NO', '4019')->pluck('MSG_CONT')->first();
                                    $fail($msg_4019);
                                    return;
                                }
                            }
                        }
                    }
                }
            ],
            'extTime.*' => [
                function ($attribute, $value, $fail) {
                    $input_dt = $this->all();
                    // 2002メッセージ取得（必須入力項目です)
                    if (!is_nullorwhitespace($value['excCd'].
                                             $value['extStrHH'].
                                             $value['extStrMI'].
                                             $value['extEndHH'].
                                             $value['extEndMI'])) {
                        if (in_array(null, $value, true)) {
                            $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                            $fail($msg_2002);
                            return;
                        }
                        $pTimeStr = 0000;
                        $pTimeEnd = 0000;
                        // 2009開始値と終了値の大小関係が不正です。
                        $valStr = substr('0' .$value['extStrHH'], -2).substr('0' .$value['extStrMI'], -2);
                        $valEnd = substr('0' .$value['extEndHH'], -2).substr('0' .$value['extEndMI'], -2);
                        if ($valEnd <= $valStr) {
                            $msg_2009 = MT99Msg::where('MSG_NO', '2009')->pluck('MSG_CONT')->first();
                            $fail($msg_2009);
                            return;
                        }
                        // 4012,'割増対象時間帯は就業時間の時間帯に含まれるようにして下さい。'
                        $pTimeCds = $input_dt['pTime'];
                        // WORK_DESC_CLS_CD='00'(就業時間))の{開始時間}(n)(時・分)と{終了時間}(n)(時・分)取得
                        foreach ($pTimeCds as $pTimeCd) {
                            if (MT94WorkDesc::where('WORK_DESC_CD', $pTimeCd['pTimeCds'])
                                            ->pluck('WORK_DESC_CLS_CD')->first() == '00') {
                                $pTimeStr = substr('0' .$pTimeCd['StrHH'], -2).substr('0' .$pTimeCd['StrMI'], -2);
                                $pTimeEnd = substr('0' .$pTimeCd['EndHH'], -2).substr('0' .$pTimeCd['EndMI'], -2);
                                break;
                            }
                        }
                        $valStr = substr('0' .$value['extStrHH'], -2).substr('0' .$value['extStrMI'], -2);
                        $valEnd = substr('0' .$value['extEndHH'], -2).substr('0' .$value['extEndMI'], -2);
                        // TODO チェックの優先順位を確認
                        if (!($valStr >= $pTimeStr && $valEnd <= $pTimeEnd)) {
                            $msg_4012 = MT99Msg::where('MSG_NO', '4012')->pluck('MSG_CONT')->first();
                            $fail($msg_4012);
                            return;
                        }
                    }

                    // 4019,'項目が重複しています。'
                    $extTimes = $input_dt['extTime'];
                    $count = 0;
                    foreach ($extTimes as $extTime) {
                        if (!empty($value['excCd'])) {
                            if ($extTime['excCd'] === $value['excCd']) {
                                $count++;
                                if ($count > 1) {
                                    $msg_4019 = MT99Msg::where('MSG_NO', '4019')->pluck('MSG_CONT')->first();
                                    $fail($msg_4019);
                                    return;
                                }
                            }
                        }
                    }
                }
            ],
            'rsvTime' => [
                function ($attribute, $value, $fail) {
                    if (in_array(null, $value)) {
                        // 2002メッセージ取得（必須入力項目です)
                        $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                        $fail($msg_2002);
                        return;
                    }
                }
            ],
            'fstrdTime' => [
                function ($attribute, $value, $fail) {
                    if (in_array(null, $value)) {
                        // 2002メッセージ取得（必須入力項目です)
                        $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                        $fail($msg_2002);
                        return;
                    }
                }
            ],
            'scdprdTime' => [
                function ($attribute, $value, $fail) {
                    if (in_array(null, $value)) {
                        // 2002メッセージ取得（必須入力項目です)
                        $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                        $fail($msg_2002);
                        return;
                    }
                }
            ],
            'tmDailyTime' => [
                function ($attribute, $value, $fail) {
                    if (in_array(null, $value)) {
                        // 2002メッセージ取得（必須入力項目です)
                        $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                        $fail($msg_2002);
                        return;
                    }
                }
            ],

            // {時間数設定}
            'nTime.*' => [
                function ($attribute, $value, $fail) {
                    $input_dt = $this->all();
                    $nTimesDt = $input_dt['nTime'];
                    // 2002メッセージ取得（必須入力項目です)
                    // 職務種別が含まれてる行
                    for ($i = 1; $i<=6; $i++) {
                        if ($nTimesDt[$i]) {
                            if (!is_nullorwhitespace($value['nTimeDclsCd'].
                                                     $value['nTimeCd'].
                                                     $value['nTimeStrHH'].
                                                     $value['nTimeStrMI'].
                                                     $value['nTimeEndHH'].
                                                     $value['nTimeEndMI'])) {
                                if (in_array(null, $value, true)) {
                                    $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                                    $fail($msg_2002);
                                    return;
                                }
                                // 2009開始値と終了値の大小関係が不正です。
                                $valStr = substr('0' .$value['nTimeStrHH'], -2).substr('0' .$value['nTimeStrMI'], -2);
                                $valEnd = substr('0' .$value['nTimeEndHH'], -2).substr('0' .$value['nTimeEndMI'], -2);
                                if ($valEnd <= $valStr) {
                                    $msg_2009 = MT99Msg::where('MSG_NO', '2009')->pluck('MSG_CONT')->first();
                                    $fail($msg_2009);
                                    return;
                                }
                            }
                        }
                    }
                    // 4019,'項目が重複しています。'
                    $nTimes = $input_dt['nTime'];
                    $count = 0;
                    foreach ($nTimes as $nTime) {
                        if (!empty($value['nTimeCd'])) {
                            if ($nTime['nTimeCd'] === $value['nTimeCd']) {
                                $count++;
                                if ($count > 1) {
                                    $msg_4019 = MT99Msg::where('MSG_NO', '4019')->pluck('MSG_CONT')->first();
                                    $fail($msg_4019);
                                    return;
                                }
                            }
                        }
                    }
                }
            ],
            'nTime.0' => [
                function ($attribute, $value, $fail) {
                    $input_dt = $this->all();
                    $nTimeCds = $input_dt['nTime'];

                    foreach ($nTimeCds as $nTmCd) {
                        $nTmCds[] = MT94WorkDesc::where('WORK_DESC_CD', $nTmCd['nTimeCd'])
                                                ->pluck('WORK_DESC_CLS_CD')->first();
                    }
                    if (!(in_array("00", $nTmCds))) {
                        // 4014,'就業時間が設定されていません。'
                        $msg_4014 = MT99Msg::where('MSG_NO', '4014')
                                            ->pluck('MSG_CONT')->first();
                        $fail($msg_4014);
                        return;
                    }
                    // 2002メッセージ取得（必須入力項目です)
                    // 職務種別が含まれてない行=>1番番目
                    if (!is_nullorwhitespace($value['nTimeCd'].
                                             $value['nTimeStrHH'].
                                             $value['nTimeStrMI'].
                                             $value['nTimeEndHH'].
                                             $value['nTimeEndMI'])) {
                        if (in_array(null, $value, true)) {
                            $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                            $fail($msg_2002);
                            return;
                        }
                        // 2009開始値と終了値の大小関係が不正です。
                        $valStr = substr('0' .$value['nTimeStrHH'], -2).substr('0' .$value['nTimeStrMI'], -2);
                        $valEnd = substr('0' .$value['nTimeEndHH'], -2).substr('0' .$value['nTimeEndMI'], -2);
                        if ($valEnd <= $valStr) {
                            $msg_2009 = MT99Msg::where('MSG_NO', '2009')->pluck('MSG_CONT')->first();
                            $fail($msg_2009);
                            return;
                        }
                    }
                    if (is_nullorwhitespace($value['nTimeCd'].
                                            $value['nTimeStrHH'].
                                            $value['nTimeStrMI'].
                                            $value['nTimeEndHH'].
                                            $value['nTimeEndMI'])) {
                            // １番目必ず入力する
                            $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                            $fail($msg_2002);
                            return;
                    }
                    // 4019,'項目が重複しています。'
                    $nTimes = $input_dt['nTime'];
                    $count = 0;
                    foreach ($nTimes as $nTime) {
                        if (!empty($value['nTimeCd'])) {
                            if ($nTime['nTimeCd'] === $value['nTimeCd']) {
                                $count++;
                                if ($count > 1) {
                                    $msg_4019 = MT99Msg::where('MSG_NO', '4019')->pluck('MSG_CONT')->first();
                                    $fail($msg_4019);
                                    return;
                                }
                            }
                        }
                    }
                }
            ],
            'extHour.*' => [
                function ($attribute, $value, $fail) {
                    $input_dt = $this->all();
                    if (!is_nullorwhitespace($value['excCdH'].
                                             $value['extHourStrHH'].
                                             $value['extHourStrMI'].
                                             $value['extHourEndHH'].
                                             $value['extHourEndMI'])) {
                        // 2002メッセージ取得（必須入力項目です)
                        if (in_array(null, $value, true)) {
                            $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                            $fail($msg_2002);
                            return;
                        }
                        // 2009開始値と終了値の大小関係が不正です。
                        $valStr = substr('0' .$value['extHourStrHH'], -2).substr('0' .$value['extHourStrMI'], -2);
                        $valEnd = substr('0' .$value['extHourEndHH'], -2).substr('0' .$value['extHourEndMI'], -2);
                        if ($valEnd <= $valStr) {
                            $msg_2009 = MT99Msg::where('MSG_NO', '2009')->pluck('MSG_CONT')->first();
                            $fail($msg_2009);
                            return;
                        }
                    }

                    // 4019,'項目が重複しています。'
                    $extHours = $input_dt['extHour'];
                    $count = 0;
                    foreach ($extHours as $extHour) {
                        if (!empty($value['excCdH'])) {
                            if ($extHour['excCdH'] === $value['excCdH']) {
                                $count++;
                                if ($count > 1) {
                                    $msg_4019 = MT99Msg::where('MSG_NO', '4019')->pluck('MSG_CONT')->first();
                                    $fail($msg_4019);
                                    return;
                                }
                            }
                        }
                    }
                }
            ],
            'rsvHour' => [
                function ($attribute, $value, $fail) {
                    if (in_array(null, $value)) {
                        // 2002メッセージ取得（必須入力項目です)
                        $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                        $fail($msg_2002);
                        return;
                    }
                }
            ],
            'tmDailyHour' => [
                function ($attribute, $value, $fail) {
                    if (in_array(null, $value)) {
                        // 2002メッセージ取得（必須入力項目です)
                        $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                        $fail($msg_2002);
                        return;
                    }
                }
            ],
            'nTimeStr' => [
                function ($attribute, $value, $fail) {
                    if (!is_nullorwhitespace($value['nTimeStrHH'] .$value['nTimeStrMI'])) {
                        if (in_array(null, $value, true)) {
                            // 2002メッセージ取得（必須入力項目です)
                            $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                            $fail($msg_2002);
                            return;
                        }
                    }
                }
            ],
            'breakTime.*' => [
                function ($attribute, $value, $fail) {
                    $input_dt = $this->all();
                    if (!is_nullorwhitespace($value['brstrHH'].
                                             $value['brstrMI'].
                                             $value['brendHH'].
                                             $value['brendMI'])) {
                        // 2002メッセージ取得（必須入力項目です)
                        if (in_array(null, $value, true)) {
                            $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                            $fail($msg_2002);
                            return;
                        }

                        // 2009開始値と終了値の大小関係が不正です。
                        $valStr = substr('0' .$value['brstrHH'], -2).substr('0' .$value['brstrMI'], -2);
                        $valEnd = substr('0' .$value['brendHH'], -2).substr('0' .$value['brendMI'], -2);
                        if ($valEnd <= $valStr) {
                            $msg_2009 = MT99Msg::where('MSG_NO', '2009')->pluck('MSG_CONT')->first();
                            $fail($msg_2009);
                            return;
                        }
                        if (!empty($input_dt['pTime'])) {
                            $hikaku =[];
                            $hikaku2 =[];
                            // 4013,'休憩時間帯は勤怠項目のいずれかの時間帯に含まれるようにして下さい。
                            $pTimeDt = $input_dt['pTime'];
                            for ($i = 0; $i <= 6; $i++) {
                                $strPTime = substr('0' .$pTimeDt[$i]['StrHH'], -2).
                                            substr('0' .$pTimeDt[$i]['StrMI'], -2);
                                $endPTime = substr('0' .$pTimeDt[$i]['EndHH'], -2).
                                            substr('0' .$pTimeDt[$i]['EndMI'], -2);
                                // 0は時間外、１は時間内
                                if (!($valStr >= $strPTime && $valEnd <= $endPTime)) {
                                    $hikaku[] = 0;
                                }
                                if ($valStr >= $strPTime && $valEnd <= $endPTime) {
                                    $hikaku[] = 1;
                                }
                            }
                            // 時間外を配列に入れる
                            foreach ($hikaku as $hk) {
                                if ($hk == 0) {
                                    $hikaku2[] = 0;
                                }
                            }
                            // 勤怠項目のすべての時間帯に含まれていなければエラー
                            $countHikaku2 = count($hikaku2);
                            $countHikaku= count($hikaku);
                            if ($countHikaku == $countHikaku2) {
                                $msg_4013 = MT99Msg::where('MSG_NO', '4013')->pluck('MSG_CONT')->first();
                                $fail($msg_4013);
                                return;
                            }
                        }
                    }
                }
            ],
            'breakHour' => [
                function ($attribute, $value, $fail) {
                    if (in_array(null, $value, true)) {
                        // 2002メッセージ取得（必須入力項目です)
                        $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                        $fail($msg_2002);
                        return;
                    }
                }
            ],
            'brHourly' => [
                function ($attribute, $value, $fail) {
                    if (in_array(null, $value, true)) {
                        // 2002メッセージ取得（必須入力項目です)
                        $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                        $fail($msg_2002);
                        return;
                    }
                }
            ],
        ];
    }
    public function messages()
    {
        // 2002メッセージ取得（必須入力項目です)
        $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
        return [
            'workPtnCd.required' => $msg_2002,
            'workPtnName.required' => $msg_2002,
            'workPtnAbrName.required' => $msg_2002,
        ];
    }
}
