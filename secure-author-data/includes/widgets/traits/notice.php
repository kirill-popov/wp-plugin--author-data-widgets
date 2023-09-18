<?php

namespace Secure_Author_Data_Plugin\Widgets\Notices;

trait Notice {
    private $notice_id_suffix = '_msg';

    private function getID() {
        return $this->id . $this->notice_id_suffix;
    }

    public function setErrorNotice($text) {
        $msg = $this->makeNotice('error', $text);
        set_transient($this->getID(), $msg);
    }

    public function setSuccessNotice( $text) {
        $msg = $this->makeNotice('success', $text);
        set_transient($this->getID(), $msg);
    }

    public function getNotice(): string
    {
        $id = $this->getID();
        $notice = get_transient($id);
        delete_transient($id);
        return $notice ?? '';
    }

    private function makeNotice($type, $text) {
        $tmplt = '<div class="notice %class">%text</div>';
        return str_replace(['%class', '%text'], ['notice-'.$type, $text], $tmplt);
    }
}