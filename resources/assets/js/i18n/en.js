const en = {
    global: {
        save: 'Save',
        delete: 'Delete',
        remove: 'Remove',
        cancel: 'Cancel',
        close: 'Close',
        add: 'Add',
        edit: 'Edit',
        update: 'Update',
        replace: 'Replace',
        clear: 'Clear',
        confirm: 'OK',
        'select-all': 'Select All',
        'select-none': 'Deselect All',
        'delete-name': {
            title: 'Delete {name}',
            desc: 'Do you really want to delete {name}?'
        },
        'edit-name': {
            title: 'Edit {name}'
        },
        'remove-name': {
            title: 'Remove {name}',
            desc: 'Do you really want to remove {name}?'
        },
        'unlink-name': {
            title: 'Remove Link - {name}',
            desc: 'Do you really want to unlink {file} from {ent}?'
        },
        'all-entities': 'All Entities',
        unlinked: 'Unlinked',
        unlink: 'Unlink',
        link: 'Link',
        'unlink-from': 'Unlink from {name}',
        'link-to': 'Link to {name}',
        discard: {
            title: 'Unsaved Changes',
            msg: 'Unsaved changes in {name}. Do you really want to continue and discard changes?',
            confirm: 'Yes, discard changes',
            confirmpos: 'No, save and continue'
        },
        search: 'Search...',
        login: 'Login',
        download: 'Download',
        'download-name': 'Download {name}',
        upload: 'Upload',
        tools: {
            title: 'Tools',
            bibliography: 'Bibliography',
            analysis: 'Data Analysis',
            thesaurex: 'ThesauRex',
            dbwebgen: 'dbWebGen',
            external: 'External Tools'
        },
        settings: {
            title: 'Settings',
            users: 'User Management',
            roles: 'Role Management',
            datamodel: 'Data-Model-Editor',
            system: 'System Preferences',
            editmode: 'Toggle Edit Mode',
            about: 'About'
        },
        user: {
            settings: 'Preferences',
            logout: 'Logout'
        },
        select: {
            placehoder: 'Select option',
            select: 'Press enter to select',
            deselect: 'Press enter to remove'
        },
        attribute: 'Attribute',
        active: 'Active',
        visible: 'Visible',
        invisible: 'Invisible',
        opacity: 'Opacity',
        transparency: 'Transparency',
        text: 'Text',
        font: 'Font',
        mode: 'Mode',
        size: 'Size',
        color: 'Color',
        format: 'Format',
        version: 'Version',
        label: 'Label',
        url: 'URL',
        name: 'Name',
        'display-name': 'Displayname',
        email: 'E-Mail Address',
        password: 'Password',
        description: 'Description',
        roles: 'Roles',
        permissions: 'Permissions',
        'added-at': 'Added',
        'created-at': 'Created',
        'updated-at': 'Updated',
        options: 'Options',
        type: 'Type',
        'root-element': 'Parent-Element',
        content: 'Content',
        'geometry-type': 'Geometry-Type',
        'depends-on': 'Depends on',
        preference: 'Preference',
        value: 'Value',
        'allow-override': 'Allow Override?',
        tag: 'Tag | Tags',
        set: 'Set'
    },
    main: {
        entity: {
            title: 'Entities',
            count: 'No Top-Level-Entities | One Top-Level-Entity | {cnt} Top-Level-Entities',
            toasts: {
                updated: {
                    title: 'Entity updated',
                    msg: 'Data of {name} successfully updated.'
                },
                deleted: {
                    title: 'Entität gelöscht',
                    msg: '{name} wurde erfolgreich gelöscht.'
                }
            },
            tree: {
                add: 'Add new Top-Level-Entity'
            },
            modal: {
                title: 'New Entity'
            },
            menu: {
                add: 'Add new Entity',

            },
            references: {
                title: 'Sources',
                empty: 'No Sources found',
                certainty: 'Certainty',
                certaintyc: 'Comment',
                certaintyu: 'Update Certainty',
                bibliography: {
                    title: 'Literature',
                    add: 'Add new Source',
                    comment: 'Comment',
                    'add-button': 'Add Source'
                },
                toasts: {
                    'updated-certainty': {
                        title: 'Certainty updated',
                        msg: 'Certainty of {name} successfully set to {i}% ({desc}).'
                    }
                }
            },
            attributes: {
                'open-map': 'Open Map',
                'add-wkt': 'Add WKT',
                'set-location': 'Set Location',
                BC: 'BC',
                AD: 'AD'
            }
        },
        user: {
            'add-button': 'Add new User',
            toasts: {
                updated: {
                    title: 'User updated',
                    msg: '{name} successfully updated.'
                }
            },
            modal: {
                new: {
                    title: 'New User'
                }
            },
            'add-role-placeholder': 'Add roles'
        },
        role: {
            'add-button': 'Add new Role',
            toasts: {
                updated: {
                    title: 'Role updated',
                    msg: '{name} successfully updated.'
                }
            },
            modal: {
                new: {
                    title: 'New Role'
                }
            },
            'add-permission-placeholder': 'Add permissions'
        },
        datamodel: {
            toasts: {
                'updated-type': {
                    title: 'Entity-Type updated',
                    msg: '{name} successfully updated.'
                },
                'added-attribute': {
                    title: 'Attribut hinzugefügt',
                    msg: '{name} erfolgreich zu {etName} hinzugefügt.'
                }
            },
            attribute: {
                title: 'Available Attributes',
                'add-button': 'Add Attribute',
                modal: {
                    new: {
                        title: 'New Attribute'
                    },
                    delete: {
                        alert: 'Note: If you delete {name}, one attribute value is deleted, too. | Note: If you delete {name}, {cnt} attribute values are deleted, too.'
                    }
                }
            },
            entity: {
                title: 'Available Entity-Types',
                'add-button': 'Add Entity-Type',
                modal: {
                    new: {
                        title: 'New Entity-Type'
                    },
                    delete: {
                        alert: 'Note: If you delete {name}, one entity is deleted, too. | Note: If you delete {name}, {cnt} entities are deleted, too.'
                    }
                }
            },
            detail: {
                properties: {
                    title: 'Properties',
                    'top-level': 'Top-Level-Entity-Type?',
                    'sub-types': 'Allowed Sub-Entity-Types'
                },
                attribute: {
                    title: 'Added Attributes',
                    alert: 'Note: If you remove {name}, one entry at {refname} is deleted, too. | Note: If you remove {name}, {cnt} entries at {refname} are deleted, too.'
                }
            }
        },
        preference: {
            toasts: {
                updated: {
                    title: 'Preference updated',
                    msg: '{name} successfully updated.'
                }
            },
            key: {
                language: 'Language',
                columns: {
                    title: 'Columns in Main View',
                    left: 'Left Column',
                    center: 'Center Column',
                    right: 'Right Column',
                },
                tooltips: 'Show Tooltips',
                'tag-root': 'Thesaurus-URI for Tags',
                extensions: 'Loaded Extensions',
                'link-thesaurex': 'Show link to ThesauRex',
                project: {
                    name: 'Projectname',
                    maintainer: 'Maintainer',
                    public: 'Public accessible'
                },
                map: {
                    projection: 'Map Projection',
                    epsg: 'EPSG-Code'
                }
            },
            labels: {
                prefs: {
                    'gui-language': '@:main.preference.key.language',
                    'columns': '@:main.preference.key.columns.title',
                    'show-tooltips': '@:main.preference.key.tooltips',
                    'tag-root': '@:main.preference.key.tag-root',
                    'load-extensions': '@:main.preference.key.extensions',
                    'link-to-thesaurex': '@:main.preference.key.link-thesaurex',
                    'project-name': '@:main.preference.key.project.name',
                    'project-maintainer': '@:main.preference.key.project.maintainer',
                    'map-projection': '@:main.preference.key.map.projection',
                }
            }
        },
        about: {
            title: 'About Spacialist',
            desc: 'Development of Spacialist is co-funded by the Ministry of Science, Research and the Arts Baden-Württemberg in the "E-Science" funding programme.',
            release: {
                name: 'Release Name',
                time: 'Release Date',
                'full-name': 'Full Name'
            },
            'build-info': 'Built with <i class="fab fa-fw fa-laravel"></i> & <i class="fab fa-fw fa-vuejs"></i>!',
            contributor: 'Contributor | Contributors'
        },
        bibliography: {
            modal: {
                new: {
                    title: 'New Entry',
                    'bibtex-code': 'BibTeX-Code'
                },
                edit: {
                    title: 'Edit entry'
                },
                delete: {
                    title: 'Delete entry',
                    alert: 'Note: If you delete {name}, one source is deleted, too. | Note: If you delete {name}, {cnt} sources are deleted, too.',
                }
            },
            add: 'Add new Bibliography-Entry',
            import: 'Import BibTeX-File',
            export: 'Export BibTeX-File',
            'show-all-fields': 'Show all fields',
            column: {
                'cite-key': 'Citation-Key',
                author: 'Author',
                editor: 'Editor',
                title: 'Title',
                journal: 'Journal',
                year: 'Year',
                month: 'Month',
                pages: 'Pages',
                volume: 'Volumne',
                number: 'Number',
                chapter: 'Chapter',
                edition: 'Edition',
                series: 'Series',
                booktitle: 'Book title',
                publisher: 'Publisher',
                address: 'Address',
                note: 'Note',
                misc: 'Misc',
                howpublished: 'How Published',
                institution: 'Institution',
                organization: 'Organization',
                school: 'School'
            }
        },
        map: {
            'init-error': 'Several init-*-Attributes are provided. Only one is allowed.',
            layer: 'Layer | Layers',
            baselayer: 'Baselayer | Baselayers',
            overlay:'Overlay | Overlays',
            'entity-layers': 'Entity-Layer',
            'geometry-name': 'Geometry #{id}',
            'coords-in-epsg': 'Coordinates in EPSG:{epsg}',
            draw: {
                point: {
                    desc: 'Draw Point',
                },
                linestring: {
                    desc: 'Draw Line',
                },
                polygon: {
                    desc: 'Draw Polygon',
                },
                modify: {
                    desc: 'Modify geometries',
                    'pos-desc': 'Save modifications',
                    'neg-desc': 'Discard modifications'
                },
                delete: {
                    desc: 'Delete geometries',
                    'pos-desc': 'Confirm delete',
                    'neg-desc': 'Discard delete'
                },
                measure: {
                    desc: 'Toggle measuring tape'
                }
            }
        }
    }
};

export default en;
