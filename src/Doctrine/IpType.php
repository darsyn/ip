<?php

namespace Darsyn\IP\Doctrine;

trigger_error(
    '"Darsyn\IP\Doctrine\IpType" is deprecated and will be removed in the next major version; use "Darsyn\IP\Doctrine\MultiType" instead.',
    E_USER_DEPRECATED
);

class IpType extends MultiType
{
}
