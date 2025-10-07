<?php
require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$dompdf = new Dompdf();
$dompdf->loadHtml('<h1>DOMPDF está funcionando</h1>');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream();
?>
