<?php
/**
 * @author Mark
 */

namespace Minds\Core\Queue\Runners;

use Minds\Core\Events\Dispatcher;
use Minds\Core\Queue;
use Minds\Core\Queue\Interfaces;
use Minds\Core\Di\Di;

class BlockchainTransactions implements Interfaces\QueueRunner
{
    public function run()
    {
        $client = Queue\Client::Build();
        $client->setQueue("BlockchainTransactions")
            ->receive(function ($msg) {
                echo "Received a new blockchain transaction \n";

                $data = $msg->getData();

                $manager = Di::_()->get('Blockchain\Transactions\Manager');
                $manager
                    ->setUserGuid($data['user_guid'])
                    ->setTimestamp($data['timestamp'])
                    ->setWalletAddress($data['wallet_address'])
                    ->setTx($data['tx'])
                    ->run();
            });
    }

}
