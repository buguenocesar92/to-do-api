<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use App\Models\RoutePermission;
use Spatie\Permission\Models\Permission;

class GenerateRoutePermissions extends Command
{
    protected $signature = 'generate:route-permissions';
    protected $description = 'Genera las rutas de la aplicación y crea una relación con los permisos, omitiendo permisos excluidos';

    public function handle()
    {
        $routes = Route::getRoutes();
        $count = 0;
        // Lista de permisos que no se deben crear
        $excludedPermissions = [
            'csrfcookiecontroller.show',
            'authcontroller.login',
            'authcontroller.register',
            'authcontroller.logout',
            'authcontroller.refresh',
            'authcontroller.me',
        ];

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
                // Para rutas sin controlador se asigna un valor nulo o genérico
                $permissionName = null;
            }

            // Si el nombre del permiso está en la lista de excluidos, se omite su creación
            $permissionId = null;
            if ($permissionName && !in_array($permissionName, $excludedPermissions)) {
                $permission = Permission::firstOrCreate(['name' => $permissionName]);
                $permissionId = $permission->id;
            } else {
                // En caso de estar excluido, se establece el nombre y el ID a null para no crear el permiso
                $permissionName = null;
                $permissionId = null;
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

