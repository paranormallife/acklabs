<?php
/**
 * Admin menu, settings page, and save handler for Ack LBA.
 */

defined( 'ABSPATH' ) || exit;

/* ----------------------------------------------------------------
   Register menu
---------------------------------------------------------------- */
add_action( 'admin_menu', function () {
    add_options_page(
        'Leadership Assessment',
        'Leadership Assessment',
        'manage_options',
        'ack-lba',
        'ack_lba_settings_page'
    );
} );

/* ----------------------------------------------------------------
   Enqueue WP color picker + our admin assets on the settings page
---------------------------------------------------------------- */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( $hook !== 'settings_page_ack-lba' ) {
        return;
    }
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_script(
        'ack-lba-admin',
        ACK_LBA_URL . 'assets/admin.js',
        [ 'jquery', 'wp-color-picker' ],
        ACK_LBA_VERSION,
        true
    );
    wp_enqueue_style(
        'ack-lba-admin',
        ACK_LBA_URL . 'assets/admin.css',
        [],
        ACK_LBA_VERSION
    );
} );

/* ----------------------------------------------------------------
   Save handler
---------------------------------------------------------------- */
add_action( 'admin_post_ack_lba_save', function () {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized', 403 );
    }
    check_admin_referer( 'ack_lba_save', 'ack_lba_nonce' );

    $raw      = isset( $_POST['ack_lba'] ) ? $_POST['ack_lba'] : [];
    $settings = ack_lba_sanitize( $raw );

    update_option( ACK_LBA_OPTION, $settings );
    wp_redirect( admin_url( 'options-general.php?page=ack-lba&saved=1' ) );
    exit;
} );

/* ----------------------------------------------------------------
   Sanitize
---------------------------------------------------------------- */
function ack_lba_sanitize( $raw ) {
    $defaults = ack_lba_defaults();

    // --- General ---
    $gen = isset( $raw['general'] ) ? $raw['general'] : [];
    $settings['general'] = [
        'title'       => wp_kses( isset( $gen['title'] ) ? $gen['title'] : $defaults['general']['title'], [ 'em' => [], 'strong' => [] ] ),
        'intro'       => wp_kses( isset( $gen['intro'] ) ? $gen['intro'] : $defaults['general']['intro'], [ 'strong' => [], 'em' => [], 'br' => [] ] ),
        'cta_heading' => sanitize_text_field( isset( $gen['cta_heading'] ) ? $gen['cta_heading'] : $defaults['general']['cta_heading'] ),
        'cta_body'    => sanitize_textarea_field( isset( $gen['cta_body'] ) ? $gen['cta_body'] : $defaults['general']['cta_body'] ),
        'cta_label'   => sanitize_text_field( isset( $gen['cta_label'] ) ? $gen['cta_label'] : $defaults['general']['cta_label'] ),
        'cta_email'   => sanitize_email( isset( $gen['cta_email'] ) ? $gen['cta_email'] : $defaults['general']['cta_email'] ),
    ];

    // --- Sections ---
    $settings['sections'] = [];
    $raw_sections = isset( $raw['sections'] ) && is_array( $raw['sections'] ) ? $raw['sections'] : [];
    foreach ( $raw_sections as $sec ) {
        $items = [];
        if ( isset( $sec['items'] ) && is_array( $sec['items'] ) ) {
            foreach ( $sec['items'] as $item ) {
                $t = sanitize_text_field( $item );
                if ( $t !== '' ) {
                    $items[] = $t;
                }
            }
        }
        if ( empty( $items ) ) {
            continue; // skip sections with no questions
        }
        $color = isset( $sec['color'] ) ? sanitize_hex_color( $sec['color'] ) : '#6db3f2';
        if ( ! $color ) {
            $color = '#6db3f2';
        }
        $tiers = isset( $sec['tiers'] ) ? $sec['tiers'] : [];
        $settings['sections'][] = [
            'label' => sanitize_text_field( isset( $sec['label'] ) ? $sec['label'] : 'Section' ),
            'color' => $color,
            'items' => $items,
            'tiers' => [
                'low_ceil'  => isset( $tiers['low_ceil'] ) ? floatval( $tiers['low_ceil'] ) : 2.5,
                'mid_ceil'  => isset( $tiers['mid_ceil'] ) ? floatval( $tiers['mid_ceil'] ) : 3.5,
                'low_text'  => sanitize_textarea_field( isset( $tiers['low_text'] ) ? $tiers['low_text'] : '' ),
                'mid_text'  => sanitize_textarea_field( isset( $tiers['mid_text'] ) ? $tiers['mid_text'] : '' ),
                'high_text' => sanitize_textarea_field( isset( $tiers['high_text'] ) ? $tiers['high_text'] : '' ),
            ],
        ];
    }
    if ( empty( $settings['sections'] ) ) {
        $settings['sections'] = $defaults['sections'];
    }

    // --- Thresholds ---
    $thr = isset( $raw['thresholds'] ) ? $raw['thresholds'] : [];
    $settings['thresholds'] = [
        'high' => isset( $thr['high'] ) ? floatval( $thr['high'] ) : 3.5,
        'low'  => isset( $thr['low'] )  ? floatval( $thr['low'] )  : 2.8,
    ];

    // --- Pattern matrix ---
    $settings['patterns'] = [];
    $raw_patterns = isset( $raw['patterns'] ) && is_array( $raw['patterns'] ) ? $raw['patterns'] : [];
    foreach ( $raw_patterns as $p ) {
        $title = sanitize_text_field( isset( $p['title'] ) ? $p['title'] : '' );
        $desc  = sanitize_textarea_field( isset( $p['desc'] ) ? $p['desc'] : '' );
        if ( $title === '' && $desc === '' ) {
            continue;
        }
        $settings['patterns'][] = [
            'high_idx' => intval( isset( $p['high_idx'] ) ? $p['high_idx'] : 0 ),
            'low_idx'  => intval( isset( $p['low_idx'] )  ? $p['low_idx']  : 1 ),
            'title'    => $title,
            'desc'     => $desc,
        ];
    }

    // --- Catch-alls ---
    $ca = isset( $raw['catch_alls'] ) ? $raw['catch_alls'] : [];
    $settings['catch_alls'] = [
        'all_low' => [
            'avg_ceil' => isset( $ca['all_low']['avg_ceil'] ) ? floatval( $ca['all_low']['avg_ceil'] ) : 2.5,
            'title'    => sanitize_text_field( isset( $ca['all_low']['title'] ) ? $ca['all_low']['title'] : $defaults['catch_alls']['all_low']['title'] ),
            'desc'     => sanitize_textarea_field( isset( $ca['all_low']['desc'] ) ? $ca['all_low']['desc'] : $defaults['catch_alls']['all_low']['desc'] ),
        ],
        'balanced' => [
            'avg_floor' => isset( $ca['balanced']['avg_floor'] ) ? floatval( $ca['balanced']['avg_floor'] ) : 3.8,
            'title'     => sanitize_text_field( isset( $ca['balanced']['title'] ) ? $ca['balanced']['title'] : $defaults['catch_alls']['balanced']['title'] ),
            'desc'      => sanitize_textarea_field( isset( $ca['balanced']['desc'] ) ? $ca['balanced']['desc'] : $defaults['catch_alls']['balanced']['desc'] ),
        ],
        'mixed' => [
            'title' => sanitize_text_field( isset( $ca['mixed']['title'] ) ? $ca['mixed']['title'] : $defaults['catch_alls']['mixed']['title'] ),
            'desc'  => sanitize_textarea_field( isset( $ca['mixed']['desc'] ) ? $ca['mixed']['desc'] : $defaults['catch_alls']['mixed']['desc'] ),
        ],
    ];

    return $settings;
}

/* ================================================================
   Settings Page HTML
================================================================ */
function ack_lba_settings_page() {
    $settings = get_option( ACK_LBA_OPTION, ack_lba_defaults() );
    $saved    = isset( $_GET['saved'] );
    $sections = $settings['sections'];
    $gen      = $settings['general'];
    $thr      = $settings['thresholds'];
    $patterns = $settings['patterns'];
    $ca       = $settings['catch_alls'];
    ?>
    <div class="wrap ack-lba-admin">
        <h1>Leadership Assessment Settings</h1>

        <?php if ( $saved ) : ?>
            <div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="ack_lba_save">
            <?php wp_nonce_field( 'ack_lba_save', 'ack_lba_nonce' ); ?>

            <!-- ====================================================
                 TAB NAV
            ==================================================== -->
            <nav class="nav-tab-wrapper ack-lba-tabs">
                <a href="#tab-general"  class="nav-tab nav-tab-active">General</a>
                <a href="#tab-sections" class="nav-tab">Sections &amp; Questions</a>
                <a href="#tab-tiers"    class="nav-tab">Tier Diagnoses</a>
                <a href="#tab-patterns" class="nav-tab">Pattern Matrix</a>
            </nav>

            <!-- ====================================================
                 TAB: GENERAL
            ==================================================== -->
            <div id="tab-general" class="ack-lba-tab">
                <table class="form-table" role="presentation">
                    <tr>
                        <th><label for="lba_title">Assessment Title</label></th>
                        <td>
                            <input type="text" id="lba_title" name="ack_lba[general][title]"
                                   value="<?php echo esc_attr( $gen['title'] ); ?>"
                                   class="large-text">
                            <p class="description">Supports <code>&lt;em&gt;</code> and <code>&lt;strong&gt;</code> tags.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="lba_intro">Intro Paragraph</label></th>
                        <td>
                            <textarea id="lba_intro" name="ack_lba[general][intro]" class="large-text" rows="4"><?php echo esc_textarea( $gen['intro'] ); ?></textarea>
                            <p class="description">Supports <code>&lt;strong&gt;</code> and <code>&lt;em&gt;</code> tags.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="lba_cta_heading">CTA Heading</label></th>
                        <td><input type="text" id="lba_cta_heading" name="ack_lba[general][cta_heading]"
                                   value="<?php echo esc_attr( $gen['cta_heading'] ); ?>" class="large-text"></td>
                    </tr>
                    <tr>
                        <th><label for="lba_cta_body">CTA Body</label></th>
                        <td><textarea id="lba_cta_body" name="ack_lba[general][cta_body]" class="large-text" rows="3"><?php echo esc_textarea( $gen['cta_body'] ); ?></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="lba_cta_label">CTA Button Label</label></th>
                        <td><input type="text" id="lba_cta_label" name="ack_lba[general][cta_label]"
                                   value="<?php echo esc_attr( $gen['cta_label'] ); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="lba_cta_email">CTA Email Address</label></th>
                        <td><input type="email" id="lba_cta_email" name="ack_lba[general][cta_email]"
                                   value="<?php echo esc_attr( $gen['cta_email'] ); ?>" class="regular-text"></td>
                    </tr>
                </table>
            </div>

            <!-- ====================================================
                 TAB: SECTIONS & QUESTIONS
            ==================================================== -->
            <div id="tab-sections" class="ack-lba-tab" style="display:none;">
                <p class="description" style="margin:16px 0 12px;">Drag to reorder sections. Each section can have any number of questions.</p>

                <div id="ack-lba-sections-list">
                    <?php foreach ( $sections as $si => $sec ) : ?>
                        <?php ack_lba_render_section_row( $si, $sec ); ?>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="button" id="ack-lba-add-section">+ Add Section</button>
            </div>

            <!-- ====================================================
                 TAB: TIER DIAGNOSES
            ==================================================== -->
            <div id="tab-tiers" class="ack-lba-tab" style="display:none;">
                <p class="description" style="margin:16px 0 24px;">
                    Each section's score (1–5 average) is mapped to <strong>Low</strong>, <strong>Mid</strong>, or <strong>High</strong> using two ceiling values you set per section.
                    A score ≤ Low Ceiling → Low diagnosis. A score ≤ Mid Ceiling → Mid. Above Mid Ceiling → High.
                </p>
                <div id="ack-lba-tiers-list">
                    <?php foreach ( $sections as $si => $sec ) : ?>
                        <?php ack_lba_render_tier_row( $si, $sec ); ?>
                    <?php endforeach; ?>
                </div>
                <p class="description" style="margin-top:16px;">Tier panels update automatically when you add or rename sections on the Sections tab.</p>
            </div>

            <!-- ====================================================
                 TAB: PATTERN MATRIX
            ==================================================== -->
            <div id="tab-patterns" class="ack-lba-tab" style="display:none;">
                <h3>Score Thresholds</h3>
                <table class="form-table" role="presentation">
                    <tr>
                        <th><label>HIGH threshold (≥)</label></th>
                        <td>
                            <input type="number" name="ack_lba[thresholds][high]"
                                   value="<?php echo esc_attr( $thr['high'] ); ?>"
                                   step="0.1" min="1" max="5" style="width:80px;">
                            <p class="description">A section scoring at or above this value is considered "high."</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label>LOW threshold (&lt;)</label></th>
                        <td>
                            <input type="number" name="ack_lba[thresholds][low]"
                                   value="<?php echo esc_attr( $thr['low'] ); ?>"
                                   step="0.1" min="1" max="5" style="width:80px;">
                            <p class="description">A section scoring below this value is considered "low."</p>
                        </td>
                    </tr>
                </table>

                <h3 style="margin-top:24px;">Pattern Rules <span class="description">(evaluated in order; first matches win)</span></h3>
                <p class="description" style="margin-bottom:16px;">
                    Each rule fires when one section score is HIGH (≥ threshold) <em>and</em> another is LOW (&lt; threshold).
                    Multiple rules can match simultaneously.
                </p>

                <div id="ack-lba-patterns-list">
                    <?php foreach ( $patterns as $pi => $pat ) : ?>
                        <?php ack_lba_render_pattern_row( $pi, $pat, $sections ); ?>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="button" id="ack-lba-add-pattern">+ Add Pattern Rule</button>

                <h3 style="margin-top:32px;">Catch-all Patterns</h3>
                <p class="description" style="margin-bottom:16px;">
                    These fire only when no matrix rule matched. Evaluated in order: <em>All Low</em> → <em>Balanced</em> → <em>Mixed</em> (always fires last).
                </p>

                <div class="ack-lba-catchall-block">
                    <h4>All Low <span class="description">(fires when average score &lt; threshold below)</span></h4>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th>Average Score Ceiling</th>
                            <td><input type="number" name="ack_lba[catch_alls][all_low][avg_ceil]"
                                       value="<?php echo esc_attr( $ca['all_low']['avg_ceil'] ); ?>"
                                       step="0.1" min="1" max="5" style="width:80px;"></td>
                        </tr>
                        <tr>
                            <th>Title</th>
                            <td><input type="text" name="ack_lba[catch_alls][all_low][title]"
                                       value="<?php echo esc_attr( $ca['all_low']['title'] ); ?>" class="large-text"></td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td><textarea name="ack_lba[catch_alls][all_low][desc]" class="large-text" rows="3"><?php echo esc_textarea( $ca['all_low']['desc'] ); ?></textarea></td>
                        </tr>
                    </table>
                </div>

                <div class="ack-lba-catchall-block">
                    <h4>Balanced <span class="description">(fires when average score ≥ threshold below)</span></h4>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th>Average Score Floor</th>
                            <td><input type="number" name="ack_lba[catch_alls][balanced][avg_floor]"
                                       value="<?php echo esc_attr( $ca['balanced']['avg_floor'] ); ?>"
                                       step="0.1" min="1" max="5" style="width:80px;"></td>
                        </tr>
                        <tr>
                            <th>Title</th>
                            <td><input type="text" name="ack_lba[catch_alls][balanced][title]"
                                       value="<?php echo esc_attr( $ca['balanced']['title'] ); ?>" class="large-text"></td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td><textarea name="ack_lba[catch_alls][balanced][desc]" class="large-text" rows="3"><?php echo esc_textarea( $ca['balanced']['desc'] ); ?></textarea></td>
                        </tr>
                    </table>
                </div>

                <div class="ack-lba-catchall-block">
                    <h4>Mixed / Fallback <span class="description">(always fires if nothing else matched)</span></h4>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th>Title</th>
                            <td><input type="text" name="ack_lba[catch_alls][mixed][title]"
                                       value="<?php echo esc_attr( $ca['mixed']['title'] ); ?>" class="large-text"></td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td><textarea name="ack_lba[catch_alls][mixed][desc]" class="large-text" rows="3"><?php echo esc_textarea( $ca['mixed']['desc'] ); ?></textarea></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php submit_button( 'Save Settings' ); ?>
        </form>

        <!-- Hidden template: section row -->
        <script type="text/html" id="tmpl-lba-section">
            <?php ack_lba_render_section_row( '__SI__', [ 'label' => '', 'color' => '#6db3f2', 'items' => [] ], true ); ?>
        </script>

        <!-- Hidden template: question row -->
        <script type="text/html" id="tmpl-lba-question">
            <div class="ack-lba-question-row">
                <span class="ack-lba-drag-handle dashicons dashicons-move"></span>
                <input type="text" name="ack_lba[sections][__SI__][items][]" value="" class="large-text" placeholder="Question text">
                <button type="button" class="button-link ack-lba-remove-question" aria-label="Remove question">&#10005;</button>
            </div>
        </script>

        <!-- Hidden template: pattern row -->
        <script type="text/html" id="tmpl-lba-pattern">
            <?php ack_lba_render_pattern_row( '__PI__', [ 'high_idx' => 0, 'low_idx' => 1, 'title' => '', 'desc' => '' ], $sections, true ); ?>
        </script>
    </div><!-- .wrap -->
    <?php
}

/* ----------------------------------------------------------------
   Render helpers
---------------------------------------------------------------- */
function ack_lba_render_section_row( $si, $sec, $template = false ) {
    $is_tmpl = ( $si === '__SI__' );
    $label   = $is_tmpl ? '' : esc_attr( $sec['label'] );
    $color   = $is_tmpl ? '#6db3f2' : esc_attr( $sec['color'] );
    $items   = $is_tmpl ? [] : $sec['items'];
    ?>
    <div class="ack-lba-section-row postbox" data-si="<?php echo esc_attr( $si ); ?>">
        <div class="ack-lba-section-header">
            <span class="ack-lba-drag-handle dashicons dashicons-move"></span>
            <input type="text"
                   name="ack_lba[sections][<?php echo esc_attr( $si ); ?>][label]"
                   value="<?php echo $label; ?>"
                   class="regular-text ack-lba-section-label"
                   placeholder="Section label">
            <input type="text"
                   name="ack_lba[sections][<?php echo esc_attr( $si ); ?>][color]"
                   value="<?php echo $color; ?>"
                   class="ack-lba-color-picker"
                   data-default-color="#6db3f2">
            <button type="button" class="button-link ack-lba-remove-section" aria-label="Remove section">&#10005; Remove section</button>
        </div>

        <div class="ack-lba-questions-list">
            <?php foreach ( $items as $item ) : ?>
            <div class="ack-lba-question-row">
                <span class="ack-lba-drag-handle dashicons dashicons-move"></span>
                <input type="text"
                       name="ack_lba[sections][<?php echo esc_attr( $si ); ?>][items][]"
                       value="<?php echo esc_attr( $item ); ?>"
                       class="large-text"
                       placeholder="Question text">
                <button type="button" class="button-link ack-lba-remove-question" aria-label="Remove question">&#10005;</button>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="button ack-lba-add-question" data-si="<?php echo esc_attr( $si ); ?>">+ Add Question</button>
    </div>
    <?php
}

function ack_lba_render_tier_row( $si, $sec ) {
    $label = esc_html( $sec['label'] ?: 'Section ' . ( $si + 1 ) );
    $t     = $sec['tiers'];
    ?>
    <div class="ack-lba-tier-row postbox" data-si="<?php echo esc_attr( $si ); ?>">
        <h3 class="hndle"><span><?php echo $label; ?></span></h3>
        <div class="inside">
            <table class="form-table" role="presentation">
                <tr>
                    <th>Low Ceiling (≤)</th>
                    <td><input type="number" name="ack_lba[sections][<?php echo esc_attr( $si ); ?>][tiers][low_ceil]"
                               value="<?php echo esc_attr( $t['low_ceil'] ); ?>" step="0.1" min="1" max="5" style="width:80px;"></td>
                </tr>
                <tr>
                    <th>Mid Ceiling (≤)</th>
                    <td><input type="number" name="ack_lba[sections][<?php echo esc_attr( $si ); ?>][tiers][mid_ceil]"
                               value="<?php echo esc_attr( $t['mid_ceil'] ); ?>" step="0.1" min="1" max="5" style="width:80px;"></td>
                </tr>
                <tr>
                    <th>Low Diagnosis</th>
                    <td><textarea name="ack_lba[sections][<?php echo esc_attr( $si ); ?>][tiers][low_text]"
                                  class="large-text" rows="3"><?php echo esc_textarea( $t['low_text'] ); ?></textarea></td>
                </tr>
                <tr>
                    <th>Mid Diagnosis</th>
                    <td><textarea name="ack_lba[sections][<?php echo esc_attr( $si ); ?>][tiers][mid_text]"
                                  class="large-text" rows="3"><?php echo esc_textarea( $t['mid_text'] ); ?></textarea></td>
                </tr>
                <tr>
                    <th>High Diagnosis</th>
                    <td><textarea name="ack_lba[sections][<?php echo esc_attr( $si ); ?>][tiers][high_text]"
                                  class="large-text" rows="3"><?php echo esc_textarea( $t['high_text'] ); ?></textarea></td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

function ack_lba_render_pattern_row( $pi, $pat, $sections, $template = false ) {
    $is_tmpl  = ( $pi === '__PI__' );
    $name_pre = 'ack_lba[patterns][' . esc_attr( $pi ) . ']';

    // Build section option lists
    ob_start();
    foreach ( $sections as $si => $sec ) {
        $lbl = esc_html( $sec['label'] ?: 'Section ' . ( $si + 1 ) );
        echo '<option value="' . esc_attr( $si ) . '">' . $lbl . '</option>';
    }
    $opts = ob_get_clean();
    ?>
    <div class="ack-lba-pattern-row postbox">
        <div class="inside">
            <table class="form-table" role="presentation">
                <tr>
                    <th>HIGH section (≥ threshold)</th>
                    <td>
                        <select name="<?php echo $name_pre; ?>[high_idx]">
                            <?php echo $opts; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>LOW section (&lt; threshold)</th>
                    <td>
                        <select name="<?php echo $name_pre; ?>[low_idx]">
                            <?php echo $opts; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Pattern Title</th>
                    <td><input type="text" name="<?php echo $name_pre; ?>[title]"
                               value="<?php echo $is_tmpl ? '' : esc_attr( $pat['title'] ); ?>"
                               class="large-text"></td>
                </tr>
                <tr>
                    <th>Pattern Description</th>
                    <td><textarea name="<?php echo $name_pre; ?>[desc]"
                                  class="large-text" rows="3"><?php echo $is_tmpl ? '' : esc_textarea( $pat['desc'] ); ?></textarea></td>
                </tr>
            </table>
            <button type="button" class="button-link ack-lba-remove-pattern" style="color:#b32d2e;">&#10005; Remove this rule</button>
        </div>
    </div>
    <?php
}
