<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Marketplace\Rewirte\Encryption;

use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Encryption\Adapter\EncryptionAdapterInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Encryption\Adapter\SodiumChachaIetf;
use Magento\Framework\Encryption\Adapter\Mcrypt;

/**
 * Class Encryptor provides basic logic for hashing strings and encrypting/decrypting misc data
 */
class Encryptor extends \Magento\Framework\Encryption\Encryptor
{
    /**
     * Key of sha256 algorithm
     */
    const HASH_VERSION_SHA1 = 2;

    /**
     * Key of md5 algorithm
     */
    const HASH_VERSION_MD5 = 0;

    /**
     * Key of sha256 algorithm
     */
    const HASH_VERSION_SHA256 = 1;

    

    /**
     * Key of latest used algorithm
     * @deprecated
     * @see \Magento\Framework\Encryption\Encryptor::getLatestHashVersion
     */
    const HASH_VERSION_LATEST = 2;

    /**
     * Default length of salt in bytes
     */
    const DEFAULT_SALT_LENGTH = 32;

    /**#@+
     * Exploded password hash keys
     */
    const PASSWORD_HASH = 0;
    const PASSWORD_SALT = 1;
    const PASSWORD_VERSION = 2;
    /**#@-*/

    /**
     * Array key of encryption key in deployment config
     */
    const PARAM_CRYPT_KEY = 'crypt/key';

    /**#@+
     * Cipher versions
     */
    const CIPHER_BLOWFISH = 0;

    const CIPHER_RIJNDAEL_128 = 1;

    const CIPHER_RIJNDAEL_256 = 2;

    const CIPHER_AEAD_CHACHA20POLY1305 = 3;

    const CIPHER_LATEST = 3;
    /**#@-*/

    /**
     * Default hash string delimiter
     */
    const DELIMITER = ':';

    /**
     * @var array map of hash versions
     */
    /**
     * @var array map of hash versions
     */
    private $hashVersionMap = [
        self::HASH_VERSION_MD5 => 'md5',
        self::HASH_VERSION_SHA256 => 'sha256',
        self::HASH_VERSION_SHA1 => 'sha1'
    ];

    /**
     * @var array map of password hash
     */
    private $passwordHashMap = [
        self::PASSWORD_HASH => '',
        self::PASSWORD_SALT => '',
        self::PASSWORD_VERSION => self::HASH_VERSION_SHA256
    ];

    /**
     * Indicate cipher
     *
     * @var int
     */
    protected $cipher = self::CIPHER_LATEST;

    /**
     * Version of encryption key
     *
     * @var int
     */
    protected $keyVersion;

    /**
     * Array of encryption keys
     *
     * @var string[]
     */
    protected $keys = [];

    /**
     * @var Random
     */
    private $random;

    /**
     * @var KeyValidator
     */
    private $keyValidator;

    /**
     * Encryptor constructor.
     *
     * @param Random $random
     * @param DeploymentConfig $deploymentConfig
     * @param KeyValidator|null $keyValidator
     */
    public function __construct(
        Random $random,
        DeploymentConfig $deploymentConfig,
        \Magento\Framework\Encryption\KeyValidator $keyValidator = null
    ) {
        $this->random = $random;

        // load all possible keys
        $this->keys = preg_split('/\s+/s', trim((string)$deploymentConfig->get(self::PARAM_CRYPT_KEY)));
        $this->keyVersion = count($this->keys) - 1;
        $this->keyValidator = $keyValidator ?: ObjectManager::getInstance()->get(\Magento\Framework\Encryption\KeyValidator::class);
        $latestHashVersion = $this->getLatestHashVersion();
        $this->passwordHashMap[self::PASSWORD_VERSION] = self::HASH_VERSION_SHA1;
        /*if ($latestHashVersion === self::HASH_VERSION_ARGON2ID13) {
            $this->hashVersionMap[self::HASH_VERSION_ARGON2ID13] = SODIUM_CRYPTO_PWHASH_ALG_ARGON2ID13;
            $this->passwordHashMap[self::PASSWORD_VERSION] = self::HASH_VERSION_ARGON2ID13;
        }*/
        parent::__construct($random, $deploymentConfig, $keyValidator);
    }

    /**
     * Gets latest hash algorithm version.
     *
     * @return int
     */
    public function getLatestHashVersion(): int
    {
        if (extension_loaded('sodium')) {
            return self::HASH_VERSION_ARGON2ID13;
        }

        return self::HASH_VERSION_SHA256;
    }

    /**
     * Check whether specified cipher version is supported
     *
     * Returns matched supported version or throws exception
     *
     * @param int $version
     * @return int
     * @throws \Exception
     */
    public function validateCipher($version)
    {
        $types = [
            self::CIPHER_BLOWFISH,
            self::CIPHER_RIJNDAEL_128,
            self::CIPHER_RIJNDAEL_256,
            self::CIPHER_AEAD_CHACHA20POLY1305,
        ];

        $version = (int)$version;
        if (!in_array($version, $types, true)) {
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception((string)new \Magento\Framework\Phrase('Not supported cipher version'));
        }
        return $version;
    }

    /**
     * @inheritdoc
     */
    public function getHash($password, $salt = false, $version = self::HASH_VERSION_LATEST)
    {
        if ($salt === false) {
            return $this->hash($password);
        }
        if ($salt === true) {
            $salt = self::DEFAULT_SALT_LENGTH;
        }
        if (is_integer($salt)) {
            $salt = $this->random->getRandomString($salt);
        }

        if($version == self::HASH_VERSION_SHA1) {
        	return implode(
	            self::DELIMITER,
	            [
	                $this->hash($password),
	                $version
	            ]
	        );
        } else {
	        return implode(
	            self::DELIMITER,
	            [
	                $this->hash($salt . $password),
	                $salt,
	                $version
	            ]
	        );
        }

    }

    /*public function getHash($password, $salt = false, $version = self::HASH_VERSION_LATEST)
    {
        if (!isset($this->hashVersionMap[$version])) {
            $version = self::HASH_VERSION_SHA256;
        }

        if ($salt === false) {
            $version = $version === self::HASH_VERSION_ARGON2ID13 ? self::HASH_VERSION_SHA256 : $version;
            return $this->hash($password, $version);
        }
        if ($salt === true) {
            $salt = self::DEFAULT_SALT_LENGTH;
        }
        if (is_integer($salt)) {
            $salt = $version === self::HASH_VERSION_ARGON2ID13 ?
                SODIUM_CRYPTO_PWHASH_SALTBYTES :
                $salt;
            $salt = $this->random->getRandomString($salt);
        }

        if ($version === self::HASH_VERSION_ARGON2ID13) {
            $hash = $this->getArgonHash($password, $salt);
        } else {
            $hash = $this->generateSimpleHash($salt . $password, $version);
        }

        return implode(
            self::DELIMITER,
            [
                $hash,
                $salt,
                $version
            ]
        );
    }*/

    /**
     * @inheritdoc
     */
    public function hash($data, $version = self::HASH_VERSION_LATEST)
    {
        return hash($this->hashVersionMap[$version], $data);
    }

    /**
     * @inheritdoc
     */
    public function isValidHash($password, $hash)
    {
        $this->explodePasswordHash($hash);

        foreach ($this->getPasswordVersion() as $hashVersion) {
        	if($hashVersion == self::HASH_VERSION_SHA1) {
            	$password = $this->hash($password, $hashVersion);
        	} else {
            	$password = $this->hash($this->getPasswordSalt() . $password, $hashVersion);
        	}
        }

        return Security::compareStrings(
            $password,
            $this->getPasswordHash()
        );
    }

    /**
     * @param string $hash
     * @return array
     */
    private function explodePasswordHash($hash)
    {
        $explodedPassword = explode(self::DELIMITER, $hash, 3);

        foreach ($this->passwordHashMap as $key => $defaultValue) {
            $this->passwordHashMap[$key] = (isset($explodedPassword[$key])) ? $explodedPassword[$key] : $defaultValue;
        }

        return $this->passwordHashMap;
    }

    /**
     * @return array
     */
    private function getPasswordVersion()
    {
        return array_map('intval', explode(self::DELIMITER, $this->passwordHashMap[self::PASSWORD_VERSION]));
    }

    /**
     * @return string
     */
    private function getPasswordHash()
    {
        return (string)$this->passwordHashMap[self::PASSWORD_HASH];
    }

    /**
     * @return string
     */
    private function getPasswordSalt()
    {
        return (string)$this->passwordHashMap[self::PASSWORD_SALT];
    }
}