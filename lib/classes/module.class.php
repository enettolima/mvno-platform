<?php
/**
 * All methods in this class are protected
 * @access protected
 */
class Module {
    /**
    * Method to create a new module
    *
    * Add a new module
    *
    * @url POST create
    * @smart-auto-routing false
    * @access public
    */
    function create($request_data) {
      //Validating data from the API call
      $this->_validate($request_data, "insert");
      $db = DataConnection::readWrite();
      foreach ($request_data as $key => $value) {
        if ($key != "key") {
          $data[$key] = $value;
        }
      }
      $result = $db->module()->insert($data);
      if ($result) {
        //Preparing response
        $response = array();
        $response['code']    = 201;
        $response['message'] = 'Module has been created!';
        $response['id']      = $result['id'];
        natural_set_message($response['message'], 'success');
        $this->_updateComposer();
        return $response;
      } else {
        $error_message = 'Module could not be created!';
        natural_set_message($error_message, 'error');
        throw new Luracast\Restler\RestException(500, $error_message);
      }
    }

  /**
  * Method to fecth Book Record by ID
  *
  * Fech a record for a specific module
  * by ID
  *
  * @url GET byID/{id}
  * @smart-auto-routing false
  * 
  * @access public
  * @throws 404 User not found for requested id  
  * @param int $id Module to be fetched
  * @return mixed 
  */
  function byID($id) {
    //If id is null
    if (is_null($id)) {
      $error_message = 'Parameter id is missing or invalid!';
      natural_set_message($error_message, 'error');
      throw new Luracast\Restler\RestException(400, $error_message);
    }
    //Get object by id
    //$this->loadSingle("id='{$id}'");
    $db = DataConnection::readOnly();
    $q = $db->module[$id];
    //If object not found throw an error
    if(count($q) > 0) {
      $result['code'] = 200;
      foreach($q as $key => $value){
        $result[$key] = $value;
        $this->$key = $value;
      }
      $this->affected 		 = 1;
      return $result;
    }else{
      $error_message = 'Module not found!';
      natural_set_message($error_message, 'error');
      throw new Luracast\Restler\RestException(404, $error_message);
    }
  }

  /**
  * Method to fecth Book Record by Module Name
  *
  * Fech a record for a specific module
  * by ID
  *
  * @url GET byName/{$module_name}
  * @smart-auto-routing false
  * 
  * @access public
  * @throws 404 User not found for requested id  
  * @param int $id Module to be fetched
  * @return mixed 
  */
  function byName($module_name=null) {
    //If id is null
    if (is_null($module_name)) {
      $error_message = 'Parameter name is missing or invalid!';
      natural_set_message($error_message, 'error');
      throw new Luracast\Restler\RestException(400, $error_message);
    }
    //Get object by id
    //$this->loadSingle("id='{$id}'");
    $db = DataConnection::readOnly();
    //$q = $db->module->where("module",$module_name)->fetch();
    
    $q = $db->module()
    ->select("*")
    ->where("module", $module_name)
    ->limit(1);
    //if($this->affected>0){
    //if(count($books)){
    //  $error[] = 'This book name already exists!';
    //}
    //If object not found throw an error
    if(count($q) > 0) {
      $result['code'] = 200;
      foreach($q as $key => $value){
        $result[$key] = $value;
        $this->$key = $value;
      }
      $this->affected 		 = 1;
      return $result;
    }else{
      $result['code'] = 404;
      $this->affected = 0;
      return $result;
      //$error_message = 'Module not found!';
      //natural_set_message($error_message, 'error');
      //throw new Luracast\Restler\RestException(404, $error_message);
    }
  }
  /**
  * Method to fecth All Books
  *
  * Fech all records from the database
  *
  * @url GET fetchAll
  * @smart-auto-routing false
  * 
  * @access public
  * @throws 404 Module not found
  * @return mixed 
  */
  function fetchAll() {
    $db = DataConnection::readOnly();
    $q = $db->module();
    if(count($q) > 0) {
      foreach($q as $id => $q){
        if(count($columns)<1){
          $columns = $db->module[$q['id']];
        }
        //setting response for api calls
        foreach($columns as $k => $v){
          $res[$id][$k] = $q[$k];
        }
      }
      return $res;
    }else{
      throw new Luracast\Restler\RestException(404, 'Book not found');
    }
  }

  /**
  * Method to Update module information
  *
  * Update module on database
  *
  * @url PUT update
  * @smart-auto-routing false
  * 
  * @access public
  * @return mixed 
  */
  function update($request_data) {
    $this->_validate($request_data, "update");
    $response = array();
    $db = DataConnection::readWrite();
    $id = $request_data['id'];
    $q  = $db->module[$id];
    unset($request_data['fn']);
    foreach ($request_data as $key => $value) {
      $this->$key = $value;
    }
    
    if($q){
      if($q->update($request_data)){
        $response['code'] = 200;
        $response['message'] = 'Module has been updated!';
        natural_set_message($response['message'], 'success');
      }else{
        //Could not update record! maybe the data is the same.
        $response['code'] = 500;
        $response['message'] = 'Could not update Module at this time!';
        natural_set_message($response['message'], 'error');
        throw new Luracast\Restler\RestException($response['code'], $response['message']);
      }
      return $response;
    }else{
      throw new Luracast\Restler\RestException(404, 'Module not found');
    }
  }

  /**
  * Method to delete a module
  *
  * Delete module from database
  *
  * @url DELETE delete
  * @smart-auto-routing false
  *
  * @access public
  * @throws 404 Module not found
  * @return mixed 
  */
  function delete($id) {
    $data['id'] = $id;
    $this->_validate($data, "delete");
    $db = DataConnection::readWrite();
    $q = $db->module[$id];
    
    $response = array();
    if($q && $q->delete()){
      $response['code'] = 200;
      $response['message'] = 'Module has been removed!';
      natural_set_message($response['message'], 'success');
      $this->_updateComposer();
      return $response;
    }else{
      $response['code'] = 404;
      $response['message'] = 'Module not found!';
      natural_set_message($response['message'], 'error');
      throw new Luracast\Restler\RestException($response['code'], $response['message']);
      return $response;
    }
  }
    
  /**
  * @smart-auto-routing false
  * @access private
  */
  function _validate($data, $type, $from_api = true) {
    //If the method called is an update, check if the id exists, otherwise return error
    if ($type == "update" || $type == "delete") {
      if (!$data['id']) {
        throw new Luracast\Restler\RestException(404, 'Parameter ID is required!');
      }
    }
    /*
    * check if field is empty
    * Add more fields as needed
    */
    if ($type != "delete") {
      if (!$data['label']) {
        $error[] = 'Label is required!';
      }
    }
    //If error exists return or throw exception if the call has been made from the API
    if (!empty($error)) {
      natural_set_message($error[0], 'error');
      if ($from_api) {
        throw new Luracast\Restler\RestException($error_code, $error[0]);
      }
      return $error;
      exit(0);
    }
  }

  /**
  * @smart-auto-routing false
  *@access private
  */
  function _updateComposer(){
    $result = 'Composer Update: '. (exec("composer update -o &") || 'Finished');
    natural_set_message($result, 'success');
  }
}
?>
