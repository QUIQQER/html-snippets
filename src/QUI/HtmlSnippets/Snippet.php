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
}
