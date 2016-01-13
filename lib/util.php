<?php

function generate_random_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890') {
    $chars_length = (strlen($chars) - 1);
    $string = $chars{rand(0, $chars_length)};
    for ($i = 1; $i < $length; $i = strlen($string)) {
        $r = $chars{rand(0, $chars_length)};
        if ($r != $string{$i - 1})
            $string .= $r;
    }
    return $string;
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * This separates records by user, group and admin
 */
function set_permission_clause($level = NULL, $user_id = NULL, $table_alias = NULL, $user_table = 0, $public_test = 0) {
    $clause = '';
    if (!$level) {
        $level = $_SESSION['log_access_level'];
    }
    if (!$user_id) {
        $user_id = $_SESSION['log_id'];
    }
    if ($user_table) {
        $clause = $table_alias . 'id = ' . $user_id;
    } else {
        $clause = $table_alias . 'user_id = ' . $user_id;
    }
    return $clause;
}

/**
 * Transform seconds in hms
 */
function sec2hms($sec) {
    // holds formatted string
    $hms = "";
    $hours = intval(intval($sec) / 3600);
    $hms .= $hours . ':';
    $minutes = intval(($sec / 60) % 60);
    $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ':';
    $seconds = intval($sec % 60);
    $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);
    return $hms;
}

/**
 * Convert all strings to a different language
 */
function translate($string, $lang = 'en') {
	
		if($lang == 'en' || $lang == null){
			/**
				* Language is either default or missing so no translation
				* is necessary :D
			 */
			return $string;	
		}

	  $db = DataConnection::readOnly();
		$lg = $db->language()
							->where('original', $string)
							->and('lang', $lang)
							->fetch();
    if ($lg) {
        return $lg['translate'];
    } else {
        return $string;
    }
}

/**
 * Set messages to the Session. - Session based
 */
function natural_set_message($msg, $type = 'status') {
  $_SESSION['messages'][] = array('type' => $type, 'msg' => $msg);
  return isset($_SESSION['messages']) ? $_SESSION['messages'] : NULL;
}

/**
 * Builds the process_information link with its parameters
 * process_information(formname, func, module, ask_confirm, extra_value, error_el, response_el, response_type, request_type, parent, el, proc_message, timer)
 */
function theme_link_process_information($text, $formname, $func, $module, $options = array()) {

  // process_information options.
  $process_information_options = array(
    'ask_confirm',
    'extra_value',
    'error_el',
    'response_el',
    'response_type',
    'request_type',
    'parent',
    'el',
    'proc_message',
    'timer',
  );

  $render_options = '';

  // Add an icon.
  /*if (!empty($options['icon'])) {
    $text = '<i class="' . $options['icon'] . '">' . $text . '</i>';
  }*/
	if(isset($options['icon'])){
		$text = '<i class="' . $options['icon'] . '">' . $text . '</i>';
	}

  // Set the javascript properties for the function.
  foreach($process_information_options as $key) {
    if (array_key_exists($key, $options)) {
      if ($options[$key] == 'this') {
        $render_options[] = $options[$key];
      }
      else {
        $render_options[] = "'" . $options[$key] . "'";
      }
    }
    else {
      $render_options[] = 'null';
    }
  }

  $href = "javascript:process_information('" . $formname . "', '" . $func . "', '" . $module . "', " .  implode(', ', $render_options) . ")";

  if ($options['class'] == 'disabled') {
    $href = '#';
  }
  
  return '<a class="' . $options['class'] . '" href="' . $href . '">' . $text . '</a>';
}

function print_debug($val) {
    echo "<pre>";
    if (is_array($val)) {
        print_r($val);
    } else {
        if (is_object($val)) {
            print_r($val);
        } else {
            echo str_replace("\n", "<br>", $val);
        }
    }
    echo "</pre>";
}

function format_us_phone($phone) {
    return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
}

function isValidUsNumber($number) {
    $validation = true;
    if (!is_numeric($number)) {
        $validation = false;
    }
    if (strlen($number) != 10) {
        $validation = false;
    }
    if (substr($number, 0, 1) < 2) {
        $validation = false;
    }
    return $validation;
}

?>
