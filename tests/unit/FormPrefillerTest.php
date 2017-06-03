<?php
/**
 * Created by solly [25.05.17 1:59]
 */

namespace tests\unit;

use Codeception\Specify;
use Codeception\Test\Unit;
use Codeception\Util\Stub;
use insolita\prefiller\FormPrefiller;
use insolita\prefiller\PrefillConfig;
use insolita\prefiller\storages\CookieStorage;
use insolita\prefiller\storages\SessionStorage;
use yii\base\DynamicModel;
use yii\helpers\Json;
use yii\web\Request;

class FormPrefillerTest extends Unit
{
    use Specify;
    
    public function testFillFromStorage()
    {
        $this->specify(
            'serializedMode',
            function () {
                
                $request = Stub::makeEmpty(Request::class);
                \Yii::$container->setSingleton(Request::class, $request);
                
                $storage = Stub::make(
                    SessionStorage::class,
                    [
                        'setValue' => Stub::never(),
                        'getValue' => Stub::once(
                            function ($name, $default) {
                                verify($default)->isEmpty();
                                verify($name)->equals('test_modelFormName');
                                return Json::encode(['oneField' => 'fooBar', 'secondField' => 321]);
                            }
                        ),
                    ],
                    $this
                );
                \Yii::$app->set(
                    'prefiller',
                    ['class' => FormPrefiller::class, 'storage' => $storage, 'request' => $request]
                );
                $model = new DummyModel();
                verify($model->oneField)->isEmpty();
                verify($model->secondField)->isEmpty();
                verify($model->thirdField)->isEmpty();
                \Yii::$app->prefiller->setStoragePrefix('test_')->fillFromStorage(
                    $model,
                    $model->fillConfig3()
                );
                verify($model->oneField)->equals('fooBar');
                verify($model->secondField)->equals(321);
                verify($model->thirdField)->isEmpty();
                
            }
        );
        
        $this->specify(
            'separatedMode',
            function () {
                $request = Stub::makeEmpty(Request::class);
                \Yii::$container->setSingleton(Request::class, $request);
                
                $storage = Stub::make(
                    SessionStorage::class,
                    [
                        'setValue' => Stub::never(),
                        'getValue' => Stub::exactly(
                            3,
                            function ($name, $default) {
                                verify($default)->null();
                                verify(
                                    in_array(
                                        $name,
                                        [
                                            'test_oneField',
                                            'test_secondField',
                                            'test_thirdField',
                                        ]
                                    )
                                )->true();
                                if ($name == 'test_oneField') {
                                    return 'fooo';
                                } else {
                                    return null;
                                }
                            }
                        ),
                    ],
                    $this
                );
                \Yii::$app->set(
                    'prefiller',
                    ['class' => FormPrefiller::class, 'storage' => $storage, 'request' => $request]
                );
                
                $model = new DummyModel();
                verify($model->oneField)->isEmpty();
                verify($model->secondField)->isEmpty();
                verify($model->thirdField)->isEmpty();
                \Yii::$app->prefiller->setStoragePrefix('test_')->fillFromStorage(
                    $model,
                    $model->fillConfig1()
                );
                verify($model->oneField)->equals('fooo');
                verify($model->secondField)->null();
                verify($model->thirdField)->null();
            }
        );
        $this->specify(
            'separatedMode_withskips',
            function () {
                $request = Stub::makeEmpty(Request::class);
                \Yii::$container->setSingleton(Request::class, $request);
                
                $storage = Stub::make(
                    SessionStorage::class,
                    [
                        'setValue' => Stub::never(),
                        'getValue' => Stub::exactly(
                            2,
                            function ($name, $default) {
                                verify($default)->null();
                                verify(
                                    in_array(
                                        $name,
                                        [
                                            'test_oneField',
                                            'test_thirdField',
                                        ]
                                    )
                                )->true();
                                if ($name == 'test_oneField') {
                                    return 'fooo';
                                } else {
                                    return true;
                                }
                            }
                        ),
                    ],
                    $this
                );
                \Yii::$app->set(
                    'prefiller',
                    ['class' => FormPrefiller::class, 'storage' => $storage, 'request' => $request]
                );
                
                $model = new DummyModel();
                verify($model->oneField)->isEmpty();
                verify($model->secondField)->isEmpty();
                verify($model->thirdField)->isEmpty();
                $config = $model->fillConfig1();
                $config->skipFromStorageAttributes = ['secondField'];
                \Yii::$app->prefiller->setStoragePrefix('test_')->fillFromStorage(
                    $model,
                    $config
                );
                verify($model->oneField)->equals('fooo');
                verify($model->secondField)->null();
                verify($model->thirdField)->true();
            }
        );
    }
    
    public function testPersist()
    {
        $this->specify(
            'serializedMode',
            function () {
                $request = Stub::makeEmpty(Request::class);
                \Yii::$container->setSingleton(Request::class, $request);
                
                $storage = Stub::make(
                    SessionStorage::class,
                    [
                        'setValue' => Stub::once(
                            function ($name, $value) {
                                verify($name)->equals('test_customFormName');
                                verify($value)->equals(
                                    Json::encode(
                                        [
                                            'oneField'    => 'foo',
                                            'secondField' => 999,
                                            'thirdField'  => true,
                                        ]
                                    )
                                );
                            }
                        ),
                        'getValue' => Stub::never(),
                    ],
                    $this
                );
                \Yii::$app->set(
                    'prefiller',
                    ['class' => FormPrefiller::class, 'storage' => $storage, 'request' => $request]
                );
                $model = new DummyModel(['oneField' => 'foo', 'secondField' => 999, 'thirdField' => true]);
                $config = $model->fillConfig1();
                $config->serialized = true;
                \Yii::$app->prefiller->setStoragePrefix('test_')->persist($model, $config);
                
            }
        );
        $this->specify(
            'separatedMode',
            function () {
                $request = Stub::makeEmpty(Request::class);
                \Yii::$container->setSingleton(Request::class, $request);
                
                $storage = Stub::make(
                    SessionStorage::class,
                    [
                        'setValue' => Stub::exactly(
                            3,
                            function ($name, $value) {
                                verify(
                                    in_array(
                                        $name,
                                        [
                                            'test_oneField',
                                            'test_secondField',
                                            'test_thirdField',
                                        ]
                                    )
                                )->true();
                                if ($name == 'test_oneField') {
                                    verify($value)->equals('foo');
                                } elseif ($name == 'test_secondField') {
                                    verify($value)->equals(999);
                                } else {
                                    verify($value)->equals(true);
                                }
                            }
                        ),
                        'getValue' => Stub::never(),
                    ],
                    $this
                );
                \Yii::$app->set(
                    'prefiller',
                    ['class' => FormPrefiller::class, 'storage' => $storage, 'request' => $request]
                );
                $model = new DummyModel(['oneField' => 'foo', 'secondField' => 999, 'thirdField' => true]);
                $config = $model->fillConfig1();
                \Yii::$app->prefiller->setStoragePrefix('test_')->persist($model, $config);
            }
        );
    }
    
    public function testFill()
    {
        $this->specify(
            'With model defaults; From post',
            function () {
                $request = Stub::make(
                    Request::class,
                    [
                        'post' => Stub::once(
                            function ($formName) {
                                verify($formName)->equals('MyDummy');
                                return [
                                    'some'        => 'any',
                                    'oneField'    => 'fooBar',
                                    'secondField' => 321,
                                ];
                            }
                        ),
                        'get'  => Stub::never(),
                    ],
                    $this
                );
                $storage = Stub::make(
                    SessionStorage::class,
                    [
                        'setValue' => Stub::exactly(
                            3,
                            function ($name, $value) {
                                verify(['test_oneField', 'test_secondField', 'test_thirdField'])
                                    ->contains($name);
                                verify(['fooBar', '321', false])->contains($value);
                                return true;
                            }
                        ),
                        'getValue' => Stub::once(
                            function ($name, $default) {
                                verify($default)->null();
                                verify($name)->equals('test_thirdField');
                                return null;
                            }
                        ),
                    ],
                    $this
                );
                
                \Yii::$container->setSingleton(Request::class, $request);
                \Yii::$app->set(
                    'prefiller',
                    [
                        'class'   => FormPrefiller::class,
                        'storage' => $storage,
                        'request' => $request,
                    ]
                );
                $model = new DummyModel();
                \Yii::$app->prefiller->setStoragePrefix('test_')->fill(
                    $model,$model->fillConfig2()
                );
                verify($model->oneField)->equals('fooBar');
                verify($model->secondField)->equals(321);
                verify($model->thirdField)->equals(false);
                
            }
        );
        $this->specify(
            'emptyrequest;bystoragefilled',
            function () {
                $request = Stub::make(
                    Request::class,
                    [
                        'post' => Stub::once(
                            function ($formName) {
                                verify($formName)->equals('MyDummy');
                                return [];
                            }
                        ),
                        'get'  => Stub::never(),
                    ],
                    $this
                );
                $storage = Stub::make(
                    CookieStorage::class,
                    [
                        'setValue' => Stub::never(),//On empty request nothing for persist
                        'getValue' => Stub::consecutive('fooBar', 321, false),
                    ],
                    $this
                );
                \Yii::$container->setSingleton(Request::class, $request);
                \Yii::$app->set(
                    'prefiller',
                    [
                        'class'   => FormPrefiller::class,
                        'storage' => $storage,
                        'request' => $request,
                    ]
                );
                $model = new DummyModel();
                \Yii::$app->prefiller->setStoragePrefix('test_')->fill(
                    $model,$model->fillConfig2()
                );
                verify($model->oneField)->equals('fooBar');
                verify($model->secondField)->equals(321);
                verify($model->thirdField)->equals(false);
            }
        );
        $this->specify(
            'ignoredrequest',
            function () {
                $request = Stub::make(
                    Request::class,
                    [
                        'post' => Stub::once(
                            function ($formName) {
                                verify($formName)->equals('MyDummy');
                                return [
                                    'oneField'=>'qqqq',
                                    'secondField'=>333
                                ];
                            }
                        ),
                        'get'  => Stub::never(),
                    ],
                    $this
                );
                $storage = Stub::make(
                    CookieStorage::class,
                    [
                        'setValue' => Stub::never(),//On skipped request data nothing for persist
                        'getValue' => Stub::consecutive('fooBar', 321, false),
                    ],
                    $this
                );
                \Yii::$container->setSingleton(Request::class, $request);
                \Yii::$app->set(
                    'prefiller',
                    [
                        'class'   => FormPrefiller::class,
                        'storage' => $storage,
                        'request' => $request,
                    ]
                );
                $model = new DummyModel();
                $config = $model->fillConfig2();
                $config->skipFromRequestAttributes=['oneField','secondField'];
                \Yii::$app->prefiller->setStoragePrefix('test_')->fill(
                    $model,$config
                );
                verify($model->oneField)->equals('fooBar');
                verify($model->secondField)->equals(321);
                verify($model->thirdField)->equals(false);
            }
        );
    
        $this->specify(
            'request with invalid data',
            function () {
                $request = Stub::make(
                    Request::class,
                    [
                        'post' => Stub::once(
                            function ($formName) {
                                verify($formName)->equals('MyDummy');
                                return [
                                    'oneField'=>'w',
                                    'secondField'=>64646.9994,
                                    'thirdField'=>[34567]
                                ];
                            }
                        ),
                        'get'  => Stub::never(),
                    ],
                    $this
                );
                $storage = Stub::make(
                    CookieStorage::class,
                    [
                        'setValue' => Stub::never(),//Request data ignored as invalid nothing for persist
                        'getValue' => Stub::consecutive('fooBar', 321, false),
                    ],
                    $this
                );
                \Yii::$container->setSingleton(Request::class, $request);
                \Yii::$app->set(
                    'prefiller',
                    [
                        'class'   => FormPrefiller::class,
                        'storage' => $storage,
                        'request' => $request,
                    ]
                );
                $model = new DummyModel();
                $config = $model->fillConfig2();
                $config->validateRequest=true;
                \Yii::$app->prefiller->setStoragePrefix('test_')->fill(
                    $model,$config
                );
                verify($model->oneField)->equals('fooBar');
                verify($model->secondField)->equals(321);
                verify($model->thirdField)->equals(false);
            }
        );
    
        $this->specify(
            'emptyrequest;storageignored',
            function () {
                $request = Stub::make(
                    Request::class,
                    [
                        'post' => Stub::once(
                            function ($formName) {
                                verify($formName)->equals('MyDummy');
                                return [];
                            }
                        ),
                        'get'  => Stub::never(),
                    ],
                    $this
                );
                $storage = Stub::make(
                    CookieStorage::class,
                    [
                        'setValue' => Stub::never(),//On empty request nothing for persist
                        'getValue' => Stub::consecutive('fooBarBaz', 321, true),
                    ],
                    $this
                );
                \Yii::$container->setSingleton(Request::class, $request);
                \Yii::$app->set(
                    'prefiller',
                    [
                        'class'   => FormPrefiller::class,
                        'storage' => $storage,
                        'request' => $request,
                    ]
                );
                $model = new DummyModel();
                $config = $model->fillConfig2();
                $config->skipFromStorageAttributes=['secondField','thirdField'];
                \Yii::$app->prefiller->setStoragePrefix('test_')->fill(
                    $model,$config
                );
                verify($model->oneField)->equals('fooBarBaz');
                verify($model->secondField)->equals(100500);
                verify($model->thirdField)->equals(false);
            }
        );
    
        $this->specify(
            'DynamicModel',
            function () {
                $request = Stub::make(
                    Request::class,
                    [
                        'get' => Stub::once(
                            function ($formName) {
                                verify($formName)->equals('DynamicModel');
                                return ['one'=>'alpha','two'=>'beta'];
                            }
                        ),
                        'post'  => Stub::never(),
                    ],
                    $this
                );
                $storage = Stub::make(
                    CookieStorage::class,
                    [
                        'setValue' => Stub::once(function($name,$value){
                            verify($name)->equals('appDynamicModel');
                            verify($value)->equals(Json::encode([
                                'one'=>'alpha','two'=>'beta','three'=>'epsilon'
                                                                ]));
                        }),//On empty request nothing for persist
                        'getValue' => Stub::once(function(){
                            return Json::encode(['one'=>'gamma','two'=>'delta','three'=>'epsilon']);
                        }),
                    ],
                    $this
                );
                \Yii::$container->setSingleton(Request::class, $request);
                \Yii::$app->set(
                    'prefiller',
                    [
                        'class'   => FormPrefiller::class,
                        'storage' => $storage,
                        'request' => $request,
                    ]
                );
                $model = new DynamicModel(['one','two','three']);
                $model->addRule('one', 'string',['min'=>1]);
                $model->addRule('two', 'string',['max'=>100]);
                \Yii::$app->prefiller->fill(
                    $model,
                    new PrefillConfig([
                        'method' => 'get',
                        'serialized' => true
                                      ])
                );
                verify($model->one)->equals('alpha');
                verify($model->two)->equals('beta');
                verify($model->three)->equals('epsilon');
            }
        );
    }
    
}
