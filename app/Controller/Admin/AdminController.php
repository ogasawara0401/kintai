<?php
App::uses('AppController', 'Controller');

class AdminController extends AppController
{
   public $components = array('Security');

   public function beforeFilter()
   {
      parent::beforeFilter();

      $this->autoRender = true;
      $this->loadModel('User');
   }

   public function index()
   {
      $this->set('title_for_layout', 'スタッフ管理');
      $this->set('staff', $this->User->find('all', array('fields' => array('staff_name', 'mail_address'))));
   }

   public function syukkinjyokyo()
   {
      //タイトル
      $this->set('title_for_layout', '出勤状況');
      //DaySettingモデルを使う
      $this->loadModel('DaySetting');
      //祝日判定クラスの追加
      App::import('Vendor', 'HolidayDateTime');

      //時間と曜日を取得
      $datetime = new HolidayDateTime();
      $year = $datetime->format('Y');
      $month = $datetime->format('m');
      $day = $datetime->format('Ymd');
      if (isset($this->request->data['time'])) {
         $time = $this->request->data['time'];
         $tmp_y = substr($time, 0, 4);   //年を取得
         $tmp_m = substr($time, 4, 2);   //月を取得
         //システム日付以降の月は取得しない
         if ($time <= $datetime->format('Ym')) {
            $year = $tmp_y;
            $month = $tmp_m;
         }
         //2016年1月以前は取得しない
         if ($time <= 201601) {
            $year = 2016;
            $month = sprintf('%02d', 1);
         }
      }
      $weekList = array("日", "月", "火", "水", "木", "金", "土");
      $datetime->setDate($year, $month, 1);
      $date_count = $datetime->format('t');
      $this->set('lastmonth', $datetime->modify('-1 months')->format('Ym'));
      $this->set('nextmonth', $datetime->modify('+2 months')->format('Ym'));

      $date = array('year', 'month', 'day', 'week', 'day_name');
      $day_state = $this->DaySetting->find('list', array('fields' => 'DaySetting.day_name'));

      for ($i = 0; $i < $date_count; $i++) {
         $datetime->setDate($year, $month, $i + 1);
         $week = $weekList[(int) $datetime->format('w')];
         $day_name = '平日';

         if ($week == '土' || $week == '日') {
            $day_name = '休日';
         }
         if ($datetime->holiday()) {
            $day_name = '祝日';
         }

         $date[$i] = array(
            'year' => $year,
            'month' => $month,
            'day' => $i + 1,
            'week' => $weekList[(int) $datetime->format('w')],
            'day_name' => $day_name
         );
      }

      $this->set('today', $day);
      $this->set('date', $date);
      $this->set('day_state', $this->DaySetting->find('list', array(
         'fields' => array('DaySetting.day_name', 'DaySetting.day_name')
      )));
   }

   public function stafftoroku()
   {
      $this->set('title_for_layout', 'スタッフ登録');
      //ラジオボタン作成用
      $this->set('daily', array('0' => '無し', '1' => '有り'));
      $this->set('unused_flag', array('0' => '使用中', '1' => '未使用'));

      //登録ボタンが押されたら
      if (isset($this->request->data['toroku'])) {
         unset($this->request->data['toroku']);

         $staff_info = $this->request->data;

         $this->User->save($this->request->data);
      }

      //スタッフ管理の編集から遷移した場合
      if (isset($this->request->data['mail_address'])) {
         $staff_name = $this->request->data['mail_address'];
         $data = $this->User->find('first', array('conditions' => array('mail_address' => $staff_name)));
         if (!empty($data)) {
            $staff_info = $data;
         }

         //HH:mm:ss形式をHH：mm形式に変換する]
         if (
            !empty($staff_info['recess_time1']) && !empty($staff_info['recess_time2'])
            && !empty($staff_info['base_time1']) && !empty($staff_info['base_time1'])
         ) {
            $staff_info['recess_time1'] = substr($staff_info['recess_time1'], 0, 2) . ':' . substr($staff_info['recess_time1'], 3, 2);
            $staff_info['recess_time2'] = substr($staff_info['recess_time2'], 0, 2) . ':' . substr($staff_info['recess_time2'], 3, 2);
            $staff_info['base_time1'] = substr($staff_info['base_time1'], 0, 2) . ':' . substr($staff_info['base_time1'], 3, 2);
            $staff_info['base_time2'] = substr($staff_info['base_time2'], 0, 2) . ':' . substr($staff_info['base_time2'], 3, 2);
         }
      } else {
         //初期値
         $staff_info['daily_approval'] = 0;
         $staff_info['recess_time1'] = '01:00';
         $staff_info['recess_time2'] = '00:45';
         $staff_info['base_time1'] = '12:00';
         $staff_info['base_time2'] = '19:00';
         $staff_info['unused_flag'] = 1;
      }

      $this->set('staff_info', $staff_info);
      $this->set('val', $this->User->validationErrors);
   }
}
