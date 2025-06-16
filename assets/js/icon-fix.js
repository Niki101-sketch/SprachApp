// Icon Fix JavaScript - Automatische Icon-Ersetzung
document.addEventListener('DOMContentLoaded', function() {
    
    // Icon-Mapping: Bootstrap Icons zu Emoji/Unicode
    const iconMapping = {
        'bi-translate': '🌍',
        'bi-house-door': '🏠', 
        'bi-collection': '📚',
        'bi-pencil-square': '✏️',
        'bi-check2-circle': '✅',
        'bi-people': '👥',
        'bi-gear': '⚙️',
        'bi-person-circle': '👤',
        'bi-mortarboard': '🎓',
        'bi-person-workspace': '👨‍🏫',
        'bi-shield-check': '🛡️',
        'bi-box-arrow-right': '🚪',
        'bi-arrow-up': '⬆️',
        'bi-star': '⭐',
        'bi-info-circle': 'ℹ️',
        'bi-envelope': '✉️',
        'bi-box-arrow-in-right': '🔑',
        'bi-person-plus': '👤➕',
        'bi-rocket-takeoff': '🚀',
        'bi-book': '📖',
        'bi-play-fill': '▶️',
        'bi-eye': '👁️',
        'bi-eye-slash': '🙈',
        'bi-lock': '🔒',
        'bi-key': '🔑',
        'bi-send': '📤',
        'bi-arrow-left': '⬅️',
        'bi-exclamation-triangle': '⚠️',
        'bi-check-circle': '✅',
        'bi-check-circle-fill': '✅',
        'bi-x-circle': '❌',
        'bi-lightning-charge': '⚡',
        'bi-headset': '🎧',
        'bi-graph-up-arrow': '📈',
        'bi-trophy': '🏆',
        'bi-trophy-fill': '🏆',
        'bi-collection-fill': '📋',
        'bi-mortarboard-fill': '🎓',
        'bi-file-text': '📄',
        'bi-shield-fill-check': '🛡️',
        'bi-calendar': '📅',
        'bi-clock': '🕐',
        'bi-download': '⬇️',
        'bi-upload': '⬆️',
        'bi-search': '🔍',
        'bi-heart': '❤️',
        'bi-heart-fill': '❤️',
        'bi-plus': '➕',
        'bi-dash': '➖',
        'bi-x': '❌',
        'bi-question-circle': '❓',
        'bi-bell': '🔔',
        'bi-chat': '💬',
        'bi-phone': '📞',
        'bi-camera': '📷',
        'bi-mic': '🎤',
        'bi-volume-up': '🔊',
        'bi-volume-mute': '🔇'
    };

    // Font Awesome zu Emoji Mapping
    const faMapping = {
        'fa-language': '🌍',
        'fa-home': '🏠',
        'fa-th-large': '📚',
        'fa-edit': '✏️',
        'fa-check-circle': '✅',
        'fa-users': '👥',
        'fa-cog': '⚙️',
        'fa-user-circle': '👤',
        'fa-graduation-cap': '🎓',
        'fa-chalkboard-teacher': '👨‍🏫',
        'fa-shield-alt': '🛡️',
        'fa-sign-out-alt': '🚪',
        'fa-arrow-up': '⬆️',
        'fa-star': '⭐',
        'fa-info-circle': 'ℹ️',
        'fa-envelope': '✉️',
        'fa-sign-in-alt': '🔑',
        'fa-user-plus': '👤➕',
        'fa-rocket': '🚀'
    };

    // Funktion um Icons zu ersetzen
    function replaceIcons() {
        // Bootstrap Icons ersetzen
        Object.keys(iconMapping).forEach(iconClass => {
            const elements = document.querySelectorAll('.' + iconClass);
            elements.forEach(element => {
                // Prüfen ob das Icon bereits korrekt geladen ist
                if (!hasValidIcon(element)) {
                    replaceWithEmoji(element, iconMapping[iconClass]);
                }
            });
        });

        // Font Awesome Icons ersetzen
        Object.keys(faMapping).forEach(iconClass => {
            const elements = document.querySelectorAll('.' + iconClass);
            elements.forEach(element => {
                if (!hasValidIcon(element)) {
                    replaceWithEmoji(element, faMapping[iconClass]);
                }
            });
        });

        // Fallback für alle anderen Icons
        const allIcons = document.querySelectorAll('[class*="bi-"], [class*="fa-"]');
        allIcons.forEach(element => {
            if (!hasValidIcon(element) && !element.hasAttribute('data-icon-replaced')) {
                replaceWithEmoji(element, '●');
            }
        });
    }

    // Prüfen ob ein Icon korrekt geladen wurde
    function hasValidIcon(element) {
        const computed = window.getComputedStyle(element, '::before');
        const content = computed.getPropertyValue('content');
        
        // Wenn content nicht 'none' oder leer ist, dann ist ein Icon vorhanden
        return content && content !== 'none' && content !== '""' && content !== "''";
    }

    // Icon durch Emoji ersetzen
    function replaceWithEmoji(element, emoji) {
        // Verhindere mehrfache Ersetzung
        if (element.hasAttribute('data-icon-replaced')) {
            return;
        }

        element.setAttribute('data-icon-replaced', 'true');
        
        // Verschiedene Ersetzungsmethoden probieren
        
        // Methode 1: CSS content überschreiben
        const style = document.createElement('style');
        style.textContent = `
            [data-icon-id="${element.getAttribute('data-icon-id') || generateId()}"]::before {
                content: "${emoji}" !important;
                font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif !important;
                font-size: 1.4em !important;
                display: inline-block !important;
                width: 1.2em !important;
                text-align: center !important;
                line-height: 1 !important;
                vertical-align: middle !important;
            }
        `;
        document.head.appendChild(style);
        
        if (!element.getAttribute('data-icon-id')) {
            element.setAttribute('data-icon-id', generateId());
        }

        // Methode 2: Direkter Text-Content (Fallback)
        if (!hasValidIcon(element)) {
            element.innerHTML = `<span style="font-family: Apple Color Emoji, Segoe UI Emoji, Noto Color Emoji, sans-serif; font-size: 1.4em; display: inline-block; width: 1.2em; text-align: center; vertical-align: middle;">${emoji}</span>`;
        }

        // Methode 3: Data-Attribut für CSS
        element.setAttribute('data-icon-emoji', emoji);
    }

    // Eindeutige ID generieren
    function generateId() {
        return 'icon-' + Math.random().toString(36).substr(2, 9);
    }

    // Icons beim Laden der Seite ersetzen
    replaceIcons();

    // Icons nach kurzer Verzögerung nochmal prüfen (falls CSS noch lädt)
    setTimeout(replaceIcons, 500);
    setTimeout(replaceIcons, 1000);

    // MutationObserver für dynamisch hinzugefügte Icons
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        const icons = node.querySelectorAll('[class*="bi-"], [class*="fa-"]');
                        icons.forEach(icon => {
                            setTimeout(() => {
                                if (!hasValidIcon(icon)) {
                                    const classList = Array.from(icon.classList);
                                    const iconClass = classList.find(cls => cls.startsWith('bi-') || cls.startsWith('fa-'));
                                    if (iconClass && (iconMapping[iconClass] || faMapping[iconClass])) {
                                        replaceWithEmoji(icon, iconMapping[iconClass] || faMapping[iconClass]);
                                    }
                                }
                            }, 100);
                        });
                    }
                });
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Zusätzliche CSS-Regeln für bessere Icon-Darstellung
    const additionalCSS = document.createElement('style');
    additionalCSS.textContent = `
        /* Emoji Icons Styling */
        [data-icon-emoji]::before {
            content: attr(data-icon-emoji) !important;
            font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", "Android Emoji", sans-serif !important;
            font-size: 1.4em !important;
            display: inline-block !important;
            width: 1.2em !important;
            text-align: center !important;
            line-height: 1 !important;
            vertical-align: middle !important;
            margin-right: 0.3em !important;
        }

        /* Spezielle Größen für verschiedene Kontexte */
        .navbar-brand [data-icon-emoji]::before {
            font-size: 1.8em !important;
            width: 1em !important;
            margin-right: 0.2em !important;
        }

        .nav-link [data-icon-emoji]::before {
            font-size: 1.3em !important;
            margin-right: 0.4em !important;
        }

        .btn [data-icon-emoji]::before {
            font-size: 1.1em !important;
            margin-right: 0.3em !important;
        }

        .role-badge [data-icon-emoji]::before {
            font-size: 1em !important;
            margin-right: 0.2em !important;
        }

        /* Responsive Größen */
        @media (max-width: 768px) {
            [data-icon-emoji]::before {
                font-size: 1.6em !important;
            }
            
            .navbar-brand [data-icon-emoji]::before {
                font-size: 2em !important;
            }
            
            .nav-link [data-icon-emoji]::before {
                font-size: 1.5em !important;
            }
        }

        /* Fallback für Browser die Emojis nicht unterstützen */
        @supports not (font-family: "Apple Color Emoji") {
            [data-icon-emoji]::before {
                content: "■" !important;
                font-size: 1.2em !important;
                color: currentColor !important;
            }
        }

        /* Text-Icons als ultimativer Fallback */
        [data-icon-replaced] {
            position: relative;
        }

        [data-icon-replaced]:empty::after {
            content: "ICON";
            display: inline-block;
            background: rgba(0, 0, 0, 0.1);
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.7em;
            font-weight: bold;
            margin-right: 0.3em;
            vertical-align: middle;
        }
    `;
    document.head.appendChild(additionalCSS);

    // Debug-Funktion (optional)
    window.debugIcons = function() {
        const allIcons = document.querySelectorAll('[class*="bi-"], [class*="fa-"]');
        console.log('Total icons found:', allIcons.length);
        
        allIcons.forEach((icon, index) => {
            const classes = Array.from(icon.classList).filter(c => c.startsWith('bi-') || c.startsWith('fa-'));
            const hasValid = hasValidIcon(icon);
            const isReplaced = icon.hasAttribute('data-icon-replaced');
            
            console.log(`Icon ${index + 1}:`, {
                classes: classes,
                hasValidIcon: hasValid,
                isReplaced: isReplaced,
                element: icon
            });
        });
    };

    // Auto-Fix für häufige Icon-Probleme
    function autoFixCommonIssues() {
        // Bootstrap Icons CDN nochmal laden falls nicht verfügbar
        if (!window.getComputedStyle(document.querySelector('.bi-house-door, .bi-translate'), '::before').getPropertyValue('content')) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css';
            document.head.appendChild(link);
            
            // Nach dem Laden nochmal prüfen
            link.onload = function() {
                setTimeout(replaceIcons, 200);
            };
        }

        // Font Awesome als Backup laden
        if (!document.querySelector('link[href*="font-awesome"]')) {
            const faLink = document.createElement('link');
            faLink.rel = 'stylesheet';
            faLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';
            document.head.appendChild(faLink);
        }
    }

    // Auto-Fix ausführen
    autoFixCommonIssues();

    // Globale Funktionen verfügbar machen
    window.iconFix = {
        replaceIcons: replaceIcons,
        debugIcons: window.debugIcons,
        hasValidIcon: hasValidIcon
    };

    console.log('Icon Fix JavaScript loaded. Use window.iconFix.debugIcons() to debug icon issues.');
});