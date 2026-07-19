<?php

namespace QUITests\HtmlSnippets\Integration;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\HtmlSnippets\EventHandler;
use QUI\HtmlSnippets\Snippet;
use QUI\HtmlSnippets\Snippets;
use QUI\Projects\Project;
use QUI\Smarty\Collector;
use ReflectionProperty;
use Throwable;

class SnippetLifecycleTest extends TestCase
{
    private const PROJECT_PREFIX = 'phpunit-html-snippets-';

    private Project $Project;
    private string $projectName;

    public static function setUpBeforeClass(): void
    {
        self::skipIfDatabaseIsUnavailable();
        self::cleanupFixtures();
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (!QUI::getSchemaManager()->tablesExist([Snippets::table()])) {
            self::markTestSkipped('The HTML Snippets database table is not installed.');
        }

        $this->projectName = self::PROJECT_PREFIX . bin2hex(random_bytes(8));
        $this->Project = $this->createMock(Project::class);
        $this->Project->method('getName')->willReturn($this->projectName);
    }

    protected function tearDown(): void
    {
        self::deleteProjectFixtures($this->projectName);
        self::resetEventCache();

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        self::cleanupFixtures();
        self::resetEventCache();
    }

    public function testSnippetLifecyclePersistsAllPublicState(): void
    {
        $SystemUser = QUI::getUsers()->getSystemUser();
        $Snippet = Snippets::create(
            $this->Project,
            'header-code',
            'onQuiqqer::template::header::begin',
            '<meta name="fixture" content="initial">',
            $SystemUser
        );

        self::assertSame('header-code', $Snippet->getName());
        self::assertSame($this->Project, $Snippet->getProject());
        self::assertSame('<meta name="fixture" content="initial">', $Snippet->getSnippet());
        self::assertFalse($Snippet->isActive());

        $Snippet->setEvent('onQuiqqer::template::body::end');
        $Snippet->setSnippet('<script>window.fixture = true;</script>');
        $Snippet->setGDPRCategory('analytics');
        $Snippet->activate($SystemUser);

        $StoredSnippet = Snippets::get($this->Project, 'header-code');

        self::assertTrue($StoredSnippet->isActive());
        self::assertSame('<script>window.fixture = true;</script>', $StoredSnippet->getSnippet());
        self::assertSame(
            [
                'name' => 'header-code',
                'event' => 'onQuiqqer::template::body::end',
                'Project' => $this->projectName,
                'snippet' => '<script>window.fixture = true;</script>',
                'gdpr' => 'analytics',
                'active' => 1
            ],
            $StoredSnippet->toArray()
        );

        $StoredSnippet->deactivate($SystemUser);
        self::assertFalse(Snippets::get($this->Project, 'header-code')->isActive());

        Snippets::delete($this->Project, 'header-code', $SystemUser);

        $this->expectException(QUI\Exception::class);
        $this->expectExceptionCode(404);
        Snippets::get($this->Project, 'header-code');
    }

    public function testListIsRestrictedToTheRequestedProject(): void
    {
        $SystemUser = QUI::getUsers()->getSystemUser();
        Snippets::create($this->Project, 'first', 'event-a', 'first content', $SystemUser);
        Snippets::create($this->Project, 'second', 'event-b', 'second content', $SystemUser);

        $OtherProject = $this->createMock(Project::class);
        $OtherProject->method('getName')->willReturn($this->projectName . '-other');
        Snippets::create($OtherProject, 'foreign', 'event-c', 'foreign content', $SystemUser);

        $items = Snippets::getList($this->Project);
        $names = array_column($items, 'name');

        sort($names);

        self::assertSame(['first', 'second'], $names);
        self::deleteProjectFixtures($this->projectName . '-other');
    }

    public function testCreateRejectsMissingNameAndEvent(): void
    {
        $SystemUser = QUI::getUsers()->getSystemUser();

        try {
            Snippets::create($this->Project, '', 'event-a', '', $SystemUser);
            self::fail('Creating a snippet without a name must fail.');
        } catch (QUI\Exception $Exception) {
            self::assertSame('Please enter a name', $Exception->getMessage());
        }

        $this->expectException(QUI\Exception::class);
        $this->expectExceptionMessage('Please enter an event');
        Snippets::create($this->Project, 'missing-event', '', '', $SystemUser);
    }

    public function testEventHandlerRegistersStoredSnippetsAndTemplateAssets(): void
    {
        $eventName = 'phpunitHtmlSnippets' . bin2hex(random_bytes(6));

        self::insertFixture([
            'name' => 'registered',
            'project' => $this->projectName,
            'event' => $eventName,
            'snippet' => '<strong>registered</strong>',
            'active' => 1,
            'gdpr' => null
        ]);
        self::insertFixture([
            'name' => 'without-event',
            'project' => $this->projectName,
            'event' => '',
            'snippet' => '<strong>ignored</strong>',
            'active' => 1,
            'gdpr' => null
        ]);

        self::resetEventCache();
        EventHandler::onQuiqqerInit();
        EventHandler::onQuiqqerInit();

        $EventsProperty = new ReflectionProperty(EventHandler::class, 'events');
        $events = $EventsProperty->getValue();

        self::assertIsArray($events);
        self::assertArrayHasKey($eventName, $events);
        self::assertCount(1, $events[$eventName]);

        $Collector = new Collector();
        EventHandler::onTemplateEnd($Collector, new QUI\Template());

        if (QUI::getPackageManager()->isInstalled('quiqqer/gdpr')) {
            self::assertStringContainsString('gdprReader.js', $Collector->getContent());
        } else {
            self::assertSame('', $Collector->getContent());
        }
    }

    /**
     * @param array<string, int|string|null> $data
     */
    private static function insertFixture(array $data): void
    {
        QUI::getDataBaseConnection()->insert(
            QUI\Utils\Doctrine::quoteIdentifier(Snippets::table()),
            $data
        );
    }

    private static function skipIfDatabaseIsUnavailable(): void
    {
        try {
            QUI::getDataBaseConnection()->executeQuery('SELECT 1')->free();
        } catch (Throwable $Exception) {
            self::markTestSkipped('QUIQQER database is not available: ' . $Exception->getMessage());
        }
    }

    private static function cleanupFixtures(): void
    {
        try {
            $QueryBuilder = QUI::getQueryBuilder();
            $QueryBuilder
                ->delete(QUI\Utils\Doctrine::quoteIdentifier(Snippets::table()))
                ->where($QueryBuilder->expr()->like('project', ':project'))
                ->setParameter('project', self::PROJECT_PREFIX . '%')
                ->executeStatement();
        } catch (Throwable) {
            // The availability check reports database problems; cleanup must not hide the test result.
        }
    }

    private static function deleteProjectFixtures(string $projectName): void
    {
        try {
            QUI::getDataBaseConnection()->delete(
                QUI\Utils\Doctrine::quoteIdentifier(Snippets::table()),
                ['project' => $projectName]
            );
        } catch (Throwable) {
            // Cleanup must not hide the actual PHPUnit result.
        }
    }

    private static function resetEventCache(): void
    {
        $EventsProperty = new ReflectionProperty(EventHandler::class, 'events');
        $EventsProperty->setValue(null, null);
    }
}
