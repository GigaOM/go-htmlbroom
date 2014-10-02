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
		wp_nonce_field( );
		echo '<input value="1" type="checkbox" name="post_category[]" id="in-category-1" checked="checked">';
		_e( 'Filter on', 'metabox' );
		echo '<lable = "HTML Filtering on">';

		$value = get_post_meta( $post->ID, 'filter_on', true );


	}

	public function saved_metabox( $post_id )
	{


	}

	/**
	 * Strips 'div' & 'span' tags and 'style' attributes WITHIN tags
	 */
	public function content_save_pre( $content )
	{
		global $allowedposttags;

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
