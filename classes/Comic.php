<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 15/12/15
 * Time: 15:39
 */
class Comic extends Kickass {

	public function buildLink(){
		//date is in american format as vertigo is american
		$week = $this->getDate()->format('m-d-Y');
		$search = "DC Week $week";
		$url = $this->buildSearch($search);

		return "<a href='$url' title='$week' target='_blank'>DC You</a>";
	}
}