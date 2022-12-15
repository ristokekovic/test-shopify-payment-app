<?php

namespace Logeecom\Tests\Infrastructure\ORM;

use Logeecom\Infrastructure\ORM\IntermediateObject;
use Logeecom\Infrastructure\ORM\Utility\EntityTranslator;
use Logeecom\Infrastructure\Serializer\Serializer;
use Logeecom\Infrastructure\TaskExecution\Interfaces\Priority;
use Logeecom\Infrastructure\TaskExecution\QueueItem;
use Logeecom\Infrastructure\TaskExecution\TaskRunnerStatus;
use Logeecom\Tests\Infrastructure\Common\BaseInfrastructureTestWithServices;

/**
 * Class EntityTranslatorTest.
 *
 * @package Logeecom\Tests\Infrastructure\ORM
 */
class EntityTranslatorTest extends BaseInfrastructureTestWithServices
{
    /**
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\EntityClassException
     * @throws \Exception
     */
    public function testTranslate()
    {
        $entity = new QueueItem();
        $entity->setStatus('created');
        $entity->setId(123);
        $entity->setCreateTimestamp(time());
        $entity->setLastUpdateTimestamp(time());
        $entity->setFailTimestamp(time());
        $entity->setFinishTimestamp(time());
        $entity->setPriority(Priority::LOW);

        $intermediate = new IntermediateObject();
        $data = $entity->toArray();
        $data['class_name'] = $entity::getClassName();
        $data = json_encode($data);
        $intermediate->setData($data);

        $translator = new EntityTranslator();
        $translator->init(QueueItem::getClassName());
        $entities = $translator->translate(array($intermediate));

        $this->assertEquals($entity, $entities[0]);
    }

    /**
     * @expectedException \Logeecom\Infrastructure\ORM\Exceptions\EntityClassException
     */
    public function testTranslateWithoutInit()
    {
        $intermediate = new IntermediateObject();
        $translator = new EntityTranslator();
        $translator->translate(array($intermediate));
    }

    /**
     * @expectedException \Logeecom\Infrastructure\ORM\Exceptions\EntityClassException
     */
    public function testInitOnNonEntity()
    {
        $translator = new EntityTranslator();
        $translator->init('\Logeecom\Infrastructure\ORM\IntermediateObject');
    }

    /**
     * @expectedException \Logeecom\Infrastructure\ORM\Exceptions\EntityClassException
     */
    public function testTranslateWrongEntity()
    {
        $entity = new TaskRunnerStatus('Test', 123);

        $intermediate = new IntermediateObject();
        $intermediate->setData(Serializer::serialize($entity));

        $translator = new EntityTranslator();
        $translator->init(QueueItem::getClassName());
        $translator->translate(array($intermediate));
    }
}
