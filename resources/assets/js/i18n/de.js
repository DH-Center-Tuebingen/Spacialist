const de = {
    global: {
        save: 'Speichern',
        delete: 'Löschen',
        remove: 'Entfernen',
        cancel: 'Abbrechen',
        close: 'Schließen',
        add: 'Hinzufügen',
        edit: 'Editieren',
        update: 'Aktualisieren',
        replace: 'Ersetzen',
        clear: 'Leeren',
        confirm: 'Ok',
        'select-all': 'Alle auswählen',
        'select-none': 'Alle abwählen',
        'delete-name': {
            title: '{name} löschen',
            desc: 'Willst du {name} wirklich löschen?'
        },
        'edit-name': {
            title: '{name} editieren'
        },
        'remove-name': {
            title: '{name} entfernen',
            desc: 'Willst du {name} wirklich entfernen?'
        },
        'unlink-name': {
            title: 'Verknüpfung entfernen - {name}',
            desc: 'Willst du die Verknüpfung von {file} und {ent} wirklich entfernen?'
        },
        'all-entities': 'Alle Entitäten',
        unlinked: 'Unverknüpft',
        unlink: 'Verknüpfung entfernen',
        link: 'Verknüpfen',
        'unlink-from': 'Verknüpfung mit {name} entfernen',
        'link-to': 'Mit {name} verknüpfen',
        discard: {
            title: 'Ungespeicherte Änderungen',
            msg: 'Ungespeicherte Änderungen in {name}. Willst du wirklich fortfahren und die Änderungen verwerfen?',
            confirm: 'Ja, Änderungen verwerfen',
            confirmpos: 'Nein, Speichern und fortfahren'
        },
        search: 'Suche...',
        login: 'Einloggen',
        download: 'Herunterladen',
        'download-name': '{name} herunterladen',
        upload: 'Hochladen',
        tools: {
            title: 'Werkzeuge',
            bibliography: 'Literatur',
            analysis: 'Datenanalyse',
            thesaurex: 'ThesauRex',
            dbwebgen: 'dbWebGen',
            external: 'Externe Werkzeuge'
        },
        settings: {
            title: 'Einstellungen',
            users: 'Benutzerverwaltung',
            roles: 'Rollenverwaltung',
            datamodel: 'Datenmodell-Editor',
            system: 'System-Einstellungen',
            editmode: 'Editiermodus umschalten',
            about: 'Über'
        },
        user: {
            settings: 'Einstellungen',
            logout: 'Ausloggen'
        },
        attribute: 'Attribut',
        active: 'Aktiviert',
        visible: 'Sichtbar',
        invisible: 'Unsichtbar',
        opacity: 'Deckkraft',
        transparency: 'Transparenz',
        text: 'Text',
        font: 'Schrift',
        mode: 'Modus',
        size: 'Größe',
        color: 'Farbe',
        format: 'Format',
        version: 'Version',
        label: 'Beschriftung',
        url: 'URL',
        name: 'Name',
        'display-name': 'Anzeigename',
        email: 'E-Mail-Adresse',
        password: 'Passwort',
        description: 'Beschreibung',
        roles: 'Rollen',
        permissions: 'Berechtigungen',
        'added-at': 'Hinzugefügt',
        'created-at': 'Erstellt',
        'updated-at': 'Aktualisert',
        options: 'Optionen',
        type: 'Typ',
        'root-element': 'Eltern-Element',
        content: 'Inhalt',
        'geometry-type': 'Geometrietyp',
        'depends-on': 'Hängt ab von',
        preference: 'Einstellung',
        value: 'Wert',
        'allow-override': 'Überschreibbar?',
        tag: 'Schlagwort | Schlagworte',
        set: 'Setzen'
    },
    main: {
        entity: {
            title: 'Entitäten',
            count: 'Keine Top-Level Entitäten | Eine Top-Level Entität | {cnt} Top-Level Entitäten',
            toasts: {
                updated: {
                    title: 'Entität aktualisiert',
                    msg: 'Daten von {name} wurden erfolgreich aktualisiert.'
                },
                deleted: {
                    title: 'Entität gelöscht',
                    msg: '{name} wurde erfolgreich gelöscht.'
                }
            },
            tree: {
                add: 'Neue Top-Level Entität hinzufügen'
            },
            modal: {
                title: 'Neue Entität'
            },
            menu: {
                add: 'Neue Entität hinzufügen',

            },
            references: {
                title: 'Quellenangaben',
                empty: 'Keine Quellen gefunden',
                certainty: 'Sicherheit',
                certaintyc: 'Kommentar',
                certaintyu: 'Sicherheit aktualisieren',
                bibliography: {
                    title: 'Quellen',
                    add: 'Neue Quellen hinzufügen',
                    comment: 'Kommentar',
                    'add-button': 'Quelle hinzufügen'
                },
                toasts: {
                    'updated-certainty': {
                        title: 'Sicherheit aktualisiert',
                        msg: 'Sicherheit von {name} erfolgreich auf {i}% ({desc}) gesetzt.'
                    }
                }
            },
            attributes: {
                'open-map': 'Karte öffnen',
                'add-wkt': 'WKT hinzufügen',
                'set-location': 'Position setzen',
                BC: 'v. Chr.',
                AD: 'n. Chr.'
            }
        },
        user: {
            'add-button': 'Neuen Benutzer hinzufügen',
            toasts: {
                updated: {
                    title: 'Benutzer aktualisiert',
                    msg: '{name} wurde erfolgreich aktualisiert.'
                }
            },
            modal: {
                new: {
                    title: 'Neuer Benutzer'
                }
            }
        },
        role: {
            'add-button': 'Neue Rolle hinzufügen',
            toasts: {
                updated: {
                    title: 'Rolle aktualisiert',
                    msg: '{name} wurde erfolgreich aktualisiert.'
                }
            },
            modal: {
                new: {
                    title: 'Neue Rolle'
                }
            }
        },
        datamodel: {
            toasts: {
                'updated-type': {
                    title: 'Entitätstyp aktualisiert',
                    msg: '{name} erfolgreich aktualisiert.'
                },
                'added-attribute': {
                    title: 'Attribut hinzugefügt',
                    msg: '{name} erfolgreich zu {etName} hinzugefügt.'
                }
            },
            attribute: {
                title: 'Verfügbare Attribute',
                'add-button': 'Attribut hinzufügen',
                modal: {
                    new: {
                        title: 'Neues Attribut'
                    },
                    delete: {
                        alert: 'Beachte: Wenn du {name} löschst, wird ein Attributwert ebenfalls gelöscht. | Beachte: Wenn du {name} löschst, werden {cnt} Attributwerte ebenfalls gelöscht.'
                    }
                }
            },
            entity: {
                title: 'Verfügbare Entitätstypen',
                'add-button': 'Entitätstyp hinzufügen',
                modal: {
                    new: {
                        title: 'Neuer Entitätstyp'
                    },
                    delete: {
                        alert: 'Beachte: Wenn du {name} löschst, wird eine Entität ebenfalls gelöscht. | Beachte: Wenn du {name} löschst, werden {cnt} Entitäten ebenfalls gelöscht.'
                    }
                }
            },
            detail: {
                properties: {
                    title: 'Eigenschaften',
                    'top-level': 'Top-Level Entitätstyp?',
                    'sub-types': 'Erlaubte Unter-Entitätstypen'
                },
                attribute: {
                    title: 'Hinzugefügte Attribute',
                    alert: 'Beachte: Wenn du {name} entfernst, wird ein weiterer Eintrag unter {refname} ebenfalls gelöscht. | Beachte: Wenn du {name} entfernst, werden {cnt} weitere Einträge unter {refname} ebenfalls gelöscht.'
                }
            }
        },
        preference: {
            toasts: {
                updated: {
                    title: 'Einstellung aktualisiert',
                    msg: '{name} wurde erfolgreich aktualisiert.'
                }
            },
            key: {
                language: 'Sprache',
                columns: {
                    title: 'Spalten Hauptansicht',
                    left: 'Linke Spalte',
                    center: 'Mittlere Spalte',
                    right: 'Rechte Spalte',
                },
                tooltips: 'Infos anzeigen',
                'tag-root': 'Thesaurus-URI für Schlagworte',
                extensions: 'Geladene Erweiterungen',
                'link-thesaurex': 'Link zu ThesauRex anzeigen',
                project: {
                    name: 'Projektname',
                    maintainer: 'Verantwortlicher',
                    public: 'Öffentlich verfügbar'
                },
                map: {
                    projection: 'Kartenprojektion',
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
            title: 'Über Spacialist',
            desc: 'Spacialist wird vom Ministerium für Wissenschaft, Forschung und Kunst, Baden-Württemberg im Rahmen des "E-Science"-Programms gefördert.',
            release: {
                name: 'Name der Veröffentlichung',
                time: 'Datum der Veröffentlichung',
                'full-name': 'Vollständiger Name'
            },
            'build-info': 'Mit <i class="fab fa-fw fa-laravel"></i> & <i class="fab fa-fw fa-vuejs"></i> erstellt!',
            contributor: 'Mitwirkender | Mitwirkende'
        },
        bibliography: {
            modal: {
                new: {
                    title: 'Neuer Eintrag',
                    'bibtex-code': 'BibTeX-Code'
                },
                edit: {
                    title: 'Eintrag editieren'
                },
                delete: {
                    title: 'Eintrag löschen',
                    alert: 'Beachte: Wenn du {name} löschst, wird eine Quellenangabe ebenfalls gelöscht. | Beachte: Wenn du {name} löschst, werden {cnt} Quellenangaben ebenfalls gelöscht.',
                }
            },
            add: 'Neuen Literatur-Eintrag anlegen',
            import: 'BibTeX-Datei importieren',
            export: 'BibTeX-Datei exportieren',
            'show-all-fields': 'Alle Felder anzeigen',
            column: {
                'cite-key': 'Zitations-Schlüssel',
                author: 'Autor',
                editor: 'Herausgeber',
                title: 'Titel',
                journal: 'Zeitschrift',
                year: 'Jahr',
                month: 'Monat',
                pages: 'Seiten',
                volume: 'Band',
                number: 'Ausgabe',
                chapter: 'Kapitel',
                edition: 'Edition',
                series: 'Reihe',
                booktitle: 'Buchtitel',
                publisher: 'Verlag',
                address: 'Adresse',
                note: 'Notiz',
                misc: 'Diverse',
                howpublished: 'Verweis',
                institution: 'Institution',
                organization: 'Organisation',
                school: 'Schule'
            }
        },
        map: {
            'init-error': 'Mehrere init-*-Attribute gesetzt. Es darf nur eines gesetzt sein.',
            layer: 'Ebene | Ebenen',
            baselayer: 'Basis-Ebene | Basis-Ebenen',
            overlay:'Overlay | Overlays',
            'entity-layers': 'Entitäts-Ebenen',
            'geometry-name': 'Geometrie #{id}',
            'coords-in-epsg': 'Koordinaten in EPSG:{epsg}',
            draw: {
                point: {
                    desc: 'Punkt anlegen',
                },
                linestring: {
                    desc: 'Linie anlegen',
                },
                polygon: {
                    desc: 'Polygon anlegen',
                },
                modify: {
                    desc: 'Geometrien bearbeiten',
                    'pos-desc': 'Änderungen speichern',
                    'neg-desc': 'Änderungen verwerfen'
                },
                delete: {
                    desc: 'Geometrien löschen',
                    'pos-desc': 'Löschen bestätigen',
                    'neg-desc': 'Löschen verwerfen'
                },
                measure: {
                    desc: 'Maßband umschalten'
                }
            }
        }
    }
};

export default de;
