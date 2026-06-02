<?php
/**
 * Shortcode [lba_assessment] and front-end asset registration.
 */

defined( 'ABSPATH' ) || exit;

/* ----------------------------------------------------------------
   Register assets (not enqueued yet — conditional below)
---------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', function () {
    wp_register_style(
        'ack-lba-front',
        ACK_LBA_URL . 'assets/lba-front.css',
        [],
        ACK_LBA_VERSION
    );
    wp_register_script(
        'ack-lba-front',
        ACK_LBA_URL . 'assets/lba-front.js',
        [],
        ACK_LBA_VERSION,
        true // footer
    );
} );

/* ----------------------------------------------------------------
   Shortcode
---------------------------------------------------------------- */
add_shortcode( 'lba_assessment', 'ack_lba_shortcode' );

function ack_lba_shortcode() {
    $settings = get_option( ACK_LBA_OPTION, ack_lba_defaults() );

    // Enqueue & localize on demand
    wp_enqueue_style( 'ack-lba-front' );
    wp_enqueue_script( 'ack-lba-front' );
    wp_localize_script( 'ack-lba-front', 'ackLbaConfig', ack_lba_build_config( $settings ) );

    $gen = $settings['general'];

    ob_start();
    ?>
    <div class="lba-wrap">
        <div class="lba-inner">

            <!-- ===================================================
                 FORM VIEW
            =================================================== -->
            <div id="lba-form-view">

                <h1 class="lba-title"><?php echo wp_kses( $gen['title'], [ 'em' => [], 'strong' => [] ] ); ?></h1>
                <p class="lba-intro"><?php echo wp_kses( $gen['intro'], [ 'strong' => [], 'em' => [], 'br' => [] ] ); ?></p>

                <div class="lba-progress-bar">
                    <div class="lba-progress-fill" id="lba-progress-fill"></div>
                    <div class="lba-progress-label">
                        <span class="lba-answered-count" id="lba-answered-count">0</span>
                        of <span id="lba-total-count">…</span> answered
                    </div>
                </div>

                <div id="lba-questions-container"></div>

                <div class="lba-submit-area">
                    <button class="lba-submit-btn" id="lba-submit-btn" disabled onclick="lbaShowResults()">
                        See my results
                    </button>
                    <div class="lba-submit-hint" id="lba-submit-hint">Loading…</div>
                </div>

            </div><!-- #lba-form-view -->

            <!-- ===================================================
                 RESULTS VIEW
            =================================================== -->
            <div id="lba-results-view">

                <div class="lba-results-header">
                    <h2>Here's what the data shows.</h2>
                    <p>Your scores reveal a pattern — and patterns are the starting point for everything we do.</p>
                </div>

                <div class="lba-scores" id="lba-scores-display"></div>
                <div class="lba-patterns" id="lba-patterns-display"></div>

                <div class="lba-cta-block">
                    <h3 id="lba-cta-heading"><?php echo esc_html( $gen['cta_heading'] ); ?></h3>
                    <p id="lba-cta-body"><?php echo esc_html( $gen['cta_body'] ); ?></p>
                    <a class="lba-cta-btn" id="lba-cta-btn" href="mailto:<?php echo esc_attr( $gen['cta_email'] ); ?>">
                        <?php echo esc_html( $gen['cta_label'] ); ?>
                    </a>
                </div>

            </div><!-- #lba-results-view -->

        </div><!-- .lba-inner -->
    </div><!-- .lba-wrap -->
    <?php
    return ob_get_clean();
}

/* ----------------------------------------------------------------
   Build the config object passed to JS
---------------------------------------------------------------- */
function ack_lba_build_config( $settings ) {
    // Sections: pass only what JS needs
    $sections = array_map( function ( $sec ) {
        return [
            'label' => $sec['label'],
            'color' => $sec['color'],
            'items' => array_values( $sec['items'] ),
            'tiers' => [
                'low_ceil'  => (float) $sec['tiers']['low_ceil'],
                'mid_ceil'  => (float) $sec['tiers']['mid_ceil'],
                'low_text'  => $sec['tiers']['low_text'],
                'mid_text'  => $sec['tiers']['mid_text'],
                'high_text' => $sec['tiers']['high_text'],
            ],
        ];
    }, array_values( $settings['sections'] ) );

    return [
        'sections'   => $sections,
        'thresholds' => [
            'high' => (float) $settings['thresholds']['high'],
            'low'  => (float) $settings['thresholds']['low'],
        ],
        'patterns'   => array_values( $settings['patterns'] ),
        'catch_alls' => $settings['catch_alls'],
        'general'    => $settings['general'],
    ];
}
