<?php

define('MODX_API_MODE', true);
require dirname(dirname(dirname(dirname(__FILE__)))).'/index.php';

$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);

$html = $_POST['html'];
$action = $_POST['action'];
$splitter = $modx->getOption('pagebreaker_splitter', null, '<!-- pagebreak -->', true);
$max_char = 2000;

$arr = array($splitter, '<p></p>', '<p>&nbsp;</p>');
$html = str_replace($arr, '', $html); // Убираем все pagebreake и пустые параграфы из текста

if ($action == 'clear') {
	$output = $html;
}
else {
	$max = (!empty($_POST['num'])) ?
		(int) $_POST['num']
		: $max_char;

	$content = explode('<p', $html); // Делим по параграфам
	$html2 = '';
	$max2 = 0;
	
	// Разбивка идёт по параграфам, если сумма символов в параграфах больше $max - ставится $splitter
	foreach ($content as $p) {
		$p = trim($p);
	    if (!empty($p)) {
			$cur = '';
			$cur = '<p' . $p;
			$len = mb_strlen(strip_tags($cur));
			
			$max2 = $max2 + $len;
			$html2 .= $cur;
				
			if ($max2 >= $max) {
				$html2 .= $splitter;
				$max2 = 0;
			}
		}
	}
	$output = preg_replace('/'.$splitter.'$/', '', $html2);
}

echo $modx->toJSON(array(
	'success' => true,
	'message' => '',
	'object' => $output,
	'data' => array(),
));