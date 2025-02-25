<!-- 組織変更参照   -->
@extends('menu.main')

@section('title', '組織変更参照 ')

@section('content')
    <div id="contents-stage">
        <table class="BaseContainerStyle1">
            <tbody>
                <tr>
                    <td>
                        <div id="ctl00_cphContentsArea_UpdatePanel1">

                            <div class="GridViewStyle1">
                                <div>
                                    <table id="ctl00_cphContentsArea_gvDept" style="border-collapse: collapse;" border="1"
                                        rules="all" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <th scope="col">
                                                    部門
                                                </th>
                                            </tr>
                                            @foreach ($paginateOrg as $dept_item)
                                            <tr>
                                                <td>
                                                    <a href="{{ url('master/MT12OrgEditor/'.$dept_item->DEPT_CD )}}">
                                                    {{ $dept_item->DEPT_CD }} :
                                                        @for ($i=0 ; $i< ( $dept_item->LEVEL_NO ); $i++)
                                                        　　　
                                                        @endfor
                                                    {{ $dept_item->DEPT_NAME }}
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="line"></div>
                            <tr class="ButtonField1">
                                <td>
                                    <div class="d-flex justify-content-center" id="pegination">
                                    {{ $paginateOrg->links() }}
                                    </div>
                                </td>
                            </tr>

                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
