<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Repositories\Master\MT02CalendarPtnRepository;
use App\Http\Requests\MT02CalendarPtnRequest;
use App\Http\Requests\MT02CalendarPtnUpdRequest;
use App\Http\Requests\MT02CalendarPtnDelRequest;
use App\Models\MT02CalendarPtn;
use Illuminate\Http\Request;
use App\Repositories\MT93PgRepository;

class MT02CalendarPtnController extends Controller
{
    /**
     * Work_Timeリポジトリの実装
     *
     * @var MT02CalendarPtnRepository
     */

    protected $CalendarPtn_repository;

    /**
     * 新しいコントローラインスタンスの生成
     *
     * @param  UserRepository  $CalendarPtn_repository
     * @return void
     */
    public function __construct(
        MT02CalendarPtnRepository $CalendarPtn_repository,
        MT93PgRepository $pg_repository
    ) {
        parent::__construct($pg_repository, '000008');
        $this->CalendarPtn_repository = $CalendarPtn_repository;
    }
    /**
     * 新規カレンダーパターン登録画面
     *
     * @return void
     */
    public function storeIndex(Request $request)
    {
        $request_data = $request->all();
        $workptns = $this->CalendarPtn_repository->workPtn();
        return parent::viewWithMenu('master.MT02CalendarPtnEditor', compact('request_data', 'workptns'));
    }

    /**
     * 新規カレンダーパターン登録
     * MT02CalendarPtnEditor(カレンダーパターン情報入力「新規カレンダーパターン登録」)
     * @param  int  $request
     * @return Response
     */

    public function search(Request $request)
    {
        $calendar_ptns = $this->CalendarPtn_repository->calendarPtns();
        return parent::viewWithMenu('master.MT02CalendarPtnReference', compact('calendar_ptns'));
    }

    public function store(MT02CalendarPtnRequest $request)
    {
        $newPtnStore = MT02CalendarPtn::find($request->CalendarCd);

        if (!isset($newPtnStore)) {
            $newPtnStore = new MT02CalendarPtn;
            $newPtnStore->CALENDAR_CD = $request->CalendarCd;
            $newPtnStore->CALENDAR_NAME = $request->CalendarPtnName;
            if ($request->WorkPtn === '00') {
                $request->WorkPtn = '00';
                $newPtnStore->CALENDAR_CLS_CD = $request->WorkPtn;
                $newPtnStore->MON_WORKPTN_CD = $request->ddlMonWorkPtn;
                $newPtnStore->TUE_WORKPTN_CD = $request->ddlTueWorkPtn;
                $newPtnStore->WED_WORKPTN_CD = $request->ddlWedWorkPtn;
                $newPtnStore->THU_WORKPTN_CD = $request->ddlThuWorkPtn;
                $newPtnStore->FRI_WORKPTN_CD = $request->ddlFriWorkPtn;
                $newPtnStore->SAT_WORKPTN_CD = $request->ddlSatWorkPtn;
                $newPtnStore->SUN_WORKPTN_CD = $request->ddlSunWorkPtn;
                $newPtnStore->HLD_WORKPTN_CD = $request->ddlHldWorkPtn;
            } else {
                $request->WorkPtn = '01';
                $newPtnStore->CALENDAR_CLS_CD = $request->WorkPtn;
                $newPtnStore->MON_WORKPTN_CD = '';
                $newPtnStore->TUE_WORKPTN_CD = '';
                $newPtnStore->WED_WORKPTN_CD = '';
                $newPtnStore->THU_WORKPTN_CD = '';
                $newPtnStore->FRI_WORKPTN_CD = '';
                $newPtnStore->SAT_WORKPTN_CD = '';
                $newPtnStore->SUN_WORKPTN_CD = '';
                $newPtnStore->HLD_WORKPTN_CD = '';
            }
            $newPtnStore->RSV1_CLS_CD = '';
            $newPtnStore->RSV2_CLS_CD = '';
            // 登録日付（TimeZone->Asia/Tokyo)
            date_default_timezone_set('Asia/Tokyo');
            $newPtnStore->UPD_DATE = now();
            $newPtnStore->timestamps = false;
            $newPtnStore->save();
        }

        return redirect()->route('MT02.storeIndex');
    }
    /**
     * カレンダーパターン修正
     * MT02CalendarPtnEditor(カレンダーパターン情報入力「更新・修正用」)
     * @param [type] $id
     * @return void
     */
    public function edit(Request $request, $id)
    {
        $request_data = $request->all();
        $workptns = $this->CalendarPtn_repository->workPtn();
        $MT02calendarPtnEdit = $this->CalendarPtn_repository->calendarPtnsEdit($id);

        return parent::viewWithMenu('master.MT02CalendarPtnEditor', compact(
            'request_data',
            'workptns',
            'MT02calendarPtnEdit'
        ));
    }

    /**
     * カレンダーパターン更新・修正
     * MT02CalendarPtnEditor(カレンダーパターン情報入力「更新・修正用」)
     * @return void
     */
    public function update(MT02CalendarPtnUpdRequest $request)
    {
        $request_data = $request->all();
        $update_info = MT02CalendarPtn::find($request_data['CalendarCd']);
        if (isset($update_info['CALENDAR_CD'])) {
            $update_info->CALENDAR_CD = $request_data['CalendarCd'];
            $update_info->CALENDAR_NAME = $request_data['CalendarPtnName'];
            if ($request->WorkPtn === '00') {
                $request->WorkPtn = '00';
                $update_info->CALENDAR_CLS_CD = $request->WorkPtn;
                $update_info->MON_WORKPTN_CD = $request_data['ddlMonWorkPtn'];
                $update_info->TUE_WORKPTN_CD = $request_data['ddlTueWorkPtn'];
                $update_info->WED_WORKPTN_CD = $request_data['ddlWedWorkPtn'];
                $update_info->THU_WORKPTN_CD = $request_data['ddlThuWorkPtn'];
                $update_info->FRI_WORKPTN_CD = $request_data['ddlFriWorkPtn'];
                $update_info->SAT_WORKPTN_CD = $request_data['ddlSatWorkPtn'];
                $update_info->SUN_WORKPTN_CD = $request_data['ddlSunWorkPtn'];
                $update_info->HLD_WORKPTN_CD = $request_data['ddlHldWorkPtn'];
            } else {
                $request->WorkPtn = '01';
                $update_info->CALENDAR_CLS_CD = $request->WorkPtn;
                $update_info->MON_WORKPTN_CD = '';
                $update_info->TUE_WORKPTN_CD = '';
                $update_info->WED_WORKPTN_CD = '';
                $update_info->THU_WORKPTN_CD = '';
                $update_info->FRI_WORKPTN_CD = '';
                $update_info->SAT_WORKPTN_CD = '';
                $update_info->SUN_WORKPTN_CD = '';
                $update_info->HLD_WORKPTN_CD = '';
            }
            $update_info->RSV1_CLS_CD = '';
            $update_info->RSV2_CLS_CD = '';
            // 登録日付（TimeZone->Asia/Tokyo)
            date_default_timezone_set('Asia/Tokyo');
            $update_info->UPD_DATE = now();
            $update_info->timestamps = false;
            $update_info->save();
        }
        return redirect()->route('MT02.search');
    }

    /**
     * カレンダーパターン削除
     * MT02CalendarPtnEditor(カレンダーパターン情報入力「削除用」)
     * @return void
     */
    public function delete(MT02CalendarPtnDelRequest $request)
    {
        $request_data = $request->all();
        $update_info = MT02CalendarPtn::find($request_data['CalendarCd']);
        $update_info->delete();
        return redirect()->route('MT02.search');
    }
}
