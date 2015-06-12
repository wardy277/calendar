<?php
include(dirname(__FILE__)."/settings.php");

$data   = array('api_key' => $settings['tvrage_api_key']);
$tvrage = new TvRage($data);

if($_GET['add_show']){
	$tvrage_id = $_GET['add_show'];

	//get show id from tvrage_id
	$show = Show::loadFromTvrage($tvrage_id);

	if(!$show || !$show->getId()){
		//this show is new
		$data = $tvrage->getShow($tvrage_id);
		$show = Show::create($data);

		$show_id = $show->getId();
	}
	else{
		$show_id = $show->getId();
	}

	$session->getUser()->AddShow($show_id);

	//if no episode foudn then need to generate them first
	$show->syncEpisodes();
	exit;
	Url::redirect('/');
}


$table_data = array('class' => 'table');
$table      = new Table($table_data);

foreach($tvrage->searchShows($_GET['search']) as $show){
	$add_show = new Url();
	$add_show->addParam('add_show', $show->getShowid());
	$add_show->setLabel('Add');

	$data = array(
		'tvrage_id' => $show->getShowid(),
		'title'     => $show->getName(),
		'url'       => $show->getLink(),
		'add_show'  => $add_show
	);
	$table->addRow($data);
}

echo $table->buildTable();
