<!-- 社員情報取込処理 -->
@extends('menu.main')

@section('title', '社員情報取込処理')

@section('content')
    <div id="contents-stage">
        <form action="" method="POST" id="form" enctype="multipart/form-data" >
            {{ csrf_field() }}
            <table>
                <tbody>
                    <tr>
                        <td>
                            <div id="ctl00_cphContentsArea_UpdatePanel1">
                                <!-- header -->
                                <table class="InputFieldStyle1">
                                    <tbody>
                                        <tr>
                                            <th>ファイル</th>
                                            <td>
                                                    <input type="file" name="csvFile" class="" id="csvFile"/>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="line"></div>

                                <table class="mg10">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input name="btnImport" tabindex="2" class="ButtonStyle1 import"
                                                    id="btnImport" type="button" value="取込開始"
                                                    data-url="{{ route('EmpImport.import')}}"
                                                    @if(!isset($no_error_num))
                                                        autofocus
                                                    @endif>
                                                <input type="button" value="更新" name="btnUpdate" tabindex="3" id="btnUpdate"
                                                    class="ButtonStyle1 update" autofocus
                                                    data-url="{{ route('EmpImport.update')}}"
                                                    @if(!isset($no_error_num))
                                                        disabled
                                                    @endif>
                                                <input type="button" name="btnCancel" tabindex="4" id="btnCancel"
                                                    class="ButtonStyle1" value="キャンセル"
                                                    onclick="location.href='{{ route('EmpImport.index') }}'">
                                            </td>
                                            <td>
                                                &nbsp;
                                            </td>
                                            <td>
                                                &nbsp;
                                                @error('csvFile')
                                                <span class="text-danger">{{ getArrValue($error_messages, $errors->first('csvFile')) }}
                                                </span>
                                                @enderror
                                                @isset($no_error_num)
                                                    <font size="4" style="font-weight:bold">エラー件数：{{ $no_error_num }}件 更新ボタンを押して下さい。</font>
                                                @endisset
                                                @isset($error_num)
                                                    <font size="4" style="font-weight:bold">エラー件数：{{ $error_num }}件</font>
                                                @endisset
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- detail -->
                                <div class="GridViewStyle1" id="gridview-container">
                                    <div class="GridViewPanelStyle1">
                                        <div id="ctl00_cphContentsArea_pnl">

                                            <div>
                                                <table class="GridViewBorder GridViewPadding" id="gvEmpImport" style="border-collapse: collapse;" border="1" rules="all" cellspacing="0">
                                                    @isset($datas_error)
                                                    <tbody>
                                                    <tr>
                                                        <th scope="col">社員コード</th>
                                                        <th scope="col">社員名</th>
                                                        <th scope="col">社員カナ名</th>
                                                        <th scope="col">部門コード</th>
                                                        <th scope="col">エラー内容</th>
                                                    </tr>
                                                    @foreach ($datas_error as $data)
                                                    <tr>
                                                        <td style="pointer-events: none;">{{ $data['EMP_CD'] }}</td>
                                                        <td style="pointer-events: none;">{{ $data['EMP_NAME'] }}</td>
                                                        <td style="pointer-events: none;">{{ $data['EMP_KANA'] }}</td>
                                                        <td style="pointer-events: none;">{{ $data['DEPT_CD'] }}</td>
                                                        <td style="pointer-events: none;">
                                                        @foreach ($data['ERROR_MSG'] as $error_msg)
                                                            {{ $error_msg }}
                                                        @endforeach
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                @endisset
                                                @isset($file_not_exist)
                                                <tbody>
                                                    <tr>
                                                        <th scope="col">社員コード</th>
                                                        <th scope="col">社員名</th>
                                                        <th scope="col">社員カナ名</th>
                                                        <th scope="col">部門コード</th>
                                                        <th scope="col">エラー内容</th>
                                                    </tr>
                                                    @foreach ($file_not_exist as $data_not_match)
                                                    <tr id="tr_hover">
                                                        <td style="pointer-events: none;"></td>
                                                        <td style="pointer-events: none;"></td>
                                                        <td style="pointer-events: none;"></td>
                                                        <td style="pointer-events: none;"></td>
                                                        <td style="pointer-events: none;">
                                                        @foreach ($data_not_match['ERROR_MSG'] as $msg)
                                                            {{ $msg }}
                                                        @endforeach
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                @endisset
                                            </table>
                                            </div>
                                            @isset($count_data)
                                            <input type="hidden" name="count_data" id="count_data" value="{{ $count_data }}">
                                            @endisset
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
@endsection
@section('script')
<script>
$(function(){
    // 更新処理
    $(document).on('click', '.update', function() {
         // エラー文言削除
         var errors = $("#form").find('span.text-danger');
        if (errors.length) {
            errors.text("");
        }

        if (!window.confirm("{{ getArrValue($error_messages, '1005') }}")) {
            return false;
        }
        var url = $(this).data('url');
        $('#form').attr('action', url);
        $('#form').submit();
    });

    // 取込処理
    $(document).on('click', '.import', function() {
         // エラー文言削除
         var errors = $("#form").find('span.text-danger');
        if (errors.length) {
            errors.text("");
        }

        var url = $(this).data('url');
        $('#form').attr('action', url);
        $('#form').submit();
    });

    // ファイル選択するとき、エラー文言削除
    $(document).on('click', '#csvFile', function() {
         // エラー文言削除
         var errors = $("#form").find('span.text-danger');
        if (errors.length) {
            errors.text("");
        }
    });
});
</script>
@endsection

