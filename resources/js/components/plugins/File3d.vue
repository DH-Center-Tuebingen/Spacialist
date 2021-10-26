<script>
    import { ColladaLoader } from 'three/examples/jsm/loaders/ColladaLoader.js';
    import { FBXLoader } from 'three/examples/jsm/loaders/FBXLoader.js';
    import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
    import { MTLLoader } from 'three/examples/jsm/loaders/MTLLoader.js';
    import { OBJLoader2 } from 'three/examples/jsm/loaders/OBJLoader2.js';
    import { MtlObjBridge } from 'three/examples/jsm/loaders/obj2/bridge/MtlObjBridge.js';
    import { PDBLoader } from 'three/examples/jsm/loaders/PDBLoader.js';
    import { CSS2DRenderer, CSS2DObject } from 'three/examples/jsm/renderers/CSS2DRenderer.js';
    import * as dat from 'three/examples/jsm/libs/dat.gui.module.js';
    import { VRButton } from 'three/examples/jsm/webxr/VRButton.js';

    export default {
        destroyed() {
            window.removeEventListener('resize', this.onWindowResize, false);
            this.renderer.domElement.removeEventListener('mousedown', this.onMouseDown, false);
            // VR Events
            if(this.grabController) {
                this.grabController.removeEventListener('selectstart', this.onGrabDown);
                this.grabController.removeEventListener('selectend', this.onGrabUp);
                this.grabController.removeEventListener('thumbpadup', this.dimWorldLight);
                // this.grabController.removeEventListener('axischanged', this.recognizeTouch);
            }
            if(this.flashlightController) {
                this.flashlightController.removeEventListener('selectstart', this.onLightOn);
                this.flashlightController.removeEventListener('selectend', this.onLightOff);
                this.flashlightController.removeEventListener('thumbpadup', this.dimFlashLight);
            }

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
                if(!this.activeMesh) return;
                const sx = this.guiConfig.scaleX;
                const sy = this.guiConfig.scaleY;
                const sz = this.guiConfig.scaleZ;
                this.activeMesh.scale.set(sx, sy, sz);
            },
            setPosition() {
                if(!this.activeMesh) return;
                const px = this.guiConfig.positionX;
                const py = this.guiConfig.positionY;
                const pz = this.guiConfig.positionZ;
                this.activeMesh.position.set(px, py, pz);
            },
            selectObject(object) {
                dat.GUI.toggleHide();
                if(object) {
                    this.addTransformControlsTo(object);
                } else {
                    this.removeTransformControls();
                }
            },
            addTransformControlsTo(mesh) {
                this.activeMesh = mesh;
                this.scene.add(this.transformControls);
                this.transformControls.attach(mesh);
                this.transformControls.enabled = true;
                this.controls.target = mesh.position.clone();
                this.controls.update();
            },
            removeTransformControls() {
                this.transformControls.detach();
                this.activeMesh = null;
                this.scene.remove(this.transformControls);
                this.transformControls.enabled = false;
                this.controls.target = new Vector3(0, 0, 0);
                this.controls.update();
            },
            zoomToObject(object) {
                const offset = 1.25;
                if(!object.geometry.boundingBox) {
                    object.geometry.computeBoundingBox();
                }
                const bbox = object.geometry.boundingBox;
                const center = bbox.getCenter();
                let size = bbox.getSize();
                size.multiply(object.scale);

                const maxDim = Math.max(size.x, size.y, size.z);
                const fov = this.camera.fov * (Math.PI/180);
                const z = Math.abs(maxDim / Math.sin(fov/2));

                this.scene.updateMatrixWorld();
                const objWP = object.getWorldPosition();

                const dir = this.camera.position.sub(objWP);
                const unitDir = dir.normalize();
                unitDir.multiplyScalar(z);
                let newPos = new Vector3();
                newPos.add(objWP);
                newPos.add(unitDir);
                this.camera.position.copy(newPos);
                this.camera.lookAt(object.position);
	            this.camera.updateProjectionMatrix();
                this.controls.update();
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
            toggleFullscreen() {
                if(!this.fullscreenHandler) return;
                this.fullscreenHandler.toggle(document.getElementById(this.containerId))
            },
            initEventListeners: function() {
                window.addEventListener('resize', this.onWindowResize, false);
                this.renderer.domElement.addEventListener('mousedown', this.onMouseDown, false);
                this.renderer.domElement.addEventListener('mousemove', this.onMouseMove, false);
                this.renderer.domElement.addEventListener('mouseup', this.onMouseUp, false);
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
            initViveEventListeners: function() {
                // Vive Events
                if(this.grabController) {
                    this.grabController.addEventListener('selectstart', this.onGrabDown);
                    this.grabController.addEventListener('selectend', this.onGrabUp);
                    this.grabController.addEventListener('thumbpadup', this.dimWorldLight);
                    // this.grabController.addEventListener('axischanged', this.recognizeTouch);
                }
                if(this.flashlightController) {
                    this.flashlightController.addEventListener('selectstart', this.onLightOn);
                    this.flashlightController.addEventListener('selectend', this.onLightOff);
                    this.flashlightController.addEventListener('thumbpadup', this.dimFlashLight);
                }

                window.addEventListener('vrdisplaypresentchange', this.vrDisplayStateChanged, false);
            },
            initViveControls: function() {
                if(!this.renderer.vr) {
                    return;
                }
                const vm = this;
                this.grabController = this.renderer.vr.getController(0);
                this.flashlightController = this.renderer.vr.getController(1);
                if(this.grabController || this.flashlightController) {
                    this.initViveEventListeners();
                    let ctrlLoader = new OBJLoader2();
                    ctrlLoader.load('./img/vr_controller_vive_1_5.obj', function(object) {
                        let txtLoader = new TextureLoader();
                        txtLoader.setPath('./img/vive-controller/');
                        let controllerModel = object.children[0];
                        controllerModel.material.map = txtLoader.load('./img/onepointfive_texture.png');
                        if(vm.grabController) {
                            vm.grabController.add(object.clone());
                        }
                        if(vm.flashlightController) {
                            vm.flashlightController.add(object.clone());
                        }
                    });
                    // Add Controller Rays
                    let geometry = new Geometry();
                    geometry.vertices.push(new Vector3(0, 0, 0));
                    geometry.vertices.push(new Vector3(0, 0, -1));
                    let line = new Line(geometry);
                    line.name = 'line';
                    line.scale.z = 5;

                    if(this.grabController) {
                        this.grabController.add(line.clone());
                        this.scene.add(this.grabController);
                    }

                    if(this.flashlightController) {
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
                            this.flashlightController.position.z + 0.05
                        );
                        this.flashlightController.add(this.flashlight);

                        // point the light source to the controller's "view" direction
                        let c2line = line.clone();
                        this.flashlightController.add(c2line);
                        this.flashlight.target = c2line;
                        this.flashlightController.add(this.flashlight.target);
                        this.scene.add(this.flashlightController);
                    }
                }
            },
            // Loaders
            loadObj: function(file) {
                const url = file.url;
                const sep = url.lastIndexOf('/')+1;
                const path = url.substr(0, sep);
                const filename = url.substr(sep);
                // we assume that the mtl file has the same name as the obj file
                const mtlname = filename.substr(0, filename.lastIndexOf('.')) + '.mtl';
                const mtlLoader = new MTLLoader();
                mtlLoader.setMaterialOptions({
                    side: DoubleSide
                });
                mtlLoader.setPath(path);
                // try to load mtl file
                mtlLoader.load(mtlname, materials => {
                    // load obj file with loaded materials
                    this.loadObjModel(path, filename, materials);
                }, event => {
                    this.updateProgress(event);
                }, event => {
                    // onError: try to load obj without materials
                    this.loadObjModel(path, filename, undefined);
                });
            },
            loadObjModel: function(path, filename, materials) {
                let objLoader = new OBJLoader2();
                objLoader.setModelName(filename);
                if(materials) {
                    objLoader.addMaterials(MtlObjBridge.addMaterialsFromMtlLoader(materials));
                }
                objLoader.load(path + filename,
                    object => { // onSuccess
                        this.addModelToScene(object);
                    },
                    event => { // onProgress
                        this.updateProgress(event);
                    },
                    event => { // on
                    }
                );
            },
            intersectAtClick(event) {
                this.mouse.x = (event.layerX / this.renderer.domElement.clientWidth) * 2 - 1;
                this.mouse.y = -(event.layerY / this.renderer.domElement.clientHeight) * 2 + 1;
                this.raycaster.setFromCamera(this.mouse, this.camera);

                return this.raycaster.intersectObjects(this.raycastTargets);
            },
            getIntersections: function(controller) {
        		this.tempMatrix.identity().extractRotation(controller.matrixWorld);
        		this.raycaster.ray.origin.setFromMatrixPosition(controller.matrixWorld);
        		this.raycaster.ray.direction.set(0, 0, -1).applyMatrix4(this.tempMatrix);
        		return this.raycaster.intersectObjects(this.group.children, true);
        	},
            hasModelLods(objectGroup) {
                if(objectGroup.type != 'Group') return false;
                if(!objectGroup.children) return false;
                const regex = RegExp('(LOD|lod)\\d+$');
                let isLod = true;
                for(let i=0; i<objectGroup.children.length; i++) {
                    const c = objectGroup.children[i];
                    if(!regex.test(c.name)) {
                        isLod = false;
                        break;
                    }
                }
                return isLod;
            },
            isLodGroup(model) {
                return !!this.lodGroup[model.uuid];
            },
            isPartOfLodGroup(mesh) {
                const part = this.lodParts[mesh.uuid];
                if(!part)  return false;
                return !!this.lodGroup[part];
            },
            getLodGroup(mesh) {
                // TODO throw error instead?
                if(!this.isPartOfLodGroup(mesh)) return {};
                return this.lodGroup[this.lodParts[mesh.uuid]];
            },
            //EventListeners
            // track if primary button is pressed
            onMouseDown(event) {
                // only capture left (primary) button click
                if(event.buttons !== 1) return;
                // do not capture event, if transform controls are active
                if(this.transformControls.enabled) return;
                this.mouseDown = event.buttons;
            },
            // track if primary button is dragged
            onMouseMove(event) {
                // only capture left (primary) button click
                if(event.buttons !== 1) return;
                // do not capture event, if transform controls are active
                if(this.transformControls.enabled) return;
                if(this.mouseDown !== 1) return;
                this.mouseMoving = true;
            },
            // handle click only if primary button and not dragged
            onMouseUp(event) {
                if(this.mouseMoving || this.mouseDown !== 1) {
                    this.mouseDown = 0;
                    this.mouseMoving = false;
                    return;
                }
                // do not capture event, if transform controls are active
                if(this.transformControls.enabled) return;
                const intersections = this.intersectAtClick(event);
                if(intersections.length) {
                    // if current model is part of a LOD group, select group
                    let mesh = intersections[0].object;
                    if(!this.isLodGroup(mesh) && this.isPartOfLodGroup(mesh)) {
                        mesh = this.getLodGroup(mesh);
                    }
                    this.selectObject(mesh);
                }
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
        },
    }
</script>
