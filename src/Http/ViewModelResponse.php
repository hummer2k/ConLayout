<?php

declare(strict_types=1);

namespace ConLayout\Http;

use Laminas\Diactoros\Response;
use Laminas\View\Model\ModelInterface;

class ViewModelResponse extends Response
{
    public function __construct(
        private ModelInterface $viewModel,
        int $status = 200,
        array $headers = []
    ) {
        parent::__construct('php://memory', $status, $headers);
    }

    public function getViewModel(): ModelInterface
    {
        return $this->viewModel;
    }
}
