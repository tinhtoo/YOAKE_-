<?php

namespace App\Http\Controllers\Master;

use App\Repositories\Master\MT14AuthRefRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MT14PgAuth;
use App\Http\Requests\MT14PgAuthEditorRequest;
use App\Repositories\MT93PgRepository;

class MT14PGAuthEditorController extends Controller
{
    protected $AuthRef_repository;

    /**
     * コンストラクタ
     * リポジトリのインスタンスを生成、格納
     *
     * @param  UserRepository  $AuthRef_repository
     * @return
     */
    public function __construct(
        MT14AuthRefRepository $AuthRef_repository,
        MT93PgRepository $pg_repository
    ) {
        parent::__construct($pg_repository, '000003');
        $this->AuthRef_repository = $AuthRef_repository;
    }

    public function insert(MT14PgAuthEditorRequest $request)
    {
        $pgCD =  $request->input('PG_AUTH_CD');
        $this->AuthRef_repository->deleteAuth($pgCD);

        $today = date('Y-m-d H:i:s');

        foreach ($request['checkList'] as $checkedCd) {
            $param[] = array(
                'PG_AUTH_CD' => $request->PG_AUTH_CD,
                'PG_AUTH_NAME'=> $request-> PG_AUTH_NAME,
                'PG_CD '=>$checkedCd,
                'RSV1_CLS_CD' => '',
                'RSV2_CLS_CD' => '',
                'UPD_DATE' => $today
            );
        }

        $update_col = ['PG_AUTH_NAME','PG_CD','UPD_DATE'];
        $this->AuthRef_repository->upsertAuth($param, $update_col);

        if ($request->change != null) {
            return redirect('master/MT14PGAuthReference');
        }

        return redirect('master/MT14PGAuthEditor');
    }

    public function delete(Request $request)
    {
         $request->validate([
            'PG_AUTH_CD' =>
                [ 'required',
                    function ($attribute, $value, $fail) {
                        if ($this->AuthRef_repository->empExist($value)) {
                            // 4003,'社員情報で使用されているため削除できません。'
                            $fail('4003');
                        }
                    }
                ]
            ]);

        $pgCD =  $request->input('PG_AUTH_CD');
        $this->AuthRef_repository->deleteAuth($pgCD);
        return redirect('master/MT14PGAuthReference');
    }

    /**
     * 画面表示
     * 引数がない、またはnullの場合は新規登録
     *
     * @param [type] $id
     * @return void
     */
    public function edit($id = null)
    {
        $pgAuth_data = new MT14PgAuth();
        if ($id != null) {
            $pgAuth_data = $this->AuthRef_repository->edit($id);
        }
        $pg = $this->AuthRef_repository->pg($id);
        return parent::viewWithMenu('master.MT14PGAuthEditor', compact('pgAuth_data', 'pg'));
    }
}
