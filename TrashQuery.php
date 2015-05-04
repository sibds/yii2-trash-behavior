<?php
/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 02.05.15
 * Time: 2:40
 */

namespace sibds\behaviors;


use yii\db\ActiveQuery;

class TrashQuery extends ActiveQuery {
    public function behaviors(){
        return [
            TrashQueryBehavior::className(),
        ];
    }
} 