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
        setup(props){
            const header = computed(_ => {
                return props.value.split(props.headerSeparator)[0].trim();
            });

            const items = computed(_ => {

                const headerParts = props.value.split(props.headerSeparator);
                headerParts.shift();

                const parts = headerParts.join(props.separator);
                return parts.split(props.separator);
            });

            const hasItems = computed(_ => {
                return items.value.length > 0 && items.value[0].trim() !== '';
            });


            return {
                hasItems,
                header,
                items
            };
        }
    };
</script>