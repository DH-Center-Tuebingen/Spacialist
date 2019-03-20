<template>
    <div class="h-100">
        <transition name="fade">
            <div class="progress mb-2" style="height: 1px;" v-if="progress<100">
                <div class="progress-bar" role="progressbar" :style="{width: progress+'%'}" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
                    <span class="sr-only">{{progress}}</span>
                </div>
            </div>
        </transition>
        <div :id="containerId" class="w-100 h-100">
            <div id="file-controls" class="position-relative">
                <button type="button" class="btn btn-outline-info position-absolute m-2" v-if="entity.id" @click="loadAllSubModels" style="left: 0;">
                    {{ $t('plugins.files.modal.detail.threed.load-sub-models') }}
                </button>
                <button type="button" class="btn btn-outline-info position-absolute m-2" v-if="fullscreenHandler" @click="toggleFullscreen" style="right: 0;">
                    <i class="fas fa-fw fa-expand"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    import * as dat from 'dat.gui';

    import {
        AmbientLight,
        AnimationMixer,
        BoxBufferGeometry,
        ColladaLoader,
        Color,
        Clock,
        CSS2DObject,
        CSS2DRenderer,
        DDSLoader,
        DirectionalLight,
        DoubleSide,
        FBXLoader,
        GLTFLoader,
        Geometry,
        GridHelper,
        Group,
        HemisphereLight,
        IcosahedronBufferGeometry,
        Line,
        Loader,
        Matrix4,
        Mesh,
        MeshPhongMaterial,
        MTLLoader,
        OBJLoader,
        Octree,
        OrbitControls,
        PCFSoftShadowMap,
        PDBLoader,
        PerspectiveCamera,
        Raycaster,
        Scene,
        TextureLoader,
        Vector3,
        ViveController,
        WebGLRenderer,
    } from 'three-full';
    import {SpotLight} from 'three-full/sources/lights/SpotLight.js';
    import {WebVR} from 'three-full/sources/vr/WebVR.js';

    export default {
        props: {
            file: {
                required: true,
                type: Object
            },
            entity: {
                required: false,
                type: Object
            },
            fullscreenHandler: {
                required: false,
                type: Function
            }
        },
        mounted() {
            this.startup();
        },
        destroyed() {
            window.removeEventListener('resize', this.onWindowResize, false);
            this.renderer.domElement.removeEventListener('mousedown', this.onMouseDown, false);
            // VR Events
    		this.grabController.removeEventListener('triggerdown', this.onGrabDown);
    		this.grabController.removeEventListener('triggerup', this.onGrabUp);
            this.grabController.removeEventListener('thumbpadup', this.dimWorldLight);
            // this.grabController.removeEventListener('axischanged', this.recognizeTouch);
    		this.flashlightController.removeEventListener('triggerdown', this.onLightOn);
    		this.flashlightController.removeEventListener('triggerup', this.onLightOff);
    		this.flashlightController.removeEventListener('thumbpadup', this.dimFlashLight);

            window.removeEventListener('vrdisplaypresentchange', this.vrDisplayStateChanged, false);

            for(let i=this.scene.children.length-1; i>=0; i--) {
                let obj = this.scene.children[i];
                if(obj.geometry) obj.geometry.dispose();
                if(obj.material) obj.material.dispose();
                this.scene.remove(obj);
            }
            this.renderer.forceContextLoss();
            this.renderer.dispose();
            this.renderer = null;
            this.scene = null;
            if(this.labelRenderer) {
                this.labelRenderer.forceContextLoss();
                this.labelRenderer.dispose();
                this.labelRenderer = null;
            }
        },
        methods: {
            setScale() {
                const s = this.guiConfig.scale;
                this.group.scale.set(s, s, s);
            },
            getFileType: function(file) {
                if(file.mime_type == 'model/vnd.collada+xml') {
                    return 'dae'
                }
                if(file.mime_type == 'model/gltf-binary' || file.mime_type == 'model/gltf+json') {
                    return 'gltf'
                }
                if(file.mime_type == 'chemical/x-pdb') {
                    return 'pdb'
                }
                let extension = this.getFileExtension(file.name);
                switch(extension) {
                    case 'dae':
                        return 'dae';
                    case 'gltf':
                        return 'gltf';
                    case 'pdb':
                        return 'pdb';
                    case 'obj':
                        return 'obj';
                    case 'fbx':
                        return 'fbx';
                    default:
                        return undefined;
                }
            },
            getFileExtension: function(filename) {
                return filename.substr(filename.lastIndexOf('.')+1);
            },
            startup: function() {
                this.init();
                this.loadModel(this.file);
                this.animate();
            },
            init: function() {
                this.gui = new dat.GUI({
                    autoPlace: false
                });
                this.guiConfig = {
                    scale: this.scale
                };
                this.gui.add(this.guiConfig, 'scale', 0.01, 100, 0.01).onChange(this.setScale);

                this.container = document.getElementById(this.containerId);
                document.getElementById('file-controls').appendChild(this.gui.domElement);
                this.containerWidth = this.container.clientWidth;
                this.containerHeight = this.container.clientHeight;

                this.renderer = new WebGLRenderer({
                    antialias: true
                });
                this.renderer.setPixelRatio(window.devicePixelRatio);
        		this.renderer.setSize(this.containerWidth, this.containerHeight);
        		this.renderer.shadowMap.enabled = true;
        		this.renderer.shadowMap.type = PCFSoftShadowMap;
        		this.renderer.gammaInput = true;
        		this.renderer.gammaOutput = true;
                this.camera = new PerspectiveCamera(45, this.containerWidth/this.containerHeight, 0.1, 2000);
                this.camera.position.set(7, 5, 7);

                this.scene = new Scene();
                this.group = new Group();

                this.initEventListeners();
                this.initLights();
                this.initControls();
                if(navigator.getVRDisplays) {
                    this.container.appendChild(WebVR.createButton(this.renderer));
                    this.initViveControls();
                }

                this.container.appendChild(this.renderer.domElement);
                this.scene.add(this.camera);
                this.scene.add(this.group);
        		this.scene.add(new GridHelper(10, 20));
            },
            loadAllSubModels: function() {
                const vm = this;
                if(!vm.entity && !vm.entity.id) return;
                const id = vm.entity.id;
                $httpQueue.add(() => vm.$http.get(`/file/${id}/sub_files?c=3d`).then(function(response) {
                    const models = response.data;
                    models.forEach(m => vm.loadModel(m));
                }));
            },
            loadModel: function(file) {
                let fileType = this.getFileType(file);
                if(!fileType) return;
                switch(fileType) {
                    case 'dae':
                        this.loadCollada(file);
                        break;
                    case 'gltf':
                        this.loadGltf(file);
                        break;
                    case 'obj':
                        this.loadObj(file);
                        break;
                    case 'fbx':
                        this.loadFbx(file);
                        break;
                    case 'pdb':
                        this.loadProteinDb(file);
                        break;
                }
            },
            toggleFullscreen() {
                if(!this.fullscreenHandler) return;
                const element = document.getElementById(this.containerId);
                this.fullscreenHandler(element)
            },
            animate: function() {
                this.animationId = requestAnimationFrame(this.animate);
                if(this.animationMixer.update) {
                     this.animationMixer.update(this.animationClock.getDelta());
                }
                this.render();
            },
            render: function() {
                if(!this.renderer) return;
                if(this.renderer.vr && this.renderer.vr.enabled) {
                    this.grabController.update();
                    this.flashlightController.update();
                }
                this.renderer.render(this.scene, this.camera);
                if(this.labelRenderer) {
                    this.labelRenderer.render(this.scene, this.camera);
                }
                this.octree.update();
            },
            initEventListeners: function() {
                window.addEventListener('resize', this.onWindowResize, false);
                this.renderer.domElement.addEventListener('mousedown', this.onMouseDown, false);
            },
            initLights: function() {
                this.hemisphereLight = new HemisphereLight(0x808080, 0x606060);
                this.directionalLight = new DirectionalLight(0xffffff);
                this.directionalLight.position.set(this.camera.position);
                this.directionalLight.castShadow = true;
        		this.directionalLight.shadow.camera.top = 2;
        		this.directionalLight.shadow.camera.bottom = -2;
        		this.directionalLight.shadow.camera.right = 2;
        		this.directionalLight.shadow.camera.left = -2;
        		this.directionalLight.shadow.mapSize.set(4096, 4096);
                this.scene.add(this.hemisphereLight);
        		this.scene.add(this.directionalLight);
            },
            initControls: function() {
                this.controls = new OrbitControls(this.camera, this.renderer.domElement);
            },
            initViveEventListeners: function() {
                // Vive Events
        		this.grabController.addEventListener('triggerdown', this.onGrabDown);
        		this.grabController.addEventListener('triggerup', this.onGrabUp);
                this.grabController.addEventListener('thumbpadup', this.dimWorldLight);
                // this.grabController.addEventListener('axischanged', this.recognizeTouch);
        		this.flashlightController.addEventListener('triggerdown', this.onLightOn);
        		this.flashlightController.addEventListener('triggerup', this.onLightOff);
        		this.flashlightController.addEventListener('thumbpadup', this.dimFlashLight);

                window.addEventListener('vrdisplaypresentchange', this.vrDisplayStateChanged, false);
            },
            initViveControls: function() {
                const vm = this;
                // TODO only init if VR enabled?
                this.initViveEventListeners();
                let ctrlLoader = new OBJLoader();
                ctrlLoader.setPath('./img/');
                ctrlLoader.load('vr_controller_vive_1_5.obj', function(object) {
                    let txtLoader = new TextureLoader();
                    txtLoader.setPath('./img/vive-controller/');
                    let controllerModel = object.children[0];
                    controllerModel.material.map = txtLoader.load('onepointfive_texture.png');
                    vm.grabController.add(object.clone());
                    vm.flashlightController.add(object.clone());
                });
                // Add Controller Rays
                let geometry = new Geometry();
                geometry.vertices.push(new Vector3(0, 0, 0));
                geometry.vertices.push(new Vector3(0, 0, -1));
                let line = new Line(geometry);
                line.name = 'line';
                line.scale.z = 5;
                this.grabController.add(line.clone());

                this.flashlight = new SpotLight(0xffffff, 1, 5, 0.15, 1, 2);
                this.flashlight.castShadow = true;
        		this.flashlight.shadow.mapSize.width = 1024;
        		this.flashlight.shadow.mapSize.height = 1024;
        		this.flashlight.shadow.camera.near = 0.05;
        		this.flashlight.shadow.camera.far = 1000;
        		// switch light off, is triggered using button on the ctrl
        		this.flashlight.intensity = 0;
        		// position the light source right behind the ctrl
        		this.flashlight.position.set(
                    this.flashlightController.position.x,
                    this.flashlightController.position.y,
                    this.flashlightController.position.z + 0.05);
        		this.flashlightController.add(this.flashlight);

                // point the light source to the controller's "view" direction
                let c2line = line.clone();
                this.flashlightController.add(c2line);
                this.flashlight.target = c2line;
                this.flashlightController.add(this.flashlight.target);
                this.scene.add(this.grabController);
                this.scene.add(this.flashlightController);
            },
            // Loaders
            loadCollada: function(file) {
                const loader = new ColladaLoader();
                loader.load(file.url, collada => {
                    let object = collada.scene;
                    let material;
                    let children;
                    let parent = object;
                    do {
                        children = parent.children;
                        if(!children || !children[0]) break;
                        material = children[0].material;
                        parent = children[0];
                    } while(!material);
                    if(material) {
                        material.side = DoubleSide;
                    }
                    object.castShadow = true;
                    object.receiveShadow = true;
					for(let i=0; i<object.children.length; i++) {
						this.octree.add(object.children[i], {
							useFaces: false
						});
					}
                    this.group.add(object);
                    this.onWindowResize();
                },
                event => { // onProgress
                    this.updateProgress(event);
                },
                event => { // onError
                });
            },
            loadGltf: function(file) {
                const loader = new GLTFLoader();
                loader.load(file.url, data => {
                    let gltf = data;
                    let object = gltf.scene;

                    object.traverse(node => {
                        if(node.isMesh) {
                            node.castShadow = true;
                            node.receiveShadow = true;
                        }
						this.octree.add(node, {
							useFaces: false
						});
                    });

                    let animations = gltf.animations;
                    if(animations && animations.length > 0) {
                        this.animationMixer = new AnimationMixer(object);
                        // Play first animation if available
                        if(animations && animations.length) {
                            animationMixer.clipAction(animations[0]).play();
                        }
                    }
                    this.group.add(object);
                    this.onWindowResize();
                }, event => {
                    this.updateProgress(event);
                }, error => {
                });
            },
            loadObj: function(file) {
                const url = file.url;
                const sep = url.lastIndexOf('/')+1;
                const path = url.substr(0, sep);
                const filename = url.substr(sep);
                // we assume that the mtl file has the same name as the obj file
                const mtlname = filename.substr(0, filename.lastIndexOf('.')) + '.mtl';
                Loader.Handlers.add(/\.dds$/i, new DDSLoader());
                const mtlLoader = new MTLLoader();
                mtlLoader.setMaterialOptions({
                    side: DoubleSide
                });
                mtlLoader.setPath(path);
                // try to load mtl file
                mtlLoader.load(mtlname, materials => {
                    // load obj file with loaded materials
                    materials.preload();
                    this.loadObjModel(path, filename, materials);
                }, event => {
                    this.updateProgress(event);
                }, event => {
                    // onError: try to load obj without materials
                    this.loadObjModel(path, filename, undefined);
                });
            },
            loadObjModel: function(path, filename, materials) {
                let objLoader = new OBJLoader();
                if(materials) {
                    objLoader.setMaterials(materials);
                }
                objLoader.setPath(path);
                objLoader.load(filename,
                    object => { // onSuccess
                        object.castShadow = true;
                        object.receiveShadow = true;
                        for(var i=0; i<object.children.length; i++) {
                            var child = object.children[i];
                            this.octree.add(child, {
                                useFaces: false
                            });
                        }
                        this.group.add(object);
                        this.onWindowResize();
                    },
                    event => { // onProgress
                        this.updateProgress(event);
                    },
                    event => { // on
                    }
                );
            },
            loadFbx: function(file) {
                const url = file.url;
                const loader = new FBXLoader();
                loader.load(url, object => {
                    this.animationMixer = new AnimationMixer(object);
                    // Play first animation if available
                    if(object.animations && object.animations.length) {
                        animationMixer.clipAction(object.animations[0]).play();
                    }
                    object.traverse(node => {
                        if(node.isMesh) {
                            node.castShadow = true;
                            node.receiveShadow = true;
                        }
                        this.octree.add(node, {
                            useFaces: false
                        });
                    });
                    this.group.add(object);
                    this.onWindowResize();
                }, event => {
                    this.updateProgress(event);
                }, event => {
                });
            },
            loadProteinDb: function(file) {
                const vm = this;
                let labelRenderer = new CSS2DRenderer();
                labelRenderer.setSize(this.containerWidth, this.containerHeight);
				labelRenderer.domElement.style.position = 'absolute';
				labelRenderer.domElement.style.top = '0';
				labelRenderer.domElement.style.pointerEvents = 'none';
                this.container.appendChild(labelRenderer.domElement);
                this.loadMolecules(file.url);
            },
            loadMolecules: function(url) {
                const vm = this;
                while(this.group.children.length > 0) {
                    let object = this.group.children[0];
                    object.parent.remove(object);
                }
                let pdbLoader = new PDBLoader();
                pdbLoader.load(url, function(pdb) {
                    var atoms = pdb.geometryAtoms;
                    let bonds = pdb.geometryBonds;
                    let json = pdb.json;

                    let boxGeometry = new BoxBufferGeometry(1, 1, 1);
                    let sphereGeometry = new IcosahedronBufferGeometry(1, 2);

                    let offset = atoms.center();
                    bonds.translate(offset.x, offset.y, offset.z);

                    let positions = atoms.getAttribute('position');
                    let colors = atoms.getAttribute('color');

                    let position = new Vector3();
                    let color = new Color();

                    for(let i=0; i<positions.count; i++) {
                        position.x = positions.getX(i);
                        position.y = positions.getY(i);
                        position.z = positions.getZ(i);

                        color.r = colors.getX(i);
                        color.g = colors.getY(i);
                        color.b = colors.getZ(i);

                        let material = new MeshPhongMaterial({color: color});

                        let object = new Mesh(sphereGeometry, material);
                        object.position.copy(position);
                        object.position.multiplyScalar(1);
                        object.scale.multiplyScalar(0.33);
                        for(let j=0; j<object.children.length; j++) {
        					vm.octree.add(object.children[j], {
        						useFaces: false
        					});
        				}
                        vm.group.add(object);

                        let atom = json.atoms[i];
        				let text = document.createElement('div');
        				text.className = 'atom-label';
        				text.style.color = 'rgb(' + atom[3][0] + ',' + atom[3][1] + ',' + atom[3][2] + ')';
        				text.textContent = atom[4];
        				let label = new CSS2DObject(text);
        				label.position.copy(object.position);
        				vm.group.add(label);
                    }

                    positions = bonds.getAttribute('position');
                    let start = new Vector3();
                    let end = new Vector3();

                    for(let i=0; i<positions.count; i+=2) {
                        start.x = positions.getX(i);
                        start.y = positions.getY(i);
                        start.z = positions.getZ(i);

                        end.x = positions.getX(i+1);
                        end.y = positions.getY(i+1);
                        end.z = positions.getZ(i+1);

                        start.multiplyScalar(1);
                        end.multiplyScalar(1);

                        let object = new Mesh(boxGeometry, new MeshPhongMaterial(0xffffff));
                        object.position.copy(start);
                        object.position.lerp(end, 0.5);
                        object.scale.set(0.1, 0.1, start.distanceTo(end));
                        object.lookAt(end);
                        for(let j=0; j<object.children.length; j++) {
        					vm.octree.add(object.children[j], {
        						useFaces: false
        					});
        				}
                        vm.group.add(object);
                    }
                    vm.onWindowResize();
                },
                function(event) { // onProgress
                    vm.updateProgress(event);
                });
            },
            getIntersections: function(controller) {
        		this.tempMatrix.identity().extractRotation(controller.matrixWorld);
        		this.raycaster.ray.origin.setFromMatrixPosition(controller.matrixWorld);
        		this.raycaster.ray.direction.set(0, 0, -1).applyMatrix4(this.tempMatrix);
                const octreeObjects = this.octree.search(this.raycaster.ray.origin, this.raycaster.ray.far, true, this.raycaster.ray.direction);
                return this.raycaster.intersectOctreeObjects(octreeObjects);
        		// return this.raycaster.intersectObjects(group.children, true);
        	},
            //EventListeners
            onMouseDown: function() {

            },
            onWindowResize: function() {
                this.containerWidth = this.renderer.domElement.parentElement.clientWidth;
                this.containerHeight = this.renderer.domElement.parentElement.clientHeight;
                this.camera.aspect = this.containerWidth/this.containerHeight;
                this.camera.updateProjectionMatrix();
                this.renderer.setSize(this.containerWidth, this.containerHeight);
                if(this.labelRenderer) {
                    this.labelRenderer.setSize(this.containerWidth, this.containerHeight);
                }
            },
            // Vive EventListeners
            onGrabDown: function(event) {
        		let controller = event.target;
        		const intersections = this.getIntersections(controller);
        		if(intersections.length) {
        			const intersection = intersections[0];
        			this.tempMatrix.getInverse(controller.matrixWorld);
        			let object = intersection.object;
        			object.matrix.premultiply(this.tempMatrix);
        			object.matrix.decompose(object.position, object.quaternion, object.scale);
        			controller.add(object);
        			controller.userData.selected = object;
        		}
            },
            onGrabUp: function(event) {
        		let controller = event.target;
        		if(controller.userData.selected !== undefined) {
        			let object = controller.userData.selected;
        			object.matrix.premultiply(controller.matrixWorld);
        			object.matrix.decompose(object.position, object.quaternion, object.scale);
                    for(let i=0; i<object.children.length; i++) {
        				this.octree.add(object.children[i], {
        					useFaces: false
        				});
        			}
        			this.group.add(object);
        			controller.userData.selected = undefined;
        		}
            },
            onLightOn: function(event) {
        		this.flashlight.intensity = this.flashlightIntensity;
        		this.flashlightOn = true;
            },
            onLightOff: function(event) {
                this.flashlight.intensity = 0;
        		this.flashlightOn = false;
            },
            dimFlashLight: function(event) {
        		// thumbpad values are from -1 to 1, intesity goes from 0 to 2
        		this.flashlightIntensity = event.axes[0] + 1;
        		if(this.flashlightOn) this.flashlight.intensity = this.flashlightIntensity;
            },
            vrDisplayStateChanged(event) {
                if(this.renderer) {
                    this.renderer.vr.enabled = event.display.isPresenting;
                }
            },
            dimWorldLight: function(event) {
        		// thumbpad values are from -1 to 1, intesity goes from 0 to 2
        		const hem = event.axes[0];
        		const dir = event.axes[1];
        		// only update the intensity for the light with the higher value
        		// (emulate a d-pad)
        		if(Math.abs(hem) > Math.abs(dir)) {
        			this.hemisphereIntensity = hem + 1;
        			this.hemisphereLight.intensity = this.hemisphereIntensity;
        		} else {
        			this.directionalIntensity = dir + 1;
        			this.directionalLight.intensity = this.directionalIntensity;
        		}
            },
            updateProgress: function(event) {
                if(event.lengthComputable) {
                    this.progress = Math.round(event.loaded / event.total * 100);
                }
            }
        },
        data() {
            return {
                container: {},
                containerId: 'three-container',
                progress: 0,
                // three
                scale: 1,
                gui: null,
                guiConfig: null,
                animationClock: new Clock(),
                animationId: -1,
                animationMixer: {},
                camera: {},
                controls: {},
                containerWidth: 200,
                containerHeight: 100,
                directionalLight: {},
                heimisphereLight: {},
                group: {},
                octree: new Octree({
            		undeferred: false,
            		depthMax: Infinity,
            		objectsThreshold: 8,
            		overlapPct: 0.15
            	}),
                raycaster: new Raycaster(),
                renderer: {},
                scene: {},
	            tempMatrix: new Matrix4(),
                // controllers
                grabController: new ViveController(0),
                flashlightController: new ViveController(1),
                flashlight: {},
                flashlightOn: false,
                flashlightIntensity: 1,
                hemisphereIntensity: 1,
                directionalIntensity: 1
            }
        },
        watch: {
            file(newFile, oldFile) {
                this.loadModel(newFile);
            }
        }
    }
</script>
