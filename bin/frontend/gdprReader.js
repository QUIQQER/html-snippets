(function() {
    'use strict';

    const loadNode = function(Node) {
        if (Node.getAttribute('data-qui-html-snippet-gdpr-loaded')) {
            return;
        }

        Node.innerHTML = atob(Node.innerHTML);
        const scripts = Array.from(Node.getElementsByTagName('script'));

        scripts.forEach((script) => {
            const newScript = document.createElement('script');
            newScript.textContent = script.textContent;
            script.parentNode.replaceChild(newScript, script);
        });

        Node.setAttribute('data-qui-html-snippet-gdpr-loaded', 1);
        Node.style.display = '';
    };

    const fetchNodes = function(nodes) {
        nodes.forEach((Node) => {
            const gdprCategory = Node.getAttribute('data-qui-html-snippet-gdpr-category');

            if (window.GDPR.isCookieCategoryAccepted(gdprCategory)) {
                loadNode(Node);
            } else {
                window.GDPR.waitForCookieCategoryAcceptance(gdprCategory).then(() => {
                    loadNode(Node);
                });
            }
        });
    };

    fetchNodes(document.querySelectorAll('[data-qui-html-snippet="gdpr"]'));

    window.whenQuiLoaded().then(() => {
        fetchNodes(document.querySelectorAll('[data-qui-html-snippet="gdpr"]'));
    });
})();
