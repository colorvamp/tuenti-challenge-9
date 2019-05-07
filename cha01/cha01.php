#!/usr/bin/php
<?php
	$lines = file('php://stdin');
	$num = trim(array_shift($lines));

	$case = 0;
	while (($line = trim(array_shift($lines)))) {
		$types = explode(' ',$line);
		$tortillas = ceil($types[0] / 2) + ceil($types[1] / 2);
		echo 'Case #'.(++$case).': '.$tortillas.PHP_EOL;
	}
