<?php
/**
 * Created by IntelliJ IDEA.
 * User: miso
 * Date: 20.6.2017
 * Time: 15:54
 */

namespace PluginNewsFeed\Model;


class Priority extends AbstractModel {
    /**
     * @var integer
     */
    private $ID;
    /**
     * @var string;
     */
    private $priorityFrom;
    /**
     * @var string
     */
    private $priorityTo;
    /**
     * @var integer
     */
    private $priorityValue;
    /**
     * @var integer
     */
    private $newsID;
    /**
     * @var integer
     */
    private $streamID;

    /**
     * @return integer
     */
    public function getID() {
        return $this->ID;
    }

    /**
     * @return string
     */
    public function getPriorityFrom() {
        return $this->priorityFrom;
    }

    /**
     * @return string
     */
    public function getPriorityTo() {
        return $this->priorityTo;
    }

    /**
     * @return integer
     */
    public function getPriorityValue() {
        return $this->priorityValue;
    }

    public function checkValidity() {
        if ((time() < strtotime($this->priorityFrom)) || (time() > strtotime($this->priorityTo))) {
            $this->priorityValue = 0;
        }
    }

    public function update() {
        return $this->sqlite->query('UPDATE priority SET priority=?,priority_from=?,priority_to=? WHERE stream_id=? AND news_id =?',
            $this->priorityValue,
            $this->priorityFrom,
            $this->priorityTo,
            $this->streamID,
            $this->newsID);
    }

    public function create() {
        $res = $this->sqlite->query('SELECT * FROM priority WHERE news_id=? AND stream_id=?',
            $this->newsID,
            $this->streamID);
        if (count($this->sqlite->res2arr($res)) == 0) {
            $this->sqlite->query('INSERT INTO priority (news_id,stream_id,priority) VALUES(?,?,?)',
                $this->newsID,
                $this->streamID,
                0);
        };
        return (int)1;
    }

    public function delete() {
        $res = $this->sqlite->query('DELETE FROM priority WHERE stream_id=? AND news_id =?', $this->streamID, $this->newsID);
        return $this->sqlite->res2arr($res);
    }

    public function fillFromDatabase() {
        $res = $this->sqlite->query('SELECT * FROM priority WHERE stream_id=? AND news_id =?', $this->streamID, $this->newsID);
        return $this->fill($this->sqlite->res2row($res));
    }

    public function fill($data) {
        $this->priorityFrom = $data['priority_from'];
        $this->ID = $data['priority_id'];
        $this->priorityTo = $data['priority_to'];
        $this->priorityValue = $data['priority'];
        $this->checkValidity();
        return true;
    }

    public function __construct($params = [], $newsID = null, $streamID = null) {
        parent::__construct();
        $this->fill($params);
        $this->newsID = $newsID;
        $this->streamID = $streamID;
    }
}
