<?php
class Cli_ProductController extends Base_Controller_Action{
    protected $oProduct;
    
	public function init()
	{
		parent::init();
		$this->oProduct = new Config_Product_Product();
		$this->oSkin = new Config_Skin();
		$this->oHero = new Config_Hero();
	}
    
    public function getProductQueueToSocketAction()
    {
		//$this->oProduct->resetProductQueue();
		$count = intval($this->request->count)? intval($this->request->count) : 500;
		for($i = 0;$i<10;$i++)
		{
		   $ProductList = $this->oProduct->getProductQueueToProcess($count);
		   if(is_array($ProductList))
		   {
				foreach($ProductList as $key=>$val)
				{
					echo $val['ProductType']."<br>";
					$convertProduct = $this->oProduct->convertProductToSocket($val);				   	
				}
		   }
		   sleep(5);
		}
    }
}