jQuery(document).ready( function($) {
	// Type-ahead
	//TODO prevent enter from submitting form
	$('.user-input').suggest( 'admin-ajax.php?action=anno-user-search', { delay: 200, minchars: 2, multiple: false} );
	
	$('input[type="submit"], a.submitdelete, #submitpost').click(function() {
		$(this).siblings('.ajax-loading').css('visibility', 'visible');
	});

	
    // Register our add functions so we can call them with a generated string.
    var anno_manage_user = {};


	//TODO possibly abstract this and anno_add_reviewer into a single function;
 	// @param string user (login or email expected) 
	anno_manage_user.add_co_author = function anno_add_co_author(user) {
		if (user == '') {
			return false;
		}
		var data = {action: 'anno-add-co-author', user: user, post_id: ANNO_POST_ID};
		data['_ajax_nonce-manage-co_author'] = $('#_ajax_nonce-manage-co_author').val();
		$.post(ajaxurl, data, function(d) {
			if (d.message == 'success') {
				$('ul#co-author-list').prepend(d.html);
				// Append co-author to author dropdown
				$('#post_author_override').append(d.author);
				// Clear error box
				$('#co-author-add-error').html('').hide();
				//Clear input box
				$('input[type="text"]#co-author-input').val('');
			}
			else {
				$('#co-author-add-error').html(d.html).show();
			}
		}, 'json');
	}
	
	$('input[type="text"]#co-author-input').keydown(function(e) {
		if (e.keyCode && e.keyCode == 13) {
			var user = $('input[type="text"]#co-author-input').val();
			anno_add_co_author(user);
			return false;
		}
	});
	
	$('input[type="button"]#co-author-add').click(function() {
		var user = $('input[type="text"]#co-author-input').val();
		anno_add_co_author(user)
	});
	
	
	// @param string user (login or email expected) 
	anno_manage_user.add_reviewer = function anno_add_reviewer(user) {
		if (user == '') {
			return false;
		}

		var data = {action: 'anno-add-reviewer', user: user, post_id: ANNO_POST_ID};
		data['_ajax_nonce-manage-reviewer'] = $('#_ajax_nonce-manage-reviewer').val();

		$.post(ajaxurl, data, function(d) {
			if (d.message == 'success') {
				$('ul#reviewer-list').prepend(d.html);
				$('#reviewer-add-error').html('').hide();
				$('input[type="text"]#reviewer-input').val('');
				
				
				// Increment reviewer counts on in_review state page
				if ($("#anno-reviewers-count").length > 0) {
					var reviewers = $('#anno-reviewers-count').html();
					$('#anno-reviewers-count').html(Number(reviewers) + 1);
				}
				
				if ($("#anno-reviewed-count").length > 0) {
					var reviewed = $('#anno-reviewed-count').html();
					if (d.increment == 1) {
						$('#anno-reviewed-count').html(Number(reviewed) + 1);
					}
				}
			}
			else {
				$('#reviewer-add-error').html(d.html).show();
			}
		}, 'json');
	}
	
	$('input[type="text"]#reviewer-input').keydown(function(e) {
		if (e.keyCode && e.keyCode == 13) {
			var user = $('input[type="text"]#reviewer-input').val();
			anno_add_reviewer(user);
			return false;
		}
	});
	
	$('input[type="button"]#reviewer-add').click(function() {
		var user = $('input[type="text"]#reviewer-input').val();
		anno_add_reviewer(user)
	});	
	
	$('.anno-user-remove').live('click', function() {
		var clicked = $(this);
		var type = $(this).closest('ul').attr('data-type');
		var user_id = $(this).closest('li').attr('id').replace('user-', '');
		var data = {action: 'anno-remove-' + type, user_id: user_id, post_id: ANNO_POST_ID};
		data['_ajax_nonce-manage-' + type] = $('#_ajax_nonce-manage-' + type).val();
		
		$.post(ajaxurl, data, function(d) {
			if (d.message == 'success') {
				clicked.closest('li').fadeOut();
				// Remove from author dropdown menu
				if (type == 'co_author') {
					$('#post_author_override option[value=' + user_id + ']').remove();
				}
				else if (type == 'reviewer') {
					if ($("#anno-reviewers-count").length > 0) {
						var reviewers = $('#anno-reviewers-count').html();
						$('#anno-reviewers-count').html(Number(reviewers) - 1);
					}

					if ($("#anno-reviewed-count").length > 0) {
						var reviewed = $('#anno-reviewed-count').html();
						if (d.decrement == 1) {
							$('#anno-reviewed-count').html(Number(reviewed) - 1);
						}
					}
				}
			}
		}, 'json');
		return false;
	});
	
	// Create form and submit to avoid embedding a form within a form in the markup
	$('.anno-create-user').live('click', function() {
		// Type, reviewer or co-author
		var type = $(this).attr('data-type');
		var div_selector = 'div#anno-invite-' + type;
		var status_div = $('#' + type +  '-add-error');
		
		var user_login = $(div_selector + ' input[name="invite_user"]').val();
		var user_email = $(div_selector + ' input[name="invite_email"]').val();
		var post_data = {user_login : user_login, user_email : user_email, action : 'anno-invite-user'};
		post_data['_ajax_nonce-create-user'] = $('#_ajax_nonce-create-user').val();

		status_div.html('').hide();
		
		$.post(ajaxurl, post_data, function(d) {
			if (d.code == 'success') {
				// Determine which function adds the user to the post
				// co_author or reviewer.
				var fn = 'add_' + type;
				if (fn in anno_manage_user) {
				    anno_manage_user[fn](d.user);
				}
			}
			else {
				status_div.html(d.message).show();
			}			
			
		}, 'json');
		return false; 	
	});
	
	
	
	$('.anno-show-search-co_author').live('click', function() {
		$('#user-input-co_author').show();
		$('#anno-invite-co_author').hide();
		return false;
	});
	
	
	$('.anno-show-create-co_author').live('click', function() {
		$('#user-input-co_author').hide();
		$('#anno-invite-co_author').show();
		return false;
	});
	
	$('.anno-show-search-reviewer').live('click', function() {
		$('#user-input-reviewer').show();
		$('#anno-invite-reviewer').hide();
		return false;
	});
	
	$('.anno-show-create-reviewer').live('click', function() {
		$('#user-input-reviewer').hide();
		$('#anno-invite-reviewer').show();
		return false;
	});
});

