const en = {
    plugins: {
        files: {
            header: {
                export: {
                    selected: 'export selected files <span class="badge badge-secondary">{cnt}</span>'
                }
            },
            image_filters: {
                grayscale: 'Grayscale',
                bw: 'Black & White',
                invert: 'Invert',
                sepia: 'Sepia',
                remove_color: 'Remove Color',
                brightness: 'Brightness',
                contrast: 'Contrast',
                hue: 'Hue',
                saturation: 'Saturation',
                noise: 'Noise',
                pixelate: 'Pixelate',
                blur: 'Blur',
                sharpen: 'Sharpen',
                emboss: 'Emboss',
                save: 'Save Changes'
            },
            toasts: {
                deleted: {
                    title: 'File deleted',
                    msg: '{name} successfully deleted.'
                },
                unlinked: {
                    title: 'Link removed',
                    msg: 'Link from {name} to {eName} successfully removed.'
                },
                linked: {
                    title: 'Link added',
                    msg: 'Link from {name} to {eName} successfully added.'
                }
            },
            'include-sub-files': 'Show files from Sub-Entities',
            upload: {
                title: 'Drop Zone',
                desc: 'Drop Files here or click on the box..',
                error: 'Error while uploading your file.',
                finish: {
                    title: 'Upload of {files} file finished | Upload of {files} files finished',
                    msg: 'No errors | One file not uploaded | {files} files not uploaded'
                }
            },
            modal: {
                detail: {
                    title: '{name} - Details',
                    'toggle-html': 'Toggle HTML-Rendering',
                    'no-links': 'No links. Select a entity first or use the search below to link to entities.',
                    'link-further-entities': 'Link to further entities',
                    toasts: {
                        'tags-updated': {
                            title: 'Tags updated',
                            msg: 'Tags of {name} successfully updated.'
                        }
                    },
                    previous: 'Previous File',
                    next: 'Next File',
                    threed: {
                        'load-sub-models': 'Load models of Sub-Entities'
                    },
                    dicom: {
                        controls: {
                            title: 'Controls',
                            zoom: 'Zoom',
                            'zoom-desc': 'Use <kbd><kbd>Right Click</kbd> + <kbd>Drag</kbd></kbd> up/down to zoom out/in.',
                            move: 'Move',
                            'move-desc': 'Use <kbd><kbd>Middle Click</kbd> + <kbd>Drag</kbd></kbd> to move the image.',
                            voi: 'VOI (Values of Interest)',
                            'voi-desc': 'Use <kbd><kbd>Left Click</kbd> + <kbd>Drag</kbd></kbd> to change window width (up/down) and window center (left/right).',
                        },
                        metadata: {
                            title: '@:plugins.files.modal.detail.metadata.title',
                            'search-placeholder': 'Search for Metadata-Tags...'
                        },
                        save: 'Save Image',
                        wwwc: 'WW/WC'
                    },
                    archive: {
                        info: 'Click on files to download them. Downloading folders is currently not supported.',
                        files_in_folder: 'Contains <span class="fw-medium">no</span> files | Contains <span class="fw-medium">one</span> file | Contains <span class="fw-medium">{cnt}</span> files'
                    },
                    undef: {
                        info: `The mime-type <code>{mime}</code> of <span class="font-italic">{name}</span> is currently not supported in Spacialist.
                        If you need support for this mime-type, please open an <a href="https://github.com/eScienceCenter/Spacialist/issues/new">issue on GitHub<sup><i class="fas fa-fw fa-external-link-alt"></i></sup></a>.`,
                        'as-html': 'Load Content as HTML',
                        'html-info': {
                            title: 'What\'s this?',
                            desc: 'Because the mime-type is not supported, there is no way to natively display it\'s content in Spacialist. Though, it supports displaying simple text to (at least) view the content without downloading the file.'
                        }
                    },
                    metadata: {
                        title: 'Metadata',
                        created: 'Created',
                        lastmodified: 'Last modified',
                        filesize: 'Size'
                    },
                    props: {
                        copyright: 'Copyright',
                        description: 'Description'
                    },
                    replace: {
                        button: 'Replace file',
                        confirm: 'Do you want to replace the file ({size}) with {name} ({size2})?',
                        different_mime: 'These files have a different mime-type. If you proceed, type is changed from <span class="fw-medium">{mime_old}</span> to <span class="fw-medium">{mime_new}</span>. You should adjust the filename.'
                    }
                },
                delete: {
                    alert: 'Note: If you delete {name}, one link to an entity is deleted, too. | Note: If you delete {name}, {cnt} links to entities are deleted, too.'
                },
                clipboard: {
                    title: '{name} - Confirm upload',
                    file_info: '@:plugins.files.modal.detail.metadata.title',
                    toggle_edit_mode: 'Toggle Edit-Mode',
                    no_preview: 'No preview available for this file type.'
                }
            },
        }
    }
}

export default en;
