spacialistApp.controller('threeCtrl', ['$scope', function($scope) {
    $scope.threeContainer = 'threejs-container';
    var container;
    var camera, scene, renderer, controls, animationId;
    var sdisplay, controller1, controller2, group;
    var raycaster, intersected = [];
	var tempMatrix = new THREE.Matrix4();
    var fileUrl, extension;
    var width, height;
    var mouse = new THREE.Vector2();
    // var raycaster = new THREE.Raycaster();
    var sceneObjects = [];
    var particles = [];
    var measureLine;
    var ts = Date.now();

    $scope.status = {
        progress: 0,
        vr: {
            errored: false,
            message: '',
            button: {
                text: 'No VR Display',
                toggle: function() {
                    if(sdisplay) {
        				sdisplay.isPresenting ? sdisplay.exitPresent() : sdisplay.requestPresent([
                            {
                                source: renderer.domElement
                            }
                        ]);
                    }
                }
            }
        }
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
        // console.log(camera.zoom);
        camera.zoom -= 0.1;
        camera.updateProjectionMatrix();
    };

    $scope.zoomIn = function() {
        camera.zoom += 0.1;
        camera.updateProjectionMatrix();
    };

    function loadObj(path, objFile, materials) {
        var objLoader = new THREE.OBJLoader2();
        if(materials) {
            objLoader.setMaterials(materials.materials);
        }
        objLoader.setPath(path);
        objLoader.load(objFile,
            function(object) { // onSuccess
                // scene.add(object);
                object.castShadow = true;
				object.receiveShadow = true;
                group.add(object);
                sceneObjects.push(object);
                onWindowResize();
            },
            function(event) { // onProgress
                if(event.lengthComputable) {
                    $scope.status.progress = Math.round(event.loaded / event.total * 100);
                    $scope.$apply();
                    console.log('Downloaded ' + $scope.status.progress + '% of model');
                }
            },
            function(event) { // onError
            }
        );
    }

    function init() {
        WEBVR.checkAvailability().catch(function(message) {
            $scope.status.vr.errored = true;
            $scope.status.vr.message = message;
        });

        container = document.getElementById($scope.threeContainer);
        width = container.clientWidth;
        height = container.clientHeight;
        scene = new THREE.Scene();
		camera = new THREE.PerspectiveCamera(45, width/height, 0.1, 2000);
		camera.position.set(7, 5, 7);
        scene.add(camera);

		var gridHelper = new THREE.GridHelper(10, 20);
		scene.add(gridHelper);

        scene.add(new THREE.HemisphereLight(0x808080, 0x606060));
		var directionalLight = new THREE.DirectionalLight(0xffffff);
        directionalLight.position = camera.position;
        directionalLight.castShadow = true;
		directionalLight.shadow.camera.top = 2;
		directionalLight.shadow.camera.bottom = -2;
		directionalLight.shadow.camera.right = 2;
		directionalLight.shadow.camera.left = -2;
		directionalLight.shadow.mapSize.set( 4096, 4096 );
		scene.add(directionalLight);

        group = new THREE.Group();
		scene.add(group);

        if(extension == 'dae') { // collada
            var loader = new THREE.ColladaLoader();
            loader.options.convertUpAxis = true;
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
                // scene.add(object);
                object.castShadow = true;
                object.receiveShadow = true;
                group.add(object);
                sceneObjects.push(object);
                onWindowResize();
            },
            function(event) { // onProgress
                if(event.lengthComputable) {
                    $scope.status.progress = Math.round(event.loaded / event.total * 100);
                    $scope.$apply();
                    console.log('Downloaded ' + $scope.status.progress + '% of model');
                }
            },
            function(event) { // onError
            });
        } else if(extension == 'obj') { // obj
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
            }, function() {}, function() {
                // onError: try to load obj without materials
                loadObj(path, objUrl);
            });
        }

		renderer = new THREE.WebGLRenderer({
            antialias: true
        });
		renderer.setPixelRatio(window.devicePixelRatio);
		renderer.setSize(width, height);
		renderer.shadowMap.enabled = true;
		renderer.gammaInput = true;
		renderer.gammaOutput = true;
		container.appendChild(renderer.domElement);
        renderer.vr.enabled = true;
        renderer.vr.standing = true;

		// controls = new THREE.OrbitControls(camera, renderer.domElement);
        // Add Vive Controller
        controller1 = new THREE.ViveController(0);
		controller1.standingMatrix = renderer.vr.getStandingMatrix();
		controller1.addEventListener( 'triggerdown', onTriggerDown );
		controller1.addEventListener( 'triggerup', onTriggerUp );
		scene.add(controller1);
		controller2 = new THREE.ViveController(1);
		controller2.standingMatrix = renderer.vr.getStandingMatrix();
		controller2.addEventListener( 'triggerdown', onTriggerDown );
		controller2.addEventListener( 'triggerup', onTriggerUp );
		scene.add(controller2);

        // Add Models to Vive Controller
        var cloader = new THREE.OBJLoader2();
		cloader.setPath('models/obj/vive-controller/');
		cloader.load('vr_controller_vive_1_5.obj', function(object) {
			var loader = new THREE.TextureLoader();
			loader.setPath('models/obj/vive-controller/');
			var controller = object.children[0];
			controller.material.map = loader.load('onepointfive_texture.png');
			controller.material.specularMap = loader.load('onepointfive_spec.png');
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
		controller2.add(line.clone());
		raycaster = new THREE.Raycaster();

        WEBVR.getVRDisplay(function(display) {
			renderer.vr.setDevice(display);
            sdisplay = display;

            window.addEventListener('vrdisplaypresentchange', function() {
                $scope.status.vr.button.text = sdisplay.isPresenting ? 'Exit VR' : 'Enter VR';
            }, false );
		});

		window.addEventListener('resize', onWindowResize, false);
        renderer.domElement.addEventListener('mousedown', onDocumentMouseDown, false);
    }

    function onWindowResize() {
        width = renderer.domElement.clientWidth;
        height = renderer.domElement.clientHeight;
		camera.aspect = width/height;
		camera.updateProjectionMatrix();
		renderer.setSize(width, height);
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
		var intersects = raycaster.intersectObjects(sceneObjects, true);
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

	function onTriggerDown(event) {
		var controller = event.target;
		var intersections = getIntersections(controller);
        console.log(intersections.length);
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

	function onTriggerUp(event) {
		var controller = event.target;
		if(controller.userData.selected !== undefined) {
			var object = controller.userData.selected;
			object.matrix.premultiply(controller.matrixWorld);
			object.matrix.decompose(object.position, object.quaternion, object.scale);
			group.add(object);
			controller.userData.selected = undefined;
		}
	}

	function getIntersections(controller) {
		tempMatrix.identity().extractRotation(controller.matrixWorld);
		raycaster.ray.origin.setFromMatrixPosition(controller.matrixWorld);
		raycaster.ray.direction.set(0, 0, -1).applyMatrix4(tempMatrix);
		return raycaster.intersectObjects(group.children, true);
	}

	function intersectObjects(controller) {
		if(controller.userData.selected !== undefined) return;
		var line = controller.getObjectByName('line');
		var intersections = getIntersections(controller);
		if(intersections.length > 0) {
			var intersection = intersections[0];
			var object = intersection.object;
			intersected.push(object);
			line.scale.z = intersection.distance;
		} else {
			line.scale.z = 5;
		}
	}

	function cleanIntersected() {
		while(intersected.length) {
			var object = intersected.pop();
		}
	}

    function animate() {
        animationId = requestAnimationFrame(animate);
        renderer.animate(render);
    }

    function render() {
        controller1.update();
        controller2.update();
        cleanIntersected();
        intersectObjects(controller1);
		intersectObjects(controller2);
        renderer.render(scene, camera);
    }

    $scope.$on('$destroy', function() {
        cancelAnimationFrame(animationId);
        for(var i=0; i<sceneObjects.length; i++) {
            scene.remove(sceneObjects[i]);
            sceneObjects[i] = null;
        }
        for(var i=0; i<particles.length; i++) {
            scene.remove(particles[i]);
            particles[i] = null;
        }
        sceneObjects.length = 0;
        particles.length = 0;
        sceneObjects = null;
        particles = null;
		window.addEventListener('resize', null, false);
        window.addEventListener('vrdisplaypresentchange', null, false);
        renderer.domElement.addEventListener('mousedown', null, false);
        var domParent = renderer.domElement.parentElement;
        domParent.removeChild(renderer.domElement);
        renderer = null;
        scene = null;
        controls = null;
        camera = null;
    });
}]);
