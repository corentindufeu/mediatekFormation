<?php
namespace App\Controller\admin;

use App\Repository\CategorieRepository;
use App\Repository\PlaylistRepository;
use App\Repository\FormationRepository;
use App\Entity\Formation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controleur des formations
 *
 * @author Corentin Dufeu
 */
class AdminController extends AbstractController
{
    /**
     * @var FormationRepository
     */
    private $repository;
    
    /**
     * @param FormationRepository $repository
     */
    public function __construct(FormationRepository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * @Route("/admin/", name="admin")
     * @return Response
     */
    public function index(): Response
    {
       
        $formations = $this->repository->findAllLasted(2);
        return $this->render("admin/admin.accueil.html.twig", [
            'formations' => $formations
        ]);
    }
    
    /**
     * @Route("/admin/cgu", name="admin.cgu")
     * @return Response
     */
    public function cgu(): Response
    {
        return $this->render("pages/cgu.html.twig");
    }
}