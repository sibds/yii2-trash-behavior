<?php
/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 01.05.15
 * Time: 17:49
 */

namespace sibds\behaviors;


use yii\behaviors\AttributeBehavior;
use yii\helpers\ArrayHelper;

/**
 * Class TrashQuery
 * @package sibds\behaviors
 * @since 0.1
 */
class TrashQueryBehavior extends AttributeBehavior
{
    public $showRemoved = false;

    public function findRemoved() {
        return $this->filterRemoved($this->showRemoved);
    }

    public function onlyRemoved() {
        $this->showRemoved = true;
        return $this->findRemoved();
    }

    public function withRemoved() {
        $model = new $this->owner->modelClass();

        return $this->owner->where(ArrayHelper::merge($model->fullTrashAttribute(true), $model->fullTrashAttribute(false)));
    }

    public function onlyActive() {
        $this->showRemoved = false;
        return $this->findRemoved();
    }
    /*
    public function events(){
        return [
            ActiveQuery::EVENT_INIT => 'filterRemoved',
        ];
    }
    */
    public function filterRemoved($removed = false) {
        $model = new $this->owner->modelClass();

        return $this->owner->where($model->fullTrashAttribute($removed));
        //return $this->owner;
    }


} 