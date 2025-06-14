<?php

namespace ALI\Translator\Tests\unit\OriginalGroups;

use ALI\Translator\Tests\components\Factories\OriginalGroupRepositoryFactory;
use PHPUnit\Framework\TestCase;

class GetOriginalsByGroupTest extends TestCase
{
    public function test()
    {
        $groupAlias = 'default';
        $originals = [
            'first',
            'second',
            'third',
            'fourth',
            'fifth',
            'sixth',
        ];

        $factory = new OriginalGroupRepositoryFactory();
        foreach ($factory->getGroupRepositories() as $groupRepository) {
            $groupRepository->getTranslatorSource()->saveOriginals($originals);

            $installer = $groupRepository->generateInstaller();
            if ($installer->isInstalled()) {
                $installer->destroy();
            }
            $installer->install();

            $groupRepository->addGroups($originals, [$groupAlias]);

            $this->assertEquals(
                $originals,
                $groupRepository->getOriginalsByGroup($groupAlias)
            );

            $this->assertEquals(
                array_slice($originals, 0, 2),
                $groupRepository->getOriginalsByGroup($groupAlias, 0, 2)
            );

            $this->assertEquals(
                array_slice($originals, 2, 2),
                $groupRepository->getOriginalsByGroup($groupAlias, 2, 2)
            );

            $this->assertEquals(
                array_slice($originals, 4, 10),
                $groupRepository->getOriginalsByGroup($groupAlias, 4, 10)
            );
        }
    }
}
