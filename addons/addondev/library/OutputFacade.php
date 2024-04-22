<?php
namespace addons\addondev\library;
use think\exception\ValidateException;

/**
 * 
 * @author dungang
 * @method void info($message)
 * @method void error($message)
 * @method void comment($message)
 * @method void warning($message)
 * @method void highlight($message)
 * @method void question($message)
 */
class OutputFacade
{
  
    public $output;

    public $showError = true;
    
    public function __construct($output = null){
        $this->output = $output;
    }
    
    public function __call($name, $params){
        if($this->output) {
            if($this->showError == false && $name == 'error') {
                return null;
            } else {
                return call_user_func_array([$this->output,$name], $params);
            }
        } else {
            if($this->showError && $name == 'error') {
                throw new ValidateException($params);
            }
            return null;
        }
    }
}

