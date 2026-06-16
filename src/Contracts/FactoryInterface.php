<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

/**
 * @experimental
 */
interface FactoryInterface
{
    /**
     * Create a New IP From Protocol Notation
     *
     * Strictly parses an IP address from its protocol notation only. Unlike
     * factory(), a raw binary sequence is NOT accepted; user-supplied strings
     * never reach the binary path (avoiding an SSRF/validation footgun).
     *
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     * @return static
     */
    public static function fromProtocol(string $ip);

    /**
     * Create a New IP From Protocol Notation, or Null on Failure
     *
     * @return static|null
     */
    public static function tryFromProtocol(string $ip);

    /**
     * Create a New IP From a Raw Binary Sequence
     *
     * @throws \Darsyn\IP\Exception\InvalidBinaryException
     * @return static
     */
    public static function fromBinary(string $binary);

    /**
     * Create a New IP From a Raw Binary Sequence, or Null on Failure
     *
     * @return static|null
     */
    public static function tryFromBinary(string $binary);

    /**
     * Create a New IP From a Hexadecimal String
     *
     * @throws \Darsyn\IP\Exception\InvalidIpAddressException
     * @return static
     */
    public static function fromHex(string $hex);

    /**
     * Create a New IP From a Hexadecimal String, or Null on Failure
     *
     * @return static|null
     */
    public static function tryFromHex(string $hex);

    /** Whether the supplied string is valid IP protocol notation. */
    public static function isValid(string $ip): bool;
}
