<?php
   /*
   Plugin Name: pgOption Creator
   Plugin URI: http://option.com
   Description: a plugin to test an option page
   Version: 1.0
   Author: Mr. Option
   Author URI: http://option.com
   License: GPL2
   */

	if ( ! function_exists( 'wp_handle_upload' ) ) {
    	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}
	

class Pg_option {

	public $options;




	public function __construct(){
		$this->options = get_option('Pg_option_plugin');
		$this->register_fields();	
	}

	public static function add_menu_page(){
		add_options_page("Page_title", "Menu_title", "administrator", __FILE__,array('Pg_option','display_options_page'));
	}

 	// do_settings_sections( $page );
	public static function display_options_page(){
		?>
			<div class="wrapper">
				
				<?php 
					// il codice seguente ci permette di vedere i valori presenti all'interno delle nostre opzioni 				
					
					$o = get_option('Pg_option_plugin');
					echo '<pre>';
					print_r($o);
					// per il singolo valore echo $o['Pg_option_banner'];
					echo '</pre>';
				?>

				<?php  //get_screen_icon(); non più usata 3.8+ ?>				
				<h2>Form di esempio.</h2>
				<form method="post" action="options.php" enctype="multipart/form-data"> 		
					<?php settings_fields('Pg_option_plugin'); ?>
					<?php do_settings_sections(__FILE__); ?>

					<p class="submit">
						<input name="submit" type="submit" class="button-primary" value="Save"></input>	
					</p>		

				</form>
			
				<form method="post" action="">
 					<p class="submit">
 						Settings reset: <input name="reset" class="button button-secondary" type="submit" value="Reset to theme default settings" >
 						<input type="hidden" name="action" value="reset" />
 					</p>
				</form>	



			</div>
		<?php
	}


	public  function register_fields(){
		// <?php register_setting( $option_group, $option_name, $sanitize_callback );  ,
		register_setting('Pg_option_plugin','Pg_option_plugin',array($this,'Pg_option_validate'));
		
		//add_settings_section( string $id, string $title, callable $callback, string $page )
		add_settings_section('Pg_option-section','Impostazioni Principali',array($this,'Pg_option_section_sn'),__FILE__);

		//add_settings_field( string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = array() )
		add_settings_field( 'Pg_option_banner','Testata banner',array($this,'Pg_option_tst_settings') , __FILE__, 'Pg_option-section');

		add_settings_field( 'Pg_option_logo','Logo',array($this,'Pg_option_lg_settings') , __FILE__, 'Pg_option-section');

		add_settings_field('Pg_option_cscheme', 'Color',array($this,'Pg_option_color'),__FILE__,'Pg_option-section'); 

	}

	public function Pg_option_validate($input){
		
	/*	    Esempio di codice di debug delle variabili

		    error_log(print_r($input,1));
		    error_log(print_r('------ Pg_option_banner ------',1));
			error_log(print_r($this->options['Pg_option_banner'],1));
			error_log(print_r('------- FILES -----',1));
			error_log(print_r(empty($_FILES['name_logo']['tmp_name']),1));
			error_log(print_r('------------',1));
	*/

		 
		if(empty($input['Pg_option_banner'])){
			if (!empty($this->options['Pg_option_banner'])){	
				$input['Pg_option_banner']=$this->options['Pg_option_banner'];
			} 
		}
		 

		if (!empty($_FILES['name_logo']['tmp_name']))  {
			$override=array('test_form'=>false);
			
			error_log(print_r('----- FILES ----',1));
			error_log(print_r($_FILES,1));
			
			//  wp_handle_upload( $file, $overrides, $time ); 
			$file = wp_handle_upload($_FILES['name_logo'],$override);


			// funzione alternativa per l'upload dell'immagine	
			// media_handle_upload( $file_id, $post_id, $post_data, $overrides ); 
			//$file = media_handle_upload($_FILES['name_logo'],0);

			error_log(print_r('----- file ----',1));
			error_log(print_r($file,1));



			/*	Se le options non sono settate avremo un doppio passaggio nella funzione sanitize il che crea dei 		problemi nell'aggiornamento dei messaggi d'errore e nella gestione dell'upload del file 
			*/

			if  ((isset($file['error']) || isset($file['upload_error_handler'])) && (empty($file['url']))) {			
				if (empty($input['logo'])) {		 								
	    			add_settings_error(
	    				'Pg_option_plugin', // whatever you registered in `register_setting
	    				'00012', // doesn't really mater
	    				"Errore nell''inserimento delle opzioni",
	    				'error' // error or notice works to make things pretty
					);
	    			// chiamata automaticamente al submit		
					//settings_errors('Pg_option_plugin');
				}
			} else {$input['logo'] = $file['url'];}

		} else { $input['logo'] = $this->options['logo']; }


		//echo '<input type="text" name="test_api_opt" value="VALIDATE" />';
				
		return $input;
	} 

	public function Pg_option_tst_settings(){

		printf(
            "<input type='text'  id = 'Pg_option_banner_id' name='Pg_option_plugin[Pg_option_banner]' value='%s' />",
            isset( $this->options['Pg_option_banner'] ) ? esc_attr( $this->options['Pg_option_banner']) : ''
        );

	}

	public function Pg_option_lg_settings(){
		echo "<input  id='id_logo' name='name_logo' type='file'/>";

		if (isset($this->options['logo'])) {
				echo "<img src='{$this->options['logo']}' alt='' />";
		}

	}


	public function Pg_option_color(){
		$items = array('Rosso','Giallo','Verde');
		echo " <select name= 'Pg_option_plugin[Pg_option_cscheme]'>";
		foreach ($items as $item){
			$selected =  ($this->options['Pg_option_cscheme'] === $item) ? 'selected="selected"':'';
			echo "<option value='$item' $selected>$item</option>";
		}		 
		echo "</select>";
	}


	public function Pg_option_section_sn(){
		//opzionale
	}

	public static function Pg_option_reset(){
		// Reset delle Options
		delete_option('Pg_option_plugin');
	}

}

add_action('admin_menu',function(){
	Pg_option::add_menu_page();
});

add_action('admin_init',function(){
	new Pg_option();
});

/*
add_filter( 'upload_mimes', 'your_custom_mimes' );
function your_custom_mimes( $mimes ) {
	error_log(print_r($mimes,1));
    $mimes['jpg'] = 'image/jpeg';
    return $mimes;
}
*/

// Tasto che richiede il reset dei valori delle opzioni
if(isset($_POST['reset'])) {
	Pg_option::Pg_option_reset();
}

?>