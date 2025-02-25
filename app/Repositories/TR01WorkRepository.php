<?php

namespace App\Repositories;

use App\Models\TR01Work;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Filters\WorkPlanPrintFilter;
use App\WorkTmSvc\CalculateWorkTime;
use Carbon\Carbon;

class TR01WorkRepository extends TR01Work
{
    public function getWithEmpAndCaldYearMonth($emp_cd, $cald_year, $cald_month)
    {
        return TR01Work::where('EMP_CD', $emp_cd)
                    ->where('CALD_YEAR', $cald_year)
                    ->where('CALD_MONTH', (int)$cald_month)
                    ->orderBy('CALD_DATE')
                    ->get();
    }

    public function getWithPrimaryKey($emp_cd, $cald_date)
    {
        return TR01Work::where('EMP_CD', $emp_cd)
                    ->where('CALD_DATE', $cald_date)
                    ->first();
    }

    public function existWithEmpAndCaldYearMonth($emp_cd, $cald_year, $cald_month)
    {
        return TR01Work::where('EMP_CD', $emp_cd)
                    ->where('CALD_YEAR', $cald_year)
                    ->where('CALD_MONTH', (int)$cald_month)
                    ->exists();
    }

    public function insertWork($record)
    {
        return TR01Work::insert($record);
    }

    /**
     * TR01_WORKの更新
     *
     * @param [type] $emp_cd
     * @param [type] $cald_date
     * @param [type] $udpate_data
     * @return void
     */
    public function updateWithKey($emp_cd, $cald_date, $udpate_data)
    {
        return TR01Work::where('EMP_CD', $emp_cd)
                        ->where('CALD_DATE', $cald_date)
                        ->update($udpate_data);
    }

    public function updateWithKeyAndNotFix($emp_cd, $cald_date, $udpate_data)
    {
        return TR01Work::where('EMP_CD', $emp_cd)
                        ->where('CALD_DATE', $cald_date)
                        ->where('UPD_CLS_CD', '00')
                        ->where('FIX_CLS_CD', '00')
                        ->update($udpate_data);
    }

    public function upsertRecord($record)
    {
        return DB::table($this->table)->upsert($record, $this->primaryKey, $this->fillable);
    }


    /**
     * 就業情報を確定させる
     * .netの実装より、退職かの判定は行っていない（退職者にも就業情報の確定を行う可能性があるため？）
     *
     * @param [type] $year
     * @param [type] $month
     * @param [type] $closing_date_cd
     * @param [type] $dept_cd_list
     * @param [type] $today
     * @return void
     */
    public function updateForFix($year, $month, $closing_date_cd, $dept_cd_list, $today)
    {
        return TR01Work::from('TR01_WORK as TR01')
                        ->leftJoin('MT10_EMP as MT10', 'TR01.EMP_CD', '=', 'MT10.EMP_CD')
                        ->where('TR01.CALD_YEAR', $year)
                        ->where('TR01.CALD_MONTH', $month)
                        ->where('MT10.CLOSING_DATE_CD', $closing_date_cd)
                        ->whereIn('MT10.DEPT_CD', $dept_cd_list)
                        ->update([
                            'FIX_CLS_CD' => '01',
                            'UPD_DATE' => $today
                        ]);
    }

    /**
     * 就業情報から、指定年月の社員名部署名を取得する
     *
     * @param [type] $login_emp_dept_cd
     * @param [type] $dept_cd_list
     * @param [type] $year
     * @param [type] $month
     * @param [type] $closing_date_cd
     * @param boolean $no_fix falseを指定すると、「確定済み」の場合〇を設定する
     * @return object
     */
    public function searchFixEmp($login_emp_dept_cd, $dept_cd_list, $year, $month, $closing_date_cd, $no_fix = true) : object
    {
        return TR01Work::select('MT12.DEPT_CD', 'MT12.DEPT_NAME', 'MT10.EMP_CD', 'MT10.EMP_NAME')
                        ->when(!$no_fix, function ($q) {
                            $q->selectRaw("CASE WHEN MIN([FIX_CLS_CD]) = '00' THEN '' ELSE '〇' END AS FIXED");
                        })
                        ->when($no_fix, function ($q) {
                            $q->selectRaw(" '' AS FIXED");
                        })
                        ->from('TR01_WORK as TR01')
                        ->join('MT10_EMP as MT10', 'TR01.EMP_CD', '=', 'MT10.EMP_CD')
                        ->leftJoin('MT11_LOGIN as MT11', 'TR01.EMP_CD', '=', 'MT11.EMP_CD')
                        ->join('MT12_DEPT as MT12', 'MT10.DEPT_CD', '=', 'MT12.DEPT_CD')
                        ->where('TR01.CALD_YEAR', $year)
                        ->where('TR01.CALD_MONTH', $month)
                        ->where('MT10.CLOSING_DATE_CD', $closing_date_cd)
                        ->when($no_fix, function ($q) {
                            $q->where('TR01.FIX_CLS_CD', '00');
                        })
                        ->where(function ($q) use ($dept_cd_list, $login_emp_dept_cd) {
                            $q->whereIn('MT10.DEPT_CD', $dept_cd_list)
                                ->orWhere('MT10.DEPT_CD', $login_emp_dept_cd);
                        })
                        ->groupBy('MT10.EMP_CD', 'MT10.EMP_NAME', 'MT12.DEPT_CD', 'MT12.DEPT_NAME')
                        ->orderby('MT12.DEPT_CD')
                        ->orderby('MT10.EMP_CD')
                        ->get();
    }

    public function getEmpWorkStatusData($filter, $login_emp_dept_cd, $dept_cd_list)
    {
        return TR01Work::selectRaw("'' OFC_TERM_NAME, '' LEV_TERM_NAME")
            ->select(
                'MT10.DEPT_CD',
                'MT12.DEPT_NAME',
                'TR01.EMP_CD',
                'MT10.EMP_NAME',
                'TR01.WORKPTN_CD',
                'MT05.WORKPTN_NAME',
                'MT05.WORK_CLS_CD',
                'TR01.REASON_CD',
                'MT09.REASON_NAME',
                'MT09.REASON_PTN_CD',
                'TR01.OFC_TIME_HH',
                'TR01.OFC_CNT',
                'TR01.LEV_TIME_HH',
                'TR01.LEV_CNT'
            )
            ->selectRaw("Case When TR01.OFC_TIME_HH Is Null Then ''
                    Else Cast(TR01.OFC_TIME_HH As VarChar) + ':' + RIGHT('00' + Cast(TR01.OFC_TIME_MI As VarChar), 2)
                         End As OFC_TIME")
            ->selectRaw("Case When TR01.LEV_TIME_HH Is Null Then ''
                    Else Cast(TR01.LEV_TIME_HH As VarChar) + ':' + RIGHT('00' + Cast(TR01.LEV_TIME_MI As VarChar), 2)
                         End As LEV_TIME")
            ->from('TR01_WORK as TR01')
            ->join('MT10_EMP as MT10', function ($join) {
                $join->on('TR01.EMP_CD', '=', 'MT10.EMP_CD')
                     ->where('MT10.REG_CLS_CD', '=', '00');
            })
            ->join('MT12_DEPT as MT12', 'MT10.DEPT_CD', '=', 'MT12.DEPT_CD')
            ->join('MT05_WORKPTN as MT05', 'TR01.WORKPTN_CD', '=', 'MT05.WORKPTN_CD')
            ->join('MT09_REASON as MT09', 'TR01.REASON_CD', '=', 'MT09.REASON_CD')
            ->filter($filter)
            ->where(function ($q) use ($dept_cd_list, $login_emp_dept_cd) {
                $q->whereIn('MT10.DEPT_CD', $dept_cd_list)
                    ->orWhere('MT10.DEPT_CD', $login_emp_dept_cd);
            })
            ->orderby('MT10.DEPT_CD')
            ->orderby('TR01.EMP_CD')
            ->get();
    }

    /**
     * 勤務予定表PDFに表示するデータの取得
     *
     * @param [type] $str_date
     * @param [type] $end_date
     * @param [type] $filter
     * @return void
     */
    public function getWorkPlanReportData($str_date, $end_date, $filter)
    {
        return TR01Work::from('TR01_WORK as TR01')
                         ->leftjoin('MT10_EMP as MT10', 'TR01.EMP_CD', '=', 'MT10.EMP_CD')
                         ->join('MT12_DEPT as MT12', 'MT10.DEPT_CD', '=', 'MT12.DEPT_CD')
                         ->join('MT05_WORKPTN as MT05', 'TR01.WORKPTN_CD', '=', 'MT05.WORKPTN_CD')
                         ->filter($filter)
                         ->where('MT12.DISP_CLS_CD', '=', '01')
                         ->whereBetween('TR01.CALD_DATE', [$str_date, $end_date])
                         ->select('MT10.DEPT_CD', 'MT12.DEPT_NAME', 'TR01.EMP_CD', 'MT10.EMP_NAME', 'MT05.WORKPTN_ABR_NAME', 'TR01.CALD_DATE')
                         ->orderby('MT10.DEPT_CD')
                         ->orderby('TR01.EMP_CD')
                         ->orderby('TR01.CALD_DATE')
                         ->get();
    }

    /**
     * 部門コードまたは社員コードで勤怠情報を取得する
     *
     * @param [boolean] $dept_flg trueの場合部門コードで検索、falseの場合社員コードで検索
     * @param [Object] $filter
     * @param [String] $key 部門コードまたは社員コードを設定
     * @return object
     */
    public function getWorkWithEmpOrDept($dept_flg, $filter, $key = null) : object
    {
        return TR01Work::select(
            'TR01.EMP_CD',
            'TR01.CALD_YEAR',
            'TR01.CALD_MONTH',
            'TR01.CALD_DATE',
            'MT09.SPCHOL_CLS_CD',
            'TR01.SPCHOL_CNT',
            'MT09.PADHOL_CLS_CD',
            'TR01.PADHOL_CNT',
            'MT09.ABCWORK_CLS_CD',
            'TR01.ABCWORK_CNT',
            'MT09.COMPDAY_CLS_CD',
            'TR01.COMPDAY_CNT',
            'MT09.RSV1_CLS_CD',
            'TR01.RSV1_CNT',
            'MT09.SUBHOL_CLS_CD',
            'TR01.SUBHOL_CNT',
            'MT09.SUBWORK_CLS_CD',
            'TR01.SUBWORK_CNT'
        )
            ->from('TR01_WORK as TR01')
            ->when(!$dept_flg, function ($sql) use ($key) {
                $sql->where('TR01.EMP_CD', '=', $key);
            })
            ->when($dept_flg, function ($sql) use ($key) {
                $sql->leftJoin('MT10_EMP as MT10', 'TR01.EMP_CD', '=', 'MT10.EMP_CD')
                    ->where('MT10.DEPT_CD', '=', $key)
                    ->where('MT10.REG_CLS_CD', '<>', '02');
            })
            ->leftJoin('MT09_REASON as MT09', 'TR01.REASON_CD', '=', 'MT09.REASON_CD')
            ->filter($filter)
            ->get();
    }

    /**
     * emp_cdで検索して、cald_dateが最新のレコードの「年月」を返す
     *
     * @param [type] $emp_cd
     * @return void
     */
    public function getLastCald($emp_cd)
    {
        return TR01Work::select('CALD_YEAR', 'CALD_MONTH')
                        ->where('EMP_CD', $emp_cd)
                        ->orderby('CALD_DATE', 'desc')
                        ->limit(1)
                        ->get();
    }

    /**
     * 引数に指定された主キーで該当データを削除する
     * （社員情報が登録されているレコードのみ削除）
     *
     * @param $emp_cd
     * @param $cald_date
     * @return
     */
    public function deleteWithPrimaryKey($emp_cd, $cald_date)
    {
        return TR01Work::join('MT10_EMP', 'TR01_WORK.EMP_CD', '=', 'MT10_EMP.EMP_CD')
                        ->where('TR01_WORK.EMP_CD', $emp_cd)
                        ->where('TR01_WORK.CALD_DATE', $cald_date)
                        ->delete();
    }

    /**
     * 事由一覧表用情報取得
     *
     * @param [type] $filter
     * @param [type] $start
     * @param [type] $end
     * @return void
     */
    public function getReasonReportData($filter, $start, $end)
    {
        $reson_01_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as WORKD_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 01")
            ->groupBy('EMP_CD')
            ->toSql();
        $reson_02_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as PADH_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 02")
            ->groupBy('EMP_CD')
            ->toSql();
        $reson_03_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as PADBH_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 03")
            ->groupBy('EMP_CD')
            ->toSql();
        $reson_04_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as PADAH_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 04")
            ->groupBy('EMP_CD')
            ->toSql();

        $reson_05_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as COMPD_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 05")
            ->groupBy('EMP_CD')
            ->toSql();
        $reson_06_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as COMPBD_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 06")
            ->groupBy('EMP_CD')
            ->toSql();
        $reson_07_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as COMPAD_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 07")
            ->groupBy('EMP_CD')
            ->toSql();
        $reson_08_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as SPCH_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 08")
            ->groupBy('EMP_CD')
            ->toSql();
        $reson_09_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as ABCD_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 09")
            ->groupBy('EMP_CD')
            ->toSql();
        $reson_10_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as DIRG_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 10")
            ->groupBy('EMP_CD')
            ->toSql();
        $reson_11_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as DIRR_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 11")
            ->groupBy('EMP_CD')
            ->toSql();
        $reson_12_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as DIRQR_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 12")
            ->groupBy('EMP_CD')
            ->toSql();
        $reson_13_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as BUSJ_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 13")
            ->groupBy('EMP_CD')
            ->toSql();
        $reson_14_sub_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('count(*) as DELAY_COUNT'))
            ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
            ->whereRaw("REASON_CD = 14")
            ->groupBy('EMP_CD')
            ->toSql();

        return TR01Work::select(
            'MT10.DEPT_CD',
            'MT12.DEPT_NAME',
            'TR01.EMP_CD',
            'MT10.EMP_NAME'
        )
        ->selectRaw('IsNull(TR01_WORKD.WORKD_COUNT, 0)   WORKD_COUNT')
        ->selectRaw('IsNull(TR01_PADH.PADH_COUNT, 0)     PADH_COUNT')
        ->selectRaw('IsNull(TR01_PADBH.PADBH_COUNT, 0)   PADBH_COUNT')
        ->selectRaw('IsNull(TR01_PADAH.PADAH_COUNT, 0)   PADAH_COUNT')
        ->selectRaw('IsNull(TR01_COMPD.COMPD_COUNT, 0)   COMPD_COUNT')
        ->selectRaw('IsNull(TR01_COMPBD.COMPBD_COUNT, 0) COMPBD_COUNT')
        ->selectRaw('IsNull(TR01_COMPAD.COMPAD_COUNT, 0) COMPAD_COUNT')
        ->selectRaw('IsNull(TR01_SPCH.SPCH_COUNT, 0)     SPCH_COUNT')
        ->selectRaw('IsNull(TR01_ABCD.ABCD_COUNT, 0)     ABCD_COUNT')
        ->selectRaw('IsNull(TR01_DIRG.DIRG_COUNT, 0)     DIRG_COUNT')
        ->selectRaw('IsNull(TR01_DIRR.DIRR_COUNT, 0)     DIRR_COUNT')
        ->selectRaw('IsNull(TR01_DIRQR.DIRQR_COUNT, 0)   DIRQR_COUNT')
        ->selectRaw('IsNull(TR01_BUSJ.BUSJ_COUNT, 0)     BUSJ_COUNT')
        ->selectRaw('IsNull(TR01_DELAY.DELAY_COUNT, 0)   DELAY_COUNT')
        ->from('TR01_WORK as TR01')
        ->leftJoin('MT10_EMP as MT10', 'TR01.EMP_CD', '=', 'MT10.EMP_CD')
        ->leftJoin('MT12_DEPT as MT12', 'MT10.DEPT_CD', '=', 'MT12.DEPT_CD')
        ->leftJoinSub($reson_01_sub_query, 'TR01_WORKD', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_WORKD.EMP_CD');
        })
        ->leftJoinSub($reson_02_sub_query, 'TR01_PADH', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_PADH.EMP_CD');
        })
        ->leftJoinSub($reson_03_sub_query, 'TR01_PADBH', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_PADBH.EMP_CD');
        })
        ->leftJoinSub($reson_04_sub_query, 'TR01_PADAH', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_PADAH.EMP_CD');
        })
        ->leftJoinSub($reson_05_sub_query, 'TR01_COMPD', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_COMPD.EMP_CD');
        })
        ->leftJoinSub($reson_06_sub_query, 'TR01_COMPBD', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_COMPBD.EMP_CD');
        })
        ->leftJoinSub($reson_07_sub_query, 'TR01_COMPAD', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_COMPAD.EMP_CD');
        })
        ->leftJoinSub($reson_08_sub_query, 'TR01_SPCH', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_SPCH.EMP_CD');
        })
        ->leftJoinSub($reson_09_sub_query, 'TR01_ABCD', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_ABCD.EMP_CD');
        })
        ->leftJoinSub($reson_10_sub_query, 'TR01_DIRG', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_DIRG.EMP_CD');
        })
        ->leftJoinSub($reson_11_sub_query, 'TR01_DIRR', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_DIRR.EMP_CD');
        })
        ->leftJoinSub($reson_12_sub_query, 'TR01_DIRQR', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_DIRQR.EMP_CD');
        })
        ->leftJoinSub($reson_13_sub_query, 'TR01_BUSJ', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_BUSJ.EMP_CD');
        })
        ->leftJoinSub($reson_14_sub_query, 'TR01_DELAY', function ($join) {
            $join->on('TR01.EMP_CD', '=', 'TR01_DELAY.EMP_CD');
        })
        ->whereBetween('CALD_DATE', [$start,$end])
        ->filter($filter)
        ->distinct()
        ->orderBy('MT10.DEPT_CD')
        ->orderBy('TR01.EMP_CD')
        ->get();
    }

    /**
     * 勤怠一覧表用情報取得
     *
     * @param [type] $filter
     * @param [type] $start
     * @param [type] $end
     * @return void
     */
    public function getWorkPtnReportData($filter, $start, $end)
    {
        $sum_count_query = \DB::table('TR01_WORK')->select(
            'EMP_CD',
            DB::raw('Sum(WORKDAY_CNT) SUM_WORKDAY_CNT'),
            DB::raw('Sum(HOLWORK_CNT) SUM_HOLWORK_CNT'),
            DB::raw('Sum(SPCHOL_CNT) SUM_SPCHOL_CNT'),
            DB::raw('Sum(PADHOL_CNT) SUM_PADHOL_CNT'),
            DB::raw('Sum(ABCWORK_CNT) SUM_ABCWORK_CNT'),
            DB::raw('Sum(COMPDAY_CNT) SUM_COMPDAY_CNT')
        )
        ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
        ->groupBy('EMP_CD')
        ->toSql();
        $sum_tard_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('Count(*) SUM_TARD_CNT'))
                    ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
                    ->whereRaw("Not(TARD_TIME_HH = 0 And TARD_TIME_MI = 0)")
                    ->groupBy('EMP_CD')
                    ->toSql();
        $sum_leave_query = \DB::table('TR01_WORK')->select('EMP_CD', DB::raw('Count(*) SUM_LEAVE_CNT'))
                    ->whereRaw("CALD_DATE between '". $start ."' and '". $end."'")
                    ->whereRaw("Not(LEAVE_TIME_HH = 0 And LEAVE_TIME_MI = 0)")
                    ->groupBy('EMP_CD')
                    ->toSql();

        return TR01Work::select('MT10.DEPT_CD', 'MT12.DEPT_NAME', 'TR01.EMP_CD', 'MT10.EMP_NAME')
                        ->selectRaw('IsNull(TR01_SUB.SUM_WORKDAY_CNT, 0) SUM_WORKDAY_CNT')
                        ->selectRaw('IsNull(TR01_SUB.SUM_HOLWORK_CNT, 0) SUM_HOLWORK_CNT')
                        ->selectRaw('IsNull(TR01_SUB.SUM_SPCHOL_CNT, 0)  SUM_SPCHOL_CNT')
                        ->selectRaw('IsNull(TR01_SUB.SUM_PADHOL_CNT, 0)  SUM_PADHOL_CNT')
                        ->selectRaw('IsNull(TR01_SUB.SUM_ABCWORK_CNT, 0) SUM_ABCWORK_CNT')
                        ->selectRaw('IsNull(TR01_SUB.SUM_COMPDAY_CNT, 0) SUM_COMPDAY_CNT')
                        ->selectRaw('IsNull(TR01_TARD.SUM_TARD_CNT, 0)   SUM_TARD_CNT')
                        ->selectRaw('IsNull(TR01_LEAVE.SUM_LEAVE_CNT, 0) SUM_LEAVE_CNT')
                        ->from('TR01_WORK as TR01')
                        ->leftJoin('MT10_EMP as MT10', 'TR01.EMP_CD', '=', 'MT10.EMP_CD')
                        ->leftJoin('MT12_DEPT as MT12', 'MT10.DEPT_CD', '=', 'MT12.DEPT_CD')
                        ->leftJoinSub($sum_count_query, 'TR01_SUB', function ($join) {
                            $join->on('TR01.EMP_CD', '=', 'TR01_SUB.EMP_CD');
                        })
                        ->leftJoinSub($sum_tard_query, 'TR01_TARD', function ($join) {
                            $join->on('TR01.EMP_CD', '=', 'TR01_TARD.EMP_CD');
                        })
                        ->leftJoinSub($sum_leave_query, 'TR01_LEAVE', function ($join) {
                            $join->on('TR01.EMP_CD', '=', 'TR01_LEAVE.EMP_CD');
                        })
                        ->whereBetween('CALD_DATE', [$start,$end])
                        ->filter($filter)
                        ->distinct()
                        ->orderBy('MT10.DEPT_CD')
                        ->orderBy('TR01.EMP_CD')
                        ->get();
    }

    public function getWorkTimeTotalCursor($request, $login_emp_cd, $enable_dept_list)
    {
        $input = $request->all()['filter'];
        $start_date = substr($input['startDate'], 0, 4). substr($input['startDate'], 7, 2). substr($input['startDate'], 12, 2);
        $end_date = substr($input['endDate'], 0, 4). substr($input['endDate'], 7, 2). substr($input['endDate'], 12, 2);
        $start_dept = $input['txtStartDeptCd'] ?? '';
        $end_dept = $input['txtEndDeptCd'] ?? '';
        $start_company = $input['startCompany'] ?? '';
        $end_company = $input['endCompany'] ?? '';
        $start_emp = $input['txtStartEmpCd'] ?? '';
        $end_emp = $input['txtEndEmpCd'] ?? '';
        $reg_cls = $input['regCls'] ?? '';

        $subQuery0 = function ($query) use ($start_date, $end_date) {
            $query->select(\DB::raw("
                SUB0.EMP_CD
                , Sum(SUB0.WORKDAY_CNT) + Sum(SUB0.SUBWORK_CNT)                                              WORKDAY_CNT
                , FORMAT(Round((Sum(SUB0.WORK_TIME_HH)  * 60 + Sum(SUB0.WORK_TIME_MI))  / 60.0, 2, 1), 'F2') WORK_TIME
                , FORMAT(Round((Sum(SUB0.TARD_TIME_HH)  * 60 + Sum(SUB0.TARD_TIME_MI))  / 60.0, 2, 1), 'F2') TARD_TIME
                , FORMAT(Round((Sum(SUB0.EXT1_TIME_HH)  * 60 + Sum(SUB0.EXT1_TIME_MI))  / 60.0, 2, 1), 'F2') EXT1_TIME
                , FORMAT(Round((Sum(SUB0.OVTM1_TIME_HH) * 60 + Sum(SUB0.OVTM1_TIME_MI)) / 60.0, 2, 1), 'F2') OVTM1_TIME
                , FORMAT(Round((Sum(SUB0.OVTM2_TIME_HH) * 60 + Sum(SUB0.OVTM2_TIME_MI)) / 60.0, 2, 1), 'F2') OVTM2_TIME
                , FORMAT(Round((Sum(SUB0.OVTM3_TIME_HH) * 60 + Sum(SUB0.OVTM3_TIME_MI)) / 60.0, 2, 1), 'F2') OVTM3_TIME
                , FORMAT(Round((Sum(SUB0.OVTM4_TIME_HH) * 60 + Sum(SUB0.OVTM4_TIME_MI)) / 60.0, 2, 1), 'F2') OVTM4_TIME
                "))
            ->from('TR01_WORK as SUB0')
            ->whereBetween('SUB0.CALD_DATE', [$start_date, $end_date])
            ->groupby('SUB0.EMP_CD');
        };
        $subQuery1 = function ($query) use ($start_date, $end_date) {
            $query->select(\DB::raw("
                    SUB1.EMP_CD, Count(*) CNT
                        , FORMAT(Round((Sum(MT05.RSV1_HH) * 60 + Sum(MT05.RSV1_MI)) / 60.0, 2, 1), 'F2') RSV1_TIME
                "))
                ->from('TR01_WORK as SUB1')
                ->leftJoin('MT05_WORKPTN as MT05', 'SUB1.WORKPTN_CD', '=', 'MT05.WORKPTN_CD')
                ->whereBetween('SUB1.CALD_DATE', [$start_date, $end_date])
                ->where('MT05.WORK_CLS_CD', '01')
                ->groupby('SUB1.EMP_CD');
        };
        $subQuery2 = function ($query) use ($start_date, $end_date) {
            $query->select(\DB::raw("
                    SUB2.EMP_CD
                    , FORMAT(Round((Sum(SUB2.WORK_TIME_HH) * 60 + Sum(SUB2.WORK_TIME_MI)) / 60.0, 2, 1), 'F2') WORK_TIME
                "))
                ->from('TR01_WORK as SUB2')
                ->leftJoin('MT05_WORKPTN as MT05', 'SUB2.WORKPTN_CD', '=', 'MT05.WORKPTN_CD')
                ->whereBetween('SUB2.CALD_DATE', [$start_date, $end_date])
                ->where('MT05.WORK_CLS_CD', '00')
                ->whereNotIn('SUB2.REASON_CD', ['15', '16', '17'])
                ->groupby('SUB2.EMP_CD');
        };

        return \DB::table($subQuery0, 'TR01')
                    ->selectRaw("MT10.DEPT_CD, MT12.DEPT_NAME, TR01.EMP_CD, MT10.EMP_NAME, MT10.EMP_CLS1_CD
                                , MT92.DESC_DETAIL_NAME EMP_CLS1_NAME
                                , TR01.WORKDAY_CNT, TR01.WORK_TIME, IsNull(SUB1.CNT, 0) CNT
                                , IsNull(SUB1.RSV1_TIME, 0.00) RSV1_TIME
                                , TR01.TARD_TIME, TR01.EXT1_TIME
                                , TR01.OVTM1_TIME, TR01.OVTM2_TIME, TR01.OVTM3_TIME, TR01.OVTM4_TIME
                                , IsNull(SUB2.WORK_TIME, 0) HOL_WORK_TIME")
                    ->leftJoin('MT10_EMP as MT10', 'TR01.EMP_CD', '=', 'MT10.EMP_CD')
                    ->leftJoin('MT12_DEPT as MT12', 'MT10.DEPT_CD', '=', 'MT12.DEPT_CD')
                    ->leftJoin('MT92_DESC_DETAIL as MT92', function ($join) {
                        $join->on('MT10.EMP_CLS1_CD', '=', 'MT92.DESC_DETAIL_CD')
                             ->where('MT92.CLS_CD', '=', '50');
                    })
                    ->leftJoinSub($subQuery1, 'SUB1', function ($join) {
                        $join->on('TR01.EMP_CD', '=', 'SUB1.EMP_CD');
                    })
                    ->leftJoinSub($subQuery2, 'SUB2', function ($join) {
                        $join->on('TR01.EMP_CD', '=', 'SUB2.EMP_CD');
                    })
                    ->where(function ($q) use ($enable_dept_list, $login_emp_cd) {
                        $q->whereIn('MT10.DEPT_CD', $enable_dept_list)
                            ->orWhere('MT10.EMP_CD', $login_emp_cd);
                    })
                    ->when(!is_nullorwhitespace($start_dept), function ($q) use ($start_dept) {
                        $q->where("MT10.DEPT_CD", ">=", $start_dept);
                    })
                    ->when(!is_nullorwhitespace($end_dept), function ($q) use ($end_dept) {
                        $q->where("MT10.DEPT_CD", "<=", $end_dept);
                    })
                    ->when(!is_nullorwhitespace($start_company), function ($q) use ($start_company) {
                        $q->where("MT10.COMPANY_CD", ">=", $start_company);
                    })
                    ->when(!is_nullorwhitespace($end_company), function ($q) use ($end_company) {
                        $q->where("MT10.COMPANY_CD", "<=", $end_company);
                    })
                    ->when(!is_nullorwhitespace($start_emp), function ($q) use ($start_emp) {
                        $q->where("TR01.EMP_CD", ">=", $start_emp);
                    })
                    ->when(!is_nullorwhitespace($end_emp), function ($q) use ($end_emp) {
                        $q->where("TR01.EMP_CD", "<=", $end_emp);
                    })
                    ->when(!is_nullorwhitespace($reg_cls), function ($q) use ($reg_cls) {
                        $q->where("MT10.REG_CLS_CD", "=", $reg_cls);
                    })
                    ->orderBy('MT10.DEPT_CD')
                    ->orderBy('TR01.EMP_CD')
                    ->cursor();
    }

    public function getWorkTimeDetailCursor($filter, $login_emp_cd, $enable_dept_list)
    {
        return TR01Work::from('TR01_WORK as TR01')
                    ->filter($filter)
                    ->where(function ($q) use ($enable_dept_list, $login_emp_cd) {
                        $q->whereIn('MT10.DEPT_CD', $enable_dept_list)
                            ->orWhere('MT10.EMP_CD', $login_emp_cd);
                    })
                    ->cursor();
    }

    /**
     * 未打刻／二重打刻一覧表用情報取得
     *
     * @param [type] $filter
     * @param [type] $start
     * @param [type] $end
     * @return object
     */
    public function getNoDbTimeStampReportData($chkNoTime, $filter, $start, $end) : object
    {
        return TR01Work::select(
            'MT10.DEPT_CD',
            'MT12.DEPT_NAME',
            'TR01.EMP_CD',
            'MT10.EMP_NAME',
            'TR01.CALD_DATE',
            'MT05.WORKPTN_NAME',
            'MT09.REASON_NAME',
            'TR01.WORKPTN_STR_TIME',
            'TR01.WORKPTN_END_TIME',
            'TR01.OFC_TIME_HH',
            'TR01.LEV_TIME_HH',
            'TR01.OUT1_TIME_HH',
            'TR01.IN1_TIME_HH',
            'TR01.OUT2_TIME_HH',
            'TR01.IN2_TIME_HH',
            'TR01.OFC_CNT',
            'TR01.LEV_CNT',
            'TR01.OUT1_CNT',
            'TR01.IN1_CNT',
            'TR01.OUT2_CNT',
            'TR01.IN2_CNT'
        )
            ->from('TR01_WORK as TR01')
            ->leftJoin('MT10_EMP as MT10', 'TR01.EMP_CD', '=', 'MT10.EMP_CD')
            ->leftJoin('MT12_DEPT as MT12', 'MT10.DEPT_CD', '=', 'MT12.DEPT_CD')
            ->leftJoin('MT05_WORKPTN as MT05', 'TR01.WORKPTN_CD', '=', 'MT05.WORKPTN_CD')
            ->leftJoin('MT09_REASON as MT09', 'TR01.REASON_CD', '=', 'MT09.REASON_CD')
            ->filter($filter)
            ->whereBetween('TR01.CALD_DATE', [$start,$end])
            ->where(function ($q) use ($chkNoTime) {
                $q->where(function ($sql) {
                    $sql->whereNull('TR01.OFC_TIME_HH')
                        ->whereNotNull('TR01.LEV_TIME_HH');
                });
                $q->orwhere(function ($sql) {
                    $sql->whereNotNull('TR01.OFC_TIME_HH')
                        ->whereNull('TR01.LEV_TIME_HH');
                });
                $q->orwhere(function ($sql) {
                    $sql->whereNull('TR01.OUT1_TIME_HH')
                        ->whereNotNull('TR01.IN1_TIME_HH');
                });
                $q->orwhere(function ($sql) {
                    $sql->whereNotNull('TR01.OUT1_TIME_HH')
                        ->whereNull('TR01.IN1_TIME_HH');
                });
                $q->orwhere(function ($sql) {
                    $sql->whereNull('TR01.OUT2_TIME_HH')
                        ->whereNotNull('TR01.IN2_TIME_HH');
                });
                $q->orwhere(function ($sql) {
                    $sql->whereNotNull('TR01.OUT2_TIME_HH')
                        ->whereNull('TR01.IN2_TIME_HH');
                });
                $q->when($chkNoTime, function ($query) {
                    $query->orwhere(function ($query2) {
                        $query2->where('MT05.WORK_CLS_CD', '=', '01')
                        ->whereNull('TR01.OFC_TIME_HH')
                        ->whereNull('TR01.LEV_TIME_HH')
                        ->whereNotIn('TR01.REASON_CD', ['02', '05', '08', '09'])
                        ->where('TR01.CALD_DATE', '<=', Carbon::today()->format('Y-m-d'));
                    });
                });
                $q->orWhere(function ($sql) {
                    $sql->where('TR01.OFC_CNT', '>=', '2')
                        ->whereNull('TR01.OFC_TIME_HH');
                });
                $q->orwhere(function ($sql) {
                    $sql->where('TR01.LEV_CNT', '>=', '2')
                        ->whereNull('TR01.LEV_TIME_HH');
                });
                $q->orwhere(function ($sql) {
                    $sql->where('TR01.OUT1_CNT', '>=', '2')
                        ->whereNull('TR01.OUT1_TIME_HH');
                });
                $q->orwhere(function ($sql) {
                    $sql->where('TR01.IN1_CNT', '>=', '2')
                        ->whereNull('TR01.IN1_TIME_HH');
                });
                $q->orwhere(function ($sql) {
                    $sql->where('TR01.OUT2_CNT', '>=', '2')
                        ->whereNull('TR01.OUT2_TIME_HH');
                });
                $q->orwhere(function ($sql) {
                    $sql->where('TR01.IN2_CNT', '>=', '2')
                        ->whereNull('TR01.IN2_TIME_HH');
                });
            })
            ->orderBy('MT10.DEPT_CD')
            ->orderBy('TR01.EMP_CD')
            ->get();
    }

    /**
     * 社員コードと日付から、該当行を取得
     * 日付の条件は「WORKPTN_STR_TIME～WORKPTN_END_TIMEの範囲」
     *
     * @param $emp_cd
     * @param $date
     * @return object
     */
    public function getWithEmpAndDate($emp_cd, $date)
    {
        return TR01Work::where('EMP_CD', $emp_cd)
                    ->where('WORKPTN_STR_TIME', '<=', $date)
                    ->where('WORKPTN_END_TIME', '>', $date)
                    ->first();
    }

    /**
     * 勤務時間の再計算を行う
     * 引数を計算後の値で更新する。
     *
     * @param object $work_row
     * @return object
     */
    public function recalcWork($work_row)
    {
        $calc_row = clone $work_row;

        // 再計算するために値設定
        if (is_nullorempty($work_row->OFC_TIME_HH)) {
            $calc_row->OFC_TIME_HH = null;              // 出勤時刻(時間)
            $calc_row->OFC_TIME_MI = null;              // 出勤時刻(分)
        }
        $calc_row->OFC_CNT = $work_row->OFC_CNT;        // 出勤回数

        if (is_nullorempty($work_row->LEV_TIME_HH)) {
            $calc_row->LEV_TIME_HH = null;              // 退出時刻(時間)
            $calc_row->LEV_TIME_MI = null;              // 退出時刻(分)
        }
        $calc_row->LEV_CNT = $work_row->LEV_CNT;        // 退出回数

        if (is_nullorempty($work_row->OUT1_TIME_HH)) {
            $calc_row->OUT1_TIME_HH = null;             // 外出1時刻(時間)
            $calc_row->OUT1_TIME_MI = null;             // 外出1時刻(分)
        }
        $calc_row->OUT1_CNT = $work_row->OUT1_CNT;      // 外出1回数

        if (is_nullorempty($work_row->IN1_TIME_HH)) {
            $calc_row->IN1_TIME_HH = null;              // 再入1時刻(時間)
            $calc_row->IN1_TIME_MI = null;              // 再入1時刻(分)
        }
        $calc_row->IN1_CNT = $work_row->IN1_CNT;        // 再入1回数

        if (is_nullorempty($work_row->OUT2_TIME_HH)) {
            $calc_row->OUT2_TIME_HH = null;             // 外出2時刻(時間)
            $calc_row->OUT2_TIME_MI = null;             // 外出2時刻(分)
        }
        $calc_row->OUT2_CNT = $work_row->OUT2_CNT;      // 外出2回数

        if (is_nullorempty($work_row->IN2_TIME_HH)) {
            $calc_row->IN2_TIME_HH = null;              // 再入2時刻(時間)
            $calc_row->IN2_TIME_MI = null;              // 再入2時刻(分)
        }

        // 再計算
        $calc = new CalculateWorkTime($calc_row);
        $calc->caluclateWorkTime();

        // 再計算後の値設定
        $work_row->WORK_TIME_HH = $calc_row->WORK_TIME_HH;          // 出勤時間(時間)
        $work_row->WORK_TIME_MI = $calc_row->WORK_TIME_MI;          // 出勤時間(分)
        $work_row->TARD_TIME_HH = $calc_row->TARD_TIME_HH;          // 遅刻時間(時間)
        $work_row->TARD_TIME_MI = $calc_row->TARD_TIME_MI;          // 遅刻時間(分)
        $work_row->LEAVE_TIME_HH = $calc_row->LEAVE_TIME_HH;        // 早退時間(時間)
        $work_row->LEAVE_TIME_MI = $calc_row->LEAVE_TIME_MI;        // 早退時間(分)
        $work_row->OUT_TIME_HH = $calc_row->OUT_TIME_HH;            // 外出時間(時間)
        $work_row->OUT_TIME_MI = $calc_row->OUT_TIME_MI;            // 外出時間(分)
        $work_row->OVTM1_TIME_HH = $calc_row->OVTM1_TIME_HH;        // 残業項目1時間(時間)
        $work_row->OVTM1_TIME_MI = $calc_row->OVTM1_TIME_MI;        // 残業項目1時間(分)
        $work_row->OVTM2_TIME_HH = $calc_row->OVTM2_TIME_HH;        // 残業項目2時間(時間)
        $work_row->OVTM2_TIME_MI = $calc_row->OVTM2_TIME_MI;        // 残業項目2時間(分)
        $work_row->OVTM3_TIME_HH = $calc_row->OVTM3_TIME_HH;        // 残業項目3時間(時間)
        $work_row->OVTM3_TIME_MI = $calc_row->OVTM3_TIME_MI;        // 残業項目3時間(分)
        $work_row->OVTM4_TIME_HH = $calc_row->OVTM4_TIME_HH;        // 残業項目4時間(時間)
        $work_row->OVTM4_TIME_MI = $calc_row->OVTM4_TIME_MI;        // 残業項目4時間(分)
        $work_row->OVTM5_TIME_HH = $calc_row->OVTM5_TIME_HH;        // 残業項目5時間(時間)
        $work_row->OVTM5_TIME_MI = $calc_row->OVTM5_TIME_MI;        // 残業項目5時間(分)
        $work_row->OVTM6_TIME_HH = $calc_row->OVTM6_TIME_HH;        // 残業項目6時間(時間)
        $work_row->OVTM6_TIME_MI = $calc_row->OVTM6_TIME_MI;        // 残業項目6時間(分)
        $work_row->EXT1_TIME_HH = $calc_row->EXT1_TIME_HH;          // 割増項目1時間(時間)
        $work_row->EXT1_TIME_MI = $calc_row->EXT1_TIME_MI;          // 割増項目1時間(分)
        $work_row->EXT2_TIME_HH = $calc_row->EXT2_TIME_HH;          // 割増項目2時間(時間)
        $work_row->EXT2_TIME_MI = $calc_row->EXT2_TIME_MI;          // 割増項目2時間(分)
        $work_row->EXT3_TIME_HH = $calc_row->EXT3_TIME_HH;          // 割増項目3時間(時間)
        $work_row->EXT3_TIME_MI = $calc_row->EXT3_TIME_MI;          // 割増項目3時間(分)
        $work_row->WORKDAY_CNT = (float) $calc_row->WORKDAY_CNT;    // 出勤日数
        $work_row->HOLWORK_CNT = (float) $calc_row->HOLWORK_CNT;    // 休出日数
        $work_row->SPCHOL_CNT = (float) $calc_row->SPCHOL_CNT;      // 特休日数
        $work_row->PADHOL_CNT = (float) $calc_row->PADHOL_CNT;      // 有休日数
        $work_row->ABCWORK_CNT = (float) $calc_row->ABCWORK_CNT;    // 欠勤日数
        $work_row->COMPDAY_CNT = (float) $calc_row->COMPDAY_CNT;    // 代休日数
        $work_row->SUBHOL_CNT = (float) $calc_row->SUBHOL_CNT;      // 振休日数
        $work_row->SUBWORK_CNT = (float) $calc_row->SUBWORK_CNT;    // 振出日数
        $work_row->RSV1_CNT = $calc_row->RSV1_CNT;                  // 予備１日数(無給特休日数)

        // 浮動小数点型の文字列になっているため、ここでfloatに変換して元の値に戻す。
        $work_row->RSV2_CNT = (float) $work_row->RSV2_CNT;
        $work_row->RSV3_CNT = (float) $work_row->RSV3_CNT;
        return $work_row;
    }

    /**
     * 引数の年月度に在籍中のシフト勤務者の勤務パターンを一覧で取得
     *
     * @param [type] $year
     * @param [type] $month
     * @param [type] $closing_date_cd
     * @param [type] $dept_cd
     * @param [type] $emp_cd
     * @return object
     */
    public function getShiftWorkPtnInYearMonth($year, $month, $closing_date_cd, $dept_cd, $emp_cd)
    {
        return TR01Work::from('TR01_WORK as TR01')
                ->select(
                    'MT12.DEPT_NAME',
                    'TR01.EMP_CD',
                    'MT10.EMP_NAME',
                    'TR01.CALD_DATE',
                    'TR01.WORKPTN_CD',
                    'TR02.LAST_PTN_CD',
                    'TR02.LAST_DAY_NO',
                )
                ->leftJoin('MT10_EMP as MT10', function ($q) {
                    $q->on('TR01.EMP_CD', '=', 'MT10.EMP_CD')
                        ->where('MT10.REG_CLS_CD', '=', '00');
                })
                ->leftJoin('TR02_EMPCALENDAR as TR02', function ($q) {
                    $q->on('TR01.EMP_CD', '=', 'TR02.EMP_CD')
                        ->on('TR01.CALD_YEAR', '=', 'TR02.CALD_YEAR')
                        ->on('TR01.CALD_MONTH', '=', 'TR02.CALD_MONTH');
                })
                ->leftJoin('MT12_DEPT as MT12', 'MT10.DEPT_CD', '=', 'MT12.DEPT_CD')
                ->leftJoin('MT02_CALENDAR_PTN as MT02', 'MT10.CALENDAR_CD', '=', 'MT02.CALENDAR_CD')
                ->where('TR01.CALD_YEAR', $year)
                ->where('TR01.CALD_MONTH', $month)
                ->where('MT10.REG_CLS_CD', '00')
                ->where('MT02.CALENDAR_CLS_CD', '01')
                ->when(!is_null($dept_cd), function ($q) use ($dept_cd) {
                    $q->where('MT10.DEPT_CD', $dept_cd);
                })
                ->when(!is_null($closing_date_cd), function ($q) use ($closing_date_cd) {
                    $q->where('MT10.CLOSING_DATE_CD', $closing_date_cd);
                })
                ->when(!is_null($emp_cd), function ($q) use ($emp_cd) {
                    $q->where('MT10.EMP_CD', $emp_cd);
                })
                ->orderBy('TR01.EMP_CD')
                ->orderBy('TR01.CALD_DATE')
                ->get();
    }

    public function getWithEmpCd($emp_cd)
    {
        return TR01Work::where('EMP_CD', $emp_cd)
                        ->get();
    }

    public function updateWithEmpCd($emp_cd, $udpate_data)
    {
        return TR01Work::where('EMP_CD', $emp_cd)
                        ->update($udpate_data);
    }
}
