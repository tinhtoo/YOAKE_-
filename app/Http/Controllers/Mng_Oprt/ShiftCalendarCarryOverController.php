<?php

namespace App\Http\Controllers\Mng_Oprt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\ShiftCalendarCarryOverUpdateRequest;
use App\Repositories\MT01ControlRepository;
use App\Repositories\MT04ShiftPtnRepository;
use App\Repositories\MT05WorkptnRepository;
use App\Repositories\Search\MT12DeptSearchRepository;
use App\Repositories\MT13DeptAuthRepository;
use App\Repositories\MT16DeptShiftCalendarRepository;
use App\Repositories\MT22ClosingDateRepository;
use App\Repositories\MT93PgRepository;
use App\Repositories\TR01WorkRepository;
use App\Repositories\TR02EmpCalendarRepository;
use App\Repositories\TR03DeptCalendarRepository;

use Carbon\Carbon;

/**
 * シフト月次更新処理画面
 */
class ShiftCalendarCarryOverController extends Controller
{
    private $mt01_control;
    private $mt04_shift_ptn;
    private $mt05_workptn;
    private $mt12_dept_search;
    private $mt13_dept_auth;
    private $mt16_deptshiftcalendar;
    private $mt22_closing_date;
    private $tr01_work;
    private $tr02_emp_calendar;
    private $tr03_dept_calendar;

    /**
     * コントローラインスタンスの生成
     * @param
     * @return void
     */
    public function __construct(
        MT93PgRepository $pg_repository,
        MT01ControlRepository $mt01_control_rep,
        MT04ShiftPtnRepository $mt04_shift_ptn_rep,
        MT05WorkptnRepository $mt05_workptn_rep,
        MT12DeptSearchRepository $mt12_dept_search_rep,
        MT13DeptAuthRepository $mt13_dept_auth_rep,
        MT16DeptShiftCalendarRepository $mt16_deptshiftcalendar_rep,
        MT22ClosingDateRepository $mt22_closing_date_rep,
        MT93PgRepository $mt93_pg_rep,
        TR01WorkRepository $tr01_work_rep,
        TR02EmpCalendarRepository $tr02_emp_calendar_rep,
        TR03DeptCalendarRepository $tr03_dept_calendar_rep
    ) {
        parent::__construct($pg_repository, '040002');
        $this->mt01_control = $mt01_control_rep;
        $this->mt04_shift_ptn = $mt04_shift_ptn_rep;
        $this->mt05_workptn = $mt05_workptn_rep;
        $this->mt12_dept_search = $mt12_dept_search_rep;
        $this->mt13_dept_auth = $mt13_dept_auth_rep;
        $this->mt16_deptshiftcalendar = $mt16_deptshiftcalendar_rep;
        $this->mt22_closing_date = $mt22_closing_date_rep;
        $this->mt93_pg = $mt93_pg_rep;
        $this->tr01_work = $tr01_work_rep;
        $this->tr02_emp_calendar = $tr02_emp_calendar_rep;
        $this->tr03_dept_calendar = $tr03_dept_calendar_rep;
    }

    /**
     * シフト月次更新処理画面表示
     * @return view
     */
    public function index(Request $request)
    {
        return parent::viewWithMenu('mng_oprt.ShiftCalendarCarryOver', $this->createViewData());
    }

    public function update(ShiftCalendarCarryOverUpdateRequest $request)
    {
        $today = date('Y-m-d H:i:s');
        $year = substr($request['yearMonth'], 0, 4);
        $month = substr($request['yearMonth'], 7, 2);
        $closing_date_cd = $request['closingDateCd'];
        $dept_cd_list = $request['dept_cd'];
        $last_month_year = $year;
        $last_month = (int)$month - 1;
        if ($last_month == 0) {
            $last_month_year = (int)$last_month_year - 1;
            $last_month = 12;
        }

        // 権限チェック
        // 権限外のdept_cdが送られてきた場合、何もせず終了
        $disp_cls_cd = '01';
        $changeable_dept_cd_list = $this->mt13_dept_auth->getChangeableDept(
            session('id'),
            $disp_cls_cd
        )->pluck('DEPT_CD')->toArray();

        foreach ($dept_cd_list as $checked_dept_cd) {
            if (!in_array($checked_dept_cd, $changeable_dept_cd_list, true)) {
                return redirect('mng_oprt/ShiftCalendarCarryOver');
            }
        }

        $emp_calendar_list = $this->tr02_emp_calendar->getShiftEmpWithMonthAndDept(
            $last_month_year,
            $last_month,
            $closing_date_cd,
            $dept_cd_list
        );

        $month_cls_cd = $this->mt01_control->getMt01()->MONTH_CLS_CD;
        $closing_date = $this->mt22_closing_date->getFirst($closing_date_cd)->CLOSING_DATE;
        $dates = getDatesToClosingDate($year, $month, $closing_date, $month_cls_cd);

        \DB::beginTransaction();
        try {
            foreach ($emp_calendar_list as $emp_calendar) {
                $emp_cd = $emp_calendar->EMP_CD;
                $last_ptn_cd = $emp_calendar->LAST_PTN_CD;
                $last_day_no = $emp_calendar->LAST_DAY_NO;
                // 検索結果の値が無い場合、処理しない
                if (is_nullorwhitespace($last_ptn_cd) || is_nullorwhitespace($last_day_no)) {
                    continue;
                }
                // TR01_WORK登録
                $next_month_last_day_no = $this->insertWork(
                    $emp_cd,
                    $last_ptn_cd,
                    $last_day_no,
                    $year,
                    $month,
                    $dates,
                    $today
                );

                // TR02_EMPCALENDAR登録
                $this->insertEmpcalendar($emp_cd, $year, (int)$month, $last_ptn_cd, $next_month_last_day_no);
            }

            $dept_calendar_list = $this->tr03_dept_calendar->getDeptCalendarDate(
                $last_month_year,
                $last_month,
                $closing_date_cd,
                $dept_cd_list
            );
            foreach ($dept_calendar_list as $dept_calendar) {
                $dept_cd = $dept_calendar->DEPT_CD;
                $last_ptn_cd = $dept_calendar->LAST_PTN_CD;
                $last_day_no = $dept_calendar->LAST_DAY_NO;
                // 検索結果の値が無い場合、処理しない
                if (is_nullorwhitespace($last_ptn_cd) || is_nullorwhitespace($last_day_no)) {
                    continue;
                }

                // MT16_DEPTSHIFTCALENDAR登録
                $next_month_last_day_no = $this->insertDeptshiftcalendar(
                    $dept_cd,
                    $year,
                    (int)$month,
                    $last_ptn_cd,
                    $last_day_no,
                    $closing_date_cd,
                    $dates,
                    $today
                );

                // TR03_DEPTCALENDAR登録
                $this->insertDeptcalendar(
                    $dept_cd,
                    $year,
                    (int)$month,
                    $last_ptn_cd,
                    $next_month_last_day_no,
                    $closing_date_cd
                );
            }
            \DB::commit();
        } catch (\Throwable $e) {
            \Log::debug($e);
            \DB::rollBack();
        }

        return redirect('mng_oprt/ShiftCalendarCarryOver');
    }


    private function createViewData()
    {
        $control = $this->mt01_control->getMt01();
        $today = date('Y-m-d');
        // 月の初期値設定
        // 今月を取得
        $year_and_month = getYearAndMonthWithControl($today, $control->MONTH_CLS_CD, $control->CLOSING_DATE);
        $def_year = $year_and_month['year'];
        // 翌月にする
        $def_month = $year_and_month['month'] + 1;
        if ($def_month === 13) {
            $def_year++;
            $def_month = 1;
        }
        $disp_cls_cd = '01';
        $dept_list = $this->mt12_dept_search->getSorted($disp_cls_cd);
        $changeable_dept_cd_list = $this->mt13_dept_auth->getChangeableDept(
            session('id'),
            $disp_cls_cd
        )->pluck('DEPT_CD')->toArray();
        $closing_dates = $this->mt22_closing_date->getMt22();
        $def_closing_date_cd = $closing_dates->firstWhere('RSV1_CLS_CD', '01')->CLOSING_DATE_CD;

        return compact(
            'def_year',
            'def_month',
            'dept_list',
            'changeable_dept_cd_list',
            'closing_dates',
            'def_closing_date_cd'
        );
    }

    /**
     * 就業情報の新規登録、更新を行う
     * 既にデータがあれば登録せず、引数の$last_day_noを返す
     * データが無ければ最終日のMT04_SHIFTPTN.DAY_NOの項番を返す
     *
     * @param [type] $emp_cd
     * @param [type] $last_ptn_cd
     * @param [type] $last_day_no
     * @param [type] $cald_year
     * @param [type] $cald_month
     * @param [type] $dates
     * @param [type] $today
     * @return integer
     */
    private function insertWork($emp_cd, $last_ptn_cd, $last_day_no, $cald_year, $cald_month, $dates, $today):int
    {

        // 該当データがあれば登録しない
        if ($this->tr01_work->existWithEmpAndCaldYearMonth($emp_cd, $cald_year, $cald_month)) {
            return $last_day_no;
        }

        $shiftPtns = $this->mt04_shift_ptn->getWithShiftPtnCd($last_ptn_cd)->pluck('WORKPTN_CD');
        $countShiftPtn = count($shiftPtns);
        $days_index = (int)$last_day_no; // シフトパターンのインデックス用
        $last_index = count($dates) - 1;
        $workptns = $this->mt05_workptn->getWithWorkptn($shiftPtns->all())->pluck(null, 'WORKPTN_CD');
        $work_records = [];
        foreach ($dates as $i => $date) {
            if (++$days_index > $countShiftPtn) {
                $days_index = 1;
            }
            $workptnCd = $shiftPtns[$days_index - 1];
            $workptn = $workptns[$workptnCd];
            $str_time = $date->format("Y/m/d"). " ". $workptn->TIME_DAILY_HH. ":". $workptn->TIME_DAILY_MI;
            $next_day = (clone $date)->addDay();
            $end_time = $next_day->format("Y/m/d"). " ". $workptn->TIME_DAILY_HH. ":". $workptn->TIME_DAILY_MI;
            if ($i === 0) {
                // 月度の初日の場合、前日の適用終了時間を更新する
                $before_day = (clone $date)->subDay()->format("Y/m/d");
                $update_data = [
                    'WORKPTN_END_TIME' => $str_time,
                    'UPD_DATE' => $today,
                ];
                $this->tr01_work->updateWithKeyAndNotFix($emp_cd, $before_day, $update_data);
            } else {
                // 「対象日翌日の[TR01_WORK.WORKPTN_STR_TIME]」を設定
                // 前日のWORKPTN_END_TIMEを処理中日付のWORKPTN_STR_TIMEで上書きする
                $work_records[$i - 1]['WORKPTN_END_TIME'] = $str_time;
            }
            if ($i === $last_index) {
                // 月度の最終日の場合、データがあれば翌日の開始時間を終了時間にする
                $next_month_first = $this->tr01_work->getWithPrimaryKey($emp_cd, $next_day);
                if ($next_month_first != null) {
                    $end_time = $next_month_first->WORKPTN_STR_TIME;
                }
            }
            $work_records[] = [
                'EMP_CD' => $emp_cd,
                'CALD_YEAR' => $cald_year,
                'CALD_MONTH' => (int)$cald_month,
                'CALD_DATE' => $date->format("Y/m/d"),
                'WORKPTN_CD' => $workptnCd,
                'WORKPTN_STR_TIME' => $str_time,
                'WORKPTN_END_TIME' => $end_time,
                'REASON_CD' => '01',
                'OFC_TIME_HH' => null,
                'OFC_TIME_MI' => null,
                'OFC_CNT' => 0,
                'LEV_TIME_HH' => null,
                'LEV_TIME_MI' => null,
                'LEV_CNT' => 0,
                'OUT1_TIME_HH' => null,
                'OUT1_TIME_MI' => null,
                'OUT1_CNT' => 0,
                'IN1_TIME_HH' => null,
                'IN1_TIME_MI' => null,
                'IN1_CNT' => 0,
                'OUT2_TIME_HH' => null,
                'OUT2_TIME_MI' => null,
                'OUT2_CNT' => 0,
                'IN2_TIME_HH' => null,
                'IN2_TIME_MI' => null,
                'IN2_CNT' => 0,
                'WORK_TIME_HH' => 0,
                'WORK_TIME_MI' => 0,
                'TARD_TIME_HH' => 0,
                'TARD_TIME_MI' => 0,
                'LEAVE_TIME_HH' => 0,
                'LEAVE_TIME_MI' => 0,
                'OUT_TIME_HH' => 0,
                'OUT_TIME_MI' => 0,
                'OVTM1_TIME_HH' => 0,
                'OVTM1_TIME_MI' => 0,
                'OVTM2_TIME_HH' => 0,
                'OVTM2_TIME_MI' => 0,
                'OVTM3_TIME_HH' => 0,
                'OVTM3_TIME_MI' => 0,
                'OVTM4_TIME_HH' => 0,
                'OVTM4_TIME_MI' => 0,
                'OVTM5_TIME_HH' => 0,
                'OVTM5_TIME_MI' => 0,
                'OVTM6_TIME_HH' => 0,
                'OVTM6_TIME_MI' => 0,
                'OVTM7_TIME_HH' => 0,
                'OVTM7_TIME_MI' => 0,
                'OVTM8_TIME_HH' => 0,
                'OVTM8_TIME_MI' => 0,
                'OVTM9_TIME_HH' => 0,
                'OVTM9_TIME_MI' => 0,
                'OVTM10_TIME_HH' => 0,
                'OVTM10_TIME_MI' => 0,
                'EXT1_TIME_HH' => 0,
                'EXT1_TIME_MI' => 0,
                'EXT2_TIME_HH' => 0,
                'EXT2_TIME_MI' => 0,
                'EXT3_TIME_HH' => 0,
                'EXT3_TIME_MI' => 0,
                'EXT4_TIME_HH' => 0,
                'EXT4_TIME_MI' => 0,
                'EXT5_TIME_HH' => 0,
                'EXT5_TIME_MI' => 0,
                'RSV1_TIME_HH' => 0,
                'RSV1_TIME_MI' => 0,
                'RSV2_TIME_HH' => 0,
                'RSV2_TIME_MI' => 0,
                'RSV3_TIME_HH' => 0,
                'RSV3_TIME_MI' => 0,
                'WORKDAY_CNT' => 0,
                'HOLWORK_CNT' => 0,
                'SPCHOL_CNT' => 0,
                'PADHOL_CNT' => 0,
                'ABCWORK_CNT' => 0,
                'COMPDAY_CNT' => 0,
                'RSV1_CNT' => 0,
                'RSV2_CNT' => 0,
                'RSV3_CNT' => 0,
                'UPD_CLS_CD' => '00',
                'FIX_CLS_CD' => '00',
                'RSV1_CLS_CD' => '',
                'RSV2_CLS_CD' => '',
                'ADD_DATE' => $today,
                'UPD_DATE' => $today,
                'REMARK' => '',
                'SUBHOL_CNT' => 0,
                'SUBWORK_CNT' => 0,
            ];
        }
        // 一括の場合パラメータが多すぎてエラーになるため、2回に分けて登録する
        foreach (collect($work_records)->chunk(16) as $work_record) {
            $this->tr01_work->insertWork($work_record->toArray());
        }
        return $days_index;
    }

    /**
     * 社員別カレンダー情報を登録する
     * 既に該当年月のデータがあれば登録しない
     *
     * @param [type] $emp_cd
     * @param [type] $year
     * @param [type] $month
     * @param [type] $last_ptn_cd
     * @param [type] $next_month_last_day_no
     * @return void
     */
    private function insertEmpcalendar($emp_cd, $year, $month, $last_ptn_cd, $next_month_last_day_no)
    {
        if ($this->tr02_emp_calendar->existWithPrimary($year, $month, $emp_cd)) {
            return;
        }

        $this->tr02_emp_calendar->insertRecord([
            'CALD_YEAR' => $year,
            'CALD_MONTH' => $month,
            'EMP_CD' => $emp_cd,
            'LAST_PTN_CD' => $last_ptn_cd,
            'LAST_DAY_NO' => $next_month_last_day_no
        ]);
    }

    private function insertDeptshiftcalendar(
        $dept_cd,
        $year,
        $month,
        $last_ptn_cd,
        $last_day_no,
        $closing_date_cd,
        $dates,
        $today
    ) {
        if ($this->mt16_deptshiftcalendar->existWithDeptYearMonthAndClosing($year, $month, $dept_cd, $closing_date_cd)) {
            return $last_day_no;
        }
        $shiftPtns = $this->mt04_shift_ptn->getWithShiftPtnCd($last_ptn_cd)->pluck('WORKPTN_CD');
        $countShiftPtn = count($shiftPtns);
        $days_index = (int)$last_day_no; // シフトパターンのインデックス用
        $dept_shift_calendar_records = [];
        foreach ($dates as $i => $date) {
            if (++$days_index > $countShiftPtn) {
                $days_index = 1;
            }
            $dept_shift_calendar_records[] = [
                'CALD_YEAR' => $year,
                'CALD_MONTH' => $month,
                'DEPT_CD' => $dept_cd,
                'CALD_DATE' => $date,
                'WORKPTN_CD' => $shiftPtns[$days_index - 1],
                'RSV1_CLS_CD' => '',
                'RSV2_CLS_CD' => '',
                'UPD_DATE' => $today,
                'CLOSING_DATE_CD' => $closing_date_cd
            ];
        }
        $this->mt16_deptshiftcalendar->insertRecord($dept_shift_calendar_records);
        return $days_index;
    }


    /**
     * 社員別カレンダー情報を登録する
     * 既に該当年月のデータがあれば登録しない
     *
     * @param [type] $emp_cd
     * @param [type] $year
     * @param [type] $month
     * @param [type] $last_ptn_cd
     * @param [type] $next_month_last_day_no
     * @return void
     */
    private function insertDeptcalendar(
        $dept_cd,
        $year,
        $month,
        $last_ptn_cd,
        $next_month_last_day_no,
        $closing_date_cd
    ) {
        if ($this->tr03_dept_calendar->existWithPrimary($year, $month, $dept_cd, $closing_date_cd)) {
            return;
        }

        $this->tr03_dept_calendar->insertRecord([
            'CALD_YEAR' => $year,
            'CALD_MONTH' => $month,
            'DEPT_CD' => $dept_cd,
            'LAST_PTN_CD' => $last_ptn_cd,
            'LAST_DAY_NO' => $next_month_last_day_no,
            'CLOSING_DATE_CD' => $closing_date_cd
        ]);
    }
}
