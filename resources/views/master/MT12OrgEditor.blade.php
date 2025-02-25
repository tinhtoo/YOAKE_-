<!-- 組織変更入力 -->
@extends('menu.main')
@section('title', '組織変更入力 ')
@section('content')
    <div id="contents-stage">
        <table class="BaseContainerStyle2">
            <tbody>
                <tr>
                    <td>
                        <div id="ctl00_cphContentsArea_UpdatePanel1">
                            <form action="" method="post" id="form">
                                @csrf
                                <table class="InputFieldStyle1">
                                    <tbody>
                                        <tr>
                                            <th>部門コード</th>
                                            <td>
                                                <input type="text "name="txt_DeptCd" id="txt_DeptCd"
                                                        class="OutlineLabel" style="width: 80px;"
                                                        value="{{ old('txt_DeptCd') ?? $dept_data->DEPT_CD }}"
                                                        @if(isset($dept_data->DEPT_CD))
                                                        disabled
                                                        @endif
                                                        >
                                                        <input type="hidden" name="deptCdBumon" class="deptCdBumon" value={{ $dept_data->DEPT_CD }}>
                                                <input type="text" name="txtDeptName" class="OutlineLabel"
                                                        id="txtDeptName" style="width: 280px;"
                                                        value="{{ old('txtDeptName') ?? $dept_data ->DEPT_NAME }}"
                                                        @if(isset($dept_data->DEPT_NAME))
                                                        disabled
                                                        @endif
                                                        >
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>親部門コード</th>
                                            <td>
                                                <input type="text "name="txtUpDeptCd" id="txtUpDeptCd"
                                                        class="OutlineLabel" style="width: 80px;"
                                                        value="{{ old('txtUpDeptCd') ?? $dept_data->UP_DEPT_CD }}"
                                                        disabled
                                                        >
                                                <input type="hidden" name="txtUpHideCd" id="txtUpHideCd" value="{{ old('txtUpHideCd') ?? $dept_data->UP_DEPT_CD }}">
                                                <input type="text" name="txtUpDeptName" class="OutlineLabel"
                                                        id="txtUpDeptName" style="width: 280px;"
                                                        value="{{ $up_dept_data->DEPT_NAME ?? ''}}"
                                                        disabled
                                                        >
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>
                                                @error('txtUpHideCd')
                                                <span class="text-danger">{{ getArrValue($error_messages, $message) }}</span>
                                                @enderror
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>

                            <div class="line"></div>
                            <p class="ButtonField1">
                                <input type="button" name="btnSearchDeptCd" tabindex="1" id="btnSearchDeptCd" autofocus
                                    class="ButtonStyle1 SearchButton" value="親部門選択"
                                    data-url="{{ url('master/UpDeptSearch/'.$dept_data->DEPT_CD) }}"
                                    >
                                <input type="button" value="更新" name="btnUpdate" tabindex="2" id="btnUpdate"
                                    class="ButtonStyle1 update"
                                    data-url="{{ url('master/MT12OrgUpdate') }}"
                                    >
                                <input type="button" name="btnCancel" tabindex="3" id="btnCancel"
                                    class="ButtonStyle1" value="キャンセル"
                                    onclick="location.reload();"
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
$(function() {
    // ENTER時に送信されないようにする
    $('input').not('[type="button"]').keypress(function(e) {
        if (e.which == 13) {
            return false;
        }
    });

    // 更新submit-form
    $(document).on('click', '.update', function() {
        if (!window.confirm("{{ getArrValue($error_messages, 1005) }}")) {
            return false;
        }
        var url = $(this).data('url');
        $('#form').attr('action', url);
        $('#form').submit();
    });

    // 組織変更入力　部門情報検索サブ画面

    var popupDept;
    // ポップアップ画面を開く
    $(document).on('click', '.SearchButton',function() {
        popupDept = window.open('/search/UpDeptSearch/'+ $('#txt_DeptCd').val(), '部門情報検索', 'height=550,width=400,left=400,top=90,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');
        window.focus();
        popupDept.focus();
    })

    // ポップアップ画面を閉じる
    window.addEventListener('unload', function(event) {
        if (typeof popupDept != "undefined") {
            popupDept.close();
        }
    });

});
</script>
@endsection
