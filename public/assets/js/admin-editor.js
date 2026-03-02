document.addEventListener('DOMContentLoaded', () => {
    
    // Safety check - only run if editorjs container exists
    if (!document.getElementById('editorjs')) return;

    // Cyrillic to Latin transliteration for slug
    const translit = (str) => {
        const ru = {
            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 
            'е': 'e', 'ё': 'e', 'ж': 'j', 'з': 'z', 'и': 'i', 
            'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 
            'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 
            'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch', 
            'ш': 'sh', 'щ': 'shch', 'ъ': '', 'ы': 'y', 'ь': '', 
            'э': 'e', 'ю': 'yu', 'я': 'ya'
        };
        let n_str = '';
        str = str.toLowerCase();
        for (let i = 0; i < str.length; i++) {
            if (ru[str[i]] !== undefined) n_str += ru[str[i]];
            else if (/[a-z0-9]/.test(str[i])) n_str += str[i];
            else if (str[i] === ' ' || str[i] === '-') n_str += '-';
        }
        return n_str.replace(/-+/g, '-').replace(/^-|-$/g, '');
    }

    const titleInput = document.getElementById('article-title');
    const slugInput = document.getElementById('seo-slug');
    const seoTitleInput = document.getElementById('seo-title');

    titleInput.addEventListener('input', () => {
        // Auto-fill seo title if empty
        if (seoTitleInput.value === '') {
            seoTitleInput.value = titleInput.value;
        }
    });

    window.toggleSeoPanel = () => {
        const panel = document.getElementById('seo-sidebar');
        if (panel) {
            panel.classList.toggle('translate-x-full');
        }
    };

    // Cover Image Upload Handler
    const coverInput = document.getElementById('cover-upload-input');
    const seoCover = document.getElementById('seo-cover');
    const coverPreview = document.getElementById('cover-preview');
    const coverPlaceholder = document.getElementById('cover-placeholder');
    const coverLoading = document.getElementById('cover-loading');

    if (coverInput) {
        coverInput.addEventListener('change', async (e) => {
            const file = e.target.files[0];
            if (!file) return;

            coverLoading.classList.remove('hidden');
            if (coverPlaceholder) coverPlaceholder.style.opacity = '0.5';

            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('/admin/blog/upload-image', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    seoCover.value = result.file.url;
                    coverPreview.src = result.file.url;
                    coverPreview.classList.remove('hidden');
                    if (coverPlaceholder) coverPlaceholder.classList.add('hidden');
                } else {
                    alert('Ошибка загрузки обложки: ' + result.message);
                }
            } catch (error) {
                console.error(error);
                alert('Сбой сети при загрузке обложки');
            } finally {
                coverLoading.classList.add('hidden');
                if (coverPlaceholder) coverPlaceholder.style.opacity = '1';
                coverInput.value = ''; // reset so same file can trigger change again
            }
        });
    }

    // Initialize Editor.js
    const editor = new EditorJS({
        holder: 'editorjs',
        placeholder: 'Нажмите Tab чтобы открыть меню, или просто начните писать...',
        data: window.articleData && Object.keys(window.articleData).length > 0 ? window.articleData : undefined,
        tools: {
            header: {
                class: Header,
                inlineToolbar: ['marker', 'link'],
                config: {
                    placeholder: 'Введите заголовок',
                    levels: [2, 3, 4],
                    defaultLevel: 2
                }
            },
            list: {
                class: EditorjsList,
                inlineToolbar: true,
            },
            image: {
                class: ImageTool,
                config: {
                    endpoints: {
                        byFile: '/admin/blog/upload-image', // The endpoint we created
                    },
                    captionPlaceholder: 'Описание изображения (будет использовано как SEO alt текст)'
                }
            },
            code: {
                class: CodeTool,
            },
            quote: {
                class: Quote,
                inlineToolbar: true,
                config: {
                    quotePlaceholder: 'Введите цитату',
                    captionPlaceholder: 'Автор цитаты',
                },
            },
            marker: {
                class: Marker,
                shortcut: 'CMD+SHIFT+M',
            },
            delimiter: Delimiter,
        }
    });

    // Save functionality
    const saveArticle = async (status) => {
        const btn = status === 'published' ? document.getElementById('btn-publish') : document.getElementById('btn-draft');
        const originalText = btn.innerText;
        btn.innerText = 'Сохранение...';
        btn.disabled = true;

        try {
            const outputData = await editor.save();
            const title = titleInput.value.trim();
            
            if (!title) {
                alert('Нельзя сохранить без заголовка!');
                btn.innerText = originalText;
                btn.disabled = false;
                titleInput.focus();
                return;
            }

            // Generate auto-slug if empty
            if (!slugInput.value.trim() && !window.articleId) {
                slugInput.value = translit(title);
            }

            const payload = {
                id: window.articleId,
                title: title,
                slug: slugInput.value.trim(),
                seoTitle: seoTitleInput.value.trim(),
                seoDescription: document.getElementById('seo-description').value.trim(),
                coverImage: document.getElementById('seo-cover').value.trim(),
                status: status,
                categories: document.getElementById('article-categories') ? Array.from(document.getElementById('article-categories').selectedOptions).map(o => parseInt(o.value)) : [],
                content: outputData
            };

            const response = await fetch('/admin/blog/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (result.success) {
                window.articleId = result.id;
                document.getElementById('save-status').innerText = 'Успешно сохранено ' + new Date().toLocaleTimeString();
                
                // Show floating message
                const msg = document.createElement('div');
                msg.className = 'fixed bottom-6 right-6 bg-green-500 text-white px-6 py-3 rounded-full shadow-2xl z-50 animate-bounce';
                msg.innerText = status === 'published' ? 'Статья опубликована и добавлена в sitemap!' : 'Черновик сохранен';
                document.body.appendChild(msg);
                setTimeout(() => msg.remove(), 4000);
            } else {
                alert('Ошибка: ' + result.message);
            }

        } catch (error) {
            console.error('Saving failed: ', error);
            alert('Сбой сохранения. Проверьте консоль.');
        }

        btn.innerText = originalText;
        btn.disabled = false;
    };

    document.getElementById('btn-publish').addEventListener('click', () => saveArticle('published'));
    document.getElementById('btn-draft').addEventListener('click', () => saveArticle('draft'));
    
    // Delete functionality
    const btnDelete = document.getElementById('btn-delete');
    if (btnDelete) {
        btnDelete.addEventListener('click', async () => {
            if(confirm('Вы уверены, что хотите навсегда удалить эту статью? Это действие нельзя отменить.')) {
                btnDelete.innerText = 'Удаление...';
                try {
                    const response = await fetch('/admin/blog/delete/' + window.articleId, { method: 'POST' });
                    const res = await response.json();
                    if(res.success) {
                        window.location.href = '/admin/blog';
                    }
                } catch(e) {
                    alert('Ошибка удаления.');
                    btnDelete.innerText = 'Удалить статью';
                }
            }
        });
    }
});
