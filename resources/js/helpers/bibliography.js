export const bibliographyTypes = [
    {
        name: 'article',
        id: 0,
        fields: [
            'author',
            'title',
            'journal',
            'year',
            'volume',
            'number',
            'pages',
            'month',
            'note',
            'doi',
            'email',
            'url'
        ],
        mandatory: {
            author: true,
            title: true,
            journal: true,
            year: true,
        },
    },
    {
        name: 'book',
        id: 1,
        fields: [
            'title',
            'publisher',
            'year',
            'author',
            'editor',
            'volume',
            'number',
            'address',
            'series',
            'edition',
            'month',
            'note',
            'email',
            'url'
        ],
        mandatory: {
            author: 'editor',
            editor: 'author',
            title: true,
            publisher: true,
            year: true,
        },
    },
    {
        name: 'incollection',
        id: 2,
        fields: [
            'author',
            'title',
            'booktitle',
            'publisher',
            'year',
            'editor',
            'volume',
            'number',
            'series',
            'pages',
            'address',
            'month',
            'organization',
            'publisher',
            'note',
            'subtype',
            'email',
            'url'
        ],
        mandatory: {
            author: true,
            title: true,
            booktitle: true,
            publisher: true,
            year: true,
        },
    },
    {
        name: 'misc',
        id: 3,
        fields: ['author',
            'title',
            'howpublished',
            'month',
            'year',
            'note',
            'email',
            'url'
        ]
    },
    {
        name: 'booklet',
        id: 4,
        fields: [
            'title',
            'author',
            'howpublished',
            'address',
            'month',
            'year',
            'note',
            'email',
            'url'
        ],
        mandatory: {
            title: true,
        },
    },
    {
        name: 'conference',
        id: 5,
        fields: [
            'author',
            'title',
            'booktitle',
            'year',
            'editor',
            'volume',
            'number',
            'series',
            'pages',
            'address',
            'month',
            'organization',
            'publisher',
            'note',
            'email',
            'url'
        ],
        mandatory: {
            author: true,
            title: true,
            booktitle: true,
            year: true,
        },
    },
    {
        name: 'inbook',
        id: 6,
        fields: [
            'title',
            'publisher',
            'year',
            'author',
            'editor',
            'chapter',
            'pages',
            'volume',
            'number',
            'series',
            'address',
            'edition',
            'month',
            'note',
            'subtype',
            'email',
            'url'
        ],
        mandatory: {
            author: 'editor',
            editor: 'author',
            title: true,
            chapter: 'pages',
            pages: 'chapter',
            publisher: true,
            year: true,
        },
    },
    {
        name: 'inproceedings',
        id: 7,
        fields: [
            'author',
            'title',
            'booktitle',
            'year',
            'editor',
            'volume',
            'number',
            'series',
            'pages',
            'address',
            'month',
            'organization',
            'publisher',
            'note',
            'email',
            'url'
        ],
        mandatory: {
            author: true,
            title: true,
            booktitle: true,
            year: true,
        },
    },
    {
        name: 'manual',
        id: 8,
        fields: [
            'title',
            'author',
            'organization',
            'address',
            'edition',
            'month',
            'year',
            'note',
            'email',
            'url'
        ],
        mandatory: {
            title: true,
        },
    },
    {
        name: 'mastersthesis',
        id: 9,
        fields: [
            'author',
            'title',
            'school',
            'year',
            'address',
            'month',
            'note',
            'subtype',
            'email',
            'url'
        ],
        mandatory: {
            author: true,
            title: true,
            school: true,
            year: true,
        },
    },
    {
        name: 'phdthesis',
        id: 10,
        fields: [
            'author',
            'title',
            'school',
            'year',
            'address',
            'month',
            'note',
            'subtype',
            'email',
            'url'
        ],
        mandatory: {
            author: true,
            title: true,
            school: true,
            year: true,
        },
    },
    {
        name: 'proceedings',
        id: 11,
        fields: [
            'title',
            'year',
            'editor',
            'volume',
            'number',
            'series',
            'address',
            'month',
            'organization',
            'publisher',
            'note',
            'email',
            'url'
        ],
        mandatory: {
            title: true,
            year: true,
        },
    },
    {
        name: 'techreport',
        id: 12,
        fields: [
            'author',
            'title',
            'institution',
            'year',
            'number',
            'address',
            'month',
            'note',
            'subtype',
            'email',
            'url'
        ],
        mandatory: {
            author: true,
            title: true,
            institution: true,
            year: true,
        },
    },
    {
        name: 'unpublished',
        id: 13,
        fields: [
            'author',
            'title',
            'note',
            'month',
            'year',
            'email',
            'url'
        ],
        mandatory: {
            author: true,
            title: true,
            note: true,
        },
    }
];

export const formatAuthors = (authors, count = 3) => {
    if(!authors) return authors;

    const authorList = authors.trim().split(/\s+and\s+/g);
    let authorNameList = authorList.map(author => formatAuthorName(author));

    // only add 'et. al.' to `count` or more authors
    // otherwise return original authors
    if(authorNameList.length === 0) {
        return authors;
    } else if(authorNameList.length === 1) {
        return authorNameList[0];
    } else if(authorNameList.length >= 2 && authorList.length <= count) {
        const lastAuthor = authorNameList.pop();
        return authorNameList.join(', ') + ' & ' + lastAuthor;
    }

    return authorNameList.slice(0, count).join(', ') + ' et al.';
};

export const formatAuthorName = (author) => {
    let names = author.split(/\s*,\s*/g);
    return names.reverse().join(' ');
};