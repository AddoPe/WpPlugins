<?php
 /*
   Plugin Name: pgTypeCl  Creator
   Plugin URI: http://type.com
   Description: a plugin to test an new type Version: 1.0
   Author: Mr. Type Taxonomy Class Example
   Author URI: http://type.com
   License: GPL2
 */


	// https://github.com/jjgrainger/wp-custom-post-type-class
    // https://gist.github.com/dobbyloo/6478616

	include_once('CPT.php');
	include_once('CustomTaxonomy.php');

	$people = new CPT(array(
		'post_type_name' => 'person',
		'singular' => 'Person',
		'plural' => 'People',
		'slug' => 'people'
	));

	$people->register_taxonomy(array(
		'taxonomy_name' => 'age',
		'singular' => 'Age',
		'plural' => 'Ages',
		'slug' => 'age'
	));

	$people->register_taxonomy(array(
		'taxonomy_name' => 'instruction',
		'singular' => 'Instruction',
		'plural' => 'Instructions',
		'slug' => 'instruction'
	));


?>