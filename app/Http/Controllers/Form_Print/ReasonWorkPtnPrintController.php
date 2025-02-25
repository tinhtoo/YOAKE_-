<?php

namespace App\Http\Controllers\Form_Print;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\MT93PgRepository;
use App\Http\Requests\TimeStampPrintRequest;
use App\Filters\TimeStampPrintFilter;
use App\Repositories\MT01ControlRepository;
use App\Repositories\TR01WorkRepository;
use App\Repositories\MT09ReasonRepository;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * 事由／勤怠一覧表 処理
 */
class ReasonWorkPtnPrintController extends Controller
{
    private $mt01_control;
    private $tr01_work;
    private $mt09_reason;

    /**
     * コントローラインスタンスの生成
     * @param
     * @return void
     */
    public function __construct(
        MT93PgRepository $pg_repository,
        MT01ControlRepository $mt01_control_rep,
        TR01WorkRepository $tr01_work_rep,
        MT09ReasonRepository $mt09_reason_rep
    ) {
        parent::__construct($pg_repository, '020004');
        $this->tr01_work = $tr01_work_rep;
        $this->mt01_control = $mt01_control_rep;
        $this->mt09_reason = $mt09_reason_rep;
    }

    /**
     * 事由／勤怠一覧表 処理 画面
     * @return view
     */
    public function index(Request $request)
    {
        return parent::viewWithMenu('form_print.ReasonWorkPtnPrint');
    }

    /**
     * 事由/勤怠一覧表用データ取得
     * @return view
     */
    public function print(TimeStampPrintRequest $request, TimeStampPrintFilter $filter)
    {
        ini_set('memory_limit', '300M');
        ini_set('max_execution_time', 300);
        $input_datas = $request->all();
        $now_date = Carbon::now()->format('Y/m/d'); // 現在の日付
        $reason_names = $this->mt09_reason->reasons(); // 事由名

        $control = $this->mt01_control->getMt01();
        $closing_date = $control->CLOSING_DATE;
        $month_cls_cd = $control->MONTH_CLS_CD;

        // 日付範囲
        if ($input_datas['OutputCls'] == 'rbDateRange') {
            $start_year = substr($input_datas['filter']['startDate'], 0, 4);
            $start_month = substr($input_datas['filter']['startDate'], 7, 2);
            $start_day = substr($input_datas['filter']['startDate'], 12, 2);

            $end_year = substr($input_datas['filter']['endDate'], 0, 4);
            $end_month = substr($input_datas['filter']['endDate'], 7, 2);
            $end_day = substr($input_datas['filter']['endDate'], 12, 2);

            $str_date = $start_year . '-' . $start_month . '-' . $start_day;
            $end_date = $end_year . '-' . $end_month . '-' . $end_day;
        }
        // 月度
        if ($input_datas['OutputCls'] == 'rbMonthCls') {
            $year = substr(($input_datas['filter']['yearMonthDate']), 0, 4);
            $month = abs(substr(($input_datas['filter']['yearMonthDate']), 7, 2));

            $str_date =  getStartDateAtMonth($year, $month, $closing_date, $month_cls_cd);
            $end_date =  getEndDateAtMonth($year, $month, $closing_date, $month_cls_cd);
        }

        // 事由一覧表用情報
        if ($input_datas['ReportCategory'] == 'rbReason') {
            $reason_datas = $this->tr01_work->getReasonReportData($filter, $str_date, $end_date); // 事由一覧表用
            $pdf = PDF::loadView('form_print.templates.ReasonWorkPtnPdf', compact(
                'input_datas',
                'reason_datas',
                'reason_names',
                'now_date',
                'str_date',
                'end_date'
            ));
        }
        // 勤怠一覧表用情報
        if ($input_datas['ReportCategory'] == 'rbWorkPtn') {
            $work_ptn_datas = $this->tr01_work->getWorkPtnReportData($filter, $str_date, $end_date); // 勤怠一覧表用
            $pdf = PDF::loadView('form_print.templates.ReasonWorkPtnPdf', compact(
                'input_datas',
                'work_ptn_datas',
                'reason_names',
                'now_date',
                'str_date',
                'end_date'
            ));
        }

        $pdf->setPaper('A4', 'Landscape');
        return $pdf->stream();
    }
}
