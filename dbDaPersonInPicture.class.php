<?php

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

    /**
     * remove person tag from a picture
     * @param int $personId
     * @param int $pictureId
     * @return bool
     */
    public function deletePersonInPicture($personId, $pictureId) {
        return $this->dataBase->deleteWhere("personInPicture", "personID=".$personId." and pictureID=".$pictureId);
    }

    /**
     * Save person position on a picture, the values are relative to the picture size an ar less then 1
     * @param int $personId
     * @param int $pictureId
     * @param float $xPos
     * @param float $yPos
     * @param float $size
     * @return bool
     */
    public function savePersonInPicture($personId, $pictureId,$xPos=0.5, $yPos=0.5, $size=0.02) {
        $data = array();
        $data = $this->dataBase->insertFieldInArray($data,"personID",$personId);
        $data = $this->dataBase->insertFieldInArray($data,"pictureID",$pictureId);
        $data = $this->dataBase->insertFieldInArray($data,"changeUserID",getLoggedInUserId());
        $data = $this->dataBase->insertUserDateIP($data);
        $data = $this->dataBase->insertFieldInArray($data,"xPos",$xPos);
        $data = $this->dataBase->insertFieldInArray($data,"yPos",$yPos);
        $data = $this->dataBase->insertFieldInArray($data,"size",$size);

        return $this->dataBase->insert("personInPicture",$data);
    }

    /**
     * Get the list of persons as array tagged on a picture
     * @param int $pictureId
     * @return array
     */
    public function getListOfPersonInPicture($pictureId) {
        $where= " where pictureID=".$pictureId;
        $sql  = "select personInPicture.*,person.lastname,person.firstname,person.title,person.picture";
        $sql .= " from personInPicture join person on person.id=personInPicture.personid ".$where;
        $this->dataBase->query($sql);
        return $this->dataBase->getRowList();
    }

    /**
     * Get a  person tag position ans size in a picture
     * @param int $pictureId
     * @param int $personId
     * @return array|null
     */
    public function getPersonInPicture($pictureId, $personId) {
        $where= " where pictureID=".$pictureId.' and personID='.$personId;
        $sql  = "select personInPicture.*,person.lastname,person.firstname,person.title,person.picture";
        $sql .= " from personInPicture join person on person.id=personInPicture.personid ".$where;
        return $this->dataBase->querySignleRow($sql);
    }
}