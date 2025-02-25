<?php

namespace App\Repositories;

use App\Models\TR02EmpCalendar;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class TR02EmpCalendarRepository extends TR02EmpCalendar
{
    /**
     * シフトカレンダーから、シフト勤務社員の行を検索して返す。
     * 引数$dept_cd_listがNULLの場合、NULLを返す。
     * 引数$dept_cd_listが空配列の場合、該当なしになる。
     *
     * @param [type] $year
     * @param [type] $month
     * @param [type] $closing_date_cd
     * @param [type] $dept_cd_list
     * @return object
     */
    public function getShiftEmpWithMonthAndDept($year, $month, $closing_date_cd, $dept_cd_list): object
    {
        if ($dept_cd_list == null) {
            return null;
        }

        return TR02EmpCalendar::select('TR02.EMP_CD', 'TR02.LAST_PTN_CD', 'TR02.LAST_DAY_NO')
                    ->from('TR02_EMPCALENDAR as TR02')
                    ->leftJoin('MT10_EMP as MT10', 'TR02.EMP_CD', '=', 'MT10.EMP_CD')
                    ->leftJoin('MT02_CALENDAR_PTN as MT02', 'MT10.CALENDAR_CD', '=', 'MT02.CALENDAR_CD')
                    ->where('TR02.CALD_YEAR', $year)
                    ->where('TR02.CALD_MONTH', $month)
                    ->where('MT10.CLOSING_DATE_CD', $closing_date_cd)
                    ->whereIn('MT10.DEPT_CD', $dept_cd_list)
                    ->whereIn('MT10.REG_CLS_CD', ['00', '01'])
                    ->where('MT02.CALENDAR_CLS_CD', '01')
                    ->whereExists(function ($query) {
                        $query->select(\DB::raw(1))
                            ->from('MT04_SHIFTPTN as MT04')
                            ->whereColumn('TR02.LAST_PTN_CD', 'MT04.SHIFTPTN_CD');
                    })
                    ->orderBy('TR02.EMP_CD')
                    ->get();
    }

    public function getWithPrimary($year, $month, $emp_cd)
    {
        return TR02EmpCalendar::where('CALD_YEAR', $year)
                            ->where('CALD_MONTH', $month)
                            ->where('EMP_CD', $emp_cd)
                            ->first();
    }

    public function existWithPrimary($year, $month, $emp_cd)
    {
        return TR02EmpCalendar::where('CALD_YEAR', $year)
                            ->where('CALD_MONTH', $month)
                            ->where('EMP_CD', $emp_cd)
                            ->exists();
    }

    public function insertRecord($record)
    {
        return TR02EmpCalendar::insert($record);
    }

    public function upsertRecord($record, $update_col)
    {
        return DB::table($this->table)->upsert($record, $this->primaryKey, $update_col);
    }

    public function getWithEmpCd($emp_cd)
    {
        return TR02EmpCalendar::where('EMP_CD', $emp_cd)
                                ->get();
    }

    public function updateWithEmpCd($emp_cd, $update_data)
    {
        return TR02EmpCalendar::where('EMP_CD', $emp_cd)
                    ->update($update_data);
    }

    /**
     * 引数に指定された主キーで該当データを削除する
     * （社員情報が登録されているレコードのみ削除）
     *
     * @param $emp_cd
     * @param $cald_year
     * @param $cald_month
     * @return
     */
    public function deleteWithPrimaryKey($emp_cd, $cald_year, $cald_month)
    {
        return TR02EmpCalendar::join('MT10_EMP', 'TR02_EMPCALENDAR.EMP_CD', '=', 'MT10_EMP.EMP_CD')
                        ->where('TR02_EMPCALENDAR.EMP_CD', $emp_cd)
                        ->where('TR02_EMPCALENDAR.CALD_YEAR', $cald_year)
                        ->where('TR02_EMPCALENDAR.CALD_MONTH', $cald_month)
                        ->delete();
    }
}
