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
                    // ... (keep your existing config)
                    
                    // Enhanced HTML support for links
                    htmlSupport: {
                        allow: [
                            {
                                name: 'a',
                                attributes: {
                                    href: true,
                                    target: true,
                                    rel: true
                                }
                            }
                        ]
                    },
                    
                    // Enhanced link configuration
                    link: {
                        decorators: {
                            openInNewTab: {
                                mode: 'manual',
                                label: 'Open in new tab',
                                attributes: {
                                    target: '_blank',
                                    rel: 'noopener noreferrer'
                                }
                            }
                        },
                        addTargetToExternalLinks: true,
                        defaultProtocol: 'https://'
                    },
                    
                    // ... (rest of your config)
                })
                .then(editor => {
                    // ... (keep your existing editor setup code)
                    
                    // Ensure target="_blank" is preserved
                    editor.conversion.for('upcast').add(upcastDispatcher => {
                        upcastDispatcher.on('element:a', (evt, data, conversionApi) => {
                            const viewA = data.viewItem;
                            const modelA = data.modelRange.start.parent;
                            
                            if (viewA.hasAttribute('target')) {
                                conversionApi.writer.setAttribute(
                                    'linkTarget',
                                    viewA.getAttribute('target'),
                                    modelA
                                );
                            }
                            
                            if (viewA.hasAttribute('rel')) {
                                conversionApi.writer.setAttribute(
                                    'linkRel',
                                    viewA.getAttribute('rel'),
                                    modelA
                                );
                            }
                        });
                    });
                    
                    editor.conversion.for('downcast').add(dispatcher => {
                        dispatcher.on('attribute:linkHref', (evt, data, conversionApi) => {
                            if (!conversionApi.consumable.consume(data.item, 'attribute:linkHref')) {
                                return;
                            }
                            
                            const viewWriter = conversionApi.writer;
                            const viewElement = conversionApi.mapper.toViewElement(data.item);
                            
                            if (data.item.hasAttribute('linkTarget')) {
                                viewWriter.setAttribute(
                                    'target',
                                    data.item.getAttribute('linkTarget'),
                                    viewElement
                                );
                            }
                            
                            if (data.item.hasAttribute('linkRel')) {
                                viewWriter.setAttribute(
                                    'rel',
                                    data.item.getAttribute('linkRel'),
                                    viewElement
                                );
                            }
                        });
                    });
                    
                    // ... (rest of your editor setup)
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