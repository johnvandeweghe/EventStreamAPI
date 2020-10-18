<?php
namespace PostChat\Api;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use PostChat\Api\Entity\Event;
use PostChat\Api\Entity\Role;
use PostChat\Api\Entity\Stream;
use PostChat\Api\Entity\StreamUser;
use PostChat\Api\Entity\Subscription;
use PostChat\Api\Entity\User;
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

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        $this->modifyQuery($queryBuilder, $resourceClass, true);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ): void {
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
            case Role::class:
                $this->applyUserFilterToQueryBuilderForRole($queryBuilder, $user);
                break;
            case Stream::class:
                $this->applyUserFilterToQueryBuilderForStream($queryBuilder, $user, $isCollection);
                break;
            case StreamUser::class:
                $this->applyUserFilterToQueryBuilderForStreamUser($queryBuilder, $user);
                break;
            case Subscription::class:
                $this->applyUserFilterToQueryBuilderForSubscriptions($queryBuilder, $user);
                break;
            case Event::class:
                $this->applyUserFilterToQueryBuilderForEvent($queryBuilder, $user);
                break;
            case User::class:
                $this->applyUserFilterToQueryBuilderForUser($queryBuilder, $user);
                break;
        }
    }

    /**
     * Limits role queries to roles that the user can view.
     * Users can access roles in streams they are members of.
     * @param QueryBuilder $queryBuilder
     * @param UserInterface $user
     */
    private function applyUserFilterToQueryBuilderForRole(
        QueryBuilder $queryBuilder,
        UserInterface $user
    ): void {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->innerJoin("$rootAlias.stream", 'rs');
        $queryBuilder->innerJoin("rs.streamUsers", 'su', Expr\Join::WITH, "su.user = :userId");

        $queryBuilder->setParameter('userId', $user->getUsername());
    }

    /**
     * Limits stream queries to streams that the user can view.
     * Users can access stream they are members of, and discoverable children of those streams.
     * @param QueryBuilder $queryBuilder
     * @param UserInterface $user
     * @param bool $isCollection
     */
    private function applyUserFilterToQueryBuilderForStream(
        QueryBuilder $queryBuilder,
        UserInterface $user,
        bool $isCollection
    ): void {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->leftJoin("$rootAlias.owner", 'os');
        $queryBuilder->leftJoin("os.streamUsers", 'osu');

        //Order of joins here matters, when this was first the osu was broken by the os part being replaced incorrectly.
        $queryBuilder->leftJoin("$rootAlias.streamUsers", 'su');

        $queryBuilder->andWhere(
            //Either you are in the stream
            "su.user = :userId" .
            ($isCollection ?
                //Or you belong to it's parent and it's discoverable
                " OR (os is not null AND osu.user = :userId AND $rootAlias.discoverable = true)" :
                //Or you know it's id and it is root, or you belong to it's parent
                " OR os IS NULL OR (os is not null AND osu.user = :userId)"
            )
        );

        $queryBuilder->setParameter('userId', $user->getUsername());
    }

    /**
     * Limits stream user queries to stream users in streams that the user is directly a member of.
     * @param QueryBuilder $queryBuilder
     * @param UserInterface $user
     */
    private function applyUserFilterToQueryBuilderForStreamUser(
        QueryBuilder $queryBuilder,
        UserInterface $user
    ): void {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->innerJoin("$rootAlias.stream", 'us');
        $queryBuilder->innerJoin("us.streamUsers", 'su', Expr\Join::WITH, "su.user = :userId");

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

        $queryBuilder->innerJoin("$rootAlias.streamUser", 'su', Expr\Join::WITH, "su.user = :userId");
        $queryBuilder->setParameter('userId', $user->getUsername());
    }

    /**
     * Limits event queries to streams that the user is a member of.
     * Users can access streams they are members of, and discoverable children of those streams.
     * @param QueryBuilder $queryBuilder
     * @param UserInterface $user
     */
    private function applyUserFilterToQueryBuilderForEvent(QueryBuilder $queryBuilder, UserInterface $user): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->innerJoin("$rootAlias.stream", 'es');
        $queryBuilder->innerJoin("es.streamUsers", 'su');
        $queryBuilder->andWhere("su.user = :userId");
        $queryBuilder->setParameter('userId', $user->getUsername());
    }

    /**
     * Limits user queries to users in streams that the user is directly a member of.
     * @param QueryBuilder $queryBuilder
     * @param UserInterface $user
     */
    private function applyUserFilterToQueryBuilderForUser(
        QueryBuilder $queryBuilder,
        UserInterface $user
    ): void {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder->leftJoin("$rootAlias.streamUsers", 'usu');
        $queryBuilder->leftJoin("usu.stream", 'us');
        $queryBuilder->leftJoin("us.streamUsers", 'su');
        $queryBuilder->andWhere("$rootAlias.id = :userId OR su.user = :userId");


        $queryBuilder->setParameter('userId', $user->getUsername());
    }
}