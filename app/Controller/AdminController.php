<?php

App::uses('AppController', 'Controller');
App::uses('Sanitize', 'Utility');

class AdminController extends AppController
{

   public function beforeFilter()
   {
      $this->loadModel('User');
      parent::beforeFilter();
      /*
        //Basic認証　
        $this->autoRender = false;
        $loginId = 'root';
        $loginPassword = 'test';
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
            header('WWW-Authenticate: Basic realm="Please enter your ID and password"');
            header('HTTP/1.0 401 Unauthorized');
            die("id / password Required");
        } else {
            if ($_SERVER['PHP_AUTH_USER'] != $loginId || $_SERVER['PHP_AUTH_PW'] != $loginPassword) {
                header('WWW-Authenticate: Basic realm="Please enter your ID and password"');
                header('HTTP/1.0 401 Unauthorized');
                die("Invalid id / password combination.  Please try again");
                }
            }
            $this->autoRender = true;
        }
        */
   }

   public function index()
   {
      $this->set('title_for_layout', 'スタッフ管理');
      $this->set('staff', $this->User->find('all', array('fields' => array('staff_name', 'mail_address'))));

      if ($this->request->is('post')) {
         //スタッフの削除
         if (isset($this->request->data['del_staff'])) {
            $this->User->delete($this->request->data['del_staff']);
            $this->set('staff', $this->User->find('all', array('fields' => array('staff_name', 'mail_address'))));
         }
      }
   }

   public function syukkinjyokyo()
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

      //ユーザの取得
      if (isset($this->request->query['user'])) {
         $user = $this->request->query['user'];
         $staff_name = $this->User->find('first', array('fields' => 'staff_name', 'conditions' => array('mail_address' => $user)));
         $staff_name = $staff_name['User']['staff_name'];
      }

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


      if ($this->request->is('post')) {
         //承認
         if (isset($this->request->data['app'])) {
            $start = explode(':', $this->request->data['syukkin']);
            $end = explode(':', $this->request->data['taikin']);
            $diff = 0;

            if (isset($start[0], $start[1], $end[0], $end[1])) {
               $diff = abs(((int) $start[0] * 60 + (int) $start[1]) - ((int) $end[0] * 60 + (int) $end[1]));
            }

            $work = array(
               'syukkin' => str_replace(":", "", $this->request->data['syukkin'] . '00'),
               'taikin' => str_replace(":", "", $this->request->data['taikin'] . '00'),
               'recess_time1' => str_replace(":", "", $this->request->data['recess_time1'] . '00'),
               'recess_time2' => str_replace(":", "", $this->request->data['recess_time2'] . '00'),
               'worktime' => sprintf("%02d", $diff / 60) . sprintf("%02d", $diff % 60) . '00',
               'work_contents' => htmlentities($this->request->data['work_contents']),
               'day_name' => $this->request->data['day_state'],
               'approval' => 2
            );

            $this->Work->set($work);
            //バリデーション
            if ($this->Work->validates()) {
               $params = array('conditions' => array('mail_address' => $user, 'date' => $this->request->data['app']));
               if (empty($this->Work->find('first', $params))) { //ログインユーザと今日の日付のレコードが存在しなければ
                  $work = array_merge(array('mail_address' => $user, 'date' => $this->request->data['app']), $work);
                  $this->Work->save($work); //INSERTする
               } else {
                  //サニタイズ処理
                  foreach ($work as $key => $value) {
                     $work[$key] = "'" . Sanitize::escape($value) . "'";
                  }
                  $this->Work->updateAll( //UPDATEする
                     $work,
                     array('mail_address' => $user, 'date' => $this->request->data['app'])
                  );
               }
            } else {
               $this->set('validationDate', explode('-', $this->request->data['approvalBtn'])[2]);
            }
         }

         //非承認
         if (isset($this->request->data['non_app'])) {
            $this->Work->updateAll(
               array('approval' => -1),
               array('mail_address' => $user, 'date' => $this->request->data['non_app'])
            );
         }

         //月末承認
         if (isset($this->request->data['EOMApp'])) {
            $this->Month->updateAll(
               array('end_of_month_app' => 2),
               array('mail_address' => $user, 'date' => $this->request->data['EOMApp'])
            );
         }
         //月末非承認
         if (isset($this->request->data['non_EOMApp'])) {
            $this->Month->updateAll(
               array('end_of_month_app' => -1),
               array('mail_address' => $user, 'date' => $this->request->data['non_EOMApp'])
            );
         }
      }


      $datetime = new HolidayDateTime();

      //日ごとの出退勤内容を取得する
      $works = $this->Work->find('all', array(
         'fields' => array(
            'date', 'syukkin', 'taikin', 'recess_time1', 'recess_time2', 'worktime', 'work_contents', 'day_name', 'approval'
         ),
         'conditions' => array(
            'mail_address' => $user,
            'date between ? and ?' => array($datetime->format($year . $month . '01'), $datetime->format($year . $month . 't'))
         )
      ));

      //日の状態を取得
      $day_state = $this->DaySetting->find('list', array('fields' => array('id', 'day_name')));

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

         //出退勤時間、休憩時間、作業内容、状態、承認状態を格納
         foreach ($works as $value) {
            $d = new Datetime($value['date']);
            if ($i + 1 == $d->format('d')) {
               $date[$i]['work'] = $value;
               $date[$i]['day_name_selected'] = $value['day_name'];
            }
         }
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
         $filename = $user . '_' . $datetime->format('Ym') . '.xlsx';
         $filename = mb_convert_encoding($filename, 'sjis', 'utf-8');
         //出力ファイルパス
         $uploadPath = realpath(TMP) . DS . 'excel' . DS . $filename;

         $sheet->getStyle('L4')->getNumberFormat()->setFormatCode('yyyy/m');
         $sheet->setCellValue('L4', $this->request->data['outputExcel']);
         $sheet->setCellValue('AA4', $staff_name);

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
            $sheet->setCellValue('Q' . $row, isset($d['work']['worktime']) ? $d['work']['worktime'] : '');
            $sheet->setCellValue('S' . $row, isset($d['work']['work_contents']) ? $d['work']['work_contents'] : '');

            $row++;
         }
         $sheet->setCellValue('N43', $this->request->data['workingDays']); //勤務日数
         $sheet->setCellValue('P43', $this->request->data['totalWorktime']); //勤務時間
         $sheet->setCellValue('S43', $this->request->data['basicTime']); //基本時間
         $sheet->setCellValue('T43', $this->request->data['overtime']); //超過時間


         //ダウンロード
         Configure::write('debug', 0);       // debugコードを非表示
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
      $this->set(compact('date', 'day_state', 'user', 'staff_name'));

      //月末承認状態取得
      $this->set('EOMApp', $this->Month->find('first', array(
         'fields' => array('end_of_month_app'),
         'conditions' => array(
            'mail_address' => $user,
            'date' => $datetime->format('Ym')
         )
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

         if ($this->User->save($this->request->data)) {
            return $this->redirect('index');
         }
      }

      //スタッフ管理の編集から遷移した場合
      if (isset($this->request->query['user'])) {
         $staff_name = $this->request->query['user'];
         $data = $this->User->find('first', array(
            'fields' => array('mail_address', 'staff_name', 'daily_approval', 'recess_time1', 'recess_time2', 'base_time1', 'base_time2', 'unused_flag'),
            'conditions' => array('mail_address' => $staff_name)
         ));
         if (!empty($data)) {
            $staff_info = $data['User'];
         }

         //HH:mm:ss形式をHH：mm形式に変換する
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
      $this->set('validation', $this->User->validationErrors);
   }
}
