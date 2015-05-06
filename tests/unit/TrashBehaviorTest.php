<?php
/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 01.05.15
 * Time: 0:02
 */

use Codeception\Util\Debug;

class TrashBehaviorTest extends \yii\codeception\TestCase
{
    public $appConfig = '@tests/unit/_config.php';

    /**
     * @var Connection test db connection
     */
    protected $dbConnection;

    public static function setUpBeforeClass()
    {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite')) {
            static::markTestSkipped('PDO and SQLite extensions are required.');
        }
    }
    public function setUp()
    {
        $this->mockApplication(\yii\helpers\ArrayHelper::merge(
            require(Yii::getAlias($this->appConfig)),[
                'components' => [
                    'db' => [
                        'class' => '\yii\db\Connection',
                        'dsn' => 'sqlite::memory:',
                    ]
                ]
            ]));
        $columns = [
            'id' => 'pk',
            'removed' => 'integer'
        ];
        Yii::$app->getDb()->createCommand()->createTable('test_auto_trash', $columns)->execute();

        for($i=1; $i<4; $i++){
            $field = new ActiveRecordTrash();
            $field->id=$i;
            $field->removed = 0;
            $field->save();
        }
    }
    public function tearDown()
    {
        Yii::$app->getDb()->createCommand()->delete('test_auto_trash')->execute();
        Yii::$app->getDb()->close();
        parent::tearDown();
    }
    // Tests :
    public function testMarkRecord()
    {
        $model = ActiveRecordTrash::findOne(['id'=>1]);
        $model->delete();

        $this->assertTrue($model->removed==1);
        $this->assertTrue($model->isRemoved);
        $this->assertFalse(is_null(ActiveRecordTrash::find(['id'=>1])));
    }

    /**
     * @depends testMarkRecord
     */
    public function testRestoreRecord(){
        $model = ActiveRecordTrash::findOne(['id'=>1]);
        $model->restore();

        $this->assertTrue($model->removed==0);
        $this->assertFalse($model->isRemoved);
        $this->assertFalse(is_null(ActiveRecordTrash::findOne(['id'=>1])));
    }
    /**
     * @depends testRestoreRecord
     */
    public function testDeleteRecord()
    {
        $model = ActiveRecordTrash::findOne(['id'=>1]);

        $model->delete();
        $this->assertTrue($model->isRemoved);

        $model->delete();
        $this->assertTrue(is_null(ActiveRecordTrash::findOne(['id'=>1])));
    }

    public function testFullTrashAttribute(){
        $model = new ActiveRecordTrash();

        $this->assertTrue($model->fullTrashAttribute()==['removed'=>0]);
        $this->assertTrue($model->fullTrashAttribute(true)==['removed'=>1]);
    }

    /**
     * @depends testDeleteRecord
     */
    public function testFindWithoutRemoved()
    {
        $countBefore = ActiveRecordTrash::find()->count();

        $model = ActiveRecordTrash::findOne(['id'=>2]);

        $model->delete();

        $countAfter = ActiveRecordTrash::find()->count();

        $countRemoved = ActiveRecordTrash::find()->onlyRemoved()->count();

        $this->assertTrue($countRemoved>0);
        $this->assertTrue($countBefore>$countAfter);
    }
}

/**
 * Test Active Record class with [[TrashBehavior]] behavior attached.
 *
 * @property integer $id
 * @property integer $created_at
 * @property integer $updated_at
 */
class ActiveRecordTrash extends yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            \sibds\behaviors\TrashBehavior::className(),
        ];
    }

    public static function tableName()
    {
        return 'test_auto_trash';
    }

    public static function find(){
        return (new \sibds\behaviors\TrashQuery(get_called_class()))->findRemoved();
    }
}