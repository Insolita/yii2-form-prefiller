Form assistant
==============
Remember user-filled form data, prefill forms with remembered data
Usefully for remember filter/sorting preferences, or make some sticky attributes

![Status](https://travis-ci.org/Insolita/yii2-form-prefiller.svg?branch=master)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist insolita/yii2-form-prefiller "~0.0.1"
```

or add

```
"insolita/yii2-form-prefiller": "~0.0.1"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, configure component  :

```php
'components'=>[
    'prefiller' => [
            'class'   => \insolita\prefiller\FormPrefiller::class,
            'storage' => [
            //one of storage types - db,session,cookie,redis supported
                'class'    => \insolita\prefiller\storages\CookieStorage::class,
            ],
        ],
]

```

Support ActiveRecord models,yii\base\Model,DummyModel

Remember $model data in storage

```
   Yii::$app->prefiller->persist($model, new PrefillConfig([]));

```
Fill $model from storage

```
  Yii::$app->prefiller->fillFromStorage($model, new PrefillConfig([
       'skipFromStorageAttributes'=>['someAttr'],
       'validateStorage'=>false
  ]));

```

Fill model from request->fillFromStorage->fillDefaults->persist if request update model

```
  $filter = new DummyModel(['page','sort','sortOrder','searchQuery','viewMode']);

  Yii::$app->prefiller->fill($filter, new PrefillConfig([
       'method'=>'get',
       'defaults'=>['sort'=>'price','sortOrder'=>'desc','viewMode'=>'grid']
       'skipFromRequestAttributes'=>['searchQuery'],
       'validateRequest'=>true
  ]));

```

