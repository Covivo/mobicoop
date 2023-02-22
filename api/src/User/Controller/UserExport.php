<?php

namespace App\User\Controller;

use App\User\Admin\Service\ExportManager;

class UserExport
{
    /**
     * @var ExportManager
     */
    private $_exportManager;

    public function __construct(ExportManager $exportManager)
    {
        $this->_exportManager = $exportManager;
    }

    public function __invoke()
    {
        return $this->_exportManager->exportAll();
    }
}
