<?php

namespace QUITests\HtmlSnippets\Unit;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\HtmlSnippets\SnippetTemplateEvent;
use QUI\Smarty\Collector;

class SnippetTemplateEventTest extends TestCase
{
    public function testOnlyActiveSnippetsAreAppended(): void
    {
        $Collector = new Collector();
        $Event = new SnippetTemplateEvent([
            [
                'snippet' => '<span>inactive</span>',
                'active' => 0,
                'gdpr' => ''
            ],
            [
                'snippet' => '<span>active</span>',
                'active' => 1,
                'gdpr' => ''
            ]
        ]);

        $Event->onFireEvent($Collector, new QUI\Template());

        self::assertSame('<span>active</span>', $Collector->getContent());
    }

    public function testInvalidEventArgumentsAreIgnored(): void
    {
        $Collector = new Collector();
        $Event = new SnippetTemplateEvent([
            [
                'snippet' => '<span>active</span>',
                'active' => 1,
                'gdpr' => ''
            ]
        ]);

        $Event->onFireEvent();
        $Event->onFireEvent($Collector);
        $Event->onFireEvent(new \stdClass(), new QUI\Template());
        $Event->onFireEvent($Collector, new \stdClass());

        self::assertSame('', $Collector->getContent());
    }

    public function testGdprCategoryUsesTheInstalledIntegrationBehavior(): void
    {
        $Collector = new Collector();
        $content = '<script>window.consentFixture = true;</script>';
        $Event = new SnippetTemplateEvent([
            [
                'snippet' => $content,
                'active' => 1,
                'gdpr' => 'analytics'
            ]
        ]);

        $Event->onFireEvent($Collector, new QUI\Template());

        if (QUI::getPackageManager()->isInstalled('quiqqer/gdpr')) {
            self::assertStringContainsString('data-qui-html-snippet="gdpr"', $Collector->getContent());
            self::assertStringContainsString('data-qui-html-snippet-gdpr-category="analytics"', $Collector->getContent());
            self::assertStringContainsString(base64_encode($content), $Collector->getContent());
            self::assertStringNotContainsString($content, $Collector->getContent());
        } else {
            self::assertSame($content, $Collector->getContent());
        }
    }
}
