<?php
/*
   Plugin Name: pgSchedule Creator
   Plugin URI: http://Schedule.com
   Description: a plugin to test an new type Version: 1.0
   Author: Mr. Schedule
   Author URI: http://Schedule.com
   License: GPL2
 */

add_action('init',function(){
	
	//$time =wp_next_scheduled('pg_cron_hook');
	//wp_unschedule_event($time,'pg_cron_hook');  

	if (!wp_next_scheduled('pg_cron_hook')){
		wp_schedule_event(time(),'ten-minutes','pg_cron_hook');

		// possiamo schedulare anche un singolo evento
		//wp_schedule_single_event(time()+3600,'pg_cron_hoook');
	}	

});

add_action('admin_menu',function(){
	// add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);
	add_options_page('Cron Settings','Cron Settings','manage_options','pg-cron',function(){
		$cron= _get_cron_array();
		$schedules = wp_get_schedules();
		//print_r($schedules); 
		?>
		<div class="wrap">
			<h2>Cron Events Scheduled</h2>		
			<?php
				foreach($cron as $time => $hook){
					echo "<h3>$time</h3>";
					print_r($hook);	
				}

			?>
		</div>
		<?php
	});
});



add_action('pg_cron_hook',function(){
	$str =time();
	//wp_mail( string|array $to, string $subject, string $message, string|array $headers = '', string|array $attachments = array() )
	wp_mail('addopecile@gmail.com','Scheduled with WP_Cron!',$str);	

	$sent = wp_mail('addopecile@gmail.com','Scheduled with WP_Cron!',$str);	
	$converted_res = ($sent) ? 'true' : 'false';
	error_log(print_r('mail spedita from pgSchedule:'.$converted_res,1));

});

add_filter('cron_schedules',function(){
	$schedules['two-minutes'] = array(
		'interval' =>120,
		'display'  =>'Every Two Minutes'
	);

	$schedules['ten-minutes'] = array(
		'interval' =>600,
		'display'  =>'Every Ten Minutes'

	);

	return $schedules;
});


?>