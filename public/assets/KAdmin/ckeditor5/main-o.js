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
function lfm(type = 'file', { prefix = '/demo25/laravel-filemanager' } = {}) {
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
							target: {  // Single decorator for target selection
								mode: 'manual',
								attributes: {
									// Will be set dynamically
								}
							}
						},
						addTargetToExternalLinks: true
					},
                    image: {
						styles: {
							options: ['linkImage']
						},
                        toolbar: [
                            'imageTextAlternative', 'toggleImageCaption', 'linkImage', '|',
                            'imageStyle:inline', 'imageStyle:block', 'imageStyle:side', '|',
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
            attributes: ['href', 'target', 'rel']
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

    // Add both enhancements
    enhanceLinkBalloonWithLFM(editor);
    enhanceLinkTargetDropdown(editor);

    // Hook the built-in Link command to open LFM first
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

    // Enhanced Conversion Handlers
    editor.conversion.for('downcast').add(dispatcher => {
    // Handle link target attributes
    dispatcher.on('attribute:linkHref', (evt, data, conversionApi) => {
        const viewWriter = conversionApi.writer;
        const viewElement = conversionApi.mapper.toViewElement(data.item);
        
        if (!viewElement) return;
        
        // Check for any active decorators
        const decoratorKeys = Object.keys(editor.config.get('link.decorators') || []);
        for (const key of decoratorKeys) {
            const attrName = 'linkIs' + key.charAt(0).toUpperCase() + key.slice(1);
            if (data.item.hasAttribute(attrName)) {
                const decorator = editor.config.get('link.decorators')[key];
                if (decorator?.attributes) {
                    Object.entries(decorator.attributes).forEach(([attr, value]) => {
                        viewWriter.setAttribute(attr, value, viewElement);
                    });
                }
            }
        }
    });
    
    // Handle decorator attribute changes
    const decoratorKeys = Object.keys(editor.config.get('link.decorators') || []);
    decoratorKeys.forEach(key => {
        const attrName = 'linkIs' + key.charAt(0).toUpperCase() + key.slice(1);
        
        dispatcher.on(`attribute:${attrName}`, (evt, data, conversionApi) => {
            const viewWriter = conversionApi.writer;
            const viewElement = conversionApi.mapper.toViewElement(data.item);
            
            if (!viewElement) return;
            
            const decorator = editor.config.get('link.decorators')[key];
            if (!decorator?.attributes) return;
            
            Object.entries(decorator.attributes).forEach(([attr, value]) => {
                if (data.attributeNewValue) {
                    viewWriter.setAttribute(attr, value, viewElement);
                } else {
                    viewWriter.removeAttribute(attr, viewElement);
                }
            });
        });
    });
});

    editorInstances.push(editor);
})
.catch(error => {
    console.error('Error initializing CKEditor:', error);
});
        });
    });
}

// Enhanced Link Balloon with Browse Server button
function enhanceLinkBalloonWithLFM(editor) {
    const observer = new MutationObserver(() => {
        const forms = document.querySelectorAll('.ck-body-wrapper .ck-link-form');
        forms.forEach(form => {
            if (form.dataset.lfmEnhanced === '1') return;

            const urlInput = form.querySelector('input[type="url"]') || form.querySelector('.ck-input-text');
            if (!urlInput) return;

            // Create Browse Server button
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ck ck-button ck-button_with-text';
            btn.style.marginInlineStart = '8px';
            btn.textContent = 'Browse Server';

            // Insert next to URL input
            const urlRow = urlInput.closest('.ck-labeled-field-view') || urlInput.parentElement;
            (urlRow || form).appendChild(btn);

            btn.addEventListener('click', async () => {
                try {
                    const { urls } = await lfm('file');
                    if (!urls || !urls.length) return;

                    urlInput.value = urls[0];
                    urlInput.dispatchEvent(new Event('input', { bubbles: true }));

                    const applyButton = form.querySelector('.ck-button-save, .ck-button_with-text[aria-label="Save"], .ck-button_with-text[title="Save"]');
                    if (applyButton) applyButton.click();
                } catch (e) {
                    console.error('LFM error:', e);
                }
            });

            form.dataset.lfmEnhanced = '1';
        });
    });

    const uiRoot = editor.ui.view.body?._bodyCollection?.container || document.body;
    observer.observe(uiRoot, { childList: true, subtree: true });
    editor.on('destroy', () => observer.disconnect());
}

// Enhanced Link Target Dropdown
// Replace your enhanceLinkTargetDropdown function with this version
function enhanceLinkTargetDropdown(editor) {
    // 1. Register the linkTarget attribute
    editor.model.schema.extend('$text', {
        allowAttributes: ['linkTarget']
    });

    // 2. Modify the link command
    const linkCommand = editor.commands.get('link');
    const originalExecute = linkCommand.execute.bind(linkCommand);
    
    linkCommand.execute = function(href, options = {}) {
        const { target, ...restOptions } = options;
        
        editor.model.change(writer => {
            const selection = editor.model.document.selection;
            if (target) {
                writer.setAttribute('linkTarget', target, selection.getFirstRange());
            }
        });
        
        return originalExecute(href, restOptions);
    };

    // 3. Enhanced conversion for ALL link cases
    editor.conversion.for('downcast').add(dispatcher => {
        // Handle target attribute changes
        dispatcher.on('attribute:linkTarget', (evt, data, conversionApi) => {
            const viewWriter = conversionApi.writer;
            const viewElement = conversionApi.mapper.toViewElement(data.item);
            
            if (!viewElement) return;
            
            // Find the anchor element (could be direct <a> or inside <figure>)
            let anchor = viewElement.is('a') ? viewElement : null;
            if (!anchor && viewElement.is('figure')) {
                const children = Array.from(viewElement.getChildren());
                anchor = children.find(child => child.is('a'));
            }
            
            if (anchor) {
                if (data.attributeNewValue) {
                    viewWriter.setAttribute('target', data.attributeNewValue, anchor);
                    if (data.attributeNewValue === '_blank') {
                        viewWriter.setAttribute('rel', 'noopener noreferrer', anchor);
                    }
                } else {
                    viewWriter.removeAttribute('target', anchor);
                    viewWriter.removeAttribute('rel', anchor);
                }
            }
        });

        // Also handle new link creation
        dispatcher.on('insert:a', (evt, data, conversionApi) => {
    const viewWriter = conversionApi.writer;
    const viewElement = conversionApi.mapper.toViewElement(data.item);

    const target = data.item.getAttribute('linkTarget'); // No fallback

    if (viewElement && target) {
        viewWriter.setAttribute('target', target, viewElement);
        if (target === '_blank') {
            viewWriter.setAttribute('rel', 'noopener noreferrer', viewElement);
        }
    }
}, { priority: 'high' });
    });

    // 4. UI Dropdown implementation
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

            // Set initial value
            const updateSelection = () => {
                const selection = editor.model.document.selection;
                const target = selection.getAttribute('linkTarget') || '_self';
                select.value = target;
            };
            updateSelection();

            // Handle changes
            select.addEventListener('change', () => {
                editor.model.change(writer => {
                    const selection = editor.model.document.selection;
                    const range = selection.getFirstRange();
                    writer.setAttribute('linkTarget', select.value, range);
                    
                    // Force update of the link
                    const linkHref = selection.getAttribute('linkHref');
                    if (linkHref) {
                        writer.removeAttribute('linkHref', range);
                        writer.setAttribute('linkHref', linkHref, range);
                    }
                });
            });

            // Update on selection changes
            editor.model.document.on('change', updateSelection);
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
    editor.on('destroy', () => observer.disconnect());
}
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