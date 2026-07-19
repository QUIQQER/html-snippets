<?php

namespace QUITests\HtmlSnippets\Unit;

use PHPUnit\Framework\TestCase;
use QUI;
use QUI\HtmlSnippets\SnippetTemplateEvent;
use QUI\Smarty\Collector;
use QUITests\HtmlSnippets\Stubs\GdprInstalledSnippetTemplateEvent;

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
        $category = 'analytics"><script>alert(1)</script>';
        $Event = new GdprInstalledSnippetTemplateEvent([
            [
                'snippet' => $content,
                'active' => 1,
                'gdpr' => $category
            ]
        ]);

        $Event->onFireEvent($Collector, new QUI\Template());

        self::assertStringContainsString('data-qui-html-snippet="gdpr"', $Collector->getContent());
        self::assertStringContainsString(
            'data-qui-html-snippet-gdpr-category="analytics&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;"',
            $Collector->getContent()
        );
        self::assertStringNotContainsString($category, $Collector->getContent());
        self::assertStringContainsString(base64_encode($content), $Collector->getContent());
        self::assertStringNotContainsString($content, $Collector->getContent());
    }
}
