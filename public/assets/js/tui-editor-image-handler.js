/**
 * Manejador de subida automática de imágenes para Toast UI Editor
 * Captura eventos de paste (Ctrl+V) y drop de imágenes para subirlas
 * al servidor y prevenir la inserción de cadenas base64 gigantes.
 */
(function() {
    function attachImageUploadToEditor(editor, containerSelector, uploadUrl, csrfToken) {
        const container = typeof containerSelector === 'string' ? document.querySelector(containerSelector) : containerSelector;
        if (!container || !editor) return;

        async function uploadImageFile(file) {
            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', csrfToken);

            const res = await fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) {
                throw new Error('HTTP ' + res.status);
            }

            const data = await res.json();
            return data.relative_url || data.url;
        }

        // Interceptar evento paste en la fase de captura
        container.addEventListener('paste', async (e) => {
            const clipboardData = e.clipboardData || window.clipboardData;
            if (!clipboardData) return;

            let imageFile = null;

            if (clipboardData.items) {
                for (let i = 0; i < clipboardData.items.length; i++) {
                    const item = clipboardData.items[i];
                    if (item.type && item.type.startsWith('image/')) {
                        imageFile = item.getAsFile();
                        break;
                    }
                }
            }

            if (!imageFile && clipboardData.files && clipboardData.files.length > 0) {
                for (let i = 0; i < clipboardData.files.length; i++) {
                    if (clipboardData.files[i].type && clipboardData.files[i].type.startsWith('image/')) {
                        imageFile = clipboardData.files[i];
                        break;
                    }
                }
            }

            if (imageFile) {
                e.preventDefault();
                e.stopPropagation();

                try {
                    const alt = imageFile.name ? imageFile.name.replace(/\.[^/.]+$/, "") : 'imagen';
                    const url = await uploadImageFile(imageFile);

                    if (typeof editor.exec === 'function') {
                        editor.exec('addImage', { imageUrl: url, altText: alt });
                    } else if (typeof editor.insertText === 'function') {
                        editor.insertText(`![${alt}](${url})`);
                    }
                } catch (err) {
                    console.error('Error al subir imagen pegada:', err);
                    alert('No se pudo subir la imagen al servidor. Por favor verifique el formato o intente de nuevo.');
                }
            }
        }, true);

        // Interceptar evento drop
        container.addEventListener('drop', async (e) => {
            if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                const file = e.dataTransfer.files[0];
                if (file.type && file.type.startsWith('image/')) {
                    e.preventDefault();
                    e.stopPropagation();

                    try {
                        const alt = file.name ? file.name.replace(/\.[^/.]+$/, "") : 'imagen';
                        const url = await uploadImageFile(file);
                        if (typeof editor.exec === 'function') {
                            editor.exec('addImage', { imageUrl: url, altText: alt });
                        }
                    } catch (err) {
                        console.error('Error al subir imagen arrastrada:', err);
                    }
                }
            }
        }, true);
    }

    window.attachImageUploadToEditor = attachImageUploadToEditor;
})();

