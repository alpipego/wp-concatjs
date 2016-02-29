<?php

namespace ConcatJs;

class File
{
    protected function fileName()
    {
        return substr(md5($_SERVER['REQUEST_URI']), 0, 8) . '-' . $this->versionTimestamp() . '.js';
    }

    protected function versionTimestamp()
    {
        exec("cd " . realpath(ABSPATH . '..') . " && git rev-list --format=format:'%ct' --max-count=1 `git rev-parse HEAD`", $git);
        return $git[1];
    }

    public function path()
    {
        return trailingslashit(WP_CONTENT_DIR) . 'js/cache/' . $this->fileName();
    }

    public function url()
    {
        return trailingslashit(pi_get_path('js')) . 'cache/' . $this->fileName();
    }
}
