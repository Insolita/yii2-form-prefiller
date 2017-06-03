<?php
/**
 * Created by solly [28.01.17 23:55]
 */

namespace insolita\prefiller\storages;

use Carbon\Carbon;
use insolita\prefiller\contracts\IPrefillStorage;
use yii\di\Instance;
use yii\web\Cookie;
use yii\web\Request;
use yii\web\Response;

/**
 * Class CookieStorage
 *
 * @package insolita\prefiller\storages
 */
class CookieStorage extends BaseStorage implements IPrefillStorage
{
    /**
     * @var Request $request
     */
    public $request = 'request';
    
    /**
     * @var Response $response
     */
    public $response = 'response';
    
    /**
     * Cookie timeout before clean //prevent cookie overflow
     *
     * @var int
     */
    public $timeout = 3600;
    
    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->response = Instance::ensure($this->response, Response::class);
        $this->request = Instance::ensure($this->request, Request::class);
    }
    
    /**
     * @inheritDoc
     */
    public function getValue(string $name, $default = null)
    {
        return $this->request->cookies->getValue($name, $default);
    }
    
    /**
     * @inheritDoc
     */
    public function setValue(string $name, $value)
    {
        $cook = new Cookie(
            [
                'name'     => $name,
                'httpOnly' => true,
                'value'    => $value,
                'expire'   => Carbon::now()->addSeconds($this->timeout)->timestamp,
            ]
        );
        $this->response->cookies->add($cook);
    }
    
}
