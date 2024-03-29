const de = {
    plugins: {
        files: {
            header: {
                export: {
                    selected: 'Ausgewählte Dateien exportieren <span class="badge badge-secondary">{cnt}</span>'
                }
            },
            image_filters: {
                grayscale: 'Graustufen',
                bw: 'Schwarz-Weiß',
                invert: 'Invertieren',
                sepia: 'Sepia',
                remove_color: 'Farbe entfernen',
                brightness: 'Helligkeit',
                contrast: 'Kontrast',
                hue: 'Farbton',
                saturation: 'Sättigung',
                noise: 'Rauschen',
                pixelate: 'Pixeln',
                blur: 'Unschärfe',
                sharpen: 'Schärfe',
                emboss: 'Prägen',
                save: 'Änderungen speichern'
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
                error: 'Fehler beim Hochladen der Datei.',
                finish: {
                    title: 'Dateiupload von {files} Datei abgeschlossen | Dateiupload von {files} Dateien abgeschlossen',
                    msg: 'Es sind keine Fehler aufgetreten | Es ist bei einer Datei ein Fehler augetreten. | Es sind bei {files} Dateien Fehler aufgetreten'
                }
            },
            modal: {
                detail: {
                    title: '{name} - Details',
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
                            title: '@:plugins.files.modal.detail.metadata.title',
                            'search-placeholder': 'Suche nach Metadaten-Schlagworten...'
                        },
                        save: 'Bild speichern',
                        wwwc: 'WW/WC'
                    },
                    archive: {
                        info: 'Klicke auf Dateien um sie direkt herunterzuladen. Das Herunterladen ganzer Ordner wird noch nicht unterstützt.',
                        files_in_folder: 'Enthält <span class="fw-medium">keine</span> Dateien | Enthält <span class="fw-medium">eine</span> Datei | Enthält <span class="fw-medium">{cnt}</span> Dateien'
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
                        confirm: 'Willst du die Datei ({size}) durch {name} ({size2}) ersetzen?',
                        different_mime: 'Die Dateien haben einen unterschiedlichen Mime-Type. Wenn du fortfährst, wird der Typ von <span class="fw-medium">{mime_old}</span> zu <span class="fw-medium">{mime_new}</span> geändert. Du solltest daher den Dateinamen anpassen.'
                    }
                },
                delete: {
                    alert: 'Beachte: Wenn du {name} löschst, wird eine Verknüpfung zu einer Entität ebenfalls gelöscht. | Beachte: Wenn du {name} löschst, werden {cnt} Verknüpfungen zu Entitäten ebenfalls gelöscht.'
                },
                clipboard: {
                    title: '{name} - Upload bestätigen',
                    file_info: '@:plugins.files.modal.detail.metadata.title',
                    toggle_edit_mode: 'Editiermodus umschalten',
                    no_preview: 'Für diesen Dateityp ist keine Vorschau verfügbar.'
                }
            },
        }
    }
}

export default de;
