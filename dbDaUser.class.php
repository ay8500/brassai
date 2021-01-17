<?php
include_once 'dbDAO.class.php';

class dbDaUser implements \maierlabs\lpfw\iDbDaUser
{
    /**
     * @var dbBL
     */
    public $dbDAO;

    /**
     * @var \maierlabs\lpfw\MySql
     */
    public $dataBase;

    /**
     * dbDaOpinion constructor.
     * @param dbDAO $dbDAO
     */
    public function __construct($dbDAO)
    {
        $this->dbDAO = $dbDAO;
        $this->dataBase = $this->dbDAO->dataBase;
    }

    public function getUserById($id)
    {
        return $this->dbDAO->getPersonByID($id);
    }

    public function getUserByUsename($id)
    {
        return $this->dbDAO->getPersonByUser($id);
    }

    public function getUserByEmail($id)
    {
        return $this->dbDAO->getPersonByEmail($id);
    }

    public function getUserByFacebookId($id)
    {
        return $this->dbDAO->getPersonByFacobookId($id);
    }

    public function setUserPassword($id, $password)
    {
        if ($this->dbDAO->dataBase->update("person",[["field"=>"passw","type"=>"s","value"=>encrypt_decrypt("encrypt",$password)]],"id",$id))
            return $id;
        else
            return -1;
    }

    public function setUserName($id, $username)
    {
        return $this->dbDAO->savePersonField($id,"user",$username);
    }

    public function setUserFullName($id, $fullname)
    {
        //TODO split fullname into firstanam and lastname
        return $this->dbDAO->savePersonField($id,"lastname",$fullname);
    }

    public function setUserFacebookId($id, $facebookId)
    {
        return $this->dbDAO->savePersonField($id,"facebookid",$facebookId);
    }

    public function setUserEmail($id, $email)
    {
        return $this->dbDAO->savePersonField($id,"email",$email);
    }

    public function setUserRole($id, $role)
    {
        return $this->dbDAO->savePersonField($id,"role",$role);
    }

    public function setUserLastLogin($id)
    {
       return $this->dataBase->update("person", [["field"=>"userLastLogin","type"=>"s","value"=>date("Y-m-d H:i:s")]],"id",$id);
    }

    public function checkRequesterIp($requestId)
    {
        return $this->dbDAO->checkRequesterIP($requestId);
    }

    public function setRequest($requestId)
    {
        return $this->dbDAO->saveRequest($requestId);
    }

    public function getPersonName($user)
    {
        if ($user!=null) {
            $ret ="";
            if (isset($user["title"]))
                $ret = $user["title"].' ';
            $ret .= $user["lastname"]." ".$user["firstname"];
            if (isset($user["birthname"]) && trim($user["birthname"])!="")
                $ret .= " (".trim($user["birthname"]).")";
            return $ret;
        }
        return '';
    }


}