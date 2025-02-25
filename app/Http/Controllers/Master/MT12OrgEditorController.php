<?php

namespace App\Http\Controllers\Master;

use App\Repositories\Master\MT12DeptRepository;
use App\Http\Controllers\Controller;
use App\Repositories\MT93PgRepository;
use App\Http\Requests\MT12OrgEditorRequest;
use App\Repositories\Search\MT12DeptSearchRepository;
use Illuminate\Support\Facades\DB;

class MT12OrgEditorController extends Controller
{
    protected $MT12Dept;

    /**
     * コンストラクタ
     * リポジトリのインスタンスを生成、格納
     *
     * @param
     * @return
     */
    public function __construct(
        MT12DeptRepository $MT12Dept_repository,
        MT93PgRepository $pg_repository,
        MT12DeptSearchRepository $mt12_dept_search_repository
    ) {
        parent::__construct($pg_repository, '000018');
        $this->MT12Dept = $MT12Dept_repository;
        $this->MT12DeptSearch = $mt12_dept_search_repository;
    }

    /**
     * 画面表示
     *
     * @param [type] $id
     * @return
     */
    public function edit($id = null)
    {
        $dept_data = null;
        $up_dept_data = null;
        if ($id != null) {
            $dept_data = $this->MT12Dept->getWithPrimary($id);
            if ($dept_data == null) {
                // TODO 該当データなしエラー画面作成
                return redirect('master/MT12OrgReference');
            }
            $up_dept_data = $this->MT12Dept->getWithPrimary($dept_data->UP_DEPT_CD);
        }
        return parent::viewWithMenu('master.MT12OrgEditor', compact(
            'dept_data',
            'up_dept_data'
        ));
    }

    /**
     * 更新処理
     *
     */
    public function update(MT12OrgEditorRequest $request)
    {
        $today = date('Y-m-d H:i:s');

        $new_up_dept_cd = $request->txtUpHideCd;
        $new_up_dept =  $this->MT12Dept->getWithPrimary($new_up_dept_cd);

        $param = [
            'DEPT_CD' => $request->deptCdBumon,
            'UP_DEPT_CD' => $request->txtUpHideCd,
            'LEVEL_NO' => $new_up_dept->LEVEL_NO + 1,
            'UPD_DATE' => $today
        ];

        $all_dept = $this->MT12DeptSearch->getSorted();
        $bumon_cd = $request->deptCdBumon;
        $oya_level_no = null;
        $children_dept_list = [];

        // 更新対象（選択した部門の子、孫…）を取得
        foreach ($all_dept as $dept_data) {
            if ($dept_data->DEPT_CD === $bumon_cd) {
                $oya_level_no = $dept_data->LEVEL_NO;
            } elseif ($oya_level_no !== null) {
                if ($dept_data->LEVEL_NO > $oya_level_no) {
                    $children_dept_list[] = $dept_data;
                } else {
                    break;
                }
            }
        }

        DB::beginTransaction();
        try {
            // 選択した部門を更新
            $this->MT12Dept->updateDeptOrg($param);

            if (!empty($children_dept_list)) {
                $top_child_level_no = $children_dept_list[0]->LEVEL_NO;
                foreach ($children_dept_list as $child_dept) {
                        $param_child = [
                            'DEPT_CD' => $child_dept->DEPT_CD,
                            'LEVEL_NO' => $param['LEVEL_NO'] + 1 + $child_dept->LEVEL_NO - $top_child_level_no,
                            'UPD_DATE' => $today
                        ];
                        // 選択した部門の子、孫…を更新
                        $this->MT12Dept->updateLevelNo($param_child);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            \Log::debug($e);
            DB::rollback();
        }

        return redirect('master/MT12OrgReference');
    }
}
