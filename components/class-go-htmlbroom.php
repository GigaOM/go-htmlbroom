<?php

class GO_Htmlbroom
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_filter( 'tiny_mce_before_init', array( $this, 'tiny_mce_before_init' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}// end __construct

	/**
	 * Queues up 'style' 'div' and 'span' stripping
	 */
	public function admin_init()
	{
		//Adds the filter to 'content_save_pre'
		add_filter( 'content_save_pre', array( $this, 'content_save_pre' ) );

		add_filter( 'option_use_balanceTags', '__return_true' );

		add_meta_box( 'go_htmlbroom', 'HTML Filtering', array( $this, 'metabox' ), 'post' );
	}//end admin_init

	/**
	 * Changes the elements in the TinyMCE init array so we can tweak the TinyMCE UI
	 *
	 * @param Array $init Array of TinyMCE init settings
	 */
	public function tiny_mce_before_init( $init )
	{
		if ( empty( $init['block_formats'] ) )
		{
			$init['block_formats'] = 'Paragraph=p;Pre=pre;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6';
		}//end if
		else
		{
			// if block_formats was already set, let's just remove heading 1 and heading 2
			$init['block_formats'] = preg_replace( '/;[^=]+\=h[12]/', '', $init['block_formats'] );
		}//end else

		return $init;
	}//end tiny_mce_before_init

	public function metabox()
	{
		global $post;
		$post_id = $post->ID;

		echo wp_nonce_field( 'go-htmlbroom-save-post', 'go-htmlbroom-save-post' );

		$checked = ! ( get_post_meta( $post_id, 'go_htmlbroom_disable', TRUE ) ) ? TRUE: FALSE;

		?>
		<div id="display-htmlbroom">
			<p>
				<input type="checkbox" id="htmlbroom-enable" name="htmlbroom-enable" <?php checked( $checked ); ?> />
				<label for="htmlbroom-disable">Automatically clean stray HTML typically caused by copy/paste from other apps or sites</label>
			</p>
		</div>
		<?php

	}//end metabox

	public function save_data( $post_id )
	{
		do_action( 'debug_robot', 'KN: ' . print_r( $_POST, true ) );

		$disabled = isset( $_POST['htmlbroom-enable'] ) ? FALSE : TRUE;

		update_post_meta( $post_id, 'go_htmlbroom_disable', $disabled );


	}
	/**
	 * Hooked to the save_post action
	 */
	public function save_post( $post_id )
	{
		// Check that this isn't an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		{
			return;
		}// end if

		// Don't run on post revisions (almost always happens just before the real post is saved)
		if ( wp_is_post_revision( $post_id ) )
		{
			return;
		}// end if

		$post = get_post( $post_id );
		if ( ! is_object( $post ) )
		{
			return;
		}// end if

		// check post type matches what you intend
		$whitelisted_post_types = array( 'post', 'page' );
		if ( ! isset( $post->post_type ) || ! in_array( $post->post_type, $whitelisted_post_types ) )
		{
			return;
		}// end if

		// Check the nonce
		if ( ! wp_verify_nonce( $_POST['go-htmlbroom-save-post'], 'go-htmlbroom-save-post' ) )
		{
			return;
		}// end if

		// Check the permissions
		if ( ! current_user_can( 'edit_post', $post->ID ) )
		{
			return;
		}// end if

		$this->save_data( $post_id );
	}// end save_post

	/**
	 * Strips 'div' & 'span' tags and 'style' attributes WITHIN tags
	 */
	public function content_save_pre( $content )
	{

		global $allowedposttags, $post;

		if (! get_post_meta( $post->ID, 'go_htmlbroom_disable' ) )
		{
			return $content;
		}



		//Saves original list of $allowedposttags
		$original_allowedposttags = $allowedposttags;

		//Remove blacklisted tags from allowed list
		unset( $allowedposttags['div'] );
		unset( $allowedposttags['span'] );

		//Loops through $allowedposttags and removes style
		foreach ( $allowedposttags as &$tags )
		{
			if ( isset( $tags['style'] ) )
			{
				unset( $tags['style'] );
			}
		}
		//Apply kses filter to $content
		$content = wp_kses_post( $content );

		//Resets $allowedposttags to default AFTER 'div' & 'span' stripping
		$allowedposttags = $original_allowedposttags;


		// Make sure the h tags start at h3. The only h tags that we'll escalate up are h1 & h2 and turn
		// them into h3
		if ( preg_match( '!(</?)(h[12])([^>]*>)!im', $content ) )
		{
			// Replace the h[12] tags with h3 in the content
			$content = preg_replace( '!(</?)(h[12])([^>]*>)!im', '$1h3$3', $content );
		}//end if

		return $content;
	}//end content_save_pre
}// end class

/**
 * Singleton
 */
function go_htmlbroom()
{
	global $go_htmlbroom;

	if ( ! isset( $go_htmlbroom ) )
	{
		$go_htmlbroom = new GO_Htmlbroom();
	}//END if

	return $go_htmlbroom;
}//end go_htmlbroom
