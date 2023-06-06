$(document).ready(function () {
   var time = new Date();
   var year = time.getFullYear();
   var month = ('0' + parseInt(time.getMonth() + 1)).slice(-2);
   var day = time.getDate();

   var y = $('#year').text();
   var m = $('#month').text();

   if (y == year && m == month) {
      $('#nextMonth').prop('disabled', true);
   }
   if (y == 2016 && m == 01) {
      $('#lastMonth').prop('disabled', true);
   }

   //その月の日数
   var days = new Date(parseInt(y, 10), parseInt(m, 10), 0).getDate();

   var totalWorktime = 0; //月の勤務時間
   var workingDays = 0; //月の勤務日数
   var basicTimeCount = 0; //月の基本時間

   for (var i = 1; i <= days; i++) {
      var appId = 'app_' + i;

      setWorkTime(i);

      //勤務日数
      if ($('#worktime_' + i).text() != '00 : 00') {
         workingDays++;

         var time = $('#worktime_' + i).text().split(':');
         totalWorktime += parseInt(time[0].trim()) * 60 + parseInt(time[1].trim());
      }

      //基本時間の計算
      if ($('#day_state_' + i).val() == 1) { //平日のみカウントする
         basicTimeCount++;
      }

      //承認状態を設定する
      if ($('#' + appId).val() == -1 || $('#' + appId).val() == 0 || $('#' + appId).val() == 2) {
         $('#date_' + i).find(':input').attr('disabled', true);
      }

      //承認状態が入力中または承認依頼中なら月末承認ボタンを非活性化する
      if ($('#' + appId).val() === '-1' || $('#' + appId).val() === '0' || $('#' + appId).val() === '1') {
         $('#EOMApp').attr('disabled', true);
      }
   }

   //月の勤務日数
   $('#workingDays').val(workingDays);
   //月の合計勤務時間
   $('#totalWorktime').val(('00' + Math.floor(totalWorktime / 60)).slice(-3) + ' : ' + ('0' + totalWorktime % 60).slice(-2));
   //月の基本時間を計算する
   $('#basicTime').val(basicTimeCount * 8);
   //月の超過時間を計算する
   var overtime = basicTimeCount * 8 * 60 - totalWorktime;
   if (overtime < 0) {
      $('#overtime').val(('0' + Math.floor(Math.abs(overtime) / 60)).slice(-2) + ' : ' + ('0' + Math.abs(overtime) % 60).slice(-2));
   } else {
      $('#overtime').val('00 : 00');
   }

   //月末承認依頼中または月末承認済なら
   if ($('#EOMAppState').data('state') == 2) {
      $('#date_' + i).find(':input').attr('disabled', true);
      $('#EOMApp').attr('disabled', true);
   }
});

function setWorkTime(day) {
   if ($('#syukkin_' + day).val() && $('#taikin_' + day).val()) {
      var syukkinTime = $('#syukkin_' + day).val().split(':');
      var taikinTime = $('#taikin_' + day).val().split(':');
      var recessTime1 = $('#recess_time1_' + day).val().split(':');
      var recessTime2 = $('#recess_time2_' + day).val().split(':');

      //実務時間の計算
      var diff = Math.abs((60 * parseInt(syukkinTime[0]) + parseInt(syukkinTime[1])) - (60 * parseInt(taikinTime[0]) + parseInt(taikinTime[1])));
      var hh = 0;
      var mm = 0;

      //休憩時間
      if (recessTime1 != '' && recessTime2 != '') {
         diff = diff - (parseInt(recessTime1[0]) + parseInt(recessTime2[0])) * 60;
         diff = diff - (parseInt(recessTime1[1]) + parseInt(recessTime2[1]));
      }

      hh = Math.floor(diff / 60);
      mm = diff % 60;
   }

   //実務時間を設定する
   if ($.isNumeric(hh) && $.isNumeric(mm) && diff > 0) {
      $('#worktime_' + day).html(('0' + hh).slice(-2) + ' : ' + ('0' + mm).slice(-2));
   } else {
      $('#worktime_' + day).html('00 : 00');
   }
}
