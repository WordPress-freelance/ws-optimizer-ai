<?php
// $post is available via render_metabox().
defined( 'ABSPATH' ) || exit;

$last_analysis = get_post_meta( $post->ID, '_wsoa_last_analysis', true );
$last_title    = get_post_meta( $post->ID, '_wsoa_last_analyzed_title', true );
?>
<div id="wsoa-metabox-<?php echo esc_attr( $post->ID ); ?>" class="wsoa-metabox">

    <?php if ( $last_analysis && is_array( $last_analysis ) ) : ?>
    <div id="wsoa-result-<?php echo esc_attr( $post->ID ); ?>" class="wsoa-result">
        <?php if ( $last_title ) : ?>
        <p class="wsoa-analyzed-title">
            <?php printf( esc_html__( 'Analysé : « %s »', 'ws-optimizer-ai' ), esc_html( $last_title ) ); ?>
        </p>
        <?php endif; ?>

        <?php
        $score       = absint( $last_analysis['score'] ?? 0 );
        $score_class = $score >= 80 ? 'wsoa-score--ok' : ( $score >= 60 ? 'wsoa-score--warn' : 'wsoa-score--err' );
        ?>
        <div class="wsoa-score <?php echo esc_attr( $score_class ); ?>">
            <span class="wsoa-score__value"><?php echo esc_html( $score ); ?></span>
            <span class="wsoa-score__max">/100</span>
        </div>

        <?php if ( ! empty( $last_analysis['verdict'] ) ) : ?>
        <p class="wsoa-verdict"><?php echo esc_html( $last_analysis['verdict'] ); ?></p>
        <?php endif; ?>

        <?php if ( ! empty( $last_analysis['analysis'] ) ) : ?>
        <p class="wsoa-analysis"><?php echo esc_html( $last_analysis['analysis'] ); ?></p>
        <?php endif; ?>

        <?php if ( ! empty( $last_analysis['strengths'] ) ) : ?>
        <div class="wsoa-section wsoa-section--ok">
            <strong><?php esc_html_e( 'Atouts', 'ws-optimizer-ai' ); ?></strong>
            <ul><?php foreach ( $last_analysis['strengths'] as $s ) : ?>
                <li><?php echo esc_html( $s ); ?></li>
            <?php endforeach; ?></ul>
        </div>
        <?php endif; ?>

        <?php if ( ! empty( $last_analysis['issues'] ) ) : ?>
        <div class="wsoa-section wsoa-section--err">
            <strong><?php esc_html_e( 'Problèmes', 'ws-optimizer-ai' ); ?></strong>
            <ul><?php foreach ( $last_analysis['issues'] as $issue ) :
                $icon = ( 'critical' === ( $issue['severity'] ?? '' ) ) ? '🚨' : '⚠️';
                ?>
                <li><?php echo esc_html( $icon . ' ' . $issue['message'] ); ?></li>
            <?php endforeach; ?></ul>
        </div>
        <?php endif; ?>

        <?php if ( ! empty( $last_analysis['recommendations'] ) ) : ?>
        <div class="wsoa-section wsoa-section--tip">
            <strong><?php esc_html_e( 'Recommandations', 'ws-optimizer-ai' ); ?></strong>
            <ul><?php foreach ( $last_analysis['recommendations'] as $r ) : ?>
                <li><?php echo esc_html( $r ); ?></li>
            <?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
    </div>
    <?php else : ?>
    <div id="wsoa-result-<?php echo esc_attr( $post->ID ); ?>" class="wsoa-result wsoa-result--empty"></div>
    <?php endif; ?>

    <button type="button" class="wsoa-btn wsoa-analyze-btn" data-post-id="<?php echo esc_attr( $post->ID ); ?>">
        <?php echo $last_analysis
            ? esc_html__( 'Ré-analyser', 'ws-optimizer-ai' )
            : esc_html__( 'Analyser le titre', 'ws-optimizer-ai' ); ?>
    </button>
</div>
