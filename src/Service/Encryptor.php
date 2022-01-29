<?php
/** @noinspection PhpIncludeInspection */
declare(strict_types=1);

namespace App\Service;

use Exception;
use LogicException;
use RuntimeException;
use SodiumException;
use Stringable;
use function dirname;
use function function_exists;
use const DIRECTORY_SEPARATOR;

/**
 * Service encrypt and decrypt data with Symfony secrets keys
 */
class Encryptor {

    private ?string $encryptionKey = null;
    private string|Stringable|null $decryptionKey;
    private string $pathPrefix;

    public function __construct(string $secretsDir, string|Stringable $decryptionKey = null) {
        $this->pathPrefix = rtrim(
                strtr($secretsDir, '/', DIRECTORY_SEPARATOR),
                DIRECTORY_SEPARATOR
            ) . DIRECTORY_SEPARATOR . basename($secretsDir) . '.';
        $this->decryptionKey = $decryptionKey;
    }

    /**
     * @throws SodiumException
     * @throws Exception
     */
    public function encrypt(string $value): string {
        $this->loadKeys();
        return bin2hex(
            sodium_crypto_box_seal(
                $value,
                $this->encryptionKey ?? sodium_crypto_box_publickey($this->decryptionKey)
            )
        );
    }

    /**
     * @throws SodiumException
     * @throws Exception
     */
    private function loadKeys(): void {
        if (!function_exists('sodium_crypto_box_seal')) {
            throw new LogicException(
                'The "sodium" PHP extension is required to deal with secrets. Alternatively, try running "composer require paragonie/sodium_compat" if you cannot enable the extension.".'
            );
        }

        if (null !== $this->encryptionKey || '' !== $this->decryptionKey = (string)$this->decryptionKey) {
            return;
        }

        if (is_file($this->pathPrefix . 'decrypt.private.php')) {
            $this->decryptionKey = (string)include $this->pathPrefix . 'decrypt.private.php';
        }

        if (is_file($this->pathPrefix . 'encrypt.public.php')) {
            $this->encryptionKey = (string)include $this->pathPrefix . 'encrypt.public.php';
        } elseif ('' !== $this->decryptionKey) {
            $this->encryptionKey = sodium_crypto_box_publickey($this->decryptionKey);
        } else {
            throw new RuntimeException(sprintf('Encryption key not found in "%s".', dirname($this->pathPrefix)));
        }

        if ('' === $this->decryptionKey) {
            throw new Exception(
                sprintf(
                    'Unable to decrypt as no decryption key was found in "%s".',
                    $this->getPrettyPath(dirname($this->pathPrefix) . DIRECTORY_SEPARATOR)
                )
            );
        }
    }

    protected function getPrettyPath(string $path): string {
        return str_replace(getcwd() . DIRECTORY_SEPARATOR, '', $path);
    }

    /**
     * @throws SodiumException
     * @throws Exception
     */
    public function decrypt(string $value): ?string {
        $this->loadKeys();
        if (false === $value = sodium_crypto_box_seal_open(hex2bin($value), $this->decryptionKey)) {
            throw new Exception(
                sprintf(
                    'Unable to decrypt as the wrong decryption key was provided for "%s".',
                    $this->getPrettyPath(dirname($this->pathPrefix) . DIRECTORY_SEPARATOR)
                )
            );
        }

        return $value;
    }

}
