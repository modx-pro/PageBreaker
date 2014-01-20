<?php

$chunks = array();

$tmp = array(
	'tpl.PageBreaker.outer' => 'outer',
	'tpl.PageBreaker.begin' => 'begin',
	'tpl.PageBreaker.next' => 'next',
	'tpl.PageBreaker.prev' => 'prev',
);

foreach ($tmp as $k => $v) {
	/* @avr modChunk $chunk */
	$chunk = $modx->newObject('modChunk');
	$chunk->fromArray(array(
		'id' => 0,
		'name' => $k,
		'description' => '',
		'snippet' => file_get_contents($sources['source_core'].'/elements/chunks/chunk.'.$v.'.tpl'),
		'static' => BUILD_CHUNK_STATIC,
		'source' => 1,
		'static_file' => 'core/components/'.PKG_NAME_LOWER.'/elements/chunks/chunk.'.$v.'.tpl',
	),'',true,true);

	$chunks[] = $chunk;
}

unset($tmp);
return $chunks;