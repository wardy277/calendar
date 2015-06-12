<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 08/06/15
 * Time: 16:27
 */
class Session extends DatabaseEntity{
	protected static $_table = "sessions";

	protected $user;

	//todo - impliment this
	protected $_user_groups;

	public static function loadSession(){
		//get php session id
		$session_id = session_id();

		//try and load a current session
		$session = self::loadWhere(array('session_id' => $session_id));

		if(!$session){
			//create one as this is as new user
			$data = array(
				'session_id' => $session_id,
				'date_added' => date('Y-m-d H:i:s')
			);

			$session = Session::create($data);
		}

		return $session;

	}

	public function login($user_id){
		$this->setUserId($user_id);
	}

	public function getUser(){
		if(!$this->user){
			$this->user = User::load($this->getUserId());
		}

		return $this->user;
	}


}