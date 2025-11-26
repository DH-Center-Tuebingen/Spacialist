<?php

namespace App\Services;

use App\EntityAttribute;
use App\Services\DependencyService;
use Illuminate\Support\Collection;

class AttributeDependencyService
{
    /**
     * Supported dependency operators and whether they require a value
     */
    const OPERATORS = [
        '<' => true,
        '>' => true,
        '<=' => true,
        '>=' => true,
        '=' => true,
        '!=' => true,
        '?' => false,
        '!?' => false,
    ];

    /**
     * Update dependencies for an entity attribute
     *
     * @param EntityAttribute $entityAttribute
     * @param array $dependencyData
     * @return array|null The formatted dependency data or null if empty
     * @throws \InvalidArgumentException
     */
    public function updateDependencies(EntityAttribute $entityAttribute, array $dependencyData): ?array
    {
        $this->validateDependencyStructure($dependencyData);
        
        $formattedDependencies = $this->formatDependencies($dependencyData, $entityAttribute);
        
        if($formattedDependencies === null) {
            return null;
        }

        $this->validateDependencyRules($formattedDependencies, $entityAttribute);
        
        return $formattedDependencies;
    }

    /**
     * Validate the basic structure of dependency data
     *
     * @param array $dependencyData
     * @throws \InvalidArgumentException
     */
    protected function validateDependencyStructure(array $dependencyData): void
    {
        if(!isset($dependencyData['or']) || !is_bool($dependencyData['or'])) {
            throw new \InvalidArgumentException('Dependency data must include a boolean "or" field');
        }

        if(!isset($dependencyData['groups']) || !is_array($dependencyData['groups'])) {
            throw new \InvalidArgumentException('Dependency data must include a "groups" array');
        }
    }

    /**
     * Format dependency data into the storage format
     *
     * @param array $dependencyData
     * @param EntityAttribute $entityAttribute
     * @return array|null
     * @throws \InvalidArgumentException
     */
    protected function formatDependencies(array $dependencyData, EntityAttribute $entityAttribute): ?array
    {
        $hasData = false;
        $dependsOn = [
            'or' => $dependencyData['or'],
        ];

        foreach($dependencyData['groups'] as $groupIndex => $group) {
            if(!isset($group['or']) || !is_bool($group['or'])) {
                throw new \InvalidArgumentException("Group at index {$groupIndex} must include a boolean \"or\" field");
            }

            if(!isset($group['rules']) || !is_array($group['rules'])) {
                throw new \InvalidArgumentException("Group at index {$groupIndex} must include a \"rules\" array");
            }

            if(count($group['rules']) > 0) {
                $hasData = true;
                $groupRules = [];

                foreach($group['rules'] as $ruleIndex => $rule) {
                    $groupRules[] = $this->formatRule($rule, $ruleIndex, $entityAttribute);
                }

                $dependsOn['groups'][] = [
                    'or' => $group['or'],
                    'rules' => $groupRules,
                ];
            }
        }

        return $hasData ? $dependsOn : null;
    }

    /**
     * Format a single dependency rule
     *
     * @param array $rule
     * @param int $ruleIndex
     * @param EntityAttribute $entityAttribute
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function formatRule(array $rule, int $ruleIndex, EntityAttribute $entityAttribute): array
    {
        if(!isset($rule['operator'])) {
            throw new \InvalidArgumentException("Rule at index {$ruleIndex} is missing \"operator\" field");
        }

        if(!isset($rule['attribute'])) {
            throw new \InvalidArgumentException("Rule at index {$ruleIndex} is missing \"attribute\" field");
        }

        $operator = $rule['operator'];
        
        if(!array_key_exists($operator, self::OPERATORS)) {
            throw new \InvalidArgumentException("Invalid operator \"{$operator}\" in rule at index {$ruleIndex}");
        }

        $formattedRule = [
            'operator' => $operator,
            'on' => $rule['attribute'],
        ];

        // Check if operator requires a value
        if(self::OPERATORS[$operator]) {
            if(!isset($rule['value'])) {
                throw new \InvalidArgumentException("Rule at index {$ruleIndex} with operator \"{$operator}\" requires a \"value\" field");
            }
            $formattedRule['value'] = $rule['value'];
        }

        return $formattedRule;
    }

    /**
     * Validate dependency rules for business logic
     *
     * @param array $dependsOn
     * @param EntityAttribute $entityAttribute
     * @throws \InvalidArgumentException
     */
    protected function validateDependencyRules(array $dependsOn, EntityAttribute $entityAttribute): void
    {
        $entityTypeId = $entityAttribute->entity_type_id;
        $currentAttributeId = $entityAttribute->attribute_id;

        foreach($dependsOn['groups'] as $group) {
            foreach($group['rules'] as $rule) {
                $targetAttributeId = $rule['on'];

                // Check for self-reference
                if($targetAttributeId == $currentAttributeId) {
                    throw new \InvalidArgumentException('An attribute cannot depend on itself');
                }

                // Validate that the target attribute exists in the same entity type
                $targetExists = EntityAttribute::where('entity_type_id', $entityTypeId)
                    ->where('attribute_id', $targetAttributeId)
                    ->exists();

                if(!$targetExists) {
                    throw new \InvalidArgumentException("Attribute {$targetAttributeId} does not exist in this entity type");
                }
            }
        }
    }

    /**
     * Get all attributes that depend on the given attribute
     *
     * @param EntityAttribute $entityAttribute
     * @return Collection
     */
    public function getDependentAttributes(EntityAttribute $entityAttribute): Collection
    {
        $attributeId = $entityAttribute->attribute_id;
        $entityTypeId = $entityAttribute->entity_type_id;

        return EntityAttribute::where('entity_type_id', $entityTypeId)
            ->whereNotNull('depends_on')
            ->get()
            ->filter(function($ea) use ($attributeId) {
                $dependencies = $ea->depends_on;
                if(!$dependencies || !isset($dependencies['groups'])) {
                    return false;
                }

                foreach($dependencies['groups'] as $group) {
                    foreach($group['rules'] as $rule) {
                        if($rule['on'] == $attributeId) {
                            return true;
                        }
                    }
                }

                return false;
            });
    }
}
