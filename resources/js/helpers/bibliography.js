export const bibliographyTypes = [
    {
        name: 'article',
        id: 0,
        fields: [
            'author', 'title', 'journal', 'year', 'volume', 'number', 'pages', 'month', 'note'
        ]
    },
    {
        name: 'book',
        id: 1,
        fields: [
            'title', 'publisher', 'year', 'author', 'editor', 'volume', 'number', 'address', 'series', 'edition', 'month', 'note'
        ]
    },
    {
        name: 'incollection',
        id: 2,
        fields: [
            'author', 'title', 'booktitle', 'publisher', 'year', 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note'
        ]
    },
    {
        name: 'misc',
        id: 3,
        fields: ['author', 'title', 'howpublished', 'month', 'year', 'note'
        ]
    },
    {
        name: 'booklet',
        id: 4,
        fields: [
            'title', 'author', 'howpublished', 'address', 'month', 'year', 'note'
        ]
    },
    {
        name: 'conference',
        id: 5,
        fields: [
                'author', 'title', 'booktitle', 'year', 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note'
        ]
    },
    {
        name: 'inbook',
        id: 6,
        fields: [
            'title', 'publisher', 'year', 'author', 'editor', 'chapter', 'pages', 'volume', 'number', 'series', 'address', 'edition', 'month', 'note'
        ]
    },
    {
        name: 'inproceedings',
        id: 7,
        fields: [
                'author', 'title', 'booktitle', 'year', 'editor', 'volume', 'number', 'series', 'pages', 'address', 'month', 'organization', 'publisher', 'note'
        ]
    },
    {
        name: 'manual',
        id: 8,
        fields: [
            'title', 'author', 'organization', 'address', 'edition', 'month', 'year', 'note'
        ]
    },
    {
        name: 'mastersthesis',
        id: 9,
        fields: [
            'author', 'title', 'school', 'year', 'address', 'month', 'note'
        ]
    },
    {
        name: 'phdthesis',
        id: 10,
        fields: [
            'author', 'title', 'school', 'year', 'address', 'month', 'note'
        ]
    },
    {
        name: 'proceedings',
        id: 11,
        fields: [
            'title', 'year', 'editor', 'volume', 'number', 'series', 'address', 'month', 'organization', 'publisher', 'note'
        ]
    },
    {
        name: 'techreport',
        id: 12,
        fields: [
            'author', 'title', 'institution', 'year', 'number', 'address', 'month', 'note'
        ]
    },
    {
        name: 'unpublished',
        id: 13,
        fields: [
            'author', 'title', 'note', 'month', 'year'
        ]
    }
]