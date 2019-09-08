<?php

class dbDeprecated
{
    /**
     * Union select the ids from the latest changes use accelerator for a better performace
     * @param DateTime $dateFrom
     * @param int $limit
     * @return array
     * @deprecated
     */
    public function getRecentChangeList($dateFrom, $limit = 50)
    {
        if ($dateFrom == null) {
            $dateFrom = new DateTime();
        }
        return $this->getRecentChangesListByDate($dateFrom, $limit);
        /*
        if ($dateFrom!=null) {
        return $this->getRecentChangesListByDate($dateFrom, $limit);
        }
        $data = $this->getAcceleratorData();
        if ($limit==sizeof($data)) {
        return $data;
        }
        return $this->updateRecentChangesList($dateFrom,$limit);
        */
    }

    /**
     * @deprecated
     */
    public function deleteFromRecentChangesList($id, $type)
    {
        $accList = $this->getAcceleratorData(1);
        $found = $this->array3ValueSearch($accList, "id", $id, "id", $id, "type", $type);
        if ($found !== false) {
            array_splice($accList, $found, 1);
            $date = $accList[sizeof($accList) - 1]["changeDate"];
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $date);
            $olderValue = $this->getRecentChangesListByDate($date, 1);
            array_unshift($accList, $olderValue[0]);
            $this->updateAcceleratorEntry($accList, 1);
            return true;
        }
        return false;
    }

    /**
     * @deprecated
     */
    public function insertToRecentChangesList($id, $changeDate, $type, $action, $changeUserID)
    {
        $accList = $this->getAcceleratorData(1);
        $accList = $this->insertToArrayRecentChangesList($accList, $id, $changeDate, $type, $action, $changeUserID);
        $this->updateAcceleratorEntry($accList, 1);
    }

    /**
     * @deprecated
     */
    public function insertToArrayRecentChangesList($accList, $id, $changeDate, $type, $action, $changeUserID)
    {
        $newValue = array("id" => $id, "changeDate" => $changeDate, "type" => $type, "action" => $action, "changeUserID" => $changeUserID);
        $found = $this->array3ValueSearch($accList, "id", $id, "type", $type, "action", $action);
        if ($found !== false) {
            array_splice($accList, $found, 1);
        } else {
            array_splice($accList, sizeof($accList) - 1, 1);
        }
        array_unshift($accList, $newValue);
        return $accList;
    }

    /**
     * @deprecated
     */
    public function array3ValueSearch($array, $key1, $value1, $key2, $value2, $key3, $value3)
    {
        foreach ($array as $id => $value) {
            if ($value[$key1] === $value1 && $value[$key2] === $value2 && $value[$key3] === $value3) {
                return $id;
            }
        }
        return false;
    }

    /**
     * @deprecated
     */
    public function updateRecentChangesList()
    {
        $data = $this->getAcceleratorData(1);
        $limit = sizeof($data);
        $dateFrom = date_create();
        if ($limit > 0) {
            $rows = $this->getRecentChangesListByDate($dateFrom, $limit);
            $this->updateAcceleratorEntry($rows, 1);
            return $rows;
        }
        $rows = $this->getRecentChangesListByDate($dateFrom, 1);
        $this->updateAcceleratorEntry($rows, 1);
        return $rows;
    }

    /**
     * @deprecated
     */
    public function updateAcceleratorEntry($rows, $type = 1)
    {
        $data = array();
        $data = $this->dataBase->insertFieldInArray($data, 'type', $type);
        $data = $this->dataBase->insertFieldInArray($data, 'json', json_encode($rows));
        $data = $this->dataBase->insertFieldInArray($data, "changeDate", date("Y-m-d H:i:s"));
        $this->dataBase->update('accelerator', $data, "type", $type);
    }

    /**
     * @deprecated
     * Return the date and time as string for accelerator
     * @param $type
     * @return string
     */
    public function getAcceleratorDate($type = 1)
    {
        $ret = $this->dataBase->querySignleRow("select * from accelerator where `type`=" . $type);
        if ($ret != null) {
            return date_create($ret["changeDate"]);
        }
    }

    /**
     * @param int $type
     * @return array|null
     * @deprecated
     */
    public function getAcceleratorRow($type = 1)
    {
        $row = $this->dataBase->querySignleRow("select * from accelerator where type=" . $type);
        return $row;
    }

    /**
     * @deprecated
     * @param int $type
     * @return mixed
     */
    public function getAcceleratorData($type = 1)
    {
        $data = $this->getAcceleratorRow($type);
        return json_decode($data["json"], true);
    }
}