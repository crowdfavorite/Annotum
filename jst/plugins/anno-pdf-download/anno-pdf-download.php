<?php 
/**
 * Get the PDF download link for a post
 * Overrides the parent theme with outputting of custom URL
 *
 * @param int $id 
 * @return string
 */
function anno_pdf_download_url($id = null) {
	$pdf_url = get_post_meta($id, '_annojst_pdf_url', true);
	if (empty($pdf_url)) {
		$pdf_url = Anno_PDF_Download::i()->get_download_url($id);
	}
	return $pdf_url;
}

/** 
 * Add the meta box to the article
 */
function annojst_add_pdf_url_metabox($post) {
	add_meta_box('pdf-url', _x('PDF URL', 'Meta box title', 'anno'), 'annojst_pdf_url_metabox_markup', 'article', 'normal', 'high');
}
add_action('add_meta_boxes_article', 'annojst_add_pdf_url_metabox', 11);

/** 
 * Markup for the PDF URL
 */
function annojst_pdf_url_metabox_markup($post) {
	$pdf_url = get_post_meta($post->ID, '_annojst_pdf_url', true);
?>
<input type="hidden" name="annojst_pdf_url_save" value="1" />
<input type="text" name="annojst_pdf_url" value="<?php echo esc_attr($pdf_url); ?>" class="widefat" />

<?php
}

/** 
 * Save the PDF URL
 * WP is responsible for nonce checking and permissions when saving a post.
 */
function annojst_pdf_url_save($post_id) {
	if (isset($_POST['annojst_pdf_url_save'])) {
		$pdf_url = isset($_POST['annojst_pdf_url']) ? trim($_POST['annojst_pdf_url']) : '';
		update_post_meta($post_id, '_annojst_pdf_url', $pdf_url);
	}
}
add_action('save_post', 'annojst_pdf_url_save');