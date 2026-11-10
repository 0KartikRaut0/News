<?php
/**
 * Admin settings tab panels (Homepage, Header, Footer, Sidebar, Features).
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render homepage settings tab.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_tab_homepage( $settings ) {
    ?>
    <h2><?php esc_html_e( 'Homepage sections', 'neonews' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Toggle each block on the front page. Categories and pages are managed in Posts → Categories and Pages.', 'neonews' ); ?></p>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Hero headline', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_hero]" value="1" <?php checked( ! empty( $settings['show_home_hero'] ) ); ?> /> <?php esc_html_e( 'Show hero text block', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Hero title (line 1)', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[home_hero_title]" value="<?php echo esc_attr( $settings['home_hero_title'] ); ?>" class="large-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Hero title accent', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[home_hero_title_accent]" value="<?php echo esc_attr( $settings['home_hero_title_accent'] ); ?>" class="large-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Hero subtitle', 'neonews' ); ?></th>
            <td><textarea name="neonews_platform_settings[home_hero_subtitle]" rows="2" class="large-text"><?php echo esc_textarea( $settings['home_hero_subtitle'] ); ?></textarea></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Featured carousel', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_carousel]" value="1" <?php checked( ! empty( $settings['show_home_carousel'] ) ); ?> /> <?php esc_html_e( 'Show featured carousel (mark posts in editor)', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Carousel post count', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[home_carousel_count]" value="<?php echo esc_attr( $settings['home_carousel_count'] ); ?>" min="1" max="12" class="small-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Category blocks', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_categories]" value="1" <?php checked( ! empty( $settings['show_home_categories'] ) ); ?> /> <?php esc_html_e( 'Show category grid sections', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Category blocks', 'neonews' ); ?></th>
            <td>
                <?php
                // Build initial data from saved blocks, or from the old slug list.
                $saved_blocks = (array) ( $settings['home_cat_blocks'] ?? array() );
                if ( empty( $saved_blocks ) ) {
                    $default_count = absint( $settings['home_category_posts'] ?? 3 );
                    $old_slugs = array_filter( array_map( 'trim', explode( ',', (string) ( $settings['home_category_slugs'] ?? '' ) ) ) );
                    $saved_blocks = array_map( function( $s ) use ( $default_count ) {
                        return array( 'slug' => $s, 'label' => '', 'color' => '', 'count' => $default_count, 'see_all_url' => '', 'enabled' => true );
                    }, $old_slugs );
                }
                $all_wp_cats = get_categories( array( 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC' ) );
                ?>
                <input type="hidden" name="neonews_platform_settings[home_cat_blocks]" id="nn-cat-blocks-json" value="<?php echo esc_attr( wp_json_encode( array_values( $saved_blocks ) ) ); ?>" />
                <p class="description" style="margin-bottom:8px;"><?php esc_html_e( 'Each row is one section on the homepage. Drag to reorder. The "Display title" overrides the WordPress category name (so your category "politics" can show as "Trending Now").', 'neonews' ); ?></p>

                <style>
                .nn-catblk-tbl{width:100%;max-width:680px;border-collapse:collapse;font-size:0.82rem;}
                .nn-catblk-tbl th{background:#f6f7f7;padding:7px 10px;text-align:left;border:1px solid #c3c4c7;font-size:0.78rem;}
                .nn-catblk-tbl td{padding:6px 8px;border:1px solid #dcdcde;vertical-align:middle;}
                .nn-catblk-tbl tr.nn-cb-disabled td{opacity:.45;}
                .nn-catblk-drag{cursor:grab;color:#aaa;font-size:1rem;padding:0 4px;}
                .nn-catblk-tbl input[type=text],.nn-catblk-tbl input[type=url],.nn-catblk-tbl select{width:100%;padding:4px 6px;border:1px solid #c3c4c7;border-radius:3px;font-size:0.82rem;box-sizing:border-box;}
                .nn-catblk-tbl input[type=number]{width:52px;padding:4px 6px;border:1px solid #c3c4c7;border-radius:3px;font-size:0.82rem;}
                .nn-catblk-tbl input[type=color]{width:42px;height:28px;padding:2px;border:1px solid #c3c4c7;border-radius:3px;cursor:pointer;}
                .nn-cb-del{color:#b32d2e;background:none;border:none;cursor:pointer;font-size:1rem;padding:2px 6px;}
                </style>

                <table class="nn-catblk-tbl" id="nn-catblk-table">
                    <thead><tr>
                        <th></th>
                        <th><?php esc_html_e( 'On', 'neonews' ); ?></th>
                        <th><?php esc_html_e( 'Category', 'neonews' ); ?></th>
                        <th><?php esc_html_e( 'Display title', 'neonews' ); ?></th>
                        <th><?php esc_html_e( 'Posts', 'neonews' ); ?></th>
                        <th><?php esc_html_e( 'Color', 'neonews' ); ?></th>
                        <th><?php esc_html_e( '"See all" URL', 'neonews' ); ?></th>
                        <th></th>
                    </tr></thead>
                    <tbody id="nn-catblk-body"></tbody>
                </table>
                <button type="button" id="nn-catblk-add" class="button" style="margin-top:8px;">+ <?php esc_html_e( 'Add block', 'neonews' ); ?></button>

                <script>
                (function(){
                    var tbody  = document.getElementById('nn-catblk-body');
                    var jsonIn = document.getElementById('nn-catblk-json');
                    var addBtn = document.getElementById('nn-catblk-add');
                    jsonIn = document.getElementById('nn-cat-blocks-json');
                    var blocks = <?php echo wp_json_encode( array_values( $saved_blocks ) ); ?>;
                    var cats   = <?php echo wp_json_encode( array_map( function( $c ) { return array( 'slug' => $c->slug, 'name' => $c->name ); }, $all_wp_cats ) ); ?>;

                    function catOptions(selected) {
                        var out = '<option value="">— choose —</option>';
                        cats.forEach(function(c){
                            out += '<option value="'+esc(c.slug)+'"'+(c.slug===selected?' selected':'')+'>'+esc(c.name)+' ('+esc(c.slug)+')</option>';
                        });
                        return out;
                    }

                    function esc(s){ return String(s).replace(/[&<>"']/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];}); }

                    function save(){
                        jsonIn.value = JSON.stringify(blocks);
                    }

                    function makeRow(blk, idx){
                        var tr = document.createElement('tr');
                        tr.className = blk.enabled ? '' : 'nn-cb-disabled';
                        tr.setAttribute('draggable','true');
                        tr.innerHTML =
                            '<td class="nn-catblk-drag">⣿</td>'
                           +'<td><input type="checkbox" class="nn-cb-enabled"'+(blk.enabled?' checked':'')+' /></td>'
                           +'<td><select class="nn-cb-slug">'+catOptions(blk.slug)+'</select></td>'
                           +'<td><input type="text" class="nn-cb-label" value="'+esc(blk.label||'')+'" placeholder="'+esc(<?php echo wp_json_encode( __( 'e.g. Trending Now', 'neonews' ) ); ?>)+'" /></td>'
                           +'<td><input type="number" class="nn-cb-count" min="1" max="20" value="'+(blk.count||3)+'" /></td>'
                           +'<td><input type="color" class="nn-cb-color" value="'+(blk.color||'#ff7a18')+'" /></td>'
                           +'<td><input type="url" class="nn-cb-seeall" value="'+esc(blk.see_all_url||'')+'" placeholder="https://…" /></td>'
                           +'<td><button type="button" class="nn-cb-del" title="Remove">✕</button></td>';

                        function update(){
                            blk.slug        = tr.querySelector('.nn-cb-slug').value;
                            blk.label       = tr.querySelector('.nn-cb-label').value;
                            blk.count       = parseInt(tr.querySelector('.nn-cb-count').value)||3;
                            blk.color       = tr.querySelector('.nn-cb-color').value;
                            blk.see_all_url = tr.querySelector('.nn-cb-seeall').value;
                            blk.enabled     = tr.querySelector('.nn-cb-enabled').checked;
                            tr.className    = blk.enabled ? '' : 'nn-cb-disabled';
                            save();
                        }

                        tr.querySelectorAll('input,select').forEach(function(el){ el.addEventListener('change',update); el.addEventListener('input',update); });
                        tr.querySelector('.nn-cb-del').addEventListener('click',function(){
                            var i = blocks.indexOf(blk);
                            if(i>-1){blocks.splice(i,1);}
                            tr.remove();
                            save();
                        });

                        // drag
                        var dragging = null;
                        tr.addEventListener('dragstart',function(e){ dragging=tr; tr.style.opacity='0.45'; e.dataTransfer.effectAllowed='move'; });
                        tr.addEventListener('dragend',function(){ tr.style.opacity=''; dragging=null; reindexAndSave(); });
                        tr.addEventListener('dragover',function(e){ e.preventDefault(); if(!dragging||dragging===tr)return; var b=tr.getBoundingClientRect(); tbody.insertBefore(dragging,e.clientY>b.top+b.height/2?tr.nextSibling:tr); });

                        return tr;
                    }

                    function reindexAndSave(){
                        var newOrder = [];
                        tbody.querySelectorAll('tr').forEach(function(tr){
                            var slug = tr.querySelector('.nn-cb-slug').value;
                            var blk = blocks.find(function(b){ return b.slug===slug; });
                            if(blk) newOrder.push(blk);
                        });
                        blocks.length = 0;
                        newOrder.forEach(function(b){ blocks.push(b); });
                        save();
                    }

                    function render(){
                        tbody.innerHTML='';
                        blocks.forEach(function(blk,i){ tbody.appendChild(makeRow(blk,i)); });
                    }

                    addBtn.addEventListener('click',function(){
                        var blk={slug:'',label:'',color:'#ff7a18',count:3,see_all_url:'',enabled:true};
                        blocks.push(blk);
                        tbody.appendChild(makeRow(blk,blocks.length-1));
                        save();
                    });

                    render();
                })();
                </script>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Posts per category', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[home_category_posts]" value="<?php echo esc_attr( $settings['home_category_posts'] ); ?>" min="1" max="10" class="small-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Mid-page ad after category #', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[home_mid_ad_after_cat]" value="<?php echo esc_attr( $settings['home_mid_ad_after_cat'] ); ?>" min="0" max="10" class="small-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Watch / Videos', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_videos]" value="1" <?php checked( ! empty( $settings['show_home_videos'] ) ); ?> /> <?php esc_html_e( 'Show video section', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Video category slug', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[home_video_category]" value="<?php echo esc_attr( $settings['home_video_category'] ); ?>" class="regular-text" placeholder="videos" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Video count', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[home_video_count]" value="<?php echo esc_attr( $settings['home_video_count'] ); ?>" min="1" max="12" class="small-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Watch section title', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[home_video_title]" value="<?php echo esc_attr( $settings['home_video_title'] ); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Watch section subtitle', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[home_video_subtitle]" value="<?php echo esc_attr( $settings['home_video_subtitle'] ); ?>" class="large-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Reader tipline', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="neonews_platform_settings[show_home_tipline]" value="1" <?php checked( ! empty( $settings['show_home_tipline'] ) ); ?> /> <?php esc_html_e( 'Show “Pitch a story” section', 'neonews' ); ?></label>
                <p class="description">
                    <?php esc_html_e( 'Uses the same access rules as Submit Story — who can pitch (Premium only, or basic members too) is set in', 'neonews' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-core-settings#nn-submission-settings' ) ); ?>"><?php esc_html_e( 'Core Features → Story Submissions', 'neonews' ); ?></a>.
                    <?php esc_html_e( 'Pitches are reviewed under', 'neonews' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-submissions' ) ); ?>"><?php esc_html_e( 'Story Submissions', 'neonews' ); ?></a>.
                </p>
            </td>
        </tr>
        <?php
        $tip_fields = array(
            'tipline_badge'       => __( 'Pitch — badge', 'neonews' ),
            'tipline_title'       => __( 'Pitch — heading', 'neonews' ),
            'tipline_text'        => __( 'Pitch — intro text', 'neonews' ),
            'tipline_form_title'  => __( 'Pitch — box title', 'neonews' ),
            'tipline_form_desc'   => __( 'Pitch — box description', 'neonews' ),
            'tipline_cta_open'    => __( 'Pitch — button (can submit)', 'neonews' ),
            'tipline_cta_locked'  => __( 'Pitch — button (locked)', 'neonews' ),
            'tipline_locked_text' => __( 'Pitch — locked message', 'neonews' ),
        );
        foreach ( $tip_fields as $tip_key => $tip_label ) :
            ?>
            <tr>
                <th><?php echo esc_html( $tip_label ); ?></th>
                <td><input type="text" name="neonews_platform_settings[<?php echo esc_attr( $tip_key ); ?>]" value="<?php echo esc_attr( $settings[ $tip_key ] ); ?>" class="large-text" /></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <th><?php esc_html_e( 'Pitch — bullet points', 'neonews' ); ?></th>
            <td>
                <textarea name="neonews_platform_settings[tipline_points]" rows="3" class="large-text"><?php echo esc_textarea( $settings['tipline_points'] ); ?></textarea>
                <p class="description"><?php esc_html_e( 'One per line.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Latest posts', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_latest]" value="1" <?php checked( ! empty( $settings['show_home_latest'] ) ); ?> /> <?php esc_html_e( 'Show latest news grid', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Latest post count', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[home_latest_count]" value="<?php echo esc_attr( $settings['home_latest_count'] ); ?>" min="1" max="24" class="small-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Sidebar newsletter', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_home_newsletter]" value="1" <?php checked( ! empty( $settings['show_home_newsletter'] ) ); ?> /> <?php esc_html_e( 'Show newsletter box in homepage sidebar', 'neonews' ); ?></label></td>
        </tr>
    </table>

    <?php
    $section_labels = array(
        'hero_text'  => '🅷  ' . __( 'Hero headline & subtitle', 'neonews' ),
        'carousel'   => '🎠  ' . __( 'Featured posts carousel', 'neonews' ),
        'ad_hero'    => '📢  ' . __( 'Ad banner (below carousel)', 'neonews' ),
        'categories' => '📰  ' . __( 'Category blocks', 'neonews' ),
        'videos'     => '▶  ' . __( 'Videos section', 'neonews' ),
        'tipline'    => '✏  ' . __( 'Pitch a story', 'neonews' ),
        'latest'     => '🕐  ' . __( 'Latest news grid', 'neonews' ),
        'ad_footer'  => '📢  ' . __( 'Ad banner (bottom)', 'neonews' ),
    );
    $order_raw  = $settings['home_section_order'] ?? '';
    $order_keys = array_filter( array_map( 'trim', explode( ',', $order_raw ) ) );
    // fill any missing sections at the end
    $all_keys   = array_keys( $section_labels );
    $order_keys = array_merge( $order_keys, array_diff( $all_keys, $order_keys ) );
    ?>
    <h3><?php esc_html_e( 'Section order', 'neonews' ); ?></h3>
    <p class="description"><?php esc_html_e( 'Drag rows up or down to change the order sections appear on the homepage.', 'neonews' ); ?></p>
    <input type="hidden" name="neonews_platform_settings[home_section_order]" id="nn-section-order-input" value="<?php echo esc_attr( implode( ',', $order_keys ) ); ?>" />
    <?php
    $default_order = 'hero_text,carousel,ad_hero,categories,videos,tipline,latest,ad_footer';
    ?>
    <ul id="nn-section-sortable" style="max-width:520px;margin:0.75rem 0 1.5rem;padding:0;list-style:none;border:1px solid #c3c4c7;border-radius:6px;overflow:hidden;">
        <?php foreach ( $order_keys as $k ) :
            if ( ! isset( $section_labels[ $k ] ) ) continue; ?>
            <li data-key="<?php echo esc_attr( $k ); ?>" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#fff;border-bottom:1px solid #c3c4c7;cursor:grab;user-select:none;" class="nn-sort-row">
                <span style="color:#aaa;font-size:1.1rem;line-height:1;">⣿</span>
                <span><?php echo esc_html( $section_labels[ $k ] ); ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <button type="button" id="nn-restore-order" class="button" style="margin-bottom:1.5rem;" data-default="<?php echo esc_attr( $default_order ); ?>">
        ↺ <?php esc_html_e( 'Restore default order', 'neonews' ); ?>
    </button>
    <script id="nn-custom-section-data">
    var nnCustomSections = <?php echo wp_json_encode( array_values( (array) ( $settings['home_custom_sections'] ?? array() ) ) ); ?>;
    </script>
    <script>
    (function(){
        var list = document.getElementById('nn-section-sortable');
        var input = document.getElementById('nn-section-order-input');
        if (!list || !input) return;

        var dragging = null;

        list.querySelectorAll('.nn-sort-row').forEach(function(row){
            row.setAttribute('draggable','true');
            row.addEventListener('dragstart', function(e){
                dragging = row;
                row.style.opacity = '0.45';
                e.dataTransfer.effectAllowed = 'move';
            });
            row.addEventListener('dragend', function(){
                row.style.opacity = '';
                dragging = null;
                saveOrder();
            });
            row.addEventListener('dragover', function(e){
                e.preventDefault();
                if (!dragging || dragging === row) return;
                var bounding = row.getBoundingClientRect();
                var after = e.clientY > bounding.top + bounding.height / 2;
                list.insertBefore(dragging, after ? row.nextSibling : row);
            });
        });

        // touch support
        var touchDragging = null, touchClone = null, touchOffsetY = 0;
        list.querySelectorAll('.nn-sort-row').forEach(function(row){
            row.addEventListener('touchstart', function(e){
                touchDragging = row;
                var t = e.touches[0];
                var rect = row.getBoundingClientRect();
                touchOffsetY = t.clientY - rect.top;
                touchClone = row.cloneNode(true);
                touchClone.style.cssText = 'position:fixed;z-index:9999;width:'+rect.width+'px;left:'+rect.left+'px;top:'+(t.clientY - touchOffsetY)+'px;opacity:0.85;pointer-events:none;background:#f0f7ff;';
                document.body.appendChild(touchClone);
                row.style.opacity = '0.35';
            }, {passive:true});
            row.addEventListener('touchmove', function(e){
                if (!touchDragging || !touchClone) return;
                var t = e.touches[0];
                touchClone.style.top = (t.clientY - touchOffsetY) + 'px';
                var rows = list.querySelectorAll('.nn-sort-row');
                rows.forEach(function(r){
                    if (r === touchDragging) return;
                    var rb = r.getBoundingClientRect();
                    if (t.clientY > rb.top && t.clientY < rb.bottom) {
                        var after = t.clientY > rb.top + rb.height / 2;
                        list.insertBefore(touchDragging, after ? r.nextSibling : r);
                    }
                });
            }, {passive:true});
            row.addEventListener('touchend', function(){
                if (touchClone) { document.body.removeChild(touchClone); touchClone = null; }
                if (touchDragging) { touchDragging.style.opacity = ''; touchDragging = null; }
                saveOrder();
            });
        });

        function saveOrder(){
            var keys = [];
            list.querySelectorAll('.nn-sort-row').forEach(function(r){ keys.push(r.getAttribute('data-key')); });
            input.value = keys.join(',');
        }

        // Restore default order button
        var restoreBtn = document.getElementById('nn-restore-order');
        if (restoreBtn) {
            restoreBtn.addEventListener('click', function(){
                var defaultKeys = restoreBtn.getAttribute('data-default').split(',');
                // Collect all current rows (including custom ones) indexed by key
                var rowMap = {};
                list.querySelectorAll('.nn-sort-row').forEach(function(r){
                    rowMap[r.getAttribute('data-key')] = r;
                });
                // Re-append in default order first (built-in sections only)
                defaultKeys.forEach(function(k){
                    if (rowMap[k]) list.appendChild(rowMap[k]);
                });
                // Append any custom sections at the end (preserve their relative order)
                list.querySelectorAll('.nn-sort-row').forEach(function(r){
                    var k = r.getAttribute('data-key');
                    if (k && k.indexOf('custom_') === 0) list.appendChild(r);
                });
                saveOrder();
                restoreBtn.textContent = '✓ <?php echo esc_js( __( 'Order restored — click Save to apply', 'neonews' ) ); ?>';
                restoreBtn.style.color = '#0a6';
                setTimeout(function(){ restoreBtn.textContent = '↺ <?php echo esc_js( __( 'Restore default order', 'neonews' ) ); ?>'; restoreBtn.style.color = ''; }, 3000);
            });
        }

        // ── Custom section rows in sort list ──────────────────────────────
        function addCustomSortRow(cs) {
            var existing = list.querySelector('[data-key="custom_' + cs.id + '"]');
            if (existing) { return; }
            var icons = { tag: '🏷', page: '📄', html: '🖊' };
            var li = document.createElement('li');
            li.setAttribute('data-key', 'custom_' + cs.id);
            li.className = 'nn-sort-row nn-sort-custom';
            li.setAttribute('draggable', 'true');
            li.style.cssText = 'display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f0f7ff;border-bottom:1px solid #c3c4c7;cursor:grab;user-select:none;';
            li.innerHTML = '<span style="color:#aaa;font-size:1.1rem;">⣿</span><span>' + (icons[cs.type]||'📦') + '  ' + (cs.title || cs.id) + '</span>';
            attachDragToRow(li);
            list.appendChild(li);
            saveOrder();
        }

        function removeCustomSortRow(id) {
            var row = list.querySelector('[data-key="custom_' + id + '"]');
            if (row) { row.remove(); saveOrder(); }
        }

        function attachDragToRow(row) {
            row.addEventListener('dragstart', function(e){ dragging = row; row.style.opacity='0.45'; e.dataTransfer.effectAllowed='move'; });
            row.addEventListener('dragend', function(){ row.style.opacity=''; dragging=null; saveOrder(); });
            row.addEventListener('dragover', function(e){ e.preventDefault(); if(!dragging||dragging===row)return; var b=row.getBoundingClientRect(); list.insertBefore(dragging, e.clientY>b.top+b.height/2?row.nextSibling:row); });
            // touch
            row.addEventListener('touchstart', function(e){ touchDragging=row; var t=e.touches[0]; var rect=row.getBoundingClientRect(); touchOffsetY=t.clientY-rect.top; touchClone=row.cloneNode(true); touchClone.style.cssText='position:fixed;z-index:9999;width:'+rect.width+'px;left:'+rect.left+'px;top:'+(t.clientY-touchOffsetY)+'px;opacity:0.85;pointer-events:none;background:#dbeafe;'; document.body.appendChild(touchClone); row.style.opacity='0.35'; }, {passive:true});
        }

        window.nnAddCustomSortRow    = addCustomSortRow;
        window.nnRemoveCustomSortRow = removeCustomSortRow;
    })();
    </script>

    <?php
    // ── Pages dropdown for admin ──────────────────────────────────────────
    $all_pages = get_pages( array( 'post_status' => 'publish', 'sort_column' => 'post_title' ) );
    ?>

    <h3><?php esc_html_e( 'Custom sections', 'neonews' ); ?></h3>
    <p class="description"><?php esc_html_e( 'Add extra sections — posts from a tag, a page\'s content, or any custom HTML block. Each one appears in the order list above.', 'neonews' ); ?></p>

    <input type="hidden" name="neonews_platform_settings[home_custom_sections]" id="nn-custom-sections-json" value="<?php echo esc_attr( wp_json_encode( array_values( (array) ( $settings['home_custom_sections'] ?? array() ) ) ) ); ?>" />

    <div id="nn-custom-sections-wrap" style="max-width:660px;"></div>

    <button type="button" id="nn-add-custom-section" class="button" style="margin-top:0.5rem;">+ <?php esc_html_e( 'Add section', 'neonews' ); ?></button>

    <style>
    .nn-cs-row{background:#fff;border:1px solid #c3c4c7;border-radius:6px;padding:14px 16px;margin-bottom:10px;}
    .nn-cs-row h4{margin:0 0 10px;display:flex;justify-content:space-between;align-items:center;font-size:0.9rem;}
    .nn-cs-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 14px;}
    .nn-cs-grid label{display:flex;flex-direction:column;gap:3px;font-size:0.82rem;font-weight:600;color:#50575e;}
    .nn-cs-grid input,.nn-cs-grid select,.nn-cs-grid textarea{padding:5px 8px;border:1px solid #c3c4c7;border-radius:4px;font-size:0.85rem;width:100%;box-sizing:border-box;}
    .nn-cs-row .nn-cs-html-wrap{grid-column:1/-1;}
    .nn-cs-row .nn-cs-page-wrap,.nn-cs-row .nn-cs-slug-wrap{grid-column:1/-1;}
    </style>

    <script>
    (function(){
        var wrap    = document.getElementById('nn-custom-sections-wrap');
        var jsonIn  = document.getElementById('nn-custom-sections-json');
        var addBtn  = document.getElementById('nn-add-custom-section');
        var sections = (typeof nnCustomSections !== 'undefined') ? JSON.parse(JSON.stringify(nnCustomSections)) : [];
        var pages = <?php echo wp_json_encode( array_map( function( $p ) { return array( 'id' => $p->ID, 'title' => $p->post_title ); }, $all_pages ) ); ?>;

        function uid() {
            return 'cs' + Date.now().toString(36) + Math.random().toString(36).slice(2,5);
        }

        function saveAll() {
            jsonIn.value = JSON.stringify(sections);
        }

        function pageOptions(selectedId) {
            var out = '<option value="0">— select page —</option>';
            pages.forEach(function(p){ out += '<option value="'+p.id+'"'+(parseInt(selectedId)===p.id?' selected':'')+'>'+p.title+'</option>'; });
            return out;
        }

        function buildRow(cs, idx) {
            var row = document.createElement('div');
            row.className = 'nn-cs-row';
            row.innerHTML =
                '<h4><span>'+( cs.title || ('Section '+(idx+1)) )+'</span>'
                +'<button type="button" class="button-link-delete nn-cs-delete" style="color:#b32d2e;">✕ '+<?php echo wp_json_encode( __( 'Remove', 'neonews' ) ); ?>+'</button></h4>'
                +'<div class="nn-cs-grid">'
                +'<label>'+<?php echo wp_json_encode( __( 'Type', 'neonews' ) ); ?>+'<select class="nn-cs-type"><option value="tag"'+(cs.type==='tag'?' selected':'')+'>'+<?php echo wp_json_encode( __( 'Posts by Tag', 'neonews' ) ); ?>+'</option><option value="page"'+(cs.type==='page'?' selected':'')+'>'+<?php echo wp_json_encode( __( 'Page content', 'neonews' ) ); ?>+'</option><option value="html"'+(cs.type==='html'?' selected':'')+'>'+<?php echo wp_json_encode( __( 'Free HTML / Embed', 'neonews' ) ); ?>+'</option></select></label>'
                +'<label>'+<?php echo wp_json_encode( __( 'Section title', 'neonews' ) ); ?>+'<input type="text" class="nn-cs-title" value="'+escHtml(cs.title||'')+'" placeholder="'+<?php echo wp_json_encode( __( 'e.g. From our readers', 'neonews' ) ); ?>+'" /></label>'
                +'<div class="nn-cs-slug-wrap"><label>'+<?php echo wp_json_encode( __( 'Tag slug', 'neonews' ) ); ?>+'<input type="text" class="nn-cs-slug" value="'+escHtml(cs.slug||'')+'" placeholder="featured-interviews" /></label></div>'
                +'<label>'+<?php echo wp_json_encode( __( 'Post count', 'neonews' ) ); ?>+'<input type="number" class="nn-cs-count" min="1" max="20" value="'+(cs.count||4)+'" /></label>'
                +'<div class="nn-cs-page-wrap"><label>'+<?php echo wp_json_encode( __( 'Page', 'neonews' ) ); ?>+'<select class="nn-cs-page">'+pageOptions(cs.page_id)+'</select></label></div>'
                +'<label>'+<?php echo wp_json_encode( __( '"See all" link', 'neonews' ) ); ?>+'<input type="url" class="nn-cs-seeall" value="'+escHtml(cs.see_all_url||'')+'" placeholder="https://…" /></label>'
                +'<div class="nn-cs-html-wrap"><label>'+<?php echo wp_json_encode( __( 'HTML / embed code', 'neonews' ) ); ?>+'<textarea class="nn-cs-html" rows="3">'+escHtml(cs.html||'')+'</textarea></label></div>'
                +'</div>';

            updateVisibility(row, cs.type || 'tag');

            row.querySelector('.nn-cs-delete').addEventListener('click', function(){
                var i = sections.findIndex(function(s){ return s.id === cs.id; });
                if (i > -1) { sections.splice(i,1); }
                row.remove();
                if (window.nnRemoveCustomSortRow) { window.nnRemoveCustomSortRow(cs.id); }
                saveAll();
            });

            function update() {
                var type = row.querySelector('.nn-cs-type').value;
                cs.type       = type;
                cs.title      = row.querySelector('.nn-cs-title').value;
                cs.slug       = row.querySelector('.nn-cs-slug').value;
                cs.count      = parseInt(row.querySelector('.nn-cs-count').value) || 4;
                cs.page_id    = parseInt(row.querySelector('.nn-cs-page').value) || 0;
                cs.html       = row.querySelector('.nn-cs-html').value;
                cs.see_all_url = row.querySelector('.nn-cs-seeall').value;
                row.querySelector('h4 span').textContent = cs.title || ('Section '+(sections.indexOf(cs)+1));
                updateVisibility(row, type);
                saveAll();
                if (window.nnAddCustomSortRow) { window.nnAddCustomSortRow(cs); }
                // update label in sort list
                var sortRow = document.querySelector('[data-key="custom_'+cs.id+'"]');
                if (sortRow) {
                    var icons = {tag:'🏷',page:'📄',html:'🖊'};
                    sortRow.querySelectorAll('span')[1].textContent = (icons[type]||'📦')+'  '+(cs.title||cs.id);
                }
            }

            row.querySelectorAll('input,select,textarea').forEach(function(el){
                el.addEventListener('change', update);
                el.addEventListener('input', update);
            });

            return row;
        }

        function updateVisibility(row, type) {
            row.querySelector('.nn-cs-slug-wrap').style.display   = type === 'tag'  ? '' : 'none';
            row.querySelector('.nn-cs-page-wrap').style.display   = type === 'page' ? '' : 'none';
            row.querySelector('.nn-cs-html-wrap').style.display   = type === 'html' ? '' : 'none';
            row.querySelectorAll('.nn-cs-grid > label')[2].style.display = type !== 'html' ? '' : 'none'; // see-all
            row.querySelectorAll('.nn-cs-grid > label')[1].style.display = type !== 'html' ? '' : 'none'; // count
        }

        function escHtml(s) {
            return String(s).replace(/[&<>"']/g, function(c){
                return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
            });
        }

        function render() {
            wrap.innerHTML = '';
            sections.forEach(function(cs, i){ wrap.appendChild(buildRow(cs, i)); });
        }

        addBtn.addEventListener('click', function(){
            var cs = { id: uid(), type: 'tag', title: '', slug: '', count: 4, page_id: 0, html: '', show_title: true, see_all_url: '' };
            sections.push(cs);
            wrap.appendChild(buildRow(cs, sections.length-1));
            if (window.nnAddCustomSortRow) { window.nnAddCustomSortRow(cs); }
            saveAll();
        });

        render();
        // register existing custom sections in sort list (in case they aren't there yet)
        sections.forEach(function(cs){
            if (window.nnAddCustomSortRow) { window.nnAddCustomSortRow(cs); }
        });
    })();
    </script>

    <?php
}

/**
 * Render header settings tab.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_tab_header( $settings ) {
    ?>
    <h2><?php esc_html_e( 'Header & navigation', 'neonews' ); ?></h2>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Date & time bar', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_header_datetime]" value="1" <?php checked( $settings['show_header_datetime'] ); ?> /> <?php esc_html_e( 'Show live date and time', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Breaking news ticker', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_breaking_news]" value="1" <?php checked( ! empty( $settings['show_breaking_news'] ) ); ?> /> <?php esc_html_e( 'Show breaking news bar (mark posts as breaking in editor)', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Breaking labels', 'neonews' ); ?></th>
            <td>
                <input type="text" name="neonews_platform_settings[breaking_label_live]" value="<?php echo esc_attr( $settings['breaking_label_live'] ); ?>" class="small-text" placeholder="LIVE" />
                <input type="text" name="neonews_platform_settings[breaking_label_tag]" value="<?php echo esc_attr( $settings['breaking_label_tag'] ); ?>" class="small-text" placeholder="Breaking" />
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Breaking item count', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[breaking_count]" value="<?php echo esc_attr( $settings['breaking_count'] ); ?>" min="1" max="10" class="small-text" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Logo mark (no custom logo)', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[logo_mark]" value="<?php echo esc_attr( $settings['logo_mark'] ); ?>" class="small-text" maxlength="4" /></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Search bar', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_header_search]" value="1" <?php checked( ! empty( $settings['show_header_search'] ) ); ?> /> <?php esc_html_e( 'Show search in header', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Search placeholders', 'neonews' ); ?></th>
            <td>
                <input type="text" name="neonews_platform_settings[search_placeholder_desktop]" value="<?php echo esc_attr( $settings['search_placeholder_desktop'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Desktop placeholder', 'neonews' ); ?>" /><br />
                <input type="text" name="neonews_platform_settings[search_placeholder_mobile]" value="<?php echo esc_attr( $settings['search_placeholder_mobile'] ); ?>" class="large-text" style="margin-top:6px;" placeholder="<?php esc_attr_e( 'Mobile placeholder', 'neonews' ); ?>" />
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Sign-in / account menu', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_header_auth]" value="1" <?php checked( ! empty( $settings['show_header_auth'] ) ); ?> /> <?php esc_html_e( 'Show user menu and login', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Fallback menu categories', 'neonews' ); ?></th>
            <td>
                <input type="text" name="neonews_platform_settings[fallback_nav_categories]" value="<?php echo esc_attr( $settings['fallback_nav_categories'] ); ?>" class="large-text" />
                <p class="description"><?php esc_html_e( 'Used when no Primary Menu is assigned (Appearance → Menus).', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Fallback menu pages', 'neonews' ); ?></th>
            <td><input type="text" name="neonews_platform_settings[fallback_nav_pages]" value="<?php echo esc_attr( $settings['fallback_nav_pages'] ); ?>" class="large-text" placeholder="about,contact" /></td>
        </tr>
    </table>
    <?php
}

/**
 * Render footer settings tab.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_tab_footer( $settings ) {
    ?>
    <h2><?php esc_html_e( 'Footer content', 'neonews' ); ?></h2>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Brand tagline', 'neonews' ); ?></th>
            <td><textarea name="neonews_platform_settings[footer_tagline]" rows="2" class="large-text"><?php echo esc_textarea( $settings['footer_tagline'] ); ?></textarea></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Copyright text', 'neonews' ); ?></th>
            <td>
                <textarea name="neonews_platform_settings[footer_copyright]" rows="2" class="large-text"><?php echo esc_textarea( $settings['footer_copyright'] ); ?></textarea>
                <p class="description"><?php esc_html_e( 'Leave empty for default “© YEAR Site Name”. Also editable in Appearance → Customize → Footer.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Social media & WhatsApp', 'neonews' ); ?></th>
            <td>
                <p><strong><?php esc_html_e( 'Profile URLs', 'neonews' ); ?></strong></p>
                <p>
                    <label>X / Twitter<br /><input type="url" name="neonews_platform_settings[social_x]" value="<?php echo esc_url( $settings['social_x'] ); ?>" class="large-text" placeholder="https://x.com/yournews" /></label>
                </p>
                <p>
                    <label>Facebook<br /><input type="url" name="neonews_platform_settings[social_facebook]" value="<?php echo esc_url( $settings['social_facebook'] ); ?>" class="large-text" placeholder="https://facebook.com/yournews" /></label>
                </p>
                <p>
                    <label>LinkedIn<br /><input type="url" name="neonews_platform_settings[social_linkedin]" value="<?php echo esc_url( $settings['social_linkedin'] ); ?>" class="large-text" /></label>
                </p>
                <p>
                    <label>Instagram<br /><input type="url" name="neonews_platform_settings[social_instagram]" value="<?php echo esc_url( $settings['social_instagram'] ); ?>" class="large-text" /></label>
                </p>
                <p class="description">
                    <?php esc_html_e( 'WhatsApp number, page chat buttons and the floating icon are configured in the', 'neonews' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=neonews-settings&tab=whatsapp' ) ); ?>"><?php esc_html_e( 'WhatsApp tab', 'neonews' ); ?></a>.
                </p>
                <p style="margin-top:1rem;"><strong><?php esc_html_e( 'Where to show', 'neonews' ); ?></strong></p>
                <label><input type="checkbox" name="neonews_platform_settings[show_header_social]" value="1" <?php checked( ! empty( $settings['show_header_social'] ) ); ?> /> <?php esc_html_e( 'Social icons in header date/time bar', 'neonews' ); ?></label><br />
                <label><input type="checkbox" name="neonews_platform_settings[show_footer_social]" value="1" <?php checked( ! empty( $settings['show_footer_social'] ) ); ?> /> <?php esc_html_e( 'Social icons in footer', 'neonews' ); ?></label><br />
                <label><input type="checkbox" name="neonews_platform_settings[show_contact_social]" value="1" <?php checked( ! empty( $settings['show_contact_social'] ) ); ?> /> <?php esc_html_e( 'Social icons on Contact page', 'neonews' ); ?></label>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Sections column', 'neonews' ); ?></th>
            <td>
                <input type="text" name="neonews_platform_settings[footer_section_categories]" value="<?php echo esc_attr( $settings['footer_section_categories'] ); ?>" class="large-text" />
                <p class="description"><?php esc_html_e( 'Category slugs for footer “Sections” links.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Company column', 'neonews' ); ?></th>
            <td>
                <input type="text" name="neonews_platform_settings[footer_company_pages]" value="<?php echo esc_attr( $settings['footer_company_pages'] ); ?>" class="large-text" placeholder="about,careers,advertise,contact" />
                <p class="description"><?php esc_html_e( 'Page slugs for footer “Company” links. Create pages in Pages → Add New.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Footer newsletter', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="neonews_platform_settings[show_footer_newsletter]" value="1" <?php checked( ! empty( $settings['show_footer_newsletter'] ) ); ?> /> <?php esc_html_e( 'Show newsletter form in footer', 'neonews' ); ?></label>
                <p><input type="text" name="neonews_platform_settings[footer_newsletter_title]" value="<?php echo esc_attr( $settings['footer_newsletter_title'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Title', 'neonews' ); ?>" /></p>
                <p><textarea name="neonews_platform_settings[footer_newsletter_text]" rows="2" class="large-text"><?php echo esc_textarea( $settings['footer_newsletter_text'] ); ?></textarea></p>
                <p><input type="url" name="neonews_platform_settings[newsletter_form_action]" value="<?php echo esc_url( $settings['newsletter_form_action'] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Form action URL (Mailchimp, etc.) — leave empty for placeholder', 'neonews' ); ?>" /></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Render WhatsApp settings tab.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_tab_whatsapp( $settings ) {
    $name  = 'neonews_platform_settings';
    $pages = neonews_get_whatsapp_pages();
    ?>
    <h2><?php esc_html_e( 'WhatsApp contact', 'neonews' ); ?></h2>
    <?php if ( ! neonews_has_whatsapp_number() ) : ?>
        <div class="notice notice-warning inline"><p><?php esc_html_e( 'No WhatsApp number saved yet — WhatsApp buttons and the floating icon stay hidden until you add one below.', 'neonews' ); ?></p></div>
    <?php endif; ?>
    <table class="form-table">
        <tr>
            <th><label for="nn-wa-number"><?php esc_html_e( 'WhatsApp number', 'neonews' ); ?></label></th>
            <td>
                <input type="text" id="nn-wa-number" name="<?php echo esc_attr( $name ); ?>[whatsapp_number]" value="<?php echo esc_attr( $settings['whatsapp_number'] ); ?>" class="regular-text" placeholder="919876543210" />
                <p class="description"><?php esc_html_e( 'Country code + number, digits only (no +, spaces or dashes). Example: 919876543210 for India. Used on every page unless a page has its own number below.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="nn-wa-hours"><?php esc_html_e( 'Availability note', 'neonews' ); ?></label></th>
            <td>
                <input type="text" id="nn-wa-hours" name="<?php echo esc_attr( $name ); ?>[whatsapp_hours]" value="<?php echo esc_attr( $settings['whatsapp_hours'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Mon–Sat, 10am–7pm IST', 'neonews' ); ?>" />
                <p class="description"><?php esc_html_e( 'Optional. Shown under each WhatsApp button.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Floating WhatsApp icon', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[whatsapp_float_enabled]" value="1" <?php checked( ! empty( $settings['whatsapp_float_enabled'] ) ); ?> /> <?php esc_html_e( 'Show a floating WhatsApp icon on pages where WhatsApp is enabled below', 'neonews' ); ?></label><br />
                <label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[whatsapp_float_mobile]" value="1" <?php checked( ! empty( $settings['whatsapp_float_mobile'] ) ); ?> /> <?php esc_html_e( 'Also show on mobile', 'neonews' ); ?></label>
                <p>
                    <label><?php esc_html_e( 'Position', 'neonews' ); ?>
                        <select name="<?php echo esc_attr( $name ); ?>[whatsapp_float_position]">
                            <option value="right" <?php selected( $settings['whatsapp_float_position'], 'right' ); ?>><?php esc_html_e( 'Bottom right', 'neonews' ); ?></option>
                            <option value="left" <?php selected( $settings['whatsapp_float_position'], 'left' ); ?>><?php esc_html_e( 'Bottom left', 'neonews' ); ?></option>
                        </select>
                    </label>
                </p>
                <p>
                    <label><?php esc_html_e( 'Label next to icon (desktop)', 'neonews' ); ?><br />
                    <input type="text" name="<?php echo esc_attr( $name ); ?>[whatsapp_float_label]" value="<?php echo esc_attr( $settings['whatsapp_float_label'] ); ?>" class="regular-text" /></label>
                </p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Post sharing', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[show_post_whatsapp]" value="1" <?php checked( ! empty( $settings['show_post_whatsapp'] ) ); ?> /> <?php esc_html_e( 'WhatsApp share button on posts', 'neonews' ); ?></label>
                <p class="description"><?php esc_html_e( 'Requires “Post shares” enabled under Features & Colors. Does not need a number.', 'neonews' ); ?></p>
            </td>
        </tr>
    </table>

    <?php foreach ( $pages as $key => $label ) : ?>
        <h2><?php echo esc_html( sprintf( __( '%s page', 'neonews' ), $label ) ); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Enable', 'neonews' ); ?></th>
                <td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[show_<?php echo esc_attr( $key ); ?>_whatsapp]" value="1" <?php checked( ! empty( $settings[ 'show_' . $key . '_whatsapp' ] ) ); ?> /> <?php echo esc_html( sprintf( __( 'Show WhatsApp contact on the %s page', 'neonews' ), $label ) ); ?></label></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Number for this page', 'neonews' ); ?></th>
                <td>
                    <input type="text" name="<?php echo esc_attr( $name ); ?>[whatsapp_<?php echo esc_attr( $key ); ?>_number]" value="<?php echo esc_attr( $settings[ 'whatsapp_' . $key . '_number' ] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Leave blank to use the main number', 'neonews' ); ?>" />
                    <p class="description"><?php esc_html_e( 'Optional — e.g. route Advertise to your sales team and Careers to HR.', 'neonews' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Box title', 'neonews' ); ?></th>
                <td><input type="text" name="<?php echo esc_attr( $name ); ?>[whatsapp_<?php echo esc_attr( $key ); ?>_title]" value="<?php echo esc_attr( $settings[ 'whatsapp_' . $key . '_title' ] ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Box description', 'neonews' ); ?></th>
                <td><input type="text" name="<?php echo esc_attr( $name ); ?>[whatsapp_<?php echo esc_attr( $key ); ?>_desc]" value="<?php echo esc_attr( $settings[ 'whatsapp_' . $key . '_desc' ] ); ?>" class="large-text" /></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Button text', 'neonews' ); ?></th>
                <td><input type="text" name="<?php echo esc_attr( $name ); ?>[whatsapp_<?php echo esc_attr( $key ); ?>_button]" value="<?php echo esc_attr( $settings[ 'whatsapp_' . $key . '_button' ] ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Prefilled message', 'neonews' ); ?></th>
                <td>
                    <input type="text" name="<?php echo esc_attr( $name ); ?>[whatsapp_<?php echo esc_attr( $key ); ?>_message]" value="<?php echo esc_attr( $settings[ 'whatsapp_' . $key . '_message' ] ); ?>" class="large-text" />
                    <p class="description"><?php esc_html_e( 'Text already typed in the visitor’s WhatsApp when they open the chat.', 'neonews' ); ?></p>
                </td>
            </tr>
        </table>
    <?php endforeach; ?>
    <?php
}

/**
 * Render sidebar settings tab.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_tab_sidebar( $settings ) {
    ?>
    <h2><?php esc_html_e( 'Sidebar widgets', 'neonews' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Built-in sidebar blocks. Additional widgets: Appearance → Widgets → Sidebar.', 'neonews' ); ?></p>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Weather widget', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[sidebar_weather]" value="1" <?php checked( ! empty( $settings['sidebar_weather'] ) ); ?> /> <?php esc_html_e( 'Show weather in sidebar', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Fresh Reads', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="neonews_platform_settings[sidebar_fresh_reads]" value="1" <?php checked( ! empty( $settings['sidebar_fresh_reads'] ) ); ?> /> <?php esc_html_e( 'Show trending posts widget', 'neonews' ); ?></label>
                <p><input type="text" name="neonews_platform_settings[sidebar_fresh_reads_title]" value="<?php echo esc_attr( $settings['sidebar_fresh_reads_title'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Widget title', 'neonews' ); ?>" /></p>
                <p><input type="number" name="neonews_platform_settings[sidebar_fresh_reads_count]" value="<?php echo esc_attr( $settings['sidebar_fresh_reads_count'] ); ?>" min="1" max="10" class="small-text" /> <?php esc_html_e( 'posts', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Topics', 'neonews' ); ?></th>
            <td>
                <label><input type="checkbox" name="neonews_platform_settings[sidebar_topics]" value="1" <?php checked( ! empty( $settings['sidebar_topics'] ) ); ?> /> <?php esc_html_e( 'Show category topic pills', 'neonews' ); ?></label>
                <p><input type="number" name="neonews_platform_settings[sidebar_topics_count]" value="<?php echo esc_attr( $settings['sidebar_topics_count'] ); ?>" min="1" max="20" class="small-text" /> <?php esc_html_e( 'categories', 'neonews' ); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Render Master Control tab — full access to every feature and setting.
 */
function neonews_admin_tab_access( $settings ) {
    $mem  = wp_parse_args( get_option( 'neonews_membership_settings', array() ), function_exists( 'neonews_membership_default_settings' ) ? neonews_membership_default_settings() : array() );
    $smt  = wp_parse_args( get_option( 'neonews_smart_settings', array() ), function_exists( 'neonews_smart_default_settings' ) ? neonews_smart_default_settings() : array() );

    ?>
    <style>
    /* ── Master Control page styles ── */
    .nn-mc-wrap { max-width: 900px; }
    .nn-mc-section { background: #fff; border: 1px solid #c3c4c7; border-radius: 8px; margin-bottom: 14px; overflow: hidden; }
    .nn-mc-head { display: flex; align-items: center; gap: 10px; padding: 14px 18px; cursor: pointer; user-select: none; border-bottom: 1px solid #e0e0e0; }
    .nn-mc-head:hover { background: #f6f7f7; }
    .nn-mc-head h3 { margin: 0; font-size: 0.95rem; flex: 1; }
    .nn-mc-head .nn-mc-arrow { font-size: 0.8rem; color: #999; transition: transform .2s; }
    .nn-mc-section.open .nn-mc-arrow { transform: rotate(180deg); }
    .nn-mc-body { display: none; padding: 16px 20px; }
    .nn-mc-section.open .nn-mc-body { display: block; }
    .nn-mc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px 24px; margin-bottom: 16px; }
    .nn-mc-row { display: flex; align-items: center; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid #f0f0f1; }
    .nn-mc-row:last-child { border-bottom: none; }
    .nn-mc-label { font-size: 0.82rem; color: #1d2327; flex: 1; padding-right: 8px; }
    .nn-mc-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 20px; margin-top: 12px; }
    .nn-mc-fields label { display: flex; flex-direction: column; gap: 3px; font-size: 0.8rem; color: #555; }
    .nn-mc-fields input[type=text],.nn-mc-fields input[type=number],.nn-mc-fields input[type=email],.nn-mc-fields select,.nn-mc-fields textarea { padding: 5px 8px; border: 1px solid #c3c4c7; border-radius: 4px; font-size: 0.82rem; width: 100%; box-sizing: border-box; }
    .nn-mc-fields textarea { resize: vertical; min-height: 60px; }
    .nn-mc-sep { grid-column: 1 / -1; border: none; border-top: 1px solid #e8e8e8; margin: 4px 0; }
    .nn-mc-full { grid-column: 1 / -1; }
    /* Toggle switch */
    .nn-toggle { position: relative; display: inline-block; width: 40px; height: 22px; flex-shrink: 0; }
    .nn-toggle input { opacity: 0; width: 0; height: 0; }
    .nn-toggle-slider { position: absolute; inset: 0; background: #c3c4c7; border-radius: 22px; cursor: pointer; transition: .2s; }
    .nn-toggle-slider:before { content: ''; position: absolute; width: 16px; height: 16px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .2s; }
    .nn-toggle input:checked + .nn-toggle-slider { background: #2271b1; }
    .nn-toggle input:checked + .nn-toggle-slider:before { transform: translateX(18px); }
    </style>

    <h2><?php esc_html_e( 'Master Control', 'neonews' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Every feature and setting in one place. Click a section to expand it. Toggle switches are instant on/off. Save Settings applies everything.', 'neonews' ); ?></p>

    <div class="nn-mc-wrap">

    <?php
    /* ── helper to render a toggle row ── */
    $tog = function( $key, $label, $val ) {
        echo '<div class="nn-mc-row">';
        echo '<span class="nn-mc-label">' . esc_html( $label ) . '</span>';
        echo '<label class="nn-toggle"><input type="checkbox" name="neonews_platform_settings[' . esc_attr( $key ) . ']" value="1"' . checked( ! empty( $val ), true, false ) . ' /><span class="nn-toggle-slider"></span></label>';
        echo '</div>';
    };
    $tog_m = function( $key, $label, $val ) {
        echo '<div class="nn-mc-row">';
        echo '<span class="nn-mc-label">' . esc_html( $label ) . '</span>';
        echo '<label class="nn-toggle"><input type="checkbox" name="neonews_platform_settings[_membership][' . esc_attr( $key ) . ']" value="1"' . checked( ! empty( $val ), true, false ) . ' /><span class="nn-toggle-slider"></span></label>';
        echo '</div>';
    };
    $tog_s = function( $key, $label, $val ) {
        echo '<div class="nn-mc-row">';
        echo '<span class="nn-mc-label">' . esc_html( $label ) . '</span>';
        echo '<label class="nn-toggle"><input type="checkbox" name="neonews_platform_settings[_smart][' . esc_attr( $key ) . ']" value="1"' . checked( ! empty( $val ), true, false ) . ' /><span class="nn-toggle-slider"></span></label>';
        echo '</div>';
    };

    /* ─────────────────────────────────────────────────────────────────────
       1. APPEARANCE
    ──────────────────────────────────────────────────────────────────────── */
    ?>
    <div class="nn-mc-section open">
        <div class="nn-mc-head"><span>🌓</span><h3><?php esc_html_e( 'Appearance & Dark Mode', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <?php $tog( 'show_dark_mode_toggle', __( 'Show Dark / Light mode toggle to visitors', 'neonews' ), $settings['show_dark_mode_toggle'] ); ?>
            <div class="nn-mc-fields" style="margin-top:12px;">
                <label><?php esc_html_e( 'Default mode for new visitors', 'neonews' ); ?>
                    <select name="neonews_platform_settings[dark_mode_default]">
                        <option value="light" <?php selected( $settings['dark_mode_default'], 'light' ); ?>><?php esc_html_e( 'Light', 'neonews' ); ?></option>
                        <option value="dark"  <?php selected( $settings['dark_mode_default'], 'dark'  ); ?>><?php esc_html_e( 'Dark', 'neonews' ); ?></option>
                        <option value="system" <?php selected( $settings['dark_mode_default'], 'system' ); ?>><?php esc_html_e( 'Follow system preference', 'neonews' ); ?></option>
                    </select>
                </label>
                <label><?php esc_html_e( 'Carousel slide interval (ms)', 'neonews' ); ?>
                    <input type="number" name="neonews_platform_settings[carousel_interval]" value="<?php echo esc_attr( $settings['carousel_interval'] ); ?>" min="2000" max="30000" step="500" />
                    <span style="font-size:0.75rem;color:#888;"><?php esc_html_e( '5000 = 5 seconds between slides', 'neonews' ); ?></span>
                </label>
                <label><?php esc_html_e( 'Breaking news ticker speed (px/s)', 'neonews' ); ?>
                    <input type="number" name="neonews_platform_settings[breaking_scroll_speed]" value="<?php echo esc_attr( $settings['breaking_scroll_speed'] ); ?>" min="10" max="200" />
                    <span style="font-size:0.75rem;color:#888;"><?php esc_html_e( '40 = normal, higher = faster', 'neonews' ); ?></span>
                </label>
                <label><?php esc_html_e( 'Site logo mark', 'neonews' ); ?>
                    <input type="text" name="neonews_platform_settings[logo_mark]" value="<?php echo esc_attr( $settings['logo_mark'] ); ?>" />
                </label>
            </div>
        </div>
    </div>

    <?php /* ── 2. HOMEPAGE ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>🏠</span><h3><?php esc_html_e( 'Homepage', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <div class="nn-mc-grid">
                <?php
                $tog( 'show_home_hero',       __( 'Hero headline block', 'neonews' ),         $settings['show_home_hero'] );
                $tog( 'show_home_carousel',   __( 'Featured posts carousel', 'neonews' ),     $settings['show_home_carousel'] );
                $tog( 'show_home_categories', __( 'Category blocks', 'neonews' ),             $settings['show_home_categories'] );
                $tog( 'show_home_videos',     __( 'Videos section', 'neonews' ),              $settings['show_home_videos'] );
                $tog( 'show_home_tipline',    __( 'Pitch a Story / Tipline', 'neonews' ),     $settings['show_home_tipline'] );
                $tog( 'show_home_latest',     __( 'Latest news grid', 'neonews' ),            $settings['show_home_latest'] );
                $tog( 'show_home_newsletter', __( 'Daily Briefing / Newsletter box', 'neonews' ), $settings['show_home_newsletter'] );
                ?>
            </div>
            <div class="nn-mc-fields">
                <label><?php esc_html_e( 'Hero title', 'neonews' ); ?><input type="text" name="neonews_platform_settings[home_hero_title]" value="<?php echo esc_attr( $settings['home_hero_title'] ); ?>" /></label>
                <label><?php esc_html_e( 'Hero accent (colored part)', 'neonews' ); ?><input type="text" name="neonews_platform_settings[home_hero_title_accent]" value="<?php echo esc_attr( $settings['home_hero_title_accent'] ); ?>" /></label>
                <label class="nn-mc-full"><?php esc_html_e( 'Hero subtitle', 'neonews' ); ?><input type="text" name="neonews_platform_settings[home_hero_subtitle]" value="<?php echo esc_attr( $settings['home_hero_subtitle'] ); ?>" /></label>
                <hr class="nn-mc-sep">
                <label><?php esc_html_e( 'Carousel slide count', 'neonews' ); ?><input type="number" name="neonews_platform_settings[home_carousel_count]" value="<?php echo esc_attr( $settings['home_carousel_count'] ); ?>" min="1" max="12" /></label>
                <label><?php esc_html_e( 'Category posts per block', 'neonews' ); ?><input type="number" name="neonews_platform_settings[home_category_posts]" value="<?php echo esc_attr( $settings['home_category_posts'] ); ?>" min="1" max="10" /></label>
                <label><?php esc_html_e( 'Mid-page ad after category #', 'neonews' ); ?><input type="number" name="neonews_platform_settings[home_mid_ad_after_cat]" value="<?php echo esc_attr( $settings['home_mid_ad_after_cat'] ); ?>" min="0" max="10" /></label>
                <label><?php esc_html_e( 'Video section title', 'neonews' ); ?><input type="text" name="neonews_platform_settings[home_video_title]" value="<?php echo esc_attr( $settings['home_video_title'] ); ?>" /></label>
                <label><?php esc_html_e( 'Video section subtitle', 'neonews' ); ?><input type="text" name="neonews_platform_settings[home_video_subtitle]" value="<?php echo esc_attr( $settings['home_video_subtitle'] ); ?>" /></label>
                <label><?php esc_html_e( 'Video category slug', 'neonews' ); ?><input type="text" name="neonews_platform_settings[home_video_category]" value="<?php echo esc_attr( $settings['home_video_category'] ); ?>" /></label>
                <label><?php esc_html_e( 'Video count', 'neonews' ); ?><input type="number" name="neonews_platform_settings[home_video_count]" value="<?php echo esc_attr( $settings['home_video_count'] ); ?>" min="1" max="12" /></label>
                <label><?php esc_html_e( 'Latest posts count', 'neonews' ); ?><input type="number" name="neonews_platform_settings[home_latest_count]" value="<?php echo esc_attr( $settings['home_latest_count'] ); ?>" min="1" max="24" /></label>
            </div>
        </div>
    </div>

    <?php /* ── 3. HEADER ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>🔝</span><h3><?php esc_html_e( 'Header & Breaking News', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <div class="nn-mc-grid">
                <?php
                $tog( 'show_breaking_news',  __( 'Breaking news ticker', 'neonews' ),          $settings['show_breaking_news'] );
                $tog( 'show_header_search',  __( 'Search bar', 'neonews' ),                    $settings['show_header_search'] );
                $tog( 'show_header_auth',    __( 'Sign-in / account menu', 'neonews' ),        $settings['show_header_auth'] );
                $tog( 'show_header_social',  __( 'Social icons in header', 'neonews' ),        $settings['show_header_social'] );
                $tog( 'show_header_datetime', __( 'Date & time in header', 'neonews' ),        $settings['show_header_datetime'] );
                ?>
            </div>
            <div class="nn-mc-fields">
                <label><?php esc_html_e( 'Breaking "LIVE" label', 'neonews' ); ?><input type="text" name="neonews_platform_settings[breaking_label_live]" value="<?php echo esc_attr( $settings['breaking_label_live'] ); ?>" /></label>
                <label><?php esc_html_e( 'Breaking "Breaking" label', 'neonews' ); ?><input type="text" name="neonews_platform_settings[breaking_label_tag]" value="<?php echo esc_attr( $settings['breaking_label_tag'] ); ?>" /></label>
                <label><?php esc_html_e( 'Breaking news post count', 'neonews' ); ?><input type="number" name="neonews_platform_settings[breaking_count]" value="<?php echo esc_attr( $settings['breaking_count'] ); ?>" min="1" max="20" /></label>
                <label><?php esc_html_e( 'Search placeholder (desktop)', 'neonews' ); ?><input type="text" name="neonews_platform_settings[search_placeholder_desktop]" value="<?php echo esc_attr( $settings['search_placeholder_desktop'] ); ?>" /></label>
                <label><?php esc_html_e( 'Search placeholder (mobile)', 'neonews' ); ?><input type="text" name="neonews_platform_settings[search_placeholder_mobile]" value="<?php echo esc_attr( $settings['search_placeholder_mobile'] ); ?>" /></label>
            </div>
        </div>
    </div>

    <?php /* ── 4. FOOTER ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>🦶</span><h3><?php esc_html_e( 'Footer', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <div class="nn-mc-grid">
                <?php
                $tog( 'show_footer_social',     __( 'Social icons in footer', 'neonews' ),       $settings['show_footer_social'] );
                $tog( 'show_footer_newsletter', __( 'Newsletter signup in footer', 'neonews' ),  $settings['show_footer_newsletter'] );
                ?>
            </div>
            <div class="nn-mc-fields">
                <label class="nn-mc-full"><?php esc_html_e( 'Footer tagline', 'neonews' ); ?><input type="text" name="neonews_platform_settings[footer_tagline]" value="<?php echo esc_attr( $settings['footer_tagline'] ); ?>" /></label>
                <label><?php esc_html_e( 'Copyright text (leave blank = auto)', 'neonews' ); ?><input type="text" name="neonews_platform_settings[footer_copyright]" value="<?php echo esc_attr( $settings['footer_copyright'] ); ?>" /></label>
                <label><?php esc_html_e( 'Newsletter section title', 'neonews' ); ?><input type="text" name="neonews_platform_settings[footer_newsletter_title]" value="<?php echo esc_attr( $settings['footer_newsletter_title'] ); ?>" /></label>
                <label class="nn-mc-full"><?php esc_html_e( 'Newsletter description', 'neonews' ); ?><input type="text" name="neonews_platform_settings[footer_newsletter_text]" value="<?php echo esc_attr( $settings['footer_newsletter_text'] ); ?>" /></label>
                <label class="nn-mc-full"><?php esc_html_e( 'Newsletter form action URL', 'neonews' ); ?><input type="text" name="neonews_platform_settings[newsletter_form_action]" value="<?php echo esc_attr( $settings['newsletter_form_action'] ); ?>" placeholder="https://…" /></label>
            </div>
        </div>
    </div>

    <?php /* ── 5. SIDEBAR ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>📰</span><h3><?php esc_html_e( 'Sidebar', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <div class="nn-mc-grid">
                <?php
                $tog( 'sidebar_weather',     __( 'Weather widget', 'neonews' ),            $settings['sidebar_weather'] );
                $tog( 'sidebar_fresh_reads', __( 'Fresh Reads / Daily Briefing', 'neonews' ), $settings['sidebar_fresh_reads'] );
                $tog( 'sidebar_topics',      __( 'Topics / Tags cloud', 'neonews' ),       $settings['sidebar_topics'] );
                ?>
            </div>
            <div class="nn-mc-fields">
                <label><?php esc_html_e( 'Fresh Reads title', 'neonews' ); ?><input type="text" name="neonews_platform_settings[sidebar_fresh_reads_title]" value="<?php echo esc_attr( $settings['sidebar_fresh_reads_title'] ); ?>" /></label>
                <label><?php esc_html_e( 'Fresh Reads post count', 'neonews' ); ?><input type="number" name="neonews_platform_settings[sidebar_fresh_reads_count]" value="<?php echo esc_attr( $settings['sidebar_fresh_reads_count'] ); ?>" min="1" max="10" /></label>
                <label><?php esc_html_e( 'Topics count', 'neonews' ); ?><input type="number" name="neonews_platform_settings[sidebar_topics_count]" value="<?php echo esc_attr( $settings['sidebar_topics_count'] ); ?>" min="1" max="20" /></label>
                <label><?php esc_html_e( 'Weather city (fallback)', 'neonews' ); ?><input type="text" name="neonews_platform_settings[weather_city]" value="<?php echo esc_attr( $settings['weather_city'] ); ?>" /></label>
            </div>
        </div>
    </div>

    <?php /* ── 6. POST ENGAGEMENT ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>📝</span><h3><?php esc_html_e( 'Post Engagement', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <div class="nn-mc-grid">
                <?php
                $tog( 'show_engagement_bar', __( 'Engagement bar (likes, shares, comments)', 'neonews' ), $settings['show_engagement_bar'] );
                $tog( 'show_post_likes',     __( 'Like button', 'neonews' ),                $settings['show_post_likes'] );
                $tog( 'show_post_shares',    __( 'Share buttons', 'neonews' ),              $settings['show_post_shares'] );
                $tog( 'show_post_views',     __( 'View count', 'neonews' ),                 $settings['show_post_views'] );
                ?>
            </div>
            <div class="nn-mc-fields">
                <label><?php esc_html_e( 'Excerpt length (words)', 'neonews' ); ?><input type="number" name="neonews_platform_settings[excerpt_length]" value="<?php echo esc_attr( $settings['excerpt_length'] ); ?>" min="5" max="80" /></label>
                <label><?php esc_html_e( 'Reading speed (words/min)', 'neonews' ); ?><input type="number" name="neonews_platform_settings[reading_wpm]" value="<?php echo esc_attr( $settings['reading_wpm'] ); ?>" min="100" max="400" /></label>
            </div>
        </div>
    </div>

    <?php /* ── 7. ADVERTISING ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>📢</span><h3><?php esc_html_e( 'Advertising', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <div class="nn-mc-grid">
                <?php
                $tog( 'ads_master',            __( 'ALL ads — master switch', 'neonews' ),          $settings['ads_master'] );
                $tog( 'mobile_ad_collapsible', __( 'Mobile sticky ad — collapse toggle', 'neonews' ), $settings['mobile_ad_collapsible'] );
                ?>
            </div>
            <div class="nn-mc-fields">
                <label><?php esc_html_e( 'Mobile ad collapsed label', 'neonews' ); ?><input type="text" name="neonews_platform_settings[mobile_ad_collapsed_text]" value="<?php echo esc_attr( $settings['mobile_ad_collapsed_text'] ); ?>" placeholder="Ad" /></label>
            </div>
            <p class="description" style="margin-top:8px;"><?php esc_html_e( 'Individual ad zone on/off and AdSense codes are in the Ads & Integrations tab.', 'neonews' ); ?></p>
        </div>
    </div>

    <?php /* ── 8. WHATSAPP ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>💬</span><h3><?php esc_html_e( 'WhatsApp', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <div class="nn-mc-grid">
                <?php
                $tog( 'whatsapp_float_enabled',  __( 'Floating WhatsApp icon', 'neonews' ),        $settings['whatsapp_float_enabled'] );
                $tog( 'whatsapp_float_mobile',   __( 'Show float icon on mobile', 'neonews' ),     $settings['whatsapp_float_mobile'] );
                $tog( 'show_contact_whatsapp',   __( 'WhatsApp on Contact page', 'neonews' ),      $settings['show_contact_whatsapp'] );
                $tog( 'show_advertise_whatsapp', __( 'WhatsApp on Advertise page', 'neonews' ),    $settings['show_advertise_whatsapp'] );
                $tog( 'show_careers_whatsapp',   __( 'WhatsApp on Careers page', 'neonews' ),      $settings['show_careers_whatsapp'] );
                $tog( 'show_post_whatsapp',      __( 'WhatsApp share on posts', 'neonews' ),       $settings['show_post_whatsapp'] );
                ?>
            </div>
            <div class="nn-mc-fields">
                <label><?php esc_html_e( 'Default number (+country code)', 'neonews' ); ?><input type="text" name="neonews_platform_settings[whatsapp_number]" value="<?php echo esc_attr( $settings['whatsapp_number'] ); ?>" placeholder="911234567890" /></label>
                <label><?php esc_html_e( 'Business hours label', 'neonews' ); ?><input type="text" name="neonews_platform_settings[whatsapp_hours]" value="<?php echo esc_attr( $settings['whatsapp_hours'] ); ?>" placeholder="Mon–Fri 9am–6pm" /></label>
                <label><?php esc_html_e( 'Float button label', 'neonews' ); ?><input type="text" name="neonews_platform_settings[whatsapp_float_label]" value="<?php echo esc_attr( $settings['whatsapp_float_label'] ); ?>" /></label>
                <label><?php esc_html_e( 'Float button position', 'neonews' ); ?>
                    <select name="neonews_platform_settings[whatsapp_float_position]">
                        <option value="right" <?php selected( $settings['whatsapp_float_position'], 'right' ); ?>><?php esc_html_e( 'Right', 'neonews' ); ?></option>
                        <option value="left"  <?php selected( $settings['whatsapp_float_position'], 'left'  ); ?>><?php esc_html_e( 'Left', 'neonews' ); ?></option>
                    </select>
                </label>
            </div>
        </div>
    </div>

    <?php /* ── 9. SOCIAL PROFILES ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>🔗</span><h3><?php esc_html_e( 'Social Profiles', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <div class="nn-mc-grid">
                <?php
                $tog( 'show_header_social',  __( 'Social icons in header', 'neonews' ), $settings['show_header_social'] );
                $tog( 'show_footer_social',  __( 'Social icons in footer', 'neonews' ), $settings['show_footer_social'] );
                $tog( 'show_contact_social', __( 'Social icons on Contact page', 'neonews' ), $settings['show_contact_social'] );
                ?>
            </div>
            <div class="nn-mc-fields">
                <label>X / Twitter<input type="text" name="neonews_platform_settings[social_x]" value="<?php echo esc_attr( $settings['social_x'] ); ?>" placeholder="https://x.com/yourhandle" /></label>
                <label>Facebook<input type="text" name="neonews_platform_settings[social_facebook]" value="<?php echo esc_attr( $settings['social_facebook'] ); ?>" placeholder="https://facebook.com/yourpage" /></label>
                <label>LinkedIn<input type="text" name="neonews_platform_settings[social_linkedin]" value="<?php echo esc_attr( $settings['social_linkedin'] ); ?>" placeholder="https://linkedin.com/in/…" /></label>
                <label>Instagram<input type="text" name="neonews_platform_settings[social_instagram]" value="<?php echo esc_attr( $settings['social_instagram'] ); ?>" placeholder="https://instagram.com/…" /></label>
            </div>
        </div>
    </div>

    <?php /* ── 10. PREMIUM / EXCLUSIVE PAGE ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>👑</span><h3><?php esc_html_e( 'Premium Membership & Exclusive Page', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <?php if ( ! function_exists( 'neonews_membership_default_settings' ) ) : ?>
                <p class="description"><?php esc_html_e( 'NeoNews Membership plugin is not active.', 'neonews' ); ?></p>
            <?php else : ?>
            <div class="nn-mc-grid">
                <?php
                $tog_m( 'enable_paywall',             __( 'Enable paywall / article limit', 'neonews' ),       $mem['enable_paywall'] );
                $tog_m( 'hide_ads_for_premium',       __( 'Hide ads for premium members', 'neonews' ),         $mem['hide_ads_for_premium'] );
                $tog_m( 'enable_upgrade_popup',       __( 'Show upgrade popup to non-members', 'neonews' ),    $mem['enable_upgrade_popup'] );
                $tog_m( 'show_home_premium_section',  __( 'Premium Picks section on homepage', 'neonews' ),    $mem['show_home_premium_section'] );
                ?>
            </div>
            <div class="nn-mc-fields">
                <label><?php esc_html_e( 'Free articles before paywall', 'neonews' ); ?><input type="number" name="neonews_platform_settings[_membership][free_article_limit]" value="<?php echo esc_attr( $mem['free_article_limit'] ); ?>" min="0" max="100" /></label>
                <label><?php esc_html_e( 'Free article period', 'neonews' ); ?>
                    <select name="neonews_platform_settings[_membership][free_article_period]">
                        <option value="day"   <?php selected( $mem['free_article_period'], 'day'   ); ?>><?php esc_html_e( 'Per day', 'neonews' ); ?></option>
                        <option value="week"  <?php selected( $mem['free_article_period'], 'week'  ); ?>><?php esc_html_e( 'Per week', 'neonews' ); ?></option>
                        <option value="month" <?php selected( $mem['free_article_period'], 'month' ); ?>><?php esc_html_e( 'Per month', 'neonews' ); ?></option>
                    </select>
                </label>
                <label><?php esc_html_e( 'Popup delay (seconds)', 'neonews' ); ?><input type="number" name="neonews_platform_settings[_membership][popup_delay_seconds]" value="<?php echo esc_attr( $mem['popup_delay_seconds'] ); ?>" min="0" /></label>
                <label><?php esc_html_e( 'Popup re-show interval (hours)', 'neonews' ); ?><input type="number" name="neonews_platform_settings[_membership][popup_interval_hours]" value="<?php echo esc_attr( $mem['popup_interval_hours'] ); ?>" min="1" /></label>
                <label><?php esc_html_e( 'Exclusive page post count', 'neonews' ); ?><input type="number" name="neonews_platform_settings[_membership][exclusive_page_count]" value="<?php echo esc_attr( $mem['exclusive_page_count'] ); ?>" min="1" max="50" /></label>
                <label><?php esc_html_e( 'Homepage Premium Picks count', 'neonews' ); ?><input type="number" name="neonews_platform_settings[_membership][home_premium_count]" value="<?php echo esc_attr( $mem['home_premium_count'] ); ?>" min="1" max="12" /></label>
                <label><?php esc_html_e( 'Homepage Premium Picks title', 'neonews' ); ?><input type="text" name="neonews_platform_settings[_membership][home_premium_title]" value="<?php echo esc_attr( $mem['home_premium_title'] ); ?>" /></label>
                <label><?php esc_html_e( 'Exclusive page subtitle', 'neonews' ); ?><input type="text" name="neonews_platform_settings[_membership][exclusive_page_subtitle]" value="<?php echo esc_attr( $mem['exclusive_page_subtitle'] ); ?>" /></label>
                <label><?php esc_html_e( 'Plan title', 'neonews' ); ?><input type="text" name="neonews_platform_settings[_membership][plan_title]" value="<?php echo esc_attr( $mem['plan_title'] ); ?>" /></label>
                <label><?php esc_html_e( 'Plan price text', 'neonews' ); ?><input type="text" name="neonews_platform_settings[_membership][plan_price]" value="<?php echo esc_attr( $mem['plan_price'] ); ?>" /></label>
                <label><?php esc_html_e( 'Contact email for enrollment', 'neonews' ); ?><input type="email" name="neonews_platform_settings[_membership][contact_email]" value="<?php echo esc_attr( $mem['contact_email'] ); ?>" /></label>
                <label><?php esc_html_e( 'Activation key duration (days)', 'neonews' ); ?><input type="number" name="neonews_platform_settings[_membership][key_default_duration_days]" value="<?php echo esc_attr( $mem['key_default_duration_days'] ); ?>" min="1" /></label>
                <label class="nn-mc-full"><?php esc_html_e( 'Enroll note shown to non-members', 'neonews' ); ?><textarea name="neonews_platform_settings[_membership][enroll_note]"><?php echo esc_textarea( $mem['enroll_note'] ); ?></textarea></label>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php /* ── 11. SMART TOOLS ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>🤖</span><h3><?php esc_html_e( 'Smart Tools (AI-free)', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <?php if ( ! function_exists( 'neonews_smart_default_settings' ) ) : ?>
                <p class="description"><?php esc_html_e( 'NeoNews Smart plugin is not active.', 'neonews' ); ?></p>
            <?php else : ?>
            <div class="nn-mc-grid">
                <?php
                $tog_s( 'enable_auto_excerpt',      __( 'Auto-generate post excerpts', 'neonews' ),    $smt['enable_auto_excerpt'] );
                $tog_s( 'enable_story_glance',      __( 'Story at a glance box', 'neonews' ),          $smt['enable_story_glance'] );
                $tog_s( 'enable_key_points',        __( 'Key points summary on posts', 'neonews' ),    $smt['enable_key_points'] );
                $tog_s( 'enable_toc',               __( 'Table of contents on posts', 'neonews' ),     $smt['enable_toc'] );
                $tog_s( 'enable_smart_related',     __( 'Smart related articles', 'neonews' ),         $smt['enable_smart_related'] );
                $tog_s( 'enable_personalized_home', __( 'Personalized homepage feed', 'neonews' ),     $smt['enable_personalized_home'] );
                $tog_s( 'enable_weekly_digest',     __( 'Weekly digest email', 'neonews' ),            $smt['enable_weekly_digest'] );
                ?>
            </div>
            <div class="nn-mc-fields">
                <label><?php esc_html_e( 'Digest send day', 'neonews' ); ?>
                    <select name="neonews_platform_settings[_smart][digest_day]">
                        <?php $days = array( __('Sunday','neonews'), __('Monday','neonews'), __('Tuesday','neonews'), __('Wednesday','neonews'), __('Thursday','neonews'), __('Friday','neonews'), __('Saturday','neonews') );
                        foreach ( $days as $i => $d ) : ?>
                            <option value="<?php echo esc_attr( $i ); ?>" <?php selected( $smt['digest_day'], $i ); ?>><?php echo esc_html( $d ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label><?php esc_html_e( 'Digest send hour (0–23)', 'neonews' ); ?><input type="number" name="neonews_platform_settings[_smart][digest_hour]" value="<?php echo esc_attr( $smt['digest_hour'] ); ?>" min="0" max="23" /></label>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php /* ── 12. USERS & REGISTRATION ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>👤</span><h3><?php esc_html_e( 'Users & Registration', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <div class="nn-mc-grid">
                <?php
                $tog( 'auth_allow_registration', __( 'Allow new user registration', 'neonews' ),       $settings['auth_allow_registration'] );
                $tog( 'auth_auto_username',       __( 'Auto-generate username from email', 'neonews' ), $settings['auth_auto_username'] );
                $tog( 'captcha_enabled',          __( 'Enable CAPTCHA', 'neonews' ),                   $settings['captcha_enabled'] );
                $tog( 'captcha_on_login',         __( 'CAPTCHA on login', 'neonews' ),                 $settings['captcha_on_login'] );
                $tog( 'captcha_on_register',      __( 'CAPTCHA on registration', 'neonews' ),          $settings['captcha_on_register'] );
                ?>
            </div>
            <div class="nn-mc-fields">
                <label><?php esc_html_e( 'CAPTCHA provider', 'neonews' ); ?>
                    <select name="neonews_platform_settings[captcha_provider]">
                        <option value="turnstile"    <?php selected( $settings['captcha_provider'], 'turnstile' ); ?>><?php esc_html_e( 'Cloudflare Turnstile', 'neonews' ); ?></option>
                        <option value="recaptcha_v2" <?php selected( $settings['captcha_provider'], 'recaptcha_v2' ); ?>><?php esc_html_e( 'reCAPTCHA v2', 'neonews' ); ?></option>
                        <option value="recaptcha_v3" <?php selected( $settings['captcha_provider'], 'recaptcha_v3' ); ?>><?php esc_html_e( 'reCAPTCHA v3', 'neonews' ); ?></option>
                        <option value="hcaptcha"     <?php selected( $settings['captcha_provider'], 'hcaptcha' ); ?>><?php esc_html_e( 'hCaptcha', 'neonews' ); ?></option>
                    </select>
                </label>
                <label><?php esc_html_e( 'CAPTCHA site key', 'neonews' ); ?><input type="text" name="neonews_platform_settings[captcha_site_key]" value="<?php echo esc_attr( $settings['captcha_site_key'] ); ?>" /></label>
            </div>
        </div>
    </div>

    <?php /* ── 13. COOKIE & PRIVACY ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>🔒</span><h3><?php esc_html_e( 'Cookie & Privacy', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <div class="nn-mc-grid">
                <?php
                $tog( 'cookie_banner_enabled',   __( 'Cookie consent banner', 'neonews' ),          $settings['cookie_banner_enabled'] );
                $tog( 'cookie_gate_ads',         __( 'Block ads until consent', 'neonews' ),         $settings['cookie_gate_ads'] );
                $tog( 'cookie_gate_statistics',  __( 'Block analytics until consent', 'neonews' ),   $settings['cookie_gate_statistics'] );
                $tog( 'analytics_enabled',       __( 'Google Analytics', 'neonews' ),                $settings['analytics_enabled'] );
                $tog( 'weather_visitor_location', __( 'Per-visitor weather location (GPS/city)', 'neonews' ), $settings['weather_visitor_location'] );
                ?>
            </div>
            <div class="nn-mc-fields">
                <label><?php esc_html_e( 'Google Analytics ID', 'neonews' ); ?><input type="text" name="neonews_platform_settings[google_analytics_id]" value="<?php echo esc_attr( $settings['google_analytics_id'] ); ?>" placeholder="G-XXXXXXXXXX" /></label>
            </div>
        </div>
    </div>

    <?php /* ── 14. PERFORMANCE ── */ ?>
    <div class="nn-mc-section">
        <div class="nn-mc-head"><span>⚡</span><h3><?php esc_html_e( 'Performance', 'neonews' ); ?></h3><span class="nn-mc-arrow">▼</span></div>
        <div class="nn-mc-body">
            <div class="nn-mc-grid">
                <?php
                $tog( 'perf_lightweight_mode',         __( 'Lightweight mode (master switch)', 'neonews' ),     $settings['perf_lightweight_mode'] );
                $tog( 'perf_system_fonts',             __( 'Use system fonts (no Google Fonts)', 'neonews' ),   $settings['perf_system_fonts'] );
                $tog( 'perf_disable_emojis',           __( 'Disable WordPress emoji scripts', 'neonews' ),      $settings['perf_disable_emojis'] );
                $tog( 'perf_disable_embeds',           __( 'Disable oEmbed scripts', 'neonews' ),               $settings['perf_disable_embeds'] );
                $tog( 'perf_disable_dashicons',        __( 'Remove Dashicons on frontend', 'neonews' ),         $settings['perf_disable_dashicons'] );
                $tog( 'perf_disable_block_css',        __( 'Remove Gutenberg block CSS', 'neonews' ),           $settings['perf_disable_block_css'] );
                $tog( 'perf_disable_heartbeat',        __( 'Disable WordPress Heartbeat', 'neonews' ),          $settings['perf_disable_heartbeat'] );
                $tog( 'perf_lazy_captcha',             __( 'Lazy-load CAPTCHA (only on auth pages)', 'neonews' ), $settings['perf_lazy_captcha'] );
                $tog( 'perf_reduce_animations',        __( 'Reduce animations', 'neonews' ),                    $settings['perf_reduce_animations'] );
                $tog( 'perf_conditional_core_assets',  __( 'Conditional core asset loading', 'neonews' ),       $settings['perf_conditional_core_assets'] );
                ?>
            </div>
        </div>
    </div>

    </div><!-- .nn-mc-wrap -->

    <script>
    (function(){
        var STORE_KEY = 'nn_mc_open_sections';

        /* Give every section a stable id from its heading text */
        document.querySelectorAll('.nn-mc-section').forEach(function(sec, i){
            if ( ! sec.id ) {
                var h = sec.querySelector('.nn-mc-head h3');
                var slug = h ? h.textContent.trim().toLowerCase().replace(/[^a-z0-9]+/g,'-') : 'section-' + i;
                sec.id = 'nn-mc-' + slug;
            }
        });

        /* Restore previously open sections from localStorage */
        var saved = [];
        try { saved = JSON.parse( localStorage.getItem( STORE_KEY ) || '[]' ); } catch(e){}
        if ( saved.length ) {
            /* Close the default-open section first, then open saved ones */
            document.querySelectorAll('.nn-mc-section.open').forEach(function(s){ s.classList.remove('open'); });
            saved.forEach(function(id){
                var el = document.getElementById(id);
                if (el) el.classList.add('open');
            });
        }

        /* Toggle on click */
        document.querySelectorAll('.nn-mc-head').forEach(function(h){
            h.addEventListener('click', function(){
                h.closest('.nn-mc-section').classList.toggle('open');
            });
        });

        /* Before form submit, save which sections are open */
        var form = document.querySelector('form[action="options.php"]') || document.querySelector('.nn-mc-wrap')?.closest('form');
        if (form) {
            form.addEventListener('submit', function(){
                var open = [];
                document.querySelectorAll('.nn-mc-section.open').forEach(function(s){ if(s.id) open.push(s.id); });
                try { localStorage.setItem( STORE_KEY, JSON.stringify(open) ); } catch(e){}
            });
        }
    })();
    </script>
    <?php

}


/**
 * Render features & content tab.
 *
 * @param array $settings Current settings.
 */
function neonews_admin_tab_features( $settings ) {
    ?>
    <h2><?php esc_html_e( 'Post & engagement features', 'neonews' ); ?></h2>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Engagement bar', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_engagement_bar]" value="1" <?php checked( ! empty( $settings['show_engagement_bar'] ) ); ?> /> <?php esc_html_e( 'Show like/share bar on single posts', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Post likes', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_post_likes]" value="1" <?php checked( ! empty( $settings['show_post_likes'] ) ); ?> /> <?php esc_html_e( 'Enable like button', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Post shares', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_post_shares]" value="1" <?php checked( ! empty( $settings['show_post_shares'] ) ); ?> /> <?php esc_html_e( 'Enable share buttons', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'View counts in meta', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_post_views]" value="1" <?php checked( ! empty( $settings['show_post_views'] ) ); ?> /> <?php esc_html_e( 'Show view counts on cards and meta lines', 'neonews' ); ?></label></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Excerpt length', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[excerpt_length]" value="<?php echo esc_attr( $settings['excerpt_length'] ); ?>" min="5" max="80" class="small-text" /> <?php esc_html_e( 'words', 'neonews' ); ?></td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Reading speed', 'neonews' ); ?></th>
            <td><input type="number" name="neonews_platform_settings[reading_wpm]" value="<?php echo esc_attr( $settings['reading_wpm'] ); ?>" min="100" max="400" class="small-text" /> <?php esc_html_e( 'words per minute', 'neonews' ); ?></td>
        </tr>
    </table>

    <h2><?php esc_html_e( 'Category accent colors', 'neonews' ); ?></h2>
    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Custom colors', 'neonews' ); ?></th>
            <td>
                <textarea name="neonews_platform_settings[category_colors]" rows="6" class="large-text code" placeholder="business:#16a34a&#10;technology:#ca8a04"><?php echo esc_textarea( $settings['category_colors'] ); ?></textarea>
                <p class="description"><?php esc_html_e( 'One per line: slug:#hexcolor. Overrides default category bar colors on homepage.', 'neonews' ); ?></p>
            </td>
        </tr>
    </table>

    <h2><?php esc_html_e( 'Design & typography', 'neonews' ); ?></h2>
    <p><?php
    printf(
        /* translators: %s: Customizer URL */
        esc_html__( 'Fonts, colors, and layout density are controlled in %s for live preview.', 'neonews' ),
        '<a href="' . esc_url( admin_url( 'customize.php' ) ) . '">' . esc_html__( 'Appearance → Customize → NewsPulse Design', 'neonews' ) . '</a>'
    );
    ?></p>
    <?php
}
