<?php

if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) { die(); }

class JST_Widget_Recently_Categories extends WP_Widget {
	public $key = 'jst_recently_categories_html';
	public $timeout = 3600; // 1 hour
	public $enable_cache = false;
	public $html_uid;
	public $number;
	
	protected static $script_initiated = false;
	
	public function __construct() {
		$args = array(
			'description' => __('Display the most recent posts sorted by category and comments in a tabbed box.', 'anno'),
			'classname' => 'widget-recent-posts widget-recent-posts-categories'
		);
		parent::__construct('jst_recently_categories', __('Recently Categories&hellip;', 'anno'), $args);
		
		$this->html_uid = uniqid('jst-recently-categories');
		
		// Clear widget cache on one of these important events
		add_action( 'save_post', array($this, 'flush_widget_cache') );
		add_action( 'deleted_post', array($this, 'flush_widget_cache') );
		add_action( 'switch_theme', array($this, 'flush_widget_cache') );
		add_action( 'comment_post', array($this, 'flush_widget_cache') );
		add_action( 'transition_comment_status', array($this, 'flush_widget_cache') );
	}
	
	public function widget($args, $instance) {
		extract($args);
		$this->number = 5;
		$cache = get_transient($this->key);
		if ($cache === false || $this->enable_cache === false) {
			ob_start();
				$this->cached($args, $instance);
			$cache = ob_get_clean();
			set_transient($this->key, $cache, $this->timeout);
		}
		echo $before_widget;
		echo $cache;
		echo $after_widget;
	}

	// Get all categories
	// Query for each category

	public function cached($args, $instance) {
		// Does not return  empty terms
		$terms = get_terms('article_category', array('fields' => 'ids'));
		foreach ($terms as $term_id) {
			$articles = new WP_Query(array(
				'post_type' => 'article',
				'posts_per_page' => $this->number,
				'tax_query' => array(
					array(
						'taxonomy' => 'article_category',
						'field' => 'id',
						'terms' => (int) $term_id,
					)
				)
			));
		}

	?>
	<div class="recently-container">
		<div class="tabs">
			<ul class="nav">
				<li><a href="#p1-<?php echo $this->html_uid; ?>"><?php _e('Recent Articles', 'anno'); ?></a></li>
				<li><a href="#p2-<?php echo $this->html_uid; ?>"><?php _e('Comments', 'anno'); ?></a></li>
			</ul>
			<div class="panel first-child" id="p1-<?php echo $this->html_uid; ?>">
				<?php 
				$terms = get_terms('article_category');
				foreach ($terms as $term) {
					$articles = new WP_Query(array(
						'post_type' => 'article',
						'posts_per_page' => $this->number,
						'tax_query' => array(
							array(
								'taxonomy' => 'article_category',
								'field' => 'id',
								'terms' => (int) $term->term_id,
							)
						)
					));
					if ($articles->have_posts()) {
						echo '<h4>'.esc_html($term->name).'</h4>';
						echo '<ol>';
						while ($articles->have_posts()) {
							$articles->the_post();
				 	?>
				<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
					<?php 
						}
						echo '</ol>';
						wp_reset_postdata();
						unset($articles);
					}
				}
			?>
				
			</div>
			<div class="panel" id="p2-<?php echo $this->html_uid; ?>">
				<?php
				$comments = get_comments(array(
					'number' => $this->number,
					'status' => 'approve',
					'post_status' => 'publish'
				));
				if (count($comments)) {
					echo '<ul>';
					foreach ((array) $comments as $comment) {
				?>
					<li class="recentcomments"><?php
						/* translators: comments widget: 1: comment author, 2: post link */ 
						printf(_x('%1$s on %2$s', '\'username\' on \'post title\'', 'anno'), get_comment_author_link($comment->comment_ID), '<a href="' . esc_url(get_comment_link($comment->comment_ID)) . '">' . get_the_title($comment->comment_post_ID) . '</a>'); 
				?></li>
				<?php
					}
					echo '</ul>';
				}
				?>
			</div>
		</div>
	</div><!-- .recently-container -->
	
	<?php
	}
	
	public function flush_widget_cache() {
		delete_transient($this->key);
	}
}
?>