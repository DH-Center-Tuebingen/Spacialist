spacialistApp.controller('threeCtrl', ['$scope', function($scope) {
    $scope.threeContainer = 'threejs-container';
    var container;
    var camera, scene, renderer, controls, animationId;
    var fileUrl, extension;
    var width, height;
    var mouse = new THREE.Vector2();
    var raycaster = new THREE.Raycaster();
    var sceneObjects = [];
    var particles = [];
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
                scene.add(object);
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
        container = document.getElementById($scope.threeContainer);
        width = container.clientWidth;
        height = container.clientHeight;
		camera = new THREE.PerspectiveCamera(45, width/height, 0.1, 2000);
		camera.position.set(7, 5, 7);
		scene = new THREE.Scene();

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
    			scene.add(object);
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

		var gridHelper = new THREE.GridHelper(10, 20);
		scene.add(gridHelper);

		var ambientLight = new THREE.AmbientLight(0xcccccc);
		scene.add(ambientLight);
		var directionalLight = new THREE.DirectionalLight(0xffffff);
		// directionalLight.position.set(0, 1, -1).normalize();
        directionalLight.position = camera.position;
		scene.add(directionalLight);

		renderer = new THREE.WebGLRenderer({
            antialias: true
        });
		renderer.setPixelRatio(window.devicePixelRatio);
		renderer.setSize(width, height);
		container.appendChild(renderer.domElement);

		controls = new THREE.OrbitControls(camera, renderer.domElement);

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

    function animate() {
        animationId = requestAnimationFrame(animate);
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
        renderer.domElement.addEventListener('mousedown', null, false);
        var domParent = renderer.domElement.parentElement;
        domParent.removeChild(renderer.domElement);
        renderer = null;
        scene = null;
        controls = null;
        camera = null;
        controls = null;
    });
}]);
