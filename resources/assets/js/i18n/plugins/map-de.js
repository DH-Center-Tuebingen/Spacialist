const de = {
    plugins: {
        map: {
            tab: {
                title: 'Karte',
                loading: 'Lade Kartendaten&hellip;'
            },
            untitled: 'Kein Titel',
            'new-item': 'Neue Ebene hinzufügen&hellip;',
            'layer-editor': {
                title: 'Ebenen-Editor',
                'unnamed-layer': 'Unbenannte Ebene',
                'properties-of': 'Eigenschaften von {name}',
                toasts: {
                    updated: {
                        title: 'Ebene aktualisiert',
                        msg: '{name} wurde erfolgreich aktualisiert.'
                    }
                },
                properties: {
                    subdomains: 'Subdomains',
                    attribution: 'Attribuierung',
                    styles: 'Stile',
                    'api-key': 'API-Key',
                    'layer-type': 'Ebenen-Typ'
                }
            },
            gis: {
                title: 'GIS',
                'import-button': 'Geodaten importieren',
                'available-layers': 'Verfügbare Ebenen',
                'selected-layers': 'Ausgewählte Ebenen',
                info: 'Benutze <kbd>Doppelklick</kbd> um eine verfügbare Ebene zu den ausgewählten Ebenen hinzuzufügen, und um sie wieder so entfernen.',
                toasts: {
                    imported: {
                        title: 'Import abgeschlossen',
                        msg: '{cnt} Objekte hinzugefügt.'
                    },
                    updated: {
                        style: {
                            title: 'Stil angewendet',
                            msg: 'Stil für {name} erfolgreich angewendet.'
                        },
                        labels: {
                            title: 'Beschriftungen angewendet',
                            msg: 'Beschriftungen für {name} erfolgreich angewendet.'
                        },
                    }
                },
                props: {
                    title: 'Ebenen-Eigenschaften',
                    style: {
                        title: 'Stil',
                        'color-ramp': 'Farbverlauf',
                        classes: 'Klassen',
                        apply: 'Stil anwenden',
                        none: 'Kein Stil',
                        categorized: 'Kategorisiert',
                        graduated: 'Abgestuft',
                        colors: {
                            blues: 'Blau',
                            greens: 'Grün',
                            reds: 'Rot',
                            'blue-green': 'Blau-Grün'
                        },
                        'equal-interval': 'Gleicher Intervall',
                        quantile: 'Quantil (Gleiche Anzahl)'
                    },
                    labels: {
                        title: 'Beschriftung',
                        style: 'Stil',
                        transform: 'Transform',
                        'fill-color': 'Füllfarbe',
                        'border-color': 'Rahmenfarbe',
                        'border-size': 'Rahmengröße',
                        buffer: 'Buffer',
                        background: {
                            title: 'Hintergrund',
                            'padding-x': 'Füllung (X)',
                            'padding-y': 'Füllung (Y)',
                        },
                        position: {
                            title: 'Position',
                            'offset-x': 'Versatz (X)',
                            'offset-y': 'Versatz (Y)',
                            placement: 'Platzierung'
                        },
                        uppercase: 'Großbuchstaben',
                        lowercase: 'Kleinbuchstaben',
                        capitalize: 'Kapitälchen',
                        normal: 'Normal',
                        bold: 'Fett',
                        italic: 'Kursiv',
                        oblique: 'Schräg',
                        'bold-italic': 'Fett-Kursiv',
                        'bold-oblique': 'Fett-Schräg',
                        top: 'Oben',
                        right: 'Rechts',
                        bottom: 'Unten',
                        left: 'Links',
                        center: 'Zentriert',
                        'top-right': 'Rechts oben',
                        'top-left': 'Links oben',
                        'bottom-right': 'Rechts unten',
                        'bottom-left': 'Links unten',
                        apply: 'Beschriftungen anwenden'
                    },
                    diagrams: {
                        title: 'Diagram'
                    }
                },
                menu: {
                    'zoom-to': 'Auf Ebene zoomen',
                    'export-layer': 'Ebene exportieren',
                    'toggle-feature': 'Objektanzahl umschalten',
                    properties: 'Eigenschaften'
                }
            }
        }
    }
}

export default de;
