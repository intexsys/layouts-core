<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsBundle\Command;

use Netgen\BlockManager\Exception\RuntimeException;
use Netgen\BlockManager\Transfer\Input\ImporterInterface;
use Netgen\BlockManager\Transfer\Input\Result\ErrorResult;
use Netgen\BlockManager\Transfer\Input\Result\SuccessResult;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Command to import Netgen Layouts entities.
 */
final class ImportCommand extends Command
{
    /**
     * @var \Netgen\BlockManager\Transfer\Input\ImporterInterface
     */
    private $importer;

    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $io;

    public function __construct(ImporterInterface $importer)
    {
        $this->importer = $importer;

        // Parent constructor call is mandatory in commands registered as services
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Imports Netgen Layouts entities')
            ->addArgument('file', InputArgument::REQUIRED, 'JSON file to import')
            ->setHelp('The command <info>%command.name%</info> imports Netgen Layouts entities.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->io = new SymfonyStyle($input, $output);

        $file = $input->getArgument('file');
        if (!is_string($file) || !file_exists($file)) {
            throw new RuntimeException('Provided file does not exist.');
        }

        $errorCount = $this->importData((string) file_get_contents($file));

        $errorCount > 0 ?
            $this->io->caution('Import completed with errors.') :
            $this->io->success('Import completed successfully.');

        return 0;
    }

    /**
     * Import new entities from the given data and returns the error count.
     */
    private function importData(string $data): int
    {
        $errorCount = 0;

        foreach ($this->importer->importData($data) as $index => $result) {
            if ($result instanceof SuccessResult) {
                $this->io->note(
                    sprintf(
                        'Imported %1$s #%2$d into %1$s ID %3$d',
                        $result->getEntityType(),
                        $index + 1,
                        $result->getEntityId()
                    )
                );

                continue;
            }

            if ($result instanceof ErrorResult) {
                $this->io->error(sprintf('Could not import %s #%d', $result->getEntityType(), $index + 1));
                $this->io->section('Error stack:');
                $this->renderThrowableStack($result->getError());
                $this->io->newLine();

                ++$errorCount;

                continue;
            }
        }

        return $errorCount;
    }

    /**
     * Renders all stacked exception messages for the given $throwable.
     */
    private function renderThrowableStack(Throwable $throwable, int $number = 0): void
    {
        $this->io->writeln(sprintf(' #%d:', $number));
        $throwableClass = get_class($throwable);
        $this->io->writeln(sprintf('  - <comment>exception:</comment> %s', $throwableClass));
        $this->io->writeln(sprintf('  - <comment>file:</comment> <info>%s</info>', $throwable->getFile()));
        $this->io->writeln(sprintf('  - <comment>line:</comment> %d', $throwable->getLine()));
        $this->io->writeln(sprintf('  - <comment>message:</comment> %s', $throwable->getMessage()));

        $previous = $throwable->getPrevious();

        if ($previous !== null) {
            $this->renderThrowableStack($previous, $number + 1);
        }
    }
}
