/*
 * Call Script utilities: highlighting, dropdown sections, formatting, basic tabs.
 * Plain JavaScript (no dependencies).
 */

(function () {
    function getQueryParam(name) {
        var params = new URLSearchParams(window.location.search);
        return params.get(name) || "";
    }

    function forEachTextNode(root, callback) {
        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
        var node;
        var nodes = [];
        while ((node = walker.nextNode())) nodes.push(node);
        nodes.forEach(callback);
    }

    function wrapPlaceholders(container) {
        if (!container) return;
        forEachTextNode(container, function (textNode) {
            var text = textNode.nodeValue;
            if (!text || !text.trim()) return;

            var hasSquare = /\[[^\]]+\]/.test(text);
            var hasCurly = /\{[^}]+\}/.test(text);
            if (!hasSquare && !hasCurly) return;

            var html = text
                .replace(/\[([^\]]+)\]/g, '<span class="bracketed-text">$1<\/span>')
                .replace(/\{([^}]+)\}/g, '<span class="braced-text">$1<\/span>');

            var span = document.createElement("span");
            span.innerHTML = html;
            textNode.parentNode.replaceChild(span, textNode);
        });
    }

    function setupAccordion(root) {
        var headers = (root || document).querySelectorAll('.rejection-accordion .rejection-header');
        headers.forEach(function (header) {
            header.addEventListener('click', function () {
                var details = header.nextElementSibling;
                if (!details || !details.classList.contains('rejection-details')) return;

                var isOpen = details.style.maxHeight && details.style.maxHeight !== '0px';
                details.style.maxHeight = isOpen ? '0' : details.scrollHeight + 'px';

                var caret = header.querySelector('span');
                if (caret) caret.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
            });
        });
    }

    function setupTabs() {
        var tabs = document.querySelectorAll('#tabs a[href^="#"]');
        var panes = document.querySelectorAll('.tab-pane');
        if (!tabs.length || !panes.length) return;

        function activate(hash) {
            tabs.forEach(function (a) {
                var li = a.parentElement;
                if (li && li.tagName === 'LI') li.classList.toggle('active', a.getAttribute('href') === hash);
            });
            panes.forEach(function (pane) {
                var shouldShow = '#' + pane.id === hash;
                pane.classList.toggle('active', shouldShow);
                pane.classList.toggle('in', shouldShow);
            });
        }

        tabs.forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                activate(a.getAttribute('href'));
            });
        });

        // Initialize to first active
        var current = Array.prototype.find.call(tabs, function (a) { return a.parentElement.classList.contains('active'); });
        activate(current ? current.getAttribute('href') : tabs[0].getAttribute('href'));
    }

    function showLoading(show) {
        var modal = document.getElementById('scriptLoadingModal');
        if (!modal) return;
        modal.classList.toggle('hidden', !show);
    }

    function formatText(text) {
        if (!text) return '';
        return text
            .replace(/\n/g, '<br>')
            .replace(/\[([^\]]+)\]/g, '<span class="bracketed-text">$1</span>')
            .replace(/\{([^}]+)\}/g, '<span class="braced-text">$1</span>');
    }

    function createSectionHeader(title) {
        var h4 = document.createElement('h4');
        h4.textContent = title;
        h4.style.marginBottom = '5px';
        h4.style.marginTop = '20px';
        return h4;
    }

    function createHr() {
        var hr = document.createElement('hr');
        hr.style.marginTop = '5px';
        hr.style.marginBottom = '10px';
        hr.style.border = '0';
        hr.style.height = '1px';
        hr.style.backgroundColor = '#eee';
        return hr;
    }

    function createMessageCard(content, speaker) {
        var card = document.createElement('div');
        card.className = 'message-card';

        if (speaker) {
            var speakerDiv = document.createElement('div');
            speakerDiv.className = 'message-speaker';
            speakerDiv.style.color = '#6f42c1';
            speakerDiv.textContent = speaker;
            card.appendChild(speakerDiv);
        }

        var contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.innerHTML = formatText(content);
        card.appendChild(contentDiv);

        return card;
    }

    function createAccordionSection(items) {
        var accordion = document.createElement('div');
        accordion.className = 'rejection-accordion';
        accordion.style.marginTop = '12px';
        accordion.style.borderRadius = '8px';
        accordion.style.overflow = 'hidden';
        accordion.style.background = '#fff5f5';

        Object.keys(items).forEach(function(key) {
            var header = document.createElement('div');
            header.className = 'rejection-header';
            header.innerHTML = key + ' <span>▼</span>';
            
            var details = document.createElement('div');
            details.className = 'rejection-details';
            
            var content = document.createElement('div');
            content.style.padding = '15px';
            content.innerHTML = formatText(items[key]);
            details.appendChild(content);

            accordion.appendChild(header);
            accordion.appendChild(details);
        });

        return accordion;
    }

    function renderScript(script) {
        var container = document.getElementById('sheet-data-container');
        if (!container) return;

        if (!script || (Array.isArray(script) && script.length === 0) || (typeof script === 'object' && Object.keys(script).length === 0)) {
            console.log('No script data available');
            return;
        }

        try {
            var frag = document.createDocumentFragment();

            // Main sections
            Object.keys(script).forEach(function (sectionKey) {
                var section = script[sectionKey];
                
                // Skip if empty
                if (!section || (typeof section === 'object' && Object.keys(section).length === 0)) {
                    return;
                }

                // Add section header
                frag.appendChild(createSectionHeader(sectionKey));
                frag.appendChild(createHr());

                // Handle different section types
                if (sectionKey === 'Rejection Options') {
                    // Render as accordion
                    frag.appendChild(createAccordionSection(section));
                } else if (typeof section === 'object') {
                    // Regular sections with subsections
                    Object.keys(section).forEach(function(subKey) {
                        var subContent = section[subKey];
                        
                        // Create subsection if needed
                        if (Object.keys(section).length > 1 && subKey !== sectionKey) {
                            var subHeader = document.createElement('h5');
                            subHeader.textContent = subKey;
                            subHeader.style.marginTop = '15px';
                            subHeader.style.marginBottom = '8px';
                            subHeader.style.fontWeight = '600';
                            subHeader.style.color = '#374151';
                            frag.appendChild(subHeader);
                        }
                        
                        // Render content
                        if (typeof subContent === 'string') {
                            frag.appendChild(createMessageCard(subContent));
                        }
                    });
                } else if (typeof section === 'string') {
                    frag.appendChild(createMessageCard(section));
                }
            });

            container.innerHTML = '';
            container.appendChild(frag);
            setupAccordion(container || document);
        } catch (e) {
            console.error('Error rendering script', e);
        }
    }

    function fetchSheetData(uid, mobileNo, city, property, callingFrom) {
        showLoading(true);
        try {
            var scriptTag = document.getElementById('call-script-data');
            var serverData = scriptTag ? JSON.parse(scriptTag.textContent || '{}') : {};
            
            console.log('Script data loaded:', Object.keys(serverData));
            
            renderScript(serverData);
            showLoading(false);
        } catch (e) {
            console.error('Failed to load script data', e);
            showLoading(false);
        }
    }

    function init() {
        setupTabs();
        var uid = getQueryParam('uid');
        var mobile = getQueryParam('mobile');
        var city = getQueryParam('city');
        var property = getQueryParam('property');
        var callingFrom = getQueryParam('calling_from');
        fetchSheetData(uid, mobile, city, property, callingFrom);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose minimal API if needed
    window.CallScript = {
        init: init,
        fetchSheetData: fetchSheetData,
        wrapPlaceholders: wrapPlaceholders,
        setupAccordion: setupAccordion
    };
})();

