<!--{html func=css path='stafftoroku.css'}-->
<!--{html func=script url='stafftoroku.js'}-->
<div class="container" id="container">
	<div class="row">
		<div class="col-lg-12">
			<h3>スタッフ登録</h3>
		</div>
	</div>
	<hr>

	<div class="row">
		<div class="col-sm-2 col-sm-offset-7 col-xs-offset-6">
			<button class="btn" onclick="location.href='/admin/index'">管理画面へ戻る</button>
		</div>
	</div>

	<div class="row">
		<form class="form-horizontal" method="post">
			<div class="form-group <!--{if isset($validation['staff_name'])}--> has-error <!--{/if}-->">
				<label class="col-sm-2 control-label">スタッフ名</label>
				<div class="col-sm-3">
					<input class="form-control" name="staff_name" value="<!--{$staff_info['staff_name']}-->">
				</div>
				<!--{if isset($validation['staff_name'])}-->
				<span class="help-block"><!--{$validation['staff_name'][0]}--></span>
				<!--{/if}-->
			</div>
			<div class="form-group <!--{if isset($validation['mail_address'])}--> has-error <!--{/if}-->">
				<label class="col-sm-2 control-label">ログインID</label>
				<div class="col-sm-3">
					<input class="form-control" name="mail_address" value="<!--{$staff_info['mail_address']}-->" <!--{if isset($staff_info['mail_address'])}-->readonly="readonly"<!--{/if}-->>
				</div>
				<!--{if isset($validation['mail_address'])}-->
				<span id="helpBlock2" class="help-block"><!--{$validation['mail_address'][0]}--></span>
				<!--{/if}-->
			</div>
			<div class="form-group <!--{if isset($validation['password'])}--> has-error <!--{/if}-->">
				<label class="col-sm-2 control-label">パスワード</label>
				<div class="col-sm-3">
					<input class="form-control" name="password" type="password">
				</div>
				<!--{if isset($validation['password'])}-->
				<span class="help-block"><!--{$validation['password'][0]}--></span>
				<!--{/if}-->
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">日別承認有無</label>
				<div class="col-sm-3" id="daily">
					<!--{html_radios name=daily_approval options=$daily selected=$staff_info['daily_approval'] label_ids=true}-->
				</div>
			</div>
			<div class="form-group <!--{if isset($validation['recess_time1'])}--> has-error <!--{/if}-->">
				<label class="col-sm-2 control-label">休憩時間①</label>
				<div class="col-sm-2">
					<input class="form-control" name="recess_time1" value="<!--{$staff_info['recess_time1']}-->">
				</div>
				<!--{if isset($validation['recess_time1'])}-->
				<span class="help-block"><!--{$validation['recess_time1'][0]}--></span>
				<!--{/if}-->
			</div>
			<div class="form-group <!--{if isset($validation['recess_time2'])}--> has-error <!--{/if}-->">
				<label class="col-sm-2 control-label">休憩時間②</label>
				<div class="col-sm-2">
					<input class="form-control" name="recess_time2" value="<!--{$staff_info['recess_time2']}-->">
				</div>
				<!--{if isset($validation['recess_time2'])}-->
				<span class="help-block"><!--{$validation['recess_time2'][0]}--></span>
				<!--{/if}-->
			</div>
			<div class="form-group <!--{if isset($validation['base_time1'])}--> has-error <!--{/if}-->">
				<label class="col-sm-2 control-label">基準時刻①</label>
				<div class="col-sm-2">
					<input class="form-control" name="base_time1" value="<!--{$staff_info['base_time1']}-->">
				</div>
				<!--{if isset($validation['base_time1'])}-->
				<span class="help-block"><!--{$validation['base_time1'][0]}--></span>
				<!--{/if}-->
			</div>
			<div class="form-group <!--{if isset($validation['base_time2'])}--> has-error <!--{/if}-->">
				<label class="col-sm-2 control-label">基準時刻②</label>
				<div class="col-sm-2">
					<input class="form-control" name="base_time2" value="<!--{$staff_info['base_time2']}-->">
				</div>
				<!--{if isset($validation['base_time2'])}-->
				<span class="help-block"><!--{$validation['base_time2'][0]}--></span>
				<!--{/if}-->
			</div>
			<div class="form-group">
				<label class="col-sm-2 control-label">未使用フラグ</label>
				<div class="col-sm-3" id="UnusedFlag">
					<!--{html_radios name=unused_flag options=$unused_flag selected=$staff_info['unused_flag'] label_ids=true}-->
				</div>
			</div>
			<div class="col-sm-2 col-sm-offset-7">
				<button type="submit" class="btn btn-primary" name="toroku" onclick="return confirm('登録しますか？')">登録</button>
			</div>
		</form>
	</div>
</div>
