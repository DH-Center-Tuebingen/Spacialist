<?php

namespace App\Validators;

use App\Interfaces\ValidateThrow;
use App\Models\Plugin\Hook;
use App\Services\Plugin\HookService;
use App\Support\Method;
use Illuminate\Support\Facades\Route;

class HookValidator implements ValidateThrow
{
    public function validate(array $hook): Hook
    {
        $hook = $this->ensureRequiredFields($hook);
        $hook = $this->trimFields($hook);
        $this->evaluateMethodFormat($hook);
        $method = $this->evaluateMethod($hook);
        $order  = $this->evaluateOrder($hook);

        $hookModel         = new Hook();
        $hookModel->on     = $hook['on'];
        $hookModel->method = $method;
        $hookModel->src    = $hook['src'];
        $hookModel->order  = $order;

        $this->validateOn($hookModel);
        $this->validateRouteIsNotForbidden($hookModel);

        return $hookModel;
    }

    public function ensureRequiredFields(array $hookJson)
    {
        $missingFields  = [];
        $requiredFields = ['on', 'src'];
        foreach($requiredFields as $requiredField) {
            if(! isset($hookJson[$requiredField])) {
                $missingFields[] = $requiredField;
            }
        }

        if(count($missingFields) > 0) {
            throw new \Exception("Hook is missing field(s): " . implode(", ", $missingFields));
        }
        return $hookJson;
    }

    public function trimFields(array $hookJson)
    {
        $fieldsToTrim = ['on', 'src', 'method'];
        foreach($fieldsToTrim as $field) {
            if(isset($hookJson[$field]) && is_string($hookJson[$field])) {
                $hookJson[$field] = trim($hookJson[$field]);
            }
        }
        return $hookJson;
    }

    public function evaluateMethodFormat(array $hookJson): void
    {
        $method = Method::parseFromString($hookJson['src']); // This will throw an exception if the format is invalid, we just use it for validation here.
        if(! $method->isValid()) {
            throw new \Exception("Hook 'src' field must be in the format 'class@method'. Given: '{$hookJson['src']}'");
        }
    }

    public function evaluateMethod(array $hookJson): string
    {
        $method = isset($hookJson['method']) ? strtoupper($hookJson['method']) : "GET";

        if(! in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'])) {
            throw new \Exception("Hook 'on' field has invalid method '$method'. Allowed methods are GET, POST, PUT, DELETE, PATCH.");
        }

        return $method;
    }

    public function evaluateOrder(array $hookJson): int
    {
        $order = isset($hookJson['order']) ? $hookJson['order'] : 0;
        return intval($order);
    }

    public function validateOn(Hook $hookModel): void
    {
        if(! $this->routeExists($hookModel->method, $hookModel->on)) {
            throw new \Exception("Hook on invalid route '" . $hookModel->getApiIdentifier() . "'.");
        }
    }

    public function validateRouteIsNotForbidden(Hook $hookModel): void
    {
        if(in_array($hookModel->getApiIdentifier(), HookService::FORBIDDEN_HOOKS)) {
            throw new \Exception("Hook on '" . $hookModel->getApiIdentifier() . "' is not allowed.");
        }
    }

    private function routeExists(string $method, string $url): bool
    {
        $methodRoutes = Route::getRoutes()->getRoutesByMethod()[$method] ?? [];
        return isset($methodRoutes[$url]);
    }
}
