<?php
/**
 * Created by solly [29.01.17 0:01]
 */

namespace insolita\prefiller\storages;

use insolita\prefiller\contracts\IPrefillStorage;
use yii\di\Instance;
use yii\web\Session;

/**
 * Class SessionStorage
 *
 * @package insolita\prefiller\storages
 */
class SessionStorage extends BaseStorage implements IPrefillStorage
{
    /**
     * @var Session $session
     **/
    public $session='session';
    
    public function init()
    {
        parent::init();
        $this->session = Instance::ensure($this->session, Session::class);
    }
    
    /**
     * @inheritDoc
     */
    public function getValue(string $name, $default = null)
    {
        return $this->session->get($name, $default);
    }
    
    /**
     * @inheritDoc
     */
    public function setValue(string $name, $value)
    {
        $this->session->set($name, $value);
    }
    
}
