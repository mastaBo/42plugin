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
		echo " The received date is ".$received_date;
		$week_dates = get_week($received_date);
		$html = str_replace('<label>mon</label>', '<label>'.convert_date($week_dates[0]).'</label>', $html);
		$html = str_replace('<label>tue</label>', '<label>'.convert_date($week_dates[1]).'</label>', $html);
		$html = str_replace('<label>wed</label>', '<label>'.convert_date($week_dates[2]).'</label>', $html);
		$html = str_replace('<label>thu</label>', '<label>'.convert_date($week_dates[3]).'</label>', $html);
		$html = str_replace('<label>fri</label>', '<label>'.convert_date($week_dates[4]).'</label>', $html);
		$html = str_replace('<label>sat</label>', '<label>'.convert_date($week_dates[5]).'</label>', $html);
		$html = str_replace('<label>sun</label>', '<label>'.convert_date($week_dates[6]).'</label>', $html);

		$positions = get_positions_order_from_db('2018-10-08', '2018-10-14', 3);
		echo " got the positions ".$positions['2018-10-08'][2];
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
	
	function ov3rfly_replace_defaults($name, $type, $text, &$html) {
		$matches = false;
		preg_match('/<input type="'.$type.'" name="'.$name.'"(.*)>/iU', $html, $matches); 
		if ($matches) {
			echo " matched for ".$type." and ".$name;
			$select = '<input type="'.$type.'" name="'.$name.'" value="'.$text.'" size="40" class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required" aria-required="true" aria-invalid="false">';
			$html = preg_replace('/<input type="'.$type.'" name="'.$name.'"(.*)>/iU', $select, $html);
		}
	}

	$received_data = get_prefetch_data_from_db();

	//ov3rfly_replace_defaults('your-name', 'text', $received_data->name, $html);
	//ov3rfly_replace_defaults('your-subject', 'text', $received_data->subject, $html);
	ov3rfly_replace_defaults('order-date', 'date', $received_data->value, $html);
	
	// <input type="email" name="your-email" value="" size="40" class="wpcf7-form-control wpcf7-text wpcf7-email wpcf7-validates-as-required wpcf7-validates-as-email" aria-required="true" aria-invalid="false">

	return $html;
}
add_filter('wpcf7_form_elements', 'my_wpcf7_form_elements');

//getting the data from database to fill the fields in form
function get_prefetch_data_from_db(){
	global $wpdb;

	$fetched_data = $wpdb->get_row( 
	"
	SELECT name, subject, mail, value 
	FROM tmp_submission
	WHERE id = 1 
	"
	);
	//var_dump($fetched_data);
	return $fetched_data;
}

// get the positions order quantity for specific dates
function get_positions_order_from_db($date_start, $date_end, $id){
	global $wpdb;
	$results = array(array());
	$positions_quantity = $wpdb->get_results( 
	"
	SELECT position_id, quantity, date FROM 42_positions_order 
	WHERE 
	(42_positions_order.date BETWEEN '2018-10-08' AND '2018-10-14')
	AND order_id=3 
	"
	);
	foreach ( $positions_quantity as $position ) 
	{
		$results[$position->date][$position->position_id] = $position->quantity;
	}
	return $results;
}


// SELECT * FROM `42_positions_order` WHERE date='2018-10-08' AND order_id=3
         
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

//the input is any date in yyyy-mm-dd format and output is the array with 7 dates set according to the week this date belongs. 
function get_week($date){
    $date_stamp = strtotime(date('Y-m-d', strtotime($date)));

     //check date is sunday or monday
    $stamp = date('l', $date_stamp);      

    if($stamp == 'Mon'){
        $monday = $date;
    }else{
        $monday = date('Y-m-d', strtotime('Last Monday', $date_stamp));
    }

    $thuesday = date('Y-m-d', strtotime($monday."+1 day"));
    $wednesday = date('Y-m-d', strtotime($monday."+2 days"));
    $thursday = date('Y-m-d', strtotime($monday."+3 days"));
    $friday = date('Y-m-d', strtotime($monday."+4 days"));
    $saturday = date('Y-m-d', strtotime($monday."+5 days"));
    $sunday = date('Y-m-d', strtotime($monday."+6 days"));
          
    return array($monday, $thuesday, $wednesday, $thursday, $friday, $saturday, $sunday);
}

//converts date from yyyy-mm-dd format into russian style dd.mm format
function convert_date($date){
	$res = explode("-", $date);
	return $res[2].'.'.$res[1];
}

add_action( 'wp_footer', 'cf7_redirect' );
//add_action( 'wp_footer', 'cf7_check_params_on_submit' );
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
    	location = 'http://r2d2.local/wp/%D1%80%D0%BE%D0%B4%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D1%81%D0%BA%D0%B0%D1%8F-%D1%81%D1%82%D1%80%D0%B0%D0%BD%D0%B8%D1%86%D0%B0-%D0%B4%D0%BB%D1%8F-%D1%84%D0%BE%D1%80%D0%BC/orders/?order-date=' + orderDate;
    } 
}, false );
</script>
<?php
}

function cf7_check_params_on_submit() {
	?>
	<script type="text/javascript">
	document.addEventListener( 'wpcf7submit', function( event ) {
		if ('38' == event.detail.contactFormId ) {
			var inputs = event.detail.inputs;
			for ( var i = 0; i < inputs.length; i++ ) {
				if ( 'order-date' == inputs[i].name ) {
					orderDate = inputs[i].value; 
					alert( orderDate );
					break;
				}
			}
		}
	}	
	</script>
<?php
}

