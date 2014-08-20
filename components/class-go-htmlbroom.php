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
		//Adds the functions to the correct hooks
		add_filter( 'content_save_pre', $this->strip_attribs() );
	}//end admin_init

	/**
	 * Strips ONLY 'style' attributes WITHIN tags
	 */
	public function strip_attribs()
	{
		global $allowedposttags;

		//Remove blacklisted tags from allowed list
		unset( $allowedposttags['div'] );
		unset( $allowedposttags['span'] );

		//Collects post data OUTSIDE of the loop
		$posts = get_posts();
		$id = $posts[0]->ID;
		$content = get_post_field( 'post_content', $id, 'attribute' );
		$pattern = '/( style=&quot.[a-z0-9:;, \-]+&quot.)/i';

		//On pattern match within post content
		if ( preg_match_all( $pattern, $content, $matches ) )
		{
			//Loops through matches found in post content
			foreach ( $matches[0] as $match )
			{
				//Replaces matches with '' or blank space
				$result = str_replace( $match, '', $content );
			}//end foreach

			//Converting HTMLEntities back
			$result = str_replace( '&lt;', '<', $result );
			$result = str_replace( '&gt;', '>', $result );

			//Updating post content
			$my_post = array(
				'ID' => $id,
				'post_content' => $result,
			);
			wp_update_post( $my_post );
		}//end if
	}//end strip_attribs
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
