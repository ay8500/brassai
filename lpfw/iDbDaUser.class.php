<?php
/** Interface class for user **/

namespace maierlabs\lpfw;


interface iDbDaUser
{
    public function __construct($db);

    public function getUserById($id);
    public function getUserByUsename($id);
    public function getUserByEmail($id);
    public function getUserByFacebookId($id);
    public function getPersonName($user);

    public function setUserPassword($id,$password);
    public function setUserName($id,$username);
    public function setUserFacebookId($id,$facebookId);
    public function setUserLastLogin($id);

    public function checkRequesterIp($requestId);
    public function setRequest($requestId);

}