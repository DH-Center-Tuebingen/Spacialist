<?php

namespace App\Enums;

enum LifecycleOperation {
    case INSTALLATION;
    
    case UNINSTALLATION;
    case UPLOAD;
    case UPDATE;
    case REMOVE;
    case DISCOVERY;
}