import axios from 'axios';
import router from '%router';

import {
    throwError,
} from '@/helpers/helpers.js';

const instance = axios.create();

// These errors need to be handled manually.
export const unhandledErrors = [400, 422];

export function isUnhandledError(axiosError) {
    if(!axiosError?.response?.status) throw Error('Response object is missing status property');
    const status = axiosError.response.status;
    // If status is below 400, we don't need to handle it.
    if(status < 400) return false;
    return unhandledErrors.includes(status);
}

// Some errors are handled by the system.
// If we handle those again, we have e.g. multiple error popups.
// This allows us to just catch the errors that are not handled by 
// the system.
export function handleUnhandledErrors(axiosError, callback) {
    if(isUnhandledError(axiosError)) {
        return callback(axiosError);
    }
}

instance.defaults.baseURL = 'api/v1';
instance.defaults.withCredentials = true;
instance.interceptors.response.use(response => {
    return response;
}, error => {
    if(isUnhandledError(error)) {
        return Promise.reject(error);
    }
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
        default:
            throwError(error);
            break;
    }
    return Promise.reject(error);
});

export function useHttp() {
    return instance;
}

export const open = axios.create();

open.defaults.baseURL = '/api/v1/open';
open.defaults.withCredentials = false;

export function useOpenHttp() {
    return open;
}

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
            );
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
            );
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
            );
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
            );
        }
    }
};

export const api_base = instance.defaults.baseURL;

export default instance;