<?php

namespace ConcatJs;

class ConcatJs
{
    protected $wpScripts;
    protected $scripts = array();
    protected $unqueue = array(); // these are scripts that are not going to be queued but can be dependencies
    protected $queue = array(); // these are scripts that are going to be queued but can be dependencies
    protected $deps = array();
    protected $file;

    function __construct()
    {
        global $wp_scripts;
        $this->wpScripts = $wp_scripts;
        $this->unqueue = $this->getUnqueued();
        $this->deps = $this->getDepsNum();
        $this->scripts = $this->getScripts();
        $this->queue = $this->buildQueue();
        $this->file = new File();

        $this->dequeueScripts();
        $this->printScript();
    }

    function getUnqueued()
    {
        foreach ($this->wpScripts->queue as $singleScript) {
            if (!isset($this->wpScripts->registered[$singleScript]->extra['group'])) {
                $unqueue[] = $singleScript;
            }
        }
        return $unqueue;

    }

    function getDepsNum() {
        foreach ($this->wpScripts->queue as $singleScript) {
            $script = $this->wpScripts->registered[$singleScript];
            if (!isset($script->extra['group'])) {
                continue;
            }

            $deps[] = count($script->deps);
        }
        return $deps;
    }

    function getScripts()
    {
        foreach ($this->wpScripts->queue as $singleScript) {
            $script = $this->wpScripts->registered[$singleScript];
            if (!isset($script->extra['group'])) {
                continue;
            }

            $scripts[$script->handle] = $script;
        }

        array_multisort($this->deps, SORT_ASC, $scripts);

        return $scripts;
    }

    function buildQueue()
    {
        $maxDep = max($this->deps);

        foreach ($this->scripts as $script) {
            if (!isset($script->extra['group'])) {
                continue;
            }

            if (empty($script->deps)) {
                $queue[] = $script->handle;
            }


            for ($i=1; $i < $maxDep + 1; $i++) {
                if (count($script->deps) <= $i) {
                    foreach ($script->deps as $dep) {
                        if ((in_array($dep, $queue) || (in_array($dep, $this->unqueue)) && !in_array($dep, $queue))) {
                            $queue[] = $script->handle;
                        } else {
                        }
                    }
                }
            }
        }


        return $queue;
    }

    function concatScripts()
    {
        $oneScript = '';

        foreach ($this->queue as $queuedItem) {
            $script = $this->scripts[$queuedItem];
            wp_dequeue_script($script->handle);
            if (isset($script->extra['data'])) {
                $oneScript .= $script->extra['data'] . ' ';
            }
            $oneScript .= file_get_contents($script->src);
        }

        return $oneScript;
    }

    function dequeueScripts()
    {
        foreach ($this->queue as $queuedItem) {
            wp_dequeue_script($this->scripts[$queuedItem]->handle);
        }
    }

    function scriptTag()
    {
        $content = $this->concatScripts();
        echo '<script>' . $content . '</script>';
    }

    function printScript()
    {
        if (!file_exists($this->file->path())) {
            $this->writeScript();
        }

        add_action('wp_footer', function() {
            echo sprintf('<script src="%s"></script>', $this->file->url());
        });
    }

    function writeScript()
    {
        file_put_contents($this->file->path(), $this->concatScripts());
    }

}
