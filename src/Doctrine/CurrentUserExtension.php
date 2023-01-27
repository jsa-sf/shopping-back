<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Product;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($resourceClass, $queryBuilder);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($resourceClass, $queryBuilder);
    }

    /**
     * @param string $resourceClass
     * @param QueryBuilder $queryBuilder
     * @return void
     */
    private function addWhere(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        $user = $this->security->getUser();
        if ($resourceClass === Product::class && $user) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->orderBy("$rootAlias.id", "DESC")
                ->join("$rootAlias.basket", 'b')
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        $queryBuilder->expr()->eq('b.user', ':current_user'),
                        $queryBuilder->expr()->eq("$rootAlias.sharedUser", ':current_user')
                    )
                )
                ->setParameter('current_user', $user->getId());
        }
    }
}