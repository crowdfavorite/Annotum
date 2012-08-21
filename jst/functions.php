<?php 


@define('JST_PATH', trailingslashit(get_stylesheet_directory()));


// WP will include this file by default but it needs to be loaded prior to the parent themes versions
include_once JST_PATH.'plugins/load.php'; 


/**
 * Get the number of authors for an article via the snapshot.
 * @param int post_id ID of the post to get the number from 
 * @return Number of authors, 1 if no snapshot found (default WP)
 **/
function annojst_num_authors($post_id) {
	$authors = get_post_meta($post_id, '_anno_author_snapshot', true);
	if (is_array($authors)) {
		return count($authors);
	}

	// Default WP, only one author
	return 1;
}