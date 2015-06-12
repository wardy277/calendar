<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 08/06/15
 * Time: 17:20
 */
class User extends DatabaseEntity{
	protected static $_table = "users";

	public function AddShow($show_id){

		$data = array(
			'user_id' => 1,
			'show_id' => $show_id
		);

		$this->_db->insert('users_shows', $data, true);
	}
}