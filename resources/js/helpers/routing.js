import router from '../bootstrap/router.js';

export function routeToEntity(id) {
    const currentRoute = router.currentRoute.value;

    // Already routed to desired entity, abort
    if((currentRoute.name == 'entitydetail' || currentRoute.name == 'entityrefs') && currentRoute.params.id == id) {
        return;
    }

    router.push({
        append: true,
        name: 'entitydetail',
        params: {
            id: id,
        },
    });
};

export function routeToFile(id) {
    const currentRoute = router.currentRoute.value;

    // Already routed to desired file, abort
    if(currentRoute.name == 'file' && currentRoute.params.id == id) {
        return;
    }

    router.push({
        append: true,
        name: 'file',
        params: {
            id: id,
        },
    });
};

export function routeToBibliography(id) {
    const currentRoute = router.currentRoute.value;

    // Already routed to desired bibliography, abort
    if(currentRoute.name == 'bibedit' && currentRoute.params.id == id) {
        return;
    }

    router.push({
        append: true,
        name: 'bibedit',
        params: {
            id: id,
        },
    });
};

export function routeToGeodata(id) {
    const currentRoute = router.currentRoute.value;

    // TODO
    // Already routed to desired geodata, abort
    // if(currentRoute.name == '' && currentRoute.params.id == id) {
    //     return;
    // }

    // router.push({
    //     append: true,
    //     name: 'file',
    //     params: {
    //         id: id,
    //     },
    // });
};