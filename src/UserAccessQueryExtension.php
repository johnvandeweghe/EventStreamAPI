<?php
namespace Productively\Api;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Productively\Api\Entity\Event;
use Productively\Api\Entity\Group;
use Productively\Api\Entity\GroupMember;
use Productively\Api\Entity\Subscription;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * A query extension that applies the JWT user id to the query to limit which resources users have access to.
 */
final class UserAccessQueryExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $this->modifyQuery($queryBuilder, $resourceClass, true);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        $this->modifyQuery($queryBuilder, $resourceClass, false);
    }


    private function modifyQuery(QueryBuilder $queryBuilder, string $resourceClass, bool $isCollection): void
    {
        if (
            null === $user = $this->security->getUser()
        ) {
            return;
        }

        switch($resourceClass) {
            case Group::class:
                $this->applyUserFilterToQueryBuilderForGroup($queryBuilder, $user, $isCollection);
                break;
            case GroupMember::class:
                $this->applyUserFilterToQueryBuilderForGroupMember($queryBuilder, $user);
                break;
            case Subscription::class:
                $this->applyUserFilterToQueryBuilderForSubscriptions($queryBuilder, $user);
                break;
            case Event::class:
                $this->applyUserFilterToQueryBuilderForEvent($queryBuilder, $user);
                break;
        }
    }

    /**
     * Limits group queries to groups that the user can view.
     * Users can access groups they are members of, and discoverable children of those groups.
     * @param QueryBuilder $queryBuilder
     * @param UserInterface $user
     * @param bool $isCollection
     */
    private function applyUserFilterToQueryBuilderForGroup(
        QueryBuilder $queryBuilder,
        UserInterface $user,
        bool $isCollection
    ): void {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->leftJoin("$rootAlias.owner", 'og');
        $queryBuilder->leftJoin("og.groupMembers", 'ogm');

        //Order of joins here matters, when this was first the ogm was broken by the gm part being replaced incorrectly.
        $queryBuilder->leftJoin("$rootAlias.groupMembers", 'gm');

        $queryBuilder->andWhere(
            "gm.userIdentifier = :userId OR (og is not null and ogm.userIdentifier = :userId)" .
            (!$isCollection ? " OR $rootAlias.owner IS NULL" : "")
        );
        $queryBuilder->setParameter('userId', $user->getUsername());

        //TODO: Grant access to more than one layer of children

    }

    /**
     * Limits group member queries to group members in groups that the user is directly a member of.
     * @param QueryBuilder $queryBuilder
     * @param UserInterface $user
     */
    private function applyUserFilterToQueryBuilderForGroupMember(
        QueryBuilder $queryBuilder,
        UserInterface $user
    ): void {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->innerJoin("$rootAlias.userGroup", 'ug');
        $queryBuilder->innerJoin("ug.groupMembers", 'gm', Expr\Join::WITH, "gm.userIdentifier = :userId");
        $queryBuilder->setParameter('userId', $user->getUsername());

    }

    /**
     * Limits subscription queries to subs that the user directly has.
     * @param QueryBuilder $queryBuilder
     * @param UserInterface $user
     */
    private function applyUserFilterToQueryBuilderForSubscriptions(QueryBuilder $queryBuilder, UserInterface $user): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->innerJoin("$rootAlias.groupMember", 'gm', Expr\Join::WITH, "gm.userIdentifier = :userId");
        $queryBuilder->setParameter('userId', $user->getUsername());

    }
    /**
     * Limits event queries to groups that the user is a member of, and sorts them by time.
     * Users can access groups they are members of, and discoverable children of those groups.
     * @param QueryBuilder $queryBuilder
     * @param UserInterface $user
     */
    private function applyUserFilterToQueryBuilderForEvent(QueryBuilder $queryBuilder, UserInterface $user): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->innerJoin("$rootAlias.eventGroup", 'eg');
        $queryBuilder->innerJoin("eg.groupMembers", 'gm');
        $queryBuilder->andWhere("gm.userIdentifier = :userId");
        $queryBuilder->setParameter('userId', $user->getUsername());

        $queryBuilder->addOrderBy("$rootAlias.datetime", 'DESC');
    }
}