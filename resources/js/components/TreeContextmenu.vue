<template>
    <ul class="v-context">
        <li>
            <a href="#" class="disabled" @click.stop.prevent @dblclick.stop.prevent>
                {{ data.data.name }}
            </a>
        </li>
        <li>
            <a href="#" @click.stop.prevent="data.data.onContextMenuAdd(data.data, data.path)" @dblclick.stop.prevent>
                <i class="fas fa-fw fa-plus text-success"></i> Add new Sub-Entity
            </a>
        </li>
        <li>
            <a href="#" @click.stop.prevent="data.data.onContextMenuDuplicate(data.data, data.path)" @dblclick.stop.prevent>
                <i class="fas fa-fw fa-copy text-info"></i> Duplicate <i>{{ data.data.name }}</i>
            </a>
        </li>
        <li>
            <a href="#" @click.stop.prevent="data.data.onContextMenuDelete(data.data, data.path)" @dblclick.stop.prevent>
                <i class="fas fa-fw fa-trash text-danger"></i> Delete <i>{{ data.data.name }}</i>
            </a>
        </li>
    </ul>
</template>

<script>
export default {
    props: {
        data: {
            required: true,
            type: Object
        }
    }
}
</script>
