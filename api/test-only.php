<?php
$date = new DateTime();
$timeZone = $date->getTimezone();
echo $timeZone->getName();

echo date("c");
echo '<br>';
$selectedTime = date("c");
$endTime = strtotime("+60 minutes", strtotime($selectedTime));
echo date('c', $endTime);
echo '<br>';