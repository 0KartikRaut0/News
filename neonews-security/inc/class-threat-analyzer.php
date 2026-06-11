<?php
/**
 * AI-style threat analysis from scan findings.
 *
 * @package NeoNews_Security
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Threat analyzer — rule-based intelligent assessment.
 */
class NeoNews_Threat_Analyzer {

    /**
     * Analyze findings and produce actionable report.
     *
     * @param array $findings Scan findings.
     * @return array
     */
    public static function analyze( $findings ) {
        $score   = NeoNews_Security_Scanner::calculate_score( $findings );
        $counts  = array( 'critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0 );
        $fixable = array();
        $manual  = array();

        foreach ( $findings as $f ) {
            $sev = $f['severity'] ?? 'low';
            if ( isset( $counts[ $sev ] ) ) {
                $counts[ $sev ]++;
            }
            if ( ! empty( $f['fixable'] ) ) {
                $fixable[] = $f;
            } else {
                $manual[] = $f;
            }
        }

        $risk_level = self::risk_label( $score, $counts );
        $summary    = self::build_summary( $score, $counts, $risk_level );
        $actions    = self::prioritized_actions( $findings );
        $narrative  = self::build_narrative( $findings, $score, $risk_level );

        return array(
            'score'       => $score,
            'risk_level'  => $risk_level,
            'summary'     => $summary,
            'narrative'   => $narrative,
            'counts'      => $counts,
            'fixable'     => $fixable,
            'manual'      => $manual,
            'actions'     => $actions,
            'fix_all_ids' => array_values( array_unique( array_filter( wp_list_pluck( $fixable, 'id' ) ) ) ),
        );
    }

    /**
     * Risk label from score.
     *
     * @param int   $score  Score.
     * @param array $counts Severity counts.
     * @return string
     */
    private static function risk_label( $score, $counts ) {
        if ( $counts['critical'] > 0 || $score < 50 ) {
            return 'critical';
        }
        if ( $counts['high'] > 0 || $score < 70 ) {
            return 'high';
        }
        if ( $counts['medium'] > 0 || $score < 85 ) {
            return 'elevated';
        }
        return 'good';
    }

    /**
     * One-line summary.
     *
     * @param int    $score Score.
     * @param array  $counts Counts.
     * @param string $risk Risk.
     * @return string
     */
    private static function build_summary( $score, $counts, $risk ) {
        $total = array_sum( $counts );
        if ( 'good' === $risk ) {
            return sprintf(
                /* translators: 1: score, 2: finding count */
                __( 'Security posture is strong (score %1$d/100). %2$d minor item(s) to review.', 'neonews-security' ),
                $score,
                $total
            );
        }
        return sprintf(
            /* translators: 1: risk level, 2: score, 3: finding count */
            __( 'Risk level: %1$s — score %2$d/100 with %3$d finding(s). Apply recommended fixes below.', 'neonews-security' ),
            ucfirst( $risk ),
            $score,
            $total
        );
    }

    /**
     * Plain-language narrative.
     *
     * @param array  $findings Findings.
     * @param int    $score    Score.
     * @param string $risk     Risk.
     * @return string
     */
    private static function build_narrative( $findings, $score, $risk ) {
        $parts = array();

        $parts[] = __( 'AI Security Analysis', 'neonews-security' ) . ' — ' . sprintf(
            /* translators: %s: datetime */
            __( 'Generated %s', 'neonews-security' ),
            wp_date( 'M j, Y g:i A' )
        );

        if ( 'good' === $risk ) {
            $parts[] = __( 'Your NeoNews site follows most security best practices. Continue monitoring the activity log and run weekly scans.', 'neonews-security' );
        } elseif ( 'critical' === $risk ) {
            $parts[] = __( 'Immediate action recommended. Critical exposure detected that could lead to credential theft or site takeover. Apply one-click fixes first, then address manual items (HTTPS, updates).', 'neonews-security' );
        } else {
            $parts[] = __( 'Several hardening gaps were found. Attackers often chain low-severity issues — apply automated fixes to reduce your attack surface.', 'neonews-security' );
        }

        $top = array_slice( $findings, 0, 3 );
        if ( $top ) {
            $parts[] = __( 'Top priorities:', 'neonews-security' );
            foreach ( $top as $i => $f ) {
                $parts[] = ( $i + 1 ) . '. [' . strtoupper( $f['severity'] ) . '] ' . $f['title'];
            }
        }

        $fixable_count = count( array_filter( $findings, function( $f ) {
            return ! empty( $f['fixable'] );
        } ) );

        if ( $fixable_count > 0 ) {
            $parts[] = sprintf(
                /* translators: %d: count */
                __( '%d issue(s) can be fixed automatically with one click.', 'neonews-security' ),
                $fixable_count
            );
        }

        return implode( "\n\n", $parts );
    }

    /**
     * Prioritized action list with fix buttons.
     *
     * @param array $findings Findings.
     * @return array
     */
    private static function prioritized_actions( $findings ) {
        $order = array( 'critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3 );
        usort( $findings, function( $a, $b ) use ( $order ) {
            $sa = $order[ $a['severity'] ?? 'low' ] ?? 4;
            $sb = $order[ $b['severity'] ?? 'low' ] ?? 4;
            return $sa <=> $sb;
        } );

        $actions = array();
        foreach ( $findings as $f ) {
            $actions[] = array(
                'id'          => $f['id'],
                'severity'    => $f['severity'],
                'title'       => $f['title'],
                'detail'      => $f['detail'],
                'fixable'     => ! empty( $f['fixable'] ),
                'button_label'=> ! empty( $f['fixable'] )
                    ? __( 'Fix automatically', 'neonews-security' )
                    : __( 'View instructions', 'neonews-security' ),
                'instruction' => self::manual_instruction( $f['id'] ),
            );
        }
        return $actions;
    }

    /**
     * Manual fix instructions.
     *
     * @param string $fix_id Fix ID.
     * @return string
     */
    private static function manual_instruction( $fix_id ) {
        $map = array(
            'force_ssl_admin'       => __( 'Install an SSL certificate in your hosting panel, then set WordPress URLs to https:// in Settings → General.', 'neonews-security' ),
            'rename_admin_user'     => __( 'Users → Add New (administrator) → log in → delete the old "admin" account.', 'neonews-security' ),
            'change_db_prefix'      => __( 'Only change on new installs using a migration tool. Not recommended on live sites.', 'neonews-security' ),
            'upgrade_php'           => __( 'Contact your host to upgrade PHP to 8.1 or 8.2 in the hosting control panel.', 'neonews-security' ),
            'update_plugins'        => __( 'Dashboard → Updates → Update all plugins immediately.', 'neonews-security' ),
            'update_themes'         => __( 'Dashboard → Updates → Update themes.', 'neonews-security' ),
            'disable_debug_display' => __( 'In wp-config.php set WP_DEBUG to false and WP_DEBUG_DISPLAY to false.', 'neonews-security' ),
            'harden_file_permissions' => __( 'Set wp-config.php permissions to 440 via FTP or SSH: chmod 440 wp-config.php', 'neonews-security' ),
            'review_stale_admins'   => __( 'Users → All Users — demote or delete unused administrator accounts.', 'neonews-security' ),
            'disable_display_errors'=> __( 'Set display_errors = Off in php.ini or hosting PHP settings.', 'neonews-security' ),
        );
        return $map[ $fix_id ] ?? __( 'Follow WordPress security best practices or contact your host.', 'neonews-security' );
    }
}
