<?php
/**
 * Created by PhpStorm.
 * User: Levi
 * Date: 17.12.2018
 * Time: 21:22
 */

class dbDaPersonInPicture
{
    /**
     * @var dbDAO
     */
    private $dbDAO;

    /**
     * @var \maierlabs\lpfw\MySql
     */
    private $dataBase;

    /**
     * dbDaOpinion constructor.
     * @param dbDAO $dbDAO
     */
    public function __construct(dbDAO $dbDAO)
    {
        $this->dbDAO = $dbDAO;
        $this->dataBase = $this->dbDAO->dataBase;
    }

    public function deletePersonInPicture($personId, $pictureId) {
        return $this->dataBase->deleteWehre("personinpicture", "personID=".$personId." and pictureID=".$pictureId);
    }

    public function savePersonInPicture($personId, $pictureId,$xPos=0.5, $yPos=0.5, $size=0.02) {
        $data = array();
        $data = $this->dataBase->insertFieldInArray($data,"personID",$personId);
        $data = $this->dataBase->insertFieldInArray($data,"pictureID",$pictureId);
        $data = $this->dataBase->insertFieldInArray($data,"changeUserID",getLoggedInUserId());
        $data = $this->dataBase->insertUserDateIP($data);
        $data = $this->dataBase->insertFieldInArray($data,"xPos",$xPos);
        $data = $this->dataBase->insertFieldInArray($data,"yPos",$yPos);
        $data = $this->dataBase->insertFieldInArray($data,"size",$size);

        return $this->dataBase->insert("personinpicture",$data);
    }

    public function getListOfPersonInPicture($pictureId) {
        $where= " where pictureID=".$pictureId;
        $sql  = "select personinpicture.*,person.lastname,person.firstname,person.title,person.picture";
        $sql .= " from personinpicture join person on person.id=personinpicture.personid ".$where;
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
    }
}