<?php
/**
 * Comments
 */
?>
	<div id="comments">
	<? if (post_password_required()): ?>
		<p class="nopassword">This post is password protected. Enter the password to view any comments.</p>
	</div><!-- #comments -->
	<?
			/* Stop the rest of comments.php from being processed,
			 * but don't kill the script entirely -- we still have
			 * to fully load the template.
			 */
			return;
		endif;
	?>

	<? if (have_comments()): ?>
		<h2 id="comments-title">
			<?
				echo get_comments_number();
				echo number_format_i18n(get_comments_number());
				echo get_the_title();
			?>
		</h2>

		<? if (get_comment_pages_count() > 1 && get_option('page_comments')): // are there comments to navigate through ?>
		<nav id="comment-nav-above">
			<h1 class="assistive-text section-heading">Comment Navigation</h1>
			<div class="nav-previous"><? previous_comments_link('&larr; Older Comments'); ?></div>
			<div class="nav-next"><? next_comments_link('Newer Comments &rarr;'); ?></div>
		</nav>
		<? endif; // check for comment navigation ?>

		<ol class="commentlist">
			<? wp_list_comments(); ?>
		</ol>

		<? if (get_comment_pages_count() > 1 && get_option('page_comments')): // are there comments to navigate through ?>
		<nav id="comment-nav-below">
			<h1 class="assistive-text section-heading">Comment navigation</h1>
			<div class="nav-previous"><? previous_comments_link('&larr; Older Comments'); ?></div>
			<div class="nav-next"><? next_comments_link('Newer Comments &rarr;'); ?></div>
		</nav>
		<?php endif; // check for comment navigation ?>

	<? endif; // have_comments() ?>

	<?
		// If comments are closed and there are no comments, let's leave a little note, shall we?
		if (!comments_open() && '0' != get_comments_number() && post_type_supports(get_post_type(), 'comments')):
	?>
		<p class="nocomments">Comments are closed.</p>
	<? endif; ?>

	<? comment_form(); ?>
	<? 
		/*
		comment_form(
			array(
				'title_reply' => 'What do you think?',
				'must_log_in' => '<p class="must-log-in"><a href="'.wp_login_url(apply_filters( 'the_permalink',get_permalink())).'">Log in or Register to post a comment.</a></p>',
				'logged_in_as' => '<p class="logged-in-as">' . sprintf( __( 'Logged in as: <a class="username" href="%1$s">%2$s</a> <a href="%3$s" title="Log out of this account" class="logout">Log out</a>' ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( ) ) ) ) . '</p>',
				'comment_field' => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label><textarea id="comment" name="comment" aria-required="true"></textarea></p>'
			)
		);
		*/ 
	?>
	

</div><!-- #comments -->
