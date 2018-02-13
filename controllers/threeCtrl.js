spacialistApp.controller('threeCtrl', ['$scope', function($scope) {
    $scope.threeContainer = 'threejs-container';
    var container;
    var camera, scene, renderer, controls, animationId;
    var controller1, controller2, group;
    var animationMixer;
    var animationClock = new THREE.Clock();
    var raycaster = new THREE.Raycaster();
    var intersected = [];
    var octree = new THREE.Octree({
		undeferred: false,
		depthMax: Infinity,
		objectsThreshold: 8,
		overlapPct: 0.15
	});
	var tempMatrix = new THREE.Matrix4();
    var fileUrl, extension;
    var width, height;
    var mouse = new THREE.Vector2();
    var flashlight, hemisphereLight, directionalLight;
	var flashlightIntensity, hemisphereIntensity, directionalIntensity;
    flashlightIntensity = hemisphereIntensity = directionalIntensity = 1;
	var flashlightOn = false;
    // var raycaster = new THREE.Raycaster();
    var particles = [];
    // pdb variables
    var labelRenderer;

    var measureLine;
    var ts = Date.now();

    $scope.status = {
        progress: 0
    };
    $scope.points = 0;
    $scope.measurementEnabled = false;
    $scope.props = {
        length: 0.0
    };

    $scope.initThree = function(url) {
        fileUrl = url;
        extension = url.substr(url.lastIndexOf('.')+1);
        init();
        animate();
    };

    $scope.toggleMeasurement = function() {
        $scope.measurementEnabled = !$scope.measurementEnabled;
        if(!$scope.measurementEnabled) {
            $scope.points = 0;
            if(measureLine) scene.remove(measureLine);
            for(var i=0; i<particles.length; i++) {
                scene.remove(particles[i]);
            }
            particles.length = 0;
        }
    };

    $scope.zoomOut = function() {
        camera.zoom -= 0.1;
        camera.updateProjectionMatrix();
    };

    $scope.zoomIn = function() {
        camera.zoom += 0.1;
        camera.updateProjectionMatrix();
    };

    function updateProgress(event) {
        if(event.lengthComputable) {
            $scope.status.progress = Math.round(event.loaded / event.total * 100);
            $scope.$apply();
            console.log('Downloaded ' + $scope.status.progress + '% of model');
        }
    };

    function loadObj(path, objFile, materials) {
        var objLoader = new THREE.OBJLoader();
        if(materials) {
            objLoader.setMaterials(materials);
        }
        objLoader.setPath(path);
        objLoader.load(objFile,
            function(object) { // onSuccess
                object.castShadow = true;
                object.receiveShadow = true;
                for(var i=0; i<object.children.length; i++) {
					octree.add(object.children[i], {
						useFaces: false
					});
				}
                group.add(object);
                onWindowResize();
            },
            function(event) { // onProgress
                updateProgress(event);
            },
            function(event) { // onError
                console.log(event);
            }
        );
    }

    function init() {
		renderer = new THREE.WebGLRenderer({
            antialias: true
        });
		renderer.setPixelRatio(window.devicePixelRatio);
		renderer.setSize(width, height);
		renderer.shadowMap.enabled = true;
		renderer.shadowMap.type = THREE.PCFSoftShadowMap;
		renderer.gammaInput = true;
		renderer.gammaOutput = true;

        container = document.getElementById($scope.threeContainer);
        width = container.clientWidth;
        height = container.clientHeight;
        scene = new THREE.Scene();
		camera = new THREE.PerspectiveCamera(45, width/height, 0.1, 2000);
		camera.position.set(7, 5, 7);
        scene.add(camera);

		container.appendChild(renderer.domElement);
		container.appendChild(WEBVR.createButton(renderer));

        if(screenfull.enabled) {
            window.addEventListener('keydown', toggleFullscreen, false);
        }
		window.addEventListener('resize', onWindowResize, false);
        renderer.domElement.addEventListener('mousedown', onDocumentMouseDown, false);
		renderer.vr.enabled = true;
		controller1 = new THREE.ViveController(0);
		controller1.addEventListener('triggerdown', onGrabDown);
		controller1.addEventListener('triggerup', onGrabUp);
        controller1.addEventListener('thumbpadup', dimWorldLight);
        controller1.addEventListener('axischanged', recognizeTouch);
		scene.add(controller1);
		controller2 = new THREE.ViveController(1);
		controller2.addEventListener('triggerdown', onLightOn);
		controller2.addEventListener('triggerup', onLightOff);
		controller2.addEventListener('thumbpadup', dimFlashLight);
		scene.add(controller2);

		// Add point light to controller2
		flashlight = new THREE.SpotLight(0xffffff, 1, 5, 0.15, 1, 2);
		flashlight.castShadow = true;
		flashlight.shadow.mapSize.width = 1024;
		flashlight.shadow.mapSize.height = 1024;
		flashlight.shadow.camera.near = 0.05;
		flashlight.shadow.camera.far = 1000;
		// switch light off, is triggered using button on the ctrl
		flashlight.intensity = 0;
		// position the light source right behind the ctrl
		flashlight.position.set(controller2.position.x, controller2.position.y, controller2.position.z + 0.05);
		controller2.add(flashlight);

		// Add Models to Vive Controller
		var cloader = new THREE.OBJLoader();
		cloader.setPath('./img/vive-controller/');
		cloader.load('vr_controller_vive_1_5.obj', function(object) {
			var loader = new THREE.TextureLoader();
			loader.setPath('./img/vive-controller/');
			var controller = object.children[0];
			controller.material.map = loader.load('onepointfive_texture.png');
			controller1.add(object.clone());
			controller2.add(object.clone());
		} );

		// Add Controller Rays
		var geometry = new THREE.Geometry();
		geometry.vertices.push(new THREE.Vector3(0, 0, 0));
		geometry.vertices.push(new THREE.Vector3(0, 0, -1));
		var line = new THREE.Line(geometry);
		line.name = 'line';
		line.scale.z = 5;
		controller1.add(line.clone());
		// point the light source to the controller's "view" direction
		var c2line = line.clone();
		controller2.add(c2line);
		flashlight.target = c2line;
		controller2.add(flashlight.target);
		controls = new THREE.OrbitControls(camera, renderer.domElement);

        var gridHelper = new THREE.GridHelper(10, 20);
		scene.add(gridHelper);

        hemisphereLight = new THREE.HemisphereLight(0x808080, 0x606060)
		directionalLight = new THREE.DirectionalLight(0xffffff);
        directionalLight.position = camera.position;
        directionalLight.castShadow = true;
		directionalLight.shadow.camera.top = 2;
		directionalLight.shadow.camera.bottom = -2;
		directionalLight.shadow.camera.right = 2;
		directionalLight.shadow.camera.left = -2;
		directionalLight.shadow.mapSize.set( 4096, 4096 );
        scene.add(hemisphereLight);
		scene.add(directionalLight);

        group = new THREE.Group();
		scene.add(group);

        switch(extension) {
            case 'dae': // collada
                var loader = new THREE.ColladaLoader();
                loader.load(fileUrl, function(collada) {
                    var object = collada.scene;
                    var material, children;
                    var parent = object;
                    do {
                        children = parent.children;
                        if(!children || !children[0]) break;
                        material = children[0].material;
                        parent = children[0];
                    } while(!material);
                    if(material) {
                        material.side = THREE.DoubleSide;
                    }
                    object.castShadow = true;
                    object.receiveShadow = true;
					for(var i=0; i<object.children.length; i++) {
						octree.add(object.children[i], {
							useFaces: false
						});
					}
                    group.add(object);
                    onWindowResize();
                },
                function(event) { // onProgress
                    updateProgress(event);
                },
                function(event) { // onError
                });
                break;
            case 'obj': // wavefront obj
                var sep = fileUrl.lastIndexOf('/')+1;
                var path = fileUrl.substr(0, sep);
                var objUrl = fileUrl.substr(sep);
                // we assume that the mtl file has the same name as the obj file
                var mtlUrl = objUrl.substr(0, objUrl.lastIndexOf('.obj')) + '.mtl';
                THREE.Loader.Handlers.add(/\.dds$/i, new THREE.DDSLoader());
                var mtlLoader = new THREE.MTLLoader();
                mtlLoader.setMaterialOptions({
                    side: THREE.DoubleSide
                });
                mtlLoader.setPath(path);
                // try to load mtl file
                mtlLoader.load(mtlUrl, function(materials) {
                    // load obj file with loaded materials
                    materials.preload();
                    loadObj(path, objUrl, materials);
                }, function(event) {
                    updateProgress(event);
                }, function(event) {
                    console.log(event.target.response);
                    // onError: try to load obj without materials
                    loadObj(path, objUrl);
                });
                break;
            case 'pdb':
                labelRenderer = new THREE.CSS2DRenderer();
                labelRenderer.setSize(width, height);
				labelRenderer.domElement.style.position = 'absolute';
				labelRenderer.domElement.style.top = '0';
				labelRenderer.domElement.style.pointerEvents = 'none';
                container.appendChild(labelRenderer.domElement);
                var pdbLoader = new THREE.PDBLoader();
                loadMolecule(fileUrl, pdbLoader);
                break;
            case 'gltf':
                var loader = new THREE.GLTFLoader();
                loader.load(fileUrl, function(data) {
                    var gltf = data;
                    var object = gltf.scene;

                    object.traverse(function(node) {
                        if(node.isMesh) {
                            node.castShadow = true;
                            node.receiveShadow = true;
                        }
                    });

                    var animations = gltf.animations;
                    if(animations && animations.length > 0) {
                        animationMixer = new THREE.AnimationMixer(object);
                        for(var i=0; i<animations.length; i++) {
                            animationMixer.clipAction(animations[i]).play();
                        }
                    }
                    for(var i=0; i<object.children.length; i++) {
						octree.add(object.children[i], {
							useFaces: false
						});
					}
                    group.add(object);
                    onWindowResize();
                }, function(event) {
                    updateProgress(event);
                }, function(error) {
                    console.log(error);
                });
        }
    }

    function loadMolecule(url, loader) {
        while(group.children.length > 0) {
            var object = group.children[0];
            object.parent.remove(object);
        }

        loader.load(url, function(pdb) {
            var atoms = pdb.geometryAtoms;
            var bonds = pdb.geometryBonds;
            var json = pdb.json;

            var boxGeometry = new THREE.BoxBufferGeometry(1, 1, 1);
            var sphereGeometry = new THREE.IcosahedronBufferGeometry(1, 2);

            var offset = atoms.center();
            bonds.translate(offset.x, offset.y, offset.z);

            var positions = atoms.getAttribute('position');
            var colors = atoms.getAttribute('color');

            var position = new THREE.Vector3();
            var color = new THREE.Color();

            for(var i=0; i<positions.count; i++) {
                position.x = positions.getX(i);
                position.y = positions.getY(i);
                position.z = positions.getZ(i);

                color.r = colors.getX(i);
                color.g = colors.getY(i);
                color.b = colors.getZ(i);

                var material = new THREE.MeshPhongMaterial({color: color});

                var object = new THREE.Mesh(sphereGeometry, material);
                object.position.copy(position);
                object.position.multiplyScalar(1);
                object.scale.multiplyScalar(0.33);
                for(var i=0; i<object.children.length; i++) {
					octree.add(object.children[i], {
						useFaces: false
					});
				}
                group.add(object);

                var atom = json.atoms[i];
				var text = document.createElement('div');
				text.className = 'atom-label';
				text.style.color = 'rgb(' + atom[3][0] + ',' + atom[3][1] + ',' + atom[3][2] + ')';
				text.textContent = atom[4];
				var label = new THREE.CSS2DObject(text);
				label.position.copy(object.position);
				group.add(label);
            }

            positions = bonds.getAttribute('position');
            var start = new THREE.Vector3();
            var end = new THREE.Vector3();

            for(var i=0; i<positions.count; i+=2) {
                start.x = positions.getX(i);
                start.y = positions.getY(i);
                start.z = positions.getZ(i);

                end.x = positions.getX(i+1);
                end.y = positions.getY(i+1);
                end.z = positions.getZ(i+1);

                start.multiplyScalar(1);
                end.multiplyScalar(1);

                var object = new THREE.Mesh(boxGeometry, new THREE.MeshPhongMaterial(0xffffff));
                object.position.copy(start);
                object.position.lerp(end, 0.5);
                object.scale.set(0.1, 0.1, start.distanceTo(end));
                object.lookAt(end);
                for(var i=0; i<object.children.length; i++) {
					octree.add(object.children[i], {
						useFaces: false
					});
				}
                group.add(object);
            }
            onWindowResize();
        },
        function(event) { // onProgress
            updateProgress(event);
        });
    }

    function toggleFullscreen(event, hack) {
        var k = event.keyCode;
        if(k != 70) return; // 70 = 'f' key
        screenfull.request(renderer.domElement);
    }

    function onWindowResize() {
        var isFullscreen = screenfull.enabled || screenfull.isFullscreen;
        if(isFullscreen) {
            width = renderer.domElement.clientWidth;
            height = renderer.domElement.clientHeight;
        } else {
            width = renderer.domElement.parentElement.clientWidth;
            height = renderer.domElement.parentElement.clientHeight;
        }
        camera.aspect = width/height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
        if(labelRenderer) labelRenderer.setSize(width, height);
	}

    function onDocumentMouseDown(event) {
        if(!$scope.measurementEnabled) return;
        if(event.which != 1) return; // not left mouse button
        if($scope.points == 2) {
            $scope.points = 0;
            scene.remove(measureLine);
            measureLine = null;
            for(var i=0; i<particles.length; i++) {
                scene.remove(particles[i]);
                particles[i] = null;
            }
            particles.length = 0;
        }
		event.preventDefault();
		mouse.x = (event.offsetX/width)*2 - 1;
		mouse.y = -(event.offsetY/height)*2 + 1;
		raycaster.setFromCamera(mouse, camera);
		var intersects = raycaster.intersectObjects(group.children, true);
		if(intersects.length > 0) {
			var particle = new THREE.Sprite();
			particle.position.copy(intersects[0].point);
			particle.scale.x = particle.scale.y = 0.1;
			scene.add(particle);
            particles.push(particle);
            $scope.points++;
		}

        if($scope.points == 2) {
            var pointA = particles[0];
            var pointB = particles[1];
            var geometry = new THREE.Geometry();
            geometry.vertices.push(pointA);
            geometry.vertices.push(pointB);
            var material = new THREE.LineBasicMaterial();
            line = new THREE.Line(geometry, material);
            scene.add(line);
            measureLine = line;
            var length = pointA.getWorldPosition().distanceTo(pointB.getWorldPosition());
            $scope.props.length = length.toFixed(2);
        }
	}

    function onLightOn(event) {
		flashlight.intensity = flashlightIntensity;
		flashlightOn = true;
    }

    function onLightOff(event) {
        flashlight.intensity = 0;
		flashlightOn = false;
    }

	function dimWorldLight(event) {
		// thumbpad values are from -1 to 1, intesity goes from 0 to 2
		var hem = event.axes[0];
		var dir = event.axes[1];
		// only update the intensity for the light with the higher value
		// (emulate a d-pad)
		if(Math.abs(hem) > Math.abs(dir)) {
			hemisphereIntensity = hem + 1;
			hemisphereLight.intensity = hemisphereIntensity;
		} else {
			directionalIntensity = dir + 1;
			directionalLight.intensity = directionalIntensity;
		}
	}

	function dimFlashLight(event) {
		// thumbpad values are from -1 to 1, intesity goes from 0 to 2
		flashlightIntensity = event.axes[0] + 1;
		if(flashlightOn) flashlight.intensity = flashlightIntensity;
	}

    function recognizeTouch(event) {
    }

	function onGrabDown(event) {
		var controller = event.target;
		var intersections = getIntersections(controller);
		if(intersections.length > 0) {
			var intersection = intersections[0];
			tempMatrix.getInverse(controller.matrixWorld);
			var object = intersection.object;
			object.matrix.premultiply(tempMatrix);
			object.matrix.decompose(object.position, object.quaternion, object.scale);
			controller.add(object);
			controller.userData.selected = object;
		}
	}

	function onGrabUp(event) {
		var controller = event.target;
		if(controller.userData.selected !== undefined) {
			var object = controller.userData.selected;
			object.matrix.premultiply(controller.matrixWorld);
			object.matrix.decompose(object.position, object.quaternion, object.scale);
            for(var i=0; i<object.children.length; i++) {
				octree.add(object.children[i], {
					useFaces: false
				});
			}
			group.add(object);
			controller.userData.selected = undefined;
		}
	}

	function getIntersections(controller) {
		tempMatrix.identity().extractRotation(controller.matrixWorld);
		raycaster.ray.origin.setFromMatrixPosition(controller.matrixWorld);
		raycaster.ray.direction.set(0, 0, -1).applyMatrix4(tempMatrix);
        var octreeObjects = octree.search(raycaster.ray.origin, raycaster.ray.far, true, raycaster.ray.direction);
        return raycaster.intersectOctreeObjects(octreeObjects);
		// return raycaster.intersectObjects(group.children, true);
	}

	function cleanIntersected() {
		while(intersected.length) {
			var object = intersected.pop();
		}
	}

    function animate() {
        animationId = requestAnimationFrame(animate);
        if(animationMixer) animationMixer.update(animationClock.getDelta());
        renderer.animate(render);
    }

    function render() {
        if(!renderer) return;
        if(renderer.vr && renderer.vr.enabled) {
            controller1.update();
            controller2.update();
        }
        renderer.render(scene, camera);
        if(labelRenderer) labelRenderer.render(scene, camera);
        octree.update();
    }

    $scope.$on('$destroy', function() {
        cancelAnimationFrame(animationId);
        for(var i=0; i<group.length; i++) {
            scene.remove(group[i]);
            group[i] = null;
        }
        for(var i=0; i<particles.length; i++) {
            scene.remove(particles[i]);
            particles[i] = null;
        }
        group.length = 0;
        particles.length = 0;
        group = null;
        particles = null;
		window.addEventListener('keydown', null, false);
		window.addEventListener('resize', null, false);
        window.addEventListener('vrdisplaypresentchange', null, false);
        renderer.domElement.addEventListener('mousedown', null, false);
        var domParent = renderer.domElement.parentElement;
        domParent.removeChild(renderer.domElement);
        renderer = null;
        labelRenderer = null;
        scene = null;
        controls = null;
        camera = null;
        if(animationMixer) animationMixer.stopAllAction();
    });
}]);
