<?php

if ($object->xpdo) {
	/* @var modX $modx */
	$modx =& $object->xpdo;
	$tiny = MODX_ASSETS_PATH . 'components/tinymce/jscripts/tiny_mce/plugins/';
	$typo = MODX_ASSETS_PATH . 'components/typomce/jscripts/tiny_mce/plugins/';
	$source = MODX_CORE_PATH . 'components/pagebreaker/editor_plugin/';

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
		case xPDOTransport::ACTION_UPGRADE:
			foreach (array($tiny, $typo) as $v) {
				if (file_exists($v)) {
					if (!file_exists($v . 'pagebreaker/')) {
						mkdir($v . 'pagebreaker/');
					}
					copy($source . 'editor_plugin.js', $v . 'pagebreaker/' . 'editor_plugin.js');
					copy($source . 'editor_plugin_src.js', $v . 'pagebreaker/' . 'editor_plugin_src.js');
				}
			}
			/** @var modSystemSetting $setting */
			if ($setting = $modx->getObject('modSystemSetting', 'typo.custom_buttons3')) {
				$tmp = array_map('trim', explode(',', $setting->get('value')));
				foreach (array('pagebreak','pagebreakmanual','pagebreakauto','pagebreakcls') as $v) {
					if (!in_array($v, $tmp)) {
						if (empty($tmp[0])) {
							$tmp = array($v);
						}
						else {
							$tmp[] = $v;
						}
					}
					$setting->set('value', implode(',', $tmp));
					$setting->save();
				}
			}
			/** @var modSystemSetting $setting */
			if ($setting = $modx->getObject('modSystemSetting', 'typo.custom_plugins')) {
				$tmp = array_map('trim', explode(',', $setting->get('value')));
				if (!in_array('pagebreaker', $tmp)) {
					$tmp[] = 'pagebreaker';
					$setting->set('value', implode(',', $tmp));
					$setting->save();
				}
			}
		break;

		case xPDOTransport::ACTION_UNINSTALL:
			foreach (array($tiny, $typo) as $v) {
				if (file_exists($v)) {
					unlink($v . 'pagebreaker/' . 'editor_plugin.js');
					unlink($v . 'pagebreaker/' . 'editor_plugin_src.js');
					rmdir($v . 'pagebreaker/');
				}
			}
			/** @var modSystemSetting $setting */
			if ($setting = $modx->getObject('modSystemSetting', 'typo.custom_buttons3')) {
				$tmp = array_map('trim', explode(',', $setting->get('value')));
				foreach (array('pagebreak','pagebreakmanual','pagebreakauto','pagebreakcls') as $v) {
					$key = array_search($v, $tmp);
					if ($key !== false) {
						unset($tmp[$key]);
					}
					$setting->set('value', implode(',', $tmp));
					$setting->save();
				}
			}
			/** @var modSystemSetting $setting */
			if ($setting = $modx->getObject('modSystemSetting', 'typo.custom_plugins')) {
				$tmp = array_map('trim', explode(',', $setting->get('value')));
				$key = array_search('pagebreaker', $tmp);
				if ($key !== false) {
					unset($tmp[$key]);
				}
				$setting->set('value', implode(',', $tmp));
				$setting->save();
			}
			break;
	}
}
return true;
