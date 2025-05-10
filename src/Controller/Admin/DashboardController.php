<?php

namespace App\Controller\Admin;

use App\Repository\ArticleCommandeRepository;
use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(
        ArticleCommandeRepository $acRepo,
        CommandeRepository $cRepo
    ): Response {
      
        $to = new \DateTimeImmutable('now');
        $from = $to->modify('-30 days');

      
        $best = $acRepo->findBestSellingBook($from, $to);
     
        $raw = $cRepo->countOrdersPerDay($from, $to);

      
        $ordersChart = [['Date', 'Commandes']];
        foreach ($raw as $row) {
            $date = sprintf('%04d-%02d-%02d', $row['year'], $row['month'], $row['day']);
            $ordersChart[] = [ $date, (int) $row['nbCommandes'] ];
        }

        return $this->render('admin/dashboard.html.twig', [
            'bestBook'    => $best,
            'ordersChart' => json_encode($ordersChart),
            'periodFrom'  => $from->format('Y-m-d'),
            'periodTo'    => $to->format('Y-m-d'),
        ]);
    }
}