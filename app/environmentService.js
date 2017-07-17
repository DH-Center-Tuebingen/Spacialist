spacialistApp.service('environmentService', ['httpGetFactory', 'httpGetPromise', function(httpGetFactory, httpGetPromise) {
    var env = {};
    env.contexts = {};
    env.contexts.data = {};
    env.contexts.children = {};
    env.contexts.roots = [];

    // init();
    //
    // function init() {
    //     getContextList();
    // }

    env.getParentId = function(id) {
        if(!env.contexts.data[id]) return null;
        return env.contexts.data[id].root_context_id;
    };

    function getContextList() {
        httpGetFactory('api/context', function(response) {
            env.contexts.data = response.contexts;
            env.contexts.roots = response.roots;
            env.contexts.children = response.children;

            angular.forEach(env.contexts.data, function(context) {
                context.collapsed = true;
                context.visible = true;
            });
        });
    }

    env.getContexts = function() {
        return httpGetPromise.getData('api/context').then(function(response) {
            angular.forEach(response.contexts, function(c) {
                c.visible = true;
                c.collapsed = true;
            });
            return {
                data: response.contexts,
                roots: response.roots,
                children: response.children
            };
        });
    }

    return env;
}]);
