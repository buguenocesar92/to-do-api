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
    protected $signature = 'make:scaffold {name : El nombre del modelo (ej. Task)}';

    /**
     * La descripción del comando.
     *
     * @var string
     */
    protected $description = 'Genera automáticamente la interfaz, el repositorio y el servicio para un modelo dado';

    /**
     * Ejecuta el comando.
     */
    public function handle()
    {
        $name = Str::studly($this->argument('name'));

        // Rutas de los archivos a generar.
        $interfacePath = app_path("Repositories/Contracts/{$name}RepositoryInterface.php");
        $repositoryPath = app_path("Repositories/{$name}Repository.php");
        $servicePath = app_path("Services/{$name}Service.php");

        // Crear directorios si no existen.
        if (!is_dir(app_path('Repositories/Contracts'))) {
            mkdir(app_path('Repositories/Contracts'), 0755, true);
        }
        if (!is_dir(app_path('Repositories'))) {
            mkdir(app_path('Repositories'), 0755, true);
        }
        if (!is_dir(app_path('Services'))) {
            mkdir(app_path('Services'), 0755, true);
        }

        // Verificar si los archivos ya existen.
        if (file_exists($interfacePath) || file_exists($repositoryPath) || file_exists($servicePath)) {
            $this->error('Algunos de los archivos ya existen. Aborta la generación.');
            return;
        }

        // Contenido de la interfaz.
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

        // Contenido del repositorio.
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

        // Contenido del servicio.
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

        // Escribir los archivos.
        file_put_contents($interfacePath, $interfaceContent);
        file_put_contents($repositoryPath, $repositoryContent);
        file_put_contents($servicePath, $serviceContent);

        $this->info("Scaffold generado para el modelo {$name} exitosamente.");
    }
}
