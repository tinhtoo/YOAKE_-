Complete = function() {
    $('#gridview-container .GridViewPanelStyle1').width(1);
    $('#gridview-container .GridViewPanelStyle1').width($('#contents').width() - 50);
};
Complete();
$(window).on("resize", Complete);

//***** table header add *****//
function hh() {
    var header = '<tr class="d_head" ><th scope="col">部門コード</th><th scope="col">部門名</th><th scope="col">行削除</th></tr>';
    $(".gvDept").append(header);
}

var count = 0;
//***** table row add *****//
function hh_2() {
    count++;
    var html_input = '<tr class="data"><td ><input tabindex="4" class="dep_id" name="record_' + (count) + ' " style="width: 55px;" type="text" maxlength="6" value=""></td><td><input tabindex="4" style="width: 270px;" type="text" maxlength="20" value=""></td><td><input tabindex="4" class="DeleteRow" name="del-col" type="button" value="削除"></td></tr>';

    $(".gvDept").append(html_input);

}

//***** テーブル行追加ボタン処理 *****//
let lineNo = 0;
$(document).on('click', '#AddNewRow', function() {
    if ($(".d_head")[0]) {
        hh_2()
    } else {
        hh()
        hh_2()
    }

})
lineNo++;


//***** 行削除ボタン処理 *****//
$(".gvDept").on("click", ".DeleteRow", function() {

    if ($(".data")[1]) {
        $(this).closest('.data').remove();
    } else {
        $(this).closest('.data').remove();
        $(".d_head").remove();
    }

});

var popupDept;
// 部門情報検索サブ画面
function SearchDept(element) {
    $(".clickedTableRecord").remove();
    $(element).closest('td').append("<input type='hidden' class='clickedTableRecord'>");
    var deptNameObj = $(element).closest("td").find("#deptName");
    var param = "";
    if (Object.keys(deptNameObj.data()).length) {
        var paramObject = {};
        deptNameObj.data().dispclscd ? paramObject['dispClsCd'] = deptNameObj.data().dispclscd : null;
        deptNameObj.data().isdeptauth ? paramObject['isDeptAuth'] = deptNameObj.data().isdeptauth : null;
        var searchParam = new URLSearchParams(paramObject).toString();
        param = searchParam ? '?' + searchParam : '';
    }
    var url = '/search/MT12DeptSearch' + param;
    popupDept = window.open(url, '部門情報検索', 'height=550,width=400,left=400,top=90,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');
    window.focus();
    popupDept.focus();
}

// ポップアップ画面を閉じる
window.addEventListener('unload', function(event) {
    if (typeof popupDept != "undefined") {
        popupDept.close();
    }
});

// 社員情報検索サブ画面

var popupEmp;
// ポップアップ画面を開く
function SearchEmp(element) {
    $(".clickedTableRecord").remove();
    $(element).closest('td').append("<input type='hidden' class='clickedTableRecord'>");
    var empNameObj = $(element).closest("td").find("#empName");
    var param = "";
    if (Object.keys(empNameObj.data()).length) {
        var paramObject = {};
        empNameObj.data().regclscd ? paramObject['regClsCd'] = empNameObj.data().regclscd : null;
        empNameObj.data().isdeptauth ? paramObject['isDeptAuth'] = empNameObj.data().isdeptauth : null;
        empNameObj.data().calendarclscd ? paramObject['calendarClsCd'] = empNameObj.data().calendarclscd : null;
        var searchParam = new URLSearchParams(paramObject).toString();
        param = searchParam ? '?' + searchParam : '';
    }
    var url = '/search/MT10EmpSearch' + param;
    popupEmp = window.open(url, '社員情報検索', 'height=650,width=500,left=350,top=90,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');
    window.focus();
    popupEmp.focus();
}

// ポップアップ画面を閉じる
window.addEventListener('unload', function(event) {
    if (typeof popupEmp != "undefined") {
        popupEmp.close();
    }
});

/**
 * 出退勤入力画面詳細枠の表示
 **/

$('#btnDisp').on('click', function() {
    $('#gridview-warp').show('slow');
    $(this).attr('disabled', 'disabled');
});

/* 日プルダウン作成 */
function AddDropDownList(txtYearClientId, ddlMonthClientId, ddlDayClientId, noSpace = false) {
    var txtYear = document.getElementById(txtYearClientId);
    var ddlMonth = document.getElementById(ddlMonthClientId);
    var ddlDay = document.getElementById(ddlDayClientId);
    if (txtYear.value.trim().length == 0 || ddlMonth.options[ddlMonth.selectedIndex].value.trim().length == 0) {
        return false;
    }

    var ddlDaySelectIndex = ddlDay.selectedIndex;
    var yearValue = parseInt(txtYear.value);
    var monthValue = parseInt(ddlMonth.options[ddlMonth.selectedIndex].value);
    var nextYearValue;
    var nextMonthValue;
    if (monthValue == 12) {
        nextYearValue = yearValue + 1;
        nextMonthValue = 1;
    } else {
        nextYearValue = yearValue;
        nextMonthValue = monthValue + 1;
    }
    var lastDay = new Date(nextYearValue, nextMonthValue - 1, 0);
    lastDay = lastDay.getDate();
    ddlDay.options.length = 0;
    ddlDay.options[0] = new Option('', '');

    var indexOffset = noSpace ? -1 : 0;

    for (index = 1; index <= lastDay; index++) {
        ddlDay.options[index + indexOffset] = new Option(index, index);
    }
    if (ddlDay.options.length <= ddlDaySelectIndex) {
        ddlDay.selectedIndex = ddlDay.options.length - 1;
    } else {
        ddlDay.selectedIndex = ddlDaySelectIndex;
    }
}

// 「時間数」の「職務種別」プルダウン作成
function SetTimeJobType(jobTypeId, itemJobId, startHourId, startMinId, endHourId, endMinId, startHourTextId, endHourTextId) {

    var jobType = document.getElementById(jobTypeId);
    var itemJob = document.getElementById(itemJobId);
    var startHour = document.getElementById(startHourId);
    var startMin = document.getElementById(startMinId);
    var endHour = document.getElementById(endHourId);
    var endMin = document.getElementById(endMinId);
    var startHourText = document.getElementById(startHourTextId);
    var endHourText = document.getElementById(endHourTextId);

    var startHourSelectIndex = startHour.selectedIndex;
    var endHourSelectIndex = endHour.selectedIndex;

    // 空欄を選択する場合、他のセレクトも空欄のものを選択する
    if (jobType.value === '') {
        startHourText.textContent = "";
        endHourText.textContent = "";
        newLastStartHour = 23;
        newLastEndHour = 24;
        itemJob.selectedIndex = new Option('', '');
        startMin.selectedIndex = new Option('', '');
        endMin.selectedIndex = new Option('', '');
        startHour.selectedIndex = new Option('', '');
        endHour.selectedIndex = new Option('', '');
    }

    var jobTypeVal = parseInt(jobType.value);
    var newLastStartHour;
    var newLastEndHour;

    if (jobTypeVal == 00) {
        newLastStartHour = 35;
        newLastEndHour = 36;
        startHourText.textContent = "時";
        endHourText.textContent = "時";
    } else if (jobTypeVal == 01) {
        newLastStartHour = 23;
        newLastEndHour = 24;
        startHourText.textContent = "時間";
        endHourText.textContent = "時間";
    }
    startHour.options.length = 0;
    startHour.options[0] = new Option('', '');

    endHour.options.length = 0;
    endHour.options[0] = new Option('', '');

    for (index = 0; index <= newLastStartHour; index++) {
        startHour.options[index + 1] = new Option(index, index);
    }
    for (index = 0; index <= newLastEndHour; index++) {
        endHour.options[index + 1] = new Option(index, index);
    }
    if (startHour.options.length <= startHourSelectIndex || endHour.options.length <= endHourSelectIndex) {
        startHour.selectedIndex = startHour.options.length - 1;
        endHour.selectedIndex = endHour.options.length - 1;

    } else {
        startHour.selectedIndex = startHourSelectIndex;
        endHour.selectedIndex = endHourSelectIndex;
    }

    // 入っている値に新たに選択したら全てを空欄を選択する
    $(jobType).on('change ', function() {
        itemJob.selectedIndex = new Option('', '');
        startMin.selectedIndex = new Option('', '');
        endMin.selectedIndex = new Option('', '');
        startHour.selectedIndex = new Option('', '');
        endHour.selectedIndex = new Option('', '');
    });

}
// 休憩時間
function SetBreakTime(startHourId, startMinId, endHourId, endMinId, spanTextId, hideTextId) {

    var startHour = document.getElementById(startHourId);
    var startMin = document.getElementById(startMinId);
    var endHour = document.getElementById(endHourId);
    var endMin = document.getElementById(endMinId);
    var spanTextId = document.getElementById(spanTextId);
    var hideTextId = document.getElementById(hideTextId);

    var startHourVal = parseInt(startHour.value);
    var startMinVal = parseInt(startMin.value);
    var endHourVal = parseInt(endHour.value);
    var endMinVal = parseInt(endMin.value);

    // 空を選択したら計算を非表示
    if (startHour.value === '' || startMin.value === '' || endHour.value === '' || endMin.value === '') {
        spanTextId.textContent = "";
        return false;
    }

    var breakTimeMin = ((endHourVal * 60) + endMinVal) - ((startHourVal * 60) + startMinVal);
    // 計算結果がマイナスの数値の場合、
    if (breakTimeMin <= 0) {
        spanTextId.textContent = "";
        return false;
        // 計算が正しい場合
    } else {
        spanTextId.textContent = breakTimeMin + "分";
        spanTextId.value = breakTimeMin;
        hideTextId.value = breakTimeMin;
    }
}

$(function() {
    // 部門検索
    var searchDeptName = function(element, first = false) {
        if (!(element instanceof Element)) {
            element = this;
        }
        var parent = $(element).closest("td");
        var deptCdObj = parent.find("#txtDeptCd");
        var deptNameObj = parent.find("#deptName");
        var deptNameErrorObj = parent.find("#deptNameError");
        var deptCdValidErrorObj = parent.find("#DeptCdValidError");
        var beforeDeptObj = parent.find("#beforeDept");
        // レスポンスの出力先が無い場合、何もせず終了
        if (!deptNameObj.length || !deptNameErrorObj.length) {
            return false;
        }
        var deptCd = deptCdObj.val();
        // 変更なしの場合、再検索しない
        var beforeDeptCd = beforeDeptObj.val();
        // 変更なしの場合、再検索しない
        if (beforeDeptCd && beforeDeptCd == deptCd) {
            return false;
        }
        deptNameErrorObj.empty();
        deptNameObj.prop("disabled", false);
        deptNameObj.val('');
        deptNameObj.prop("disabled", true);

        // 部門コード未入力の場合、初期化のみ検索なし
        if (!deptCd) {
            return false;
        }

        if (!first && deptCdValidErrorObj.length) {
            deptCdValidErrorObj.text('');
        }

        var param = "";
        if (Object.keys(deptNameObj.data()).length) {
            var paramObject = {};
            deptNameObj.data().dispclscd ? paramObject['dispClsCd'] = deptNameObj.data().dispclscd : null;
            deptNameObj.data().isdeptauth ? paramObject['isDeptAuth'] = deptNameObj.data().isdeptauth : null;
            var searchParam = new URLSearchParams(paramObject).toString();
            param = searchParam ? '?' + searchParam : '';
        }
        var url = '/search/MT12DeptSearch/' + deptCd + param;
        $.get(url, function(data) {
            if (beforeDeptObj.length === 0) {
                parent.append("<input type='hidden' id='beforeDept' value=" + deptCd + ">")
            } else {
                beforeDeptObj.val(deptCd);
            }
            if (data.deptName != null) {
                deptNameObj.prop("disabled", false);
                deptNameObj.val(data.deptName);
                deptNameObj.prop("disabled", true);
            } else {
                deptNameErrorObj.append(data.errorMessage);
                if (deptCdValidErrorObj.length && deptNameErrorObj.text() === deptCdValidErrorObj.text()) {
                    deptCdValidErrorObj.text('');
                }
            }
        });
    };
    $('.searchDeptCd').each(function(i, e) {
        searchDeptName(e, true);
    });
    $('.searchDeptCd').focusout(searchDeptName);

    // 社員検索
    var searchEmpName = function(element, first = false) {
        if (!(element instanceof Element)) {
            element = this;
        }
        var parent = $(element).closest("td");
        var empCdObj = parent.find("#txtEmpCd");
        var empNameObj = parent.find("#empName");
        var empCdErrorObj = parent.find("#EmpCdError");
        var empCdValidErrorObj = parent.find("#EmpCdValidError");
        var beforeEmpObj = parent.find("#beforeEmp");
        var deptNameObj = $('#deptNameWithEmp');

        // レスポンスの出力先が無い場合、何もせず終了
        if (!empNameObj.length || !empCdErrorObj.length) {
            return false;
        }
        var empCd = empCdObj.val();
        var beforeEmpCd = beforeEmpObj.val();
        // 変更なしの場合、再検索しない
        if (beforeEmpCd && beforeEmpCd == empCd) {
            return false;
        }

        empCdErrorObj.empty();
        empNameObj.prop("disabled", false);
        empNameObj.val('');
        empNameObj.prop("disabled", true);
        if (deptNameObj.length) {
            deptNameObj.prop("disabled", false);
            deptNameObj.val('');
            deptNameObj.prop("disabled", true);
        }

        // 社員コード未入力の場合、初期化のみ検索なし
        if (!empCd) {
            return false;
        }

        if (!first && empCdValidErrorObj.length) {
            empCdValidErrorObj.text('');
        }

        var param = "";
        if (Object.keys(empNameObj.data()).length) {
            var paramObject = {};
            empNameObj.data().regclscd ? paramObject['regClsCd'] = empNameObj.data().regclscd : null;
            empNameObj.data().isdeptauth ? paramObject['isDeptAuth'] = empNameObj.data().isdeptauth : null;
            empNameObj.data().calendarclscd ? paramObject['calendarClsCd'] = empNameObj.data().calendarclscd : null;
            var searchParam = new URLSearchParams(paramObject).toString();
            param = searchParam ? '?' + searchParam : '';
        }
        var url = '/search/MT10EmpSearch/' + empCd + param;
        $.get(url, function(data) {
            if (beforeEmpObj.length === 0) {
                parent.append("<input type='hidden' id='beforeEmp' value=" + empCd + ">")
            } else {
                beforeEmpObj.val(empCd);
            }
            if (data.empName != null && data.deptName != null) {
                empNameObj.prop("disabled", false);
                empNameObj.val(data.empName);
                empNameObj.prop("disabled", true);
                if (deptNameObj.length) {
                    deptNameObj.prop("disabled", false);
                    deptNameObj.val(data.deptName);
                    deptNameObj.prop("disabled", true);
                }
            } else {
                empCdErrorObj.append(data.errorMessage);
                if (empCdValidErrorObj.length && empCdErrorObj.text() == empCdValidErrorObj.text()) {
                    empCdValidErrorObj.text('');
                }
            }
        });
    };
    $('.searchEmpCd').each(function(i, e) {
        searchEmpName(e, true);
    });
    $('.searchEmpCd').focusout(searchEmpName);


    // 汎用loading
    loading = function() {
        $.LoadingOverlay("show");
        setTimeout(function() {
            $.LoadingOverlay("hide");
        }, 10000);
    }

    ajaxWithLoading = function(arg) {
        var opt = $.extend({ global: false }, $.ajaxSettings, arg);
        var jqXHR = $.ajax(opt);
        var defer = $.Deferred();

        $.LoadingOverlay("show");

        jqXHR.done(function(data, statusText, jqXHR) {
            $.LoadingOverlay("hide");
            defer.resolveWith(this, arguments);
        });

        jqXHR.fail(function(jqXHR, statusText, errorThrown) {
            $.LoadingOverlay("hide");
            if (jqXHR.status == 403) {

            }

            defer.rejectWith(this, arguments);
        });
        jqXHR.always(function() {});

        return $.extend({}, jqXHR, defer.promise());
    };

    $(document).ajaxSend(function() {
        $.LoadingOverlay("show");
    }).ajaxComplete(function() {
        $.LoadingOverlay("hide");
    });
});
