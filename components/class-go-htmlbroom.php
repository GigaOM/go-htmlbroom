<?php

class GO_Htmlbroom
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		add_action( 'admin_init', array( $this, 'admin_init' ) );
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
		foreach( $allowedposttags as &$tags ){
			if( isset( $tags['style'] ) ){
				unset( $tags['style'] );
			}
		}
		//Apply kses filter to $content
		$content = wp_kses_post( $content );

		//Resets $allowedposttags to default AFTER 'div' & 'span' stripping
		$allowedposttags = $original_allowedposttags;


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
