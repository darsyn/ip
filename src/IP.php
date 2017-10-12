<?php

namespace Darsyn\IP;

trigger_error(
    '"Darsyn\IP\IP" is deprecated and will be removed in the next major version; use "Darsyn\IP\Multi" instead.',
    E_USER_DEPRECATED
);

class IP extends Multi
{
}
