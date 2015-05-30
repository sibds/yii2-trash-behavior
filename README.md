# yii2-trash-behavior
AR models behavior that allows to mark models as deleted and then restore them.

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
$ composer require sibds/yii2-trash-behavior
```

or add

```
"sibds/yii2-trash-behavior": "*"
```

to the `require` section of your `composer.json` file.

## How to use?

Paste in the model code:
```php
  public function behaviors()
  {
      return [
          \sibds\behaviors\TrashBehavior::className(),
      ];
  }
```

To mark a record for deletion, call function `delete`:

```php
  $model->delete();
```
The second call `delete`, delete the record.

To restore record:
```php
  $model->restore();
```

For the correct selection of records in the model, it is necessary to redefine the function of `find`:
```php
  public static function find()
  {
      return (new \sibds\behaviors\TrashQuery(get_called_class()))->findRemoved();
  }
```
Example of use:
```php
  $posts = Post::find()->all();
```
Selecting only marked for deletion of records:
```php
  $count = Post::find()->onlyRemoved()->count();
```
