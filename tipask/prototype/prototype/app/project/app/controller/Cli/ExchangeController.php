<?php
class Cli_ExchangeController extends Base_Controller_Action{
    protected $oExchange;
    
	public function init()
	{
		parent::init();
		$this->oExchange = new Lm_Exchange();
	}
    
    public function getExchangeQueueToSocketAction()
    {
       $count = intval($this->request->count)? intval($this->request->count) : 200;
       for($i = 0;$i<=5;$i++)
       {
	       $ExchangeList = $this->oExchange->getExchangeQueueToProcess($count);
	       if(is_array($ExchangeList))
	       {
	            foreach($ExchangeList as $key=>$val)
	            {
	                $convertExchange = $this->oExchange->convertExchangeToSocket($val['MinExchangeId']);
	                echo $val['MinExchangeId']."-".$convertExchange."\n";
	            }
	       }
	       sleep(10);
   		}
    }
}