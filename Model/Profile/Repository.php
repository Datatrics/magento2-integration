<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Profile;

use Datatrics\Connect\Api\Profile\SearchResultsInterface;
use Exception;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Data\Customer as DataCustomer;
use Magento\Sales\Model\Order;
use Datatrics\Connect\Api\Log\RepositoryInterface as LogRepository;
use Datatrics\Connect\Api\Profile\DataInterface as ProfileData;
use Datatrics\Connect\Model\Profile\DataFactory as DataFactory;
use Datatrics\Connect\Api\Profile\RepositoryInterface as ProfileRepository;
use Datatrics\Connect\Api\Profile\SearchResultsInterfaceFactory as SearchResultsFactory;
use Magento\Framework\Encryption\Encryptor;
use Datatrics\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Magento\Customer\Model\Address;

/**
 * Datatrics profile repository class
 */
class Repository implements ProfileRepository
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
     * @var Customer
     */
    private $customer;

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
     * @param Customer $customer
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
        Customer $customer,
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
        $this->customer = $customer;
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
    public function get(int $entityId): ProfileData
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
    public function delete(ProfileData $entity): bool
    {
        try {
            $this->resource->delete($entity);
        } catch (Exception $exception) {
            $this->logRepository->addErrorLog('Delete profile', $exception->getMessage());
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
        ProfileData $entity
    ): ProfileData {
        try {
            $this->resource->save($entity);
        } catch (Exception $exception) {
            $this->logRepository->addErrorLog('Save profile', $exception->getMessage());
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
    public function prepareProfileData($customer, bool $forceUpdate = false)
    {

        $customer = $this->customer->load($customer->getId());
        $storeId = (int)$customer->getStoreId();
        if (!$this->configRepository->getCustomerSyncEnabled()) {
            return 0;
        }
        if ($this->configRepository->getCustomerSyncRestriction()) {
            $customerGroups = explode(',', $this->configRepository->getCustomerSyncGroup());
            if (!in_array($customer->getGroupId(), $customerGroups)) {
                return 0;
            }
        }
        $profileId = $this->encryptor->getHash(
            $customer->getEmail(),
            $this->configRepository->getProjectId($storeId)
        );
        if ($this->resource->isExists($customer->getId(), 'customer_id')) {
            if (!$forceUpdate) {
                return 0;
            }
        }
        if ($entityId = $this->resource->getIdByProfile($profileId)) {
            try {
                $profile = $this->get($entityId);
            } catch (\Exception $e) {
                return 0;
            }
        } else {
            $profile = $this->create()
                ->setProfileId((string)$profileId)
                ->setCustomerId((int)$customer->getId());
        }
        $profile->setStoreId($storeId)
            ->setStatus(self::STATUS['queued']);
        $profile->setData(
            array_merge(
                $profile->getData(),
                $this->collectAddressData($customer)
            )
        );
        /** @phpstan-ignore-next-line */
        if ($customer->getDefaultBilling() && $customer->getDefaultBillingAddress()) {
            $profile->setAddressId((int)$customer->getDefaultBillingAddress()->getId());
        } else {
            $profile->setAddressId(0);
        }
        $this->logRepository->addDebugLog('Customer', 'ID ' . $customer->getId() . ' invalidated');
        try {
            $this->save($profile);
        } catch (\Exception $e) {
            return 0;
        }
        return 1;
    }

    /**
     * @inheritDoc
     */
    public function prepareGuestProfileData(Order $order)
    {
        $storeId = (int)$order->getStoreId();
        if (!$this->configRepository->getCustomerSyncEnabled($storeId)) {
            return 0;
        }
        $profileId = $this->encryptor->getHash(
            $order->getCustomerEmail(),
            $this->configRepository->getProjectId($storeId)
        );
        if ($this->resource->getIdByProfile($profileId)) {
            return 0;
        } else {
            $profile = $this->create()
                ->setProfileId((string)$profileId);
        }
        $profile->setStoreId($storeId)
            ->setStatus(self::STATUS['queued']);
        $profile->setAddressId(
            (int)$order->getBillingAddress()->getId()
        );
        $profile->setData(
            array_merge(
                $this->collectGuestAddressData($order),
                $profile->getData()
            )
        );
        try {
            $this->save($profile);
        } catch (\Exception $e) {
            return 0;
        }
        return 1;
    }

    /**
     * @param DataCustomer|Customer $customer
     * @return array
     */
    private function collectAddressData($customer): array
    {
        /** @phpstan-ignore-next-line */
        if (!$customer->getDefaultBilling() || !$customer->getDefaultBillingAddress()) {
            return [];
        }
        $address = $customer->getDefaultBillingAddress();
        $data = [
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'prefix' => $address->getPrefix(),
            'name' => $this->formatName($address),
            'email' => $customer->getEmail(),
            'company' => $address->getCompany(),
            'country' => $address->getCountry(),
            'city' => $address->getCity(),
            'zip' => $address->getPostcode(),
            'phone' => $address->getTelephone(),
            'region' => $address->getRegion(),
            'street' => $address->getStreetFull(),
            'address' => $this->formatAddress($address)
        ];
        return $data;
    }

    /**
     * @param Order $order
     * @return array
     */
    private function collectGuestAddressData(Order $order): array
    {
        $address = $order->getBillingAddress();
        $data = [
            'firstname' => $address->getFirstname(),
            'lastname' => $address->getLastname(),
            'prefix' => $address->getPrefix(),
            'name' => $this->formatName($address),
            'email' => $order->getCustomerEmail(),
            'company' => $address->getCompany(),
            'country' => $address->getCountry(),
            'city' => $address->getCity(),
            'zip' => $address->getPostcode(),
            'phone' => $address->getTelephone(),
            'region' => $address->getRegion(),
            'street' => $address->getStreetFull(),
            'address' => $this->formatAddress($address)
        ];
        return $data;
    }

    /**
     * @param \Magento\Customer\Model\Address $address
     * @return string
     */
    private function formatName($address)
    {
        $nameData = [
            (string)$address->getPrefix(),
            (string)$address->getFirstname(),
            (string)$address->getMiddlename(),
            (string)$address->getLastname()
        ];
        $name = array_filter(
            $nameData,
            function ($value) {
                return $value !== '';
            }
        );
        return implode(' ', $name);
    }

    /**
     * @param \Magento\Customer\Model\Address $address
     * @return string
     */
    private function formatAddress($address)
    {
        $addressData = [
            (string)$address->getStreetFull(),
            (string)$address->getPostcode(),
            (string)$address->getRegion(),
            (string)$address->getCountry()
        ];
        $addressFormated = array_filter(
            $addressData,
            function ($value) {
                return $value !== '';
            }
        );
        return implode(', ', $addressFormated);
    }
}
