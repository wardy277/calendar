<?php

/**
 * Created by PhpStorm.
 * User: ico3
 * Date: 29/05/15
 * Time: 15:46
 */
class Url extends Entity{

	private $params;

	public function __construct($url = false){
		if(!$url){
			$url = $_SERVER['REQUEST_URI'];
		}

		$parsed = parse_url($url);

		$params = parse_str($parsed['query']);
		$data   = array(
			'params' => $params,
			'path'   => $parsed['path']
		);

		parent::__construct($data);
	}

	public function addParam($field, $value){
		$this->_data['params'][ $field ] = $value;
	}

	public function buildAnchor($label=false){
		$class = "";

		if(!$label){
			if($this->getButton()){
				$label = $this->getButton();
				$class = "btn btn-info ";
			}
			else{
				$label = $this->getLabel();
			}

			if(!$label){
				$label = 'Link';
			}
		}

		$class .= $this->getClass();

		$url = $this->getPath().'?'.http_build_query($this->getParams());

		return "<a href='$url' class='$class'>$label</a>";
	}

	public function redirect($url){
		header("Location: $url");
		exit;
	}
}