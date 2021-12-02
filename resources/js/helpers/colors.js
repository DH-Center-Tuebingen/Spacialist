export const gradients = [
    {
        key: 'reds',
        label: 'Reds',
        values: [{
                color: 'rgba(255, 0, 0, 1)',
                index: 0,
            }, {
                color: 'rgba(255, 255, 255, 1)',
                index: 100,
            },
        ]
    },
    {
        key: 'blues',
        label: 'Blues',
        values: [{
                color: 'rgba(0, 0, 255, 1)',
                index: 0,
            }, {
                color: 'rgba(255, 255, 255, 1)',
                index: 100,
            },
        ]
    },
    {
        key: 'greens',
        label: 'Greens',
        values: [{
                color: 'rgba(0, 255, 0, 1)',
                index: 0,
            }, {
                color: 'rgba(255, 255, 255, 1)',
                index: 100,
            },
        ]
    },
    {
        key: 'yellows',
        label: 'Yellows',
        values: [{
                color: 'rgba(255, 255, 0, 1)',
                index: 0,
            }, {
                color: 'rgba(0, 0, 0, 1)',
                index: 100,
            },
        ]
    },
    {
        key: 'oranges',
        label: 'Oranges',
        values: [{
                color: 'rgba(255, 170, 0, 1)',
                index: 0,
            }, {
                color: 'rgba(255, 255, 255, 1)',
                index: 100,
            },
        ]
    },
    {
        key: 'purples',
        label: 'Purples',
        values: [{
                color: 'rgba(170, 0, 255, 1)',
                index: 0,
            }, {
                color: 'rgba(0, 0, 0, 1)',
                index: 100,
            },
        ]
    },
    {
        key: 'magmas',
        label: 'Magmas',
        values: [{
                color: 'rgba(65, 20, 95, 1)',
                index: 0,
            }, {
                color: 'rgba(255, 30, 30, 1)',
                index: 50,
            }, {
                color: 'rgba(250, 225, 70, 1)',
                index: 100,
            },
        ]
    },
    {
        key: 'summer',
        label: 'Summer',
        values: [{
                color: 'rgba(35, 195, 200, 1)',
                index: 0,
            }, {
                color: 'rgba(255, 190, 45, 1)',
                index: 100,
            },
        ]
    },
    {
        key: 'winter',
        label: 'Winter',
        values: [{
                color: 'rgba(65, 95, 250, 1)',
                index: 0,
            }, {
                color: 'rgba(250, 70, 110, 1)',
                index: 100,
            },
        ]
    },
    {
        key: 'blush',
        label: 'Blush',
        values: [{
                color: 'rgba(240, 175, 200, 1)',
                index: 0,
            }, {
                color: 'rgba(150, 190, 235, 1)',
                index: 100,
            },
        ]
    },
    {
        key: 'morning',
        label: 'Morning',
        values: [{
                color: 'rgba(40, 100, 205, 1)',
                index: 0,
            }, {
                color: 'rgba(255, 200, 30, 1)',
                index: 65,
            }, {
                color: 'rgba(255, 70, 210, 1)',
                index: 100,
            },
        ]
    },
];

export const calculateColorSteps = (key, steps) => {
    const grad = gradients.find(g => g.key == key);

    if(!grad) {
        return [];
    }

    const stepSize = 100 / (steps - 1);
    const vc = grad.values;
    const vcr = grad.values.slice();
    vcr.reverse();
    const vcl = vc.length - 1;

    const colorList = [];
    let idx = -1;
    for(let i=0, step=0; step<100; i++) {
        step = i*stepSize;
        idx = vc.findIndex(c => c.index >= step);
        const c1 = vc[idx];
        idx = vcl - vcr.findIndex(c => c.index <= step);
        const c2 = vc[idx];
        const size = Math.abs(c2.index - c1.index);
        const w1 = size > 0 ? 1 - (Math.abs(step - c1.index) / size) : 1;
        const w2 = size > 0 ? 1 - (Math.abs(c2.index - step) / size) : 0;
        const startColor = c1.color.match(/rgba\((\d+),\s?(\d+),\s?(\d+),\s?(\d+|\d?\.\d+)\)/);
        const endColor = c2.color.match(/rgba\((\d+),\s?(\d+),\s?(\d+),\s?(\d+|\d?\.\d+)\)/);
        const r1 = parseInt(startColor[1])*w1;
        const g1 = parseInt(startColor[2])*w1;
        const b1 = parseInt(startColor[3])*w1;
        const a1 = parseInt(startColor[4])*w1;
        const r2 = parseInt(endColor[1])*w2;
        const g2 = parseInt(endColor[2])*w2;
        const b2 = parseInt(endColor[3])*w2;
        const a2 = parseInt(endColor[4])*w2;
        const r = (r1+r2);
        const g = (g1+g2);
        const b = (b1+b2);
        const a = (a1+a2);
        colorList.push(`rgba(${r}, ${g}, ${b}, ${a})`);
    }

    return colorList;
}