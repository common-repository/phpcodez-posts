<?php
/**
* Plugin Name: PHPCodez Posts
* Plugin URI: http://phpcodez.com/
* Description: A Widget That Displays Posts
* Version: 0.1
* Author: Pramod T P
* Author URI: http://phpcodez.com/
*/

add_action( 'widgets_init', 'wpc_posts_widgets' );

function wpc_posts_widgets() {
	register_widget( 'wpcpostsWidget' );
}

class wpcpostsWidget extends WP_Widget {
	function wpcpostsWidget() {
		$widget_ops = array( 'classname' => 'wpcClass', 'description' => __('A Widget That Displays posts.', 'wpcClass') );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wpc-posts' );
		$this->WP_Widget( 'wpc-posts', __('PHPCodez posts', ''), $widget_ops, $control_ops );
	}

	
	function widget( $args, $instance ) {
		extract( $args );
		global $wpdb;
		if($instance['post_count']) $limitPost =" LIMIT 0,".$instance['post_count'];
		if($instance['post_random']){
				$postOrderBy =" ORDER BY rand()";
		}elseif($instance['post_sort']) {
			$postOrderBy =" ORDER BY ".$instance['post_sort'];
			if($instance['post_order']) $postOrderBy .=" " .$instance['post_order'];
		}
	
		if($instance['post_exclude']) $postExlucde .=" AND  ID NOT IN(".$instance['post_exclude'].")  ";
		
		if($instance['post_category']) $post_category .=" AND  $wpdb->term_taxonomy.term_id  IN(".$instance['post_category'].")  ";

		$postsQry = "SELECT distinct(wposts.post_title),wposts.ID,wposts.comment_count  FROM $wpdb->posts wposts
		 			 LEFT JOIN $wpdb->term_relationships ON (wposts.ID = $wpdb->term_relationships.object_id)
					 LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id =
					 $wpdb->term_taxonomy.term_taxonomy_id) WHERE $wpdb->term_taxonomy.taxonomy = 'category' 
					 $post_category $postExlucde  $postOrderBy $limitPost ";
		$postsData = $wpdb->get_results($postsQry, OBJECT);
		
?>
	<div class="arch_box">
		<?php if($instance['post_title']) { ?>
			<div class="side_hd">
				<h3><?php echo $instance['post_title'] ?></h3>
			</div>
		<?php } ?>
		<div class="sider_mid">
			<ul>
			<?php
				foreach($postsData as $key=>$post) { $havePost=1;
			 ?>
				<li>
					<a href="<?php echo get_permalink($post->ID); ?>">
						<?php echo $post->post_title; ?> <?php if($instance['post_comments']) { ?> (<?php echo empty($post->comment_count)?0:$post->comment_count; ?>)<?php }  ?>
					</a>
				</li>
			<?php }  ?>
			
			<?php if(!$havePost){ ?>
				<li>No posts Are Added Yet</li>
			<?php } ?>
			</ul>	
			
		</div>	
	</div>
<?php

}


function update( $new_instance, $old_instance ) {
	$instance = $old_instance;
	
	$instance['post_title']		=  $new_instance['post_title'] ;
	$instance['post_random']	=  $new_instance['post_random'] ;
	$instance['post_count'] 	=  $new_instance['post_count'] ;
	$instance['post_comments'] 	=  $new_instance['post_comments'] ;
	$instance['post_sort'] 		=  $new_instance['post_sort'] ;
	$instance['post_order'] 	=  $new_instance['post_order'] ;
	$instance['post_exclude'] 	=  $new_instance['post_exclude'] ;
	$instance['post_category'] 	=   implode(",",$new_instance['post_category']) ;
	
	return $instance;
}

function form( $instance ) {?>
	<p>
		<label for="<?php echo $this->get_field_id( 'post_title' ); ?>"><?php _e('Title', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'post_title' ); ?>" name="<?php echo $this->get_field_name( 'post_title' ); ?>" value="<?php echo $instance['post_title'] ?>"  type="text" width="99%" />
	</p>
	<?php
		global $wpdb;
		$linkCategoryQry = "SELECT c.*,ct.* FROM {$wpdb->prefix}terms as c JOIN  {$wpdb->prefix}term_taxonomy as ct  ON c.term_id=ct.term_id 
				WHERE ct.taxonomy='category' ";
		$linkCategoryData	=	$wpdb->get_results($linkCategoryQry);		
	?>
	<p>
		<label for="<?php echo $this->get_field_name( 'post_category' ); ?>"><?php _e('Post Categories', 'wpclass'); ?></label>
		<select id="<?php echo $this->get_field_name( 'post_category' ); ?>" name="<?php echo $this->get_field_name( 'post_category' ); ?>[]" multiple="multiple" style="width:90%; height:100px;">
		<?php foreach($linkCategoryData as $key=>$linkCategory) { ?>
			<option value="<?php echo $linkCategory->term_id; ?>"  <?php if(in_array($linkCategory->term_id,explode(",",$instance['post_category']))) echo 'selected="selected"'; ?>>
				<?php echo $linkCategory->name; ?> 
			</option>
		<?php } ?>		
		</select>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'post_random' ); ?>"><?php _e('Show Random Posts', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'post_random' ); ?>" name="<?php echo $this->get_field_name( 'post_random' ); ?>" value="1" <?php if($instance['post_random']) echo 'checked="checked"'; ?> type="checkbox" />
	</p>
	
	<p>
		<label for="<?php echo $this->get_field_name( 'post_sort' ); ?>"><?php _e('Order BY', 'wpclass'); ?></label>
		<select id="<?php echo $this->get_field_name( 'post_sort' ); ?>" name="<?php echo $this->get_field_name( 'post_sort' ); ?>">
			<option value="post_title"  <?php if($instance['post_sort']=="post_title") echo 'selected="selected"'; ?>>Name</option>
			<option value="ID"  <?php if($instance['post_sort']=="ID") echo 'selected="selected"'; ?>>ID</option>
			<option value="comment_count"  <?php if($instance['post_sort']=="comment_count") echo 'selected="selected"'; ?>>No Of Comments</option>
		</select>
		<select id="<?php echo $this->get_field_name( 'post_order' ); ?>" name="<?php echo $this->get_field_name( 'post_order' ); ?>">
			<option value="ASC" <?php if($instance['post_order']=="ASC") echo 'selected="selected"'; ?>>ASC</option>
			<option value="DESC" <?php if($instance['post_order']=="DESC") echo 'selected="selected"'; ?>>DESC</option>
		</select>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'post_count' ); ?>"><?php _e('Number of posts . for "0" or "No Value" It will list all the posts', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'post_count' ); ?>" name="<?php echo $this->get_field_name( 'post_count' ); ?>" value="<?php echo $instance['post_count'] ?>"  type="text" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'post_exclude' ); ?>"><?php _e('Exclude Posts - Enter post ids to be excluded (example 5,78,90)', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'post_exclude' ); ?>" name="<?php echo $this->get_field_name( 'post_exclude' ); ?>" value="<?php echo $instance['post_exclude'] ?>"  type="text" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'post_comments' ); ?>"><?php _e('Display Comment Count', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'post_comments' ); ?>" name="<?php echo $this->get_field_name( 'post_comments' ); ?>" value="1" <?php if($instance['post_comments']) echo 'checked="checked"'; ?> type="checkbox" />
	</p>
<?php
	}
}

?>