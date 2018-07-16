<template>
    <div class="dropdown-menu show">
        <a href="#" class="dropdown-item disabled" @click.stop.prevent @dblclick.stop.prevent>
            {{ data.data.name }}
        </a>
        <a href="#" class="dropdown-item" @click.stop.prevent="data.data.onContextMenuAdd(data.data)" @dblclick.stop.prevent>
            <i class="fas fa-fw fa-plus text-success"></i> Add new Sub-Entity
        </a>
        <a href="#" class="dropdown-item" @click.stop.prevent="data.data.onContextMenuDuplicate(data.data, data.path)" @dblclick.stop.prevent>
            <i class="fas fa-fw fa-copy text-info"></i> Duplicate <i>{{ data.data.name }}</i>
        </a>
        <a href="#" class="dropdown-item" @click.stop.prevent="data.data.onContextMenuDelete(data.data, data.path)" @dblclick.stop.prevent>
            <i class="fas fa-fw fa-trash text-danger"></i> Delete <i>{{ data.data.name }}</i>
        </a>
    </div>
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
