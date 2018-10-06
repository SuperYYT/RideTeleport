<?php

namespace ridetp\language;

class LanguageManager
{

    /**
     * 継承
     */
    public function __construct($owner, $lang)
    {
        $this->owner = $owner;
        $this->lang = $lang;
    }


    /**
     * 文章取得
     * @param String $key
     * @param array  $params
     */
    public function get($key, $params=null)
    {
        if (!isset($this->lang)) return;
        if (!isset($this->lang[$key])) {
            $text = $this->lang["notfound"];
        } else {
            $text = $this->lang[$key];
        }

        if (is_string($text)) {
            $content = $text;
        }

        if (is_array($text)) {
            $content = implode("\n", $text);
        }

        if ($params == null) {
            return $content;
        } else {
            $search = ["%0", "%1", "%2", "%3"];
            $txt = str_replace($search, $params, $content);
            return $txt;
        }
    }
}