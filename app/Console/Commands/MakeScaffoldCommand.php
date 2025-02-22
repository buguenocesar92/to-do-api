<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeScaffoldCommand extends Command
{
    /**
     * El nombre y la firma del comando.
     *
     * @var string
     */
    protected $signature = 'make:scaffold {name : El nombre de la entidad (ej. Example)}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Genera modelo, migración, controlador, interfaz, repositorio y servicio para una entidad dada, agregando campos genéricos';

    /**
     * Ejecuta el comando.
     */
    public function handle()
    {
        // Convertir el argumento en StudlyCase para nombres de clases.
        $name = Str::studly($this->argument('name'));
        $nameLower = Str::camel($name);
        $controllerName = "{$name}Controller";

        // 1. Crear el modelo junto con la migración.
        $this->call('make:model', [
            'name'        => $name,
            '--migration' => true,
        ]);

        // 1.1. Actualizar la migración con campos genéricos.
        $tableName = Str::plural(Str::snake($name));
        $migrationFiles = glob(database_path("migrations/*_create_{$tableName}_table.php"));
        if (count($migrationFiles) > 0) {
            $migrationPath = $migrationFiles[0];
            $migrationContent = file_get_contents($migrationPath);

            // Agregar campos genéricos justo después de la creación de la clave primaria.
            $migrationContent = preg_replace(
                '/(\$table->id\(\);)/',
                "$1\n            \$table->string('name');\n            \$table->text('description')->nullable();\n            \$table->boolean('status')->default(true);",
                $migrationContent
            );
            file_put_contents($migrationPath, $migrationContent);
            $this->info("Migración actualizada con campos genéricos en {$migrationPath}.");
        } else {
            $this->error("No se encontró la migración para la tabla {$tableName}.");
        }

        // 1.2. Actualizar el modelo para agregar la propiedad fillable.
        $modelPath = app_path("Models/{$name}.php");
        if (!file_exists($modelPath)) {
            // En algunos proyectos el modelo se crea en la raíz de app/
            $modelPath = app_path("{$name}.php");
        }
        if (file_exists($modelPath)) {
            $modelContent = file_get_contents($modelPath);
            if (strpos($modelContent, 'protected $fillable') === false) {
                $modelContent = preg_replace(
                    '/(class\s+' . $name . '\s+extends\s+\S+\s*\{)/',
                    "$1\n    protected \$fillable = ['name', 'description', 'status'];",
                    $modelContent
                );
                file_put_contents($modelPath, $modelContent);
                $this->info("Modelo {$name} actualizado con fillable.");
            }
        } else {
            $this->error("No se encontró el modelo {$name}.");
        }

        // 2. Crear el controlador.
        $this->call('make:controller', [
            'name' => $controllerName,
        ]);

        // Sobrescribir el controlador con métodos CRUD.
        $controllerPath = app_path("Http/Controllers/{$controllerName}.php");
        $controllerContent = <<<EOT
<?php

namespace App\Http\Controllers;

use App\Services\\{$name}Service;
use Illuminate\Http\Request;

class {$controllerName} extends Controller
{
    protected {$name}Service \${$nameLower}Service;

    public function __construct({$name}Service \${$nameLower}Service)
    {
        \$this->{$nameLower}Service = \${$nameLower}Service;
    }

    public function index()
    {
        \$data = \$this->{$nameLower}Service->getAll();
        return response()->json(\$data);
    }

    public function store(Request \$request)
    {
        \$data = \$this->{$nameLower}Service->create(\$request->all());
        return response()->json(\$data, 201);
    }

    public function show(\$id)
    {
        \$data = \$this->{$nameLower}Service->findById(\$id);
        return response()->json(\$data);
    }

    public function update(Request \$request, \$id)
    {
        \$data = \$this->{$nameLower}Service->update(\$id, \$request->all());
        return response()->json(\$data);
    }

    public function destroy(\$id)
    {
        \$this->{$nameLower}Service->delete(\$id);
        return response()->json(['message' => '{$name} eliminado']);
    }
}
EOT;
        file_put_contents($controllerPath, $controllerContent);
        $this->info("Controlador {$controllerName} generado exitosamente.");

        // 3. Generar la interfaz, repositorio y servicio.
        $interfacePath = app_path("Repositories/Contracts/{$name}RepositoryInterface.php");
        $repositoryPath = app_path("Repositories/{$name}Repository.php");
        $servicePath = app_path("Services/{$name}Service.php");

        if (!is_dir(app_path('Repositories/Contracts'))) {
            mkdir(app_path('Repositories/Contracts'), 0755, true);
        }
        if (!is_dir(app_path('Repositories'))) {
            mkdir(app_path('Repositories'), 0755, true);
        }
        if (!is_dir(app_path('Services'))) {
            mkdir(app_path('Services'), 0755, true);
        }

        if (file_exists($interfacePath) || file_exists($repositoryPath) || file_exists($servicePath)) {
            $this->error('Algunos de los archivos de repositorio/servicio ya existen. Abortando la generación.');
            return;
        }

        $interfaceContent = <<<EOT
<?php

namespace App\Repositories\Contracts;

interface {$name}RepositoryInterface
{
    public function getAll();
    public function findById(int \$id);
    public function create(array \$data);
    public function update(int \$id, array \$data);
    public function delete(int \$id);
}
EOT;

        $repositoryContent = <<<EOT
<?php

namespace App\Repositories;

use App\Repositories\Contracts\\{$name}RepositoryInterface;
use App\Models\\{$name};

class {$name}Repository implements {$name}RepositoryInterface
{
    public function getAll()
    {
        return {$name}::all();
    }

    public function findById(int \$id)
    {
        return {$name}::findOrFail(\$id);
    }

    public function create(array \$data)
    {
        return {$name}::create(\$data);
    }

    public function update(int \$id, array \$data)
    {
        \$model = {$name}::findOrFail(\$id);
        \$model->update(\$data);
        return \$model;
    }

    public function delete(int \$id): void
    {
        \$model = {$name}::findOrFail(\$id);
        \$model->delete();
    }
}
EOT;

        $serviceContent = <<<EOT
<?php

namespace App\Services;

use App\Repositories\Contracts\\{$name}RepositoryInterface;

class {$name}Service
{
    private {$name}RepositoryInterface \$repository;

    public function __construct({$name}RepositoryInterface \$repository)
    {
        \$this->repository = \$repository;
    }

    public function getAll()
    {
        return \$this->repository->getAll();
    }

    public function findById(int \$id)
    {
        return \$this->repository->findById(\$id);
    }

    public function create(array \$data)
    {
        return \$this->repository->create(\$data);
    }

    public function update(int \$id, array \$data)
    {
        return \$this->repository->update(\$id, \$data);
    }

    public function delete(int \$id): void
    {
        \$this->repository->delete(\$id);
    }
}
EOT;

        file_put_contents($interfacePath, $interfaceContent);
        file_put_contents($repositoryPath, $repositoryContent);
        file_put_contents($servicePath, $serviceContent);

        $this->info("Scaffold generado para la entidad {$name} exitosamente.");

        // 4. Actualizar AppServiceProvider para registrar la vinculación de la interfaz con el repositorio.
        $providerPath = app_path("Providers/AppServiceProvider.php");
        $providerContent = file_get_contents($providerPath);
        $binding = "\$this->app->bind(\\App\\Repositories\\Contracts\\{$name}RepositoryInterface::class, \\App\\Repositories\\{$name}Repository::class);";

        if (strpos($providerContent, $binding) === false) {
            $providerContent = preg_replace(
                '/(public function register\(\): void\s*\{\s*)/',
                "$1\n        {$binding}\n",
                $providerContent
            );
            file_put_contents($providerPath, $providerContent);
            $this->info("Se ha registrado la vinculación en AppServiceProvider.");
        } else {
            $this->info("La vinculación ya existe en AppServiceProvider.");
        }
    }
}
