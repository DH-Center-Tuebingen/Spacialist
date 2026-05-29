<?php

namespace App\Enums;

enum LifecycleOperation {
    case INSTALLATION;
    case ACTIVATION;
    case DEACTIVATION;
    case UNINSTALLATION;
    case UPLOAD;
    case UPDATE;
    case REMOVE;
    case DISCOVERY;
}