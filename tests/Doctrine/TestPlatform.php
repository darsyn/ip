<?php

namespace Darsyn\IP\Tests\Doctrine;

use Doctrine\DBAL\Platforms\MySqlPlatform;

class TestPlatform extends MySqlPlatform
{
    public function getBinaryTypeDeclarationSQL(array $column)
    {
        return 'DUMMYBINARY()';
    }
}
