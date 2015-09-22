<?php

/**
 * Created by PhpStorm.
 * User: chris
 * Date: 15/09/15
 * Time: 17:31
 */
interface ApiAbstract{

	public function getUrl($type);

	public function getShows($type = 'all');

	public function getAllShows();

	public function getCurrentShows();

	public function searchShows($search);

	public function getShow($show_id);

	public function getEpisodes($show_id);

}