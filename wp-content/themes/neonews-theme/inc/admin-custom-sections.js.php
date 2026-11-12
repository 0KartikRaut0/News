<?php
/**
 * Inline admin script for homepage custom sections (included from admin-settings-tabs).
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
    <script>
    (function(){
        var wrap    = document.getElementById('nn-custom-sections-wrap');
        var jsonIn  = document.getElementById('nn-custom-sections-json');
        var addBtn  = document.getElementById('nn-add-custom-section');
        if (!wrap || !jsonIn || !addBtn) { return; }

        var sections = (typeof nnCustomSections !== 'undefined') ? JSON.parse(JSON.stringify(nnCustomSections)) : [];
        var pages = <?php echo wp_json_encode( array_map( function( $p ) { return array( 'id' => $p->ID, 'title' => $p->post_title ); }, $all_pages ) ); ?>;
        var postLayouts = <?php echo wp_json_encode( neonews_home_layout_choices( 'posts' ) ); ?>;
        var templateRegistry = <?php echo wp_json_encode( $hs_templates_admin ); ?>;
        var templateDefaults = <?php echo wp_json_encode( $hs_template_defaults ); ?>;
        var defaultTemplateId = <?php echo wp_json_encode( (string) array_key_first( neonews_home_custom_templates() ) ); ?>;

        function uid() {
            return 'cs' + Date.now().toString(36) + Math.random().toString(36).slice(2,5);
        }

        function saveAll() {
            jsonIn.value = JSON.stringify(sections);
        }

        function escHtml(s) {
            return String(s).replace(/[&<>"']/g, function(c){
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
            });
        }

        function pageOptions(selectedId) {
            var out = '<option value="0">— select page —</option>';
            pages.forEach(function(p){
                out += '<option value="'+p.id+'"'+(parseInt(selectedId,10)===p.id?' selected':'')+'>'+escHtml(p.title)+'</option>';
            });
            return out;
        }

        function postLayoutOptions(selected) {
            var out = '<option value="">' + <?php echo wp_json_encode( __( '— card grid —', 'neonews' ) ); ?> + '</option>';
            Object.keys(postLayouts).forEach(function(k){
                out += '<option value="'+k+'"'+(k===selected?' selected':'')+'>'+escHtml(postLayouts[k])+'</option>';
            });
            return out;
        }

        function templateOptions(selected) {
            var out = '';
            Object.keys(templateRegistry).forEach(function(k){
                var t = templateRegistry[k];
                out += '<option value="'+k+'"'+(k===selected?' selected':'')+'>'+(t.icon||'')+' '+escHtml(t.label)+'</option>';
            });
            return out;
        }

        function normalizeSection(cs) {
            if (cs.type === 'tag') { cs.type = 'posts'; cs.source = cs.source || 'tag'; }
            if (!cs.type) { cs.type = 'template'; }
            if (!cs.template && cs.type === 'template') { cs.template = defaultTemplateId; }
            if (!cs.config || typeof cs.config !== 'object') { cs.config = {}; }
            if (cs.type === 'template' && templateDefaults[cs.template]) {
                cs.config = Object.assign({}, templateDefaults[cs.template], cs.config);
            }
            if (!cs.source) { cs.source = 'category'; }
            return cs;
        }

        function buildTemplateFieldsHtml(tid, config) {
            var reg = templateRegistry[tid];
            if (!reg || !reg.fields) { return ''; }
            var html = '';
            Object.keys(reg.fields).forEach(function(key){
                var f = reg.fields[key];
                var val = config[key] !== undefined ? config[key] : (f.default || '');
                var full = (f.type === 'textarea' || key === 'links') ? ' nn-cs-field-full' : '';
                html += '<label class="'+full+'">'+escHtml(f.label);
                if (f.type === 'textarea') {
                    html += '<textarea class="nn-cs-cfg" data-key="'+key+'" rows="'+(key==='links'?4:2)+'">'+escHtml(val)+'</textarea>';
                } else if (f.type === 'number') {
                    html += '<input type="number" class="nn-cs-cfg" data-key="'+key+'" value="'+escHtml(String(val))+'" />';
                } else if (f.type === 'url') {
                    html += '<input type="url" class="nn-cs-cfg" data-key="'+key+'" value="'+escHtml(val)+'" placeholder="https://" />';
                } else {
                    html += '<input type="text" class="nn-cs-cfg" data-key="'+key+'" value="'+escHtml(val)+'" />';
                }
                html += '</label>';
            });
            return html;
        }

        function syncSortLabel(cs) {
            var sortRow = document.querySelector('[data-key="custom_'+cs.id+'"]');
            if (!sortRow) { return; }
            var icons = { template:'🧩', posts:'📰', page:'📄', html:'🖊' };
            var label = cs.title || '';
            if (!label && cs.type === 'template' && templateRegistry[cs.template]) {
                label = templateRegistry[cs.template].label;
            }
            if (!label) { label = cs.id; }
            var spans = sortRow.querySelectorAll('span');
            if (spans[1]) {
                spans[1].textContent = (icons[cs.type]||'📦') + '  ' + label;
            }
        }

        function buildRow(cs, idx) {
            cs = normalizeSection(cs);
            var row = document.createElement('div');
            row.className = 'nn-cs-row';
            var intro = (cs.type === 'template' && templateRegistry[cs.template]) ? templateRegistry[cs.template].description : '';

            row.innerHTML =
                '<h4><span>'+escHtml(cs.title || (cs.type === 'template' && templateRegistry[cs.template] ? templateRegistry[cs.template].label : ('Section '+(idx+1))))+'</span>'
                +'<button type="button" class="button-link-delete nn-cs-delete" style="color:#b32d2e;">✕ '+<?php echo wp_json_encode( __( 'Remove', 'neonews' ) ); ?>+'</button></h4>'
                +'<div class="nn-cs-grid">'
                +'<label>'+<?php echo wp_json_encode( __( 'Section kind', 'neonews' ) ); ?>
                +'<select class="nn-cs-type">'
                +'<option value="template"'+(cs.type==='template'?' selected':'')+'>'+<?php echo wp_json_encode( __( 'Ready-made template', 'neonews' ) ); ?>+'</option>'
                +'<option value="posts"'+(cs.type==='posts'?' selected':'')+'>'+<?php echo wp_json_encode( __( 'Posts from category or tag', 'neonews' ) ); ?>+'</option>'
                +'<option value="page"'+(cs.type==='page'?' selected':'')+'>'+<?php echo wp_json_encode( __( 'WordPress page', 'neonews' ) ); ?>+'</option>'
                +'</select></label>'
                +'<label>'+<?php echo wp_json_encode( __( 'Optional section heading', 'neonews' ) ); ?>
                +'<input type="text" class="nn-cs-title" value="'+escHtml(cs.title||'')+'" placeholder="'+<?php echo wp_json_encode( __( 'Leave empty to use template default', 'neonews' ) ); ?>+'" /></label>'
                +'<label class="nn-cs-showtitle-wrap"><input type="checkbox" class="nn-cs-showtitle" '+(cs.show_title?'checked':'')+' /> '+<?php echo wp_json_encode( __( 'Show heading above block', 'neonews' ) ); ?>+'</label>'
                +'<div class="nn-cs-template-wrap" style="grid-column:1/-1;">'
                +'<label>'+<?php echo wp_json_encode( __( 'Template design', 'neonews' ) ); ?>
                +'<select class="nn-cs-template">'+templateOptions(cs.template||defaultTemplateId)+'</select></label>'
                +'<p class="nn-cs-template-intro">'+escHtml(intro)+'</p>'
                +'<div class="nn-cs-template-fields">'+buildTemplateFieldsHtml(cs.template||defaultTemplateId, cs.config)+'</div></div>'
                +'<div class="nn-cs-posts-wrap"><div class="nn-cs-grid" style="padding:0;margin:0;">'
                +'<label>'+<?php echo wp_json_encode( __( 'Source', 'neonews' ) ); ?>
                +'<select class="nn-cs-source"><option value="category"'+(cs.source==='category'?' selected':'')+'>'+<?php echo wp_json_encode( __( 'Category', 'neonews' ) ); ?>+'</option>'
                +'<option value="tag"'+(cs.source==='tag'?' selected':'')+'>'+<?php echo wp_json_encode( __( 'Tag', 'neonews' ) ); ?>+'</option></select></label>'
                +'<label>'+<?php echo wp_json_encode( __( 'Slug', 'neonews' ) ); ?>
                +'<input type="text" class="nn-cs-slug" value="'+escHtml(cs.slug||'')+'" placeholder="business" /></label>'
                +'<label>'+<?php echo wp_json_encode( __( 'Number of posts', 'neonews' ) ); ?>
                +'<input type="number" class="nn-cs-count" min="1" max="20" value="'+(cs.count||4)+'" /></label>'
                +'<label>'+<?php echo wp_json_encode( __( 'Posts layout', 'neonews' ) ); ?>
                +'<select class="nn-cs-layout">'+postLayoutOptions(cs.layout||'')+'</select></label>'
                +'<label style="grid-column:1/-1;">'+<?php echo wp_json_encode( __( '“See all” link (optional)', 'neonews' ) ); ?>
                +'<input type="url" class="nn-cs-seeall" value="'+escHtml(cs.see_all_url||'')+'" /></label>'
                +'</div></div>'
                +'<div class="nn-cs-page-wrap"><label>'+<?php echo wp_json_encode( __( 'Page', 'neonews' ) ); ?>
                +'<select class="nn-cs-page">'+pageOptions(cs.page_id)+'</select></label></div>'
                +'</div>';

            row.querySelector('.nn-cs-delete').addEventListener('click', function(){
                var i = sections.findIndex(function(s){ return s.id === cs.id; });
                if (i > -1) { sections.splice(i,1); }
                row.remove();
                if (window.nnRemoveCustomSortRow) { window.nnRemoveCustomSortRow(cs.id); }
                saveAll();
            });

            function refreshTemplateFields() {
                var tid = row.querySelector('.nn-cs-template').value;
                var fieldsWrap = row.querySelector('.nn-cs-template-fields');
                var introEl = row.querySelector('.nn-cs-template-intro');
                if (!fieldsWrap) { return; }
                if (!cs.config) { cs.config = {}; }
                cs.config = Object.assign({}, templateDefaults[tid] || {}, cs.config);
                fieldsWrap.innerHTML = buildTemplateFieldsHtml(tid, cs.config);
                fieldsWrap.querySelectorAll('.nn-cs-cfg').forEach(function(el){
                    el.addEventListener('input', update);
                    el.addEventListener('change', update);
                });
                if (introEl && templateRegistry[tid]) { introEl.textContent = templateRegistry[tid].description || ''; }
            }

            function update() {
                cs.type = row.querySelector('.nn-cs-type').value;
                cs.title = row.querySelector('.nn-cs-title').value;
                cs.show_title = row.querySelector('.nn-cs-showtitle').checked;
                if (cs.type === 'template') {
                    cs.template = row.querySelector('.nn-cs-template').value;
                    cs.config = cs.config || {};
                    row.querySelectorAll('.nn-cs-cfg').forEach(function(el){
                        var k = el.getAttribute('data-key');
                        if (k) { cs.config[k] = el.value; }
                    });
                }
                if (cs.type === 'posts') {
                    cs.source = row.querySelector('.nn-cs-source').value;
                    cs.slug = row.querySelector('.nn-cs-slug').value;
                    cs.count = parseInt(row.querySelector('.nn-cs-count').value, 10) || 4;
                    cs.layout = row.querySelector('.nn-cs-layout').value;
                    cs.see_all_url = row.querySelector('.nn-cs-seeall').value;
                }
                if (cs.type === 'page') {
                    cs.page_id = parseInt(row.querySelector('.nn-cs-page').value, 10) || 0;
                    cs.see_all_url = row.querySelector('.nn-cs-seeall') ? row.querySelector('.nn-cs-seeall').value : cs.see_all_url;
                }
                var h = row.querySelector('h4 span');
                var display = cs.title || (cs.type === 'template' && templateRegistry[cs.template] ? templateRegistry[cs.template].label : ('Section '+(sections.indexOf(cs)+1)));
                h.textContent = display;
                updateVisibility(row, cs.type);
                saveAll();
                if (window.nnAddCustomSortRow) { window.nnAddCustomSortRow(cs); }
                syncSortLabel(cs);
            }

            updateVisibility(row, cs.type);

            row.querySelector('.nn-cs-type').addEventListener('change', function(){
                if (cs.type === 'template' && !cs.template) { cs.template = defaultTemplateId; }
                update();
            });
            row.querySelector('.nn-cs-template').addEventListener('change', function(){
                cs.config = Object.assign({}, templateDefaults[row.querySelector('.nn-cs-template').value] || {});
                refreshTemplateFields();
                update();
            });

            row.querySelectorAll('input,select,textarea').forEach(function(el){
                if (el.classList.contains('nn-cs-cfg')) { return; }
                el.addEventListener('change', update);
                el.addEventListener('input', update);
            });
            row.querySelectorAll('.nn-cs-cfg').forEach(function(el){
                el.addEventListener('input', update);
                el.addEventListener('change', update);
            });

            return row;
        }

        function updateVisibility(row, type) {
            var tw = row.querySelector('.nn-cs-template-wrap');
            var pw = row.querySelector('.nn-cs-posts-wrap');
            var pageW = row.querySelector('.nn-cs-page-wrap');
            var showTitle = row.querySelector('.nn-cs-showtitle-wrap');
            if (tw) { tw.style.display = type === 'template' ? '' : 'none'; }
            if (pw) { pw.style.display = type === 'posts' ? '' : 'none'; }
            if (pageW) { pageW.style.display = type === 'page' ? '' : 'none'; }
            if (showTitle) { showTitle.style.display = type === 'template' ? 'none' : ''; }
        }

        function render() {
            wrap.innerHTML = '';
            sections.forEach(function(cs, i){ wrap.appendChild(buildRow(normalizeSection(cs), i)); });
        }

        addBtn.addEventListener('click', function(){
            var cs = {
                id: uid(),
                type: 'template',
                template: defaultTemplateId,
                config: Object.assign({}, templateDefaults[defaultTemplateId] || {}),
                title: '',
                show_title: false,
                slug: '',
                source: 'category',
                count: 4,
                page_id: 0,
                see_all_url: '',
                layout: ''
            };
            sections.push(cs);
            wrap.appendChild(buildRow(cs, sections.length - 1));
            if (window.nnAddCustomSortRow) { window.nnAddCustomSortRow(cs); }
            saveAll();
        });

        sections.forEach(normalizeSection);
        render();
        sections.forEach(function(cs){
            if (window.nnAddCustomSortRow) { window.nnAddCustomSortRow(cs); }
        });
    })();
    </script>
