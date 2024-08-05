import {
    createRouter,
    createWebHashHistory,
    onBeforeRouteUpdate,
    onBeforeRouteLeave,
} from 'vue-router';

// Pages
import Login from '@/components/Login.vue';
import MainView from '@/components/MainView.vue';
import EntityDetailView from '@/components/view/EntityDetailView.vue';
import EntityReferenceModal from '@/components/modals/entity/Reference.vue';
// Tools
import Bibliography from '@/components/BibliographyTable.vue';
import GlobalActivity from '@/components/GlobalActivity.vue';
import DataImporter from '@/components/view/DataImporter.vue';
// Settings
import Users from '@/components/Users.vue';
import Roles from '@/components/Roles.vue';
import Plugins from '@/components/Plugins.vue';
import DataModel from '@/components/DataModel.vue';
import DataModelDetailView from '@/components/DataModelDetailView.vue';
import Preferences from '@/components/Preferences.vue';
// User
import UserProfile from '@/components/UserProfile.vue';
import UserActivity from '@/components/UserActivity.vue';
import UserNotifications from '@/components/notification/UserNotifications.vue';
// Open Access Router Pages
import Landing from '@/components/openaccess/Landing.vue';
import FreeSearch from '@/components/openaccess/FreeSearch.vue';
import SingleSearch from '@/components/openaccess/SingleSearch.vue';

import DummyComponent from '@/components/DummyComponent.vue';
import NotFound from '@/components/NotFound.vue';

export {
    onBeforeRouteUpdate,
    onBeforeRouteLeave,
};

export const router = createRouter({
    history: createWebHashHistory(),
    scrollBehavior(to, from, savedPosition) {
        return {
            x: 0,
            y: 0
        };
    },
    routes: [
        // deprecated pre-0.6 routes
        // {
        //     path: '/s',
        //     redirect: { name: 'home' },
        //     children: [
        //         {
        //             path: 'context/:id',
        //             redirect: to => {
        //                 return {
        //                     name: 'entitydetail',
        //                     params: {
        //                         id: to.params.id
        //                     }
        //                 }
        //             },
        //             children: [{
        //                 path: 'sources/:aid',
        //                 redirect: to => {
        //                     return {
        //                         name: 'entityrefs',
        //                         params: {
        //                             id: to.params.id,
        //                             aid: to.params.aid,
        //                         }
        //                     }
        //                 }
        //             }]
        //         },
        //         {
        //             path: 'f/:id',
        //             redirect: to => {
        //                 return {
        //                     name: 'file',
        //                     params: {
        //                         id: to.params.id
        //                     }
        //                 }
        //             }
        //         },
        //         {
        //             path: 'user',
        //             redirect: { name: 'users' },
        //             // TODO user edit route (redirect to users or add it)
        //         },
        //         {
        //             path: 'role',
        //             redirect: { name: 'roles' },
        //             // TODO role edit route (redirect to roles or add it)
        //         },
        //         {
        //             path: 'editor/data-model',
        //             redirect: { name: 'dme' },
        //             children: [{
        //                 path: 'contexttype/:id',
        //                 redirect: to => {
        //                     return {
        //                         name: 'dmdetail',
        //                         params: {
        //                             id: to.params.id
        //                         }
        //                     }
        //                 }
        //             }]
        //         },
        //         {
        //             path: 'preferences/:id',
        //             redirect: to => {
        //                 return {
        //                     name: 'userpreferences',
        //                     params: {
        //                         id: to.params.id
        //                     }
        //                 }
        //             }
        //         }
        //     ]
        // },
        {
            path: '/',
            name: 'home',
            component: MainView,
            children: [
                {
                    path: 'e/:id',
                    name: 'entitydetail',
                    component: EntityDetailView,
                    children: [
                        {
                            path: 'refs/:aid',
                            name: 'entityrefs',
                            component: EntityReferenceModal,
                        }
                    ]
                }
            ],
            meta: {
                auth: true
            }
        },
        // Tools
        {
            path: '/bibliography',
            name: 'bibliography',
            component: Bibliography,
            children: [
                {
                    path: 'edit/:id',
                    name: 'bibedit',
                    component: DummyComponent
                    // component: BibliographyItemModal
                },
                {
                    path: 'new',
                    name: 'bibnew',
                    component: DummyComponent
                    // component: BibliographyItemModal
                }
            ],
            meta: {
                auth: true
            }
        },
        {
            path: '/activity/g',
            name: 'globalactivity',
            component: GlobalActivity,
            meta: {
                auth: true
            }
        },
        {
            path: '/import',
            name: 'dataimporter',
            component: DataImporter,
            meta: {
                auth: true
            }
        },
        {
            path: '/login',
            name: 'login',
            component: Login,
            meta: {
                auth: false
            }
        },
        // Settings
        {
            path: '/mg/users',
            name: 'users',
            component: Users,
            meta: {
                auth: true
            }
        },
        {
            path: '/mg/roles',
            name: 'roles',
            component: Roles,
            meta: {
                auth: true
            }
        },
        {
            path: '/mg/plugins',
            name: 'plugins',
            component: Plugins,
            meta: {
                auth: true
            }
        },
        {
            path: '/editor/dm',
            name: 'dme',
            component: DataModel,
            children: [
                {
                    path: 'et/:id',
                    name: 'dmdetail',
                    component: DataModelDetailView
                }
            ],
            meta: {
                auth: true
            }
        },
        {
            path: '/preferences',
            redirect: _ => {
                return {
                    name: 'preferences',
                };
            },
        },
        {
            path: '/preferences/system',
            name: 'preferences',
            component: Preferences,
            meta: {
                auth: true
            }
        },
        {
            path: '/preferences/user',
            name: 'userpreferences',
            component: Preferences,
            meta: {
                auth: true
            }
        },
        {
            path: '/notifications/:id',
            name: 'notifications',
            component: UserNotifications,
            meta: {
                auth: true
            }
        },
        {
            path: '/activity/u',
            name: 'useractivity',
            component: UserActivity,
            meta: {
                auth: true
            }
        },
        {
            path: '/profile',
            name: 'userprofile',
            component: UserProfile,
            meta: {
                auth: true
            }
        },
        {
            path: '/:pathMatch(.*)',
            name: 'not-found',
            component: NotFound,
        }
    ]
});

export function useRouter() {
    return router;
}

export default router;

export const openRouter = createRouter({
    history: createWebHashHistory(),
    scrollBehavior(to, from, savedPosition) {
        return {
            x: 0,
            y: 0,
        };
    },
    routes: [
        {
            path: '/',
            name: 'landing',
            component: Landing,
            meta: {
                auth: false,
            },
        },
        {
            path: '/free',
            name: 'freesearch',
            component: FreeSearch,
            meta: {
                auth: false,
            },
        },
        {
            path: '/single',
            name: 'singlesearch',
            component: SingleSearch,
            meta: {
                auth: false,
            },
        },
        {
            path: '/:pathMatch(.*)',
            name: 'not-found',
            component: NotFound,
            meta: {
                auth: false,
            },
        },
    ],
});

export function useOpenRouter() {
    return openRouter;
}