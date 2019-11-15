<?php

namespace RobM\Templating;

use Symfony\Component\Templating\DelegatingEngine;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\StreamingEngineInterface;

class Templating implements EngineInterface, StreamingEngineInterface
{
    /** @var DelegatingEngine */
    private $delegatingEngine;

    public function __construct(DelegatingEngine $delegatingEngine, EngineInterface ...$engines)
    {
        foreach ($engines as $engine) {
            $delegatingEngine->addEngine($engine);
        }

        $this->delegatingEngine = $delegatingEngine;
    }

    /**
     * @inheritDoc
     */
    public function render($name, array $parameters = []): void
    {
        echo $this->delegatingEngine->render($name, $parameters);
    }

    /**
     * @inheritDoc
     */
    public function exists($name): bool
    {
        return $this->delegatingEngine->exists($name);
    }

    /**
     * @inheritDoc
     */
    public function supports($name)
    {
        return $this->delegatingEngine->supports($name);
    }

    /**
     * @inheritDoc
     */
    public function stream($name, array $parameters = []): void
    {
        return $this->delegatingEngine->stream($name, $parameters);
    }
}
