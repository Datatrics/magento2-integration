<?php
/**
 * Copyright © Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Datatrics\Connect\Api\Profile;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for Datatrics profile
 * @api
 */
interface DataInterface extends ExtensibleDataInterface
{

    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = 'entity_id';
    public const STORE_ID = 'store_id';
    public const PROFILE_ID = 'profile_id';
    public const CUSTOMER_ID = 'customer_id';
    public const ADDRESS_ID = 'address_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const UPDATE_MSG = 'update_msg';
    public const UPDATE_ATTEMPTS = 'update_attempts';
    public const STATUS = 'status';
    public const FIRSTNAME = 'firstname';
    public const LASTNAME = 'lastname';
    public const NAME = 'name';
    public const EMAIL = 'email';
    public const COMPANY = 'company';
    public const ADDRESS = 'address';
    public const COUNTRY = 'country';
    public const CITY = 'city';
    public const ZIP = 'zip';
    public const PHONE = 'phone';
    public const REGION = 'region';
    public const STREET = 'street';
    public const PREFIX = 'prefix';
    /**#@-*/

    /**
     * @return string
     */
    public function getProfileId() : string;

    /**
     * @param string $profileId
     * @return $this
     */
    public function setProfileId(string $profileId) : self;

    /**
     * @return int
     */
    public function getCustomerId() : int;

    /**
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId) : self;

    /**
     * @return int
     */
    public function getAddressId() : int;

    /**
     * @param int $addressId
     * @return $this
     */
    public function setAddressId(int $addressId) : self;

    /**
     * @return int
     */
    public function getStoreId() : int;

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId(int $storeId) : self;

    /**
     * @return string
     */
    public function getCreatedAt() : string;

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt) : self;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt) : self;

    /**
     * @return string
     */
    public function getUpdateMsg() : string;

    /**
     * @param string $updateMsg
     * @return $this
     */
    public function setUpdateMsg(string $updateMsg) : self;

    /**
     * @return string
     */
    public function getUpdateAttempts() : string;

    /**
     * @param int $updateAttempts
     * @return $this
     */
    public function setUpdateAttempts(int $updateAttempts) : self;

    /**
     * @return string
     */
    public function getStatus() : string;

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status) : self;

    /**
     * @param string $firstname
     * @return $this
     */
    public function setFirstname(string $firstname) : self;

    /**
     * @param string $lastname
     * @return $this
     */
    public function setLastname(string $lastname) : self;

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name) : self;

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email) : self;

    /**
     * @param string $company
     * @return $this
     */
    public function setCompany(string $company) : self;

    /**
     * @param string $address
     * @return $this
     */
    public function setAddress(string $address) : self;

    /**
     * @param string $country
     * @return $this
     */
    public function setCountry(string $country) : self;

    /**
     * @param string $city
     * @return $this
     */
    public function setCity(string $city) : self;

    /**
     * @param string $zip
     * @return $this
     */
    public function setZip(string $zip) : self;

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone(string $phone) : self;

    /**
     * @param string $region
     * @return $this
     */
    public function setRegion(string $region) : self;

    /**
     * @param string $street
     * @return $this
     */
    public function setStreet(string $street) : self;

    /**
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix) : self;

    /**
     * @return string
     */
    public function getFirstname() : string;

    /**
     * @return string
     */
    public function getLastname() : string;

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @return string
     */
    public function getEmail() : string;

    /**
     * @return string
     */
    public function getCompany() : string;

    /**
     * @return string
     */
    public function getAddress() : string;

    /**
     * @return string
     */
    public function getCountry() : string;

    /**
     * @return string
     */
    public function getCity() : string;

    /**
     * @return string
     */
    public function getZip() : string;

    /**
     * @return string
     */
    public function getPhone() : string;

    /**
     * @return string
     */
    public function getRegion() : string;

    /**
     * @return string
     */
    public function getStreet() : string;

    /**
     * @return string
     */
    public function getPrefix() : string;
}
