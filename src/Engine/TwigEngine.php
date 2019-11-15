<?php

namespace RobM\Templating\Engine;

use InvalidArgumentException;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\StreamingEngineInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Throwable;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ExistsLoaderInterface;
use Twig\Loader\SourceContextLoaderInterface;
use Twig\Template;

class TwigEngine implements EngineInterface, StreamingEngineInterface
{
    /** @var Environment */
    protected $environment;

    /** @var TemplateNameParserInterface */
    protected $parser;

    public function __construct(Environment $environment, TemplateNameParserInterface $parser)
    {
        $this->environment = $environment;
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     *
     * It also supports Template as name parameter.
     *
     * @throws Throwable
     */
    public function render($name, array $parameters = [])
    {
        return $this->load($name)->render($parameters);
    }

    /**
     * {@inheritdoc}
     *
     * It also supports Template as name parameter.
     */
    public function stream($name, array $parameters = [])
    {
        $this->load($name)->display($parameters);
    }

    /**
     * {@inheritdoc}
     *
     * It also supports Template as name parameter.
     */
    public function exists($name)
    {
        if ($name instanceof Template) {
            return true;
        }

        $loader = $this->environment->getLoader();

        if (1 === Environment::MAJOR_VERSION && ! $loader instanceof ExistsLoaderInterface) {
            try {
                // cast possible TemplateReferenceInterface to string because the
                // EngineInterface supports them but LoaderInterface does not
                if ($loader instanceof SourceContextLoaderInterface) {
                    $loader->getSourceContext((string)$name);
                } else {
                    $loader->getSource((string)$name);
                }

                return true;
            } catch (LoaderError $e) {
            }

            return false;
        }

        return $loader->exists((string)$name);
    }

    /**
     * {@inheritdoc}
     *
     * It also supports Template as name parameter.
     */
    public function supports($name)
    {
        if ($name instanceof Template) {
            return true;
        }

        $template = $this->parser->parse($name);

        return 'twig' === $template->get('engine');
    }

    /**
     * Loads the given template.
     *
     * @param string|TemplateReferenceInterface|Template $name A template name or an instance of
     *                                                         TemplateReferenceInterface or Template
     *
     * @return Template
     *
     * @throws RuntimeError
     * @throws SyntaxError
     */
    protected function load($name): ?Template
    {
        if ($name instanceof Template) {
            return $name;
        }

        try {
            return $this->environment->loadTemplate((string)$name);
        } catch (LoaderError $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
