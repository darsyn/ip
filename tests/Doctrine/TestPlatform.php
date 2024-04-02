<?php

namespace Darsyn\IP\Tests\Doctrine;

use Doctrine\DBAL\Platforms\SqlitePlatform;

class TestPlatform extends SqlitePlatform
{
    public function getBinaryTypeDeclarationSQL(array $column)
    {
        return 'DUMMYBINARY()';
    }
}
