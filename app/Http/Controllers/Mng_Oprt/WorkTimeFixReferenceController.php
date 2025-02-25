<?php

namespace App\Http\Controllers\Mng_Oprt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\MT01ControlRepository;
use App\Repositories\Master\MT10EmpRefRepository;
use App\Repositories\Master\MT11LoginRefRepository;
use App\Repositories\MT13DeptAuthRepository;
use App\Repositories\MT22ClosingDateRepository;
use App\Repositories\MT93PgRepository;
use App\Repositories\TR01WorkRepository;

/**
 * 月次確定状況照会画面
 */
class WorkTimeFixReferenceController extends Controller
{
    private $mt01_control;
    private $mt10_emp;
    private $mt11_login;
    private $mt13_deptauth;
    private $mt22_closing_date;
    private $tr01_work;
    /**
     * コントローラインスタンスの生成
     * @param
     * @return void
     */
    public function __construct(
        MT01ControlRepository $mt01_control_rep,
        MT10EmpRefRepository $mt10_emp_ref_repository,
        MT11LoginRefRepository $mt11_login_ref_rep,
        MT13DeptAuthRepository $mt13_dept_auth_rep,
        MT22ClosingDateRepository $mt22_closing_date_rep,
        MT93PgRepository $pg_repository,
        TR01WorkRepository $tr01_work_rep
    ) {
        $this->mt01_control = $mt01_control_rep;
        $this->mt10_emp = $mt10_emp_ref_repository;
        $this->mt11_login = $mt11_login_ref_rep;
        $this->mt13_deptauth = $mt13_dept_auth_rep;
        $this->mt22_closing_date = $mt22_closing_date_rep;
        $this->tr01_work = $tr01_work_rep;
        parent::__construct($pg_repository, '040009');
    }

    /**
     * 出退勤照会 画面表示
     * @return view
     */
    public function index(Request $request)
    {
        $view_data = $this->createViewData();
        return parent::viewWithMenu('mng_oprt.WorkTimeFixReference', compact('view_data'));
    }

    /**
     * 出退勤照会 データ表示
     * @return view
     */
    public function view(Request $request)
    {
        $search_data = $request->only(['yearMonth', 'closingDateCd', 'noFix']);

        $year = substr($request['yearMonth'], 0, 4);
        $month = (int)substr($request['yearMonth'], 7, 2);
        $closing_date_cd = $request['closingDateCd'];
        $no_fix = !!$request['noFix'];

        // 検索条件チェック
        if (is_nullorwhitespace($year)
                || is_nullorwhitespace($month)
                || is_nullorwhitespace($closing_date_cd)) {
            // 検索条件が設定されていない場合、再表示する
            return redirect('mng_oprt/WorkTimeFixReference');
        }

        // 検索
        $login_emp_dept_cd = $this->mt10_emp->getEmp($this->mt11_login->getEmpCd(session('id')))->DEPT_CD;
        $view_dept = $this->mt13_deptauth->getChangeableDept(session('id'));
        $results = $this->tr01_work->searchFixEmp(
            $login_emp_dept_cd,
            $view_dept,
            $year,
            $month,
            $closing_date_cd,
            $no_fix
        );

        $view_data = $this->createViewData();
        return parent::viewWithMenu('mng_oprt.WorkTimeFixReference', compact('view_data', 'search_data', 'results'));
    }

    /**
     * 画面表示用データを返す
     *
     * @return array
     */
    private function createViewData(): array
    {
        $control = $this->mt01_control->getMt01();
        $today = date('Y-m-d');
        // 月の初期値設定
        // 今月を取得
        $year_and_month = getYearAndMonthWithControl($today, $control->MONTH_CLS_CD, $control->CLOSING_DATE);
        $def_year = $year_and_month['year'];
        // 前月にする
        $def_month = $year_and_month['month'] - 1;
        if ($def_month === 0) {
            $def_year--;
            $def_month = 12;
        }
        $closing_dates = $this->mt22_closing_date->getMt22();
        $def_closing_date_cd = $closing_dates->firstWhere('RSV1_CLS_CD', '01')->CLOSING_DATE_CD;

        return compact('def_year', 'def_month', 'closing_dates', 'def_closing_date_cd');
    }
}
