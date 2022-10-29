<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Content;

use Datatrics\Connect\Api\Content\SearchResultsInterface;
use Exception;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Datatrics\Connect\Api\Log\RepositoryInterface as LogRepository;
use Datatrics\Connect\Api\Content\DataInterface as ContentData;
use Datatrics\Connect\Model\Content\DataFactory as DataFactory;
use Datatrics\Connect\Api\Content\RepositoryInterface as ContentRepository;
use Datatrics\Connect\Api\Content\SearchResultsInterfaceFactory as SearchResultsFactory;
use Magento\Framework\Encryption\Encryptor;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;

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
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface|null
     */
    private $collectionProcessor;

    /**
     * @var LogRepository
     */
    private $logRepository;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * Repository constructor.
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ResourceModel $resource
     * @param DataFactory $dataFactory
     * @param LogRepository $logRepository
     * @param Encryptor $encryptor
     * @param ConfigRepository $configRepository
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        SearchResultsFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ResourceModel $resource,
        DataFactory $dataFactory,
        LogRepository $logRepository,
        Encryptor $encryptor,
        ConfigRepository $configRepository,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->resource = $resource;
        $this->dataFactory = $dataFactory;
        $this->logRepository = $logRepository;
        $this->encryptor = $encryptor;
        $this->configRepository = $configRepository;
        $this->collectionProcessor = $collectionProcessor ?: ObjectManager::getInstance()
            ->get(CollectionProcessorInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        return $this->dataFactory->create();
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
        } catch (Exception $exception) {
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
    public function save(
        ContentData $entity
    ): ContentData {
        try {
            $this->resource->save($entity);
        } catch (Exception $exception) {
            $this->logRepository->addErrorLog('Save content', $exception->getMessage());
            $exceptionMsg = self::COULD_NOT_SAVE_EXCEPTION;
            throw new CouldNotSaveException(__(
                $exceptionMsg,
                $exception->getMessage()
            ));
        }
        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function prepareContentData(array $productIds)
    {
        $connection = $this->resource->getConnection();
        $selectContent = $connection->select()->from(
            $this->resource->getTable('datatrics_content_store'),
            [
                'product_id',
                'store_id',
                'status'
            ]
        )->where('product_id in (?)', $productIds);
        $contents = $connection->fetchAll($selectContent);
        $toInvalidate = [];
        $skip = 0;
        $toAdd = array_diff($productIds, $connection->fetchCol($selectContent));
        foreach ($contents as $content) {
            if ($content['status'] != 0) {
                $toInvalidate[] = $content['product_id'];
            } else {
                $skip++;
            }
        }
        // invalidating
        $invalidated = $connection->update(
            $this->resource->getTable('datatrics_content_store'),
            ['status' => 0],
            ['product_id in (?)' => $toInvalidate]
        );

        //adding
        $added = $this->addContent($toAdd);
        return [
            'added' => $added,
            'invalidated' => $invalidated,
            'skipped' => $skip
        ];
    }

    /**
     * Collect products and add to tables
     *
     * @param array $productIds
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addContent($productIds)
    {
        $connection = $this->resource->getConnection();
        $selectStores = $connection->select()->from(
            $this->resource->getTable('store'),
            'store_id'
        );
        $stores = [];
        foreach ($connection->fetchAll($selectStores) as $store) {
            $stores[] = $store['store_id'];
        }
        $select = $connection->select()->from(
            $this->resource->getTable('catalog_product_entity'),
            'entity_id'
        )->joinLeft(
            ['super_link' => $this->resource->getTable('catalog_product_super_link')],
            'super_link.product_id =' . $this->resource->getTable('catalog_product_entity') . '.entity_id',
            [
                'parent_id' => 'GROUP_CONCAT(parent_id)'
            ]
        )->where('entity_id in (?)', $productIds)
            ->group('entity_id')->limit(self::LIMIT);
        $result = $connection->fetchAll($select);
        $count = 0;
        $this->resource->beginTransaction();
        $data = [];
        foreach ($result as $entity) {
            $count++;
            $content = $this->create();
            $content->setContentId($entity['entity_id'])
                ->setParentId((string)$entity['parent_id']);
            foreach ($stores as $store) {
                $data[] = [
                    $entity['entity_id'],
                    $store
                ];
            }
            $this->save($content);
        }
        if ($data) {
            $connection->insertArray(
                $this->resource->getTable('datatrics_content_store'),
                ['product_id', 'store_id'],
                $data
            );
        }
        $this->resource->commit();
        return $count;
    }
}
