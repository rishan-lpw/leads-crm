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
        // Setup tabs for English, Tamil, and Call History (hash-based tabs)
        var tabs = document.querySelectorAll('#tabs a[href^="#"]');
        var panes = document.querySelectorAll('.tab-pane');
        if (!tabs.length || !panes.length) return;

        function activate(hash) {
            tabs.forEach(function (a) {
                var href = a.getAttribute('href');
                if (!href.startsWith('#')) return; // Skip route-based tabs
                
                var li = a.parentElement;
                var isActive = href === hash;
                
                if (li && li.tagName === 'LI') {
                    if (isActive) {
                        li.classList.add('active');
                    } else {
                        li.classList.remove('active');
                    }
                }
                
                // Update inline styles for active/inactive state (hash-based tabs only)
                if (isActive) {
                    a.style.color = '#2563eb';
                    a.style.backgroundColor = 'white';
                    a.style.border = '2px solid #2563eb';
                    a.style.borderBottom = 'none';
                } else {
                    a.style.color = '#6b7280';
                    a.style.backgroundColor = '#f9fafb';
                    a.style.border = '2px solid transparent';
                    a.style.borderBottom = 'none';
                }
            });
            
            panes.forEach(function (pane) {
                var shouldShow = '#' + pane.id === hash;
                if (shouldShow) {
                    pane.classList.add('active', 'in');
                } else {
                    pane.classList.remove('active', 'in');
                }
            });
        }

        tabs.forEach(function (a) {
            var href = a.getAttribute('href');
            if (href && href.startsWith('#')) {
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    activate(href);
                });
            }
        });
    }

    function showLoading(show) {
        var modal = document.getElementById('scriptLoadingModal');
        if (!modal) return;
        modal.classList.toggle('hidden', !show);
    }

    function evaluateCondition(condition, values) {
        var originalCondition = condition;
        condition = condition.toLowerCase().trim();
        
        var result = false;
        
        // Check for Total_Leads > threshold
        if (condition.includes('total_leads') && condition.includes('>')) {
            var match = condition.match(/>\s*(\d+)/);
            var threshold = match ? parseInt(match[1]) : 0;
            result = values.totalLeads > threshold;
            console.log('Evaluating Total_Leads:', values.totalLeads, '>', threshold, '=', result);
            return result;
        }
        
        // Check for Total_Views > threshold
        if (condition.includes('total_views') && condition.includes('>')) {
            var match = condition.match(/>\s*(\d+)/);
            var threshold = match ? parseInt(match[1]) : 0;
            result = values.totalViews > threshold;
            console.log('Evaluating Total_Views:', values.totalViews, '>', threshold, '=', result);
            return result;
        }
        
        // Check for "price less than avg" or "price < 50M"
        if (condition.includes('price') && (condition.includes('less') || condition.includes('<'))) {
            if (condition.includes('50m') || condition.includes('50 m')) {
                result = values.price < 50000000;
                console.log('Evaluating price < 50M:', values.price, '<', 50000000, '=', result);
                return result;
            }
            if (condition.includes('150k/month') || condition.includes('150k')) {
                result = values.pricePerMonth < 150000;
                console.log('Evaluating price < 150K:', values.pricePerMonth, '<', 150000, '=', result);
                return result;
            }
            if (condition.includes('avg')) {
                result = values.price < values.avgPrice;
                console.log('Evaluating price < avg:', values.price, '<', values.avgPrice, '=', result);
                return result;
            }
        }
        
        // Check for "price > 50M" or high price
        if (condition.includes('price') && condition.includes('>')) {
            if (condition.includes('50m') || condition.includes('50 m')) {
                result = values.price > 50000000;
                console.log('Evaluating price > 50M:', values.price, '>', 50000000, '=', result);
                return result;
            }
            if (condition.includes('150k/month') || condition.includes('150k')) {
                result = values.pricePerMonth > 150000;
                console.log('Evaluating price > 150K:', values.pricePerMonth, '>', 150000, '=', result);
                return result;
            }
        }
        
        // Check for "NOT Rentals"
        if (condition.includes('not') && condition.includes('rental')) {
            result = !values.isRental;
            console.log('Evaluating NOT Rentals:', !values.isRental, '=', result);
            return result;
        }
        
        // Check for "Rentals"
        if (condition.includes('rental')) {
            result = values.isRental;
            console.log('Evaluating Rentals:', values.isRental, '=', result);
            return result;
        }
        
        // Default: don't show if condition not recognized
        console.warn('Unrecognized condition:', originalCondition);
        return false;
    }

    function processConditionalContent(text) {
        if (!text) return '';
        
        // Hardcoded values for conditional evaluation
        // Update these values based on the specific property/lead
        var conditions = {
            isRental: false,           // Set to true if property is for rent
            price: 35000000,           // Property price in LKR (35M for land example)
            pricePerMonth: 120000,     // Monthly rent in LKR
            totalLeads: 25,            // Total leads count (>20 will show lead text)
            totalViews: 150,           // Total views count (>100 will show views text)
            belowMarket: true,         // Is price below market value
            avgPrice: 40000000         // Average price for comparison
        };
        
        var result = text;
        var processedMatches = [];
        
        // Pattern 1: $ If condition $ "quoted content"
        var quotedPattern = /\$\s*If\s+([^\$]+?)\$\s*"([^"]+)"/gi;
        var match;
        while ((match = quotedPattern.exec(text)) !== null) {
            var condition = match[1];
            var content = match[2];
            var shouldShow = evaluateCondition(condition, conditions);
            var replacement = shouldShow ? content : '';
            
            // Store match info to replace later
            processedMatches.push({
                original: match[0],
                replacement: replacement,
                index: match.index
            });
        }
        
        // Pattern 2: $ If condition $ unquoted content (until end of line or paragraph)
        var unquotedPattern = /\$\s*If\s+([^\$]+?)\$\s*([^\n]+?)(?=\n\n|\n\$|$)/gi;
        quotedPattern.lastIndex = 0; // Reset
        while ((match = unquotedPattern.exec(text)) !== null) {
            // Skip if this position was already processed by quoted pattern
            var alreadyProcessed = processedMatches.some(function(pm) {
                return match.index >= pm.index && match.index < (pm.index + pm.original.length);
            });
            
            if (!alreadyProcessed && !match[0].includes('"')) {
                var condition = match[1];
                var content = match[2];
                var shouldShow = evaluateCondition(condition, conditions);
                var replacement = shouldShow ? content.trim() : '';
                
                processedMatches.push({
                    original: match[0],
                    replacement: replacement,
                    index: match.index
                });
            }
        }
        
        // Sort matches by index in reverse order (to maintain correct positions when replacing)
        processedMatches.sort(function(a, b) { return b.index - a.index; });
        
        // Apply all replacements
        processedMatches.forEach(function(pm) {
            result = result.substring(0, pm.index) + pm.replacement + result.substring(pm.index + pm.original.length);
        });
        
        console.log('Conditional processing:', {
            conditions: conditions,
            matchesFound: processedMatches.length,
            originalLength: text.length,
            resultLength: result.length
        });
        
        return result;
    }

    function formatText(text, commonTextReplacements) {
        if (!text) return '';
        
        // First process conditional content ($ wrapped conditions)
        var processedText = processConditionalContent(text);
        
        // Replace @placeholders with common text values
        if (commonTextReplacements) {
            Object.keys(commonTextReplacements).forEach(function(key) {
                var placeholder = '@' + key;
                var replacement = commonTextReplacements[key] || '';
                // Replace all occurrences of the placeholder
                processedText = processedText.split(placeholder).join(replacement);
            });
        }
        
        return processedText
            .replace(/\n/g, '<br>')
            .replace(/\[([^\]]+)\]/g, '<span class="bracketed-text">$1</span>')
            .replace(/\{([^}]+)\}/g, '<span class="braced-text">$1</span>');
    }

    function createSectionHeader(title) {
        var h4 = document.createElement('h4');
        h4.textContent = title;
        h4.style.color = '#fff202';
        h4.style.marginBottom = '5px';
        h4.style.fontWeight = '800';
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

    function createMessageCard(content, speaker, commonTextReplacements) {
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
        contentDiv.innerHTML = formatText(content, commonTextReplacements);
        card.appendChild(contentDiv);

        return card;
    }

    function createAccordionSection(items, commonTextReplacements) {
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
            content.innerHTML = formatText(items[key], commonTextReplacements);
            details.appendChild(content);

            accordion.appendChild(header);
            accordion.appendChild(details);
        });

        return accordion;
    }

    function renderBundlePackages(bundleSection, commonTextReplacements) {
        var container = document.getElementById('bundle-packages-container');
        if (!container) return;

        try {
            var frag = document.createDocumentFragment();

            if (bundleSection && typeof bundleSection === 'object') {
                Object.keys(bundleSection).forEach(function(packageKey) {
                    var packageContent = bundleSection[packageKey];
                    
                    // Create package card
                    var packageCard = document.createElement('div');
                    packageCard.className = 'message-card';
                    packageCard.style.marginBottom = '20px';
                    packageCard.style.borderLeft = '4px solid #3b82f6';
                    packageCard.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                    
                    // Package name header
                    var packageHeader = document.createElement('div');
                    packageHeader.className = 'message-speaker';
                    packageHeader.style.color = '#1e40af';
                    packageHeader.style.fontSize = '20px';
                    packageHeader.style.marginBottom = '10px';
                    packageHeader.textContent = packageKey;
                    packageCard.appendChild(packageHeader);
                    
                    // Package content
                    var contentDiv = document.createElement('div');
                    contentDiv.className = 'message-content';
                    contentDiv.innerHTML = formatText(packageContent, commonTextReplacements);
                    packageCard.appendChild(contentDiv);
                    
                    frag.appendChild(packageCard);
                });
            }

            container.innerHTML = '';
            container.appendChild(frag);
        } catch (e) {
            console.error('Error rendering bundle packages', e);
        }
    }

    function renderStats(statsSection, commonTextReplacements) {
        var container = document.getElementById('stats-container');
        if (!container) return;

        try {
            var frag = document.createDocumentFragment();

            if (statsSection && typeof statsSection === 'object') {
                Object.keys(statsSection).forEach(function(statKey) {
                    var statContent = statsSection[statKey];
                    
                    // Create stat card
                    var statCard = document.createElement('div');
                    statCard.className = 'message-card';
                    statCard.style.marginBottom = '20px';
                    statCard.style.borderLeft = '4px solid #10b981';
                    statCard.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                    
                    // Stat name header
                    var statHeader = document.createElement('div');
                    statHeader.className = 'message-speaker';
                    statHeader.style.color = '#059669';
                    statHeader.style.fontSize = '20px';
                    statHeader.style.marginBottom = '10px';
                    statHeader.textContent = statKey;
                    statCard.appendChild(statHeader);
                    
                    // Stat content
                    var contentDiv = document.createElement('div');
                    contentDiv.className = 'message-content';
                    contentDiv.innerHTML = formatText(statContent, commonTextReplacements);
                    statCard.appendChild(contentDiv);
                    
                    frag.appendChild(statCard);
                });
            }

            container.innerHTML = '';
            container.appendChild(frag);
        } catch (e) {
            console.error('Error rendering stats', e);
        }
    }

    function renderScript(script, activeTab) {
        if (!script || (Array.isArray(script) && script.length === 0) || (typeof script === 'object' && Object.keys(script).length === 0)) {
            console.log('No script data available');
            return;
        }

        try {
            // Extract Common Text section for replacements
            var commonTextReplacements = script['Common Text'] || {};
            console.log('Common Text replacements available:', Object.keys(commonTextReplacements));
            console.log('Active tab:', activeTab);
            
            // Render content based on active tab
            if (activeTab === 'bundle-package') {
                // Only render Bundle Packages
                if (script['Bundle package']) {
                    renderBundlePackages(script['Bundle package'], commonTextReplacements);
                }
                return; // Exit early
            }
            
            if (activeTab === 'stats') {
                // Only render Stats
                if (script['Stats']) {
                    renderStats(script['Stats'], commonTextReplacements);
                }
                return; // Exit early
            }
            
            // Default: render Sinhala tab content
            var container = document.getElementById('sheet-data-container');
            if (!container) return;
            
            var frag = document.createDocumentFragment();

            // Main sections (Sinhala tab only)
            Object.keys(script).forEach(function (sectionKey) {
                // Skip Common Text, Bundle package, Stats, and standalone Rejection Options sections
                if (sectionKey === 'Common Text' || sectionKey === 'Bundle package' || sectionKey === 'Stats' || sectionKey === 'Rejection Options') {
                    return;
                }
                
                var section = script[sectionKey];
                
                // Skip if empty
                if (!section || (typeof section === 'object' && Object.keys(section).length === 0)) {
                    return;
                }

                // Add section header
                frag.appendChild(createSectionHeader(sectionKey));
                frag.appendChild(createHr());

                // Handle different section types
                if (typeof section === 'object') {
                    // Regular sections with subsections
                    Object.keys(section).forEach(function(subKey) {
                        var subContent = section[subKey];
                        
                        // Special handling for Rejection Options link
                        if (subKey === 'Rejection Options' && (subContent === '🔗' || subContent.includes('🔗'))) {
                            // Create subsection header
                            var rejectionHeader = document.createElement('h4');
                            rejectionHeader.textContent = 'Rejection Options';
                            rejectionHeader.style.marginTop = '20px';
                            rejectionHeader.style.marginBottom = '5px';
                            rejectionHeader.style.color = '#00ff9d';
                            rejectionHeader.style.fontWeight = '800';
                            frag.appendChild(rejectionHeader);
                            
                            var rejectionHr = createHr();
                            frag.appendChild(rejectionHr);
                            
                            // Render the full rejection options accordion from top-level section
                            if (script['Rejection Options']) {
                                frag.appendChild(createAccordionSection(script['Rejection Options'], commonTextReplacements));
                            }
                            return;
                        }
                        
                        // Create subsection if needed
                        if (Object.keys(section).length > 1 && subKey !== sectionKey) {
                            var subHeader = document.createElement('h5');
                            subHeader.textContent = subKey;
                            subHeader.style.marginTop = '15px';
                            subHeader.style.marginBottom = '8px';
                            subHeader.style.fontWeight = '600';
                            subHeader.style.color = '#00ff9d';
                            frag.appendChild(subHeader);
                        }
                        
                        // Render content
                        if (typeof subContent === 'string') {
                            frag.appendChild(createMessageCard(subContent, null, commonTextReplacements));
                        }
                    });
                } else if (typeof section === 'string') {
                    frag.appendChild(createMessageCard(section, null, commonTextReplacements));
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
            
            var activeTabTag = document.getElementById('active-tab-data');
            var activeTab = activeTabTag ? JSON.parse(activeTabTag.textContent || '"sinhala"') : 'sinhala';
            
            console.log('Script data loaded:', Object.keys(serverData));
            console.log('Active tab from server:', activeTab);
            
            renderScript(serverData, activeTab);
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

