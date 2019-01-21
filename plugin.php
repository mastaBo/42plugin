<?php
/**
 * @package test plugin
 * @version 1.1
 */
/*
Plugin Name: Dymanic data manipulation on CF7 forms, wpforms, wpdatatables. 

Plugin URI: http://#
Description: This plugin is made for custom wp development and aim very specific use case of genearting and processing cf7 form data based on DB select contents. It makes the form extremely flexible and generated on-flght from DB. Results are also processed on-flight and sent to DB. Other plugins are used for custom user registration forms and data representation and submit.
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

// update headers of columns with dates instead of the name labels
function filter_wpcf7_form_elements( $html ) {
	$main_form_id=10;
	$pre_form_id=38;
	$form = wpcf7_get_current_contact_form();
	if ($main_form_id == $form->id()){
		$received_date = $_GET["order-date"];
		$username = $_GET["username"];

		//$today_date = date('Y-m-d', strtotime("today"));

		$week_dates = get_week($received_date);
		substitute_dates($html, $week_dates);

		$client_point_id=get_client_point_id($username);
		$client_point = get_client_point($client_point_id);	
		//FIXME remove the hack for order id 
		$order_id = get_client_order($week_dates[0], $week_dates[6], $client_point_id);
		//$order_id = get_client_order('2018-10-08', '2018-10-14', $client_point_id);
		if(null != $order_id){
		error_log("non empty order");

		} else {
			//here we shold add the order to DB
			error_log("empty order");
	  		$order_id=put_client_order($week_dates[0], $client_point_id);
		}
		//$fake_order = get_client_order($week_dates[0], $week_dates[6], $client_point_id);
		
		error_log("Fake order ");
		error_log( print_r( $fake_order, 1 ));

		$pos_list = get_positions();
		$client_name = get_client_name($client_point->company_id);
		echo "client_name = ".$client_name;
		//var_dump($pos_list);
		$positions_order = get_positions_order($week_dates[0], $week_dates[6], $order_id);
		
		foreach ($pos_list as $pos) {
			// here we should take the same week we done select for 
			generate_table_row($html, $pos, $positions_order, $week_dates);
			//generate_table_row($html, $pos, $positions_order, get_week('2018-10-09'));
		}


		replace_hidden_defaults('order_id', 'hidden', $order_id, $html);
		replace_hidden_defaults('client_name', 'hidden', $client_name, $html); 
		replace_hidden_defaults('client_point_name', 'hidden', $client_point->name, $html);
		replace_hidden_defaults('address', 'hidden', $client_point->address, $html);
		replace_hidden_defaults('start_date', 'hidden', $week_dates[0], $html);
		replace_hidden_defaults('end_date', 'hidden', $week_dates[6], $html);

		
		//position_id, quantity, date
		// $select = '<input type="number" name="'.get_weekday('2018-10-08').'_2" value="" class="wpcf7-form-control wpcf7-number wpcf7-validates-as-number" id="'.get_weekday('2018-10-08').'_2" min="0" aria-invalid="false" placeholder="'.$positions_order[2]['2018-10-08'].'">';
		// $html = preg_replace('/<input type="number" name="'.get_weekday('2018-10-08').'_2".*>/iU', $select, $html);
	}
	return $html;
}


function replace_hidden_defaults($name, $type, $text, &$html) {
	$matches = false;
	preg_match('/<input type="'.$type.'" name="'.$name.'"(.*)>/iU', $html, $matches); 
	if ($matches) {
		echo " matched for ".$type." and ".$name;
		$select = '<input type="'.$type.'" name="'.$name.'" value="'.$text.'" class="wpcf7-form-control wpcf7-hidden">';
		$html = preg_replace('/<input type="'.$type.'" name="'.$name.'"(.*)>/iU', $select, $html);
	}
}

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
function get_positions_order($date_start, $date_end, $id){
	global $wpdb;
	$results = array(array());
	$positions_quantity = $wpdb->get_results( 
	"
	SELECT position_id, quantity, date 
	FROM 42_positions_order 
	WHERE order_id=".$id." AND 
	(42_positions_order.date BETWEEN '".$date_start."' AND '".$date_end."')"
	);
	foreach ( $positions_quantity as $position ) 
	{
		$results[$position->position_id][$position->date] = $position->quantity;
	}
	return $results;
}

############################
function put_positions_order($date_start, $date_end, $id){
	global $wpdb;
	$results = array(array());
	$positions_quantity = $wpdb->get_results( 
	"
	SELECT position_id, quantity, date 
	FROM 42_positions_order 
	WHERE order_id=".$id." AND 
	(42_positions_order.date BETWEEN '".$date_start."' AND '".$date_end."')"
	);
	foreach ( $positions_quantity as $position ) 
	{
		$results[$position->position_id][$position->date] = $position->quantity;
	}
	return $results;
}

function put_client_order($date_start, $client_point){
	global $wpdb;

	$res = $wpdb->insert("42_client_order", array(
               "client_link" => $client_point,
               "date" => $date_start,
       )); 
	return $res;
}

function get_client_point_id($user){
	global $wpdb;
	$id = $wpdb->get_var( "SELECT meta.meta_value 
		FROM $wpdb->users AS user , 
		$wpdb->usermeta AS meta  
		WHERE meta.user_id=user.id 
		AND user.user_login='$user' 
		AND meta.meta_key='_client_point'" );
	return $id;
}

function get_client_point($point_id){
	global $wpdb;
	$client_point = $wpdb->get_row( "SELECT id, name, address, company_id
		FROM 42_client_point   
		WHERE id=".$point_id );
	return $client_point;
}

function get_client_order($date_start, $date_end, $client_point){
	global $wpdb;
	$order_id = $wpdb->get_var( "SELECT order_id
		FROM 42_client_order   
		WHERE client_link=".$client_point."
		AND date BETWEEN '".$date_start."' AND '".$date_end."'");
	return $order_id;
}

function get_client_name($client_id){
	global $wpdb;
	$name = $wpdb->get_var( "SELECT name
		FROM 42_clients   
		WHERE id=".$client_id);
	return $name;
}

function get_positions(){
	global $wpdb;
	$positions = $wpdb->get_results( "SELECT id, name
		FROM 42_positions   
		WHERE activity_status='active'");
	return $positions;
}

function substitute_dates(&$html, $week_dates){
		$html = str_replace('<label>пн</label>', '<label>'.convert_date($week_dates[0]).'</label>', $html);
		$html = str_replace('<label>вт</label>', '<label>'.convert_date($week_dates[1]).'</label>', $html);
		$html = str_replace('<label>ср</label>', '<label>'.convert_date($week_dates[2]).'</label>', $html);
		$html = str_replace('<label>чт</label>', '<label>'.convert_date($week_dates[3]).'</label>', $html);
		$html = str_replace('<label>пт</label>', '<label>'.convert_date($week_dates[4]).'</label>', $html);
		$html = str_replace('<label>сб</label>', '<label>'.convert_date($week_dates[5]).'</label>', $html);
		$html = str_replace('<label>вс</label>', '<label>'.convert_date($week_dates[6]).'</label>', $html);

		$html = str_replace('<label>mon</label>', '<label>'.convert_date($week_dates[0]).'</label>', $html);
		$html = str_replace('<label>tue</label>', '<label>'.convert_date($week_dates[1]).'</label>', $html);
		$html = str_replace('<label>wed</label>', '<label>'.convert_date($week_dates[2]).'</label>', $html);
		$html = str_replace('<label>thu</label>', '<label>'.convert_date($week_dates[3]).'</label>', $html);
		$html = str_replace('<label>fri</label>', '<label>'.convert_date($week_dates[4]).'</label>', $html);
		$html = str_replace('<label>sat</label>', '<label>'.convert_date($week_dates[5]).'</label>', $html);
		$html = str_replace('<label>sun</label>', '<label>'.convert_date($week_dates[6]).'</label>', $html);
}

function generate_table_row(&$html, $position, $positions_order, $week_dates){
	$row_header_template = "
<div class=\"row\">
    <div class=\"columns five\">
      <div class=\"field\"><label>".$position->name."</label>
      </div>
    </div>
    ";
  
  	$row_footer_template="</div>";
  	$generated_row = $row_header_template.print_rows_for_week($position, $positions_order, $week_dates).$row_footer_template;
	$html = str_replace('<!-- HERE IS THE PLACEHOLDER -->',$generated_row.'<!-- HERE IS THE PLACEHOLDER -->', $html);
}


function print_rows_for_week($position, $positions_order, $week_dates){	
 $res='';
 #TODO add check for empty row. In this case print the default one 
 $array_of_dates = $positions_order[$position->id];
 if(null == $array_of_dates){
 	// here we got no any values for the product in DB
 	//in this case we should return 7 lines with no value tag
 	for ($i=0; $i < 7; $i++){
 		$identifier=$week_dates[$i]."_".$position->id;
 		$res = $res."
 		<div class=\"columns one\">
      		<div class=\"field\"><span class=\"wpcf7-form-control-wrap ".$identifier."\">
				<input type=\"number\" name=\"".$identifier."\" value=\"\" class=\"wpcf7-form-control wpcf7-number wpcf7-validates-as-number\" id=".$identifier." min=\"0\" aria-invalid=\"false\" placeholder=\"0\">
	  		</div>
	  	</div>";
 	}
 }
 else {

 	for ($i=0; $i < 7; $i++){
		$identifier=$week_dates[$i]."_".$position->id;
		$value = $array_of_dates["$week_dates[$i]"];

 		if($value){
 			$res = $res."
 			<div class=\"columns one\">
      			<div class=\"field\"><span class=\"wpcf7-form-control-wrap ".$identifier."\">
					<input type=\"number\" name=\"".$identifier."\" value=\"".$value."\" class=\"wpcf7-form-control wpcf7-number wpcf7-validates-as-number\" id=".$identifier." min=\"0\" aria-invalid=\"false\" placeholder=\"".$value."\">
	  			</div>
	  		</div>";
 		} 
 		else {
 			$res = $res."
 				<div class=\"columns one\">
      				<div class=\"field\"><span class=\"wpcf7-form-control-wrap ".$identifier."\">
					<input type=\"number\" name=\"".$identifier."\" value=\"\" class=\"wpcf7-form-control wpcf7-number wpcf7-validates-as-number\" id=".$identifier." min=\"0\" aria-invalid=\"false\" placeholder=\"0\">
	  				</div>
	  			</div>"; 		
	  	}	
 	}
 }
 return $res;
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

	echo '<pre>'.print_r( $current_user->ID , true ).'</pre>';
	echo '<pre>'.print_r( get_user_meta($current_user->ID) , true ).'</pre>';
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
	error_log( "Form data: ");
	error_log( print_r( $form, 1 ));

	if($main_form_id  == $form->id())
	{
		// filling the DB with data from submmitted form
		$keys = array_keys($posted_data);
		
		//keys array cleanup
		$keys = array_diff($keys, array('address','order_id','client_name', 'client_point_name', 'email', 'first_name', 'last_name', 'start_date', 'end_date',  'acceptance-151', '_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag', '_wpcf7_container_post', '_wpcf7_key', '_cf7sg_toggles'));

		$wpdb->query('START TRANSACTION');
		//fill the client_order tabel with new value if applicable 
		//make pre-select


		//$posted_data['order_id']
		
		//fill the positions_order table 
		foreach ($posted_data as $key => $value) {
			if(in_array($key, $keys)) {
				$pair = explode('_', $key);
				$date = $pair[0];
				$product = $pair[1];

				$wpdb->replace("42_positions_order", array(
				"order_id" => $posted_data['order_id'],
   				"date" => $date ,
   				"position_id" => $product ,
   				"quantity" => $value ,
				));
			}
		}
		$wpdb->query('COMMIT'); 
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

function get_weekday($date){
	$date_stamp = strtotime(date('Y-m-d', strtotime($date)));
	$stamp = date('D', $date_stamp);
	return strtolower($stamp);
}

add_action( 'wp_footer', 'cf7_redirect' );
add_action( 'wp_footer', 'cf7_check_params_on_submit' );
//add_filter('wpcf7_form_name_attr', 'debug_hello', 10, 3);
// add_filter( 'wpcf7_form_tag', 'update_date_labels', 10, 2);

//add_action( 'wpcf7_before_send_mail', 'debug_filter' ); 


function cf7_redirect() {
?>
<script type="text/javascript">
document.addEventListener( 'wpcf7submit', function( event ) {
    if ( '10' == event.detail.contactFormId ) {
       location = 'http://r2d2.local/wp/thanks/';
    } 
    else if ('38' == event.detail.contactFormId ) {
		var inputs = event.detail.inputs;
		//alert(event.detail);
		console.log(event.detail);
		var orderDate='2019-01-01'; //defaut value
		var username='';
		// Ищем поле с именем order-date и злоупотребляем alert'ом при нахождении поля
		for ( var i = 0; i < inputs.length; i++ ) {
			if ( 'order-date' == inputs[i].name ) {
				orderDate = inputs[i].value; //TODO: fix the search method
				//alert( orderDate );
			}
			else if ( 'username' == inputs[i].name ) {
				username = inputs[i].value; //TODO: fix the search method
			}
		}
    	location = 'http://r2d2.local/wp/%D1%80%D0%BE%D0%B4%D0%B8%D1%82%D0%B5%D0%BB%D1%8C%D1%81%D0%BA%D0%B0%D1%8F-%D1%81%D1%82%D1%80%D0%B0%D0%BD%D0%B8%D1%86%D0%B0-%D0%B4%D0%BB%D1%8F-%D1%84%D0%BE%D1%80%D0%BC/orders/?order-date=' + orderDate + '&username='+username;
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

