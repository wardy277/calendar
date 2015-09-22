<?php
include("settings.php");

//defaults to now
if($_GET['m']){
	$year = $_GET['y']? $_GET['y']: date('Y');
	$date = $year."-".$_GET['m']."-01";
}

$calendar = new Calendar($date);
$today    = new DateTime('', new DateTimeZone($session->getTimezone()));

?>

<?=$calendar->buildNav()?>


<div class="calendar">
	<div class="calendar-header">
		<div class="row seven-cols calendar_title">
			<?php foreach($calendar->getWeekdays() as $weekday){ ?>
				<div class="col-md-1 week-title"><?=$weekday?></div>
			<?php } ?>
		</div>
	</div>

	<div class="calendar_body">
		<div class="row seven-cols week">
			<?php
			foreach($calendar->getDays() as $date){
				//separates out the days of the current month (span and div in order for last-of-type to work)
				if($date->format('m') == $calendar->format('m')){
					$class = "active";
					$tag   = 'div';
				}
				else{
					$class = "";
					$tag   = 'span';
				}

				$day_content = "";
				$day         = new Day($date);
				foreach($day->getTorrents() as $type => $torrent){
					#if($torrent->getTitle() == 'Not Fade Away'){
					#	pre_R($torrent);
					#}
					$day_content .= '<div class="torrent_link">'.$torrent->buildLink()."</div>";
				}


				//today?
				if($date->format('Y-m-d') == $today->format('Y-m-d')){
					$class .= " today";
				}

				echo "<$tag class='col-md-1 day $class'>
						<div class='day-no'>".$date->format('d')."</div>
						$day_content
					  </$tag>";

				if($date->format('w') == $calendar->getWeekEnd() && $date->format('YW') != $calendar->getEndDate()->format('YW') - 1){
					echo "</div><div class='row seven-cols week'>";
				}
			}
			?>
		</div>
	</div>

</div>
