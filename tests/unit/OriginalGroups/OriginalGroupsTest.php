<?php

namespace ALI\Translator\Tests\unit\OriginalGroups;

use ALI\Translator\OriginalGroups\OriginalGroupRepositoryInterface;
use ALI\Translator\Tests\components\Factories\OriginalGroupRepositoryFactory;
use PHPUnit\Framework\TestCase;

class OriginalGroupsTest extends TestCase
{
    public function test()
    {
        $defaultGroupsAliases = ['default', 'not_default'];
        $originalsContents = ['lucky text', 'no so lucky text'];

        $groupRepositories = (new OriginalGroupRepositoryFactory())->getGroupRepositories();
        foreach ($groupRepositories as $groupRepository) {

            $groupRepository->getTranslatorSource()->saveOriginals($originalsContents);

            $installer = $groupRepository->generateInstaller();
            if ($installer->isInstalled()) {
                $installer->destroy();
            }
            $installer->install();

            // Add
            $groupRepository->addGroups($originalsContents, $defaultGroupsAliases);
            // Duplicate adding, without error
            $groupRepository->addGroups($originalsContents, $defaultGroupsAliases);

            // Check all added
            $this->checkOriginalsGroups($groupRepository, $originalsContents, $defaultGroupsAliases);

            // Delete one by one
            foreach ($originalsContents as $originalContent) {
                $originalGroups = $defaultGroupsAliases;
                foreach ($originalGroups as $groupsAliasKey => $groupsAlias) {
                    unset($originalGroups[$groupsAliasKey]);

                    $groupRepository->removeGroups([$originalContent], [$groupsAlias]);
                    $this->checkOriginalsGroups($groupRepository, [$originalContent], $originalGroups);
                }
            }

            // Check delete all
            $groupRepository->addGroups($originalsContents, $defaultGroupsAliases);
            $groupRepository->removeAllGroups($originalsContents);
            $this->checkOriginalsGroups($groupRepository, $originalsContents, []);
        }
    }

    /**
     * @param OriginalGroupRepositoryInterface $groupRepository
     * @param array $originalsContents
     * @param array $defaultGroupsAliases
     */
    protected function checkOriginalsGroups(OriginalGroupRepositoryInterface $groupRepository, array $originalsContents, array $defaultGroupsAliases): void
    {
        $originalsGroups = $groupRepository->getGroups($originalsContents);
        foreach ($originalsContents as $originalsContent) {
            if ($defaultGroupsAliases) {
                static::assertTrue(isset($originalsGroups[$originalsContent]));
            } else {
                static::assertFalse(isset($originalsGroups[$originalsContent]));
            }
            if (isset($originalsGroups[$originalsContent])) {
                static::assertEquals(array_values($defaultGroupsAliases), array_values($originalsGroups[$originalsContent]));
            }
        }
    }
}
