<?php

$settings = array();

$tmp = array(
	'splitter' => array(
		'xtype' => 'textfield',
		'value' => '<!-- pagebreak -->',
	),
	'page_var' => array(
		'xtype' => 'textfield',
		'value' => 'p',
	),
	'frontend_js' => array(
		'xtype' => 'textfield',
		'value' => '[[+assetsUrl]]js/default.js',
	),
	'frontend_css' => array(
		'xtype' => 'textfield',
		'value' => '[[+assetsUrl]]css/default.css',
	),
	'ajax_selector' => array(
		'xtype' => 'textfield',
		'value' => '#pagebreaker_content',
	),
	'ajax' => array(
		'xtype' => 'combo-boolean',
		'value' => false,
	),

);

foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => 'pagebreaker_'.$k,
			'namespace' => PKG_NAME_LOWER,
			'area' => 'pagebreaker_main',
		), $v
	),'',true,true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;
