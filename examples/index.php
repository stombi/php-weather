<?php

include('../lib/weather.php');

$weather = new Weather();

echo '<pre>';
print_r($weather->get('SFXX0010'));
echo '</pre>';

/* end of file */