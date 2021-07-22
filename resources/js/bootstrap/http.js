import axios from 'axios';
import router from './router.js';

import {
    throwError,
} from '../helpers/helpers.js';

const instance = axios.create();

instance.defaults.baseURL = 'api/v1';
instance.defaults.withCredentials = true;
instance.interceptors.response.use(response => {
    return response;
}, error => {
    const code = error.response.status;
    switch(code) {
        case 401:
            // TODO how to redirect in vue-router?
            let redirectTo = ''
            // Only append redirect query if from another route than login
            // to prevent recursivly appending current route's full path
            // on reloading login page
            if(router.currentRoute.name != 'login') {
                redirectTo = router.currentRoute.value.fullPath;
            }
            // auth().logout({
            //     redirect: {
            //         name: 'login',
            //         query: redirectQuery
            //     }
            // });
            break;
        case 400:
        case 422:
            // don't do anything. Handle these types at caller
            break;
        default:
            throwError(error);
            break;
    }
    return Promise.reject(error);
});

export function useHttp() {
    return instance;
};

export const external = axios.create({
    headers: {
        common: {},
    },
});

export const global_api = (verb, url, data, external = false) => {
    if(external) {
        if(verb.toLowerCase() == 'get' || verb.toLowerCase() == 'delete') {
            return $httpQueue.add(
                () => external[verb](url, {
                    crossdomain: true,
                }).then(response => response.data)
            )
        } else {
            return $httpQueue.add(
                () => external[verb](url, data, {
                    crossdomain: true,
                }).then(response => response.data)
            )
        }
    } else {
        if(verb.toLowerCase() == 'get' || verb.toLowerCase() == 'delete') {
            return $httpQueue.add(
                () => instance[verb](url).then(response => response.data)
            )
        } else {
            return $httpQueue.add(
                () => instance[verb](url, data).then(response => response.data)
            )
        }
    }
};

export default instance;