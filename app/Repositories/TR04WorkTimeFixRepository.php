<?php

namespace App\Repositories;

use App\Models\TR04WorkTimeFix;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TR04WorkTimeFixRepository extends TR04WorkTimeFix
{

    /**
     * 部門コードのリストと他主キーを受け取り、該当するレコードを返す
     *
     * @param [type] $year
     * @param [type] $month
     * @param [type] $closing_date_cd
     * @param [type] $dept_cd_list
     * @return object
     */
    public function getWithDeptListAndPrimary($year, $month, $closing_date_cd, $dept_cd_list) :object
    {
        return TR04WorkTimeFix::where('CALD_YEAR', $year)
                    ->where('CALD_MONTH', $month)
                    ->where('CLOSING_DATE_CD', $closing_date_cd)
                    ->whereIn('DEPT_CD', $dept_cd_list)
                    ->get();
    }

    public function upsertRecord($record, $update_col)
    {
        return DB::table($this->table)->upsert($record, $this->primaryKey, $update_col);
    }

    /**
     * 社員番号と年月をキーに、確定済みかチェックする
     *
     * @param $emp_cd
     * @param $year
     * @param $month
     * @return bool
     */
    public function existWithEmpAndYM($emp_cd, $year, $month) :bool
    {
        return TR04WorkTimeFix::from('TR04_WORKTIMEFIX as TR04')
                ->join('MT10_EMP as MT10', function ($q) use ($emp_cd) {
                    $q->on('TR04.DEPT_CD', '=', 'MT10.DEPT_CD')
                      ->on('TR04.CLOSING_DATE_CD', '=', 'MT10.CLOSING_DATE_CD')
                      ->where('MT10.EMP_CD', $emp_cd);
                })
                ->where('CALD_YEAR', $year)
                ->where('CALD_MONTH', $month)
                ->exists();
    }
    
    /**
     * 部門コードと年月、締日コードをキーに、確定済みかチェックする
     *
     * @param $emp_cd
     * @param $year
     * @param $month
     * @return bool
     */
    public function existWithDeptAndYM($dept_cd, $year, $month, $closing_date_cd)
    {
        return TR04WorkTimeFix::where('DEPT_CD', $dept_cd)
                ->where('CALD_YEAR', $year)
                ->where('CALD_MONTH', $month)
                ->where('CLOSING_DATE_CD', $closing_date_cd)
                ->exists();
    }
}
