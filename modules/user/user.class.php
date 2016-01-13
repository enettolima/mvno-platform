<?php

class User {
	/**
	 * Method to fetch Authenticated user
	 *
	 * Fech a record for a specific authenticated user
	 * by Username and password
	 *
	 * @url GET authenticate/{username}/{password}
	 * @url POST authenticate
	 * @smart-auto-routing false
	 *
	 * @access public
	 * @throws 403 User cannot be authenticated
	 * @param string $username User to be fetched
	 * @param string $password Authentication Password
	 * @return mixed
	 */

	public function authenticate($username,$password, $api_call=false) {
	  $db = DataConnection::readOnly();
	  $user = $db->user()
    		->where("username", $username)
				->and("status > ?", 0)
				->limit(1)
				->fetch();

		if(count($user)>0){
			//Authenticating password
			$pwHasher = new Phpass\PasswordHash(8,false);
			$passed = $pwHasher->CheckPassword($password, $user['password']);

			if($passed){
				$res = array();
				foreach ($user as $field => $value){
					if($field != "password"){
						$res[$field] = $value;
					}
					$this->{$field} = $value ;
				}
				$res['granted'] = true;
				$this->granted  = true;

				return $res;
			}else{
				$this->granted = false;
				if($api_call){
					throw new Luracast\Restler\RestException(403, 'Unable to authenticate user');
				}
			}
	  }else{
			$this->granted = false;
			if($api_call){
				throw new Luracast\Restler\RestException(403, 'Unable to authenticate user');
			}
	  }
	}

	/**
	 * Method to fecth all User Records
	 *
	 * Fech a record for all  Natural users
	 *
	 * @url GET fetchAll
	 * @smart-auto-routing false
	 *
	 * @access public
	 * @throws 404 User not found for requested user id
	 * @return mixed
	 */

	public function fetchAll() {
		$db = DataConnection::readOnly();
		$q = $db->user();

		if(count($q) > 0) {
			/*foreach($users as $id => $u){
				//setting response for api calls
				$res[$id]= array( 'id'			 => $u['id'],
											'file_id'      => $u['file_id'],
										  'first_name'   => $u['first_name'],
											'last_name'    => $u['last_name'],
											'username'     => $u['username'],
											'email'        => $u['email'],
											'access_level' => $u['access_level'],
											'status'       => $u['status'],
											'language'     => $u['preferred_language'],
											'dashboard'    => unserialize($u['dashboard']));
			}
			return $res;
			*/
			foreach($q as $id => $val){
				//Doing this loop to make sure we unserialize the dashboard widget fields
				foreach($val as $k => $v){
					if($k=="dashboard_1" || $k=="dashboard_2"){
						$v = unserialize($v);
					}
					$arr[$k] = $v;
				}
				$res[$id] = $arr;
			}
      return $res;
		}else{
		   throw new Luracast\Restler\RestException(404, 'User not found');
		}
	}


	/**
	 * Method to fecth User Record by database Id
	 *
	 * Fech a record for a specific Natural user
	 * by database Id
	 *
	 * @url GET byID/{id}
	 * @smart-auto-routing false
	 *
	 * @access public
	 * @throws 404 User not found for requested user id
	 * @param string $userid User to be fetched
	 * @return mixed
	 */

	public function byID($id) {
		$db = DataConnection::readOnly();
		$u = $db->user[$id];

		if(count($u) > 0) {
				//setting object properties for in app use
				$this->id 					 = $u['id'];
				$this->file_id       = $u['file_id'];
				$this->first_name    = $u['first_name'];
				$this->last_name     = $u['last_name'];
				$this->username      = $u['username'];
				$this->email         = $u['email'];
				$this->access_level  = $u['access_level'];
				$this->status        = $u['status'];
				$this->language      = $u['preferred_language'];
				$this->dashboard     = json_decode(unserialize($u['dashboard']), true);
				$this->affected 		 = 1;

				//setting response for api calls
				$res = array( 'id'					 => $u['id'],
											'file_id'      => $u['file_id'],
										  'first_name'   => $u['first_name'],
											'last_name'    => $u['last_name'],
											'username'     => $u['username'],
											'email'        => $u['email'],
											'access_level' => $u['access_level'],
											'status'       => $u['status'],
											'language'     => $u['preferred_language'],
											'dashboard'    => json_decode(unserialize($u['dashboard']), true));
			return $res;
		}else{
		   throw new Luracast\Restler\RestException(404, 'User not found');
		}
	}

	/**
	 * Method to fecth User Record by Username
	 *
	 * Fech a record for a specific Natural user
	 * by Username
	 *
	 * @url GET byUsername/{username}
	 * @smart-auto-routing false
	 *
	 * @access public
	 * @throws 404 User not found for requested username
	 * @param string $username User to be fetched
	 * @return mixed
	 */

	public function byUsername($username) {
		$db = DataConnection::readOnly();
		$u = $db->user()
			->where("username", $username)
			->limit(1)
			->fetch();

		if(count($u) > 0) {
				//setting object properties for in app use
				$this->id 					 = $u['id'];
				$this->file_id       = $u['file_id'];
				$this->first_name    = $u['first_name'];
				$this->last_name     = $u['last_name'];
				$this->username      = $u['username'];
				$this->email         = $u['email'];
				$this->access_level  = $u['access_level'];
				$this->status        = $u['status'];
				$this->language      = $u['preferred_language'];
				$this->dashboard     = unserialize($u['dashboard']);

				//setting response for api calls
				$res = array( 'id'					 => $u['id'],
											'file_id'      => $u['file_id'],
										  'first_name'   => $u['first_name'],
											'last_name'    => $u['last_name'],
											'username'     => $u['username'],
											'email'        => $u['email'],
											'access_level' => $u['access_level'],
											'status'       => $u['status'],
											'language'     => $u['preferred_language'],
											'dashboard'    => unserialize($u['dashboard']));
			return $res;
		}else{
		   throw new Luracast\Restler\RestException(404, 'User not found');
		}
	}

	/**
	 * Method to create a  User Record
	 *
	 * Create a new User
	 *
	 * @url POST create
	 * @smart-auto-routing false
	 *
	 * @access public
	 * @return mixed
	 */
	public function create($show_password=false, $auto_gen_pass=true, $data = null){

			if($auto_gen_pass){
				$temp_password = generate_random_str(6, 'abcdefghijklmnopqrstuvwxyz1234567890');
			}else{
				$temp_password = $this->password;
			}

			$hasher = new Phpass\PasswordHash(8,false);
			$hashed_pass = $hasher->HashPassword($temp_password);


			$db = DataConnection::readWrite();
			$u = $db->user();

			if($data == null) {
				$data = array(  'file_id'      => $this->file_id,
											  'first_name'   => $this->first_name,
												'last_name'    => $this->last_name,
												'username'     => $this->username,
												'email'        => $this->email,
												'access_level' => $this->access_level,
												'status'       => $this->status,
												'language'     => $this->preferred_language,
												'dashboard'    => serialize($this->dashboard));
			}else{
				$data['dashboard']  = serialize($data['dashboard']);
			}

			if(!isset($data['status'])){
					$data['status'] = 1;
			}

			if(!isset($data['language'])){
					$data['language'] = 'en';
			}

			$data['password'] = $hashed_pass;
      $data['access_level'] = '51';
			unset($data['password2']);
			unset($data['fn']);
			//print_debug($data);
			$result = $u->insert($data);
			foreach($data as $key => $value){
				$this->{$key} = $value;
			}

			$this->id = $result['id'];
			if($show_password){
				$this->temp_password = $hashed_password;
			}
			return $this;
    }

	/**
	 * Method to update a User Record
	 *
	 * Update an User record
	 *
	 * @url PUT update
	 * @smart-auto-routing false
	 *
	 * @access public
	 * @return mixed
	 */
	public function update($id){
			$response = array();
			$db = DataConnection::readWrite();
			$u = $db->user[$id];
			if($u){
				$data = array('file_id'      => $this->file_id,
				'first_name'   => $this->first_name,
				'last_name'    => $this->last_name,
				'username'     => $this->username,
				'email'        => $this->email,
				'access_level' => $this->access_level,
				'status'       => $this->status,
				'language'     => $this->preferred_language,
				'dashboard'    => serialize($this->dashboard));
				if($u->update($data)){
					$response['code'] = 200;
					$response['message'] = 'User has been updated!';
				}else{
					$response['code'] = 500;
					$response['message'] = 'Could not update User at this time!';
				}
				return $response;
			}else{
				throw new Luracast\Restler\RestException(404, 'User not found');
			}
    }

	/**
    * Method to delete a user
    *
    * Delete user from database
    *
    * @url DELETE delete
    * @smart-auto-routing false
    *
    * @access public
    * @throws 404 User not found
    * @return mixed
    */
	public function delete($id){
		$this->affected 		 = 0;
		$db = DataConnection::readWrite();
		$u = $db->user[$id];
		if($u && $u->delete()){
			//print_debug('User deleted succesfuly');
			$this->affected 		 = 1;
			$response = array();
			$response['code'] = 200;
			$response['message'] = 'User has been removed!';
			return $response;
		}else{
			throw new Luracast\Restler\RestException(404, 'User not found');
		}
	}

	/**
	* @smart-auto-routing false
	* @access private
	*/
	public function updateUserStatus($status,$user_id){
		$db = DataConnection::readWrite();
		$u = $db->user[$user_id];
		if($u){
			$data = array ('status' => $status);
			$affected = $u->update($data);
		}
	}
}
?>
