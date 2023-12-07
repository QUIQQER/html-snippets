<?php

namespace QUI\HtmlSnippets;

use QUI;
use QUI\Database\Exception;
use QUI\Projects\Project;

class Snippets
{
    public static function table(): string
    {
        return QUI::getDBTableName('html_snippets');
    }

    /**
     * @throws Exception
     */
    public static function create(Project $Project, $name): Snippet
    {
        QUI::getDatabase()->insert(self::tabble(), [
            'name' => $name,
            'project' => $Project->getName()
        ]);

        return self::get($Project, $name);
    }

    /**
     * @param Project $Project
     * @param $name
     * @return void
     *
     * @throws Exception
     */
    public static function delete(Project $Project, $name): void
    {
        QUI::getDatabase()->delete(self::tabble(), [
            'name' => $name,
            'project' => $Project->getName()
        ]);
    }

    /**
     * @param Project $Project
     * @param $name
     *
     * @return Snippet
     */
    public static function get(Project $Project, $name): Snippet
    {
        return new Snippet($Project, $name);
    }

    /**
     * @throws QUI\Exception
     * @throws Exception
     */
    public static function getSnippetData(Project $Project, $name): Snippet
    {
        $result = QUI::getDatabase()->fetch([
            'from' => self::table(),
            'where' => [
                'name' => $name,
                'project' => $Project->getName()
            ]
        ]);

        if (isset($result[0])) {
            return $result[0];
        }

        throw new QUI\Exception('Snippet not found', 404);
    }
}
