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

    function init() {
        container = document.getElementById($scope.threeContainer);
		camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 2000);
		camera.position.set(7, 5, 7);
		scene = new THREE.Scene();

        if(extension == 'dae') { // collada
    		var loader = new THREE.ColladaLoader();
    		loader.options.convertUpAxis = true;
    		loader.load(fileUrl, function(collada) {
    			var object = collada.scene;
    			scene.add(object);
                sceneObjects.push(object);
    		} );
        } else if(extension == 'obj') { // obj

        }

		var gridHelper = new THREE.GridHelper(10, 20);
		scene.add(gridHelper);

		var ambientLight = new THREE.AmbientLight(0xcccccc);
		scene.add(ambientLight);
		var directionalLight = new THREE.DirectionalLight(0xffffff);
		directionalLight.position.set(0, 1, -1).normalize();
		scene.add(directionalLight);

		renderer = new THREE.WebGLRenderer({
            antialias: true
        });
		renderer.setPixelRatio(window.devicePixelRatio);
		renderer.setSize(window.innerWidth, window.innerHeight);
		container.appendChild(renderer.domElement);

		controls = new THREE.OrbitControls(camera, renderer.domElement);

		window.addEventListener('resize', onWindowResize, false);
        renderer.domElement.addEventListener('mousedown', onDocumentMouseDown, false);
    }

    function onWindowResize() {
		camera.aspect = window.innerWidth / window.innerHeight;
		camera.updateProjectionMatrix();
		renderer.setSize(window.innerWidth, window.innerHeight);
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
		mouse.x = (event.offsetX/renderer.domElement.clientWidth)*2 - 1;
		mouse.y = -(event.offsetY/renderer.domElement.clientHeight)*2 + 1;
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
        }
        for(var i=0; i<particles.length; i++) {
            scene.remove(particles[i]);
        }
		window.addEventListener('resize', null, false);
        renderer.domElement.addEventListener('mousedown', null, false);
        scene = null;
        controls = null;
        camera = null;
        controls = null;
    });
}]);
