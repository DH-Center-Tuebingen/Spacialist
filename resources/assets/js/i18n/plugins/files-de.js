const de = {
    plugins: {
        files: {
            title: 'Dateien',
            header: {
                linked: 'Verknüpfte Dateien',
                unlinked: 'Unverknüpfte Dateien',
                all: 'Alle Dateien',
                upload: 'Dateien hochladen',
                rules: {
                    title: 'Filterregeln',
                    types: {
                        file: 'Dateityp',
                        camera: 'Kameramodell',
                        date: 'Datum',
                    },
                    apply: 'Filter anwenden',
                    active: 'keine Filter aktiv | ein Filter aktiv | {cnt} Filter aktiv'
                }
            },
            toasts: {
                deleted: {
                    title: 'Datei gelöscht',
                    msg: '{name} wurde erfolgreich gelöscht.'
                },
                unlinked: {
                    title: 'Verknüpfung entfernt',
                    msg: 'Verknüpfung von {name} zu {eName} wurde erfolgreich entfernt.'
                },
                linked: {
                    title: 'Verknüpfung erstellt',
                    msg: 'Verknüpfung von {name} zu {eName} wurde erfolgreich erstellt.'
                }
            },
            'include-sub-files': 'Dateien von Unter-Entitäten anzeigen',
            upload: {
                title: 'Drop Zone',
                desc: 'Dateien hierherziehen oder auf die Box klicken.',
                error: 'Fehler beim Hochladen der Datei.'
            },
            modal: {
                detail: {
                    title: '{name} - Details',
                    properties: 'Eigenschaften',
                    links: 'Verknüpfungen',
                    exif: 'Exif-Daten',
                    'toggle-edit': 'Editiermodus umschalten',
                    'toggle-highlight': 'Hervorhebung umschalten',
                    'toggle-csv': 'CSV-Anzeige umschalten',
                    'toggle-md': 'Markdown-Anzeige umschalten',
                    'toggle-html': 'HTML-Anzeige umschalten',
                    'no-links': 'Keine Verknüpfungen. Wähle eine Entität aus oder benutze die Suche hier drunter, um Entitäten zu verknüpfen.',
                    'link-further-entities': 'Mit weiteren Entitäten verknüpfen',
                    toasts: {
                        'tags-updated': {
                            title: 'Schlagworte aktualisiert',
                            msg: 'Schlagworte für {name} wurden erfolgreich aktualisiert.'
                        }
                    },
                    previous: 'Vorherige Datei',
                    next: 'Nächste Datei',
                    csv: {
                        delimiter: 'Trennzeichen',
                        header: 'Kopfzeile?',
                        rows: 'Anzahl der angezeigten Zeilen'
                    },
                    threed: {
                        'load-sub-models': 'Modelle aus Sub-Entitäten laden'
                    },
                    dicom: {
                        controls: {
                            title: 'Steuerung',
                            zoom: 'Zoom',
                            'zoom-desc': 'Benutze <kbd><kbd>Rechtsklick</kbd> + <kbd>Ziehen</kbd></kbd> hoch/runter um raus/reinzuzoomen.',
                            move: 'Bewegen',
                            'move-desc': 'Benutze <kbd><kbd>Mittelklick</kbd> + <kbd>Ziehen</kbd></kbd> um das Bild zu verschieben.',
                            voi: 'VOI (Values of Interest)',
                            'voi-desc': 'Benutze <kbd><kbd>Linksklick</kbd> + <kbd>Ziehen</kbd></kbd> um die Fensterbreite (hoch/runter) und das Fensterzentrum (links/rechts) zu ändern.',
                        },
                        metadata: {
                            title: 'Metadaten',
                            'search-placeholder': 'Suche nach Metadaten-Schlagworten...'
                        },
                        save: 'Bild speichern',
                        wwwc: 'WW/WC'
                    },
                    archive: {
                        info: 'Klicke auf Dateien um sie direkt herunterzuladen. Das Herunterladen ganzer Ordner wird noch nicht unterstützt.'
                    },
                    undef: {
                        info: `Der Dateityp <code>{mime}</code> von <span class="font-italic">{name}</span> wird im Moment nicht von Spacialist unterstützt.
                        Wenn du Unterstützung für diesen Dateityp benötigst, erstelle bitte einen <a href="https://github.com/eScienceCenter/Spacialist/issues/new">Issue auf GitHub<sup><i class="fas fa-fw fa-external-link-alt"></i></sup></a>.`,
                        'as-html': 'Inhalt als HTML laden',
                        'html-info': {
                            title: 'Was ist das?',
                            desc: 'Da der Dateityp nicht unterstützt wird, gibt es keine direkte Möglichkeit, den Dateiinhalt in Spacialist anzuzeigen. Allerdings unterstützt der Dateityp die Anzeige als einfachen Text um den Inhalt (immerhin) ohne vorheriges Herunterladen anzuschauen.'
                        }
                    },
                    metadata: {
                        title: 'Metadaten',
                        created: 'Erstellt',
                        lastmodified: 'Zuletzt geändert',
                        filesize: 'Größe'
                    },
                    props: {
                        copyright: 'Copyright',
                        description: 'Beschreibung'
                    },
                    replace: {
                        button: 'Datei ersetzen',
                        confirm: 'Willst du die Datei ({size}) durch {name} ({size2}) ersetzen?'
                    }
                },
                delete: {
                    alert: 'Beachte: Wenn du {name} löschst, wird eine Verknüpfung zu einer Entität ebenfalls gelöscht. | Beachte: Wenn du {name} löschst, werden {cnt} Verknüpfungen zu Entitäten ebenfalls gelöscht.'
                }
            },
            list: {
                display: 'Zeige {from}-{to} von {total} Dateien an'
            }
        }
    }
}

export default de;