(function() {
    'use strict';

    const processSnippetNodes = function(nodes) {
        // Gather all HTML snippet nodes ("template" elements with a specific "data-" attribute)
        const SnippetsNodeList = document.querySelectorAll('template[data-qui-html-snippet="gdpr"]');

        SnippetsNodeList.forEach((SnippetNode) => {
            const gdprCategory = SnippetNode.getAttribute('data-qui-html-snippet-gdpr-category');

            // Only decode (and execute) the snippet node, if the corresponding gdpr category was accepted
            window.GDPR.waitForCookieCategoryAcceptance(gdprCategory).then(() => {
                decodeSnippetNode(SnippetNode);
            });
        });
    };

    const decodeSnippetNode = function(SnippetNode) {
        // The node's inner HTML is base64 encoded, turn it back to normal HTML
        SnippetNode.innerHTML = atob(SnippetNode.innerHTML);

        // The snippet code is in a wrapping <template> node and thus isn't interpreted
        // Therefore we have to move all nodes inside this template snippet node into the document
        // We do that by iterating over all direct children of the template element and...
        Array.from(SnippetNode.content.children).forEach(ChildNode => {
            let NodeToInsert = ChildNode;

            // ...if it is a script tag it needs special treatment
            if (ChildNode.tagName === 'SCRIPT') {
                // "script" tags are not executed when moved from the snippet node to the document
                // Therefore a new element has to be created instead
                // The new element is executed when added to the document
                NodeToInsert = createExecutableCopyOfScriptNode(ChildNode);

                // Remove the original script node as we are using the copy now
                ChildNode.remove();
            }

            // ...moving the child node from inside the template snippet node in front of the snippet node
            SnippetNode.insertAdjacentElement('beforebegin', NodeToInsert);
        });

        // ...and deleting the now empty snippet node
        SnippetNode.remove();
    };

    const createExecutableCopyOfScriptNode = function (ScriptNode) {
        const ExecutableScriptNode = document.createElement('script');

        // Copy the script's text content (the JavaScript code)
        ExecutableScriptNode.textContent = ScriptNode.textContent;

        // Copy all the script's attributes
        Array.from(ScriptNode.attributes).forEach(ScriptAttribute => {
            ExecutableScriptNode.setAttribute(ScriptAttribute.name, ScriptAttribute.value);
        });

        return ExecutableScriptNode;
    };

    processSnippetNodes();

    window.whenQuiLoaded().then(processSnippetNodes);
})();
