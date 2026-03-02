<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'app_admin_dashboard')]
    public function index(\Doctrine\ORM\EntityManagerInterface $em, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $period = $request->query->get('period', 'all'); // day, week, month, year, all
        $articleRepo = $em->getRepository(\App\Entity\Article::class);
        
        $startDate = null;
        switch ($period) {
            case 'day': $startDate = new \DateTime('today'); break;
            case 'week': $startDate = new \DateTime('-7 days'); break;
            case 'month': $startDate = new \DateTime('-30 days'); break;
            case 'year': $startDate = new \DateTime('-365 days'); break;
        }

        $getStats = function($repo, $status = null) use ($startDate, $em) {
            $qb = $repo->createQueryBuilder('e')->select('count(e.id)');
            
            if ($status) {
                $qb->where('e.status = :status')->setParameter('status', $status);
            }

            $class = $repo->getClassName();
            $meta = $em->getClassMetadata($class);
            if ($startDate && $meta->hasField('createdAt')) {
                if ($status) {
                    $qb->andWhere("e.createdAt >= :start");
                } else {
                    $qb->where("e.createdAt >= :start");
                }
                $qb->setParameter('start', $startDate);
            }

            return (int)$qb->getQuery()->getSingleScalarResult();
        };

        $totalArticles = $getStats($articleRepo, 'published');
        $totalDrafts = $getStats($articleRepo, 'draft');
        $totalCategories = $getStats($em->getRepository(\App\Entity\Category::class));
        $totalPromos = $getStats($em->getRepository(\App\Entity\Promo::class));

        // Chart data generation based on period
        $chartData = [];
        $articleRepo = $em->getRepository(\App\Entity\Article::class);

        switch ($period) {
            case 'day':
                // 2-hourly stats for today (00:00 to 24:00)
                for ($h = 0; $h <= 24; $h += 2) {
                    $startOfInterval = (new \DateTime('today'))->setTime(min($h, 23), 0);
                    if ($h == 24) $startOfInterval = (new \DateTime('tomorrow'))->setTime(0,0);
                    
                    $endOfInterval = (clone $startOfInterval)->modify('+1 hour 59 minutes 59 seconds');
                    if ($h == 24) $endOfInterval = (clone $startOfInterval)->modify('+1 second'); // just a point
                    
                    $count = $articleRepo->createQueryBuilder('a')
                        ->select('count(a.id)')
                        ->where('a.createdAt >= :start AND a.createdAt < :end')
                        ->setParameter('start', $startOfInterval)
                        ->setParameter('end', $endOfInterval)
                        ->getQuery()->getSingleScalarResult();
                    $chartData[] = ['label' => sprintf("%02d:00", $h), 'value' => (int)$count];
                }
                break;

            case 'week':
                // Daily stats for last 7 days
                for ($i = 6; $i >= 0; $i--) {
                    $start = (new \DateTime("-$i days"))->setTime(0,0);
                    $end = (clone $start)->setTime(23,59,59);
                    $count = $articleRepo->createQueryBuilder('a')
                        ->select('count(a.id)')
                        ->where('a.createdAt >= :start AND a.createdAt <= :end')
                        ->setParameter('start', $start)
                        ->setParameter('end', $end)
                        ->getQuery()->getSingleScalarResult();
                    $chartData[] = ['label' => $start->format('d.m'), 'value' => (int)$count];
                }
                break;

            case 'month':
                // Stats for last 30 days grouped
                for ($i = 28; $i >= 0; $i -= 4) {
                    $start = (new \DateTime("-$i days"))->setTime(0,0);
                    $end = (clone $start)->modify('+3 days')->setTime(23,59,59);
                    $count = $articleRepo->createQueryBuilder('a')
                        ->select('count(a.id)')
                        ->where('a.createdAt >= :start AND a.createdAt <= :end')
                        ->setParameter('start', $start)
                        ->setParameter('end', $end)
                        ->getQuery()->getSingleScalarResult();
                    $chartData[] = ['label' => $start->format('d.m'), 'value' => (int)$count];
                }
                break;

            default: // year or all
                $monthsArr = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
                $year = (int)date('Y');
                foreach (range(1, 12) as $m) {
                    $start = new \DateTime("$year-$m-01 00:00:00");
                    $end = (clone $start)->modify('last day of this month')->setTime(23, 59, 59);
                    $count = $articleRepo->createQueryBuilder('a')
                        ->select('count(a.id)')
                        ->where('a.createdAt >= :start AND a.createdAt <= :end')
                        ->setParameter('start', $start)
                        ->setParameter('end', $end)
                        ->getQuery()->getSingleScalarResult();
                    $chartData[] = ['label' => $monthsArr[$m-1], 'value' => (int)$count];
                }
                break;
        }

        return $this->render('admin/dashboard.html.twig', [
            'stats' => [
                'articles' => $totalArticles,
                'drafts' => $totalDrafts,
                'categories' => $totalCategories,
                'promos' => $totalPromos
            ],
            'chartData' => $chartData,
            'currentPeriod' => $period
        ]);
    }
}
