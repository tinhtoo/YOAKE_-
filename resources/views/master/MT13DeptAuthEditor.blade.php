<!-- 部門権限情報入力 -->
@extends('menu.main')
@section('title', '部門権限情報入力')
@section('content')
    <div id="contents-stage">
        <table class="BaseContainerStyle1">
            <tbody>
                <tr>
                    <td>
                        <div id="UpdatePanel1">
                            <form action="" method="post" id="form">
                                @csrf
                                <table class="InputFieldStyle1">
                                    <tbody>
                                        <tr>
                                            <th>部門権限コード</th>
                                            <td>
                                                <input type="text" name="txtDeptAuthCd" id="txtDeptAuthCd" maxlength="6"
                                                    value="{{ old('txtDeptAuthCd') ?? $dept_auth_data->DEPT_AUTH_CD }}" tabindex="1"
                                                    style="width: 80px;" onFocus="this.select()" oninput="value = onlyHalfWord(value)"
                                                    @if(isset($dept_auth_data->DEPT_AUTH_CD))
                                                    disabled
                                                    @else
                                                    autofocus
                                                    onFocus="this.select()"
                                                    @endif
                                                    >
                                                    @error('txtDeptAuthCd')
                                                    <span class="text-danger">{{ getArrValue($error_messages, $message) }}</span>
                                                    @enderror
                                                @if(isset($dept_auth_data->DEPT_AUTH_CD))
                                                <input type="hidden" name="txtDeptAuthCd" value="{{ $dept_auth_data->DEPT_AUTH_CD }}">
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>部門権限名</th>
                                            <td>
                                                <input type="search" name="txtDeptAuthName" id="txtDeptAuthName" maxlength="20"
                                                value="{{ old('txtDeptAuthName') ?? $dept_auth_data->DEPT_AUTH_NAME }}" tabindex="2"
                                                style="width: 300px;" onFocus="this.select()" oninput="value = ngVerticalBar(value)"
                                                @if (isset($dept_auth_data->DEPT_AUTH_NAME))
                                                autofocus
                                                onFocus="this.select()"
                                                @endif>
                                                @error('txtDeptAuthName')
                                                <span class="text-danger">{{ getArrValue($error_messages, $message) }}</span>
                                                @enderror
                                        </td>
                                        </tr>
                                    </tbody>
                                </table>

                            <div class="line"></div>

                            <input name="btnAllCheck" tabindex="3" id="btnAllCheck" type="button" value="全選択" >
                            <input name="btnAllNotCheck" tabindex="4" id="btnAllNotCheck" type="button" value="全解除">
                            @error('chkListSelect')
                            <span class="text-danger">{{ getArrValue($error_messages, $message) }}</span>
                            @enderror

                            <div class="GridViewStyle1 mg10" id="gridview-container">
                                <div class="GridViewPanelStyle7" id="pnlDeptAuth">

                                    <div>
                                        <table class="GridViewBorder GridViewPadding" id="gvDeptAuth"
                                            style="border-collapse: collapse;" border="1" rules="all" cellspacing="0">
                                            <tbody tabindex="5">
                                                <tr>
                                                    <th scope="col">&nbsp;</th>
                                                    <th scope="col">部門</th>
                                                </tr>
                                                @foreach ($all_dept_list as $num => $dept)
                                                <tr>
                                                    <td class="GridViewRowCenter" style="width: 30px; white-space: nowrap;">
                                                        <input type="checkbox" name="chkListSelect[{{ $num }}]" id="chkListSelect"
                                                        value="{{ $dept->DEPT_CD }}"
                                                        @foreach ($dept_auth as $dept_cd)
                                                        @if ((!old() && $dept->DEPT_CD === $dept_cd) || (old('chkListSelect.'.$num) === $dept->DEPT_CD))
                                                        checked
                                                        @endif
                                                        @endforeach
                                                        @if ((!isset($dept_auth_data->DEPT_AUTH_CD)))
                                                        {{ old('chkListSelect.'.$num) === $dept->DEPT_CD ? ' checked' : '' }}
                                                        @endif
                                                        >
                                                    </td>
                                                    <td class="GridViewRowLeft">
                                                        @for ($i = 0; $i < ($dept->LEVEL_NO); $i++)
                                                        　　　
                                                        @endfor
                                                        {{ $dept->DEPT_NAME }}
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>
                            <input type="hidden" name="hideDeptAuthCd" value="{{ $dept_auth_data->DEPT_AUTH_CD }}">
                            <div class="line"></div>

                            <p class="ButtonField1">
                                <input type="button" value="更新" name="btnUpdate" tabindex="6" id="btnUpdate"
                                            class="ButtonStyle1 update"
                                            data-url="{{ url('master/MT13DeptAuthUpsert') }}">
                                <input type="button" name="btnCancel" tabindex="7" id="btnCancel"
                                            class="ButtonStyle1" value="キャンセル"
                                            onclick="location.reload();">
                                <input type="button" value="削除" name="btnDelete" tabindex="8" id="btnDelete"
                                            class="ButtonStyle1 delete"
                                            @if(!isset($dept_auth_data->DEPT_AUTH_CD))
                                            disabled
                                            @else
                                            data-url="{{ url('master/MT13DeptAuthDelete') }}"
                                            @endif
                                            >
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

// 全選択・全解除ボタン
$(document).ready(function () {

    $('#btnAllCheck').on('click', function () {
      $('input[type=checkbox]').prop( 'checked', true );
    });

    $('#btnAllNotCheck').on('click', function () {
      $('input[type=checkbox]').prop( 'checked', false );
    });

});

$(function() {
     // ENTER時に送信されないようにする
     $('input').not('[type="button"]').keypress(function(e) {
        if (e.which == 13) {
            return false;
        }
    });

    // 更新submit-form
    $(document).on('click', '.update', function() {
        if (!window.confirm("{{ getArrValue($error_messages, '1005') }}")) {
            return false;
        }
        var url = $(this).data('url');
        $('#form').attr('action', url);
        $('#form').submit();
    });

    // 削除処理submit-form
    $(document).on('click', '.delete', function() {
        if (!window.confirm("{{ getArrValue($error_messages, '1004') }}")) {
            return false;
        }
        var url = $(this).data('url');
        $('#form').attr('action', url);
        $('#form').submit();

    });

    // 機能権限名入力不可能文字：|
    ngVerticalBar = n => n.replace(/\|/g, '');

    // 機能権限コード英数半角のみ入力可
    onlyHalfWord = n => n.replace(/[０-９Ａ-Ｚａ-ｚ]/g, s => String.fromCharCode(s.charCodeAt(0) - 65248))
            .replace(/[^0-9a-zA-Z]/g, '');

});
</script>
@endsection
