<?php

App::uses('AppModel', 'Model');

class Work extends AppModel
{
   //public $primaryKey = array('mail_address', 'date');

   public $validate = array(
      'syukkin' => array(
         'rule' => array('customTime'),
         'message' => 'HH:MM形式で入力してください'
      ),
      'taikin' => array(
         'rule' => array('customTime'),
         'message' => 'HH:MM形式で入力してください'
      ),
      'recess_time1' => array(
         'rule' => array('customTime'),
         'message' => 'HH:MM形式で入力してください'
      ),
      'recess_time2' => array(
         'rule' => array('customTime'),
         'message' => 'HH:MM形式で入力してください'
      ),
      'work_contents' => array(
         'rule' => array('maxLength', 200),
         'message' => '200字以内で入力して下さい。'
      ),
   );

   public function customTime($check)
   {
      //時 00-29 分 00-59 秒 00
      $pattern = '/(0[0-9]{1}|1{1}[0-9]{1}|2{1}[0-9]{1})' . '(0[0-9]{1}|[1-5]{1}[0-9]{1})' . '(00)/';

      $value = array_values($check);
      $value = $value[0];
      if (!preg_match($pattern, $value)) {
         return false;
      }
      return true;
   }

   public function afterFind($result, $primary = false)
   {
      foreach ($result as $key => $val) {
         if (isset($val['Work'])) {
            $result[$key] = $val['Work'];
         }
      }

      //秒の部分を除去する
      $target = array('syukkin', 'taikin', 'recess_time1', 'recess_time2', 'worktime');
      foreach ($result as $key => $value) {
         foreach ($value as $k => $val) {
            if (in_array($k, $target) && isset($val)) {
               $time = explode(':', $val);
               $result[$key][$k] = $time[0] . ':' . $time[1];
            }
         }
      }

      return $result;
   }
}
