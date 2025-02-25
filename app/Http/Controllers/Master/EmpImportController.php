<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\MT93PgRepository;
use App\Http\Requests\EmpImportRequest;
use App\Models\MT01Control;
use App\Models\MT02CalendarPtn;
use App\Models\MT12Dept;
use App\Models\MT13DeptAuth;
use App\Models\MT14PgAuth;
use App\Models\MT22ClosingDate;
use App\Models\MT23Company;
use App\Models\MT91ClsDetail;
use App\Models\MT92DescDetail;
use App\Models\MT99Msg;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Repositories\Master\MT10EmpRefRepository;
use App\Repositories\Master\MT11LoginRefRepository;
use Illuminate\Support\Facades\DB;

/**
 * 社員情報取込処理　処理
 */
class EmpImportController extends Controller
{
    /**
     * コントローラインスタンスの生成
     * @param
     * @return void
     */
    public function __construct(
        MT93PgRepository $pg_repository,
        MT10EmpRefRepository $emp_ref_repository,
        MT11LoginRefRepository $login_ref_repository
    ) {
        parent::__construct($pg_repository, '000021');
        $this->emp_ref = $emp_ref_repository;
        $this->login_ref = $login_ref_repository;
    }

    /**
     * 社員情報取込処理　処理 画面表示
     * @return view
     */
    public function index(Request $request)
    {
        return parent::viewWithMenu('master.EmpImport');
    }

    /**
     * 社員情報取込処理 処理　取込
     */
    public function import(EmpImportRequest $request)
    {
        $uploaded_file = $request->file('csvFile');
        $mt01_control_path = MT01Control::where('CONTROL_CD', '=', '1')
                        ->pluck('EMPFILE_PATH')
                        ->first();

        // 一度アップロードしたファイルがあれば削除
        $upload_file_path = session('file_upload_path');
        if (file_exists($upload_file_path)) {
            unlink($upload_file_path);
        }

        $id_file = md5(uniqid(rand(), 1));
        $source = fopen($uploaded_file, 'r');
        $upload_place = fopen($mt01_control_path.'/'.$id_file, 'w');
        $contents = fread($source, filesize($uploaded_file));
        fwrite($upload_place, $contents);
        fclose($source);
        fclose($upload_place);
        session()->put(['file_upload_path' => $mt01_control_path.'/'.$id_file]);

        // CSV取得
        $file = new \SplFileObject($uploaded_file);
        $file->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::DROP_NEW_LINE
        );
        // CSV格納用データテーブル
        $datas = [];
        // エラーデータ格納用データテーブル
        $update_errors_list = [];
        $i = 0;
        $count_data = 0;
        // 各行を処理
        foreach ($file as $key => $line) {
            mb_convert_variables("UTF-8", "SJIS", $line);
            if ($key === 0) {
                // インデックスの０番目がヘッダーの為、配列に代入しない
                continue;
            } else {
                $datas[] = $line;
            }
            $count_data++;
        }
        // ヘッダーのみの場合でもエラーは表示する
        if ($count_data <= 0) {
            foreach ($file as $key => $line) {
                mb_convert_variables("UTF-8", "SJIS", $line);
                $datas[] = $line;
            }
        }
        // エラーメッセージ「フィールド数が不一致です。」だけ表示
        foreach ($datas as $data) {
            $validator_file = Validator::make(
                [
                    'all' => $data
                ],
                $this->defineValidationRulesAll(),
            );
            if ($validator_file->fails()) {
                $file_not_exist[$i]['ERROR_MSG'] = $validator_file->errors()->all();
            }
            $i++;
        }
        if (!empty($file_not_exist)) {
            $error_num = count($file_not_exist);
            return parent::viewWithMenu('master.EmpImport', compact('file_not_exist', 'error_num'));
        }

        // フィールド数が一致したら、各行のエラーチェック
        foreach ($datas as $data) {
            // バリデーションチェック
            $validator = Validator::make(
                [
                    'EMP_CD' => $data[0],
                    'EMP_NAME' => $data[1],
                    'EMP_KANA' => $data[2],
                    'LOGIN_ID' => $data[3],
                    'PASSWORD' => $data[4],
                    'DEPT_CD' => $data[5],
                    'ENT_DATE' => $data,
                    'RET_DATE' => $data,
                    'REG_CLS_CD' => $data[13],
                    'BIRTH_DATE' => $data,
                    'SEX_CLS_CD' => $data[18],
                    'EMP_CLS1_CD' => $data[20],
                    'EMP_CLS2_CD' => $data[22],
                    'CALENDAR_CD' => $data[24],
                    'DEPT_AUTH_CD' => $data[26],
                    'PG_AUTH_CD' => $data[28],
                    'POST_CD' => $data[30],
                    'ADDRESS1' => $data[31],
                    'ADDRESS2' => $data[32],
                    'TEL' => $data[33],
                    'CELLULAR' => $data[34],
                    'MAIL' => $data[35],
                    'PH_GRANT_DATE' => $data,
                    'CLOSING_DATE_CD' => $data[38],
                    'COMPANY_CD' => $data[40],
                    'EMP2_CD' => $data[42]
                ],
                $this->defineValidationRules(),
                $this->defineValidationMessages()
            );
            if ($validator->fails()) {
                $datas_error[$i]['ERROR_MSG'] = $validator->errors()->all();
                $datas_error[$i]['EMP_CD'] = $data[0];
                $datas_error[$i]['EMP_NAME'] = $data[1];
                $datas_error[$i]['EMP_KANA'] = $data[2];
                $datas_error[$i]['DEPT_CD'] = $data[5];
            }
            $i++;
        }

        // アップロードファイルを閉じる
        $file = null;
        // バリデーションエラーがある場合
        if (!empty($datas_error)) {
            unlink($mt01_control_path.'/'.$id_file);
            $error_num = count($datas_error);
            return parent::viewWithMenu('master.EmpImport', compact('datas_error', 'error_num'));
        } else {
            // バリデーションエラーがない場合
            $no_error_num = 0;
            return parent::viewWithMenu('master.EmpImport', compact('no_error_num', 'count_data'));
        }
    }

     /**
     * 社員情報取込処理 処理　更新処理
     */
    public function update(Request $request)
    {
        $today = date('Y-m-d H:i:s');
        $upload_file_path = session('file_upload_path');
        // CSV取得
        $file = new \SplFileObject($upload_file_path);
        $file->setFlags(
            \SplFileObject::READ_CSV |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::DROP_NEW_LINE
        );

        $count_data = $request->input('count_data');
        //各行を処理
        $param_emp_all = [];
        $param_login_all = [];
        foreach ($file as $key => $data) {
            mb_convert_variables("UTF-8", "SJIS", $data);
            if ($key === 0 && $count_data > 0) {
                // インデックスの０番目はヘッダーの為、次の行へ
                continue;
            }
            $param_emp_all[$data[0]] = [
                'EMP_CD' => $data[0],
                'EMP_NAME' => $data[1],
                'EMP_KANA' => $data[2],
                'EMP_ABR' => '',
                'DEPT_CD' => $data[5],
                'ENT_DATE' => $data[7]
                                ? substr('0' . $data[7], -4).substr('0' . $data[8], -2).substr('0' . $data[9], -2)
                                : '',
                'ENT_YEAR' => $data[7] ? $data[7] : '',
                'ENT_MONTH' => $data[8] ? (int)$data[8] : '',
                'ENT_DAY' => $data[9] ? (int)$data[9] : '',
                'RET_DATE' => $data[10]
                                ? substr('0' . $data[10], -4).
                                substr('0' . $data[11], -2).
                                substr('0' . $data[12], -2)
                                : '',
                'RET_YEAR' => $data[10] ? (int)$data[10] : '',
                'RET_MONTH' => $data[11] ? (int)$data[11] : '',
                'RET_DAY' => $data[12] ? (int)$data[12] : '',
                'REG_CLS_CD' => $data[13],
                'BIRTH_DATE' => $data[15]
                                ? substr('0' . $data[15], -4).
                                substr('0' . $data[16], -2).
                                substr('0' . $data[17], -2)
                                : '',
                'BIRTH_YEAR' => $data[15] ? $data[15] : '',
                'BIRTH_MONTH' => $data[16] ? (int)$data[16] : '',
                'BIRTH_DAY' => $data[17] ? (int)$data[17] : '',
                'SEX_CLS_CD' => $data[18],
                'EMP_CLS1_CD' => $data[20],
                'EMP_CLS2_CD' => $data[22],
                'EMP_CLS3_CD' => '',
                'CALENDAR_CD' => $data[24],
                'DEPT_AUTH_CD' => $data[26],
                'PG_AUTH_CD' => $data[28],
                'POST_CD' => $data[30],
                'ADDRESS1' => $data[31],
                'ADDRESS2' => $data[32],
                'TEL' => $data[33],
                'CELLULAR' => $data[34],
                'MAIL' => $data[35],
                'RSV1_CLS_CD' => '',
                'RSV2_CLS_CD' => '',
                'UPD_DATE' => $today,
                'PH_GRANT' => $data[36]
                                ? substr('0' . $data[36], -4).substr('0' . $data[37], -2)
                                : '',
                'PH_GRANT_YEAR' => $data[36] ? (int)$data[36] : '',
                'PH_GRANT_MONTH' => $data[37] ? (int)$data[37] : '',
                'CLOSING_DATE_CD' => $data[38],
                'COMPANY_CD' => $data[40],
                'EMP2_CD' => $data[42] ?? '',
                'EMP3_CD' => '',
            ];
            $param_login_all[$data[0]] = [
                'EMP_CD' => $data[0],
                'LOGIN_ID' => $data[3],
                'PASSWORD' => $data[4],
                'UPD_DATE' => $today,
            ];
        }
        // 読み込んだcsvファイルを閉じて削除
        $file = null;
        unlink($upload_file_path);
        try {
            DB::beginTransaction();
            foreach (array_chunk($param_emp_all, 40) as $param_emp) {
                $this->emp_ref->upsertEmpImport($param_emp);
            }
            foreach (array_chunk($param_login_all, 40) as $param_login) {
                $this->login_ref->upsertLogin($param_login);
            }
            DB::commit();
        } catch (\Exception $e) {
            \Log::debug($e);
            DB::rollback();
        }
        return parent::viewWithMenu('master.EmpImport');
    }

    /**
     * バリデーションの定義
     *
     * @return
     */
    private function defineValidationRules()
    {
        return [
            // CSVデータ用バリデーションルール
            'EMP_CD' => ['required',
                function ($attribute, $value, $fail) {
                    // 項目長チェック
                    $length = Str::length($value);
                    if ($length > 10) {
                        $msg_2015 = MT99Msg::where('MSG_NO', '2015')->pluck('MSG_CONT')->first();
                        $fail("社員コード:".$msg_2015);
                    }
                }
            ],
            'EMP_NAME' => ['required',
                function ($attribute, $value, $fail) {
                    // 項目長チェック
                    $length = Str::length($value);
                    if ($length > 20) {
                        $msg_2015 = MT99Msg::where('MSG_NO', '2015')->pluck('MSG_CONT')->first();
                        $fail("社員名:".$msg_2015);
                    }
                }
            ],
            'EMP_KANA' => ['required',
                function ($attribute, $value, $fail) {
                    // 項目長チェック
                    $length = Str::length($value);
                    if ($length > 20) {
                        $msg_2015 = MT99Msg::where('MSG_NO', '2015')->pluck('MSG_CONT')->first();
                        $fail("社員カナ名:".$msg_2015);
                    }
                }
            ],
            'LOGIN_ID' => ['required',
                function ($attribute, $value, $fail) {
                    // 項目長チェック
                    $length = Str::length($value);
                    if ($length > 10) {
                        $msg_2015 = MT99Msg::where('MSG_NO', '2015')->pluck('MSG_CONT')->first();
                        $fail("ログインID:".$msg_2015);
                    }
                }
            ],
            'PASSWORD' => ['required',
                function ($attribute, $value, $fail) {
                    // 項目長チェック
                    $length = Str::length($value);
                    if ($length > 10) {
                        $msg_2015 = MT99Msg::where('MSG_NO', '2015')->pluck('MSG_CONT')->first();
                        $fail("パスワード:".$msg_2015);
                    }
                }
            ],
            'DEPT_CD' => ['required',
                function ($attribute, $value, $fail) {
                    if (is_nullorempty($value)) {
                        return;
                    }
                    // 存在チェック
                    $mt12_dept = MT12Dept::where('DEPT_CD', $value)->pluck('DEPT_CD')->first();
                    if (!$mt12_dept) {
                        $msg_2000 = MT99Msg::where('MSG_NO', '2000')->pluck('MSG_CONT')->first();
                        $fail("部門コード:".$msg_2000);
                    }
                }
            ],
            'ENT_DATE' => [
                function ($attribute, $value, $fail) {
                    $year = $value[7];
                    $month = $value[8];
                    $day = $value[9];
                    if (!is_nullorempty($year .$month .$day)) {
                        $date = $year."-".$month."-".$day;
                        // 日付文字列の形式チェック
                        if (preg_match('/\A[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}\z/', $date) == false) {
                            $msg_2004 = MT99Msg::where('MSG_NO', '2004')->pluck('MSG_CONT')->first();
                            $fail("入社年,入社月,入社日:".$msg_2004);
                            return;
                        }
                        // 日付形式チェック（0000-00-00や2021-02-29をエラーにする）
                        if (!checkdate($month, $day, $year)) {
                            $msg_2004 = MT99Msg::where('MSG_NO', '2004')->pluck('MSG_CONT')->first();
                            $fail("入社年,入社月,入社日:".$msg_2004);
                            return;
                        }
                    }
                }
            ],
            'RET_DATE' => [
                function ($attribute, $value, $fail) {
                    $year = $value[10];
                    $month = $value[11];
                    $day = $value[12];
                    if (!is_nullorempty($year .$month .$day)) {
                        $date = $year."-".$month."-".$day;
                        // 日付文字列の形式チェック
                        if (preg_match('/\A[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}\z/', $date) == false) {
                            $msg_2004 = MT99Msg::where('MSG_NO', '2004')->pluck('MSG_CONT')->first();
                            $fail("退職年,退職月,退職日:".$msg_2004);
                            return;
                        }
                        // 日付形式チェック（0000-00-00や2021-02-29をエラーにする）
                        if (!checkdate($month, $day, $year)) {
                            $msg_2004 = MT99Msg::where('MSG_NO', '2004')->pluck('MSG_CONT')->first();
                            $fail("退職年,退職月,退職日:".$msg_2004);
                            return;
                        }
                    }
                }
            ],
            'REG_CLS_CD' => ['required',
                function ($attribute, $value, $fail) {
                    // 存在チェック
                    $mt91_cls_detail = MT91ClsDetail::where('CLS_CD', '=', '02')
                                                    ->where('CLS_DETAIL_CD', $value)
                                                    ->pluck('CLS_DETAIL_CD')->first();
                    if (!$mt91_cls_detail) {
                        $msg_2000 = MT99Msg::where('MSG_NO', '2000')->pluck('MSG_CONT')->first();
                        $fail("在籍区分コード:".$msg_2000);
                    }
                }
            ],
            'BIRTH_DATE' => [
                function ($attribute, $value, $fail) {
                    $year = $value[15];
                    $month = $value[16];
                    $day = $value[17];
                    if (!is_nullorempty($year .$month .$day)) {
                        $date = $year."-".$month."-".$day;
                        // 日付文字列の形式チェック
                        if (preg_match('/\A[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}\z/', $date) == false) {
                            $msg_2004 = MT99Msg::where('MSG_NO', '2004')->pluck('MSG_CONT')->first();
                            $fail("生年月日:".$msg_2004);
                            return;
                        }
                        // 日付形式チェック（0000-00-00や2021-02-29をエラーにする）
                        if (!checkdate($month, $day, $year)) {
                            $msg_2004 = MT99Msg::where('MSG_NO', '2004')->pluck('MSG_CONT')->first();
                            $fail("生年月日:".$msg_2004);
                            return;
                        }
                    }
                }
            ],
            'SEX_CLS_CD' => ['required',
                function ($attribute, $value, $fail) {
                    // 存在チェック
                    $mt91_cls_detail = MT91ClsDetail::where('CLS_CD', '=', '03')
                                                    ->where('CLS_DETAIL_CD', $value)
                                                    ->pluck('CLS_DETAIL_CD')->first();
                    if (!$mt91_cls_detail) {
                        $msg_2000 = MT99Msg::where('MSG_NO', '2000')->pluck('MSG_CONT')->first();
                        $fail("性別区分コード:".$msg_2000);
                    }
                }
            ],
            'EMP_CLS1_CD' => ['required',
                function ($attribute, $value, $fail) {
                    // 存在チェック
                    $mt92_desc_detail = MT92DescDetail::where('CLS_CD', '=', '50')
                                                    ->where('DESC_DETAIL_CD', $value)
                                                    ->pluck('DESC_DETAIL_CD')->first();
                    if (!$mt92_desc_detail) {
                        $msg_2000 = MT99Msg::where('MSG_NO', '2000')->pluck('MSG_CONT')->first();
                        $fail("社員区分１コード:".$msg_2000);
                    }
                }
            ],
            'EMP_CLS2_CD' => ['required',
                function ($attribute, $value, $fail) {
                    // 存在チェック
                    $mt92_desc_detail = MT92DescDetail::where('CLS_CD', '=', '51')
                                                    ->where('DESC_DETAIL_CD', $value)
                                                    ->pluck('DESC_DETAIL_CD')->first();
                    if (!$mt92_desc_detail) {
                        $msg_2000 = MT99Msg::where('MSG_NO', '2000')->pluck('MSG_CONT')->first();
                        $fail("社員区分2コード:".$msg_2000);
                    }
                }
            ],
            'CALENDAR_CD' => ['required',
                function ($attribute, $value, $fail) {
                    // 存在チェック
                    $mt02_calender = MT02CalendarPtn::where('CALENDAR_CD', $value)->pluck('CALENDAR_CD')->first();
                    if (!$mt02_calender) {
                        $msg_2000 = MT99Msg::where('MSG_NO', '2000')->pluck('MSG_CONT')->first();
                        $fail("使用カレンダーコード:".$msg_2000);
                    }
                }
            ],
            'DEPT_AUTH_CD' => [
                function ($attribute, $value, $fail) {
                    if (is_nullorempty($value)) {
                        return;
                    }
                    // 存在チェック
                    $mt13_dept_auth = MT13DeptAuth::where('DEPT_AUTH_CD', $value)->pluck('DEPT_AUTH_CD')->first();
                    if (!$mt13_dept_auth) {
                        $msg_2000 = MT99Msg::where('MSG_NO', '2000')->pluck('MSG_CONT')->first();
                        $fail("部門権限コード:".$msg_2000);
                    }
                }
            ],
            'PG_AUTH_CD' => ['required',
                function ($attribute, $value, $fail) {
                    if (is_nullorempty($value)) {
                        return;
                    }
                    // 存在チェック
                    $mt14_pg_auth = MT14PgAuth::where('PG_AUTH_CD', $value)->pluck('PG_AUTH_CD')->first();
                    if (!$mt14_pg_auth) {
                        $msg_2000 = MT99Msg::where('MSG_NO', '2000')->pluck('MSG_CONT')->first();
                        $fail("機能権限コード:".$msg_2000);
                    }
                }
            ],
            'POST_CD' => [
                function ($attribute, $value, $fail) {
                    // 項目長チェック
                    $length = Str::length($value);
                    if ($length > 8) {
                        $msg_2015 = MT99Msg::where('MSG_NO', '2015')->pluck('MSG_CONT')->first();
                        $fail("郵便番号:".$msg_2015);
                    }
                }
            ],
            'ADDRESS1' => [
                function ($attribute, $value, $fail) {
                    // 項目長チェック
                    $length = Str::length($value);
                    if ($length > 30) {
                        $msg_2015 = MT99Msg::where('MSG_NO', '2015')->pluck('MSG_CONT')->first();
                        $fail("住所1:".$msg_2015);
                    }
                }
            ],
            'ADDRESS2' => [
                function ($attribute, $value, $fail) {
                    // 項目長チェック
                    $length = Str::length($value);
                    if ($length > 30) {
                        $msg_2015 = MT99Msg::where('MSG_NO', '2015')->pluck('MSG_CONT')->first();
                        $fail("住所2:".$msg_2015);
                    }
                }
            ],
            'TEL' => [
                function ($attribute, $value, $fail) {
                    // 項目長チェック
                    $length = Str::length($value);
                    if ($length > 15) {
                        $msg_2015 = MT99Msg::where('MSG_NO', '2015')->pluck('MSG_CONT')->first();
                        $fail("電話番号:".$msg_2015);
                    }
                }
            ],
            'CELLULAR' => [
                function ($attribute, $value, $fail) {
                    // 項目長チェック
                    $length = Str::length($value);
                    if ($length > 15) {
                        $msg_2015 = MT99Msg::where('MSG_NO', '2015')->pluck('MSG_CONT')->first();
                        $fail("携帯番号:".$msg_2015);
                    }
                }
            ],
            'MAIL' => [
                function ($attribute, $value, $fail) {
                    // 項目長チェック
                    $length = Str::length($value);
                    if ($length > 50) {
                        $msg_2015 = MT99Msg::where('MSG_NO', '2015')->pluck('MSG_CONT')->first();
                        $fail("Eメール:".$msg_2015);
                    }
                }
            ],
            'PH_GRANT_DATE' => [
                function ($attribute, $value, $fail) {
                    $year = $value[36];
                    $month = $value[37];
                    if (!is_nullorempty($year .$month)) {
                        $day = '01';
                        $date = $year."-".$month."-".$day;
                        // 日付文字列の形式チェック
                        if (preg_match('/\A[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}\z/', $date) == false) {
                            $msg_2004 = MT99Msg::where('MSG_NO', '2004')->pluck('MSG_CONT')->first();
                            $fail("有休付与算出基準年,有休付与算出基準月:".$msg_2004);
                            return;
                        }
                        // 日付形式チェック（0000-00-00や2021-02-29をエラーにする）
                        if (!checkdate($month, $day, $year)) {
                            $msg_2004 = MT99Msg::where('MSG_NO', '2004')->pluck('MSG_CONT')->first();
                            $fail("有休付与算出基準年,有休付与算出基準月:".$msg_2004);
                            return;
                        }
                    }
                }
            ],
            'CLOSING_DATE_CD' => ['required',
                function ($attribute, $value, $fail) {
                    // 存在チェック
                    $mt22_closing_date = MT22ClosingDate::where('CLOSING_DATE_CD', $value)
                                                    ->pluck('CLOSING_DATE_CD')->first();
                    $msg_2000 = MT99Msg::where('MSG_NO', '2000')->pluck('MSG_CONT')->first();
                    if (!$mt22_closing_date) {
                        $fail("締日コード:".$msg_2000);
                    }
                }
            ],
            'COMPANY_CD' => [
                function ($attribute, $value, $fail) {
                    if (is_nullorempty($value)) {
                        return;
                    }
                    // 存在チェック
                    $mt23_company = MT23Company::where('COMPANY_CD', $value)
                                                    ->pluck('COMPANY_CD')->first();
                    $msg_2000 = MT99Msg::where('MSG_NO', '2000')->pluck('MSG_CONT')->first();
                    if (!$mt23_company) {
                        $fail("所属コード:".$msg_2000);
                    }
                }
            ],
            'EMP2_CD' => [
                function ($attribute, $value, $fail) {
                    // 項目長チェック
                    $length = Str::length($value);
                    if ($length > 10) {
                        $msg_2015 = MT99Msg::where('MSG_NO', '2015')->pluck('MSG_CONT')->first();
                        $fail("社員２コード:".$msg_2015);
                    }
                }
            ],
        ];
    }

    /**
     * バリデーションメッセージの定義
     *
     * @return
     */
    private function defineValidationMessages()
    {
        // 2002メッセージ取得（必須入力項目です)
        $msg_2002 = MT99Msg::where('MSG_NO', '2002')->pluck('MSG_CONT')->first();

        return [
            'EMP_CD.required' => "社員コード:".$msg_2002,
            'EMP_NAME.required' => "社員名:".$msg_2002,
            'EMP_KANA.required' => "社員カナ名:".$msg_2002,
            'LOGIN_ID.required' => "ログインID:".$msg_2002,
            'PASSWORD.required' => "パスワード:".$msg_2002,
            'DEPT_CD.required' => "部門コード:".$msg_2002,
            'REG_CLS_CD.required' => "在籍区分コード:".$msg_2002,
            'SEX_CLS_CD.required' => "性別区分コード:".$msg_2002,
            'EMP_CLS1_CD.required' => "社員区分1コード:".$msg_2002,
            'EMP_CLS2_CD.required' => "社員区分2コード:".$msg_2002,
            'CALENDAR_CD.required' => "使用カレンダーコード:".$msg_2002,
            'PG_AUTH_CD.required' => "機能権限コード:".$msg_2002,
            'CLOSING_DATE_CD.required' => "締日コード:".$msg_2002,
        ];
    }

    /**
     * バリデーションの定義
     *
     * @return
     */
    private function defineValidationRulesAll()
    {
        return [
            // CSVデータ用バリデーションルール
            'all' => [
                function ($attribute, $value, $fail) {
                    $count_data = count($value);
                    if ($count_data <> 43) {
                        $msg_2012 = MT99Msg::where('MSG_NO', '2012')->pluck('MSG_CONT')->first();
                        $fail($msg_2012);
                    }
                }
            ],
        ];
    }
}
