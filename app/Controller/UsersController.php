<?php

App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');

class UsersController extends AppController
{
   public $components = array(
      'Session',
      'Cookie',
      'Auth' => array(
         //ログイン後の遷移先
         'loginRedirect' => array('controller' => 'Users', 'action' => 'index'),
         //ログアウト後の遷移先
         'logoutRedirect' => array('controller' => 'Users', 'action' => 'login'),
         'authenticate' => array(
            'Form' => array(
               'userModel' => 'User',
               'fields' => array(
                  'username' => 'mail_address',
                  'password' => 'password'
               ),
               'scope' => array('unused_flag' => 0)
            )
         )
      )
   );

   public function beforeFilter()
   {
      $this->Auth->allow('login');
   }

   public function index()
   {
      //タイトル
      $this->set('title_for_layout', '出退勤管理');
      //DaySettingモデルを使う
      $this->loadModel('DaySetting');
      //Workモデルを使う
      $this->loadModel('Work');
      //Monthモデルを使う
      $this->loadModel('Month');
      //祝日判定クラスの追加
      App::import('Vendor', 'HolidayDateTime');

      //時間と曜日を取得
      $datetime = new DateTime();
      $year = $datetime->format('Y');
      $month = $datetime->format('m');
      $day = $datetime->format('Ymd');

      //翌月と先月を取得
      $lastmonth = new Datetime($year . $month . '01');
      $nextmonth = new Datetime($year . $month . '01');
      $lastmonth->modify('-1 months');
      $nextmonth->modify('+1 months');

      if (isset($this->request->query['date'])) {
         $date = $this->request->query['date'];
         $y = substr($date, 0, 4);   //年を取得
         $m = substr($date, 4, 2);   //月を取得
         $lastmonth->setDate($y, $m, '01')->modify('-1 months');
         $nextmonth->setDate($y, $m, '01')->modify('+1 months');

         //システム日付以降の月は設定しない
         if ($date < $datetime->format('Ym')) {
            $year = $y;
            $month = $m;
         } else {
            $nextmonth->modify('-1 months');
         }
         //2016年1月以前は設定しない
         if ($date <= 201601) {
            $year = 2016;
            $month = sprintf('%02d', 1);
            $lastmonth->modify('+1 months');
         }
      }

      //先月、翌月を設定する
      $this->set('lastmonth', $lastmonth->format('Ym'));
      $this->set('nextmonth', $nextmonth->format('Ym'));

      //曜日リスト
      $weekList = array("日", "月", "火", "水", "木", "金", "土");
      $datetime->setDate($year, $month, 1);
      $date_count = $datetime->format('t');

      $date = array('year', 'month', 'day', 'week', 'day_name');
      $day_state = $this->DaySetting->find('list', array('fields' => 'day_name'));


      //日の状態を取得
      $day_state = $this->DaySetting->find('list', array('fields' => array('id', 'day_name')));


      $datetime = new HolidayDateTime();

      //日別に内容をセットする
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
            'day_name' => $day_name,
            'day_name_selected' => array_search($day_name, $day_state),
            'work' => array()
         );

         //日ごとの出退勤内容を取得する
         $works = $this->Work->find('all', array(
            'fields' => array(
               'date', 'syukkin', 'taikin', 'recess_time1', 'recess_time2', 'worktime', 'work_contents', 'day_name', 'approval'
            ),
            'conditions' => array(
               'mail_address' => $this->Auth->user()['mail_address'],
               'date between ? and ?' => array($datetime->format($year . $month . '01'), $datetime->format($year . $month . 't'))
            )
         ));


         //出退勤時間、休憩時間、作業内容、状態、承認状態を格納
         foreach ($works as $value) {
            $d = new Datetime($value['date']);
            if ($i + 1 == $d->format('d')) {
               $date[$i]['work'] = $value;
               $date[$i]['day_name_selected'] = $value['day_name'];
            }
         }
      }

      if ($this->request->is('post')) {
         //出勤時間をWorkテーブルにINSERTまたはUPDATEする
         if (isset($this->request->data['syukkinBtn'])) {
            $datetime = new Datetime();

            $work = array(
               'mail_address' => $this->Auth->user()['mail_address'],
               'date' => $datetime->format('Y-m-d'),
               'syukkin' => $datetime->format('Hi00'),
               'day_name' => $date[$datetime->format('d')]['day_name_selected'],
               'approval' => 0
            );

            $params = array('conditions' => array('mail_address' => $work['mail_address'], 'date' => $work['date']));
            if (empty($this->Work->find('first', $params))) { //ログインユーザと今日の日付のレコードが存在しなければ
               $this->Work->save($work); //INSERTする
            } else {
               $this->Work->updateAll( //UPDATEする
                  array('syukkin' => $work['syukkin']),
                  array('mail_address' => $work['mail_address'], 'date' => $work['date'])
               );
            }
         }

         //退勤時間をUPDATEする
         if (isset($this->request->data['taikinBtn'])) {
            $datetime = new DateTimeImmutable();

            //基準時刻を超えたかどうか
            $basetime = $this->User->find('first', array(
               'fields' => array('base_time1', 'base_time2', 'recess_time1', 'recess_time2'),
               'conditions' => array('mail_address' => $this->Auth->user()['mail_address'])
            ));
            $basetime1 = explode(':', $basetime['User']['base_time1']);
            $basetime2 = explode(':', $basetime['User']['base_time2']);

            //退勤時間が24時以降の場合、前日のレコードの退勤時間をUPDATEする
            if (0 < (int) $datetime->format('H') && (int) $datetime->format('H') < 6) {
               $this->Work->updateAll( //UPDATEする
                  array(
                     'taikin' => ((int) $datetime->format('H') + 24) . $datetime->format('i00'),
                     'recess_time1' => ($basetime1[0] <= $datetime->format('H')) ? str_replace(":", "", $basetime['User']['recess_time1']) : "000000",
                     'recess_time2' => ($basetime2[0] <= $datetime->format('H')) ? str_replace(":", "", $basetime['User']['recess_time2']) : "000000",
                  ),
                  array('mail_address' => $this->Auth->user()['mail_address'], 'date' => $datetime->modify('-1 days')->format('Y-m-d'))
               );
            } else {
               $this->Work->updateAll( //UPDATEする
                  array(
                     'taikin' => $datetime->format('Hi00'),
                     'recess_time1' => ($basetime1[0] <= $datetime->format('H')) ? str_replace(":", "", $basetime['User']['recess_time1']) : "000000",
                     'recess_time2' => ($basetime2[0] <= $datetime->format('H')) ? str_replace(":", "", $basetime['User']['recess_time2']) : "000000",
                  ),
                  array('mail_address' => $this->Auth->user()['mail_address'], 'date' => $datetime->format('Y-m-d'))
               );
            }
         }

         //承認依頼
         if (isset($this->request->data['approvalBtn'])) {

            $work_contents = htmlentities($this->request->data['work_contents']);

            $work = array(
               'syukkin' => str_replace(":", "", $this->request->data['syukkin'] . '00'),
               'taikin' => str_replace(":", "", $this->request->data['taikin'] . '00'),
               'recess_time1' => str_replace(":", "", $this->request->data['recess_time1'] . '00'),
               'recess_time2' => str_replace(":", "", $this->request->data['recess_time2'] . '00'),
               'worktime' => str_replace(":", "", str_replace(" ", "", $this->request->data['worktime']) . '00'),
               'work_contents' => "'{$work_contents}'",
               'day_name' => $this->request->data['day_state'],
               'approval' => 1
            );

            $this->Work->set($work);
            //バリデーション
            if ($this->Work->validates()) {
               $params = array('conditions' => array('mail_address' => $this->Auth->user()['mail_address'], 'date' => $this->request->data['approvalBtn']));
               if (empty($this->Work->find('first', $params))) { //ログインユーザと今日の日付のレコードが存在しなければ
                  $work = array_merge(array('mail_address' => $this->Auth->user()['mail_address'], 'date' => $this->request->data['approvalBtn']), $work);
                  $this->Work->save($work); //INSERTする
               } else {
                  $this->Work->updateAll( //UPDATEする
                     $work,
                     array('mail_address' => $this->Auth->user()['mail_address'], 'date' => $this->request->data['approvalBtn'])
                  );
               }

               //メール送信
               //$email = new CakeEmail('kintai');
               //$email->from(array('sample123@sample.com' => 'Sender'));
               //$email->to('kintai@sample.com');
               //$email->subject('勤怠管理承認依頼');
               //$email->send('承認依頼');
            } else {
               $this->set('validationDate', explode('-', $this->request->data['approvalBtn'])[2]);
            }

            //$this->redirect($this->request->referer());
         }

         //承認依頼中から入力中に変更する
         if (isset($this->request->data['change_app'])) {
            $datetime = new Datetime();
            if (isset($this->request->query['date'])) {
               $datetime = new Datetime($this->request->query['date'] . "01");
            }

            $this->Work->updateAll(
               array('approval' => 0),
               array('mail_address' => $this->Auth->user()['mail_address'], 'date' => $datetime->format('Y-m-') . $this->request->data['change_app'])
            );
         }
      }

      //月末承認
      if (isset($this->request->data['EOMApp'])) {
         $total = array(
            'mail_address' => $this->Auth->user()['mail_address'],
            'date' => $this->request->data['EOMApp'],
            'working_days' => $this->request->data['workingDays'],
            'total_worktime' => str_replace(' : ', '', $this->request->data['totalWorktime']) . '00',
            'overtime' => str_replace(' : ', '', $this->request->data['overtime']) . '00',
            'end_of_month_app' => 1
         );

         $params = array('conditions' => array('mail_address' => $this->Auth->user()['mail_address'], 'date' => $this->request->data['EOMApp']));
         if (empty($this->Month->find('first', $params))) { //ログインユーザと今月のレコードが存在しなければ
            $this->Month->save($total);
         } else {
            //サニタイズ処理
            foreach ($total as $key => $value) {
               $total[$key] = "'" . $value . "'";
            }
            $this->Month->updateAll(
               $total,
               array('mail_address' => $this->Auth->user()['mail_address'], 'date' => $this->request->data['EOMApp'])
            );
         }
      }

      //月末承認取消
      if (isset($this->request->data['change_EOMApp'])) {
         $this->Month->updateAll(
            array('end_of_month_app' => 0),
            array('mail_address' => $this->Auth->user()['mail_address'], 'date' => $this->request->data['change_EOMApp'])
         );
      }

      //Excelを出力しダウンロードする
      if (isset($this->request->data['outputExcel'])) {
         // Excel出力用ライブラリ
         App::import('Vendor', 'PHPExcel', array('file' => 'phpsexcel' . DS . 'PHPExcel.php'));
         App::import('Vendor', 'PHPExcel_IOFactory', array('file' => 'phpexcel' . DS . 'PHPExcel' . DS . 'IOFactory.php'));
         App::import('Vendor', 'PHPExcel_Cell_AdvancedValueBinder', array('file' => 'phpexcel' . DS . 'PHPExcel' . DS . 'Cell' . DS . 'AdvancedValueBinder.php'));

         //テンプレートファイルの読み込み
         $tmpExcelPath = realpath(TMP) . DS . 'excel' . DS;
         $objReader = PHPExcel_IOFactory::createReader('Excel2007');
         $tmpPath = $tmpExcelPath . 'roster_tmp.xlsx';
         $PHPExcel = $objReader->load($tmpPath);
         //シートの設定
         $PHPExcel->setActiveSheetIndex(0);
         $sheet = $PHPExcel->getActiveSheet();
         $sheet->setTitle('勤務表');
         //Excel出力ファイル名
         $filename = $this->Auth->user()['mail_address'] . '_' . $datetime->format('Ym') . '.xlsx';
         $filename = mb_convert_encoding($filename, 'sjis', 'utf-8');
         //出力ファイルパス
         $uploadPath = realpath(TMP) . DS . 'excel' . DS . $filename;

         $sheet->getStyle('L4')->getNumberFormat()->setFormatCode('yyyy/m');
         $sheet->setCellValue('L4', $this->request->data['outputExcel']);
         $sheet->setCellValue('AA4', $this->Auth->user()['staff_name']);

         //書き込み処理
         $row = 11;
         foreach ($date as $d) {
            if ($d['day_name_selected'] == '2' || $d['day_name_selected'] == '3') {
               $sheet->setCellValue('J' . $row, 1);
               $sheet->getStyle('L' . $row . ':AF' . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('bfbfbf');
            }
            $sheet->setCellValue('L' . $row, $d['day']);
            $sheet->setCellValue('M' . $row, $d['week']);
            $sheet->setCellValue('N' . $row, isset($d['work']['syukkin']) ? $d['work']['syukkin'] : '');
            $sheet->setCellValue('O' . $row, isset($d['work']['taikin']) ? $d['work']['taikin'] : '');
            $sheet->setCellValue('P' . $row, isset($d['work']['taikin']) ? $d['work']['taikin'] : '');
            $sheet->setCellValue('Q' . $row, isset($d['work']['worktime']) ? $d['work']['worktime'] : '');
            $sheet->setCellValue('S' . $row, isset($d['work']['work_contents']) ? $d['work']['work_contents'] : '');

            $row++;
         }
         $sheet->setCellValue('N43', $this->request->data['workingDays']); //勤務日数
         $sheet->setCellValue('P43', $this->request->data['totalWorktime']); //勤務時間
         $sheet->setCellValue('S43', $this->request->data['basicTime']); //基本時間
         $sheet->setCellValue('T43', $this->request->data['overtime']); //超過時間


         //ダウンロード
         header('Content-Type: application/octet-stream');
         ob_end_clean();
         header('Content-Disposition: attachment;filename="' . $filename . '"');
         header('Cache-Control: max-age=0');
         //xlsx形式で保存
         $objWriter = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
         $objWriter->save('php://output');
         exit;
      }

      $this->set('today', $day);
      $this->set('date', $date);
      $this->set('day_state', $day_state);

      //月末承認状態取得
      $this->set('EOMApp', $this->Month->find('first', array(
         'fields' => array('end_of_month_app'),
         'conditions' => array(
            'mail_address' => $this->Auth->user()['mail_address'],
            'date' => $datetime->format('Ym')
         )
      )));
   }

   public function login()
   {
      //タイトル
      $this->set('title_for_layout', 'ログイン');

      //ログインボタン
      if ($this->request->is('post')) {
         $this->Session->destroy();

         $this->request->data['User'] = array(
            'mail_address' => $this->request->data['mail_address'],
            'password' => $this->request->data['password']
         );

         //ログイン成功ならログインする
         if ($this->Auth->login()) {
            if ($this->request->data['autoLogin']) {
               unset($this->request->data['autoLogin']);
               $cookie = $this->request->data;
               $this->Cookie->write('Auth', $cookie, true, '+2 weeks');
            }
            unset($this->request->data);
            return $this->redirect($this->Auth->redirectUrl());
         } else {
            $this->Session->destroy();
            return false;
         }
      }

      //cookieがあるなら
      if ($this->Cookie->check('Auth')) {
         $this->request->data = $this->Cookie->read('Auth');
         //自動ログイン
         if ($this->Auth->login()) {
            return $this->redirect($this->Auth->redirectUrl());
         } else {
            $this->Cookie->delete('Auth');
         }
      }
   }

   public function logout()
   {
      $this->Cookie->delete('Auth');
      return $this->redirect($this->Auth->logout());
   }
}
