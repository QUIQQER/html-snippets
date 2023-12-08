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
     * Creates a new snippet associated with the given project.
     *
     * @param Project $Project The project object.
     * @param string $name The name of the snippet.
     * @param string $eventName The event associated with the snippet.
     * @param string $snippet The content of the snippet.
     * @param null|QUI\Interfaces\Users\User $User Optional. The user object. Defaults to null.
     * @return Snippet The newly created snippet.
     *
     * @throws Exception If the name parameter is empty or if the eventName parameter is empty.
     * @throws QUI\Permissions\Exception
     * @throws QUI\Exception
     */
    public static function create(
        Project $Project,
        string $name,
        string $eventName,
        string $snippet,
        QUI\Interfaces\Users\User $User = null
    ): Snippet {
        QUI\Permissions\Permission::checkPermission('quiqqer.html-snippets.create', $User);

        if (empty($name)) {
            throw new QUI\Exception('Please enter a name');
        }

        if (empty($eventName)) {
            throw new QUI\Exception('Please enter a event');
        }

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
     * @param string $name
     * @param null|QUI\Interfaces\Users\User $User
     * @return void
     *
     * @throws Exception
     * @throws QUI\Permissions\Exception
     */
    public static function delete(
        Project $Project,
        string $name,
        QUI\Interfaces\Users\User $User = null
    ): void {
        QUI\Permissions\Permission::checkPermission('quiqqer.html-snippets.create', $User);

        QUI::getDatabase()->delete(self::table(), [
            'name' => $name,
            'project' => $Project->getName()
        ]);
    }

    /**
     * Retrieves a specific snippet associated with the given project.
     *
     * @param Project $Project The project object.
     * @param string $name The name of the snippet.
     * @return Snippet The snippet object.
     *
     * @throws QUI\Exception
     */
    public static function get(Project $Project, string $name): Snippet
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
     * Retrieves the data of a specific snippet associated with the given project and name.
     *
     * @param Project $Project The project object.
     * @param string $name The name of the snippet.
     * @return array The data of the specified snippet.
     *
     * @throws QUI\Exception Throws an exception if the snippet is not found.
     */
    public static function getSnippetData(Project $Project, string $name): array
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
