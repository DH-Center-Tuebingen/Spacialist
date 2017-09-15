spacialistApp.service('transitionService', [function() {
    var transitions = {
        to: 'root.spacialist',
        params: {},
        initialized: false
    };

    transitions.setTransition = function(state) {
        transitions.to = state.to;
        transitions.params = state.params;
        transitions.initialized = true;
    };

    transitions.unsetTransition = function() {
        transitions.to = 'root.spacialist';
        transitions.params = {};
        transitions.initialized = false;
    };

    transitions.isInitialized = function() {
        return transitions.initialized;
    };

    return transitions;
}]);
