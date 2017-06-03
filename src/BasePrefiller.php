<?php
/**
 * Created by solly [29.01.17 1:56]
 */

namespace insolita\prefiller;

use insolita\prefiller\contracts\IFormPrefiller;
use insolita\prefiller\contracts\IPrefillStorage;
use insolita\prefiller\storages\BaseStorage;
use yii\base\Component;
use yii\web\Request;
use yii\di\Instance;

/**
 * Class BasePrefiller
 *
 * @package insolita\prefiller
 */
abstract class BasePrefiller extends Component implements IFormPrefiller
{
    /**
     * @var
     */
    protected $storagePrefix = 'app';
    /**
     * @var \yii\web\Request
     */
    public $request;
    
    /**
     * @var IPrefillStorage $storage
     */
    public $storage;
    
    
    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->request = Instance::ensure($this->request, Request::class);
        $this->storage = Instance::ensure($this->storage, BaseStorage::class);
    }
    
    /**
     * @param string $prefix
     *
     * @return $this
     */
    public function setStoragePrefix($prefix)
    {
        $this->storagePrefix = $prefix;
        return $this;
    }
    
}
