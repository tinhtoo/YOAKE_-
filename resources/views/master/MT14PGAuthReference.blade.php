<!-- 機能権限情報照会 -->
@extends('menu.main')

@section('title', '機能権限情報照会')

@section('content')
<div id="contents-stage">
    <table class="BaseContainerStyle1">
        <tbody>
            <tr>
                <td>
                    <div id="ctl00_cphContentsArea_UpdatePanel1">

                        <p class="FunctionMenu1">
                            <a id="ctl00_cphContentsArea_hlAddPgAuth" href="MT14PGAuthEditor?Id=Add">新規機能権限登録</a>
                        </p>

                        <div class="line"></div>
                        <div class="GridViewStyle1">
                            <table style="border-collapse: separate;">
                                <tbody>
                                    <tr>
                                        <th>
                                            機能権限
                                        </th>
                                        <th>
                                            機能権限
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="GridViewStyle1" id="tableForm">
                            <div>
                                <table class="GridViewSpace" id="ctl00_cphContentsArea_gvPgAuth" style="border-collapse: collapse;" border="1" rules="all" cellspacing="0">
                                    <tbody>
                                        @for($i = 0; $i < count($all) && $i < 20; $i++)
                                            <tr style="background-color: white;">
                                                <td class="col-sm-4" style="width=50%">
                                                        <a href="{{ url('master/MT14PGAuthEditor/'. $all[$i]->PG_AUTH_CD )}}">
                                                            {{ $all[$i]->PG_AUTH_CD }} : {{ $all[$i]->PG_AUTH_NAME }}
                                                        </a>
                                                </td>
                                                <td class="col-sm-4" style="width=50%">
                                                    @if($all[$i + 20] != null )
                                                    <a href="{{ url('master/MT14PGAuthEditor/'. $all[$i + 20]->PG_AUTH_CD )}}">
                                                        {{ $all[$i + 20]->PG_AUTH_CD }} : {{ $all[$i + 20]->PG_AUTH_NAME }}
                                                    </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="line"></div>
                        <div class="d-flex justify-content-center" id="pegination">
                                {{ $all->links() }}
                        </div>
                        <div class="line"></div>
                        <p class="ButtonField1">
                            <input name="btnCancel" class="SearchButton" id="btnCancel" type="button"
                                onclick="Cancel();" value="キャンセル">
                        </p>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
@section('script')
    <script>
            // キャンセルボタン
            function Cancel() {
                $("#tableForm").val('');
                window.location.replace("MT14PGAuthReference");
            }
</script>
@endsection
