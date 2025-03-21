<?php

namespace LunaCMS;

class Exception extends \Exception
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        // Détermine le chemin du fichier error.log à la racine du projet
        $logPath = defined('_BASE_PROJECT_') ? _BASE_PROJECT_ . '/error.log' : __DIR__ . '/../../error.log';

        // Format du message avec date
        $logMessage = sprintf(
            "[%s] [%s] %s in %s on line %d\n",
            date('Y-m-d H:i:s'),
            static::class,
            $message,
            $this->getFile(),
            $this->getLine()
        );

        // Log dans error.log à la racine du projet
        error_log($logMessage, 3, $logPath);

        parent::__construct($message, $code, $previous);
    }
}
