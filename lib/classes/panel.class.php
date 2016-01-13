<?php
/** 
* NATURAL - Copyright Open Source Mind, LLC 
* Last Modified: Date: 05-06-2014 17:23:02 -0500  $ @ Revision: $Rev: 11 $ 
* @package Natural Framework 
*/

	/**
	 *  This functions build the output for the panel in order to standardize 
	 */
	class Panel {
		function buildPanel($left = '', $right = '', $msg = ''){
			$output  = '<div id="panel-wrapper">';
			$output .= '<div id="panel-msg">'.$msg.'</div>';
			if ($left != '') {
			  $output .= '<div id="panel-left">'.$left.'</div>';			  	
			}
			else {
				$class = ' full';
			}
			$output .= '<div id="panel-right" class="content'.$class.'">'.$right.'</div>';
			$output .= '</div>';
			$output .= '<a id="panel-close">Close</a>';
			return $output;
		}
	}
?>
