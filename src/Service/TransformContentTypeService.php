<?php

namespace EMS\CoreBundle\Service;

use EMS\CommonBundle\Helper\EmsFields;
use EMS\CommonBundle\Search\Search;
use EMS\CommonBundle\Service\ElasticaService;
use EMS\CoreBundle\ContentTransformer\ContentTransformContext;
use EMS\CoreBundle\ContentTransformer\ContentTransformInterface;
use EMS\CoreBundle\Entity\ContentType;
use EMS\CoreBundle\Entity\DataField;
use EMS\CoreBundle\Form\DataField\DataFieldType;
use EMS\CoreBundle\Form\Form\RevisionType;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;

class TransformContentTypeService
{
    /** @var int */
    const DEFAULT_SCROLL_SIZE = 100;

    /** @var LoggerInterface */
    private $logger;
    /** @var ContentTypeService */
    private $contentTypeService;
    /** @var DataService */
    private $dataService;
    /** @var FormFactoryInterface */
    private $formFactory;
    /** @var ElasticaService */
    private $elasticaService;

    public function __construct(
        LoggerInterface $logger,
        ElasticaService $elasticaService,
        ContentTypeService $contentTypeService,
        DataService $dataService,
        FormFactoryInterface $formFactory
    ) {
        $this->logger = $logger;
        $this->elasticaService = $elasticaService;
        $this->contentTypeService = $contentTypeService;
        $this->dataService = $dataService;
        $this->formFactory = $formFactory;
    }

    public function transform(ContentType $contentType, string $user): \Generator
    {
        $search = $this->getSearch($contentType);
        $scroll = $this->elasticaService->scroll($search, '10m');

        foreach ($scroll as $resultSet) {
            foreach ($resultSet as $result) {
                if (false === $result) {
                    continue;
                }
                $isChanged = false;
                $ouuid = $result->getId();
                $revision = $this->dataService->getNewestRevision($contentType->getName(), $ouuid);

                if ($revision->getDraft()) {
                    $this->logger->warning('service.data.transform_content_type.cant_process_draft', [
                        EmsFields::LOG_REVISION_ID_FIELD => $revision->getId(),
                        EmsFields::LOG_CONTENTTYPE_FIELD => $contentType->getName(),
                        EmsFields::LOG_ENVIRONMENT_FIELD => $contentType->getEnvironment()->getName(),
                        EmsFields::LOG_OUUID_FIELD => $revision->getOuuid(),
                    ]);
                    yield $revision;
                    continue;
                }

                $revisionType = $this->formFactory->create(RevisionType::class, $revision);
                $result = $this->dataService->walkRecursive($revisionType->get('data'), $result->getSource(), function (string $name, $data, DataFieldType $dataFieldType, DataField $dataField) use (&$isChanged) {
                    if (null === $data) {
                        return [];
                    }
                    if ($dataFieldType->isVirtual()) {
                        return $data;
                    }

                    $transformer = $this->getTransformer($dataField);
                    $contentTransformContext = ContentTransformContext::fromDataFieldType(\get_class($dataFieldType), $data);
                    if (!empty($transformer) && $transformer->canTransform($contentTransformContext)) {
                        $dataTransformed = $transformer->transform($contentTransformContext);
                        $contentTransformContext->setTransformedData($dataTransformed);
                        if ($transformer->hasChanges($contentTransformContext)) {
                            $isChanged = true;

                            return [$name => $dataTransformed];
                        }
                    }

                    return [$name => $data];
                });

                if (!$isChanged) {
                    yield $revision;
                    continue;
                }

                try {
                    $revision = $this->dataService->initNewDraft($contentType->getName(), $ouuid, null, $user);
                    $revision->setRawData($result);
                    $this->dataService->finalizeDraft($revision, $revisionType, $user);
                } catch (Exception $e) {
                    $this->logger->error('service.data.transform_content_tyoe.errer_on_save', [
                        EmsFields::LOG_REVISION_ID_FIELD => $revision->getId(),
                        EmsFields::LOG_CONTENTTYPE_FIELD => $contentType->getName(),
                        EmsFields::LOG_ENVIRONMENT_FIELD => $contentType->getEnvironment()->getName(),
                        EmsFields::LOG_OUUID_FIELD => $revision->getOuuid(),
                        EmsFields::LOG_ERROR_MESSAGE_FIELD => $e->getMessage(),
                        EmsFields::LOG_EXCEPTION_FIELD => $e,
                    ]);
                }
                yield $revision;
            }
        }
    }

    private function getTransformer(DataField $dataField): ?ContentTransformInterface
    {
        $transformerClass = $dataField->getFieldType()->getMigrationgOption('transformer');
        if (null === $transformerClass) {
            return null;
        }

        return new $transformerClass();
    }

    public function getTotal(ContentType $contentType): int
    {
        $search = $this->getSearch($contentType);

        return $this->elasticaService->count($search);
    }

    private function getSearch(ContentType $contentType): Search
    {
        $query = $this->elasticaService->filterByContentTypes(null, [$contentType->getName()]);
        $search = new Search([$this->contentTypeService->getIndex($contentType)], $query);
        $search->setSize(self::DEFAULT_SCROLL_SIZE);

        return $search;
    }
}
