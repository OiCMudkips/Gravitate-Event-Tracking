<?php

/**
 * @package Gravitate Event Tracking for Google Analytics
 */
/*
Plugin Name: Gravitate Event Tracking
Plugin URI: http://www.gravitatedesign.com
Description: This is Plugin allows you to add custom Tracking events for Google Analytics.
Version: 1.0.0
*/

/*
Here is a Description of the Plugin
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Gravitate GA Tracker.';
	exit;
}



//////////////////////////////////////////////////////////////
// Hooks and filters
//////////////////////////////////////////////////////////////

register_activation_hook( __FILE__, 'GETGA_activate' );
add_action('admin_menu', 'GETGA_create_menu');
add_action('wp_footer', 'GETGA_add_tracking_code');

//////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////




/**
 * GETGA_activate function
 * This Function Runs any Activation for the Plugin
 **/
function GETGA_activate()
{
	GETGA_check_and_create_option();
	update_option( 'GETGA_DISMISS_GA_WARNING', 0 );
}

/**
 * GETGA_presets function
 * This Function returns an array of Preset Actions to Track
 **/
function GETGA_presets()
{
	$presets = array();
	$presets[] = array(
		'selector' => 'a[href$=".pdf"], a[href$=".doc"], a[href$=".docx"], a[href$=".ods"], a[href$=".odt"], a[href$=".xls"], a[href$=".xlsx"], a[href$=".txt"], a[href$=".zip"], a[href$=".csv"]',
		'description' => 'Downloads - pdf, doc(x), xls(x), txt, zip, csv',
		'category' => 'Downloads',
		'action' => 'click',
		'action_label' => 'Downloaded',
		'label' => 'Default_{ITEM_TITLE}_{PAGE_URL}_{LINK_URL}'
		);

	$presets[] = array(
		'selector' => 'input[type=submit]',
		'description' => 'All Submit Buttons',
		'category' => 'Form Submits',
		'action' => 'click',
		'action_label' => 'Form Submitted',
		'label' => 'Form_Submitted_{TAG_HTML}_{PAGE_URL}'
		);

	return $presets;
}

/**
 * GETGA_check_and_create_option function
 * This Function will check to see if the Option has been added if not it will create it.
 **/
function GETGA_check_and_create_option()
{
	$getga_events_option = get_option('GETGA_EVENTS');
    if(empty($getga_events_option))
    {
    	$event = array();
    	$event['selector'] = '.gtrack';
    	$event['description'] = 'Generic Event Tracker';
    	$event['category'] = 'Default';
    	$event['action_type'] = 'click';
    	$event['action_label'] = 'Default Item Clicked';
    	$event['label'] = 'Default_{ITEM_TITLE}_{PAGE_URL}';
    	$event['status'] = 'active';

    	$events = array($event);

    	foreach (GETGA_presets() as $preset)
    	{
    		$event = array();
	    	$event['selector'] = htmlentities($preset['selector'], ENT_QUOTES);
	    	$event['description'] = $preset['description'];
	    	$event['category'] = $preset['category'];
	    	$event['action_type'] = $preset['action'];
	    	$event['action_label'] = $preset['action_label'];
	    	$event['label'] = $preset['label'];
	    	$event['status'] = 'active';

	    	$events[] = $event;
    	}

    	update_option( 'GETGA_EVENTS', $events );
    }
}

/**
 * GETGA_create_menu function
 * This Function Runs hook to add the Page to the Settings Menu
 **/
function GETGA_create_menu()
{
	add_submenu_page( 'options-general.php', 'Gravitate Event Tracking', 'Gravitate Event Tracking', 'manage_options', 'getga_tracking', 'GETGA_tracker_page');
}

function GETGA_save()
{
	if(!empty($_POST['save_events']) && isset($_POST['selectors']))
	{
		$events = array();

		foreach ($_POST['selectors'] as $key => $selector)
		{
			$event = array();
	    	$event['selector'] = htmlentities($selector, ENT_QUOTES);
	    	$event['description'] = $_POST['descriptions'][$key];
	    	$event['category'] = (!empty($_POST['categories'][$key]) ? $_POST['categories'][$key] : '');
	    	$event['action_type'] = (!empty($_POST['action_types'][$key]) ? $_POST['action_types'][$key] : 'click');
	    	$event['action_label'] = (!empty($_POST['action_labels'][$key]) ? $_POST['action_labels'][$key] : '');
	    	$event['label'] = (!empty($_POST['labels'][$key]) ? $_POST['labels'][$key] : '');
	    	$event['status'] = (!empty($_POST['active'][$key]) ? 'active' : '0');

	    	$events[] = $event;
		}

		if(update_option( 'GETGA_EVENTS', $events ) || serialize($events) == serialize(get_option('GETGA_EVENTS')))
		{
			return 'Your Events were saved Successfully!';
		}
	}
	return false;
}

/**
 * GETGA_tracker_page function
 * This Function Displays the HTML Content and form for the Admin Page
 **/
function GETGA_tracker_page()
{
	if(!empty($_POST['save_events']) && isset($_POST['selectors']))
	{
		$saved = GETGA_save();
	}

	if(!empty($_GET['dismiss_ga_warning']))
	{
		update_option( 'GETGA_DISMISS_GA_WARNING', 1 );
	}

	$presets = GETGA_presets();

	?>
	<div class="wrap">
		<h2>Gravitate Event Tracking for Google Analytics</h2>
		<h4 style="margin: 6px 0;">Version 1.0.0</h4>
		<br>
		This Plugin only adds the Tracking Script to your website.  It does not offer any reports.  To view the Tracking details, you will need to login to your Google Analytics account that is associated with this website.  Google Analytics Reports for Event Tracking are in real time, so you should be able to see the results immediately from your Google Analytics account.
		<br>
		<br>
		<br>
		<form method="post">
		<input type="hidden" name="save_events" value="1">
		<h3 style="margin: 6px 0;">Custom</h3>
		<table cellspacing="0" class="wp-list-table widefat plugins">
			<thead>
				<tr>
					<th class="manage-column column-cb" id="cb" scope="col">
						Active
					</th>
					<th style="" class="manage-column column-description" id="description" scope="col">
						Title / Description<br>
						<span style="font-size: 10px; color: #777;">Used only for Reference and in HTML Comments</span>
					</th>
					<th style="" class="manage-column column-name" id="name" scope="col">
						Selector / Element<br>
						<span style="font-size: 10px; color: #777;">Use CSS Class's or ID's</span>
					</th>
					<th style="" class="manage-column column-name" id="name" scope="col">
						Category<br>
						<span style="font-size: 10px; color: #777;">Google Analytics Category Label</span>
					</th>
					<th style="" class="manage-column column-name" id="name" scope="col">
						Action<br>
						<span style="font-size: 10px; color: #777;">Google Analytics Action and Action Label</span>
					</th>
					<th style="" class="manage-column column-name" id="name" scope="col">
						Label<br>
						<span style="font-size: 10px; color: #777;">Google Analytics Label <br>Tags: {ITEM_TITLE} {PAGE_URL} {LINK_URL} {IMAGE_SRC} {IMAGE_ALT} {TAG_HTML}</span>
					</th>
					<th class="manage-column" scope="col">
						&nbsp;
					</th>
				</tr>
			</thead>

			<tbody id="the-list">
				<?php
				$getga_events = get_option('GETGA_EVENTS');

				if(!empty($getga_events) && is_string($getga_events))
				{
					$getga_events = unserialize($getga_events);
				}

				if(!empty($getga_events) && is_array($getga_events))
				{
					foreach($getga_events as $key => $getga_event)
					{
						?>
						<tr class="event <?php echo $getga_event['status'];?>">

							<th style="vertical-align:middle;" class="check-column" scope="row">
								<input type="hidden" name="active[]" class="hidden_event_status" value="<?php echo $getga_event['status'];?>">
								<input style="margin: 0 11px 8px;" class="event_status" type="checkbox" value="active" name="active_input[]" <?php checked($getga_event['status'], 'active');?>>
							</th>
							<td style="vertical-align:middle;">
								<input style="width: 100%; min-width: 280px;" class="track_description" placeholder="Title / Description" type="text" value="<?php echo $getga_event['description'];?>" name="descriptions[]">
							</td>
							<td style="vertical-align:middle;">
								<input type="text" style="width: 100%; min-width: 100px;" class="track_selector" placeholder="Selector / Element" value='<?php echo stripcslashes($getga_event['selector']);?>' name="selectors[]">
							</td>
							<td style="vertical-align:middle;">
								<input type="text" style="width: 100%; min-width: 100px;" class="track_category" placeholder="Google Analytics Category" value="<?php echo $getga_event['category'];?>" name="categories[]">
							</td>
							<td style="vertical-align:middle;">
								<select name="action_types[]" class="track_action">
									<option value="click" <?php selected($getga_event['action_type'], 'click');?>>On Mouse Click</option>
									<option value="mouseover" <?php selected($getga_event['action_type'], 'mouseover');?>>On Mouse Over</option>
									<option value="keypress" <?php selected($getga_event['action_type'], 'keypress');?>>When Typing</option>
								</select>
								<input type="text" style="width: 100%; min-width: 100px;" class="track_action-label" placeholder="Action Label" value="<?php echo $getga_event['action_label'];?>" name="action_labels[]">
							</td>
							<td style="vertical-align:middle;">
								<input style="width: 100%; min-width: 160px;" class="track_label" placeholder="Google Analytics Label" type="text" value="<?php echo $getga_event['label'];?>" name="labels[]">
							</td>
							<th style="vertical-align:middle;" scope="row">
								<a class="delete button" style="border: 1px solid #ccc !important;">X</a>
							</th>
						</tr>

						<?php
					}
				}
				?>
			</tbody>
		</table>

		<p class="right">
		<h3 style="margin: 6px 0;">Add Custom Event &nbsp; &nbsp; &nbsp; &nbsp; Add Presets</h3>
		<a class="add-tracking button" data-action="click">+ Add Custom Tracking</a> &nbsp; &nbsp;
		<?php
		foreach (GETGA_presets() as $preset)
		{
			?>
			<a class="add-tracking button"
				data-selector='<?php echo $preset['selector'];?>'
				data-category="<?php echo $preset['category'];?>"
				data-action="<?php echo $preset['action'];?>"
				data-action-label="<?php echo $preset['action_label'];?>"
				data-label="<?php echo $preset['label'];?>"><?php echo $preset['description'];?>
			</a>
			<?php
		}
		?>
		<br>
		</p>
		<p class="submit"><input type="submit" value="Save Changes" class="button button-primary" id="submit" name="submit"></p>
		<br>
		<br>
		<p style="text-align:right;">Plugin Create by <a target="_blank" href="http://www.gravitatedesign.com">Gravitate</a></p>


<script type="text/javascript">
(function($){
	var new_item;
	$('.add-tracking.button').on('click', function(e){
		e.preventDefault();
		new_item = $('#the-list .event:first').clone();
		new_item.find('.track_description').val($(this).html());
		new_item.find('.track_selector').val($(this).data('selector'));
		new_item.find('.track_category').val($(this).data('category'));
		new_item.find('.track_action').val($(this).data('action'));
		new_item.find('.track_action-label').val($(this).data('action-label'));
		new_item.find('.track_label').val($(this).data('label')); //event_status
		new_item.find('.hidden_event_status').val('active');
		new_item.find('.event_status').prop('checked', 'checked');
		new_item.appendTo( "#the-list" );

		getga_update_listeners();
	});

	getga_update_listeners();

})(jQuery)

function getga_update_listeners()
{
	jQuery('.event_status').on('click', function(e){
		if(this.checked)
		{
			jQuery(this).prev().val('active');
		}
		else
		{
			jQuery(this).prev().val('0');
		}
	});

	jQuery('.delete.button').on('click', function(e){
		jQuery(this).parent().parent().remove();
	});
}
</script>

		<?php

		if(!get_option('GETGA_DISMISS_GA_WARNING'))
		{
			$default_socket_timeout = ini_get('default_socket_timeout');
			if(!empty($default_socket_timeout))
			{
				ini_set('default_socket_timeout', 5);
			}

			// Check for Google Analytics
			$home_page_content = file_get_contents(site_url());

			if(!empty($default_socket_timeout))
			{
				ini_set('default_socket_timeout', $default_socket_timeout);
			}

			if(!empty($home_page_content) && strpos($home_page_content, '</body>') && strpos($home_page_content, 'UA-') === false && strpos($home_page_content, 'google-analytics.com') === false)
			{
				?>
				<div class="error"><p>Your Website does not seem to have Google Analytics Installed.  We can't find it in your Home Page HTML.  Without Google Analytics installed this Plugin wont do anything. If this message is an error and Google Analytics is installed Properly then just Dismiss it. &nbsp; &nbsp; &nbsp; - <a href="?page=<?php echo $_GET['page'];?>&amp;dismiss_ga_warning=true">Dismiss</a></p></div>
				<?php
			}
		}
		?>

		<?php if(!empty($saved)){ echo '<div class="updated"><p>' . $saved . '</p></div>'; } ?>
		<?php if(isset($saved) && !$saved){ echo '<div class="error"><p>Error Saving your Events. Please try again.</p></div>'; } ?>
		</form>
    </div>
	<?php
}

/**
 * GETGA_add_tracking_code function
 * This Function adds the Javascript and jQuery Code to the Footer on the Front End for Every page.
 **/
function GETGA_add_tracking_code()
{

	$getga_events = get_option('GETGA_EVENTS');

	if(!empty($getga_events) && is_string($getga_events))
	{
		$getga_events = unserialize($getga_events);
	}

	if(!empty($getga_events) && is_array($getga_events))
	{
	?>

<script type="text/javascript">
/**************************************************************\
****************   Gravitate Event Tracking   ******************
\**************************************************************/



function getga_has_universal_event_tracking()
{
	var scripts = document.getElementsByTagName('script');

	for (var i = scripts.length; i--;)
	{
	    if (scripts[i].src.indexOf(String.fromCharCode(103,111,111,103,108,101,45,97,110,97,108,121,116,105,99,115,46,99,111,109,47,97,110,97,108,121,116,105,99,115,46,106,115)) > 0)
	    {
	    	return true;
	    }
	}
}

function getga_add_event_tracking()
{
	var has_universal_event_tracking = getga_has_universal_event_tracking();

	if (typeof(_gaq) !== 'undefined' || has_universal_event_tracking) // Check if Google Analytics is Loaded
	{
		if (typeof jQuery !== 'undefined') // Check if jQuery is Loaded
		{
			(function($){

			<?php
			foreach($getga_events as $getga_event)
			{
				if(!empty($getga_event['status']) && $getga_event['status'] == 'active')
				{
					$tags = array();
					$tags['{ITEM_TITLE}'] = "'+$(this).attr('title')+'";
					$tags['{PAGE_URL}'] = "'+location.href+'";
					$tags['{LINK_URL}'] = "'+$(this).attr('href')+'";
					$tags['{IMAGE_SRC}'] = "'+$(this).attr('src')+'";
					$tags['{IMAGE_ALT}'] = "'+$(this).attr('alt')+'";
					$tags['{TAG_HTML}'] = "'+$(this).html().replace(/(<([^>]+)>)/ig,'').replace(/(\\r\\n|\\n|\\r)/gm,'').replace(/\s{2,}/g, ' ')+'";

					foreach($tags as $tag => $value)
					{
						$getga_event['label'] = str_ireplace($tag, $value, $getga_event['label']);
					}

					?>

					/* <?php echo $getga_event['description'];?> */
					$('<?php echo str_replace('\\"', '"', html_entity_decode($getga_event['selector'], ENT_QUOTES));?>').on('<?php echo trim($getga_event['action_type']);?>', function(e){
						if(has_universal_event_tracking)
						{
							ga('send', '<?php echo trim($getga_event['action_type']);?>', '<?php echo $getga_event['category'];?>', '<?php echo $getga_event['action_label'];?>', '<?php echo $getga_event['label'];?>');
						}
						else
						{
							_gaq.push(['_trackEvent', '<?php echo $getga_event['category'];?>', '<?php echo $getga_event['action_label'];?>', '<?php echo $getga_event['label'];?>']);
						}
					});

					<?php
				}
			}
			?>

			})(jQuery)
		}
	}
}

getga_add_event_tracking();

/**************************************************************\
**************   End Gravitate Event Tracking   ****************
\**************************************************************/
</script>

<?php
	}
}
