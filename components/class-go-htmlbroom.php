<?php

class GO_Htmlbroom
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		add_action( 'init', array( $this, 'admin_init' ) );
	}// end __construct

	/**
	 * Queues up 'style' stripping
	 */
	public function admin_init()
	{
		global $allowedposttags;

		//Remove blacklisted tags from allowed list
		unset( $allowedposttags['div'] );
		unset( $allowedposttags['span'] );
		add_filter( 'content_save_pre', 'wp_kses_post' );

		//Adds the filter to 'content_edit_pre'
		add_filter( 'content_edit_pre', array( $this, 'content_edit_pre' ), 10, 1 );
	}//end admin_init

	/**
	 * Strips ONLY 'style' attributes WITHIN tags
	 */
	public function content_edit_pre( $content )
	{
		$style_pattern = '/( style=\"[a-z0-9:;, \-]+\")/i';
		//On pattern match within post content
		if ( preg_match_all( $pattern, $content, $matches ) )
		{
			//Loops through matches found in $content
			foreach ( $matches[0] as $match )
			{
				//Replaces matches with ''
				$content = str_replace( $match, '', $content );
			}//end foreach
		}//end if
		return $content;
	}//end content_edit_pre
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
