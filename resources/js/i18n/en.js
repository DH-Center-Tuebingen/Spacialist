const en = {
    global: {
        save: 'Save',
        delete: 'Delete',
        restore: 'Restore',
        deactivate: 'Deactivate',
        reactivate: 'Reactivate',
        remove: 'Remove',
        reset: 'Reset',
        send_reset_mail: 'Send mail to reset password',
        cancel: 'Cancel',
        close: 'Close',
        add: 'Add',
        edit: 'Edit',
        edited: 'edited',
        duplicate: 'Duplicate',
        resort: 'Re-sort',
        update: 'Update',
        replace: 'Replace',
        clear: 'Clear',
        confirm: 'OK',
        create: 'Create',
        apply: 'Apply',
        move: 'Move',
        parse: 'Parse',
        list: {
            nr_of_entries: 'No Entries | 1 Entry | {cnt} Entries',
            no_entries: 'No entries found.',
            fetch_next_entries: 'Fetch next entries',
            no_more_entries: 'No more entries',
        },
        select_all: 'Select All',
        select_none: 'Deselect All',
        delete_name: {
            title: 'Delete {name}',
            desc: 'Do you really want to delete <span class="fw-bold">{name}</span>?'
        },
        deactivate_name: {
            title: 'Deactivate {name}',
            desc: 'Do you really want to deactivate {name}?',
            info: 'Certain entries can not be deleted or have to be deactivated first. Otherwise deleting these entries could lead to data loss.'
        },
        remove_name: {
            title: 'Remove {name}',
            desc: 'Do you really want to remove {name}?'
        },
        unlink_name: {
            title: 'Remove Link - {name}',
            desc: 'Do you really want to unlink {file} from {ent}?'
        },
        all_entities: 'All Entities',
        unlinked: 'Unlinked',
        unlink: 'Unlink',
        link: 'Link',
        unlink_from: 'Unlink from {name}',
        link_to: 'Link to {name}',
        has_links: 'Has no links | Has one link | Has {cnt} links',
        discard: {
            title: 'Unsaved Changes',
            msg: 'There are unsaved changes on the current page. Do you really want to continue and discard changes?',
            confirm: 'Yes, discard changes',
            confirm_and_save: 'No, save and continue'
        },
        error: {
            alert_title: 'Error Message:',
            info_issue: 'If you think this is a bug, please have a look at our <a href="https://github.com/eScienceCenter/Spacialist/issues" target="_blank">issue tracker</a> on github to see if this has been already reported or <a href="https://github.com/eScienceCenter/Spacialist/issues?q=is:issue+is:closed" target="_blank">closed</a> in a later version (Your current version can be found in Settings > <i class="fas fa-fw fa-info-circle"></i> About Dialog). If it is neither reported nor closed, feel free to <a href="https://github.com/eScienceCenter/Spacialist/issues/new" target="_blank">open a new issue</a>.',
            occur: 'Error occured',
            headers: 'Headers',
            request_failed: `<code class="normal">{method}</code>-Request to endpoint <code class="bg-light-dark rounded px-2 normal text-dark">{url}</code> failed with status code <span class="fw-medium">{status}</span>.`,
        },
        search: 'Search…',
        search_no_results: 'No results',
        search_no_results_for: 'No results for term <span class="fst-italic fw-bold">{term}</span>',
        search_no_term_info: 'Enter a search term',
        search_no_term_info_global: 'Enter a search term to search globally',
        login: 'Login',
        login_title: 'Login',
        login_subtitle: 'Welcome to Spacialist',
        download: 'Download',
        download_with_name: 'Download {name}',
        upload: 'Upload',
        preview: 'Preview',
        preview_not_available: 'No preview available',
        note: 'Note',
        information: 'Information',
        tools: {
            title: 'Tools',
            bibliography: 'Bibliography',
            analysis: 'Data Analysis',
            thesaurex: 'ThesauRex',
            dbwebgen: 'dbWebGen',
            external: 'External Tools',
            record: {
                start: 'Start Recording',
                stop: 'Stop Recording'
            }
        },
        settings: {
            title: 'Settings',
            users: 'User Management',
            roles: 'Role Management',
            plugins: 'Plugins',
            datamodel: 'Data-Model-Editor',
            system: 'System Preferences',
            about: 'About'
        },
        user: {
            settings: 'Preferences',
            profile: 'Profile',
            logout: 'Logout',
            info_title: 'User Information',
            personal_info_title: 'Personal Informationen',
            member_since: 'Member since',
            deactivated_since: '<span class="fw-bold">deactivated</span> since {dt}',
            contact: 'Contact',
            avatar: 'Avatar',
            invalid_orcid: 'This ORCID is invalid',
        },
        select: {
            placeholder: 'Select option',
            select: 'Press enter to select',
            deselect: 'Press enter to remove',
        },
        attribute: 'Attribute',
        attributes: {
            string: 'Textfield',
            stringf: 'Textbox',
            'string-sc': 'Single Choice Dropdown',
            'string-mc': 'Multiple Choice Dropdown',
            double: 'Numeric Input (Floating Point)',
            integer: 'Numeric Input (Integer)',
            boolean: 'Checkbox',
            percentage: 'Percentage',
            entity: 'Entity',
            epoch: 'Time Period and Epoch',
            timeperiod: 'Time Period',
            date: 'Date',
            dimension: 'Dimensions (BxHxT)',
            list: 'List',
            geography: 'WKT (Well-Known-Binary)',
            table: 'Table',
            sql: 'SQL-Query',
            serial: 'Serial (Auto-incrementing ID)',
            serial_info: `<i class="fas fa-lightbulb me-1"></i>
            <br />
            All instances share this identifier as attribute. Add <code class="normal">%d</code> as counter.
            <br />
            <span class="fw-medium mt-2">Example:</span>
            <br />
            <code class="normal">Find_%d_Stone</code> would create Find_1_Stone, Find_2_Stone, &hellip;
            <br />
            To add a fixed width (e.g. 3 for 002 instead of 2), you can use <code class="normal">%03d</code>.`,
            sql_info: `You can use <code class="normal">:entity_id</code> in your SQL-Query to reference the entity this attribute is assigned to.`,
            iconclass: 'Iconclass',
            rism: 'RISM',
        },
        activity: 'Activity',
        action: 'Action',
        users: 'User | Users',
        timespan: 'Time span',
        visible: 'Visible',
        invisible: 'Invisible',
        opacity: 'Opacity',
        transparency: 'Transparency',
        text: 'Text',
        font: 'Font',
        mode: 'Mode',
        size: 'Size',
        duration: 'Duration',
        color: 'Color',
        format: 'Format',
        version: 'Version',
        label: 'Label',
        url: 'URL',
        name: 'Name',
        nickname: 'Nickname',
        display_name: 'Displayname',
        email: 'E-Mail Address',
        email_or_nick: 'E-Mail Address or Nickname',
        phonenumber: 'Phone number',
        password: 'Password',
        confirm_password: 'Repeat Password',
        remember_me: 'Remember me',
        orcid: 'ORCID',
        description: 'Description',
        roles: 'Roles',
        permissions: 'Permissions',
        added_at: 'Added',
        created_at: 'Created',
        updated_at: 'Updated',
        timestamp: 'Timestamp',
        deactivated_at: 'Deactivated',
        options: 'Options',
        reply_to: 'Reply',
        replying_to: 'In reply to {name}',
        type: 'Type',
        parent_entity: 'Parent-Entity',
        root_attribute: 'Parent-Attribute',
        root_attribute_toggle: 'Use value of an existing attribute as Parent-Element',
        root_element: 'Parent-Element',
        recursive: 'All descendants (recursive)',
        content: 'Content',
        column: 'Column | Columns',
        add_column: 'Add column',
        geometry_type: 'Geometry-Type',
        depends_on: 'Depends on',
        preference: 'Preference | Preferences',
        value: 'Value',
        allow_override: 'Allow Override?',
        tag: 'Tag | Tags',
        set: 'Set',
        has_tags: 'Has no tags | Has one tag | Has {cnt} tags',
        from_subentity: 'Is from Sub-Entity',
        comments: {
            deleted_info: 'Comment deleted',
            empty_list: 'No comments yet.',
            hide: 'Hide comments',
            show: 'Show comments',
            submit: 'Submit Comment',
            text_placeholder: 'Enter a comment',
            hide_reply: 'Hide <span class="fw-bold">one</span> reply | Hide <span class="fw-bold">{cnt}</span> replies',
            show_reply: 'Show <span class="fw-bold">one</span> reply | Show <span class="fw-bold">{cnt}</span> replies',
            fetching: 'Fetching comments&hellip;',
            fetching_failed: 'Fetching comments failed!',
            retry_failed: 'Retry',
        },
        notifications: {
            title: 'Notifications',
            count: 'Notifications ({cnt})',
            mark_all_as_read: 'Mark all as read',
            delete_all: 'Delete all',
            empty_list: 'No notifications',
            view_all: 'View all',
            tab_all: 'All',
            tab_unread: 'Unread',
            tab_read: 'Read',
            tab_system: 'System Notifications',
            tab_default_empty_list: 'You are up-to-date. No notifications for you!',
            tab_unread_empty_list: 'You are up-to-date. No new notifications for you!',
            tab_system_empty_list: 'No system notifications. No action to take for you!',
            body: {
                title_new: 'New Notification',
                title_read: 'Notification',
                type: {
                    system: 'system-message'
                },
                user_left_comment_on: 'left a comment on <span class="fw-bold">{name}</span>.',
                user_edited_entity: 'made changes to <span class="fw-bold">{name}</span>.',
                reply: 'Reply',
                mention_info: "Write your reply. Use {'@'}nickname to mention other users.",
                goto_comments: 'Open comment section',
                goto_entity: 'Open entity detail',
                reply_sent: 'Reply sent',
                reply_to_user: 'Reply to <span class="fw-bold">{name}</span>',
                reply_to_chat: 'Reply to comment section',
            }
        },
        validations: {
            required: '',
        },
    },
    main: {
        app: {
            loading_screen_msg: '<span class="fw-light">Loading</span> {appname}<span class="fw-light">&hellip;</span>',
            not_found: {
                title: 'Site not found!',
                msg: 'Site <code>{site}</code> was not found. Check the spelling or contact the administrator.',
                go_to: 'To start page',
            },
        },
        csv: {
            uploader: {
                title: 'CSV-Uploader',
                delimiter: 'Delimiter',
                delimiter_with_info: "Delimiter (',', ';', …)",
                has_header: 'Has Header Row?',
                nr_of_shown_rows: 'Number of shown rows',
            },
            picker: {
                title: 'CSV-Picker',
            },
        },
        entity: {
            title: 'Entity | Entities',
            count: 'No Top-Level-Entities | One Top-Level-Entity | {cnt} Top-Level-Entities',
            detail_tab_none_selected: 'No Entity selected. Select an Entity from the tree to see the details in this tab.',
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
                add: 'Add new Top-Level-Entity',
                contextmenu: {
                    add: 'Add Sub-Entity',
                    duplicate: 'Create Duplicate',
                    move: 'Move in Tree',
                    delete: 'Delete Entity',
                },
                sorts: {
                    asc: {
                        rank: 'Rank - Ascending (Drag & Drop)',
                        name: 'Name - Ascending',
                        children: 'Count Sub-Entities - Ascending',
                        type: 'Entity-Type - Ascending'
                    },
                    desc: {
                        rank: 'Rank - Descending',
                        name: 'Name - Descending',
                        children: 'Count Sub-Entities - Descending',
                        type: 'Entity-Type - Descending'
                    }
                }
            },
            tabs: {
                attributes: 'Attributes',
                comments: 'Comments',
            },
            modals: {
                add: {
                    title: 'New Entity',
                },
                move: {
                    title: 'Move Entity',
                    to_root: 'Add to Root',
                },
                edit: {
                    title_type: 'Edit Entitytype - {name}',
                    title_attribute: 'Edit Attribute - {name}',
                },
                delete: {
                    alert: 'If you delete <span class="fw-bold">{name}</span>, at least one entity is deleted, too. | If you delete <span class="fw-bold">{name}</span>, at least {cnt} entities are deleted, too.',
                },
                screencast: {
                    title: 'Save Screencast',
                    info: 'Screencast recorded. What do you want to do with it?',
                    actions: {
                        local: {
                            button: 'Store local'
                        },
                        server: {
                            button: 'Store in Spacialist'
                        }
                    }
                }
            },
            references: {
                title: 'References',
                empty: 'No References found',
                certainty: 'Certainty',
                bibliography: {
                    title: 'Literature',
                    add: 'Add new Reference',
                    comment: 'Comment',
                    add_button: 'Add Reference',
                },
                toasts: {
                    updated_certainty: {
                        title: 'Certainty updated',
                        msg: 'Certainty of {name} successfully set to {i}%.'
                    }
                }
            },
            attributes: {
                open_map: 'Open Map',
                add_wkt: 'Add WKT',
                set_location: 'Set Location',
                BC: 'BC',
                bc: '@:main.entity.attributes.BC',
                AD: 'AD',
                ad: '@:main.entity.attributes.AD',
                hidden: 'no attributes hidden | one attribute hidden | {cnt} attributes hidden',
                entity: {
                    go_to: 'Go to {name}',
                },
                iconclass: {
                    cite_info: 'Infos from <cite title="Iconclass"><a href="http://iconclass.org/{class}.json" target="_blank">http://iconclass.org/{class}.json</a></cite>',
                    doesnt_exist: 'This iconclass does not exist'
                },
                rism: {
                    cite_info: 'Infos from <cite title="Muscat-SRU-Interface"><a href="https://muscat.rism.info/sru/sources?operation=searchRetrieve&version=1.1&query=id={id}&maximumRecords=1" target="_blank">https://muscat.rism.info/sru/sources?operation=searchRetrieve&version=1.1&query=id={id}&maximumRecords=1</a></cite>',
                    doesnt_exist: 'THIS RSIM-Entry does not exist',
                },
                table: {
                    chart: {
                        number_sets: 'Last {cnt} data sets',
                        include_in: 'Use in chart',
                        use_difference: 'Use difference',
                        use_as_label: 'Use as label'
                    }
                }
            }
        },
        user: {
            add_button: 'Add new User',
            active_users: 'Active Users',
            deactivated_users: 'Deactivated Users',
            empty_list: 'User-list is empty',
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
            add_role_placeholder: 'Add roles'
        },
        role: {
            add_button: 'Add new Role',
            toasts: {
                updated: {
                    title: 'Role updated',
                    msg: '{name} successfully updated.'
                }
            },
            modal: {
                new: {
                    title: 'New Role'
                },
                delete: {
                    alert: 'If you delete <span class="fw-bold">{name}</span>, the connection to one user is deleted. | If you delete <span class="fw-bold">{name}</span>, the connection to {cnt} users is deleted.',
                },
            },
            add_permission_placeholder: 'Add permissions'
        },
        activity: {
            title_project: 'Project Activity | Project Activities',
            title_user: 'Your Activity | Your Activities',
            rawdata: 'Raw Data',
            apply_filter: 'Apply filter',
            search_in_raw_data: 'Search in raw data',
            hide_filter_panel: 'Hide filter',
            toggle_raw_data: 'Toggle Raw Data',
            toggle_pretty_print: 'Toggle Pretty Print',
        },
        datamodel: {
            toasts: {
                updated_type: {
                    title: 'Entity-Type updated',
                    msg: '{name} successfully updated.'
                },
                added_attribute: {
                    title: 'Attribute added',
                    msg: '{name} successfully added to {etName}.'
                },
                attribute_deleted: {
                    title: 'Attribute deleted',
                    msg: '{name} successfully deleted.'
                }
            },
            attribute: {
                title: 'Available Attributes',
                add_button: 'Add Attribute',
                show_hidden: 'Show hidden',
                modal: {
                    new: {
                        title: 'New Attribute'
                    },
                    delete: {
                        alert: 'If you delete <span class="fw-bold">{name}</span>, one attribute value is deleted, too. | If you delete <span class="fw-bold">{name}</span>, {cnt} attribute values are deleted, too.'
                    }
                }
            },
            entity: {
                title: 'Available Entity-Types',
                add_button: 'Add Entity-Type',
                modal: {
                    new: {
                        title: 'New Entity-Type'
                    },
                    delete: {
                        alert: 'If you delete <span class="fw-bold">{name}</span>, one entity is deleted, too. | If you delete <span class="fw-bold">{name}</span>, {cnt} entities are deleted, too.'
                    }
                }
            },
            detail: {
                properties: {
                    title: 'Properties',
                    top_level: 'Top-Level-Entity-Type?',
                    sub_types: 'Allowed Sub-Entity-Types'
                },
                attribute: {
                    title: 'Added Attributes',
                    alert: 'If you remove <span class="fw-bold">{name}</span>, one entry at <span class="fw-bold">{refname}</span> is deleted, too. | If you remove <span class="fw-bold">{name}</span>, {cnt} entries at <span class="fw-bold">{refname}</span> are deleted, too.'
                }
            }
        },
        preference: {
            toasts: {
                updated: {
                    msg: 'Preferences successfully updated.'
                }
            },
            info: {
                set_to_language: 'Set to <span class="fw-bold">{lang}</span>',
                password_reset_link: 'To use this function, you have to set the <code>MAIL_*</code> settings in your <code>.env</code> file. <a href="https://github.com/eScienceCenter/Spacialist/blob/master/INSTALL.md#send-mails" target="_blank">Further informations</a>.',
                columns: 'The sum of the column values must not be higher than 12.'
            },
            key: {
                language: 'Language',
                password_reset_link: 'Enable Reset Password Link',
                columns: {
                    title: 'Columns in Main View',
                    left: 'Left Column',
                    center: 'Center Column',
                    right: 'Right Column',
                },
                tooltips: 'Show Tooltips',
                tag_root: 'Thesaurus-URI for Tags',
                extensions: 'Loaded Extensions',
                link_thesaurex: 'Show link to ThesauRex',
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
        },
        about: {
            title: 'About Spacialist',
            desc: 'Development of Spacialist is co-funded by the Ministry of Science, Research and the Arts Baden-Württemberg in the "E-Science" funding programme.',
            release: {
                name: 'Release Name',
                time: 'Release Date',
                full_name: 'Full Name'
            },
            build_info: 'Built with <i class="fab fa-fw fa-laravel"></i> & <i class="fab fa-fw fa-vuejs"></i>!',
            contributor: 'Contributor | Contributors'
        },
        bibliography: {
            title: 'Literature',
            modal: {
                paste_info: 'You can use <kbd><kbd>ctrl</kbd> + <kbd>v</kbd></kbd> to fill out the fields with a BibTeX entry from your clipboard.',
                new: {
                    title: 'New Entry',
                    bibtex_code: 'BibTeX-Code'
                },
                edit: {
                    title: 'Edit entry'
                },
                delete: {
                    title: 'Delete entry',
                    alert: 'If you delete <span class="fw-bold">{name}</span>, one reference is deleted, too. | If you delete <span class="fw-bold">{name}</span>, {cnt} references are deleted, too.',
                }
            },
            toast: {
                delete: {
                    title: 'Entry deleted successfully',
                    msg: 'Entry {name} is deleted',
                },
            },
            add: 'Add new Bibliography-Entry',
            import: 'Import BibTeX-File',
            export: 'Export BibTeX-File',
            show_all_fields: 'Show all fields',
            column: {
                citekey: 'Citation-Key',
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
                address: 'Place',
                note: 'Note',
                misc: 'Misc',
                howpublished: 'How Published',
                institution: 'Institution',
                organization: 'Organization',
                school: 'School'
            }
        },
        map: {
            init_error: 'Several init-*-Attributes are provided. Only one is allowed.',
            layer: 'Layer | Layers',
            baselayer: 'Baselayer | Baselayers',
            overlay:'Overlay | Overlays',
            entity_layers: 'Entity-Layer',
            geometry_name: 'Geometry #{id}',
            coords_in_epsg: 'Coordinates in EPSG:{epsg}',
            length: 'Length',
            area: 'Area',
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
                    pos_desc: 'Save modifications',
                    neg_desc: 'Discard modifications'
                },
                delete: {
                    desc: 'Delete geometries',
                    pos_desc: 'Confirm delete',
                    neg_desc: 'Discard delete'
                },
                measure: {
                    desc: 'Toggle measuring tape'
                }
            }
        }
    }
};

export default en;
