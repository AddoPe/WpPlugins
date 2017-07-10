<?php

add_shortcode('pg_books', function(){

	$loop = new WP_Query(
	array(
		'post_type' => 'pg_books',
		'orderby' => 'title'
		));

	if ($loop->have_posts()) {

		$output ='<ul class="pg_books_list">';
		while($loop->have_posts()){
			$loop->the_post();			
			$meta = get_post_meta(get_the_id());											
			$output .= '
			<li>
				<a href="'. get_permalink(). '">
				'.get_the_title().'|'.
				$meta['pg_book_lenght'][0].'</a> 
				<div>'.get_the_excerpt().'</div>
			</li>';

		}
	} else {
		return '<p>Nessun Libro presente.</p>';		
	}

	$output .= '</ul>';

	return $output;
});



?>