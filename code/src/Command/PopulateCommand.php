<?php
namespace App\Command;

use App\Entity\Joke;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PopulateCommand
 * @package App\Command
 */
class PopulateCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:populate';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * PopulateCommand constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    /**
     *
     */
    protected function configure()
    {
        $this->addArgument(
            'number',
            InputArgument::OPTIONAL,
            'Number of random jokes to retrieve',
            10);
        $this->setDescription("Populate DB with n number of Jokes")
             ->setHelp("Populate DB with n number of random jokes from the website https://icanhazdadjoke.com");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $num = $input->getArgument('number');
        $output->writeln([
            "Retrieving {$num} jokes:",
            "===================="
        ]);
        $client = new Client();
        for ($i = 0; $i < $num; $i++) {
            $response = $client->request('GET', 'https://icanhazdadjoke.com', [
                'headers' => [
                    'User-Agent' => 'BorrowWorksDemo by William Penton',
                    'Accept'     => 'application/json',
                ]
            ]);

            if ($response->getStatusCode() === Response::HTTP_OK) {
                $punchline = json_decode($response->getBody())->joke;
                $em = $this->entityManager;
                $joke = new Joke();
                $joke->setPunchline($punchline);
                $em->persist($joke);
                $em->flush();

                $output->writeln("Joke: " . $punchline);
            } else {
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}