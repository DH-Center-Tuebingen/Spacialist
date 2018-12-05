const en = {
    plugins: {
        map: {
            tab: {
                title: 'Map',
                loading: 'Loading map data&hellip;'
            },
            untitled: 'Untitled',
            'new-item': 'Add new layer&hellip;',
            'layer-editor': {
                title: 'Layer-Editor',
                'unnamed-layer': 'Unnamed Layer',
                'properties-of': 'Properties of {name}',
                toasts: {
                    updated: {
                        title: 'Layer updated',
                        msg: '{name} successfully updated.'
                    }
                },
                properties: {
                    subdomains: 'Subdomains',
                    attribution: 'Attribution',
                    styles: 'Styles',
                    'api-key': 'API-Key',
                    'layer-type': 'Layer-Type'
                }
            },
            gis: {
                title: 'GIS',
                import: {
                    button: 'Import Geodata',
                    title: 'Import Geodata',
                    epsg: 'EPSG-Code',
                    feature_count: '# of features',
                    files: {
                        button: 'Select files or drop here',
                        selected: 'Selected files',
                        confirm: 'Confirm import'
                    },
                    csv: {
                        label: 'CSV',
                        delimiter: 'Delimiter',
                        header_row: 'Header Row',
                        points: 'Points',
                        wkt: 'WKT',
                        preview_hide: 'Hide Data-Preview',
                        preview_show: 'Show Data-Preview',
                        lon: 'Easting (X)',
                        lat: 'Northing (Y)',
                        wkt: 'WKT',
                        delimiters: {
                            comma: 'Comma',
                            colon: 'Colon',
                            semicolon: 'Semicolon',
                            tab: 'Tab',
                            pipe: 'Vertical Bar (Pipe)',
                            space: 'Space'
                        }
                    },
                    kml: {
                        label: 'KML/KMZ',
                    },
                    shape: {
                        label: 'Shape File',
                    },
                    geojson: {
                        label: 'GeoJSON'
                    },
                },
                'available-layers': 'Available Layers',
                'selected-layers': 'Selected Layers',
                info: 'Use <kbd>Double Click</kbd> to add an available layer to selected layers, and again to remove it.',
                toasts: {
                    imported: {
                        title: 'Import finished',
                        msg: '{cnt} features added.'
                    },
                    updated: {
                        style: {
                            title: 'Style applied',
                            msg: 'Style for {name} successfully applied.'
                        },
                        labels: {
                            title: 'Labels applied',
                            msg: 'Labels for {name} successfully applied.'
                        },
                    }
                },
                props: {
                    title: 'Layer Properties',
                    style: {
                        title: 'Style',
                        'color-ramp': 'Color Ramp',
                        classes: 'Classes',
                        apply: 'Apply Style',
                        none: 'None',
                        categorized: 'Categorized',
                        graduated: 'Graduated',
                        colors: {
                            blues: 'Blue',
                            greens: 'Green',
                            reds: 'Red',
                            'blue-green': 'Blue-Green'
                        },
                        'equal-interval': 'Equal Interval',
                        quantile: 'Quantile (Equal Count)'
                    },
                    labels: {
                        title: 'Label',
                        'use-entity-name': 'Use Entity Names',
                        style: 'Style',
                        transform: 'Transform',
                        'fill-color': 'Fill Color',
                        'border-color': 'Border Color',
                        'border-size': 'Border Size',
                        buffer: 'Buffer',
                        background: {
                            title: 'Background',
                            'padding-x': 'Padding (X)',
                            'padding-y': 'Padding (Y)',
                        },
                        position: {
                            title: 'Position',
                            'offset-x': 'Offset (X)',
                            'offset-y': 'Offset (Y)',
                            placement: 'Placement'
                        },
                        uppercase: 'Uppercase',
                        lowercase: 'Lowercase',
                        capitalize: 'Small Caps',
                        normal: 'Normal',
                        bold: 'Bold',
                        italic: 'Italic',
                        oblique: 'Oblique',
                        'bold-italic': 'Bold-Italic',
                        'bold-oblique': 'Bold-Oblique',
                        top: 'top',
                        right: 'right',
                        bottom: 'bottom',
                        left: 'left',
                        center: 'center',
                        'top-right': 'top-right',
                        'top-left': 'top-left',
                        'bottom-right': 'bottom-right',
                        'bottom-left': 'bottom-left',
                        apply: 'Apply Labels'
                    },
                    diagrams: {
                        title: 'Diagrams'
                    }
                },
                menu: {
                    'zoom-to': 'Zoom to layer',
                    'export-layer': 'Export layer',
                    'toggle-feature': 'Toggle feature count',
                    properties: 'Properties'
                }
            }
        }
    }
}

export default en;
