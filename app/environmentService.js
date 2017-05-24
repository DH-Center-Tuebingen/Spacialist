spacialistApp.service('environmentService', ['httpGetFactory', function(httpGetFactory) {
    var env = {};
    env.contexts = {};
    env.contexts.data = {};
    env.contexts.children = {};
    env.contexts.roots = [];


    init();

    function init() {
        getContextList();
    }

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

    return env;
}]);
