<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\MT06OverTmLmtEditorRequest;
use Illuminate\Http\Request;
use App\Models\MT06OvertmLmt;
use App\Models\MT02CalendarPtn;
use App\Models\MT94WorkDesc;
use App\Models\MT91ClsDetail;

use App\Repositories\MT93PgRepository;

class MT06OverTmLmtEditorController extends Controller
{
    public function __construct(MT93PgRepository $pg_repository)
    {
        parent::__construct($pg_repository, '000010');
    }

    public function index(Request $request)
    {
        // カレンダーリストボックスを取得
        $items = MT02CalendarPtn::orderBy('CALENDAR_CD', 'ASC')->get();

        // {残業項目}リストボックスを取得
        $works = MT94WorkDesc::where('WORK_DESC_CLS_CD', '01')->orderBy('WORK_DESC_CD', 'ASC')->get();

        // セレクトボックスのデータを取得
        $CalendarCdKey = $request->CalendarCd;

        $OVTM1key = null;
        $OVTM2key = null;
        $OVTM3key = null;
        $OVTM4key = null;
        $OVTM5key = null;
        $OVTM6key = null;
        $OVTM1HRkey = null;
        $OVTM2HRkey = null;
        $OVTM3HRkey = null;
        $OVTM4HRkey = null;
        $OVTM5HRkey = null;
        $OVTM6HRkey = null;
        $TtlOvtm1Hr = null;
        $TtlOvtm2Hr = null;
        $TtlOvtm3Hr = null;
        $enabled02 = null;
        $NoOvertmMisOld = null;

        if ($CalendarCdKey != null) {
            $mt06_overtm_lmt = MT06OvertmLmt::where('CALENDAR_CD', $CalendarCdKey)->first() ?? new MT06OvertmLmt();
            // 各残業項目ボックスの値を取得
            $OVTM1key = $mt06_overtm_lmt['OVTM1_CD'];
            $OVTM2key = $mt06_overtm_lmt['OVTM2_CD'];
            $OVTM3key = $mt06_overtm_lmt['OVTM3_CD'];
            $OVTM4key = $mt06_overtm_lmt['OVTM4_CD'];
            $OVTM5key = $mt06_overtm_lmt['OVTM5_CD'];
            $OVTM6key = $mt06_overtm_lmt['OVTM6_CD'];

            // 各残業項目(時間/月)の値を取得
            $OVTM1HRkey = $mt06_overtm_lmt['OVTM1_HR'];
            $OVTM2HRkey = $mt06_overtm_lmt['OVTM2_HR'];
            $OVTM3HRkey = $mt06_overtm_lmt['OVTM3_HR'];
            $OVTM4HRkey = $mt06_overtm_lmt['OVTM4_HR'];
            $OVTM5HRkey = $mt06_overtm_lmt['OVTM5_HR'];
            $OVTM6HRkey = $mt06_overtm_lmt['OVTM6_HR'];

            $TtlOvtm1Hr = $mt06_overtm_lmt['TTL_OVTM1_HR'];
            $TtlOvtm2Hr = $mt06_overtm_lmt['TTL_OVTM2_HR'];
            $TtlOvtm3Hr = $mt06_overtm_lmt['TTL_OVTM3_HR'];

            $NoOvertmMisOld = $mt06_overtm_lmt['NO_OVERTM_MI'];

            // セレクトボックスで取得した値の行のMT_02のCALENDAR_CLS_CDを検索
            $enabled02 =  MT02CalendarPtn::where('CALENDAR_CD', $CalendarCdKey)->first()['CALENDAR_CLS_CD'];
        }
        // 分未満リストボックス
        $NoOvertmMis = MT91ClsDetail::where('CLS_CD', '05')->orderBy('CLS_DETAIL_CD', 'ASC')->get();

        // ビューに変数を渡す
        return parent::viewWithMenu('master.MT06OverTmLmtEditor', compact(
            'items',
            'works',
            'CalendarCdKey',
            'enabled02',
            'OVTM1key',
            'OVTM2key',
            'OVTM3key',
            'OVTM4key',
            'OVTM5key',
            'OVTM6key',
            'OVTM1HRkey',
            'OVTM2HRkey',
            'OVTM3HRkey',
            'OVTM4HRkey',
            'OVTM5HRkey',
            'OVTM6HRkey',
            'TtlOvtm1Hr',
            'TtlOvtm2Hr',
            'TtlOvtm3Hr',
            'NoOvertmMis',
            'NoOvertmMisOld'
        ));
    }

    public function update(MT06OverTmLmtEditorRequest $request)
    {
     // 更新ボタンが押されたとき
        if ($request->has('btnUpdate')) {
            // 選択されたデータ
            $inputs = $request->all();
            // リストボックスで選択されている項目のカレンダーコードから列を取得
            $data = MT06OvertmLmt::find($request->CalendarCdData);

            // 新規登録
            if ($data == null) {
                $data = new MT06OvertmLmt;

                $data->CALENDAR_CD = $request->CalendarCdData;

                $data->OVTM7_CD = null;
                $data->OVTM8_CD = null;
                $data->OVTM9_CD = null;
                $data->OVTM10_CD = null;

                $data->OVTM7_HR = null;
                $data->OVTM8_HR = null;
                $data->OVTM9_HR = null;
                $data->OVTM10_HR = null;

                $data->RSV1_CLS_CD = "";
                $data->RSV2_CLS_CD = "";
            }

            // 未選択時はnull,残業項目(1)
            $data->OVTM1_CD = $request->Ovtm1Cd;

            // 未選択時はnull,残業項目(2)
            $data->OVTM2_CD = $request->Ovtm2Cd;

            // 未選択時はnull,残業項目(3)
            $data->OVTM3_CD = $request->Ovtm3Cd;

            // 未選択時はnull,残業項目(4)
            $data->OVTM4_CD = $request->Ovtm4Cd;

            // 未選択時はnull,残業項目(5)
            $data->OVTM5_CD = $request->Ovtm5Cd;

            // 未選択時はnull,残業項目(6)
            $data->OVTM6_CD = $request->Ovtm6Cd;

            // 残業項目（n）時間/月の入力値
            // 空白時はnull,残業項目（1）時間/月
            if ($request->Ovtm1Hr == "") {
                $request->Ovtm1Hr = null;
            }
            $data->OVTM1_HR = $request->Ovtm1Hr;

            // 空白時はnull,残業項目（2）時間/月
            if ($request->Ovtm2Hr == "") {
                $request->Ovtm2Hr = null;
            }
            $data->OVTM2_HR = $request->Ovtm2Hr;

            // 空白時はnull,残業項目（3）時間/月
            if ($request->Ovtm3Hr == "") {
                $request->Ovtm3Hr = null;
            }
            $data->OVTM3_HR = $request->Ovtm3Hr;

            // 空白時はnull,残業項目（4）時間/月
            if ($request->Ovtm4Hr == "") {
                $request->Ovtm4Hr = null;
            }
            $data->OVTM4_HR = $request->Ovtm4Hr;

            // 空白時はnull,残業項目（5）時間/月
            if ($request->Ovtm5Hr == "") {
                $request->Ovtm5Hr = null;
            }
            $data->OVTM5_HR = $request->Ovtm5Hr;

            // 空白時はnull,残業項目（6）時間/月
            if ($request->Ovtm6Hr == "") {
                $request->Ovtm6Hr = null;
            }
            $data->OVTM6_HR = $request->Ovtm6Hr;

            // 残業未対応時間
            $data->NO_OVERTM_MI = $request->NoOvertmMi;

            // 日付
            date_default_timezone_set('Asia/Tokyo');
            $data->UPD_DATE = now();

            // 総残業時間上限1
            if ($request->TtlOvtm1Hr == "") {
                $request->TtlOvtm1Hr = 0;
            }
            $data->TTL_OVTM1_HR = $request->TtlOvtm1Hr;

            // 総残業時間上限2
            if ($request->TtlOvtm2Hr == "") {
                $request->TtlOvtm2Hr = 0;
            }
            $data->TTL_OVTM2_HR = $request->TtlOvtm2Hr;

            // 総残業時間上限3
            if ($request->TtlOvtm3Hr == "") {
                $request->TtlOvtm3Hr = 0;
            }
            $data->TTL_OVTM3_HR = $request->TtlOvtm3Hr;

            $data->timestamps = false;
            $data->save();


        // 削除ボタンが押されたとき
        } elseif ($request->has('btnDelete1')) {
            // カレンダーリストボックスで選択されている項目のカレンダーコード
            $deleteCalenderCode = $request->CalendarCdData;
            $deletedata = MT06OvertmLmt::find($deleteCalenderCode);
            // 削除
            if ($deletedata != null) {
                $deletedata->delete();
            }
        }

        return redirect('master/MT06OverTmLmtEditor');
    }
}
