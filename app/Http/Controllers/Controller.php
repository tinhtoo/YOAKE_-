<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Repositories\MT93PgRepository;

define('ALL_MENU', [
    'main' => ['mcls' => '', 'pg' => '', 'default' => ''],

    '000001' => ['mcls' => '00', 'pg' => '000001'], // master.MT10EmpEditor
    '000002' => ['mcls' => '00', 'pg' => '000002'], // master.MT11LoginEditor
    '000003' => ['mcls' => '00', 'pg' => '000003'], // master.MT14PGAuthEditor
    '000004' => ['mcls' => '00', 'pg' => '000004'], // master.MT12DeptEditor
    '000005' => ['mcls' => '00', 'pg' => '000005'], // master.MT13DeptAuthEditor
    '000006' => ['mcls' => '00', 'pg' => '000006'], // master.MT05WorkPtnEditor
    '000007' => ['mcls' => '00', 'pg' => '000007'], // master.MT07FractionEditor
    '000008' => ['mcls' => '00', 'pg' => '000008'], // master.MT02CalendarPtn
    '000010' => ['mcls' => '00', 'pg' => '000010'], // master.MT06OverTmLmtEditor
    '000011' => ['mcls' => '00', 'pg' => '000011'], // master.MT01Control
    '000012' => ['mcls' => '00', 'pg' => '000012'], // master.MT08HolidayEditor
    '000014' => ['mcls' => '00', 'pg' => '000014'], // master.MT11PasswordEditor
    '000018' => ['mcls' => '00', 'pg' => '000018'], // master.MT12OrgEditor
    '000019' => ['mcls' => '00', 'pg' => '000019'], // master.MT10EmpCnvert
    '000020' => ['mcls' => '00', 'pg' => '000020'], // master.EmpExport
    '000021' => ['mcls' => '00', 'pg' => '000021'], // master.EmpImport
    '000022' => ['mcls' => '00', 'pg' => '000022'], // master.MT23CompanyEditor

    '010001' => ['mcls' => '01', 'pg' => '010001'], // work_time.WorkTimeEditor
    '010002' => ['mcls' => '01', 'pg' => '010002'], // work_time.WorkTimeReference
    '010003' => ['mcls' => '01', 'pg' => '010003'], // work_time.EmpWorkTimeReference
    '010004' => ['mcls' => '01', 'pg' => '010004'], // work_time.EmpWorkStatusReference
    '010005' => ['mcls' => '01', 'pg' => '010005'], // work_time.WorkTimeDeptEditor

    '020001' => ['mcls' => '02', 'pg' => '020001'], // form_print.WorkPlanPrint
    '020002' => ['mcls' => '02', 'pg' => '020002'], // form_print.WorkTimePrint
    '020003' => ['mcls' => '02', 'pg' => '020003'], // form_print.TimeStampPrint
    '020004' => ['mcls' => '02', 'pg' => '020004'], // form_print.ReasonWorkPtnPrint
    '020005' => ['mcls' => '02', 'pg' => '020005'], // form_print.OvertimeAplPrint

    '030001' => ['mcls' => '03', 'pg' => '030001'], // shift.MT04ShiftPtn
    '030003' => ['mcls' => '03', 'pg' => '030003'], // shift.MonthShiftEditor
    '030004' => ['mcls' => '03', 'pg' => '030004'], // shift.MonthShiftEmpEditor

    '040001' => ['mcls' => '04', 'pg' => '040001'], // mng_oprt.MT03CalendarEditor
    '040002' => ['mcls' => '04', 'pg' => '040002'], // mng_oprt.ShiftCalendarCarryOver
    '040003' => ['mcls' => '04', 'pg' => '040003'], // mng_oprt.WorkTimeConvert
    '040004' => ['mcls' => '04', 'pg' => '040004'], // mng_oprt.PdHolidayCarryOver
    '040005' => ['mcls' => '04', 'pg' => '040005'], // mng_oprt.TermInfoConvert
    '040006' => ['mcls' => '04', 'pg' => '040006'], // mng_oprt.WorkTimeFix
    '040007' => ['mcls' => '04', 'pg' => '040007'], // mng_oprt.WorkTimeClear
    '040009' => ['mcls' => '04', 'pg' => '040009'], // mng_oprt.WorkTimeFixReference
    '040011' => ['mcls' => '04', 'pg' => '040011'], // mng_oprt.CalendarClear
    '040012' => ['mcls' => '04', 'pg' => '040012'], // mng_oprt.WorkTimeDlyFix
    '040013' => ['mcls' => '04', 'pg' => '040013'], // mng_oprt.WorkTimeFixDlyReference
    '042000' => ['mcls' => '04', 'pg' => '042000'], // mng_oprt.WorkTimeExport
]);

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $pg_repository = null;
    private $pg_cd = null;
    private $menu_list = null;

    /**
     * コンストラクタ
     * リポジトリのインスタンスを生成、格納
     *
     * @param  UserRepository  $EmpRef_repository
     * @return void
     */
    public function __construct(MT93PgRepository $pg_repository, $pg_cd)
    {
        $this->pg_repository = $pg_repository;
        $this->pg_cd = $pg_cd;

        // 画面存在チェック
        if (!key_exists($pg_cd, ALL_MENU)) {
            abort(404, 'Not Found');
        }

        $this->middleware(function ($request, $next) use ($pg_cd) {
            // セッションID取得のため、ミドルウェアを使用
            $this->menu_list = $this->pg_repository->getMenu(session('id'));
            if (!key_exists('default', ALL_MENU[$pg_cd])
                && (!key_exists(ALL_MENU[$pg_cd]['mcls'], $this->menu_list)
                    || !key_exists(ALL_MENU[$pg_cd]['pg'], $this->menu_list[ALL_MENU[$pg_cd]['mcls']]))) {
                $data = [];
                $data['menu_list'] = $this->menu_list;
                $data['this_pg_cd'] = ALL_MENU[$this->pg_cd];
                return response(view('auth.forbidden', $data));
            }
            return $next($request);
        });
    }

    /**
     * メニュー付き画面の表示
     * メニュー表示情報を画面に返す情報に追加する
     *
     * @param [type] $view
     * @param array $data
     * @return void
     */
    protected function viewWithMenu($view, $data = [])
    {
        $data['menu_list'] = $this->menu_list;
        $data['this_pg_cd'] = ALL_MENU[$this->pg_cd];
        return view($view, $data);
    }

    /**
     * CSV出力
     * response()->streamDownload()の第１引数に指定すると、
     * 引数に指定されたデータをCSVでダウンロードさせる。
     *
     * なお、データ取得クエリはget()やall()ではなくcursor()を指定すること。
     *
     * @param array $header　CSVのヘッダー
     * @param [type] $output_data　CSVのデータ
     * @return void
     */
    protected function outputCsv($header, $output_data)
    {
        return function () use ($header, $output_data) {
            $stream = fopen('php://output', 'w');
            // 文字コードはJIS
            stream_filter_prepend($stream, 'convert.iconv.utf-8/cp932//TRANSLIT');

            fputcsv($stream, $header);
            foreach ($output_data as $record) {
                if (get_class($record) === 'stdClass') {
                    // From句にサブクエリを指定している場合、
                    // toArrayが使えないため json_decode で連想配列に変換する。
                    fputcsv($stream, json_decode(json_encode($record), true));
                } else {
                    fputcsv($stream, $record->toArray());
                }
            }
            fclose($stream);
        };
    }
}
