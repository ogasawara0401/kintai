<?php

App::uses('AppModel', 'Model');

class User extends AppModel
{
   public $primaryKey = 'mail_address';

   //入力チェック機能
   public $validate = array(
      'mail_address' => array(
         'rule1' => array(
            'rule' => 'email',
            'message' => '有効なメールアドレスではありません'
         ),
         'rule2' => array(
            'rule' => array('between', 1, 255),
            'message' => 'メールアドレスは255文字以下で入力してください'
         ),
      ),

      'password' => array(
         'rule1' => array(
            'rule' => array('custom', '/^[a-zA-Z0-9]+$/'),
            'message' => 'パスワードは半角英数字で入力してください'
         ),
         'rule2' => array(
            'rule' => array('between', 4, 255),
            'message' => 'パスワードは4文字以上255文字以下で入力して下さい。'
         ),
      ),

      'staff_name' => array(
         'rule1' => array(
            'rule' => array('between', 1, 255),
            'message' => 'スタッフ名は1文字以上255文字以下で入力して下さい。'
         )
      ),

      'recess_time1' => array(
         'rule1' => array(
            'rule' => 'time',
            'message' => 'HH:mm形式で入力してください'
         )
      ),

      'recess_time2' => array(
         'rule1' => array(
            'rule' => 'time',
            'message' => 'HH:mm形式で入力してください'
         )
      ),

      'base_time1' => array(
         'rule1' => array(
            'rule' => 'time',
            'message' => 'HH:mm形式で入力してください'
         )
      ),

      'base_time2' => array(
         'rule1' => array(
            'rule' => 'time',
            'message' => 'HH:mm形式で入力してください'
         )
      ),
   );

   public function beforeSave($options = array())
   {
      if (isset($this->data[$this->alias]['password'])) {
         $passwordHasher = new SimplePasswordHasher(array('hashType' => 'sha1'));
         $this->data[$this->alias]['password'] = $passwordHasher->hash($this->data[$this->alias]['password']);
      }
      return true;
   }

   public function afterFind($result, $primary = false)
   {
      /*
        foreach($result as $key => $val){
            if(isset($val['User'])){
                $result['User'] = $val['User'];
            }
        }
        foreach($result as $key => $val){
            if(isset($val['User'])){
              $result[$key] = $val['User'];
            }
        }*/
      //var_dump($result);
      return $result;
   }
}
