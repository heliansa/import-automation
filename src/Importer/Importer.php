<?php
namespace Heliansa\Automation\Importer;

/**
 *
 * @author Heliansa
 *
 */
class Importer
{
    /**
     * @var Data $data
     */
    protected $data;

    protected $current_row;


    protected $session_prefix = 'heliansa_importer';


    protected function initSession(){
        if(isset($_SESSION[$this->session_prefix])){
            $this->data = unserialize($_SESSION[$this->session_prefix]);
        }else{
            $_SESSION[$this->session_prefix] = serialize($this->data);
        }
    }


    protected function getSession(){

        if($this->hasSession()){
            $this->data->unserialize($_SESSION[$this->session_prefix] );
        }
    }

    protected function setSession(){


        $_SESSION[$this->session_prefix] = $this->data->serialize();

    }

    protected function hasSession()
    {
        if (isset($_SESSION[$this->session_prefix]) ) {
            return true;
        } else {
            return false;
        }
    }

    protected function destroySession(){
        unset($_SESSION[$this->session_prefix]);
    }

    protected $last_touch_time;

    protected function touch(){
        $last_touch_time = round(microtime(true) * 1000);

        if($this->last_touch_time){
            $diff  = $last_touch_time - $this->last_touch_time;
            if($diff > 0){

                $this->data->setSpeed(1000/$diff);
            }
        }

        $this->last_touch_time = $last_touch_time;
    }

    protected function calculateLimit(){
        $speed = $this->data->getSpeed();
        $limit = floor($speed * 5);
        $this->data->setLimit($limit);
    }

    protected $callback_next;
    public function Next($callback){

        if (is_callable($callback)) {
            $this->callback_next = $callback;
        }
    }

    protected function loopNext(){

        $this->touch();
        $linecount = $this->data->getCurrentIndex();
        $total = $this->data->getTotal();

        $offset = $this->data->getOffset();
        $limit = $this->data->getLimit();
        if($linecount <= ($limit + $offset) && $linecount < $total ){
            $this->data->IncreaseIndex();
            $linecount = $this->data->getCurrentIndex();


            if (is_callable($this->callback_next)) {
                $next = call_user_func($this->callback_next);
            }

            $this->loopCurrent();

            if($next == false){
                return false;
            }


            $this->calculateLimit();
            return true;
        }
        return false;





    }

    protected $callback_total;
    public function Total($callback){
        if (is_callable($callback)) {
            $this->callback_total = $callback;
        }
    }
    protected function loopTotal(){
        if (is_callable($this->callback_total)) {
            $total = call_user_func($this->callback_total);
            $this->data->setTotal($total? $total : 0);
        }
    }

    protected $callback_current;
    public function Current($callback){

        if (is_callable($callback)) {
            $this->callback_current = $callback;
        }
    }

    protected function loopCurrent(){
        if (is_callable($this->callback_current)) {
            $this->current_row = call_user_func($this->callback_current);
        }
    }

    protected $callback_insert;
    public function Insert($callback){

        if (is_callable($callback)) {
            $this->callback_insert = $callback;
        }
    }

    public function getCurrentRow(){
        return $this->current_row;
    }

    protected function loopInsert(){
        if (is_callable($this->callback_insert)) {
            $return = call_user_func($this->callback_insert,$this);
            if($return === -1){
                $this->data->increaseDuplicate();
            }elseif($return == 1){
                $this->data->increaseInserted();
            }else{
                $this->data->setError($return);
            }
        }else{
            $this->data->setError('Error!');
        }
    }


    public function import(){


        if($this->hasSession()){
            $this->doProcess();

        }else{
            $this->doStart();
        }

        $this->data->setCurrentIndex(0);
        do{
            $linecount = $this->data->getCurrentIndex();
            $offset = $this->data->getOffset();
            $limit = $this->data->getLimit();
            $total = $this->data->getTotal();

            if($linecount >= $offset){
                $this->loopInsert();
            }

            $this->setSession();

        }while($this->loopNext());

        $this->data->setOffsetToCurrent();
        $this->setSession();

        //        print_r($this->data);
        $linecount = $this->data->getCurrentIndex();
        if($linecount < $total  ){
            $this->doRedirect();
        }else{
            $this->doComplete();
        }

    }


    public $global;

    function __construct()
    {
        $this->data = new Data();
    }

    public function setGlobal(&$global ){

        $this->global = $global;
    }

    protected function doStart(){

        $this->data->setCurrentTime(date('U'));
        $this->data->setStartedTime(date('U'));
        $this->data->setStartedIndex(0);
        $this->data->setLimit(100);

        $this->loopTotal();
        $this->setSession();

    }

    protected function doComplete(){
        $this->destroySession();
    }

    protected function doProcess(){

        $this->getSession();
        $this->data->setCurrentTime(date('U'));

    }

    protected function doEndProcess(){

    }

    protected $callback_redirect;
    public function Redirect($callback){

        if (is_callable($callback)) {
            $this->callback_redirect = $callback;
        }
    }

    protected function doRedirect(){

        if (is_callable($this->callback_redirect)) {
            call_user_func($this->callback_redirect);

        }
    }


    function __destruct()
    {
        $this->data->setOffsetToCurrent();
        $this->setSession();


    }

    public function getData(){
        return $this->data;
    }


}
