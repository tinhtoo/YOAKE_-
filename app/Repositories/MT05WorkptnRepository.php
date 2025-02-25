<?php

namespace App\Repositories;

use App\Models\MT05Workptn;
use App\Models\MT91ClsDetail;
use App\Models\MT94WorkDesc;
use Illuminate\Support\Facades\DB;

class MT05WorkptnRepository extends MT05Workptn
{
    // 勤務体系から一般のみ取得
    public function workptnsNormal()
    {
        return MT05Workptn::where('COM_CLS_CD', '01')
                ->orderby('WORKPTN_CD')
                ->get();
    }

    public function getAll()
    {
        return MT05Workptn::get();
    }

    /**
     * 引数のワークパターン配列に該当するレコードを取得
     *
     * @param array $workptns
     * @return object
     */
    public function getWithWorkptn($workptns = []) : object
    {
        return MT05Workptn::whereIn('WORKPTN_CD', $workptns)
            ->get();
    }

    public function getCdNameWorkPtnWithComCls($com_cls)
    {
        return MT05Workptn::select('WORKPTN_CD', 'WORKPTN_NAME')
                ->where('COM_CLS_CD', $com_cls)
                ->orderBy('WORKPTN_CD')
                ->paginate(40);
    }

    public function getWorkPtnWithPrimaryKey($id)
    {
        return MT05Workptn::where('WORKPTN_CD', $id)
                ->first();
    }

    public function getWorkDescExcepting02()
    {
        return MT94WorkDesc::where('WORK_DESC_CLS_CD', '<>', '02')
                ->get();
    }

    public function getWithWorkDesc($cls_cd)
    {
        return MT94WorkDesc::where('WORK_DESC_CLS_CD', $cls_cd)
                ->get();
    }

    public function getClsDetail($cls_detail_cd)
    {
        return MT91ClsDetail::where('CLS_CD', $cls_detail_cd)
                ->get();
    }

    public function upsertWorkPtn($record)
    {
        return DB::table($this->table)
                ->upsert($record, $this->primaryKey);
    }

    public function deleteWorkPtn($record)
    {
        return MT05Workptn::where('WORKPTN_CD', $record)
                ->delete();
    }
}
