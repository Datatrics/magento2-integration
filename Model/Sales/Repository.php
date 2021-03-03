<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Sales;

use Datatrics\Connect\Api\Sales\SearchResultsInterface;
use Exception;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Datatrics\Connect\Api\Log\RepositoryInterface as LogRepository;
use Datatrics\Connect\Api\Sales\DataInterface as SalesData;
use Datatrics\Connect\Model\Sales\DataFactory as DataFactory;
use Datatrics\Connect\Api\Sales\RepositoryInterface as SalesRepository;
use Datatrics\Connect\Api\Sales\SearchResultsInterfaceFactory as SearchResultsFactory;
use Magento\Framework\Encryption\Encryptor;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Datatrics sales repository class
 */
class Repository implements SalesRepository
{

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
     * @var Json
     */
    private $json;

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
     * @param Json $json
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
        Json $json,
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
        $this->json = $json;
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
    public function get(int $entityId): SalesData
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
    public function delete(SalesData $entity): bool
    {
        try {
            $this->resource->delete($entity);
        } catch (Exception $exception) {
            $this->logRepository->addErrorLog('Delete datatrics sales', $exception->getMessage());
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
        SalesData $entity
    ): SalesData {
        try {
            $this->resource->save($entity);
        } catch (Exception $exception) {
            $this->logRepository->addErrorLog('Save datatrics sales', $exception->getMessage());
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
    public function prepareSaleData(Order $order)
    {
        $storeId = (int)$order->getStoreId();
        if (!$this->configRepository->getOrderSyncEnabled($storeId)) {
            return 0;
        }
        if ($this->configRepository->getOrderSyncStateRestriction($storeId)) {
            $states = explode(',', $this->configRepository->getOrderSyncState($storeId));
            if (!in_array($order->getState(), $states)) {
                return 0;
            }
        }
        if ($this->configRepository->getOrderSyncCustomerRestriction($storeId)) {
            $customerGroups = explode(
                ',',
                $this->configRepository->getOrderSyncCustomerGroup($storeId)
            );
            if (!in_array($order->getCustomerGroupId(), $customerGroups)) {
                return 0;
            }
        }
        if ($this->resource->getIdByOrder($order->getId())) {
            return 0;
        }
        $profileId = $this->encryptor->getHash(
            $order->getCustomerEmail(),
            $this->configRepository->getProjectId($storeId)
        );
        $sale = $this->create();
        $sale->setOrderId((int)$order->getId())
            ->setStoreId($storeId)
            ->setStatus(self::STATUS['queued'])
            ->setEmail($order->getCustomerEmail())
            ->setTotal((float)$order->getGrandTotal())
            ->setProfileId($profileId);
        $items = [];
        foreach ($order->getAllItems() as $item) {
            $items[] = [
                'itemid' => $item->getId(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'quantity' => $item->getQtyOrdered(),
                'total' => $item->getRowTotal()
            ];
        }
        $sale->setItems($this->json->serialize($items));
        try {
            $this->save($sale);
            $this->logRepository->addDebugLog('Customer', 'ID ' . $order->getId() . ' invalidated');
        } catch (\Exception $e) {
            $this->logRepository->addErrorLog('Customer', $e->getMessage());
            return 0;
        }
        return 1;
    }
}
