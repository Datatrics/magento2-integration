<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Content;

use Datatrics\Connect\Api\Content\DataInterface as ContentData;
use Datatrics\Connect\Api\Content\RepositoryInterface as ContentRepository;
use Datatrics\Connect\Api\Content\SearchResultsInterface;
use Datatrics\Connect\Api\Content\SearchResultsInterfaceFactory as SearchResultsFactory;
use Datatrics\Connect\Api\Log\RepositoryInterface as LogRepository;
use Datatrics\Connect\Model\Content\DataFactory as DataFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Datatrics content repository class
 */
class Repository implements ContentRepository
{

    /**
     * Processing limit
     */
    public const LIMIT = 100000;

    /**
     * @var SearchResultsFactory
     */
    private $searchResultsFactory;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var ResourceModel
     */
    private $resource;
    /**
     * @var DataFactory
     */
    private $dataFactory;
    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param ResourceModel $resource
     * @param DataFactory $dataFactory
     * @param LogRepository $logRepository
     */
    public function __construct(
        SearchResultsFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        ResourceModel $resource,
        DataFactory $dataFactory,
        LogRepository $logRepository
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->dataFactory = $dataFactory;
        $this->logRepository = $logRepository;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        return $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria)
            ->setItems($collection->getItems())
            ->setTotalCount($collection->getSize());
    }

    /**
     * @inheritDoc
     */
    public function create(): ContentData
    {
        return $this->dataFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $entityId): bool
    {
        $entity = $this->get($entityId);
        return $this->delete($entity);
    }

    /**
     * @inheritDoc
     */
    public function get(int $entityId): ContentData
    {
        if (!$entityId) {
            $exceptionMsg = static::INPUT_EXCEPTION;
            throw new InputException(__($exceptionMsg));
        } elseif (!$this->resource->isExists($entityId)) {
            $exceptionMsg = self::NO_SUCH_ENTITY_EXCEPTION;
            throw new NoSuchEntityException(__($exceptionMsg, $entityId));
        }
        return $this->dataFactory->create()
            ->load($entityId);
    }

    /**
     * @inheritDoc
     */
    public function delete(ContentData $entity): bool
    {
        try {
            $this->resource->delete($entity);
        } catch (\Exception $exception) {
            $this->logRepository->addErrorLog('Delete content', $exception->getMessage());
            $exceptionMsg = self::COULD_NOT_DELETE_EXCEPTION;
            throw new CouldNotDeleteException(__(
                $exceptionMsg,
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(ContentData $entity): ContentData
    {
        try {
            $this->resource->save($entity);
        } catch (\Exception $exception) {
            $this->logRepository->addErrorLog('Save content', $exception->getMessage());
            $exceptionMsg = self::COULD_NOT_SAVE_EXCEPTION;
            throw new CouldNotSaveException(__(
                $exceptionMsg,
                $exception->getMessage()
            ));
        }
        return $entity;
    }
}
