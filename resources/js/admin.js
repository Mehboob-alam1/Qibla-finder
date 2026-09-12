document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const uploadUrl = document.querySelector('meta[name="media-upload"]')?.getAttribute('content');

const uploadImage = (file) =>
    new Promise((resolve, reject) => {
        const body = new FormData();
        body.append('file', file);

        fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                Accept: 'application/json',
            },
            body,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Upload failed');
                }
                return response.json();
            })
            .then((data) => resolve(data.location))
            .catch(reject);
    });

if (window.tinymce && document.querySelector('[data-editor]')) {
    window.tinymce.init({
        selector: 'textarea[data-editor]',
        base_url: 'https://cdn.jsdelivr.net/npm/tinymce@7.6.1',
        suffix: '.min',
        height: 520,
        menubar: 'edit view insert format table',
        plugins: 'lists link image table media autoresize code',
        toolbar:
            'undo redo | blocks | h1 h2 h3 h4 | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | blockquote hr | removeformat',
        block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6',
        image_caption: true,
        image_advtab: true,
        image_title: true,
        image_dimensions: true,
        object_resizing: true,
        automatic_uploads: true,
        images_reuse_filename: false,
        file_picker_types: 'image',
        image_class_list: [
            { title: 'Original', value: '' },
            { title: 'Small', value: 'content-img-sm' },
            { title: 'Medium', value: 'content-img-md' },
            { title: 'Large', value: 'content-img-lg' },
            { title: 'Full width', value: 'content-img-full' },
        ],
        content_style:
            'body { font-family: Plus Jakarta Sans, sans-serif; font-size: 16px; line-height: 1.7; color: #0d3b2e; } img { max-width: 100%; height: auto; } .content-img-sm { width: 240px; } .content-img-md { width: 420px; } .content-img-lg { width: 720px; } .content-img-full { width: 100%; }',
        images_upload_handler: (blobInfo) => uploadImage(blobInfo.blob()),
        file_picker_callback: (callback, _value, meta) => {
            if (meta.filetype !== 'image') {
                return;
            }
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/jpeg,image/png,image/gif,image/webp';
            input.addEventListener('change', async () => {
                const file = input.files?.[0];
                if (!file) {
                    return;
                }
                try {
                    const location = await uploadImage(file);
                    callback(location, { alt: file.name.replace(/\.[^.]+$/, '') });
                } catch {
                    window.alert('The image could not be uploaded. Try a JPG, PNG, GIF, or WebP under 5 MB.');
                }
            });
            input.click();
        },
        setup: (editor) => {
            editor.on('change input undo redo', () => editor.save());
        },
    });
}
