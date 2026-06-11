<?php
/**
 * The template for displaying comments
 *
 * @package NeoNews
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( post_password_required() ) {
    return;
}
?>

<section id="comments" class="nn-comments">
    <?php if ( have_comments() ) : ?>
        <h2 class="nn-comments-title">
            <?php
            $comment_count = get_comments_number();
            printf(
                /* translators: 1: comment count, 2: post title */
                esc_html( _nx(
                    '%1$s Comment',
                    '%1$s Comments',
                    $comment_count,
                    'comments title',
                    'neonews'
                ) ),
                number_format_i18n( $comment_count )
            );
            ?>
        </h2>

        <ol class="nn-comment-list">
            <?php
            wp_list_comments( array(
                'style'       => 'ol',
                'short_ping'  => true,
                'callback'    => 'neonews_comment_callback',
                'avatar_size' => 48,
            ) );
            ?>
        </ol>

        <?php
        the_comments_navigation( array(
            'prev_text' => esc_html__( '&larr; Older Comments', 'neonews' ),
            'next_text' => esc_html__( 'Newer Comments &rarr;', 'neonews' ),
        ) );
        ?>

    <?php endif; ?>

    <?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
        <p class="nn-no-comments"><?php esc_html_e( 'Comments are closed.', 'neonews' ); ?></p>
    <?php endif; ?>

    <?php
    $commenter = wp_get_current_commenter();
    $req = get_option( 'require_name_email' );
    $aria_req = ( $req ? " aria-required='true' required" : '' );

    $fields = array(
        'author' => sprintf(
            '<div class="nn-form-group"><label for="author" class="nn-form-label">%s%s</label><input id="author" name="author" type="text" class="nn-form-input" value="%s"%s /></div>',
            esc_html__( 'Name', 'neonews' ),
            ( $req ? ' *' : '' ),
            esc_attr( $commenter['comment_author'] ),
            $aria_req
        ),
        'email' => sprintf(
            '<div class="nn-form-group"><label for="email" class="nn-form-label">%s%s</label><input id="email" name="email" type="email" class="nn-form-input" value="%s"%s /></div>',
            esc_html__( 'Email', 'neonews' ),
            ( $req ? ' *' : '' ),
            esc_attr( $commenter['comment_author_email'] ),
            $aria_req
        ),
        'url' => sprintf(
            '<div class="nn-form-group"><label for="url" class="nn-form-label">%s</label><input id="url" name="url" type="url" class="nn-form-input" value="%s" /></div>',
            esc_html__( 'Website', 'neonews' ),
            esc_attr( $commenter['comment_author_url'] )
        ),
    );

    comment_form( array(
        'class_form'         => 'nn-comment-form',
        'title_reply'        => esc_html__( 'Leave a Comment', 'neonews' ),
        'title_reply_before' => '<h3 id="reply-title" class="nn-comments-title">',
        'title_reply_after'  => '</h3>',
        'fields'             => $fields,
        'comment_field'      => sprintf(
            '<div class="nn-form-group"><label for="comment" class="nn-form-label">%s *</label><textarea id="comment" name="comment" class="nn-form-textarea" aria-required="true" required></textarea></div>',
            esc_html__( 'Comment', 'neonews' )
        ),
        'submit_button'      => '<button type="submit" name="%1$s" id="%2$s" class="nn-btn %3$s">%4$s</button>',
        'submit_field'       => '<div class="nn-form-submit">%1$s %2$s</div>',
        'comment_notes_before' => '',
    ) );
    ?>
</section>
