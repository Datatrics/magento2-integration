<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Model\Profile;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Model\AbstractModel;
use Datatrics\Connect\Api\Profile\DataInterface as ProfileData;

/**
 * Datatrics profile data class
 */
class Data extends AbstractModel implements ExtensibleDataInterface, ProfileData
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getProfileId(): string
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProfileId(string $profileId): ProfileData
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): int
    {
        return (int)$this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(int $customerId): ProfileData
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getAddressId(): int
    {
        return (int)$this->getData(self::ADDRESS_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAddressId(int $addressId): ProfileData
    {
        return $this->setData(self::ADDRESS_ID, $addressId);
    }

    /**
     * @inheritDoc
     */
    public function getStoreId(): int
    {
        return (int)$this->getData(self::STORE_ID);
    }

    /**
     * @inheritDoc
     */
    public function setStoreId(int $storeId): ProfileData
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): ProfileData
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt(): string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt(string $updatedAt): ProfileData
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdateMsg(): string
    {
        return $this->getData(self::UPDATE_MSG);
    }

    /**
     * @inheritDoc
     */
    public function setUpdateMsg(string $updateMsg): ProfileData
    {
        return $this->setData(self::UPDATE_MSG, $updateMsg);
    }

    /**
     * @inheritDoc
     */
    public function getUpdateAttempts(): string
    {
        return $this->getData(self::UPDATE_ATTEMPTS);
    }

    /**
     * @inheritDoc
     */
    public function setUpdateAttempts(int $updateAttempts): ProfileData
    {
        return $this->setData(self::UPDATE_ATTEMPTS, $updateAttempts);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(string $status): ProfileData
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function setFirstname(string $firstname): ProfileData
    {
        return $this->setData(self::FIRSTNAME, $firstname);
    }

    /**
     * @inheritDoc
     */
    public function setLastname(string $lastname): ProfileData
    {
        return $this->setData(self::LASTNAME, $lastname);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): ProfileData
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function setEmail(string $email): ProfileData
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritDoc
     */
    public function setCompany(string $company): ProfileData
    {
        return $this->setData(self::COMPANY, $company);
    }

    /**
     * @inheritDoc
     */
    public function setAddress(string $address): ProfileData
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * @inheritDoc
     */
    public function setCountry(string $country): ProfileData
    {
        return $this->setData(self::COUNTRY, $country);
    }

    /**
     * @inheritDoc
     */
    public function setCity(string $city): ProfileData
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @inheritDoc
     */
    public function setZip(string $zip): ProfileData
    {
        return $this->setData(self::ZIP, $zip);
    }

    /**
     * @inheritDoc
     */
    public function setPhone(string $phone): ProfileData
    {
        return $this->setData(self::PHONE, $phone);
    }

    /**
     * @inheritDoc
     */
    public function setRegion(string $region): ProfileData
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * @inheritDoc
     */
    public function setStreet(string $street): ProfileData
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * @inheritDoc
     */
    public function setPrefix(string $prefix): ProfileData
    {
        return $this->setData(self::PREFIX, $prefix);
    }

    /**
     * @inheritDoc
     */
    public function getFirstname(): string
    {
        return $this->getData(self::FIRSTNAME);
    }

    /**
     * @inheritDoc
     */
    public function getLastname(): string
    {
        return $this->getData(self::LASTNAME);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function getEmail(): string
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function getCompany(): string
    {
        return $this->getData(self::COMPANY);
    }

    /**
     * @inheritDoc
     */
    public function getAddress(): string
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * @inheritDoc
     */
    public function getCountry(): string
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * @inheritDoc
     */
    public function getCity(): string
    {
        return $this->getData(self::CITY);
    }

    /**
     * @inheritDoc
     */
    public function getZip(): string
    {
        return $this->getData(self::ZIP);
    }

    /**
     * @inheritDoc
     */
    public function getPhone(): string
    {
        return $this->getData(self::PHONE);
    }

    /**
     * @inheritDoc
     */
    public function getRegion(): string
    {
        return $this->getData(self::REGION);
    }

    /**
     * @inheritDoc
     */
    public function getStreet(): string
    {
        return $this->getData(self::STREET);
    }

    /**
     * @inheritDoc
     */
    public function getPrefix(): string
    {
        return $this->getData(self::PREFIX);
    }
}
