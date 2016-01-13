<?php
/**
* NATURAL - Copyright Open Source Mind, LLC
* Last Modified: Date: 05-06-2014 17:23:02 -0500  $ @ Revision: $Rev: 11 $
* @package Natural Framework
*/

/**
 * Responsible for the List View UI
 */
class ListView {

  /**
   * Method build Just a helper module that invokes the twig table template.
   */
  public function build($rows, $headers = NULL, $options = array()) {

    global $twig;

    $render = array(
      'rows' => $rows,
      'headers' => $headers,
      'show_headers' => isset($options['show_headers']) ? $options['show_headers'] : TRUE,
      'page_title' => isset($options['page_title']) ? $options['page_title'] : '',
      'page_subtitle' => isset($options['page_subtitle']) ? $options['page_subtitle'] : '',
      'empty_message' => isset($options['empty_message']) ? $options['empty_message'] : '',
			'table_prefix' => isset($options['table_prefix']) ? $options['table_prefix'] : '',
      'pager_items' => isset($options['pager_items']) ? $options['pager_items'] : '',
			'page' => isset($options['page']) ? $options['page'] : 1,
			'sort' => isset($options['sort']) ? $options['sort'] : '',
      'search' => isset($options['search']) ? $options['search'] : '',
			'show_search' => isset($options['show_search']) ? $options['show_search'] : TRUE,
      'function' => isset($options['function']) ? $options['function'] : '',
      'module' => isset($options['module']) ? $options['module'] : '',
      'update_row_id' => isset($options['update_row_id']) ? $options['update_row_id'] : '',
      'table_form_id' => isset($options['table_form_id']) ? $options['table_form_id'] : '',
      'table_form_process' => isset($options['table_form_process']) ? $options['table_form_process'] : '',
    );

    $template = $twig->loadTemplate('table.html');
    $template->display($render);
  }
}

/**
 * This function builds a pager based on given parameters.
 */
function build_pager($function, $module, $pager_total, $limit, $page = 1, $pager_length = 10) {
  $quantity = ceil($pager_total / $limit);

  // Links before current page.
  for ($i = 1; $i <= $quantity; $i++) {
    // theme_link_process_information($text, $formname, $func, $module, $options = array())
    // <a href="javascript:proccess_information('user_list_pager', 'user_list_pager', 'user', null, 'pager_current|1', null, null);">2</a>
    $items[$i]['link'] = theme_link_process_information($i, $function . '_table_info', $function . '_pager', $module, array('extra_value' => 'page|' . $i));
    $items[$i]['class'] = 'pager-item';
    if ($i == $page) {
      $items[$i]['class'] = 'active';
    }
  }

  if (!empty($items)) {
    return $items;
  }
  else {
    return FALSE;
  }
}


/**
 * Build Sort Header.
 */
function build_sort_header($function, $module, $fields, $sort) {

  $line = array();

  if ($fields) {
    $i = 0;
    foreach ($fields as $field) {
      if ($field['field']) {
        $field['display'] = translate($field['display'], $_SESSION['log_preferred_language']);
        if ($sort == $field['field'] . ' ASC') {
          $line[$i]['display'] = theme_link_process_information($field['display'], $function . '_table_info', $function . '_sort', $module, array('extra_value' => 'sort|' . $field['field'] . ' DESC'));
          $line[$i]['class'] = 'sorting_asc';
        }
        elseif ($sort == $field['field'] . ' DESC') {
          $line[$i]['display'] = theme_link_process_information($field['display'], $function . '_table_info', $function . '_sort', $module, array('extra_value' => 'sort|' . $field['field'] . ' ASC'));
          $line[$i]['class'] = 'sorting_desc';
        }
        else {
          $line[$i]['display'] = theme_link_process_information($field['display'], $function . '_table_info', $function . '_sort', $module, array('extra_value' => 'sort|' . $field['field'] . ' ASC'));
          $line[$i]['class'] = 'sorting';
        }
      }
      else {
        $line[$i]['display'] = $field['display'];
      }
      $i++;
    }
  }
  return $line;
}

/**
 * Search build.
 */
function build_search_query($query, $search_fields, $exceptions = NULL) {
  if ($search_fields) {
    foreach ($search_fields as $field) {
      if ($exceptions[$field]) {
        if (is_array($exceptions[$field])) {
          foreach ($exceptions[$field] as $key => $value) {
            $pos = strpos(strtolower($value), strtolower($query));
            if ($pos !== FALSE) {
              $query_fields[] = "$field LIKE '%$key%'";
            }
          }
        }
        elseif ($exceptions[$field] == 'date') {

          // This is just for visual compatibility when you print a date in your listiview colunm like 02/27/2010.
          // It's going to transfrom the content from the search box to 2010-02-27 but if there is no '/' then you could type values in the search box like 2010-02 or 2010-02-27 and is going to work too.
          if (strstr($query, '/')) {
            $date = str_replace('/', '-', $query);
            if (strlen($query) == 10) {
              $date = substr($query, 6, 4) . '-' . substr($query, 0, 2) . '-' . substr($query, 3, 2);
            }
            if (strlen($query) == 7) {
              $date = substr($query, 3, 4) . '-%-' . substr($query, 0, 2);
            }
            $query_fields[] = "$field LIKE '%$date%'";
          }
          else {
            $query_fields[] = "$field LIKE '%$query%'";
          }
        }
      }
      else {
        $query_fields[] = "$field LIKE '%$query%'";
      }
    }
    $query = ' (' . implode(' OR ', $query_fields) . ')';
  }
  return $query;
}

?>
