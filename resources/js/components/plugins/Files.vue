<template>
    <div class="d-flex flex-column h-100">
        <ul class="nav nav-pills nav-fill mb-2">
            <li class="nav-item">
                <a class="nav-link" href="#" :class="{active: isAction('linkedFiles'), disabled: !selectedEntity.id}" @click.prevent="setAction('linkedFiles')">
                    <i class="fas fa-fw fa-link"></i> {{ $t('plugins.files.header.linked') }} <span class="badge" :class="[isAction('linkedFiles') ? 'badge-light' : 'badge-primary']" v-show="selectedEntity.id">{{linkedFiles.files.length}}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" :class="{active: isAction('unlinkedFiles')}" @click.prevent="setAction('unlinkedFiles')">
                    <i class="fas fa-fw fa-unlink"></i> {{ $t('plugins.files.header.unlinked') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" :class="{active: isAction('allFiles')}" @click.prevent="setAction('allFiles')">
                    <i class="fas fa-fw fa-copy"></i> {{ $t('plugins.files.header.all') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" :class="{active: isAction('upload')}" @click.prevent="setAction('upload')">
                    <i class="fas fa-fw fa-file-upload"></i> {{ $t('plugins.files.header.upload') }}
                </a>
            </li>
        </ul>
        <div v-if="!this.isAction('upload')">
            <h5 class="clickable" @click="toggleFilters">{{ $t('plugins.files.header.rules.title') }}
                <small>
                    <span v-show="!showFilters">
                        <i class="fas fa-fw fa-angle-down"></i>
                    </span>
                    <span v-show="showFilters">
                        <i class="fas fa-fw fa-angle-up"></i>
                    </span>
                </small>
                <small class="badge" :class="[filterCounts[selectedTopAction] ? 'badge-primary' : 'badge-secondary']">
                    {{
                        $tc('plugins.files.header.rules.active', filterCounts[selectedTopAction], {
                            cnt: filterCounts[selectedTopAction]
                        })
                    }}
                </small>
            </h5>
            <div class="mb-2" v-show="showFilters">
                <form v-on:submit.prevent="applyFilters(selectedTopAction)">
                    <div class="form-group row">
                        <label class="col-form-label col-md-3" for="tags">
                            {{ $tc('global.tag', 2) }}:
                        </label>
                        <div class="col-md-9">
                            <multiselect
                                id="tags"
                                label="concept_url"
                                track-by="id"
                                v-model="filterTags[selectedTopAction]"
                                :allow-empty="true"
                                :close-on-select="false"
                                :custom-label="translateLabel"
                                :hide-selected="true"
                                :multiple="true"
                                :options="tags"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                            </multiselect>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3" for="names">
                            {{ $t('plugins.files.header.rules.types.name') }}:
                        </label>
                        <div class="col-md-9">
                            <input type="text" id="names" class="form-control" v-model="filterNames[selectedTopAction]" />
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3" for="dates">
                            {{ $t('plugins.files.header.rules.types.date') }}:
                        </label>
                        <div class="col-md-9">
                            <multiselect
                                id="dates"
                                label="value"
                                track-by="id"
                                v-model="filterDates[selectedTopAction]"
                                :allow-empty="true"
                                :close-on-select="false"
                                :hide-selected="true"
                                :multiple="true"
                                :options="filterDateList"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                            </multiselect>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3" for="filetypes">
                            {{ $t('plugins.files.header.rules.types.file') }}:
                        </label>
                        <div class="col-md-9">
                            <multiselect
                                id="filetypes"
                                label="label"
                                track-by="key"
                                v-model="filterTypes[selectedTopAction]"
                                :allow-empty="true"
                                :close-on-select="false"
                                :hide-selected="true"
                                :multiple="true"
                                :options="filterTypeList"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                            </multiselect>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3" for="cameras">
                            {{ $t('plugins.files.header.rules.types.camera') }}:
                        </label>
                        <div class="col-md-9">
                            <multiselect
                                id="cameras"
                                v-model="filterCameras[selectedTopAction]"
                                :allow-empty="true"
                                :close-on-select="false"
                                :hide-selected="true"
                                :multiple="true"
                                :options="filterCameraList"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                            </multiselect>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-success">
                        {{ $t('plugins.files.header.rules.apply') }}
                    </button>
                </form>
            </div>
        </div>
        <div class="col px-0" v-show="isAction('linkedFiles')">
            <form>
                <div class="custom-control custom-switch">
                    <input type="checkbox" id="sub-entities-check" class="custom-control-input" v-model="includeSubEntities" @change="applyFilters('linkedFiles')"/>
                    <label class="custom-control-label" for="sub-entities-check">
                        {{ $t('plugins.files.include-sub-files') }} <i class="fas fa-fw fa-sitemap"></i>
                    </label>
                </div>
            </form>
            <file-list
                :context-menu="contextMenu"
                :entity-id="selectedEntity.id"
                :files="linkedFiles.files"
                :file-state="linkedFiles.fileState"
                :is-fetching="linkedFiles.fetchingFiles"
                :on-click="showFileModal"
                :on-load-chunk="linkedFiles.loadChunk">
            </file-list>
        </div>
        <div class="col px-0" v-show="isAction('unlinkedFiles')">
            <file-list
                :context-menu="contextMenu"
                :files="unlinkedFiles.files"
                :file-state="unlinkedFiles.fileState"
                :is-fetching="unlinkedFiles.fetchingFiles"
                :on-click="showFileModal"
                :on-load-chunk="unlinkedFiles.loadChunk">
            </file-list>
        </div>
        <div class="col px-0" v-show="isAction('allFiles')">
            <file-list
                :context-menu="contextMenu"
                :files="allFiles.files"
                :file-state="allFiles.fileState"
                :is-fetching="allFiles.fetchingFiles"
                :on-click="showFileModal"
                :on-load-chunk="allFiles.loadChunk"
                :show-links="true">
            </file-list>
        </div>
        <div v-if="isAction('upload')" @paste="handleClipboardPaste">
            <file-upload class="w-100"
                ref="upload"
                v-model="uploadFiles"
                :custom-action="uploadFile"
                :directory="false"
                :drop="true"
                :multiple="true"
                @input-file="inputFile">
                    <div class="text-center rounded text-light bg-dark px-2 py-5">
                        <h3>{{ $t('plugins.files.upload.title') }}</h3>
                        <p>
                            {{ $t('plugins.files.upload.desc') }}
                        </p>
                    </div>
            </file-upload>
            <div v-show="!uploadFiles.length" class="mt-2">
                <form role="form">
                    <div class="form-group row">
                        <label class="col-form-label col-md-3" for="upload-property-copyright">
                            {{ $t('plugins.files.modal.detail.props.copyright') }}:
                        </label>
                        <div class="col-md-9">
                            <textarea class="form-control" id="upload-property-copyright" v-model="toUpload.copyright"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3" for="upload-property-description">
                            {{ $t('plugins.files.modal.detail.props.description') }}:
                        </label>
                        <div class="col-md-9">
                            <textarea class="form-control" id="upload-property-description" v-model="toUpload.description"></textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-md-3" for="upload-property-tags">
                            {{ $tc('global.tag', 2) }}:
                        </label>
                        <div class="col-md-9">
                            <multiselect
                                id="upload-property-tags"
                                label="concept_url"
                                track-by="id"
                                v-model="toUpload.tags"
                                :allow-empty="true"
                                :close-on-select="false"
                                :custom-label="translateLabel"
                                :hide-selected="true"
                                :multiple="true"
                                :options="tags"
                                :placeholder="$t('global.select.placehoder')"
                                :select-label="$t('global.select.select')"
                                :deselect-label="$t('global.select.deselect')">
                            </multiselect>
                        </div>
                    </div>
                </form>
            </div>
            <ul class="list-group list-group-flush" v-show="uploadFiles.length">
                <transition-group name="fade">
                    <li class="list-group-item" v-for="file in uploadFiles" :key="file.id" v-if="!file.success">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span>{{file.name}}</span>
                                <span class="text-muted font-weight-light" v-if="!file.error">
                                    {{file.size|bytes}} - {{file.speed|bytes}}/s
                                </span>
                            </div>
                            <button v-show="file.active" type="button" class="btn btn-outline-danger" @click.prevent="abortFileUpload(file)">
                                <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
                            </button>
                            <a href="#" v-show="file.error" @click="abortFileUpload(file)">
                                <i class="fas fa-fw fa-times"></i> {{ $t('global.clear') }}
                            </a>
                        </div>
                        <div class="progress" style="height: 2px;" v-if="!file.error">
                            <div class="progress-bar" role="progressbar" :style="{width: file.progress+'%'}" :aria-valuenow="file.progress" aria-valuemin="0" aria-valuemax="100">
                                <span class="sr-only">{{file.progress}}</span>
                            </div>
                        </div>
                        <p class="alert alert-danger" v-if="file.error">
                            {{ $t('plugins.files.upload.error') }}
                        </p>
                    </li>
                </transition-group>
            </ul>
        </div>

        <modal name="file-modal" id="file-modal" width="80%" height="80%" @closed="hideFileModal" classes="of-visible" @opened="onFileModalCreate" @before-close="onFileModalDestroy" tabindex="0">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" @mouseenter="onFileHeaderHover(true)" @mouseleave="onFileHeaderHover(false)">
                        <span v-if="!selectedFile.editing">
                            {{ $t('plugins.files.modal.detail.title', {name: selectedFile.name}) }}
                            <a href="#" v-if="fileHeaderHovered" class="text-dark" @click.prevent="enableFilenameEditing()">
                                <i class="fas fa-fw fa-edit"></i>
                            </a>
                        </span>
                        <form class="form-inline" v-else>
                            <input type="text" class="form-control mr-2" v-model="newFilename" />
                            <button type="submit" class="btn btn-outline-success mr-2" @click="updateFilename(newFilename)">
                                <i class="fas fa-fw fa-check"></i>
                            </button>
                            <button type="reset" class="btn btn-outline-danger" @click="cancelUpdateFilename()">
                                <i class="fas fa-fw fa-ban"></i>
                            </button>
                        </form>
                    </h5>
                    <button type="button" class="close" aria-label="Close" @click="closeFileModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body row flex-grow-1 text-center overflow-hidden">
                    <div class="col-md-6 d-flex flex-column h-100">
                        <component
                            class="flex-grow-1 overflow-hidden"
                            id="file-container"
                            :entity="localEntity"
                            :file="selectedFile"
                            :fullscreen-handler="fullscreenHandler"
                            :is="fileCategoryComponent"
                            :storage-config="storageConfig"
                            @handle-ocr="handleOCR"
                            @update-file-content="updateFileContent">
                        </component>
                        <div class="d-flex flex-row justify-content-between mt-2">
                            <button type="button" class="btn btn-outline-secondary" :disabled="isFirstFile" @click="gotoPreviousFile(selectedFile)">
                                <i class="fas fa-fw fa-angle-left"></i> {{ $t('plugins.files.modal.detail.previous') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" :disabled="isLastFile" @click="gotoNextFile(selectedFile)">
                                {{ $t('plugins.files.modal.detail.next') }} <i class="fas fa-fw fa-angle-right"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex flex-column h-100">
                        <ul class="nav nav-tabs nav-fill">
                            <li class="nav-item">
                                <a href="#" class="nav-link" :class="{active: modalTab == 'properties'}" @click.prevent="modalTab = 'properties'">
                                    <i class="fas fa-fw fa-sliders-h"></i> {{ $t('plugins.files.modal.detail.properties') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link" :class="{active: modalTab == 'links'}" @click.prevent="modalTab = 'links'">
                                    <i class="fas fa-fw fa-link"></i> {{ $t('plugins.files.modal.detail.links') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link" :class="{active: modalTab == 'accessrules'}" @click.prevent="modalTab = 'accessrules'">
                                    <i class="fas fa-fw fa-user-lock"></i> {{ $t('plugins.files.modal.detail.accessrules') }}
                                </a>
                            </li>
                            <li class="nav-item" v-if="selectedFile.exif && hasExif">
                                <a href="#" class="nav-link" :class="{active: modalTab == 'exif'}" @click.prevent="modalTab = 'exif'">
                                    <i class="fas fa-fw fa-camera"></i> {{ $t('plugins.files.modal.detail.exif') }}
                                </a>
                            </li>
                        </ul>
                        <div class="col mx-0 scroll-y-auto" v-show="modalTab == 'properties'">
                            <h5 class="mt-3">{{ $t('plugins.files.modal.detail.properties') }}</h5>
                            <table class="table table-striped table-hover table-sm mb-0">
                                <tbody>
                                    <tr v-for="p in fileProperties" class="d-flex justify-content-between">
                                        <td class="text-left font-weight-medium">
                                            {{ $t(`plugins.files.modal.detail.props.${p}`) }}
                                        </td>
                                        <td class="col text-left">
                                            <div class="text-muted text-line" v-if="editingProperty.key != p">
                                                {{selectedFile[p]}}
                                            </div>
                                            <form role="form" class="form-inline" v-else @submit.prevent="updateProperty()">
                                                <textarea class="form-control mr-1 col" :id="`edit-property-${p}`" v-model="selectedFile[p]"></textarea>
                                                <button type="submit" class="btn btn-sm btn-outline-success mr-1">
                                                    <i class="fas fa-fw fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" @click="cancelPropertyEditing()">
                                                    <i class="fas fa-fw fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <span id="dropdownMenuButton" class="clickable" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-fw fa-ellipsis-h"></i>
                                                </span>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                    <a class="dropdown-item" href="#" @click.prevent="enablePropertyEditing(p)">
                                                        <i class="fas fa-fw fa-edit text-info"></i> {{ $t('global.edit') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <h5 class="mt-3">{{ $t('plugins.files.modal.detail.metadata.title') }}</h5>
                            <table class="table table-striped table-hover table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-left font-weight-medium">
                                            {{ $t('plugins.files.modal.detail.metadata.created') }}
                                        </td>
                                        <td class="text-right text-muted">
                                            {{selectedFile.created_unix|date(undefined, true)}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left font-weight-medium">
                                            {{ $t('plugins.files.modal.detail.metadata.lastmodified') }}
                                        </td>
                                        <td class="text-right text-muted">
                                            {{selectedFile.modified_unix|date(undefined, true)}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left font-weight-medium">
                                            {{ $t('plugins.files.modal.detail.metadata.filesize') }}
                                        </td>
                                        <td class="text-right text-muted">
                                            {{selectedFile.size|bytes}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-left font-weight-medium">
                                            <file-upload
                                                class="mb-0"
                                                v-show="!replaceFiles.length"
                                                ref="replace"
                                                v-model="replaceFiles"
                                                :directory="false"
                                                :drop="true"
                                                :multiple="false"
                                                :custom-action="replaceFile"
                                                @input-file="onReplaceFileSet">
                                                    <span class="text-primary clickable hover-underline">
                                                        <i class="fas fa-fw fa-file-import text-muted"></i> {{ $t('plugins.files.modal.detail.replace.button') }}
                                                    </span>
                                            </file-upload>
                                            <div class="d-flex flex-column align-items-start font-weight-normal" v-if="replaceFiles.length">
                                                <span>
                                                    {{
                                                        $t('plugins.files.modal.detail.replace.confirm', {
                                                            size: $options.filters.bytes(selectedFile.size),
                                                            name: replaceFiles[0].name,
                                                            size2: $options.filters.bytes(replaceFiles[0].size)
                                                        })
                                                    }}
                                                </span>
                                                <span class="text-danger" v-if="replaceFiles[0].type != selectedFile.mime_type" v-html="$t('plugins.files.modal.detail.replace.different_mime', { mime_old: selectedFile.mime_type, mime_new: replaceFiles[0].type})">
                                                </span>
                                                <div class="d-flex mt-1">
                                                    <button type="button" class="btn btn-outline-success" @click="doReplaceFile">
                                                        <i class="fas fa-fw fa-check"></i>
                                                        {{ $t('global.replace') }}
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger ml-2" @click="cancelReplaceFile">
                                                        <i class="fas fa-fw fa-ban"></i>
                                                        {{ $t('global.cancel') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-right font-weight-medium">
                                            <a :href="selectedFile.url" :download="selectedFile.name" target="_blank">
                                                {{ $t('global.download') }}
                                                <i class="fas fa-fw fa-file-download text-muted"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <h5 class="mt-3">{{ $tc('global.tag', 2) }}</h5>
                            <form role="form" class="row" @submit.prevent="updateTags(selectedFile)">
                                <div class="col-md-9">
                                    <multiselect
                                        id="tags"
                                        label="concept_url"
                                        track-by="id"
                                        v-model="selectedFile.tags"
                                        :allow-empty="true"
                                        :close-on-select="false"
                                        :custom-label="translateLabel"
                                        :hide-selected="true"
                                        :multiple="true"
                                        :options="tags"
                                        :placeholder="$t('global.select.placehoder')"
                                        :select-label="$t('global.select.select')"
                                        :deselect-label="$t('global.select.deselect')">
                                    </multiselect>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-outline-success">
                                        <span class="fa-stack d-inline">
                                            <i class="fas fa-tags"></i>
                                            <i class="fas fa-check" data-fa-transform="shrink-4 left-13 down-4"></i>
                                        </span>
                                        <span class="stacked-icon-text">
                                            {{ $t('global.update') }}
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div v-show="modalTab == 'links'" class="h-100">
                            <div class="d-flex flex-column h-100 mx-4">
                                <div class="my-3 col p-0 scroll-y-auto">
                                    <ul class="list-group mx-0" v-if="selectedFile.entities && selectedFile.entities.length">
                                        <li class="list-group-item d-flex justify-content-between" v-for="link in selectedFile.entities">
                                            <a href="#" @click.prevent="routeToEntity(link)">
                                                <i class="fas fa-fw fa-monument"></i> {{ link.name }}
                                            </a>
                                            <a href="#" class="text-body" @click.prevent="requestUnlinkFile(selectedFile, link)">
                                                <i class="fas fa-fw fa-xs fa-times" style="vertical-align: 0;"></i>
                                            </a>
                                        </li>
                                    </ul>
                                    <p class="alert alert-info" v-else>
                                        {{ $t('plugins.files.modal.detail.no-links') }}
                                    </p>
                                </div>
                                <button type="button" class="btn btn-outline-success mb-3" @click.prevent="linkFile(selectedFile, selectedEntity)" v-if="!linkedToCurrentEntity && selectedEntity.id">
                                    <span class="fa-stack d-inline">
                                        <i class="fas fa-monument"></i>
                                        <i class="fas fa-plus" data-fa-transform="shrink-5 left-10 down-5"></i> {{ $t('global.link-to', {name: selectedEntity.name})}}
                                    </span>
                                </button>
                                <h5>
                                    {{ $t('plugins.files.modal.detail.link-further-entities') }}
                                </h5>
                                <entity-search
                                    class="mt-2"
                                    id="link-entity-search"
                                    name="link-entity-search"
                                    :filters="selectedFile.entities ? selectedFile.entities.map(c => c.id) : []"
                                    :on-select="selection => linkFile(selectedFile, selection)"
                                    :reset-input="true">
                                </entity-search>
                            </div>
                        </div>
                        <div v-show="modalTab == 'accessrules'" class="h-100">
                            <div class="d-flex flex-column h-100 mx-4">
                                <div class="my-3 col p-0 scroll-y-auto">
                                    <ul class="list-group mx-0 mt-2 flex-grow-1 scroll-y-auto" v-if="$hasAccessRules(selectedFile)">
                                        <li class="list-group-item d-flex justify-content-between" v-for="rule in selectedFile.access_rules">
                                            <a href="#" @click.prevent="">
                                                <i class="fas fa-fw fa-users-cog"></i> {{ $getGroup(rule.group_id).display_name }}
                                            </a>
                                            <template>
                                                <span v-if="rule.rules == 'rw'">
                                                    Write Access
                                                </span>
                                                <span v-else>
                                                    Read-Only
                                                </span>
                                            </template>
                                            <a href="#" class="text-body" @click.prevent="removeAccessRule(selectedFile, rule)" v-if="selectedFile.hasWriteAccess">
                                                <i class="fas fa-fw fa-xs fa-times" style="vertical-align: 0;"></i>
                                            </a>
                                        </li>
                                    </ul>
                                    <p class="alert alert-info" v-else>
                                        {{ $t('global.access_restricted_no_groups') }}
                                    </p>
                                </div>

                                <h5>Restrict access to certain groups</h5>

                                <form role="form" @submit.prevent="addAccessRule(selectedFile, selectedAccessRule)">
                                    <div class="form-group row">
                                        <div class="col-md-9">
                                            <multiselect
                                                id="access-rule-select"
                                                label="display_name"
                                                track-by="id"
                                                v-model="selectedAccessRule.group"
                                                :allow-empty="true"
                                                :close-on-select="false"
                                                :hide-selected="true"
                                                :multiple="false"
                                                :options="availableGroups"
                                                :placeholder="$t('global.select.placehoder')"
                                                :select-label="$t('global.select.select')"
                                                :deselect-label="$t('global.select.deselect')">
                                            </multiselect>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="checkbox" v-model="selectedAccessRule.writeAccess" />
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-success">
                                        <span class="fa-stack d-inline">
                                            <i class="fas fa-lock"></i>
                                            <i class="fas fa-plus" data-fa-transform="shrink-4 left-9 down-10"></i>
                                        </span>
                                        <span class="stacked-icon-text">
                                            Add group
                                        </span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div v-show="modalTab == 'exif'">
                            <p class="alert alert-info" v-if="!selectedFile.exif">
                            </p>
                            <table class="table table-striped" v-else>
                                <tbody>
                                    <tr>
                                        <td>
                                            <i class="fas fa-fw fa-camera"></i>
                                        </td>
                                        <td>
                                            {{ selectedFile.exif.Model }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="far fa-fw fa-circle"></i>
                                        </td>
                                        <td>
                                            {{ selectedFile.exif.Exif.FNumber }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="fas fa-fw fa-circle"></i>
                                        </td>
                                        <td>
                                            <span>
                                                {{ selectedFile.exif.Exif.FocalLength }} <span v-if="selectedFile.exif.MakMakerNotes">({{    selectedFile.exif.MakerNotes.LensModel }})</span>
                                            </span>
                                            <span v-if="selectedFile.exif.MakerNotes" style="display: block;font-size: 90%;color: gray;">
                                                {{     selectedFile.exif.MakerNotes.LensType }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="fas fa-fw fa-stopwatch"></i>
                                        </td>
                                        <td>
                                            {{ selectedFile.exif.Exif.ExposureTime }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="fas fa-fw fa-plus"></i>
                                        </td>
                                        <td>
                                            {{ selectedFile.exif.Exif.ISOSpeedRatings }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <!-- EXIF.Flash is hex, trailing 0 means no flash -->
                                            <i class="fas fa-fw fa-bolt"></i>
                                        </td>
                                        <td>
                                            {{ selectedFile.exif.Exif.Flash }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="fas fa-fw fa-clock"></i>
                                        </td>
                                        <td>
                                            {{ selectedFile.exif.Exif.DateTimeOriginal }}
                                        </td>
                                    </tr>
                                    <tr v-if="selectedFile.exif.Makernotes">
                                        <td>
                                            <i class="fas fa-fw fa-sun"></i>
                                        </td>
                                        <td>
                                            {{ selectedFile.exif.MakerNotes.WhiteBalanceSetting }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="fas fa-fw fa-copyright"></i>
                                        </td>
                                        <td>
                                            {{ selectedFile.exif.Copyright }}
                                        </td>
                                    </tr>
                                    <tr v-if="selectedFile.exif.Makernotes">
                                        <td>
                                            <i class="fas fa-fw fa-microchip"></i>
                                        </td>
                                        <td>
                                            {{ selectedFile.exif.MakerNotes.FirmwareVersion }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"     @click="closeFileModal">
                        {{ $t('global.close') }}
                    </button>
                </div>
            </div>
        </modal>

        <modal name="delete-file-modal" width="50%" height="50%">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('global.delete-name.title', {name: contextMenuFile.name}) }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideDeleteFileModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        {{ $t('global.delete-name.desc', {name: contextMenuFile.name}) }}
                    </p>
                    <p class="alert alert-danger">
                        {{
                            $tc('plugins.files.modal.delete.alert', linkCount, {
                                name: contextMenuFile.name,
                                cnt: linkCount
                            })
                        }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="deleteFile(contextMenuFile)">
                        <i class="fas fa-fw fa-check"></i> {{ $t('global.delete') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary"     @click="hideDeleteFileModal">
                        {{ $t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal>

        <modal name="unlink-file-modal" width="50%" height="50%">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $t('global.unlink-name.title', {name: contextMenuFile.name}) }}
                    </h5>
                    <button type="button" class="close" aria-label="Close" @click="hideUnlinkFileModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        {{
                            $t('global.unlink-name.desc', {
                                file: contextMenuFile.name,
                                ent: contextMenuEntity.name
                            })
                        }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="unlinkFile(contextMenuFile, contextMenuEntity)">
                        <i class="fas fa-fw fa-check"></i> {{ $t('global.unlink')}}
                    </button>
                    <button type="button" class="btn btn-outline-secondary"     @click="hideUnlinkFileModal">
                        {{ $t('global.cancel')}}
                    </button>
                </div>
            </div>
        </modal>

        <file-confirm-upload-modal
            :id="confirmModalId"
            @confirm="uploadFileFromClipboard"
        ></file-confirm-upload-modal>
    </div>
</template>

<script>
    import { EventBus } from '../../event-bus.js';
    import * as screenfull from 'screenfull';
    import { createWorker as createTesseractWorker } from 'tesseract.js';

    import FileList from './FileList.vue';

    import FileImage from './FileImage.vue';
    import FileAudio from './FileAudio.vue';
    import FileVideo from './FileVideo.vue';
    import FilePdf from './FilePdf.vue';
    import FileXml from './FileXml.vue';
    import File3d from './File3d.vue';
    import FileDicom from './FileDicom.vue';
    import FileArchive from './FileArchive.vue';
    import FileText from './FileText.vue';
    import FileUndefined from './FileUndefined.vue';

    import FileConfirmUploadModal from './FileConfirmUploadModal.vue';

    export default {
        tesseractWorker: createTesseractWorker(),
        components: {
            'file-list': FileList,
            'file-image': FileImage,
            'file-audio': FileAudio,
            'file-video': FileVideo,
            'file-pdf': FilePdf,
            'file-xml': FileXml,
            'file-3d': File3d,
            'file-dicom': FileDicom,
            'file-archive': FileArchive,
            'file-text': FileText,
            'file-undefined': FileUndefined,
            'file-confirm-upload-modal': FileConfirmUploadModal
        },
        props: {
            selectedEntity: {
                required: false,
                type: Object,
                default: {}
            },
            entityDataLoaded: {
                required: false,
                type: Boolean,
                default: false
            },
            params: {
                required: false,
                type: Object
            }
        },
        activated() {
            if(this.selectedEntity.id) {
                this.linkedFiles.apiUrl = '/file/linked/' + this.selectedEntity.id;
                this.setAction('linkedFiles', true);
            } else {
                this.linkedFilesChanged();
            }
            if(this.$route.query.f) {
                this.openFile(this.$route.query.f);
            }
        },
        mounted() {
            this.initFilters();
            this.initTags();
            EventBus.$on('files-uploaded', this.handleFileUpload);
        },
        methods: {
            initTags() {
                $httpQueue.add(() => $http.get('file/tags').then(response => {
                    this.tags = response.data;
                }));
            },
            updateTags(file) {
                const data = {
                    tags: file.tags.map(t => t.id)
                };
                $http.patch(`file/${file.id}/tag`, data).then(response => {
                    const title = this.$t('plugins.files.modal.detail.toasts.tags-updated.title');
                    const msg = this.$t('plugins.files.modal.detail.toasts.tags-updated.msg', {name: file.name});
                    this.$showToast(title, msg, 'success');
                });
            },
            initFilters() {
                const vm = this;
                $httpQueue.add(() => vm.$http.get('/file/filter/category').then(function(response) {
                    vm.filterTypeList = [];
                    vm.filterTypeList = response.data;
                }));
                $httpQueue.add(() => vm.$http.get('/file/filter/camera').then(function(response) {
                    vm.filterCameraList = [];
                    vm.filterCameraList = response.data;
                }));
                $httpQueue.add(() => vm.$http.get('/file/filter/date').then(function(response) {
                    vm.filterDateList = [];
                    let cnt = 0;
                    vm.filterDateList = response.data.map(d => {
                        cnt++;
                        d.id = cnt;
                        return d;
                    });
                }));
            },
            toggleFilters() {
                this.showFilters = !this.showFilters;
            },
            applyFilters(action) {
                const vm = this;
                const filters = vm.getFilters(action);
                let count = 0;
                count += vm.filterTypes[action].length;
                count += vm.filterCameras[action].length;
                count += vm.filterDates[action].length;
                count += vm.filterTags[action].length;
                count += vm.filterNames[action].length ? 1 : 0;
                vm.filterCounts[action] = count;
                vm.resetFiles(action);
                vm.getNextFiles(action, filters);

            },
            getFilters(action) {
                const vm = this;
                let filters = {
                    categories: vm.filterTypes[action].map(f => f.key),
                    cameras: vm.filterCameras[action],
                    dates: vm.filterDates[action],
                    tags: vm.filterTags[action],
                    name: vm.filterNames[action]
                    // strategy: vm.filterMatching[action]
                };
                if(action == 'linkedFiles') {
                    filters.sub_entities = vm.includeSubEntities;
                }
                return filters;
            },
            onFileModalCreate() {
                let modal = document.getElementById('file-modal');
                modal.addEventListener('keydown', this.fileShortcuts, false);
            },
            onFileModalDestroy() {
                let modal = document.getElementById('file-modal');
                modal.removeEventListener('keydown', this.fileShortcuts, false);
            },
            fileShortcuts(event) {
                if(event.keyCode != 37 && event.keyCode != 39) return;
                if(event.target.tagName.toUpperCase() == 'INPUT') return;
                if(event.target.tagName.toUpperCase() == 'TEXTAREA') return;
                if(event.keyCode == 37) {
                    this.gotoPreviousFile(this.selectedFile);
                } else {
                    this.gotoNextFile(this.selectedFile);
                }
            },
            gotoPreviousFile(file) {
                if(this.isAction('upload')) {
                    return;
                }
                if(this.isFirstFile) {
                    return;
                }
                const type = this.selectedTopAction;
                const index = this[type].files.findIndex(f => f.id == file.id);
                if(index > 0) {
                    const prevFile = this[type].files[index-1];
                    let query = {...this.$route.query};
                    query.f = prevFile.id;
                    this.$router.push({
                        params: this.$route.params,
                        query: query
                    });
                }
            },
            gotoNextFile(file) {
                if(this.isAction('upload')) {
                    return;
                }
                if(this.isLastFile) {
                    return;
                }
                const type = this.selectedTopAction;
                const index = this[type].files.findIndex(f => f.id == file.id);
                // Load next chunk if last file
                if(index == this[type].files.length-1) {
                    // Return if we are on last page
                    if(this[type].pagination.last_page == this[type].pagination.current_page) {
                        return;
                    }
                    this[type].loadChunk().then(response => {
                        const nextFile = this[type].files[index+1];
                        let query = {...this.$route.query};
                        query.f = nextFile.id;
                        this.$router.push({
                            params: this.$route.params,
                            query: query
                        });
                    });
                } else {
                    const nextFile = this[type].files[index+1];
                    let query = {...this.$route.query};
                    query.f = nextFile.id;
                    this.$router.push({
                        params: this.$route.params,
                        query: query
                    });
                }
            },
            handleOCR(event) {
                this.$options.tesseractWorker
                    .recognize(event.image)
                    .then(result => {
                        const f = new File([result.text], 'ocr-result.txt', {
                            type: 'text/plain'
                        });
                        this.$options.tesseractWorker.terminate();
                        this.confirmClipboardUpload(f);
                    });
            },
            updateFileContent(event) {
                const file = event.file;
                const content = event.content;
                let id = file.id;
                let blob;
                if(content instanceof Blob) {
                    blob = content;
                } else {
                    blob = new Blob([content], {type: file.mime_type});
                }
                let data = new FormData();
                data.append('file', blob, file.name);
                $http.post(`/file/${id}/patch`, data, {
                    headers: { 'content-type': false }
                }).then(response => {
                    if(event.onSuccess) {
                        event.onSuccess(response, file);
                    }
                });
            },
            onFileHeaderHover(active) {
                // If edit mode is enabled, do not disable it on hover
                if(this.selectedFile.editing) return;
                this.fileHeaderHovered = active;
            },
            enableFilenameEditing() {
                this.newFilename = this.selectedFile.name;
                this.selectedFile.editing = true;
            },
            updateFilename(newName) {
                const vm = this;
                const id = vm.selectedFile.id;
                const data = {
                    name: newName
                };
                vm.$http.patch(`/file/${id}/property`, data).then(function(response) {
                    const filedata = response.data;
                    let file = vm[vm.selectedTopAction].files.find(f => f.id == id);
                    const keys = ['name', 'url', 'thumb', 'thumb_url', 'modified', 'modified_unix', 'lasteditor'];
                    for(let i=0; i<keys.length; i++) {
                        const k = keys[i];
                        file[k] = filedata[k];
                        vm.selectedFile[k] = filedata[k];
                    }
                    vm.selectedFile.editing = false;
                });
            },
            cancelUpdateFilename() {
                this.newFilename = '';
                this.selectedFile.editing = false;
            },
            toggleFullscreen(element) {
                if(!screenfull.isEnabled) return new Promise(r => r(null));
                if(!element) return new Promise(r => r(null));
                return screenfull.toggle(element);
            },
            addToggleListener(callback) {
                if(screenfull.isEnabled) {
                    screenfull.on('change', callback);
                }
            },
            removeToggleListener(callback) {
                if(screenfull.isEnabled) {
                    screenfull.off('change', callback);
                }
            },
            linkedFilesChanged() {
                this.resetFiles('linkedFiles');
                if(!this.selectedEntity.id) return;
                this.linkedFiles.apiUrl = '/file/linked/' + this.selectedEntity.id;
                this.getNextFiles('linkedFiles', this.getFilters('linkedFiles'));
            },
            handleClipboardPaste(e) {
                const tag = e.target.tagName.toLowerCase();
                // do not handle if pasted in input field
                if(tag == 'input' || tag == 'textarea') return;
                let items = e.clipboardData.items;
                for(let i=0; i<items.length; i++) {
                    let c = items[i];
                    if(c.kind == 'string' && c.type == 'text/plain') {
                        c.getAsString(s => {
                            const f = new File([s], 'clipboard-paste.txt', {
                                type: 'text/plain'
                            });
                            this.confirmClipboardUpload(f);
                        });
                    } else if(c.kind == 'file') {
                        const f = c.getAsFile();
                        this.confirmClipboardUpload(f);
                    }
                }
            },
            confirmClipboardUpload(file) {
                this.$modal.show(this.confirmModalId, {
                    file: file
                });
            },
            uploadFileFromClipboard(event) {
                this.uploadFile({file: event.file}).then(response => {
                    this.onFilesUploaded(this.unlinkedFiles);
                    this.onFilesUploaded(this.allFiles);
                });
            },
            uploadFile(file, component) {
                return this.$uploadFile(file, this.toUpload);
            },
            handleFileUpload(event) {
                let affects = [];
                if(event.new) {
                    affects.push('unlinkedFiles');
                    affects.push('allFiles');
                } else {
                    if(event.linkedFiles) {
                        affects.push('linkedFiles');
                    }
                    if(event.unlinkedFiles) {
                        affects.push('unlinkedFiles');
                    }
                    if(event.allFiles) {
                        affects.push('allFiles');
                    }
                }
                affects.forEach(a => {
                    this.onFilesUploaded(this[a]);
                });
            },
            openFile(id) {
                $httpQueue.add(() => $http.get(`/file/${id}`).then(response => {
                    this.showFileModal(response.data);
                }));
            },
            setAction(id, dontLoad = false) {
                // disable linked tab if no entity is selected
                if(id == 'linkedFiles' && !this.localEntity.id) return;
                this.selectedTopAction = id;
                if(dontLoad) return;
                // If it is the first time the action is set, load images
                if(this[id] && !Object.keys(this[id].pagination).length) {
                    this.getNextFiles(id);
                }
            },
            isAction(id) {
                return this.selectedTopAction == id;
            },
            abortFileUpload(file) {
                this.$refs.upload.remove(file);
            },
            inputFile(newFile, oldFile) {
                // Wait for response
                if(newFile && oldFile && newFile.success && !oldFile.success) {
                    this.filesUploaded++;
                }
                if(newFile && oldFile && newFile.error && !oldFile.error) {
                    this.filesErrored++;
                }
                // Enable automatic upload
                if(Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
                    if(!this.$refs.upload.active) {
                        this.$refs.upload.active = true
                    }
                }
                if(this.filesUploaded + this.filesErrored == this.uploadFiles.length) {
                    if(this.filesUploaded > 0) {
                        this.onFilesUploaded(this.unlinkedFiles);
                        this.onFilesUploaded(this.allFiles);
                    }
                    this.filesUploaded = 0;
                    this.filesErrored = 0;
                    this.uploadFiles = [];
                }
            },
            replaceFile(file) {
                if(!this.replaceFileUrl) return;
                let formData = new FormData();
                formData.append('file', file.file);
                return $http.post(this.replaceFileUrl, formData).then(response => {
                    Vue.set(this, 'selectedFile', response.data);
                    this.showFileModal(this.selectedFile);
                    return response;
                });
            },
            doReplaceFile() {
                this.$refs.replace.active = true;
            },
            cancelReplaceFile() {
                this.$refs.replace.active = false;
                this.replaceFiles = [];
            },
            onReplaceFileSet(newFile, oldFile) {
                // Wait for response
                if(newFile && oldFile && newFile.success !== oldFile.success) {
                    this.cancelReplaceFile();
                }
            },
            resetFiles(fileType) {
                let arr = this[fileType];
                arr.files = [];
                arr.fileState = {};
                arr.fetchingFiles = false;
                arr.pagination = {};
            },
            getNextFiles(fileType, filters) {
                if(fileType == 'linkedFiles' && !this.selectedEntity.id) {
                    return;
                }
                let arr = this[fileType];
                arr.fetchingFiles = true;
                if(arr.pagination.current_page && arr.pagination.current_page == arr.pagination.last_page) {
                    return;
                }
                let url = arr.apiPrefix;
                // Check if we did not get any page yet
                if(!Object.keys(arr.pagination).length) {
                    url += arr.apiUrl;
                } else {
                    url += arr.pagination.next_page_url;
                }
                return this.getPage(url, arr, filters);
            },
            getPage(pageUrl, filesObj, filters) {
                let data = {};
                if(filters) {
                    data.filters = filters;
                }
                return $httpQueue.add(() => $http.post(pageUrl, data).then(response => {
                    let resp = response.data;
                    for(let i=0; i<resp.data.length; i++) {
                        filesObj.files.push(resp.data[i]);
                    }
                    delete resp.data;
                    Vue.set(filesObj, 'pagination', resp);
                    filesObj.fetchingFiles = false;
                    this.updateFileState(filesObj);
                }));
            },
            updateFileState(filesObj) {
                filesObj.fileState.from = filesObj.pagination.from ? 1 : 0,
                filesObj.fileState.to = filesObj.pagination.to,
                filesObj.fileState.total = filesObj.pagination.total,
                filesObj.fileState.toLoad = Math.min(
                    filesObj.pagination.per_page,
                    filesObj.pagination.total-filesObj.pagination.to
                )
            },
            onFilesUploaded(filesObj) {
                // if we never fetched files, wait for user to load
                if(!filesObj.pagination.current_page) {
                    return;
                }
                let url = filesObj.apiPrefix;
                url += filesObj.apiUrl + '?' + filesObj.apiPageParam + '=' + filesObj.pagination.current_page;
                if(filesObj.pagination.to <= filesObj.pagination.total) {
                    // remove current page files and reload them
                    let index = filesObj.pagination.from - 1;
                    let howmany = (filesObj.pagination.to - filesObj.pagination.from) + 1;
                    filesObj.files.splice(index, howmany);
                }
                this.updateFileState(filesObj);
                this.getPage(url, filesObj);
            },
            onFileDeleted(file, filesObj) {
                // if we never fetched files, wait for user to load
                if(!filesObj.pagination.current_page) {
                    return;
                }
                let index = filesObj.files.findIndex(f => f.id == file.id);
                // if the file was not in this tab, return
                if(index == -1) return;
                filesObj.pagination.total--;
                filesObj.pagination.to--;
                filesObj.files.splice(index, 1);
                // check if we deleted with only 1 element on last page
                if(filesObj.pagination.from > filesObj.pagination.total) {
                    // if so, set next page url to this page, because we decreased our current page
                    filesObj.pagination.next_page_url = filesObj.apiUrl + '?' + filesObj.apiPageParam + '=' + filesObj.pagination.current_page;
                }
                this.updateFileState(filesObj);
            },
            onFileLinked(file, filesObj) {
                // if we never fetched files, wait for user to load
                if(!filesObj.pagination.current_page) {
                    return;
                }
                filesObj.files.push(file);
                filesObj.pagination.total++;
                filesObj.pagination.to++;
                let count = (filesObj.pagination.to - filesObj.pagination.from) + 1;
                // check if the push created a new (local) page
                if(count > filesObj.pagination.per_page) {
                    filesObj.pagination.current_page++;
                    // check if the push created a new (db) page
                    if(filesObj.pagination.total % filesObj.pagination.per_page == 1) {
                        filesObj.pagination.last_page++;
                        filesObj.pagination.last_page_url = filesObj.apiUrl + '?' + filesObj.apiPageParam + '=' + filesObj.pagination.last_page;
                    }
                    filesObj.pagination.next_page_url = filesObj.apiUrl + '?' + filesObj.apiPageParam + '=' + filesObj.pagination.current_page;
                }
                this.updateFileState(filesObj);
            },
            onFileUnlinked(file, filesObj, linkCount) {
                // if we never fetched files, wait for user to load
                if(!filesObj.pagination.current_page) {
                    return;
                }
                // if there are still links, do not add to unlinked files
                if(typeof linkCount != 'undefined' && linkCount > 0) {
                    return;
                }
                let index = filesObj.files.findIndex(f => f.id == file.id);
                // if the file was not in this tab, return
                if(index == -1) return;
                filesObj.pagination.total--;
                filesObj.pagination.to--;
                filesObj.files.splice(index, 1);
                // check if we deleted with only 1 element on last page
                if(filesObj.pagination.from > filesObj.pagination.total) {
                    // if so, set next page url to this page, because we decreased our current page
                    filesObj.pagination.next_page_url = filesObj.apiUrl + '?' + filesObj.apiPageParam + '=' + filesObj.pagination.current_page;
                }
            },
            addAccessRule(file, rule) {
                const data = {
                    file_id: file.id,
                    has_write: rule.writeAccess
                };
                $httpQueue.add(() => $http.patch(`restrict_to/${rule.group.id}`, data).then(response => {
                    this.selectedFile.access_rules.push(response.data);
                    this.$showToast(
                        this.$t('main.entity.toasts.restriction_removed.title'),
                        this.$t('main.entity.toasts.restriction_removed.msg', {
                            name: file.name,
                            group: this.$getGroup(rule.group_id).display_name
                        }),
                        'success'
                    );
                }));
            },
            removeAccessRule(file, rule) {
                $httpQueue.add(() => $http.delete(`/restrict_to/${rule.group_id}/file/${file.id}`).then(response => {
                    const idx = file.access_rules.findIndex(ar => ar.group_id == rule.group_id);
                    if(idx) {
                        file.access_rules.splice(idx, 1);
                        this.$showToast(
                            this.$t('main.entity.toasts.restriction_removed.title'),
                            this.$t('main.entity.toasts.restriction_removed.msg', {
                                name: file.name,
                                group: this.$getGroup(rule.group_id).display_name
                            }),
                            'success'
                        );
                    }
                }));
            },
            requestDeleteFile(file) {
                this.contextMenuFile = Object.assign({}, file);
                this.$modal.show('delete-file-modal');
            },
            deleteFile(file) {
                let id = file.id;
                $http.delete('/file/'+id).then(response => {
                    this.onFileDeleted(file, this.linkedFiles);
                    this.onFileDeleted(file, this.unlinkedFiles);
                    this.onFileDeleted(file, this.allFiles);
                    this.hideDeleteFileModal();

                    this.$showToast(
                        this.$t('plugins.files.toasts.deleted.title'),
                        this.$t('plugins.files.toasts.deleted.msg', {
                            name: file.name
                        }),
                        'success'
                    );
                });
            },
            hideDeleteFileModal() {
                this.$modal.hide('delete-file-modal');
                this.contextMenuFile = {};
            },
            requestUnlinkFile(file, entity) {
                const vm = this;
                const id = file.id;
                vm.contextMenuFile = Object.assign({}, file);
                vm.contextMenuEntity = Object.assign({}, entity);
                $httpQueue.add(() => vm.$http.get(`/file/${id}/link_count`).then(function(response) {
                    vm.linkCount = response.data;
                    vm.$modal.show('unlink-file-modal');
                }));
            },
            unlinkFile(file, entity) {
                const id = file.id;
                const cid = entity.id;
                $http.delete(`/file/${id}/link/${cid}`).then(response => {
                    this.linkCount--;
                    this.onFileDeleted(file, this.linkedFiles);
                    this.onFileUnlinked(file, this.unlinkedFiles, this.linkCount);
                    const index = file.entities.findIndex(c => c.id == cid);
                    if(index > -1) {
                        file.entities.splice(index, 1);
                    }
                    this.hideUnlinkFileModal();
                    this.$showToast(
                        this.$t('plugins.files.toasts.unlinked.title'),
                        this.$t('plugins.files.toasts.unlinked.msg', {
                            name: file.name,
                            eName: entity.name
                        }),
                        'success'
                    );
                });
            },
            hideUnlinkFileModal() {
                this.$modal.hide('unlink-file-modal');
                this.contextMenuFile = {};
                this.contextMenuEntity = {};
                this.linkCount = 0;
            },
            linkFile(file, entity) {
                if(!file || !entity) return;
                const id = file.id;
                const data = {
                    'entity_id': entity.id
                };
                $http.put(`/file/${id}/link`, data).then(response => {
                    this.onFileLinked(file, this.linkedFiles);
                    this.onFileDeleted(file, this.unlinkedFiles);
                    if(!file.entities) {
                        file.entities = [];
                    }
                    file.entities.push(entity);
                    this.$showToast(
                        this.$t('plugins.files.toasts.linked.title'),
                        this.$t('plugins.files.toasts.linked.msg', {
                            name: file.name,
                            eName: entity.name
                        }),
                        'success'
                    );
                });
            },
            enablePropertyEditing(property) {
                this.editingProperty.key = property;
                this.editingProperty.value = this.selectedFile[property];
            },
            updateProperty() {
                const p = this.editingProperty;
                const id = this.selectedFile.id;
                let data = {};
                data[p.key] = this.selectedFile[p.key];
                $http.patch(`/file/${id}/property`, data).then(response => {
                    this.resetEditingProperty();
                });
            },
            cancelPropertyEditing() {
                const p = this.editingProperty;
                this.selectedFile[p.key] = p.value;
                this.resetEditingProperty();
            },
            resetEditingProperty() {
                this.editingProperty.key = '';
                this.editingProperty.value = '';
            },
            showFileModal(file) {
                this.selectedFile.editing = false;
                this.selectedFile = { ...this.selectedFile, ...file };
                switch(file.category) {
                    case 'image':
                        this.fileCategoryComponent = 'file-image';
                        break;
                    case 'audio':
                        this.fileCategoryComponent = 'file-audio';
                        break;
                    case 'video':
                        this.fileCategoryComponent = 'file-video';
                        break;
                    case 'pdf':
                        this.fileCategoryComponent = 'file-pdf';
                        break;
                    case 'xml':
                    case 'html':
                        this.fileCategoryComponent = 'file-xml';
                        break;
                    case '3d':
                        this.fileCategoryComponent = 'file-3d';
                        break;
                    case 'dicom':
                        this.fileCategoryComponent = 'file-dicom';
                        break;
                    case 'archive':
                        this.fileCategoryComponent = 'file-archive';
                        break;
                    case 'text':
                        this.fileCategoryComponent = 'file-text';
                        break;
                    default:
                        this.fileCategoryComponent = 'file-undefined';
                        break;
                }
                this.$router.push({
                    append: true,
                    query: { ...this.$route.query, f: file.id }
                });
                this.$modal.show('file-modal');
            },
            routeToEntity(entity) {
                this.routeOnFileClose = {
                    to: 'entitydetail',
                    params: {
                        id: entity.id
                    }
                };
                this.closeFileModal();
            },
            closeFileModal() {
                this.$modal.hide('file-modal');
            },
            hideFileModal() {
                let query = { ...this.$route.query };
                delete query.f;
                this.selectedFile = {};
                if(!this.routeOnFileClose.to) {
                    this.$router.push({
                        append: true,
                        query: query
                    });
                } else {
                    this.$router.push({
                        to: this.routeOnFileClose.to,
                        params: this.routeOnFileClose.params,
                        query: {...query, ...this.routeOnFileClose.query }
                    });
                }
            },
            translateLabel(element, prop) {
                return this.$translateLabel(element, prop);
            }
        },
        data() {
            return {
                storageConfig: {
                    baseURL: ''
                },
                confirmModalId: 'file-confirm-upload-modal',
                showFilters: false,
                filterRules: {
                    type: {},
                    camera: '',
                    date: ''
                },
                selectedTopAction: 'unlinkedFiles',
                uploadFiles: [],
                toUpload: {
                    tags: [],
                    copyright: '',
                    description: ''
                },
                filesUploaded: 0,
                filesErrored: 0,
                linkedFiles: {
                    files: [],
                    fileState: {},
                    fetchingFiles: false,
                    pagination: {},
                    apiPrefix: '',
                    apiUrl: '/file/linked',
                    apiPageParam: 'page',
                    loadChunk: () => {
                        if(!this.isAction('linkedFiles')) return;
                        return this.getNextFiles('linkedFiles', this.getFilters('linkedFiles'));
                    }
                },
                unlinkedFiles: {
                    files: [],
                    fileState: {},
                    fetchingFiles: false,
                    pagination: {},
                    apiPrefix: '',
                    apiUrl: '/file/unlinked',
                    apiPageParam: 'page',
                    loadChunk: () => {
                        if(!this.isAction('unlinkedFiles')) return;
                        return this.getNextFiles('unlinkedFiles', this.getFilters('unlinkedFiles'));
                    }
                },
                allFiles: {
                    files: [],
                    fileState: {},
                    fetchingFiles: false,
                    pagination: {},
                    apiPrefix: '',
                    apiUrl: '/file',
                    apiPageParam: 'page',
                    loadChunk: () => {
                        if(!this.isAction('allFiles')) return;
                        return this.getNextFiles('allFiles', this.getFilters('allFiles'));
                    }
                },
                editingProperty: {
                    key: '',
                    value: ''
                },
                fullscreenHandler: {
                    toggle: this.toggleFullscreen,
                    add: this.addToggleListener,
                    remove: this.removeToggleListener
                },
                fileHeaderHovered: false,
                newFilename: '',
                selectedFile: {},
                replaceFiles: [],
                contextMenuFile: {},
                contextMenuEntity: {},
                linkCount: 0,
                selectedAccessRule: {
                    group: {},
                    writeAccess: false
                },
                fileCategoryComponent: '',
                modalTab: 'properties',
                fileProperties: [
                    'copyright',
                    'description'
                ],
                includeSubEntities: false,
                tags: [],
                filterTypeList: [],
                filterCameraList: [],
                filterDateList: [],
                filterTypes: {
                    linkedFiles: [],
                    unlinkedFiles: [],
                    allFiles: []
                },
                filterCameras: {
                    linkedFiles: [],
                    unlinkedFiles: [],
                    allFiles: []
                },
                filterDates: {
                    linkedFiles: [],
                    unlinkedFiles: [],
                    allFiles: []
                },
                filterNames: {
                    linkedFiles: '',
                    unlinkedFiles: '',
                    allFiles: ''
                },
                filterTags: {
                    linkedFiles: [],
                    unlinkedFiles: [],
                    allFiles: []
                },
                filterCounts: {
                    linkedFiles: 0,
                    unlinkedFiles: 0,
                    allFiles: 0
                },
                routeOnFileClose: {}
            }
        },
        computed: {
            localEntity: function() {
                return Object.assign({}, this.selectedEntity);
            },
            replaceFileUrl: function() {
                if(!this.selectedFile.id) return '';
                return `file/${this.selectedFile.id}/patch`;
            },
            hasExif: function() {
                if(!this.selectedFile || !Object.keys(this.selectedFile).length) {
                    return false;
                }
                return this.selectedFile.category == 'image';
            },
            linkedToCurrentEntity: function() {
                if(!this.selectedEntity.id || !this.selectedFile.id || !this.selectedFile.entities) {
                    return false;
                }

                return !!this.selectedFile.entities.find(c => c.id == this.selectedEntity.id);
            },
            availableGroups() {
                return this.$getAvailableGroups(this.selectedFile);
            },
            isFirstFile: function() {
                if(!this.selectedFile && !this.selectedFile.id) {
                    return false;
                }
                const type = this.selectedTopAction;
                if(this.isAction('upload')) {
                    return false;
                }
                if(!this[type].files.length) {
                    return true;
                }
                return this[type].files.findIndex(f => f.id == this.selectedFile.id) <= 0;
            },
            isLastFile: function() {
                if(!this.selectedFile && !this.selectedFile.id) {
                    return false;
                }
                const type = this.selectedTopAction;
                if(this.isAction('upload')) {
                    return false;
                }
                if(!this[type].files.length) {
                    return true;
                }
                return this[type].files.findIndex(f => f.id == this.selectedFile.id) == this[type].fileState.total-1;
            },
            contextMenu: function() {
                const vm = this;
                let menu = [];
                if(vm.selectedEntity.id) {
                    menu.push({
                        getLabel: file => {
                            const isLinkedTo = file.entities.some(c => vm.selectedEntity.id == c.id);
                            if(isLinkedTo) {
                                return vm.$t('global.unlink-from', {
                                    name: vm.selectedEntity.name
                                });
                            } else {
                                return vm.$t('global.link-to', {
                                    name: vm.selectedEntity.name
                                });
                            }
                        },
                        getIconClasses: file => {
                            const isLinkedTo = file.entities.some(c => vm.selectedEntity.id == c.id);
                            if(isLinkedTo) {
                                return 'fas fa-fw fa-unlink text-warning';
                            } else {
                                return 'fas fa-fw fa-link text-success';
                            }
                        },
                        getIconContent: file => '',
                        callback: file => {
                            const isLinkedTo = file.entities.some(c => vm.selectedEntity.id == c.id);
                            if(isLinkedTo) {
                                vm.requestUnlinkFile(file, vm.selectedEntity);
                            } else {
                                vm.linkFile(file, vm.selectedEntity);
                            }
                        }
                    });
                }
                menu.push({
                    getLabel: file => vm.$t('global.delete'),
                    getIconClasses: file => 'fas fa-fw fa-trash text-danger',
                    getIconContent: file => '',
                    callback: file => {
                        vm.requestDeleteFile(file);
                    }
                });
                return menu;
            }
        },
        watch: {
            selectedEntity: function(newData, oldData) {
                if(newData && (!oldData || (newData.id != oldData.id))) {
                    this.linkedFilesChanged();
                }
            },
            params: function(newParams, oldParams) {
                // check if (different) file route is activated
                if(newParams.f && oldParams.f != newParams.f) {
                    this.openFile(newParams.f);
                }
            }
        }
    }
</script>
