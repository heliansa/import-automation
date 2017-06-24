<?php
namespace Heliansa\Automation\Importer;

class Data implements \Serializable
{

    public $total;
    public $offset;
    public $limit;
    public $speed;

    public $messages;

    public $started_time;
    public $started_index;

    public $rows_inserted;
    public $rows_error;
    public $rows_duplicate;

    public $current_index;
    public $current_time;

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param mixed $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    public function setOffsetToCurrent()
    {
        $this->offset = $this->current_index + 1;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return mixed
     */
    public function getSpeed()
    {
        return $this->speed ? $this->speed : 1000 ;
    }

    /**
     * @param mixed $speed
     */
    public function setSpeed($speed)
    {
        $this->speed = $speed;
    }

    /**
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param mixed $messages
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
    }


    public function setMessage($index,$body,$type){

        $message = new \stdClass();
        $message->index = $index;
        $message->type = $type;
        $message->message = $body;
        $this->messages[] = $message;
    }


    public function setDebugMesage($message){
        $this->setMessage($this->current_index,$message,'DEBUG');
    }

    public function setErrorMesage($error){
        $this->setMessage($this->current_index,$error,'ERROR');
    }

    public function setCustomMesage($message,$type = 'NOTICE'){
        $this->setMessage($this->current_index,$message,strtoupper($type));
    }

    public function setError($error){
        $this->rows_error++;
        $this->setErrorMesage($error);
    }
    /**
     * @return mixed
     */
    public function getStartedTime()
    {
        return $this->started_time;
    }

    /**
     * @param mixed $started_time
     */
    public function setStartedTime($started_time)
    {
        $this->started_time = $started_time;
    }

    /**
     * @return mixed
     */
    public function getStartedIndex()
    {
        return $this->started_index;
    }

    /**
     * @param mixed $started_index
     */
    public function setStartedIndex($started_index)
    {
        $this->started_index = $started_index;
    }

    /**
     * @return mixed
     */
    public function getRowsInserted()
    {
        return $this->rows_inserted;
    }

    /**
     * @param mixed $rows_inserted
     */
    public function setRowsInserted($rows_inserted)
    {
        $this->rows_inserted = $rows_inserted;
    }

    public function increaseInserted(){
        $this->rows_inserted++;
    }

    /**
     * @return mixed
     */
    public function getRowsError()
    {
        return $this->rows_error;
    }

    /**
     * @param mixed $rows_error
     */
    public function setRowsError($rows_error)
    {
        $this->rows_error = $rows_error;
    }

    public function increaseError(){
        $this->rows_error++;
    }

    /**
     * @return mixed
     */
    public function getRowsDuplicate()
    {
        return $this->rows_duplicate;
    }

    /**
     * @param mixed $rows_duplicate
     */
    public function setRowsDuplicate($rows_duplicate)
    {
        $this->rows_duplicate = $rows_duplicate;
    }

    public function increaseDuplicate(){
        $this->rows_duplicate++;
    }

    /**
     * @return mixed
     */
    public function getCurrentIndex()
    {
        return $this->current_index;
    }

    /**
     * @param mixed $current_index
     */
    public function setCurrentIndex($current_index)
    {
        $this->current_index = $current_index;
    }

    public function IncreaseIndex()
    {
        $this->current_index ++;
    }

    /**
     * @return mixed
     */
    public function getCurrentTime()
    {
        return $this->current_time;
    }

    /**
     * @param mixed $current_time
     */
    public function setCurrentTime($current_time)
    {
        $this->current_time = $current_time;
    }

    public function serialize() {
        return serialize((array)$this);
    }
    public function unserialize($data) {
        $data  = unserialize($data);

        foreach ($data as $key=>$value){
            $this->$key = $value;
        }
    }


    /**
     * @return number|boolean
     */
    public function getProgressInPercent(){
        if($this->getTotal() > 0){
            $percent =  ($this->getCurrentIndex() / $this->getTotal()) * 10000;
            return floor ($percent) / 100;
        }else{
            return false;
        }
    }

    public function getElapsedTime(){
        $current = $this->getCurrentTime();
        $started = $this->getStartedTime();
        $diff = ($current - $started )>0 ? $current - $started : 1;

        return gmdate ('H:i:s',$diff);
    }

}

