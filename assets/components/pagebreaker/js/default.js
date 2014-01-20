PageBreaker = {
	initialize: function(selector) {
		$(document).on('click', selector + ' a.pb_link', function() {
			var base = $('base'), base_url;
			var href = $(this).prop('href');
			if (base) {
				base_url = base.prop('href');
				href = base_url + href.replace(base_url, '');
			}

			if (!PageBreaker.Hash.oldbrowser()) {
				PageBreaker.Hash.set('', href);
			}
			else {
				var page = 0;
				var pcre = new RegExp(pbConfig.prefix + '(\\d)+' + pbConfig.extension);
				var tmp = href.match(pcre);
				if (tmp) {page = tmp[1];}
				tmp = PageBreaker.Hash.get();
				if (page) {tmp[pbConfig.prefix] = page;}
				else {delete tmp[pbConfig.prefix];}
				PageBreaker.Hash.set(tmp);
			}

			$.post(href, {pb_action: 'PageBreaker'}, function(response) {
				$(selector).html(response);
			});

			return false;
		});
	}
};

PageBreaker.Hash = {
	get: function() {
		var vars = {}, hash, splitter, hashes;
		if (!this.oldbrowser()) {
			var pos = window.location.href.indexOf('?');
			hashes = (pos != -1) ? decodeURIComponent(window.location.href.substr(pos + 1)) : '';
			splitter = '&';
		}
		else {
			hashes = decodeURIComponent(window.location.hash.substr(1));
			splitter = '/';
		}

		if (hashes.length == 0) {return vars;}
		else {hashes = hashes.split(splitter);}

		for (var i in hashes) {
			if (hashes.hasOwnProperty(i)) {
				hash = hashes[i].split('=');
				if (typeof hash[1] == 'undefined') {
					vars['anchor'] = hash[0];
				}
				else {
					vars[hash[0]] = hash[1];
				}
			}
		}
		return vars;
	}
	,set: function(vars, path) {
		var hash = '';
		for (var i in vars) {
			if (vars.hasOwnProperty(i)) {
				hash += '&' + i + '=' + vars[i];
			}
		}

		if (!this.oldbrowser()) {
			if (hash.length != 0) {
				hash = '?' + hash.substr(1);
			}
			if (!path) {
				path = document.location.pathname;
			}
			window.history.pushState(hash, '', path + hash);
		}
		else {
			window.location.hash = hash.substr(1);
		}
	}
	,add: function(key, val) {
		var hash = this.get();
		hash[key] = val;
		this.set(hash);
	}
	,remove: function(key) {
		var hash = this.get();
		delete hash[key];
		this.set(hash);
	}
	,clear: function() {
		this.set({});
	}
	,oldbrowser: function() {
		return !(window.history && history.pushState);
	}
};

if (window.location.hash != '' && PageBreaker.Hash.oldbrowser()) {
	var uri = window.location.hash.replace('#', '?');
	window.location.href = document.location.pathname + uri;
}

if (typeof pbConfig != 'undefined') {
	$(document).ready(function() {
		PageBreaker.initialize(pbConfig.selector);
	});
}