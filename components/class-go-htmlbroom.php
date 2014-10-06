<?php

class GO_Htmlbroom
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_filter( 'tiny_mce_before_init', array( $this, 'tiny_mce_before_init' ), 10, 2 );
	}// end __construct

	/**
	 * Queues up 'style' 'div' and 'span' stripping
	 */
	public function admin_init()
	{
		//Adds the filter to 'content_save_pre'
		add_filter( 'content_save_pre', array( $this, 'content_save_pre' ) );

		add_filter( 'option_use_balanceTags', '__return_true' );

	}//end admin_init


	/**
	 * Changes the elements in the TinyMCE init array so we can tweak the TinyMCE UI
	 */
	public function tiny_mce_before_init( $init, $unsued_editor_id )
	{
		$init['block_formats'] = 'Paragraph=p;Pre=pre;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6';

		return $init;
	}//end tiny_mce_before_init

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
