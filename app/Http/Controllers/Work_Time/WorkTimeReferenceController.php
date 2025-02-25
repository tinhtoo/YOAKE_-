<?php

namespace App\Http\Controllers\Work_Time;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\WorkTimeReferenceRequest;
use App\Repositories\MT01ControlRepository;
use App\Repositories\MT08HolidayRepository;
use App\Repositories\Work_Time\WorkTimeReferenceRepository;
use App\Repositories\MT93PgRepository;

/**
 * 勤務状況照会(個人用)
 */
class WorkTimeReferenceController extends Controller
{

    /**
     * リポジトリの実装
     * @var WorkTimeReferenceRepository
     */
    protected $wtRef_Repository;
    protected $control;
    protected $mt08_holiday;

    /**
     * コントローラインスタンスの生成
     * @param  WorkTimeReferenceRepository  $wtRef_Repository
     * @return void
     */
    public function __construct(
        WorkTimeReferenceRepository $wtRef_Repository,
        MT01ControlRepository $mt01_control_repo,
        MT08HolidayRepository $mt08_holiday_repository,
        MT93PgRepository $pg_repository
    ) {
        parent::__construct($pg_repository, '010002');
        $this->wtRef_Repository = $wtRef_Repository;
        $this->control = $mt01_control_repo;
        $this->mt08_holiday = $mt08_holiday_repository;
    }

    /**
     * 勤務状況照会(個人用) 画面表示
     * @return view
     */
    public function workTimeReference(Request $request)
    {
        $request->session()->put(['ddlDate' => $request->old('ddlDate')]);

        // 年月の初期値設定
        $control = $this->control->getMt01();
        $today = date('Y-m-d');
        $def_ym = getYearAndMonthWithControl($today, $control->MONTH_CLS_CD, $control->CLOSING_DATE);
        $def_ddlDate = $def_ym['year']. "年". sprintf('%02d', $def_ym['month']). "月";
        return parent::viewWithMenu('work_time.WorkTimeReference', compact('def_ddlDate'));
    }

    /**
     * 勤務状況照会(個人用) 画面各機能(データ渡し)処理
     * @return view
     */
    public function searchBtn(WorkTimeReferenceRequest $request)
    {
        $FIX_MSG = '確定済';

        $inputSearchData = $request->all();
        $empWorkTimeResults = $this->wtRef_Repository->empInput($request);
        $workTimeFix = $this->wtRef_Repository->check($request);
        $depEmpName = $this->wtRef_Repository->depEmpName($request);
        $holidays = $this->mt08_holiday->getHolidays()->toArray();

        return parent::viewWithMenu('work_time.WorkTimeReference', compact(
            'FIX_MSG',
            'inputSearchData',
            'empWorkTimeResults',
            'workTimeFix',
            'depEmpName',
            'holidays'
        ));
    }

    /**
     * 勤務状況照会(個人用) 画面機能キャンセル処理
     * @return redirect
     */
    public function cancelBtn(Request $request)
    {
        return redirect()->route('worktimeRef')->withInput($request->only(['ddlDate']));
    }
}
