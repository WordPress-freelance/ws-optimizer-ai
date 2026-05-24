<?php
defined( 'ABSPATH' ) || exit;

// $post est disponible via render_metabox().
$cached_analysis  = get_post_meta( $post->ID, '_wsoa_last_analysis', true );
$cached_title_val = get_post_meta( $post->ID, '_wsoa_last_analyzed_title', true );
$has_cache        = ! empty( $cached_analysis ) && is_array( $cached_analysis );
?>
<div class="wsoa-metabox">

    <div class="wsoa-metabox__actions">
        <button type="button"
                class="wsoa-btn-analyze"
                data-post-id="<?php echo esc_attr( $post->ID ); ?>">
            <?php echo $has_cache
                ? esc_html__( 'Ré-analyser', 'ws-optimizer-ai' )
                : esc_html__( 'Analyser le titre', 'ws-optimizer-ai' ); ?>
        </button>
        <span class="wsoa-spinner" style="display:none;" aria-hidden="true"></span>
    </div>

    <?php if ( $cached_title_val ) : ?>
        <p class="wsoa-metabox__cached-title">
            <?php echo esc_html( sprintf(
                /* translators: %s: titre précédemment analysé */
                __( 'Analysé : « %s »', 'ws-optimizer-ai' ),
                $cached_title_val
            ) ); ?>
        </p>
    <?php endif; ?>

    <div class="wsoa-metabox__result" id="wsoa-result-<?php echo esc_attr( $post->ID ); ?>">
        <?php if ( $has_cache ) :
            $a     = $cached_analysis;
            $score = isset( $a['score'] ) ? (int) $a['score'] : 0;
            $color = $score >= 80 ? '#22c55e' : ( $score >= 60 ? '#f59e0b' : '#ef4444' );
        ?>
        <div class="wsoa-result">
            <div class="wsoa-result__score" style="color:<?php echo esc_attr( $color ); ?>">
                <?php echo esc_html( $score ); ?><span>/100</span>
            </div>
            <div class="wsoa-result__verdict">
                <?php echo esc_html( $a['verdict'] ?? '' ); ?>
            </div>
            <?php if ( ! empty( $a['analysis'] ) ) : ?>
                <p class="wsoa-result__text"><?php echo esc_html( $a['analysis'] ); ?></p>
            <?php endif; ?>

            <?php if ( ! empty( $a['strengths'] ) ) : ?>
                <div class="wsoa-result__section wsoa-result__section--ok">
                    <strong>✅ <?php esc_html_e( 'Atouts', 'ws-optimizer-ai' ); ?></strong>
                    <ul>
                        <?php foreach ( $a['strengths'] as $s ) : ?>
                            <li><?php echo esc_html( $s ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $a['issues'] ) ) : ?>
                <div class="wsoa-result__section wsoa-result__section--err">
                    <strong>❌ <?php esc_html_e( 'Problèmes', 'ws-optimizer-ai' ); ?></strong>
                    <ul>
                        <?php foreach ( $a['issues'] as $issue ) :
                            $icon = ( isset( $issue['severity'] ) && 'critical' === $issue['severity'] ) ? '🚨' : '⚠️';
                        ?>
                            <li><?php echo $icon . ' ' . esc_html( $issue['message'] ?? '' ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $a['recommendations'] ) ) : ?>
                <div class="wsoa-result__section wsoa-result__section--tip">
                    <strong>💡 <?php esc_html_e( 'Recommandations', 'ws-optimizer-ai' ); ?></strong>
                    <ul>
                        <?php foreach ( $a['recommendations'] as $r ) : ?>
                            <li><?php echo esc_html( $r ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

</div>
