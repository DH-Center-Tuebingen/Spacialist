import axios from 'axios';
import router from '@/bootstrap/router.js';

import {
    throwError,
} from '@/helpers/helpers.js';

const instance = axios.create();

instance.defaults.baseURL = 'api/v1';
instance.defaults.withCredentials = true;
instance.interceptors.response.use(response => {
    return response;
}, error => {
    const code = error.response.status;
    switch(code) {
        case 401:
            // Only append redirect query if from another route than login
            // to prevent recursivly appending current route's full path
            // on reloading login page
            if(router.currentRoute.name != 'login' && !!router.currentRoute.value.redirectedFrom) {
                const redirectPath = router.currentRoute.value.redirectedFrom.fullPath;
                router.push({
                    name: 'login',
                    query: {
                        ...router.currentRoute.value.query,
                        redirect: redirectPath,
                    },
                });
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

export const global_api = (verb, url, data, external = false, withHeaders = false) => {
    if(external) {
        if(verb.toLowerCase() == 'get' || verb.toLowerCase() == 'delete') {
            return $httpQueue.add(
                () => external[verb](url, {
                    crossdomain: true,
                }).then(response => {
                    if(withHeaders) {
                        return {
                            data: response.data,
                            headers: response.headers,
                        };
                    } else {
                        return response.data;
                    }
                })
            )
        } else {
            return $httpQueue.add(
                () => external[verb](url, data, {
                    crossdomain: true,
                }).then(response => {
                    if(withHeaders) {
                        return {
                            data: response.data,
                            headers: response.headers,
                        };
                    } else {
                        return response.data;
                    }
                })
            )
        }
    } else {
        if(verb.toLowerCase() == 'get' || verb.toLowerCase() == 'delete') {
            return $httpQueue.add(
                () => instance[verb](url, data).then(response => {
                    if(withHeaders) {
                        return {
                            data: response.data,
                            headers: response.headers,
                        };
                    } else {
                        return response.data;
                    }
                })
            )
        } else {
            return $httpQueue.add(
                () => instance[verb](url, data).then(response => {
                    if(withHeaders) {
                        return {
                            data: response.data,
                            headers: response.headers,
                        };
                    } else {
                        return response.data;
                    }
                })
            )
        }
    }
};

export const api_base = instance.defaults.baseURL;

export default instance;