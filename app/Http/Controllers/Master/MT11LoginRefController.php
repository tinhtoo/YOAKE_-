<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Repositories\Master\MT11LoginRefRepository;
use App\Filters\MT11LoginRefFilter;
use App\Http\Requests\MT11LoginRefRequest;
use App\Http\Requests\MT11LoginEditRequest;
use App\Models\MT10Emp;
use App\Models\MT11Login;
use App\Repositories\MT93PgRepository;
use Illuminate\Support\Facades\DB;

class MT11LoginRefController extends Controller
{
    /**
     * Work_Timeリポジトリの実装
     *
     * @var MT11LoginRefRepository
     */

    protected $LoginRef_repository;

    /**
     * 新しいコントローラインスタンスの生成
     *
     * @param  UserRepository  $LoginRef_repository
     * @return void
     */
    public function __construct(
        MT11LoginRefRepository $LoginRef_repository,
        MT93PgRepository $pg_repository
    ) {
        parent::__construct($pg_repository, '000002');
        $this->LoginRef_repository = $LoginRef_repository;
    }
    /**
     * 指定ユーザーのプロファイル表示
     *
     * @param $request
     * @return Response
     */

    public function search(MT11LoginRefRequest $request, MT11LoginRefFilter $filter)
    {
        $request_data = $request->all();
        $search_data = $request->input(['filter']);
        $dept_name = $request->input('deptName');
        $search_results = $this->LoginRef_repository->search($filter);
        $Emp = $request->input('filter.txtEmpCd');

        return parent::viewWithMenu('master.MT11LoginReference', compact(
            'request_data',
            'search_data',
            'search_results'
        ));
    }

    public function edit($id)
    {
        $emp_data = $this->LoginRef_repository->edit($id);
        $login_datas = $this->LoginRef_repository->user($id);
        $pg_auth = $this->LoginRef_repository->pgauth();
        return parent::viewWithMenu('master.MT11LoginEditor', compact('emp_data', 'login_datas', 'pg_auth'));
    }

    public function update(MT11LoginEditRequest $request, MT11LoginRefFilter $filter)
    {
        $request_data = $request->all();
        $search_results = $this->LoginRef_repository->search($filter);
        try {
            DB::beginTransaction();
            $update_info = MT11Login::find($request_data['txtEmpCd']);
            $update_info2 = MT10Emp::find($request_data['txtEmpCd']);
            if ($update_info == null) {
                $update_info = new MT11Login;
                $update_info->EMP_CD = $request_data['txtEmpCd'];
                $update_info->LOGIN_ID = $request_data['txtLoginId'];
                $update_info->PASSWORD = $request_data['txtNewPassword'];
                // 登録日付（TimeZone->Asia/Tokyo)
                date_default_timezone_set('Asia/Tokyo');
                $update_info->UPD_DATE = now();
                $update_info->timestamps = false;
                $update_info->save();

                $update_info2->PG_AUTH_CD = $request_data['ddlPgAuth'];
                $update_info2->UPD_DATE = now();
                $update_info2->timestamps = false;
                $update_info2->save();
            }

            if (!empty($update_info)) {
                $update_info->LOGIN_ID = $request_data['txtLoginId'];
                $update_info->PASSWORD = $request_data['txtNewPassword'];
                // 登録日付（TimeZone->Asia/Tokyo)
                date_default_timezone_set('Asia/Tokyo');
                $update_info->UPD_DATE = now();
                $update_info->timestamps = false;
                $update_info->save();

                // if($update_info == $update_info2){
                $update_info2->PG_AUTH_CD = $request_data['ddlPgAuth'];
                $update_info2->UPD_DATE = now();
                $update_info2->timestamps = false;
                $update_info2->save();
            }
            DB::commit();
        } catch (\Throwable $e) {
            \Log::debug($e);
            DB::rollBack();
        }
        return redirect('master/MT11LoginReference');
    }
}
