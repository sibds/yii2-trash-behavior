<?php
/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 30.04.15
 * Time: 22:07
 */

namespace sibds\behaviors;

use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class TrashBehavior
 * @package sibds\behaviors
 * @author Vadim Mazur <mazurva@gmail.com>
 * @since 0.1
 */
class TrashBehavior extends AttributeBehavior
{
    /**
     * @var string The name of the table where data stored.
     * Defaults to "removed".
     */
    public $trashAttribute = 'removed';
    /**
     * @var mixed The value to set for removed model.
     * Default is 1.
     */
    public $removedFlag=1;
    /**
     * @var mixed The value to set for restored model.
     * Default is 0.
     */
    public $restoredFlag=0;

    /**
     * @var callable the value that will be assigned to the attributes. This should be a valid
     * PHP callable whose return value will be assigned to the current attribute(s).
     */
     public $value;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_DELETE => $this->trashAttribute,
            ];
        }
    }

    public function events(){
        return ArrayHelper::merge([
            ActiveRecord::EVENT_BEFORE_INSERT => 'checkNewRecord',
        ], parent::events());
    }

    public function checkNewRecord($events){
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;

        if($owner->hasAttribute($this->trashAttribute))
            if(empty($owner->{$this->trashAttribute})||is_null($owner->{$this->trashAttribute}))
                $owner->{$this->trashAttribute} = $this->restoredFlag;
    }

    protected function getValue($event)
    {
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;

        if ($this->value === null) {
            if($owner->{$this->trashAttribute}==$this->restoredFlag)
            {
                $event->isValid = false;
                $owner->{$this->trashAttribute}=$this->removedFlag;
                $owner->save(false);

                return $this->removedFlag;
            }else{
                return true;
            }
            return false;
        } else {
            return call_user_func($this->value, $event);
        }
    }

    public function restore()
    {
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;

        $owner->{$this->trashAttribute}=$this->restoredFlag;

        $owner->save(false);
    }

    public function getIsRemoved(){
        return $this->owner->{$this->trashAttribute}==$this->removedFlag;
    }

    public function trashAttribute(){
        $owner = $this->owner->className();

        return $owner::tableName().'.'.$this->trashAttribute;
    }

    public function fullTrashAttribute($removed=false){
        return [$this->trashAttribute()=>($removed?$this->removedFlag:$this->restoredFlag)];
    }
}
