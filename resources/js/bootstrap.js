import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor.css'; // Editor's Style
import katex from 'katex';
import 'katex/dist/katex.min.css';
import renderMathInElement from 'katex/dist/contrib/auto-render';
import Swal from 'sweetalert2'

window.Editor = Editor;
window.katex = katex;
window.renderMathInElement = renderMathInElement;
window.Swal = Swal;

window.renderFormulas = function(container = document.body) {
    if (!container) return;
    if (typeof window.renderMathInElement === 'function') {
        try {
            window.renderMathInElement(container, {
                delimiters: [
                    {left: '$$', right: '$$', display: true},
                    {left: '$', right: '$', display: false},
                    {left: '\\(', right: '\\)', display: false},
                    {left: '\\[', right: '\\]', display: true}
                ],
                throwOnError: false,
                errorColor: '#cc0000',
                strict: false,
                ignoredTags: ["script", "noscript", "style", "textarea"]
            });
        } catch (e) {
            console.warn('KaTeX auto-render error:', e);
        }
    }
};

window.attachKaTeXToEditor = function(editorInstance, containerQuery = '#editor') {
    const editorElement = typeof containerQuery === 'string' ? document.querySelector(containerQuery) : containerQuery;
    if (!editorElement) return;

    let isRendering = false;

    function renderPreview() {
        if (isRendering) return;
        isRendering = true;

        const previewEls = editorElement.querySelectorAll('.toastui-editor-contents');
        previewEls.forEach(el => {
            if (window.renderFormulas) {
                window.renderFormulas(el);
            }
        });

        isRendering = false;
    }

    const observer = new MutationObserver(() => {
        if (isRendering) return;
        observer.disconnect();
        renderPreview();
        setTimeout(() => {
            if (editorElement) {
                observer.observe(editorElement, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
            }
        }, 100);
    });

    observer.observe(editorElement, {
        childList: true,
        subtree: true,
        characterData: true
    });

    if (editorInstance && typeof editorInstance.on === 'function') {
        editorInstance.on('change', () => {
            setTimeout(renderPreview, 50);
            setTimeout(renderPreview, 250);
        });
    }

    setTimeout(renderPreview, 150);
    setTimeout(renderPreview, 500);
    setTimeout(renderPreview, 1000);
};




/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });
