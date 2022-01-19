<?php

namespace Akeneo\Bundle\BatchBundle\Doctrine;


use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base remover, declared as different services for different classes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseRemover implements RemoverInterface, BulkRemoverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $removedClass;

    /**
     * @param ObjectManager                    $objectManager
     * @param EventDispatcherInterface         $eventDispatcher
     * @param string                           $removedClass
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        $removedClass
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->removedClass = $removedClass;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        $this->validateObject($object);

        $objectId = $object->getId();

        $options['unitary'] = true;

        $this->objectManager->remove($object);
        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = [])
    {
        if (empty($objects)) {
            return;
        }

        $options['unitary'] = false;

        $removedObjects = [];
        foreach ($objects as $object) {
            $this->validateObject($object);
            $removedObjects[$object->getId()] = $object;
            $this->objectManager->remove($object);
        }

        $this->objectManager->flush();
    }

    /**
     * @param $object
     */
    protected function validateObject($object)
    {
        if (!$object instanceof $this->removedClass) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    $this->removedClass,
                    ClassUtils::getClass($object)
                )
            );
        }
    }
}
