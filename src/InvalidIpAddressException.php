<?php
namespace Darsyn\IP;

class InvalidIpAddressException extends \InvalidArgumentException
{
    /**
     * @access protected
     * @var string
     */
    protected $message = 'The IP address supplied is not valid.';
}
