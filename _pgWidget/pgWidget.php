<?php
/*
   Plugin Name: pgWidget
   Plugin URI: http://Widget.com
   Description: a plugin to test an new type Version: 1.0
   Author: Mr. widget
   Author URI: http://widget.com
   License: GPL2
 */
function register_pg_twitter_widget(){
	register_widget('Pg_Twitter_Widget');
}
add_action('widgets_init','register_pg_twitter_widget');

class  Pg_Twitter_Widget  extends WP_Widget {

	function __construct(){
		$options =array(
			'description' => 'Display and cache tweets',			
		);		
		parent::__construct('Pg_Twitter_Widget','Display Tweets',$options); 
	}

	public function form($instance){
		

		extract($instance);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title');?>">Title:</label>
			<input type="text"
				class="widefat"
				id="<?php echo $this->get_field_id('title');?>"
				name="<?php echo $this->get_field_name('title'); ?>"
				value ="<?php if (isset($title)) echo esc_attr($title); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('username');?>">Twitter Username:</label>
			<input type="text"
				class="widefat"
				id="<?php echo $this->get_field_id('username');?>"
				name="<?php echo $this->get_field_name('username'); ?>"
				value ="<?php if (isset($username)) echo esc_attr($username); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('tweet_count');?>">Number of Tweets to Retrive:</label>
			<input type="number"
			class="widefat"
			style="width:40px;"
			id="<?php echo $this->get_field_id('tweet_count');?>"
			name="<?php echo $this->get_field_name('tweet_count'); ?>"
			min="1"
			max="10"
			value ="<?php echo !empty($tweet_count) ? $tweet_count : 5;?>" />
		</p>

		<?php

	}

	public function widget($args, $instance){

		extract($args);
		extract($instance);


		if (empty($title)) $title = 'Recent Tweets';
		$data = $this->twitter($tweet_count,$username);
		if (false !==$data && isset($data->tweets)) {
			echo $before_widget;
				echo $before_title;
					echo $title;
				echo $after_title;
				
				echo '<ul><li>'.implode('</li><li>',$data->tweets).'</li></ul>';
			echo $after_widget;	
		}

	}

	// Updating widget replacing old instances with new
	/*
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
	*/

	private function twitter($tweet_count,$username) {
		if (empty($username) ) return false;

		$tweets = get_transient('recent_tweets_widget');

		if (!$tweets ||
			$tweets->username !== $username ||
			$tweets->tweet_count !== $tweet_count) {
			return $this->fetch_tweets($tweet_count,$username);
		}

		return $tweets;
	}


	private function fetch_tweets($tweet_count,$username){

		global $id;
		
	    $url = "https://api.twitter.com/1.1/statuses/user_timeline.json" ;
	
	    $consumer_key = "NpZEv2Em2OnXfHnSY7MNh4gl*****";    
	    $consumer_secret = "nq4jyTJx0tchfVg9Sj*****wooWfnWOAikpxOrLxk5jeyKgJGrt8Nq";    
	    $oauth_access_token = "880*****46703186825218-L2v52coMWE96VMyE4BnxGAOeUFGvcBN";    
	    $oauth_access_token_secret = "U8HE2NLsDqpsQI******jOfAJjb0JYwqM6PKCpQqcZTUziA9vL";
	    
	    $oauth = array( 'oauth_consumer_key' => $consumer_key,
	                    'oauth_nonce' => time(),
	                    'oauth_signature_method' => 'HMAC-SHA1',
	                    'oauth_token' => $oauth_access_token,
	                    'oauth_timestamp' => time(),
	                    'oauth_version' => '1.0');

	    $base_info = buildBaseString($url,'GET',$oauth);
	    $composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
	    $oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
	    $oauth['oauth_signature'] = $oauth_signature;

	    // Make requests
	    $header = array(buildAuthorizationHeader($oauth), 'Expect:');
	    $options = array( CURLOPT_HTTPHEADER => $header,
	                      //CURLOPT_POSTFIELDS => $postfields,
	                      CURLOPT_HEADER => false,
	                      CURLOPT_URL => $url,
	                      CURLOPT_RETURNTRANSFER => true,
	                      CURLOPT_SSL_VERIFYPEER => false);

	    $feed = curl_init();
	    curl_setopt_array($feed, $options);
	    $json = curl_exec($feed);
	    curl_close($feed);

	    $twitter_data = json_decode($json);

		if (isset($twitter_data->error)) return false;


		$data = new stdClass();
		$data->username = $username;
		$data->tweet_count = $tweet_count;
		$data->tweets = array();


		foreach ($twitter_data as $tweet) {
			if ($tweet_count-- == 0 ) break;
			$data->tweets[] = $this->filter_tweet($tweet->text);
		}

		error_log(print_r($data,1));	    		

		set_transient ('recent_tweets_widget',$data,60*5);

	}


	private function filter_tweet($tweet) {
		$tweet = preg_replace ('/(http[^\s]+)/im' , '<a href="$1">$1</a> ' , $tweet );
		$tweet = preg_replace(  '/@([^\s]+)/i', '<a herf="Http.../$1">@$1</a>'  ,$tweet);
		return $tweet;
	}

	function buildBaseString($baseURI, $method, $params) {
		$r = array();
		ksort($params);
		foreach($params as $key=>$value){
		    $r[] = "$key=" . rawurlencode($value);
		}
		return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
	}

    function buildAuthorizationHeader($oauth) {
        $r = 'Authorization: OAuth ';
        $values = array();
        foreach($oauth as $key=>$value)
            $values[] = "$key=\"" . rawurlencode($value) . "\"";
        $r .= implode(', ', $values);
        return $r;
    }


}


?>