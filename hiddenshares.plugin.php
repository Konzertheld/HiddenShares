<?php
class HiddenShares extends Plugin
{
	// Catch requests with secrets
	public function action_init()
	{
		$rule = new RewriteRule(array(
			'name' => "display_hiddenshare",
			// this scary regex...
			'parse_regex' => "#^hiddenshare/(?P<secret>[^/]+)/?$#i",
			// just matches requests that look like this, not regarding the case:
			'build_str' => 'hiddenshare/{$secret}',
			'handler' => 'PluginHandler',
			'action' => 'display_hiddenshare',
		));

		$this->add_rule($rule, 'display_feedcontent');
	}
	
	// Fetch post and display
	public function theme_route_display_hiddenshare($theme, $params)
	{
		$post = Post::get(array('all:info' => array('sharesecret' => $params['secret'])));
		// This will work for 0.9, too, where the Posts::get() function is still creepy
		$theme->act_display(array('posts' => $post));
	}
	
	// Add "secret" field to publish page
	public function action_form_publish($form, $post, $context)
	{
	  // only show the secret the form if the post has already been saved
		if ($post->id) {
			$form->insert('tags', 'text', 'sharesecret', 'null:null', _t('Share Secret'));
			$form->sharesecret->template = 'admincontrol_text';
			if(isset($post->info->sharesecret) && !empty($post->info->sharesecret)) {
				$form->sharesecret->value = $post->info->sharesecret;
				$form->post_links->append( 'static', 'post_hiddensharelink', '<a href="'. Site::get_url('site') . 'hiddenshare/' . $post->info->sharesecret .'" class="viewpost" style="margin-right:10px;">'. _t('Secret share link', __CLASS__) .'</a>' );
			}
		}
		$form->settings->append('checkbox', 'hidden_post', 'null:null', _t('Hidden Post', __CLASS__), 'tabcontrol_checkbox');
	}
 
	/**
	 * Save our data to the database
	 */
	public function action_publish_post( $post, $form )
	{
		if(!isset($form->sharesecret->value) || empty($form->sharesecret->value)) {
			$post->info->sharesecret = md5($post->content);
		}
		else {
			$post->info->sharesecret = $form->sharesecret->value;
		}
	}
}
?>