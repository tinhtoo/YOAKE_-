<?php

namespace App\Http\Requests;

use App\Models\MT99Msg;
use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\Search\MT12DeptSearchRepository;
use App\Repositories\Search\MT10EmpSearchRepository;
use Carbon\Carbon;

class BaseRequest extends FormRequest
{
    /**
     * 年月日の入力チェック
     * 「いずれか入力があれば必須」のチェックだが
     * 「必須」も、該当の「～Year」のルールにrequiredを加えることで可能
     *
     * @param string $yearName　年のname要素
     * @param string $monthName　月のname要素
     * @param string $dayName　日のname要素
     * @return void
     */
    public function ymdCheck($yearName = '', $monthName = '', $dayName = '')
    {
        return function ($attribute, $value, $fail) use ($yearName, $monthName, $dayName) {
            $input_data = $this->all();
            $year = $input_data[$yearName];
            $month = $input_data[$monthName];
            $day = $input_data[$dayName];

            if ($year == null && $month == null && $day == null) {
                // 全項目未入力 -> エラーなし
            } elseif ($year == null || $month == null || $day == null) {
                // 年月日いずれか未入力 -> 必須エラー
                $fail('2002');
            } elseif (!checkDate($month, $day, $year)) {
                // 不正な日付
                $fail('2004');
            } elseif ((int)$year < 1900 || 2100 < (int)$year) {
                // 年が1900～2100の範囲外
                $fail('2026');
            }
        };
    }

    /**
     * 年月のみ（日無し）のチェック
     *
     *
     * @param string $yearName
     * @param string $monthName
     * @return void
     */
    public function ymCheck($yearName = '', $monthName = '')
    {
        return function ($attribute, $value, $fail) use ($yearName, $monthName) {
            $input_data = $this->all();
            $year = $input_data[$yearName];
            $month = $input_data[$monthName];

            if ($year == null && $month == null) {
                // 全項目未入力 -> エラーなし
            } elseif ($year == null || $month == null) {
                // 年月日いずれか未入力 -> 必須エラー
                $fail('2002');
            } elseif (!checkDate($month, 1, $year)) {
                // 不正な日付
                $fail('2004');
            } elseif ((int)$year < 1900 || 2100 < (int)$year) {
                // 年が1900～2100の範囲外
                $fail('2027');
            }
        };
    }

    /**
     * 年月のみ（日無し）のチェック
     * エラーメッセージの内容を設定する（非同期通信用）
     *
     * @param string $yearName
     * @param string $monthName
     * @return void
     */
    public function ymCheckForAjax($yearName = '', $monthName = '')
    {
        return function ($attribute, $value, $fail) use ($yearName, $monthName) {
            $input_data = $this->all();
            $year = $input_data[$yearName];
            $month = $input_data[$monthName];

            if ($year == null && $month == null) {
                // 全項目未入力 -> エラーなし
            } elseif ($year == null || $month == null) {
                // 年月日いずれか未入力 -> 必須エラー
                $error_msg = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                $fail($error_msg);
            } elseif (!checkDate($month, 1, $year)) {
                // 不正な日付
                $error_msg = MT99Msg::where('MSG_NO', '2004')->pluck('MSG_CONT')->first();
                $fail($error_msg);
            } elseif ((int)$year < 1900 || 2100 < (int)$year) {
                // 年が1900～2100の範囲外
                $error_msg = MT99Msg::where('MSG_NO', '2027')->pluck('MSG_CONT')->first();
                $fail($error_msg);
            }
        };
    }

    /**
     * 部門コードの存在チェック
     * ログイン情報からユーザの権限情報の確認も行う
     *
     * @return void
     */
    public function existDeptCdWithAuth($disp_cls_cd = null, $is_dept_auth = false)
    {
        return function ($attribute, $value, $fail) use ($disp_cls_cd, $is_dept_auth) {
            $mt12_dept_search_repository = new MT12DeptSearchRepository();
            if ($mt12_dept_search_repository->getName($value, $disp_cls_cd, $is_dept_auth) == null) {
                $fail('2000');
            }
        };
    }

    /**
     * 社員コードの存在チェック
     * ログイン情報からユーザの権限情報の確認も行う
     *
     * @return void
     */
    public function existEmpCdWithAuth($reg_cls_cd = null, $is_dept_auth = false, $calendar_cls_cd = null)
    {
        return function ($attribute, $value, $fail) use ($reg_cls_cd, $is_dept_auth, $calendar_cls_cd) {
            $mt10_emp_search_repository = new MT10EmpSearchRepository();
            if ($mt10_emp_search_repository->getName($value, $reg_cls_cd, $is_dept_auth, $calendar_cls_cd) == null) {
                $fail('2000');
            }
        };
    }

    /**
     * 残業項目のチェック
     * 時間入力時はプルダウンの選択が必須
     *
     * @param string $cdName
     * @param string $hrName
     * @return void
     */
    public function requiredOvtmCd($cdName = '', $hrName = '')
    {
        return function ($attribute, $value, $fail) use ($cdName, $hrName) {
            // 残業項目（n）時間/月の入力値があり、残業項目(n)リストボックスの入力値がない場合
            if (!empty(FormRequest::get($hrName)) && FormRequest::get($cdName) == null) {
                // 2002メッセージ（必須入力項目です。)
                $fail('2002');
            }
        };
    }

    /**
     * 年月日の入力チェック
     *  [いずれか入力がなければ必須」のチェック
     * 「いずれか入力があれば必須」のチェック
     * 「必須」も、該当の「～Year」のルールにrequiredを加えることで可能
     *
     * @param string $yearName　年のname要素
     * @param string $monthName　月のname要素
     * @param string $dayName　日のname要素
     * @return void
     */
    public function ymdCheckRequired($yearName = '', $monthName = '', $dayName = '')
    {
        return function ($attribute, $value, $fail) use ($yearName, $monthName, $dayName) {
            $input_data = $this->all();
            $year = $input_data[$yearName];
            $month = $input_data[$monthName];
            $day = $input_data[$dayName];

            if ($year == null && $month == null && $day == null) {
                // 全項目未入力 -> 必須エラー
                $fail('2002');
            } elseif ($year == null || $month == null || $day == null) {
                // 年月日いずれか未入力 -> 必須エラー
                $fail('2002');
            } elseif (!checkDate($month, $day, $year)) {
                // 不正な日付
                $fail('2004');
            } elseif ((int)$year < 1900 || 2100 < (int)$year) {
                // 年が1900～2100の範囲外
                $fail('2026');
            }
        };
    }

    /**
     *  大小関係チェック
     *
     * @param string $start_name 開始項目のname要素
     * @param string $end_name 終了項目のname要素
     * @return void
     */
    public function startEndCheck($start_name = '', $end_name = '')
    {
        return function ($attribute, $value, $fail) use ($start_name, $end_name) {
            $input_data = $this->all();
            $start_value = $input_data['filter'][$start_name];
            $end_value = $input_data['filter'][$end_name];

            if ($start_value && $end_value && ($start_value > $end_value)) {
                // 開始値と終了値の大小関係が不正です。
                $fail('2009');
            }
        };
    }

    /**
     * 1か月以内のチェック
     * startからendが31日超の場合、エラー
     *
     * @param [type] $startDateName
     * @return void
     */
    public function withinAMonth($startDateName)
    {
        return function ($attribute, $value, $fail) use ($startDateName) {
            $input_data = $this->all();

            $start_value = $input_data['filter'][$startDateName];
            $start_date = new Carbon(substr($start_value, 0, 4).substr($start_value, 7, 2).substr($start_value, 12, 2));
            $end_date = new Carbon(substr($value, 0, 4). substr($value, 7, 2). substr($value, 12, 2));
            if ($end_date->gt($start_date->addDays(30))) {
                // 開始値と終了値の大小関係が不正です。
                $fail('4025');
            }
        };
    }

    /**
     * 年月日のチェック
     * カレンダー形式で入力した年月日のチェックを行う
     *
     * @return void
     */
    public function validYear()
    {
        return function ($attribute, $value, $fail) {
            if (strlen($value) !== 17) {
                // 不正な日付
                $fail('2004');
            } elseif (!checkDate(substr($value, 7, 2), substr($value, 12, 2), substr($value, 0, 4))) {
                // 不正な日付
                $fail('2004');
            } elseif ((int)substr($value, 0, 4) < 1900 || 2100 < (int)substr($value, 0, 4)) {
                // 年が1900～2100の範囲外
                $fail('2026');
            }
        };
    }

    /**
     * 年月のチェック
     * カレンダー形式で入力した年月のチェックを行う
     *
     * @return void
     */
    public function validYM()
    {
        return function ($attribute, $value, $fail) {
            if (strlen($value) !== 12) {
                // 不正な日付
                $fail('2004');
            } elseif (!checkDate(substr($value, 7, 2), 1, substr($value, 0, 4))) {
                // 不正な日付
                $fail('2004');
            } elseif ((int)substr($value, 0, 4) < 1900 || 2100 < (int)substr($value, 0, 4)) {
                // 年が1900～2100の範囲外
                $fail('2027');
            }
        };
    }

    /**
     * 出退勤端数処理項目のチェック
     * [いずれか入力がなければ必須」のチェック
     *
     * @param string $cd
     * @param string $underMin
     * @param string $frcClsCd
     * @return void
     */
    public function fractionRequiredThereIsNoInput($cd = "", $underMin = "", $frcClsCd = "")
    {
        return function ($attribute, $value, $fail) use ($cd, $underMin, $frcClsCd) {
            // 「いずれか入力がなければ必須」のチェック
            // 2002,'必須入力項目です。'
            if (!is_nullorwhitespace($value[$cd] .$value[$underMin] .$value[$frcClsCd])) {
                if (in_array(null, $value, true)) {
                    $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                    $fail($msg_2002);
                }
            }
        };
    }

    /**
     * 未入力エラーチェック
     *
     * @return void
     */
    public function fractionCheckRequiredAll()
    {
        return function ($attribute, $value, $fail) {
            // 未入力エラー
            // 2002,'必須入力項目です。'
            if (in_array(null, $value, true)) {
                $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();
                $fail($msg_2002);
            }
        };
    }

    /**
     * 項目重複のチェック「残業時間端数処理」「割増時間端数処理」
     *
     * @param string $itemCD
     * @param string $itemName
     * @return void
     */
    public function fractionDuplicateItems($itemCD = "", $itemName = "")
    {
        return function ($attribute, $value, $fail) use ($itemCD, $itemName) {
            // 4019,'項目が重複しています。'
            $input_dt = $this->all();
            $itemDatas = $input_dt[$itemName];
            $count = 0;
            foreach ($itemDatas as $itemData) {
                if (!empty($value[$itemCD])) {
                    if ($itemData[$itemCD] === $value[$itemCD]) {
                        $count++;
                        if ($count > 1) {
                            $msg_4019 = MT99Msg::where('MSG_NO', '4019')->pluck('MSG_CONT')->first();
                            $fail($msg_4019);
                        }
                    }
                }
            }
        };
    }
}
