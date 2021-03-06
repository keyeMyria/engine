<?php
namespace Minds\Core\Analytics;

use Minds\Core\Analytics\Aggregates\ActionsHistogram;
use Minds\Core\Di\Di;

class Manager
{

    protected $es;
    private $user;
    private $from;
    private $to;
    private $interval = 'day';

    private $actions = [
        'subscribers' => 'subscribe',
        'comments' => 'comment',
        'reminds' => 'remind',
        'votes' => 'vote:up',
        'referrals' => 'referral',
    ];

    public function __construct($client = null)
    {
        $this->es = $client ?: Di::_()->get('Database\ElasticSearch');
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    public function setInterval($interval)
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * Return a batch result of a series of analytic metrics
     * @return array
     */
    public function getCounts()
    {
        $metrics = [];
        foreach($this->actions as $id => $action) {
            $aggregate = new ActionsHistogram($this->es);
            $aggregate
                ->setAction($action)
                ->setTo($this->to)
                ->setFrom($this->from)
                ->setInterval($this->interval);

            if ($this->user) {
                $aggregate->setUser($this->user);           
            }

            $result = $aggregate->get();
            foreach ($result as $period => $count) {
                $metrics[$period][$id] = $count;
            }
        }

        return $metrics;
    }

}
