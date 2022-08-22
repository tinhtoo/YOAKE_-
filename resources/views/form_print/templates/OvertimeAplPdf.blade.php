<?php use Carbon\CarbonPeriod; ?>
<html lang="ja">
    <head>
        <title>印刷</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            @font-face{
                font-family: "MS Pゴシック";
                font-style: normal;
                font-weight: normal;
                src: url("{{ public_path('fonts/migmix-2p-regular.ttf')}}") format('truetype');
            }
            @page { 
                margin: 30px 15px 30px 15px;
            }
            body {
                font-family: "MS Pゴシック";
                line-height: 80%;
                text-align: center;
                font-size: 10px;
            }
            tbody {
                border: 1px solid black;
            }
            .workTable {
                border-collapse: collapse;
                width: 100%;
            }
            .workTable tr th{
                height: 30px;
                border: 1px solid black;
                text-align: left;
                font-size: 11px;
            }
            .workTable tr td{
                height: 26px;
                border-bottom: 1px black;
                border-top: 1px black;
                border-left: 1px black solid;
                border-right: 1px black solid;
                text-align: right;
                border-top-style: dotted;
            }
            .date{
                padding-bottom: 20px;
                position: relative;
                margin-left:70%;
                font-size: xx-small;
            }
            .record{
                padding-bottom: 10px;
                text-align:justify;
                font-size: xx-small;
                position: relative;
            }

        </style>
    </head>
    @foreach ($overtime_datas->unique('EMP_CD') as $overtime_data)
    <body>
        <table class="workTable">
            <thead>
                <tr>
                    <td colspan="7" style="border: none; height:2px; padding-right: 30px; text-align: right;">
                        作表日：{{ date('Y/m/d', strtotime($now_date)) }}
                    </td>
                    <td style="border: none; width:10px; height:2px; text-align: left;">
                        {{ $loop->iteration }} / {{ $loop->count }}
                    </td>
                </tr>
                <tr>
                    <td colspan="8" style="border: none; height:2px; font-size:large; text-align: center;">
                        残業申請書
                    </td>
                </tr>
                <tr>
                    <td colspan="8" style="border: none; height:3px; text-align:left;">
                        対象月度 : {{ $target_date }}&nbsp;&nbsp;月度
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="border: none; padding-left: 22px; text-align: left;">
                        社員 : {{ $overtime_data['EMP_CD'] }}&nbsp;&nbsp;{{ $overtime_data['EMP_NAME'] }}
                    </td>
                    <td colspan="4" style="border: none; padding-left: 70px; text-align: left;">
                        部門 : {{ $overtime_data['DEPT_CD'] }}&nbsp;&nbsp;{{ $overtime_data['DEPT_NAME'] }}
                    </td>
                </tr>
                <tr>
                    <th style="text-align: center; width:30px;">日付</th>
                    <th style="text-align: center; width:80px;">開始時間 ～ 終了時間</th>
                    <th style="text-align: center; width:20px;">普通</th>
                    <th style="text-align: center; width:20px;">深夜</th>
                    <th style="text-align: center; width:20px;">休出</th>
                    <th style="text-align: center; width:20px;">休深</th>
                    <th style="text-align: center; width:110px;">残業内容</th>
                    <th style="text-align: center; width:20px;">承認</th>
                </tr>
            </thead>
            <tbody >
                @foreach ($period as $i => $day)
                <tr>
                    <td style="padding-right: 3px;">
                    @if ($i == 0)
                    {{ (int)$day->format('m') }}月
                    @elseif ($day->format('d') === "01")
                    {{ (int)$day->format('m') }}月&nbsp;
                    @else
                    &nbsp;&nbsp;
                    @endif
                    &nbsp;&nbsp;
                    {{ (int)$day->format('d') }} ({{ config('consts.weeks')[date('w', strtotime($day))] }})
                    </td>
                    <td style="text-align: center;">～</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endforeach
                @for ($i; $i < 30; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endfor
                <tr>
                    <td></td>
                    <td style="text-align: center;">合計</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </body>
    @endforeach
</html>