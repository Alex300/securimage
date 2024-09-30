<?php

namespace Securimage\StorageAdapter;

class Session implements AdapterInterface
{
    protected $session_name;

    public function __construct($options = null)
    {
        if (!empty($options) && is_array($options)) {
            if (isset($options['session_name'])) {
                $this->session_name = $options['session_name'];
            }
        }

        $this->bootstrap();
    }

    public function store($captchaId, $captchaInfo)
    {
        if ((function_exists('session_status') && PHP_SESSION_ACTIVE == session_status()) || session_id() != '') {
            $_SESSION['securimage_data'][$captchaId] = serialize($captchaInfo);
            return true;
        }

        return false;
    }

    public function storeAudioData($captchaId, $audioData)
    {
        $info = $this->get($captchaId);
        if ($info === null) {
            return false;
        }

        $info->captchaAudioData = $audioData;
        $this->store($captchaId, $info);
        return true;
    }

    public function get($captchaId, $what = null): ?\Securimage\CaptchaObject
    {
        if (isset($_SESSION['securimage_data'][$captchaId])) {
            $info = unserialize($_SESSION['securimage_data'][$captchaId]);
            if (!($info instanceof \Securimage\CaptchaObject)) {
                return null;
            }
            return $info;
        }

        return null;
    }

    public function delete($captchaId)
    {
        unset($_SESSION['securimage_data'][$captchaId]);

        return true;
    }

    protected function bootstrap()
    {
        if ( session_id() == '' || (function_exists('session_status') && PHP_SESSION_NONE == session_status()) ) {
            // no session has been started yet (or it was previousy closed), which is needed for validation
            if (!is_null($this->session_name) && trim($this->session_name) != '') {
                session_name(trim($this->session_name)); // set session name if provided
            }
            session_start();
        }

        if (!isset($_SESSION['securimage_data'])) {
            $_SESSION['securimage_data'] = [];
        }
    }
}