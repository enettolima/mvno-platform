<?php
use Luracast\Restler\iAuthenticate;
class SimpleAuth implements iAuthenticate
{
    const KEY = NATURAL_API_KEY;
    function __isAllowed()
    {
        return isset($_GET['key']) && $_GET['key'] == SimpleAuth::KEY ? TRUE : FALSE;
    }
    public function __getWWWAuthenticateString()
    {
        return 'Query name="key"';
    }
    /**
    * API Key to allow method to be visible
    *
    * @access protected
    * @throws 404 User not found for requested id  
    * @param int $id Book to be fetched
    * @return mixed 
    */
    function key()
    {
        return SimpleAuth::KEY;
    }
}