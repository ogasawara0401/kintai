<?php

App::uses('AppModel', 'Model');

class UserEntry extends AppModel{
	public $useTable = 'users';
	
	//入力チェック機能
	public $validate = array(
		'mail_addr' => array(
			'rule1' => array(
				'rule'       => array('email'),
				'required'   => true,
				'allowEmpty' => false,
				'message'    => 'メールアドレスを入力してください。'
			),
			'rule2' => array(
				'rule' => 'isUnique',
				'message' => '指定されたメールアドレスは既に使われています。'
			),
			'rule3' => array(
				'rule' => array('between', 1, 255),
				'message' => 'メールアドレスは255文字以下で入力してください。'
			),
		),
	
		'nicknm' => array(
			'rule1' => array(
				'rule' => array('custom', '/^[a-zA-Z0-9]+$/'),
				'required'   => true,
				'allowEmpty' => false,
				'message' => 'ニックネームは半角英数字で入力してください。'
			),
			'rule2' => array(
				'rule' => array('between', 5, 255),
				'message' => 'ニックネームは5文字以上255文字以下で入力して下さい。'
			),
			'rule3' => array(
				'rule' => array('custom', '/\A(?=.*?\d)[a-z\d]{1,}+\z/i'),
				'message' => '1文字以上の数字が必要です。'
			),
			'rule4' => array(
				'rule' => array('custom', '/\A(?=.*?[a-z])[a-z\d]{1,}+\z/i'),
				'message' => '1文字以上のアルファベットが必要です。'
			),
			'rule5' => array(
				'rule' => 'isUnique',
				'message' => '指定されたニックネームは既に使われています。'
			),
		),
		
		'pwd' => array(
			'rule1' => array(
				'rule' => array('custom', '/^[a-zA-Z0-9]+$/'),
				'message' => 'パスワードは半角英数字で入力してください。'
			),
			'rule2' => array(
				'rule' => array('between', 8, 255),
				'message' => 'パスワードは8文字以上255文字以下で入力して下さい。'
			),
			'rule3' => array(
				'rule' => array('custom', '/\A(?=.*?\d)[a-z\d]{1,}+\z/i'),
				'message' => '1文字以上の数字が必要です。'
			),
			'rule4' => array(
				'rule' => array('custom', '/\A(?=.*?[a-z])[a-z\d]{1,}+\z/i'),
				'message' => '1文字以上のアルファベットが必要です。'
			),
			'rule5' => array(
				'rule' => array('sameCheck', 'mail_addr'),
				'message' => 'メールアドレスと同じものは使用できません。'
			),
			'rule6' => array(
				'rule' => array('sameCheck', 'nicknm'),
				'message' => 'ニックネームと同じものは使用できません。'
			),
			'rule7' => array(
				'rule' => array('sameCheck', 'birth_ymd'),
				'message' => '誕生日と同じものは使用できません。'
			),
		),

		'sex_kbn' => array(
			'rule1' => array(
				'rule' => array('numeric'),
				'required'   => true,
				'allowEmpty' => false,
				'message' => '選択してください。'
			),
		),
	);
	
	//同一チェック
	public function sameCheck($data, $target){
		return strcmp(array_shift($data), $this->data[$this->name][$target]) != 0;
	}
	
	
	
	public function beforeSave($options = array()){
		$this->data['UserEntry']['pwd'] = AuthComponent::password($this->data['UserEntry']['pwd']);
		return true;
	}
}