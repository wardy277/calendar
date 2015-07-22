<?php
include(dirname(__FILE__)."/settings.php");

//delete on request
if($_GET['delete_show']){
	$id = $_GET['delete_show'];

	$sql = $db->build("DELETE FROM users_shows
						WHERE user_id = '?'
						AND show_id = '?'",
		$session->getUser()->getId(), $id);
	$db->query($sql);
}

//only shows from users
$sql = $db->build("SELECT s.*
					FROM tv_shows s
					JOIN users_shows u ON u.show_id = s.id
					JOIN episode_list e ON e.show_id = s.id
					WHERE u.user_id = '?'
					GROUP BY s.id
					ORDER BY MAX(IF(e.aired_date < NOW(), e.aired_date, 0)) DESC
					", $session->getUser()->getId()
);


foreach($db->getArray($sql) as $row){
	$show = Show::load($row['id'], $row);

	$delete_show = new Url();
	$delete_show->addParam('delete_show', $show->getId());
	$delete_show->setLabel('<span class="glyphicon glyphicon-trash pull-right" aria-hidden="true"></span>');
	$delete_show->setClass('confirm');

	?>

	<div class="col-md-4">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title">
					<?=$show->getTitle()?>
					<?=$delete_show->buildAnchor()?>
				</h3>
			</div>
			<div class="panel-body show_block">
				<img src="<?=$show->getImage()?>"/>
			</div>
		</div>
	</div>

<?
}

