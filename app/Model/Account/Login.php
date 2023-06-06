<?php

App::uses('AppModel', 'Model');

class Login extends AppModel{
	public $useTable = 'users';
	
	//入力チェック機能
	public $validate = array(
		'mail_addr' => array(
			'rule1' => array(
				'rule' => array('email'),
				'message' => '有効なメールアドレスではありません。'
			),
			'rule2' => array(
				'rule' => array('between', 1, 255),
				'message' => 'メールアドレスは255文字以下で入力してください。'
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
		),
	);
}