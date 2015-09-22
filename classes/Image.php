<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 22/06/15
 * Time: 16:20
 */
class Image{

	protected $im;

	public function __construct($image_src){

		$this->im = new Imagick($image_src);
	}

	public function cropImage($width, $height, $x, $y){
		$this->im->cropImage($width, $height, $x, $y);
	}

	public function centerThumbnail($width, $height){
		//get x and y
		$x = ($this->im->getImageWidth() - $width) / 2;
		$y = ($this->im->getImageHeight() - $height) / 2;

		$this->cropImage(($width, $height, $x, $y);
	}

	public function getHeaders(){
		//clean the output
		while(@ob_end_clean()){
			;
		}

		header("Content-Type: image/jpg");
	}

	public function output(){
		$this->getHeaders();
		echo $this->im->getImageBlob();
	}
}