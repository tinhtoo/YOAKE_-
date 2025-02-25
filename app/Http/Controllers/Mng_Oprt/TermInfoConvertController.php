<?php

namespace App\Http\Controllers\Mng_Oprt;

use App\Http\Controllers\Controller;
use App\Repositories\Work_Time\MT95TermRepository;
use App\Repositories\MT93PgRepository;

/**
 * 端末情報更新処理画面
 *
 * 凍結（開発対象外）
 */
class TermInfoConvertController extends Controller
{
    private $mt95_term;
    /**
     * コントローラインスタンスの生成
     * @param
     * @return void
     */
    public function __construct(
        MT93PgRepository $pg_repository,
        MT95TermRepository $mt95_term_repository
    ) {
        parent::__construct($pg_repository, '040005');
        $this->mt95_term = $mt95_term_repository;
    }

    /**
     * 端末情報更新処理画面表示
     * @return view
     */
    public function index()
    {
        return parent::viewWithMenu('mng_oprt.TermInfoConvert');
    }

    /**
     * 端末情報更新
     *
     * @return view
     */
    public function update()
    {
        $result = $this->execUpdate();
        return parent::viewWithMenu('mng_oprt.TermInfoConvert', compact('result'));
    }

    /**
     * 端末情報更新処理
     * MT95から各端末のDBを取得して、各端末のDBを更新する
     *
     * @return void
     */
    private function execUpdate()
    {
        $fail_terms = [];
        $term_list = $this->mt95_term->getList();
        foreach ($term_list as $term) {
            // 各端末への接続情報を作成


            // 連絡事項更新


            // 社員情報更新
        }

        return $fail_terms;
    }


    private function createConnecter()
    {
        return ;
    }
}
