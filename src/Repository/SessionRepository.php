<?php

namespace App\Repository;

use App\Entity\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Session::class);
  }

  //    /**
  //     * @return Session[] Returns an array of Session objects
  //     */
  //    public function findByExampleField($value): array
  //    {
  //        return $this->createQueryBuilder('s')
  //            ->andWhere('s.exampleField = :val')
  //            ->setParameter('val', $value)
  //            ->orderBy('s.id', 'ASC')
  //            ->setMaxResults(10)
  //            ->getQuery()
  //            ->getResult()
  //        ;
  //    }

  //    public function findOneBySomeField($value): ?Session
  //    {
  //        return $this->createQueryBuilder('s')
  //            ->andWhere('s.exampleField = :val')
  //            ->setParameter('val', $value)
  //            ->getQuery()
  //            ->getOneOrNullResult()
  //        ;
  //    }

  public function findNonProgrammes($id)
  {
    $em = $this->getEntityManager();

    $qb = $em->createQueryBuilder();
    $qb->select('m')
      ->from('App\Entity\Module', 'm')
      ->leftJoin('m.programmes', 'pr')
      ->where('pr.session = :id');

    $sub = $em->createQueryBuilder();
    $sub->select('mo')
      ->from('App\Entity\Module', 'mo')
      ->where($sub->expr()->notIn('mo.id', $qb->getDQL()))
      ->setParameter('id', $id)
      ->orderBy('mo.nom_module', 'ASC');

    $query = $sub->getQuery();
    return $query->getResult();
  }
  // SELECT mo.nom_module
  // FROM module mo
  // WHERE mo.id NOT IN (
  //   SELECT m.id
  //   FROM module m
  //   LEFT JOIN programme pr ON pr.module_id = m.id
  //   WHERE pr.session_id = :id
  // )

  public function findNonStagiaires($id)
  {
    $em = $this->getEntityManager();

    $qb = $em->createQueryBuilder();
    $qb->select('s')
      ->from('App\Entity\Stagiaire', 's')
      ->leftJoin('s.sessions', 'ss')
      ->where('ss.id = :id');

    $sub = $em->createQueryBuilder();
    $sub->select('st')
      ->from('App\Entity\Stagiaire', 'st')
      ->where($sub->expr()->notIn('st.id', $qb->getDQL()))
      ->setParameter('id', $id)
      ->orderBy('st.nom', 'ASC');

    $query = $sub->getQuery();
    return $query->getResult();
  }
}
