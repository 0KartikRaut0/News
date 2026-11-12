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
            <th><?php esc_html_e( 'Carousel style', 'neonews' ); ?></th>
            <td>
                <select name="neonews_platform_settings[home_carousel_style]">
                    <?php foreach ( neonews_home_layout_choices( 'carousel' ) as $key => $label ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['home_carousel_style'] ?? 'overlay', $key ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Hover & mouse effects', 'neonews' ); ?></th>
            <td>
                <select name="neonews_platform_settings[home_hover_effect]">
                    <?php foreach ( neonews_home_hover_choices() as $key => $label ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['home_hover_effect'] ?? 'lift', $key ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e( 'Applies to post cards, list rows, video tiles, carousel slides, and the homepage banner (where supported). Respects “Reduce motion” and touch devices.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Default category layout', 'neonews' ); ?></th>
            <td>
                <select name="neonews_platform_settings[home_category_layout]">
                    <?php foreach ( neonews_home_layout_choices( 'posts' ) as $key => $label ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['home_category_layout'] ?? 'featured-list', $key ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e( 'Default for category blocks (override per block in the table below).', 'neonews' ); ?></p>
            </td>
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
                        <th><?php esc_html_e( 'Layout', 'neonews' ); ?></th>
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
                    var layoutOpts = <?php echo wp_json_encode( neonews_home_layout_choices( 'posts' ) ); ?>;
                    var layoutDefaultOpt = <?php echo wp_json_encode( __( '— site default —', 'neonews' ) ); ?>;

                    function layoutSelectHtml(selected) {
                        var out = '<option value="">' + layoutDefaultOpt + '</option>';
                        Object.keys(layoutOpts).forEach(function(k){
                            out += '<option value="'+esc(k)+'"'+(k===selected?' selected':'')+'>'+esc(layoutOpts[k])+'</option>';
                        });
                        return out;
                    }

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
                           +'<td><select class="nn-cb-layout">'+layoutSelectHtml(blk.layout||'')+'</select></td>'
                           +'<td><button type="button" class="nn-cb-del" title="Remove">✕</button></td>';

                        function update(){
                            blk.slug        = tr.querySelector('.nn-cb-slug').value;
                            blk.label       = tr.querySelector('.nn-cb-label').value;
                            blk.count       = parseInt(tr.querySelector('.nn-cb-count').value)||3;
                            blk.color       = tr.querySelector('.nn-cb-color').value;
                            blk.see_all_url = tr.querySelector('.nn-cb-seeall').value;
                            blk.layout      = tr.querySelector('.nn-cb-layout').value;
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
                        var blk={slug:'',label:'',color:'#ff7a18',count:3,see_all_url:'',layout:'',enabled:true};
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
            <th><?php esc_html_e( 'Latest section layout', 'neonews' ); ?></th>
            <td>
                <select name="neonews_platform_settings[home_latest_layout]">
                    <?php foreach ( neonews_home_layout_choices( 'latest' ) as $key => $label ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['home_latest_layout'] ?? 'cards', $key ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><?php esc_html_e( 'Videos layout', 'neonews' ); ?></th>
            <td>
                <select name="neonews_platform_settings[home_video_layout]">
                    <?php foreach ( neonews_home_layout_choices( 'video' ) as $key => $label ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['home_video_layout'] ?? 'grid', $key ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
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
    <h3 style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <?php esc_html_e( 'Section order', 'neonews' ); ?>
        <button type="button" id="nn-restore-order" class="button button-small" style="font-size:0.8rem;" title="<?php esc_attr_e( 'Reset to the default homepage section sequence', 'neonews' ); ?>">
            ↺ <?php esc_html_e( 'Restore default order', 'neonews' ); ?>
        </button>
    </h3>
    <p class="description"><?php esc_html_e( 'Drag rows up or down to change the order sections appear on the homepage. Click "Restore default order" to reset to the original sequence, then save.', 'neonews' ); ?></p>
    <input type="hidden" name="neonews_platform_settings[home_section_order]" id="nn-section-order-input" value="<?php echo esc_attr( implode( ',', $order_keys ) ); ?>" />
    <ul id="nn-section-sortable" style="max-width:520px;margin:0.75rem 0 1.5rem;padding:0;list-style:none;border:1px solid #c3c4c7;border-radius:6px;overflow:hidden;">
        <?php foreach ( $order_keys as $k ) :
            if ( ! isset( $section_labels[ $k ] ) ) continue; ?>
            <li data-key="<?php echo esc_attr( $k ); ?>" style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#fff;border-bottom:1px solid #c3c4c7;cursor:grab;user-select:none;" class="nn-sort-row">
                <span style="color:#aaa;font-size:1.1rem;line-height:1;">⣿</span>
                <span><?php echo esc_html( $section_labels[ $k ] ); ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
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

        // ── Restore default order ─────────────────────────────────────────
        var defaultOrder = <?php echo wp_json_encode( array( 'hero_text', 'carousel', 'ad_hero', 'categories', 'videos', 'tipline', 'latest', 'ad_footer' ) ); ?>;
        var sectionLabels = <?php echo wp_json_encode( $section_labels ); ?>;

        document.getElementById('nn-restore-order').addEventListener('click', function(){
            if ( ! confirm(<?php echo wp_json_encode( __( 'Reset homepage section order to the default sequence? Custom sections will stay at the bottom. You still need to click Save to apply.', 'neonews' ) ); ?>) ) return;

            // Collect any custom rows so we can re-append them after defaults.
            var customRows = [];
            list.querySelectorAll('.nn-sort-row.nn-sort-custom').forEach(function(r){ customRows.push(r); });

            // Clear the list.
            list.innerHTML = '';

            // Re-build default rows.
            defaultOrder.forEach(function(key){
                var label = sectionLabels[key];
                if (!label) return;
                var li = document.createElement('li');
                li.setAttribute('data-key', key);
                li.className = 'nn-sort-row';
                li.setAttribute('draggable', 'true');
                li.style.cssText = 'display:flex;align-items:center;gap:10px;padding:10px 14px;background:#fff;border-bottom:1px solid #c3c4c7;cursor:grab;user-select:none;';
                li.innerHTML = '<span style="color:#aaa;font-size:1.1rem;line-height:1;">⣿</span><span>' + label + '</span>';
                attachDragToRow(li);
                list.appendChild(li);
            });

            // Re-append custom rows at the end.
            customRows.forEach(function(r){ attachDragToRow(r); list.appendChild(r); });

            saveOrder();

            // Brief visual feedback.
            var btn = document.getElementById('nn-restore-order');
            btn.textContent = '✓ <?php echo esc_js( __( 'Order restored — save to apply', 'neonews' ) ); ?>';
            btn.style.color = '#00a32a';
            setTimeout(function(){ btn.innerHTML = '↺ <?php echo esc_js( __( 'Restore default order', 'neonews' ) ); ?>'; btn.style.color=''; }, 3000);
        });

        // ── Custom section rows in sort list ──────────────────────────────
        function addCustomSortRow(cs) {
            var existing = list.querySelector('[data-key="custom_' + cs.id + '"]');
            if (existing) { return; }
            var icons = { template: '🧩', posts: '📰', tag: '📰', page: '📄', html: '🖊' };
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

    <?php
    $hs_templates_admin = array();
    foreach ( neonews_home_custom_templates() as $tid => $tdef ) {
        $hs_templates_admin[ $tid ] = array(
            'label'       => $tdef['label'],
            'description' => $tdef['description'],
            'icon'        => $tdef['icon'],
            'fields'      => $tdef['fields'],
        );
    }
    $hs_template_defaults = array();
    foreach ( array_keys( $hs_templates_admin ) as $tid ) {
        $hs_template_defaults[ $tid ] = neonews_home_custom_template_defaults( $tid );
    }
    ?>

    <h3><?php esc_html_e( 'Custom sections', 'neonews' ); ?></h3>
    <p class="description"><?php esc_html_e( 'Drop in ready-made blocks (newsletter, stats, promo, video, topic chips, and more) — no HTML needed. Or add a post feed from a category/tag, or embed a WordPress page. Drag sections in the order list above.', 'neonews' ); ?></p>

    <input type="hidden" name="neonews_platform_settings[home_custom_sections]" id="nn-custom-sections-json" value="<?php echo esc_attr( wp_json_encode( array_values( (array) ( $settings['home_custom_sections'] ?? array() ) ) ) ); ?>" />

    <div id="nn-custom-sections-wrap" style="max-width:720px;"></div>

    <p style="margin-top:0.75rem;display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
        <button type="button" id="nn-add-custom-section" class="button button-primary">+ <?php esc_html_e( 'Add section', 'neonews' ); ?></button>
        <span class="description" style="margin:0;"><?php esc_html_e( 'Tip: choose “Ready-made template” and pick a design — fill in text only.', 'neonews' ); ?></span>
    </p>

    <style>
    .nn-cs-row{background:#fff;border:1px solid #c3c4c7;border-radius:8px;padding:14px 16px;margin-bottom:12px;box-shadow:0 1px 2px rgba(0,0,0,.04);}
    .nn-cs-row h4{margin:0 0 10px;display:flex;justify-content:space-between;align-items:center;font-size:0.9rem;}
    .nn-cs-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px 14px;}
    .nn-cs-grid label{display:flex;flex-direction:column;gap:3px;font-size:0.82rem;font-weight:600;color:#50575e;}
    .nn-cs-grid input,.nn-cs-grid select,.nn-cs-grid textarea{padding:5px 8px;border:1px solid #c3c4c7;border-radius:4px;font-size:0.85rem;width:100%;box-sizing:border-box;}
    .nn-cs-template-intro{grid-column:1/-1;font-size:0.8rem;color:#646970;margin:-4px 0 4px;}
    .nn-cs-template-fields{grid-column:1/-1;display:grid;grid-template-columns:1fr 1fr;gap:8px 14px;padding:10px 12px;background:#f6f7f7;border-radius:6px;border:1px solid #dcdcde;}
    .nn-cs-template-fields label{font-weight:600;font-size:0.8rem;}
    .nn-cs-template-fields textarea{min-height:56px;}
    .nn-cs-template-fields .nn-cs-field-full{grid-column:1/-1;}
    .nn-cs-row .nn-cs-page-wrap,.nn-cs-row .nn-cs-posts-wrap{grid-column:1/-1;}
    </style>

    <?php require __DIR__ . '/admin-custom-sections.js.php'; ?>

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
            <th><?php esc_html_e( 'Save / favorites', 'neonews' ); ?></th>
            <td><label><input type="checkbox" name="neonews_platform_settings[show_post_favorites]" value="1" <?php checked( ! empty( $settings['show_post_favorites'] ) ); ?> /> <?php esc_html_e( 'Enable bookmark button (saved only for the signed-in user)', 'neonews' ); ?></label></td>
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

    <h2><?php esc_html_e( 'Article back button', 'neonews' ); ?></h2>
    <table class="form-table">
        <tr>
            <th><label for="neonews-article-back-color"><?php esc_html_e( 'Button color', 'neonews' ); ?></label></th>
            <td>
                <?php
                $back_color = $settings['article_back_color'] ?? '#ff7a18';
                ?>
                <input type="color" id="neonews-article-back-color-picker" value="<?php echo esc_attr( $back_color ); ?>" />
                <input type="text" name="neonews_platform_settings[article_back_color]" id="neonews-article-back-color" value="<?php echo esc_attr( $back_color ); ?>" class="regular-text" pattern="#[0-9a-fA-F]{6}" style="max-width:8rem;margin-left:0.5rem;" />
                <p class="description"><?php esc_html_e( 'Default matches the orange “back to top” arrow. Used on the floating back control at the bottom of single posts.', 'neonews' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="neonews-article-back-opacity"><?php esc_html_e( 'Idle opacity', 'neonews' ); ?></label></th>
            <td>
                <input type="range" id="neonews-article-back-opacity-range" min="5" max="100" step="1" value="<?php echo esc_attr( (int) ( $settings['article_back_opacity'] ?? 52 ) ); ?>" style="vertical-align:middle;width:220px;" />
                <input type="number" name="neonews_platform_settings[article_back_opacity]" id="neonews-article-back-opacity" value="<?php echo esc_attr( (int) ( $settings['article_back_opacity'] ?? 52 ) ); ?>" min="5" max="100" step="1" class="small-text" style="margin-left:0.5rem;" />
                <span>%</span>
                <p class="description"><?php esc_html_e( 'How see-through the button is while reading (hover/focus goes to full opacity).', 'neonews' ); ?></p>
            </td>
        </tr>
    </table>
    <script>
    (function() {
        var picker = document.getElementById('neonews-article-back-color-picker');
        var hex = document.getElementById('neonews-article-back-color');
        if (picker && hex) {
            picker.addEventListener('input', function() { hex.value = picker.value; });
            hex.addEventListener('input', function() {
                if (/^#[0-9a-fA-F]{6}$/.test(hex.value)) { picker.value = hex.value; }
            });
        }
        var range = document.getElementById('neonews-article-back-opacity-range');
        var num = document.getElementById('neonews-article-back-opacity');
        if (range && num) {
            range.addEventListener('input', function() { num.value = range.value; });
            num.addEventListener('input', function() { range.value = num.value; });
        }
    })();
    </script>

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
