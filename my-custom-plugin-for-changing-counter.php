<?php 
/**
* Plugin Name: My Custom Plugin For Changin Counter
* Plugin URI: https://github.com/tjthouhid/
* Description: This is a plugin for Changing Counter of theme using soon counter plugin.
* Version: 1.0.1
* Author: Tj Thouhid
* Author URI: https://www.tjthouhid.com
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
function new_shortcode_timeleft( $atts, $content = null ) { global $wpdb, $userdata, $CORE, $post, $shortcode_tags; $STRING = ""; $strTxt = "";
	
	extract( shortcode_atts( array('postid' => "", "layout" => "", "text_before" => "", "text_ended" => "", "key" => "listing_expiry_date" ), $atts ) );
	
	// SETUP ID FOR CUSTOM DISPLAY	
	$milliseconds = str_replace("+","",round(microtime(true) * 100)); $milliseconds .= rand( 0, 10000 );
	 
	// CHECK FOR CUSTOM POST ID
	if($postid == ""){ $postid = $post->ID; }
	
	// GET VALUE FROM LISTING
	$expiry_date = get_post_meta($postid,$key,true);		 
	if($expiry_date == "" || strlen($expiry_date) < 3){ 
			
			// EXPIRED DISPLAY HERE		 
			if(defined('IS_MOBILEVIEW')){
				return "<span class='aetxt'>".$CORE->_e(array('auction','3'))."</span>";
			}
			
			// GET THE LISTING DATA
			$str = "";
			$expiry_date = get_post_meta($post->ID,'listing_expiry_date',true);
			$current_bidding_data = get_post_meta($post->ID,'current_bid_data',true);
			$reserve_price = get_post_meta($post->ID,'price_reserve',true);
			$price_current = get_post_meta($post->ID,'price_current',true);	
			
			
			if(!is_array($current_bidding_data)){ $current_bidding_data = array(); }
			krsort($current_bidding_data);
			$checkme = current($current_bidding_data);
						
			// AUCTION HAS ENDED				
			if($expiry_date == "" || strtotime($expiry_date) < strtotime(current_time( 'mysql' ))){
			
				if(is_numeric($reserve_price) && $reserve_price != "0" && $price_current < $reserve_price){
				
					$strTxt  .= $CORE->_e(array('auction','1'));
				
				}elseif(isset($checkme['username']) ){
						 
					$strTxt  .= "".$checkme['username'].$CORE->_e(array('auction','2'));
					
				}
				
				if(isset($strTxt) && strlen($strTxt) > 1){
				
					$str  .=  "<span>".$strTxt."</span>";
					
				}
			
			}
			
			return "<div class='ea_finished'><span class='aetxt'>".$CORE->_e(array('auction','3'))."</span> ".$str."</div>";
					 
	} // END EXPIRY DATE DISPLAY
	
	// SWITCH LAYOUTS
	switch($layout){
		case "1": { $layout_code = ",layout: '".$text_before." {sn} {sl}, {mn} {ml}, {hn} {hl}, and {dn} {dl}',"; } break;
		case "2": { $layout_code = ",compact: true, "; } break;
		default: { $layout_code = ""; } break;
	} 
	if(strlen($expiry_date) == 10){ $expiry_date = $expiry_date." 00:00:00"; }
	
	// REFRESH PAGE EXTR
	$run_extra =  ""; $run_extrab  = "";

	// DISPLAY AFTER FINISHED
	if(isset($GLOBALS['flag-single'])){ 
	
	$run_extra = "location.reload(); jQuery('#auctionbidform').hide();"; 
	
	}else{
	
	$run_extra = "jQuery('#timeleft_".$postid.$milliseconds."_wrap').html('<div class=ea_finished><span class=aetxt>".$CORE->_e(array('auction','3'))."</span></div>');"; 
	
	}
	//return date('Y-m-d H:i:s');
	if(strtotime($expiry_date) < strtotime(current_time( 'mysql' ))){
		return '<div class="soon" id="my-soon-counter-11" data-due="2017-12-13 21:57:17" data-event-complete="soonCompleteCallback_11" data-layout="group label-uppercase" data-format="d,h,m,s" data-face="flip color-light shadow-soft fast corners-round" data-initialized="true" data-scale="m"><span class="soon-group " data-value="1000">'.$CORE->_e(array('auction','3')).'</span></div>';
		 
	}else{
		$dt = new DateTime($expiry_date);

		$date = $dt->format('Y-m-d');
		$time = $dt->format('H:i:s');
		//2017-12-14T23:00:07
		$expp= $date. 'T'. $time;
		//return $expp;
		
		return do_shortcode('[soon name="new_soon" due="'.$expp.'"][/soon]');
	}
	
	// BUILD DISPLAY
	$STRING = "<span id='timeleft_".$postid.$milliseconds."_wrap'><span id='timeleft_".$postid.$milliseconds."'></span></span>";
	
	// FORE EXPIRY IF ALREADY EXPIRED
	if(strtotime($expiry_date) < strtotime(current_time( 'mysql' )) ) {
	
	$STRING .= "<script> jQuery(document).ready(function(){ CoreDo('". str_replace("https://","",str_replace("http://","",get_home_url()))."/?core_aj=1&action=validateexpiry&pid=".$postid."', 'timeleft_".$postid.$milliseconds."'); });</script> ";
	
	}
	
	$STRING .= "<script>
		
		jQuery(document).ready(function() {		
		var dateStr ='".$expiry_date."'
		var a=dateStr.split(' ');
		var d=a[0].split('-');
		var t=a[1].split(':');
		var date1 = new Date(d[0],(d[1]-1),d[2],t[0],t[1],t[2]);			 
		jQuery('#timeleft_".$postid.$milliseconds."').countdown({timezone: ".get_option('gmt_offset').", until: date1, onExpiry: WLTvalidateexpiry".$postid."".$layout_code." });
		});
		
		function WLTvalidateexpiry".$postid."(){ ".$run_extrab." setTimeout(function(){ CoreDo('". str_replace("https://","",str_replace("http://","",get_home_url()))."/?core_aj=1&action=validateexpiry&pid=".$postid."', 'timeleft_".$postid.$milliseconds."'); ".$run_extra." }, 1000);  };
		
		</script>";	 
		
		return $milliseconds;
			//testing tj	
		
		return $STRING;
}

add_action( 'init', 'remove_my_shortcodes',20 );
function remove_my_shortcodes() {
    remove_shortcode( 'BIDDINGTIMELEFT');
    add_shortcode( 'BIDDINGTIMELEFT', 'new_shortcode_timeleft' );
}

//