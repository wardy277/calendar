<pre>
<?php
error_reporting(1);
ini_set("display_errors", 1);

//weeks start on a monday
$week_start = 1;

//calculated
$week_start_name = date('l', strtotime("Sunday +{$week_start} days"));
$week_end = $week_start==0?6:$week_start-1;
$week_end_name = date('l', strtotime("Sunday +{$week_end} days"));


//default to now
if($_GET['m']){
	$year = $_GET['y']? $_GET['y']: date('Y');

	$date         = $year."-".$_GET['m']."-01";
	$current_date = new DateTime($date);
}
if (!$current_date) {
    $current_date = new DateTime('first day of this month');
}

//nav
$d = clone($current_date);
$d->modify('last month');
$month = $d->format('m');
$year = $d->format('y');

if ($year != $current_date->format('y')) {
    $url = "?m=$month&y=$year";
} else {
    $url = "?m=$month";
}

echo "<a href='$url'><</a>";


$d = clone($current_date);
$d->modify('next month');
$month = $d->format('m');
$year = $d->format('y');

if ($year != $current_date->format('y')) {
    $url = "?m=$month&y=$year";
} else {
    $url = "?m=$month";
}

echo "      " . $current_date->format('M') . "      ";
echo "<a href='$url'>></a>\n";



//gap before start
$month_start_day = $current_date->format('w')==0?7:$current_date->format('w');

$start_date = clone($current_date);
if($start_date->format('w') != $week_start){
    $start_date->modify('last '.$week_start_name);
}

//build end date
$end_date = clone($current_date);
$end_date->modify('last day of this month');
if($end_date->format('w') != $week_end){
    $end_date->modify('next '.$week_end_name);
}

//build range
$interval = new DateInterval('P1D');
$daterange = new DatePeriod($start_date, $interval ,$end_date);

foreach($daterange as $date){
    $day = $date->format('d');
    echo "[$day]";

     //new line on sundays
    if ($date->format('w') == 0) {
        echo "\n";
    }
}
