<?php

declare(strict_types=1);

namespace Netgen\Bundle\LayoutsAdminBundle\Serializer\Normalizer;

use Exception;
use Symfony\Component\Debug\Exception\FlattenException as DebugFlattenException;
use Symfony\Component\ErrorHandler\Exception\FlattenException as ErrorHandlerFlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ExceptionNormalizer implements NormalizerInterface
{
    /**
     * @var bool
     */
    private $outputDebugInfo;

    public function __construct(bool $outputDebugInfo)
    {
        $this->outputDebugInfo = $outputDebugInfo;
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        $data = [
            'code' => $object->getCode(),
            'message' => $object->getMessage(),
        ];

        if ($object instanceof HttpExceptionInterface) {
            $statusCode = $object->getStatusCode();
            if (isset(Response::$statusTexts[$statusCode])) {
                $data['status_code'] = $statusCode;
                $data['status_text'] = Response::$statusTexts[$statusCode];
            }
        }

        if ($this->outputDebugInfo) {
            $debugException = $object->getPrevious() ?? $object;
            if (class_exists(ErrorHandlerFlattenException::class)) {
                $debugException = ErrorHandlerFlattenException::createFromThrowable($debugException);
            } elseif ($debugException instanceof Exception && class_exists(DebugFlattenException::class)) {
                $debugException = DebugFlattenException::create($debugException);
            }

            $data['debug'] = [
                'file' => $debugException->getFile(),
                'line' => $debugException->getLine(),
                'trace' => $debugException->getTrace(),
            ];
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Exception;
    }
}
