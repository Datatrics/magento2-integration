<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Sales;

use Datatrics\Connect\Api\Config\System\SalesInterface as SalesConfigRepository;
use Datatrics\Connect\Api\Log\RepositoryInterface as LogRepository;
use Datatrics\Connect\Api\Sales\DataInterface as SalesData;
use Datatrics\Connect\Api\Sales\RepositoryInterface as SalesRepository;
use Datatrics\Connect\Api\Sales\SearchResultsInterface;
use Datatrics\Connect\Api\Sales\SearchResultsInterfaceFactory as SearchResultsFactory;
use Datatrics\Connect\Model\Sales\DataFactory as DataFactory;
use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;

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
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var Encryptor
     */
    private $encryptor;
    /**
     * @var SalesConfigRepository
     */
    private $salesConfigRepository;
    /**
     * @var Json
     */
    private $json;

    /**
     * Repository constructor.
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param ResourceModel $resource
     * @param DataFactory $dataFactory
     * @param LogRepository $logRepository
     * @param Encryptor $encryptor
     * @param SalesConfigRepository $salesConfigRepository
     * @param Json $json
     */
    public function __construct(
        SearchResultsFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        ResourceModel $resource,
        DataFactory $dataFactory,
        LogRepository $logRepository,
        Encryptor $encryptor,
        SalesConfigRepository $salesConfigRepository,
        Json $json
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->dataFactory = $dataFactory;
        $this->logRepository = $logRepository;
        $this->encryptor = $encryptor;
        $this->salesConfigRepository = $salesConfigRepository;
        $this->json = $json;
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
    public function create(): SalesData
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
    public function get(int $entityId): SalesData
    {
        if (!$entityId) {
            $exceptionMsg = static::INPUT_EXCEPTION;
            throw new InputException(__($exceptionMsg));
        } elseif (!$this->resource->isExists($entityId)) {
            $exceptionMsg = self::NO_SUCH_ENTITY_EXCEPTION;
            throw new NoSuchEntityException(__($exceptionMsg, $entityId));
        }

        return $this->dataFactory->create()->load($entityId);
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
    public function prepareSaleData(Order $order): bool
    {
        $storeId = (int)$order->getStoreId();
        if (!$this->salesConfigRepository->isEnabled($storeId)) {
            return false;
        }
        if ($this->salesConfigRepository->getSyncStateRestriction($storeId)) {
            if (!in_array($order->getState(), $this->salesConfigRepository->getSyncState($storeId))) {
                return false;
            }
        }
        if ($this->salesConfigRepository->getSyncCustomerRestriction($storeId)) {
            if (!in_array($order->getCustomerGroupId(), $this->salesConfigRepository->getSyncCustomerGroup($storeId))) {
                return false;
            }
        }
        if ($this->resource->getIdByOrder($order->getId())) {
            return false;
        }
        $profileId = $this->encryptor->getHash(
            $order->getCustomerEmail(),
            $this->salesConfigRepository->getProjectId($storeId)
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
                'itemid' => $item->getSku(),
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
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function save(SalesData $entity): SalesData
    {
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
}
