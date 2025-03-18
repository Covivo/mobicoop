<?php

namespace App\User\Admin\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\User\Admin\Service\ExportManager;
use App\User\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class UserExportCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private const EXPORT_TYPE_PARAMETER = 'exportType';

    private const EXPORT_STANDARD = 0;
    private const EXPORT_EXTENDED = 1;

    private const SERIALISATION_GROUP_STANDARD = 'export:standard';
    private const SERIALISATION_GROUP_EXTENDED = 'export:extended';

    private const SERIALIZATION_GROUPS = [
        self::EXPORT_STANDARD => self::SERIALISATION_GROUP_STANDARD,
        self::EXPORT_EXTENDED => self::SERIALISATION_GROUP_EXTENDED,
    ];

    private $collectionExtensions;
    private $managerRegistry;

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var ExportManager
     */
    private $_exportManager;

    public function __construct(
        RequestStack $requestStack,
        ManagerRegistry $managerRegistry,
        ExportManager $exportManager,
        iterable $collectionExtensions
    ) {
        $this->_request = $requestStack->getCurrentRequest();
        $this->collectionExtensions = $collectionExtensions;
        $this->_exportManager = $exportManager;
        $this->managerRegistry = $managerRegistry;
    }

    public function supports(string $resourceClass, ?string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && 'ADMIN_exportAll' === $operationName;
    }

    public function getCollection(string $resourceClass, ?string $operationName = null, array $context = []): iterable
    {
        $exportType = (int) $this->_request->get(self::EXPORT_TYPE_PARAMETER);

        $this->_setSerializationGroups($exportType);

        if (self::EXPORT_EXTENDED === $exportType) {
            return $this->_exportManager->exportExtended();
        }

        $manager = $this->managerRegistry->getManagerForClass($resourceClass);

        /**
         * @var EntityRepository $repository
         */
        $repository = $manager->getRepository($resourceClass);
        $qb = $repository->createQueryBuilder('u');
        $qb
            ->where('u.status != :status')
            ->setParameter('status', User::STATUS_PSEUDONYMIZED)
        ;
        $queryNameGenerator = new QueryNameGenerator();

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($qb, $queryNameGenerator, $resourceClass, $operationName, $context);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operationName)) {
                $users = $extension->getResult($qb, $resourceClass, $operationName);
            }
        }

        return $users;
    }

    private function _setSerializationGroups(int $exportType)
    {
        $groups = [];

        $groups[] = isset(self::SERIALIZATION_GROUPS[$exportType]) ? self::SERIALIZATION_GROUPS[$exportType] : self::SERIALIZATION_GROUPS[0];

        $this->_request->attributes->set('_api_normalization_context', ['groups' => $groups]);
    }
}
