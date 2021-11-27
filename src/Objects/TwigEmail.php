<?php
namespace CarloNicora\Minimalism\Services\TwigMailer\Objects;

use CarloNicora\Minimalism\Interfaces\Mailer\Objects\Email;
use Exception;
use RuntimeException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;

class TwigEmail extends Email
{
    /** @var array|null  */
    private ?array $templateDirectory=null;

    /** @var string|null  */
    private ?string $templateName=null;

    /** @var Environment|null  */
    private ?Environment $template=null;

    /** @var array  */
    private array $parameters=[];

    /**
     * email constructor.
     * @param array $templateDirectory
     */
    public function addTemplateDirectory(
        array $templateDirectory,
    ): void
    {
        $this->templateDirectory = $templateDirectory;
    }

    /**
     * @param string $template
     */
    public function addTemplate(
        string $template,
    ): void
    {
        $this->templateName = 'email.twig';

        $arrayLoader = new ArrayLoader([
            $this->templateName => $template
        ]);

        if ($this->templateDirectory !== null){
            $filesystemLoader = new FilesystemLoader($this->templateDirectory);
            $loader = new ChainLoader([$arrayLoader, $filesystemLoader]);
        } else {
            $loader = new ChainLoader([$arrayLoader]);
        }

        $this->template = new Environment($loader);
    }

    /**
     * @param string $templateName
     * @throws Exception
     */
    public function addTemplateFile(
        string $templateName,
    ): void
    {
        if ($this->templateDirectory === null) {
            throw new RuntimeException('No configured email template directory', 500);
        }

        $this->templateName = $templateName;

        $loader = new FilesystemLoader($this->templateDirectory);

        $this->template = new Environment($loader);
    }

    /**
     * @param array $parameters
     * @throws Exception
     */
    public function setParameters(
        array $parameters,
    ): void
    {
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        try {
            return $this->template->render($this->templateName, $this->parameters);
        } catch (LoaderError | RuntimeError | SyntaxError) {
            throw new RuntimeException('Error creating the email content', 500);
        }
    }
}