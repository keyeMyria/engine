<?php

namespace Spec\Minds\Core\Blockchain\Transactions;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Minds\Core\Data\Cassandra\Client;
use Cassandra\Timestamp;
use Cassandra\Varint;
use Minds\Core\Blockchain\Transactions\Transaction;
use Spec\Minds\Mocks;

class RepositorySpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldHaveType('Minds\Core\Blockchain\Transactions\Repository');
    }

    function it_should_add_transactions(Client $db)
    {
        $this->beConstructedWith($db);

        $db->batchRequest(Argument::that(function($requests) {
            return $requests[0]['values'][0] == new Varint(123)
                && $requests[0]['values'][1] == '0xWALLETADDR'
                && $requests[0]['values'][2] == new Timestamp(time())
                && $requests[0]['values'][3] == '0xtid'
                && $requests[0]['values'][4] == 'spec'
                && $requests[0]['values'][5] == new Varint(50)
                && $requests[0]['values'][6] == false
                && $requests[0]['values'][7] == json_encode([ 'foo' => 'bar']);
            }), 1)
            ->shouldBeCalled();

        $transaction = new Transaction;
        $transaction
            ->setUserGuid(123)
            ->setWalletAddress('0xWALLETADDR')
            ->setTimestamp(time())
            ->setTx('0xtid')
            ->setContract('spec')
            ->setAmount(50)
            ->setData([
                'foo' => 'bar'
            ]);

        $this->add($transaction);
    }

    function it_should_get_a_list_of_transaction(Client $db)
    {
        $this->beConstructedWith($db);

        $db->request(Argument::that(function($transactions) {
            return true;
        }))
            ->willReturn(new Mocks\Cassandra\Rows([
                [
                    'user_guid' => 123,
                    'wallet_address' => '0xWALLETADDR',
                    'timestamp' => new Timestamp(time()),
                    'tx' => '0xtid',
                    'contract' => 'spec',
                    'amount' => 50,                    
                    'completed' => true,
                    'data' => json_encode([ 'foo' => 'bar' ])
                ]
            ], ''));

        $result = $this->getList([
                'user_guid' => 123
            ]);
  
        $result['transactions'][0]
            ->getUserGuid()
            ->shouldBe(123);
        
        $result['transactions'][0]
            ->getWalletAddress()
            ->shouldBe('0xWALLETADDR');

        $result['transactions'][0]
            ->getTx()
            ->shouldBe('0xtid');

        $result['transactions'][0]
            ->getContract()
            ->shouldBe('spec');

        $result['transactions'][0]
            ->getAmount()
            ->shouldBe('50');
        
        $result['transactions'][0]
            ->isCompleted()
            ->shouldBe(true);
        
        $result['transactions'][0]
            ->getData()
            ->shouldBe([ 'foo' => 'bar' ]);
    }

    function it_should_get_a_list_of_transaction_with_multiple_addresses(Client $db)
    {
        $this->beConstructedWith($db);

        $db->request(Argument::that(function($query) {
            $values = $query->build()['values'];
            $string = $query->build()['string'];
            return $string == "SELECT * from blockchain_transactions WHERE user_guid = ? AND wallet_address IN (?, ?)"
                && $values[0] == new Varint(123)
                && $values[1] == 'offchain'
                && $values[2] == '0xWALLETADDR';
        }))->shouldBeCalled();

        $result = $this->getList([
            'user_guid' => 123,
            'wallet_addresses' => [
                'offchain',
                '0xWALLETADDR'
            ]
        ]);
    }

    function it_should_get_a_single_transaction(Client $db)
    {
        $this->beConstructedWith($db);

        $db->request(Argument::that(function($query) {
                $values = $query->build()['values'];
                return $values[1] == new Varint(123)
                    && $values[0] == '0xtid';
            }))
            ->willReturn(new Mocks\Cassandra\Rows([
                [
                    'user_guid' => 123,
                    'wallet_address' => '0xWALLETADDR',
                    'timestamp' => new Timestamp(time()),
                    'tx' => '0xtid',
                    'contract' => 'spec',
                    'amount' => 50,                    
                    'completed' => true,
                    'data' => json_encode([ 'foo' => 'bar' ])
                ]
            ], ''));

        $result = $this->get(123, '0xtid');
        
        $result
            ->getUserGuid()
            ->shouldBe(123);
        
        $result
            ->getWalletAddress()
            ->shouldBe('0xWALLETADDR');

        $result
            ->getTx()
            ->shouldBe('0xtid');

        $result
            ->getContract()
            ->shouldBe('spec');

        $result
            ->getAmount()
            ->shouldBe('50');
        
        $result
            ->isCompleted()
            ->shouldBe(true);
        
        $result
            ->getData()
            ->shouldBe([ 'foo' => 'bar' ]);
    }

}
