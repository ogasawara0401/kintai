<!--{html func=css path='staffkanri.css'}-->
<div class="container">
   <div class="row">
      <div class="col-lg-12">
         <h3>スタッフ管理</h3>
      </div>
   </div>
   <hr>

   <div class="row" id="header">
      <div class="col-md-2 col-md-offset-10 col-xs-offset-7">
         <button type="button" class="btn btn-primary" id="addStaff" onClick="location.href='/admin/stafftoroku'">スタッフ追加</button>
      </div>
   </div>

   <div class="table-responsive">
      <table class="table table-bordered table-condensed table-hover table-striped">
         <thead>
            <tr>
               <th class="staffname">スタッフ名</th>
               <th class="loginid">ログインID</th>
               <th class="syukkinstate"></th>
               <th class="edit"></th>
               <th class="delete"></th>
            </tr>
         </thead>
         <tbody>
            <!--{section name=staff loop=$staff}-->
            <tr>
               <td>
                  <!--{$staff[staff]['User']['staff_name']}-->
               </td>
               <td>
                  <!--{$staff[staff]['User']['mail_address']}-->
               </td>
               <form method="get" action="/admin/syukkinjyokyo">
                  <td><button type="submit" class="btn" name="user" value="<!--{$staff[staff]['User']['mail_address']}-->">出勤状況</button></td>
               </form>
               <form method="get" action="/admin/stafftoroku">
                  <td><button type="submit" class="btn" name="user" value="<!--{$staff[staff]['User']['mail_address']}-->">編集</button></td>
               </form>
               <form method="post">
                  <td><button type="submit" class="btn" name="del_staff" value="<!--{$staff[staff]['User']['mail_address']}-->" onclick='return confirm("削除しますか？")'>削除</button></td>
               </form>
            </tr>
            <!--{/section}-->
         </tbody>
      </table>
   </div>
</div>