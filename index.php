<?php
include("settings.php");

//defaults to now
if($_GET['m']){
	$year = $_GET['y']? $_GET['y']: date('Y');
	$date = $year."-".$_GET['m']."-01";
}

$calendar = new Calendar($date);

?>

<div class="calendar">
	<?=$calendar->buildNav()?>


	<div class="row seven-cols">
		<?php foreach($calendar->getWeekdays() as $weekday){ ?>
			<div class="col-md-1 week-title"><?=$weekday?></div>
		<?php } ?>
	</div>


	<div class="row seven-cols week">
		<?php
		foreach($calendar->getDays() as $date){
			if($date->format('m') == $calendar->format('m')){
				$class = "active";
			}
			else{
				$class = "";
			}
			?>

			<div class="col-md-1 day <?=$class?>">
				<?=$date->format('d')?>
			</div>

			<?php
			if($date->format('w') == $calendar->getWeekEnd() && $date->format('YW') != $calendar->getEndDate()->format('YW')-1){
				echo "</div><div class='row seven-cols week'>";
			}
		}
		?>
	</div>
</div>