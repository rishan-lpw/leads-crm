/*
 * Call Script utilities: highlighting, dropdown sections, formatting, basic tabs.
 * Plain JavaScript (no dependencies).
 */

(function () {
    // Add responsive CSS styles
    var style = document.createElement('style');
    style.textContent = `
        @media (max-width: 768px) {
            .call-btn-text { display: none; }
            .panel-heading { padding: 10px 15px !important; }
            .panel-body { padding: 10px 15px !important; }
            .nav-tabs { font-size: 12px !important; }
            .message-card { margin-bottom: 15px !important; padding: 12px !important; }
            .message-speaker { font-size: 14px !important; }
            .message-content { font-size: 13px !important; }
        }
        @media (max-width: 480px) {
            .panel-title { font-size: 18px !important; }
            .call-btn { padding: 8px 12px !important; font-size: 12px !important; }
            .nav-tabs a { padding: 8px 12px !important; font-size: 11px !important; }
            .message-card { padding: 10px !important; }
        }
        .message-card, .message-content, #sheet-data-container, 
        #bundle-packages-container, #stats-container, #call-log-data-container {
            max-width: 100% !important;
            width: 100% !important;
            box-sizing: border-box !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
        }
    `;
    document.head.appendChild(style);
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
                    // Render call logs if call history tab is activated
                    if (pane.id === 'calllog') {
                        renderCallLogs();
                    }
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

        // Activate tab based on URL hash on page load
        if (window.location.hash) {
            activate(window.location.hash);
        }
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

    function processConditionalContent(text, propertyData) {
        if (!text) return '';

        // Extract key property info
        var priceType = (propertyData.Price_Type || propertyData.price_type || '').toLowerCase();
        var propType = (propertyData.Type || propertyData.offer_type || propertyData.Prop_Type || '').toLowerCase();
        var price = parseFloat(propertyData.Price || propertyData.price) || 0;
        var pricePercentageLower = parseFloat(propertyData.Price_Precentage_Lower) || 0;
        
        // Use merged data from both sources
        var totalLeads = parseInt(propertyData.total_leads || propertyData.Total_Leads) || 0;
        var totalViews = parseInt(propertyData.total_views || propertyData.Total_Views) || 0;
        
        var isRental = propType.includes('rent') || priceType.includes('month');
        var isBelowMarket = pricePercentageLower > 0;
        var priceThreshold = priceType.includes('month') ? 150000 : 50000000;

        // Early return if no $ conditions exist
        if (!/\$[^$]+\$/.test(text)) {
            return text;
        }

        // Helper function to evaluate a condition
        function evaluateCondition(condition) {
            var cond = condition.trim().toLowerCase();

            // Rentals / Not Rentals
            if (cond.includes('not') && cond.includes('rental')) {
                return !isRental;
            }
            if (cond.includes('rental')) {
                return isRental;
            }

            // Price less than avg - check Price_Precentage_Lower
            if (cond.includes('price less than avg') || cond.includes('if price < avg') || cond.includes('price < avg')) {
                return pricePercentageLower > 0 && isBelowMarket;
            }

            // Total Leads conditions
            if (cond.includes('total_leads > 20') || cond.includes('total_leads>20') || cond.includes('total leads > 20')) {
                return totalLeads > 20;
            }
            if (cond.includes('total_leads >= 20') || cond.includes('total_leads>=20')) {
                return totalLeads >= 20;
            }
            if (cond.includes('total_leads < 20') || cond.includes('total_leads<20')) {
                return totalLeads < 20;
            }

            // Total Views conditions
            if (cond.includes('total_views > 100') || cond.includes('total_views>100') || cond.includes('total views > 100')) {
                return totalViews > 100;
            }
            if (cond.includes('total_views >= 100') || cond.includes('total_views>=100')) {
                return totalViews >= 100;
            }
            if (cond.includes('total_views < 100') || cond.includes('total_views<100')) {
                return totalViews < 100;
            }

            // Price > threshold (50M for sales, 150K for rentals)
            if (cond.includes('price > 50m') || cond.includes('price > 150k') || 
                (cond.includes('price >') && (cond.includes('50m') || cond.includes('150k')))) {
                return price >= priceThreshold;
            }

            // Price >= threshold
            if (cond.includes('price >= 50m') || cond.includes('price >= 150k')) {
                return price >= priceThreshold;
            }

            // Price < threshold (50M for sales, 150K for rentals)
            if (cond.includes('price < 50m') || cond.includes('price < 150k') || 
                (cond.includes('price <') && (cond.includes('50m') || cond.includes('150k')))) {
                return price > 0 && price < priceThreshold;
            }

            // Price <= threshold
            if (cond.includes('price <= 50m') || cond.includes('price <= 150k')) {
                return price > 0 && price <= priceThreshold;
            }

            // Additional generic checks
            if (cond.includes('true')) return true;
            if (cond.includes('false')) return false;

            // Default: if condition doesn't match any pattern, return false
            console.warn('Unknown conditional statement:', condition);
            return false;
        }

        // Replace conditional blocks - handles patterns like:
        // $condition$content$
        // $If condition$content$
        // $if condition$content$
        var result = text.replace(/\$([^$]+)\$([^$]*?)(?=\$[^$]+\$|$)/gis, function(match, condition, content) {
            var cond = condition.trim();
            
            // Check if it's a conditional pattern (starts with "If", "if", or is a condition)
            if (/^(If|if)\s+/i.test(cond)) {
                // Extract the actual condition after "If" or "if"
                var actualCondition = cond.replace(/^(If|if)\s+/i, '').trim();
                var shouldInclude = evaluateCondition(actualCondition);
                return shouldInclude ? content.trim() : '';
            } else {
                // Direct condition without "If" prefix
                var shouldInclude = evaluateCondition(cond);
                return shouldInclude ? content.trim() : '';
            }
        });

        return result.trim();
    }

    function formatText(text, commonTextReplacements, propertyData) {
        if (!text) return '';
        
        // First replace @placeholders with common text values (so they're available in conditions too)
        var processedText = text;
        if (commonTextReplacements) {
            // Sort keys by length (longest first) to avoid partial replacements
            var sortedKeys = Object.keys(commonTextReplacements).sort(function(a, b) {
                return b.length - a.length;
            });
            
            sortedKeys.forEach(function(key) {
                var placeholder = '@' + key;
                var replacement = commonTextReplacements[key] || '';
                // Use regex to match @key followed by non-word characters or end of string
                // This ensures we match the exact placeholder and not part of another word
                var regex = new RegExp('@' + key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '(?=\\s|\\W|$)', 'g');
                processedText = processedText.replace(regex, replacement);
            });
        }
        
        // Then process conditional content ($ wrapped conditions)
        processedText = processConditionalContent(processedText, propertyData || {});
        
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

    function createMessageCard(content, speaker, commonTextReplacements, propertyData) {
        var card = document.createElement('div');
        card.className = 'message-card';
        card.style.width = '100%';
        card.style.maxWidth = '100%';
        card.style.boxSizing = 'border-box';
        card.style.wordWrap = 'break-word';
        card.style.overflowWrap = 'break-word';

        if (speaker) {
            var speakerDiv = document.createElement('div');
            speakerDiv.className = 'message-speaker';
            speakerDiv.style.color = '#6f42c1';
            speakerDiv.textContent = speaker;
            card.appendChild(speakerDiv);
        }

        var contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.style.width = '100%';
        contentDiv.style.wordWrap = 'break-word';
        contentDiv.style.overflowWrap = 'break-word';
        contentDiv.innerHTML = formatText(content, commonTextReplacements, propertyData);
        card.appendChild(contentDiv);

        return card;
    }

    function createAccordionSection(items, commonTextReplacements, propertyData) {
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
            content.innerHTML = formatText(items[key], commonTextReplacements, propertyData);
            details.appendChild(content);

            accordion.appendChild(header);
            accordion.appendChild(details);
        });

        return accordion;
    }

    function renderBundlePackages(bundleSection, commonTextReplacements, propertyData) {
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
                    packageCard.style.width = '100%';
                    packageCard.style.maxWidth = '100%';
                    packageCard.style.boxSizing = 'border-box';
                    packageCard.style.marginBottom = '20px';
                    packageCard.style.borderLeft = '4px solid #3b82f6';
                    packageCard.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                    packageCard.style.wordWrap = 'break-word';
                    packageCard.style.overflowWrap = 'break-word';
                    
                    // Package name header
                    var packageHeader = document.createElement('div');
                    packageHeader.className = 'message-speaker';
                    packageHeader.style.color = '#1e40af';
                    packageHeader.style.fontSize = 'clamp(16px, 3vw, 20px)';
                    packageHeader.style.marginBottom = '10px';
                    packageHeader.textContent = packageKey;
                    packageCard.appendChild(packageHeader);
                    
                    // Package content
                    var contentDiv = document.createElement('div');
                    contentDiv.className = 'message-content';
                    contentDiv.style.width = '100%';
                    contentDiv.style.wordWrap = 'break-word';
                    contentDiv.style.overflowWrap = 'break-word';
                    contentDiv.innerHTML = formatText(packageContent, commonTextReplacements, propertyData);
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

    function renderStats(statsSection, commonTextReplacements, propertyData) {
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
                    statCard.style.width = '100%';
                    statCard.style.maxWidth = '100%';
                    statCard.style.boxSizing = 'border-box';
                    statCard.style.marginBottom = '20px';
                    statCard.style.borderLeft = '4px solid #10b981';
                    statCard.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
                    statCard.style.wordWrap = 'break-word';
                    statCard.style.overflowWrap = 'break-word';
                    
                    // Stat name header
                    var statHeader = document.createElement('div');
                    statHeader.className = 'message-speaker';
                    statHeader.style.color = '#059669';
                    statHeader.style.fontSize = 'clamp(16px, 3vw, 20px)';
                    statHeader.style.marginBottom = '10px';
                    statHeader.textContent = statKey;
                    statCard.appendChild(statHeader);
                    
                    // Stat content
                    var contentDiv = document.createElement('div');
                    contentDiv.className = 'message-content';
                    contentDiv.style.width = '100%';
                    contentDiv.style.wordWrap = 'break-word';
                    contentDiv.style.overflowWrap = 'break-word';
                    contentDiv.innerHTML = formatText(statContent, commonTextReplacements, propertyData);
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

    function renderScript(script, activeTab, propertyData) {
        if (!script || (Array.isArray(script) && script.length === 0) || (typeof script === 'object' && Object.keys(script).length === 0)) {
            console.log('No script data available');
            return;
        }

        try {
            // Extract Common Text section for replacements
            var commonTextReplacements = script['Common Text'] || {};
            console.log('Common Text replacements available:', Object.keys(commonTextReplacements));
            console.log('Active tab:', activeTab);
            console.log('Property data:', propertyData);
            
            // Render content based on active tab
            if (activeTab === 'bundle-package') {
                // Only render Bundle Packages
                if (script['Bundle package']) {
                    renderBundlePackages(script['Bundle package'], commonTextReplacements, propertyData);
                }
                return; // Exit early
            }
            
            if (activeTab === 'stats') {
                // Only render Stats
                if (script['Stats']) {
                    renderStats(script['Stats'], commonTextReplacements, propertyData);
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
                                frag.appendChild(createAccordionSection(script['Rejection Options'], commonTextReplacements, propertyData));
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
                            frag.appendChild(createMessageCard(subContent, null, commonTextReplacements, propertyData));
                        }
                    });
                } else if (typeof section === 'string') {
                    frag.appendChild(createMessageCard(section, null, commonTextReplacements, propertyData));
                }
            });

            container.innerHTML = '';
            container.appendChild(frag);
            setupAccordion(container || document);
        } catch (e) {
            console.error('Error rendering script', e);
        }
    }

    async function fetchPriceMeterData(uid) {
        try {
            var url = 'https://www.lankapropertyweb.com/su/LPW-Admin/public/get-price-meter/stats?uid=' + encodeURIComponent(uid);
            var response = await fetch(url);
            var result = await response.json();
            
            var advert = result.data;
            
            var payload = {
                token: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJuYW1lIjoiYXBpX2tleSJ9.l6YJhp_Jm2tryHhDdodjOE1kui6vfLordQUDXWF3y3U",
                offer_type: advert.offer_type,
                property_type: advert.property_type,
                city: advert.city,
                price: advert.price,
                floor_area: advert.floor_area,
                land_area: advert.land_area,
                price_type: advert.price_type,
                pagetype: 'market-insight',
            };
            
            var queryString = new URLSearchParams(payload).toString();
            var apiUrl = 'https://www.lankapropertyweb.com/api/v3/PriceValidateV2?' + queryString;
            
            try {
                var res = await fetch(apiUrl);
                var meterResponse = await res.json();
                
                return {
                    percentage: meterResponse.message.percentage,
                    status: meterResponse.message.status,
                    message: meterResponse.message.message1,
                    message2: meterResponse.message.message2,
                    am: advert.am,
                    username: advert.username,
                    total_views: advert.stats.total_views,
                    total_leads: advert.stats.total_leads,
                    compaired_price: advert.compaarised_precentage,
                    offer_type: advert.offer_type,
                    last_yr_price: advert.last_year_price,
                    this_yr_price: advert.this_year_price,
                    price: advert.price,
                    price_type: advert.price_type,
                    price_land_pp: advert.price_land_pp,
                    source_type: advert.source_type,
                    property_type: advert.property_type,
                    comm_type: advert.comm_type,
                };
            } catch (err) {
                console.warn("Price Meter API call failed", err);
                return {
                    percentage: 0,
                    status: '',
                    message: '',
                    message2: '',
                    am: advert.am,
                    username: advert.username,
                    total_views: advert.stats.total_views,
                    total_leads: advert.stats.total_leads,
                    compaired_price: advert.compaarised_precentage,
                    offer_type: advert.offer_type,
                    last_yr_price: advert.last_year_price,
                    this_yr_price: advert.this_year_price,
                    price: advert.price,
                    price_type: advert.price_type,
                    price_land_pp: advert.price_land_pp,
                    source_type: advert.source_type,
                    property_type: advert.property_type,
                    comm_type: advert.comm_type,
                };
            }
        } catch (error) {
            console.error("Price Meter fetch failed", error);
            return null;
        }
    }
    
    function getTimeBasedGreeting() {
        var now = new Date();
        var hour = now.getHours();
        
        if (hour >= 5 && hour < 12) {
            return "Morning";
        } else if (hour >= 12 && hour < 17) {
            return "Afternoon";
        } else if (hour >= 17 && hour < 21) {
            return "Evening";
        } else {
            return "Day";
        }
    }
    
    function formatPrice(price) {
        if (price >= 1000000) {
            var formatted = Math.round(price / 1000000 * 10) / 10;
            formatted = Number.isInteger(formatted) ? parseInt(formatted) : formatted;
            return formatted + 'M';
        } else if (price >= 1000) {
            var formatted = Math.round(price / 1000 * 10) / 10;
            formatted = Number.isInteger(formatted) ? parseInt(formatted) : formatted;
            return formatted + 'K';
        } else {
            return price.toLocaleString();
        }
    }

    async function fetchSheetData(uid, mobileNo, city, property, callingFrom) {
        showLoading(true);
        try {
            var scriptTag = document.getElementById('call-script-data');
            var serverData = scriptTag ? JSON.parse(scriptTag.textContent || '{}') : {};
            
            var propertyDataTag = document.getElementById('property-data');
            var propertyData = propertyDataTag ? JSON.parse(propertyDataTag.textContent || '{}') : {};
            
            var activeTabTag = document.getElementById('active-tab-data');
            var activeTab = activeTabTag ? JSON.parse(activeTabTag.textContent || '"sinhala"') : 'sinhala';
            
            console.log('Script data loaded:', Object.keys(serverData));
            console.log('Property data loaded:', propertyData);
            console.log('Active tab from server:', activeTab);
            
            // Fetch additional price meter data if uid is available
            if (uid) {
                try {
                    var priceMeterData = await fetchPriceMeterData(uid);
                    if (priceMeterData) {
                        // Merge price meter data with property data
                        propertyData = Object.assign({}, propertyData, {
                            total_views: priceMeterData.total_views || propertyData.Total_Views,
                            total_leads: priceMeterData.total_leads || propertyData.Total_Leads,
                            price_percentage: priceMeterData.percentage,
                            price_status: priceMeterData.status,
                            am_name: priceMeterData.am,
                            username: priceMeterData.username,
                            source_type: priceMeterData.source_type,
                        });
                        console.log('Merged property data with price meter:', propertyData);
                    }
                } catch (err) {
                    console.warn('Could not fetch price meter data, continuing with basic data', err);
                }
            }
            
            renderScript(serverData, activeTab, propertyData);
            showLoading(false);
        } catch (e) {
            console.error('Failed to load script data', e);
            showLoading(false);
        }
    }

    function setupCallButton() {
        var callButtons = document.querySelectorAll('.call-btn');
        if (!callButtons.length) return;
        
        callButtons.forEach(function(callButton) {
            callButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                var phoneNumber = callButton.getAttribute('data-phone');
                
                if (!phoneNumber || phoneNumber.trim() === '' || phoneNumber === '[Customer Phone Number]') {
                    alert('No phone number available for this customer.');
                    return;
                }
                
                // Clean the phone number (remove spaces, dashes, parentheses, brackets)
                var cleanPhone = phoneNumber.replace(/[\s\-\(\)\[\]]/g, '');
                
                // Add + prefix if not present (for international format)
                if (!cleanPhone.startsWith('+')) {
                    cleanPhone = '+' + cleanPhone;
                }
                
                console.log('Initiating call to:', cleanPhone);
                
                // Create tel: URI to trigger Windows default app chooser
                var telUri = 'tel:' + cleanPhone;
                
                // Open tel: URI in a new window to ensure it triggers the app chooser
                window.open(telUri, '_self');
            });
        });
        
        console.log('Call button(s) initialized:', callButtons.length);
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            var date = new Date(dateString);
            if (isNaN(date.getTime())) return dateString;
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateString;
        }
    }

    function formatDuration(duration) {
        if (!duration && duration !== 0) return 'N/A';
        
        // If already a formatted string, return as-is
        if (typeof duration === 'string' && (duration.includes(':') || duration.includes('m') || duration.includes('s'))) {
            return duration;
        }
        
        // Assume it's seconds if it's a number
        var seconds = parseInt(duration);
        if (isNaN(seconds)) return duration;
        
        var mins = Math.floor(seconds / 60);
        var secs = seconds % 60;
        if (mins > 0) {
            return mins + 'm ' + secs + 's';
        }
        return secs + 's';
    }

    function renderCallLogs() {
        var container = document.getElementById('call-log-data-container');
        if (!container) return;

        var callLogsTag = document.getElementById('call-logs-data');
        var callLogs = callLogsTag ? JSON.parse(callLogsTag.textContent || '[]') : [];

        if (!Array.isArray(callLogs) || callLogs.length === 0) {
            container.innerHTML = '<li style="padding: 40px; text-align: center; color: #6b7280; font-size: 14px;">No call logs available</li>';
            return;
        }

        // Clear any previous tabs data
        var sheetContainer = document.getElementById('sheet-data-container');
        if (sheetContainer) sheetContainer.innerHTML = '';
        var bundleContainer = document.getElementById('bundle-packages-container');
        if (bundleContainer) bundleContainer.innerHTML = '';
        var statsContainer = document.getElementById('stats-container');
        if (statsContainer) statsContainer.innerHTML = '';

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            if (!text) return '';
            var div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        var html = '';
        var totalLogs = callLogs.length;
        callLogs.forEach(function(log, index) {
            var dateTime = formatDate(log.date_time || log.date || log.created_at || log.call_date);
            var duration = formatDuration(log.duration || log.call_duration);
            var talktime = log.talktime || log.duration || log.call_duration || '';
            var sentiment = log.sentiment || '';
            var status = log.status || '';
            var recordingUrl = log.recording_url || log.recording || '';
            var qaScore = log.qa_scorecard || log.qa_score || '';
            var qaPercentage = log.qa_final_percentage || log.qa_percentage || '';
            var am = log.am || log.am_name || '';
            
            // Header fields
            var summarySi = escapeHtml(log.summary_si || '');
            var callSummaryCategory = escapeHtml(log.call_summary_category || '');
            var callSummaryTag = escapeHtml(log.call_summary_tag || '');
            
            // Expandable fields
            var transcript = log.transcript || '';
            var summaryEn = log.summary_en || '';
            var summaryTa = log.summary_ta || '';

            // Sentiment color classes
            var sentimentClass = '';
            var sentimentBgColor = '';
            var sentimentTextColor = '';
            if (sentiment) {
                var sentimentLower = sentiment.toLowerCase();
                if (sentimentLower === 'positive') {
                    sentimentBgColor = '#10b981';
                    sentimentTextColor = '#ffffff';
                } else if (sentimentLower === 'negative') {
                    sentimentBgColor = '#ef4444';
                    sentimentTextColor = '#ffffff';
                } else if (sentimentLower === 'neutral') {
                    sentimentBgColor = '#6b7280';
                    sentimentTextColor = '#ffffff';
                } else {
                    sentimentBgColor = '#f59e0b';
                    sentimentTextColor = '#ffffff';
                }
                sentimentClass = 'background: ' + sentimentBgColor + '; color: ' + sentimentTextColor + ';';
            }

            // Status color classes
            var statusClass = '';
            var statusBgColor = '';
            var statusTextColor = '';
            if (status) {
                var statusLower = status.toLowerCase();
                if (statusLower.includes('success') || statusLower.includes('completed') || statusLower.includes('done')) {
                    statusBgColor = '#10b981';
                    statusTextColor = '#ffffff';
                } else if (statusLower.includes('failed') || statusLower.includes('error') || statusLower.includes('cancelled')) {
                    statusBgColor = '#ef4444';
                    statusTextColor = '#ffffff';
                } else if (statusLower.includes('pending') || statusLower.includes('waiting')) {
                    statusBgColor = '#f59e0b';
                    statusTextColor = '#ffffff';
                } else {
                    statusBgColor = '#3b82f6';
                    statusTextColor = '#ffffff';
                }
                statusClass = 'background: ' + statusBgColor + '; color: ' + statusTextColor + ';';
            }

            var logId = 'call-log-' + index;
            var hasExpandableContent = transcript || summaryEn || summaryTa;
            var isLastItem = index === totalLogs - 1;

            html += '<li style="position: relative; padding-left: clamp(120px, 20vw, 180px); margin-bottom: 40px; list-style: none; overflow: visible;">';
            
            // Timeline date marker with talktime and am name
            html += '<div style="position: absolute; left: 0; top: 0; width: clamp(100px, 18vw, 160px); text-align: right; padding-right: clamp(10px, 2vw, 20px); padding-top: 5px; word-wrap: break-word; overflow-wrap: break-word;">';
            html += '<div style="font-size: clamp(11px, 2vw, 13px); font-weight: 700; color: #2563eb; margin-bottom: 6px; line-height: 1.4;">' + escapeHtml(dateTime) + '</div>';
            
            // Talktime and AM name under date/time
            if (talktime) {
                var talktimeDisplay = '';
                if (typeof talktime === 'number') {
                    // Convert seconds to minutes if >= 60 seconds
                    if (talktime >= 60) {
                        var minutes = Math.floor(talktime / 60);
                        var seconds = talktime % 60;
                        if (seconds > 0) {
                            talktimeDisplay = minutes + ' min ' + seconds + ' sec';
                        } else {
                            talktimeDisplay = minutes + ' min';
                        }
                    } else {
                        talktimeDisplay = talktime + ' sec';
                    }
                } else if (typeof talktime === 'string') {
                    // Try to parse if it's a numeric string
                    var parsed = parseFloat(talktime);
                    if (!isNaN(parsed)) {
                        if (parsed >= 60) {
                            var minutes = Math.floor(parsed / 60);
                            var seconds = parsed % 60;
                            if (seconds > 0) {
                                talktimeDisplay = minutes + ' min ' + seconds + ' sec';
                            } else {
                                talktimeDisplay = minutes + ' min';
                            }
                        } else {
                            talktimeDisplay = parsed + ' sec';
                        }
                    } else {
                        // If it's already a formatted string, use it as-is
                        talktimeDisplay = talktime;
                    }
                } else {
                    talktimeDisplay = String(talktime);
                }
                html += '<div style="font-size: 14px; color: #6b7280; margin-bottom: 4px; line-height: 1.5; display: flex; align-items: center; justify-content: flex-end; gap: 4px;">';
                html += '<i class="fa fa-clock" style="font-size: 10px; color: #9ca3af;"></i>';
                html += '<span>' + escapeHtml(talktimeDisplay) + '</span>';
                html += '</div>';
            }
            if (am) {
                html += '<div style="font-size: 14px; color: #6b7280; margin-bottom: 4px; line-height: 1.5; display: flex; align-items: center; justify-content: flex-end; gap: 4px;">';
                html += '<i class="fa fa-user" style="font-size: 10px; color: #9ca3af;"></i>';
                html += '<span style="font-weight: 500;">' + escapeHtml(am) + '</span>';
                html += '</div>';
            }
            
            html += '<div style="position: absolute; right: 0; top: ' + (talktime || am ? '50px' : '20px') + '; width: 16px; height: 16px; background: #2563eb; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 0 2px #2563eb; z-index: 10; flex-shrink: 0;"></div>';
            html += '</div>';
            
            // Vertical line (don't extend on last item)
            var timelineTop = (talktime || am) ? '66px' : '36px';
            if (!isLastItem) {
                html += '<div style="position: absolute; left: 158px; top: ' + timelineTop + '; bottom: -40px; width: 2px; background: #e5e7eb; z-index: 1;"></div>';
            }
            
            // Call log card with relative positioning for QA Scores
            html += '<div style="position: relative; background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: clamp(12px, 3vw, 20px); box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s ease; overflow: hidden; word-wrap: break-word; overflow-wrap: break-word; width: 100%; max-width: 100%; box-sizing: border-box;">';
            
            // QA Scores at top right corner
            if (qaScore || qaPercentage) {
                var qaParts = [];
                if (qaScore) qaParts.push(escapeHtml(String(qaScore)));
                if (qaPercentage) qaParts.push(escapeHtml(String(qaPercentage)) + '%');
                html += '<div style="position: absolute; top: 16px; right: 16px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 8px 12px; border-radius: 8px; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3); z-index: 5; min-width: 80px;">';
                html += '<div style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; margin-bottom: 2px;">QA Scores</div>';
                html += '<div style="font-size: 14px; font-weight: 700; line-height: 1.2;">' + qaParts.join(' / ') + '</div>';
                html += '</div>';
            }
            
            // Header section
            html += '<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; flex-wrap: wrap; gap: 12px; padding-right: ' + ((qaScore || qaPercentage) ? 'clamp(80px, 15vw, 120px)' : '0') + ';">';
            html += '<div style="flex: 1; min-width: 150px; max-width: 100%;">';
            html += '<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px; flex-wrap: wrap;">';
            html += '<h4 style="font-size: clamp(16px, 3vw, 18px); font-weight: 700; color: #1f2937; margin: 0; line-height: 1.3; word-break: break-word;">Call #' + (index + 1) + '</h4>';
            if (sentiment) {
                html += '<span style="display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; flex-shrink: 0; ' + sentimentClass + '">' + escapeHtml(sentiment) + '</span>';
            }
            if (status) {
                html += '<span style="display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; flex-shrink: 0; ' + statusClass + '">' + escapeHtml(status) + '</span>';
            }
            html += '</div>';
            
            // Header fields (call_summary_category, call_summary_tag) - removed summary_si from here
            if (callSummaryCategory || callSummaryTag) {
                html += '<div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;">';
                if (callSummaryCategory) {
                    html += '<span style="display: inline-block; padding: 4px 10px; background: #e0e7ff; border-radius: 4px; font-size: 12px; color: #1e40af; font-weight: 500; word-break: break-word; max-width: 100%;">Category: ' + callSummaryCategory + '</span>';
                }
                if (callSummaryTag) {
                    html += '<span style="display: inline-block; padding: 4px 10px; background: #fef3c7; border-radius: 4px; font-size: 12px; color: #92400e; font-weight: 500; word-break: break-word; max-width: 100%;">Tag: ' + callSummaryTag + '</span>';
                }
                html += '</div>';
            }
            html += '</div>';
            html += '</div>';
            
            // Recording if available
            if (recordingUrl) {
                html += '<div style="margin-bottom: 16px; padding: 12px; background: #f0f9ff; border-radius: 8px; border-left: 3px solid #0ea5e9; overflow: hidden;">';
                html += '<div style="font-size: 12px; font-weight: 600; color: #0369a1; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">';
                html += '<i class="fa fa-volume-up" style="color: #0ea5e9;"></i>';
                html += '<span>Recording</span>';
                html += '</div>';
                html += '<audio controls style="width: 100%; height: 36px; outline: none; max-width: 100%; box-sizing: border-box; border-radius: 6px;">';
                html += '<source src="' + escapeHtml(recordingUrl) + '" type="audio/mpeg">';
                html += '<source src="' + escapeHtml(recordingUrl) + '" type="audio/wav">';
                html += 'Your browser does not support the audio element.';
                html += '</audio>';
                html += '</div>';
            }
            
            // Summary SI after recording
            if (summarySi) {
                html += '<div style="margin-bottom: 16px; padding: 14px; background: #fefce8; border-radius: 8px; border-left: 3px solid #eab308; overflow: hidden;">';
                html += '<div style="font-size: 12px; font-weight: 600; color: #854d0e; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">';
                html += '<i class="fa fa-language" style="color: #eab308;"></i>';
                html += '<span>Summary (සිංහල)</span>';
                html += '</div>';
                html += '<div style="font-size: 13px; color: #713f12; line-height: 1.6; white-space: pre-wrap; word-wrap: break-word; overflow-wrap: break-word;">' + summarySi + '</div>';
                html += '</div>';
            }
            
            // Expandable section with text button
            if (hasExpandableContent) {
                html += '<div style="margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 16px; text-align: center;">';
                html += '<button onclick="toggleCallLogDetails(\'' + logId + '\')" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; padding: 10px 20px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; transition: all 0.2s ease; font-size: 13px; font-weight: 600; color: #374151; outline: none;" onmouseover="this.style.background=\'#f3f4f6\'; this.style.borderColor=\'#d1d5db\';" onmouseout="this.style.background=\'#f9fafb\'; this.style.borderColor=\'#e5e7eb\';" onmousedown="this.style.transform=\'scale(0.98)\';" onmouseup="this.style.transform=\'scale(1)\';" onmouseleave="this.style.transform=\'scale(1)\';">';
                html += '<span id="' + logId + '-button-text">Show Details</span>';
                html += '<i id="' + logId + '-icon" class="fa fa-chevron-down" style="font-size: 12px; color: #6b7280; transition: transform 0.3s ease;"></i>';
                html += '</button>';
                html += '</div>';
                
                html += '<div id="' + logId + '-details" style="display: none; margin-top: 12px; padding: 20px; background: linear-gradient(to bottom, #fafafa 0%, #f9fafb 100%); border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden; word-wrap: break-word; overflow-wrap: break-word; transition: all 0.3s ease;">';
                
                if (transcript) {
                    html += '<div style="margin-bottom: 20px;">';
                    html += '<div style="font-size: 13px; font-weight: 600; color: #1f2937; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; padding-bottom: 8px; border-bottom: 2px solid #e5e7eb;">';
                    html += '<i class="fa fa-file-text" style="color: #3b82f6; font-size: 14px;"></i>';
                    html += '<span>Transcript</span>';
                    html += '</div>';
                    html += '<div style="font-size: 13px; color: #4b5563; line-height: 1.8; white-space: pre-wrap; background: white; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05); word-wrap: break-word; overflow-wrap: break-word; max-width: 100%; overflow-x: auto;">' + escapeHtml(transcript).replace(/\n/g, '<br>') + '</div>';
                    html += '</div>';
                }
                
                if (summaryEn) {
                    html += '<div style="margin-bottom: 20px;">';
                    html += '<div style="font-size: 13px; font-weight: 600; color: #1f2937; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; padding-bottom: 8px; border-bottom: 2px solid #e5e7eb;">';
                    html += '<i class="fa fa-language" style="color: #10b981; font-size: 14px;"></i>';
                    html += '<span>Summary (English)</span>';
                    html += '</div>';
                    html += '<div style="font-size: 13px; color: #4b5563; line-height: 1.8; white-space: pre-wrap; background: white; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05); word-wrap: break-word; overflow-wrap: break-word; max-width: 100%; overflow-x: auto;">' + escapeHtml(summaryEn).replace(/\n/g, '<br>') + '</div>';
                    html += '</div>';
                }
                
                if (summaryTa) {
                    html += '<div>';
                    html += '<div style="font-size: 13px; font-weight: 600; color: #1f2937; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; padding-bottom: 8px; border-bottom: 2px solid #e5e7eb;">';
                    html += '<i class="fa fa-language" style="color: #f59e0b; font-size: 14px;"></i>';
                    html += '<span>Summary (Tamil)</span>';
                    html += '</div>';
                    html += '<div style="font-size: 13px; color: #4b5563; line-height: 1.8; white-space: pre-wrap; background: white; padding: 16px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.05); word-wrap: break-word; overflow-wrap: break-word; max-width: 100%; overflow-x: auto;">' + escapeHtml(summaryTa).replace(/\n/g, '<br>') + '</div>';
                    html += '</div>';
                }
                
                html += '</div>';
                html += '</div>';
            }
            
            html += '</div>';
            html += '</li>';
        });

        container.innerHTML = html;
        console.log('Call logs rendered:', callLogs.length);
    }

    // Toggle function for expandable call log details
    function toggleCallLogDetails(logId) {
        var details = document.getElementById(logId + '-details');
        var buttonText = document.getElementById(logId + '-button-text');
        var icon = document.getElementById(logId + '-icon');
        if (!details || !buttonText || !icon) return;
        
        var isExpanded = details.style.display !== 'none';
        details.style.display = isExpanded ? 'none' : 'block';
        
        // Update button text and icon
        if (isExpanded) {
            buttonText.textContent = 'Show Details';
            icon.className = 'fa fa-chevron-down';
            icon.style.transform = 'rotate(0deg)';
        } else {
            buttonText.textContent = 'Hide Details';
            icon.className = 'fa fa-chevron-up';
            icon.style.transform = 'rotate(180deg)';
        }
    }

    // Expose toggle function globally
    window.toggleCallLogDetails = toggleCallLogDetails;

    function init() {
        setupTabs();
        setupCallButton();
        var uid = getQueryParam('uid');
        var mobile = getQueryParam('mobile');
        var city = getQueryParam('city');
        var property = getQueryParam('property');
        var callingFrom = getQueryParam('calling_from');
        fetchSheetData(uid, mobile, city, property, callingFrom);
        
        // Check if call history tab is active and render call logs
        var calllogPane = document.getElementById('calllog');
        if (calllogPane && calllogPane.classList.contains('active')) {
            renderCallLogs();
        }
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

