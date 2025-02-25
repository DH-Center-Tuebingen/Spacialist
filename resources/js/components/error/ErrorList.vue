<template>
    <div class="alert alert-danger error-list">
        <header class="fw-bold">
            {{ header }}
        </header>
        <ul
            v-if="hasItems"
            class="mb-0"
        >
            <li
                v-for="(item, idx) in items"
                :key="idx"
            >
                {{ item }}
            </li>
        </ul>
    </div>
</template>

<script>
    import { computed } from 'vue';

    export default {
        props: {
            headerSeparator: {
                type: String,
                default: ':'
            },
            separator: {
                type: String,
                default: ','
            },
            value: {
                type: String,
                required: true
            }
        },
        setup(props) {
            const header = computed(_ => {
                return props.value.split(props.headerSeparator)[0].trim();
            });

            const items = computed(_ => {
                // Replace text inside double curly braces with placeholders
                const preservationRegex = RegExp('{{(.*?)}}', 'g');
                const variables = {};
                let counter = 1;
                const preservationMatches = props.value.replace(preservationRegex, (match, p1 = '') => {
                    const key = `$${counter++}`;
                    variables[key] = p1;
                    return key;
                });

                const [header, ...body] = preservationMatches.split(props.headerSeparator);

                const joinedBody = body.join(props.headerSeparator).trim();
                let lines = joinedBody.split(props.separator);
                lines = lines.map((line, idx) => {
                    let result = line;
                    for(const [key, value] of Object.entries(variables)) {
                        result = result.replace(key, value);
                    }
                    return result;
                });
                return lines;
            });

            const hasItems = computed(_ => {
                return items.value.length > 0 && items.value[0] && items.value[0].trim() !== '';
            });

            return {
                hasItems,
                header,
                items,
            };
        }
    };
</script>