<?php

switch ($modx->event->name) {


	case 'OnManagerPageBeforeRender':
		/** @var modManagerController $controller */
		$controller->addLexiconTopic('pagebreaker:default');
		break;


	case 'OnPageNotFound':
		if (!$modx->getOption('friendly_urls')) {return;}

		$prefix = $modx->getOption('pagebreaker_page_var', null, 'p', true);
		$extension = $modx->getObject('modContentType', array('mime_type' => 'text/html'))->file_extensions;
		$container_suffix = $modx->getOption('container_suffix', null, '/', true);
		$word_delimiter = $modx->getOption('friendly_alias_word_delimiter', null, '-', true);

		$page = '';
		$q = $modx->getOption('request_param_alias', null, true, 'q');
		if (preg_match('#'. $prefix . '(\d+)' . $extension . '$#', $_REQUEST[$q], $matches)) {
			$page = $matches[1];
		}
		else {
			return;
		}

		$uri = preg_replace('#' . $prefix . $page . $extension . '$#', '', $_REQUEST[$q]);
		$uri = rtrim($uri, $word_delimiter);
		if (substr($uri, -1, 1) != $container_suffix) {
			$uri .= $extension;
		}

		if ($id = $modx->findResource($uri, $modx->context->key)) {
			$_REQUEST[$prefix] = $page;
			$modx->sendForward($id);
		}
		break;


	case 'OnLoadWebDocument':
		$splitter = $modx->getOption('pagebreaker_splitter', null, '<!-- pagebreak -->', true);
		$prefix = $modx->getOption('pagebreaker_page_var', null, 'p', true);
		$container_suffix = $modx->getOption('container_suffix', null, '/', true);
		$word_delimiter = $modx->getOption('friendly_alias_word_delimiter', null, '-', true);

		$fromCache = false;
		if (!$raw = $modx->resource->get('raw_content')) {
			if (strpos($modx->resource->content, $splitter) === false) {return;}
			$modx->resource->set('raw_content', $modx->resource->content);
			$raw = $modx->resource->content;
		}
		else {
			$fromCache = true;
		}

		/** @var PageBreaker $PageBreaker */
		$PageBreaker = $modx->getService('pagebreaker','PageBreaker', MODX_CORE_PATH . 'components/pagebreaker/model/pagebreaker/');
		$PageBreaker->initialize();
		$tplOuter = $modx->getOption('pagebreaker.tplOuter', null, 'tpl.PageBreaker.outer', true);
		$tplNext = $modx->getOption('pagebreaker.tplNext', null, 'tpl.PageBreaker.next', true);
		$tplPrev = $modx->getOption('pagebreaker.tplPrev', null, 'tpl.PageBreaker.prev', true);
		$tplBegin = $modx->getOption('pagebreaker.tplBegin', null, 'tpl.PageBreaker.begin', true);

		$content = explode($splitter, $raw);
		$count = count($content);
		$current = isset($_REQUEST[$prefix])
			? $_REQUEST[$prefix]
			: 1;
		if ($current > $count || $current < 1) {
			$current = 1;
		}

		$extension = $modx->getObject('modContentType', array('mime_type' => 'text/html'))->file_extensions;
		$url = $modx->context->makeUrl($modx->resource->id);
		$uri = str_replace($extension, '', $url);
		$isfolder = substr($uri, -1, 1) == $container_suffix || $modx->resource->isfolder;

		$prev = $current - 1;
		$next = $current + 1;

		if ($modx->getOption('friendly_urls')) {
			if ($prev == 0) {
				$link_prev = '';
			}
			elseif ($prev == 1) {
				$link_prev = $url;
			}
			else {
				$link_prev = $isfolder
					? $uri . $prefix . $prev . $extension
					: $uri . $word_delimiter . $prefix . $prev . $extension;
			}
			$link_next = ($next > $count)
				? ''
				: ($isfolder
					? $uri . $prefix . $next . $extension
					: $uri . $word_delimiter . $prefix . $next . $extension
				);
			$link_begin =  $modx->context->makeUrl($modx->resource->id);

			$params = $_GET;
			$q = $modx->getOption('request_param_alias', null, true, 'q');
			unset($params[$q]);
			if (!empty($params)) {
				if (!empty($link_prev)) {$link_prev .= '?' . http_build_query($params);}
				if (!empty($link_next)) {$link_next .= '?' . http_build_query($params);}
				$link_begin .= '?' . http_build_query($params);
			}
		}
		else {
			$params = $_GET;
			$id = $modx->getOption('request_param_id', null, 'id', true);
			unset($params[$id], $params[$prefix]);

			if ($prev == 0) {
				$link_prev = '';
			}
			else {
				if ($prev > 1) {
					$params[$prefix] = $prev;
				}
				$link_prev = $modx->context->makeUrl($modx->resource->id, $params);
			}

			unset($params[$prefix]);
			if ($next > $count) {
				$link_next = '';
			}
			else {
				$params[$prefix] = $next;
				$link_next = $modx->context->makeUrl($modx->resource->id, $params);
			}
			unset($params[$prefix]);
			$link_begin =  $modx->context->makeUrl($modx->resource->id, $params);
		}


		$navigation = $modx->getChunk($tplOuter, array(
			'pb_link_prev' => !empty($link_prev) ? $modx->getChunk($tplPrev, array('link' => $link_prev)) : '',
			'pb_link_next' => !empty($link_next) ? $modx->getChunk($tplNext, array('link' => $link_next)) : '',
			'pb_link_begin' => $modx->getChunk($tplBegin, array('link' => $link_begin)),
			'pb_page' => $current,
			'pb_total' => $count
		));

		$content = preg_replace('#^</p>#', '', $content);
		$content = preg_replace('#<p>$#', '', $content);
		$content = $content[$current - 1];
		$content .= '<!-- pb_nav -->' . $navigation . '<!--/ pb_nav -->';

		if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' && !empty($_POST['pb_action'])) {
			if ($_POST['pb_action'] == 'PageBreaker') {
				$modx->getParser()->processElementTags('', $content, true, false, '[[', ']]', array(), 10);
				$modx->getParser()->processElementTags('', $content, true, true, '[[', ']]', array(), 10);
				@session_write_close();
				exit($content);
			}
		}
		elseif ($fromCache) {
			$tmp1 = preg_replace('#<\!-- pb_nav -->.*?<\!--/ pb_nav -->#s', '', $modx->resource->_content);
			$tmp2 = preg_replace('#<\!-- pb_nav -->.*?<\!--/ pb_nav -->#s', '', $modx->resource->content);
			$modx->resource->_content = str_replace($tmp2, $content, $tmp1);
		}
		else {
			$modx->resource->content = $content;
		}
		break;
}