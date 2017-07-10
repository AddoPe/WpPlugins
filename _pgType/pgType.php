<?php

 /*
   Plugin Name: pgType Creator
   Plugin URI: http://type.com
   Description: a plugin to test an new type Version: 1.0
   Author: Mr. Type
   Author URI: http://type.com
   License: GPL2
 */


include dirname(__FILE__).'/books_type_shortcode.php';

add_action ('init', function(){   
	new Pg_Post_Type(); 	
});  


 class Pg_Post_Type {

	public function __construct(){
		// la tassonomia deve essere già presente nel momento in cui si crea il post	
     	$this->taxonomies();		
		$this->register_post_type();
		$this->metaboxes(); 
 	} 
   public function register_post_type(){

      // https://codex.wordpress.org/Function_Reference/register_post_type
      $args = array (
         'labels'=>array(
            'name'=>'Books', 
            'singular_name'=>'Book',
            'add_new'=>'Add New Book',
            'add_new_item'=>'Add New Book',
            'edit_item'=>'Edit Item',
            'new_item'=>'Add New Item',
            'view_item'=>'View Movie',
            'search_items'=>'Search Books',
            'not_found'=>'No Books Found',
            'not_founf_in_trash'=>'No Books Found in Trash'
         ),         
         'public' => true,
         'publicly_queryable' => true,
         'exclude_from_search' => false,
         // 'capability_type' => 'post',         
         'taxonomies' => array('genre'),
         'has_archive' => true,
         'rewrite' => true,
         'query_var' => true,
         'hierarchical' => false,
         'rewrite' => array('slug' => 'books/'),
         'menu_position' => 5, // 25 - below comments 
         'menu_icon' => admin_url().'images/media-button-video.gif',
         'supports' => array(
            'title',
            'thumbnail', 
            'excerpt',
            // togliamo i custom-fields perchè utilizziamo i metaboxes
            //'custom-fields' 
         )


      );
      // in fase di debug attivando le opzioni di Wp relative al debug del codice possiamo 
      // analizzare il valore delle variabili impostate nel codice
      //error_log(print_r($args,1));

      // https://codex.wordpress.org/Function_Reference/register_post_type
      register_post_type('pg_Books',$args);
   }

   public function taxonomies() {
   	  	
      $taxonomies = array();
      
      $taxonomies['genre'] = array(
         //'name' => 'genre',
         'public' => true,
         'query_var' => 'book_genre',
         'rewrite' => array(
            'slug' => 'book/genre'
         ),
         'labels'=>array(               
               'name'=>'Genres', 
               'singular_name'=>'Genre',
               'add_new'=>'Add New Genre',
               'add_new_item'=>'Add New Genre',
               'edit_item'=>'Edit Item',
               'new_item'=>'Add New Item',
               'view_item'=>'View Genre',
               'search_items'=>'Search Genres',
               'not_found'=>'No Genres Found',
               'not_founf_in_trash'=>'No Genres Found in Trash'            
         ) 
      ); 
      

      $this->register_all_taxonomies($taxonomies);      

   }
   
   public function register_all_taxonomies($taxonomies){
       
      //error_log(print_r($taxonomies,1));   
      foreach($taxonomies as $name=>$arr) {
         register_taxonomy($name,array('pg_Books'),$arr);
      }
   }

	public function metaboxes(){
		add_action('add_meta_boxes', function(){
			add_meta_box('pg_book_lenght','Book_lenght',array($this,'book_lenght'),'pg_Books' );			
		});
		
		add_action('save_post',function($id){
			//error_log(print_r($_POST ,1));
			//http://php.net/manual/en/function.strip-tags.php  clean tags html
			if (isset($_POST['pg_book_lenght'])){
				error_log(print_r('save metaboxes' ,1));	
				update_post_meta($id,'pg_book_lenght',strip_tags($_POST['pg_book_lenght']));	
			}
		});
	}

	function book_lenght($post){		
		$lenght = get_post_meta($post->ID,'pg_book_lenght',true);
		?>	
			<p>
				<label for="pg_book_lenght" >Lenght:</label> 
				<input type='text' class="widefat" name="pg_book_lenght"  id="pg_book_lenght"  value="<?php  echo esc_attr($lenght); ?>"/> 
			</p>
		<?php 
	}

} 


?>