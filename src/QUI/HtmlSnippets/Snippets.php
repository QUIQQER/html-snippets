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
    public static function create(Project $Project, $name, $eventName, $snippet): Snippet
    {
        QUI::getDatabase()->insert(self::table(), [
            'name' => $name,
            'project' => $Project->getName(),
            'event' => $eventName,
            'snippet' => $snippet
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
        QUI::getDatabase()->delete(self::table(), [
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
     * Retrieves the list of items associated with the given project.
     *
     * @param Project $Project The project object.
     * @return array The list of items associated with the project.
     * @throws Exception
     */
    public static function getList(Project $Project): array
    {
        return QUI::getDatabase()->fetch([
            'from' => self::table(),
            'where' => [
                'project' => $Project->getName()
            ]
        ]);
    }

    /**
     * @throws QUI\Exception
     * @throws Exception
     */
    public static function getSnippetData(Project $Project, $name): array
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
