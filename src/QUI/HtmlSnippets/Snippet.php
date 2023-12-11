<?php

namespace QUI\HtmlSnippets;

use QUI;
use QUI\Interfaces\Users\User;
use QUI\Projects\Project;

class Snippet
{
    protected string $name;
    protected Project $Project;
    protected string $snippet;
    protected string $event;
    protected string $gdprCategory = '';

    /**
     * @param Project $Project
     * @param string $name
     */
    public function __construct(Project $Project, string $name)
    {
        $data = Snippets::getSnippetData($Project, $name);

        $this->Project = $Project;
        $this->name = $name;
        $this->snippet = $data['snippet'];
        $this->event = $data['event'];
        $this->gdprCategory = !empty($data['gdpr']) ? $data['gdpr'] : '';
    }

    //region get

    /**
     * Retrieves the name of the object.
     *
     * @return string The name of the object.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retrieves the associated project object.
     *
     * @return Project The associated project object.
     */
    public function getProject(): Project
    {
        return $this->Project;
    }

    /**
     * Retrieves the snippet content.
     * -> alias for getContent()
     *
     * @return string The snippet content as a string.
     */
    public function getSnippet(): string
    {
        return $this->snippet;
    }

    /**
     * Converts the object to an array representation.
     * The array representation includes the name, event, project, and snippet properties.
     *
     * @return array The object properties as an associative array.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'event' => $this->event,
            'Project' => $this->Project->getName(),
            'snippet' => $this->getSnippet(),
            'gdpr' => $this->gdprCategory
        ];
    }

    //endregion

    //region set

    /**
     * Sets the event for the current object
     *
     * @param string $event The event to set
     * @return void
     */
    public function setEvent(string $event): void
    {
        $this->event = $event;
    }

    /**
     * Set the snippet for the current object.
     *
     * @param string $snippet The snippet to be set.
     * @return void
     */
    public function setSnippet(string $snippet): void
    {
        $this->snippet = $snippet;
    }

    public function setGDPRCategory(string $gdprCategory): void
    {
        $this->gdprCategory = $gdprCategory;
    }

    /**
     * Saves the snippet to the database.
     *
     * @param User|null $User The user who is saving the snippet. If null, the user from the session will be used.
     *
     * @return void
     * @throws QUI\Permissions\Exception|QUI\Database\Exception
     */
    public function save(User $User = null): void
    {
        if ($User === null) {
            $User = QUI::getUserBySession();
        }

        QUI\Permissions\Permission::checkPermission('quiqqer.html-snippets.update', $User);

        QUI::getDatabase()->update(
            Snippets::table(),
            [
                'event' => $this->event,
                'snippet' => $this->snippet,
                'gdpr' => $this->gdprCategory
            ],
            [
                'project' => $this->Project->getName(),
                'name' => $this->name
            ]
        );
    }

    //endregion
}
