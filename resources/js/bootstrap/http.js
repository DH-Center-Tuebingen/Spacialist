import axios from 'axios';
import router from './router.js';


axios.defaults.baseURL = 'api/v1';
axios.defaults.withCredentials = true;
axios.interceptors.response.use(response => {
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
    return axios;
}

export default axios;