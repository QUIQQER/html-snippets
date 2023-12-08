<?php

namespace QUI\HtmlSnippets;

use QUI\Projects\Project;

class Snippet
{
    protected string $name;
    protected Project $Project;
    protected string $content;
    protected string $event;

    public function __construct(Project $Project, $name)
    {
        $data = Snippets::getSnippetData($Project, $name);

        $this->Project = $Project;
        $this->name = $name;
        $this->content = $data['snippet'];
        $this->event = $data['event'];
    }

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
     *
     * @return string The content as a string.
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Retrieves the snippet content.
     * -> alias for getContent()
     *
     * @return string The snippet content as a string.
     */
    public function getSnippet(): string
    {
        return $this->getContent();
    }
}
