<?php
/**
 * Created by solly [01.06.17 9:53]
 */

namespace tests\unit;

use insolita\prefiller\PrefillConfig;
use yii\base\DynamicModel;
use yii\base\Model;

class DummyModel extends Model
{
    public $oneField;
    
    public $secondField;
    
    public $thirdField;
    
    public function rules()
    {
        return [
            [['oneField'], 'required'],
            [['oneField'], 'string','min'=>3],
            [['secondField'], 'number', 'min' => 100,'integerOnly'=>true],
            [['thirdField'], 'boolean'],
        ];
    }
    
    public function formName()
    {
        return 'modelFormName';
    }
    
    public function defaults()
    {
        return [
            'oneField'=>'Dummy',
            'secondField'=>100500,
            'thirdField'=>false
        ];
    }
    
    /**
     * @return object|PrefillConfig
     */
    public function fillConfig1()
    {
        return \Yii::createObject(
            [
                'class'           => PrefillConfig::class,
                'formName'        => 'customFormName',
                'method'          => 'post',
                'defaultValues'   => $this->defaults(),
                'skipFromRequestAttributes' => ['thirdField'],
            ]
        );
    }
    /**
     * @return object|PrefillConfig
     */
    public function fillConfig2()
    {
        return \Yii::createObject(
            [
                'class'           => PrefillConfig::class,
                'formName'        => 'MyDummy',
                'method'          => 'post',
                'defaultValues'   => $this->defaults(),
            ]
        );
    }
    /**
     * @return object|PrefillConfig
     */
    public function fillConfig3()
    {
        return \Yii::createObject(
            [
                'class'           => PrefillConfig::class,
                'serialized' => true,
                'method'          => 'get',
            ]
        );
    }
}
