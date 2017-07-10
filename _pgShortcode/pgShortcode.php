<?php
 /*
   Plugin Name: pgShortcode  Creator
   Plugin URI: http://shortcode.com
   Description: a plugin to test an new type Version: 1.0
   Author: Mr. shortcode Example
   Author URI: http://shortcode.com
   License: GPL2
 */

	add_shortcode('tweet',function($attr,$cont){
		// nel caso non ci siano attributi
		if (!isset($attr['username'])) $attr['username'] = 'fattoquotidiano';
		//return '<a href="https://twitter.com/'.$attr['username'].'">Leggi i miei post.</a>';
		// or 
		// return "<a href='https://twitter.com/{$attr['username']}'>Leggi i miei post.</a>";
		// se con contenuto	
		if (empty($cont)) $cont = 'Contenuto.';
		return "<a href='https://twitter.com/{$attr['username']}'>{$cont}</a>";		
	});

	add_shortcode('tweety',function($attr,$cont){
		/*
		shortcode_atts( $pairs , $atts, $shortcode ); 

		$pairs (array) (richiesto) Lista con tutti gli attributi supportati e i loro valori predefiniti - Default: Nessuno
		$atts  (array) (richiesto) Attributi definiti dall'utente nel tag dello shortcode - Default: Nessuno 
		$shortcode (string) (opzionale) Nome dello shortcode da usare nel filtro shortcode_atts_{$shortcode} - Anche se questo parametro è opzionale, è meglio indicarlo, altrimenti sarà impossibile per i plugin fare riferimento a questo shortcode per la pre-elaborazione. -  Default: Nessuno 
		*/

		$attr = shortcode_atts(
			array(
			'username' => 'addopecile',
			'content' => !empty($cont) ? $cont: 'Seguimi  su Twitter',
			'show_tweets' => true,
			'tweet_reset_time' => 1,
			'num_tweets' => 5
			),$attr); 

		extract($attr);	
		if ($show_tweets) { $tweets = fetch_tweets($num_tweets,$username,$tweet_reset_time); }
			
		echo "<pre>";
		print_r($tweets);
		echo "</pre>";
		
		return "<a href='https://twitter.com/{$username}'>{$content}</a>";	

	});

	function reset_data($recent_tweets,$tweet_reset_time){
		global $id;
		
		if (isset($recent_tweets[0][0])) {$time = $recent_tweets[0][0];}		
		if (isset($time)){
			$delay = (int)$time + (int) $tweet_reset_time;
			if ($delay>= 60 ) $delay -=60;
			if ($delay <= (int)date('i',time()) ){
				delete_post_meta($id,'pg_recent_tweets');
			}			
		}
	}

	function fetch_tweets($num_tweets,$username,$tweet_reset_time){

		global $id;
		
		$recent_tweets = get_post_meta($id,'pg_recent_tweets');				
		reset_data($recent_tweets,$tweet_reset_time);
		

		if (empty($recent_tweets)){	

		    
		    $url = "https://api.twitter.com/1.1/statuses/user_timeline.json" ;

		    $consumer_key = "NpZEv2Em2OnXfHnSY7MNh4gl*****";    
		    $consumer_secret = "nq4jyTJx0tchfVg9Sj*****wooWfnWOAikpxOrLxk5jeyKgJGrt8Nq";    
		    $oauth_access_token = "880*****46703186825218-L2v52coMWE96VMyE4BnxGAOeUFGvcBN";    
		    $oauth_access_token_secret = "U8HE2NLsDqpsQI******jOfAJjb0JYwqM******PKCpQqcZTUziA9vL";
		    

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
		                      CURLOPT_HEADER => false,
		                      CURLOPT_URL => $url,
		                      CURLOPT_RETURNTRANSFER => true,
		                      CURLOPT_SSL_VERIFYPEER => false);

		    $feed = curl_init();
		    curl_setopt_array($feed, $options);
		    $json = curl_exec($feed);
		    curl_close($feed);

		    $twitter_data = json_decode($json);

			$data = array();

			foreach( $twitter_data as $tweet) {
				if ($num_tweets --== 0 ) break;
				$data[] = $tweet->text;
			}

			$recent_tweets = array((int) date('i',time()));
			$recent_tweets[] = '<ul class="pg_tweets"><li>'.implode('</li><li>',$data).'</li></ul>';   

			cache($recent_tweets);

		} // end empty recent tweet

		return isset($recent_tweets[0][1]) ? $recent_tweets[0][1] : $recent_tweets[1];

	}

	function cache($recent_tweets) {
		// [0] = current minute
		// [1] = tweet html fragment

		global $id;
		add_post_meta($id,'pg_recent_tweets',$recent_tweets);

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


?>