<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:make-controllers',
    description: 'Add a short description for your command',
)]
class MakeControllersCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('number', InputArgument::OPTIONAL, 'Argument description', 100)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $num = $input->getArgument('number');

        $filesystem = new Filesystem();

        for ($i = 0; $i < $num; $i++) {
            $filesystem->remove("src/Controller/".'Foo'.($i % 10),);
        }

        for ($i = 0; $i < $num; $i++) {
            $io->writeln("Creating controller $i");
            $pathComponents = [
                'Foo'.($i % 10),
                'Bar'.($i % 20),
                'Baz'.$i,
            ];
            $path = implode('/', $pathComponents);
            $last = $pathComponents[count($pathComponents) - 1].'Controller';
            $filesystem->dumpFile("src/Controller/$path/$last.php", $this->getController($pathComponents));
        }
        return Command::SUCCESS;
    }

    private function getController(array $pathComponents)
    {
        $lowerPathComponents = array_map('strtolower', $pathComponents);
        $path = implode('/', $lowerPathComponents).'/{id}';
        $pathName = implode('_', $lowerPathComponents);
        $className = $pathComponents[count($pathComponents) - 1].'Controller';
        $namespace = implode('\\', $pathComponents);
        return <<<EOF
<?php

namespace App\Controller\\$namespace;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class $className extends AbstractController
{
    /**
     * @Route(path="/$path", name="$pathName", methods={"GET"}, requirements = {"id" = "\d+"})
     */
    # [Route('/$path', name: '$pathName', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function index(\$id): JsonResponse
    {
        return \$this->json(['class' => 'App\Controller\\$namespace\\$className', 'id' => \$id]);
    }
}
EOF;

    }
}
