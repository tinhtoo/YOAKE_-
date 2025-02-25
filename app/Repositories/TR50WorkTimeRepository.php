<?php

namespace App\Repositories;

use App\Models\TR50WorkTime;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class TR50WorkTimeRepository extends TR50WorkTime
{
    /**
     * 社員コードと日付をキーに更新する
     *
     * @param [type] $emp_cd
     * @param [type] $cald_date
     * @param [type] $udpate_data
     * @return void
     */
    public function update50WorkWithEmpCdCaldDate($emp_cd, $cald_date, $udpate_data)
    {
        return TR50WorkTime::where('EMP_CD', $emp_cd)
                        ->where('CALD_DATE', $cald_date)
                        ->update($udpate_data);
    }

    /**
     * 端末設置場所名を取得
     *
     * @param [type] $emp_cd
     * @param [type] $cald_date
     * @param [type] $worktime_cls_cd
     * @return object
     */
    public function getTermName($emp_cd, $cald_date, $worktime_cls_cd)
    {
        return TR50WorkTime::select("MT95.TERM_NAME")
                        ->from("TR50_WORKTIME as TR50")
                        ->join("MT95_TERM as MT95", "TR50.TERM_NO", "=", "MT95.TERM_NO")
                        ->where("TR50.EMP_CD", $emp_cd)
                        ->where("TR50.CALD_DATE", $cald_date)
                        ->where("TR50.WORKTIME_CLS_CD", $worktime_cls_cd)
                        ->groupBy("TR50.EMP_CD")
                        ->groupBy("TR50.CALD_DATE")
                        ->groupBy("TR50.TERM_NO")
                        ->groupBy("MT95.TERM_NAME")
                        ->get();
    }

    /**
     * 未打刻・二重打刻一覧表のデータ取得
     * @param [type] $emp_cd
     * @param [type] $str_time
     * @param [type] $end_time
     * @return void
     */
    public function getWorkTimeRecords($emp_cd, $str_time, $end_time)
    {
        return TR50WorkTime::select("CRT_DATE", "WORKTIME_CLS_CD", "WORK_DATE", "WORK_TIME_HH", "WORK_TIME_MI")
                        ->from("TR50_WORKTIME")
                        ->where("EMP_CD", $emp_cd)
                        ->whereBetween("CRT_DATE", [$str_time, $end_time])
                        ->orderBy("CRT_DATE")
                        ->orderBy("WORKTIME_CLS_CD")
                        ->get();
    }

    public function getNoOuted()
    {
        return TR50WorkTime::where('DATA_OUT_CLS_CD', '00')
                ->get();
    }

    /**
     * 主キーで検索して更新
     *
     * @param $emp_cd
     * @param $crt_date
     * @param $term_no
     * @param array $update_data
     * @return void
     */
    public function updateWithPrimary($emp_cd, $crt_date, $term_no, $update_data)
    {
        TR50WorkTime::where('EMP_CD', $emp_cd)
                    ->where('CRT_DATE', $crt_date)
                    ->where('TERM_NO', $term_no)
                    ->update($update_data);
        return ;
    }

    public function getWithEmpAndCrtRange($emp_cd, $workptn_str_time, $workptn_end_time)
    {
        return TR50WorkTime::where('DATA_OUT_CLS_CD', '00')
                        ->where('EMP_CD', $emp_cd)
                        ->whereBetween('CRT_DATE', [$workptn_str_time, $workptn_end_time])
                        ->get();
    }

    /**
     * レコードを登録
     *
     * @param array $records
     * @return void
     */
    public function insertRecords($records)
    {
        foreach (array_chunk($records, 100) as $record_chunk) {
            TR50WorkTime::insert($record_chunk);
        }
        return ;
    }


    public function upsertRecords($records)
    {
        foreach (array_chunk($records, 100) as $record_chunk) {
            DB::table($this->table)->upsert($record_chunk, $this->primaryKey, $this->fillable);
        }
        return ;
    }

    public function getWithEmpCd($emp_cd)
    {
        return TR50WorkTime::where('EMP_CD', $emp_cd)
                            ->get();
    }

    public function updateWithEmpCd($emp_cd, $update_data)
    {
        return TR50WorkTime::where('EMP_CD', $emp_cd)
                    ->update($update_data);
    }
}
