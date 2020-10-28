<?php

class dbDaGames
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

    public function deleteGame($gameId) {
        return $this->dataBase->deleteWhere("game", "id=".$gameId)!==false;
    }

    public function saveGameStatus($gameId,$status) {
        $data = array();
        $data = $this->dataBase->insertFieldInArray($data,"gameStatusJson",json_encode($status));
        //$data = $this->dataBase->insertUserDateIP($data);

        return $this->dataBase->updateWhere("games",$data,"id=".$gameId);
    }

    public function newGame($gameId) {
        $data = array();
        $data = $this->dataBase->insertFieldInArray($data,"gameId",$gameId);
        return $this->dataBase->insert("game",$data);
    }

    public function getGameById($id) {
        $sql  = "select * from game where id=".$id;
        $ret = $this->dataBase->querySignleRow($sql);
        if ($ret==null)
            return null;
        $ret["gameStatus"]=$this->getGameDataFromJsonString($ret["gameStatusJson"]);
        return $ret;
    }

    public function saveGame($gameId,$gameStatus) {
        $data=array();
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "gameStatusJson", $gameStatus);
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "dateEnd", Date("Y-m-d h:i:s"));
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "highScore", (json_decode($gameStatus))->score);
        return $this->dbDAO->dataBase->update("game", $data,"id",$gameId);
    }

    public function createGame($userId,$ip,$agent,$lang,$gameId){
        $data=array();
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "userId", $userId);
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "gameId", $gameId);
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "ip", $ip);
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "agent", $agent);
        $data=$this->dbDAO->dataBase->insertFieldInArray($data, "language", $lang);
        return $this->dbDAO->dataBase->insert("game", $data);
    }


        public function getLastActivGame($userId,$ip,$agent,$lang,$gameId){
        $ret = $this->getGameByUseridAgentLangGameId($userId,$ip,$agent,$lang,$gameId);
        if ($ret==null)
            return null;
        foreach ($ret as $game) {
            if (!isset($game["gameStatus"]) || !isset($game["gameStatus"]["over"]) || !$game["gameStatus"]["over"] )
                return $game;
        }
        return null;
    }

    public function getGameByUseridAgentLangGameId($userId,$ip,$agent,$lang,$gameId,$limit=25) {
        if ($userId!=null) {
            $query = "select * from game  ";
            $query .= " where gameId=" . $gameId . " and userId=" . $userId . " order by dateBegin desc limit " . $limit;
            $ret = $this->dataBase->queryArray($query);
            if (sizeof($ret) != 0)
                return $this->decodeGameDataInArray($ret);
        }
        //IP, Agent, Language
        $query  = "select * from game  ";
        $query .=" where gameId=".$gameId." and ip='".$ip."' and agent='".$agent."' and language='".$lang."' order by dateBegin desc limit ".$limit;
        $ret = $this->dataBase->queryArray($query);
        if (sizeof($ret)!=0)
            return $this->decodeGameDataInArray($ret);
        //IP
        $query  = "select * from game  ";
        $query .=" where gameId=".$gameId." and ip='".$ip."'  order by dateBegin desc limit ".$limit;
        $ret = $this->dataBase->queryArray($query);
        if (sizeof($ret)!=0)
            return $this->decodeGameDataInArray($ret);
        //Agent Language
        $query  = "select * from game  ";
        $query .=" where gameId=".$gameId." and agent='".$agent."' and language='".$lang."' order by dateBegin desc limit ".$limit;
        $ret = $this->dataBase->queryArray($query);
        if (sizeof($ret)!=0)
            return $this->decodeGameDataInArray($ret);
        return null;
    }

    public function getBestPlayers(int $gameId, int $limit=25)
    {
        $query = "select person.*,game.highScore,game.gameStatusJson from game left join person on person.id=game.userId where gameId=".$gameId." order by highScore desc limit ".$limit;
        $ret = $this->dataBase->queryArray($query);
        return $this->decodeGameDataInArray($ret);;
    }

    private function decodeGameDataInArray($games)  {
        foreach ($games as $idx=>$game) {
            $games[$idx]["gameStatus"]=$this->getGameDataFromJsonString($game["gameStatusJson"]);
        }
        return $games;
    }

    private function getGameDataFromJsonString($jsonString) {
        return json_decode($jsonString,true);
    }

}