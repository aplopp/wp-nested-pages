<?php 
	/**
	* Extremely simplified version of public inline_edit method of WP_Posts_List_Table class
	* Bulk functionality removed
	* Other post type functionality removed (this will only be used for pages)
	* Order and parents are saved using drag and drop functionality
	*/
	$post = get_default_post_to_edit( 'page' );
	$post_type_object = get_post_type_object( 'page' );
	$m = ( isset( $mode ) && 'excerpt' == $mode ) ? 'excerpt' : 'list';
	$can_publish = current_user_can( $post_type_object->cap->publish_posts );
?>

	<form method="get" action="">
		<div class="form-interior">
		<h3><?php _e('Quick Edit', 'nestedpages'); ?></h3>

		<div class="np-quickedit-error" style="clear:both;display:none;"></div>

		<div class="fields">
		
		<div class="left">
			
			<div class="form-control">
				<label><?php _e( 'Title' ); ?></label>
				<input type="text" name="post_title" class="np_title" value="" />
			</div>
			<div class="form-control">
				<label><?php _e( 'Slug' ); ?></label>
				<input type="text" name="post_name" class="np_slug" value="" />
			</div>
			<div>
				<label><?php _e( 'Date' ); ?></label>
				<div class="dates"><?php touch_time( 1, 1, 0, 1 ); ?></div>
			</div>

			<?php 
			$authors_dropdown = '';
			if ( is_super_admin() || current_user_can( $post_type_object->cap->edit_others_posts ) ) :
				$users_opt = array(
					'hide_if_only_one_author' => false,
					'who' => 'authors',
					'name' => 'post_author',
					'id' => 'post_author',
					'class'=> 'authors',
					'multi' => 1,
					'echo' => 0
				);

				if ( $authors = wp_dropdown_users( $users_opt ) ) :
					$authors_dropdown  = '<div class="form-control np_author"><label>' . __( 'Author' ) . '</label>';
					$authors_dropdown .= $authors;
					$authors_dropdown .= '</div>';
				endif;
				echo $authors_dropdown;
			endif;
			?>

		</div><!-- .left -->

		<div class="right">
			<div class="form-control">
				<label><?php _e( 'Template' ); ?></label>
				<select name="page_template" class="np_template">
					<option value="default"><?php _e( 'Default Template' ); ?></option>
					<?php page_template_dropdown() ?>
				</select>
			</div>
			<div class="comments">
				<label>
					<input type="checkbox" name="comment_status" class="np_cs" value="open" />
					<span class="checkbox-title"><?php _e( 'Allow Comments' ); ?></span>
				</label>
			</div>
			<div class="form-control">
				<label><?php _e( 'Status' ); ?></label>
				<select name="_status" class="np_status">
				<?php if ( $can_publish ) : ?>
					<option value="publish"><?php _e( 'Published' ); ?></option>
					<option value="future"><?php _e( 'Scheduled' ); ?></option>
				<?php endif; ?>
					<option value="pending"><?php _e( 'Pending Review' ); ?></option>
					<option value="draft"><?php _e( 'Draft' ); ?></option>
				</select>
			</div>
		</div><!-- .right -->
		</div><!-- .fields -->

		</div><!-- .form-interior -->

		<div class="buttons">
			<input type="hidden" name="post_id" class="np_id" value="<?php echo get_the_id(); ?>">
			<a accesskey="c" href="#inline-edit" class="button-secondary alignleft np-cancel-quickedit">
				<?php _e( 'Cancel' ); ?>
			</a>
			<a accesskey="s" href="#inline-edit" class="button-primary np-save-quickedit alignright">
				<?php _e( 'Update' ); ?>
			</a>
			<span class="np-qe-loading"></span>
		</div>
	</form>