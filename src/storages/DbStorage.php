<?php
/**
 * Created by solly [29.01.17 0:45]
 */

namespace insolita\prefiller\storages;

use insolita\prefiller\contracts\IPrefillStorage;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

/**
 * Class DbStorage
 * This class for example purpose only
 *
 * @package insolita\prefiller\storages
 */
class DbStorage extends BaseStorage implements IPrefillStorage
{
    /**
     * @var Connection $connection
     */
    public $connection;
    
    /**
     * @var string
     */
    public $tableName = '{{%user_settings}}';
    
    /**
     * @var string
     */
    public $userField = 'userId';
    
    /**
     * @var string
     */
    public $keyField = 'setting';
    
    /**
     * @var string
     */
    public $valueField = 'data';
    
    /**
     * Callback for userId resolve, by default userId detected as Yii::$app->user->id
     *
     * @var callable|null
     */
    public $userIdDetector;
    
    /**
     * @var
     */
    protected $data;
    
    /**
     *
     */
    public function init()
    {
        parent::init();
        $this->connection = Instance::ensure($this->connection, Connection::class);
    }
    
    /**
     * @inheritDoc
     */
    public function getValue(string $name, $default = null)
    {
        $userId = $this->getUserId();
        return ArrayHelper::getValue($this->getData($userId), $name, $default);
    }
    
    /**
     * @inheritDoc
     */
    public function setValue(string $name, $value)
    {
        $userId = $this->getUserId();
        if (empty($this->getData($userId))) {
            $exists = false;
        } else {
            $exists = isset($this->data[$name]);
        }
        $this->data[$name] = $value;
        if ($exists) {
            $this->connection->createCommand()->update(
                $this->tableName,
                [$this->valueField => $value],
                [
                    'AND',
                    [$this->userField => $userId],
                    [$this->keyField => $name],
                ]
            );
        } else {
            $this->connection->createCommand()->insert(
                $this->tableName,
                [
                    $this->keyField   => $name,
                    $this->valueField => $value,
                    $this->userField  => $userId,
                ]
            );
        }
        
    }
    
    protected function getUserId()
    {
        if (is_callable($this->userIdDetector)) {
            $userId = call_user_func($this->userIdDetector);
        } else {
            $userId = \Yii::$app->getUser()->id;
        }
        return $userId;
    }
    
    /**
     * @return array
     */
    protected function getData($userId)
    {
        if (!$this->data) {
            $this->data = (new Query())
                ->select('*')
                ->from($this->tableName)
                ->where([$this->userField => $userId])
                ->all($this->connection);
            $this->data = ArrayHelper::map($this->data, $this->keyField, $this->valueField);
        }
        return $this->data;
    }
    
}
