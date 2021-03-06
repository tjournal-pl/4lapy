<?php

namespace FourPaws\Console\Command;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use FourPaws\App\Application;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SendSubscribedOrders
 * Обход подписок и генерация заказов
 *
 * @package FourPaws\Console\Command
  */
class SendSubscribedOrders extends Command
{
    use LazyLoggerAwareTrait;

    const ARG_LIMIT = 'limit';

    public function __construct($name = null)
    {
        parent::__construct($name);
    }
    
    protected function configure()
    {
        $this->setName('fourpaws:orderssubscribe:send');
        $this->setDescription('Обход подписок и генерация заказов');

        $this->addArgument(
            static::ARG_LIMIT,
            InputArgument::OPTIONAL,
            'Лимит обхода подписок. Все по умолчанию.',
            0
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int)$input->getArgument(self::ARG_LIMIT);
        $limit = $limit > 0 ? $limit : 1000;

        $output->writeln('Обход подписок начат: '.date('Y-m-d H:i:s'));
        try {
            /** @var OrderSubscribeService $service */
            $service = Application::getInstance()->getContainer()->get(
                'order_subscribe.service'
            );
            $service->sendOrders($limit);
        } catch (\Exception $exception) {
            $output->writeln('Произошла ошибка: '.$exception->getMessage());

            $this->log()->critical(
                sprintf(
                    '%s exception: %s',
                    __METHOD__,
                    $exception->getMessage()
                )
            );
        }
        $output->writeln('Обход подписок завершен: '.date('Y-m-d H:i:s'));
    }
}
