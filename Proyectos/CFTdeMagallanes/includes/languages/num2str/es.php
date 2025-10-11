<?php
$data = array(
//zero value
		'nul' => 'cero',
		//form 1-9
		'ten' =>
		array(
				array('','uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'),
				array('','uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'),
		),
		//from 10-19
		'a20' =>
		array('diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'),
		//from 20-90
		'tens' =>
		array(2=>'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'),
		//from 100-900
		'hundred' =>
		array('','cien', 'docientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'sietecientos', 'ochocientos', 'nuevecientos'),
		//units
		'unit' =>
		array( // Units
				array('centavo', 'centavos', 'centavos',	 1),
				array('dólar','dólares','dólares'    ,0),
				array('mil', 'miles', 'miles'     ,1),
				array('millón' ,'millones','millones' ,0),
				array('billón', 'billones', 'billones',0),
		)
);