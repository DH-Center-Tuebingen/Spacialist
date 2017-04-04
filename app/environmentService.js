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



    function getContextList() {
        httpGetFactory('api/context/getRecursive', function(response) {
            env.contexts.data = response.contexts;
            env.contexts.roots = response.roots;
            env.contexts.children = response.children;


            angular.forEach(env.contexts.data, function(context) {
                context.collapsed = true;
            });
        });
    }

    return env;
}]);
