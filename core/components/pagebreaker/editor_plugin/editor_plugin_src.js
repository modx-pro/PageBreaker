
/**
 * $Id: editor_plugin_src.js 201 2007-02-12 15:56:56Z spocke $
 *
 * @author Moxiecode
 * @copyright Copyright © 2004-2008, Moxiecode Systems AB, All rights reserved.
 */

(function() {
	var path = MODx.config.assets_url + 'components/pagebreaker/';

	tinymce.create('tinymce.plugins.PageBreakerPlugin', {
		init : function(ed, url) {
			var pb = '<img src="' + path + 'img/mgr/trans.gif" class="mcePageBreaker mceItemNoResize" />'
				,cls = 'mcePageBreaker'
				,sep = ed.getParam('pagebreaker_separator', MODx.config.pagebreaker_splitter)
				,pbRE = new RegExp(sep.replace(/[\?\.\*\[\]\(\)\{\}\+\^\$\:]/g, function(a) {return '\\' + a;}), 'g');

			// Register commands
			ed.addCommand('mcePageBreaker', function() {
				ed.execCommand('mceInsertContent', 0, pb);
			});

			ed.addCommand('mcePageBreakerManual', function() {
				var w = Ext.getCmp('pb-editor-window');
				if (w) {w.hide().getEl().remove();}

				w = new MODx.Window({
					width: 400
					,height: 150
					,title: _('pb_editor_manual')
					,labelAlign: 'left'
					,labelWidth: 150
					,id: 'pb-editor-window'
					,url: path + 'processor.php'
					,baseParams: {
						action: 'break',
						html: tinymce.get('ta').getContent()
					}
					,fields:[{
						xtype: 'textfield'
						,fieldLabel: _('pb_editor_symbols')
						,labelStyle: 'line-height: 35px;'
						,id: 'pb_editor_symbols'
						,name: 'num'
						,value: 2500
					}]
					,success: function(form, response) {
						ed.setContent(response.result.object);
					}
				});
				w.show();
			});

			ed.addCommand('mcePageBreakerAuto', function() {
				Ext.Msg.wait(_('saving'), _('please_wait'));
				MODx.Ajax.request({
					url: path + 'processor.php'
					,method: 'POST'
					,params: {
						action: 'break'
						,html: tinymce.get('ta').getContent()
					}
					,listeners: {
						success: {fn: function(response) {
							ed.setContent(response.object);
							Ext.Msg.hide();
						}, scope: this}
						,failure: function() {
							Ext.Msg.hide();
						}
					}
				});
			});

			ed.addCommand('mcePageBreakerClear', function() {
				Ext.Msg.wait(_('saving'), _('please_wait'));
				MODx.Ajax.request({
					url: path + 'processor.php'
					,method: 'POST'
					,params: {
						action: 'clear'
						,html: tinymce.get('ta').getContent()
					}
					,listeners: {
						success: {fn: function(response) {
							ed.setContent(response.object);
							Ext.Msg.hide();
						}, scope: this}
						,failure: function() {
							Ext.Msg.hide();
						}
					}
				});
			});

			// Register buttons
			ed.addButton('pagebreak', {title : _('pb_editor_pagebreak'), cmd : 'mcePageBreaker', image : path + 'img/mgr/standart.png'});
			ed.addButton('pagebreakcls', {title : _('pb_editor_pagebreakcls'), cmd :  'mcePageBreakerClear', image : path + 'img/mgr/cls.png'});
			ed.addButton('pagebreakmanual', {title : _('pb_editor_pagebreakmanual'), cmd : 'mcePageBreakerManual', image : path + 'img/mgr/manual.png'});
			ed.addButton('pagebreakauto', {title : _('pb_editor_pagebreakauto'), cmd : 'mcePageBreakerAuto', image : path + 'img/mgr/auto.png'});

			ed.onInit.add(function() {
				if (ed.settings.content_css !== false) {
					ed.dom.loadCSS(path + "css/mgr/editor.css");
				}
				if (ed.theme.onResolveName) {
					ed.theme.onResolveName.add(function(th, o) {
						if (o.node.nodeName == 'IMG' && ed.dom.hasClass(o.node, cls))
							o.name = 'pagebreaker';
					});
				}
			});

			ed.onClick.add(function(ed, e) {
				e = e.target;
				if (e.nodeName === 'IMG' && ed.dom.hasClass(e, cls)) {
					ed.selection.select(e);
				}
			});

			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('pagebreaker', n.nodeName === 'IMG' && ed.dom.hasClass(n, cls));
			});

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = o.content.replace(pbRE, pb);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.get) {
					o.content = o.content.replace(/<img[^>]+>/g, function(im) {
						if (im.indexOf('class="mcePageBreaker') !== -1) {
							im = sep;
						}
						return im;
					});
				}
			});
		},

		getInfo : function() {
			return {
				longname : 'PageBreaker',
				author : '',
				authorurl : '',
				infourl : '',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('pagebreaker', tinymce.plugins.PageBreakerPlugin);
})();
