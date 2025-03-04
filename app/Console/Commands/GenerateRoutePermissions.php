<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use App\Models\RoutePermission;
use Spatie\Permission\Models\Permission;

class GenerateRoutePermissions extends Command
{
    protected $signature = 'generate:route-permissions';
    protected $description = 'Genera las rutas de la aplicación y crea una relación con los permisos';

    public function handle()
    {
        $routes = Route::getRoutes();
        $count = 0;

        foreach ($routes as $route) {
            $action = $route->getAction();
            // Se usa el nombre de la ruta o, en su defecto, la URI
            $routeName = $route->getName() ?: $route->uri();

            // Si existe un controlador asignado, generamos un nombre de permiso
            if (isset($action['controller'])) {
                // Se espera que el controlador tenga el formato Controller@method
                $controllerAction = explode('@', $action['controller']);
                if (count($controllerAction) === 2) {
                    $controller = class_basename($controllerAction[0]);
                    $method = $controllerAction[1];
                    $permissionName = strtolower($controller . '.' . $method);
                } else {
                    $permissionName = null;
                }
            } else {
                // Para rutas sin controlador se deja nulo o se asigna un valor genérico
                $permissionName = null;
            }

            // Buscamos o creamos el permiso correspondiente y obtenemos su ID
            $permissionId = null;
            if ($permissionName) {
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
                $permissionId = $permission->id;
            }

            // Verificamos si la ruta ya está registrada en la tabla
            $exists = RoutePermission::where('route_name', $routeName)->first();
            if (!$exists) {
                RoutePermission::create([
                    'route_name'      => $routeName,
                    'permission_name' => $permissionName,
                    'permission_id'   => $permissionId,
                ]);
                $this->info("Creada: {$routeName} con permiso: {$permissionName}");
                $count++;
            }
        }

        $this->info("Total de rutas procesadas: {$count}");
    }
}
