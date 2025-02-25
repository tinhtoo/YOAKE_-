<!-- シフトパターン情報入力(新規シフトパタン登録)画面 -->
@extends('menu.main')
@section('title', 'シフトパターン情報入力')
@section('content')
    <div id="contents-stage">
        <form action="" method="post" id="form">
            @csrf
            <table class="BaseContainerStyle1">
                <tbody>
                    <tr>
                        <td>

                            <div id="ctl00_cphContentsArea_UpdatePanel1">

                                <table class="InputFieldStyle1">
                                    <tbody>
                                        <tr>
                                            <th>パターンコード</th>
                                            <td>
                                                <input type="text" name="txtShiftPtnCd" tabindex="1"
                                                    value="{{ old('txtShiftPtnCd') ?? $shiftptn_data->SHIFTPTN_CD }}"
                                                    class="txtShiftPtnCd" id="txtShiftPtnCd" oninput="value = onlyHalfWord(value)"
                                                    style="width: 35px;" onfocus="this.select();" maxlength="3"
                                                    @if(isset($shiftptn_data->SHIFTPTN_CD))
                                                    disabled
                                                    @else
                                                    autofocus
                                                    onFocus="this.select()"
                                                    @endif>
                                                <span class="text-danger" id="shiftPtnCd"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>パターン名</th>
                                            <td>
                                                <input type="search" name="txtShiftPtnName" tabindex="2"
                                                    value="{{ old('txtShiftPtnName') ?? $shiftptn_data->SHIFTPTN_NAME }}"
                                                    class="txtShiftPtnName" id="txtShiftPtnName" oninput="value = ngVerticalBar(value)"
                                                    style="width: 300px;" onfocus="this.select();" maxlength="20"
                                                    @if (isset($shiftptn_data->SHIFTPTN_NAME))
                                                    autofocus
                                                    onFocus="this.select()"
                                                    @endif>
                                                <span class="text-danger" id="shiftPtnName"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <div class="line"></div>

                                <input name="btnAddNewRow" tabindex="3"
                                    id="btnAddNewRow" type="button" onclick="appendRow()"
                                    value="新規行追加">
                                    <span class="text-danger" id="shiftPtn"></span>
                                <div class="GridViewStyle1 mg10">
                                    <div class="GridViewPanelStyle5" id="ctl00_cphContentsArea_pnlShiftPtn"
                                        style="width: 450px; height: 482px;">
                                        <div>
                                            <table tabindex="4" class="GridViewPadding GridViewBorder"
                                                id="gvShiftPtn" style="border-collapse: collapse;" border="1"
                                                rules="all" cellspacing="0">
                                                <tbody>
                                                    <tr id ="headerGvShiftPtn">
                                                        <th scope="col">日</th>
                                                        <th scope="col">勤務体系</th>
                                                        <th scope="col">行削除</th>
                                                    </tr>
                                                    @foreach ($shiftptn_select as $key => $shiftptn)
                                                    <tr class="rowGvShiftPtn" style="pointer-events:disabled">
                                                        <td align="center">
                                                            <span id="lblDayNo{{ $key }}" class="lblDayNo">{{ $shiftptn->DAY_NO}}日目</span>
                                                        </td>
                                                        <td>
                                                            <select name="ddlWorkPtn[{{ $key }}][workPtn]"
                                                            id="ddlWorkPtn{{ $key }}workPtn" tabindex="5"
                                                            class="ddlWorkPtn coloredSelect" style="width: 260px;">
                                                            <option style=color:black; value="" ></option>
                                                            @foreach ($workptn_datas as $workptn_data)
                                                            <option value="{{ $workptn_data->WORKPTN_CD }}"
                                                                {{ $workptn_data->WORK_CLS_CD == '00' ? 'style=color:red;' : 'style=color:black;'}}
                                                                {{ $workptn_data->WORKPTN_CD == (old('ddlWorkPtn',$shiftptn->WORKPTN_CD)) ? 'selected' : ''}}>
                                                                {{ $workptn_data->WORKPTN_NAME }}
                                                            </option>
                                                            @endforeach
                                                            </select>
                                                            <br>
                                                            <span class="text-danger" id="shiftPtn{{ $key }}workPtn"></span>
                                                        </td>
                                                        <td align="center">
                                                            <input name="dltBtn[{{ $key }}][btnDelete]" class="ButtonStyle1 deleteButtonRow"
                                                                id="btnDeleteRow" type="button" value="削除">
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="hideShiftPtnCd" id="hideShiftPtnCd" value="{{ $shiftptn_data->SHIFTPTN_CD }}">

                                <div class="line"></div>

                                <p class="ButtonField1">
                                    <input type="button" value="更新" name="btnUpdate" tabindex="6" id="btnUpdate"
                                                class="ButtonStyle1 update"
                                                data-url="{{ url('shift/MT04ShiftPtnUpdate') }}">
                                    <input type="button" name="btnCancel" tabindex="7" id="btnCancel"
                                                class="ButtonStyle1" value="キャンセル"
                                                onclick="location.reload();">
                                    <input type="button" value="削除" name="btnDelete" tabindex="8" id="btnDelete"
                                                class="ButtonStyle1 delete"
                                                @if(!isset($shiftptn_data->SHIFTPTN_CD))
                                                disabled
                                                @else
                                                data-url="{{ url('shift/MT04ShiftPtnDelete') }}"
                                                @endif>
                                </p>

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
$(function() {
    // ENTER時に送信されないようにする
    $('input').not('[type="button"]').keypress(function(e) {
        if (e.which == 13) {
            return false;
        }
    });

    // データーが無い場合、ヘッダー非表示
    var objTbl = document.getElementById("gvShiftPtn");
    var countTbl = objTbl.rows.length;
    if (countTbl == 1) {
        document.getElementById("headerGvShiftPtn").style.display ="none";
    }

    // 行削除や追加する際にインデックス番号を更新
    var updateIndex = function(clickedObj) {
        var index = $(clickedObj).attr("name").replace(/[^0-9]/g, "");
        // 取得したインデックス番号が０～になっている為、＋１にする
        var day = parseInt(index) + 1;
        $(clickedObj).closest('tr').nextAll().each(function(i,element){
            $(element).find("input,span,select").each(function(i,e) {
                var elementObj = $(e);
                var eleId = elementObj.attr("id");
                var eleName = elementObj.attr("name");

                if (eleId && eleId.replace(/^([^0-9]+)/g, "")) {
                    var newId = eleId.replace(/^([^0-9]+)[0-9]+([^0-9]*)$/, function(){return arguments[1] + index + arguments[2]});
                    elementObj.attr("id", newId);
                }
                if (eleName && eleName.replace(/^([^0-9]+)/g, "")) {
                    var newName = eleName && eleName.replace(/^([^0-9]+)[0-9]+([^0-9]+)$/, function(){return arguments[1] + index + arguments[2]});
                    elementObj.attr("name", newName);
                }
            });
            // 日カラムの番号を更新
            $(element).find(".lblDayNo").each(function(i,e) {
                var elementObj = $(e);
                var text = elementObj.text();

                if (text && text.replace(/^([^0-9]+)/g, "")) {
                    var newText = text && text.replace(/^([^0-9]*)[0-9]+([^0-9]+)$/, function(){return day + arguments[2]});
                    elementObj.text(newText);
                }
            });
            index++;
            day++
        });
    }

    // ヘッダー以外行がない場合は、ヘッダーを非表示にする
    var headerHidden = function() {
        var objTblShiftPtn = document.getElementById("gvShiftPtn");
        var countShiftPtn = objTblShiftPtn.rows.length;
        if (countShiftPtn == 1){
            document.getElementById("headerGvShiftPtn").style.display ="none";
        }
    }

    // 更新処理
    $(document).on('click', '.update', function() {
        if (!window.confirm("{{ getArrValue($error_messages, 1005) }}")) {
            $('#btnUpdate').focus();
            return false;
        }

        var errors = $("#form").find('span.text-danger');
        if (errors.length) {
            errors.text("");
        }

        var shiftPtn = [];
        $('.rowGvShiftPtn').each(function(i,element) {
            shiftPtn[i] = {
                'dayNoPtn': $(element).find('.lblDayNo').val(),
                'workPtn': $(element).find('.ddlWorkPtn').val(),
            };
        })

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url:$(this).data('url'),
            type:'POST',
            data:{
                'shiftPtnCd':$('#txtShiftPtnCd').val(),
                'shiftPtnName':$('#txtShiftPtnName').val(),
                'hideShiftPtnCd':$('#hideShiftPtnCd').val(),
                'shiftPtn':shiftPtn,
            }
        })
        .done((data, textStatus, jqXHR) => {
            if ($('#hideShiftPtnCd').val() == '') {
                location.reload();
            } else {
                location.href='{{ url('shift/MT04ShiftPtnReference') }}';
            }
        })
        .fail ((jqXHR, textStatus, errorThrown) => {
            $.each(jqXHR.responseJSON.errors, function(i, value) {
                $('#' + i.replaceAll('.', '')).text(value[0]);
            });
        });
        return false;
    });

    // 行削除
    $(document).on('click', '.deleteButtonRow', function() {
        var parent = $(this).closest('tr');
        // フォーカス設定
        var indexRow = $(this).attr("name").replace(/[^0-9]/g, "");
        if (parseInt(indexRow) == 0) {
            var indexRow = parseInt(indexRow) + 1;
        } else {
            var indexRow = parseInt(indexRow) - 1;
        }
        $('#ddlWorkPtn' + indexRow + 'workPtn').focus();
        updateIndex(this);
        parent.remove();
        headerHidden();

        return false;
    });

    // エラー文言削除
    $('.deleteButtonRow').click(function() {
        var errors = $("#form").find('span.text-danger');
        if (errors.length) {
            errors.text("");
        }
    });

    // 削除処理
    $(document).on('click', '.delete', function() {
        if (!window.confirm("{{ getArrValue($error_messages, 1004) }}")) {
            $('#btnDelete').focus();
            return false;
        }

        var errors = $("#form").find('span.text-danger');
        if (errors.length) {
            errors.text("");
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url:$(this).data('url'),
            type:'POST',
            data:{
                'shiftPtnCd':$('#txtShiftPtnCd').val(),
            }
        })
        .done((data, textStatus, jqXHR) => {
            location.href='{{ url('shift/MT04ShiftPtnReference') }}';
        })
        .fail ((jqXHR, textStatus, errorThrown) => {
            $.each(jqXHR.responseJSON.errors, function(i, value) {
                $('#' + i.replaceAll('.', '')).text(value[0]);
            });
        });
        return false;
    });

    // パターン名入力不可能文字：|
    ngVerticalBar = n => n.replace(/\|/g, '');

    // パターンコード英数半角のみ入力可
    onlyHalfWord = n => n.replace(/[０-９Ａ-Ｚａ-ｚ]/g, s => String.fromCharCode(s.charCodeAt(0) - 65248))
            .replace(/[^0-9a-zA-Z]/g, '');

    // hover修正
    $('tr').hover(
        function (){
            $('tr').css('background-color', '#FFF');
        },
        function () {
            $('tr').css('background-color', '#FFF');
        }
    );
    // エラー文言削除
    $('.ddlWorkPtn').change(function() {
        if ($(this).val() && $(this).parent().find('span.text-danger').length) {
            $(this).parent().find('span.text-danger').text("");
        }
    });
    $('#txtShiftPtnName, #txtShiftPtnCd').focusout(function() {
        if ($(this).val() && $(this).parent().find('span.text-danger').length) {
            $(this).parent().find('span.text-danger').text("");
        }
    });
    // プルダウンの色設定
    var changeColor = function() {
            $(".coloredSelect").each(function(i,e){
                $(e).css('color', $(e).children("option:selected").css("color"))
            });
        };
        changeColor();
        $(".coloredSelect").on('change', (ele) => {
            $(ele.target).css('color', $(ele.target).children("option:selected").css("color"))
    });
});

// テーブルに行を追加
function appendRow() {

    var objTBL = document.getElementById("gvShiftPtn");
    var count = objTBL.rows.length;

    // 全行削除時また追加するときにヘッダー表示
    if (count == 1) {
        document.getElementById("headerGvShiftPtn").style.display ="";
    }

    // 最終行に新しい行を追加
    var row = objTBL.insertRow(count);
    row.className = 'rowGvShiftPtn';

    // 列の追加
    var days = row.insertCell(0);
    var selectSystem = row.insertCell(1);
    var deleteButton = row.insertCell(2);

    // 各列にスタイルを設定
    days.style.cssText = "text-align:center;";
    deleteButton.style.cssText = "text-align:center;";

    // 各列に表示内容を設定
    days.innerHTML = '<span id="lblDayNo' + (count - 1) + '" class="lblDayNo">' + (count) + '日目</span>' ;
    selectSystem.innerHTML =    '<select name="ddlWorkPtn[' + (count - 1) + '][workPtn]" id="ddlWorkPtn' + (count - 1) + 'workPtn"'
                                + 'tabindex="5" class="ddlWorkPtn coloredSelect" style="width: 260px;">'
                                + '<option style=color:black; value="" ></option>'
                                + '@foreach ($workptn_datas as $workptn_data)'
                                + '<option value="{{ $workptn_data->WORKPTN_CD }}"'
                                   + '{{ $workptn_data->WORK_CLS_CD == '00' ? 'style=color:red;' : 'style=color:black;'}}'
                                   + '{{ $workptn_data->WORKPTN_CD == old('ddlWorkPtn') ? 'selected' : ''}}>'
                                   + '{{ $workptn_data->WORKPTN_NAME }}'
                                + '</option>'
                                + '@endforeach'
                                + '</select>'
                                + '<br>'
                                + '<span class="text-danger" id="shiftPtn' + (count - 1) + 'workPtn"></span>'
    deleteButton.innerHTML = '<input type="button" name="dltBtn[' + (count - 1) + '][btnDelete]"'
                            + 'class="ButtonStyle1 deleteButtonRow" value="削除">';
    // フォーカス設定
    $('.rowGvShiftPtn select:last').focus();
    // エラー文言削除
    var errorsAll = $("#form").find('span.text-danger');
    $('.deleteButtonRow').click(function() {
        if (errorsAll.length) {
            errorsAll.text("");
        }
    });
    if (errorsAll.length) {
        errorsAll.text("");
    }
    $('.ddlWorkPtn').change(function() {
        if ($(this).val() && $(this).parent().find('span.text-danger').length) {
            $(this).parent().find('span.text-danger').text("");
        }
    });
    // プルダウンの色設定
    var changeColor = function() {
            $(".coloredSelect").each(function(i,e){
                $(e).css('color', $(e).children("option:selected").css("color"))
            });
        };
        changeColor();
        $(".coloredSelect").on('change', (ele) => {
            $(ele.target).css('color', $(ele.target).children("option:selected").css("color"))
    });
}
</script>
@endsection
