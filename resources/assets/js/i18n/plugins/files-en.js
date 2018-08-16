const en = {
    plugins: {
        files: {
            title: 'Files',
            header: {
                linked: 'Linked Files',
                unlinked: 'Unlinked Files',
                all: 'All Files',
                upload: 'Upload FIles',
                rules: {
                    title: 'Filter Rules',
                    types: {
                        file: 'File Type',
                        camera: 'Camera Model',
                        date: 'Date',
                    },
                    apply: 'Apply Filters',
                    active: 'no filter active | one filter active | {cnt} filters active'
                }
            },
            'include-sub-files': 'Show files from Sub-Entities',
            upload: {
                title: 'Drop Zone',
                desc: 'Drop Files here or click on the box..',
                error: 'Error while uploading your file.'
            },
            modal: {
                detail: {
                    title: '{name} - Details',
                    properties: 'Properties',
                    links: 'Links',
                    exif: 'Exif-Data',
                    'toggle-edit': 'Toggle Edit-Mode',
                    'toggle-highlight': 'Toggle Highlighting',
                    'toggle-csv': 'Toggle CSV-Rendering',
                    'toggle-md': 'Toggle Markdown-Rendering',
                    'toggle-html': 'Toggle HTML-Rendering',
                    csv: {
                        delimiter: 'Delimiter',
                        header: 'Header Row?',
                        rows: 'Number of displayed rows'
                    },
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
                            title: 'Metadata',
                            'search-placeholder': 'Search for Metadata-Tags...'
                        },
                        save: 'Save Image',
                        wwwc: 'WW/WC'
                    },
                    archive: {
                        info: 'Click on files to download them. Downloading folders is currently not supported.'
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
                    replace: {
                        button: 'Replace file',
                        confirm: 'Do you want to replace the file ({size}) with {name} ({size2})?'
                    }
                },
                delete: {
                    alert: 'Note: If you delete {name}, one link to an entity is deleted, too. | Note: If you delete {name}, {cnt} links to entities are deleted, too.'
                }
            },
            list: {
                display: 'Displaying {from}-{to} of {total} files'
            }
        }
    }
}

export default en;
