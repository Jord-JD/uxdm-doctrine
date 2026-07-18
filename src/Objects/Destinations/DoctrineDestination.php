<?php

namespace JordJD\uxdm\Objects\Destinations;

use JordJD\uxdm\Interfaces\DestinationInterface;
use JordJD\uxdm\Objects\DataRow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DoctrineDestination implements DestinationInterface
{
    private $entityManager;
    private $entityRepository;
    private $entityClassName;
    private $propertyAccessor;

    public function __construct(EntityManagerInterface $entityManager, $entityClassName)
    {
        $this->entityManager = $entityManager;
        $this->entityRepository = $this->entityManager->getRepository($entityClassName);
        $this->entityClassName = $entityClassName;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    private function alreadyExists(array $keyDataItems)
    {
        $criteria = [];

        foreach ($keyDataItems as $keyDataItem) {
            $criteria[$keyDataItem->fieldName] = $keyDataItem->value;
        }

        if (method_exists($this->entityRepository, 'count')) {
            return $this->entityRepository->count($criteria) > 0;
        } else {
            return $this->entityRepository->findOneBy($criteria) !== null;
        }
    }

    private function insertDataRow(DataRow $dataRow)
    {
        $dataItems = $dataRow->getDataItems();

        $className = $this->entityClassName;
        $newRecord = new $className();

        foreach ($dataItems as $dataItem) {
            $this->propertyAccessor->setValue($newRecord, $dataItem->fieldName, $dataItem->value);
        }

        $this->entityManager->persist($newRecord);
    }

    private function updateDataRow(DataRow $dataRow)
    {
        $dataItems = $dataRow->getDataItems();
        $keyDataItems = $dataRow->getKeyDataItems();

        $criteria = [];

        foreach ($keyDataItems as $keyDataItem) {
            $criteria[$keyDataItem->fieldName] = $keyDataItem->value;
        }

        $record = $this->entityRepository->findOneBy($criteria);

        foreach ($dataItems as $dataItem) {
            $this->propertyAccessor->setValue($record, $dataItem->fieldName, $dataItem->value);
        }

    }

    public function putDataRows(array $dataRows): void
    {
        foreach ($dataRows as $dataRow) {
            $keyDataItems = $dataRow->getKeyDataItems();

            if (!$keyDataItems) {
                $this->insertDataRow($dataRow);
                continue;
            }

            if ($this->alreadyExists($keyDataItems)) {
                $this->updateDataRow($dataRow);
            } else {
                $this->insertDataRow($dataRow);
            }
        }

        $this->entityManager->flush();
    }

    public function finishMigration(): void
    {
    }
}
