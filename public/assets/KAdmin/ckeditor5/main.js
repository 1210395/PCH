// Your custom upload adapter class
class MyUploadAdapter {
  constructor(loader) {
    this.loader = loader;
  }

  upload() {
    return this.loader.file
      .then(file => new Promise((resolve, reject) => {
        this._initRequest();
        this._initListeners(resolve, reject, file);
        this._sendRequest(file);
      }));
  }

  abort() {
    if (this.xhr) {
      this.xhr.abort();
    }
  }

  _initRequest() {
    const xhr = this.xhr = new XMLHttpRequest();
    xhr.open('POST', site_url+'/en/Control/upload-image', true);

    // Try multiple ways to get the CSRF token
    const csrfToken = document.querySelector('input[name="_token"]')?.value;
    console.log('CSRF Token:', csrfToken);

    if (csrfToken) {
      xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
    }
    
    xhr.responseType = 'json';
  }

  _getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
  }

  _initListeners(resolve, reject, file) {
    const xhr = this.xhr;
    const loader = this.loader;
    const genericErrorText = `Couldn't upload file: ${file.name}.`;

    xhr.addEventListener('error', () => reject(genericErrorText));
    xhr.addEventListener('abort', () => reject());
    xhr.addEventListener('load', () => {
      const response = xhr.response;

      if (!response || response.error) {
        return reject(response && response.error ? response.error : genericErrorText);
      }

      resolve({
        default: response.url
      });
    });

    if (xhr.upload) {
      xhr.upload.addEventListener('progress', evt => {
        if (evt.lengthComputable) {
          loader.uploadTotal = evt.total;
          loader.uploaded = evt.loaded;
        }
      });
    }
  }

  _sendRequest(file) {
    const data = new FormData();
    data.append('upload', file);
    this.xhr.send(data);
  }
}

// Register your custom upload adapter plugin
function MyCustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
    return new MyUploadAdapter(loader);
    };
}
function lfm(type = 'file', { prefix = folder+'/laravel-filemanager' } = {}) {
  return new Promise(resolve => {
    const route = prefix + '?type=' + encodeURIComponent(type);
    window.open(route, 'FileManager', 'width=1100,height=600');
    window.SetUrl = items => resolve({ items, urls: items.map(i => i.url) });
  });
}

function LfmLinkPlugin(editor) {
  editor.ui.componentFactory.add('lfmLink', locale => {
    const view = new editor.ui.button.ButtonView(locale);
    view.set({ label: 'Browse Server', withText: true, tooltip: true });
    view.on('execute', async () => {
      const { urls } = await lfm('file');
      if (urls.length) {
        editor.execute('link', urls[0]);   // apply URL to selection
        editor.editing.view.focus();
      }
    });
    return view;
  });
}

/*var editorElement=document.querySelector('.editor');
const contentLanguage = editorElement.getAttribute('data-language') || 'en';
const contentDirection = editorElement.getAttribute('data-dir') || 'ltr';*/

let editorInstances = [];

//initializeCKEditors();

function initializeCKEditors(editor_id=".editor", contentLanguage="", contentDirection="", height="400") {
    // Destroy all existing editors
    destroyEditors().then(() => {
        document.querySelectorAll(editor_id).forEach((element) => {
            if(contentLanguage=="") contentLanguage = element.getAttribute('data-language') || 'en';
            if(contentDirection=="") contentDirection = element.getAttribute('data-dir') || 'ltr';

            ClassicEditor
                .create(element, {
                    extraPlugins: [MyCustomUploadAdapterPlugin],
                    toolbar: {
                        items: [
                            'undo', 'redo', '|',
                            'findAndReplace', 'selectAll', 'removeFormat', '|',
                            'heading', '|',
                            'fontFamily', 'fontSize', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
                            'bold', 'italic', 'underline', 'strikethrough', 'subscript', 'superscript', '|',
                            'alignment', 'outdent', 'indent', '|',
                            'bulletedList', 'numberedList', 'todoList', '|',
                            'link', 'blockQuote', 'code', 'codeBlock', 'insertTable', 'horizontalLine', 'pageBreak',
                            'specialCharacters', 'htmlEmbed', 'sourceEditing', '|',
                            'imageUpload', 'mediaEmbed'
                        ],
                        shouldNotGroupWhenFull: true
                    },
                    link: {
                        decorators: {
                            openInNewTab: {
                                mode: 'manual',
                                label: 'Open in new tab',
                                attributes: {
                                    target: '_blank',
                                    rel: 'noopener noreferrer'
                                }
                            },
                            /*
                            openInSameTab: {
                                mode: 'manual',
                                label: 'Open in same tab',
                                attributes: {
                                    target: '_self'
                                }
                            },
                            openInParent: {
                                mode: 'manual',
                                label: 'Open in parent frame',
                                attributes: {
                                    target: '_parent'
                                }
                            },
                            openInTop: {
                                mode: 'manual',
                                label: 'Open in top window',
                                attributes: {
                                    target: '_top'
                                }
                            }
                                */
                        }
                    },
                    image: {
                        styles: {
                            options: [
                                'inline',       // default inline image
                                'block',        // full width
                                'side',         // side (float right usually)
                                'alignLeft',    // new left alignment
                                'alignCenter',  // new center alignment
                                'alignRight'    // new right alignment
                            ]
                        },
                        toolbar: [
                            'imageTextAlternative',
                            'toggleImageCaption',
                            'linkImage',
                            '|',
                            'imageStyle:inline',
                            'imageStyle:block',
                            'imageStyle:side',
                            '|',
                            'imageStyle:alignLeft',
                            'imageStyle:alignCenter',
                            'imageStyle:alignRight',
                            '|',
                            'resizeImage'
                        ]
                    },
                    table: {
                        contentToolbar: [
                            'tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties'
                        ]
                    },
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                            { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                            { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                            { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                            { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
                        ]
                    },
                    language: {
                        ui: contentLanguage,
                        content: contentLanguage
                    },
                    htmlSupport: {
                        allow: [
                            {
                                name: 'a',
                                attributes: ['href', 'target', 'rel', 'nofollow'],
                                styles: true,
                                classes: true,
                                allowEmpty: true  // <-- Add this
                            },
                            {
                                name: 'div',
                                attributes: true,
                                styles: true,
                                classes: true,
                                allowEmpty: true  // <-- Add this
                            },
                            {
                                name: 'span',
                                attributes: true,
                                styles: true,
                                classes: true,
                                allowEmpty: true  // <-- Important for empty <span>
                            },
                            {
                                name: 'i',
                                attributes: ["class", "style"],
                                styles: true,
                                classes: true,
                                allowEmpty: true  // <-- Important for empty <span>
                            }
                        ]
                    },
                    pasteFromOffice: {
                        preserveHeaders: true,
                        preserveFooters: true,
                        preserveLists: true,
                        preserveTables: true,
                        preserveImages: true
                    },
                    clipboard: { matchVisual: false },
                    removePlugins: [ 'Markdown' ]
                })
                .then(editor => {
                    editor.editing.view.change(writer => {
                        writer.setAttribute('dir', contentDirection, editor.editing.view.document.getRoot());
                        writer.setStyle('min-height', height + 'px', editor.editing.view.document.getRoot());
                    });

                    const schema = editor.model.schema;
                    const conversion = editor.conversion;

                    // Allow <i> inside all blocks including headings
                    schema.register('i', {
                        allowIn: '$block',      // inline inside all blocks
                        isInline: true,
                        allowAttributes: ['class', 'style']
                    });

                    schema.register('span', {
                        allowIn: '$block',
                        isInline: true,
                        allowAttributes: ['class', 'style']
                    });

                    // Upcast <i> from view to model
                    conversion.for('upcast').elementToElement({
                        view: 'i',
                        model: (viewElement, { writer }) => writer.createElement('i', viewElement.getAttributes())
                    });

                    // Upcast <span>
                    conversion.for('upcast').elementToElement({
                        view: 'span',
                        model: (viewElement, { writer }) => writer.createElement('span', viewElement.getAttributes())
                    });

                    // Downcast <i> from model to view
                    conversion.for('downcast').elementToElement({
                        model: 'i',
                        view: (modelElement, { writer }) => writer.createContainerElement('i', modelElement.getAttributes())
                    });

                    // Downcast <span>
                    conversion.for('downcast').elementToElement({
                        model: 'span',
                        view: (modelElement, { writer }) => writer.createContainerElement('span', modelElement.getAttributes())
                    });



                    // Add both enhancements
                    enhanceLinkBalloonWithLFM(editor);
                    //enhanceLinkTargetDropdown(editor);

                    // Hook the built-in Link command to open LFM first
                    /*
                    const linkCmd = editor.commands.get('link');
                    const originalExecute = linkCmd.execute.bind(linkCmd);

                    linkCmd.execute = async (hrefOrOptions) => {
                        if (typeof hrefOrOptions === 'string' || (hrefOrOptions && hrefOrOptions.href)) {
                            return originalExecute(hrefOrOptions);
                        }

                        const { items, urls } = await lfm('file');
                        if (!urls.length) return;

                        const url  = urls[0];
                        const name = items[0]?.name || url.split('/').pop();

                        const model = editor.model;
                        model.change(writer => {
                            const selection = model.document.selection;
                            if (selection.isCollapsed) {
                                const text = writer.createText(name, { linkHref: url });
                                const p = writer.createElement('paragraph');
                                writer.append(text, p);
                                model.insertContent(p, selection);
                            } else {
                                originalExecute(url);
                            }
                        });

                        editor.editing.view.focus();
                    };
					*/
                    editorInstances.push(editor);
                   
                })
                .catch(error => {
                    console.error('Error initializing CKEditor:', error);
                });
        });
    });
}
function enhanceLinkBalloonWithLFM(editor) {
    const observer = new MutationObserver(() => {
        const forms = document.querySelectorAll('.ck-body-wrapper .ck-link-form');
        forms.forEach(form => {
            if (form.dataset.lfmEnhanced === '1') return;

            const urlInput = form.querySelector('input[type="url"]') || form.querySelector('.ck-input-text');
            if (!urlInput) return;

            // Create Browse Server button
            const browseBtn = document.createElement('button');
            browseBtn.type = 'button';
            browseBtn.className = 'ck ck-button ck-button_with-text';
            browseBtn.style.marginInlineStart = '8px';
            browseBtn.textContent = 'Browse Server';

            // Create Internal Pages button
            const internalBtn = document.createElement('button');
            internalBtn.type = 'button';
            internalBtn.className = 'ck ck-button ck-button_with-text';
            internalBtn.style.marginInlineStart = '8px';
            internalBtn.textContent = 'Internal Pages';

            // Create container for buttons
            const buttonContainer = document.createElement('div');
            buttonContainer.style.display = 'flex';
            buttonContainer.style.gap = '8px';
            buttonContainer.style.marginTop = '8px';
            buttonContainer.appendChild(browseBtn);
            buttonContainer.appendChild(internalBtn);

            // Insert after URL input
            const urlRow = urlInput.closest('.ck-labeled-field-view') || urlInput.parentElement;
            (urlRow || form).appendChild(buttonContainer);

            // Browse Server functionality
            browseBtn.addEventListener('click', async () => {
                try {
                    const { urls } = await lfm('file');
                    if (!urls || !urls.length) return;

                    urlInput.value = urls[0];
                    urlInput.dispatchEvent(new Event('input', { bubbles: true }));
                    urlInput.focus();
                } catch (e) {
                    console.error('LFM error:', e);
                }
            });

            // Internal Pages functionality
            internalBtn.addEventListener('click', () => {
                // Set a cookie with the current URL input ID for the popup to access
                const urlInputId = urlInput.id || '';
                document.cookie = `ckeditor_input_id=${urlInputId}; path=/; max-age=300`; // 5 minutes
                
                // Open the internal pages popup
                const popupWidth = 1000;
                const popupHeight = 700;
                const left = (screen.width - popupWidth) / 2;
                const top = (screen.height - popupHeight) / 2;
                
                window.open(
                    site_url + '/Control/getLinks',
                    'InternalPagesPopup',
                    `width=${popupWidth},height=${popupHeight},top=${top},left=${left},resizable=yes,scrollbars=yes`
                );
            });

            form.dataset.lfmEnhanced = '1';
        });
    });

    const uiRoot = editor.ui.view.body?._bodyCollection?.container || document.body;
    observer.observe(uiRoot, { childList: true, subtree: true });
    editor.on('destroy', () => observer.disconnect());
}
// Enhanced Link Target Dropdown - UPDATED VERSION
/*
function enhanceLinkTargetDropdown(editor) {
    const observer = new MutationObserver(() => {
        const linkForms = document.querySelectorAll('.ck-link-form:not([data-has-target])');
        
        linkForms.forEach(form => {
            form.dataset.hasTarget = true;
            
            const container = document.createElement('div');
            container.className = 'ck-labeled-field-view';
            container.style.marginTop = '10px';
            container.innerHTML = `
                <span class="ck-labeled-field-view__label">Open in:</span>
                <select class="ck ck-input-text ck-target-select">
                    <option value="_self">Same window</option>
                    <option value="_blank">New tab</option>
                    <option value="_parent">Parent frame</option>
                    <option value="_top">Top window</option>
                </select>
            `;
            
            const select = container.querySelector('select');
            const urlInput = form.querySelector('.ck-input-text');
            
            // Insert after URL input
            if (urlInput && urlInput.parentNode) {
                urlInput.parentNode.insertBefore(container, urlInput.nextSibling);
            } else {
                form.appendChild(container);
            }

            // Update selection when dropdown is shown
            const updateSelection = () => {
                const selection = editor.model.document.selection;
                const range = selection.getFirstRange();
                
                if (range) {
                    // Check which decorator is currently active
                    const decorators = editor.config.get('link.decorators') || {};
                    let currentTarget = '_self';
                    
                    for (const [key, decorator] of Object.entries(decorators)) {
                        const decoratorCommand = editor.commands.get('link' + key.charAt(0).toUpperCase() + key.slice(1));
                        if (decoratorCommand && decoratorCommand.value) {
                            currentTarget = decorator.attributes.target;
                            break;
                        }
                    }
                    
                    select.value = currentTarget;
                }
            };

            // Initial update
            updateSelection();

            // Handle changes
            select.addEventListener('change', () => {
                editor.model.change(writer => {
                    const selection = editor.model.document.selection;
                    const range = selection.getFirstRange();
                    
                    if (range) {
                        // Remove all target decorators first
                        const decorators = editor.config.get('link.decorators') || {};
                        for (const [key] of Object.entries(decorators)) {
                            const decoratorCommand = editor.commands.get('link' + key.charAt(0).toUpperCase() + key.slice(1));
                            if (decoratorCommand && decoratorCommand.value) {
                                decoratorCommand.execute({ forceDisabled: false });
                            }
                        }
                        
                        // Apply the selected decorator
                        const targetValue = select.value;
                        let decoratorToApply = null;
                        
                        for (const [key, decorator] of Object.entries(decorators)) {
                            if (decorator.attributes.target === targetValue) {
                                decoratorToApply = key;
                                break;
                            }
                        }
                        
                        if (decoratorToApply) {
                            const decoratorCommand = editor.commands.get('link' + decoratorToApply.charAt(0).toUpperCase() + decoratorToApply.slice(1));
                            if (decoratorCommand) {
                                decoratorCommand.execute({ forceDisabled: false });
                            }
                        }
                    }
                });
            });

            // Update on selection changes
            const documentSelection = editor.model.document.selection;
            documentSelection.on('change', updateSelection);
            
            // Cleanup when form is removed
            const cleanup = () => {
                documentSelection.off('change', updateSelection);
                form.removeEventListener('DOMNodeRemoved', cleanup);
            };
            form.addEventListener('DOMNodeRemoved', cleanup);
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
    editor.on('destroy', () => observer.disconnect());
}
    */
function destroyEditors() {
    return Promise.all(editorInstances.map(editor => editor.destroy()))
    .then(() => {
        editorInstances = [];
    })
    .catch(error => {
        console.error('Error destroying editor instances:', error);
    });
}

/*ClassicEditor
.create(document.querySelector('.editor'))
.then(editor => {
    console.log('Editor was initialized', editor);
})
.catch(error => {
    console.error('Error initializing CKEditor:', error);
});*/