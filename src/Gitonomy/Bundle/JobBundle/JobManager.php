<?php

/**
 * This file is part of Gitonomy.
 *
 * (c) Alexandre Salomé <alexandre.salome@gmail.com>
 * (c) Julien DIDIER <genzo.wm@gmail.com>
 *
 * This source file is subject to the GPL license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Gitonomy\Bundle\JobBundle;

use Gitonomy\Bundle\JobBundle\Hydrator\JobHydrator;
use Gitonomy\Bundle\JobBundle\Job\Job;
use Gitonomy\Bundle\JobBundle\Storage\StorageInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JobManager
{
    private $storage;
    private $hydrator;

    public function __construct(JobHydrator $hydrator, StorageInterface $storage)
    {
        $this->hydrator = $hydrator;
        $this->storage  = $storage;
    }

    public function delegate(Job $job)
    {
        $name = $this->hydrator->getName(get_class($job));
        $id = $this->storage->store($name, $job->getParameters());

        $job->setId($id);

        return $job;
    }

    public function getStatus($id)
    {
        return $this->storage->getStatus($id);
    }

    public function runBackground(OutputInterface $output, $pollInterval = 5, $iterations = 100)
    {
        $output->writeln(sprintf('<comment>Job manager started (poll every %s seconds, %s iterations)</comment>', $pollInterval, $iterations));

        while ($iterations > 0) {
            $iterations--;
            $row = $this->storage->find();

            if (!$row) {
                $output->writeln(sprintf('<comment>- found no job to process... (%s iterations left)', $iterations));
                sleep($pollInterval);

                continue;
            }

            $job = $this->hydrator->hydrateJob($row[1], $row[2]);
            $job->setId($row[0]);

            try {
                $output->writeln(sprintf('- executing job <info>#%s</info> (%s iterations left)...', $job->getId(), $iterations));
                $res = $job->execute();
                $this->storage->finish($job->getId(), true, $res);
                $output->writeln('  <info>OK</info> - job succeeded');
            } catch (\Exception $e) {
                $this->storage->finish($job->getId(), false, $e->getMessage());
                $output->writeln(sprintf('  <error>KO</error> - error: %s', $e->getMessage()));
            }
        }

        $output->writeln('<comment>Job manager job finished</comment>');
    }
}
