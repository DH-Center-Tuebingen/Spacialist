export async function updateAttributeDependency(entityTypeAttributeId, dependency) {
    const apiData = {};
    const dependencyData = {};
    dependencyData.or = dependency.or;
    dependencyData.groups = dependency.groups.map(group => {
        group.rules = group.rules.map(rule => {
            const formattedRule = {
                attribute: rule.attribute.id,
                operator: rule.operator.operator,
            };

            if(!rule.operator.no_parameter) {
                formattedRule.value = rule.value.value || rule.value;
            }

            return formattedRule;
        });
        return group;
    });
    apiData.data = dependencyData;
    return $httpQueue.add(
        () => http.patch(`/editor/dm/entity_type/attribute/${entityTypeAttributeId}/dependency`, apiData)
    );
}