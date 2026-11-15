/**
 * TutorBot+ Theme Toggle (Light / Dark Mode)
 * Persists user preference in localStorage('tutorbot_theme') with zero FOUC.
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'tutorbot_theme';

    function getCurrentTheme() {
        return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function updateToggleButtons(theme) {
        var buttons = document.querySelectorAll('[data-theme-toggle]');
        var isDark = theme === 'dark';
        buttons.forEach(function (btn) {
            var label = isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';
            btn.setAttribute('aria-label', label);
            btn.setAttribute('title', label);
            btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
            var icon = btn.querySelector('i');
            if (icon) {
                icon.className = isDark ? 'fas fa-sun text-warning' : 'fas fa-moon';
            }
            var textSpan = btn.querySelector('[data-theme-label]');
            if (textSpan) {
                textSpan.textContent = isDark ? 'Claro' : 'Oscuro';
            }
        });
    }

    function syncAceEditorTheme(theme) {
        if (typeof window.ace === 'undefined') return;
        var editorEl = document.getElementById('editor');
        if (!editorEl) return;
        try {
            var aceInstance = window.ace.edit(editorEl);
            if (aceInstance && typeof aceInstance.setTheme === 'function') {
                aceInstance.setTheme(theme === 'dark' ? 'ace/theme/twilight' : 'ace/theme/chrome');
            }
        } catch (e) {
            // Ignore if Ace is not initialized on #editor yet
        }
    }

    function syncChartsTheme(theme) {
        if (typeof window.Chart === 'undefined') return;
        var isDark = theme === 'dark';
        var titleColor = isDark ? '#F8FAFC' : '#344767';
        var legendColor = isDark ? '#CBD5E1' : '#344767';
        var tickColor = isDark ? '#94A3B8' : '#67748e';
        var gridColor = isDark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(0, 0, 0, 0.08)';

        try {
            var instances = window.Chart.instances;
            if (!instances) return;
            Object.keys(instances).forEach(function (key) {
                var chart = instances[key];
                if (!chart || !chart.options) return;
                if (chart.options.plugins) {
                    if (chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                        chart.options.plugins.legend.labels.color = legendColor;
                    }
                    if (chart.options.plugins.title) {
                        chart.options.plugins.title.color = titleColor;
                    }
                    if (chart.options.plugins.tooltip) {
                        chart.options.plugins.tooltip.backgroundColor = isDark ? '#1E293B' : 'rgba(15, 23, 42, 0.9)';
                        chart.options.plugins.tooltip.titleColor = '#F8FAFC';
                        chart.options.plugins.tooltip.bodyColor = '#E2E8F0';
                        chart.options.plugins.tooltip.borderColor = isDark ? '#334155' : 'transparent';
                        chart.options.plugins.tooltip.borderWidth = isDark ? 1 : 0;
                    }
                }
                if (chart.options.scales) {
                    Object.keys(chart.options.scales).forEach(function (axisKey) {
                        var scale = chart.options.scales[axisKey];
                        if (scale.ticks) scale.ticks.color = tickColor;
                        if (scale.grid) scale.grid.color = gridColor;
                        if (scale.pointLabels) scale.pointLabels.color = legendColor;
                        if (scale.angleLines) scale.angleLines.color = gridColor;
                    });
                }
                chart.update('none');
            });
        } catch (e) {
            // Ignore chart sync errors
        }
    }

    function applyTheme(theme, persist) {
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
            theme = 'light';
        }
        if (persist !== false) {
            try {
                localStorage.setItem(STORAGE_KEY, theme);
            } catch (e) {}
        }
        updateToggleButtons(theme);
        syncAceEditorTheme(theme);
        syncChartsTheme(theme);
    }

    window.toggleTutorBotTheme = function () {
        var next = getCurrentTheme() === 'dark' ? 'light' : 'dark';
        applyTheme(next, true);
    };

    document.addEventListener('DOMContentLoaded', function () {
        var current = getCurrentTheme();
        updateToggleButtons(current);
        setTimeout(function () {
            syncAceEditorTheme(current);
            syncChartsTheme(current);
        }, 120);

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-theme-toggle]');
            if (btn) {
                e.preventDefault();
                window.toggleTutorBotTheme();
            }
        });
    });
})();
