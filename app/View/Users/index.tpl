<!--{html func=css path='syuttaikin.css'}-->
<!--{html func=script url='syuttaikin.js'}-->
<div class="container-fluid">
   <div class="row">
      <div class="col-lg-12">
         <h3>出退勤管理</h3>
      </div>
      <div class="col-xs-offset-9 col-sm-offset-10">
         <button class="btn btn-link" onclick="location.href='Users/logout'">ログアウト</button>
      </div>
   </div>
   <hr>

   <div class="row" id="header">
      <div class="col-sm-3 col-sm-offset-1">
         <div class="btn-inline">
            <form method="get">
               <button type="submit" class="btn btn-primary btn-sm" id="lastMonth" name="date" value="<!--{$lastmonth}-->">◀</button>
               <span id="year">
                  <!--{$date[0]['year']}--></span>年<span id="month">
                  <!--{$date[0]['month']}--></span>月
               <button type="submit" class="btn btn-primary btn-sm" id="nextMonth" name="date" value="<!--{$nextmonth}-->">▶</button>
            </form>
         </div>
      </div>
      <div class="col-sm-5 col-sm-offset-3">
         <div class="row">
            <form method="post">
               <div class="col-xs-4">
                  <button type="submit" class="btn btn-primary btn-block" id="syukkin" name="syukkinBtn" disabled="" value="">出勤</button>
               </div>
               <div class="col-xs-4">
                  <button type="submit" class="btn btn-primary btn-block" id="taikin" name="taikinBtn" disabled="" value="">退勤</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <div class="table-responsive">
      <table class="table table--extend table-bordered table-condensed table-hover" id="content1">
         <thead>
            <tr>
               <th class="day">日</th>
               <th class="week">曜日</th>
               <th class="syukkinjikan">出勤時間</th>
               <th class="taikinjikan">退勤時間</th>
               <th class="kyukeijikan1">休憩時間①</th>
               <th class="kyukeijikan2">休憩時間②</th>
               <th class="jitsumujikan">実務時間</th>
               <th class="sagyonaiyo">作業内容</th>
               <th class="state">状態</th>
               <th class="syonin">承認</th>
            </tr>
         </thead>
         <tbody>
            <!--{section name=date loop=$date}-->
            <form method="post" name="syuttaikin">
               <!--{if $today == "`$date[date]['year']``$date[date]['month']``$date[date]['day']|string_format:"%02d"`"}-->
               <tr class="info" id="date_<!--{$date[date]['day']}-->">
                  <!--{elseif $date[date]['day_name'] == '休日' || $date[date]['day_name'] == '祝日'}-->
               <tr class="holiday" id="date_<!--{$date[date]['day']}-->">
                  <!--{else}-->
               <tr id="date_<!--{$date[date]['day']}-->">
                  <!--{/if}-->
                  <td>
                     <!--{$date[date]['day']}-->
                  </td>
                  <td>
                     <!--{$date[date]['week']}-->
                  </td>
                  <td>
                     <div <!--{if $validationDate==$date[date]['day']&&isset($this->validationErrors['Work']['syukkin'][0])}-->class="form-group has-error"
                        <!--{/if}-->>
                        <!--{if $validationDate==$date[date]['day']}--><label class="control-label">
                           <!--{$this->validationErrors['Work']['syukkin'][0]}--></label>
                        <!--{/if}-->
                        <input class="form-control" id="syukkin_<!--{$date[date]['day']}-->" name="syukkin" value="<!--{$date[date]['work']['syukkin']}-->" onchange="setWorkTime(<!--{$date[date]['day']}-->);">
                     </div>
                  </td>
                  <td>
                     <div <!--{if $validationDate==$date[date]['day']&&isset($this->validationErrors['Work']['taikin'][0])}-->class="form-group has-error"
                        <!--{/if}-->>
                        <!--{if $validationDate==$date[date]['day']}--><label class="control-label">
                           <!--{$this->validationErrors['Work']['taikin'][0]}--></label>
                        <!--{/if}-->
                        <input class="form-control" id="taikin_<!--{$date[date]['day']}-->" name="taikin" value="<!--{$date[date]['work']['taikin']}-->" onchange="setWorkTime(<!--{$date[date]['day']}-->);">
                     </div>
                  </td>
                  <td>
                     <div <!--{if $validationDate==$date[date]['day']&&isset($this->validationErrors['Work']['recess_time1'][0])}-->class="form-group has-error"
                        <!--{/if}-->>
                        <!--{if $validationDate==$date[date]['day']}--><label class="control-label">
                           <!--{$this->validationErrors['Work']['recess_time1'][0]}--></label>
                        <!--{/if}-->
                        <input class="form-control" id="recess_time1_<!--{$date[date]['day']}-->" name="recess_time1" value="<!--{$date[date]['work']['recess_time1']}-->" onchange="setWorkTime(<!--{$date[date]['day']}-->);">
                     </div>
                  </td>
                  <td>
                     <div <!--{if $validationDate==$date[date]['day']&&isset($this->validationErrors['Work']['recess_time2'][0])}-->class="form-group has-error"
                        <!--{/if}-->>
                        <!--{if $validationDate==$date[date]['day']}--><label class="control-label">
                           <!--{$this->validationErrors['Work']['recess_time2'][0]}--></label>
                        <!--{/if}-->
                        <input class="form-control" id="recess_time2_<!--{$date[date]['day']}-->" name="recess_time2" value="<!--{$date[date]['work']['recess_time2']}-->" onchange="setWorkTime(<!--{$date[date]['day']}-->);">
                     </div>
                  </td>
                  <td id="worktime_<!--{$date[date]['day']}-->"></td>
                  <input type="hidden"  name="worktime" id="ip_worktime_<!--{$date[date]['day']}-->" value="<!--{$date[date]['work']['worktime']}-->">
                  <td><input class="form-control" name="work_contents" value="<!--{$date[date]['work']['work_contents']}-->"></td>
                  <td><input type="hidden">
                     <!--{html_options name=day_state id="day_state_`$date[date]['day']`" options=$day_state selected=$date[date]['day_name_selected']}-->
                  </td>
                  <td>
                     <input type="hidden" id="app_<!--{$date[date]['day']}-->" value="<!--{$date[date]['work']['approval']}-->">
                     <!--{if $date[date]['work']['approval'] === '1'}-->
                     <div>
                        <button type="submit" class="btn change" name="change_app" value="<!--{$date[date]['day']}-->" onclick='return confirm("変更しますか？")'>変更</button>
                        <span>承認依頼中</span>
                     </div>
                     <!--{elseif $date[date]['work']['approval'] === '2'}-->
                     <span>承認済</span>
                     <!--{else}-->
                     <button type="submit" class="btn app" name="approvalBtn" value="<!--{" `$date[date]['year']`-`$date[date]['month']|string_format:"%02d"`-`$date[date]['day']|string_format:"%02d"`"}-->" onclick='return confirm("承認しますか？")'>承認</button>
                     <!--{if $date[date]['work']['approval'] === '-1'}--><span class="text-danger">差戻し</span>
                     <!--{elseif $date[date]['work']['approval'] === '0'}--><span>入力中</span>
                     <!--{/if}-->
                     <!--{/if}-->
                  </td>
               </tr>
            </form>
            <!--{/section}-->
         </tbody>
      </table>

      <form method="post" id="total">
         <table class="table table-bordered table-condensed" id="content2">
            <tbody>
               <tr>
                  <td rowspan="2" class="total">合計</td>
                  <td class="working-day">勤務日数</td>
                  <td class="working-time">勤務時間</td>
                  <!--<td class="latenight-time">深夜時間</td>-->
                  <td class="basic-time">基本時間</td>
                  <td class="overtime">超過時間</td>
                  <td rowspan="2" class="EOM-app-state">月末承認状態</td>
                  <td rowspan="2" class="state"><span id="EOMAppState" data-state="<!--{$EOMApp['Month']['end_of_month_app']}-->">
                        <!--{if $EOMApp['Month']['end_of_month_app']==-1}-->差戻し
                        <!--{elseif $EOMApp['Month']['end_of_month_app']==0}-->入力中
                        <!--{elseif $EOMApp['Month']['end_of_month_app']==1}-->承認依頼中
                        <!--{elseif $EOMApp['Month']['end_of_month_app']==2}-->承認済
                        <!--{/if}--></span>
                  </td>
               </tr>
               <tr>
                  <td><input type="text" name="workingDays" id="workingDays" class="noborder" readonly="readonly"></td>
                  <td><input type="text" name="totalWorktime" id="totalWorktime" class="noborder" readonly="readonly"></td>
                  <!--<td></td>-->
                  <td><input type="text" name="basicTime" id="basicTime" class="noborder" readonly="readonly"></td>
                  <td><input type="text" name="overtime" id="overtime" class="noborder" readonly="readonly"></td>
               </tr>
            </tbody>
         </table>
      </form>
   </div>

   <div class="row" id="footer">
      <form method="post">
         <!--{if $EOMApp['Month']['end_of_month_app'] == 1}-->
         <button type="submit" class="btn btn-primary" form="total" name="change_EOMApp" id="EOMApp_request" value="<!--{" `$date[0]['year']``$date[0]['month']`"}-->">月末承認取消</button>
         <!--{else}-->
         <button type="submit" class="btn btn-primary" form="total" name="EOMApp" id="EOMApp" value="<!--{" `$date[0]['year']``$date[0]['month']`"}-->">月末承認</button>
         <!--{/if}-->
         <button type="submit" class="btn btn-primary" form="total" name="outputExcel" value="<!--{" `$date[0]['year']`/`$date[0]['month']`"}-->">Excel出力</button>
      </form>
   </div>
</div>