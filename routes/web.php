<?php

use App\Http\Controllers\Master\MT10EmpCnvertController;
use App\Http\Controllers\UserAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Search\MT10EmpSearchController;
use App\Http\Controllers\Search\MT12DeptSearchController;
use App\Http\Controllers\Search\UpDeptSearchController;

use App\Http\Controllers\Work_Time\WorkTimeEditorController;
use App\Http\Controllers\Work_Time\WorkTimeReferenceController;
use App\Http\Controllers\Work_Time\EmpWorkTimeReferenceController;
use App\Http\Controllers\Work_Time\EmpWorkStatusReferenceController;
use App\Http\Controllers\Work_Time\WorkTimeDeptEditorController;

use App\Http\Controllers\Form_Print\WorkPlanPrintController;
use App\Http\Controllers\Form_Print\WorkTimePrintController;
use App\Http\Controllers\Form_Print\TimeStampPrintController;
use App\Http\Controllers\Form_Print\ReasonWorkPtnPrintController;
use App\Http\Controllers\Form_Print\OvertimeAplPrintController;

use App\Http\Controllers\Shift\MT04ShiftPtnReferenceController;
use App\Http\Controllers\Shift\MT04ShiftPtnEditorController;
use App\Http\Controllers\Shift\MonthShiftEditorController;
use App\Http\Controllers\Shift\MonthShiftEmpEditorController;

use App\Http\Controllers\Mng_Oprt\MT03CalendarEditorController;
use App\Http\Controllers\Mng_Oprt\ShiftCalendarCarryOverController;
use App\Http\Controllers\Mng_Oprt\WorkTimeFixController;
use App\Http\Controllers\Mng_Oprt\WorkTimeFixReferenceController;
use App\Http\Controllers\Mng_Oprt\WorkTimeConvertController;
use App\Http\Controllers\Mng_Oprt\WorkTimeClearController;
use App\Http\Controllers\Mng_Oprt\CalendarClearController;
use App\Http\Controllers\Mng_Oprt\WorkTimeExportController;

use App\Http\Controllers\Master\MT01ControlEditorController;
use App\Http\Controllers\Master\MT06OverTmLmtEditorController;
use App\Http\Controllers\Master\MT11PasswordEditorController;
use App\Http\Controllers\Master\MT23CompanyController;
use App\Http\Controllers\Master\MT11LoginRefController;
use App\Http\Controllers\Master\MT10EmpReferenceController;
use App\Http\Controllers\Master\MT10EmpEditorController;
use App\Http\Controllers\Master\MT02CalendarPtnController;
use App\Http\Controllers\Master\MT14PGAuthReferenceController;
use App\Http\Controllers\Master\MT14PGAuthEditorController;
use App\Http\Controllers\Master\MT12DeptEditorController;
use App\Http\Controllers\Master\MT12DeptReferenceController;
use App\Http\Controllers\Master\MT12OrgReferenceController;
use App\Http\Controllers\Master\MT12OrgEditorController;
use App\Http\Controllers\Master\MT13DeptAuthReferenceController;
use App\Http\Controllers\Master\MT13DeptAuthEditorController;
use App\Http\Controllers\Master\MT08HolidayEditorController;
use App\Http\Controllers\Master\MT05WorkPtnReferenceController;
use App\Http\Controllers\Master\MT05WorkPtnEditorController;
use App\Http\Controllers\Master\MT07FractionEditorController;
use App\Http\Controllers\Master\EmpExportController;
use App\Http\Controllers\Master\EmpImportController;
use App\Http\Controllers\Mng_Oprt\PdHolidayCarryOverController;
use App\Http\Controllers\Mng_Oprt\TermInfoConvertController;
use App\Http\Controllers\Mng_Oprt\WorkTimeDlyFixController;
use App\Http\Controllers\Mng_Oprt\WorkTimeFixDlyReferenceController;
use App\Http\Controllers\Search\GetLastCaldController;
use App\Http\Controllers\Shift\ShiftPtnSearchController;
use PhpParser\Node\Expr\PostDec;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Routeを設定
Auth::routes();

Route::middleware('cache.headers:public;max_age=2628000;etag')
->middleware('AlreadyLoggedIn')
->group(function () {
    // ログイン画面表示
    Route::get('/', [UserAuthController::class, 'login']);
    Route::post('check', [UserAuthController::class, 'loginCheck'])->name('auth.check');
});

// メインメニュー画面の表示
Route::middleware('cache.headers:public;max_age=2628000;etag')
->middleware('islogged')
->group(function () {
    Auth::routes();
    Route::get('main', [UserAuthController::class, 'main'])->name('main');
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout.form');

    /***** 勤怠管理 *****/
    Route::prefix('work_time')
    ->group(function () {
        // 出退勤入力画面表示
        Route::get('WorkTimeEditor', [WorkTimeEditorController::class, 'worktimeeditor'])->name('worktimeeditor'); // フォーム起動
        Route::post('WorkTimeEditor', [WorkTimeEditorController::class, 'search'])->name('wte.search'); // 検索
        Route::post('WorkTimeEditorTimeCal/{cd}', [WorkTimeEditorController::class, 'timeCal'])->name('wte.timeCal'); // 時間計算
        Route::post('WorkTimeEditorDayCal/{cd}', [WorkTimeEditorController::class, 'dayCal'])->name('wte.dayCal'); // 日数計算
        Route::post('WorkTimeEditorUpdate', [WorkTimeEditorController::class, 'update'])->name('wte.update'); // 更新
        Route::post('WorkTimeEditorCancel', [WorkTimeEditorController::class, 'cancel'])->name('wte.cancel'); // キャンセル

        // 出退勤入力（部門別）画面表示
        Route::get('WorkTimeDeptEditor', [WorkTimeDeptEditorController::class, 'workTimeDeptEditor'])->name('wtdepteditor');
        Route::post('WorkTimeDeptEditor', [WorkTimeDeptEditorController::class, 'search'])->name('wtde.search');
        Route::post('WorkTimeDeptEditorEdit', [WorkTimeDeptEditorController::class, 'edit'])->name('wtde.edit');
        Route::post('WorkTimeDeptEditorTimeCal/{cd}', [WorkTimeDeptEditorController::class, 'timeCal'])->name('wtde.timeCal'); // 時間計算
        Route::post('WorkTimeDeptEditorDayCal/{cd}', [WorkTimeDeptEditorController::class, 'dayCal'])->name('wtde.dayCal'); // 日数計算
        Route::post('WorkTimeDeptEditorUpdate', [WorkTimeDeptEditorController::class, 'update'])->name('wtde.update');
        Route::post('WorkTimeDeptEditorCancel', [WorkTimeDeptEditorController::class, 'cancel'])->name('wtde.cancel');

        // 出退勤照会　画面表示
        Route::get('EmpWorkStatusReference', [EmpWorkStatusReferenceController::class, 'empWorkStatusReference'])->name('empworkstatusRef');
        Route::post('EmpWorkStatusReference', [EmpWorkStatusReferenceController::class, 'search'])->name('empworkstatusRef.search');
        Route::post('EmpWorkStatusReferenceCancel', [EmpWorkStatusReferenceController::class, 'cancel'])->name('empworkstatusRef.cancel');

        // 勤務状況照会(個人用) 画面表示
        Route::get('WorkTimeReference', [WorkTimeReferenceController::class, 'workTimeReference'])->name('worktimeRef');
        Route::post('WorkTimeReference', [WorkTimeReferenceController::class, 'searchBtn'])->name('worktimeRef.search');
        Route::post('WorkTimeReferenceCancel', [WorkTimeReferenceController::class, 'cancelBtn'])->name('worktimeRef.cancel');

        // 勤務状況照会(管理者用) 画面表示
        Route::get('EmpWorkTimeReference', [EmpWorkTimeReferenceController::class, 'empworktimeRef'])->name('empworktime_ref');
        Route::post('EmpWorkTimeReference', [EmpWorkTimeReferenceController::class, 'search'])->name('ewtr.search');
        Route::post('EmpWorkTimeReferenceCancel', [EmpWorkTimeReferenceController::class, 'cancel'])->name('ewtr.cancel');
    });


    /***** 帳票 *****/
    Route::prefix('form_print')->group(function () {
        // 勤務予定表(週・月別) 画面表示
        Route::get('WorkPlanPrint', [WorkPlanPrintController::class, 'index'])->name('WorkPlanPrint.index');
        Route::post('templates/WorkPlanPdf', [WorkPlanPrintController::class, 'print'])->name('WorkPlanPrint.print'); // 印刷

        // 勤務実績表(日・週・月別) 画面表示
        Route::get('WorkTimePrint', [WorkTimePrintController::class, 'index'])->name('WorkTimePrint.index');
        Route::post('template/WorkTimePdf', [WorkTimePrintController::class, 'print'])->name('WorkTimePrint.Print');

        // 未打刻／二重打刻一覧表  画面表示
        Route::get('TimeStampPrint', [TimeStampPrintController::class, 'index'])->name('TimeStampPrint.index');
        Route::post('templates/TimeStampPdf', [TimeStampPrintController::class, 'print'])->name('TimeStampPrint.print'); // 印刷

        // 事由／勤怠一覧表 画面表示
        Route::get('ReasonWorkPtnPrint', [ReasonWorkPtnPrintController::class, 'index'])->name('ReasonWorkPtnPrint.index');
        Route::post('templates/ReasonWorkPtnPdf', [ReasonWorkPtnPrintController::class, 'print'])->name('ReasonWorkPtnPrint.print'); // 印刷

        // 残業申請書 画面表示
        Route::get('OvertimeAplPrint', [OvertimeAplPrintController::class, 'index'])->name('OvertimeAplPrint.index');
        Route::post('templates/OvertimeAplPdf', [OvertimeAplPrintController::class, 'print'])->name('OvertimeAplPdf.print'); // 印刷
    });


    /***** シフト管理 *****/
    Route::prefix('shift')->group(function () {
        // シフトパターン情報入力 画面表示
        Route::get('MT04ShiftPtnReference', [MT04ShiftPtnReferenceController::class, 'index'])->name('MT04ShiftPtnReference.index');

        // シフトパターン情報入力(新規シフトパターン登録) 画面表示
        Route::get('MT04ShiftPtnEditorAddNew', [MT04ShiftPtnEditorController::class, 'index'])->name('MT04ShiftPtnEditor.add');
        Route::get('MT04ShiftPtnEditor/{id}', [MT04ShiftPtnEditorController::class, 'index'])->name('MT04ShiftPtnEditor.index');
        Route::post('MT04ShiftPtnUpdate', [MT04ShiftPtnEditorController::class, 'update'])->name('MT04ShiftPtnEditor.update'); // 更新
        Route::post('MT04ShiftPtnDelete', [MT04ShiftPtnEditorController::class, 'delete'])->name('MT04ShiftPtnEditor.delete'); // 削除

        // 月別シフト入力  画面表示
        Route::get('MonthShiftEditor', [MonthShiftEditorController::class, 'index'])->name('MonthShiftEditor.index');
        Route::post('MonthShiftEditor', [MonthShiftEditorController::class, 'view'])->name('MonthShiftEditor.view');
        Route::post('MonthShiftUpdate', [MonthShiftEditorController::class, 'update'])->name('MonthShiftEditor.update');

        // シフトパターン検索
        Route::get('ShiftPtnSearch', [ShiftPtnSearchController::class, 'index'])->name('ShiftPtnSearch.index');
        Route::post('ShiftPtnSearch', [ShiftPtnSearchController::class, 'search'])->name('ShiftPtnSearch.search');

        // 社員別月別シフト入力
        Route::get('MonthShiftEmpEditor', [MonthShiftEmpEditorController::class, 'index'])->name('MonthShiftEmpEditor.index');
        Route::post('MonthShiftEmpEditor', [MonthShiftEmpEditorController::class, 'view'])->name('MonthShiftEmpEditor.view');
        Route::post('MonthShiftEmpUpdate', [MonthShiftEmpEditorController::class, 'update'])->name('MonthShiftEmpEditor.update');
    });


    /***** 管理業務 *****/
    Route::prefix('mng_oprt')->group(function () {
        // カレンダー情報入力画面表示
        Route::get('MT03CalendarEditor', [MT03CalendarEditorController::class, 'index'])->name('MT03CalendarEditor.index');
        Route::post('MT03CalendarEditor', [MT03CalendarEditorController::class, 'view'])->name('MT03CalendarEditor.view');
        Route::post('MT03CalendarUpdate', [MT03CalendarEditorController::class, 'update'])->name('MT03CalendarEditor.update');
        Route::post('MT03CalendarDelete', [MT03CalendarEditorController::class, 'delete'])->name('MT03CalendarEditor.delete');

        // シフト月次更新処理画面表示
        Route::get('ShiftCalendarCarryOver', [ShiftCalendarCarryOverController::class, 'index'])->name('ShiftCalendarCarryOver.index');
        Route::post('ShiftCalendarCarryOver', [ShiftCalendarCarryOverController::class, 'update'])->name('ShiftCalendarCarryOver.update');

        // 月次確定処理画面表示
        Route::get('WorkTimeFix', [WorkTimeFixController::class, 'index'])->name('WorkTimeFix.index');
        Route::post('WorkTimeFix', [WorkTimeFixController::class, 'update'])->name('WorkTimeFix.update');

        // 月次確定状況照会画面
        Route::get('WorkTimeFixReference', [WorkTimeFixReferenceController::class, 'index'])->name('WorkTimeFixReference.index');
        Route::post('WorkTimeFixReference', [WorkTimeFixReferenceController::class, 'view'])->name('WorkTimeFixReference.view');

        // 最新打刻情報取得処理画面
        Route::get('WorkTimeConvert', [WorkTimeConvertController::class, 'index'])->name('WorkTimeConvert.index');
        Route::post('WorkTimeConvert', [WorkTimeConvertController::class, 'search'])->name('WorkTimeConvert.search');

        // 凍結（開発対象外）
        // // 端末情報更新処理画面
        // Route::get('TermInfoConvert', [TermInfoConvertController::class, 'index'])->name('TermInfoConvert.index');
        // Route::post('TermInfoConvert', [TermInfoConvertController::class, 'update'])->name('TermInfoConvert.update');

        // 出退勤情報クリア処理画面表示
        Route::get('WorkTimeClear', [WorkTimeClearController::class, 'index'])->name('WorkTimeClear.index');
        Route::post('WorkTimeClear', [WorkTimeClearController::class, 'clear'])->name('WorkTimeClear.clear');

        // カレンダー情報クリア処理画面表示
        Route::get('CalendarClear', [CalendarClearController::class, 'index'])->name('CalendarClear.index');
        Route::post('CalendarClear', [CalendarClearController::class, 'clear'])->name('CalendarClear.clear');

        // 凍結（開発対象外）
        // // 有休情報更新処理
        // Route::get('PdHolidayCarryOver', [PdHolidayCarryOverController::class, 'index'])->name('PdHolidayCarryOver.index');

        // 凍結（開発対象外）
        // // 日次確定処理
        // Route::get('WorkTimeDlyFix', [WorkTimeDlyFixController::class, 'index'])->name('WorkTimeDlyFix.index');

        // 凍結（開発対象外）
        // // 日次確定状況照会
        // Route::get('WorkTimeFixDlyReference', [WorkTimeFixDlyReferenceController::class, 'index'])->name('WorkTimeFixDlyReference.index');

        // 勤務実績情報出力画面
        Route::get('WorkTimeExport', [WorkTimeExportController::class, 'index'])->name('WorkTimeExport.index');
        Route::post('WorkTimeExport', [WorkTimeExportController::class, 'export'])->name('WorkTimeExport.export');
    });


    /***** マスタ *****/
    Route::prefix('master')->group(function () {
        // 社員情報照会入力
        Route::get('MT10EmpReference', [MT10EmpReferenceController::class, 'search'])->name('MT10EmpRef.search'); // 照会画面表示、検索
        Route::get('MT10EmpEditor', [MT10EmpEditorController::class, 'edit'])->name('MT10EmpEdit.edit'); // 登録画面表示
        Route::get('MT10EmpEditor/{id}', [MT10EmpEditorController::class, 'edit']); // 編集画面表示
        Route::post('MT10EmpUpdate', [MT10EmpEditorController::class, 'update'])->name('MT10EmpEdit.update'); // 更新
        Route::post('MT10EmpDelete', [MT10EmpEditorController::class, 'delete'])->name('MT10EmpEdit.delete'); // 削除

        // ログイン情報入力
        Route::get('MT11LoginReference', [MT11LoginRefController::class, 'search'])->name('MT11LoginRef.search');
        Route::get('MT11LoginEditor/{id}', [MT11LoginRefController::class, 'edit'])->name('MT11LoginEdit.edit');
        Route::post('MT11LoginEditor/{id}', [MT11LoginRefController::class, 'update'])->name('MT11LoginEdit.update');

        // パスワード変更入力
        Route::get('MT11PasswordEditor', [MT11PasswordEditorController::class, 'index']);
        Route::post('MT11PasswordEditor', [MT11PasswordEditorController::class, 'update'])->name('MT11Pass.update');

        // 機能権限情報照会
        Route::get('MT14PGAuthReference', [MT14PGAuthReferenceController::class, 'index']);

        // 機能権限情報入力
        Route::get('MT14PGAuthEditor', [MT14PGAuthEditorController::class, 'edit'])->name('MT14pgAuth.edit');
        Route::post('MT14PgAuthUpdate', [MT14PGAuthEditorController::class, 'insert'])->name('MT14pgAuth.insert'); // 更新ボタン
        Route::get('MT14PGAuthEditor/{id}', [MT14PGAuthEditorController::class, 'edit'])->name('MT14pgAuth.update'); // 権限情報編集画面
        Route::post('MT14PgAuthDelete', [MT14PGAuthEditorController::class, 'delete'])->name('MT14pgAuth.delete'); // 削除

        // 部門情報照会
        Route::get('MT12DeptReference', [MT12DeptReferenceController::class,'index']);

        // 部門情報入力
        Route::get('MT12DeptEditor/{id}', [MT12DeptEditorController::class, 'edit']); // 部門情報編集画面
        Route::post('MT12DeptUpdate', [MT12DeptEditorController::class, 'upsert']); // 更新
        Route::post('MT12DeptDelete', [MT12DeptEditorController::class, 'delete']); // 削除
        Route::post('MT12DeptDelRow', [MT12DeptEditorController::class, 'delRow']); // 行削除

        // 組織変更照会
        Route::get('MT12OrgReference', [MT12OrgReferenceController::class,'index']);

        // 組織変更入力
        Route::get('MT12OrgEditor/{id}', [MT12OrgEditorController::class, 'edit']); // 部門情報編集画面
        Route::post('MT12OrgUpdate', [MT12OrgEditorController::class,'update']); // 更新

        // 部門権限情報照会
        Route::get('MT13DeptAuthReference', [MT13DeptAuthReferenceController::class, 'index'])->name('MT13DeptAuthReference.index');

        // 部門権限情報入力
        Route::get('MT13DeptAuthEditor', [MT13DeptAuthEditorController::class, 'index'])->name('MT13DeptAuthEditor.add'); // 新規
        Route::get('MT13DeptAuthEditor/{id}', [MT13DeptAuthEditorController::class, 'index'])->name('MT13DeptAuthEditor.index'); // 部門権限情報入力編集画面
        Route::post('MT13DeptAuthUpsert', [MT13DeptAuthEditorController::class, 'upsert'])->name('MT13DeptAuthEditor.upsert'); // 更新ボタン
        Route::post('MT13DeptAuthDelete', [MT13DeptAuthEditorController::class, 'delete'])->name('MT13DeptAuthEditor.delete'); // 削除ボタン

        // 祝祭日・会社休日情報入力
        Route::get('MT08HolidayEditor', [MT08HolidayEditorController::class, 'index'])->name('MT08HolidayEditor.index'); // 祝祭日・会社休日情報入力画面
        Route::post('MT08HolidayUpdate', [MT08HolidayEditorController::class, 'update'])->name('MT08HolidayEditor.update`'); // 更新

        // 勤務体系情報照会
        Route::get('MT05WorkPtnReference', [MT05WorkPtnReferenceController::class, 'index'])->name('MT05WorkPtnReference.index'); // 勤務体系情報照会画面

        // 勤務体系情報入力
        Route::get('MT05WorkPtnEditor', [MT05WorkPtnEditorController::class, 'index'])->name('MT05WorkPtnEditor');
        Route::get('MT05WorkPtnEditor/{id}', [MT05WorkPtnEditorController::class, 'index'])->name('MT05WorkPtnEditor.index'); // 勤務体系情報入力画面
        Route::get('MT05WorkPtnEditorAddNew', [MT05WorkPtnEditorController::class, 'index'])->name('MT05WorkPtnEditor.add'); // 新規
        Route::post('MT05WorkPtnUpdate', [MT05WorkPtnEditorController::class, 'update'])->name('MT05WorkPtnEditor.update'); // 更新ボタン
        Route::post('MT05WorkPtnDelete', [MT05WorkPtnEditorController::class, 'delete'])->name('MT05WorkPtnEditor.delete'); // 削除ボタン

        // 端数処理情報入力
        Route::get('MT07FractionEditor', [MT07FractionEditorController::class, 'index'])->name('MT07FractionEditor.index');
        Route::post('MT07FractionEditor', [MT07FractionEditorController::class, 'view'])->name('MT07FractionEditor.view');
        Route::post('MT07FractionUpdate', [MT07FractionEditorController::class, 'update'])->name('MT07FractionEditor.update'); // 更新
        Route::post('MT07FractionDelete', [MT07FractionEditorController::class, 'delete'])->name('MT07FractionEditor.delete'); // 削除

        // カレンダーパターン情報照会
        Route::get('MT02CalendarPtnReference', [MT02CalendarPtnController::class, 'search'])->name('MT02.search');

        // カレンダーパターン情報入力(更新・修正・削除)
        Route::get('MT02CalendarPtnEditor/{id}', [MT02CalendarPtnController::class, 'edit'])->name('MT02.edit');
        Route::post('MT02CalendarPtnEditor/{id}', [MT02CalendarPtnController::class, 'update'])->name('MT02.update');
        Route::post('MT02CalendarPtnDelete', [MT02CalendarPtnController::class, 'delete'])->name('MT02.delete');

        // 新規カレンダーパターン情報登録
        Route::get('MT02CalendarPtnEditor', [MT02CalendarPtnController::class, 'storeIndex'])->name('MT02.storeIndex');
        Route::post('MT02CalendarPtnEditor', [MT02CalendarPtnController::class, 'store'])->name('MT02.store');

        // 残業上限情報入力
        Route::get('MT06OverTmLmtEditor', [MT06OverTmLmtEditorController::class, 'index'])->name('MT06.index');
        Route::post('MT06OverTmLmtEditor', [MT06OverTmLmtEditorController::class,  'update'])->name('MT06.update');

        // 基本情報入力
        Route::get('MT01ControlEditor', [MT01ControlEditorController::class, 'index'])->name('MT01.index');
        Route::post('MT01ControlEditor', [MT01ControlEditorController::class, 'update'])->name('MT01.update');

        // 社員番号一括変換
        Route::get('MT10EmpCnvert', [MT10EmpCnvertController::class, 'index'])->name('MT10EmpCnvert.index');
        Route::post('MT10EmpCnvert/', [MT10EmpCnvertController::class, 'update'])->name('MT10EmpCnvert.update');

        // 社員情報書出処理
        Route::get('EmpExport', [EmpExportController::class, 'index'])->name('EmpExport.index'); // 画面
        Route::post('EmpExport', [EmpExportController::class, 'export'])->name('EmpExport.export'); // 出力

        // 社員情報取込処理
        Route::get('EmpImport', [EmpImportController::class, 'index'])->name('EmpImport.index'); // 画面表示
        Route::post('EmpImport', [EmpImportController::class, 'import'])->name('EmpImport.import'); // 取り込み
        Route::post('EmpImportUpdate', [EmpImportController::class, 'update'])->name('EmpImport.update'); // 更新

        // 所属情報
        Route::get('MT23CompanyReference', [MT23CompanyController::class, 'MT23CompanyReferenceIndex'])->name('MT23.index');
        Route::get('MT23CompanyRegisterIndex', [MT23CompanyController::class, 'registerIndex'])->name('MT23.registerIndex'); // 登録画面表示
        Route::post('MT23CompanyRegister', [MT23CompanyController::class, 'companyRegister'])->name('MT23.companyRegister'); // 登録
        Route::get('MT23CompanyEditor/{id}', [MT23CompanyController::class, 'edit'])->name('MT23.edit'); // 更新画面表示
        Route::post('MT23CompanyUpdate/{id}', [MT23CompanyController::class, 'update'])->name('MT23.update'); // 更新
        Route::post('MT23CompanyEditor/{id}', [MT23CompanyController::class, 'cancel'])->name('MT23.cancel'); // キャンセル
        Route::post('MT23CompanyDelete/{id}', [MT23CompanyController::class, 'delete'])->name('MT23.delete'); // 削除
    });


    /***** sub-画面 *****/
    Route::prefix('search')->group(function () {
        // 部門情報検索（MT12DeptSearch）
        Route::get('MT12DeptSearch', [MT12DeptSearchController::class, 'search'])->name('dep.search');
        Route::get('MT12DeptSearch/{cd}', [MT12DeptSearchController::class, 'getName'])->name('dep.getName');

        // 社員情報検索（MT10EmpSearch）
        Route::get('MT10EmpSearch', [MT10EmpSearchController::class, 'search'])->name('emp.search');
        Route::get('MT10EmpSearch/{cd}', [MT10EmpSearchController::class, 'getName'])->name('emp.getName');

        // 組織変更入力 (UpDeptSearch)
        Route::get('UpDeptSearch/{deptCd}', [UpDeptSearchController::class, 'search'])->name('updept.search');

        // 最新カレンダー年月取得
        Route::get('GetLastCald/{empCd}', [GetLastCaldController::class, 'get'])->name('lastCald.get');
    });
});
