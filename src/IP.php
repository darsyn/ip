<?php

namespace Darsyn\IP;

use Darsyn\IP\Version\Multi;

trigger_error(
    '"Darsyn\IP\IP" is deprecated and will be removed in the next major version; use "Darsyn\IP\Version\Multi" instead.',
    E_USER_DEPRECATED
);

class IP extends Multi
{
}
