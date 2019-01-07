<?php
/**
 * @package test plugin
 * @version 1.0
 */
/*
Plugin Name: Test Plugin for data generation
Plugin URI: http://#
Description: This is plugin to tesst forms ald list creation for custom data 
Author: Bob Dee
Version: 1.0
Author URI: http://#
*/



add_action( 'init', function() {
	include dirname(__FILE__) . '/includes/class-client-admin-menu.php';
	include dirname(__FILE__) . '/includes/class-client-list-table.php';
	include dirname(__FILE__) . '/includes/class-form-handler.php';
	include dirname(__FILE__) . '/includes/client-functions.php';
	new Client_Admin_Menu();
});



// // define the wpcf7_form_elements callback 
// function filter_wpcf7_form_elements( $this_replace_all_form_tags ) { 
//     // make filter magic happen here... 
//     echo 'magic';
//     //echo $this_replace_all_form_tags;
//     return 'magic'; 
// } 


// update headers of columns with dates instead of the name labels
function filter_wpcf7_form_elements( $html ) {
	$main_form_id=10;
	$pre_form_id=38;
	$form = wpcf7_get_current_contact_form();
	if ($main_form_id == $form->id()){
		$received_date = $_GET["order-date"];
		$html = str_replace('<label>mon</label>', '<label>'.$received_date.'</label>', $html);
	}
	// elseif ($pre_form_id  == $form->id()){
	// 	$received_data = get_prefetch_data_from_db();
	// 	//var_dump($received_data);

	// 	$string = '<input type="text" name="your-name" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false">';
	// 	$pattern = '/\<(.*) name="your-name" (.*)\>/i';
	// 	$replacement = '<$1 name="your-name" $2 placeholder='.$received_data->name.'>';
	// 	echo " Here is the received data ".$received_data->name;
	// 	$res_name = preg_replace($pattern, $replacement, $html);
	// 	// echo " Here is the result \n".$res_name."\n";

	// 	$matches = false;
	// 	preg_match('/<input type="text" name="your-name".*[^>]*>(.*)>/iU', $html, $matches); 
	// 	if ($matches) {
	// 		$select = '<input type="text" name="your-name" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="here is my text">';
	// 		$html = preg_replace('/<input type="text" name="your-name".*[^>]*>(.*)>/iU', $select, $html);
	// 	}



		// $html = str_replace(
		// 	'<input type=\"text\" name=\"your-name\" value=\"\" size=\"40\" class=\"wpcf7-form-control wpcf7-text wpcf7-validates-as-required\" aria-required=\"true\" aria-invalid=\"false\">',
		//  	'<input type=\"text\" name=\"your-name\" value=\"XXX\" size=\"40\" class=\"wpcf7-form-control wpcf7-text wpcf7-validates-as-required\" aria-required=\"true\" aria-invalid=\"false\" placeholder=\"here is my text\">', 
		//  	$html);

	//	<input type="text" name="your-name" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false">

	//	<input type="text" name="your-name" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false" placeholder="here is my text">
	// }
	return $html;
}

function my_wpcf7_form_elements($html) {
	function ov3rfly_replace_include_blank($name, $text, &$html) {
		$matches = false;
		preg_match('/<select name="' . $name . '"[^>]*>(.*)<\/select>/iU', $html, $matches);
		if ($matches) {
			$select = str_replace('<option value="">---</option>', '<option value="">' . $text . '</option>', $matches[0]);
			$html = preg_replace('/<select name="' . $name . '"[^>]*>(.*)<\/select>/iU', $select, $html);
		}
	}
	
	function ov3rfly_replace_defaults($name, $type, $text, &$html) {
		$matches = false;
		preg_match('/<input type="'.$type.'" name="'.$name.'"(.*)>/iU', $html, $matches); 
		if ($matches) {
			echo " matched for ".$type." and ".$name;
			$select = '<input type="'.$type.'" name="'.$name.'" value="'.$text.'" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false">';
			$html = preg_replace('/<input type="'.$type.'" name="'.$name.'"(.*)>/iU', $select, $html);
		}
	}
	ov3rfly_replace_include_blank('menu-296', 'zigota', $html);

	$received_data = get_prefetch_data_from_db();

	ov3rfly_replace_defaults('your-name', 'text', $received_data->name, $html);
	ov3rfly_replace_defaults('your-subject', 'text', $received_data->subject, $html);
	ov3rfly_replace_defaults('your-email', 'email', $received_data->mail, $html);
	
	// <input type="email" name="your-email" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-email wpcf7-validates-as-required wpcf7-validates-as-email" aria-required="true" aria-invalid="false">

	return $html;
}
add_filter('wpcf7_form_elements', 'my_wpcf7_form_elements');

//getting the data from database to fill the fields in db
function get_prefetch_data_from_db(){
	global $wpdb;

	$fetched_data = $wpdb->get_results( 
	"
	SELECT name, subject, mail, value 
	FROM tmp_submission
	WHERE id = 0 
	"
	);
	var_dump($fetched_data);
	return $fetched_data[0];
}
         
// add the filter 
add_filter( 'wpcf7_form_elements', 'filter_wpcf7_form_elements', 10, 1 ); 

// add_filter('cf7sg_pre_cf7_field_html', 'filter_pre_html', 10, 2);
// function filter_pre_html($html, $cf7_key){
//   //the $html string to change
//   //the $cf7_key is a unique string key to identify your form, which you can find in your form table in the dashboard.
// }

function debug_filter($posted_data){
	 echo '<pre>'.print_r( $posted_data, true ).'</pre>';
	 echo "<script> alert($posted_data); </script>"; 
	 return $posted_data;
}

function debug_hello($posted_data){
	$current_user = wp_get_current_user();

	$form = wpcf7_get_current_contact_form();

	 echo '<pre>'.print_r( $current_user->display_name , true ).'</pre>';
	 echo '<pre>'.print_r(  $posted_data, true ).'</pre>';
	 var_dump($_GET);
	 var_dump($_POST);
	 if ($form->name() == 'prefetch') {
	 	echo "PREFETCH";
	 }

	 //echo "<script> alert($posted_data["name"]); </script>"; 
	 return $posted_data;
}

function action_wpcf7_data( $posted_data ) {  
 //   $submission = WPCF7_Submission::get_instance();
	// $posted_data = $submission->get_posted_data();
	 // echo '<pre>'.print_r( $posted_data["mon_kru"], true ).'</pre>';

	global $wpdb;
	$pre_form_id = 38;
	$main_form_id=10;

	$form = wpcf7_get_current_contact_form();

	if($pre_form_id  == $form->id())
	{
		$wpdb->insert("tmp_submission", array(
   		"name" => $posted_data["your-name"],
   		"subject" => $posted_data["your-subject"],
   		"mail" => $posted_data["your-email"],
   		"value" => $posted_data["order-date"],
	)); 
	}
	elseif($main_form_id  == $form->id())
	{
		$wpdb->insert("tmp_submission", array(
   		"name" => "main_form",
   		"value" => $posted_data["date-start"],
	));
	}
	return $posted_data; 
}

add_filter( 'wpcf7_posted_data', 'action_wpcf7_data', 10, 1 ); 


// function update_date_labels($tag){

// 	 if($tag['name'] == 'date-start') {
// 	 	echo '<pre>'.print_r( $tag['value'], true).'</pre>';
// 	 }
// 	 //echo "<script> alert($posted_data["name"]); </script>"; 
// 	 return $tag;
// }
     
// add_filter('wpcf7_posted_data','debug_filter');

// add_filter('wpcf7_before_send_mail','filter_wpcf7_form_elements');


add_action( 'wp_footer', 'cf7_redirect' );
//add_filter( 'the_content', 'debug_hello' );
add_filter('wpcf7_form_name_attr', 'debug_hello', 10, 3);
// add_filter( 'wpcf7_form_tag', 'update_date_labels', 10, 2);

//add_action( 'wpcf7_before_send_mail', 'debug_filter' ); 


function cf7_redirect() {
?>
<script type="text/javascript">
document.addEventListener( 'wpcf7mailsent', function( event ) {
    if ( '10' == event.detail.contactFormId ) {
       location = 'http://r2d2.local/wp/thanks/';
    } 
    else if ('38' == event.detail.contactFormId ) {
		var inputs = event.detail.inputs;
		//alert(event.detail);
		console.log(event.detail);
		var orderDate='2019-01-01'; //defaut value
		// Ищем поле с именем order-date и злоупотребляем alert'ом при нахождении поля
		for ( var i = 0; i < inputs.length; i++ ) {
			if ( 'order-date' == inputs[i].name ) {
				orderDate = inputs[i].value; //TODO: fix the search method
				//alert( orderDate );
				break;
			}
		}
		resultingDate = orderDate.split("-");
		var theDate = resultingDate[2]+'.'+resultingDate[1];
    	// location = 'http://r2d2.local/wp/%D1%80%D0%BE%D0%B4%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D1%81%D0%BA%D0%B0%D1%8F-%D1%81%D1%82%D1%80%D0%B0%D0%BD%D0%B8%D1%86%D0%B0-%D0%B4%D0%BB%D1%8F-%D1%84%D0%BE%D1%80%D0%BC/orders/?order-date=' + theDate;
    	   
    } 
}, false );
</script>
<?php
}


// add the filter 
// add_action('wpcf7_before_send_mail', 'printDatas');

// function printDatas($cf7) {
// echo 'printDatas';
// global $wpdb;
// //declare your varialbes, for example:
// $id = $cf7->posted_data[“id”];
// $first_name = $cf7->posted_data[“your-name”];
// $email_txt = $cf7->posted_data[“email-id”];
// echo $first_name;
// echo $email_txt;
// }
