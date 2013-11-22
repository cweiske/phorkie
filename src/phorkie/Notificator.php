<?php
namespace phorkie;

/**
 * Send out webhook callbacks when something happens
 */
class Notificator
{
    protected $notificators = array();

    public function __construct()
    {
        $this->loadNotificators();
    }

    protected function loadNotificators()
    {
        foreach ($GLOBALS['phorkie']['cfg']['notificator'] as $type => $config) {
            $class = '\\phorkie\\Notificator_' . ucfirst($type);
            $this->notificators[] = new $class($config);
        }
    }

    /**
     * A repository has been created
     */
    public function create(Repository $repo)
    {
        $this->send('create', $repo);
    }

    /**
     * A repository has been modified
     */
    public function edit(Repository $repo)
    {
        $this->send('edit', $repo);
    }

    /**
     * A repository has been deleted
     */
    public function delete(Repository $repo)
    {
        $this->send('delete', $repo);
    }

    /**
     * Call all notificator plugins
     */
    protected function send($event, Repository $repo)
    {
        foreach ($this->notificators as $notificator) {
            $notificator->send($event, $repo);
        }
    }
}
?>
