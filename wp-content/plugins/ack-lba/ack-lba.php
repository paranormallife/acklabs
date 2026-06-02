<?php
/**
 * Plugin Name: Ack LBA — Leadership Balance Assessment
 * Plugin URI:  https://acklabs.org
 * Description: Renders a configurable Likert-scale assessment with per-section scoring, tier diagnoses, and a cross-dimension pattern matrix. Output via [lba_assessment] shortcode.
 * Version:     1.0.0
 * Author:      Ackerbauer Laboratories
 * Author URI:  https://acklabs.org
 * License:     GPL-2.0-or-later
 * Text Domain: ack-lba
 */

defined( 'ABSPATH' ) || exit;

define( 'ACK_LBA_VERSION', '1.0.0' );
define( 'ACK_LBA_DIR',     plugin_dir_path( __FILE__ ) );
define( 'ACK_LBA_URL',     plugin_dir_url( __FILE__ ) );
define( 'ACK_LBA_OPTION',  'ack_lba_settings' );

/* ----------------------------------------------------------------
   Default settings – used on first activation
---------------------------------------------------------------- */
function ack_lba_defaults() {
    return [
        'general' => [
            'title'      => 'Where does your team <em>actually</em> stand?',
            'intro'      => 'Most teams already have a sense something\'s off — they just can\'t name it precisely. This assessment helps you <strong>see the pattern</strong>, not just feel it. 15 questions. About 5 minutes.',
            'cta_heading' => 'Ready to name it?',
            'cta_body'   => 'Your results tell part of the story. A conversation tells the rest. Reach out and your scores will be included automatically — so we can start where you actually are.',
            'cta_label'  => 'Reach out to me. I\'ll listen.',
            'cta_email'  => 'hello@acklabs.org',
        ],
        'sections' => [
            [
                'label' => 'Alignment',
                'color' => '#6db3f2',
                'items' => [
                    'We consistently validate that our work aligns with our core purpose and priorities.',
                    'Before starting work, we invest time in defining the real problem or opportunity.',
                    'We adjust priorities when conditions change and communicate those adjustments clearly.',
                    'We choose what to work on based on impact rather than urgency or habit.',
                    'We stop or redesign work quickly when it no longer produces value.',
                ],
                'tiers' => [
                    'low_ceil'  => 2.5,
                    'mid_ceil'  => 3.5,
                    'low_text'  => 'The team may be busy without clear direction — effort is dispersed rather than focused. Work is chosen by urgency or habit rather than impact.',
                    'mid_text'  => 'Alignment is inconsistent. Priorities shift without clear communication, and it\'s not always obvious why certain work matters.',
                    'high_text' => 'The team has a clear sense of purpose and chooses work based on impact. Priorities are visible and communicated when they change.',
                ],
            ],
            [
                'label' => 'Structure',
                'color' => '#7dd4a8',
                'items' => [
                    'Our work is broken into clear steps with shared definitions of \'done.\'',
                    'Roles, owners, and next steps are visible and well understood.',
                    'We maintain \'just enough\' structure — neither too rigid nor too loose.',
                    'We iterate in short cycles that help us learn, adapt, and make steady progress.',
                    'We minimize duplicated effort and ensure expectations are understood by all.',
                ],
                'tiers' => [
                    'low_ceil'  => 2.5,
                    'mid_ceil'  => 3.5,
                    'low_text'  => 'Roles, handoffs, and definitions of \'done\' are unclear. Duplicated effort and confusion about ownership slow things down.',
                    'mid_text'  => 'Some structure exists but it\'s uneven — certain things work smoothly while others create friction or depend on specific individuals.',
                    'high_text' => 'Work flows through clear steps with shared ownership. The team can iterate and adapt without losing momentum.',
                ],
            ],
            [
                'label' => 'Engagement',
                'color' => '#e8a045',
                'items' => [
                    'People feel comfortable sharing concerns, questions, or ideas without fear.',
                    'We interact with respect, even during disagreement or pressure.',
                    'We regularly reflect on our work and process to improve our performance.',
                    'Our pace feels sustainable — urgency does not become the default setting.',
                    'Contributions and progress are acknowledged in meaningful ways.',
                ],
                'tiers' => [
                    'low_ceil'  => 2.5,
                    'mid_ceil'  => 3.5,
                    'low_text'  => 'People may be showing up without really being present. Concerns go unvoiced, pace feels unsustainable, and contributions go unacknowledged.',
                    'mid_text'  => 'Engagement is uneven — some people feel invested, others feel like they\'re just getting through it. Psychological safety is inconsistent.',
                    'high_text' => 'The team feels safe to speak up, works at a sustainable pace, and sees their contributions as meaningful.',
                ],
            ],
        ],
        'thresholds' => [
            'high' => 3.5,
            'low'  => 2.8,
        ],
        'patterns' => [
            // Each entry: [high_section_index, low_section_index, title, desc]
            // These are ordered pairs evaluated in sequence; first matches win.
            [
                'high_idx' => 0,
                'low_idx'  => 1,
                'title'    => 'Clear on direction — fuzzy on execution',
                'desc'     => 'You know where you\'re going but the operating rhythm to get there isn\'t holding. The work needs more consistent structure to match the ambition.',
            ],
            [
                'high_idx' => 1,
                'low_idx'  => 2,
                'title'    => 'Process is solid — people feel the pressure',
                'desc'     => 'The mechanics are working, but psychological safety and trust aren\'t keeping pace. When people can\'t speak up, the system starts hiding its own problems.',
            ],
            [
                'high_idx' => 2,
                'low_idx'  => 0,
                'title'    => 'Strong team, scattered effort',
                'desc'     => 'There\'s real energy and connection here — but without clearer direction and shared priorities, that energy disperses. Alignment is the missing multiplier.',
            ],
            [
                'high_idx' => 0,
                'low_idx'  => 2,
                'title'    => 'Direction is set — people aren\'t with you yet',
                'desc'     => 'Strategy is clear but engagement hasn\'t caught up. The work ahead is less about what to do and more about bringing people along.',
            ],
        ],
        'catch_alls' => [
            'all_low' => [
                'avg_ceil' => 2.5,
                'title'    => 'Significant imbalance across the board',
                'desc'     => 'All three dimensions are under stress at once — this usually means the team is running on resilience rather than design. The good news: a clear starting point exists.',
            ],
            'balanced' => [
                'avg_floor' => 3.8,
                'title'     => 'More balanced than most',
                'desc'      => 'Your team is performing with real balance across all dimensions. The work at this stage is about sustaining what\'s working and finding where to sharpen.',
            ],
            'mixed' => [
                'title' => 'Inconsistent performance across dimensions',
                'desc'  => 'Your scores show a mixed picture — some areas are holding, others aren\'t. Pinpointing where the drag is coming from is where a conversation would start.',
            ],
        ],
    ];
}

/* ----------------------------------------------------------------
   Activation — seed defaults if no option exists yet
---------------------------------------------------------------- */
register_activation_hook( __FILE__, function () {
    if ( false === get_option( ACK_LBA_OPTION ) ) {
        add_option( ACK_LBA_OPTION, ack_lba_defaults() );
    }
} );

/* ----------------------------------------------------------------
   Load sub-files
---------------------------------------------------------------- */
require_once ACK_LBA_DIR . 'includes/admin.php';
require_once ACK_LBA_DIR . 'includes/shortcode.php';
